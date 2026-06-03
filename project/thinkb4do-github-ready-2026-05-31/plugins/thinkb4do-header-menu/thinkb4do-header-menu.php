<?php
/**
 * Plugin Name: Thinkb4do Header Menu
 * Plugin URI: https://thinkb4do.com/
 * Description: แยก Header/Menu ของ Thinkb4do ออกจากธีม เพื่อให้ Body Layout และ Footer อยู่ในธีมเหมือนเดิม แต่อัปเดต/ปิดเปิด Header ได้แบบปลั๊กอิน
 * Version: 1.0.16
 * Author: Thinkb4do
 * Text Domain: thinkb4do-header-menu
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

defined( 'ABSPATH' ) || exit;

define( 'TB4_HEADER_MENU_PLUGIN_VERSION', '1.0.16' );
define( 'TB4_HEADER_MENU_PLUGIN_FILE', __FILE__ );
define( 'TB4_HEADER_MENU_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'TB4_HEADER_MENU_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

add_action( 'wp_enqueue_scripts', 'tb4hm_enqueue_assets', 12 );
add_action( 'admin_menu', 'tb4hm_register_settings_page' );
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'tb4hm_plugin_action_links' );
add_action( 'wp_body_open', 'tb4hm_render_header_once', 5 );
add_action( 'template_redirect', 'tb4hm_start_header_fallback_buffer', 0 );


/**
 * โหลด CSS/JS เฉพาะ Header/Menu
 */
function tb4hm_enqueue_assets() {
    $deps = [];
    if ( wp_style_is( 'thinkb4do-style', 'registered' ) || wp_style_is( 'thinkb4do-style', 'enqueued' ) ) {
        $deps[] = 'thinkb4do-style';
    }

    wp_enqueue_style(
        'tb4hm-header-menu',
        TB4_HEADER_MENU_PLUGIN_URL . 'assets/css/tb4-header-menu.css',
        $deps,
        TB4_HEADER_MENU_PLUGIN_VERSION
    );

    wp_enqueue_script(
        'tb4hm-header-menu',
        TB4_HEADER_MENU_PLUGIN_URL . 'assets/js/tb4-header-menu.js',
        [],
        TB4_HEADER_MENU_PLUGIN_VERSION,
        true
    );
}

function tb4hm_register_settings_page() {
    add_options_page(
        'Thinkb4do Header Menu',
        'Thinkb4do Header Menu',
        'manage_options',
        'thinkb4do-header-menu',
        'tb4hm_render_settings_page'
    );
}

function tb4hm_plugin_action_links( $links ) {
    $settings = '<a href="' . esc_url( admin_url( 'options-general.php?page=thinkb4do-header-menu' ) ) . '">Settings</a>';
    array_unshift( $links, $settings );
    return $links;
}


/**
 * Fallback สำหรับบางหน้า/เทมเพลต เช่น หน้าชุมชน ที่อาจไม่เรียก wp_body_open()
 * ใช้ output buffer เพื่อตรวจ HTML สุดท้าย ถ้ายังไม่มี Header จากปลั๊กอิน จะฉีดเข้าไปหลัง <body>
 * จุดนี้ทำให้เมนู Discovery / ชุมชน / ผลิตภัณฑ์ มีโอกาสแสดงครบทุกหน้าโดยไม่ซ้ำกับหน้าเดิม
 */
function tb4hm_start_header_fallback_buffer() {
    if ( is_admin() || wp_doing_ajax() || is_feed() || is_robots() || is_trackback() ) {
        return;
    }

    if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
        return;
    }

    if ( defined( 'WP_CLI' ) && WP_CLI ) {
        return;
    }

    ob_start( 'tb4hm_inject_header_fallback_into_body' );
}

function tb4hm_inject_header_fallback_into_body( $html ) {
    if ( ! is_string( $html ) || '' === $html ) {
        return $html;
    }

    // ถ้ามี Header แล้ว ไม่ต้องแทรกซ้ำ
    if ( false !== strpos( $html, 'data-tb4-live-component="header"' ) || false !== strpos( $html, 'id="site-header"' ) ) {
        return $html;
    }

    if ( false === stripos( $html, '<body' ) ) {
        return $html;
    }

    ob_start();
    tb4_header_menu_render();
    $header_markup = ob_get_clean();

    if ( empty( $header_markup ) ) {
        return $html;
    }

    $pattern = '/<body\b[^>]*>/i';
    if ( ! preg_match( $pattern, $html, $matches, PREG_OFFSET_CAPTURE ) ) {
        return $header_markup . $html;
    }

    $body_open_end = $matches[0][1] + strlen( $matches[0][0] );

    return substr( $html, 0, $body_open_end ) . "\n" . $header_markup . substr( $html, $body_open_end );
}

function tb4hm_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap tb4hm-admin-page">
      <h1>Thinkb4do Header Menu</h1>
      <p><strong>Settings | โดย Thinkb4do | ดูรายละเอียด</strong></p>
      <div style="max-width:780px;background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:18px 20px;margin-top:16px;box-shadow:0 8px 30px rgba(15,23,42,.06);">
        <h2 style="margin-top:0;">สถานะการแยกระบบ</h2>
        <p>Header/Menu ถูกควบคุมจากปลั๊กอินนี้ ส่วน Body Layout และ Footer ยังอยู่ในธีม Thinkb4do เหมือนเดิม</p>
        <ul style="list-style:disc;padding-left:22px;">
          <li>Header Render: <strong>เชื่อมต่อสำเร็จ</strong></li>
          <li>ตำแหน่งเมนู: <strong>รองรับ Desktop / Tablet / Mobile</strong></li>
          <li>Fallback: <strong>มีในธีม หากปิดปลั๊กอิน</strong></li>
        </ul>
      </div>
    </div>
    <?php
}

function tb4hm_text( $key, $fallback = '' ) {
    if ( function_exists( 'tb4_dev_text' ) ) {
        return tb4_dev_text( $key, $fallback );
    }

    $defaults = [
        'header_search_placeholder' => 'ค้นหา Reel ชุมชน ผลิตภัณฑ์',
        'notification_badge'        => 'อัปเดตล่าสุด',
        'notification_1_title'      => 'คู่มือระบบ',
        'notification_1_body'       => 'ดูสถานะและรายละเอียดการพัฒนา',
        'notification_2_title'      => 'ตั้งค่า Header',
        'notification_2_body'       => 'ปรับตำแหน่งและการแสดงผลของส่วนหัว',
        'notification_3_title'      => 'ผลิตภัณฑ์ใหม่',
        'notification_3_body'       => 'ดูพื้นที่ผลิตภัณฑ์ของ Thinkb4do',
        'message_button_title'      => 'ข้อความ',
    ];

    return $defaults[ $key ] ?? $fallback;
}


function tb4hm_icon( $name, $class = 'tb4hm-icon' ) {
    $icons = [
        'search' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M10.7 5.5a5.2 5.2 0 1 0 0 10.4 5.2 5.2 0 0 0 0-10.4Zm-7 5.2a7 7 0 1 1 12.5 4.35l3.57 3.57a.9.9 0 0 1-1.27 1.27l-3.57-3.57A7 7 0 0 1 3.7 10.7Z" fill="currentColor"/></svg>',
        'bell'   => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 3.25a5.8 5.8 0 0 0-5.8 5.8v2.65c0 .92-.34 1.8-.95 2.49l-.7.8a1.8 1.8 0 0 0 1.35 2.99h3.06a3.18 3.18 0 0 0 6.08 0h3.06a1.8 1.8 0 0 0 1.35-2.99l-.7-.8a3.78 3.78 0 0 1-.95-2.49V9.05A5.8 5.8 0 0 0 12 3.25Zm0 1.8a4 4 0 0 1 4 4v2.65c0 1.36.5 2.68 1.4 3.7l.7.8H5.9l.7-.8A5.58 5.58 0 0 0 8 11.7V9.05a4 4 0 0 1 4-4Zm-1.1 12.93a1.38 1.38 0 0 0 2.2 0h-2.2Z" fill="currentColor"/></svg>',
        'chat'   => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M5.25 5.7A7.9 7.9 0 0 1 12 2.75c4.55 0 8.25 3.2 8.25 7.15s-3.7 7.15-8.25 7.15c-.75 0-1.48-.08-2.18-.25l-3.58 2.1a1.1 1.1 0 0 1-1.62-1.05l.38-3.2A6.78 6.78 0 0 1 3.75 9.9c0-1.56.55-3.02 1.5-4.2Zm1.38 1.15A4.99 4.99 0 0 0 5.55 9.9c0 1.32.56 2.56 1.57 3.53.2.2.3.48.27.76l-.26 2.17 2.08-1.22c.2-.12.45-.16.68-.1.67.18 1.38.27 2.11.27 3.56 0 6.45-2.43 6.45-5.4S15.56 4.5 12 4.5c-2.2 0-4.13.93-5.37 2.35Z" fill="currentColor"/></svg>',
        'globe'  => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 3.25a8.75 8.75 0 1 0 0 17.5 8.75 8.75 0 0 0 0-17.5Zm5.78 5.25h-2.2a12.15 12.15 0 0 0-1.15-2.75A7 7 0 0 1 17.78 8.5Zm-5.78-3c.58.82 1.18 1.84 1.55 3h-3.1c.37-1.16.97-2.18 1.55-3Zm-6.64 7.38a7.07 7.07 0 0 1 0-1.76h3.08a10.7 10.7 0 0 0 0 1.76H5.36Zm.86 2.62h2.2c.28.99.68 1.92 1.15 2.75A7 7 0 0 1 6.22 15.5Zm2.2-7h-2.2a7 7 0 0 1 3.35-2.75A12.15 12.15 0 0 0 8.42 8.5ZM12 18.5c-.58-.82-1.18-1.84-1.55-3h3.1c-.37 1.16-.97 2.18-1.55 3Zm1.98-4.75h-3.96a8.82 8.82 0 0 1 0-3.5h3.96a8.82 8.82 0 0 1 0 3.5Zm.45 4.5c.47-.83.87-1.76 1.15-2.75h2.2a7 7 0 0 1-3.35 2.75Zm1.13-5.37a10.7 10.7 0 0 0 0-1.76h3.08a7.07 7.07 0 0 1 0 1.76h-3.08Z" fill="currentColor"/></svg>',
        'signin' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M10.25 5.15a.9.9 0 0 1 .9-.9h6.1A2.75 2.75 0 0 1 20 7v10a2.75 2.75 0 0 1-2.75 2.75h-6.1a.9.9 0 1 1 0-1.8h6.1c.52 0 .95-.43.95-.95V7a.95.95 0 0 0-.95-.95h-6.1a.9.9 0 0 1-.9-.9Zm1.52 3.72a.9.9 0 0 1 1.27 0l2.48 2.5a.9.9 0 0 1 0 1.26l-2.48 2.5a.9.9 0 1 1-1.28-1.27l.95-.96H4.9a.9.9 0 1 1 0-1.8h7.81l-.95-.96a.9.9 0 0 1 .01-1.27Z" fill="currentColor"/></svg>',
        'caret'  => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M7.3 9.3a1 1 0 0 1 1.4 0l3.3 3.29 3.3-3.3a1 1 0 1 1 1.4 1.42l-4 4a1 1 0 0 1-1.4 0l-4-4a1 1 0 0 1 0-1.42Z" fill="currentColor"/></svg>',
    ];

    if ( empty( $icons[ $name ] ) ) {
        return '';
    }

    return '<span class="' . esc_attr( $class ) . ' tb4hm-icon--' . esc_attr( $name ) . '">' . $icons[ $name ] . '</span>';
}

function tb4hm_normalize_avatar_value_to_url( $raw, $user_id, $size = 40 ) {
    if ( empty( $raw ) ) {
        return '';
    }

    $size = max( 24, absint( $size ) );

    if ( is_array( $raw ) ) {
        $candidate_keys = [
            'full',
            'original',
            'url',
            'avatar_url',
            'profile_photo',
            'photo',
            'file',
            'path',
            'media_id',
            'attachment_id',
            $size,
            (string) $size,
        ];

        foreach ( $candidate_keys as $candidate_key ) {
            if ( isset( $raw[ $candidate_key ] ) && ! empty( $raw[ $candidate_key ] ) ) {
                $resolved = tb4hm_normalize_avatar_value_to_url( $raw[ $candidate_key ], $user_id, $size );
                if ( $resolved ) {
                    return $resolved;
                }
            }
        }

        foreach ( $raw as $value ) {
            $resolved = tb4hm_normalize_avatar_value_to_url( $value, $user_id, $size );
            if ( $resolved ) {
                return $resolved;
            }
        }

        return '';
    }

    if ( is_numeric( $raw ) ) {
        $attachment_url = wp_get_attachment_image_url( absint( $raw ), [ $size, $size ] );
        return $attachment_url ? esc_url_raw( $attachment_url ) : '';
    }

    if ( ! is_string( $raw ) ) {
        return '';
    }

    $raw = trim( $raw );
    if ( '' === $raw ) {
        return '';
    }

    if ( filter_var( $raw, FILTER_VALIDATE_URL ) ) {
        return esc_url_raw( $raw );
    }

    if ( 0 === strpos( $raw, '//' ) ) {
        return esc_url_raw( is_ssl() ? 'https:' . $raw : 'http:' . $raw );
    }

    $uploads = wp_upload_dir();
    if ( ! empty( $uploads['baseurl'] ) ) {
        $file = ltrim( $raw, '/' );

        // Ultimate Member มักเก็บแค่ชื่อไฟล์ไว้ใน meta profile_photo แล้วไฟล์จริงอยู่ใน uploads/ultimatemember/{user_id}/
        $um_url = trailingslashit( $uploads['baseurl'] ) . 'ultimatemember/' . absint( $user_id ) . '/' . $file;
        if ( ! empty( $uploads['basedir'] ) ) {
            $um_file = trailingslashit( $uploads['basedir'] ) . 'ultimatemember/' . absint( $user_id ) . '/' . $file;
            if ( file_exists( $um_file ) ) {
                return esc_url_raw( $um_url );
            }
        }

        if ( preg_match( '/\.(jpg|jpeg|png|gif|webp|avif|svg)$/i', $file ) ) {
            return esc_url_raw( trailingslashit( $uploads['baseurl'] ) . $file );
        }
    }

    return '';
}

function tb4hm_extract_avatar_src_from_html( $html ) {
    if ( ! is_string( $html ) || '' === $html ) {
        return '';
    }

    if ( preg_match( '/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $matches ) && ! empty( $matches[1] ) ) {
        $src = html_entity_decode( $matches[1], ENT_QUOTES, get_bloginfo( 'charset' ) );
        return filter_var( $src, FILTER_VALIDATE_URL ) ? esc_url_raw( $src ) : '';
    }

    return '';
}

function tb4hm_get_user_avatar_url( $user, $size = 40 ) {
    if ( ! $user || empty( $user->ID ) ) {
        return '';
    }

    $size    = max( 24, absint( $size ) );
    $user_id = absint( $user->ID );

    /**
     * 1) ดึงจากระบบสมาชิกยอดนิยมก่อน เพราะหลายระบบไม่ได้ส่งค่ากลับผ่าน get_avatar_url()
     */
    if ( function_exists( 'bp_core_fetch_avatar' ) ) {
        $bp_avatar = bp_core_fetch_avatar( [
            'item_id' => $user_id,
            'object'  => 'user',
            'type'    => 'full',
            'html'    => false,
            'width'   => $size,
            'height'  => $size,
        ] );
        $bp_avatar = tb4hm_normalize_avatar_value_to_url( $bp_avatar, $user_id, $size );
        if ( $bp_avatar ) {
            return $bp_avatar;
        }
    }

    if ( function_exists( 'um_get_user_avatar_url' ) ) {
        $um_avatar = um_get_user_avatar_url( $user_id, 'original' );
        $um_avatar = tb4hm_normalize_avatar_value_to_url( $um_avatar, $user_id, $size );
        if ( $um_avatar ) {
            return $um_avatar;
        }
    }

    /**
     * 2) รองรับ user meta ของปลั๊กอิน avatar / membership หลายแบบ
     */
    $meta_keys = apply_filters( 'tb4hm_avatar_meta_keys', [
        'tb4_profile_avatar',
        'tb4_avatar_url',
        'profile_avatar',
        'profile_picture',
        'user_profile_picture',
        'profile_photo',
        'user_avatar',
        'avatar_url',
        'avatar',
        'simple_local_avatar',
        'wp_user_avatar',
        '_wp_user_avatar',
        'wp_user_avatar_attachment_id',
        'basic_user_avatar',
        'metronet_image_id',
        'pp_profile_picture',
        'peepso_user_avatar',
    ] );

    foreach ( (array) $meta_keys as $meta_key ) {
        $raw = get_user_meta( $user_id, $meta_key, true );
        $url = tb4hm_normalize_avatar_value_to_url( $raw, $user_id, $size );
        if ( $url ) {
            return $url;
        }
    }

    /**
     * 3) บางปลั๊กอินกรองเฉพาะ get_avatar() เป็น HTML ไม่กรอง get_avatar_url()
     */
    $avatar_html = get_avatar( $user_id, $size, '404', '', [
        'force_default' => false,
        'class'         => 'tb4hm-avatar-probe',
    ] );
    $html_src = tb4hm_extract_avatar_src_from_html( $avatar_html );
    if ( $html_src && false === strpos( $html_src, 'd=404' ) && false === strpos( $html_src, 'default=404' ) ) {
        return $html_src;
    }

    /**
     * 4) Gravatar จริงเท่านั้น ถ้าไม่มีรูปจะปล่อย fallback แทน ไม่ใช้ mystery/blank ที่ทำให้ปุ่มดูว่าง
     */
    $avatar_url = get_avatar_url( $user_id, [
        'size'          => $size,
        'default'       => '404',
        'force_default' => false,
    ] );

    return $avatar_url ? esc_url_raw( $avatar_url ) : '';
}

function tb4hm_get_user_avatar_markup( $user, $size = 40, $extra_class = '' ) {
    $label   = $user && ! empty( $user->display_name ) ? $user->display_name : ( $user && ! empty( $user->user_login ) ? $user->user_login : 'U' );
    $initial = function_exists( 'mb_substr' ) ? mb_substr( $label, 0, 1, 'UTF-8' ) : substr( $label, 0, 1 );
    $initial = function_exists( 'mb_strtoupper' ) ? mb_strtoupper( $initial, 'UTF-8' ) : strtoupper( $initial );
    $class   = trim( 'tb4-user-avatar ' . $extra_class );
    $url     = tb4hm_get_user_avatar_url( $user, $size );

    $markup  = '<span class="' . esc_attr( $class ) . '" data-initial="' . esc_attr( $initial ) . '" aria-hidden="true">';
    $markup .= '<span class="tb4-user-avatar-fallback">' . esc_html( $initial ) . '</span>';

    if ( $url ) {
        $markup .= '<img class="tb4-user-avatar-img" src="' . esc_url( $url ) . '" alt="" width="' . absint( $size ) . '" height="' . absint( $size ) . '" loading="eager" decoding="async" />';
    }

    $markup .= '</span>';

    return $markup;
}

function tb4hm_render_site_logo( $context = 'header' ) {
    if ( function_exists( 'tb4_render_site_logo' ) ) {
        tb4_render_site_logo( $context );
        return;
    }

    $site_name = get_bloginfo( 'name' );
    echo '<a href="' . esc_url( home_url( '/' ) ) . '" class="site-logo site-logo--header" aria-label="' . esc_attr( $site_name ) . '">';
    echo '<span class="logo-icon" aria-hidden="true">T</span>';
    echo '<span class="site-logo-copy"><span class="logo-text">' . esc_html( $site_name ) . '</span></span>';
    echo '</a>';
}

function tb4hm_product_url() {
    $product_url = home_url( '/products/' );
    if ( post_type_exists( 'tb4_product' ) ) {
        $archive = get_post_type_archive_link( 'tb4_product' );
        if ( $archive ) {
            $product_url = $archive;
        }
    }
    return $product_url;
}

function tb4hm_minimal_links() {
    /**
     * บังคับเมนูส่วนหัวให้เหลือเฉพาะ 3 รายการตามโครง Discovery / ชุมชน / ผลิตภัณฑ์
     * ไม่ดึงเมนูเก่าจากธีม เพื่อป้องกันชื่อเมนูซ้ำหรือหลุดกลับไปเป็น หน้าแรก
     */
    $links = [
        [ 'url' => home_url( '/' ),            'label' => 'Discovery',  'match' => [ '/' ] ],
        [ 'url' => home_url( '/community/' ),  'label' => 'ชุมชน',      'match' => [ '/community/' ] ],
        [ 'url' => tb4hm_product_url(),        'label' => 'ผลิตภัณฑ์',  'match' => [ '/products/' ] ],
    ];

    return apply_filters( 'tb4hm_header_links', $links );
}

function tb4hm_is_active_link( $item ) {
    if ( function_exists( 'tb4_is_minimal_header_link_active' ) ) {
        return tb4_is_minimal_header_link_active( $item );
    }

    $request_uri  = wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' );
    $request_path = wp_parse_url( $request_uri, PHP_URL_PATH );
    $request_path = trailingslashit( '/' . ltrim( (string) $request_path, '/' ) );
    $item_path    = trailingslashit( wp_parse_url( $item['url'] ?? '', PHP_URL_PATH ) ?: '/' );

    if ( '/' === $item_path ) {
        return is_front_page() || '/' === $request_path;
    }

    return '/' !== $item_path && 0 === strpos( $request_path, $item_path );
}

function tb4hm_render_nav( $context = 'desktop' ) {
    $context = 'mobile' === $context ? 'mobile' : 'desktop';
    $links   = tb4hm_minimal_links();

    if ( 'mobile' === $context ) {
        echo '<ul class="tb4-mobile-nav-list tb4-mobile-nav-list--minimal">';
    }

    foreach ( $links as $item ) {
        $active = tb4hm_is_active_link( $item );
        $class  = $active ? 'active' : '';
        $aria   = $active ? ' aria-current="page"' : '';
        $anchor = '<a href="' . esc_url( $item['url'] ?? '#' ) . '" class="' . esc_attr( trim( $class ) ) . '"' . $aria . '>' . esc_html( $item['label'] ?? '' ) . '</a>';
        echo 'mobile' === $context ? '<li>' . $anchor . '</li>' : $anchor; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    if ( 'mobile' === $context ) {
        echo '</ul>';
    }
}

function tb4hm_render_app_grid_button() {
    // ใช้ปุ่มจากปลั๊กอินโดยตรง เพื่อให้แตะบนมือถือแล้วเปิดทันที ไม่ชน JS/CSS จากธีมเดิม
    ?>
    <div class="tb4-app-grid-wrap">
      <button class="header-icon-btn tb4-app-grid-trigger" id="tb4AppGridTrigger" type="button" aria-label="เมนูและผลิตภัณฑ์" title="เมนูและผลิตภัณฑ์" aria-haspopup="dialog" aria-expanded="false" aria-controls="tb4AppGridPanel">
        <span class="tb4-nine-grid-icon" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span></span>
      </button>
    </div>
    <?php
}

function tb4hm_render_app_grid_panel() {
    // ใช้ panel จากปลั๊กอินโดยตรง เพื่อแก้ปัญหาต้องกดเมนู 9 จุดซ้ำรอบที่สอง
    $groups = apply_filters( 'tb4_app_grid_groups', [
        'menu' => [
            'title' => 'เมนูหลัก',
            'items' => [
                [ home_url( '/' ), 'Discovery', 'ฟีดค้นพบและหน้าแรก' ],
                [ home_url( '/community/' ), 'ชุมชน', 'พื้นที่พูดคุยและติดตาม' ],
                [ tb4hm_product_url(), 'ผลิตภัณฑ์', 'รวมสิ่งที่ Thinkb4do ทำ' ],
            ],
        ],
        'products' => [
            'title' => 'ผลิตภัณฑ์',
            'items' => [
                [ tb4hm_product_url(), 'ผลิตภัณฑ์ทั้งหมด', 'ดูรายการทั้งหมด' ],
                [ home_url( '/products/think-control/' ), 'Think Control', 'ระบบควบคุมและจัดการ' ],
                [ home_url( '/products/aira-studio/' ), 'AiRA Studio', 'พื้นที่สร้างและพัฒนา' ],
                [ home_url( '/contact/' ), 'ติดต่อเรา', 'สอบถามหรือเสนอความต้องการ' ],
            ],
        ],
    ] );
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
                $item = array_pad( (array) $item, 3, '' );
              ?>
                <a class="tb4-app-grid-item" href="<?php echo esc_url( $item[0] ); ?>">
                  <span class="tb4-app-grid-item-title"><?php echo esc_html( $item[1] ); ?></span>
                  <small><?php echo esc_html( $item[2] ); ?></small>
                </a>
              <?php endforeach; ?>
            </div>
          </section>
        <?php endforeach; ?>
      </div>
    </div>
    <?php
}


/**
 * ทำให้ Header/Menu แสดงได้ทุกหน้าเมื่อธีมมี wp_body_open และป้องกันการแสดงซ้ำ
 * หากธีมเดิมเรียก tb4_header_menu_render() อยู่แล้ว ระบบจะใช้ตัวกันซ้ำด้านใน
 */
function tb4hm_render_header_once() {
    tb4_header_menu_render();
}

/**
 * Render Header/Menu จากปลั๊กอิน
 */
function tb4_header_menu_render() {
    static $tb4hm_header_rendered = false;

    if ( $tb4hm_header_rendered ) {
        return;
    }

    $tb4hm_header_rendered = true;

    do_action( 'tb4_before_header' );
    ?>
    <!-- ==================== HEADER MENU PLUGIN ==================== -->
    <header id="site-header" role="banner" data-tb4-live-component="header" data-tb4-live-title="ส่วนหัวเว็บ">
      <div class="container">
        <div class="header-inner">

          <?php tb4hm_render_site_logo( 'header' ); ?>

          <div class="tb4-header-center" aria-label="แถบเมนูและค้นหา">
            <nav class="main-nav" role="navigation" aria-label="เมนูหลัก">
              <?php tb4hm_render_nav( 'desktop' ); ?>
            </nav>

            <form class="header-search tb4-desktop-header-search tb4-header-search-form" id="headerSearch" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" autocomplete="off" aria-label="ค้นหาใน Thinkb4do">
              <button class="tb4-header-search-leading-submit" type="submit" aria-label="ค้นหา"><?php echo tb4hm_icon( 'search' ); ?><span class="tb4hm-visually-hidden">ค้นหา</span></button>
              <input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php echo esc_attr( tb4hm_text( 'header_search_placeholder', 'ค้นหา' ) ); ?>" aria-label="ค้นหา" id="headerSearchInput" class="tb4-header-search-input" aria-controls="searchResults" aria-expanded="false">
              <span class="kbd-hint" aria-hidden="true">CTRL K</span>
            </form>
          </div>

          <div class="header-right">
            <button class="header-icon-btn tb4-mobile-header-search-trigger" id="tb4MobileSearchTrigger" type="button" aria-label="เปิดค้นหา" aria-haspopup="dialog" aria-expanded="false" aria-controls="tb4MobileHeaderSearchPanel">
              <?php echo tb4hm_icon( 'search' ); ?>
            </button>

            <?php if ( is_user_logged_in() ) : ?>
              <?php tb4hm_render_app_grid_button(); ?>

              <div class="tb4-notification-wrap">
                <button class="header-icon-btn" id="tb4NotificationTrigger" type="button" aria-label="การแจ้งเตือน" title="การแจ้งเตือน" aria-haspopup="dialog" aria-expanded="false" aria-controls="tb4NotificationPanel">
                  <?php echo tb4hm_icon( 'bell' ); ?><span class="notif-dot" aria-hidden="true"></span>
                </button>
                <div id="tb4NotificationPanel" class="tb4-notification-panel" role="dialog" aria-label="รายการแจ้งเตือน" aria-hidden="true" hidden>
                  <div class="tb4-notification-head"><strong>การแจ้งเตือน</strong><span><?php echo esc_html( tb4hm_text( 'notification_badge', 'อัปเดตล่าสุด' ) ); ?></span></div>
                  <div class="tb4-notification-list">
                    <a href="<?php echo esc_url( current_user_can( 'edit_theme_options' ) ? admin_url( 'themes.php?page=thinkb4do-guide' ) : home_url( '/system-guide/' ) ); ?>" class="tb4-notification-item"><span class="tb4-notification-icon">🛠️</span><span><strong><?php echo esc_html( tb4hm_text( 'notification_1_title', 'คู่มือระบบ' ) ); ?></strong><small><?php echo esc_html( tb4hm_text( 'notification_1_body', 'ดูสถานะและรายละเอียดการพัฒนา' ) ); ?></small></span></a>
                    <a href="<?php echo esc_url( current_user_can( 'edit_theme_options' ) ? admin_url( 'customize.php?autofocus[section]=tb4_header_settings' ) : home_url( '/system-guide/' ) ); ?>" class="tb4-notification-item"><span class="tb4-notification-icon">📌</span><span><strong><?php echo esc_html( tb4hm_text( 'notification_2_title', 'ตั้งค่า Header' ) ); ?></strong><small><?php echo esc_html( tb4hm_text( 'notification_2_body', 'ปรับตำแหน่งและการแสดงผลของส่วนหัว' ) ); ?></small></span></a>
                    <a href="<?php echo esc_url( home_url( '/products/' ) ); ?>" class="tb4-notification-item"><span class="tb4-notification-icon">🧪</span><span><strong><?php echo esc_html( tb4hm_text( 'notification_3_title', 'ผลิตภัณฑ์ใหม่' ) ); ?></strong><small><?php echo esc_html( tb4hm_text( 'notification_3_body', 'ดูพื้นที่ผลิตภัณฑ์ของ Thinkb4do' ) ); ?></small></span></a>
                  </div>
                </div>
              </div>

              <div class="tb4-message-wrap">
                <button class="header-icon-btn" id="tb4MessageTrigger" aria-label="ข้อความ" title="<?php echo esc_attr( tb4hm_text( 'message_button_title', 'ข้อความ' ) ); ?>" type="button" aria-haspopup="dialog" aria-expanded="false" aria-controls="tb4MessagePanel">
                  <?php echo tb4hm_icon( 'chat' ); ?>
                </button>
                <div id="tb4MessagePanel" class="tb4-message-panel" role="dialog" aria-label="ข้อความ" aria-hidden="true" hidden>
                  <div class="tb4-message-head"><strong>ข้อความ</strong><span>อัปเดตข่าวสาร</span></div>
                  <div class="tb4-message-list">
                    <?php
                    $tb4_message_items = apply_filters( 'tb4_message_panel_items', [
                      [ home_url( '/system-guide/' ), '🧭', 'ดูสถานะระบบ', 'ไปยังหน้าคู่มือและสถานะการพัฒนาระบบ' ],
                    ] );
                    foreach ( $tb4_message_items as $tb4_message_item ) {
                        $tb4_message_item = array_pad( (array) $tb4_message_item, 4, '' );
                        echo '<a href="' . esc_url( $tb4_message_item[0] ) . '" class="tb4-message-item"><span class="tb4-message-icon">' . esc_html( $tb4_message_item[1] ) . '</span><span><strong>' . esc_html( $tb4_message_item[2] ) . '</strong><small>' . esc_html( $tb4_message_item[3] ) . '</small></span></a>';
                    }
                    ?>
                  </div>
                </div>
              </div>

              <div class="user-menu-wrap">
                <?php $current_user = wp_get_current_user(); ?>
                <?php
                $tb4_profile_label    = $current_user->display_name ? $current_user->display_name : $current_user->user_login;
                $tb4_profile_info     = apply_filters( 'tb4hm_profile_chip_info', 'ข้อมูลบัญชี', $current_user );
                ?>
                <button class="user-avatar-btn tb4-profile-chip" id="userMenuTrigger" type="button" aria-label="เมนูโปรไฟล์ของ <?php echo esc_attr( $tb4_profile_label ); ?>" title="เมนูโปรไฟล์" aria-haspopup="menu" aria-expanded="false" aria-controls="userMenuDropdown">
                  <?php echo tb4hm_get_user_avatar_markup( $current_user, 40 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </button>
                <div id="userMenuDropdown" class="tb4-user-dropdown" role="menu" aria-hidden="true" hidden>
                  <div class="tb4-user-dropdown-head">
                    <?php echo tb4hm_get_user_avatar_markup( $current_user, 40, 'tb4-user-dropdown-avatar' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <span class="tb4-user-dropdown-copy">
                      <strong><?php echo esc_html( $tb4_profile_label ); ?></strong>
                      <small><?php echo esc_html( $tb4_profile_info ); ?></small>
                    </span>
                  </div>
                  <?php
                  $menu_items = apply_filters( 'tb4_user_menu_items', [
                    [ home_url( '/dashboard/' ), 'ph-house', 'แดชบอร์ด' ],
                    [ home_url( '/products/my/' ), 'ph-package', 'ผลิตภัณฑ์ของฉัน' ],
                    [ home_url( '/affiliate/' ), 'ph-link', 'Affiliate' ],
                    [ admin_url(), 'ph-gear', 'การตั้งค่า' ],
                    [ wp_logout_url( home_url() ), 'ph-sign-out', 'ออกจากระบบ' ],
                  ] );
                  foreach ( $menu_items as $item ) {
                      $item = array_pad( (array) $item, 3, '' );
                      echo '<a href="' . esc_url( $item[0] ) . '" role="menuitem"><i class="ph ' . esc_attr( $item[1] ) . '" aria-hidden="true"></i>' . esc_html( $item[2] ) . '</a>';
                  }
                  ?>
                </div>
              </div>

            <?php else : ?>
              <?php tb4hm_render_app_grid_button(); ?>
              <button class="lang-btn" type="button" aria-label="ภาษา" aria-describedby="tb4-lang-note"><?php echo tb4hm_icon( 'globe' ); ?><span>TH</span><span id="tb4-lang-note" class="screen-reader-text">ระบบหลายภาษาพร้อมเชื่อมต่อในอนาคต</span><?php echo tb4hm_icon( 'caret' ); ?></button>
              <a href="<?php echo esc_url( wp_login_url() ); ?>" class="btn btn-secondary btn-sm"><?php echo tb4hm_icon( 'signin' ); ?> เข้าสู่ระบบ</a>
            <?php endif; ?>

            <button class="hamburger" id="mobileMenuToggle" type="button" aria-label="เมนู" aria-expanded="false" aria-controls="mobileMenuOverlay"><span></span><span></span><span></span></button>
          </div>
        </div>
      </div>
    </header>

    <?php tb4hm_render_app_grid_panel(); ?>

    <div id="tb4MobileHeaderSearchPanel" class="tb4-mobile-header-search-panel" role="dialog" aria-label="ค้นหาเว็บไซต์" aria-hidden="true" hidden>
      <form class="tb4-mobile-header-search-form tb4-header-search-form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" autocomplete="off">
        <label class="screen-reader-text" for="tb4MobileHeaderSearchInput">ค้นหาเว็บไซต์</label>
        <?php echo tb4hm_icon( 'search' ); ?>
        <input id="tb4MobileHeaderSearchInput" class="tb4-header-search-input" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php echo esc_attr( tb4hm_text( 'header_search_placeholder', 'ค้นหา' ) ); ?>" aria-label="ค้นหา">
        <button type="submit" class="tb4-mobile-header-search-submit" aria-label="ค้นหา"><?php echo tb4hm_icon( 'search' ); ?><span class="tb4hm-visually-hidden">ค้นหา</span></button>
        <button type="button" class="tb4-mobile-header-search-close" aria-label="ปิดค้นหา">×</button>
      </form>
    </div>

    <div id="mobileMenuOverlay" class="tb4-mobile-menu-overlay" role="dialog" aria-modal="true" aria-label="เมนูมือถือ" aria-hidden="true" hidden>
      <nav class="tb4-mobile-nav" aria-label="เมนูมือถือ">
        <?php tb4hm_render_nav( 'mobile' ); ?>
      </nav>
      <?php if ( is_user_logged_in() ) : ?>
        <?php $tb4_mobile_user = wp_get_current_user(); ?>
        <div class="tb4-mobile-profile-card">
          <?php echo tb4hm_get_user_avatar_markup( $tb4_mobile_user, 48, 'tb4-mobile-profile-avatar' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
          <span class="tb4-mobile-profile-copy">
            <strong><?php echo esc_html( $tb4_mobile_user->display_name ? $tb4_mobile_user->display_name : $tb4_mobile_user->user_login ); ?></strong>
            <small>โปรไฟล์และเมนูบัญชี</small>
          </span>
        </div>
        <div class="tb4-mobile-account-actions">
          <a href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>">แดชบอร์ด</a>
          <a href="<?php echo esc_url( home_url( '/products/my/' ) ); ?>">ผลิตภัณฑ์ของฉัน</a>
          <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>">ออกจากระบบ</a>
        </div>
      <?php endif; ?>
      <?php if ( ! is_user_logged_in() ) : ?>
      <div class="tb4-mobile-auth-actions">
        <a href="<?php echo esc_url( wp_login_url() ); ?>" class="btn btn-secondary">เข้าสู่ระบบ</a>
        <a href="<?php echo esc_url( wp_registration_url() ); ?>" class="btn btn-primary">ติดตามการพัฒนา</a>
      </div>
      <?php endif; ?>
    </div>

    <div id="searchResults" class="tb4-search-results" hidden role="listbox" aria-label="ผลการค้นหา"></div>
    <!-- /HEADER MENU PLUGIN -->
    <?php
    do_action( 'tb4_after_header' );
}
