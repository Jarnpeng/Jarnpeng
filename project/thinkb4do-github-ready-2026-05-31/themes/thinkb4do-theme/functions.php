<?php
/**
 * Thinkb4do Theme Functions
 *
 * @package Thinkb4do
 * @version 2.4.12
 */

defined( 'ABSPATH' ) || exit;

define( 'TB4_VERSION', '2.4.12' );
define( 'TB4_DIR', get_template_directory() );
define( 'TB4_URI', get_template_directory_uri() );

// v2.2.2: Smart Daily Sidebar uses public daily-life ideas only and avoids revealing backend/private connection details.
require_once TB4_DIR . '/inc/tb4-page-sync.php';


/* =============================================
   v1.7.5 Native WordPress Admin Bar Rescue Lock
   เป้าหมาย: คืน Admin Bar ต้นทางของ WordPress ให้แสดงเสมอเมื่อผู้ใช้ล็อกอินอยู่หน้าเว็บ
   ไม่สร้างแถบ custom ใหม่ และไม่ดัดแปลงโครงสร้าง toolbar ของ WordPress
   ============================================= */
function tb4_restore_native_frontend_admin_bar( $show ) {
    if ( is_user_logged_in() && ! is_admin() ) {
        return true;
    }
    return $show;
}
add_filter( 'show_admin_bar', 'tb4_restore_native_frontend_admin_bar', PHP_INT_MAX );

function tb4_force_native_admin_bar_on_frontend() {
    if ( is_user_logged_in() && ! is_admin() ) {
        show_admin_bar( true );
    }
}
add_action( 'after_setup_theme', 'tb4_force_native_admin_bar_on_frontend', PHP_INT_MAX );
add_action( 'wp', 'tb4_force_native_admin_bar_on_frontend', PHP_INT_MAX );

function tb4_native_admin_bar_rescue_css() {
    if ( ! is_user_logged_in() || is_admin() ) {
        return;
    }
    ?>
    <style id="tb4-native-wp-adminbar-rescue-v175">
      html { margin-top: 32px !important; }
      @media screen and (max-width: 782px) { html { margin-top: 46px !important; } }
      #wpadminbar {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
        min-width: 0 !important;
        z-index: 2147483647 !important;
        pointer-events: auto !important;
        transform: none !important;
        filter: none !important;
      }
      #wpadminbar * { pointer-events: auto !important; }
      body.admin-bar.thinkb4do-theme.tb4-sticky-header #site-header,
      body.admin-bar #site-header { top: var(--tb4-sticky-top, 32px) !important; }
      @media screen and (max-width: 782px) {
        body.admin-bar.thinkb4do-theme.tb4-sticky-header #site-header,
        body.admin-bar #site-header { top: var(--tb4-sticky-top, 46px) !important; }
      }
    </style>
    <?php
}
add_action( 'wp_head', 'tb4_native_admin_bar_rescue_css', PHP_INT_MAX );

/* =============================================
   THEME SETUP
   ============================================= */
function tb4_setup() {
    load_theme_textdomain( 'thinkb4do', TB4_DIR . '/languages' );

    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [ 'search-form','comment-form','comment-list','gallery','caption','style','script' ] );
    add_theme_support( 'customize-selective-refresh-widgets' );
    add_theme_support( 'woocommerce' );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'custom-logo', [
        'height'               => 120,
        'width'                => 360,
        'flex-height'          => true,
        'flex-width'           => true,
        'unlink-homepage-logo' => true,
    ] );

    add_image_size( 'tb4-card',   400, 300, true );
    add_image_size( 'tb4-thumb',  600, 400, true );
    add_image_size( 'tb4-hero',   1280, 640, true );
    add_image_size( 'tb4-avatar', 160, 160, true );

    register_nav_menus([
        'primary'  => __( 'เมนูหลัก', 'thinkb4do' ),
        'footer-1' => __( 'เมนู Footer 1', 'thinkb4do' ),
        'footer-2' => __( 'เมนู Footer 2', 'thinkb4do' ),
        'footer-3' => __( 'เมนู Footer 3', 'thinkb4do' ),
    ]);
}
add_action( 'after_setup_theme', 'tb4_setup' );

/* =============================================
   CONTENT WIDTH
   ============================================= */
function tb4_content_width() {
    $GLOBALS['content_width'] = 1280;
}
add_action( 'after_setup_theme', 'tb4_content_width', 0 );

/* =============================================
   SCRIPTS & STYLES
   ============================================= */
function tb4_scripts() {
    // Google Fonts — Kanit
    wp_enqueue_style(
        'tb4-google-fonts',
        'https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600;700;800&family=Roboto:wght@300;400;500;700&family=Kanit:wght@300;400;500;600;700;800&display=swap',
        [], null
    );

    // Main stylesheet
    wp_enqueue_style(
        'thinkb4do-style',
        get_stylesheet_uri(),
        [ 'tb4-google-fonts' ],
        TB4_VERSION
    );

    // Phosphor Icons (lightweight icon library)
    wp_enqueue_style(
        'tb4-icons',
        'https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css',
        [], '2.1.1'
    );

    // Compatibility bootstrap — small ES5 script loaded early for no-js, device and browser feature classes.
    wp_enqueue_script(
        'thinkb4do-compat',
        TB4_URI . '/js/compat.js',
        [], TB4_VERSION, false
    );

    // Main JS
    wp_enqueue_script(
        'thinkb4do-main',
        TB4_URI . '/js/main.js',
        [ 'thinkb4do-compat' ], TB4_VERSION, true
    );

    // v2.4.2 Performance Bundle — รวม CSS layer หลายไฟล์ให้เหลือไฟล์เดียวเพื่อลด request และลดความหน่วงบนมือถือ.
    wp_enqueue_style(
        'thinkb4do-performance-bundle-v242',
        TB4_URI . '/css/tb4-performance-bundle-v242.css',
        [ 'thinkb4do-style' ],
        TB4_VERSION
    );

    // v2.4.2 Performance JS Bundle — รวมสคริปต์ UI หลายชุดให้เหลือชุดเดียว โหลดแบบ defer เพื่อลด main-thread blocking.
    wp_enqueue_script(
        'thinkb4do-performance-bundle-v242',
        TB4_URI . '/js/tb4-performance-bundle-v242.js',
        [ 'thinkb4do-main' ],
        TB4_VERSION,
        true
    );

    // v2.4.12 Plugin Safe Shell — เคลียร์หน้าที่ปลั๊กอินเป็นเจ้าของ ไม่ให้ธีมซ้อน/ทับ UI ของปลั๊กอินเดิม.
    wp_enqueue_style(
        'thinkb4do-plugin-safe-shell-v2412',
        TB4_URI . '/css/tb4-plugin-safe-shell-v2412.css',
        [ 'thinkb4do-performance-bundle-v242' ],
        TB4_VERSION
    );

    // v2.4.4 Header Search Fix — ย้ายไปควบคุมในปลั๊กอิน Thinkb4do Header Menu แล้ว
    // ถ้าปลั๊กอินยังไม่เปิด ให้ธีมโหลด fallback assets เดิมเพื่อความปลอดภัย
    if ( ! defined( 'TB4_HEADER_MENU_PLUGIN_VERSION' ) ) {
        wp_enqueue_style(
            'thinkb4do-header-search-fix-v244',
            TB4_URI . '/css/tb4-header-search-fix-v244.css',
            [ 'thinkb4do-performance-bundle-v242' ],
            TB4_VERSION
        );

        wp_enqueue_script(
            'thinkb4do-header-search-fix-v244',
            TB4_URI . '/js/tb4-header-search-fix-v244.js',
            [ 'thinkb4do-main' ],
            TB4_VERSION,
            true
        );
    }

    // Localize script data
    wp_localize_script( 'thinkb4do-main', 'tb4Data', [
        'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
        'nonce'     => wp_create_nonce( 'tb4_nonce' ),
        'siteUrl'   => get_site_url(),
        'isLoggedIn'=> is_user_logged_in() ? 'yes' : 'no',
        'compat'   => [
            'browserNoticeEnabled' => (bool) get_theme_mod( 'tb4_compat_browser_notice', true ),
            'browserNoticeTitle'   => tb4_compat_setting( 'browser_notice_title' ),
            'browserNoticeBody'    => tb4_compat_setting( 'browser_notice_body' ),
        ],
    ]);

    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}
add_action( 'wp_enqueue_scripts', 'tb4_scripts' );


/* =============================================
   v2.4.2 PERFORMANCE LITE
   ลดความหน่วงโดยไม่รื้อ UI เดิม:
   - ปิด assets WordPress ที่ไม่จำเป็น
   - ใส่ defer ให้ JS ฝั่งธีม
   - เพิ่ม resource hints ให้ fonts/icons
   - เพิ่ม lazy/async ให้ media
   ============================================= */
function tb4_v242_disable_unused_core_assets() {
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'admin_print_styles', 'print_emoji_styles' );
    remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
    remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
    remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
    remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
    remove_action( 'wp_head', 'wp_oembed_add_host_js' );
}
add_action( 'init', 'tb4_v242_disable_unused_core_assets' );

function tb4_v242_resource_hints( $urls, $relation_type ) {
    if ( 'preconnect' === $relation_type ) {
        $urls[] = 'https://fonts.googleapis.com';
        $urls[] = 'https://fonts.gstatic.com';
        $urls[] = 'https://unpkg.com';
    }
    if ( 'dns-prefetch' === $relation_type ) {
        $urls[] = '//fonts.googleapis.com';
        $urls[] = '//fonts.gstatic.com';
        $urls[] = '//unpkg.com';
    }
    return array_values( array_unique( $urls ) );
}
add_filter( 'wp_resource_hints', 'tb4_v242_resource_hints', 10, 2 );

function tb4_v242_defer_theme_scripts( $tag, $handle, $src ) {
    $defer_handles = [
        'thinkb4do-main',
        'thinkb4do-performance-bundle-v242',
    ];

    if ( in_array( $handle, $defer_handles, true ) && false === strpos( $tag, ' defer' ) ) {
        return str_replace( ' src=', ' defer src=', $tag );
    }

    return $tag;
}
add_filter( 'script_loader_tag', 'tb4_v242_defer_theme_scripts', 10, 3 );

function tb4_v242_image_performance_attrs( $attr ) {
    if ( empty( $attr['decoding'] ) ) {
        $attr['decoding'] = 'async';
    }
    if ( empty( $attr['loading'] ) ) {
        $attr['loading'] = 'lazy';
    }
    return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'tb4_v242_image_performance_attrs', 10, 1 );


/* =============================================
   WIDGETS
   ============================================= */
function tb4_widgets_init() {
    $defaults = [
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ];

    register_sidebar( array_merge( $defaults, [
        'name' => __( 'Sidebar หลัก', 'thinkb4do' ),
        'id'   => 'sidebar-main',
    ]));

    register_sidebar( array_merge( $defaults, [
        'name' => __( 'Footer พื้นที่ 1', 'thinkb4do' ),
        'id'   => 'footer-1',
    ]));

    register_sidebar( array_merge( $defaults, [
        'name' => __( 'Footer พื้นที่ 2', 'thinkb4do' ),
        'id'   => 'footer-2',
    ]));
}
add_action( 'widgets_init', 'tb4_widgets_init' );

/* =============================================
   CUSTOM POST TYPES
   ============================================= */
function tb4_register_cpts() {
    // Products / comparison systems are now owned by dedicated plugins.
    // Theme registers only fallback CPTs when no product plugin is active, so it will not duplicate admin menus or rewrite rules.
    $products_plugin_active = defined( 'TB4_PRODUCTS_PLUGIN_VERSION' ) || class_exists( 'TB4_Products_Plugin' ) || post_type_exists( 'tb4_product' );

    if ( ! $products_plugin_active ) {
        register_post_type( 'tb4_product', [
            'labels' => [
                'name'          => __( 'ผลิตภัณฑ์', 'thinkb4do' ),
                'singular_name' => __( 'ผลิตภัณฑ์', 'thinkb4do' ),
                'add_new_item'  => __( 'เพิ่มผลิตภัณฑ์', 'thinkb4do' ),
            ],
            'public'       => true,
            'has_archive'  => true,
            'show_in_rest' => true,
            'supports'     => [ 'title', 'editor', 'thumbnail', 'custom-fields' ],
            'rewrite'      => [ 'slug' => 'products' ],
            'menu_icon'    => 'dashicons-products',
        ]);

        register_post_type( 'tb4_compare_item', [
            'labels' => [
                'name'          => __( 'รายการเปรียบเทียบ', 'thinkb4do' ),
                'singular_name' => __( 'รายการเปรียบเทียบ', 'thinkb4do' ),
            ],
            'public'       => false,
            'show_ui'      => true,
            'show_in_rest' => true,
            'supports'     => [ 'title', 'custom-fields' ],
            'menu_icon'    => 'dashicons-chart-bar',
        ]);
    }
}
add_action( 'init', 'tb4_register_cpts' );

/* =============================================
   CUSTOM TAXONOMIES
   ============================================= */
function tb4_register_taxonomies() {
    $products_plugin_active = defined( 'TB4_PRODUCTS_PLUGIN_VERSION' ) || class_exists( 'TB4_Products_Plugin' );

    if ( ! $products_plugin_active && post_type_exists( 'tb4_product' ) ) {
        register_taxonomy( 'tb4_product_cat', 'tb4_product', [
            'label'        => __( 'หมวดหมู่ผลิตภัณฑ์', 'thinkb4do' ),
            'hierarchical' => true,
            'show_in_rest' => true,
            'rewrite'      => [ 'slug' => 'product-cat' ],
        ]);
    }

    if ( ! $products_plugin_active && post_type_exists( 'tb4_compare_item' ) ) {
        register_taxonomy( 'tb4_compare_cat', 'tb4_compare_item', [
            'label'        => __( 'หมวดเปรียบเทียบ', 'thinkb4do' ),
            'hierarchical' => true,
            'show_in_rest' => true,
        ]);
    }
}
add_action( 'init', 'tb4_register_taxonomies' );

/* =============================================
   HEADER SETTINGS HELPERS
   ============================================= */

function tb4_sanitize_sidebar_mode( $value ) {
    $allowed = [ 'smart', 'mixed', 'wordpress' ];
    return in_array( $value, $allowed, true ) ? $value : 'smart';
}

function tb4_sanitize_header_sticky_mode( $value ) {
    $allowed = [ 'all', 'home', 'none' ];
    return in_array( $value, $allowed, true ) ? $value : 'all';
}

function tb4_get_header_sticky_mode() {
    return tb4_sanitize_header_sticky_mode( get_theme_mod( 'tb4_header_sticky_mode', 'all' ) );
}

function tb4_should_use_sticky_header() {
    $mode = tb4_get_header_sticky_mode();
    if ( 'none' === $mode ) {
        return false;
    }
    if ( 'home' === $mode ) {
        return is_front_page();
    }
    return true;
}


/* =============================================
   BRAND LOGO / MEDIA LIBRARY
   v2.3.4: Logo clarity + auto light/dark logo selection
   ============================================= */
function tb4_sanitize_logo_variant_mode( $value ) {
    $allowed = [ 'auto', 'dark', 'light' ];
    return in_array( $value, $allowed, true ) ? $value : 'auto';
}

function tb4_get_logo_variant_mode() {
    return tb4_sanitize_logo_variant_mode( get_theme_mod( 'tb4_logo_variant_mode', 'auto' ) );
}

/**
 * Dark logo = dark/normal logo for light backgrounds.
 * Light logo = light/white logo for dark backgrounds.
 */
function tb4_get_logo_variant_id( $variant = 'dark' ) {
    $variant = 'light' === $variant ? 'light' : 'dark';

    if ( 'light' === $variant ) {
        $light_id = absint( get_theme_mod( 'tb4_brand_logo_light_id', 0 ) );
        if ( $light_id ) {
            return $light_id;
        }
    }

    $dark_id = absint( get_theme_mod( 'tb4_brand_logo_dark_id', 0 ) );
    if ( $dark_id ) {
        return $dark_id;
    }

    $legacy_id = absint( get_theme_mod( 'tb4_brand_logo_id', 0 ) );
    if ( $legacy_id ) {
        return $legacy_id;
    }

    return absint( get_theme_mod( 'custom_logo', 0 ) );
}

function tb4_get_brand_logo_id() {
    return tb4_get_logo_variant_id( 'dark' );
}

function tb4_get_brand_logo_url( $variant = 'dark' ) {
    $logo_id = tb4_get_logo_variant_id( $variant );
    if ( ! $logo_id ) {
        return '';
    }

    $src = wp_get_attachment_image_src( $logo_id, 'full' );
    return $src ? esc_url_raw( $src[0] ) : '';
}

function tb4_get_logo_context_default_variant( $context = 'header' ) {
    $context = sanitize_key( $context );

    // Footer is quiet/light from v2.3.3, so dark logo is safer by default.
    $defaults = [
        'header' => 'dark',
        'footer' => 'dark',
    ];

    return $defaults[ $context ] ?? 'dark';
}

function tb4_render_logo_variant_image( $logo_id, $variant, $site_name ) {
    if ( ! $logo_id ) {
        return '';
    }

    return wp_get_attachment_image( $logo_id, 'full', false, [
        'class'         => 'site-logo-img site-logo-img--' . sanitize_html_class( $variant ),
        'alt'           => '',
        'loading'       => 'eager',
        'decoding'      => 'async',
        'aria-hidden'   => 'true',
        'data-variant'  => sanitize_key( $variant ),
    ] );
}

function tb4_render_site_logo( $context = 'header' ) {
    $context       = sanitize_key( $context );
    $dark_logo_id  = tb4_get_logo_variant_id( 'dark' );
    $light_logo_id = tb4_get_logo_variant_id( 'light' );
    $has_dark      = (bool) $dark_logo_id;
    $has_light     = $light_logo_id && $light_logo_id !== $dark_logo_id;
    $logo_id       = $dark_logo_id ?: $light_logo_id;
    $site_name     = get_bloginfo( 'name' );
    $tagline       = get_theme_mod( 'tb4_tagline', 'Think before you do.' );
    $show_text     = (bool) get_theme_mod( 'tb4_logo_show_text', true );
    $show_tagline  = ( 'header' === $context ) ? (bool) get_theme_mod( 'tb4_logo_show_tagline_header', true ) : (bool) get_theme_mod( 'tb4_logo_show_tagline_footer', false );
    $logo_mode     = tb4_get_logo_variant_mode();
    $default       = tb4_get_logo_context_default_variant( $context );
    $classes       = [
        'site-logo',
        'site-logo--' . $context,
        $logo_id ? 'site-logo--has-image' : 'site-logo--text-only',
        'site-logo--mode-' . $logo_mode,
        'site-logo--default-' . $default,
    ];

    if ( $has_dark ) {
        $classes[] = 'site-logo--has-dark-logo';
    }
    if ( $has_light ) {
        $classes[] = 'site-logo--has-light-logo';
    }

    echo '<a href="' . esc_url( home_url( '/' ) ) . '" class="' . esc_attr( implode( ' ', $classes ) ) . '" data-logo-mode="' . esc_attr( $logo_mode ) . '" data-logo-context="' . esc_attr( $context ) . '" aria-label="' . esc_attr( $site_name ) . '">';

    if ( $logo_id ) {
        $wrap_classes = [ 'site-logo-img-wrap' ];
        if ( $has_light ) {
            $wrap_classes[] = 'tb4-logo-switch';
        }

        echo '<span class="' . esc_attr( implode( ' ', $wrap_classes ) ) . '" aria-hidden="true">';
        $dark_image = tb4_render_logo_variant_image( $dark_logo_id ?: $light_logo_id, 'dark', $site_name );
        if ( $dark_image ) {
            echo $dark_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
        if ( $has_light ) {
            $light_image = tb4_render_logo_variant_image( $light_logo_id, 'light', $site_name );
            if ( $light_image ) {
                echo $light_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }
        }
        echo '</span>';
    } else {
        echo '<span class="logo-icon" aria-hidden="true">T</span>';
    }

    if ( $show_text || $show_tagline ) {
        echo '<span class="site-logo-copy">';
        if ( $show_text ) {
            echo '<span class="logo-text">' . esc_html( $site_name ) . '</span>';
        }
        if ( $show_tagline && $tagline ) {
            echo '<span class="logo-tagline">' . esc_html( $tagline ) . '</span>';
        }
        echo '</span>';
    }

    echo '</a>';
}



/* =============================================
   v2.4.0 Community Clean Shell
   Hide theme chrome that duplicates the dedicated community experience.
   ============================================= */
function tb4_is_community_context() {
    if ( is_admin() ) {
        return false;
    }

    if ( is_page( [ 'community', 'thinkb4do-community', 'ชุมชน' ] ) ) {
        return true;
    }

    $queried = get_queried_object();
    if ( $queried instanceof WP_Post ) {
        $slug  = (string) $queried->post_name;
        $title = (string) get_the_title( $queried );
        $body  = (string) $queried->post_content;

        if ( in_array( $slug, [ 'community', 'thinkb4do-community' ], true ) ) {
            return true;
        }

        $thai_title_pos = function_exists( 'mb_stripos' ) ? mb_stripos( $title, 'ชุมชน' ) : stripos( $title, 'ชุมชน' );
        if ( false !== $thai_title_pos || false !== stripos( $title, 'community' ) ) {
            return true;
        }

        $community_markers = [
            '[thinkb4do_community',
            'wp:thinkb4do/community',
            'thinkb4do/community',
            'tb4-community',
        ];

        foreach ( $community_markers as $marker ) {
            if ( false !== stripos( $body, $marker ) ) {
                return true;
            }
        }
    }

    if ( is_singular( [ 'tb4_community', 'tb4_post', 'community_post', 'community' ] ) ) {
        return true;
    }

    if ( is_post_type_archive( [ 'tb4_community', 'tb4_post', 'community_post', 'community' ] ) ) {
        return true;
    }

    $request_uri  = wp_unslash( $_SERVER['REQUEST_URI'] ?? '' );
    $request_path = trailingslashit( '/' . ltrim( (string) wp_parse_url( $request_uri, PHP_URL_PATH ), '/' ) );
    $community_paths = [ '/community/', '/thinkb4do-community/', '/ชุมชน/' ];

    foreach ( $community_paths as $path ) {
        if ( 0 === strpos( $request_path, $path ) ) {
            return true;
        }
    }

    return (bool) apply_filters( 'tb4_is_community_context', false );
}

function tb4_should_hide_footer_on_community() {
    return tb4_is_community_context();
}

function tb4_remove_community_link_items( $items ) {
    if ( ! tb4_is_community_context() ) {
        return $items;
    }

    return array_values( array_filter( (array) $items, function ( $item ) {
        $url   = is_array( $item ) ? (string) ( $item['url'] ?? $item[0] ?? '' ) : '';
        $label = is_array( $item ) ? (string) ( $item['label'] ?? $item[1] ?? '' ) : '';
        $haystack = strtolower( $url . ' ' . $label );

        return false === strpos( $haystack, '/community' )
            && false === strpos( $haystack, 'thinkb4do-community' )
            && false === strpos( $haystack, 'ชุมชน' );
    } ) );
}

add_filter( 'body_class', function ( $classes ) {
    if ( tb4_is_community_context() ) {
        $classes[] = 'tb4-is-community-page';
        $classes[] = 'tb4-community-clean-shell';
    }

    return $classes;
} );



/* v2.4.9: Member label normalization moved out of theme to avoid duplicate member/member-area behavior. */

/* =============================================
   v2.3.7 Minimal Center Menu + App Grid Products
   เป้าหมาย: เมนูกลางเหลือเฉพาะ หน้าแรก / ชุมชน / ผลิตภัณฑ์ / ช่องค้นหา
   และเพิ่มปุ่ม 9 ช่องสำหรับเปิดเมนูและผลิตภัณฑ์แบบเห็นผลจริงบน Header
   ============================================= */
function tb4_get_minimal_header_links() {
    $product_url = home_url( '/products/' );
    if ( post_type_exists( 'tb4_product' ) ) {
        $archive = get_post_type_archive_link( 'tb4_product' );
        if ( $archive ) {
            $product_url = $archive;
        }
    }

    $links = [
        [
            'url'   => home_url( '/' ),
            'label' => 'Discovery',
            'match' => [ '/' ],
        ],
        [
            'url'   => home_url( '/community/' ),
            'label' => 'ชุมชน',
            'match' => [ '/community/', '/thinkb4do-community/' ],
        ],
        [
            'url'   => $product_url,
            'label' => 'ผลิตภัณฑ์',
            'match' => [ '/products/', '/product-cat/' ],
        ],
    ];

    return apply_filters( 'tb4_minimal_header_links', $links );
}

function tb4_is_minimal_header_link_active( $item ) {
    $request_uri  = wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' );
    $request_path = wp_parse_url( $request_uri, PHP_URL_PATH );
    $request_path = trailingslashit( '/' . ltrim( (string) $request_path, '/' ) );

    $item_url  = $item['url'] ?? '';
    $item_path = trailingslashit( wp_parse_url( $item_url, PHP_URL_PATH ) ?: '/' );

    if ( '/' === $item_path ) {
        return is_front_page() || '/' === $request_path;
    }

    if ( $item_path && 0 === strpos( $request_path, $item_path ) ) {
        return true;
    }

    foreach ( (array) ( $item['match'] ?? [] ) as $match_path ) {
        $match_path = trailingslashit( '/' . trim( $match_path, '/' ) );
        if ( '/' !== $match_path && 0 === strpos( $request_path, $match_path ) ) {
            return true;
        }
    }

    return false;
}

function tb4_render_minimal_header_nav( $context = 'desktop' ) {
    $context = 'mobile' === $context ? 'mobile' : 'desktop';
    $links   = tb4_get_minimal_header_links();

    if ( 'mobile' === $context ) {
        echo '<ul class="tb4-mobile-nav-list tb4-mobile-nav-list--minimal">';
    }

    foreach ( $links as $item ) {
        $url    = $item['url'] ?? '#';
        $label  = $item['label'] ?? '';
        $active = tb4_is_minimal_header_link_active( $item );
        $class  = $active ? 'active' : '';
        $aria   = $active ? ' aria-current="page"' : '';
        $anchor = '<a href="' . esc_url( $url ) . '" class="' . esc_attr( trim( $class ) ) . '"' . $aria . '>' . esc_html( $label ) . '</a>';

        if ( 'mobile' === $context ) {
            echo '<li>' . $anchor . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        } else {
            echo $anchor; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
    }

    if ( 'mobile' === $context ) {
        echo '</ul>';
    }
}

function tb4_get_app_grid_groups() {
    $product_url = home_url( '/products/' );
    if ( post_type_exists( 'tb4_product' ) ) {
        $archive = get_post_type_archive_link( 'tb4_product' );
        if ( $archive ) {
            $product_url = $archive;
        }
    }

    $groups = [
        'menu' => [
            'title' => 'เมนูหลัก',
            'items' => [
                [ home_url( '/' ), 'Discovery', 'เริ่มต้นค้นพบฟีดและพื้นที่หลัก' ],
                [ home_url( '/community/' ), 'ชุมชน', 'พื้นที่พูดคุยและติดตาม' ],
                [ $product_url, 'ผลิตภัณฑ์', 'รวมสิ่งที่ Thinkb4do ทำ' ],
            ],
        ],
        'products' => [
            'title' => 'ผลิตภัณฑ์',
            'items' => [
                [ $product_url, 'ผลิตภัณฑ์ทั้งหมด', 'ดูรายการทั้งหมด' ],
                [ home_url( '/products/think-control/' ), 'Think Control', 'ระบบควบคุมและจัดการ' ],
                [ home_url( '/products/aira-studio/' ), 'AiRA Studio', 'พื้นที่สร้างและพัฒนา' ],
                [ home_url( '/contact/' ), 'ติดต่อเรา', 'สอบถามหรือเสนอความต้องการ' ],
            ],
        ],
    ];

    return apply_filters( 'tb4_app_grid_groups', $groups );
}

function tb4_render_app_grid_button() {
    ?>
    <div class="tb4-app-grid-wrap">
      <button class="header-icon-btn tb4-app-grid-trigger" id="tb4AppGridTrigger" type="button" aria-label="เมนูและผลิตภัณฑ์" title="เมนูและผลิตภัณฑ์" aria-haspopup="dialog" aria-expanded="false" aria-controls="tb4AppGridPanel">
        <span class="tb4-nine-grid-icon" aria-hidden="true">
          <span></span><span></span><span></span>
          <span></span><span></span><span></span>
          <span></span><span></span><span></span>
        </span>
      </button>
    </div>
    <?php
}


function tb4_mobile_nav_items() {
    $product_url = home_url( '/products/' );
    if ( post_type_exists( 'tb4_product' ) ) {
        $archive = get_post_type_archive_link( 'tb4_product' );
        if ( $archive ) {
            $product_url = $archive;
        }
    }

    return apply_filters( 'tb4_mobile_nav_items', [
        [ 'type' => 'link',   'slug' => 'home',      'label' => 'Discovery',   'url' => home_url( '/' ) ],
        [ 'type' => 'link',   'slug' => 'community', 'label' => 'ชุมชน',       'url' => home_url( '/community/' ) ],
        [ 'type' => 'link',   'slug' => 'products',  'label' => 'สินค้า',       'url' => $product_url ],
    ] );
}

function tb4_get_mobile_nav_icon_svg( $slug ) {
    $icons = [
        'home' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M3.75 10.5 12 4l8.25 6.5v8.25a1.5 1.5 0 0 1-1.5 1.5h-4.5v-6h-4.5v6h-4.5a1.5 1.5 0 0 1-1.5-1.5z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'community' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M8.25 11.25a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm7.5 1.5a2.625 2.625 0 1 0 0-5.25 2.625 2.625 0 0 0 0 5.25ZM3.75 18.75a4.5 4.5 0 0 1 9 0M13.5 18.75a3.75 3.75 0 0 1 6.75-2.25" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'products' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4.5 7.5h15l-1.2 9.6A1.5 1.5 0 0 1 16.82 18.5H7.18a1.5 1.5 0 0 1-1.48-1.4L4.5 7.5Zm3-1.5a4.5 4.5 0 0 1 9 0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'search' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M10.5 18a7.5 7.5 0 1 1 5.3-2.2L21 21" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'menu' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><g fill="currentColor"><circle cx="5" cy="5" r="1.7"/><circle cx="12" cy="5" r="1.7"/><circle cx="19" cy="5" r="1.7"/><circle cx="5" cy="12" r="1.7"/><circle cx="12" cy="12" r="1.7"/><circle cx="19" cy="12" r="1.7"/><circle cx="5" cy="19" r="1.7"/><circle cx="12" cy="19" r="1.7"/><circle cx="19" cy="19" r="1.7"/></g></svg>',
    ];

    return $icons[ $slug ] ?? $icons['menu'];
}

function tb4_is_mobile_nav_item_active( $item ) {
    $slug = $item['slug'] ?? '';
    if ( 'button' === ( $item['type'] ?? 'link' ) ) {
        return false;
    }

    if ( 'home' === $slug ) {
        return is_front_page();
    }

    $url = $item['url'] ?? '';
    $item_path = trailingslashit( wp_parse_url( $url, PHP_URL_PATH ) ?: '/' );
    $request_uri  = wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' );
    $request_path = trailingslashit( '/' . ltrim( (string) wp_parse_url( $request_uri, PHP_URL_PATH ), '/' ) );

    return '/' !== $item_path && 0 === strpos( $request_path, $item_path );
}

function tb4_render_mobile_bottom_nav() {
    if ( function_exists( 'tb4_theme_should_render_mobile_bottom_nav' ) && ! tb4_theme_should_render_mobile_bottom_nav() ) {
        return;
    }

    $items = tb4_mobile_nav_items();
    if ( empty( $items ) ) {
        return;
    }

    echo '<nav class="tb4-mobile-bottom-nav" aria-label="เมนูมือถือ">';
    echo '<div class="tb4-mobile-bottom-nav__inner">';

    foreach ( $items as $item ) {
        $slug   = sanitize_html_class( $item['slug'] ?? 'menu' );
        $label  = $item['label'] ?? '';
        $icon   = tb4_get_mobile_nav_icon_svg( $slug );
        $active = tb4_is_mobile_nav_item_active( $item );
        $class  = 'tb4-mobile-bottom-nav__item tb4-mobile-bottom-nav__item--' . $slug . ( $active ? ' is-active' : '' );

        if ( 'button' === ( $item['type'] ?? 'link' ) ) {
            $action = $item['action'] ?? '';
            if ( 'app-grid' === $action ) {
                echo '<button type="button" class="' . esc_attr( $class ) . '" data-tb4-app-grid-open="1" aria-label="' . esc_attr( $label ) . '"><span class="tb4-mobile-bottom-nav__icon">' . $icon . '</span><span class="tb4-mobile-bottom-nav__label">' . esc_html( $label ) . '</span></button>';
            } else {
                echo '<button type="button" class="' . esc_attr( $class ) . '" data-tb4-focus-search="1" aria-label="' . esc_attr( $label ) . '"><span class="tb4-mobile-bottom-nav__icon">' . $icon . '</span><span class="tb4-mobile-bottom-nav__label">' . esc_html( $label ) . '</span></button>';
            }
        } else {
            $aria = $active ? ' aria-current="page"' : '';
            echo '<a href="' . esc_url( $item['url'] ?? '#' ) . '" class="' . esc_attr( $class ) . '"' . $aria . '><span class="tb4-mobile-bottom-nav__icon">' . $icon . '</span><span class="tb4-mobile-bottom-nav__label">' . esc_html( $label ) . '</span></a>';
        }
    }

    echo '</div>';
    echo '</nav>';
}

function tb4_render_app_grid_panel() {
    $groups = tb4_get_app_grid_groups();
    ?>
    <div id="tb4AppGridPanel" class="tb4-app-grid-panel" role="dialog" aria-label="เมนูและผลิตภัณฑ์" aria-hidden="true" hidden>
      <div class="tb4-app-grid-head">
        <strong>เมนูและผลิตภัณฑ์</strong>
        <button type="button" class="tb4-app-grid-close" id="tb4AppGridClose" aria-label="ปิดเมนู">×</button>
      </div>
      <div class="tb4-app-grid-content">
        <?php foreach ( $groups as $group ) : ?>
          <section class="tb4-app-grid-section">
            <h2><?php echo esc_html( $group['title'] ?? '' ); ?></h2>
            <div class="tb4-app-grid-items">
              <?php foreach ( (array) ( $group['items'] ?? [] ) as $item ) :
                [ $url, $title, $desc ] = array_pad( (array) $item, 3, '' );
              ?>
                <a class="tb4-app-grid-item" href="<?php echo esc_url( $url ); ?>">
                  <span class="tb4-app-grid-item-title"><?php echo esc_html( $title ); ?></span>
                  <small><?php echo esc_html( $desc ); ?></small>
                </a>
              <?php endforeach; ?>
            </div>
          </section>
        <?php endforeach; ?>
      </div>
    </div>
    <?php
}


/* =============================================
   v2.4.10 Community Menu Restore
   คืนเมนู Discovery / ชุมชน / ผลิตภัณฑ์ ให้แสดงครบทุกหน้า
   โดยไม่รื้อ Community Clean Shell และยังคงซ่อน footer บนหน้าชุมชนได้
   ============================================= */
function tb4_restore_core_menu_items( $items ) {
    $product_url = home_url( '/products/' );
    if ( post_type_exists( 'tb4_product' ) ) {
        $archive = get_post_type_archive_link( 'tb4_product' );
        if ( $archive ) {
            $product_url = $archive;
        }
    }

    $core = [
        'home' => [
            'url'   => home_url( '/' ),
            'label' => 'Discovery',
            'match' => [ '/' ],
        ],
        'community' => [
            'url'   => home_url( '/community/' ),
            'label' => 'ชุมชน',
            'match' => [ '/community/', '/thinkb4do-community/' ],
        ],
        'products' => [
            'url'   => $product_url,
            'label' => 'ผลิตภัณฑ์',
            'match' => [ '/products/', '/product-cat/' ],
        ],
    ];

    $normalized = [];
    foreach ( (array) $items as $item ) {
        if ( ! is_array( $item ) ) {
            continue;
        }
        $url   = (string) ( $item['url'] ?? '' );
        $label = (string) ( $item['label'] ?? '' );
        $key   = '';
        $haystack = strtolower( $url . ' ' . $label );

        if ( '/' === wp_parse_url( $url, PHP_URL_PATH ) || false !== strpos( $haystack, 'discovery' ) || false !== strpos( $label, 'หน้าแรก' ) ) {
            $key = 'home';
        } elseif ( false !== strpos( $haystack, '/community' ) || false !== strpos( $haystack, 'thinkb4do-community' ) || false !== strpos( $label, 'ชุมชน' ) ) {
            $key = 'community';
        } elseif ( false !== strpos( $haystack, '/products' ) || false !== strpos( $haystack, 'tb4_product' ) || false !== strpos( $label, 'ผลิตภัณฑ์' ) || false !== strpos( $label, 'สินค้า' ) ) {
            $key = 'products';
        }

        if ( $key && ! isset( $normalized[ $key ] ) ) {
            $normalized[ $key ] = array_merge( $core[ $key ], $item, [ 'label' => $core[ $key ]['label'], 'url' => $core[ $key ]['url'] ] );
        }
    }

    foreach ( $core as $key => $item ) {
        if ( ! isset( $normalized[ $key ] ) ) {
            $normalized[ $key ] = $item;
        }
    }

    return [ $normalized['home'], $normalized['community'], $normalized['products'] ];
}
add_filter( 'tb4_minimal_header_links', 'tb4_restore_core_menu_items', 999 );

function tb4_restore_core_mobile_nav_items( $items ) {
    $product_url = home_url( '/products/' );
    if ( post_type_exists( 'tb4_product' ) ) {
        $archive = get_post_type_archive_link( 'tb4_product' );
        if ( $archive ) {
            $product_url = $archive;
        }
    }

    return [
        [ 'type' => 'link', 'slug' => 'home',      'label' => 'Discovery', 'url' => home_url( '/' ) ],
        [ 'type' => 'link', 'slug' => 'community', 'label' => 'ชุมชน',     'url' => home_url( '/community/' ) ],
        [ 'type' => 'link', 'slug' => 'products',  'label' => 'สินค้า',     'url' => $product_url ],
    ];
}
add_filter( 'tb4_mobile_nav_items', 'tb4_restore_core_mobile_nav_items', 999 );

function tb4_restore_core_app_grid_groups( $groups ) {
    $product_url = home_url( '/products/' );
    if ( post_type_exists( 'tb4_product' ) ) {
        $archive = get_post_type_archive_link( 'tb4_product' );
        if ( $archive ) {
            $product_url = $archive;
        }
    }

    if ( ! is_array( $groups ) ) {
        $groups = [];
    }
    if ( ! isset( $groups['menu'] ) || ! is_array( $groups['menu'] ) ) {
        $groups['menu'] = [ 'title' => 'เมนูหลัก', 'items' => [] ];
    }

    $groups['menu']['items'] = [
        [ home_url( '/' ), 'Discovery', 'เริ่มต้นค้นพบฟีดและพื้นที่หลัก' ],
        [ home_url( '/community/' ), 'ชุมชน', 'พื้นที่พูดคุยและติดตาม' ],
        [ $product_url, 'ผลิตภัณฑ์', 'รวมสิ่งที่ Thinkb4do ทำ' ],
    ];

    return $groups;
}
add_filter( 'tb4_app_grid_groups', 'tb4_restore_core_app_grid_groups', 999 );


function tb4_runtime_restore_community_menu_link() {
    if ( is_admin() ) {
        return;
    }

    // Do not mutate header/mobile-menu output when the dedicated Header Menu plugin is active.
    if ( function_exists( 'tb4_is_header_menu_plugin_active' ) && tb4_is_header_menu_plugin_active() ) {
        return;
    }

    $community_url = esc_url( home_url( '/community/' ) );
    ?>
    <script id="tb4-community-menu-restore-v2410">
    (function(){
      var communityUrl = <?php echo wp_json_encode( $community_url ); ?>;
      var communityLabel = 'ชุมชน';
      var communityIcon = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M8.25 11.25a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm7.5 1.5a2.625 2.625 0 1 0 0-5.25 2.625 2.625 0 0 0 0 5.25ZM3.75 18.75a4.5 4.5 0 0 1 9 0M13.5 18.75a3.75 3.75 0 0 1 6.75-2.25" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg>';

      function hasCommunityLink(root){
        if (!root) return false;
        var links = root.querySelectorAll ? root.querySelectorAll('a') : [];
        for (var i = 0; i < links.length; i++) {
          var href = String(links[i].getAttribute('href') || '').toLowerCase();
          var text = String(links[i].textContent || '');
          if (href.indexOf('/community') !== -1 || href.indexOf('thinkb4do-community') !== -1 || text.indexOf('ชุมชน') !== -1) return true;
        }
        return false;
      }

      function insertAfter(referenceNode, newNode, parent){
        parent = parent || (referenceNode && referenceNode.parentNode);
        if (!parent) return;
        if (referenceNode && referenceNode.nextSibling) parent.insertBefore(newNode, referenceNode.nextSibling);
        else parent.appendChild(newNode);
      }

      function findHomeLike(container){
        var links = container.querySelectorAll ? container.querySelectorAll('a') : [];
        for (var i = 0; i < links.length; i++) {
          var href = String(links[i].getAttribute('href') || '');
          var text = String(links[i].textContent || '').toLowerCase();
          var path = href.replace(/^https?:\/\/[^/]+/i, '').replace(/[?#].*$/, '');
          if (text.indexOf('discovery') !== -1 || text.indexOf('หน้าแรก') !== -1 || path === '/' || path === '') return links[i];
        }
        return null;
      }

      function restoreMainNav(){
        var navs = document.querySelectorAll('#site-header .main-nav, #site-header nav[aria-label*="เมนู"], .tb4-header-center .main-nav');
        for (var i = 0; i < navs.length; i++) {
          var nav = navs[i];
          if (hasCommunityLink(nav)) continue;
          var list = nav.matches && nav.matches('ul,ol') ? nav : nav.querySelector('ul,ol');
          var a = document.createElement('a');
          a.href = communityUrl;
          a.textContent = communityLabel;
          if (location.pathname.indexOf('/community') === 0 || location.pathname.indexOf('/thinkb4do-community') === 0) {
            a.className = 'active';
            a.setAttribute('aria-current', 'page');
          }
          if (list) {
            var li = document.createElement('li');
            li.className = 'menu-item tb4-restored-community-menu-item';
            li.appendChild(a);
            var home = findHomeLike(list);
            insertAfter(home && home.parentNode ? home.parentNode : null, li, list);
          } else {
            var homeLink = findHomeLike(nav);
            insertAfter(homeLink, a, nav);
          }
        }
      }

      function restoreMobileBottomNav(){
        var inner = document.querySelector('.tb4-mobile-bottom-nav__inner');
        if (!inner || hasCommunityLink(inner)) return;
        var a = document.createElement('a');
        a.href = communityUrl;
        a.className = 'tb4-mobile-bottom-nav__item tb4-mobile-bottom-nav__item--community';
        if (location.pathname.indexOf('/community') === 0 || location.pathname.indexOf('/thinkb4do-community') === 0) {
          a.className += ' is-active';
          a.setAttribute('aria-current', 'page');
        }
        a.innerHTML = '<span class="tb4-mobile-bottom-nav__icon">' + communityIcon + '</span><span class="tb4-mobile-bottom-nav__label">' + communityLabel + '</span>';
        var home = inner.querySelector('.tb4-mobile-bottom-nav__item--home') || inner.firstElementChild;
        insertAfter(home, a, inner);
      }

      function restoreAppGrid(){
        var panel = document.getElementById('tb4AppGridPanel');
        if (!panel || hasCommunityLink(panel)) return;
        var group = panel.querySelector('.tb4-app-grid-items');
        if (!group) return;
        var a = document.createElement('a');
        a.href = communityUrl;
        a.className = 'tb4-app-grid-item tb4-restored-community-grid-item';
        a.innerHTML = '<span class="tb4-app-grid-item-title">' + communityLabel + '</span><small>พื้นที่พูดคุยและติดตาม</small>';
        var home = findHomeLike(group);
        insertAfter(home, a, group);
      }

      function restoreAll(){
        restoreMainNav();
        restoreMobileBottomNav();
        restoreAppGrid();
      }

      if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', restoreAll);
      else restoreAll();
      setTimeout(restoreAll, 300);
    })();
    </script>
    <?php
}
add_action( 'wp_footer', 'tb4_runtime_restore_community_menu_link', 99 );

/* =============================================
   TRUST / DBD REGISTRATION HELPERS — GLOBAL STYLE
   ============================================= */
function tb4_sanitize_dbd_status( $value ) {
    $allowed = [ 'hidden', 'preparing', 'pending', 'registered' ];
    return in_array( $value, $allowed, true ) ? $value : 'preparing';
}

function tb4_dbd_default( $key ) {
    $defaults = [
        'status'            => 'preparing',
        'show_home'         => true,
        'show_footer'       => true,
        'show_guide'        => true,
        'business_name'     => 'Thinkb4do',
        'registration_no'   => '',
        'registered_date'   => '',
        'office'            => '',
        'website'           => 'thinkb4do.com',
        'verify_url'        => '',
        'badge_image_url'   => '',
        'section_title'     => 'Trust Center',
        'preparing_note'    => 'Thinkb4do อยู่ระหว่างตรวจความพร้อมและเตรียมข้อมูลสำหรับการจดทะเบียนพาณิชย์อิเล็กทรอนิกส์ เราจะไม่ใช้เลขสมมติหรือเครื่องหมายรับรองก่อนมีสิทธิ์ใช้งานจริง',
        'registered_note'   => 'Thinkb4do แสดงข้อมูลจดทะเบียนเพื่อให้ผู้ใช้งานตรวจสอบตัวตนของผู้ประกอบการและความโปร่งใสของเว็บไซต์ได้ง่ายขึ้น',
        'footer_note'       => 'อยู่ระหว่างเตรียมข้อมูลจดทะเบียนอย่างถูกต้อง ยังไม่ใช้เครื่องหมายรับรองอย่างเป็นทางการ',
    ];
    return $defaults[ $key ] ?? '';
}

function tb4_dbd_setting( $key ) {
    $id = 'tb4_dbd_' . $key;
    return get_theme_mod( $id, tb4_dbd_default( $key ) );
}

function tb4_dbd_status_label( $status = null ) {
    $status = $status ? tb4_sanitize_dbd_status( $status ) : tb4_sanitize_dbd_status( tb4_dbd_setting( 'status' ) );
    $labels = [
        'hidden'     => 'ไม่แสดงข้อมูล',
        'preparing'  => 'กำลังเตรียมข้อมูลจดทะเบียน',
        'pending'    => 'อยู่ระหว่างยื่น/รอตรวจสอบ',
        'registered' => 'จดทะเบียนพาณิชย์แล้ว',
    ];
    return $labels[ $status ] ?? $labels['preparing'];
}

function tb4_dbd_status_class( $status = null ) {
    $status = $status ? tb4_sanitize_dbd_status( $status ) : tb4_sanitize_dbd_status( tb4_dbd_setting( 'status' ) );
    return 'tb4-dbd-status-' . sanitize_html_class( $status );
}

function tb4_dbd_trust_page_url() {
    $page = get_page_by_path( 'trust-registration' );
    if ( $page ) {
        return get_permalink( $page );
    }
    return home_url( '/trust-registration/' );
}

function tb4_dbd_note_for_status( $status, $context = 'full' ) {
    if ( 'registered' === $status ) {
        return tb4_dbd_setting( 'registered_note' );
    }
    if ( 'footer' === $context ) {
        return tb4_dbd_setting( 'footer_note' );
    }
    return tb4_dbd_setting( 'preparing_note' );
}

function tb4_render_dbd_trust_strip( $context = 'home' ) {
    $status = tb4_sanitize_dbd_status( tb4_dbd_setting( 'status' ) );
    if ( 'hidden' === $status ) {
        return;
    }

    $verify_url = trim( tb4_dbd_setting( 'verify_url' ) );
    $target_url = $verify_url ?: tb4_dbd_trust_page_url();
    $button_text = $verify_url ? 'ตรวจสอบข้อมูล' : 'ดูข้อมูลความน่าเชื่อถือ';
    $message = 'registered' === $status
        ? 'ตรวจสอบข้อมูลได้จากลิงก์ทางการที่ระบุ'
        : 'โปร่งใสตามจริง — ยังไม่ใช้เลขสมมติหรือโลโก้รับรอง';
    ?>
    <section class="tb4-trust-strip tb4-trust-strip-<?php echo esc_attr( $context ); ?> <?php echo esc_attr( tb4_dbd_status_class( $status ) ); ?>" aria-label="สถานะความน่าเชื่อถือ Thinkb4do">
        <div class="tb4-trust-strip-inner">
            <div class="tb4-trust-mark" aria-hidden="true"><i class="ph ph-shield-check"></i></div>
            <div class="tb4-trust-copy">
                <strong><?php echo esc_html( tb4_dbd_status_label( $status ) ); ?></strong>
                <span><?php echo esc_html( $message ); ?></span>
            </div>
            <a class="tb4-trust-link" href="<?php echo esc_url( $target_url ); ?>" <?php echo $verify_url ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
                <?php echo esc_html( $button_text ); ?> <i class="ph ph-arrow-up-right"></i>
            </a>
        </div>
    </section>
    <?php
}

function tb4_render_dbd_registration_block( $context = 'footer' ) {
    $status = tb4_sanitize_dbd_status( tb4_dbd_setting( 'status' ) );
    if ( 'hidden' === $status ) {
        return;
    }

    if ( in_array( $context, [ 'footer', 'home', 'home_badge', 'compact' ], true ) ) {
        tb4_render_dbd_trust_strip( $context );
        return;
    }

    $business_name   = trim( tb4_dbd_setting( 'business_name' ) );
    $registration_no = trim( tb4_dbd_setting( 'registration_no' ) );
    $registered_date = trim( tb4_dbd_setting( 'registered_date' ) );
    $office          = trim( tb4_dbd_setting( 'office' ) );
    $website         = trim( tb4_dbd_setting( 'website' ) );
    $verify_url      = trim( tb4_dbd_setting( 'verify_url' ) );
    $badge_image_url = trim( tb4_dbd_setting( 'badge_image_url' ) );
    $note            = tb4_dbd_note_for_status( $status, $context );
    $verified        = ( 'registered' === $status && $registration_no );
    ?>
    <section class="tb4-trust-panel tb4-dbd-context-<?php echo esc_attr( $context ); ?> <?php echo esc_attr( tb4_dbd_status_class( $status ) ); ?>" aria-label="ข้อมูลความน่าเชื่อถือและจดทะเบียน Thinkb4do">
        <div class="tb4-trust-panel-hero">
            <div class="tb4-trust-panel-copy">
                <span class="tb4-trust-kicker"><i class="ph ph-shield-check"></i> Trust Center</span>
                <h2><?php echo esc_html( tb4_dbd_setting( 'section_title' ) ); ?></h2>
                <p><?php echo esc_html( $note ); ?></p>
                <div class="tb4-trust-actions">
                    <span class="tb4-trust-status-pill"><?php echo esc_html( tb4_dbd_status_label( $status ) ); ?></span>
                    <?php if ( $verify_url ) : ?>
                        <a class="tb4-trust-button" href="<?php echo esc_url( $verify_url ); ?>" target="_blank" rel="noopener noreferrer">เปิดลิงก์ตรวจสอบ <i class="ph ph-arrow-up-right"></i></a>
                    <?php else : ?>
                        <span class="tb4-trust-muted">จะแสดงลิงก์ตรวจสอบเมื่อมีข้อมูลจริง</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="tb4-trust-visual" aria-hidden="true">
                <?php if ( 'registered' === $status && $registration_no && $badge_image_url ) : ?>
                    <img src="<?php echo esc_url( $badge_image_url ); ?>" alt="เครื่องหมายรับรองที่ได้รับอนุมัติจริง">
                <?php else : ?>
                    <div class="tb4-trust-orb"><i class="ph ph-certificate"></i></div>
                    <div class="tb4-trust-mini-card">
                        <span>Status</span>
                        <strong><?php echo esc_html( $verified ? 'Verified' : 'Preparing' ); ?></strong>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="tb4-trust-details-grid">
            <div><span>ชื่อธุรกิจ</span><strong><?php echo esc_html( $business_name ?: 'รอกรอกข้อมูลจริง' ); ?></strong></div>
            <div><span>เลขทะเบียน</span><strong><?php echo esc_html( $registration_no ?: 'ยังไม่มีเลขทะเบียนจริง' ); ?></strong></div>
            <div><span>วันที่จดทะเบียน</span><strong><?php echo esc_html( $registered_date ?: 'รอข้อมูลจริง' ); ?></strong></div>
            <div><span>พื้นที่จดทะเบียน</span><strong><?php echo esc_html( $office ?: 'รอข้อมูลจริง' ); ?></strong></div>
            <div><span>เว็บไซต์</span><strong><?php echo esc_html( $website ?: wp_parse_url( home_url(), PHP_URL_HOST ) ); ?></strong></div>
        </div>
    </section>
    <?php
}

function tb4_dbd_registration_shortcode( $atts = [] ) {
    $atts = shortcode_atts( [ 'layout' => 'full' ], $atts, 'thinkb4do_dbd_info' );
    ob_start();
    tb4_render_dbd_registration_block( 'compact' === $atts['layout'] ? 'compact' : 'shortcode' );
    return ob_get_clean();
}
add_shortcode( 'thinkb4do_dbd_info', 'tb4_dbd_registration_shortcode' );


/* =============================================
   UNIVERSAL COMPATIBILITY HELPERS
   ============================================= */
function tb4_compat_default( $key ) {
    $defaults = [
        'show_home'            => true,
        'show_footer'          => false,
        'browser_notice'       => true,
        'section_title'        => 'รองรับทุกอุปกรณ์ ทุกระบบ และผู้ใช้งานหลายบริบท',
        'section_desc'         => 'Thinkb4do วางโครงให้ใช้งานได้บนมือถือ แท็บเล็ต เดสก์ท็อป เบราว์เซอร์หลัก และสภาพแวดล้อมท้องถิ่น/สากล โดยมี fallback สำหรับกรณีอุปกรณ์หรือเบราว์เซอร์ไม่รองรับฟีเจอร์บางส่วน',
        'browser_notice_title' => 'เบราว์เซอร์นี้รองรับบางฟีเจอร์ไม่ครบ',
        'browser_notice_body'  => 'เว็บไซต์ยังแสดงเนื้อหาหลักได้ตามปกติ แต่บางเอฟเฟกต์อาจถูกลดลงเพื่อความเสถียร แนะนำให้อัปเดตเบราว์เซอร์เมื่อสะดวก',
        'local_note'           => 'รองรับภาษาไทยเป็นหลัก พร้อมโครงสร้างที่พร้อมต่อยอดหลายภาษา เขตเวลา และข้อมูลท้องถิ่นในอนาคต',
    ];
    return $defaults[ $key ] ?? '';
}

function tb4_compat_setting( $key ) {
    return get_theme_mod( 'tb4_compat_' . $key, tb4_compat_default( $key ) );
}

function tb4_render_compatibility_block( $context = 'home' ) {
    if ( 'home' === $context && ! get_theme_mod( 'tb4_compat_show_home', true ) ) {
        return;
    }

    $items = [
        [ 'ph-device-mobile', 'ทุกอุปกรณ์', 'รองรับมือถือ แท็บเล็ต โน้ตบุ๊ก เดสก์ท็อป และจอขนาดใหญ่ด้วย responsive layout' ],
        [ 'ph-browser', 'เบราว์เซอร์หลัก', 'เตรียม fallback สำหรับ Chrome, Safari, Edge, Firefox และ WebView ที่ใช้บนมือถือ' ],
        [ 'ph-person-arms-spread', 'เข้าถึงง่าย', 'รองรับ keyboard focus, reduced motion, contrast mode และ no‑JS fallback ในส่วนสำคัญ' ],
        [ 'ph-globe-hemisphere-east', 'สากล + ท้องถิ่น', tb4_compat_setting( 'local_note' ) ],
    ];
    ?>
    <section class="tb4-compat-block tb4-compat-<?php echo esc_attr( sanitize_key( $context ) ); ?>" aria-label="Universal Compatibility">
        <div class="tb4-compat-head">
            <span class="tb4-compat-kicker"><i class="ph ph-sparkle"></i> Universal Compatibility</span>
            <h2><?php echo esc_html( tb4_compat_setting( 'section_title' ) ); ?></h2>
            <p><?php echo esc_html( tb4_compat_setting( 'section_desc' ) ); ?></p>
        </div>
        <div class="tb4-compat-grid">
            <?php foreach ( $items as [ $icon, $title, $desc ] ) : ?>
                <article class="tb4-compat-card">
                    <i class="ph <?php echo esc_attr( $icon ); ?>" aria-hidden="true"></i>
                    <h3><?php echo esc_html( $title ); ?></h3>
                    <p><?php echo esc_html( $desc ); ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
}

function tb4_compatibility_shortcode( $atts = [] ) {
    $atts = shortcode_atts( [ 'context' => 'shortcode' ], $atts, 'thinkb4do_compatibility' );
    ob_start();
    tb4_render_compatibility_block( sanitize_key( $atts['context'] ) );
    return ob_get_clean();
}
add_shortcode( 'thinkb4do_compatibility', 'tb4_compatibility_shortcode' );

/* =============================================
   DEVELOPMENT MESSAGE HELPERS
   ============================================= */
function tb4_dev_message_defaults() {
    return [
        'notice_title'                 => 'เว็บไซต์อยู่ระหว่างพัฒนา',
        'notice_body'                  => 'ข้อมูลบางส่วนเป็นตัวอย่างเพื่อทดสอบโครงหน้า ระบบ และประสบการณ์ใช้งานก่อนเปิดจริง',
        'header_search_placeholder'    => 'ค้นหาแนวคิด ระบบต้นแบบ หรือบันทึกการพัฒนา...',
        'notification_badge'           => 'ระบบตัวอย่าง',
        'notification_1_title'         => 'เว็บไซต์กำลังวางโครงระบบ',
        'notification_1_body'          => 'ใช้หน้านี้เพื่อตรวจไกด์ สถานะระบบ และข้อความก่อนเปิดจริง',
        'notification_2_title'         => 'ส่วนหัวตั้งค่าได้แล้ว',
        'notification_2_body'          => 'เลือกได้ว่าจะตรึงทุกหน้า เฉพาะหน้าแรก หรือไม่ตรึงเลย',
        'notification_3_title'         => 'ข้อมูลสินค้ายังเป็นตัวอย่าง',
        'notification_3_body'          => 'ใช้เพื่อทดสอบการ์ดสินค้า หมวดหมู่ ราคา และปุ่มก่อนจำหน่ายจริง',
        'message_button_title'         => 'ข้อความระบบตัวอย่าง',
        'hero_badge'                   => 'กำลังพัฒนา Thinkb4do ทีละขั้น',
        'hero_search_placeholder'      => 'ค้นหาแนวคิด ระบบต้นแบบ หรือบันทึกการพัฒนา...',
        'cta_primary'                  => 'ติดตามการพัฒนา',
        'site_stage'                   => 'Beta',
        'feature_product_desc'         => 'พื้นที่ทดลองสินค้า',
        'feature_community_desc'       => 'กำลังออกแบบระบบ',
        'feature_affiliate_desc'       => 'เตรียมโครงสร้างระบบ',
        'feature_booking_desc'         => 'ยังไม่เปิดใช้งานจริง',
        'products_title'               => 'ตัวอย่างระบบผลิตภัณฑ์',
        'products_note'                => 'ข้อมูลจำลอง / ยังไม่เปิดขายจริง',
        'community_title'              => 'ชุมชนกำลังพัฒนา',
        'community_note'               => 'โพสต์ตัวอย่าง / ยังไม่เปิดจริง',
        'community_cta_title'          => 'ระบบชุมชนกำลังอยู่ระหว่างออกแบบ',
        'community_cta_body'           => 'ตอนนี้ใช้เป็นพื้นที่ตัวอย่างสำหรับทดสอบโพสต์ ความคิดเห็น และระบบสมาชิก',
        'systems_title'                => 'ระบบที่ Thinkb4do กำลังพัฒนา',
        'systems_intro'                => 'รายการนี้เป็นภาพรวมของโมดูลต้นแบบที่กำลังวางโครงและเชื่อมเข้ากับเว็บไซต์',
        'newsletter_title'             => 'ติดตามการพัฒนา Thinkb4do',
        'newsletter_body'              => 'เรากำลังค่อย ๆ พัฒนาเว็บไซต์ ระบบ และผลิตภัณฑ์ดิจิทัลให้พร้อมใช้งานจริง',
        'newsletter_button'            => 'ติดตามความคืบหน้า',
        'footer_summary'               => 'เว็บไซต์กำลังอยู่ระหว่างพัฒนา เพื่อทดลองแนวคิด ระบบต้นแบบ ผลิตภัณฑ์ตัวอย่าง และการต่อยอดในอนาคต',
        'footer_newsletter_body'       => 'รับอัปเดตความคืบหน้าของระบบที่กำลังพัฒนา',
        'footer_newsletter_note'       => 'ระบบรับข่าวสารเป็นตัวอย่างก่อนเชื่อมบริการจริง',
        'sidebar_note'                 => 'เว็บไซต์กำลังพัฒนา ข้อมูลบางส่วนเป็นตัวอย่างเพื่อทดสอบระบบก่อนเปิดจริง',
        'sidebar_button'               => 'ติดตามการพัฒนา',
        'products_page_kicker'         => 'ผลิตภัณฑ์ตัวอย่าง Thinkb4do',
        'products_page_title'          => 'ระบบสินค้า\nกำลังอยู่ระหว่างพัฒนา',
        'products_page_desc'           => 'หน้านี้ใช้ทดสอบโครงสินค้า หมวดหมู่ ราคา รูปภาพ และปุ่ม ก่อนเปิดให้ใช้งานจริง',
        'community_page_title'         => 'ชุมชนกำลังพัฒนา',
        'community_page_desc'          => 'พื้นที่ตัวอย่างสำหรับทดลองระบบโพสต์ ความคิดเห็น และสมาชิกก่อนเปิดใช้งานจริง',
        'community_empty_title'        => 'ยังไม่มีโพสต์ในชุมชน',
        'community_empty_body'         => 'ยังไม่มีโพสต์จริง ใช้พื้นที่นี้ทดสอบระบบก่อนเปิดจริง',
        'member_highlight'             => 'โปรไฟล์ตัวอย่าง',
        'member_trust'                 => 'ข้อมูลตัวอย่าง',
        'guide_badge'                  => 'Beta / กำลังพัฒนา',
        'guide_title'                  => 'Thinkb4do กำลังพัฒนาระบบ',
        'guide_desc'                   => 'หน้านี้ใช้เป็นไกด์ตัวอย่างสำหรับบอกผู้ชมว่าเว็บไซต์ยังอยู่ระหว่างวางโครง ทดสอบระบบ และปรับปรุงเนื้อหา ข้อมูลบางส่วนจึงเป็นข้อมูลจำลองเพื่อช่วยให้เห็นทิศทางก่อนเปิดใช้งานจริง',
        'guide_card_1_title'           => 'ระบบต้นแบบ',
        'guide_card_1_body'            => 'เรากำลังวางโครงหน้าแรก ระบบสมาชิก ระบบสินค้า และพื้นที่ชุมชนให้ใช้งานได้จริงในอนาคต',
        'guide_card_2_title'           => 'ข้อมูลตัวอย่าง',
        'guide_card_2_body'            => 'สินค้า โพสต์ ตัวเลข และปุ่มบางส่วนเป็นข้อมูลจำลองเพื่อใช้ทดสอบ UX/UI ก่อนเปิดจริง',
        'guide_card_3_title'           => 'ติดตามความคืบหน้า',
        'guide_card_3_body'            => 'ผู้ชมสามารถติดตามการพัฒนา แทนการเข้าใจว่าเว็บนี้มีสินค้าและบริการจริงครบแล้ว',
    ];
}

function tb4_dev_text( $key, $fallback = '' ) {
    $defaults = tb4_dev_message_defaults();
    $default  = $fallback !== '' ? $fallback : ( $defaults[ $key ] ?? '' );
    return get_theme_mod( 'tb4_dev_' . $key, $default );
}


/**
 * v2.3.5 Footer Community Removed
 * Keep footer neutral when the community system is being built elsewhere.
 */
function tb4_footer_summary_text() {
    $summary = tb4_dev_text( 'footer_summary' );

    $replacements = [
        ' และชุมชนในอนาคต' => ' และการต่อยอดในอนาคต',
        'ชุมชนในอนาคต'      => 'การต่อยอดในอนาคต',
        'ระบบชุมชน'          => 'ระบบหลัก',
        'ชุมชน'              => '',
        'community'          => '',
        'Community'          => '',
    ];

    $summary = str_replace( array_keys( $replacements ), array_values( $replacements ), $summary );
    $summary = preg_replace( '/\s{2,}/u', ' ', $summary );
    $summary = trim( $summary, " \t\n\r\0\x0B—-,،" );

    if ( $summary === '' ) {
        $summary = 'เว็บไซต์กำลังอยู่ระหว่างพัฒนา เพื่อทดลองแนวคิด ระบบต้นแบบ ผลิตภัณฑ์ตัวอย่าง และการต่อยอดในอนาคต';
    }

    return $summary;
}

/* =============================================
   CUSTOMIZER
   ============================================= */
function tb4_customizer( $wp_customize ) {
    // Panel
    $wp_customize->add_panel( 'tb4_panel', [
        'title'    => __( 'Thinkb4do Settings', 'thinkb4do' ),
        'priority' => 10,
    ]);

    // --- Section: Brand Identity / Logo ---
    $wp_customize->add_section( 'tb4_brand_identity', [
        'title'       => __( 'โลโก้ / อัตลักษณ์แบรนด์', 'thinkb4do' ),
        'description' => __( 'เลือกโลโก้จาก Media Library ของ WordPress แล้วให้แสดงอัตโนมัติใน Header, Footer และส่วนแบรนด์หลักของธีม', 'thinkb4do' ),
        'panel'       => 'tb4_panel',
        'priority'    => 1,
    ]);
    $wp_customize->add_setting( 'tb4_brand_logo_id', [
        'default'           => 0,
        'sanitize_callback' => 'absint',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'tb4_brand_logo_id', [
        'label'       => __( 'โลโก้หลักจาก Media Library', 'thinkb4do' ),
        'description' => __( 'อัปโหลดหรือเลือกโลโก้จากคลังสื่อ WordPress แนะนำไฟล์ PNG/SVG/WebP พื้นหลังโปร่งใส หรือภาพคมชัดสัดส่วนแนวนอน/สี่เหลี่ยม', 'thinkb4do' ),
        'section'     => 'tb4_brand_identity',
        'mime_type'   => 'image',
    ] ) );

    $wp_customize->add_setting( 'tb4_brand_logo_dark_id', [
        'default'           => 0,
        'sanitize_callback' => 'absint',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'tb4_brand_logo_dark_id', [
        'label'       => __( 'โลโก้สีเข้ม / ใช้บนพื้นสว่าง', 'thinkb4do' ),
        'description' => __( 'แนะนำใช้โลโก้สีเข้มหรือโลโก้สีปกติ เพื่อให้เห็นชัดบนพื้นขาวหรือพื้นอ่อน ถ้าไม่เลือก ระบบจะใช้โลโก้หลักแทน', 'thinkb4do' ),
        'section'     => 'tb4_brand_identity',
        'mime_type'   => 'image',
    ] ) );
    $wp_customize->add_setting( 'tb4_brand_logo_light_id', [
        'default'           => 0,
        'sanitize_callback' => 'absint',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'tb4_brand_logo_light_id', [
        'label'       => __( 'โลโก้สีสว่าง / ใช้บนพื้นเข้ม', 'thinkb4do' ),
        'description' => __( 'แนะนำใช้โลโก้สีขาวหรือสีสว่าง พื้นหลังโปร่งใส เพื่อไม่ให้จมเมื่ออยู่บนพื้นเขียวหรือพื้นเข้ม', 'thinkb4do' ),
        'section'     => 'tb4_brand_identity',
        'mime_type'   => 'image',
    ] ) );
    $wp_customize->add_setting( 'tb4_logo_variant_mode', [
        'default'           => 'auto',
        'sanitize_callback' => 'tb4_sanitize_logo_variant_mode',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control( 'tb4_logo_variant_mode', [
        'label'       => __( 'โหมดเลือกสีโลโก้', 'thinkb4do' ),
        'description' => __( 'Auto จะให้ธีมเลือกโลโก้สีเข้มหรือสีสว่างตามพื้นหลังของ Header/Footer เพื่อให้โลโก้เห็นชัดขึ้น', 'thinkb4do' ),
        'section'     => 'tb4_brand_identity',
        'type'        => 'select',
        'choices'     => [
            'auto'  => __( 'Auto — ให้ระบบเลือกตามพื้นหลัง', 'thinkb4do' ),
            'dark'  => __( 'บังคับใช้โลโก้สีเข้ม', 'thinkb4do' ),
            'light' => __( 'บังคับใช้โลโก้สีสว่าง', 'thinkb4do' ),
        ],
    ]);
    $wp_customize->add_setting( 'tb4_logo_show_text', [
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control( 'tb4_logo_show_text', [
        'label'       => __( 'แสดงชื่อเว็บข้างโลโก้', 'thinkb4do' ),
        'section'     => 'tb4_brand_identity',
        'type'        => 'checkbox',
    ]);
    $wp_customize->add_setting( 'tb4_logo_show_tagline_header', [
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control( 'tb4_logo_show_tagline_header', [
        'label'       => __( 'แสดง Tagline ใต้โลโก้ใน Header', 'thinkb4do' ),
        'section'     => 'tb4_brand_identity',
        'type'        => 'checkbox',
    ]);
    $wp_customize->add_setting( 'tb4_logo_show_tagline_footer', [
        'default'           => false,
        'sanitize_callback' => 'wp_validate_boolean',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control( 'tb4_logo_show_tagline_footer', [
        'label'       => __( 'แสดง Tagline ใต้โลโก้ใน Footer', 'thinkb4do' ),
        'section'     => 'tb4_brand_identity',
        'type'        => 'checkbox',
    ]);

    // --- Section: General ---
    $wp_customize->add_section( 'tb4_general', [
        'title' => __( 'ทั่วไป', 'thinkb4do' ),
        'panel' => 'tb4_panel',
    ]);
    tb4_add_text_control( $wp_customize, 'tb4_tagline', 'tb4_general', __( 'Tagline', 'thinkb4do' ), 'Think before you do.' );
    tb4_add_text_control( $wp_customize, 'tb4_stats_members',  'tb4_general', __( 'สถานะสมาชิก/ชุมชน', 'thinkb4do' ), 'กำลังพัฒนา' );
    tb4_add_text_control( $wp_customize, 'tb4_stats_products', 'tb4_general', __( 'สถานะผลิตภัณฑ์', 'thinkb4do' ), 'ตัวอย่างระบบ' );
    tb4_add_text_control( $wp_customize, 'tb4_stats_partners', 'tb4_general', __( 'สถานะพาร์ทเนอร์', 'thinkb4do' ), 'รอเปิดจริง' );

    // --- Section: Header / Sticky ---
    $wp_customize->add_section( 'tb4_header_settings', [
        'title' => __( 'ส่วนหัว / Sticky Header', 'thinkb4do' ),
        'panel' => 'tb4_panel',
    ]);
    $wp_customize->add_setting( 'tb4_header_sticky_mode', [
        'default'           => 'all',
        'sanitize_callback' => 'tb4_sanitize_header_sticky_mode',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control( 'tb4_header_sticky_mode', [
        'label'       => __( 'การตรึงส่วนหัว', 'thinkb4do' ),
        'description' => __( 'เลือกว่าจะให้ส่วนหัวลอยติดด้านบนทุกหน้า เฉพาะหน้าแรก หรือไม่ตรึงเลย', 'thinkb4do' ),
        'section'     => 'tb4_header_settings',
        'type'        => 'select',
        'choices'     => [
            'all'  => __( 'ตรึงทุกหน้า', 'thinkb4do' ),
            'home' => __( 'ตรึงเฉพาะหน้าแรก', 'thinkb4do' ),
            'none' => __( 'ไม่ตรึงส่วนหัว', 'thinkb4do' ),
        ],
    ]);
    $wp_customize->add_setting( 'tb4_dev_notice_enabled', [
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control( 'tb4_dev_notice_enabled', [
        'label'       => __( 'แสดงป้ายเว็บไซต์กำลังพัฒนา', 'thinkb4do' ),
        'section'     => 'tb4_header_settings',
        'type'        => 'checkbox',
    ]);

    $wp_customize->add_setting( 'tb4_dev_notice_dismissible', [
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control( 'tb4_dev_notice_dismissible', [
        'label'       => __( 'ให้ผู้ชมปิดแถบประกาศบนหน้าเว็บได้', 'thinkb4do' ),
        'description' => __( 'ผู้ชมสามารถกดปิดได้ชั่วคราวบนเบราว์เซอร์ของตัวเอง ส่วนผู้ดูแลยังเปิด/ปิดจริงได้จากช่อง “แสดงป้ายเว็บไซต์กำลังพัฒนา”', 'thinkb4do' ),
        'section'     => 'tb4_header_settings',
        'type'        => 'checkbox',
    ]);

    $wp_customize->add_setting( 'tb4_dev_notice_admin_edit_link', [
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control( 'tb4_dev_notice_admin_edit_link', [
        'label'       => __( 'แสดงปุ่มแก้ไขแถบประกาศสำหรับผู้ดูแล', 'thinkb4do' ),
        'description' => __( 'เมื่อผู้ดูแลระบบล็อกอิน จะมีปุ่ม “แก้ไข” บนแถบประกาศเพื่อเข้า Customizer ได้เร็วขึ้น', 'thinkb4do' ),
        'section'     => 'tb4_header_settings',
        'type'        => 'checkbox',
    ]);

    // --- Section: Development Messages ---
    $wp_customize->add_section( 'tb4_dev_messages', [
        'title'       => __( 'ข้อความกำลังพัฒนา', 'thinkb4do' ),
        'description' => __( 'แก้ข้อความสถานะระบบจากใน WordPress ได้โดยไม่ต้องแก้โค้ด ใช้สำหรับบอกผู้ชมว่าเว็บอยู่ระหว่างพัฒนาและข้อมูลบางส่วนเป็นตัวอย่าง', 'thinkb4do' ),
        'panel'       => 'tb4_panel',
    ]);
    $dev_defaults = tb4_dev_message_defaults();
    foreach ( [
        'notice_title'              => 'แถบสถานะ: ชื่อสั้น',
        'notice_body'               => 'แถบสถานะ: คำอธิบาย',
        'header_search_placeholder' => 'ช่องค้นหาด้านบน',
        'notification_badge'        => 'ป้ายในกล่องแจ้งเตือน',
        'notification_1_title'      => 'แจ้งเตือน 1: หัวข้อ',
        'notification_1_body'       => 'แจ้งเตือน 1: รายละเอียด',
        'notification_2_title'      => 'แจ้งเตือน 2: หัวข้อ',
        'notification_2_body'       => 'แจ้งเตือน 2: รายละเอียด',
        'notification_3_title'      => 'แจ้งเตือน 3: หัวข้อ',
        'notification_3_body'       => 'แจ้งเตือน 3: รายละเอียด',
        'message_button_title'      => 'ปุ่มข้อความ: Tooltip',
        'hero_badge'                => 'หน้าแรก: ป้ายเหนือหัวข้อ',
        'hero_search_placeholder'   => 'หน้าแรก: ช่องค้นหา',
        'cta_primary'               => 'ปุ่มหลักสำหรับติดตาม',
        'site_stage'                => 'สถานะเว็บ เช่น Beta',
        'feature_product_desc'      => 'แถบฟีเจอร์: ผลิตภัณฑ์',
        'feature_community_desc'    => 'แถบฟีเจอร์: ชุมชน',
        'feature_affiliate_desc'    => 'แถบฟีเจอร์: Affiliate',
        'feature_booking_desc'      => 'แถบฟีเจอร์: Booking',
        'products_title'            => 'หน้าแรก: หัวข้อสินค้า',
        'products_note'             => 'หน้าแรก: หมายเหตุสินค้า',
        'community_title'           => 'หน้าแรก: หัวข้อชุมชน',
        'community_note'            => 'หน้าแรก: หมายเหตุชุมชน',
        'community_cta_title'       => 'หน้าแรก: กล่องชุมชน',
        'community_cta_body'        => 'หน้าแรก: รายละเอียดกล่องชุมชน',
        'systems_title'             => 'หน้าแรก: หัวข้อระบบที่พัฒนา',
        'systems_intro'             => 'หน้าแรก: คำอธิบายระบบที่พัฒนา',
        'newsletter_title'          => 'หน้าแรก: หัวข้อติดตาม',
        'newsletter_body'           => 'หน้าแรก: รายละเอียดติดตาม',
        'newsletter_button'         => 'หน้าแรก: ปุ่มติดตาม',
        'footer_summary'            => 'Footer: สรุปเว็บไซต์',
        'footer_newsletter_body'    => 'Footer: คำอธิบายรับข่าวสาร',
        'footer_newsletter_note'    => 'Footer: หมายเหตุฟอร์ม',
        'sidebar_note'              => 'Sidebar: ข้อความสถานะ',
        'sidebar_button'            => 'Sidebar: ปุ่ม',
        'products_page_kicker'      => 'หน้าสินค้า: ข้อความเล็ก',
        'products_page_title'       => 'หน้าสินค้า: หัวข้อ',
        'products_page_desc'        => 'หน้าสินค้า: คำอธิบาย',
        'community_page_title'      => 'หน้าชุมชน: หัวข้อ',
        'community_page_desc'       => 'หน้าชุมชน: คำอธิบาย',
        'community_empty_title'     => 'หน้าชุมชน: ไม่มีโพสต์ หัวข้อ',
        'community_empty_body'      => 'หน้าชุมชน: ไม่มีโพสต์ รายละเอียด',
        'member_highlight'          => 'หน้าสมาชิก: ป้ายโปรไฟล์',
        'member_trust'              => 'หน้าสมาชิก: ป้ายความน่าเชื่อถือ',
        'guide_badge'               => 'หน้าไกด์: ป้ายสถานะ',
        'guide_title'               => 'หน้าไกด์: หัวข้อ',
        'guide_desc'                => 'หน้าไกด์: คำอธิบาย',
        'guide_card_1_title'        => 'หน้าไกด์: การ์ด 1 หัวข้อ',
        'guide_card_1_body'         => 'หน้าไกด์: การ์ด 1 รายละเอียด',
        'guide_card_2_title'        => 'หน้าไกด์: การ์ด 2 หัวข้อ',
        'guide_card_2_body'         => 'หน้าไกด์: การ์ด 2 รายละเอียด',
        'guide_card_3_title'        => 'หน้าไกด์: การ์ด 3 หัวข้อ',
        'guide_card_3_body'         => 'หน้าไกด์: การ์ด 3 รายละเอียด',
    ] as $key => $label ) {
        $long_keys = [ 'notice_body', 'notification_1_body', 'notification_2_body', 'notification_3_body', 'products_note', 'community_note', 'community_cta_body', 'systems_intro', 'newsletter_body', 'footer_summary', 'footer_newsletter_body', 'footer_newsletter_note', 'products_page_title', 'products_page_desc', 'community_page_desc', 'community_empty_body', 'guide_desc', 'guide_card_1_body', 'guide_card_2_body', 'guide_card_3_body' ];
        if ( in_array( $key, $long_keys, true ) ) {
            tb4_add_textarea_control( $wp_customize, 'tb4_dev_' . $key, 'tb4_dev_messages', __( $label, 'thinkb4do' ), $dev_defaults[ $key ] ?? '' );
        } else {
            tb4_add_text_control( $wp_customize, 'tb4_dev_' . $key, 'tb4_dev_messages', __( $label, 'thinkb4do' ), $dev_defaults[ $key ] ?? '' );
        }
    }

    // --- Section: Commercial Registration / Trust Center ---
    $wp_customize->add_section( 'tb4_dbd_registration', [
        'title'       => __( 'Trust Center / ข้อมูลจดทะเบียน', 'thinkb4do' ),
        'description' => __( 'ตั้งค่าพื้นที่ความน่าเชื่อถือและข้อมูลจดทะเบียนแบบ Global UI หากยังไม่มีเลขทะเบียนจริง ให้ใช้สถานะกำลังเตรียมข้อมูลหรือรอตรวจสอบ ห้ามกรอกเลขสมมติ และห้ามใช้โลโก้/เครื่องหมายรับรองก่อนอนุมัติจริง', 'thinkb4do' ),
        'panel'       => 'tb4_panel',
    ]);
    $wp_customize->add_setting( 'tb4_dbd_status', [
        'default'           => tb4_dbd_default( 'status' ),
        'sanitize_callback' => 'tb4_sanitize_dbd_status',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control( 'tb4_dbd_status', [
        'label'       => __( 'สถานะข้อมูลจดทะเบียน', 'thinkb4do' ),
        'section'     => 'tb4_dbd_registration',
        'type'        => 'select',
        'choices'     => [
            'hidden'     => __( 'ไม่แสดงส่วนนี้', 'thinkb4do' ),
            'preparing'  => __( 'กำลังเตรียมข้อมูลจดทะเบียน', 'thinkb4do' ),
            'pending'    => __( 'อยู่ระหว่างยื่น/รอตรวจสอบ', 'thinkb4do' ),
            'registered' => __( 'จดทะเบียนพาณิชย์แล้ว', 'thinkb4do' ),
        ],
    ]);
    $wp_customize->add_setting( 'tb4_dbd_show_home', [
        'default'           => tb4_dbd_default( 'show_home' ),
        'sanitize_callback' => 'wp_validate_boolean',
    ] );
    $wp_customize->add_control( 'tb4_dbd_show_home', [
        'label'   => __( 'แสดง Trust Badge แบบสั้นบนหน้าแรก', 'thinkb4do' ),
        'section' => 'tb4_dbd_registration',
        'type'    => 'checkbox',
    ] );

    $wp_customize->add_setting( 'tb4_dbd_show_footer', [
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control( 'tb4_dbd_show_footer', [
        'label'   => __( 'แสดงข้อมูลจดทะเบียนบริเวณ Footer', 'thinkb4do' ),
        'section' => 'tb4_dbd_registration',
        'type'    => 'checkbox',
    ]);
    $wp_customize->add_setting( 'tb4_dbd_show_guide', [
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control( 'tb4_dbd_show_guide', [
        'label'   => __( 'แสดงในหน้าไกด์ระบบ', 'thinkb4do' ),
        'section' => 'tb4_dbd_registration',
        'type'    => 'checkbox',
    ]);
    tb4_add_text_control( $wp_customize, 'tb4_dbd_section_title', 'tb4_dbd_registration', __( 'หัวข้อส่วนจดทะเบียน', 'thinkb4do' ), tb4_dbd_default( 'section_title' ) );
    tb4_add_text_control( $wp_customize, 'tb4_dbd_business_name', 'tb4_dbd_registration', __( 'ชื่อผู้ประกอบการ/ชื่อธุรกิจ', 'thinkb4do' ), tb4_dbd_default( 'business_name' ) );
    tb4_add_text_control( $wp_customize, 'tb4_dbd_registration_no', 'tb4_dbd_registration', __( 'เลขทะเบียนพาณิชย์/เลขอ้างอิงจริง', 'thinkb4do' ), tb4_dbd_default( 'registration_no' ) );
    tb4_add_text_control( $wp_customize, 'tb4_dbd_registered_date', 'tb4_dbd_registration', __( 'วันที่จดทะเบียน', 'thinkb4do' ), tb4_dbd_default( 'registered_date' ) );
    tb4_add_text_control( $wp_customize, 'tb4_dbd_office', 'tb4_dbd_registration', __( 'สำนักงาน/พื้นที่จดทะเบียน', 'thinkb4do' ), tb4_dbd_default( 'office' ) );
    tb4_add_text_control( $wp_customize, 'tb4_dbd_website', 'tb4_dbd_registration', __( 'เว็บไซต์ที่จดทะเบียน', 'thinkb4do' ), tb4_dbd_default( 'website' ) );
    tb4_add_url_control( $wp_customize, 'tb4_dbd_verify_url', 'tb4_dbd_registration', __( 'ลิงก์ตรวจสอบทางการหลังมีข้อมูลจริง', 'thinkb4do' ), tb4_dbd_default( 'verify_url' ) );
    tb4_add_url_control( $wp_customize, 'tb4_dbd_badge_image_url', 'tb4_dbd_registration', __( 'URL รูปเครื่องหมายรับรองที่ได้รับอนุมัติจริงเท่านั้น', 'thinkb4do' ), tb4_dbd_default( 'badge_image_url' ) );
    tb4_add_textarea_control( $wp_customize, 'tb4_dbd_preparing_note', 'tb4_dbd_registration', __( 'ข้อความเมื่อยังไม่มีเลขทะเบียนจริง', 'thinkb4do' ), tb4_dbd_default( 'preparing_note' ) );
    tb4_add_textarea_control( $wp_customize, 'tb4_dbd_registered_note', 'tb4_dbd_registration', __( 'ข้อความเมื่อจดทะเบียนแล้ว', 'thinkb4do' ), tb4_dbd_default( 'registered_note' ) );
    tb4_add_textarea_control( $wp_customize, 'tb4_dbd_footer_note', 'tb4_dbd_registration', __( 'ข้อความย่อใน Footer', 'thinkb4do' ), tb4_dbd_default( 'footer_note' ) );

    // --- Section: Hero ---
    $wp_customize->add_section( 'tb4_hero', [
        'title' => __( 'Hero Section', 'thinkb4do' ),
        'panel' => 'tb4_panel',
    ]);
    tb4_add_text_control( $wp_customize, 'tb4_hero_title',  'tb4_hero', __( 'ชื่อหลัก', 'thinkb4do' ), 'สร้างระบบให้คิดก่อนทำ และทำงานร่วมกันได้จริง' );
    tb4_add_text_control( $wp_customize, 'tb4_hero_desc',   'tb4_hero', __( 'คำอธิบาย', 'thinkb4do' ), 'Thinkb4do กำลังพัฒนาพื้นที่สำหรับรวบรวมแนวคิด เว็บไซต์ ระบบ และผลิตภัณฑ์ดิจิทัล โดยเริ่มจากการวางโครง ทดลอง และปรับปรุงทีละขั้น เพื่อให้พร้อมใช้งานจริงในอนาคต' );

    // --- Section: Colors ---
    $wp_customize->add_section( 'tb4_colors', [
        'title' => __( 'สี', 'thinkb4do' ),
        'panel' => 'tb4_panel',
    ]);
    tb4_add_color_control( $wp_customize, 'tb4_color_green',  'tb4_colors', __( 'สีเขียวหลัก', 'thinkb4do' ), '#1E6B45' );
    tb4_add_color_control( $wp_customize, 'tb4_color_orange', 'tb4_colors', __( 'สีส้ม CTA', 'thinkb4do' ), '#F97316' );

    // --- Section: Universal Compatibility ---
    $wp_customize->add_section( 'tb4_universal_compatibility', [
        'title'       => __( 'รองรับทุกอุปกรณ์ / Universal', 'thinkb4do' ),
        'description' => __( 'ตั้งค่าข้อความและการแสดงผลสำหรับส่วนรองรับเบราว์เซอร์ อุปกรณ์ ระบบปฏิบัติการ ภาษาไทย/สากล และ fallback เมื่อฟีเจอร์บางอย่างไม่รองรับ', 'thinkb4do' ),
        'panel'       => 'tb4_panel',
    ]);
    $wp_customize->add_setting( 'tb4_compat_show_home', [
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control( 'tb4_compat_show_home', [
        'label'   => __( 'แสดงส่วน Universal Compatibility บนหน้าแรก', 'thinkb4do' ),
        'section' => 'tb4_universal_compatibility',
        'type'    => 'checkbox',
    ]);
    $wp_customize->add_setting( 'tb4_compat_show_footer', [
        'default'           => false,
        'sanitize_callback' => 'wp_validate_boolean',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control( 'tb4_compat_show_footer', [
        'label'   => __( 'แสดงข้อความรองรับระบบใน Footer', 'thinkb4do' ),
        'section' => 'tb4_universal_compatibility',
        'type'    => 'checkbox',
    ]);
    $wp_customize->add_setting( 'tb4_compat_browser_notice', [
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control( 'tb4_compat_browser_notice', [
        'label'       => __( 'แจ้งเตือนเมื่อเบราว์เซอร์เก่ามาก', 'thinkb4do' ),
        'description' => __( 'ใช้กับเบราว์เซอร์ที่ไม่รองรับฟีเจอร์พื้นฐาน เช่น querySelector, classList หรือ CSS Grid', 'thinkb4do' ),
        'section'     => 'tb4_universal_compatibility',
        'type'        => 'checkbox',
    ]);
    tb4_add_text_control( $wp_customize, 'tb4_compat_section_title', 'tb4_universal_compatibility', __( 'หัวข้อส่วนรองรับระบบ', 'thinkb4do' ), tb4_compat_default( 'section_title' ) );
    tb4_add_textarea_control( $wp_customize, 'tb4_compat_section_desc', 'tb4_universal_compatibility', __( 'คำอธิบายส่วนรองรับระบบ', 'thinkb4do' ), tb4_compat_default( 'section_desc' ) );
    tb4_add_textarea_control( $wp_customize, 'tb4_compat_local_note', 'tb4_universal_compatibility', __( 'ข้อความสากล/ท้องถิ่น', 'thinkb4do' ), tb4_compat_default( 'local_note' ) );
    tb4_add_text_control( $wp_customize, 'tb4_compat_browser_notice_title', 'tb4_universal_compatibility', __( 'หัวข้อแจ้งเตือนเบราว์เซอร์เก่า', 'thinkb4do' ), tb4_compat_default( 'browser_notice_title' ) );
    tb4_add_textarea_control( $wp_customize, 'tb4_compat_browser_notice_body', 'tb4_universal_compatibility', __( 'รายละเอียดแจ้งเตือนเบราว์เซอร์เก่า', 'thinkb4do' ), tb4_compat_default( 'browser_notice_body' ) );

    // --- Section: Smart Daily Sidebar ---
    $wp_customize->add_section( 'tb4_smart_sidebar', [
        'title'       => __( 'Smart Daily Sidebar', 'thinkb4do' ),
        'description' => __( 'ปรับ Sidebar ให้เป็นผู้ช่วยประจำวัน แสดงไอเดียกิจกรรมใกล้ฉัน วันหยุด ร้านอาหาร สมาชิกที่ติดตาม และพื้นที่โฆษณา โดยไม่เปิดเผยข้อมูลระบบภายใน', 'thinkb4do' ),
        'panel'       => 'tb4_panel',
    ]);
    $wp_customize->add_setting( 'tb4_sidebar_mode', [
        'default'           => 'smart',
        'sanitize_callback' => 'tb4_sanitize_sidebar_mode',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control( 'tb4_sidebar_mode', [
        'label'       => __( 'รูปแบบ Sidebar', 'thinkb4do' ),
        'description' => __( 'Smart = ใช้การ์ดทันสมัยอย่างเดียว, Mixed = แสดงการ์ด Smart ก่อนแล้วต่อด้วย Widget WordPress, WordPress = ใช้ Widget เดิม', 'thinkb4do' ),
        'section'     => 'tb4_smart_sidebar',
        'type'        => 'select',
        'choices'     => [
            'smart'     => __( 'Smart Daily Sidebar', 'thinkb4do' ),
            'mixed'     => __( 'Smart + WordPress Widgets', 'thinkb4do' ),
            'wordpress' => __( 'WordPress Widgets เดิม', 'thinkb4do' ),
        ],
    ]);
    tb4_add_text_control( $wp_customize, 'tb4_sidebar_daily_title', 'tb4_smart_sidebar', __( 'หัวข้อการ์ดหลัก', 'thinkb4do' ), 'ผู้ช่วยประจำวัน' );
    tb4_add_text_control( $wp_customize, 'tb4_sidebar_daily_status', 'tb4_smart_sidebar', __( 'สถานะวันนี้', 'thinkb4do' ), 'รวมไอเดียใกล้ตัวสำหรับวันนี้: ไปไหนดี กินอะไรดี มีอะไรน่าสนใจ และควรติดตามเรื่องไหน' );
    tb4_add_text_control( $wp_customize, 'tb4_sidebar_weather_location', 'tb4_smart_sidebar', __( 'พื้นที่ประจำวัน', 'thinkb4do' ), 'ใกล้ฉัน' );
    tb4_add_text_control( $wp_customize, 'tb4_sidebar_weather_text', 'tb4_smart_sidebar', __( 'ข้อความแนะนำชีวิตประจำวัน', 'thinkb4do' ), 'ดูไอเดียกิจกรรมใกล้ฉัน วางแผนวันหยุด เช็กอากาศก่อนออกเดินทาง และเลือกสิ่งที่เหมาะกับวันนี้' );
    tb4_add_text_control( $wp_customize, 'tb4_sidebar_tip', 'tb4_smart_sidebar', __( 'คำแนะนำสั้นประจำวัน', 'thinkb4do' ), 'เลือกหนึ่งอย่างที่ช่วยให้วันนี้ดีขึ้น เช่น พักผ่อนให้พอ วางแผนมื้ออาหาร หรือหาแรงบันดาลใจจากกิจกรรมใกล้ตัว' );
    tb4_add_text_control( $wp_customize, 'tb4_sidebar_primary_cta', 'tb4_smart_sidebar', __( 'ปุ่มหลัก', 'thinkb4do' ), 'สำรวจไอเดียวันนี้' );
    tb4_add_url_control( $wp_customize, 'tb4_sidebar_primary_url', 'tb4_smart_sidebar', __( 'ลิงก์ปุ่มหลัก', 'thinkb4do' ), home_url( '/community/' ) );
    tb4_add_text_control( $wp_customize, 'tb4_sidebar_secondary_cta', 'tb4_smart_sidebar', __( 'ปุ่มรอง', 'thinkb4do' ), 'ค้นหาในเว็บไซต์' );
    tb4_add_text_control( $wp_customize, 'tb4_sidebar_event_text', 'tb4_smart_sidebar', __( 'ข้อความอีเวนต์/วันหยุด', 'thinkb4do' ), 'วันหยุดนี้ลองเช็กกิจกรรมใกล้ตัว เช่น ตลาดสร้างสรรค์ นิทรรศการ เวิร์กช็อป หรือพื้นที่พักผ่อนใหม่ ๆ' );
    tb4_add_text_control( $wp_customize, 'tb4_sidebar_food_text', 'tb4_smart_sidebar', __( 'ข้อความร้านอาหาร/วันนี้กินอะไรดี', 'thinkb4do' ), 'วันนี้กินอะไรดี? ลองเลือกจากอารมณ์วันนี้: เบา ๆ, อิ่มเร็ว, คาเฟ่ทำงาน, หรือร้านใกล้ฉัน' );
    tb4_add_text_control( $wp_customize, 'tb4_sidebar_ads_text', 'tb4_smart_sidebar', __( 'ข้อความสนใจลงโฆษณา', 'thinkb4do' ), 'สนใจลงโฆษณาหรือฝากแคมเปญกับ Thinkb4do สามารถเตรียมรายละเอียดสินค้า กลุ่มเป้าหมาย และงบประมาณเบื้องต้นไว้ได้' );

    // --- Section: Social ---
    $wp_customize->add_section( 'tb4_social', [
        'title' => __( 'โซเชียลมีเดีย', 'thinkb4do' ),
        'panel' => 'tb4_panel',
    ]);
    foreach ( [ 'facebook' => 'Facebook', 'youtube' => 'YouTube', 'tiktok' => 'TikTok', 'twitter' => 'X (Twitter)', 'line' => 'Line OA' ] as $key => $label ) {
        tb4_add_text_control( $wp_customize, "tb4_social_{$key}", 'tb4_social', $label, '' );
    }
}
add_action( 'customize_register', 'tb4_customizer' );

function tb4_add_text_control( $wp_customize, $id, $section, $label, $default = '' ) {
    $wp_customize->add_setting( $id, [ 'default' => $default, 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'postMessage' ] );
    $wp_customize->add_control( $id, [ 'label' => $label, 'section' => $section, 'type' => 'text' ] );
}
function tb4_add_textarea_control( $wp_customize, $id, $section, $label, $default = '' ) {
    $wp_customize->add_setting( $id, [ 'default' => $default, 'sanitize_callback' => 'sanitize_textarea_field', 'transport' => 'refresh' ] );
    $wp_customize->add_control( $id, [ 'label' => $label, 'section' => $section, 'type' => 'textarea' ] );
}

function tb4_add_url_control( $wp_customize, $id, $section, $label, $default = '' ) {
    $wp_customize->add_setting( $id, [ 'default' => $default, 'sanitize_callback' => 'esc_url_raw', 'transport' => 'refresh' ] );
    $wp_customize->add_control( $id, [ 'label' => $label, 'section' => $section, 'type' => 'url' ] );
}

function tb4_add_color_control( $wp_customize, $id, $section, $label, $default ) {
    $wp_customize->add_setting( $id, [ 'default' => $default, 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage' ] );
    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $id, [ 'label' => $label, 'section' => $section ] ) );
}

/* =============================================
   DYNAMIC CSS FROM CUSTOMIZER
   ============================================= */
function tb4_dynamic_css() {
    $green  = get_theme_mod( 'tb4_color_green', '#1E6B45' );
    $orange = get_theme_mod( 'tb4_color_orange', '#F97316' );
    echo "<style id='tb4-dynamic-css'>:root{--tb4-green:{$green};--tb4-orange:{$orange};}</style>\n";
}
add_action( 'wp_head', 'tb4_dynamic_css', 20 );

/* =============================================
   AJAX — SEARCH
   ============================================= */
function tb4_ajax_search() {
    check_ajax_referer( 'tb4_nonce', 'nonce' );
    $q = sanitize_text_field( $_GET['q'] ?? '' );
    if ( strlen( $q ) < 2 ) { wp_send_json_success( [] ); }

    $args = [
        'post_type'      => apply_filters( 'tb4_search_post_types', [ 'post', 'page', 'tb4_product' ] ),
        'post_status'    => 'publish',
        's'              => $q,
        'posts_per_page' => 8,
    ];
    $query   = new WP_Query( $args );
    $results = [];
    foreach ( $query->posts as $p ) {
        $results[] = [
            'id'    => $p->ID,
            'title' => get_the_title( $p ),
            'url'   => get_permalink( $p ),
            'type'  => $p->post_type,
            'thumb' => get_the_post_thumbnail_url( $p, 'tb4-avatar' ),
        ];
    }
    wp_send_json_success( $results );
}
add_action( 'wp_ajax_tb4_search', 'tb4_ajax_search' );
add_action( 'wp_ajax_nopriv_tb4_search', 'tb4_ajax_search' );


/* =============================================
   v2.4.12 Plugin Safe Shell / Non-overlap Guard
   ทำให้ธีมเป็นเพียง shell เมื่อหน้านั้นถูกปลั๊กอินเป็นเจ้าของ
   ============================================= */
function tb4_is_header_menu_plugin_active() {
    return defined( 'TB4_HEADER_MENU_PLUGIN_VERSION' ) || function_exists( 'tb4_header_menu_render' ) || class_exists( 'TB4_Header_Menu_Plugin' );
}

function tb4_post_contains_plugin_owned_markers( $post = null ) {
    $post = $post instanceof WP_Post ? $post : get_post();
    if ( ! ( $post instanceof WP_Post ) ) {
        return false;
    }

    $content = strtolower( (string) $post->post_content );
    $markers = [
        '[thinkb4do_home_discovery_feed',
        '[thinkb4do_discovery',
        '[thinkb4do_community',
        '[thinkb4do_products',
        '[thinkb4do_product',
        '[thinkb4do_member_area',
        'wp:thinkb4do/discovery',
        'wp:thinkb4do/community',
        'wp:thinkb4do/products',
        'wp:thinkb4do/member-area',
        'tb4-discovery',
        'tb4-community',
        'tb4-products',
        'tb4-member-area',
    ];

    foreach ( $markers as $marker ) {
        if ( false !== strpos( $content, strtolower( $marker ) ) ) {
            return true;
        }
    }

    return false;
}

function tb4_is_plugin_owned_page_context() {
    if ( is_admin() ) {
        return false;
    }

    if ( is_front_page() ) {
        return true;
    }

    if ( function_exists( 'tb4_is_community_context' ) && tb4_is_community_context() ) {
        return true;
    }

    if ( is_page( [ 'products', 'product', 'member-area', 'discovery', 'community', 'thinkb4do-community' ] ) ) {
        return true;
    }

    if ( is_singular( [ 'tb4_product', 'tb4_community', 'tb4_post', 'community_post', 'community' ] ) ) {
        return true;
    }

    if ( is_post_type_archive( [ 'tb4_product', 'tb4_community', 'tb4_post', 'community_post', 'community' ] ) ) {
        return true;
    }

    $queried = get_queried_object();
    if ( $queried instanceof WP_Post ) {
        $slug = strtolower( (string) $queried->post_name );
        if ( in_array( $slug, [ 'products', 'product', 'member-area', 'discovery', 'community', 'thinkb4do-community' ], true ) ) {
            return true;
        }
        if ( tb4_post_contains_plugin_owned_markers( $queried ) ) {
            return true;
        }
    }

    return (bool) apply_filters( 'tb4_is_plugin_owned_page_context', false );
}

function tb4_theme_should_render_mobile_bottom_nav() {
    if ( is_admin() ) {
        return false;
    }

    if ( tb4_is_header_menu_plugin_active() ) {
        return false;
    }

    if ( tb4_is_plugin_owned_page_context() ) {
        return false;
    }

    return (bool) apply_filters( 'tb4_theme_should_render_mobile_bottom_nav', true );
}

/* =============================================
   BODY CLASS
   ============================================= */
function tb4_body_classes( $classes ) {
    if ( is_user_logged_in() ) {
        $classes[] = 'user-logged-in';
    }
    if ( is_front_page() ) {
        $classes[] = 'is-front-page';
    }
    $classes[] = 'thinkb4do-theme';
    $classes[] = is_rtl() ? 'tb4-is-rtl' : 'tb4-is-ltr';
    $locale = sanitize_html_class( strtolower( str_replace( '_', '-', get_locale() ) ) );
    $classes[] = 'tb4-locale-' . $locale;
    $classes[] = tb4_should_use_sticky_header() ? 'tb4-sticky-header' : 'tb4-static-header';
    $classes[] = 'tb4-sticky-mode-' . tb4_get_header_sticky_mode();
    $classes[] = get_theme_mod( 'tb4_dev_notice_enabled', true ) ? 'tb4-dev-notice-on' : 'tb4-dev-notice-off';
    if ( function_exists( 'tb4_is_plugin_owned_page_context' ) && tb4_is_plugin_owned_page_context() ) {
        $classes[] = 'tb4-plugin-owned-page';
        $classes[] = 'tb4-plugin-safe-shell';
    }
    if ( function_exists( 'tb4_is_header_menu_plugin_active' ) && tb4_is_header_menu_plugin_active() ) {
        $classes[] = 'tb4-header-plugin-active';
    }
    return $classes;
}
add_filter( 'body_class', 'tb4_body_classes' );


/* =============================================
   v2.4.6 Member Area body class bridge
   ============================================= */
function tb4_v246_is_member_area_context() {
    if ( is_page( 'member-area' ) ) {
        return true;
    }
    if ( ! is_page() ) {
        return false;
    }
    $post = get_post();
    if ( ! ( $post instanceof WP_Post ) ) {
        return false;
    }
    return has_shortcode( (string) $post->post_content, 'thinkb4do_member_area' )
        || ( function_exists( 'has_block' ) && has_block( 'thinkb4do/member-area', $post ) );
}

function tb4_v246_member_area_body_class( $classes ) {
    if ( tb4_v246_is_member_area_context() ) {
        $classes[] = 'tb4-page-is-member-area';
    }
    return $classes;
}
add_filter( 'body_class', 'tb4_v246_member_area_body_class', 55 );

/* =============================================
   DEVELOPMENT NOTICE BAR
   ============================================= */
function tb4_render_development_notice() {
    if ( ! get_theme_mod( 'tb4_dev_notice_enabled', true ) ) {
        return;
    }

    $notice_title = tb4_dev_text( 'notice_title' );
    $notice_body  = tb4_dev_text( 'notice_body' );
    $can_edit     = current_user_can( 'edit_theme_options' ) && get_theme_mod( 'tb4_dev_notice_admin_edit_link', true );
    $dismissible  = get_theme_mod( 'tb4_dev_notice_dismissible', true );
    $edit_url     = admin_url( 'customize.php?autofocus[section]=tb4_header_settings' );
    ?>
    <div class="tb4-dev-notice tb4-dev-notice-topmost<?php echo $dismissible ? ' tb4-dev-notice-dismissible' : ''; ?>" role="status" aria-label="สถานะเว็บไซต์" data-tb4-dev-notice="topmost" data-tb4-live-component="dev_notice" data-tb4-live-title="แถบประกาศเว็บไซต์กำลังพัฒนา">
        <div class="tb4-dev-notice__inner">
            <span class="tb4-dev-dot" aria-hidden="true"></span>
            <strong><?php echo esc_html( $notice_title ); ?></strong>
            <?php if ( $notice_body ) : ?>
                <span class="tb4-dev-notice__body"><?php echo esc_html( $notice_body ); ?></span>
            <?php endif; ?>
        </div>
        <div class="tb4-dev-notice__actions" aria-label="จัดการแถบประกาศ">
            <?php if ( $can_edit ) : ?>
                <a class="tb4-dev-notice__edit" href="<?php echo esc_url( $edit_url ); ?>"><?php esc_html_e( 'แก้ไข', 'thinkb4do' ); ?></a>
            <?php endif; ?>
            <?php if ( $dismissible ) : ?>
                <button class="tb4-dev-notice__close" type="button" aria-label="ปิดแถบประกาศ" data-tb4-dev-notice-close>&times;</button>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
// v2.1.3: render after the fixed white header so the green notice stays in normal page flow and does not overlap controls.
add_action( 'tb4_after_header', 'tb4_render_development_notice', 5 );

/* =============================================
   ADMIN GUIDE PAGE
   ============================================= */
function tb4_admin_menu() {
    add_theme_page(
        __( 'Thinkb4do Guide', 'thinkb4do' ),
        __( 'Thinkb4do Guide', 'thinkb4do' ),
        'edit_theme_options',
        'thinkb4do-guide',
        'tb4_render_admin_guide_page'
    );
}
add_action( 'admin_menu', 'tb4_admin_menu' );

function tb4_render_admin_guide_page() {
    $sticky_mode = tb4_get_header_sticky_mode();
    $sticky_labels = [
        'all'  => __( 'ตรึงทุกหน้า', 'thinkb4do' ),
        'home' => __( 'ตรึงเฉพาะหน้าแรก', 'thinkb4do' ),
        'none' => __( 'ไม่ตรึงส่วนหัว', 'thinkb4do' ),
    ];
    ?>
    <div class="wrap tb4-guide-wrap">
        <style>
            .tb4-guide-wrap{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",Arial,"Noto Sans Thai","Kanit",sans-serif;max-width:1120px}.tb4-guide-hero{background:linear-gradient(135deg,#0f3d2a,#1e6b45);color:#fff;border-radius:22px;padding:28px 32px;margin:20px 0;box-shadow:0 10px 30px rgba(30,107,69,.22)}.tb4-guide-hero h1{color:#fff;margin:0 0 8px;font-size:28px}.tb4-guide-hero p{color:rgba(255,255,255,.82);max-width:760px;font-size:15px}.tb4-guide-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin:20px 0}.tb4-guide-card{background:#fff;border:1px solid #e5e7eb;border-radius:18px;padding:18px;box-shadow:0 4px 14px rgba(15,23,42,.05)}.tb4-guide-card h2{font-size:17px;margin:0 0 10px}.tb4-guide-card p,.tb4-guide-card li{font-size:14px;color:#4b5563}.tb4-guide-pill{display:inline-flex;align-items:center;gap:6px;background:#e8f5ee;color:#1e6b45;border-radius:999px;padding:4px 10px;font-weight:600;font-size:12px}.tb4-guide-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:14px}.tb4-guide-actions .button-primary{background:#1e6b45;border-color:#1e6b45}.tb4-guide-checklist{margin:0;padding-left:18px}.tb4-guide-checklist li{margin:7px 0}@media(max-width:900px){.tb4-guide-grid{grid-template-columns:1fr}.tb4-guide-hero{padding:22px}}
        </style>
        <div class="tb4-guide-hero">
            <span class="tb4-guide-pill">v1.4.9 / Sticky Scroll Safe</span>
            <h1>Thinkb4do Guide</h1>
            <p>หน้านี้ใช้เป็นไกด์ภายใน WordPress สำหรับเช็กสถานะธีม หน้าแรกแบบ Premium Brand UI โลโก้ที่เลือกจาก Media Library ข้อความความน่าเชื่อถือที่ไม่ใช้เครื่องหมายทางการก่อนอนุมัติจริง ข้อความกำลังพัฒนาที่แก้ไขได้จาก WordPress และ Universal Compatibility สำหรับอุปกรณ์/เบราว์เซอร์หลากหลาย</p>
            <div class="tb4-guide-actions">
                <a class="button button-primary" href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=tb4_dev_messages' ) ); ?>">แก้ข้อความกำลังพัฒนา</a>
                <a class="button" href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=tb4_brand_identity' ) ); ?>">ตั้งค่าโลโก้</a>
                <a class="button" href="<?php echo esc_url( admin_url( 'customize.php?autofocus[panel]=tb4_panel' ) ); ?>">เปิด Theme Customizer</a>
                <a class="button" href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=tb4_dbd_registration' ) ); ?>">ตั้งค่าข้อมูลจดทะเบียน</a>
                <a class="button" href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=tb4_universal_compatibility' ) ); ?>">ตั้งค่ารองรับทุกระบบ</a>
                <a class="button" href="<?php echo esc_url( admin_url( 'themes.php?page=thinkb4do-page-sync' ) ); ?>">เชื่อมหน้า / Components</a>
                <a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener">ดูหน้าเว็บ</a>
            </div>
        </div>
        <div class="tb4-guide-grid">
            <div class="tb4-guide-card">
                <h2>สถานะส่วนหัว</h2>
                <p><strong>Sticky Header:</strong> <?php echo esc_html( $sticky_labels[ $sticky_mode ] ?? $sticky_mode ); ?></p>
                <p>ตั้งค่าได้ที่ <strong>Appearance → Customize → Thinkb4do Settings → ส่วนหัว / Sticky Header</strong></p>
            </div>
            <div class="tb4-guide-card">
                <h2>ปุ่มแจ้งเตือน</h2>
                <p>ตอนนี้เป็น popup ตัวอย่างสำหรับแจ้งสถานะระบบ เช่น เว็บไซต์กำลังพัฒนา ระบบสินค้าเป็นข้อมูลจำลอง และระบบชุมชนยังไม่เปิดจริง</p>
            </div>
            <div class="tb4-guide-card">
                <h2>ข้อความแก้ได้จาก WordPress</h2>
                <p>ไปที่ <strong>Appearance → Customize → Thinkb4do Settings → ข้อความกำลังพัฒนา</strong> เพื่อแก้ข้อความแต่ละตำแหน่ง เช่น ส่วนหัว หน้าแรก สินค้า ชุมชน Sidebar Footer และหน้าไกด์</p>
            </div>
            <div class="tb4-guide-card">
                <h2>ข้อมูลจดทะเบียน / Trust Center</h2>
                <p><strong>สถานะ:</strong> <?php echo esc_html( tb4_dbd_status_label() ); ?></p>
                <p>ตั้งค่าได้ที่ <strong>Appearance → Customize → Thinkb4do Settings → Trust Center / ข้อมูลจดทะเบียน</strong> โดยใช้เลขจริงเท่านั้น หากยังไม่มีให้แสดงสถานะกำลังเตรียมข้อมูล และห้ามใช้โลโก้/เครื่องหมายรับรองก่อนอนุมัติจริง</p>
            </div>
            <div class="tb4-guide-card">
                <h2>Universal Compatibility</h2>
                <p>เพิ่ม compatibility bootstrap, no-JS fallback, CSS fallback, reduced motion, forced colors, safe area และ shortcode <code>[thinkb4do_compatibility]</code></p>
                <p>ตั้งค่าได้ที่ <strong>Appearance → Customize → Thinkb4do Settings → รองรับทุกอุปกรณ์ / Universal</strong></p>
            </div>
        </div>
        <div class="tb4-guide-card">
            <h2>Checklist ก่อนเปิดใช้งานจริง</h2>
            <ol class="tb4-guide-checklist">
                <li>ตรวจหน้าแรกบนมือถือ แท็บเล็ต และเดสก์ท็อป</li>
                <li>เลือกโลโก้จริงจาก Media Library หรือใช้โลโก้ตัวอักษรสำรองจนกว่าจะมีไฟล์แบรนด์ที่พร้อมใช้งาน</li>
                <li>เช็กว่าปุ่มสมัครสมาชิก / เข้าสู่ระบบ / ค้นหา / แจ้งเตือน กดได้ตามที่ตั้งใจ</li>
                <li>แก้ข้อความสถานะจาก Customizer ก่อน หากบางตำแหน่งยังใช้ข้อความที่ไม่ตรงกับสถานะจริง</li>
                <li>เปลี่ยนข้อมูลจำลองในสินค้า ชุมชน สมาชิก และบทความ เมื่อมีข้อมูลจริง</li>
                <li>ตั้งค่า Sticky Header และแถบประกาศบนสุดให้เหมาะกับเว็บจริง: เปิด / ปิด / แก้ข้อความ / ให้ผู้ชมกดปิดได้</li>
                <li>ตรวจข้อความไม่ให้ดูเหมือนโฆษณาเกินจริงก่อนเผยแพร่</li>
                <li>ห้ามใช้โลโก้/เครื่องหมาย DBD หรือหน่วยงานราชการก่อนจดทะเบียนหรือได้รับอนุมัติจริง</li>
                <li>ตรวจบน Chrome, Safari, Edge, Firefox และมือถือ Android/iOS อย่างน้อยหนึ่งรอบก่อนเปิดใช้งานจริง</li>
                <li>ตรวจโหมดลดการเคลื่อนไหว, คอนทราสต์สูง และกรณีปิด JavaScript ว่าเนื้อหาหลักยังอ่านได้</li>
            </ol>
        </div>
    </div>
    <?php
}


/* =============================================
   EXCERPT LENGTH
   ============================================= */
add_filter( 'excerpt_length', fn() => 30 );
add_filter( 'excerpt_more',   fn() => '...' );

/* =============================================
   HELPERS
   ============================================= */
function tb4_format_price( $price ) {
    return '฿' . number_format( (float) $price );
}


/* =============================================
   v1.5.8 Critical 404 Header/Search Clamp — loaded in <head> so it beats cached/lazy CSS timing.
   ============================================= */
function tb4_404_critical_clamp_css() {
    ?>
    <style id="tb4-404-critical-clamp-v158">
      body.error404 #site-header,.error404 #site-header{display:flex!important;visibility:visible!important;opacity:1!important;min-height:var(--tb4-header-real-h,var(--header-h,64px))!important;height:auto!important;transform:none!important;z-index:1200!important}body.error404 #site-header .container,.error404 #site-header .container,body.error404 .header-inner,.error404 .header-inner{display:flex!important;visibility:visible!important;opacity:1!important}body.error404 .site-logo,.error404 .site-logo{display:inline-flex!important;align-items:center!important}.tb4-404-mini-header,.tb4-404-topbar,.error-404-topbar,.not-found-topbar{display:none!important}.tb4-404-page,.tb4-404-page *{box-sizing:border-box!important;min-width:0!important;max-width:100%!important}.tb4-404-page{width:100%!important;max-width:100vw!important;overflow-x:clip!important}.tb4-404-hero,.tb4-404-links{width:100%!important;max-width:100vw!important;overflow-x:hidden!important}.tb4-404-container,.tb4-404-links .container{width:calc(100% - 28px)!important;max-width:calc(100vw - 28px)!important;margin-left:auto!important;margin-right:auto!important;padding-left:14px!important;padding-right:14px!important;overflow:hidden!important}.tb4-404-copy,.tb4-404-copy *{white-space:normal!important;overflow-wrap:anywhere!important;word-break:break-word!important;line-break:anywhere!important}.tb4-404-copy{display:flex!important;flex-direction:column!important;align-items:center!important;gap:2px!important;width:100%!important;max-width:min(100%,270px)!important;margin-left:auto!important;margin-right:auto!important;text-align:center!important}.tb4-404-copy-line,.tb4-404-copy span{display:block!important;width:100%!important;max-width:100%!important;text-align:center!important}.tb4-404-hero h1{max-width:100%!important;text-align:center!important;overflow-wrap:anywhere!important;word-break:break-word!important}.tb4-404-search{width:100%!important;max-width:min(520px,calc(100vw - 32px))!important;margin:0 auto 22px!important}.tb4-404-search-form,.tb4-404-search .search-form{display:block!important;width:100%!important;margin:0 auto!important}.tb4-404-search-box,.tb4-404-search .search-bar{display:grid!important;grid-template-columns:minmax(0,1fr) auto!important;align-items:center!important;gap:10px!important;width:100%!important;padding:10px!important;border-radius:999px!important;background:#fff!important;border:1px solid rgba(30,107,69,.16)!important;box-shadow:0 18px 50px rgba(15,23,42,.08)!important}.tb4-404-search-control{display:flex!important;align-items:center!important;gap:8px!important;min-width:0!important;width:100%!important;padding:0 8px!important}.tb4-404-search input[type=search],.tb4-404-search .search-field{width:100%!important;min-width:0!important;height:42px!important;min-height:42px!important;padding:0!important;border:0!important;outline:0!important;background:transparent!important;text-align:left!important}.tb4-404-search button,.tb4-404-search .search-submit{display:inline-flex!important;align-items:center!important;justify-content:center!important;width:auto!important;min-width:112px!important;min-height:44px!important;height:44px!important;padding:0 18px!important;border-radius:999px!important;white-space:nowrap!important}@media(max-width:767px){.tb4-404-hero h1{font-size:clamp(1.72rem,7.4vw,2.14rem)!important;line-height:1.22!important;letter-spacing:-.006em!important}.tb4-404-copy{font-size:clamp(.9rem,3.9vw,.96rem)!important;line-height:1.62!important}.tb4-404-actions{display:grid!important;grid-template-columns:1fr!important;width:min(100%,300px)!important;max-width:calc(100vw - 64px)!important;margin-left:auto!important;margin-right:auto!important}.tb4-404-actions .btn{width:100%!important;min-width:0!important;max-width:100%!important;white-space:normal!important}}@media(max-width:520px){.tb4-404-search-box,.tb4-404-search .search-bar{grid-template-columns:1fr!important;border-radius:24px!important;padding:12px!important}.tb4-404-search button,.tb4-404-search .search-submit{width:100%!important;min-width:0!important}}
    </style>
    <?php
}
add_action( 'wp_head', 'tb4_404_critical_clamp_css', 999 );

/* =============================================
   v1.7.7 Native WordPress Admin Bar Master Restore
   เป้าหมาย: ให้แถบ Admin Bar ต้นทางของ WordPress กลับมาแสดงทุกหน้าเว็บเมื่อผู้ใช้ล็อกอิน
   ไม่สร้าง toolbar ใหม่ ไม่เปลี่ยน HTML ของ WordPress และไม่แตะหน้าตาส่วนจัดการ WordPress
   ============================================= */
function tb4_v176_force_native_wp_admin_bar_master() {
    if ( is_user_logged_in() && ! is_admin() ) {
        show_admin_bar( true );
    }
}
add_action( 'init', 'tb4_v176_force_native_wp_admin_bar_master', 0 );
add_action( 'template_redirect', 'tb4_v176_force_native_wp_admin_bar_master', 0 );
add_filter( 'show_admin_bar', function( $show ) {
    if ( is_user_logged_in() && ! is_admin() ) {
        return true;
    }
    return $show;
}, PHP_INT_MAX );

function tb4_v176_frontend_admin_bar_fallback_render() {
    if ( ! is_user_logged_in() || is_admin() ) {
        return;
    }
    if ( ! function_exists( 'is_admin_bar_showing' ) || ! is_admin_bar_showing() ) {
        return;
    }
    if ( ! function_exists( 'wp_admin_bar_render' ) ) {
        return;
    }
    // WordPress ปกติจะ render เองใน wp_footer; ถ้าถูกปลั๊กอิน/ธีมซ่อนหรือถอด action ออก ค่อย render สำรองครั้งเดียว
    if ( did_action( 'wp_before_admin_bar_render' ) ) {
        return;
    }
    wp_admin_bar_render();
}
add_action( 'wp_footer', 'tb4_v176_frontend_admin_bar_fallback_render', PHP_INT_MAX );

function tb4_v176_admin_bar_master_rescue_css() {
    if ( ! is_user_logged_in() || is_admin() ) {
        return;
    }
    ?>
    <style id="tb4-v176-native-adminbar-master-rescue">
      html { --tb4-wp-adminbar-h: 32px; }
      @media screen and (max-width: 782px) { html { --tb4-wp-adminbar-h: 46px; } }

      html body.admin-bar,
      body.admin-bar {
        padding-top: 0 !important;
      }

      body.admin-bar #wpadminbar,
      #wpadminbar {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
        min-width: 0 !important;
        max-width: none !important;
        height: var(--tb4-wp-adminbar-h) !important;
        z-index: 2147483647 !important;
        transform: none !important;
        translate: none !important;
        filter: none !important;
        clip: auto !important;
        clip-path: none !important;
        overflow: visible !important;
        pointer-events: auto !important;
      }

      #wpadminbar *,
      #wpadminbar a,
      #wpadminbar button,
      #wpadminbar .ab-item,
      #wpadminbar .ab-sub-wrapper {
        pointer-events: auto !important;
        visibility: visible !important;
      }

      /* ให้ส่วนหัวธีมและ popup อยู่ใต้ WordPress Admin Bar เสมอ ไม่ทับแถบต้นทาง */
      body.admin-bar #site-header,
      body.admin-bar.thinkb4do-theme.tb4-sticky-header #site-header {
        top: var(--tb4-wp-adminbar-h) !important;
      }

      body.admin-bar .tb4-dev-notice {
        top: calc(var(--tb4-wp-adminbar-h) + var(--tb4-header-real-h, var(--tb4-header-desktop-h, 58px))) !important;
      }

      body.admin-bar .tb4-notification-panel,
      body.admin-bar .user-dropdown,
      body.admin-bar .tb4-search-results,
      body.admin-bar .mobile-menu {
        z-index: 2147483600 !important;
      }
    </style>
    <?php
}
add_action( 'wp_head', 'tb4_v176_admin_bar_master_rescue_css', PHP_INT_MAX );


/* =============================================
   v1.7.7 Frontend Native WordPress Admin Bar Hard Restore
   เป้าหมาย: ให้ Admin Bar สีดำต้นทางของ WordPress โผล่บนหน้าเว็บด้านหน้าแน่นอน เมื่อผู้ใช้ล็อกอิน
   - ไม่สร้างแถบ custom
   - ไม่เปลี่ยน HTML ของ WordPress Admin Bar
   - Render ที่ wp_body_open เพื่อให้ขึ้นก่อนส่วนหัวธีมทุกหน้า
   ============================================= */
function tb4_v177_is_frontend_logged_in_adminbar_context() {
    return is_user_logged_in() && ! is_admin();
}

function tb4_v177_force_show_native_admin_bar() {
    if ( tb4_v177_is_frontend_logged_in_adminbar_context() ) {
        show_admin_bar( true );
    }
}
add_action( 'init', 'tb4_v177_force_show_native_admin_bar', -999 );
add_action( 'wp_loaded', 'tb4_v177_force_show_native_admin_bar', -999 );
add_action( 'template_redirect', 'tb4_v177_force_show_native_admin_bar', -999 );
add_filter( 'show_admin_bar', function( $show ) {
    return tb4_v177_is_frontend_logged_in_adminbar_context() ? true : $show;
}, PHP_INT_MAX );

function tb4_v177_enqueue_native_admin_bar_assets() {
    if ( ! tb4_v177_is_frontend_logged_in_adminbar_context() ) {
        return;
    }
    wp_enqueue_style( 'admin-bar' );
    wp_enqueue_script( 'admin-bar' );
}
add_action( 'wp_enqueue_scripts', 'tb4_v177_enqueue_native_admin_bar_assets', 0 );

function tb4_v177_body_admin_bar_class( $classes ) {
    if ( tb4_v177_is_frontend_logged_in_adminbar_context() && ! in_array( 'admin-bar', $classes, true ) ) {
        $classes[] = 'admin-bar';
    }
    return $classes;
}
add_filter( 'body_class', 'tb4_v177_body_admin_bar_class', PHP_INT_MAX );

function tb4_v177_render_native_admin_bar_at_top() {
    if ( ! tb4_v177_is_frontend_logged_in_adminbar_context() ) {
        return;
    }
    if ( did_action( 'tb4_v177_native_admin_bar_rendered' ) || did_action( 'wp_before_admin_bar_render' ) ) {
        return;
    }

    tb4_v177_force_show_native_admin_bar();

    if ( ! function_exists( 'wp_admin_bar_render' ) && file_exists( ABSPATH . WPINC . '/admin-bar.php' ) ) {
        require_once ABSPATH . WPINC . '/admin-bar.php';
    }

    if ( function_exists( '_wp_admin_bar_init' ) && ( empty( $GLOBALS['wp_admin_bar'] ) || ! is_object( $GLOBALS['wp_admin_bar'] ) ) ) {
        _wp_admin_bar_init();
    }

    if ( ! function_exists( 'wp_admin_bar_render' ) ) {
        return;
    }

    // ป้องกันการ render ซ้ำช่วง footer ถ้าเราวาดไว้ด้านบนแล้ว
    remove_action( 'wp_footer', 'wp_admin_bar_render', 1000 );

    wp_admin_bar_render();
    do_action( 'tb4_v177_native_admin_bar_rendered' );
}
add_action( 'wp_body_open', 'tb4_v177_render_native_admin_bar_at_top', 0 );
add_action( 'tb4_before_header', 'tb4_v177_render_native_admin_bar_at_top', 0 );

function tb4_v177_frontend_adminbar_hard_css() {
    if ( ! tb4_v177_is_frontend_logged_in_adminbar_context() ) {
        return;
    }
    ?>
    <style id="tb4-v177-frontend-native-adminbar-hard-restore">
      html { --tb4-wp-adminbar-h: 32px !important; }
      @media screen and (max-width: 782px) { html { --tb4-wp-adminbar-h: 46px !important; } }

      #wpadminbar {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        position: fixed !important;
        inset: 0 0 auto 0 !important;
        width: 100% !important;
        min-width: 0 !important;
        max-width: none !important;
        height: var(--tb4-wp-adminbar-h) !important;
        z-index: 2147483647 !important;
        transform: none !important;
        translate: none !important;
        clip: auto !important;
        clip-path: none !important;
        overflow: visible !important;
        pointer-events: auto !important;
        -webkit-transform: none !important;
      }
      #wpadminbar *,
      #wpadminbar a,
      #wpadminbar button,
      #wpadminbar .ab-item,
      #wpadminbar .ab-sub-wrapper {
        pointer-events: auto !important;
        visibility: visible !important;
      }

      body.admin-bar #site-header,
      body.logged-in #site-header,
      body.admin-bar.thinkb4do-theme.tb4-sticky-header #site-header,
      body.logged-in.thinkb4do-theme.tb4-sticky-header #site-header {
        top: var(--tb4-wp-adminbar-h) !important;
      }

      body.admin-bar .tb4-dev-notice,
      body.logged-in .tb4-dev-notice {
        top: calc(var(--tb4-wp-adminbar-h) + var(--tb4-header-real-h, var(--tb4-header-desktop-h, 58px))) !important;
      }

      body.admin-bar .tb4-notification-panel,
      body.admin-bar .user-dropdown,
      body.admin-bar .tb4-search-results,
      body.admin-bar .mobile-menu,
      body.logged-in .tb4-notification-panel,
      body.logged-in .user-dropdown,
      body.logged-in .tb4-search-results,
      body.logged-in .mobile-menu {
        z-index: 2147483600 !important;
      }
    </style>
    <?php
}
add_action( 'wp_head', 'tb4_v177_frontend_adminbar_hard_css', PHP_INT_MAX );

/* =============================================
   THINKB4DO CORE THEME v2.0.0
   Live edit / Gutenberg editing tools were moved to the Thinkb4do Live Studio plugin.
   ============================================= */



/* v2.0.8 note: old mobile menu hard-port inline fallbacks removed to prevent duplicate listeners, scroll lock, and resize freeze. */

