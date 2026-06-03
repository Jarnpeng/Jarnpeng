<?php
/**
 * Plugin Name: Thinkb4do Community Members
 * Plugin URI: https://thinkb4do.com
 * Description: แยกปลั๊กอินหน้าสมาชิก Thinkb4do Community สำหรับโปรไฟล์ โพสต์ของฉัน คอมเมนต์ บันทึก และตั้งค่าสมาชิก.
 * Version: 4.2.283
 * Author: Thinkb4do
 * Text Domain: thinkb4do-community-members
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

defined( 'ABSPATH' ) || exit;

define( 'TB4CM_VERSION', '4.2.283' );
define( 'TB4CM_DIR', plugin_dir_path( __FILE__ ) );
define( 'TB4CM_URL', plugin_dir_url( __FILE__ ) );

require_once TB4CM_DIR . 'includes/layout-controller.php';

/**
 * Activation/deactivation.
 */
function tb4cm_activate() {
    update_option( 'tb4cm_version', TB4CM_VERSION, false );
    tb4cm_register_cpts();
    tb4cm_register_taxonomies();
    tb4cm_maybe_create_default_terms();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'tb4cm_activate' );

function tb4cm_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'tb4cm_deactivate' );

/**
 * Marker so themes/modules can detect this community system.
 */
function tb4cm_is_active() {
    return true;
}



/**
 * v4.2.220: Reference-based comment page system with wider card, full media, clean comment section, and one Back to Feed control.
 * - ขยายการ์ดและวิดีโอบนมือถือให้ใช้พื้นที่ใกล้ Instagram feed มากขึ้น
 * - แสดงประเภทและ Tag ที่ตั้งใหม่ใต้รายละเอียด ก่อนรูป/วิดีโอ
 * - ลดความรกของ action row และคงสีแบรนด์ Thinkb4do
 */

/**
 * v4.2.207: Video Cover Refresh + Inline Owner Tools.
 * - ดึงปกวิดีโอภายนอกใหม่จาก YouTube/Vimeo/oEmbed/Gutenberg/raw HTML ให้แม่นขึ้น
 * - แสดงรูปปกจริงเป็น <img> พร้อม JS fallback แทนการพึ่ง CSS background อย่างเดียว
 * - จัดปุ่มแก้ไข/ลบให้อยู่บรรทัดเดียวกับชื่อผู้ใช้บนการ์ดโพสต์
 */

/**
 * v4.2.202: UI/UX Essential Member Comment Clean. Remove more duplicate/non-essential UI, keep bottom nav removed, keep layout positions stable, and simplify comments/member area to essential parts only.
 */

/**
 * Member feature access helpers.
 * v4.2.83: prepare member-area tabs for future purchasable tools.
 * v4.2.84: standardize member composer and force one-line post footer/actions across feed, member-area, and single post pages.
 * v4.2.85: true feed-standard composer layout and hard one-line post footer cleanup across all community pages.
 * v4.2.86: member-area create post uses the same feed popup composer without member-specific layout overrides, safe between header and footer.
 * v4.2.87: clean low-contrast UI, remove visible category/type from post surfaces, keep only custom tags below description, and simplify single-post comment controls.
 * v4.2.88: move custom tags directly below post description, hard-lock one-line post actions, standardize feed/member composer popup, and fix footer normalizer scope.
 * v4.2.89: feed-style composer redline order with visible Type + New Tag fields, backend new-tag merge, and equal-height vertical Reel popup cards.
 * v4.2.90: hard reset composer DOM/style so the change is visible even when older member-area overrides or delayed JS were winning; Tag/Type/New Tag live in one strip under the text area.
 * v4.2.91: copy the feed popup proportions directly to member/single/community composer; compact header, feed-width panel, one compact redline strip, equal reel vertical card sizing.
 * v4.2.93: rebuild the composer modal markup from the feed-native popup structure instead of styling the old long composer.
 * v4.2.94: lock the composer popup to the feed header top, ignoring member hero/cover/content banners that previously pushed the modal downward.
 * v4.2.95: hard snap composer popup to the real feed header line using measured admin/header/status bars only, then apply inline important styles to prevent legacy modal centering.
 * v4.2.96: add Products/App tab to member area and connect it with Thinkb4do Products app store/payment/delivery bridge when available.
 * v4.2.97: rebuild the composer popup again as a feed-native modal and hard-snap it to the feed header area across Community, Member Area, and Single Post.
 * v4.2.98: add desktop left/right Next controls for Reels and create a safe fallback Reel viewer on pages that have Reel buttons but no viewer markup.
 * v4.2.101: performance hard cut, removes eager Reel rescue, scroll/wheel/touchmove mini-video watchers, document-wide observers, and heavy blur/filter effects.
 * v4.2.102: add lightweight left/right side Next bars to the Reel rail on desktop/mobile without restoring heavy observers.
 * v4.2.103: rebuild the composer popup controls across all pages, make tool icons clickable/keyboard-safe, compact the tool dock, and hard-limit the popup between header and footer.
 * v4.2.106: Smart Composer parity for member-area/community/single pages; move the modal to document body, keep it centered inside the green-header/footer safe area, and clean the action footer.
 * v4.2.107: Smart Composer All Pages, final safe-area rebuild for every creatable page; center inside the green-header/footer viewport, relocate the modal to body, and simplify the tool/action layout.
 * v4.2.108: Smart Composer True Global Safe; normalize every composer modal instance, append to body, measure only real top/footer bars, and force a wider safe panel across Community, Member Area, and Single Post.
 * v4.2.109: Composer Clean Single Controller; stop stacking old composer body classes, mark one canonical smart composer, and let one controller own position/size/tools on every creatable page.
 * v4.2.110: Smart Composer Stable Rebuild; one modal, one controller, center safe area on every creatable page, no flicker/reposition animation.
 * v4.2.111: Smart Composer Clean Rebuild Final; remove legacy composer layout triggers, center one modal inside the green-header/footer safe area on every creatable page, and stop typing-time reposition to prevent flicker.
 * v4.2.113: Performance All Devices; enqueue community assets only where needed, defer JS, throttle composer repaint work, lazy-load non-critical media, and add CSS containment for smoother feed/reel/member pages.
 * v4.2.117: Hard Cleanup + Split Assets; keep the public core CSS/JS small, load legacy assets asynchronously only where needed, and add low-motion/runtime cleanup guards.
 * v4.2.118: UI/UX De-duplicate; render shared composer/mobile nav once per page, add canonical UI registry, and collapse duplicate composer/action/popup surfaces without removing core features.
 * v4.2.119: Legacy UI Cleanup + Smooth Runtime; throttle duplicate cleanup, add idle-safe UI pruning, pause offscreen media, and reduce expensive effects for smoother cross-device interaction.
 * v4.2.120: Module Extraction Runtime; merge duplicated UI cleanup layers into one lightweight runtime, reduce HTTP/observer overhead, and keep canonical UI surfaces only.
 * v4.2.121: Remove Duplicate UI; physically remove obsolete cleanup assets, prune duplicate UI nodes safely, tighten observer scope, and prevent global action-row false positives.
 * v4.2.122: Deep Duplicate Removal; remove duplicated core-JS cleanup, keep one runtime controller, reduce observer/media noise, and stop stacking old UI/UX body classes.
 * v4.2.123: Legacy Source Prune; remove old disabled composer/listener controllers, stop loading legacy CSS by default, and let one lean runtime remove duplicate UI surfaces.
 * v4.2.124: Source-level UI removal; remove legacy CSS from package, lazy-load legacy JS only on demand, and keep one lean runtime/observer for duplicate UI cleanup.
 * v4.2.125: Hard Remove Duplicate UI; remove legacy CSS rollback path, narrow legacy JS triggers, stop loading legacy on composer open, and reduce observer to a micro cleanup pass.
 * v4.2.126: Ultra Lean Runtime; remove MutationObserver from the main runtime, handle lightbox/comment popup with micro UI, and reserve legacy JS for heavy/write actions only.
 * v4.2.151: Complete Beauty Polish; refine all visible community surfaces with lighter social-network UI, safer spacing, compact mobile controls, and no new libraries.
 * v4.2.152: Complete Refined Lite; final all-surface beauty pass, lighter paint tokens, cleaner social layout rhythm, improved mobile density, and no new libraries.
 * v4.2.144: Social Network Light Layout; refine the community feed into a lightweight social-network layout with centered feed, cleaner side panels, compact actions, and mobile-first spacing.
 * v4.2.136: Flow Social UI/UX Rebuild; replace the previous active skin with a centered social-feed layout, cleaner composer/post actions, and mobile app-safe surfaces without adding JS.
 * v4.2.114: Performance Anti-Lag; skip heavy JS when no community surface exists, add slow-device anti-jank class, reduce smooth scroll on low-power devices, and async-load icon font to reduce render blocking.
 * v4.2.116: Fluid UI/UX; add a small final UI layer, runtime DOM de-dupe, lighter action rows/composer/member layout, cached member stats, and extra anti-lag guards for smoother cross-device use.
 * v4.2.112: Composer Loading Stop + Latest Jump; show soft loading dots, turn the submit button into a Stop icon while posting, allow aborting the AJAX request, and guide the user back to the newest post after completion.
 * v4.2.148: Beauty Lite UI/UX; softer social-network visuals, lighter card depth, compact Reels/sidebar, mobile spacing cleanup, and no extra heavy libraries.
 * v4.2.149: Airy Beauty Lite; clearer visual hierarchy, lighter shadows, cleaner composer/post/sidebar spacing, and CSS-only social polish.
 * v4.2.150: Complete Beauty Lite + Smart Back; all-surface lightweight UI polish and a small internal back button for page changes.
 * v4.2.158: Proportion Social Lock; divide desktop into clear left/sidebar, centered stream, and right/sidebar proportions while staying lightweight.
 * v4.2.159: Full-Bleed Social Proportion; break out of narrow theme content wrappers and distribute left/sidebar, center stream, and right/sidebar across the real viewport.
 * v4.2.164: Module Layout Lite; split Community page into Left Sidebar, Center Feed, and Right Sidebar modules inside one plugin, remove stacked patch loading, and control social proportions from one lightweight layout stylesheet.
 * v4.2.165: Feed Size Lock Lite; prevent video/iframe/Reels from escaping the center feed.
 * v4.2.166: Aligned Social UI; rebuild left/right placement to align with the site header while keeping the feed fixed and lightweight.
 * v4.2.169: Header Edge Align Lite; align left/right menu edges to the main header while preserving feed sizing and transparent sidebars.
 * v4.2.170: Sidebar Clear Named Menu; remove remaining left/right sidebar card backgrounds and force left menu labels to show horizontally.
 * v4.2.171: Hard Sidebar Fix; remove sidebar card classes at template level, load a final scoped stylesheet, and force left menu labels to render horizontally without moving the feed.
 * v4.2.172: Position Light UI; replace the stacked late patch chain with one final lightweight alignment layer, keep feed size locked, align side modules with the header, and clean icon/menu rendering.
 * v4.2.178: Feed + Reels Standard Lite; keep left/right positions frozen, polish the center feed as a social stream, and standardize Reels rail/viewer with lightweight CSS only.
 * v4.2.179: Psychology Social UI Lite; reduce cognitive load, set center-feed visual priority, keep sidebars peripheral, improve action affordance, and remove stacked visual layers from the active render path.
 * v4.2.180: Sidebar Edge Lock; keep left/right sidebars aligned to header edges and prevent returning to the old centered/sidebar-card position.
 * Core tabs stay unlocked; future modules can register locked tabs through filters.
 */
/**
 * v4.2.237: Member Curated Feed. Show selected own posts inside member profile using feed-standard post cards, clearer Thai labels, and scoped modern member-feed styling.
 */
function tb4cm_get_member_area_extra_tabs( $user_id = 0 ) {
    $user_id = $user_id ? absint( $user_id ) : get_current_user_id();
    $tabs    = apply_filters( 'tb4c_member_area_extra_tabs', [], $user_id );

    if ( ! is_array( $tabs ) ) {
        return [];
    }

    $normalized = [];
    foreach ( $tabs as $key => $tab ) {
        $feature_key = sanitize_key( $key );
        if ( '' === $feature_key || ! is_array( $tab ) ) {
            continue;
        }
        $normalized[ $feature_key ] = [
            'label'        => isset( $tab['label'] ) ? wp_strip_all_tags( (string) $tab['label'] ) : __( 'ฟีเจอร์เสริม', 'thinkb4do-community' ),
            'icon'         => isset( $tab['icon'] ) ? sanitize_html_class( $tab['icon'] ) : 'ph-lock-key',
            'fallback'     => isset( $tab['fallback'] ) ? wp_strip_all_tags( (string) $tab['fallback'] ) : '🔒',
            'description'  => isset( $tab['description'] ) ? wp_strip_all_tags( (string) $tab['description'] ) : __( 'ฟีเจอร์นี้เป็นส่วนเสริมสำหรับสมาชิก', 'thinkb4do-community' ),
            'purchasable'  => array_key_exists( 'purchasable', $tab ) ? (bool) $tab['purchasable'] : true,
            'default_open' => ! empty( $tab['default_open'] ),
            'purchase_url' => isset( $tab['purchase_url'] ) ? esc_url_raw( $tab['purchase_url'] ) : '',
        ];
    }

    return $normalized;
}

function tb4cm_member_area_feature_is_unlocked( $feature_key, $user_id = 0 ) {
    $feature_key = sanitize_key( $feature_key );
    $user_id     = $user_id ? absint( $user_id ) : get_current_user_id();

    $core_tabs = [ 'overview', 'products', 'posts', 'comments', 'saved', 'following', 'groups', 'market', 'chat', 'settings' ];
    if ( in_array( $feature_key, $core_tabs, true ) ) {
        return (bool) apply_filters( 'tb4cm_member_area_feature_is_unlocked', true, $feature_key, $user_id );
    }

    $extra_tabs   = tb4cm_get_member_area_extra_tabs( $user_id );
    $default_open = ! empty( $extra_tabs[ $feature_key ]['default_open'] );
    $meta_value   = get_user_meta( $user_id, 'tb4c_member_feature_' . $feature_key . '_unlocked', true );

    if ( '' === $meta_value ) {
        $unlocked = $default_open;
    } else {
        $unlocked = in_array( strtolower( (string) $meta_value ), [ '1', 'yes', 'true', 'on', 'unlocked' ], true );
    }

    return (bool) apply_filters( 'tb4cm_member_area_feature_is_unlocked', $unlocked, $feature_key, $user_id );
}

function tb4cm_member_area_feature_purchase_url( $feature_key, $user_id = 0 ) {
    $feature_key = sanitize_key( $feature_key );
    $user_id     = $user_id ? absint( $user_id ) : get_current_user_id();
    $extra_tabs  = tb4cm_get_member_area_extra_tabs( $user_id );
    $url         = isset( $extra_tabs[ $feature_key ]['purchase_url'] ) ? $extra_tabs[ $feature_key ]['purchase_url'] : '';

    if ( ! $url ) {
        $url = apply_filters( 'tb4c_member_area_default_purchase_url', home_url( '/products/' ), $feature_key, $user_id );
    }

    return esc_url_raw( apply_filters( 'tb4cm_member_area_feature_purchase_url', $url, $feature_key, $user_id ) );
}


/**
 * Render a front-end icon with an emoji/text fallback when external icon fonts are unavailable.
 */
function tb4cm_icon_markup( $icon_class, $fallback = '•', $extra_class = '' ) {
    $icon_class = sanitize_html_class( $icon_class );
    $extra_class = trim( preg_replace( '/[^a-zA-Z0-9_\-\s]/', '', (string) $extra_class ) );
    $fallback = wp_strip_all_tags( (string) $fallback );

    return sprintf(
        '<span class="tb4c-ui-icon %1$s" aria-hidden="true"><i class="ph %2$s"></i><span class="tb4c-icon-fallback" hidden>%3$s</span></span>',
        esc_attr( $extra_class ),
        esc_attr( $icon_class ),
        esc_html( $fallback )
    );
}

/**
 * Register Community custom post type. Member public pages were removed in v4.2.74; member-area is the single member workspace.
 */
function tb4cm_register_cpts() {
    register_post_type( 'tb4_community_post', [
        'labels' => [
            'name'               => __( 'โพสต์ชุมชน', 'thinkb4do-community' ),
            'singular_name'      => __( 'โพสต์ชุมชน', 'thinkb4do-community' ),
            'add_new_item'       => __( 'เพิ่มโพสต์ชุมชน', 'thinkb4do-community' ),
            'edit_item'          => __( 'แก้ไขโพสต์ชุมชน', 'thinkb4do-community' ),
            'new_item'           => __( 'โพสต์ชุมชนใหม่', 'thinkb4do-community' ),
            'view_item'          => __( 'ดูโพสต์ชุมชน', 'thinkb4do-community' ),
            'search_items'       => __( 'ค้นหาโพสต์ชุมชน', 'thinkb4do-community' ),
            'not_found'          => __( 'ยังไม่มีโพสต์ชุมชน', 'thinkb4do-community' ),
            'not_found_in_trash' => __( 'ไม่พบโพสต์ชุมชนในถังขยะ', 'thinkb4do-community' ),
        ],
        'public'              => true,
        'has_archive'         => true,
        'show_in_rest'        => true,
        'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'author', 'custom-fields' ],
        'rewrite'             => [ 'slug' => 'community-post' ],
        'menu_icon'           => 'dashicons-groups',
        'menu_position'       => 26,
        'capability_type'     => 'post',
        'map_meta_cap'        => true,
    ] );
}
add_action( 'init', 'tb4cm_register_cpts' );

function tb4cm_register_taxonomies() {
    register_taxonomy( 'tb4_community_topic', [ 'tb4_community_post' ], [
        'labels' => [
            'name'          => __( 'หัวข้อชุมชน', 'thinkb4do-community' ),
            'singular_name' => __( 'หัวข้อชุมชน', 'thinkb4do-community' ),
            'add_new_item'  => __( 'เพิ่มหัวข้อชุมชน', 'thinkb4do-community' ),
            'edit_item'     => __( 'แก้ไขหัวข้อชุมชน', 'thinkb4do-community' ),
        ],
        'public'       => true,
        'hierarchical' => false,
        'show_in_rest' => true,
        'rewrite'      => [ 'slug' => 'community-topic' ],
    ] );

    register_taxonomy( 'tb4_community_tag', [ 'tb4_community_post' ], [
        'labels' => [
            'name'          => __( 'แท็กชุมชน', 'thinkb4do-community' ),
            'singular_name' => __( 'แท็กชุมชน', 'thinkb4do-community' ),
            'add_new_item'  => __( 'เพิ่มแท็กชุมชน', 'thinkb4do-community' ),
            'edit_item'     => __( 'แก้ไขแท็กชุมชน', 'thinkb4do-community' ),
        ],
        'public'       => true,
        'hierarchical' => false,
        'show_in_rest' => true,
        'rewrite'      => [ 'slug' => 'community-tag' ],
    ] );
}
add_action( 'init', 'tb4cm_register_taxonomies' );

function tb4cm_maybe_create_default_terms() {
    $terms = [
        'discussion' => 'พูดคุย',
        'question'   => 'ถาม-ตอบ',
        'idea'       => 'ไอเดีย',
        'project'    => 'โปรเจกต์',
        'event'      => 'กิจกรรม',
        'creator'    => 'Creator',
    ];

    foreach ( $terms as $slug => $name ) {
        if ( ! term_exists( $slug, 'tb4_community_topic' ) ) {
            wp_insert_term( $name, 'tb4_community_topic', [ 'slug' => $slug ] );
        }
    }
}
add_action( 'init', 'tb4cm_maybe_create_default_terms', 20 );

/**
 * Register metadata.
 */
function tb4cm_register_meta() {
    $community_meta = [
        'tb4_post_type'          => 'string',
        'tb4_post_visibility'    => 'string',
        'tb4_post_review_status' => 'string',
        'tb4_post_quality_score' => 'integer',
        'tb4_like_count'         => 'integer',
        'tb4_bookmark_count'     => 'integer',
        'tb4_view_count'         => 'integer',
        'tb4_is_pinned'          => 'boolean',
    ];

    foreach ( $community_meta as $key => $type ) {
        register_post_meta( 'tb4_community_post', $key, [
            'single'            => true,
            'type'              => $type,
            'show_in_rest'      => true,
            'sanitize_callback' => 'tb4cm_sanitize_community_meta',
            'auth_callback'     => static function() {
                return current_user_can( 'edit_posts' );
            },
        ] );
    }

    register_post_meta( 'tb4_community_post', 'tb4_media_url', [
        'single'            => true,
        'type'              => 'string',
        'show_in_rest'      => true,
        'sanitize_callback' => 'tb4cm_sanitize_post_media_url',
        'auth_callback'     => static function() {
            return current_user_can( 'edit_posts' );
        },
    ] );

    register_post_meta( 'tb4_community_post', 'tb4_media_attachment_id', [
        'single'            => true,
        'type'              => 'integer',
        'show_in_rest'      => true,
        'sanitize_callback' => 'absint',
        'auth_callback'     => static function() {
            return current_user_can( 'edit_posts' );
        },
    ] );
    register_post_meta( 'tb4_community_post', 'tb4_media_album', [
        'single'            => true,
        'type'              => 'string',
        'show_in_rest'      => true,
        'sanitize_callback' => 'tb4cm_sanitize_media_album_meta',
        'auth_callback'     => static function() {
            return current_user_can( 'edit_posts' );
        },
    ] );
}
add_action( 'init', 'tb4cm_register_meta' );

function tb4cm_sanitize_member_meta( $value, $meta_key = '' ) {
    if ( in_array( $meta_key, [ 'tb4_quality_score', 'tb4_member_id' ], true ) ) {
        return absint( $value );
    }
    if ( 'tb4_aff_commission' === $meta_key ) {
        return is_numeric( $value ) ? (float) $value : 0;
    }
    if ( 'tb4_contact_url' === $meta_key ) {
        return esc_url_raw( $value );
    }
    return sanitize_text_field( $value );
}

function tb4cm_sanitize_community_meta( $value, $meta_key = '' ) {
    if ( in_array( $meta_key, [ 'tb4_post_quality_score', 'tb4_like_count', 'tb4_bookmark_count', 'tb4_view_count' ], true ) ) {
        return max( 0, absint( $value ) );
    }
    if ( 'tb4_is_pinned' === $meta_key ) {
        return (bool) $value;
    }
    return sanitize_key( $value );
}

function tb4cm_sanitize_post_media_url( $value ) {
    $url = trim( (string) $value );
    if ( '' === $url ) {
        return '';
    }

    $media = tb4cm_validate_trusted_media_url( $url );
    return is_wp_error( $media ) ? '' : esc_url_raw( $media['url'] );
}

/**
 * Templates.
 */
function tb4cm_add_page_templates( $templates ) {
    $templates['tb4-community-template.php'] = __( 'Thinkb4do Community', 'thinkb4do-community' );
    return $templates;
}
add_filter( 'theme_page_templates', 'tb4cm_add_page_templates' );

function tb4cm_template_include( $template ) {
    return $template;
}
add_filter( 'template_include', 'tb4cm_template_include', 20 );

/**
 * v4.2.74: keep old member/member-page URLs from creating a second member surface.
 */
function tb4cm_redirect_legacy_member_pages() {
    if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
        return;
    }
    if ( is_page( 'member-page' ) || is_singular( 'tb4_member_page' ) || is_post_type_archive( 'tb4_member_page' ) ) {
        wp_safe_redirect( tb4cm_get_member_area_url(), 301 );
        exit;
    }
}
add_action( 'template_redirect', 'tb4cm_redirect_legacy_member_pages', 1 );


/**
 * Assets.
 */
function tb4cm_is_frontend_context() {
    return ! is_admin();
}

function tb4cm_is_thinkb4do_theme_core() {
    $theme = wp_get_theme();
    $name  = $theme ? strtolower( (string) $theme->get( 'Name' ) ) : '';
    $text_domain = $theme ? strtolower( (string) $theme->get( 'TextDomain' ) ) : '';

    return function_exists( 'tb4_get_platform_menu_items' ) || 'thinkb4do' === $text_domain || false !== strpos( $name, 'thinkb4do' );
}


function tb4cm_should_enqueue_assets() {
    if ( ! tb4cm_is_frontend_context() || is_feed() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
        return false;
    }

    if ( is_front_page() || is_page( [ 'community', 'member-area' ] ) || is_singular( 'tb4_community_post' ) || is_post_type_archive( 'tb4_community_post' ) ) {
        return true;
    }

    $post = get_post();
    if ( $post instanceof WP_Post ) {
        $content = (string) $post->post_content;
        $shortcodes = [
            'thinkb4do_community',
            'thinkb4do_member_area',
            'thinkb4do_community_composer',
            'thinkb4do_home_menu_feed',
            'thinkb4do_home_discovery_feed',
        ];
        foreach ( $shortcodes as $shortcode ) {
            if ( has_shortcode( $content, $shortcode ) ) {
                return true;
            }
        }
        if ( function_exists( 'has_block' ) && ( has_block( 'thinkb4do/community', $post ) || has_block( 'thinkb4do/member-area', $post ) ) ) {
            return true;
        }
        if ( false !== strpos( $content, 'tb4c-shell' ) || false !== strpos( $content, 'tb4c-home-menu-feed' ) || false !== strpos( $content, 'tb4-home-feed-main' ) ) {
            return true;
        }
    }

    return (bool) apply_filters( 'tb4cm_should_enqueue_assets', false );
}


function tb4cm_get_asset_context_42117() {
    $post = get_post();
    $content = $post instanceof WP_Post ? (string) $post->post_content : '';
    $has_community_shortcode = $content && (
        has_shortcode( $content, 'thinkb4do_community' ) ||
        has_shortcode( $content, 'thinkb4do_member_area' ) ||
        has_shortcode( $content, 'thinkb4do_community_composer' )
    );
    $has_home_shortcode = $content && (
        has_shortcode( $content, 'thinkb4do_home_menu_feed' ) ||
        has_shortcode( $content, 'thinkb4do_home_discovery_feed' )
    );
    $has_community_markup = $content && (
        false !== strpos( $content, 'tb4c-shell' ) ||
        false !== strpos( $content, 'tb4c-composer' ) ||
        false !== strpos( $content, 'tb4c-member-area' ) ||
        false !== strpos( $content, 'tb4-home-feed-main' )
    );
    $has_community_block = function_exists( 'has_block' ) && $post instanceof WP_Post && (
        has_block( 'thinkb4do/community', $post ) ||
        has_block( 'thinkb4do/member-area', $post )
    );

    $is_community = is_page( 'community' ) || is_post_type_archive( 'tb4_community_post' ) || $has_community_shortcode || $has_community_block;
    $is_member    = is_page( 'member-area' );
    $is_single    = is_singular( 'tb4_community_post' );
    $is_home      = is_front_page() || $has_home_shortcode;

    $context = [
        'surface'      => 'generic',
        'core_css'     => true,
        'critical_css' => true,
        // v4.2.126: legacy CSS rollback path is removed. Core + runtime CSS owns the canonical UI only.
        'legacy_css'   => false,
        'core_js'      => true,
        // v4.2.126: legacy JS stays lazy and is triggered only by heavy/write actions/submit, not by opening the composer or read-only popups.
        'legacy_js'    => false,
        'legacy_js_lazy' => true,
        'fluid_ui'     => false,
        'icons'        => true,
    ];

    if ( $is_member ) {
        $context['surface'] = 'member';
    } elseif ( $is_single ) {
        $context['surface'] = 'single';
    } elseif ( $is_community ) {
        $context['surface'] = 'community';
    } elseif ( $is_home ) {
        $context['surface'] = 'home';
        // v4.2.121: keep the public homepage light. Load the legacy visual/interaction bundle only when the page explicitly contains community/composer surfaces.
        $home_needs_legacy = (bool) ( $has_community_shortcode || $has_community_block || $has_community_markup || false !== strpos( $content, 'data-tb4c-composer' ) );
        // v4.2.124: keep homepage visual payload lean; legacy JS is lazy-loaded only on interaction.
        $context['legacy_css'] = false;
        $context['legacy_js']  = false;
        $context['legacy_js_lazy'] = $home_needs_legacy;
    } elseif ( $has_community_markup ) {
        $context['surface'] = 'embedded';
    }

    // v4.2.126: no legacy CSS rollback branch. Duplicate visual layers stay removed from the source package.

    $context = (array) apply_filters( 'tb4c_asset_context_42117', $context );
    $context = (array) apply_filters( 'tb4c_asset_context_42122', $context );
    $context = (array) apply_filters( 'tb4c_asset_context_42123', $context );
    $context = (array) apply_filters( 'tb4c_asset_context_42124', $context );
    return (array) apply_filters( 'tb4c_asset_context_42126', (array) apply_filters( 'tb4c_asset_context_42125', $context ) );
}

function tb4cm_enqueue_assets() {
    if ( ! tb4cm_should_enqueue_assets() ) {
        return;
    }

    $asset_context = tb4cm_get_asset_context_42117();
    $surface_class = ! empty( $asset_context['surface'] ) ? sanitize_html_class( 'tb4c-surface-' . $asset_context['surface'] ) : 'tb4c-surface-generic';

    $style_deps = [];
    foreach ( [ 'thinkb4do-modern-refresh-v231', 'thinkb4do-smart-sidebar-v222', 'thinkb4do-layout-support-v243', 'thinkb4do-universal-platform-v240', 'thinkb4do-style' ] as $handle ) {
        if ( wp_style_is( $handle, 'registered' ) || wp_style_is( $handle, 'enqueued' ) ) {
            $style_deps[] = $handle;
        }
    }

    $tb4c_css_file          = TB4CM_DIR . 'assets/community.css';
    $tb4c_critical_css_file = TB4CM_DIR . 'assets/community-critical.css';
    $tb4c_js_file           = TB4CM_DIR . 'assets/community.js';
    $tb4c_legacy_js_file    = TB4CM_DIR . 'assets/community-legacy.js';
    $tb4c_runtime_css_file  = TB4CM_DIR . 'assets/community-runtime.css';
    $tb4c_runtime_js_file   = TB4CM_DIR . 'assets/community-runtime.js';

    $tb4c_css_ver           = TB4CM_VERSION . '-' . ( file_exists( $tb4c_css_file ) ? filemtime( $tb4c_css_file ) : time() );
    $tb4c_critical_css_ver  = TB4CM_VERSION . '-' . ( file_exists( $tb4c_critical_css_file ) ? filemtime( $tb4c_critical_css_file ) : time() );
    $tb4c_js_ver            = TB4CM_VERSION . '-' . ( file_exists( $tb4c_js_file ) ? filemtime( $tb4c_js_file ) : time() );
    $tb4c_legacy_js_ver     = TB4CM_VERSION . '-' . ( file_exists( $tb4c_legacy_js_file ) ? filemtime( $tb4c_legacy_js_file ) : time() );
    $tb4c_runtime_css_ver   = TB4CM_VERSION . '-' . ( file_exists( $tb4c_runtime_css_file ) ? filemtime( $tb4c_runtime_css_file ) : time() );
    $tb4c_runtime_js_ver    = TB4CM_VERSION . '-' . ( file_exists( $tb4c_runtime_js_file ) ? filemtime( $tb4c_runtime_js_file ) : time() );

    if ( ! empty( $asset_context['critical_css'] ) ) {
        wp_enqueue_style(
            'thinkb4do-community-critical',
            TB4CM_URL . 'assets/community-critical.css',
            $style_deps,
            $tb4c_critical_css_ver
        );
    }

    wp_enqueue_style(
        'thinkb4do-community',
        TB4CM_URL . 'assets/community.css',
        ! empty( $asset_context['critical_css'] ) ? [ 'thinkb4do-community-critical' ] : $style_deps,
        $tb4c_css_ver
    );

    // v4.2.126: duplicate legacy CSS layer removed completely; runtime CSS depends only on the core stylesheet.

    // v4.2.126: one ultra-lean runtime CSS remains; duplicate cleanup CSS files and legacy visual paths are no longer used.
    wp_enqueue_style(
        'thinkb4do-community-runtime',
        TB4CM_URL . 'assets/community-runtime.css',
        [ 'thinkb4do-community' ],
        $tb4c_runtime_css_ver
    );

    // v4.2.207: Video Cover Refresh — stronger outside video thumbnail extraction plus inline edit/delete owner tools.
    // Older stacked patch files remain in the package only for rollback/reference, but are not enqueued.
    $tb4c_clean_reset_css_file = TB4CM_DIR . 'assets/community-clean-ui-reset.css';
    $tb4c_clean_reset_css_ver  = TB4CM_VERSION . '-' . ( file_exists( $tb4c_clean_reset_css_file ) ? filemtime( $tb4c_clean_reset_css_file ) : time() );
    if ( file_exists( $tb4c_clean_reset_css_file ) ) {
        wp_enqueue_style(
            'thinkb4do-community-clean-ui-reset',
            TB4CM_URL . 'assets/community-clean-ui-reset.css',
            [ 'thinkb4do-community-runtime' ],
            $tb4c_clean_reset_css_ver
        );
    }

    // v4.2.241: Member Area responsive standard layer for desktop/tablet/phone.
    $tb4c_member_responsive_css_file = TB4CM_DIR . 'assets/member-responsive-standard.css';
    $tb4c_member_responsive_css_ver  = TB4CM_VERSION . '-' . ( file_exists( $tb4c_member_responsive_css_file ) ? filemtime( $tb4c_member_responsive_css_file ) : time() );
    if ( file_exists( $tb4c_member_responsive_css_file ) ) {
        wp_enqueue_style(
            'thinkb4do-community-member-responsive-standard',
            TB4CM_URL . 'assets/member-responsive-standard.css',
            [ file_exists( $tb4c_clean_reset_css_file ) ? 'thinkb4do-community-clean-ui-reset' : 'thinkb4do-community-runtime' ],
            $tb4c_member_responsive_css_ver
        );
    }

    if ( ! empty( $asset_context['icons'] ) && ! wp_style_is( 'tb4-icons', 'enqueued' ) ) {
        wp_enqueue_style(
            'tb4-icons',
            'https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css',
            [],
            '2.1.1'
        );
    }

    wp_enqueue_script(
        'thinkb4do-community',
        TB4CM_URL . 'assets/community.js',
        [],
        $tb4c_js_ver,
        true
    );

    $runtime_deps = [ 'thinkb4do-community' ];
    // v4.2.126: do not enqueue legacy JS up front unless explicitly forced. Runtime lazy-loads it only on heavy/write actions/submit.
    if ( ! empty( $asset_context['legacy_js'] ) && file_exists( $tb4c_legacy_js_file ) ) {
        wp_enqueue_script(
            'thinkb4do-community-legacy',
            TB4CM_URL . 'assets/community-legacy.js',
            [ 'thinkb4do-community' ],
            $tb4c_legacy_js_ver,
            true
        );
        $runtime_deps[] = 'thinkb4do-community-legacy';
    }

    // v4.2.126: one ultra-lean runtime controller owns duplicate removal and legacy-on-demand loading.
    wp_enqueue_script(
        'thinkb4do-community-runtime',
        TB4CM_URL . 'assets/community-runtime.js',
        $runtime_deps,
        $tb4c_runtime_js_ver,
        true
    );

    if ( function_exists( 'wp_script_add_data' ) ) {
        foreach ( [ 'thinkb4do-community', 'thinkb4do-community-legacy', 'thinkb4do-community-runtime' ] as $script_handle ) {
            wp_script_add_data( $script_handle, 'strategy', 'defer' );
        }
    }

    $tb4c_config = [
        'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
        'nonce'         => wp_create_nonce( 'tb4c_nonce' ),
        'isLoggedIn'    => is_user_logged_in(),
        'currentUserId'  => get_current_user_id(),
        'loginUrl'      => wp_login_url( tb4cm_get_current_url() ),
        'registerUrl'   => wp_registration_url(),
        'communityUrl'  => home_url( '/community/' ),
        'version'       => TB4CM_VERSION,
        'assetContext'  => $asset_context,
        'legacyScriptUrl' => TB4CM_URL . 'assets/community-legacy.js',
        'legacyScriptVersion' => $tb4c_legacy_js_ver,
        'legacyJsLazy' => ! empty( $asset_context['legacy_js_lazy'] ) && file_exists( $tb4c_legacy_js_file ),
        'messages'      => [
            'needLogin' => __( 'กรุณาเข้าสู่ระบบก่อนใช้งานส่วนนี้', 'thinkb4do-community' ),
            'saving'    => __( 'กำลังส่งข้อมูล...', 'thinkb4do-community' ),
            'saved'     => __( 'ส่งข้อมูลสำเร็จ', 'thinkb4do-community' ),
            'failed'    => __( 'เกิดข้อผิดพลาด กรุณาลองใหม่', 'thinkb4do-community' ),
            'follow'    => __( 'ติดตาม', 'thinkb4do-community' ),
            'following' => __( 'กำลังติดตาม', 'thinkb4do-community' ),
        ],
    ];
    wp_localize_script( 'thinkb4do-community', 'tb4cCommunity', $tb4c_config );

    wp_add_inline_style( 'thinkb4do-community', 'html.tb4c-v42117-hard-cleanup body{--tb4c-asset-surface:' . esc_attr( $surface_class ) . '}html.tb4c-v430-hardpatch-loaded body .tb4c-post-video-slot.tb4c-video-mini-player,html.tb4c-v430-hardpatch-loaded body [data-tb4c-video-slot].tb4c-video-mini-player{position:fixed!important;z-index:2147483646!important;right:18px!important;bottom:18px!important;width:min(390px,calc(100vw - 32px))!important;display:flex!important;flex-direction:column!important;overflow:hidden!important;border-radius:22px!important;background:#050505!important;box-shadow:0 18px 54px rgba(0,0,0,.30)!important;opacity:1!important;transform:translate3d(0,0,0)!important;pointer-events:auto!important}html.tb4c-v430-hardpatch-loaded body .tb4c-post-video-slot.tb4c-video-mini-player video,html.tb4c-v430-hardpatch-loaded body [data-tb4c-video-slot].tb4c-video-mini-player video{width:100%!important;height:auto!important;max-height:64vh!important;object-fit:contain!important;background:#000!important}html.tb4c-v430-hardpatch-loaded body .tb4c-post-video-slot:not(.tb4c-video-mini-player) .tb4c-mini-video-bar,html.tb4c-v430-hardpatch-loaded body [data-tb4c-video-slot]:not(.tb4c-video-mini-player) .tb4c-mini-video-bar{display:none!important}@media(max-width:720px){html.tb4c-v430-hardpatch-loaded body .tb4c-post-video-slot.tb4c-video-mini-player,html.tb4c-v430-hardpatch-loaded body [data-tb4c-video-slot].tb4c-video-mini-player{left:12px!important;right:12px!important;bottom:92px!important;width:auto!important}}' );
    wp_add_inline_script( 'thinkb4do-community', 'document.documentElement.classList.add("tb4c-v430-inline-loaded","tb4c-v42126-hard-remove-ui","tb4c-v42126-ultra-runtime","tb4c-v42188-complete-decoration");', 'after' );
}
add_action( 'wp_enqueue_scripts', 'tb4cm_enqueue_assets', 20 );

/**
 * v4.2.243: Clean Member Area - remove duplicate overview blocks and force one-column clean layout.
 * ใช้แก้กรณีไฟล์ CSS จากธีม/ปลั๊กอินเดิมโหลดทีหลังแล้วทับหน้าสมาชิก ทำให้ผู้ใช้เห็นว่าไม่เปลี่ยน.
 */
function tb4cm_print_member_area_hard_override_css_v4243() {
    if ( ! function_exists( 'tb4cm_is_member_area_surface_v4242' ) || ! tb4cm_is_member_area_surface_v4242() ) {
        return;
    }
    ?>
<style id="thinkb4do-community-member-hard-override-v4243">
:root{
  --tb4m-green:#1E6B45;
  --tb4m-green-2:#0f7a45;
  --tb4m-orange:#F97316;
  --tb4m-black:#111111;
  --tb4m-muted:#667085;
  --tb4m-line:rgba(17,17,17,.085);
  --tb4m-card:#ffffff;
  --tb4m-soft:#f5faf7;
  --tb4m-radius:28px;
}
body .tb4c-member-responsive-standard-page,
body .tb4c-member-area-page{
  width:100% !important;
  max-width:none !important;
  min-height:100vh !important;
  margin:0 !important;
  padding:clamp(16px,2.2vw,32px) !important;
  overflow-x:hidden !important;
  background:
    radial-gradient(circle at 85% 5%, rgba(30,107,69,.18), transparent 32%),
    linear-gradient(180deg,#ffffff 0%,#f6fbf8 42%,#ffffff 100%) !important;
  font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",Arial,"Noto Sans Thai","Kanit",sans-serif !important;
  color:var(--tb4m-black) !important;
  box-sizing:border-box !important;
}
body .tb4c-member-responsive-standard-page *,
body .tb4c-member-area-page *{box-sizing:border-box !important;}
body .tb4c-member-area-page .tb4c-container,
body .tb4c-member-responsive-standard-page .tb4c-container,
body .tb4c-member-area-container{
  width:min(100%,1180px) !important;
  max-width:1180px !important;
  margin:0 auto !important;
  padding:0 !important;
}
body .tb4c-member-area-page .tb4c-reference-page-head{
  display:flex !important;
  align-items:flex-end !important;
  justify-content:space-between !important;
  gap:18px !important;
  margin:0 0 18px !important;
  padding:0 2px !important;
  background:transparent !important;
  border:0 !important;
  box-shadow:none !important;
}
body .tb4c-member-area-page .tb4c-reference-title h1{
  margin:0 !important;
  font-size:clamp(30px,4.3vw,54px) !important;
  line-height:1.06 !important;
  font-weight:900 !important;
  letter-spacing:-.04em !important;
  color:#101828 !important;
}
body .tb4c-member-area-page .tb4c-reference-title p{
  margin:8px 0 0 !important;
  color:#667085 !important;
  font-size:clamp(14px,1.45vw,18px) !important;
  line-height:1.65 !important;
}
body .tb4c-member-area-page .tb4c-member-area-hero{
  position:relative !important;
  display:grid !important;
  grid-template-columns:minmax(260px,360px) minmax(0,1fr) !important;
  gap:22px !important;
  margin:0 0 22px !important;
  padding:clamp(18px,2.5vw,30px) !important;
  border:1px solid var(--tb4m-line) !important;
  border-radius:32px !important;
  background:rgba(255,255,255,.88) !important;
  box-shadow:0 22px 70px rgba(16,24,40,.10) !important;
  overflow:hidden !important;
  isolation:isolate !important;
}
body .tb4c-member-area-page .tb4c-member-area-hero-bg{
  grid-column:1/-1 !important;
  height:clamp(170px,21vw,260px) !important;
  margin:calc(clamp(18px,2.5vw,30px) * -1) calc(clamp(18px,2.5vw,30px) * -1) 0 !important;
  border-radius:0 0 30px 30px !important;
  overflow:hidden !important;
  background:
    linear-gradient(135deg,rgba(30,107,69,.96),rgba(12,83,49,.78)),
    url("https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=1600&q=80") center/cover no-repeat !important;
}
body .tb4c-member-area-page .tb4c-member-area-hero-bg::before,
body .tb4c-member-area-page .tb4c-member-area-hero-bg::after{content:"" !important;display:block !important;position:absolute !important;inset:auto !important;pointer-events:none !important;}
body .tb4c-member-area-page .tb4c-member-area-hero-bg::after{right:30px !important;top:28px !important;width:120px !important;height:120px !important;border-radius:999px !important;background:rgba(255,255,255,.14) !important;filter:blur(1px) !important;}
body .tb4c-member-area-page .tb4c-member-area-avatar{
  width:clamp(116px,15vw,178px) !important;
  height:clamp(116px,15vw,178px) !important;
  margin-top:calc(clamp(70px,10vw,118px) * -1) !important;
  margin-left:clamp(10px,2vw,34px) !important;
  border:8px solid #fff !important;
  border-radius:999px !important;
  box-shadow:0 18px 44px rgba(16,24,40,.18) !important;
  background:#fff !important;
  position:relative !important;
  z-index:2 !important;
}
body .tb4c-member-area-page .tb4c-member-area-avatar img{width:100% !important;height:100% !important;object-fit:cover !important;border-radius:inherit !important;display:block !important;}
body .tb4c-member-area-page .tb4c-member-area-title,
body .tb4c-member-area-page .tb4c-member-title-line{
  min-width:0 !important;
}
body .tb4c-member-area-page .tb4c-member-title-line{display:flex !important;align-items:center !important;gap:12px !important;flex-wrap:wrap !important;}
body .tb4c-member-area-page .tb4c-member-title-line h1{
  margin:0 !important;
  font-size:clamp(32px,4.2vw,56px) !important;
  line-height:1.05 !important;
  font-weight:900 !important;
  letter-spacing:-.045em !important;
  color:#101828 !important;
}
body .tb4c-member-area-page .tb4c-member-handle,
body .tb4c-member-area-page .tb4c-member-area-bio-line{color:#667085 !important;font-size:15px !important;line-height:1.65 !important;margin:8px 0 0 !important;}
body .tb4c-member-area-page .tb4c-member-area-hero-tags{display:flex !important;gap:10px !important;flex-wrap:wrap !important;margin:18px 0 0 !important;}
body .tb4c-member-area-page .tb4c-member-area-hero-tags span,
body .tb4c-member-area-page .tb4c-member-badge,
body .tb4c-member-area-page .tb4c-member-status-pill{
  display:inline-flex !important;align-items:center !important;gap:8px !important;
  min-height:38px !important;padding:8px 14px !important;border-radius:999px !important;
  border:1px solid rgba(30,107,69,.12) !important;background:#f3faf6 !important;color:#1E6B45 !important;
  font-weight:750 !important;font-size:14px !important;
}
body .tb4c-member-area-page .tb4c-member-area-actions{
  grid-column:1/-1 !important;
  display:grid !important;
  grid-template-columns:1.6fr 1fr 1fr !important;
  gap:12px !important;
  margin-top:8px !important;
}
body .tb4c-member-area-page .tb4c-member-area-actions a,
body .tb4c-member-area-page .tb4c-member-area-actions button,
body .tb4c-member-area-page .tb4c-btn{
  min-height:58px !important;border-radius:18px !important;font-weight:850 !important;text-decoration:none !important;
  display:inline-flex !important;align-items:center !important;justify-content:center !important;gap:10px !important;
  border:1px solid rgba(30,107,69,.16) !important;background:#fff !important;color:#1E6B45 !important;
  box-shadow:none !important;
}
body .tb4c-member-area-page .tb4c-member-area-actions .tb4c-btn-primary,
body .tb4c-member-area-page .tb4c-member-composer-trigger{
  background:linear-gradient(135deg,#1E6B45,#0f7a45) !important;color:#fff !important;border-color:transparent !important;
  box-shadow:0 16px 36px rgba(30,107,69,.24) !important;
}
body .tb4c-member-area-page .tb4c-member-area-tabs,
body .tb4c-member-area-page .tb4c-member-tabbar,
body .tb4c-member-area-page .tb4c-member-tabs{
  display:flex !important;gap:10px !important;overflow-x:auto !important;padding:3px 0 12px !important;margin:4px 0 18px !important;
  scrollbar-width:none !important;
}
body .tb4c-member-area-page .tb4c-member-area-tabs a,
body .tb4c-member-area-page .tb4c-member-tabbar a,
body .tb4c-member-area-page .tb4c-member-tabs a{
  flex:0 0 auto !important;min-height:50px !important;padding:12px 18px !important;border-radius:999px !important;
  border:1px solid var(--tb4m-line) !important;background:#fff !important;color:#344054 !important;font-weight:800 !important;text-decoration:none !important;
}
body .tb4c-member-area-page .tb4c-member-area-tabs a.is-active,
body .tb4c-member-area-page .tb4c-member-area-tabs a[aria-current="page"],
body .tb4c-member-area-page .tb4c-member-tabbar a.is-active,
body .tb4c-member-area-page .tb4c-member-tabs a.is-active{
  border-color:rgba(30,107,69,.28) !important;background:#ecf8f1 !important;color:#1E6B45 !important;
}
body .tb4c-member-area-page .tb4c-member-showcase-stats,
body .tb4c-member-area-page .tb4c-member-area-stat-grid{
  display:grid !important;grid-template-columns:repeat(3,minmax(0,1fr)) !important;gap:16px !important;margin:0 0 22px !important;
}
body .tb4c-member-area-page .tb4c-member-showcase-stat,
body .tb4c-member-area-page .tb4c-member-area-stat-grid>* ,
body .tb4c-member-area-page .tb4c-member-area-panel,
body .tb4c-member-area-page .tb4c-member-workspace-card,
body .tb4c-member-area-page .tb4c-member-area-list-card{
  border:1px solid var(--tb4m-line) !important;border-radius:24px !important;background:#fff !important;
  box-shadow:0 12px 36px rgba(16,24,40,.07) !important;padding:clamp(16px,2vw,24px) !important;
}
body .tb4c-member-area-page .tb4c-member-showcase-stat strong,
body .tb4c-member-area-page .tb4c-member-area-stat-grid strong{font-size:clamp(30px,4vw,44px) !important;font-weight:900 !important;color:#1E6B45 !important;line-height:1 !important;}
body .tb4c-member-area-page .tb4c-member-showcase-stat:nth-child(2) strong,
body .tb4c-member-area-page .tb4c-member-area-stat-grid>*:nth-child(2) strong{color:#F97316 !important;}
body .tb4c-member-area-page .tb4c-member-area-main,
body .tb4c-member-area-page .tb4c-member-content-grid,
body .tb4c-member-area-page .tb4c-member-workspace-grid{
  display:grid !important;grid-template-columns:minmax(0,1fr) 340px !important;gap:20px !important;align-items:start !important;
}
body .tb4c-member-area-page .tb4c-member-area-sidebar{display:grid !important;gap:16px !important;}
body .tb4c-member-area-page img,
body .tb4c-member-area-page video{max-width:100% !important;height:auto !important;touch-action:pan-y !important;}
@media (max-width:1024px){
  body .tb4c-member-area-page{padding:18px !important;}
  body .tb4c-member-area-page .tb4c-member-area-hero{grid-template-columns:1fr !important;border-radius:28px !important;}
  body .tb4c-member-area-page .tb4c-member-area-main,
  body .tb4c-member-area-page .tb4c-member-content-grid,
  body .tb4c-member-area-page .tb4c-member-workspace-grid{grid-template-columns:1fr !important;}
  body .tb4c-member-area-page .tb4c-member-showcase-stats,
  body .tb4c-member-area-page .tb4c-member-area-stat-grid{grid-template-columns:repeat(3,minmax(0,1fr)) !important;}
}
@media (max-width:767px){
  body .tb4c-member-area-page{padding:14px 12px 92px !important;background:#f7fbf8 !important;}
  body .tb4c-member-area-page .tb4c-reference-page-head{display:block !important;margin-bottom:14px !important;}
  body .tb4c-member-area-page .tb4c-reference-title h1{font-size:34px !important;}
  body .tb4c-member-area-page .tb4c-member-area-hero{display:block !important;padding:14px !important;border-radius:30px !important;margin-bottom:18px !important;}
  body .tb4c-member-area-page .tb4c-member-area-hero-bg{height:184px !important;margin:-14px -14px 0 !important;border-radius:30px 30px 20px 20px !important;}
  body .tb4c-member-area-page .tb4c-member-area-avatar{width:136px !important;height:136px !important;margin:-70px 0 0 22px !important;border-width:7px !important;}
  body .tb4c-member-area-page .tb4c-member-title-line h1{font-size:38px !important;margin-top:12px !important;}
  body .tb4c-member-area-page .tb4c-member-area-actions{grid-template-columns:1fr 1fr !important;gap:10px !important;margin-top:18px !important;}
  body .tb4c-member-area-page .tb4c-member-area-actions .tb4c-btn-primary,
  body .tb4c-member-area-page .tb4c-member-composer-trigger{grid-column:1/-1 !important;}
  body .tb4c-member-area-page .tb4c-member-showcase-stats,
  body .tb4c-member-area-page .tb4c-member-area-stat-grid{grid-template-columns:repeat(3,minmax(0,1fr)) !important;gap:10px !important;}
  body .tb4c-member-area-page .tb4c-member-showcase-stat,
  body .tb4c-member-area-page .tb4c-member-area-stat-grid>*{padding:16px 12px !important;border-radius:22px !important;min-height:132px !important;}
  body .tb4c-member-area-page .tb4c-member-showcase-stat strong,
  body .tb4c-member-area-page .tb4c-member-area-stat-grid strong{font-size:31px !important;}
  body .tb4c-member-area-page .tb4c-member-showcase-stat span,
  body .tb4c-member-area-page .tb4c-member-area-stat-grid span{font-size:12px !important;line-height:1.35 !important;}
  body .tb4c-member-area-page .tb4c-member-area-tabs a,
  body .tb4c-member-area-page .tb4c-member-tabbar a,
  body .tb4c-member-area-page .tb4c-member-tabs a{min-height:46px !important;padding:11px 14px !important;font-size:14px !important;}
}
@media (max-width:430px){
  body .tb4c-member-area-page .tb4c-member-showcase-stats,
  body .tb4c-member-area-page .tb4c-member-area-stat-grid{grid-template-columns:1fr 1fr 1fr !important;}
  body .tb4c-member-area-page .tb4c-member-area-actions a,
  body .tb4c-member-area-page .tb4c-member-area-actions button{min-height:54px !important;font-size:14px !important;}
}

/* v4.2.243 Clean Member Area: remove duplicate overview blocks below the tab bar. */
body .tb4c-member-area-page .tb4c-member-area-layout{
  display:block !important;
  grid-template-columns:1fr !important;
  width:100% !important;
  max-width:100% !important;
  gap:0 !important;
}
body .tb4c-member-area-page .tb4c-member-area-main{
  display:block !important;
  width:100% !important;
  max-width:100% !important;
}
body .tb4c-member-area-page .tb4c-member-workspace-grid,
body .tb4c-member-area-page .tb4c-member-area-stat-grid,
body .tb4c-member-area-page .tb4c-member-showcase-sidebar{
  display:none !important;
}
body .tb4c-member-area-page .tb4c-member-area-panel{
  width:100% !important;
  max-width:100% !important;
}
body .tb4c-member-area-page .tb4c-member-area-tabs{
  margin-bottom:20px !important;
}
body .tb4c-member-area-page .tb4c-member-area-overview-own-feed-panel{
  margin-top:0 !important;
}

</style>
    <?php
}
add_action( 'wp_head', 'tb4cm_print_member_area_hard_override_css_v4243', 99999 );


/**
 * v4.2.244: Instagram-like space efficient member profile.
 * - ลดพื้นที่ว่างของ Hero/Stats/Tabs
 * - เปลี่ยนฟีดภาพรวมให้เป็น Grid 3 คอลัมน์แบบ Instagram
 * - ปรับ Tablet/Phone ให้ใช้พื้นที่คุ้มค่าและไม่เกิดการ์ดซ้ำ
 */
function tb4cm_print_member_area_instagram_space_css_v4244() {
    if ( ! function_exists( 'tb4cm_is_member_area_surface_v4242' ) || ! tb4cm_is_member_area_surface_v4242() ) {
        return;
    }
    ?>
<style id="thinkb4do-community-member-instagram-space-v4244">
:root{
  --tb4m-ig-green:#1E6B45;
  --tb4m-ig-green-dark:#0F5132;
  --tb4m-ig-orange:#F97316;
  --tb4m-ig-ink:#111111;
  --tb4m-ig-muted:#667085;
  --tb4m-ig-line:rgba(17,17,17,.10);
  --tb4m-ig-soft:#F6FAF7;
}
html body .tb4c-member-area-page,
html body .tb4c-member-responsive-standard-page{
  padding:clamp(10px,1.4vw,22px) !important;
  background:linear-gradient(180deg,#ffffff 0%,#f7fbf8 100%) !important;
  color:var(--tb4m-ig-ink) !important;
}
html body .tb4c-member-area-page .tb4c-container,
html body .tb4c-member-area-page .tb4c-member-area-container,
html body .tb4c-member-responsive-standard-page .tb4c-container{
  width:min(100%,1120px) !important;
  max-width:1120px !important;
  margin-inline:auto !important;
  padding:0 !important;
}
html body .tb4c-member-area-page .tb4c-reference-page-head{
  margin:0 0 10px !important;
  padding:0 !important;
  min-height:0 !important;
  border:0 !important;
  background:transparent !important;
  box-shadow:none !important;
}
html body .tb4c-member-area-page .tb4c-reference-title h1{
  font-size:clamp(22px,2.4vw,34px) !important;
  letter-spacing:-.035em !important;
  line-height:1.12 !important;
  margin:0 !important;
}
html body .tb4c-member-area-page .tb4c-reference-title p{
  font-size:14px !important;
  line-height:1.45 !important;
  margin:4px 0 0 !important;
  color:var(--tb4m-ig-muted) !important;
}

/* Compact Instagram-style profile head */
html body .tb4c-member-area-page .tb4c-member-area-hero{
  display:grid !important;
  grid-template-columns:clamp(116px,14vw,168px) minmax(0,1fr) !important;
  grid-template-areas:
    "cover cover"
    "avatar title"
    "avatar actions" !important;
  gap:12px 22px !important;
  margin:0 0 12px !important;
  padding:0 0 16px !important;
  border:0 !important;
  border-bottom:1px solid var(--tb4m-ig-line) !important;
  border-radius:0 !important;
  background:transparent !important;
  box-shadow:none !important;
  overflow:visible !important;
  isolation:isolate !important;
}
html body .tb4c-member-area-page .tb4c-member-area-hero-bg{
  grid-area:cover !important;
  position:relative !important;
  inset:auto !important;
  width:100% !important;
  height:clamp(130px,17vw,190px) !important;
  margin:0 !important;
  border-radius:24px !important;
  overflow:hidden !important;
  z-index:0 !important;
  background:
    linear-gradient(90deg,rgba(15,81,50,.86),rgba(30,107,69,.50)),
    var(--tb4c-member-cover-image, linear-gradient(135deg,#0F5132,#DDEFE5)) !important;
  background-size:cover !important;
  background-position:center !important;
  box-shadow:none !important;
}
html body .tb4c-member-area-page .tb4c-member-area-hero-bg::before{
  content:"" !important;
  position:absolute !important;
  inset:0 !important;
  background:radial-gradient(circle at 84% 16%,rgba(255,255,255,.30),transparent 24%) !important;
  display:block !important;
}
html body .tb4c-member-area-page .tb4c-member-area-hero-bg::after,
html body .tb4c-member-area-page .tb4c-member-area-hero-bg > span{
  display:none !important;
}
html body .tb4c-member-area-page .tb4c-member-area-avatar{
  grid-area:avatar !important;
  position:relative !important;
  width:clamp(116px,14vw,168px) !important;
  height:clamp(116px,14vw,168px) !important;
  min-width:0 !important;
  margin:clamp(-72px,-6vw,-44px) 0 0 clamp(8px,1.4vw,18px) !important;
  padding:0 !important;
  border:6px solid #fff !important;
  border-radius:999px !important;
  background:#fff !important;
  box-shadow:0 12px 34px rgba(16,24,40,.14) !important;
  align-self:start !important;
  overflow:visible !important;
  z-index:3 !important;
}
html body .tb4c-member-area-page .tb4c-member-area-avatar img{
  display:block !important;
  width:100% !important;
  height:100% !important;
  object-fit:cover !important;
  border-radius:inherit !important;
}
html body .tb4c-member-area-page .tb4c-member-camera-badge,
html body .tb4c-member-area-page .tb4c-member-area-avatar-sync-badge{
  right:2px !important;
  bottom:8px !important;
  width:36px !important;
  height:36px !important;
  border:3px solid #fff !important;
  background:var(--tb4m-ig-green) !important;
  color:#fff !important;
  box-shadow:0 8px 20px rgba(30,107,69,.25) !important;
}
html body .tb4c-member-area-page .tb4c-member-area-title{
  grid-area:title !important;
  align-self:end !important;
  min-width:0 !important;
  padding:0 !important;
}
html body .tb4c-member-area-page .tb4c-member-title-line{
  display:flex !important;
  align-items:center !important;
  gap:9px !important;
  flex-wrap:wrap !important;
}
html body .tb4c-member-area-page .tb4c-member-title-line h1{
  margin:0 !important;
  font-size:clamp(28px,3.2vw,44px) !important;
  line-height:1.05 !important;
  letter-spacing:-.045em !important;
  font-weight:900 !important;
}
html body .tb4c-member-area-page .tb4c-member-verified{
  display:inline-grid !important;
  place-items:center !important;
  width:24px !important;
  height:24px !important;
  border-radius:999px !important;
  background:var(--tb4m-ig-green) !important;
  color:#fff !important;
  font-size:14px !important;
}
html body .tb4c-member-area-page .tb4c-member-handle,
html body .tb4c-member-area-page .tb4c-member-area-bio-line{
  margin:5px 0 0 !important;
  color:var(--tb4m-ig-muted) !important;
  font-size:14px !important;
  line-height:1.45 !important;
}
html body .tb4c-member-area-page .tb4c-member-area-hero-tags{
  display:flex !important;
  flex-wrap:wrap !important;
  gap:8px !important;
  margin:9px 0 0 !important;
}
html body .tb4c-member-area-page .tb4c-member-area-hero-tags span{
  display:inline-flex !important;
  align-items:center !important;
  gap:6px !important;
  min-height:30px !important;
  padding:5px 10px !important;
  border:1px solid rgba(30,107,69,.12) !important;
  border-radius:999px !important;
  background:#f4faf6 !important;
  color:var(--tb4m-ig-green) !important;
  font-size:12.5px !important;
  font-weight:750 !important;
  white-space:nowrap !important;
}
html body .tb4c-member-area-page .tb4c-member-area-actions{
  grid-area:actions !important;
  display:flex !important;
  flex-wrap:wrap !important;
  align-items:center !important;
  gap:8px !important;
  margin:0 !important;
  padding:0 !important;
}
html body .tb4c-member-area-page .tb4c-member-area-actions .tb4c-btn,
html body .tb4c-member-area-page .tb4c-member-area-actions a,
html body .tb4c-member-area-page .tb4c-member-area-actions button{
  flex:0 0 auto !important;
  min-height:38px !important;
  height:38px !important;
  padding:0 14px !important;
  border-radius:10px !important;
  border:1px solid var(--tb4m-ig-line) !important;
  background:#fff !important;
  color:var(--tb4m-ig-ink) !important;
  box-shadow:none !important;
  font-size:13px !important;
  font-weight:800 !important;
}
html body .tb4c-member-area-page .tb4c-member-area-actions .tb4c-btn-primary,
html body .tb4c-member-area-page .tb4c-member-composer-trigger{
  background:var(--tb4m-ig-green) !important;
  color:#fff !important;
  border-color:var(--tb4m-ig-green) !important;
}

/* Stats become an inline profile count bar, not bulky cards */
html body .tb4c-member-area-page .tb4c-member-showcase-stats{
  display:grid !important;
  grid-template-columns:repeat(5,minmax(0,1fr)) minmax(190px,1.05fr) !important;
  gap:0 !important;
  margin:0 0 10px !important;
  padding:10px 0 !important;
  border-bottom:1px solid var(--tb4m-ig-line) !important;
  background:transparent !important;
}
html body .tb4c-member-area-page .tb4c-member-showcase-stat{
  display:flex !important;
  flex-direction:column !important;
  align-items:center !important;
  justify-content:center !important;
  gap:2px !important;
  min-height:58px !important;
  padding:6px 8px !important;
  border:0 !important;
  border-right:1px solid rgba(17,17,17,.07) !important;
  border-radius:0 !important;
  background:transparent !important;
  box-shadow:none !important;
  text-align:center !important;
}
html body .tb4c-member-area-page .tb4c-member-showcase-stat i{
  display:none !important;
}
html body .tb4c-member-area-page .tb4c-member-showcase-stat strong{
  margin:0 !important;
  color:var(--tb4m-ig-ink) !important;
  font-size:clamp(19px,2vw,26px) !important;
  line-height:1 !important;
  font-weight:900 !important;
}
html body .tb4c-member-area-page .tb4c-member-showcase-stat span{
  color:var(--tb4m-ig-muted) !important;
  font-size:12px !important;
  line-height:1.25 !important;
  font-weight:700 !important;
}
html body .tb4c-member-area-page .tb4c-member-showcase-progress{
  display:flex !important;
  flex-direction:column !important;
  justify-content:center !important;
  gap:6px !important;
  min-height:58px !important;
  padding:6px 0 6px 16px !important;
  border:0 !important;
  border-radius:0 !important;
  background:transparent !important;
  color:var(--tb4m-ig-ink) !important;
  box-shadow:none !important;
  text-decoration:none !important;
}
html body .tb4c-member-area-page .tb4c-member-showcase-progress span{
  display:flex !important;
  align-items:center !important;
  justify-content:space-between !important;
  gap:8px !important;
  font-size:12px !important;
  line-height:1.2 !important;
}
html body .tb4c-member-area-page .tb4c-member-showcase-progress em{
  color:var(--tb4m-ig-green) !important;
  font-style:normal !important;
  font-weight:900 !important;
}
html body .tb4c-member-area-page .tb4c-member-showcase-progress i{
  display:block !important;
  width:100% !important;
  height:6px !important;
  border-radius:999px !important;
  background:#e9efe9 !important;
  overflow:hidden !important;
}
html body .tb4c-member-area-page .tb4c-member-showcase-progress i b{
  display:block !important;
  height:100% !important;
  border-radius:999px !important;
  background:linear-gradient(90deg,var(--tb4m-ig-green),var(--tb4m-ig-orange)) !important;
}
html body .tb4c-member-area-page .tb4c-member-showcase-progress small{
  display:none !important;
}

/* Tabs: Instagram-like underline, compact and sticky under header */
html body .tb4c-member-area-page .tb4c-member-area-tabs{
  position:sticky !important;
  top:0 !important;
  z-index:20 !important;
  display:flex !important;
  align-items:center !important;
  justify-content:center !important;
  gap:0 !important;
  overflow-x:auto !important;
  margin:0 0 8px !important;
  padding:0 !important;
  border-bottom:1px solid var(--tb4m-ig-line) !important;
  background:rgba(255,255,255,.94) !important;
  backdrop-filter:saturate(130%) blur(10px) !important;
  scrollbar-width:none !important;
}
html body .tb4c-member-area-page .tb4c-member-area-tabs::-webkit-scrollbar{display:none !important;}
html body .tb4c-member-area-page .tb4c-member-area-tabs a{
  position:relative !important;
  flex:0 0 auto !important;
  min-width:118px !important;
  min-height:48px !important;
  padding:0 14px !important;
  display:inline-flex !important;
  align-items:center !important;
  justify-content:center !important;
  gap:7px !important;
  border:0 !important;
  border-radius:0 !important;
  background:transparent !important;
  color:#475467 !important;
  box-shadow:none !important;
  font-size:13px !important;
  font-weight:850 !important;
  text-decoration:none !important;
}
html body .tb4c-member-area-page .tb4c-member-area-tabs a.is-active,
html body .tb4c-member-area-page .tb4c-member-area-tabs a[aria-current="page"]{
  color:var(--tb4m-ig-green) !important;
  background:transparent !important;
}
html body .tb4c-member-area-page .tb4c-member-area-tabs a.is-active::after,
html body .tb4c-member-area-page .tb4c-member-area-tabs a[aria-current="page"]::after{
  content:"" !important;
  position:absolute !important;
  left:14px !important;
  right:14px !important;
  bottom:0 !important;
  height:3px !important;
  border-radius:999px 999px 0 0 !important;
  background:var(--tb4m-ig-green) !important;
}

/* After tabs: only main content, no duplicate dashboard blocks */
html body .tb4c-member-area-page .tb4c-member-area-layout,
html body .tb4c-member-area-page .tb4c-member-area-main{
  display:block !important;
  width:100% !important;
  max-width:100% !important;
  margin:0 !important;
  padding:0 !important;
}
html body .tb4c-member-area-page .tb4c-member-workspace-grid,
html body .tb4c-member-area-page .tb4c-member-area-stat-grid,
html body .tb4c-member-area-page .tb4c-member-showcase-sidebar,
html body .tb4c-member-area-page .tb4c-member-area-sidebar{
  display:none !important;
}
html body .tb4c-member-area-page .tb4c-member-area-panel{
  margin:0 !important;
  padding:0 !important;
  border:0 !important;
  border-radius:0 !important;
  background:transparent !important;
  box-shadow:none !important;
}
html body .tb4c-member-area-page .tb4c-member-area-panel-head{
  display:flex !important;
  align-items:center !important;
  justify-content:space-between !important;
  gap:12px !important;
  margin:10px 0 10px !important;
  padding:0 !important;
}
html body .tb4c-member-area-page .tb4c-member-area-panel-head h2{
  margin:0 !important;
  font-size:18px !important;
  line-height:1.2 !important;
  font-weight:900 !important;
}
html body .tb4c-member-area-page .tb4c-member-area-panel-head p,
html body .tb4c-member-area-page .tb4c-member-area-feed-head-actions span{
  display:none !important;
}
html body .tb4c-member-area-page .tb4c-member-area-panel-head a{
  color:var(--tb4m-ig-green) !important;
  font-size:13px !important;
  font-weight:850 !important;
  text-decoration:none !important;
}

/* Instagram grid for overview/posts feed */
html body .tb4c-member-area-page .tb4c-feed-list.tb4c-member-own-feed-list,
html body .tb4c-member-area-page .tb4c-member-area-list,
html body .tb4c-member-area-page .tb4c-member-overview-own-feed-list{
  display:grid !important;
  grid-template-columns:repeat(3,minmax(0,1fr)) !important;
  gap:6px !important;
  width:100% !important;
  max-width:none !important;
  margin:0 !important;
  padding:0 !important;
}
html body .tb4c-member-area-page .tb4c-feed-list.tb4c-member-own-feed-list > article,
html body .tb4c-member-area-page .tb4c-member-area-list > article,
html body .tb4c-member-area-page .tb4c-member-own-feed-card,
html body .tb4c-member-area-page .tb4c-post-card.tb4c-member-own-feed-card,
html body .tb4c-member-area-page .tb4c-member-area-list-card{
  position:relative !important;
  display:block !important;
  aspect-ratio:1/1 !important;
  min-height:0 !important;
  height:auto !important;
  width:100% !important;
  margin:0 !important;
  padding:0 !important;
  border:0 !important;
  border-radius:14px !important;
  background:#eaf2ed !important;
  box-shadow:none !important;
  overflow:hidden !important;
  isolation:isolate !important;
}
html body .tb4c-member-area-page .tb4c-member-own-feed-card .tb4c-post-head,
html body .tb4c-member-area-page .tb4c-member-own-feed-card .tb4c-post-meta,
html body .tb4c-member-area-page .tb4c-member-own-feed-card .tb4c-author-audience-row,
html body .tb4c-member-area-page .tb4c-member-own-feed-card .tb4c-legacy-post-summary,
html body .tb4c-member-area-page .tb4c-member-own-feed-card .tb4c-native-reply-row,
html body .tb4c-member-area-page .tb4c-member-own-feed-card .tb4c-post-tag-strip,
html body .tb4c-member-area-page .tb4c-member-own-feed-card .tb4c-type-tag-strip,
html body .tb4c-member-area-page .tb4c-member-own-feed-card .tb4c-action-view-chip{
  display:none !important;
}
html body .tb4c-member-area-page .tb4c-member-own-feed-card .tb4c-post-inline-text{
  position:absolute !important;
  inset:auto 0 0 0 !important;
  z-index:4 !important;
  display:block !important;
  padding:46px 12px 12px !important;
  color:#fff !important;
  text-decoration:none !important;
  background:linear-gradient(180deg,rgba(0,0,0,0),rgba(0,0,0,.62)) !important;
  pointer-events:auto !important;
}
html body .tb4c-member-area-page .tb4c-member-own-feed-card .tb4c-post-inline-text h2{
  margin:0 !important;
  color:#fff !important;
  font-size:14px !important;
  line-height:1.25 !important;
  font-weight:850 !important;
  display:-webkit-box !important;
  -webkit-line-clamp:2 !important;
  -webkit-box-orient:vertical !important;
  overflow:hidden !important;
}
html body .tb4c-member-area-page .tb4c-member-own-feed-card .tb4c-post-inline-text p{
  display:none !important;
}
html body .tb4c-member-area-page .tb4c-member-own-feed-card .tb4c-thumb,
html body .tb4c-member-area-page .tb4c-member-own-feed-card .tb4c-ig-media,
html body .tb4c-member-area-page .tb4c-member-own-feed-card .tb4c-post-media-slot,
html body .tb4c-member-area-page .tb4c-member-own-feed-card .tb4c-post-image-slot,
html body .tb4c-member-area-page .tb4c-member-own-feed-card [data-tb4c-media-album],
html body .tb4c-member-area-page .tb4c-member-own-feed-card .tb4c-media-album,
html body .tb4c-member-area-page .tb4c-member-area-post-thumb{
  position:absolute !important;
  inset:0 !important;
  width:100% !important;
  height:100% !important;
  min-width:0 !important;
  min-height:0 !important;
  max-height:none !important;
  display:block !important;
  margin:0 !important;
  padding:0 !important;
  border:0 !important;
  border-radius:0 !important;
  background:#eaf2ed !important;
  overflow:hidden !important;
}
html body .tb4c-member-area-page .tb4c-member-own-feed-card img,
html body .tb4c-member-area-page .tb4c-member-own-feed-card video,
html body .tb4c-member-area-page .tb4c-member-area-post-thumb img{
  width:100% !important;
  height:100% !important;
  min-height:100% !important;
  max-height:none !important;
  object-fit:cover !important;
  display:block !important;
  border-radius:0 !important;
  touch-action:pan-y !important;
}
html body .tb4c-member-area-page .tb4c-member-own-feed-card.tb4c-text-only-post::before{
  content:"" !important;
  position:absolute !important;
  inset:0 !important;
  background:
    radial-gradient(circle at 80% 18%,rgba(249,115,22,.20),transparent 28%),
    linear-gradient(135deg,#0f5132,#1E6B45) !important;
  z-index:0 !important;
}
html body .tb4c-member-area-page .tb4c-member-own-feed-card.tb4c-text-only-post .tb4c-post-inline-text{
  inset:0 !important;
  display:grid !important;
  place-items:center !important;
  padding:18px !important;
  background:transparent !important;
  text-align:center !important;
}
html body .tb4c-member-area-page .tb4c-member-own-feed-card.tb4c-text-only-post .tb4c-post-inline-text h2{
  font-size:clamp(14px,1.4vw,20px) !important;
  -webkit-line-clamp:4 !important;
}
html body .tb4c-member-area-page .tb4c-member-own-feed-card .tb4c-post-actions,
html body .tb4c-member-area-page .tb4c-member-area-card-actions{
  position:absolute !important;
  z-index:6 !important;
  left:8px !important;
  right:8px !important;
  bottom:8px !important;
  display:flex !important;
  align-items:center !important;
  justify-content:space-between !important;
  gap:4px !important;
  padding:0 !important;
  background:transparent !important;
  border:0 !important;
}
html body .tb4c-member-area-page .tb4c-member-own-feed-card .tb4c-post-actions button,
html body .tb4c-member-area-page .tb4c-member-own-feed-card .tb4c-post-actions a,
html body .tb4c-member-area-page .tb4c-member-own-feed-card .tb4c-post-actions span,
html body .tb4c-member-area-page .tb4c-member-area-card-actions .tb4c-btn{
  min-width:0 !important;
  min-height:30px !important;
  height:30px !important;
  padding:0 8px !important;
  border:0 !important;
  border-radius:999px !important;
  background:rgba(0,0,0,.44) !important;
  color:#fff !important;
  backdrop-filter:blur(8px) !important;
  box-shadow:none !important;
  font-size:12px !important;
  font-weight:800 !important;
}
html body .tb4c-member-area-page .tb4c-member-own-feed-card .tb4c-post-actions em,
html body .tb4c-member-area-page .tb4c-member-area-card-actions .tb4c-btn:not(:first-child){
  display:none !important;
}
html body .tb4c-member-area-page .tb4c-member-area-list-card > div:not(.tb4c-member-area-card-actions){
  position:absolute !important;
  inset:auto 0 0 !important;
  z-index:4 !important;
  padding:40px 12px 12px !important;
  background:linear-gradient(180deg,rgba(0,0,0,0),rgba(0,0,0,.62)) !important;
  color:#fff !important;
}
html body .tb4c-member-area-page .tb4c-member-area-list-card h3,
html body .tb4c-member-area-page .tb4c-member-area-list-card h3 a{
  margin:0 !important;
  color:#fff !important;
  font-size:14px !important;
  line-height:1.25 !important;
  text-decoration:none !important;
}
html body .tb4c-member-area-page .tb4c-member-area-list-card p,
html body .tb4c-member-area-page .tb4c-member-area-list-meta,
html body .tb4c-member-area-page .tb4c-member-area-mini-stats{
  display:none !important;
}

/* Normal panels for comments/settings/following stay readable */
html body .tb4c-member-tab-comments .tb4c-member-area-panel,
html body .tb4c-member-tab-saved .tb4c-member-area-panel,
html body .tb4c-member-tab-following .tb4c-member-area-panel,
html body .tb4c-member-tab-settings .tb4c-member-area-panel,
html body .tb4c-member-tab-products .tb4c-member-area-panel{
  padding:18px !important;
  border:1px solid var(--tb4m-ig-line) !important;
  border-radius:22px !important;
  background:#fff !important;
  box-shadow:none !important;
}
html body .tb4c-member-tab-settings .tb4c-member-area-panel,
html body .tb4c-member-tab-comments .tb4c-member-area-panel{
  max-width:860px !important;
  margin-inline:auto !important;
}
html body .tb4c-member-area-page .tb4c-member-area-empty{
  padding:32px 18px !important;
  border:1px dashed rgba(30,107,69,.22) !important;
  border-radius:22px !important;
  background:#fff !important;
  text-align:center !important;
}

@media (min-width:1280px){
  html body .tb4c-member-area-page .tb4c-container,
  html body .tb4c-member-area-page .tb4c-member-area-container{max-width:1180px !important;}
  html body .tb4c-member-area-page .tb4c-feed-list.tb4c-member-own-feed-list,
  html body .tb4c-member-area-page .tb4c-member-area-list{gap:8px !important;}
}
@media (max-width:1024px){
  html body .tb4c-member-area-page .tb4c-container,
  html body .tb4c-member-area-page .tb4c-member-area-container{max-width:860px !important;}
  html body .tb4c-member-area-page .tb4c-member-showcase-stats{grid-template-columns:repeat(5,minmax(0,1fr)) !important;}
  html body .tb4c-member-area-page .tb4c-member-showcase-progress{grid-column:1/-1 !important;padding:8px 0 0 !important;}
}
@media (max-width:767px){
  html body .tb4c-member-area-page,
  html body .tb4c-member-responsive-standard-page{padding:10px 10px 86px !important;}
  html body .tb4c-member-area-page .tb4c-reference-page-head{display:none !important;}
  html body .tb4c-member-area-page .tb4c-container,
  html body .tb4c-member-area-page .tb4c-member-area-container{max-width:none !important;}
  html body .tb4c-member-area-page .tb4c-member-area-hero{
    grid-template-columns:96px minmax(0,1fr) !important;
    grid-template-areas:
      "cover cover"
      "avatar title"
      "actions actions" !important;
    gap:10px 14px !important;
    margin-bottom:8px !important;
    padding-bottom:12px !important;
  }
  html body .tb4c-member-area-page .tb4c-member-area-hero-bg{height:112px !important;border-radius:18px !important;}
  html body .tb4c-member-area-page .tb4c-member-area-avatar{
    width:96px !important;height:96px !important;border-width:4px !important;margin:-48px 0 0 8px !important;
  }
  html body .tb4c-member-area-page .tb4c-member-title-line h1{font-size:26px !important;letter-spacing:-.035em !important;}
  html body .tb4c-member-area-page .tb4c-member-area-bio-line{display:-webkit-box !important;-webkit-line-clamp:2 !important;-webkit-box-orient:vertical !important;overflow:hidden !important;}
  html body .tb4c-member-area-page .tb4c-member-area-hero-tags{gap:6px !important;margin-top:7px !important;}
  html body .tb4c-member-area-page .tb4c-member-area-hero-tags span{min-height:28px !important;padding:4px 8px !important;font-size:11.5px !important;}
  html body .tb4c-member-area-page .tb4c-member-area-actions{display:grid !important;grid-template-columns:1fr 1fr !important;gap:8px !important;}
  html body .tb4c-member-area-page .tb4c-member-area-actions .tb4c-btn,
  html body .tb4c-member-area-page .tb4c-member-area-actions a,
  html body .tb4c-member-area-page .tb4c-member-area-actions button{width:100% !important;height:40px !important;min-height:40px !important;padding:0 8px !important;}
  html body .tb4c-member-area-page .tb4c-member-composer-trigger{grid-column:1/-1 !important;}
  html body .tb4c-member-area-page .tb4c-member-showcase-stats{
    grid-template-columns:repeat(5,minmax(0,1fr)) !important;
    padding:8px 0 !important;
    margin-bottom:2px !important;
  }
  html body .tb4c-member-area-page .tb4c-member-showcase-stat{min-height:48px !important;padding:4px 2px !important;}
  html body .tb4c-member-area-page .tb4c-member-showcase-stat strong{font-size:18px !important;}
  html body .tb4c-member-area-page .tb4c-member-showcase-stat span{font-size:10.5px !important;}
  html body .tb4c-member-area-page .tb4c-member-showcase-progress{display:none !important;}
  html body .tb4c-member-area-page .tb4c-member-area-tabs{justify-content:flex-start !important;margin-inline:-10px !important;padding-inline:6px !important;top:0 !important;}
  html body .tb4c-member-area-page .tb4c-member-area-tabs a{min-width:auto !important;min-height:44px !important;padding:0 12px !important;font-size:12.5px !important;}
  html body .tb4c-member-area-page .tb4c-feed-list.tb4c-member-own-feed-list,
  html body .tb4c-member-area-page .tb4c-member-area-list,
  html body .tb4c-member-area-page .tb4c-member-overview-own-feed-list{gap:3px !important;}
  html body .tb4c-member-area-page .tb4c-feed-list.tb4c-member-own-feed-list > article,
  html body .tb4c-member-area-page .tb4c-member-area-list > article,
  html body .tb4c-member-area-page .tb4c-member-own-feed-card,
  html body .tb4c-member-area-page .tb4c-member-area-list-card{border-radius:4px !important;}
  html body .tb4c-member-area-page .tb4c-member-own-feed-card .tb4c-post-inline-text{padding:32px 8px 8px !important;}
  html body .tb4c-member-area-page .tb4c-member-own-feed-card .tb4c-post-inline-text h2{font-size:11.5px !important;-webkit-line-clamp:2 !important;}
  html body .tb4c-member-area-page .tb4c-member-own-feed-card .tb4c-post-actions{display:none !important;}
}
@media (max-width:430px){
  html body .tb4c-member-area-page .tb4c-member-area-hero-tags span:nth-child(n+3){display:none !important;}
  html body .tb4c-member-area-page .tb4c-member-showcase-stat span{letter-spacing:-.02em !important;}
}
</style>
    <?php
}
add_action( 'wp_head', 'tb4cm_print_member_area_instagram_space_css_v4244', 100000 );


/**
 * v4.2.245: Thinkb4do Space Polish.
 * - จัดพื้นที่ใหม่ให้คลีนขึ้นตามสไตล์ Thinkb4do: เขียว/ขาว/ส้ม/ดำ
 * - ใช้ความกว้างเดสก์ท็อปคุ้มขึ้น ลดช่องว่างซ้ายขวา และคงความเบาแบบ Instagram
 * - Mobile/Tablet อ่านง่ายกว่าเดิม ไม่ซ้อน ไม่กินพื้นที่เกินจำเป็น
 */
function tb4cm_print_member_area_thinkb4do_space_css_v4245() {
    if ( ! function_exists( 'tb4cm_is_member_area_surface_v4242' ) || ! tb4cm_is_member_area_surface_v4242() ) {
        return;
    }
    ?>
<style id="thinkb4do-community-member-thinkb4do-space-v4245">
:root{
  --tb4m-brand-green:#1E6B45;
  --tb4m-brand-green-2:#0F5132;
  --tb4m-brand-green-soft:#EAF6EF;
  --tb4m-brand-orange:#F97316;
  --tb4m-brand-black:#111111;
  --tb4m-brand-muted:#5F6B63;
  --tb4m-brand-line:rgba(17,17,17,.085);
  --tb4m-brand-shadow:0 16px 42px rgba(15,81,50,.08);
  --tb4m-brand-radius:26px;
}

/* Let the member surface use the page width. This fixes narrow centered content on desktop. */
html body.tb4c-member-area-page-active .entry-content,
html body.tb4c-member-area-page-active .wp-block-post-content,
html body.tb4c-member-area-page-active .site-content,
html body.tb4c-member-area-page-active .content-area,
html body.tb4c-member-area-page-active main{
  max-width:none !important;
}
html body.tb4c-member-area-page-active .entry-content:has(.tb4c-member-area-page),
html body.tb4c-member-area-page-active .wp-block-post-content:has(.tb4c-member-area-page){
  width:100% !important;
  margin:0 !important;
  padding:0 !important;
}

html body .tb4c-member-area-page,
html body .tb4c-member-responsive-standard-page{
  background:
    radial-gradient(circle at 8% 0%,rgba(30,107,69,.075),transparent 28%),
    radial-gradient(circle at 96% 10%,rgba(249,115,22,.06),transparent 24%),
    linear-gradient(180deg,#ffffff 0%,#F7FBF8 100%) !important;
  padding:clamp(12px,1.5vw,24px) !important;
  font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",Arial,"Noto Sans Thai","Kanit",sans-serif !important;
}
html body .tb4c-member-area-page .tb4c-container,
html body .tb4c-member-area-page .tb4c-member-area-container,
html body .tb4c-member-responsive-standard-page .tb4c-container{
  width:min(100%,1280px) !important;
  max-width:1280px !important;
  margin-inline:auto !important;
  padding:0 !important;
}

/* Page title: small, clean, not a second hero. */
html body .tb4c-member-area-page .tb4c-reference-page-head{
  margin:0 0 12px !important;
  padding:0 !important;
}
html body .tb4c-member-area-page .tb4c-reference-title h1{
  color:var(--tb4m-brand-black) !important;
  font-size:clamp(24px,2.15vw,36px) !important;
  font-weight:950 !important;
  letter-spacing:-.045em !important;
}
html body .tb4c-member-area-page .tb4c-reference-title p{
  color:var(--tb4m-brand-muted) !important;
  font-size:14px !important;
}

/* Thinkb4do profile block: cover + identity + command area in one calm unit. */
html body .tb4c-member-area-page .tb4c-member-area-hero{
  position:relative !important;
  display:grid !important;
  grid-template-columns:minmax(132px,170px) minmax(0,1fr) auto !important;
  grid-template-areas:
    "cover cover cover"
    "avatar title actions" !important;
  gap:16px 22px !important;
  margin:0 0 12px !important;
  padding:0 0 18px !important;
  border:1px solid rgba(30,107,69,.10) !important;
  border-radius:var(--tb4m-brand-radius) !important;
  background:rgba(255,255,255,.82) !important;
  box-shadow:var(--tb4m-brand-shadow) !important;
  overflow:hidden !important;
}
html body .tb4c-member-area-page .tb4c-member-area-hero-bg{
  grid-area:cover !important;
  height:clamp(160px,17vw,220px) !important;
  border-radius:0 !important;
  margin:0 !important;
  background:
    linear-gradient(90deg,rgba(15,81,50,.90),rgba(30,107,69,.50) 48%,rgba(234,246,239,.22)),
    var(--tb4c-member-cover-image, linear-gradient(135deg,#0F5132 0%,#1E6B45 46%,#DDEFE5 100%)) !important;
  background-size:cover !important;
  background-position:center !important;
}
html body .tb4c-member-area-page .tb4c-member-area-hero-bg::before{
  content:"" !important;
  position:absolute !important;
  inset:0 !important;
  background:
    radial-gradient(circle at 84% 20%,rgba(255,255,255,.32),transparent 20%),
    linear-gradient(180deg,rgba(0,0,0,0),rgba(0,0,0,.08)) !important;
}
html body .tb4c-member-area-page .tb4c-member-area-avatar{
  grid-area:avatar !important;
  width:clamp(132px,12.8vw,170px) !important;
  height:clamp(132px,12.8vw,170px) !important;
  margin:clamp(-86px,-6vw,-62px) 0 0 clamp(18px,2vw,30px) !important;
  border:7px solid #fff !important;
  border-radius:999px !important;
  background:#fff !important;
  box-shadow:0 14px 38px rgba(15,81,50,.20) !important;
  z-index:3 !important;
}
html body .tb4c-member-area-page .tb4c-member-camera-badge,
html body .tb4c-member-area-page .tb4c-member-area-avatar-sync-badge{
  width:38px !important;
  height:38px !important;
  right:4px !important;
  bottom:10px !important;
  background:var(--tb4m-brand-green) !important;
  border:3px solid #fff !important;
}
html body .tb4c-member-area-page .tb4c-member-area-title{
  grid-area:title !important;
  align-self:center !important;
  padding:0 0 0 0 !important;
}
html body .tb4c-member-area-page .tb4c-member-title-line h1{
  color:var(--tb4m-brand-black) !important;
  font-size:clamp(34px,3.2vw,54px) !important;
  line-height:1 !important;
  font-weight:950 !important;
  letter-spacing:-.055em !important;
}
html body .tb4c-member-area-page .tb4c-member-verified{
  background:var(--tb4m-brand-green) !important;
  box-shadow:0 8px 22px rgba(30,107,69,.22) !important;
}
html body .tb4c-member-area-page .tb4c-member-handle,
html body .tb4c-member-area-page .tb4c-member-area-bio-line{
  color:var(--tb4m-brand-muted) !important;
  max-width:760px !important;
}
html body .tb4c-member-area-page .tb4c-member-area-hero-tags span{
  border:1px solid rgba(30,107,69,.12) !important;
  background:var(--tb4m-brand-green-soft) !important;
  color:var(--tb4m-brand-green) !important;
}
html body .tb4c-member-area-page .tb4c-member-area-actions{
  grid-area:actions !important;
  align-self:center !important;
  justify-self:end !important;
  display:grid !important;
  grid-template-columns:1fr !important;
  min-width:190px !important;
  gap:8px !important;
  padding:0 clamp(18px,2vw,30px) 0 0 !important;
}
html body .tb4c-member-area-page .tb4c-member-area-actions .tb4c-btn,
html body .tb4c-member-area-page .tb4c-member-area-actions a,
html body .tb4c-member-area-page .tb4c-member-area-actions button{
  width:100% !important;
  justify-content:center !important;
  min-height:42px !important;
  height:42px !important;
  border-radius:14px !important;
  border:1px solid rgba(30,107,69,.14) !important;
  background:#fff !important;
  color:var(--tb4m-brand-black) !important;
  font-weight:850 !important;
  box-shadow:0 8px 20px rgba(17,17,17,.035) !important;
}
html body .tb4c-member-area-page .tb4c-member-area-actions .tb4c-btn-primary,
html body .tb4c-member-area-page .tb4c-member-composer-trigger{
  background:linear-gradient(135deg,var(--tb4m-brand-green),var(--tb4m-brand-green-2)) !important;
  border-color:transparent !important;
  color:#fff !important;
}

/* Metrics: one premium white strip, not scattered cards. */
html body .tb4c-member-area-page .tb4c-member-showcase-stats{
  display:grid !important;
  grid-template-columns:repeat(5,minmax(0,1fr)) minmax(220px,.95fr) !important;
  gap:0 !important;
  margin:0 0 12px !important;
  padding:10px !important;
  border:1px solid rgba(30,107,69,.10) !important;
  border-radius:24px !important;
  background:rgba(255,255,255,.90) !important;
  box-shadow:0 10px 30px rgba(15,81,50,.06) !important;
  overflow:hidden !important;
}
html body .tb4c-member-area-page .tb4c-member-showcase-stat{
  min-height:68px !important;
  padding:8px 10px !important;
  border-right:1px solid rgba(17,17,17,.07) !important;
  text-align:center !important;
}
html body .tb4c-member-area-page .tb4c-member-showcase-stat strong{
  color:var(--tb4m-brand-black) !important;
  font-size:clamp(22px,2.1vw,31px) !important;
}
html body .tb4c-member-area-page .tb4c-member-showcase-stat:nth-child(2) strong{
  color:var(--tb4m-brand-orange) !important;
}
html body .tb4c-member-area-page .tb4c-member-showcase-stat span{
  color:var(--tb4m-brand-muted) !important;
}
html body .tb4c-member-area-page .tb4c-member-showcase-progress{
  min-height:68px !important;
  padding:8px 6px 8px 18px !important;
}
html body .tb4c-member-area-page .tb4c-member-showcase-progress i b{
  background:linear-gradient(90deg,var(--tb4m-brand-green),var(--tb4m-brand-orange)) !important;
}

/* Tabs: clean app-style rail with active underline. */
html body .tb4c-member-area-page .tb4c-member-area-tabs{
  position:sticky !important;
  top:0 !important;
  z-index:32 !important;
  justify-content:center !important;
  gap:4px !important;
  margin:0 0 12px !important;
  padding:7px !important;
  border:1px solid rgba(30,107,69,.10) !important;
  border-radius:20px !important;
  background:rgba(255,255,255,.92) !important;
  box-shadow:0 8px 24px rgba(15,81,50,.055) !important;
  backdrop-filter:saturate(140%) blur(12px) !important;
}
html body .tb4c-member-area-page .tb4c-member-area-tabs a{
  min-width:112px !important;
  min-height:42px !important;
  border-radius:14px !important;
  color:#34443A !important;
}
html body .tb4c-member-area-page .tb4c-member-area-tabs a.is-active,
html body .tb4c-member-area-page .tb4c-member-area-tabs a[aria-current="page"]{
  background:var(--tb4m-brand-green-soft) !important;
  color:var(--tb4m-brand-green) !important;
}
html body .tb4c-member-area-page .tb4c-member-area-tabs a.is-active::after,
html body .tb4c-member-area-page .tb4c-member-area-tabs a[aria-current="page"]::after{
  left:18px !important;
  right:18px !important;
  bottom:3px !important;
  height:2px !important;
  background:var(--tb4m-brand-orange) !important;
}

/* Feed: use the width better. Desktop 4 columns, tablet 3, phone 3 compact like IG. */
html body .tb4c-member-area-page .tb4c-member-area-panel-head{
  margin:0 0 12px !important;
  padding:0 2px !important;
}
html body .tb4c-member-area-page .tb4c-member-area-panel-head h2{
  color:var(--tb4m-brand-black) !important;
  font-size:20px !important;
  font-weight:950 !important;
  letter-spacing:-.03em !important;
}
html body .tb4c-member-area-page .tb4c-member-area-panel-head a{
  color:var(--tb4m-brand-green) !important;
}
html body .tb4c-member-area-page .tb4c-feed-list.tb4c-member-own-feed-list,
html body .tb4c-member-area-page .tb4c-member-area-list,
html body .tb4c-member-area-page .tb4c-member-overview-own-feed-list{
  grid-template-columns:repeat(4,minmax(0,1fr)) !important;
  gap:10px !important;
}
html body .tb4c-member-area-page .tb4c-feed-list.tb4c-member-own-feed-list > article,
html body .tb4c-member-area-page .tb4c-member-area-list > article,
html body .tb4c-member-area-page .tb4c-member-own-feed-card,
html body .tb4c-member-area-page .tb4c-post-card.tb4c-member-own-feed-card,
html body .tb4c-member-area-page .tb4c-member-area-list-card{
  border-radius:18px !important;
  background:#EAF6EF !important;
  box-shadow:none !important;
}
html body .tb4c-member-area-page .tb4c-feed-list.tb4c-member-own-feed-list > article:hover,
html body .tb4c-member-area-page .tb4c-member-area-list > article:hover{
  transform:translateY(-2px) !important;
  box-shadow:0 16px 36px rgba(15,81,50,.12) !important;
}
html body .tb4c-member-area-page .tb4c-member-own-feed-card.tb4c-text-only-post::before{
  background:
    radial-gradient(circle at 82% 16%,rgba(249,115,22,.20),transparent 30%),
    linear-gradient(135deg,#0F5132 0%,#1E6B45 60%,#2E8D5D 100%) !important;
}
html body .tb4c-member-area-page .tb4c-member-own-feed-card .tb4c-post-inline-text{
  background:linear-gradient(180deg,rgba(0,0,0,0),rgba(0,0,0,.66)) !important;
}
html body .tb4c-member-area-page .tb4c-member-own-feed-card .tb4c-post-inline-text h2{
  font-weight:900 !important;
}

@media (min-width:1440px){
  html body .tb4c-member-area-page .tb4c-container,
  html body .tb4c-member-area-page .tb4c-member-area-container{max-width:1360px !important;}
  html body .tb4c-member-area-page .tb4c-feed-list.tb4c-member-own-feed-list,
  html body .tb4c-member-area-page .tb4c-member-area-list,
  html body .tb4c-member-area-page .tb4c-member-overview-own-feed-list{grid-template-columns:repeat(5,minmax(0,1fr)) !important;}
}
@media (max-width:1100px){
  html body .tb4c-member-area-page .tb4c-member-area-hero{
    grid-template-columns:132px minmax(0,1fr) !important;
    grid-template-areas:"cover cover" "avatar title" "actions actions" !important;
  }
  html body .tb4c-member-area-page .tb4c-member-area-actions{
    justify-self:stretch !important;
    grid-template-columns:repeat(3,minmax(0,1fr)) !important;
    padding:0 18px !important;
  }
  html body .tb4c-member-area-page .tb4c-member-showcase-stats{grid-template-columns:repeat(5,minmax(0,1fr)) !important;}
  html body .tb4c-member-area-page .tb4c-member-showcase-progress{grid-column:1/-1 !important;padding:10px 8px 0 !important;}
  html body .tb4c-member-area-page .tb4c-feed-list.tb4c-member-own-feed-list,
  html body .tb4c-member-area-page .tb4c-member-area-list,
  html body .tb4c-member-area-page .tb4c-member-overview-own-feed-list{grid-template-columns:repeat(3,minmax(0,1fr)) !important;gap:8px !important;}
}
@media (max-width:767px){
  html body .tb4c-member-area-page,
  html body .tb4c-member-responsive-standard-page{padding:10px 10px 88px !important;}
  html body .tb4c-member-area-page .tb4c-member-area-hero{
    grid-template-columns:92px minmax(0,1fr) !important;
    gap:10px 14px !important;
    padding-bottom:12px !important;
    border-radius:22px !important;
  }
  html body .tb4c-member-area-page .tb4c-member-area-hero-bg{height:126px !important;}
  html body .tb4c-member-area-page .tb4c-member-area-avatar{
    width:92px !important;
    height:92px !important;
    margin:-48px 0 0 12px !important;
    border-width:4px !important;
  }
  html body .tb4c-member-area-page .tb4c-member-camera-badge,
  html body .tb4c-member-area-page .tb4c-member-area-avatar-sync-badge{width:30px !important;height:30px !important;bottom:2px !important;right:0 !important;}
  html body .tb4c-member-area-page .tb4c-member-title-line h1{font-size:28px !important;}
  html body .tb4c-member-area-page .tb4c-member-area-bio-line{font-size:12.5px !important;-webkit-line-clamp:2 !important;}
  html body .tb4c-member-area-page .tb4c-member-area-hero-tags span{font-size:11px !important;min-height:26px !important;}
  html body .tb4c-member-area-page .tb4c-member-area-actions{grid-template-columns:1fr 1fr !important;padding:0 12px !important;}
  html body .tb4c-member-area-page .tb4c-member-composer-trigger{grid-column:1/-1 !important;}
  html body .tb4c-member-area-page .tb4c-member-showcase-stats{
    border-radius:18px !important;
    padding:6px !important;
    grid-template-columns:repeat(5,minmax(0,1fr)) !important;
  }
  html body .tb4c-member-area-page .tb4c-member-showcase-stat{min-height:48px !important;padding:4px 2px !important;}
  html body .tb4c-member-area-page .tb4c-member-showcase-stat strong{font-size:18px !important;}
  html body .tb4c-member-area-page .tb4c-member-showcase-stat span{font-size:10px !important;}
  html body .tb4c-member-area-page .tb4c-member-showcase-progress{display:none !important;}
  html body .tb4c-member-area-page .tb4c-member-area-tabs{
    justify-content:flex-start !important;
    margin:0 -10px 10px !important;
    border-left:0 !important;
    border-right:0 !important;
    border-radius:0 !important;
    padding:5px 8px !important;
  }
  html body .tb4c-member-area-page .tb4c-member-area-tabs a{min-width:auto !important;min-height:42px !important;padding:0 11px !important;font-size:12px !important;}
  html body .tb4c-member-area-page .tb4c-feed-list.tb4c-member-own-feed-list,
  html body .tb4c-member-area-page .tb4c-member-area-list,
  html body .tb4c-member-area-page .tb4c-member-overview-own-feed-list{grid-template-columns:repeat(3,minmax(0,1fr)) !important;gap:4px !important;}
  html body .tb4c-member-area-page .tb4c-feed-list.tb4c-member-own-feed-list > article,
  html body .tb4c-member-area-page .tb4c-member-area-list > article,
  html body .tb4c-member-area-page .tb4c-member-own-feed-card,
  html body .tb4c-member-area-page .tb4c-member-area-list-card{border-radius:6px !important;}
  html body .tb4c-member-area-page .tb4c-member-own-feed-card .tb4c-post-inline-text{display:none !important;}
}
@media (max-width:390px){
  html body .tb4c-member-area-page .tb4c-member-showcase-stat:nth-child(5){display:none !important;}
  html body .tb4c-member-area-page .tb4c-member-showcase-stats{grid-template-columns:repeat(4,minmax(0,1fr)) !important;}
}
</style>
    <?php
}
add_action( 'wp_head', 'tb4cm_print_member_area_thinkb4do_space_css_v4245', 100001 );


function tb4cm_defer_community_script( $tag, $handle, $src ) {
    if ( ! in_array( $handle, [ 'thinkb4do-community', 'thinkb4do-community-legacy', 'thinkb4do-community-runtime' ], true ) ) {
        return $tag;
    }
    if ( false !== strpos( $tag, ' defer' ) ) {
        return $tag;
    }
    return str_replace( ' src=', ' defer src=', $tag );
}
add_filter( 'script_loader_tag', 'tb4cm_defer_community_script', 10, 3 );

function tb4cm_defer_icon_font_style_42114( $html, $handle, $href, $media ) {
    if ( 'tb4-icons' !== $handle ) {
        return $html;
    }

    $href = esc_url( $href );
    return '<link rel="preload" as="style" href="' . $href . '" onload="this.onload=null;this.rel=\'stylesheet\'" data-tb4c-v42114-icon-font="async">'
        . '<noscript><link rel="stylesheet" href="' . $href . '"></noscript>';
}
add_filter( 'style_loader_tag', 'tb4cm_defer_icon_font_style_42114', 10, 4 );

// v4.2.126: legacy CSS async-loader removed because the duplicate legacy stylesheet is no longer packaged.

function tb4cm_body_classes( $classes ) {
    $page_id = is_page() ? get_queried_object_id() : 0;
    $template_slug = $page_id ? get_page_template_slug( $page_id ) : '';
    $is_community_page   = is_page( 'community' ) || 'tb4-community-template.php' === $template_slug || 'page-community.php' === $template_slug;
    $is_member_area_page = is_page( 'member-area' );

    if ( $is_community_page || $is_member_area_page || is_singular( 'tb4_community_post' ) ) {
        $classes[] = 'tb4c-active-page';
        $classes[] = 'tb4c-layout-fit-mode';
        $classes[] = 'tb4c-v42126-hard-remove-ui';
        $classes[] = 'tb4c-v42126-ultra-runtime';
        $classes[] = 'tb4c-v42188-complete-decoration-body';
        $classes[] = 'tb4c-v42205-premium-social-ui-body';
        $classes[] = 'tb4c-v42210-instagram-feed-width-body';
        $classes[] = 'tb4c-v42211-flat-card-audience-media-reels-body';
        $classes[] = 'tb4c-v42212-sharp-media-instagram-reels-body';
        $classes[] = 'tb4c-v42213-no-rounded-media-body';
        $classes[] = 'tb4c-v42214-comment-feed-tone-body';
        $classes[] = 'tb4c-v42215-force-square-media-body';
        $classes[] = 'tb4c-v42216-wide-card-body';
        $classes[] = 'tb4c-v42217-reference-width-body';
        $classes[] = 'tb4c-v42218-wider-inner-card-body';
        $classes[] = 'tb4c-v42219-full-media-inner-wide-body';
        $classes[] = 'tb4c-v42220-reference-comment-system-body';
        $classes[] = 'tb4c-v42221-single-card-comments-body';
        $classes[] = 'tb4c-v42222-wide-flex-desk-body';
        $classes[] = 'tb4c-v42223-one-card-comment-body';
        $classes[] = 'tb4c-v42224-remove-nested-comment-card-body';
        $classes[] = 'tb4c-v42225-flat-comment-thread-body';
        $classes[] = 'tb4c-v42226-comment-final-flat-body';
        $classes[] = 'tb4c-v42227-centered-comment-body';
        $classes[] = 'tb4c-v42228-comment-identity-actions-body';
        $classes[] = 'tb4c-v42229-post-comment-action-fix-body';
        $classes[] = 'tb4c-v42230-post-actions-right-no-shadow-body';
        $classes[] = 'tb4c-v42231-main-no-sidebar-body';
        $classes[] = 'tb4c-v42232-main-no-sidebar-size-fix-body';
    }
    if ( $is_member_area_page ) {
        $classes[] = 'tb4c-member-area-page-active';
        $classes[] = 'tb4c-member-area-theme-bridge';
        $classes[] = 'tb4c-v42236-member-profile-preview-body';
        $classes[] = 'tb4c-member-identity-v4255-body';
        $classes[] = 'tb4c-member-identity-v4256-body';
        $classes[] = 'tb4c-member-clean-menu-v4257-body';
        $classes[] = 'tb4c-member-world-clean-v4258-body';
        $classes[] = 'tb4c-member-facebook-style-v4259-body';
        $classes[] = 'tb4c-member-profile-menu-v4260-body';
        $classes[] = 'tb4c-member-clear-member-menu-v4261-body';
        $classes[] = 'tb4c-member-simple-menu-v4262-body';
    }
    if ( $is_community_page ) {
        $classes[] = 'tb4c-community-page';
        $classes[] = 'tb4c-community-no-footer';
    }
    if ( is_front_page() ) {
        $classes[] = 'tb4c-home-menu-feed-page';
        $classes[] = 'tb4c-home-menu-feed-v473';
        $classes[] = 'tb4c-v42126-hard-remove-ui';
        $classes[] = 'tb4c-v42126-ultra-runtime';
        $classes[] = 'tb4c-v42188-complete-decoration-body';
    }
    if ( tb4cm_is_thinkb4do_theme_core() ) {
        $classes[] = 'tb4c-theme-core-fit';
        $classes[] = 'tb4c-theme-modern-v231-fit';
    }
    return $classes;
}
add_filter( 'body_class', 'tb4cm_body_classes' );

function tb4cm_get_current_url() {
    $scheme = is_ssl() ? 'https://' : 'http://';
    $host   = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
    $uri    = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
    return esc_url_raw( $scheme . $host . $uri );
}

/**
 * Theme bridge filters. Theme can stay clean; Community can be enabled/disabled independently.
 */
function tb4cm_insert_after_key( $links, $after_url, $insert ) {
    $output  = [];
    $inserted = false;

    foreach ( (array) $links as $url => $label ) {
        $output[ $url ] = $label;
        if ( trailingslashit( $url ) === trailingslashit( $after_url ) ) {
            foreach ( $insert as $insert_url => $insert_label ) {
                $output[ $insert_url ] = $insert_label;
            }
            $inserted = true;
        }
    }

    if ( ! $inserted ) {
        foreach ( $insert as $insert_url => $insert_label ) {
            $output[ $insert_url ] = $insert_label;
        }
    }

    return $output;
}

function tb4cm_menu_has_url( $links, $target_url ) {
    $target = trailingslashit( strtolower( esc_url_raw( $target_url ) ) );
    foreach ( (array) $links as $url => $label ) {
        if ( is_array( $label ) ) {
            $candidate = $label['url'] ?? $label['href'] ?? $label[0] ?? $url;
        } else {
            $candidate = $url;
        }
        if ( trailingslashit( strtolower( esc_url_raw( $candidate ) ) ) === $target ) {
            return true;
        }
    }
    return false;
}

function tb4cm_restore_community_fallback_links( $links ) {
    $links = is_array( $links ) ? $links : [];
    $community_url = home_url( '/community/' );
    $member_url    = home_url( '/member-area/' );

    if ( tb4cm_menu_has_url( $links, $community_url ) ) {
        return $links;
    }

    $community = [ $community_url => __( 'ชุมชน', 'thinkb4do-community' ) ];
    $output = [];
    $inserted = false;
    $product_url = trailingslashit( home_url( '/products/' ) );

    foreach ( $links as $url => $label ) {
        $url_key = is_string( $url ) ? $url : '';
        if ( is_scalar( $label ) ) {
            $label_text = (string) $label;
        } elseif ( is_array( $label ) ) {
            $label_text = (string) ( $label['label'] ?? $label['title'] ?? $label[2] ?? '' );
        } elseif ( is_object( $label ) ) {
            $label_text = (string) ( $label->label ?? $label->title ?? '' );
        } else {
            $label_text = '';
        }
        $path = strtolower( (string) wp_parse_url( $url_key, PHP_URL_PATH ) );
        $label_lc = function_exists( 'mb_strtolower' ) ? mb_strtolower( $label_text ) : strtolower( $label_text );

        // Put Community before Products if Products already exists in the header menu.
        if ( ! $inserted && ( false !== strpos( $path, 'product' ) || false !== strpos( $label_lc, 'ผลิตภัณฑ์' ) || false !== strpos( $label_lc, 'สินค้า' ) || trailingslashit( $url_key ) === $product_url ) ) {
            foreach ( $community as $community_link => $community_label ) {
                $output[ $community_link ] = $community_label;
            }
            $inserted = true;
        }

        $output[ $url ] = $label;

        // If Discovery is present, place Community right after it.
        if ( ! $inserted && ( false !== strpos( $path, 'discovery' ) || false !== strpos( $label_lc, 'discovery' ) ) ) {
            foreach ( $community as $community_link => $community_label ) {
                $output[ $community_link ] = $community_label;
            }
            $inserted = true;
        }
    }

    if ( ! $inserted ) {
        $output = tb4cm_insert_after_key( $output, home_url( '/system-guide/' ), $community );
    }

    if ( ! tb4cm_menu_has_url( $output, $member_url ) ) {
        $output[ $member_url ] = __( 'พื้นที่สมาชิก', 'thinkb4do-community' );
    }

    return $output;
}

function tb4cm_add_header_links( $links ) {
    // v4.2.142: always restore the Community menu even when Thinkb4do Theme Core controls the header.
    return tb4cm_restore_community_fallback_links( $links );
}
add_filter( 'tb4_header_fallback_links', 'tb4cm_add_header_links', 20 );
add_filter( 'tb4_mobile_fallback_links', 'tb4cm_add_header_links', 20 );

function tb4cm_restore_community_platform_menu_items( $items ) {
    if ( ! is_array( $items ) ) {
        return $items;
    }

    $community_url = home_url( '/community/' );
    foreach ( $items as $item ) {
        $url = '';
        if ( is_array( $item ) ) {
            $url = $item['url'] ?? $item['href'] ?? $item[0] ?? '';
        } elseif ( is_object( $item ) ) {
            $url = $item->url ?? '';
        }
        if ( $url && trailingslashit( strtolower( esc_url_raw( $url ) ) ) === trailingslashit( strtolower( esc_url_raw( $community_url ) ) ) ) {
            return $items;
        }
    }

    $community_item = [
        'url'      => $community_url,
        'href'     => $community_url,
        'icon'     => 'ph-users-three',
        'fallback' => '👥',
        'label'    => __( 'ชุมชน', 'thinkb4do-community' ),
        'title'    => __( 'ชุมชน', 'thinkb4do-community' ),
    ];

    $output = [];
    $inserted = false;
    foreach ( $items as $item ) {
        $label = '';
        $url = '';
        if ( is_array( $item ) ) {
            $label = (string) ( $item['label'] ?? $item['title'] ?? $item[2] ?? '' );
            $url = (string) ( $item['url'] ?? $item['href'] ?? $item[0] ?? '' );
        } elseif ( is_object( $item ) ) {
            $label = (string) ( $item->label ?? $item->title ?? '' );
            $url = (string) ( $item->url ?? '' );
        }
        $path = strtolower( (string) wp_parse_url( $url, PHP_URL_PATH ) );
        $label_lc = function_exists( 'mb_strtolower' ) ? mb_strtolower( $label ) : strtolower( $label );

        $output[] = $item;
        if ( ! $inserted && ( false !== strpos( $path, 'discovery' ) || false !== strpos( $label_lc, 'discovery' ) ) ) {
            $output[] = $community_item;
            $inserted = true;
        }
    }

    if ( ! $inserted ) {
        array_splice( $output, min( 1, count( $output ) ), 0, [ $community_item ] );
    }

    return $output;
}
add_filter( 'tb4_platform_menu_items', 'tb4cm_restore_community_platform_menu_items', 20 );
add_filter( 'tb4_header_menu_items', 'tb4cm_restore_community_platform_menu_items', 20 );
add_filter( 'tb4_primary_menu_items', 'tb4cm_restore_community_platform_menu_items', 20 );

function tb4cm_add_user_menu_item( $items ) {
    array_splice( $items, 1, 0, [
        [ home_url( '/member-area/' ), 'ph-user-circle-gear', __( 'พื้นที่สมาชิก', 'thinkb4do-community' ) ],
    ] );
    return $items;
}
add_filter( 'tb4_user_menu_items', 'tb4cm_add_user_menu_item' );

function tb4cm_add_message_panel_item( $items ) {
    if ( tb4cm_is_thinkb4do_theme_core() ) {
        return $items;
    }

    array_unshift( $items, [
        home_url( '/community/' ),
        '💬',
        __( 'ศูนย์ข้อความ Thinkb4do', 'thinkb4do-community' ),
        __( 'พื้นที่ข้อความสำหรับประกาศ ข่าวสั้น และการติดตามเรื่องที่เปิดเผยได้', 'thinkb4do-community' ),
    ] );
    return $items;
}
add_filter( 'tb4_message_panel_items', 'tb4cm_add_message_panel_item' );

function tb4cm_community_url( $url ) {
    return home_url( '/community/' );
}
add_filter( 'tb4_community_url', 'tb4cm_community_url' );

function tb4cm_add_sidebar_agenda_links( $links ) {
    if ( tb4cm_is_thinkb4do_theme_core() ) {
        return $links;
    }

    $links[] = [ 'url' => home_url( '/member-area/' ), 'icon' => '◎', 'title' => 'พื้นที่สมาชิก', 'desc' => 'ดูโพสต์ ความคิดเห็น และโปรไฟล์ของฉัน' ];
    $links[] = [ 'url' => home_url( '/community/' ), 'icon' => '🎉', 'title' => 'วันเกิด / อัปเดตชุมชน', 'desc' => 'ใช้ติดตามเรื่องน่ายินดีในชุมชน' ];
    return $links;
}
add_filter( 'tb4_sidebar_agenda_links', 'tb4cm_add_sidebar_agenda_links' );

function tb4cm_add_sidebar_tool_links( $links ) {
    if ( tb4cm_is_thinkb4do_theme_core() ) {
        return $links;
    }

    array_unshift( $links, [ 'url' => home_url( '/community/' ), 'icon' => '💬', 'title' => 'ชุมชน' ] );
    return $links;
}
add_filter( 'tb4_sidebar_tool_links', 'tb4cm_add_sidebar_tool_links' );

function tb4cm_add_search_post_type( $post_types ) {
    $post_types[] = 'tb4_community_post';
    return array_values( array_unique( $post_types ) );
}
add_filter( 'tb4_search_post_types', 'tb4cm_add_search_post_type' );

/**
 * Page sync blueprints for Thinkb4do Theme Core.
 */
function tb4cm_page_sync_blueprints( $blueprints ) {
    $blueprints['member-area'] = [
        'title'      => 'พื้นที่สมาชิก',
        'slug'       => 'member-area',
        'menu_label' => 'พื้นที่สมาชิก',
        'template'   => '',
        'group'      => 'community',
        'content'    => '<!-- wp:shortcode -->[thinkb4do_member_area]<!-- /wp:shortcode -->',
    ];

    return $blueprints;
}
add_filter( 'tb4_page_sync_default_blueprints', 'tb4cm_page_sync_blueprints' );

function tb4cm_page_sync_template_is_valid( $valid, $template ) {
    if ( 'tb4-community-template.php' === $template ) {
        return true;
    }
    return $valid;
}

add_filter( 'tb4_page_sync_template_is_valid', 'tb4cm_page_sync_template_is_valid', 10, 2 );

/**
 * v4.2.154 visible UI guard.
 * Keeps the new social layout visible even when old cached stylesheet is still present.
 */
function tb4cm_visible_social_ui_style_42154() {
    static $printed = false;
    if ( $printed ) {
        return '';
    }
    $printed = true;

    return '<style id="tb4c-visible-social-ui-42154">'
        . ':root{--tb4c-v154-green:#1E6B45;--tb4c-v154-orange:#F97316;--tb4c-v154-ink:#111;--tb4c-v154-muted:#66736d;--tb4c-v154-line:rgba(30,107,69,.13);--tb4c-v154-soft:#F4FAF6;--tb4c-v154-card:#fff;}'
        . '.tb4c-shell,.tb4c-member-area-shell,.tb4-home-feed-main,.tb4c-home-menu-feed{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",Arial,"Noto Sans Thai","Kanit",sans-serif!important;}'
        . '.tb4c-v42154-force-social{background:linear-gradient(180deg,#F7FBF8 0%,#fff 46%,#F8FAF8 100%)!important;color:var(--tb4c-v154-ink)!important;}'
        . '.tb4c-v42154-force-social .tb4c-container.tb4c-social-layout{width:min(100%,1220px)!important;margin:0 auto!important;padding:18px 14px 96px!important;display:grid!important;grid-template-columns:92px minmax(0,680px) 300px!important;gap:18px!important;align-items:start!important;}'
        . '.tb4c-v42154-force-social .tb4c-social-left,.tb4c-v42154-force-social .tb4c-social-right{position:sticky!important;top:86px!important;align-self:start!important;}'
        . '.tb4c-v42154-force-social .tb4c-social-feed{min-width:0!important;display:grid!important;gap:14px!important;}'
        . '.tb4c-v42154-topbar{display:grid!important;grid-template-columns:minmax(0,1fr) auto!important;gap:12px!important;align-items:center!important;padding:12px!important;border:1px solid var(--tb4c-v154-line)!important;border-radius:26px!important;background:#fff!important;box-shadow:0 8px 22px rgba(17,24,39,.055)!important;}'
        . '.tb4c-v42154-topbar-title{display:flex!important;align-items:center!important;gap:10px!important;min-width:0!important;}'
        . '.tb4c-v42154-live-dot{width:42px!important;height:42px!important;border-radius:15px!important;display:grid!important;place-items:center!important;background:linear-gradient(135deg,var(--tb4c-v154-green),#319B66)!important;color:#fff!important;font-weight:900!important;box-shadow:0 10px 20px rgba(30,107,69,.16)!important;}'
        . '.tb4c-v42154-topbar-title strong{display:block!important;font-size:1.02rem!important;line-height:1.12!important;letter-spacing:-.02em!important;color:#111!important;}'
        . '.tb4c-v42154-topbar-title span:last-child{display:block!important;font-size:.78rem!important;color:var(--tb4c-v154-muted)!important;margin-top:2px!important;}'
        . '.tb4c-v42154-actions{display:flex!important;align-items:center!important;gap:7px!important;flex-wrap:wrap!important;justify-content:flex-end!important;}'
        . '.tb4c-v42154-actions a,.tb4c-v42154-actions button{min-height:38px!important;border-radius:999px!important;border:1px solid var(--tb4c-v154-line)!important;background:#F8FCF9!important;color:var(--tb4c-v154-green)!important;padding:0 12px!important;font-size:.82rem!important;font-weight:800!important;text-decoration:none!important;display:inline-flex!important;align-items:center!important;gap:6px!important;cursor:pointer!important;}'
        . '.tb4c-v42154-actions .is-primary{background:var(--tb4c-v154-orange)!important;color:#fff!important;border-color:rgba(249,115,22,.2)!important;box-shadow:0 8px 18px rgba(249,115,22,.18)!important;}'
        . '.tb4c-v42154-force-social .tb4c-feed-hero-bar{padding:14px!important;border-radius:26px!important;border:1px solid var(--tb4c-v154-line)!important;background:linear-gradient(135deg,#FFFFFF 0%,#F2FAF5 70%,#FFF5EC 100%)!important;box-shadow:0 8px 20px rgba(30,107,69,.06)!important;}'
        . '.tb4c-v42154-force-social .tb4c-feed-hero-copy h1{font-size:clamp(1.18rem,2.4vw,1.72rem)!important;line-height:1.16!important;margin:8px 0 4px!important;color:#111!important;letter-spacing:-.035em!important;}'
        . '.tb4c-v42154-force-social .tb4c-feed-hero-copy p{font-size:.9rem!important;color:var(--tb4c-v154-muted)!important;margin:0!important;line-height:1.55!important;}'
        . '.tb4c-v42154-force-social .tb4c-feed-eyebrow{display:inline-flex!important;border-radius:999px!important;background:#fff!important;color:var(--tb4c-v154-green)!important;border:1px solid var(--tb4c-v154-line)!important;padding:4px 9px!important;font-size:.74rem!important;font-weight:900!important;}'
        . '.tb4c-v42154-force-social .tb4c-feed-search-mini{display:grid!important;grid-template-columns:minmax(0,1fr) auto!important;gap:8px!important;margin-top:10px!important;padding:6px!important;border-radius:18px!important;border:1px solid var(--tb4c-v154-line)!important;background:#fff!important;box-shadow:none!important;}'
        . '.tb4c-v42154-force-social .tb4c-feed-search-mini input{min-height:40px!important;border:0!important;background:transparent!important;padding:0 8px!important;outline:0!important;font-size:.9rem!important;}'
        . '.tb4c-v42154-force-social .tb4c-feed-search-mini button{min-height:40px!important;border:0!important;border-radius:14px!important;background:var(--tb4c-v154-green)!important;color:#fff!important;font-weight:900!important;padding:0 13px!important;}'
        . '.tb4c-v42154-force-social .tb4c-feed-quick-stats{display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr))!important;gap:8px!important;margin-top:10px!important;}'
        . '.tb4c-v42154-force-social .tb4c-feed-quick-stats span{border:1px solid rgba(30,107,69,.1)!important;background:#fff!important;border-radius:17px!important;padding:9px!important;display:grid!important;gap:2px!important;}'
        . '.tb4c-v42154-force-social .tb4c-feed-quick-stats strong{font-size:1rem!important;color:#111!important;line-height:1!important;}.tb4c-v42154-force-social .tb4c-feed-quick-stats small{font-size:.72rem!important;color:var(--tb4c-v154-muted)!important;}'
        . '.tb4c-v42154-force-social .tb4c-social-brand-rail{min-height:96px!important;border-radius:24px!important;border:1px solid var(--tb4c-v154-line)!important;background:#fff!important;box-shadow:0 8px 18px rgba(17,24,39,.05)!important;display:grid!important;place-items:center!important;text-align:center!important;padding:10px 6px!important;margin-bottom:10px!important;}'
        . '.tb4c-v42154-force-social .tb4c-social-brand-mark{width:44px!important;height:44px!important;border-radius:16px!important;background:linear-gradient(135deg,var(--tb4c-v154-green),#2B8A5A)!important;color:#fff!important;display:grid!important;place-items:center!important;font-weight:900!important;}'
        . '.tb4c-v42154-force-social .tb4c-social-brand-rail strong{font-size:.78rem!important;line-height:1.08!important;}.tb4c-v42154-force-social .tb4c-social-brand-rail small{font-size:.68rem!important;color:var(--tb4c-v154-muted)!important;}'
        . '.tb4c-v42154-force-social .tb4c-social-nav{display:flex!important;flex-direction:column!important;gap:7px!important;padding:8px!important;border-radius:24px!important;border:1px solid var(--tb4c-v154-line)!important;background:#fff!important;box-shadow:0 8px 18px rgba(17,24,39,.05)!important;}'
        . '.tb4c-v42154-force-social .tb4c-social-nav a{min-height:54px!important;border-radius:18px!important;display:flex!important;flex-direction:column!important;justify-content:center!important;align-items:center!important;gap:4px!important;color:var(--tb4c-v154-muted)!important;text-decoration:none!important;border:1px solid transparent!important;background:transparent!important;}'
        . '.tb4c-v42154-force-social .tb4c-social-nav a.is-active,.tb4c-v42154-force-social .tb4c-social-nav a:hover{background:#EAF6EF!important;color:var(--tb4c-v154-green)!important;border-color:rgba(30,107,69,.13)!important;}'
        . '.tb4c-v42154-force-social .tb4c-nav-icon,.tb4c-v42154-force-social .tb4c-social-nav i{font-size:1.08rem!important;line-height:1!important;}.tb4c-v42154-force-social .tb4c-nav-label{font-size:.68rem!important;max-width:66px!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;}'
        . '.tb4c-v42154-force-social .tb4c-composer-card{border-radius:26px!important;border:1px solid var(--tb4c-v154-line)!important;background:#fff!important;box-shadow:0 8px 20px rgba(17,24,39,.055)!important;padding:12px!important;}'
        . '.tb4c-v42154-force-social .tb4c-composer-shell,.tb4c-v42154-force-social [data-tb4c-composer]{border-radius:22px!important;background:#F8FCF9!important;border:1px solid rgba(30,107,69,.1)!important;}'
        . '.tb4c-v42154-force-social .tb4c-rail-shell{border-radius:24px!important;border:1px solid var(--tb4c-v154-line)!important;background:#fff!important;padding:10px!important;box-shadow:0 6px 18px rgba(17,24,39,.045)!important;}'
        . '.tb4c-v42154-force-social .tb4c-story-rail{gap:10px!important;scrollbar-width:none!important;}.tb4c-v42154-force-social .tb4c-story-rail::-webkit-scrollbar{display:none!important;}'
        . '.tb4c-v42154-force-social .tb4c-story-chip{width:92px!important;min-width:92px!important;height:124px!important;border-radius:20px!important;border:1px solid rgba(30,107,69,.12)!important;box-shadow:none!important;background:#F4FAF6!important;overflow:hidden!important;}'
        . '.tb4c-v42154-force-social .tb4c-post-card{border-radius:28px!important;border:1px solid var(--tb4c-v154-line)!important;background:#fff!important;box-shadow:0 10px 24px rgba(17,24,39,.06)!important;padding:14px!important;margin:0!important;overflow:hidden!important;}'
        . '.tb4c-v42154-force-social .tb4c-post-card + .tb4c-post-card{margin-top:14px!important;}.tb4c-v42154-force-social .tb4c-post-head{display:grid!important;grid-template-columns:46px minmax(0,1fr) auto!important;gap:10px!important;align-items:start!important;margin-bottom:10px!important;}'
        . '.tb4c-v42154-force-social .tb4c-avatar{width:46px!important;height:46px!important;border-radius:16px!important;background:#EAF6EF!important;color:var(--tb4c-v154-green)!important;overflow:hidden!important;}'
        . '.tb4c-v42154-force-social .tb4c-post-inline-text h2{font-size:1.08rem!important;line-height:1.28!important;color:#111!important;margin:0 0 5px!important;letter-spacing:-.018em!important;}.tb4c-v42154-force-social .tb4c-post-inline-text p{font-size:.94rem!important;line-height:1.62!important;color:#3F4B46!important;margin:0!important;}'
        . '.tb4c-v42154-force-social .tb4c-post-type-chip,.tb4c-v42154-force-social .tb4c-privacy-chip,.tb4c-v42154-force-social .tb4c-feeling-chip{display:inline-flex!important;width:max-content!important;max-width:100%!important;border-radius:999px!important;padding:4px 8px!important;border:1px solid rgba(30,107,69,.11)!important;background:#F7FBF8!important;color:var(--tb4c-v154-green)!important;font-size:.72rem!important;font-weight:800!important;}'
        . '.tb4c-v42154-force-social .tb4c-tags,.tb4c-v42154-force-social .tb4c-ig-hashtags{display:flex!important;flex-wrap:wrap!important;gap:6px!important;margin:7px 0!important;}.tb4c-v42154-force-social .tb4c-tags a,.tb4c-v42154-force-social .tb4c-ig-hashtags a{border-radius:999px!important;border:1px solid var(--tb4c-v154-line)!important;background:#fff!important;color:var(--tb4c-v154-green)!important;padding:5px 8px!important;font-size:.75rem!important;text-decoration:none!important;}'
        . '.tb4c-v42154-force-social .tb4c-thumb,.tb4c-v42154-force-social .tb4c-ig-media,.tb4c-v42154-force-social .tb4c-post-media-slot,.tb4c-v42154-force-social .tb4c-media-album,.tb4c-v42154-force-social .tb4c-album-grid{border-radius:22px!important;overflow:hidden!important;border:1px solid rgba(17,24,39,.06)!important;background:#F2F7F4!important;}'
        . '.tb4c-v42154-force-social .tb4c-post-actions{display:grid!important;grid-template-columns:repeat(5,minmax(0,1fr)) auto!important;gap:6px!important;margin-top:10px!important;padding:8px!important;border-radius:20px!important;border:1px solid var(--tb4c-v154-line)!important;background:#F7FAF8!important;}'
        . '.tb4c-v42154-force-social .tb4c-post-actions button,.tb4c-v42154-force-social .tb4c-post-actions a,.tb4c-v42154-force-social .tb4c-view-chip{min-height:36px!important;border-radius:14px!important;border:0!important;background:#fff!important;color:#33423B!important;box-shadow:none!important;display:inline-flex!important;align-items:center!important;justify-content:center!important;gap:5px!important;padding:6px 8px!important;font-size:.8rem!important;text-decoration:none!important;}'
        . '.tb4c-v42154-force-social .tb4c-comment-preview-row{margin-top:9px!important;padding-top:9px!important;border-top:1px solid rgba(30,107,69,.1)!important;}.tb4c-v42154-force-social .tb4c-comment-composer-preview{border-radius:18px!important;border:1px solid var(--tb4c-v154-line)!important;background:#F8FCF9!important;min-height:42px!important;color:var(--tb4c-v154-muted)!important;}'
        . '.tb4c-v42154-force-social .tb4c-right-hub-card,.tb4c-v42154-force-social .tb4c-social-card{border-radius:26px!important;border:1px solid var(--tb4c-v154-line)!important;background:#fff!important;box-shadow:0 8px 20px rgba(17,24,39,.05)!important;padding:12px!important;}'
        . '.tb4c-v42154-force-social .tb4c-right-hub-head{border-radius:20px!important;background:linear-gradient(135deg,#F2FAF5,#FFF4EA)!important;border:1px solid rgba(30,107,69,.1)!important;padding:12px!important;}.tb4c-v42154-force-social .tb4c-right-hub-title{font-size:.84rem!important;color:#111!important;margin-bottom:8px!important;display:block!important;}'
        . '.tb4c-v42154-force-social .tb4c-suggest-person{display:grid!important;grid-template-columns:40px minmax(0,1fr) auto!important;gap:8px!important;align-items:center!important;padding:8px!important;border-radius:18px!important;background:#FAFCFA!important;}.tb4c-v42154-force-social .tb4c-suggest-person .tb4c-avatar{width:40px!important;height:40px!important;border-radius:14px!important;}'
        . '.tb4c-v42154-force-social .tb4c-topic-compact-list{display:flex!important;flex-wrap:wrap!important;gap:6px!important;}.tb4c-v42154-force-social .tb4c-topic-link{flex:1 1 46%!important;border-radius:16px!important;border:1px solid var(--tb4c-v154-line)!important;background:#FAFCFA!important;color:var(--tb4c-v154-green)!important;padding:8px!important;text-decoration:none!important;}'
        . '.tb4c-v42154-force-social .tb4c-smart-back-wrap{margin-bottom:0!important;}.tb4c-v42154-force-social .tb4c-smart-back,.tb4c-v42154-force-social .tb4c-back-link{min-height:38px!important;border-radius:999px!important;border:1px solid var(--tb4c-v154-line)!important;background:#fff!important;color:var(--tb4c-v154-green)!important;padding:0 12px!important;box-shadow:0 8px 20px rgba(17,24,39,.06)!important;}'
        . '.tb4c-member-area-shell,.tb4c-single-social,.tb4c-comments-card,.tb4c-modal-panel,.tb4c-composer-popup-panel,.tb4-home-feed-card,.tb4c-home-menu-card{border-radius:26px!important;border:1px solid var(--tb4c-v154-line)!important;background:#fff!important;box-shadow:0 8px 22px rgba(17,24,39,.055)!important;}'
        . '.tb4c-shell input,.tb4c-shell textarea,.tb4c-shell select,.tb4c-member-area-shell input,.tb4c-member-area-shell textarea,.tb4c-member-area-shell select{border-radius:16px!important;border-color:var(--tb4c-v154-line)!important;}'
        . '.tb4c-shell a:focus-visible,.tb4c-shell button:focus-visible,.tb4c-shell input:focus-visible,.tb4c-shell textarea:focus-visible,.tb4c-shell select:focus-visible{outline:3px solid rgba(249,115,22,.26)!important;outline-offset:2px!important;}'
        . '@media(max-width:1180px){.tb4c-v42154-force-social .tb4c-container.tb4c-social-layout{grid-template-columns:82px minmax(0,1fr) 270px!important;gap:12px!important;}}'
        . '@media(max-width:980px){.tb4c-v42154-force-social .tb4c-container.tb4c-social-layout{grid-template-columns:1fr!important;padding:12px 10px 92px!important;}.tb4c-v42154-force-social .tb4c-social-left,.tb4c-v42154-force-social .tb4c-social-right{position:relative!important;top:auto!important;width:100%!important;max-width:680px!important;margin-inline:auto!important;}.tb4c-v42154-force-social .tb4c-social-left{display:grid!important;grid-template-columns:78px minmax(0,1fr)!important;gap:8px!important;}.tb4c-v42154-force-social .tb4c-social-brand-rail{min-height:74px!important;margin:0!important;}.tb4c-v42154-force-social .tb4c-social-brand-rail strong,.tb4c-v42154-force-social .tb4c-social-brand-rail small{display:none!important;}.tb4c-v42154-force-social .tb4c-social-nav{flex-direction:row!important;overflow:auto!important;white-space:nowrap!important;min-height:74px!important;scrollbar-width:none!important;}.tb4c-v42154-force-social .tb4c-social-nav::-webkit-scrollbar{display:none!important;}.tb4c-v42154-force-social .tb4c-social-nav a{min-width:64px!important;}.tb4c-v42154-force-social .tb4c-social-feed{max-width:680px!important;margin-inline:auto!important;width:100%!important;}.tb4c-v42154-force-social .tb4c-right-hub-rules{display:none!important;}}'
        . '@media(max-width:720px){.tb4c-v42154-topbar{grid-template-columns:1fr!important;border-radius:22px!important;}.tb4c-v42154-actions{justify-content:flex-start!important;overflow:auto!important;flex-wrap:nowrap!important;scrollbar-width:none!important;}.tb4c-v42154-actions::-webkit-scrollbar{display:none!important;}.tb4c-v42154-force-social .tb4c-feed-hero-bar{border-radius:22px!important;padding:12px!important;}.tb4c-v42154-force-social .tb4c-feed-search-mini{grid-template-columns:1fr!important;}.tb4c-v42154-force-social .tb4c-feed-quick-stats{grid-template-columns:repeat(3,minmax(0,1fr))!important;}.tb4c-v42154-force-social .tb4c-post-actions{grid-template-columns:repeat(4,minmax(0,1fr))!important;}.tb4c-v42154-force-social .tb4c-view-chip{display:none!important;}.tb4c-v42154-force-social .tb4c-story-chip{width:82px!important;min-width:82px!important;height:112px!important;}.tb4c-v42154-force-social .tb4c-social-left{grid-template-columns:64px minmax(0,1fr)!important;}.tb4c-v42154-force-social .tb4c-social-brand-mark{width:38px!important;height:38px!important;border-radius:14px!important;}}'
        . '@media(max-width:520px){.tb4c-v42154-force-social .tb4c-container.tb4c-social-layout{padding-inline:8px!important;}.tb4c-v42154-force-social .tb4c-post-card{border-radius:24px!important;padding:12px!important;}.tb4c-v42154-force-social .tb4c-post-actions em{display:none!important;}.tb4c-v42154-force-social .tb4c-feed-quick-stats span{padding:8px 6px!important;}.tb4c-v42154-force-social .tb4c-feed-quick-stats strong{font-size:.9rem!important;}.tb4c-v42154-force-social .tb4c-feed-quick-stats small{font-size:.66rem!important;}}'
        . '</style>';
}


/**
 * v4.2.156 Google Plus Clean UI rebuild.
 * Lightweight, cache-resistant Google Plus style stream reset.
 */
function tb4cm_gplus_clean_ui_style_42156() {
    static $printed = false;
    if ( $printed ) {
        return '';
    }
    $printed = true;

    return <<<'HTML'
<style id="tb4c-gplus-clean-ui-42156">
:root{--tb4c-gp56-bg:#f5f6f7;--tb4c-gp56-card:#fff;--tb4c-gp56-line:#e0e3e2;--tb4c-gp56-green:#1E6B45;--tb4c-gp56-soft:#e9f4ee;--tb4c-gp56-orange:#F97316;--tb4c-gp56-ink:#202124;--tb4c-gp56-muted:#5f6368;--tb4c-gp56-shadow:0 1px 2px rgba(60,64,67,.10);--tb4c-gp56-radius:12px;}
.tb4c-v42156-gplus-clean{min-height:100vh!important;background:var(--tb4c-gp56-bg)!important;color:var(--tb4c-gp56-ink)!important;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",Arial,"Noto Sans Thai","Kanit",sans-serif!important;}
.tb4c-v42156-gplus-clean *{box-sizing:border-box!important;filter:none!important;-webkit-backdrop-filter:none!important;backdrop-filter:none!important;}
.tb4c-v42156-gplus-clean .tb4c-container.tb4c-social-layout{width:min(100%,1120px)!important;margin:0 auto!important;padding:14px 12px 92px!important;display:grid!important;grid-template-columns:190px minmax(0,620px) 264px!important;gap:14px!important;align-items:start!important;}
.tb4c-v42156-gplus-clean :where(.tb4c-social-left,.tb4c-social-right){position:sticky!important;top:calc(var(--tb4c-header-offset,64px) + 12px)!important;align-self:start!important;}
.tb4c-v42156-gplus-clean .tb4c-social-feed{display:grid!important;gap:10px!important;max-width:620px!important;width:100%!important;min-width:0!important;}
.tb4c-v42156-gplus-clean :where(.tb4c-gplus-stream-header,.tb4c-composer-card,.tb4c-post-card,.tb4c-social-card,.tb4c-right-hub-card,.tb4c-comments-card,.tb4c-member-card,.tb4c-single-social,.tb4c-modal-panel,.tb4c-composer-popup-panel,.tb4c-form-card){background:var(--tb4c-gp56-card)!important;border:1px solid var(--tb4c-gp56-line)!important;border-radius:var(--tb4c-gp56-radius)!important;box-shadow:var(--tb4c-gp56-shadow)!important;overflow:hidden!important;}
.tb4c-v42156-gplus-clean :where(.tb4c-gplus-stream-header,.tb4c-composer-card,.tb4c-post-card,.tb4c-social-card,.tb4c-right-hub-card){padding:0!important;margin:0!important;}
.tb4c-v42156-gplus-clean :where(.tb4c-v42154-topbar,.tb4c-feed-hero-bar){display:none!important;}
.tb4c-v42156-gplus-clean .tb4c-gplus-stream-header{display:grid!important;gap:0!important;}
.tb4c-v42156-gplus-clean .tb4c-gplus-clean-top{display:grid!important;grid-template-columns:minmax(0,1fr) auto!important;gap:10px!important;align-items:center!important;padding:12px!important;border-bottom:1px solid var(--tb4c-gp56-line)!important;}
.tb4c-v42156-gplus-clean .tb4c-gplus-clean-brand{display:flex!important;align-items:center!important;gap:10px!important;min-width:0!important;}
.tb4c-v42156-gplus-clean .tb4c-gplus-logo{width:40px!important;height:40px!important;border-radius:50%!important;background:var(--tb4c-gp56-green)!important;color:#fff!important;display:grid!important;place-items:center!important;font-weight:900!important;letter-spacing:-.05em!important;box-shadow:none!important;flex:0 0 auto!important;}
.tb4c-v42156-gplus-clean .tb4c-gplus-eyebrow{display:block!important;font-size:.68rem!important;color:var(--tb4c-gp56-green)!important;font-weight:900!important;letter-spacing:.02em!important;text-transform:uppercase!important;line-height:1!important;margin-bottom:2px!important;}
.tb4c-v42156-gplus-clean .tb4c-gplus-header-main h1{margin:0!important;font-size:1.08rem!important;line-height:1.2!important;color:var(--tb4c-gp56-ink)!important;letter-spacing:-.015em!important;}
.tb4c-v42156-gplus-clean .tb4c-gplus-header-main p{margin:2px 0 0!important;font-size:.78rem!important;line-height:1.45!important;color:var(--tb4c-gp56-muted)!important;}
.tb4c-v42156-gplus-clean .tb4c-gplus-clean-action{min-height:34px!important;border-radius:999px!important;border:1px solid var(--tb4c-gp56-line)!important;background:#fff!important;color:var(--tb4c-gp56-green)!important;padding:0 12px!important;text-decoration:none!important;font-size:.78rem!important;font-weight:900!important;display:inline-flex!important;align-items:center!important;gap:6px!important;}
.tb4c-v42156-gplus-clean .tb4c-gplus-search{display:grid!important;grid-template-columns:minmax(0,1fr) 42px!important;gap:8px!important;padding:10px 12px!important;border-bottom:1px solid var(--tb4c-gp56-line)!important;align-items:center!important;}
.tb4c-v42156-gplus-clean .tb4c-gplus-search input{height:38px!important;border-radius:999px!important;border:1px solid var(--tb4c-gp56-line)!important;background:#f8f9fa!important;padding:0 13px!important;box-shadow:none!important;color:var(--tb4c-gp56-ink)!important;font-size:.86rem!important;outline:0!important;}
.tb4c-v42156-gplus-clean .tb4c-gplus-search button{width:42px!important;height:38px!important;border-radius:999px!important;border:0!important;background:var(--tb4c-gp56-green)!important;color:#fff!important;display:grid!important;place-items:center!important;padding:0!important;box-shadow:none!important;}
.tb4c-v42156-gplus-clean .tb4c-gplus-search button span{display:none!important;}
.tb4c-v42156-gplus-clean .tb4c-gplus-tabs{display:flex!important;gap:6px!important;overflow:auto!important;scrollbar-width:none!important;padding:10px 12px!important;border-bottom:1px solid var(--tb4c-gp56-line)!important;}
.tb4c-v42156-gplus-clean .tb4c-gplus-tabs::-webkit-scrollbar{display:none!important;}
.tb4c-v42156-gplus-clean .tb4c-gplus-tabs a{min-height:32px!important;border-radius:999px!important;border:1px solid var(--tb4c-gp56-line)!important;background:#fff!important;color:#3c4043!important;padding:0 10px!important;text-decoration:none!important;font-size:.78rem!important;font-weight:850!important;display:inline-flex!important;align-items:center!important;gap:6px!important;white-space:nowrap!important;}
.tb4c-v42156-gplus-clean .tb4c-gplus-tabs a.is-active{background:var(--tb4c-gp56-soft)!important;border-color:rgba(30,107,69,.20)!important;color:var(--tb4c-gp56-green)!important;}
.tb4c-v42156-gplus-clean .tb4c-gplus-stats{display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr))!important;gap:0!important;}
.tb4c-v42156-gplus-clean .tb4c-gplus-stats span{padding:9px 12px!important;border:0!important;border-right:1px solid var(--tb4c-gp56-line)!important;background:#fff!important;border-radius:0!important;min-width:0!important;}
.tb4c-v42156-gplus-clean .tb4c-gplus-stats span:last-child{border-right:0!important;}
.tb4c-v42156-gplus-clean .tb4c-gplus-stats strong{display:block!important;font-size:.92rem!important;line-height:1.12!important;color:var(--tb4c-gp56-ink)!important;}
.tb4c-v42156-gplus-clean .tb4c-gplus-stats small{display:block!important;margin-top:2px!important;font-size:.66rem!important;color:var(--tb4c-gp56-muted)!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;}
.tb4c-v42156-gplus-clean .tb4c-social-brand-rail{display:flex!important;align-items:center!important;gap:9px!important;padding:10px!important;margin:0 0 8px!important;background:#fff!important;border:1px solid var(--tb4c-gp56-line)!important;border-radius:var(--tb4c-gp56-radius)!important;box-shadow:var(--tb4c-gp56-shadow)!important;min-height:auto!important;}
.tb4c-v42156-gplus-clean .tb4c-social-brand-mark{width:36px!important;height:36px!important;border-radius:50%!important;background:var(--tb4c-gp56-green)!important;color:#fff!important;display:grid!important;place-items:center!important;font-weight:900!important;box-shadow:none!important;}
.tb4c-v42156-gplus-clean .tb4c-social-brand-rail strong{font-size:.88rem!important;line-height:1.1!important;color:var(--tb4c-gp56-ink)!important;}.tb4c-v42156-gplus-clean .tb4c-social-brand-rail small{font-size:.7rem!important;color:var(--tb4c-gp56-muted)!important;}
.tb4c-v42156-gplus-clean .tb4c-social-nav{display:grid!important;gap:1px!important;padding:6px!important;background:#fff!important;border:1px solid var(--tb4c-gp56-line)!important;border-radius:var(--tb4c-gp56-radius)!important;box-shadow:var(--tb4c-gp56-shadow)!important;}
.tb4c-v42156-gplus-clean .tb4c-social-nav a{min-height:36px!important;display:flex!important;align-items:center!important;gap:9px!important;border:0!important;border-radius:999px!important;background:transparent!important;color:#3c4043!important;text-decoration:none!important;padding:0 9px!important;font-size:.83rem!important;font-weight:820!important;box-shadow:none!important;}
.tb4c-v42156-gplus-clean .tb4c-social-nav a:hover{background:#f1f3f4!important;color:var(--tb4c-gp56-green)!important;}.tb4c-v42156-gplus-clean .tb4c-social-nav a.is-active{background:var(--tb4c-gp56-soft)!important;color:var(--tb4c-gp56-green)!important;}
.tb4c-v42156-gplus-clean .tb4c-nav-icon{width:24px!important;height:24px!important;border-radius:50%!important;background:#f1f3f4!important;display:grid!important;place-items:center!important;flex:0 0 auto!important;font-size:.78rem!important;}
.tb4c-v42156-gplus-clean .tb4c-composer-card{padding:10px 12px!important;}.tb4c-v42156-gplus-clean .tb4c-quick-composer{display:grid!important;grid-template-columns:40px minmax(0,1fr) repeat(4,36px)!important;gap:8px!important;align-items:center!important;}
.tb4c-v42156-gplus-clean .tb4c-composer-trigger{height:38px!important;border-radius:999px!important;border:1px solid var(--tb4c-gp56-line)!important;background:#f8f9fa!important;color:var(--tb4c-gp56-muted)!important;justify-content:flex-start!important;text-align:left!important;padding:0 13px!important;box-shadow:none!important;font-size:.86rem!important;}
.tb4c-v42156-gplus-clean .tb4c-mini-action{width:36px!important;height:36px!important;min-height:36px!important;border-radius:50%!important;border:1px solid var(--tb4c-gp56-line)!important;background:#fff!important;color:var(--tb4c-gp56-green)!important;padding:0!important;box-shadow:none!important;display:grid!important;place-items:center!important;}.tb4c-v42156-gplus-clean .tb4c-mini-action span{display:none!important;}
.tb4c-v42156-gplus-clean .tb4c-rail-shell{background:transparent!important;border:0!important;box-shadow:none!important;padding:0!important;margin:0!important;overflow:hidden!important;}.tb4c-v42156-gplus-clean .tb4c-story-rail{gap:7px!important;padding:0 1px!important;overflow:auto!important;scrollbar-width:none!important;}.tb4c-v42156-gplus-clean .tb4c-story-rail::-webkit-scrollbar{display:none!important;}
.tb4c-v42156-gplus-clean .tb4c-story-chip{width:76px!important;min-width:76px!important;height:84px!important;border-radius:12px!important;border:1px solid var(--tb4c-gp56-line)!important;background:#fff!important;box-shadow:var(--tb4c-gp56-shadow)!important;overflow:hidden!important;}.tb4c-v42156-gplus-clean .tb4c-story-cover{min-height:50px!important;height:50px!important;}.tb4c-v42156-gplus-clean .tb4c-story-chip strong{font-size:.7rem!important;line-height:1.1!important;}.tb4c-v42156-gplus-clean .tb4c-story-chip small,.tb4c-v42156-gplus-clean .tb4c-rail-nav{display:none!important;}
.tb4c-v42156-gplus-clean .tb4c-feed-section-line{display:flex!important;align-items:center!important;justify-content:space-between!important;margin:0!important;padding:0 2px!important;color:var(--tb4c-gp56-muted)!important;}.tb4c-v42156-gplus-clean .tb4c-feed-section-line strong{font-size:.85rem!important;color:var(--tb4c-gp56-ink)!important;}.tb4c-v42156-gplus-clean .tb4c-feed-section-line span{font-size:.74rem!important;}
.tb4c-v42156-gplus-clean .tb4c-feed-list{display:grid!important;gap:10px!important;}.tb4c-v42156-gplus-clean .tb4c-post-card{padding:0!important;border-radius:var(--tb4c-gp56-radius)!important;}
.tb4c-v42156-gplus-clean .tb4c-post-head{display:grid!important;grid-template-columns:44px minmax(0,1fr) auto!important;gap:10px!important;align-items:start!important;padding:12px 12px 0!important;margin:0!important;}
.tb4c-v42156-gplus-clean .tb4c-avatar{width:40px!important;height:40px!important;border-radius:50%!important;background:var(--tb4c-gp56-soft)!important;color:var(--tb4c-gp56-green)!important;display:grid!important;place-items:center!important;overflow:hidden!important;box-shadow:none!important;}.tb4c-v42156-gplus-clean .tb4c-post-head .tb4c-avatar{width:44px!important;height:44px!important;}.tb4c-v42156-gplus-clean .tb4c-avatar img{width:100%!important;height:100%!important;object-fit:cover!important;display:block!important;}
.tb4c-v42156-gplus-clean .tb4c-author-row{display:flex!important;align-items:center!important;gap:6px!important;flex-wrap:wrap!important;}.tb4c-v42156-gplus-clean .tb4c-author-row :where(a,strong){font-size:.88rem!important;color:var(--tb4c-gp56-ink)!important;text-decoration:none!important;}.tb4c-v42156-gplus-clean .tb4c-author-row span{font-size:.72rem!important;color:var(--tb4c-gp56-muted)!important;}
.tb4c-v42156-gplus-clean :where(.tb4c-post-type-chip,.tb4c-privacy-chip,.tb4c-feeling-chip){display:inline-flex!important;border-radius:999px!important;border:1px solid var(--tb4c-gp56-line)!important;background:#fafafa!important;color:var(--tb4c-gp56-muted)!important;padding:3px 7px!important;font-size:.66rem!important;font-weight:800!important;box-shadow:none!important;margin-top:4px!important;}
.tb4c-v42156-gplus-clean .tb4c-post-inline-text{display:block!important;padding:9px 12px 0!important;text-decoration:none!important;}.tb4c-v42156-gplus-clean .tb4c-post-inline-text h2{margin:0 0 4px!important;font-size:1rem!important;line-height:1.36!important;color:var(--tb4c-gp56-ink)!important;letter-spacing:-.005em!important;}.tb4c-v42156-gplus-clean .tb4c-post-inline-text p{margin:0!important;font-size:.88rem!important;line-height:1.62!important;color:#3c4043!important;}
.tb4c-v42156-gplus-clean :where(.tb4c-tags,.tb4c-ig-hashtags){display:flex!important;flex-wrap:wrap!important;gap:6px!important;padding:7px 12px 0!important;margin:0!important;}.tb4c-v42156-gplus-clean :where(.tb4c-tags a,.tb4c-ig-hashtags a){background:transparent!important;border:0!important;color:var(--tb4c-gp56-green)!important;padding:0!important;font-size:.76rem!important;text-decoration:none!important;}
.tb4c-v42156-gplus-clean :where(.tb4c-thumb,.tb4c-ig-media,.tb4c-post-media-slot,.tb4c-media-album,.tb4c-album-grid){margin-top:10px!important;border:0!important;border-radius:0!important;background:#f1f3f4!important;overflow:hidden!important;}.tb4c-v42156-gplus-clean :where(.tb4c-thumb img,.tb4c-ig-media img,.tb4c-media-album img,.tb4c-album-grid img){width:100%!important;height:auto!important;display:block!important;object-fit:cover!important;}
.tb4c-v42156-gplus-clean .tb4c-post-actions{display:grid!important;grid-template-columns:repeat(4,minmax(0,1fr)) auto!important;gap:0!important;margin:10px 0 0!important;padding:0!important;border-top:1px solid var(--tb4c-gp56-line)!important;background:#fff!important;border-radius:0!important;}.tb4c-v42156-gplus-clean .tb4c-post-actions :where(button,a,.tb4c-view-chip){min-height:40px!important;border:0!important;border-right:1px solid var(--tb4c-gp56-line)!important;border-radius:0!important;background:#fff!important;color:#5f6368!important;box-shadow:none!important;font-size:.76rem!important;font-weight:820!important;display:flex!important;align-items:center!important;justify-content:center!important;gap:5px!important;text-decoration:none!important;padding:0 7px!important;}.tb4c-v42156-gplus-clean .tb4c-post-actions :where(button:hover,a:hover){background:#f8f9fa!important;color:var(--tb4c-gp56-green)!important;}
.tb4c-v42156-gplus-clean .tb4c-comment-preview-row{display:grid!important;grid-template-columns:minmax(0,1fr) auto!important;gap:8px!important;padding:9px 12px 12px!important;margin:0!important;border-top:1px solid var(--tb4c-gp56-line)!important;background:#fbfcfb!important;}.tb4c-v42156-gplus-clean .tb4c-comment-composer-preview{min-height:36px!important;border-radius:999px!important;border:1px solid var(--tb4c-gp56-line)!important;background:#fff!important;color:var(--tb4c-gp56-muted)!important;text-decoration:none!important;padding:0 10px!important;}.tb4c-v42156-gplus-clean .tb4c-native-build-link{width:36px!important;height:36px!important;border-radius:50%!important;border:1px solid var(--tb4c-gp56-line)!important;background:#fff!important;color:var(--tb4c-gp56-green)!important;display:grid!important;place-items:center!important;text-decoration:none!important;}
.tb4c-v42156-gplus-clean .tb4c-right-hub-card{display:grid!important;gap:0!important;}.tb4c-v42156-gplus-clean .tb4c-right-hub-head{padding:12px!important;border-bottom:1px solid var(--tb4c-gp56-line)!important;background:#fff!important;}.tb4c-v42156-gplus-clean .tb4c-right-hub-head h3{margin:0 0 2px!important;font-size:.92rem!important;color:var(--tb4c-gp56-ink)!important;}.tb4c-v42156-gplus-clean .tb4c-right-hub-head p{margin:0!important;font-size:.72rem!important;color:var(--tb4c-gp56-muted)!important;}.tb4c-v42156-gplus-clean .tb4c-right-hub-head a{display:none!important;}.tb4c-v42156-gplus-clean .tb4c-right-hub-section{padding:10px 12px!important;border-bottom:1px solid var(--tb4c-gp56-line)!important;}.tb4c-v42156-gplus-clean .tb4c-right-hub-title{display:block!important;margin:0 0 6px!important;font-size:.74rem!important;color:var(--tb4c-gp56-muted)!important;text-transform:uppercase!important;letter-spacing:.03em!important;}
.tb4c-v42156-gplus-clean .tb4c-suggest-person{display:grid!important;grid-template-columns:36px minmax(0,1fr) auto!important;gap:8px!important;align-items:center!important;padding:6px 0!important;border-bottom:1px solid #f1f3f4!important;background:transparent!important;border-radius:0!important;}.tb4c-v42156-gplus-clean .tb4c-suggest-person:last-child{border-bottom:0!important;}.tb4c-v42156-gplus-clean .tb4c-suggest-person .tb4c-avatar{width:36px!important;height:36px!important;}.tb4c-v42156-gplus-clean .tb4c-suggest-person strong{font-size:.8rem!important;color:var(--tb4c-gp56-ink)!important;}.tb4c-v42156-gplus-clean .tb4c-suggest-person span{display:block!important;font-size:.68rem!important;color:var(--tb4c-gp56-muted)!important;}
.tb4c-v42156-gplus-clean .tb4c-topic-compact-list{display:grid!important;gap:4px!important;}.tb4c-v42156-gplus-clean .tb4c-topic-link{display:flex!important;justify-content:space-between!important;gap:10px!important;border:0!important;border-bottom:1px solid #f1f3f4!important;border-radius:0!important;background:transparent!important;color:var(--tb4c-gp56-green)!important;text-decoration:none!important;padding:6px 0!important;font-size:.8rem!important;}.tb4c-v42156-gplus-clean .tb4c-right-hub-rules{display:none!important;}
.tb4c-v42156-gplus-clean :where(input,select,textarea){border:1px solid var(--tb4c-gp56-line)!important;border-radius:10px!important;background:#fff!important;box-shadow:none!important;}.tb4c-v42156-gplus-clean :where(.tb4c-btn,.tb4c-button,.tb4c-submit,.tb4c-primary){border-radius:999px!important;box-shadow:none!important;}.tb4c-v42156-gplus-clean :where(.tb4c-modal-panel,.tb4c-composer-popup-panel){border-radius:14px!important;box-shadow:0 12px 38px rgba(60,64,67,.18)!important;}.tb4c-v42156-gplus-clean .tb4c-smart-back-wrap{position:static!important;margin:0 0 8px!important;}.tb4c-v42156-gplus-clean .tb4c-smart-back{min-height:32px!important;border-radius:999px!important;border:1px solid var(--tb4c-gp56-line)!important;background:#fff!important;color:var(--tb4c-gp56-green)!important;box-shadow:var(--tb4c-gp56-shadow)!important;padding:0 11px!important;font-size:.78rem!important;}
.tb4c-v42156-gplus-clean :where(a,button,input,textarea,select):focus-visible{outline:3px solid rgba(30,107,69,.20)!important;outline-offset:2px!important;}.tb4c-v42156-gplus-clean :where(.tb4c-floating-create,.tb4c-app-floating-create){background:var(--tb4c-gp56-orange)!important;color:#fff!important;box-shadow:0 6px 18px rgba(249,115,22,.20)!important;}.tb4c-v42156-gplus-clean :where(.tb4c-mobile-bottom-nav,.tb4c-app-bottom-nav){background:#fff!important;border-top:1px solid var(--tb4c-gp56-line)!important;box-shadow:0 -1px 5px rgba(60,64,67,.10)!important;border-radius:0!important;}
@media(max-width:1100px){.tb4c-v42156-gplus-clean .tb4c-container.tb4c-social-layout{grid-template-columns:72px minmax(0,1fr) 248px!important;gap:12px!important;}.tb4c-v42156-gplus-clean .tb4c-social-brand-rail{justify-content:center!important;padding:8px!important;}.tb4c-v42156-gplus-clean .tb4c-social-brand-rail strong,.tb4c-v42156-gplus-clean .tb4c-social-brand-rail small,.tb4c-v42156-gplus-clean .tb4c-nav-label{display:none!important;}.tb4c-v42156-gplus-clean .tb4c-social-nav a{justify-content:center!important;padding:0!important;}}
@media(max-width:920px){.tb4c-v42156-gplus-clean .tb4c-container.tb4c-social-layout{grid-template-columns:1fr!important;max-width:660px!important;padding:10px 9px 90px!important;}.tb4c-v42156-gplus-clean :where(.tb4c-social-left,.tb4c-social-right){position:relative!important;top:auto!important;width:100%!important;}.tb4c-v42156-gplus-clean .tb4c-social-left{display:grid!important;grid-template-columns:auto minmax(0,1fr)!important;gap:8px!important;}.tb4c-v42156-gplus-clean .tb4c-social-brand-rail{margin:0!important;}.tb4c-v42156-gplus-clean .tb4c-social-nav{display:flex!important;overflow:auto!important;white-space:nowrap!important;scrollbar-width:none!important;}.tb4c-v42156-gplus-clean .tb4c-social-nav::-webkit-scrollbar{display:none!important;}.tb4c-v42156-gplus-clean .tb4c-social-nav a{min-width:56px!important;}.tb4c-v42156-gplus-clean .tb4c-social-feed{max-width:660px!important;}.tb4c-v42156-gplus-clean .tb4c-social-right{display:none!important;}}
@media(max-width:640px){.tb4c-v42156-gplus-clean .tb4c-gplus-clean-top{grid-template-columns:1fr!important;}.tb4c-v42156-gplus-clean .tb4c-gplus-clean-action,.tb4c-v42156-gplus-clean .tb4c-gplus-header-main p{display:none!important;}.tb4c-v42156-gplus-clean .tb4c-gplus-search{grid-template-columns:minmax(0,1fr) 40px!important;padding:9px 10px!important;}.tb4c-v42156-gplus-clean .tb4c-gplus-stats span{padding:8px 9px!important;}.tb4c-v42156-gplus-clean .tb4c-gplus-stats small{font-size:.62rem!important;}.tb4c-v42156-gplus-clean .tb4c-quick-composer{grid-template-columns:38px minmax(0,1fr) repeat(2,36px)!important;}.tb4c-v42156-gplus-clean .tb4c-mini-action:nth-of-type(n+4){display:none!important;}.tb4c-v42156-gplus-clean .tb4c-story-chip{width:70px!important;min-width:70px!important;height:78px!important;}.tb4c-v42156-gplus-clean .tb4c-feed-section-line span{display:none!important;}.tb4c-v42156-gplus-clean .tb4c-post-head{grid-template-columns:42px minmax(0,1fr)!important;padding:10px 10px 0!important;}.tb4c-v42156-gplus-clean .tb4c-post-follow{display:none!important;}.tb4c-v42156-gplus-clean .tb4c-post-inline-text{padding:8px 10px 0!important;}.tb4c-v42156-gplus-clean .tb4c-post-actions{grid-template-columns:repeat(4,minmax(0,1fr))!important;}.tb4c-v42156-gplus-clean .tb4c-post-actions :where(em,.tb4c-view-chip){display:none!important;}.tb4c-v42156-gplus-clean .tb4c-comment-preview-row{padding:8px 10px 10px!important;}.tb4c-v42156-gplus-clean .tb4c-social-left{grid-template-columns:1fr!important;}.tb4c-v42156-gplus-clean .tb4c-social-brand-rail{display:none!important;}}
@media(prefers-reduced-motion:reduce),(hover:none),(pointer:coarse){.tb4c-v42156-gplus-clean *{animation:none!important;transition-duration:.001ms!important;scroll-behavior:auto!important;}}
</style>
HTML;
}


/**
 * v4.2.157 Social Size Lock.
 * Final visible reset for correct social-network proportions: column widths, cards, buttons, media and mobile layout.
 */
function tb4cm_social_size_lock_style_42157() {
    static $printed = false;
    if ( $printed ) {
        return '';
    }
    $printed = true;

    return '<style id="tb4c-social-size-lock-42157">' . <<<'CSS'
:root{--tb4c-s57-green:#1E6B45;--tb4c-s57-orange:#F97316;--tb4c-s57-ink:#202124;--tb4c-s57-muted:#5f6b65;--tb4c-s57-line:#dfe5e1;--tb4c-s57-bg:#f5f7f6;--tb4c-s57-card:#fff;--tb4c-s57-soft:#f8fbf9;--tb4c-s57-shadow:0 1px 2px rgba(60,64,67,.10);--tb4c-s57-radius:14px;--tb4c-s57-feed:640px;--tb4c-s57-left:216px;--tb4c-s57-right:280px;}
.tb4c-v42157-social-size-lock,.tb4c-v42157-social-size-lock *,.tb4c-v42157-social-size-lock *::before,.tb4c-v42157-social-size-lock *::after{box-sizing:border-box!important;}
.tb4c-v42157-social-size-lock{width:100%!important;min-height:100vh!important;margin:0!important;padding:0!important;background:var(--tb4c-s57-bg)!important;color:var(--tb4c-s57-ink)!important;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",Arial,"Noto Sans Thai","Kanit",sans-serif!important;overflow-x:hidden!important;}
.tb4c-v42157-social-size-lock :where(a){color:inherit!important;text-decoration:none!important;}
.tb4c-v42157-social-size-lock :where(button,a,input,textarea,select){font-family:inherit!important;}
.tb4c-v42157-social-size-lock :where(button){cursor:pointer!important;}
.tb4c-v42157-social-size-lock .tb4c-container.tb4c-social-layout{width:min(100%,calc(var(--tb4c-s57-left) + var(--tb4c-s57-feed) + var(--tb4c-s57-right) + 32px))!important;max-width:1168px!important;margin:0 auto!important;padding:16px 12px 92px!important;display:grid!important;grid-template-columns:var(--tb4c-s57-left) minmax(0,var(--tb4c-s57-feed)) var(--tb4c-s57-right)!important;gap:16px!important;align-items:start!important;}
.tb4c-v42157-social-size-lock :where(.tb4c-social-left,.tb4c-social-right){position:sticky!important;top:82px!important;align-self:start!important;width:100%!important;min-width:0!important;max-width:100%!important;}
.tb4c-v42157-social-size-lock .tb4c-social-feed{width:100%!important;max-width:var(--tb4c-s57-feed)!important;min-width:0!important;margin:0!important;display:grid!important;gap:12px!important;}
.tb4c-v42157-social-size-lock :where(.tb4c-social-card,.tb4c-gplus-stream-header,.tb4c-composer-card,.tb4c-rail-shell,.tb4c-post-card,.tb4c-right-hub-card,.tb4c-empty-state,.tb4c-modal-panel,.tb4c-composer-popup-panel){background:var(--tb4c-s57-card)!important;border:1px solid var(--tb4c-s57-line)!important;border-radius:var(--tb4c-s57-radius)!important;box-shadow:var(--tb4c-s57-shadow)!important;overflow:hidden!important;}
/* Left social navigation: real social sidebar size, not random icon/card sizes. */
.tb4c-v42157-social-size-lock .tb4c-social-brand-rail{min-height:72px!important;margin:0 0 10px!important;padding:10px!important;border-radius:var(--tb4c-s57-radius)!important;display:grid!important;grid-template-columns:44px minmax(0,1fr)!important;gap:10px!important;place-items:center start!important;text-align:left!important;background:#fff!important;border:1px solid var(--tb4c-s57-line)!important;box-shadow:var(--tb4c-s57-shadow)!important;}
.tb4c-v42157-social-size-lock .tb4c-social-brand-mark,.tb4c-v42157-social-size-lock .tb4c-gplus-logo{width:44px!important;height:44px!important;min-width:44px!important;border-radius:50%!important;background:var(--tb4c-s57-green)!important;color:#fff!important;display:grid!important;place-items:center!important;font-weight:900!important;font-size:.92rem!important;line-height:1!important;box-shadow:none!important;}
.tb4c-v42157-social-size-lock .tb4c-social-brand-rail strong{font-size:.9rem!important;line-height:1.15!important;color:var(--tb4c-s57-ink)!important;margin:0!important;}
.tb4c-v42157-social-size-lock .tb4c-social-brand-rail small{font-size:.72rem!important;line-height:1.2!important;color:var(--tb4c-s57-muted)!important;margin:0!important;}
.tb4c-v42157-social-size-lock .tb4c-social-nav{display:grid!important;grid-template-columns:1fr!important;gap:2px!important;padding:6px!important;border-radius:var(--tb4c-s57-radius)!important;background:#fff!important;border:1px solid var(--tb4c-s57-line)!important;box-shadow:var(--tb4c-s57-shadow)!important;}
.tb4c-v42157-social-size-lock .tb4c-social-nav a{width:100%!important;min-height:42px!important;height:42px!important;padding:0 10px!important;border-radius:10px!important;display:grid!important;grid-template-columns:26px minmax(0,1fr)!important;gap:8px!important;align-items:center!important;justify-content:start!important;color:var(--tb4c-s57-muted)!important;background:transparent!important;border:0!important;font-size:.86rem!important;font-weight:700!important;line-height:1!important;}
.tb4c-v42157-social-size-lock .tb4c-social-nav a:hover,.tb4c-v42157-social-size-lock .tb4c-social-nav a.is-active{background:#eaf3ee!important;color:var(--tb4c-s57-green)!important;}
.tb4c-v42157-social-size-lock .tb4c-social-nav :where(.tb4c-nav-icon,i,svg){width:24px!important;height:24px!important;display:grid!important;place-items:center!important;font-size:1rem!important;line-height:1!important;}
.tb4c-v42157-social-size-lock .tb4c-nav-label{display:block!important;font-size:.84rem!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;max-width:100%!important;}
/* Header changed from hero to compact social toolbar. */
.tb4c-v42157-social-size-lock .tb4c-gplus-stream-header{padding:10px!important;margin:0!important;display:grid!important;gap:9px!important;background:#fff!important;}
.tb4c-v42157-social-size-lock .tb4c-gplus-clean-top{display:grid!important;grid-template-columns:minmax(0,1fr) auto!important;gap:10px!important;align-items:center!important;min-height:48px!important;}
.tb4c-v42157-social-size-lock .tb4c-gplus-header-main{display:grid!important;grid-template-columns:44px minmax(0,1fr)!important;gap:10px!important;align-items:center!important;min-width:0!important;}
.tb4c-v42157-social-size-lock .tb4c-gplus-header-main h1{font-size:1.06rem!important;line-height:1.18!important;margin:0!important;color:var(--tb4c-s57-ink)!important;letter-spacing:-.01em!important;font-weight:850!important;}
.tb4c-v42157-social-size-lock .tb4c-gplus-header-main p{margin:2px 0 0!important;color:var(--tb4c-s57-muted)!important;font-size:.74rem!important;line-height:1.35!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;}
.tb4c-v42157-social-size-lock .tb4c-gplus-eyebrow{display:none!important;}
.tb4c-v42157-social-size-lock .tb4c-gplus-clean-action{min-height:38px!important;height:38px!important;border-radius:999px!important;border:1px solid var(--tb4c-s57-line)!important;background:var(--tb4c-s57-soft)!important;color:var(--tb4c-s57-green)!important;padding:0 12px!important;display:inline-flex!important;align-items:center!important;gap:6px!important;font-size:.8rem!important;font-weight:800!important;}
.tb4c-v42157-social-size-lock .tb4c-gplus-search{width:100%!important;display:grid!important;grid-template-columns:minmax(0,1fr) 42px!important;gap:6px!important;padding:6px!important;border:1px solid var(--tb4c-s57-line)!important;border-radius:999px!important;background:var(--tb4c-s57-soft)!important;box-shadow:none!important;}
.tb4c-v42157-social-size-lock .tb4c-gplus-search input{height:36px!important;min-height:36px!important;border:0!important;background:transparent!important;border-radius:999px!important;padding:0 10px!important;font-size:.86rem!important;color:var(--tb4c-s57-ink)!important;outline:0!important;box-shadow:none!important;}
.tb4c-v42157-social-size-lock .tb4c-gplus-search button{width:42px!important;height:36px!important;min-height:36px!important;border:0!important;border-radius:999px!important;background:var(--tb4c-s57-green)!important;color:#fff!important;padding:0!important;display:grid!important;place-items:center!important;font-size:0!important;box-shadow:none!important;}
.tb4c-v42157-social-size-lock .tb4c-gplus-search button span{display:none!important;}
.tb4c-v42157-social-size-lock .tb4c-gplus-tabs{display:flex!important;gap:6px!important;overflow:auto!important;white-space:nowrap!important;padding:7px 1px 0!important;margin:0!important;border-top:1px solid #eef1ef!important;scrollbar-width:none!important;}
.tb4c-v42157-social-size-lock .tb4c-gplus-tabs::-webkit-scrollbar{display:none!important;}
.tb4c-v42157-social-size-lock .tb4c-gplus-tabs a{min-height:34px!important;height:34px!important;display:inline-flex!important;align-items:center!important;gap:6px!important;border-radius:999px!important;border:1px solid var(--tb4c-s57-line)!important;padding:0 10px!important;color:var(--tb4c-s57-muted)!important;background:#fff!important;font-size:.78rem!important;font-weight:800!important;}
.tb4c-v42157-social-size-lock .tb4c-gplus-tabs a.is-active{background:#eaf3ee!important;color:var(--tb4c-s57-green)!important;border-color:#cdded4!important;}
.tb4c-v42157-social-size-lock .tb4c-gplus-stats{display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr))!important;gap:6px!important;margin:0!important;}
.tb4c-v42157-social-size-lock .tb4c-gplus-stats span{min-height:44px!important;padding:7px 8px!important;border:1px solid #eef1ef!important;background:#fff!important;border-radius:10px!important;display:grid!important;align-content:center!important;gap:1px!important;}
.tb4c-v42157-social-size-lock .tb4c-gplus-stats strong{font-size:.95rem!important;line-height:1!important;color:var(--tb4c-s57-ink)!important;}
.tb4c-v42157-social-size-lock .tb4c-gplus-stats small{font-size:.66rem!important;line-height:1!important;color:var(--tb4c-s57-muted)!important;}
/* Composer: social-post input proportions. */
.tb4c-v42157-social-size-lock .tb4c-composer-card{padding:10px!important;margin:0!important;background:#fff!important;}
.tb4c-v42157-social-size-lock .tb4c-quick-composer{width:100%!important;display:grid!important;grid-template-columns:44px minmax(0,1fr) repeat(4,42px)!important;gap:8px!important;align-items:center!important;padding:0!important;margin:0!important;background:transparent!important;border:0!important;box-shadow:none!important;min-height:44px!important;}
.tb4c-v42157-social-size-lock .tb4c-avatar,.tb4c-v42157-social-size-lock .tb4c-comment-preview-avatar{width:44px!important;height:44px!important;min-width:44px!important;border-radius:50%!important;background:#e8f1ec!important;color:var(--tb4c-s57-green)!important;display:grid!important;place-items:center!important;overflow:hidden!important;font-weight:900!important;line-height:1!important;}
.tb4c-v42157-social-size-lock .tb4c-avatar img,.tb4c-v42157-social-size-lock .tb4c-comment-preview-avatar img,.tb4c-v42157-social-size-lock .tb4c-social-brand-mark img{width:100%!important;height:100%!important;object-fit:cover!important;display:block!important;}
.tb4c-v42157-social-size-lock .tb4c-composer-trigger{height:42px!important;min-height:42px!important;border-radius:999px!important;border:1px solid var(--tb4c-s57-line)!important;background:var(--tb4c-s57-soft)!important;color:var(--tb4c-s57-muted)!important;padding:0 14px!important;text-align:left!important;font-size:.9rem!important;font-weight:700!important;box-shadow:none!important;overflow:hidden!important;text-overflow:ellipsis!important;white-space:nowrap!important;}
.tb4c-v42157-social-size-lock .tb4c-mini-action{width:42px!important;height:42px!important;min-width:42px!important;min-height:42px!important;border-radius:50%!important;border:1px solid var(--tb4c-s57-line)!important;background:#fff!important;color:var(--tb4c-s57-green)!important;padding:0!important;display:grid!important;place-items:center!important;box-shadow:none!important;font-size:0!important;}
.tb4c-v42157-social-size-lock .tb4c-mini-action span{display:none!important;}
.tb4c-v42157-social-size-lock .tb4c-mini-action :where(i,svg,.tb4c-icon){font-size:1.05rem!important;line-height:1!important;}
/* Reels/story: fixed social strip size, not huge cards. */
.tb4c-v42157-social-size-lock .tb4c-rail-shell{position:relative!important;padding:8px 34px!important;background:#fff!important;min-height:104px!important;}
.tb4c-v42157-social-size-lock .tb4c-story-rail{display:flex!important;gap:8px!important;overflow:auto!important;scrollbar-width:none!important;padding:0!important;margin:0!important;}
.tb4c-v42157-social-size-lock .tb4c-story-rail::-webkit-scrollbar{display:none!important;}
.tb4c-v42157-social-size-lock .tb4c-story-chip{width:78px!important;min-width:78px!important;height:88px!important;border-radius:12px!important;border:1px solid var(--tb4c-s57-line)!important;background:#eef5f1!important;box-shadow:none!important;padding:7px!important;display:grid!important;align-content:end!important;gap:2px!important;overflow:hidden!important;color:#fff!important;text-align:left!important;}
.tb4c-v42157-social-size-lock .tb4c-story-chip strong{font-size:.68rem!important;line-height:1.1!important;max-width:100%!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;text-shadow:0 1px 2px rgba(0,0,0,.38)!important;}
.tb4c-v42157-social-size-lock .tb4c-story-chip small{font-size:.58rem!important;line-height:1.1!important;max-width:100%!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;color:rgba(255,255,255,.86)!important;text-shadow:0 1px 2px rgba(0,0,0,.38)!important;}
.tb4c-v42157-social-size-lock .tb4c-story-ring{width:28px!important;height:28px!important;border-radius:50%!important;border:2px solid #fff!important;background:rgba(30,107,69,.92)!important;color:#fff!important;display:grid!important;place-items:center!important;font-size:.8rem!important;}
.tb4c-v42157-social-size-lock .tb4c-rail-nav{position:absolute!important;top:50%!important;transform:translateY(-50%)!important;width:26px!important;height:38px!important;min-height:38px!important;border-radius:999px!important;border:1px solid var(--tb4c-s57-line)!important;background:#fff!important;color:var(--tb4c-s57-green)!important;box-shadow:var(--tb4c-s57-shadow)!important;padding:0!important;display:grid!important;place-items:center!important;z-index:3!important;}
.tb4c-v42157-social-size-lock .tb4c-rail-nav.is-prev{left:5px!important;}.tb4c-v42157-social-size-lock .tb4c-rail-nav.is-next{right:5px!important;}
.tb4c-v42157-social-size-lock .tb4c-feed-section-line{display:flex!important;align-items:center!important;justify-content:space-between!important;gap:10px!important;min-height:36px!important;margin:0!important;padding:0 2px!important;color:var(--tb4c-s57-muted)!important;}
.tb4c-v42157-social-size-lock .tb4c-feed-section-line strong{font-size:.92rem!important;color:var(--tb4c-s57-ink)!important;}
.tb4c-v42157-social-size-lock .tb4c-feed-section-line span{font-size:.72rem!important;}
/* Post cards: classic social feed proportions. */
.tb4c-v42157-social-size-lock .tb4c-feed-list{display:grid!important;gap:12px!important;margin:0!important;width:100%!important;}
.tb4c-v42157-social-size-lock .tb4c-post-card{width:100%!important;max-width:100%!important;margin:0!important;padding:0!important;border-radius:var(--tb4c-s57-radius)!important;background:#fff!important;box-shadow:var(--tb4c-s57-shadow)!important;overflow:hidden!important;}
.tb4c-v42157-social-size-lock .tb4c-pin{margin:0!important;padding:8px 12px!important;border-bottom:1px solid var(--tb4c-s57-line)!important;background:#fff7ed!important;color:#b45309!important;font-size:.78rem!important;font-weight:800!important;}
.tb4c-v42157-social-size-lock .tb4c-post-head{display:grid!important;grid-template-columns:44px minmax(0,1fr) auto!important;gap:10px!important;align-items:center!important;padding:12px 12px 8px!important;margin:0!important;min-height:64px!important;}
.tb4c-v42157-social-size-lock .tb4c-post-meta{min-width:0!important;display:grid!important;gap:4px!important;align-content:center!important;}
.tb4c-v42157-social-size-lock .tb4c-author-line{min-width:0!important;display:flex!important;align-items:center!important;justify-content:space-between!important;gap:8px!important;}
.tb4c-v42157-social-size-lock .tb4c-author-row{min-width:0!important;display:flex!important;align-items:center!important;gap:5px!important;white-space:nowrap!important;overflow:hidden!important;color:var(--tb4c-s57-muted)!important;font-size:.78rem!important;line-height:1.2!important;}
.tb4c-v42157-social-size-lock .tb4c-author-row strong{font-size:.9rem!important;color:var(--tb4c-s57-ink)!important;font-weight:850!important;}
.tb4c-v42157-social-size-lock :where(.tb4c-post-type-chip,.tb4c-privacy-chip,.tb4c-feeling-chip){display:inline-flex!important;width:max-content!important;max-width:100%!important;height:22px!important;min-height:22px!important;align-items:center!important;gap:4px!important;border-radius:999px!important;border:1px solid var(--tb4c-s57-line)!important;background:var(--tb4c-s57-soft)!important;color:var(--tb4c-s57-muted)!important;padding:0 8px!important;font-size:.66rem!important;font-weight:800!important;line-height:1!important;}
.tb4c-v42157-social-size-lock .tb4c-post-follow{display:flex!important;align-items:center!important;justify-content:flex-end!important;}
.tb4c-v42157-social-size-lock .tb4c-post-follow :where(button,a){height:32px!important;min-height:32px!important;border-radius:999px!important;padding:0 10px!important;font-size:.72rem!important;}
.tb4c-v42157-social-size-lock .tb4c-feed-owner-tools{display:flex!important;gap:4px!important;}
.tb4c-v42157-social-size-lock .tb4c-feed-owner-tools button{height:28px!important;min-height:28px!important;border-radius:999px!important;padding:0 8px!important;font-size:.7rem!important;background:#fff!important;border:1px solid var(--tb4c-s57-line)!important;color:var(--tb4c-s57-muted)!important;box-shadow:none!important;}
.tb4c-v42157-social-size-lock .tb4c-post-inline-text{display:block!important;padding:0 12px 10px!important;color:var(--tb4c-s57-ink)!important;}
.tb4c-v42157-social-size-lock .tb4c-post-inline-text h2{font-size:1.02rem!important;line-height:1.35!important;margin:0 0 5px!important;color:var(--tb4c-s57-ink)!important;letter-spacing:-.01em!important;font-weight:850!important;}
.tb4c-v42157-social-size-lock .tb4c-post-inline-text p{font-size:.91rem!important;line-height:1.58!important;margin:0!important;color:#3f4944!important;}
.tb4c-v42157-social-size-lock .tb4c-tags{display:flex!important;gap:5px!important;flex-wrap:wrap!important;padding:0 12px 10px!important;margin:0!important;}
.tb4c-v42157-social-size-lock .tb4c-tags a{height:24px!important;display:inline-flex!important;align-items:center!important;border-radius:999px!important;background:#eef6f1!important;color:var(--tb4c-s57-green)!important;padding:0 8px!important;font-size:.72rem!important;font-weight:800!important;}
.tb4c-v42157-social-size-lock :where(.tb4c-thumb,.tb4c-post-media-slot,.tb4c-featured-lightbox-trigger,.tb4c-media-album,.tb4c-album-grid,.tb4c-post-gallery,.tb4c-ig-media){width:100%!important;max-width:100%!important;margin:0!important;border-radius:0!important;border:0!important;background:#f1f3f2!important;box-shadow:none!important;overflow:hidden!important;display:block!important;}
.tb4c-v42157-social-size-lock :where(.tb4c-thumb img,.tb4c-post-media-slot img,.tb4c-featured-lightbox-trigger img,.tb4c-media-album img,.tb4c-album-grid img,.tb4c-post-gallery img,.tb4c-ig-media img){width:100%!important;height:auto!important;max-height:520px!important;object-fit:cover!important;display:block!important;margin:0!important;border-radius:0!important;}
.tb4c-v42157-social-size-lock :where(.tb4c-media-album video,.tb4c-post-media-slot video,.tb4c-ig-media video){width:100%!important;height:auto!important;max-height:520px!important;object-fit:contain!important;background:#000!important;display:block!important;}
.tb4c-v42157-social-size-lock .tb4c-post-actions{display:grid!important;grid-template-columns:repeat(5,minmax(0,1fr))!important;gap:0!important;align-items:stretch!important;min-height:44px!important;padding:0!important;margin:0!important;border-top:1px solid var(--tb4c-s57-line)!important;border-bottom:1px solid var(--tb4c-s57-line)!important;background:#fff!important;}
.tb4c-v42157-social-size-lock .tb4c-post-actions :where(button,a,span){height:44px!important;min-height:44px!important;border:0!important;border-right:1px solid #eef1ef!important;border-radius:0!important;background:#fff!important;color:var(--tb4c-s57-muted)!important;display:flex!important;align-items:center!important;justify-content:center!important;gap:5px!important;padding:0 6px!important;font-size:.78rem!important;font-weight:800!important;line-height:1!important;box-shadow:none!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;}
.tb4c-v42157-social-size-lock .tb4c-post-actions :where(button,a,span):last-child{border-right:0!important;}
.tb4c-v42157-social-size-lock .tb4c-post-actions :where(i,svg,.tb4c-icon){font-size:1rem!important;line-height:1!important;}
.tb4c-v42157-social-size-lock .tb4c-post-actions em{font-style:normal!important;font-size:.72rem!important;}
.tb4c-v42157-social-size-lock .tb4c-comment-preview-row{display:grid!important;grid-template-columns:minmax(0,1fr) 42px!important;gap:8px!important;align-items:center!important;padding:9px 12px!important;margin:0!important;background:#fff!important;}
.tb4c-v42157-social-size-lock .tb4c-comment-composer-preview{height:38px!important;display:grid!important;grid-template-columns:32px minmax(0,1fr)!important;gap:8px!important;align-items:center!important;border-radius:999px!important;background:var(--tb4c-s57-soft)!important;color:var(--tb4c-s57-muted)!important;padding:3px 10px 3px 3px!important;font-size:.82rem!important;font-weight:700!important;min-width:0!important;}
.tb4c-v42157-social-size-lock .tb4c-comment-preview-avatar{width:32px!important;height:32px!important;min-width:32px!important;font-size:.8rem!important;}
.tb4c-v42157-social-size-lock .tb4c-comment-preview-copy{white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;min-width:0!important;}
.tb4c-v42157-social-size-lock .tb4c-native-build-link{width:42px!important;height:38px!important;min-height:38px!important;border-radius:999px!important;border:1px solid var(--tb4c-s57-line)!important;background:#fff!important;color:var(--tb4c-s57-green)!important;display:flex!important;align-items:center!important;justify-content:center!important;gap:3px!important;font-size:.72rem!important;}
/* Right rail: small, usable, not larger than feed. */
.tb4c-v42157-social-size-lock .tb4c-right-hub-card{display:grid!important;gap:0!important;background:#fff!important;}
.tb4c-v42157-social-size-lock .tb4c-right-hub-head{padding:12px!important;border-bottom:1px solid var(--tb4c-s57-line)!important;background:#fff!important;}
.tb4c-v42157-social-size-lock .tb4c-right-hub-head h3{font-size:.92rem!important;line-height:1.2!important;margin:0 0 2px!important;color:var(--tb4c-s57-ink)!important;}
.tb4c-v42157-social-size-lock .tb4c-right-hub-head p{font-size:.72rem!important;line-height:1.35!important;margin:0!important;color:var(--tb4c-s57-muted)!important;}
.tb4c-v42157-social-size-lock .tb4c-right-hub-section{padding:10px 12px!important;border-bottom:1px solid var(--tb4c-s57-line)!important;}
.tb4c-v42157-social-size-lock .tb4c-right-hub-title{font-size:.72rem!important;color:var(--tb4c-s57-muted)!important;text-transform:uppercase!important;letter-spacing:.04em!important;margin:0 0 7px!important;display:block!important;}
.tb4c-v42157-social-size-lock .tb4c-suggest-person{display:grid!important;grid-template-columns:34px minmax(0,1fr) auto!important;gap:8px!important;align-items:center!important;min-height:44px!important;padding:6px 0!important;border-bottom:1px solid #eef1ef!important;background:transparent!important;border-radius:0!important;}
.tb4c-v42157-social-size-lock .tb4c-suggest-person .tb4c-avatar{width:34px!important;height:34px!important;min-width:34px!important;}
.tb4c-v42157-social-size-lock .tb4c-suggest-person strong{font-size:.78rem!important;line-height:1.15!important;color:var(--tb4c-s57-ink)!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;display:block!important;max-width:112px!important;}
.tb4c-v42157-social-size-lock .tb4c-suggest-person span{font-size:.66rem!important;color:var(--tb4c-s57-muted)!important;display:block!important;}
.tb4c-v42157-social-size-lock .tb4c-topic-link{min-height:34px!important;display:flex!important;align-items:center!important;justify-content:space-between!important;gap:8px!important;padding:6px 0!important;border:0!important;border-bottom:1px solid #eef1ef!important;border-radius:0!important;background:transparent!important;color:var(--tb4c-s57-green)!important;font-size:.78rem!important;font-weight:800!important;}
.tb4c-v42157-social-size-lock .tb4c-right-hub-rules{padding:10px 12px!important;background:#fbfdfc!important;color:var(--tb4c-s57-muted)!important;font-size:.72rem!important;line-height:1.4!important;}
/* Forms/modal/member/single pages inherit the same sizing language. */
.tb4c-v42157-social-size-lock :where(input,select,textarea){min-height:40px!important;border:1px solid var(--tb4c-s57-line)!important;border-radius:10px!important;background:#fff!important;color:var(--tb4c-s57-ink)!important;box-shadow:none!important;outline:0!important;}
.tb4c-v42157-social-size-lock :where(textarea){line-height:1.5!important;padding:10px!important;}
.tb4c-v42157-social-size-lock :where(.tb4c-btn,.tb4c-button,.tb4c-submit,.tb4c-primary){min-height:38px!important;border-radius:999px!important;padding:0 14px!important;font-size:.84rem!important;font-weight:850!important;box-shadow:none!important;}
.tb4c-v42157-social-size-lock .tb4c-smart-back-wrap{position:static!important;margin:0 0 8px!important;}
.tb4c-v42157-social-size-lock .tb4c-smart-back{min-height:34px!important;border-radius:999px!important;border:1px solid var(--tb4c-s57-line)!important;background:#fff!important;color:var(--tb4c-s57-green)!important;padding:0 12px!important;font-size:.78rem!important;font-weight:850!important;box-shadow:var(--tb4c-s57-shadow)!important;}
.tb4c-v42157-social-size-lock :where(a,button,input,textarea,select):focus-visible{outline:3px solid rgba(30,107,69,.20)!important;outline-offset:2px!important;}
.tb4c-v42157-social-size-lock :where(.tb4c-floating-create,.tb4c-app-floating-create){position:fixed!important;right:18px!important;bottom:86px!important;width:54px!important;height:54px!important;min-width:54px!important;min-height:54px!important;border-radius:50%!important;background:var(--tb4c-s57-orange)!important;color:#fff!important;box-shadow:0 8px 18px rgba(249,115,22,.22)!important;border:0!important;display:grid!important;place-items:center!important;z-index:999!important;}
.tb4c-v42157-social-size-lock :where(.tb4c-mobile-bottom-nav,.tb4c-app-bottom-nav){height:62px!important;background:#fff!important;border-top:1px solid var(--tb4c-s57-line)!important;box-shadow:0 -1px 5px rgba(60,64,67,.10)!important;border-radius:0!important;}
.tb4c-v42157-social-size-lock .screen-reader-text{position:absolute!important;width:1px!important;height:1px!important;padding:0!important;margin:-1px!important;overflow:hidden!important;clip:rect(0,0,0,0)!important;white-space:nowrap!important;border:0!important;}
@media(max-width:1180px){.tb4c-v42157-social-size-lock{--tb4c-s57-left:72px;--tb4c-s57-feed:min(100%,640px);--tb4c-s57-right:260px;}.tb4c-v42157-social-size-lock .tb4c-container.tb4c-social-layout{grid-template-columns:72px minmax(0,1fr) 260px!important;max-width:1000px!important;gap:12px!important;}.tb4c-v42157-social-size-lock .tb4c-social-brand-rail{grid-template-columns:1fr!important;place-items:center!important;min-height:62px!important;padding:8px!important;}.tb4c-v42157-social-size-lock .tb4c-social-brand-rail strong,.tb4c-v42157-social-size-lock .tb4c-social-brand-rail small,.tb4c-v42157-social-size-lock .tb4c-nav-label{display:none!important;}.tb4c-v42157-social-size-lock .tb4c-social-nav a{grid-template-columns:1fr!important;justify-items:center!important;padding:0!important;}.tb4c-v42157-social-size-lock .tb4c-social-feed{max-width:640px!important;}}
@media(max-width:980px){.tb4c-v42157-social-size-lock .tb4c-container.tb4c-social-layout{grid-template-columns:72px minmax(0,1fr)!important;max-width:736px!important;padding:12px 10px 92px!important;}.tb4c-v42157-social-size-lock .tb4c-social-right{display:none!important;}.tb4c-v42157-social-size-lock .tb4c-social-left{top:72px!important;}.tb4c-v42157-social-size-lock .tb4c-social-feed{max-width:640px!important;}}
@media(max-width:720px){.tb4c-v42157-social-size-lock .tb4c-container.tb4c-social-layout{display:grid!important;grid-template-columns:1fr!important;width:100%!important;max-width:100%!important;padding:8px 8px 86px!important;gap:8px!important;}.tb4c-v42157-social-size-lock .tb4c-social-left{position:relative!important;top:auto!important;width:100%!important;display:block!important;}.tb4c-v42157-social-size-lock .tb4c-social-brand-rail{display:none!important;}.tb4c-v42157-social-size-lock .tb4c-social-nav{display:flex!important;flex-direction:row!important;gap:6px!important;overflow:auto!important;white-space:nowrap!important;padding:6px!important;border-radius:12px!important;scrollbar-width:none!important;}.tb4c-v42157-social-size-lock .tb4c-social-nav::-webkit-scrollbar{display:none!important;}.tb4c-v42157-social-size-lock .tb4c-social-nav a{min-width:58px!important;width:58px!important;height:44px!important;grid-template-columns:1fr!important;place-items:center!important;padding:0!important;}.tb4c-v42157-social-size-lock .tb4c-social-feed{max-width:100%!important;width:100%!important;gap:8px!important;}.tb4c-v42157-social-size-lock .tb4c-gplus-stream-header{padding:8px!important;border-radius:12px!important;}.tb4c-v42157-social-size-lock .tb4c-gplus-clean-top{grid-template-columns:1fr!important;min-height:42px!important;}.tb4c-v42157-social-size-lock .tb4c-gplus-clean-action,.tb4c-v42157-social-size-lock .tb4c-gplus-header-main p,.tb4c-v42157-social-size-lock .tb4c-gplus-stats{display:none!important;}.tb4c-v42157-social-size-lock .tb4c-gplus-header-main{grid-template-columns:36px minmax(0,1fr)!important;}.tb4c-v42157-social-size-lock .tb4c-gplus-logo{width:36px!important;height:36px!important;min-width:36px!important;font-size:.78rem!important;}.tb4c-v42157-social-size-lock .tb4c-gplus-header-main h1{font-size:.98rem!important;}.tb4c-v42157-social-size-lock .tb4c-gplus-search{grid-template-columns:minmax(0,1fr) 38px!important;padding:4px!important;}.tb4c-v42157-social-size-lock .tb4c-gplus-search input{height:34px!important;min-height:34px!important;font-size:.82rem!important;}.tb4c-v42157-social-size-lock .tb4c-gplus-search button{width:38px!important;height:34px!important;min-height:34px!important;}.tb4c-v42157-social-size-lock .tb4c-gplus-tabs{display:none!important;}.tb4c-v42157-social-size-lock .tb4c-composer-card{padding:8px!important;border-radius:12px!important;}.tb4c-v42157-social-size-lock .tb4c-quick-composer{grid-template-columns:38px minmax(0,1fr) 38px 38px!important;gap:6px!important;min-height:38px!important;}.tb4c-v42157-social-size-lock .tb4c-quick-composer .tb4c-mini-action:nth-of-type(n+4){display:none!important;}.tb4c-v42157-social-size-lock .tb4c-avatar{width:38px!important;height:38px!important;min-width:38px!important;}.tb4c-v42157-social-size-lock .tb4c-composer-trigger{height:38px!important;min-height:38px!important;font-size:.82rem!important;padding:0 11px!important;}.tb4c-v42157-social-size-lock .tb4c-mini-action{width:38px!important;height:38px!important;min-width:38px!important;min-height:38px!important;}.tb4c-v42157-social-size-lock .tb4c-rail-shell{min-height:86px!important;padding:7px!important;}.tb4c-v42157-social-size-lock .tb4c-rail-nav{display:none!important;}.tb4c-v42157-social-size-lock .tb4c-story-chip{width:68px!important;min-width:68px!important;height:72px!important;border-radius:10px!important;padding:6px!important;}.tb4c-v42157-social-size-lock .tb4c-story-ring{width:24px!important;height:24px!important;}.tb4c-v42157-social-size-lock .tb4c-feed-section-line{min-height:28px!important;padding:0 1px!important;}.tb4c-v42157-social-size-lock .tb4c-feed-section-line span{display:none!important;}.tb4c-v42157-social-size-lock .tb4c-post-card{border-radius:12px!important;}.tb4c-v42157-social-size-lock .tb4c-post-head{grid-template-columns:38px minmax(0,1fr)!important;gap:8px!important;padding:10px 10px 7px!important;min-height:56px!important;}.tb4c-v42157-social-size-lock .tb4c-post-head .tb4c-avatar{width:38px!important;height:38px!important;min-width:38px!important;}.tb4c-v42157-social-size-lock .tb4c-post-follow{display:none!important;}.tb4c-v42157-social-size-lock .tb4c-author-row{font-size:.72rem!important;}.tb4c-v42157-social-size-lock .tb4c-author-row strong{font-size:.84rem!important;}.tb4c-v42157-social-size-lock :where(.tb4c-post-type-chip,.tb4c-privacy-chip,.tb4c-feeling-chip){height:20px!important;min-height:20px!important;font-size:.6rem!important;padding:0 6px!important;}.tb4c-v42157-social-size-lock .tb4c-feed-owner-tools{display:none!important;}.tb4c-v42157-social-size-lock .tb4c-post-inline-text{padding:0 10px 8px!important;}.tb4c-v42157-social-size-lock .tb4c-post-inline-text h2{font-size:.95rem!important;line-height:1.32!important;}.tb4c-v42157-social-size-lock .tb4c-post-inline-text p{font-size:.85rem!important;line-height:1.5!important;}.tb4c-v42157-social-size-lock .tb4c-tags{padding:0 10px 8px!important;}.tb4c-v42157-social-size-lock :where(.tb4c-thumb img,.tb4c-post-media-slot img,.tb4c-featured-lightbox-trigger img,.tb4c-media-album img,.tb4c-album-grid img,.tb4c-post-gallery img,.tb4c-ig-media img){max-height:420px!important;}.tb4c-v42157-social-size-lock .tb4c-post-actions{grid-template-columns:repeat(4,minmax(0,1fr))!important;min-height:40px!important;}.tb4c-v42157-social-size-lock .tb4c-post-actions :where(button,a,span){height:40px!important;min-height:40px!important;font-size:.72rem!important;padding:0 4px!important;}.tb4c-v42157-social-size-lock .tb4c-post-actions em,.tb4c-v42157-social-size-lock .tb4c-post-actions .tb4c-view-chip{display:none!important;}.tb4c-v42157-social-size-lock .tb4c-comment-preview-row{grid-template-columns:minmax(0,1fr) 38px!important;padding:8px 10px!important;}.tb4c-v42157-social-size-lock .tb4c-comment-composer-preview{height:36px!important;grid-template-columns:30px minmax(0,1fr)!important;font-size:.76rem!important;}.tb4c-v42157-social-size-lock .tb4c-comment-preview-avatar{width:30px!important;height:30px!important;min-width:30px!important;}.tb4c-v42157-social-size-lock .tb4c-native-build-link{width:38px!important;height:36px!important;min-height:36px!important;}.tb4c-v42157-social-size-lock .tb4c-floating-create{right:14px!important;bottom:74px!important;width:50px!important;height:50px!important;min-width:50px!important;min-height:50px!important;}}
@media(max-width:380px){.tb4c-v42157-social-size-lock .tb4c-container.tb4c-social-layout{padding-left:6px!important;padding-right:6px!important;}.tb4c-v42157-social-size-lock .tb4c-post-actions{grid-template-columns:repeat(3,minmax(0,1fr))!important;}.tb4c-v42157-social-size-lock .tb4c-post-actions .tb4c-reel-action-btn{display:none!important;}.tb4c-v42157-social-size-lock .tb4c-post-actions :where(button,a,span){font-size:.68rem!important;}.tb4c-v42157-social-size-lock .tb4c-story-chip{width:62px!important;min-width:62px!important;}.tb4c-v42157-social-size-lock .tb4c-mini-action:nth-of-type(n+3){display:none!important;}}
@media(prefers-reduced-motion:reduce),(hover:none),(pointer:coarse){.tb4c-v42157-social-size-lock *{animation:none!important;transition-duration:.001ms!important;scroll-behavior:auto!important;}}
CSS
    . '</style>';
}


/**
 * v4.2.158 — Proportion Social Lock.
 * Fixes the desktop proportions shown in the screenshot: left zone / stream / right zone.
 */
function tb4cm_social_proportion_style_42158() {
    static $printed = false;
    if ( $printed ) {
        return '';
    }
    $printed = true;

    return '<style id="tb4c-social-proportion-42158">' . <<<'CSS'
:root{--tb4c-p58-green:#1E6B45;--tb4c-p58-orange:#F97316;--tb4c-p58-ink:#111111;--tb4c-p58-muted:#5f6b65;--tb4c-p58-line:#dde7e1;--tb4c-p58-bg:#f5f8f6;--tb4c-p58-card:#fff;--tb4c-p58-soft:#f8fbf9;--tb4c-p58-shadow:0 1px 2px rgba(60,64,67,.10);--tb4c-p58-radius:14px;--tb4c-p58-left:260px;--tb4c-p58-feed:700px;--tb4c-p58-right:320px;--tb4c-p58-gap:18px;--tb4c-p58-max:1520px;}
.tb4c-v42158-proportion-social,.tb4c-v42158-proportion-social *,.tb4c-v42158-proportion-social *::before,.tb4c-v42158-proportion-social *::after{box-sizing:border-box!important;}
.tb4c-v42158-proportion-social{width:100vw!important;max-width:100vw!important;margin-left:calc(50% - 50vw)!important;margin-right:calc(50% - 50vw)!important;min-height:100vh!important;background:var(--tb4c-p58-bg)!important;color:var(--tb4c-p58-ink)!important;overflow-x:hidden!important;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",Arial,"Noto Sans Thai","Kanit",sans-serif!important;}
.tb4c-v42158-proportion-social :where(a){text-decoration:none!important;color:inherit!important;}
.tb4c-v42158-proportion-social :where(button,input,textarea,select){font-family:inherit!important;}
/* Desktop proportion: left / center stream / right. This is the main fix from the screenshot. */
.tb4c-v42158-proportion-social .tb4c-container.tb4c-social-layout{width:100%!important;max-width:var(--tb4c-p58-max)!important;margin:0 auto!important;padding:18px clamp(16px,2vw,34px) 96px!important;display:grid!important;grid-template-columns:minmax(220px,var(--tb4c-p58-left)) minmax(620px,var(--tb4c-p58-feed)) minmax(280px,var(--tb4c-p58-right))!important;gap:var(--tb4c-p58-gap)!important;align-items:start!important;justify-content:center!important;}
.tb4c-v42158-proportion-social :where(.tb4c-social-left,.tb4c-social-right){position:sticky!important;top:82px!important;align-self:start!important;width:100%!important;min-width:0!important;max-width:100%!important;}
.tb4c-v42158-proportion-social .tb4c-social-feed{width:100%!important;max-width:var(--tb4c-p58-feed)!important;min-width:0!important;margin:0!important;display:grid!important;gap:12px!important;}
.tb4c-v42158-proportion-social :where(.tb4c-social-card,.tb4c-gplus-stream-header,.tb4c-composer-card,.tb4c-rail-shell,.tb4c-post-card,.tb4c-right-hub-card,.tb4c-empty-state,.tb4c-comments-card,.tb4c-single-social,.tb4c-modal-panel,.tb4c-composer-popup-panel,.tb4c-form-card){background:var(--tb4c-p58-card)!important;border:1px solid var(--tb4c-p58-line)!important;border-radius:var(--tb4c-p58-radius)!important;box-shadow:var(--tb4c-p58-shadow)!important;overflow:hidden!important;filter:none!important;backdrop-filter:none!important;-webkit-backdrop-filter:none!important;}
/* Left sidebar: make it a real social sidebar, not a floating tiny rail. */
.tb4c-v42158-proportion-social .tb4c-social-brand-rail{height:72px!important;min-height:72px!important;margin:0 0 10px!important;padding:10px!important;border-radius:var(--tb4c-p58-radius)!important;display:grid!important;grid-template-columns:44px minmax(0,1fr)!important;gap:10px!important;align-items:center!important;text-align:left!important;background:#fff!important;border:1px solid var(--tb4c-p58-line)!important;box-shadow:var(--tb4c-p58-shadow)!important;}
.tb4c-v42158-proportion-social .tb4c-social-brand-mark{width:44px!important;height:44px!important;min-width:44px!important;border-radius:50%!important;background:var(--tb4c-p58-green)!important;color:#fff!important;display:grid!important;place-items:center!important;font-weight:900!important;}
.tb4c-v42158-proportion-social .tb4c-social-brand-rail strong{font-size:.92rem!important;line-height:1.1!important;color:var(--tb4c-p58-ink)!important;margin:0!important;display:block!important;}
.tb4c-v42158-proportion-social .tb4c-social-brand-rail small{font-size:.72rem!important;line-height:1.2!important;color:var(--tb4c-p58-muted)!important;margin:2px 0 0!important;display:block!important;}
.tb4c-v42158-proportion-social .tb4c-social-nav{display:grid!important;grid-template-columns:1fr!important;gap:3px!important;padding:8px!important;border-radius:var(--tb4c-p58-radius)!important;background:#fff!important;border:1px solid var(--tb4c-p58-line)!important;box-shadow:var(--tb4c-p58-shadow)!important;}
.tb4c-v42158-proportion-social .tb4c-social-nav a{width:100%!important;min-height:42px!important;height:42px!important;padding:0 11px!important;border-radius:10px!important;display:grid!important;grid-template-columns:26px minmax(0,1fr)!important;gap:9px!important;align-items:center!important;justify-content:start!important;color:var(--tb4c-p58-muted)!important;background:transparent!important;border:0!important;font-size:.86rem!important;font-weight:760!important;line-height:1!important;box-shadow:none!important;}
.tb4c-v42158-proportion-social .tb4c-social-nav a:hover,.tb4c-v42158-proportion-social .tb4c-social-nav a.is-active{background:#eaf3ee!important;color:var(--tb4c-p58-green)!important;}
.tb4c-v42158-proportion-social .tb4c-nav-icon{width:24px!important;height:24px!important;display:grid!important;place-items:center!important;font-size:1rem!important;line-height:1!important;}
.tb4c-v42158-proportion-social .tb4c-nav-label{display:block!important;max-width:100%!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;font-size:.84rem!important;}
/* Center stream: consistent Google+/social feed width. */
.tb4c-v42158-proportion-social .tb4c-gplus-stream-header{padding:10px!important;margin:0!important;display:grid!important;gap:9px!important;background:#fff!important;}
.tb4c-v42158-proportion-social .tb4c-gplus-clean-top{display:grid!important;grid-template-columns:minmax(0,1fr) auto!important;gap:10px!important;align-items:center!important;min-height:48px!important;}
.tb4c-v42158-proportion-social .tb4c-gplus-header-main{display:grid!important;grid-template-columns:44px minmax(0,1fr)!important;gap:10px!important;align-items:center!important;min-width:0!important;}
.tb4c-v42158-proportion-social .tb4c-gplus-logo{width:44px!important;height:44px!important;min-width:44px!important;border-radius:50%!important;background:var(--tb4c-p58-green)!important;color:#fff!important;display:grid!important;place-items:center!important;font-weight:900!important;font-size:.86rem!important;}
.tb4c-v42158-proportion-social .tb4c-gplus-eyebrow{display:none!important;}
.tb4c-v42158-proportion-social .tb4c-gplus-header-main h1{font-size:1.06rem!important;line-height:1.18!important;margin:0!important;color:var(--tb4c-p58-ink)!important;letter-spacing:-.01em!important;font-weight:850!important;}
.tb4c-v42158-proportion-social .tb4c-gplus-header-main p{margin:2px 0 0!important;color:var(--tb4c-p58-muted)!important;font-size:.74rem!important;line-height:1.35!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;}
.tb4c-v42158-proportion-social .tb4c-gplus-clean-action{min-height:38px!important;height:38px!important;border-radius:999px!important;border:1px solid var(--tb4c-p58-line)!important;background:var(--tb4c-p58-soft)!important;color:var(--tb4c-p58-green)!important;padding:0 12px!important;display:inline-flex!important;align-items:center!important;gap:6px!important;font-size:.8rem!important;font-weight:800!important;box-shadow:none!important;}
.tb4c-v42158-proportion-social .tb4c-gplus-search{width:100%!important;display:grid!important;grid-template-columns:minmax(0,1fr) 42px!important;gap:6px!important;padding:6px!important;border:1px solid var(--tb4c-p58-line)!important;border-radius:999px!important;background:var(--tb4c-p58-soft)!important;box-shadow:none!important;}
.tb4c-v42158-proportion-social .tb4c-gplus-search input{height:36px!important;min-height:36px!important;border:0!important;background:transparent!important;border-radius:999px!important;padding:0 10px!important;font-size:.86rem!important;color:var(--tb4c-p58-ink)!important;outline:0!important;box-shadow:none!important;}
.tb4c-v42158-proportion-social .tb4c-gplus-search button{width:42px!important;height:36px!important;min-height:36px!important;border:0!important;border-radius:999px!important;background:var(--tb4c-p58-green)!important;color:#fff!important;padding:0!important;display:grid!important;place-items:center!important;font-size:0!important;box-shadow:none!important;}
.tb4c-v42158-proportion-social .tb4c-gplus-search button span{display:none!important;}
.tb4c-v42158-proportion-social .tb4c-gplus-tabs{display:flex!important;gap:6px!important;overflow:auto!important;white-space:nowrap!important;padding:7px 1px 0!important;margin:0!important;border-top:1px solid #eef1ef!important;scrollbar-width:none!important;}
.tb4c-v42158-proportion-social .tb4c-gplus-tabs::-webkit-scrollbar{display:none!important;}
.tb4c-v42158-proportion-social .tb4c-gplus-tabs a{min-height:34px!important;height:34px!important;display:inline-flex!important;align-items:center!important;gap:6px!important;border-radius:999px!important;border:1px solid var(--tb4c-p58-line)!important;padding:0 10px!important;color:var(--tb4c-p58-muted)!important;background:#fff!important;font-size:.78rem!important;font-weight:800!important;}
.tb4c-v42158-proportion-social .tb4c-gplus-tabs a.is-active{background:#eaf3ee!important;color:var(--tb4c-p58-green)!important;border-color:#cdded4!important;}
.tb4c-v42158-proportion-social .tb4c-gplus-stats{display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr))!important;gap:6px!important;margin:0!important;}
.tb4c-v42158-proportion-social .tb4c-gplus-stats span{min-height:44px!important;padding:7px 8px!important;border:1px solid #eef1ef!important;background:#fff!important;border-radius:10px!important;display:grid!important;align-content:center!important;gap:1px!important;}
.tb4c-v42158-proportion-social .tb4c-gplus-stats strong{font-size:.95rem!important;line-height:1!important;color:var(--tb4c-p58-ink)!important;}.tb4c-v42158-proportion-social .tb4c-gplus-stats small{font-size:.66rem!important;line-height:1!important;color:var(--tb4c-p58-muted)!important;}
/* Composer proportions */
.tb4c-v42158-proportion-social .tb4c-composer-card{padding:10px!important;margin:0!important;background:#fff!important;}
.tb4c-v42158-proportion-social .tb4c-quick-composer{width:100%!important;display:grid!important;grid-template-columns:44px minmax(0,1fr) repeat(4,42px)!important;gap:8px!important;align-items:center!important;padding:0!important;margin:0!important;background:transparent!important;border:0!important;box-shadow:none!important;min-height:44px!important;}
.tb4c-v42158-proportion-social .tb4c-avatar,.tb4c-v42158-proportion-social .tb4c-comment-preview-avatar{width:44px!important;height:44px!important;min-width:44px!important;border-radius:50%!important;background:#e8f1ec!important;color:var(--tb4c-p58-green)!important;display:grid!important;place-items:center!important;overflow:hidden!important;font-weight:900!important;line-height:1!important;}
.tb4c-v42158-proportion-social .tb4c-avatar img,.tb4c-v42158-proportion-social .tb4c-comment-preview-avatar img{width:100%!important;height:100%!important;object-fit:cover!important;display:block!important;}
.tb4c-v42158-proportion-social .tb4c-composer-trigger{height:42px!important;min-height:42px!important;border-radius:999px!important;border:1px solid var(--tb4c-p58-line)!important;background:var(--tb4c-p58-soft)!important;color:var(--tb4c-p58-muted)!important;padding:0 14px!important;text-align:left!important;font-size:.9rem!important;font-weight:700!important;box-shadow:none!important;overflow:hidden!important;text-overflow:ellipsis!important;white-space:nowrap!important;}
.tb4c-v42158-proportion-social .tb4c-mini-action{width:42px!important;height:42px!important;min-width:42px!important;min-height:42px!important;border-radius:50%!important;border:1px solid var(--tb4c-p58-line)!important;background:#fff!important;color:var(--tb4c-p58-green)!important;padding:0!important;display:grid!important;place-items:center!important;box-shadow:none!important;font-size:0!important;}
.tb4c-v42158-proportion-social .tb4c-mini-action span{display:none!important;}
/* Reels/story: small horizontal strip */
.tb4c-v42158-proportion-social .tb4c-rail-shell{position:relative!important;padding:8px 34px!important;background:#fff!important;min-height:104px!important;}
.tb4c-v42158-proportion-social .tb4c-story-rail{display:flex!important;gap:8px!important;overflow:auto!important;scrollbar-width:none!important;padding:0!important;margin:0!important;}
.tb4c-v42158-proportion-social .tb4c-story-rail::-webkit-scrollbar{display:none!important;}
.tb4c-v42158-proportion-social .tb4c-story-chip{width:78px!important;min-width:78px!important;height:88px!important;border-radius:12px!important;border:1px solid var(--tb4c-p58-line)!important;background:#eef5f1!important;box-shadow:none!important;padding:7px!important;display:grid!important;align-content:end!important;gap:2px!important;overflow:hidden!important;color:#fff!important;text-align:left!important;}
.tb4c-v42158-proportion-social .tb4c-story-chip strong{font-size:.68rem!important;line-height:1.1!important;max-width:100%!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;text-shadow:0 1px 2px rgba(0,0,0,.38)!important;}
.tb4c-v42158-proportion-social .tb4c-story-chip small{font-size:.58rem!important;line-height:1.1!important;max-width:100%!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;color:rgba(255,255,255,.86)!important;text-shadow:0 1px 2px rgba(0,0,0,.38)!important;}
.tb4c-v42158-proportion-social .tb4c-story-ring{position:absolute!important;left:7px!important;top:7px!important;width:26px!important;height:26px!important;min-width:26px!important;border-radius:50%!important;background:rgba(255,255,255,.92)!important;color:var(--tb4c-p58-green)!important;display:grid!important;place-items:center!important;overflow:hidden!important;}
.tb4c-v42158-proportion-social .tb4c-story-cover{position:absolute!important;inset:0!important;background:linear-gradient(180deg,rgba(0,0,0,.08),rgba(0,0,0,.55)),var(--tb4c-reel-cover,linear-gradient(135deg,#1E6B45,#0f3424))!important;background-size:cover!important;background-position:center!important;z-index:0!important;}
.tb4c-v42158-proportion-social .tb4c-story-chip>*:not(.tb4c-story-cover){position:relative!important;z-index:1!important;}
.tb4c-v42158-proportion-social .tb4c-rail-nav{position:absolute!important;top:50%!important;transform:translateY(-50%)!important;width:26px!important;height:42px!important;min-height:42px!important;border-radius:999px!important;border:1px solid var(--tb4c-p58-line)!important;background:#fff!important;color:var(--tb4c-p58-green)!important;display:grid!important;place-items:center!important;box-shadow:var(--tb4c-p58-shadow)!important;z-index:2!important;}
.tb4c-v42158-proportion-social .tb4c-rail-nav.is-prev{left:6px!important;}.tb4c-v42158-proportion-social .tb4c-rail-nav.is-next{right:6px!important;}
/* Post card proportions */
.tb4c-v42158-proportion-social .tb4c-feed-section-line{height:34px!important;display:flex!important;align-items:center!important;justify-content:space-between!important;padding:0 2px!important;margin:0!important;color:var(--tb4c-p58-muted)!important;}
.tb4c-v42158-proportion-social .tb4c-feed-section-line strong{font-size:.9rem!important;color:var(--tb4c-p58-ink)!important;}.tb4c-v42158-proportion-social .tb4c-feed-section-line span{font-size:.74rem!important;}
.tb4c-v42158-proportion-social .tb4c-feed-list{display:grid!important;gap:12px!important;width:100%!important;}
.tb4c-v42158-proportion-social .tb4c-post-card{width:100%!important;max-width:100%!important;margin:0!important;background:#fff!important;}
.tb4c-v42158-proportion-social .tb4c-post-head{display:grid!important;grid-template-columns:44px minmax(0,1fr) auto!important;gap:10px!important;align-items:center!important;min-height:64px!important;padding:12px 12px 8px!important;}
.tb4c-v42158-proportion-social .tb4c-post-meta{min-width:0!important;display:flex!important;align-items:center!important;gap:6px!important;flex-wrap:wrap!important;}
.tb4c-v42158-proportion-social .tb4c-author-row{display:flex!important;align-items:center!important;gap:5px!important;min-width:0!important;color:var(--tb4c-p58-muted)!important;font-size:.76rem!important;}
.tb4c-v42158-proportion-social .tb4c-author-row strong{color:var(--tb4c-p58-ink)!important;font-size:.9rem!important;}
.tb4c-v42158-proportion-social :where(.tb4c-post-type-chip,.tb4c-privacy-chip,.tb4c-feeling-chip){height:22px!important;min-height:22px!important;border-radius:999px!important;border:1px solid #e6eeea!important;background:#f7faf8!important;color:var(--tb4c-p58-green)!important;padding:0 8px!important;display:inline-flex!important;align-items:center!important;gap:4px!important;font-size:.66rem!important;font-weight:780!important;line-height:1!important;}
.tb4c-v42158-proportion-social .tb4c-post-follow :where(button,a){height:30px!important;min-height:30px!important;border-radius:999px!important;border:1px solid var(--tb4c-p58-line)!important;background:#fff!important;color:var(--tb4c-p58-green)!important;padding:0 10px!important;font-size:.72rem!important;font-weight:850!important;box-shadow:none!important;}
.tb4c-v42158-proportion-social .tb4c-feed-owner-tools button{height:28px!important;min-height:28px!important;border-radius:7px!important;padding:0 8px!important;border:1px solid #dbe5df!important;background:#fff!important;color:var(--tb4c-p58-ink)!important;font-size:.72rem!important;box-shadow:none!important;}
.tb4c-v42158-proportion-social .tb4c-post-inline-text{display:block!important;padding:0 12px 10px!important;color:var(--tb4c-p58-ink)!important;}
.tb4c-v42158-proportion-social .tb4c-post-inline-text h2{font-size:1.02rem!important;line-height:1.35!important;margin:0 0 6px!important;font-weight:850!important;letter-spacing:-.01em!important;}.tb4c-v42158-proportion-social .tb4c-post-inline-text p{font-size:.9rem!important;line-height:1.55!important;margin:0!important;color:#4d5b54!important;}
.tb4c-v42158-proportion-social :where(.tb4c-thumb,.tb4c-post-media-slot,.tb4c-featured-lightbox-trigger,.tb4c-media-album,.tb4c-album-grid,.tb4c-post-gallery,.tb4c-ig-media){width:100%!important;max-width:100%!important;margin:0!important;border-radius:0!important;overflow:hidden!important;}
.tb4c-v42158-proportion-social :where(.tb4c-thumb img,.tb4c-post-media-slot img,.tb4c-featured-lightbox-trigger img,.tb4c-media-album img,.tb4c-album-grid img,.tb4c-post-gallery img,.tb4c-ig-media img){width:100%!important;height:auto!important;max-height:560px!important;object-fit:cover!important;display:block!important;border-radius:0!important;}
.tb4c-v42158-proportion-social .tb4c-tags{display:flex!important;gap:6px!important;flex-wrap:wrap!important;padding:0 12px 10px!important;margin:0!important;}.tb4c-v42158-proportion-social .tb4c-tags a,.tb4c-v42158-proportion-social .tb4c-tags span{height:24px!important;border-radius:999px!important;background:#f2f7f4!important;border:1px solid #e1ece6!important;color:var(--tb4c-p58-green)!important;padding:0 8px!important;display:inline-flex!important;align-items:center!important;font-size:.7rem!important;font-weight:760!important;}
.tb4c-v42158-proportion-social .tb4c-post-actions{display:grid!important;grid-template-columns:repeat(4,minmax(0,1fr))!important;gap:0!important;padding:6px!important;border-top:1px solid #eef2f0!important;background:#fff!important;min-height:44px!important;}
.tb4c-v42158-proportion-social .tb4c-post-actions :where(button,a,span){width:100%!important;height:38px!important;min-height:38px!important;border:0!important;border-radius:9px!important;background:transparent!important;color:var(--tb4c-p58-muted)!important;display:flex!important;align-items:center!important;justify-content:center!important;gap:5px!important;padding:0 6px!important;font-size:.78rem!important;font-weight:760!important;box-shadow:none!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;}
.tb4c-v42158-proportion-social .tb4c-post-actions :where(button:hover,a:hover){background:#f2f7f4!important;color:var(--tb4c-p58-green)!important;}
.tb4c-v42158-proportion-social .tb4c-comment-preview-row{display:grid!important;grid-template-columns:minmax(0,1fr) 42px!important;gap:8px!important;align-items:center!important;padding:9px 12px 12px!important;border-top:1px solid #eef2f0!important;background:#fff!important;}
.tb4c-v42158-proportion-social .tb4c-comment-composer-preview{height:38px!important;min-height:38px!important;border-radius:999px!important;background:#f7faf8!important;border:1px solid var(--tb4c-p58-line)!important;color:var(--tb4c-p58-muted)!important;display:grid!important;grid-template-columns:32px minmax(0,1fr)!important;gap:8px!important;align-items:center!important;padding:3px 10px 3px 3px!important;font-size:.8rem!important;}
/* Right sidebar: align as real right proportion, compact but useful. */
.tb4c-v42158-proportion-social .tb4c-right-hub-card{padding:12px!important;background:#fff!important;display:grid!important;gap:10px!important;}
.tb4c-v42158-proportion-social .tb4c-right-hub-head{border-radius:12px!important;background:#fbf8f1!important;border:1px solid #f0e5d6!important;padding:10px!important;display:grid!important;gap:4px!important;}
.tb4c-v42158-proportion-social .tb4c-right-hub-head h3{font-size:.92rem!important;line-height:1.2!important;margin:0!important;color:var(--tb4c-p58-ink)!important;}.tb4c-v42158-proportion-social .tb4c-right-hub-head p{font-size:.72rem!important;line-height:1.35!important;margin:0!important;color:var(--tb4c-p58-muted)!important;}
.tb4c-v42158-proportion-social .tb4c-right-hub-head a{height:30px!important;min-height:30px!important;border-radius:999px!important;background:var(--tb4c-p58-green)!important;color:#fff!important;padding:0 10px!important;display:inline-flex!important;align-items:center!important;justify-content:center!important;font-size:.72rem!important;font-weight:850!important;justify-self:start!important;box-shadow:none!important;}
.tb4c-v42158-proportion-social .tb4c-right-hub-section{padding:9px 0!important;border-top:1px solid #eef2f0!important;margin:0!important;}.tb4c-v42158-proportion-social .tb4c-right-hub-title{font-size:.82rem!important;line-height:1.2!important;margin:0 0 8px!important;color:var(--tb4c-p58-ink)!important;display:block!important;}
.tb4c-v42158-proportion-social .tb4c-suggest-person{display:grid!important;grid-template-columns:38px minmax(0,1fr) auto!important;gap:8px!important;align-items:center!important;min-height:50px!important;padding:6px!important;border-radius:10px!important;background:#f9fbfa!important;border:1px solid #edf2ef!important;}
.tb4c-v42158-proportion-social .tb4c-suggest-person .tb4c-avatar{width:38px!important;height:38px!important;min-width:38px!important;}.tb4c-v42158-proportion-social .tb4c-suggest-person strong{font-size:.78rem!important;color:var(--tb4c-p58-ink)!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;display:block!important;}.tb4c-v42158-proportion-social .tb4c-suggest-person span{font-size:.68rem!important;color:var(--tb4c-p58-muted)!important;display:block!important;}
.tb4c-v42158-proportion-social .tb4c-topic-compact-list{display:grid!important;grid-template-columns:1fr!important;gap:6px!important;}.tb4c-v42158-proportion-social .tb4c-topic-link{min-height:34px!important;border-radius:10px!important;border:1px solid #edf2ef!important;background:#fff!important;color:var(--tb4c-p58-green)!important;padding:0 9px!important;display:flex!important;align-items:center!important;justify-content:space-between!important;font-size:.74rem!important;font-weight:760!important;box-shadow:none!important;}.tb4c-v42158-proportion-social .tb4c-topic-link small{color:var(--tb4c-p58-muted)!important;font-size:.68rem!important;}
.tb4c-v42158-proportion-social .tb4c-right-hub-rules{padding:10px!important;border-radius:12px!important;background:#111!important;color:#fff!important;}.tb4c-v42158-proportion-social .tb4c-right-hub-rules span{font-size:.78rem!important;font-weight:850!important;}.tb4c-v42158-proportion-social .tb4c-right-hub-rules p{margin:4px 0 0!important;font-size:.72rem!important;line-height:1.38!important;color:rgba(255,255,255,.78)!important;}
.tb4c-v42158-proportion-social :where(.tb4c-smart-back,.tb4c-back-link){min-height:34px!important;border-radius:999px!important;border:1px solid var(--tb4c-p58-line)!important;background:#fff!important;color:var(--tb4c-p58-green)!important;padding:0 12px!important;font-size:.78rem!important;font-weight:850!important;box-shadow:var(--tb4c-p58-shadow)!important;}
.tb4c-v42158-proportion-social :where(a,button,input,textarea,select):focus-visible{outline:3px solid rgba(30,107,69,.22)!important;outline-offset:2px!important;}
.tb4c-v42158-proportion-social :where(.tb4c-floating-create,.tb4c-app-floating-create){position:fixed!important;right:18px!important;bottom:86px!important;width:54px!important;height:54px!important;min-width:54px!important;min-height:54px!important;border-radius:50%!important;background:var(--tb4c-p58-orange)!important;color:#fff!important;box-shadow:0 8px 18px rgba(249,115,22,.22)!important;border:0!important;display:grid!important;place-items:center!important;z-index:999!important;}
.tb4c-v42158-proportion-social :where(.tb4c-mobile-bottom-nav,.tb4c-app-bottom-nav){height:62px!important;background:#fff!important;border-top:1px solid var(--tb4c-p58-line)!important;box-shadow:0 -1px 5px rgba(60,64,67,.10)!important;border-radius:0!important;}
@media(min-width:1500px){.tb4c-v42158-proportion-social .tb4c-container.tb4c-social-layout{--tb4c-p58-left:280px;--tb4c-p58-feed:720px;--tb4c-p58-right:340px;--tb4c-p58-gap:20px;}}
@media(max-width:1320px){.tb4c-v42158-proportion-social .tb4c-container.tb4c-social-layout{--tb4c-p58-left:226px;--tb4c-p58-feed:660px;--tb4c-p58-right:286px;--tb4c-p58-gap:14px;padding-inline:12px!important;}}
@media(max-width:1180px){.tb4c-v42158-proportion-social .tb4c-container.tb4c-social-layout{grid-template-columns:78px minmax(0,1fr) 270px!important;max-width:1060px!important;gap:12px!important;}.tb4c-v42158-proportion-social .tb4c-social-brand-rail{grid-template-columns:1fr!important;place-items:center!important;height:62px!important;min-height:62px!important;padding:8px!important;}.tb4c-v42158-proportion-social .tb4c-social-brand-rail strong,.tb4c-v42158-proportion-social .tb4c-social-brand-rail small,.tb4c-v42158-proportion-social .tb4c-nav-label{display:none!important;}.tb4c-v42158-proportion-social .tb4c-social-nav a{grid-template-columns:1fr!important;justify-items:center!important;padding:0!important;}.tb4c-v42158-proportion-social .tb4c-social-feed{max-width:660px!important;}}
@media(max-width:980px){.tb4c-v42158-proportion-social .tb4c-container.tb4c-social-layout{grid-template-columns:78px minmax(0,1fr)!important;max-width:780px!important;padding:12px 10px 92px!important;}.tb4c-v42158-proportion-social .tb4c-social-right{display:none!important;}.tb4c-v42158-proportion-social .tb4c-social-left{top:72px!important;}.tb4c-v42158-proportion-social .tb4c-social-feed{max-width:660px!important;}}
@media(max-width:720px){.tb4c-v42158-proportion-social{width:100%!important;max-width:100%!important;margin-left:0!important;margin-right:0!important;}.tb4c-v42158-proportion-social .tb4c-container.tb4c-social-layout{display:grid!important;grid-template-columns:1fr!important;width:100%!important;max-width:100%!important;padding:8px 8px 86px!important;gap:8px!important;}.tb4c-v42158-proportion-social .tb4c-social-left{position:relative!important;top:auto!important;width:100%!important;display:block!important;}.tb4c-v42158-proportion-social .tb4c-social-brand-rail{display:none!important;}.tb4c-v42158-proportion-social .tb4c-social-nav{display:flex!important;flex-direction:row!important;gap:6px!important;overflow:auto!important;white-space:nowrap!important;padding:6px!important;border-radius:12px!important;scrollbar-width:none!important;}.tb4c-v42158-proportion-social .tb4c-social-nav::-webkit-scrollbar{display:none!important;}.tb4c-v42158-proportion-social .tb4c-social-nav a{min-width:58px!important;width:58px!important;height:44px!important;grid-template-columns:1fr!important;place-items:center!important;padding:0!important;}.tb4c-v42158-proportion-social .tb4c-social-feed{max-width:100%!important;width:100%!important;gap:8px!important;}.tb4c-v42158-proportion-social .tb4c-gplus-stream-header{padding:8px!important;border-radius:12px!important;}.tb4c-v42158-proportion-social .tb4c-gplus-clean-top{grid-template-columns:1fr!important;min-height:42px!important;}.tb4c-v42158-proportion-social .tb4c-gplus-clean-action,.tb4c-v42158-proportion-social .tb4c-gplus-header-main p,.tb4c-v42158-proportion-social .tb4c-gplus-stats{display:none!important;}.tb4c-v42158-proportion-social .tb4c-gplus-header-main{grid-template-columns:36px minmax(0,1fr)!important;}.tb4c-v42158-proportion-social .tb4c-gplus-logo{width:36px!important;height:36px!important;min-width:36px!important;font-size:.78rem!important;}.tb4c-v42158-proportion-social .tb4c-gplus-header-main h1{font-size:.98rem!important;}.tb4c-v42158-proportion-social .tb4c-gplus-search{grid-template-columns:minmax(0,1fr) 38px!important;padding:4px!important;}.tb4c-v42158-proportion-social .tb4c-gplus-search input{height:34px!important;min-height:34px!important;font-size:.82rem!important;}.tb4c-v42158-proportion-social .tb4c-gplus-search button{width:38px!important;height:34px!important;min-height:34px!important;}.tb4c-v42158-proportion-social .tb4c-gplus-tabs{display:none!important;}.tb4c-v42158-proportion-social .tb4c-composer-card{padding:8px!important;border-radius:12px!important;}.tb4c-v42158-proportion-social .tb4c-quick-composer{grid-template-columns:38px minmax(0,1fr) 38px 38px!important;gap:6px!important;min-height:38px!important;}.tb4c-v42158-proportion-social .tb4c-quick-composer .tb4c-mini-action:nth-of-type(n+4){display:none!important;}.tb4c-v42158-proportion-social .tb4c-avatar{width:38px!important;height:38px!important;min-width:38px!important;}.tb4c-v42158-proportion-social .tb4c-composer-trigger{height:38px!important;min-height:38px!important;font-size:.82rem!important;padding:0 11px!important;}.tb4c-v42158-proportion-social .tb4c-mini-action{width:38px!important;height:38px!important;min-width:38px!important;min-height:38px!important;}.tb4c-v42158-proportion-social .tb4c-rail-shell{min-height:86px!important;padding:7px!important;}.tb4c-v42158-proportion-social .tb4c-rail-nav{display:none!important;}.tb4c-v42158-proportion-social .tb4c-story-chip{width:68px!important;min-width:68px!important;height:72px!important;border-radius:10px!important;padding:6px!important;}.tb4c-v42158-proportion-social .tb4c-story-ring{width:24px!important;height:24px!important;}.tb4c-v42158-proportion-social .tb4c-feed-section-line{min-height:28px!important;padding:0 1px!important;}.tb4c-v42158-proportion-social .tb4c-feed-section-line span{display:none!important;}.tb4c-v42158-proportion-social .tb4c-post-card{border-radius:12px!important;}.tb4c-v42158-proportion-social .tb4c-post-head{grid-template-columns:38px minmax(0,1fr)!important;gap:8px!important;padding:10px 10px 7px!important;min-height:56px!important;}.tb4c-v42158-proportion-social .tb4c-post-head .tb4c-avatar{width:38px!important;height:38px!important;min-width:38px!important;}.tb4c-v42158-proportion-social .tb4c-post-follow{display:none!important;}.tb4c-v42158-proportion-social .tb4c-author-row{font-size:.72rem!important;}.tb4c-v42158-proportion-social .tb4c-author-row strong{font-size:.84rem!important;}.tb4c-v42158-proportion-social :where(.tb4c-post-type-chip,.tb4c-privacy-chip,.tb4c-feeling-chip){height:20px!important;min-height:20px!important;font-size:.6rem!important;padding:0 6px!important;}.tb4c-v42158-proportion-social .tb4c-feed-owner-tools{display:none!important;}.tb4c-v42158-proportion-social .tb4c-post-inline-text{padding:0 10px 8px!important;}.tb4c-v42158-proportion-social .tb4c-post-inline-text h2{font-size:.95rem!important;line-height:1.32!important;}.tb4c-v42158-proportion-social .tb4c-post-inline-text p{font-size:.85rem!important;line-height:1.5!important;}.tb4c-v42158-proportion-social .tb4c-tags{padding:0 10px 8px!important;}.tb4c-v42158-proportion-social :where(.tb4c-thumb img,.tb4c-post-media-slot img,.tb4c-featured-lightbox-trigger img,.tb4c-media-album img,.tb4c-album-grid img,.tb4c-post-gallery img,.tb4c-ig-media img){max-height:420px!important;}.tb4c-v42158-proportion-social .tb4c-post-actions{grid-template-columns:repeat(4,minmax(0,1fr))!important;min-height:40px!important;}.tb4c-v42158-proportion-social .tb4c-post-actions :where(button,a,span){height:40px!important;min-height:40px!important;font-size:.72rem!important;padding:0 4px!important;}.tb4c-v42158-proportion-social .tb4c-post-actions em,.tb4c-v42158-proportion-social .tb4c-post-actions .tb4c-view-chip{display:none!important;}.tb4c-v42158-proportion-social .tb4c-comment-preview-row{grid-template-columns:minmax(0,1fr) 38px!important;padding:8px 10px!important;}.tb4c-v42158-proportion-social .tb4c-comment-composer-preview{height:36px!important;grid-template-columns:30px minmax(0,1fr)!important;font-size:.76rem!important;}.tb4c-v42158-proportion-social .tb4c-comment-preview-avatar{width:30px!important;height:30px!important;min-width:30px!important;}.tb4c-v42158-proportion-social .tb4c-native-build-link{width:38px!important;height:36px!important;min-height:36px!important;}.tb4c-v42158-proportion-social .tb4c-floating-create{right:14px!important;bottom:74px!important;width:50px!important;height:50px!important;min-width:50px!important;min-height:50px!important;}}
@media(max-width:380px){.tb4c-v42158-proportion-social .tb4c-container.tb4c-social-layout{padding-left:6px!important;padding-right:6px!important;}.tb4c-v42158-proportion-social .tb4c-post-actions{grid-template-columns:repeat(3,minmax(0,1fr))!important;}.tb4c-v42158-proportion-social .tb4c-post-actions .tb4c-reel-action-btn{display:none!important;}.tb4c-v42158-proportion-social .tb4c-post-actions :where(button,a,span){font-size:.68rem!important;}.tb4c-v42158-proportion-social .tb4c-story-chip{width:62px!important;min-width:62px!important;}.tb4c-v42158-proportion-social .tb4c-mini-action:nth-of-type(n+3){display:none!important;}}
@media(prefers-reduced-motion:reduce),(hover:none),(pointer:coarse){.tb4c-v42158-proportion-social *{animation:none!important;transition-duration:.001ms!important;scroll-behavior:auto!important;}}
CSS
    . '</style>';
}


/**
 * v4.2.159 Full-Bleed Social Proportion.
 * Fixes the core layout issue from the screenshot: the community UI must not stay as a small centered island.
 * It breaks out of narrow theme wrappers and divides the desktop viewport into real left / feed / right social zones.
 */
function tb4cm_full_bleed_social_style_42159() {
    static $printed = false;
    if ( $printed ) {
        return '';
    }
    $printed = true;

    return '<style id="tb4c-full-bleed-social-42159">' . <<<'CSS'
:root{--tb4c-f59-green:#1E6B45;--tb4c-f59-orange:#F97316;--tb4c-f59-ink:#202124;--tb4c-f59-muted:#5f6b65;--tb4c-f59-line:#dfe5e1;--tb4c-f59-bg:#f6f8f6;--tb4c-f59-card:#fff;--tb4c-f59-soft:#f9fbfa;--tb4c-f59-shadow:0 1px 2px rgba(60,64,67,.10);--tb4c-f59-radius:14px;}
body.tb4c-v42159-full-bleed-body :where(.entry-content,.wp-block-post-content,.site-main,.content-area,.main-content,.page-content,.wp-site-blocks){max-width:none!important;width:100%!important;overflow:visible!important;}
body.tb4c-v42159-full-bleed-body :where(.entry-content,.wp-block-post-content){padding-left:0!important;padding-right:0!important;}
.tb4c-v42159-full-bleed-social,.tb4c-v42159-full-bleed-social *,.tb4c-v42159-full-bleed-social *::before,.tb4c-v42159-full-bleed-social *::after{box-sizing:border-box!important;}
.tb4c-v42159-full-bleed-social{position:relative!important;width:100vw!important;max-width:100vw!important;margin-left:calc(50% - 50vw)!important;margin-right:calc(50% - 50vw)!important;margin-top:0!important;margin-bottom:0!important;padding:18px clamp(18px,3vw,56px) 96px!important;background:var(--tb4c-f59-bg)!important;color:var(--tb4c-f59-ink)!important;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",Arial,"Noto Sans Thai","Kanit",sans-serif!important;overflow-x:clip!important;}
.tb4c-v42159-full-bleed-social .tb4c-container.tb4c-social-layout{width:min(100%,1780px)!important;max-width:1780px!important;margin:0 auto!important;padding:0!important;display:grid!important;grid-template-columns:280px minmax(640px,720px) 320px!important;justify-content:space-between!important;column-gap:24px!important;row-gap:16px!important;align-items:start!important;}
.tb4c-v42159-full-bleed-social :where(.tb4c-social-left,.tb4c-social-feed,.tb4c-social-right){min-width:0!important;max-width:100%!important;}
.tb4c-v42159-full-bleed-social :where(.tb4c-social-left,.tb4c-social-right){position:sticky!important;top:78px!important;align-self:start!important;width:100%!important;}
.tb4c-v42159-full-bleed-social .tb4c-social-feed{width:720px!important;max-width:720px!important;margin:0 auto!important;display:grid!important;gap:12px!important;}
.tb4c-v42159-full-bleed-social :where(.tb4c-social-card,.tb4c-social-brand-rail,.tb4c-gplus-stream-header,.tb4c-composer-card,.tb4c-rail-shell,.tb4c-post-card,.tb4c-right-hub-card,.tb4c-empty-state,.tb4c-modal-panel,.tb4c-composer-popup-panel){background:var(--tb4c-f59-card)!important;border:1px solid var(--tb4c-f59-line)!important;border-radius:var(--tb4c-f59-radius)!important;box-shadow:var(--tb4c-f59-shadow)!important;overflow:hidden!important;}
/* Left sidebar must be a real social navigation area, not a tiny icon strip floating in the middle. */
.tb4c-v42159-full-bleed-social .tb4c-social-brand-rail{width:100%!important;min-height:76px!important;margin:0 0 10px!important;padding:12px!important;display:grid!important;grid-template-columns:48px minmax(0,1fr)!important;gap:10px!important;align-items:center!important;text-align:left!important;background:#fff!important;}
.tb4c-v42159-full-bleed-social .tb4c-social-brand-mark,.tb4c-v42159-full-bleed-social .tb4c-gplus-logo{width:48px!important;height:48px!important;min-width:48px!important;border-radius:50%!important;background:var(--tb4c-f59-green)!important;color:#fff!important;display:grid!important;place-items:center!important;font-weight:900!important;font-size:.95rem!important;line-height:1!important;box-shadow:none!important;}
.tb4c-v42159-full-bleed-social .tb4c-social-brand-rail strong{display:block!important;margin:0!important;font-size:.94rem!important;line-height:1.1!important;color:var(--tb4c-f59-ink)!important;font-weight:900!important;}
.tb4c-v42159-full-bleed-social .tb4c-social-brand-rail small{display:block!important;margin-top:2px!important;font-size:.74rem!important;line-height:1.2!important;color:var(--tb4c-f59-muted)!important;}
.tb4c-v42159-full-bleed-social .tb4c-social-nav{width:100%!important;display:grid!important;gap:6px!important;padding:8px!important;background:#fff!important;}
.tb4c-v42159-full-bleed-social .tb4c-social-nav a{width:100%!important;min-height:46px!important;border-radius:11px!important;padding:0 10px!important;display:grid!important;grid-template-columns:34px minmax(0,1fr)!important;align-items:center!important;gap:8px!important;color:var(--tb4c-f59-muted)!important;background:transparent!important;border:1px solid transparent!important;text-decoration:none!important;font-size:.86rem!important;font-weight:800!important;}
.tb4c-v42159-full-bleed-social .tb4c-social-nav a:is(.is-active,:hover,:focus-visible){background:#eaf6ef!important;color:var(--tb4c-f59-green)!important;border-color:rgba(30,107,69,.14)!important;}
.tb4c-v42159-full-bleed-social .tb4c-nav-icon{width:34px!important;height:34px!important;display:grid!important;place-items:center!important;border-radius:50%!important;background:#f5f8f6!important;color:inherit!important;font-size:1rem!important;}
.tb4c-v42159-full-bleed-social .tb4c-nav-label{display:block!important;min-width:0!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;}
/* Center stream: fixed social feed width so posts do not look like random cards. */
.tb4c-v42159-full-bleed-social .tb4c-gplus-stream-header{padding:12px!important;background:#fff!important;}
.tb4c-v42159-full-bleed-social .tb4c-gplus-clean-top{display:grid!important;grid-template-columns:minmax(0,1fr) auto!important;gap:10px!important;align-items:center!important;margin:0 0 10px!important;}
.tb4c-v42159-full-bleed-social .tb4c-gplus-header-main{display:grid!important;grid-template-columns:48px minmax(0,1fr)!important;gap:10px!important;align-items:center!important;}
.tb4c-v42159-full-bleed-social .tb4c-gplus-eyebrow{font-size:.7rem!important;line-height:1!important;color:var(--tb4c-f59-green)!important;font-weight:900!important;text-transform:uppercase!important;letter-spacing:.04em!important;}
.tb4c-v42159-full-bleed-social .tb4c-gplus-header-main h1{margin:2px 0!important;font-size:1.12rem!important;line-height:1.18!important;color:var(--tb4c-f59-ink)!important;font-weight:900!important;letter-spacing:-.015em!important;}
.tb4c-v42159-full-bleed-social .tb4c-gplus-header-main p{margin:0!important;font-size:.8rem!important;line-height:1.35!important;color:var(--tb4c-f59-muted)!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;}
.tb4c-v42159-full-bleed-social .tb4c-gplus-clean-action{height:36px!important;min-height:36px!important;border-radius:999px!important;border:1px solid var(--tb4c-f59-line)!important;background:#fff!important;color:var(--tb4c-f59-green)!important;padding:0 12px!important;display:inline-flex!important;align-items:center!important;gap:6px!important;font-size:.78rem!important;font-weight:900!important;text-decoration:none!important;box-shadow:none!important;}
.tb4c-v42159-full-bleed-social .tb4c-gplus-search{display:grid!important;grid-template-columns:minmax(0,1fr) 42px!important;gap:6px!important;align-items:center!important;min-height:44px!important;margin:0 0 10px!important;padding:5px!important;border:1px solid var(--tb4c-f59-line)!important;border-radius:999px!important;background:#f9fbfa!important;}
.tb4c-v42159-full-bleed-social .tb4c-gplus-search input{height:34px!important;min-height:34px!important;border:0!important;background:transparent!important;padding:0 12px!important;font-size:.86rem!important;outline:0!important;box-shadow:none!important;}
.tb4c-v42159-full-bleed-social .tb4c-gplus-search button{width:42px!important;height:34px!important;min-height:34px!important;border:0!important;border-radius:999px!important;background:var(--tb4c-f59-green)!important;color:#fff!important;display:grid!important;place-items:center!important;padding:0!important;}
.tb4c-v42159-full-bleed-social .tb4c-gplus-search button span{display:none!important;}
.tb4c-v42159-full-bleed-social .tb4c-gplus-tabs{display:flex!important;gap:6px!important;overflow:auto!important;white-space:nowrap!important;scrollbar-width:none!important;margin:0 0 10px!important;}
.tb4c-v42159-full-bleed-social .tb4c-gplus-tabs::-webkit-scrollbar{display:none!important;}
.tb4c-v42159-full-bleed-social .tb4c-gplus-tabs a{height:32px!important;min-height:32px!important;border-radius:999px!important;border:1px solid var(--tb4c-f59-line)!important;background:#fff!important;color:#3c4043!important;padding:0 10px!important;display:inline-flex!important;align-items:center!important;gap:6px!important;font-size:.76rem!important;font-weight:850!important;text-decoration:none!important;}
.tb4c-v42159-full-bleed-social .tb4c-gplus-tabs a.is-active{background:#eaf6ef!important;color:var(--tb4c-f59-green)!important;border-color:rgba(30,107,69,.20)!important;}
.tb4c-v42159-full-bleed-social .tb4c-gplus-stats{display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr))!important;gap:6px!important;}
.tb4c-v42159-full-bleed-social .tb4c-gplus-stats span{height:42px!important;min-height:42px!important;border-radius:10px!important;border:1px solid #edf1ee!important;background:#fff!important;padding:6px 8px!important;display:grid!important;align-content:center!important;gap:2px!important;}
.tb4c-v42159-full-bleed-social .tb4c-gplus-stats strong{font-size:.88rem!important;line-height:1!important;color:var(--tb4c-f59-ink)!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;}.tb4c-v42159-full-bleed-social .tb4c-gplus-stats small{font-size:.65rem!important;line-height:1!important;color:var(--tb4c-f59-muted)!important;}
.tb4c-v42159-full-bleed-social .tb4c-composer-card{padding:10px!important;}
.tb4c-v42159-full-bleed-social .tb4c-quick-composer{display:grid!important;grid-template-columns:44px minmax(0,1fr) repeat(4,42px)!important;gap:8px!important;align-items:center!important;min-height:46px!important;}
.tb4c-v42159-full-bleed-social .tb4c-avatar{width:44px!important;height:44px!important;min-width:44px!important;border-radius:50%!important;}
.tb4c-v42159-full-bleed-social .tb4c-composer-trigger{height:42px!important;min-height:42px!important;border-radius:999px!important;border:1px solid var(--tb4c-f59-line)!important;background:#f9fbfa!important;color:var(--tb4c-f59-muted)!important;padding:0 14px!important;text-align:left!important;font-size:.88rem!important;font-weight:750!important;box-shadow:none!important;}
.tb4c-v42159-full-bleed-social .tb4c-mini-action{width:42px!important;height:42px!important;min-width:42px!important;min-height:42px!important;border-radius:50%!important;border:1px solid var(--tb4c-f59-line)!important;background:#fff!important;color:var(--tb4c-f59-green)!important;display:grid!important;place-items:center!important;padding:0!important;box-shadow:none!important;}
.tb4c-v42159-full-bleed-social .tb4c-mini-action span{display:none!important;}
.tb4c-v42159-full-bleed-social .tb4c-rail-shell{padding:8px!important;min-height:92px!important;}
.tb4c-v42159-full-bleed-social .tb4c-story-rail{display:flex!important;gap:8px!important;overflow:auto!important;scrollbar-width:none!important;padding:0!important;}.tb4c-v42159-full-bleed-social .tb4c-story-rail::-webkit-scrollbar{display:none!important;}
.tb4c-v42159-full-bleed-social .tb4c-story-chip{width:76px!important;min-width:76px!important;height:78px!important;border-radius:11px!important;padding:6px!important;background:#fff!important;border:1px solid var(--tb4c-f59-line)!important;box-shadow:none!important;}
.tb4c-v42159-full-bleed-social .tb4c-story-cover{height:38px!important;border-radius:9px!important;}
.tb4c-v42159-full-bleed-social .tb4c-story-ring{width:28px!important;height:28px!important;min-width:28px!important;border-radius:50%!important;}
.tb4c-v42159-full-bleed-social .tb4c-story-chip strong{font-size:.68rem!important;line-height:1.05!important;}.tb4c-v42159-full-bleed-social .tb4c-story-chip small{display:none!important;}
.tb4c-v42159-full-bleed-social .tb4c-post-card{padding:0!important;margin:0!important;background:#fff!important;}
.tb4c-v42159-full-bleed-social .tb4c-post-head{display:grid!important;grid-template-columns:44px minmax(0,1fr) auto!important;gap:10px!important;align-items:center!important;padding:12px 12px 8px!important;min-height:66px!important;}
.tb4c-v42159-full-bleed-social .tb4c-author-row{display:flex!important;align-items:center!important;gap:6px!important;min-width:0!important;color:var(--tb4c-f59-muted)!important;font-size:.78rem!important;line-height:1.2!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;}
.tb4c-v42159-full-bleed-social .tb4c-author-row strong{font-size:.92rem!important;color:var(--tb4c-f59-ink)!important;font-weight:900!important;}
.tb4c-v42159-full-bleed-social :where(.tb4c-post-type-chip,.tb4c-privacy-chip,.tb4c-feeling-chip){height:22px!important;min-height:22px!important;border-radius:999px!important;border:1px solid var(--tb4c-f59-line)!important;background:#f9fbfa!important;color:var(--tb4c-f59-muted)!important;padding:0 8px!important;display:inline-flex!important;align-items:center!important;font-size:.66rem!important;font-weight:800!important;box-shadow:none!important;}
.tb4c-v42159-full-bleed-social .tb4c-feed-owner-tools{display:flex!important;gap:4px!important;}.tb4c-v42159-full-bleed-social .tb4c-feed-owner-tools button{height:28px!important;min-height:28px!important;border-radius:999px!important;padding:0 8px!important;border:1px solid var(--tb4c-f59-line)!important;background:#fff!important;color:var(--tb4c-f59-muted)!important;box-shadow:none!important;font-size:.7rem!important;}
.tb4c-v42159-full-bleed-social .tb4c-post-inline-text{display:block!important;padding:0 12px 10px!important;color:var(--tb4c-f59-ink)!important;text-decoration:none!important;}
.tb4c-v42159-full-bleed-social .tb4c-post-inline-text h2{margin:0 0 5px!important;font-size:1.02rem!important;line-height:1.35!important;color:var(--tb4c-f59-ink)!important;font-weight:900!important;letter-spacing:-.01em!important;}
.tb4c-v42159-full-bleed-social .tb4c-post-inline-text p{margin:0!important;font-size:.91rem!important;line-height:1.58!important;color:#3f4944!important;}
.tb4c-v42159-full-bleed-social .tb4c-tags{display:flex!important;flex-wrap:wrap!important;gap:5px!important;padding:0 12px 10px!important;margin:0!important;}
.tb4c-v42159-full-bleed-social .tb4c-tags a{height:24px!important;border-radius:999px!important;background:#eef6f1!important;color:var(--tb4c-f59-green)!important;padding:0 8px!important;display:inline-flex!important;align-items:center!important;font-size:.72rem!important;font-weight:800!important;text-decoration:none!important;}
.tb4c-v42159-full-bleed-social :where(.tb4c-thumb,.tb4c-post-media-slot,.tb4c-featured-lightbox-trigger,.tb4c-media-album,.tb4c-album-grid,.tb4c-post-gallery,.tb4c-ig-media){width:100%!important;max-width:100%!important;margin:0!important;border:0!important;border-radius:0!important;background:#f1f3f2!important;box-shadow:none!important;overflow:hidden!important;display:block!important;}
.tb4c-v42159-full-bleed-social :where(.tb4c-thumb img,.tb4c-post-media-slot img,.tb4c-featured-lightbox-trigger img,.tb4c-media-album img,.tb4c-album-grid img,.tb4c-post-gallery img,.tb4c-ig-media img){width:100%!important;height:auto!important;max-height:560px!important;object-fit:cover!important;display:block!important;margin:0!important;border-radius:0!important;}
.tb4c-v42159-full-bleed-social :where(.tb4c-media-album video,.tb4c-post-media-slot video,.tb4c-ig-media video){width:100%!important;height:auto!important;max-height:560px!important;object-fit:contain!important;background:#000!important;display:block!important;}
.tb4c-v42159-full-bleed-social .tb4c-post-actions{display:grid!important;grid-template-columns:repeat(5,minmax(0,1fr))!important;gap:0!important;min-height:44px!important;padding:0!important;margin:0!important;border-top:1px solid var(--tb4c-f59-line)!important;border-bottom:1px solid var(--tb4c-f59-line)!important;background:#fff!important;}
.tb4c-v42159-full-bleed-social .tb4c-post-actions :where(button,a,span){height:44px!important;min-height:44px!important;border:0!important;border-right:1px solid #eef1ef!important;border-radius:0!important;background:#fff!important;color:var(--tb4c-f59-muted)!important;display:flex!important;align-items:center!important;justify-content:center!important;gap:5px!important;padding:0 6px!important;font-size:.78rem!important;font-weight:800!important;line-height:1!important;box-shadow:none!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;text-decoration:none!important;}
.tb4c-v42159-full-bleed-social .tb4c-post-actions :where(button,a,span):last-child{border-right:0!important;}
.tb4c-v42159-full-bleed-social .tb4c-comment-preview-row{display:grid!important;grid-template-columns:minmax(0,1fr) 42px!important;gap:8px!important;align-items:center!important;padding:9px 12px!important;background:#fff!important;}
.tb4c-v42159-full-bleed-social .tb4c-comment-composer-preview{height:38px!important;display:grid!important;grid-template-columns:32px minmax(0,1fr)!important;gap:8px!important;align-items:center!important;border-radius:999px!important;background:#f9fbfa!important;color:var(--tb4c-f59-muted)!important;padding:3px 10px 3px 3px!important;font-size:.82rem!important;font-weight:700!important;}
.tb4c-v42159-full-bleed-social .tb4c-comment-preview-avatar{width:32px!important;height:32px!important;min-width:32px!important;border-radius:50%!important;}.tb4c-v42159-full-bleed-social .tb4c-comment-preview-copy{white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;}
/* Right sidebar must sit in the right red zone, not beside the feed as a small floating block. */
.tb4c-v42159-full-bleed-social .tb4c-right-hub-card{width:100%!important;display:grid!important;gap:0!important;background:#fff!important;}
.tb4c-v42159-full-bleed-social .tb4c-right-hub-head{padding:12px!important;border-bottom:1px solid var(--tb4c-f59-line)!important;background:#fff!important;}
.tb4c-v42159-full-bleed-social .tb4c-right-hub-head h3{margin:0 0 2px!important;font-size:.94rem!important;line-height:1.2!important;color:var(--tb4c-f59-ink)!important;font-weight:900!important;}
.tb4c-v42159-full-bleed-social .tb4c-right-hub-head p{margin:0!important;font-size:.72rem!important;line-height:1.35!important;color:var(--tb4c-f59-muted)!important;}
.tb4c-v42159-full-bleed-social .tb4c-right-hub-section{padding:10px 12px!important;border-bottom:1px solid var(--tb4c-f59-line)!important;}
.tb4c-v42159-full-bleed-social .tb4c-right-hub-title{display:block!important;margin:0 0 7px!important;font-size:.72rem!important;color:var(--tb4c-f59-muted)!important;text-transform:uppercase!important;letter-spacing:.04em!important;}
.tb4c-v42159-full-bleed-social .tb4c-suggest-person{display:grid!important;grid-template-columns:36px minmax(0,1fr) auto!important;gap:8px!important;align-items:center!important;min-height:46px!important;padding:6px 0!important;border-bottom:1px solid #eef1ef!important;border-radius:0!important;background:transparent!important;}
.tb4c-v42159-full-bleed-social .tb4c-suggest-person .tb4c-avatar{width:36px!important;height:36px!important;min-width:36px!important;}
.tb4c-v42159-full-bleed-social .tb4c-suggest-person strong{display:block!important;max-width:160px!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;font-size:.8rem!important;line-height:1.15!important;color:var(--tb4c-f59-ink)!important;}
.tb4c-v42159-full-bleed-social .tb4c-suggest-person span{display:block!important;font-size:.66rem!important;color:var(--tb4c-f59-muted)!important;}
.tb4c-v42159-full-bleed-social .tb4c-topic-compact-list{display:grid!important;gap:0!important;}
.tb4c-v42159-full-bleed-social .tb4c-topic-link{min-height:34px!important;display:flex!important;align-items:center!important;justify-content:space-between!important;gap:8px!important;padding:6px 0!important;border:0!important;border-bottom:1px solid #eef1ef!important;border-radius:0!important;background:transparent!important;color:var(--tb4c-f59-green)!important;font-size:.78rem!important;font-weight:850!important;text-decoration:none!important;}
.tb4c-v42159-full-bleed-social .tb4c-right-hub-rules{padding:10px 12px!important;background:#fbfdfc!important;color:var(--tb4c-f59-muted)!important;font-size:.72rem!important;line-height:1.4!important;}
.tb4c-v42159-full-bleed-social :where(input,select,textarea){border:1px solid var(--tb4c-f59-line)!important;border-radius:10px!important;background:#fff!important;color:var(--tb4c-f59-ink)!important;box-shadow:none!important;}
.tb4c-v42159-full-bleed-social :where(a,button,input,textarea,select):focus-visible{outline:3px solid rgba(249,115,22,.26)!important;outline-offset:2px!important;}
.tb4c-v42159-full-bleed-social :where(.tb4c-floating-create,.tb4c-app-floating-create){position:fixed!important;right:18px!important;bottom:86px!important;width:54px!important;height:54px!important;border-radius:50%!important;background:var(--tb4c-f59-orange)!important;color:#fff!important;box-shadow:0 8px 18px rgba(249,115,22,.22)!important;border:0!important;display:grid!important;place-items:center!important;z-index:999!important;}
@media(max-width:1480px){.tb4c-v42159-full-bleed-social{padding-inline:clamp(14px,2.2vw,32px)!important;}.tb4c-v42159-full-bleed-social .tb4c-container.tb4c-social-layout{grid-template-columns:240px minmax(620px,680px) 300px!important;column-gap:20px!important;}.tb4c-v42159-full-bleed-social .tb4c-social-feed{width:680px!important;max-width:680px!important;}}
@media(max-width:1320px){.tb4c-v42159-full-bleed-social .tb4c-container.tb4c-social-layout{grid-template-columns:220px minmax(0,1fr) 280px!important;justify-content:stretch!important;column-gap:14px!important;}.tb4c-v42159-full-bleed-social .tb4c-social-feed{width:100%!important;max-width:660px!important;}.tb4c-v42159-full-bleed-social .tb4c-social-brand-rail{grid-template-columns:42px minmax(0,1fr)!important;}.tb4c-v42159-full-bleed-social .tb4c-social-brand-mark{width:42px!important;height:42px!important;min-width:42px!important;}}
@media(max-width:1080px){.tb4c-v42159-full-bleed-social .tb4c-container.tb4c-social-layout{grid-template-columns:220px minmax(0,1fr)!important;max-width:920px!important;justify-content:stretch!important;}.tb4c-v42159-full-bleed-social .tb4c-social-right{display:none!important;}.tb4c-v42159-full-bleed-social .tb4c-social-feed{max-width:660px!important;}}
@media(max-width:860px){.tb4c-v42159-full-bleed-social{padding:10px 10px 88px!important;}.tb4c-v42159-full-bleed-social .tb4c-container.tb4c-social-layout{grid-template-columns:1fr!important;width:100%!important;max-width:680px!important;gap:8px!important;}.tb4c-v42159-full-bleed-social .tb4c-social-left{position:relative!important;top:auto!important;width:100%!important;}.tb4c-v42159-full-bleed-social .tb4c-social-brand-rail{display:none!important;}.tb4c-v42159-full-bleed-social .tb4c-social-nav{display:flex!important;flex-direction:row!important;gap:6px!important;overflow:auto!important;white-space:nowrap!important;scrollbar-width:none!important;padding:6px!important;border-radius:12px!important;}.tb4c-v42159-full-bleed-social .tb4c-social-nav::-webkit-scrollbar{display:none!important;}.tb4c-v42159-full-bleed-social .tb4c-social-nav a{min-width:58px!important;width:58px!important;height:44px!important;grid-template-columns:1fr!important;place-items:center!important;padding:0!important;}.tb4c-v42159-full-bleed-social .tb4c-nav-label{display:none!important;}.tb4c-v42159-full-bleed-social .tb4c-social-feed{width:100%!important;max-width:100%!important;gap:8px!important;}.tb4c-v42159-full-bleed-social .tb4c-gplus-clean-action,.tb4c-v42159-full-bleed-social .tb4c-gplus-header-main p,.tb4c-v42159-full-bleed-social .tb4c-gplus-stats,.tb4c-v42159-full-bleed-social .tb4c-gplus-tabs{display:none!important;}.tb4c-v42159-full-bleed-social .tb4c-quick-composer{grid-template-columns:38px minmax(0,1fr) 38px 38px!important;gap:6px!important;}.tb4c-v42159-full-bleed-social .tb4c-mini-action:nth-of-type(n+4){display:none!important;}.tb4c-v42159-full-bleed-social .tb4c-post-head{grid-template-columns:38px minmax(0,1fr)!important;padding:10px 10px 7px!important;}.tb4c-v42159-full-bleed-social .tb4c-post-follow,.tb4c-v42159-full-bleed-social .tb4c-feed-owner-tools{display:none!important;}.tb4c-v42159-full-bleed-social .tb4c-post-actions{grid-template-columns:repeat(4,minmax(0,1fr))!important;}.tb4c-v42159-full-bleed-social .tb4c-post-actions :where(em,.tb4c-view-chip){display:none!important;}}
@media(max-width:420px){.tb4c-v42159-full-bleed-social{padding-left:7px!important;padding-right:7px!important;}.tb4c-v42159-full-bleed-social .tb4c-gplus-header-main{grid-template-columns:38px minmax(0,1fr)!important;}.tb4c-v42159-full-bleed-social .tb4c-gplus-logo{width:38px!important;height:38px!important;min-width:38px!important;}.tb4c-v42159-full-bleed-social .tb4c-gplus-header-main h1{font-size:.98rem!important;}.tb4c-v42159-full-bleed-social .tb4c-post-inline-text h2{font-size:.95rem!important;}.tb4c-v42159-full-bleed-social .tb4c-post-inline-text p{font-size:.85rem!important;}.tb4c-v42159-full-bleed-social .tb4c-post-actions{grid-template-columns:repeat(3,minmax(0,1fr))!important;}.tb4c-v42159-full-bleed-social .tb4c-post-actions .tb4c-reel-action-btn{display:none!important;}}
@media(prefers-reduced-motion:reduce),(hover:none),(pointer:coarse){.tb4c-v42159-full-bleed-social *{animation:none!important;transition-duration:.001ms!important;scroll-behavior:auto!important;}}
CSS
    . '</style>';
}

/**
 * Shortcodes and dynamic blocks.
 */
function tb4cm_render_community( $atts = [] ) {
    $atts = shortcode_atts( [
        'layout' => 'full',
    ], (array) $atts, 'thinkb4do_community' );

    ob_start();
    echo tb4cm_module_layout_inline_style_42164(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    $tb4c_atts = $atts;
    include TB4CM_DIR . 'templates/community-content.php';
    return ob_get_clean();
}
// Split plugin: community feed shortcode is owned by Thinkb4do Community Feed Main.

function tb4cm_render_member_pages_shortcode() {
    $area_url = tb4cm_get_member_area_url();
    return '<section class="tb4c-member-page-removed-notice"><h2>' . esc_html__( 'รวมสมาชิกถูกย้ายไปที่พื้นที่สมาชิกแล้ว', 'thinkb4do-community' ) . '</h2><p>' . esc_html__( 'เพื่อลดหน้าซ้ำ ระบบคงไว้เฉพาะ member-area สำหรับจัดการโปรไฟล์ โพสต์ และฟีดของตัวเอง', 'thinkb4do-community' ) . '</p><a class="tb4c-btn tb4c-btn-primary" href="' . esc_url( $area_url ) . '">' . esc_html__( 'เปิดพื้นที่สมาชิก', 'thinkb4do-community' ) . '</a></section>';
}
add_shortcode( 'thinkb4do_member_pages', 'tb4cm_render_member_pages_shortcode' );

function tb4cm_render_member_area_shortcode() {
    ob_start();
    echo tb4cm_module_layout_inline_style_42164(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    include TB4CM_DIR . 'templates/member-area-content.php';
    return ob_get_clean();
}
add_shortcode( 'thinkb4do_member_area', 'tb4cm_render_member_area_shortcode' );
add_shortcode( 'thinkb4do_members', 'tb4cm_render_member_area_shortcode' );

/**
 * v4.2.242: Hard member page owner.
 * ป้องกันกรณีปลั๊กอิน/ธีมตัวอื่นลงทะเบียน shortcode ชื่อเดียวกันหรือ template หน้า member-area ทับอยู่
 * โดยให้ปลั๊กอิน Members เป็นเจ้าของหน้า /member-area/ ตอนท้ายสุดของการโหลด WordPress.
 */
function tb4cm_force_member_area_shortcode_owner_v4242() {
    if ( ! function_exists( 'remove_shortcode' ) || ! function_exists( 'add_shortcode' ) ) {
        return;
    }
    remove_shortcode( 'thinkb4do_member_area' );
    remove_shortcode( 'thinkb4do_members' );
    add_shortcode( 'thinkb4do_member_area', 'tb4cm_render_member_area_shortcode' );
    add_shortcode( 'thinkb4do_members', 'tb4cm_render_member_area_shortcode' );
}
add_action( 'init', 'tb4cm_force_member_area_shortcode_owner_v4242', 9999 );
add_action( 'wp_loaded', 'tb4cm_force_member_area_shortcode_owner_v4242', 9999 );

function tb4cm_is_member_area_surface_v4242() {
    if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
        return false;
    }
    if ( function_exists( 'is_page' ) && is_page( 'member-area' ) ) {
        return true;
    }
    $post = function_exists( 'get_post' ) ? get_post() : null;
    if ( $post instanceof WP_Post ) {
        $content = (string) $post->post_content;
        return has_shortcode( $content, 'thinkb4do_member_area' ) || has_shortcode( $content, 'thinkb4do_members' ) || false !== strpos( $content, 'tb4c-member-area-page' );
    }
    return false;
}

function tb4cm_force_member_area_page_content_v4242( $content ) {
    if ( ! tb4cm_is_member_area_surface_v4242() || ! is_page( 'member-area' ) ) {
        return $content;
    }
    if ( function_exists( 'is_main_query' ) && ! is_main_query() ) {
        return $content;
    }
    if ( function_exists( 'in_the_loop' ) && ! in_the_loop() ) {
        return $content;
    }
    static $tb4cm_member_force_rendering = false;
    if ( $tb4cm_member_force_rendering ) {
        return $content;
    }
    $tb4cm_member_force_rendering = true;
    $rendered = tb4cm_render_member_area_shortcode();
    $tb4cm_member_force_rendering = false;
    return $rendered ? $rendered : $content;
}
add_filter( 'the_content', 'tb4cm_force_member_area_page_content_v4242', 99999 );


function tb4cm_render_composer_shortcode() {
    ob_start();
    echo tb4cm_module_layout_inline_style_42164(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    include TB4CM_DIR . 'templates/community-composer.php';
    return ob_get_clean();
}
// Split plugin: composer shortcode is owned by Thinkb4do Community Feed Main.

function tb4cm_render_home_menu_feed_shortcode( $atts = [] ) {
    $atts = shortcode_atts( [
        'limit'  => 8,
        'source' => 'shortcode',
    ], (array) $atts, 'thinkb4do_home_menu_feed' );

    ob_start();
    echo tb4cm_module_layout_inline_style_42164(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    $tb4c_home_menu_feed_atts = $atts;
    include TB4CM_DIR . 'templates/home-menu-feed.php';
    return ob_get_clean();
}
// Split plugin: home menu feed shortcode is owned by Thinkb4do Community Feed Main.

function tb4cm_render_home_discovery_feed_shortcode( $atts = [] ) {
    $atts = shortcode_atts( [
        'limit'  => 8,
        'source' => 'shortcode',
    ], (array) $atts, 'thinkb4do_home_discovery_feed' );

    ob_start();
    echo tb4cm_module_layout_inline_style_42164(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    $tb4c_home_discovery_atts = $atts;
    include TB4CM_DIR . 'templates/home-discovery-feed.php';
    return ob_get_clean();
}
// Split plugin: home discovery feed shortcode is owned by Thinkb4do Community Feed Main.


function tb4cm_maybe_inject_home_menu_feed( $content ) {
    if ( is_admin() || is_feed() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
        return $content;
    }

    if ( ! is_front_page() || ! in_the_loop() || ! is_main_query() ) {
        return $content;
    }

    if ( has_shortcode( $content, 'thinkb4do_home_menu_feed' ) || has_shortcode( $content, 'thinkb4do_home_discovery_feed' ) || false !== strpos( $content, 'tb4c-home-menu-feed' ) || false !== strpos( $content, 'tb4-home-feed-main' ) ) {
        return $content;
    }

    if ( ! apply_filters( 'tb4c_auto_home_menu_feed_enabled', true ) ) {
        return $content;
    }

    return $content . tb4cm_render_home_discovery_feed_shortcode( [ 'source' => 'auto' ] );
}
add_filter( 'the_content', 'tb4cm_maybe_inject_home_menu_feed', 28 );

function tb4cm_register_blocks() {
    if ( function_exists( 'register_block_type' ) ) {
        register_block_type( 'thinkb4do/community', [
            'render_callback' => 'tb4cm_render_community',
        ] );
        register_block_type( 'thinkb4do/member-area', [
            'render_callback' => 'tb4cm_render_member_area_shortcode',
        ] );
    }
}
add_action( 'init', 'tb4cm_register_blocks' );


/**
 * Member area helpers.
 */
function tb4cm_get_member_area_url() {
    return home_url( '/member-area/' );
}


/**
 * v4.2.78 — Member profile media sync and user-scoped upload folders.
 * โฟลเดอร์รูปของสมาชิก: uploads/thinkb4do-community/users/{user_id}-{user_slug}/{profile|cover|posts}
 */
function tb4cm_get_user_asset_folder_slug( $user_id ) {
    $user_id = absint( $user_id );
    $user    = $user_id ? get_userdata( $user_id ) : null;
    $name    = $user ? ( $user->display_name ?: $user->user_login ) : 'member';
    $slug    = sanitize_title( remove_accents( $name ) );

    if ( '' === $slug && $user ) {
        $slug = sanitize_title( $user->user_login );
    }
    if ( '' === $slug ) {
        $slug = 'member';
    }

    return $user_id . '-' . $slug;
}

function tb4cm_get_user_asset_subdir( $user_id = 0, $context = 'general' ) {
    $user_id = $user_id ? absint( $user_id ) : get_current_user_id();
    $context = sanitize_key( $context ?: 'general' );
    if ( ! in_array( $context, [ 'profile', 'cover', 'posts', 'comments', 'general' ], true ) ) {
        $context = 'general';
    }

    return '/thinkb4do-community/users/' . tb4cm_get_user_asset_folder_slug( $user_id ) . '/' . $context;
}

function tb4cm_get_user_asset_folder_label( $user_id = 0 ) {
    $user_id = $user_id ? absint( $user_id ) : get_current_user_id();
    return 'thinkb4do-community/users/' . tb4cm_get_user_asset_folder_slug( $user_id );
}

function tb4cm_upload_dir_for_user_context( $dirs ) {
    $context = isset( $GLOBALS['tb4c_upload_user_context'] ) && is_array( $GLOBALS['tb4c_upload_user_context'] ) ? $GLOBALS['tb4c_upload_user_context'] : [];
    $user_id = absint( $context['user_id'] ?? 0 );
    $folder  = sanitize_key( $context['folder'] ?? 'general' );

    if ( ! $user_id ) {
        return $dirs;
    }

    $subdir = tb4cm_get_user_asset_subdir( $user_id, $folder );
    $dirs['subdir'] = $subdir;
    $dirs['path']   = trailingslashit( $dirs['basedir'] ) . ltrim( $subdir, '/' );
    $dirs['url']    = trailingslashit( $dirs['baseurl'] ) . ltrim( $subdir, '/' );

    return $dirs;
}

function tb4cm_with_user_upload_folder( $context, $callback, $user_id = 0 ) {
    $user_id = $user_id ? absint( $user_id ) : get_current_user_id();
    if ( ! $user_id || ! is_callable( $callback ) ) {
        return is_callable( $callback ) ? call_user_func( $callback ) : null;
    }

    $previous = $GLOBALS['tb4c_upload_user_context'] ?? null;
    $GLOBALS['tb4c_upload_user_context'] = [
        'user_id' => $user_id,
        'folder'  => sanitize_key( $context ?: 'general' ),
    ];

    add_filter( 'upload_dir', 'tb4cm_upload_dir_for_user_context', 20 );
    try {
        return call_user_func( $callback );
    } finally {
        remove_filter( 'upload_dir', 'tb4cm_upload_dir_for_user_context', 20 );
        if ( null === $previous ) {
            unset( $GLOBALS['tb4c_upload_user_context'] );
        } else {
            $GLOBALS['tb4c_upload_user_context'] = $previous;
        }
    }
}

function tb4cm_record_user_media_asset( $user_id, $attachment_id, $context = 'general' ) {
    $user_id       = absint( $user_id );
    $attachment_id = absint( $attachment_id );
    if ( ! $user_id || ! $attachment_id ) {
        return;
    }

    $assets = get_user_meta( $user_id, 'tb4c_member_media_assets', true );
    if ( ! is_array( $assets ) ) {
        $assets = [];
    }

    $context = sanitize_key( $context ?: 'general' );
    $assets[ $attachment_id ] = [
        'id'      => $attachment_id,
        'context' => $context,
        'url'     => esc_url_raw( wp_get_attachment_url( $attachment_id ) ),
        'time'    => current_time( 'mysql' ),
    ];

    update_user_meta( $user_id, 'tb4c_member_media_assets', $assets );
}


/**
 * v4.2.274 — User ID storage usage menu.
 * แสดงการใช้พื้นที่ตาม user id โดยอ่านจาก attachment ที่ระบบบันทึกไว้ + ไฟล์โปรไฟล์/Cover/โพสต์ของสมาชิก.
 */
/**
 * v4.2.275 — Storage usage + storage package pages.
 * เพิ่มเมนูย่อยในหน้า พื้นที่: การใช้พื้นที่ และการเพิ่มแพ็กเกจพื้นที่ พร้อมโครงแพ็กเกจที่ผูกกับ User ID.
 *
 * v4.2.276 — Member storage menu complete.
 * เพิ่มเมนูพื้นที่ครบ 4 หน้า: พื้นที่ของฉัน / การใช้พื้นที่ / เพิ่มแพ็กเกจพื้นที่ / ประวัติการใช้พื้นที่ พร้อม fallback data ตาม User ID.
 */
function tb4cm_filesize_to_bytes_from_attachment( $attachment_id ) {
    $attachment_id = absint( $attachment_id );
    if ( ! $attachment_id ) {
        return 0;
    }

    $paths      = [];
    $upload_dir = wp_upload_dir();
    $base_dir   = ! empty( $upload_dir['basedir'] ) ? wp_normalize_path( $upload_dir['basedir'] ) : '';
    $file       = get_attached_file( $attachment_id );

    if ( $file ) {
        $paths[] = wp_normalize_path( $file );
    }

    $meta = wp_get_attachment_metadata( $attachment_id );
    if ( is_array( $meta ) && ! empty( $meta['file'] ) && $base_dir ) {
        $relative_file = ltrim( wp_normalize_path( $meta['file'] ), '/' );
        $main_path     = trailingslashit( $base_dir ) . $relative_file;
        $paths[]       = $main_path;

        $relative_dir = trim( dirname( $relative_file ), '.\/' );
        if ( ! empty( $meta['sizes'] ) && is_array( $meta['sizes'] ) ) {
            foreach ( $meta['sizes'] as $size_data ) {
                if ( empty( $size_data['file'] ) ) {
                    continue;
                }
                $paths[] = trailingslashit( $base_dir ) . ( $relative_dir ? trailingslashit( $relative_dir ) : '' ) . $size_data['file'];
            }
        }
    }

    $paths = array_values( array_unique( array_filter( $paths ) ) );
    $bytes = 0;
    foreach ( $paths as $path ) {
        if ( is_string( $path ) && is_readable( $path ) && is_file( $path ) ) {
            $bytes += (int) filesize( $path );
        }
    }

    if ( 0 === $bytes && is_array( $meta ) && ! empty( $meta['filesize'] ) ) {
        $bytes = absint( $meta['filesize'] );
    }

    return max( 0, $bytes );
}

function tb4cm_get_user_storage_quota_bytes( $user_id = 0 ) {
    $user_id  = $user_id ? absint( $user_id ) : get_current_user_id();
    $quota_mb = (int) get_user_meta( $user_id, 'tb4c_member_storage_quota_mb', true );

    $package     = function_exists( 'tb4cm_get_user_storage_package' ) ? tb4cm_get_user_storage_package( $user_id ) : [];
    $package_mb  = ! empty( $package['quota_mb'] ) ? absint( $package['quota_mb'] ) : 500;

    if ( $quota_mb <= 0 ) {
        $quota_mb = $package_mb > 0 ? $package_mb : 500;
    } elseif ( $package_mb > $quota_mb ) {
        $quota_mb = $package_mb;
    }

    return (int) apply_filters( 'tb4cm_member_storage_quota_bytes', $quota_mb * MB_IN_BYTES, $user_id, $package );
}

function tb4cm_get_storage_package_plans() {
    $plans = [
        'free' => [
            'label'       => 'ฟรี',
            'short_label' => 'Free',
            'quota_mb'    => 500,
            'price'       => '฿0 / เดือน',
            'badge'       => 'เริ่มต้น',
            'desc'        => 'เหมาะสำหรับสมาชิกเริ่มต้น ใช้ลงรูปโปรไฟล์ Cover และโพสต์ทั่วไป',
            'features'    => [ 'พื้นที่ 500 MB', 'ดูการใช้พื้นที่ได้', 'เหมาะกับการเริ่มใช้งาน' ],
        ],
        'basic' => [
            'label'       => 'Basic',
            'short_label' => 'Basic',
            'quota_mb'    => 2048,
            'price'       => 'รอระบุราคา',
            'badge'       => 'คุ้มค่า',
            'recommended' => true,
            'desc'        => 'เหมาะสำหรับสมาชิกที่โพสต์รูป วิดีโอสั้น และไฟล์ประกอบงานบ่อยขึ้น',
            'features'    => [ 'พื้นที่ประมาณ 2 GB', 'รองรับไฟล์โพสต์มากขึ้น', 'เตรียมเชื่อมระบบชำระเงิน' ],
        ],
        'pro' => [
            'label'       => 'Pro',
            'short_label' => 'Pro',
            'quota_mb'    => 10240,
            'price'       => 'รอระบุราคา',
            'badge'       => 'มืออาชีพ',
            'desc'        => 'เหมาะสำหรับ Creator หรือผู้ใช้ที่มีไฟล์ผลงานจำนวนมาก',
            'features'    => [ 'พื้นที่ประมาณ 10 GB', 'เหมาะกับพอร์ตผลงาน', 'รองรับการขยายต่อในอนาคต' ],
        ],
        'business' => [
            'label'       => 'Business',
            'short_label' => 'Business',
            'quota_mb'    => 51200,
            'price'       => 'รอระบุราคา',
            'badge'       => 'ทีม/ธุรกิจ',
            'desc'        => 'เหมาะสำหรับทีม สตูดิโอ หรือผู้ใช้ที่ต้องการพื้นที่สูงแบบต่อเนื่อง',
            'features'    => [ 'พื้นที่ประมาณ 50 GB', 'เหมาะกับทีม/สตูดิโอ', 'พร้อมต่อระบบอนุมัติและชำระเงิน' ],
        ],
    ];

    return apply_filters( 'tb4cm_member_storage_package_plans', $plans );
}

function tb4cm_get_user_storage_package_key( $user_id = 0 ) {
    $user_id = $user_id ? absint( $user_id ) : get_current_user_id();
    $plans   = tb4cm_get_storage_package_plans();
    $key     = $user_id ? sanitize_key( get_user_meta( $user_id, 'tb4c_member_storage_package', true ) ) : 'free';

    $legacy_map = [
        'plus'   => 'basic',
        'studio' => 'business',
    ];
    if ( ! empty( $legacy_map[ $key ] ) ) {
        $key = $legacy_map[ $key ];
    }

    if ( ! $key || empty( $plans[ $key ] ) ) {
        $key = 'free';
    }

    return apply_filters( 'tb4cm_member_storage_package_key', $key, $user_id );
}

function tb4cm_get_user_storage_package( $user_id = 0 ) {
    $plans = tb4cm_get_storage_package_plans();
    $key   = tb4cm_get_user_storage_package_key( $user_id );
    $plan  = ! empty( $plans[ $key ] ) ? $plans[ $key ] : ( $plans['free'] ?? [] );

    $plan['key']         = $key;
    $plan['quota_bytes'] = ! empty( $plan['quota_mb'] ) ? absint( $plan['quota_mb'] ) * MB_IN_BYTES : 0;

    return apply_filters( 'tb4cm_member_storage_package', $plan, $user_id, $plans );
}

function tb4cm_member_storage_package_request_url( $package_key = 'plus', $user_id = 0 ) {
    $user_id     = $user_id ? absint( $user_id ) : get_current_user_id();
    $package_key = sanitize_key( $package_key ?: 'plus' );
    $url         = add_query_arg( [
        'tb4c_request' => 'storage_package',
        'package'      => $package_key,
        'user_id'      => $user_id,
    ], home_url( '/contact/' ) );

    return apply_filters( 'tb4cm_member_storage_package_request_url', $url, $package_key, $user_id );
}

function tb4cm_add_storage_attachment_id( &$attachment_ids, &$context_map, $attachment_id, $context = 'general' ) {
    $attachment_id = absint( $attachment_id );
    if ( ! $attachment_id ) {
        return;
    }

    $context = sanitize_key( $context ?: 'general' );
    if ( ! in_array( $context, [ 'profile', 'cover', 'posts', 'comments', 'general' ], true ) ) {
        $context = 'general';
    }

    $attachment_ids[ $attachment_id ] = $attachment_id;
    if ( empty( $context_map[ $attachment_id ] ) || 'general' === $context_map[ $attachment_id ] ) {
        $context_map[ $attachment_id ] = $context;
    }
}

function tb4cm_get_user_storage_usage( $user_id = 0 ) {
    $user_id = $user_id ? absint( $user_id ) : get_current_user_id();
    if ( ! $user_id ) {
        return [
            'user_id'         => 0,
            'folder_label'    => '',
            'attachment_ids'  => [],
            'attachment_count'=> 0,
            'total_bytes'     => 0,
            'quota_bytes'     => 0,
            'remaining_bytes' => 0,
            'percent'         => 0,
            'by_context'      => [],
            'latest_items'    => [],
        ];
    }

    $cache_key = 'tb4cm_user_storage_usage_' . $user_id;
    $cached    = wp_cache_get( $cache_key, 'thinkb4do-community' );
    if ( false !== $cached && is_array( $cached ) ) {
        return $cached;
    }

    $attachment_ids = [];
    $context_map    = [];
    $asset_meta     = get_user_meta( $user_id, 'tb4c_member_media_assets', true );

    if ( is_array( $asset_meta ) ) {
        foreach ( $asset_meta as $asset ) {
            if ( is_array( $asset ) ) {
                tb4cm_add_storage_attachment_id( $attachment_ids, $context_map, $asset['id'] ?? 0, $asset['context'] ?? 'general' );
            } else {
                tb4cm_add_storage_attachment_id( $attachment_ids, $context_map, $asset, 'general' );
            }
        }
    }

    tb4cm_add_storage_attachment_id( $attachment_ids, $context_map, get_user_meta( $user_id, 'tb4c_member_avatar_id', true ), 'profile' );
    tb4cm_add_storage_attachment_id( $attachment_ids, $context_map, get_user_meta( $user_id, 'tb4c_member_cover_id', true ), 'cover' );

    $attachment_query_limit = (int) apply_filters( 'tb4cm_member_storage_attachment_query_limit', 800, $user_id );
    $owned_attachments = get_posts( [
        'post_type'              => 'attachment',
        'post_status'            => 'inherit',
        'author'                 => $user_id,
        'fields'                 => 'ids',
        'posts_per_page'         => max( 50, $attachment_query_limit ),
        'orderby'                => 'date',
        'order'                  => 'DESC',
        'no_found_rows'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ] );
    foreach ( $owned_attachments as $attachment_id ) {
        tb4cm_add_storage_attachment_id( $attachment_ids, $context_map, $attachment_id, 'general' );
    }

    $post_ids = get_posts( [
        'post_type'              => 'tb4_community_post',
        'post_status'            => [ 'publish', 'pending', 'draft', 'private' ],
        'author'                 => $user_id,
        'fields'                 => 'ids',
        'posts_per_page'         => 300,
        'orderby'                => 'date',
        'order'                  => 'DESC',
        'no_found_rows'          => true,
        'update_post_meta_cache' => true,
        'update_post_term_cache' => false,
    ] );
    foreach ( $post_ids as $post_id ) {
        tb4cm_add_storage_attachment_id( $attachment_ids, $context_map, get_post_meta( $post_id, 'tb4_media_attachment_id', true ), 'posts' );
        $album = function_exists( 'tb4cm_normalize_media_album' ) ? tb4cm_normalize_media_album( get_post_meta( $post_id, 'tb4_media_album', true ) ) : [];
        foreach ( $album as $album_item ) {
            if ( ! empty( $album_item['attachment_id'] ) ) {
                tb4cm_add_storage_attachment_id( $attachment_ids, $context_map, $album_item['attachment_id'], 'posts' );
            }
        }
    }

    $contexts = [
        'profile'  => [ 'label' => 'รูปโปรไฟล์', 'bytes' => 0, 'count' => 0 ],
        'cover'    => [ 'label' => 'ภาพ Cover', 'bytes' => 0, 'count' => 0 ],
        'posts'    => [ 'label' => 'ไฟล์โพสต์', 'bytes' => 0, 'count' => 0 ],
        'comments' => [ 'label' => 'ไฟล์คอมเมนต์', 'bytes' => 0, 'count' => 0 ],
        'general'  => [ 'label' => 'ไฟล์อื่น ๆ', 'bytes' => 0, 'count' => 0 ],
    ];
    $total_bytes  = 0;
    $latest_items = [];

    foreach ( $attachment_ids as $attachment_id ) {
        if ( 'attachment' !== get_post_type( $attachment_id ) ) {
            continue;
        }
        $bytes   = tb4cm_filesize_to_bytes_from_attachment( $attachment_id );
        $context = $context_map[ $attachment_id ] ?? 'general';
        if ( empty( $contexts[ $context ] ) ) {
            $context = 'general';
        }
        $contexts[ $context ]['bytes'] += $bytes;
        $contexts[ $context ]['count']++;
        $total_bytes += $bytes;

        if ( count( $latest_items ) < 6 ) {
            $latest_items[] = [
                'id'      => $attachment_id,
                'title'   => get_the_title( $attachment_id ) ?: wp_basename( get_attached_file( $attachment_id ) ),
                'url'     => wp_get_attachment_url( $attachment_id ),
                'bytes'   => $bytes,
                'context' => $context,
                'date'    => get_the_date( 'j M Y', $attachment_id ),
                'mime'    => get_post_mime_type( $attachment_id ),
            ];
        }
    }

    $quota_bytes     = tb4cm_get_user_storage_quota_bytes( $user_id );
    $remaining_bytes = max( 0, $quota_bytes - $total_bytes );
    $percent         = $quota_bytes > 0 ? min( 100, round( ( $total_bytes / $quota_bytes ) * 100, 1 ) ) : 0;

    $result = [
        'user_id'          => $user_id,
        'folder_label'     => function_exists( 'tb4cm_get_user_asset_folder_label' ) ? tb4cm_get_user_asset_folder_label( $user_id ) : '',
        'profile_subdir'   => function_exists( 'tb4cm_get_user_asset_subdir' ) ? tb4cm_get_user_asset_subdir( $user_id, 'profile' ) : '',
        'cover_subdir'     => function_exists( 'tb4cm_get_user_asset_subdir' ) ? tb4cm_get_user_asset_subdir( $user_id, 'cover' ) : '',
        'posts_subdir'     => function_exists( 'tb4cm_get_user_asset_subdir' ) ? tb4cm_get_user_asset_subdir( $user_id, 'posts' ) : '',
        'attachment_ids'   => array_values( $attachment_ids ),
        'attachment_count' => count( $attachment_ids ),
        'total_bytes'      => $total_bytes,
        'quota_bytes'      => $quota_bytes,
        'remaining_bytes'  => $remaining_bytes,
        'percent'          => $percent,
        'by_context'       => $contexts,
        'latest_items'     => $latest_items,
    ];

    wp_cache_set( $cache_key, $result, 'thinkb4do-community', 300 );
    return $result;
}

function tb4cm_storage_safe_bytes( $bytes ) {
    if ( is_numeric( $bytes ) ) {
        return max( 0, (int) $bytes );
    }
    return 0;
}

function tb4cm_format_storage_size( $bytes ) {
    $bytes = tb4cm_storage_safe_bytes( $bytes );
    if ( function_exists( 'size_format' ) ) {
        return size_format( $bytes, 1 );
    }
    if ( $bytes >= 1073741824 ) {
        return number_format_i18n( $bytes / 1073741824, 1 ) . ' GB';
    }
    return number_format_i18n( $bytes / 1048576, 1 ) . ' MB';
}

function tb4cm_get_storage_status_data( $percent = 0, $used_bytes = 0, $limit_bytes = 0 ) {
    $percent     = min( 100, max( 0, (float) $percent ) );
    $used_bytes  = tb4cm_storage_safe_bytes( $used_bytes );
    $limit_bytes = tb4cm_storage_safe_bytes( $limit_bytes );

    if ( $limit_bytes > 0 && $used_bytes >= $limit_bytes ) {
        return [
            'key'     => 'full',
            'label'   => 'เต็มแล้ว',
            'message' => 'พื้นที่เต็ม กรุณาเพิ่มแพ็กเกจพื้นที่',
            'tone'    => 'danger',
        ];
    }

    if ( $percent >= 80 ) {
        return [
            'key'     => 'warning',
            'label'   => 'ใกล้เต็ม',
            'message' => 'พื้นที่ใกล้เต็มแล้ว',
            'tone'    => 'warning',
        ];
    }

    return [
        'key'     => 'normal',
        'label'   => 'ปกติ',
        'message' => 'พื้นที่ยังอยู่ในระดับปกติ',
        'tone'    => 'normal',
    ];
}

function tb4cm_get_user_storage_post_data_bytes( $user_id = 0 ) {
    $user_id = $user_id ? absint( $user_id ) : get_current_user_id();
    if ( ! $user_id ) {
        return 0;
    }

    $post_ids = get_posts( [
        'post_type'              => 'tb4_community_post',
        'post_status'            => [ 'publish', 'pending', 'draft', 'private' ],
        'author'                 => $user_id,
        'fields'                 => 'ids',
        'posts_per_page'         => 300,
        'orderby'                => 'date',
        'order'                  => 'DESC',
        'no_found_rows'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ] );

    $bytes = 0;
    foreach ( $post_ids as $post_id ) {
        $content = (string) get_post_field( 'post_content', $post_id );
        $title   = (string) get_the_title( $post_id );
        $bytes  += strlen( wp_strip_all_tags( $content . ' ' . $title ) );
    }

    return max( 0, (int) apply_filters( 'tb4cm_member_storage_post_data_bytes', $bytes, $user_id, $post_ids ) );
}

function tb4cm_get_user_storage_history( $user_id = 0, $storage_data = [] ) {
    $user_id = $user_id ? absint( $user_id ) : get_current_user_id();
    if ( ! $user_id ) {
        return [];
    }

    $history = get_user_meta( $user_id, 'tb4c_member_storage_history', true );
    if ( ! is_array( $history ) ) {
        $history = [];
    }

    $clean = [];
    foreach ( $history as $item ) {
        if ( ! is_array( $item ) ) {
            continue;
        }
        $clean[] = [
            'date'   => sanitize_text_field( $item['date'] ?? current_time( 'Y-m-d' ) ),
            'type'   => sanitize_text_field( $item['type'] ?? 'อัปเดตพื้นที่' ),
            'size'   => tb4cm_storage_safe_bytes( $item['size'] ?? 0 ),
            'status' => sanitize_text_field( $item['status'] ?? 'สำเร็จ' ),
        ];
    }

    if ( empty( $clean ) ) {
        $package = ! empty( $storage_data['current_package_label'] ) ? $storage_data['current_package_label'] : 'ฟรี';
        $used    = tb4cm_storage_safe_bytes( $storage_data['storage_used'] ?? 0 );
        $clean[] = [
            'date'   => date_i18n( 'Y-m-d' ),
            'type'   => 'ตรวจพื้นที่ล่าสุด',
            'size'   => $used,
            'status' => 'สำเร็จ',
        ];
        $clean[] = [
            'date'   => date_i18n( 'Y-m-d' ),
            'type'   => 'แพ็กเกจปัจจุบัน: ' . $package,
            'size'   => tb4cm_storage_safe_bytes( $storage_data['storage_limit'] ?? 0 ),
            'status' => 'ใช้งานอยู่',
        ];
    }

    return array_slice( $clean, 0, 20 );
}

function tb4cm_get_user_storage_dashboard_data( $user_id = 0 ) {
    $user_id = $user_id ? absint( $user_id ) : get_current_user_id();
    if ( ! $user_id ) {
        return [
            'user_id'                 => 0,
            'storage_limit'           => 0,
            'storage_used'            => 0,
            'storage_remaining'       => 0,
            'percent'                 => 0,
            'current_package'         => 'free',
            'current_package_label'   => 'ฟรี',
            'package_expired_at'      => '',
            'status'                  => tb4cm_get_storage_status_data( 0, 0, 0 ),
            'categories'              => [],
            'storage_history'         => [],
            'latest_items'            => [],
        ];
    }

    $usage     = tb4cm_get_user_storage_usage( $user_id );
    $package   = tb4cm_get_user_storage_package( $user_id );
    $limit     = tb4cm_storage_safe_bytes( $usage['quota_bytes'] ?? 0 );
    if ( $limit <= 0 && ! empty( $package['quota_mb'] ) ) {
        $limit = absint( $package['quota_mb'] ) * MB_IN_BYTES;
    }
    if ( $limit <= 0 ) {
        $limit = 500 * MB_IN_BYTES;
    }

    $categories = [
        'images' => [
            'label' => 'พื้นที่จากรูปภาพ',
            'bytes' => 0,
            'count' => 0,
            'icon'  => 'ph-image',
        ],
        'videos' => [
            'label' => 'พื้นที่จากวิดีโอ',
            'bytes' => 0,
            'count' => 0,
            'icon'  => 'ph-video-camera',
        ],
        'attachments' => [
            'label' => 'พื้นที่จากไฟล์แนบ',
            'bytes' => 0,
            'count' => 0,
            'icon'  => 'ph-paperclip',
        ],
        'post_data' => [
            'label' => 'พื้นที่จากโพสต์/ข้อมูลอื่น ๆ',
            'bytes' => tb4cm_get_user_storage_post_data_bytes( $user_id ),
            'count' => 0,
            'icon'  => 'ph-note-pencil',
        ],
    ];

    $attachment_ids = ! empty( $usage['attachment_ids'] ) && is_array( $usage['attachment_ids'] ) ? $usage['attachment_ids'] : [];
    foreach ( $attachment_ids as $attachment_id ) {
        $attachment_id = absint( $attachment_id );
        if ( ! $attachment_id || 'attachment' !== get_post_type( $attachment_id ) ) {
            continue;
        }
        $mime  = (string) get_post_mime_type( $attachment_id );
        $bytes = tb4cm_filesize_to_bytes_from_attachment( $attachment_id );
        if ( 0 === strpos( $mime, 'image/' ) ) {
            $bucket = 'images';
        } elseif ( 0 === strpos( $mime, 'video/' ) ) {
            $bucket = 'videos';
        } else {
            $bucket = 'attachments';
        }
        $categories[ $bucket ]['bytes'] += $bytes;
        $categories[ $bucket ]['count']++;
    }

    $used      = 0;
    foreach ( $categories as $category ) {
        $used += tb4cm_storage_safe_bytes( $category['bytes'] ?? 0 );
    }
    $used      = max( 0, $used );
    $remaining = max( 0, $limit - $used );
    $percent   = $limit > 0 ? min( 100, max( 0, round( ( $used / $limit ) * 100, 1 ) ) ) : 0;
    $status    = tb4cm_get_storage_status_data( $percent, $used, $limit );

    foreach ( $categories as $key => $category ) {
        $categories[ $key ]['percent'] = $limit > 0 ? min( 100, max( 0, round( ( tb4cm_storage_safe_bytes( $category['bytes'] ?? 0 ) / $limit ) * 100, 1 ) ) ) : 0;
    }

    $data = [
        'user_id'               => $user_id,
        'storage_limit'         => $limit,
        'storage_used'          => $used,
        'storage_remaining'     => $remaining,
        'percent'               => $percent,
        'current_package'       => sanitize_key( $package['key'] ?? 'free' ),
        'current_package_label' => sanitize_text_field( $package['label'] ?? 'ฟรี' ),
        'package_expired_at'    => sanitize_text_field( get_user_meta( $user_id, 'tb4c_member_storage_package_expired_at', true ) ),
        'status'                => $status,
        'categories'            => $categories,
        'latest_items'          => ! empty( $usage['latest_items'] ) && is_array( $usage['latest_items'] ) ? $usage['latest_items'] : [],
        'raw_usage'             => $usage,
    ];
    $data['storage_history'] = tb4cm_get_user_storage_history( $user_id, $data );

    return apply_filters( 'tb4cm_member_storage_dashboard_data', $data, $user_id );
}


function tb4cm_get_custom_profile_avatar_url( $user_id, $size = 128 ) {
    $user_id = absint( $user_id );
    if ( ! $user_id ) {
        return '';
    }

    $avatar_id = absint( get_user_meta( $user_id, 'tb4c_member_avatar_id', true ) );
    if ( $avatar_id ) {
        $url = wp_get_attachment_image_url( $avatar_id, [ absint( $size ) ?: 128, absint( $size ) ?: 128 ] );
        if ( ! $url ) {
            $url = wp_get_attachment_image_url( $avatar_id, 'thumbnail' );
        }
        return $url ? esc_url_raw( $url ) : '';
    }

    $url = get_user_meta( $user_id, 'tb4c_member_avatar_url', true );
    return $url ? esc_url_raw( $url ) : '';
}

function tb4cm_get_user_avatar_url( $user_id, $args = [] ) {
    $size = isset( $args['size'] ) ? absint( $args['size'] ) : 128;
    $custom = tb4cm_get_custom_profile_avatar_url( $user_id, $size );
    if ( $custom ) {
        return $custom;
    }
    return get_avatar_url( $user_id, $args );
}

function tb4cm_resolve_user_id_from_avatar_target( $id_or_email ) {
    if ( is_numeric( $id_or_email ) ) {
        return absint( $id_or_email );
    }
    if ( $id_or_email instanceof WP_User ) {
        return absint( $id_or_email->ID );
    }
    if ( $id_or_email instanceof WP_Post ) {
        return absint( $id_or_email->post_author );
    }
    if ( $id_or_email instanceof WP_Comment ) {
        return absint( $id_or_email->user_id );
    }
    if ( is_object( $id_or_email ) && ! empty( $id_or_email->user_id ) ) {
        return absint( $id_or_email->user_id );
    }
    if ( is_string( $id_or_email ) && is_email( $id_or_email ) ) {
        $user = get_user_by( 'email', $id_or_email );
        return $user ? absint( $user->ID ) : 0;
    }
    return 0;
}

function tb4cm_filter_custom_avatar_data( $args, $id_or_email ) {
    $user_id = tb4cm_resolve_user_id_from_avatar_target( $id_or_email );
    if ( ! $user_id ) {
        return $args;
    }

    $size = isset( $args['size'] ) ? absint( $args['size'] ) : 96;
    $url  = tb4cm_get_custom_profile_avatar_url( $user_id, $size );
    if ( $url ) {
        $args['url']          = $url;
        $args['found_avatar'] = true;
    }

    return $args;
}
add_filter( 'pre_get_avatar_data', 'tb4cm_filter_custom_avatar_data', 10, 2 );

function tb4cm_get_user_latest_media_image_url( $user_id ) {
    $cache_key = 'tb4c_latest_media_' . absint( $user_id );
    $cached    = wp_cache_get( $cache_key, 'thinkb4do-community' );
    if ( false !== $cached ) {
        return (string) $cached;
    }

    $posts = get_posts( [
        'post_type'              => 'tb4_community_post',
        'post_status'            => [ 'publish', 'pending', 'draft', 'private' ],
        'author'                 => absint( $user_id ),
        'posts_per_page'         => 8,
        'orderby'                => 'date',
        'order'                  => 'DESC',
        'fields'                 => 'ids',
        'no_found_rows'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ] );

    foreach ( $posts as $post_id ) {
        $post_id = absint( $post_id );
        if ( has_post_thumbnail( $post_id ) ) {
            $thumb = get_the_post_thumbnail_url( $post_id, 'large' );
            if ( $thumb ) {
                wp_cache_set( $cache_key, esc_url_raw( $thumb ), 'thinkb4do-community', 300 );
                return esc_url_raw( $thumb );
            }
        }
        $album = function_exists( 'tb4cm_get_post_media_album' ) ? tb4cm_get_post_media_album( $post_id, get_post_field( 'post_content', $post_id ) ) : [];
        foreach ( $album as $item ) {
            if ( is_array( $item ) && 'image' === ( $item['type'] ?? '' ) && ! empty( $item['url'] ) ) {
                return esc_url_raw( $item['url'] );
            }
        }
    }

    wp_cache_set( $cache_key, '', 'thinkb4do-community', 300 );
    return '';
}

function tb4cm_get_member_cover_url( $user_id, $fallback_to_latest_media = true ) {
    $user_id   = absint( $user_id );
    $cache_key = 'tb4c_member_cover_' . $user_id . '_' . ( $fallback_to_latest_media ? '1' : '0' );
    $cached    = wp_cache_get( $cache_key, 'thinkb4do-community' );
    if ( false !== $cached ) {
        return (string) $cached;
    }

    $cover_id = $user_id ? absint( get_user_meta( $user_id, 'tb4c_member_cover_id', true ) ) : 0;

    if ( $cover_id ) {
        $cover = wp_get_attachment_image_url( $cover_id, 'large' );
        if ( $cover ) {
            wp_cache_set( $cache_key, esc_url_raw( $cover ), 'thinkb4do-community', 300 );
            return esc_url_raw( $cover );
        }
    }

    $cover = $fallback_to_latest_media ? tb4cm_get_user_latest_media_image_url( $user_id ) : '';
    wp_cache_set( $cache_key, $cover, 'thinkb4do-community', 300 );
    return $cover;
}

function tb4cm_get_user_member_page_id( $user_id = 0, $statuses = [ 'publish', 'pending', 'draft', 'private' ] ) {
    // v4.2.74: public member pages are deprecated. Profile data now lives in user meta and member-area only.
    return 0;
}

function tb4cm_get_user_meta_ids( $user_id, $meta_key ) {
    $ids = get_user_meta( absint( $user_id ), sanitize_key( $meta_key ), true );
    if ( ! is_array( $ids ) ) {
        $ids = [];
    }
    return array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );
}

function tb4cm_get_user_bookmarked_post_ids( $user_id = 0 ) {
    $user_id = $user_id ? absint( $user_id ) : get_current_user_id();
    return $user_id ? tb4cm_get_user_meta_ids( $user_id, 'tb4c_bookmarked_post_ids' ) : [];
}

function tb4cm_get_user_liked_post_ids( $user_id = 0 ) {
    $user_id = $user_id ? absint( $user_id ) : get_current_user_id();
    return $user_id ? tb4cm_get_user_meta_ids( $user_id, 'tb4c_liked_post_ids' ) : [];
}

function tb4cm_get_user_comment_count( $user_id ) {
    $comments = get_comments( [
        'user_id' => absint( $user_id ),
        'status'  => 'approve',
        'type'    => 'comment',
        'count'   => true,
        'post_type' => 'tb4_community_post',
    ] );
    return max( 0, absint( $comments ) );
}

function tb4cm_get_user_total_post_views( $user_id ) {
    $user_id = absint( $user_id );
    if ( ! $user_id ) {
        return 0;
    }

    $cache_key = 'tb4c_user_total_views_' . $user_id;
    $cached    = wp_cache_get( $cache_key, 'thinkb4do-community' );
    if ( false !== $cached ) {
        return (int) $cached;
    }

    $posts = get_posts( [
        'post_type'              => 'tb4_community_post',
        'post_status'            => [ 'publish', 'pending', 'draft', 'private' ],
        'author'                 => $user_id,
        'posts_per_page'         => 50,
        'fields'                 => 'ids',
        'no_found_rows'          => true,
        'update_post_meta_cache' => true,
        'update_post_term_cache' => false,
    ] );
    $total = 0;
    foreach ( $posts as $post_id ) {
        $total += absint( get_post_meta( $post_id, 'tb4_view_count', true ) );
    }

    wp_cache_set( $cache_key, $total, 'thinkb4do-community', 300 );
    return $total;
}

function tb4cm_get_member_profile_completeness( $user_id, $member_page_id = 0 ) {
    $user = get_userdata( absint( $user_id ) );
    if ( ! $user ) {
        return [ 'score' => 0, 'items' => [] ];
    }

    $items = [
        'display_name' => [
            'label' => 'ชื่อที่แสดง',
            'done'  => '' !== trim( (string) $user->display_name ),
        ],
        'bio' => [
            'label' => 'คำแนะนำตัว',
            'done'  => '' !== trim( (string) get_user_meta( $user_id, 'description', true ) ),
        ],
        'role' => [
            'label' => 'บทบาท/ความถนัด',
            'done'  => '' !== trim( (string) get_user_meta( $user_id, 'tb4c_member_role', true ) ),
        ],
        'interests' => [
            'label' => 'ความสนใจ',
            'done'  => '' !== trim( (string) get_user_meta( $user_id, 'tb4c_member_interests', true ) ),
        ],
        'avatar' => [
            'label' => 'รูปโปรไฟล์',
            'done'  => '' !== tb4cm_get_custom_profile_avatar_url( $user_id, 96 ),
        ],
        'cover' => [
            'label' => 'ภาพ Cover',
            'done'  => '' !== tb4cm_get_member_cover_url( $user_id, false ),
        ],
        'member_area' => [
            'label' => 'พื้นที่สมาชิก',
            'done'  => true,
        ],
    ];

    $done = 0;
    foreach ( $items as $item ) {
        if ( ! empty( $item['done'] ) ) {
            $done++;
        }
    }

    return [
        'score' => (int) round( ( $done / max( 1, count( $items ) ) ) * 100 ),
        'items' => $items,
    ];
}

function tb4cm_status_label( $status ) {
    $status = sanitize_key( $status );
    $labels = [
        'publish' => 'เผยแพร่แล้ว',
        'pending' => 'รอตรวจ',
        'draft'   => 'ฉบับร่าง',
        'private' => 'ส่วนตัว',
        'trash'   => 'ถังขยะ',
    ];
    return $labels[ $status ] ?? $status;
}

if ( ! function_exists( 'tb4cm_visibility_label' ) ) {
/**
 * v4.2.211: Human-readable audience / visibility label for feed cards and forms.
 */
function tb4cm_visibility_label( $visibility ) {
    $visibility = sanitize_key( $visibility ?: 'public' );
    $labels = [
        'public'  => 'สาธารณะ',
        'members' => 'เฉพาะสมาชิก',
        'groups'  => 'เฉพาะกลุ่ม',
    ];
    return $labels[ $visibility ] ?? $labels['public'];
}
}

if ( ! function_exists( 'tb4cm_visibility_icon_class' ) ) {
function tb4cm_visibility_icon_class( $visibility ) {
    $visibility = sanitize_key( $visibility ?: 'public' );
    if ( 'groups' === $visibility ) {
        return 'ph-users-three';
    }
    if ( 'members' === $visibility ) {
        return 'ph-lock-key';
    }
    return 'ph-globe-hemisphere-east';
}
}


/**
 * Build the canonical Community feed query used by the first render and load-more AJAX.
 */
function tb4cm_get_community_feed_query_args( $active_topic = 'all', $search_query = '', $paged = 1, $per_page = 6 ) {
    $active_topic = sanitize_key( $active_topic ?: 'all' );
    $paged        = max( 1, absint( $paged ) );
    $per_page     = min( 20, max( 1, absint( $per_page ) ) );

    $allowed_topics = [ 'all', 'following', 'latest', 'popular', 'question', 'idea', 'project', 'event', 'creator' ];
    if ( ! in_array( $active_topic, $allowed_topics, true ) ) {
        $active_topic = 'all';
    }

    $query_args = [
        'post_type'              => 'tb4_community_post',
        'posts_per_page'         => $per_page,
        'paged'                  => $paged,
        'post_status'            => 'publish',
        'ignore_sticky_posts'    => true,
        'no_found_rows'          => false,
        'update_post_meta_cache' => true,
        'update_post_term_cache' => true,
        'meta_query'             => [],
    ];

    $search_query = sanitize_text_field( (string) $search_query );
    if ( '' !== $search_query ) {
        $query_args['s'] = $search_query;
    }

    if ( 'following' === $active_topic ) {
        $following_ids = ( is_user_logged_in() && function_exists( 'tb4cm_get_following_user_ids' ) ) ? tb4cm_get_following_user_ids() : [];
        $query_args['author__in'] = ! empty( $following_ids ) ? array_map( 'absint', $following_ids ) : [ 0 ];
    }

    if ( in_array( $active_topic, [ 'question', 'idea', 'project', 'event', 'creator' ], true ) ) {
        $query_args['meta_query'][] = [
            'key'   => 'tb4_post_type',
            'value' => $active_topic,
        ];
    }

    if ( 'popular' === $active_topic ) {
        $query_args['meta_key'] = 'tb4_like_count';
        $query_args['orderby']  = 'meta_value_num';
        $query_args['order']    = 'DESC';
    } else {
        $query_args['orderby'] = 'date';
        $query_args['order']   = 'DESC';
    }

    if ( empty( $query_args['meta_query'] ) ) {
        unset( $query_args['meta_query'] );
    }

    return apply_filters( 'tb4c_community_feed_query_args', $query_args, $active_topic, $search_query, $paged, $per_page );
}

/**
 * AJAX: load the next Community feed page without refreshing the screen.
 */
function tb4cm_load_more_community_feed() {
    tb4cm_verify_ajax_nonce();

    $page         = max( 2, absint( $_POST['page'] ?? 2 ) );
    $per_page     = min( 20, max( 1, absint( $_POST['per_page'] ?? 6 ) ) );
    $active_topic = sanitize_key( wp_unslash( $_POST['topic'] ?? 'all' ) );
    $search_query = sanitize_text_field( wp_unslash( $_POST['search'] ?? '' ) );

    $query_args = tb4cm_get_community_feed_query_args( $active_topic, $search_query, $page, $per_page );
    $feed       = new WP_Query( $query_args );

    ob_start();
    if ( $feed->have_posts() ) {
        while ( $feed->have_posts() ) {
            $feed->the_post();
            include TB4CM_DIR . 'templates/parts/feed-post-card.php';
        }
        wp_reset_postdata();
    }
    $html = trim( ob_get_clean() );

    $max_pages = isset( $feed->max_num_pages ) ? (int) $feed->max_num_pages : 1;
    wp_send_json_success( [
        'html'       => $html,
        'page'       => $page,
        'next_page'  => $page + 1,
        'max_pages'  => $max_pages,
        'has_more'   => $page < $max_pages,
        'count'      => isset( $feed->post_count ) ? (int) $feed->post_count : 0,
        'message'    => $html ? __( 'โหลดโพสต์เพิ่มเติมแล้ว', 'thinkb4do-community' ) : __( 'ไม่มีโพสต์เพิ่มเติม', 'thinkb4do-community' ),
    ] );
}
add_action( 'wp_ajax_tb4c_load_more_community_feed', 'tb4cm_load_more_community_feed' );
add_action( 'wp_ajax_nopriv_tb4c_load_more_community_feed', 'tb4cm_load_more_community_feed' );

/**
 * AJAX: load more posts for the Member Area overview feed.
 * Keeps the member feed visually identical to the main feed while staying scoped to one author.
 */
function tb4cm_load_more_member_feed() {
    tb4cm_verify_ajax_nonce();

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => __( 'กรุณาเข้าสู่ระบบ', 'thinkb4do-community' ) ], 401 );
    }

    $current_user_id = get_current_user_id();
    $author_id       = absint( $_POST['author_id'] ?? $current_user_id );
    $page            = max( 2, absint( $_POST['page'] ?? 2 ) );
    $per_page        = min( 12, max( 1, absint( $_POST['per_page'] ?? 6 ) ) );
    $status_filter   = sanitize_key( wp_unslash( $_POST['status_filter'] ?? 'all' ) );

    if ( $author_id !== $current_user_id && ! current_user_can( 'edit_users' ) ) {
        $author_id = $current_user_id;
    }

    $allowed_status_filters = [ 'all', 'publish', 'pending', 'draft', 'private' ];
    if ( ! in_array( $status_filter, $allowed_status_filters, true ) ) {
        $status_filter = 'all';
    }

    $post_statuses = 'all' === $status_filter ? [ 'publish', 'pending', 'draft', 'private' ] : [ $status_filter ];
    if ( $author_id !== $current_user_id && ! current_user_can( 'edit_others_posts' ) ) {
        $post_statuses = [ 'publish' ];
    }

    $feed = new WP_Query( [
        'post_type'              => 'tb4_community_post',
        'post_status'            => $post_statuses,
        'author'                 => $author_id,
        'posts_per_page'         => $per_page,
        'paged'                  => $page,
        'orderby'                => 'date',
        'order'                  => 'DESC',
        'ignore_sticky_posts'    => true,
        'no_found_rows'          => false,
        'update_post_meta_cache' => true,
        'update_post_term_cache' => true,
    ] );

    ob_start();
    if ( $feed->have_posts() ) {
        while ( $feed->have_posts() ) {
            $feed->the_post();
            include TB4CM_DIR . 'templates/parts/feed-post-card.php';
        }
        wp_reset_postdata();
    }
    $html = trim( ob_get_clean() );

    $max_pages = isset( $feed->max_num_pages ) ? (int) $feed->max_num_pages : 1;
    wp_send_json_success( [
        'html'      => $html,
        'page'      => $page,
        'next_page' => $page + 1,
        'max_pages' => $max_pages,
        'has_more'  => $page < $max_pages,
        'count'     => isset( $feed->post_count ) ? (int) $feed->post_count : 0,
        'message'   => $html ? __( 'โหลดโพสต์สมาชิกเพิ่มเติมแล้ว', 'thinkb4do-community' ) : __( 'ไม่มีโพสต์เพิ่มเติม', 'thinkb4do-community' ),
    ] );
}
add_action( 'wp_ajax_tb4c_load_more_member_feed', 'tb4cm_load_more_member_feed' );

/**
 * AJAX helpers.
 */
function tb4cm_verify_ajax_nonce() {
    $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
    if ( wp_verify_nonce( $nonce, 'tb4c_nonce' ) || wp_verify_nonce( $nonce, 'tb4_nonce' ) ) {
        return true;
    }
    wp_send_json_error( [ 'message' => __( 'Security check failed', 'thinkb4do-community' ) ], 403 );
}

function tb4cm_submit_member_page() {
    tb4cm_verify_ajax_nonce();

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => __( 'กรุณาเข้าสู่ระบบ', 'thinkb4do-community' ) ], 401 );
    }

    wp_send_json_success( [
        'message' => __( 'ระบบพื้นที่สมาชิกถูกย้ายไปรวมใน member-area แล้ว', 'thinkb4do-community' ),
        'post_id' => 0,
        'url'     => tb4cm_get_member_area_url(),
    ] );
}
add_action( 'wp_ajax_tb4_submit_member_page', 'tb4cm_submit_member_page' );


function tb4cm_parse_tag_input( $raw_tags ) {
    $raw_tags = wp_strip_all_tags( (string) wp_unslash( $raw_tags ) );
    $raw_tags = str_replace( [ "\r", "\n", "\t" ], ' ', $raw_tags );
    $parts    = preg_split( '/[#,\s]+/u', $raw_tags, -1, PREG_SPLIT_NO_EMPTY );
    $tags     = [];

    foreach ( (array) $parts as $part ) {
        $tag = trim( sanitize_text_field( $part ) );
        $tag = trim( $tag, "#.,;:|/\\'\"()[]{}<>" );
        if ( '' === $tag ) {
            continue;
        }
        if ( function_exists( 'mb_substr' ) ) {
            $tag = mb_substr( $tag, 0, 32 );
        } else {
            $tag = substr( $tag, 0, 32 );
        }
        $tags[] = $tag;
        if ( count( $tags ) >= 10 ) {
            break;
        }
    }

    return array_values( array_unique( $tags ) );
}

function tb4cm_set_post_tags_from_input( $post_id, $raw_tags ) {
    $tags = tb4cm_parse_tag_input( $raw_tags );
    wp_set_object_terms( $post_id, $tags, 'tb4_community_tag', false );
    update_post_meta( $post_id, 'tb4_post_tags', implode( ', ', $tags ) );
    return $tags;
}

function tb4cm_get_post_tag_input_value( $post_id ) {
    $terms = get_the_terms( $post_id, 'tb4_community_tag' );
    if ( empty( $terms ) || is_wp_error( $terms ) ) {
        return (string) get_post_meta( $post_id, 'tb4_post_tags', true );
    }
    return implode( ', ', wp_list_pluck( $terms, 'name' ) );
}


if ( ! function_exists( 'tb4cm_render_post_type_tag_strip' ) ) {
/**
 * v4.2.210: Render the compact Instagram-style type + custom tag strip.
 * Shows the post type/category and user-created tags directly below the post description,
 * before image/video media, so the feed context is visible before the visual content.
 */
function tb4cm_render_post_type_tag_strip( $post_id, $type = '', $limit = 6 ) {
    $post_id = absint( $post_id );
    if ( ! $post_id ) {
        return '';
    }

    $type       = $type ? sanitize_key( $type ) : ( get_post_meta( $post_id, 'tb4_post_type', true ) ?: 'discussion' );
    $type_label = function_exists( 'tb4cm_topic_label' ) ? tb4cm_topic_label( $type ) : $type;
    $limit      = max( 1, min( 10, absint( $limit ) ) );
    $html       = '<div class="tb4c-tags tb4c-ig-hashtags tb4c-ig-custom-tags tb4c-v42210-tag-strip" aria-label="ประเภทและแท็กของโพสต์">';

    if ( $type_label ) {
        $html .= '<span class="tb4c-v42210-type-chip"><span>ประเภท</span>' . esc_html( $type_label ) . '</span>';
    }

    $seen = [];
    if ( $type_label ) {
        $seen[ sanitize_title( $type_label ) ] = true;
    }
    $taxonomies = [ 'tb4_community_topic', 'tb4_community_tag' ];
    foreach ( $taxonomies as $taxonomy ) {
        $terms = get_the_terms( $post_id, $taxonomy );
        if ( empty( $terms ) || is_wp_error( $terms ) ) {
            continue;
        }
        foreach ( $terms as $term ) {
            if ( ! $term || empty( $term->name ) ) {
                continue;
            }
            $key = sanitize_title( $term->name );
            if ( isset( $seen[ $key ] ) ) {
                continue;
            }
            $seen[ $key ] = true;
            $term_link = get_term_link( $term );
            if ( is_wp_error( $term_link ) ) {
                continue;
            }
            $html .= '<a class="tb4c-v42210-tag-chip" href="' . esc_url( $term_link ) . '">#' . esc_html( $term->name ) . '</a>';
            if ( count( $seen ) >= $limit ) {
                break 2;
            }
        }
    }

    $html .= '</div>';
    return $html;
}
}

function tb4cm_parse_poll_options( $raw_options ) {
    $raw_options = wp_strip_all_tags( (string) wp_unslash( $raw_options ) );
    $raw_options = str_replace( [ "
", "
" ], "
", $raw_options );
    $parts       = preg_split( '/[
|]+/u', $raw_options, -1, PREG_SPLIT_NO_EMPTY );
    $options     = [];

    foreach ( (array) $parts as $part ) {
        $option = trim( sanitize_text_field( $part ) );
        if ( '' === $option ) {
            continue;
        }
        if ( function_exists( 'mb_substr' ) ) {
            $option = mb_substr( $option, 0, 80 );
        } else {
            $option = substr( $option, 0, 80 );
        }
        $options[] = $option;
        if ( count( $options ) >= 6 ) {
            break;
        }
    }

    return array_values( array_unique( $options ) );
}

function tb4cm_save_post_poll_from_input( $post_id, $question_raw, $options_raw ) {
    $question = trim( sanitize_text_field( wp_unslash( $question_raw ) ) );
    $options  = tb4cm_parse_poll_options( $options_raw );

    if ( '' === $question || count( $options ) < 2 ) {
        delete_post_meta( $post_id, 'tb4_poll_question' );
        delete_post_meta( $post_id, 'tb4_poll_options' );
        delete_post_meta( $post_id, 'tb4_poll_votes' );
        return false;
    }

    $old_options = get_post_meta( $post_id, 'tb4_poll_options', true );
    $old_votes   = get_post_meta( $post_id, 'tb4_poll_votes', true );
    $old_options = is_array( $old_options ) ? array_values( $old_options ) : [];
    $old_votes   = is_array( $old_votes ) ? array_values( $old_votes ) : [];
    $votes       = [];

    foreach ( $options as $option ) {
        $old_index = array_search( $option, $old_options, true );
        $votes[]   = false !== $old_index && isset( $old_votes[ $old_index ] ) ? max( 0, absint( $old_votes[ $old_index ] ) ) : 0;
    }

    update_post_meta( $post_id, 'tb4_poll_question', $question );
    update_post_meta( $post_id, 'tb4_poll_options', $options );
    update_post_meta( $post_id, 'tb4_poll_votes', $votes );
    return true;
}

function tb4cm_get_post_poll_data( $post_id ) {
    $question = trim( (string) get_post_meta( $post_id, 'tb4_poll_question', true ) );
    $options  = get_post_meta( $post_id, 'tb4_poll_options', true );
    $votes    = get_post_meta( $post_id, 'tb4_poll_votes', true );
    $options  = is_array( $options ) ? array_values( array_map( 'strval', $options ) ) : [];
    $votes    = is_array( $votes ) ? array_values( array_map( 'absint', $votes ) ) : [];

    if ( '' === $question || count( $options ) < 2 ) {
        return null;
    }

    foreach ( $options as $index => $option ) {
        if ( ! isset( $votes[ $index ] ) ) {
            $votes[ $index ] = 0;
        }
    }

    return [
        'question' => $question,
        'options'  => $options,
        'votes'    => array_slice( $votes, 0, count( $options ) ),
        'total'    => array_sum( array_slice( $votes, 0, count( $options ) ) ),
    ];
}

function tb4cm_render_post_poll( $post_id ) {
    $poll = tb4cm_get_post_poll_data( $post_id );
    if ( ! $poll ) {
        return '';
    }

    ob_start();
    ?>
    <section class="tb4c-poll-card" data-tb4c-poll="<?php echo esc_attr( $post_id ); ?>" aria-label="โพลของโพสต์">
      <div class="tb4c-poll-head">
        <span><?php echo tb4cm_icon_markup( 'ph-chart-bar', '▥' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
        <strong><?php echo esc_html( $poll['question'] ); ?></strong>
      </div>
      <div class="tb4c-poll-options">
        <?php foreach ( $poll['options'] as $index => $option ) :
            $count   = isset( $poll['votes'][ $index ] ) ? absint( $poll['votes'][ $index ] ) : 0;
            $percent = $poll['total'] > 0 ? round( ( $count / $poll['total'] ) * 100 ) : 0;
        ?>
          <button type="button" class="tb4c-poll-option" data-tb4c-poll-vote data-post-id="<?php echo esc_attr( $post_id ); ?>" data-poll-option="<?php echo esc_attr( $index ); ?>">
            <i style="width: <?php echo esc_attr( $percent ); ?>%"></i>
            <span><?php echo esc_html( $option ); ?></span>
            <em data-tb4c-poll-percent><?php echo esc_html( $percent ); ?>%</em>
          </button>
        <?php endforeach; ?>
      </div>
      <small data-tb4c-poll-total><?php echo esc_html( number_format_i18n( $poll['total'] ) ); ?> โหวต</small>
    </section>
    <?php
    return ob_get_clean();
}

function tb4cm_vote_post_poll() {
    tb4cm_verify_ajax_nonce();

    $post_id = absint( $_POST['post_id'] ?? 0 );
    $option  = absint( $_POST['option'] ?? 0 );

    if ( ! $post_id || 'tb4_community_post' !== get_post_type( $post_id ) ) {
        wp_send_json_error( [ 'message' => __( 'ไม่พบโพสต์สำหรับโหวต', 'thinkb4do-community' ) ], 404 );
    }

    $poll = tb4cm_get_post_poll_data( $post_id );
    if ( ! $poll || ! isset( $poll['options'][ $option ] ) ) {
        wp_send_json_error( [ 'message' => __( 'ไม่พบตัวเลือกโหวต', 'thinkb4do-community' ) ], 422 );
    }

    $vote_key = 'tb4c_poll_' . $post_id;
    if ( is_user_logged_in() ) {
        $voted = (array) get_user_meta( get_current_user_id(), 'tb4c_voted_polls', true );
        if ( isset( $voted[ $post_id ] ) ) {
            wp_send_json_error( [ 'message' => __( 'คุณโหวตโพลนี้แล้ว', 'thinkb4do-community' ) ], 409 );
        }
        $voted[ $post_id ] = $option;
        update_user_meta( get_current_user_id(), 'tb4c_voted_polls', $voted );
    }

    $votes = $poll['votes'];
    $votes[ $option ] = isset( $votes[ $option ] ) ? absint( $votes[ $option ] ) + 1 : 1;
    update_post_meta( $post_id, 'tb4_poll_votes', $votes );

    $total    = array_sum( $votes );
    $percents = [];
    foreach ( $poll['options'] as $index => $label ) {
        $count      = isset( $votes[ $index ] ) ? absint( $votes[ $index ] ) : 0;
        $percents[] = $total > 0 ? round( ( $count / $total ) * 100 ) : 0;
    }

    wp_send_json_success( [
        'message'  => __( 'บันทึกโหวตแล้ว', 'thinkb4do-community' ),
        'total'    => $total,
        'percents' => $percents,
        'option'   => $option,
    ] );
}
add_action( 'wp_ajax_tb4c_vote_post_poll', 'tb4cm_vote_post_poll' );
add_action( 'wp_ajax_nopriv_tb4c_vote_post_poll', 'tb4cm_vote_post_poll' );

function tb4cm_submit_community_post() {
    tb4cm_verify_ajax_nonce();

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => __( 'กรุณาเข้าสู่ระบบก่อนสร้างโพสต์', 'thinkb4do-community' ) ], 401 );
    }

    $title      = sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) );
    $content    = wp_kses_post( wp_unslash( $_POST['content'] ?? '' ) );
    if ( '' === $title ) {
        $auto_title_source = trim( wp_strip_all_tags( $content ) );
        $title = $auto_title_source ? wp_trim_words( $auto_title_source, 10, '' ) : __( 'โพสต์ใหม่', 'thinkb4do-community' );
    }
    if ( '' === $title ) {
        $auto_title_source = trim( wp_strip_all_tags( $content ) );
        $title = $auto_title_source ? wp_trim_words( $auto_title_source, 10, '' ) : __( 'โพสต์ใหม่', 'thinkb4do-community' );
    }
    $media_url   = trim( (string) wp_unslash( $_POST['media_url'] ?? '' ) );
    $media       = null;
    $media_album = [];
    $post_type   = sanitize_key( wp_unslash( $_POST['post_type'] ?? 'discussion' ) );
    $visibility = sanitize_key( wp_unslash( $_POST['visibility'] ?? 'public' ) );
    $topic      = sanitize_key( wp_unslash( $_POST['topic'] ?? $post_type ) );
    $tags_input = (string) wp_unslash( $_POST['tags'] ?? '' );
    $new_tag_input = (string) wp_unslash( $_POST['new_tag'] ?? '' );
    if ( '' !== trim( wp_strip_all_tags( $new_tag_input ) ) ) {
        $tags_input = trim( $tags_input . ' ' . $new_tag_input );
    }
    $feeling    = sanitize_text_field( wp_unslash( $_POST['feeling'] ?? '' ) );
    $poll_question = sanitize_text_field( wp_unslash( $_POST['poll_question'] ?? '' ) );
    $poll_options  = (string) wp_unslash( $_POST['poll_options'] ?? '' );

    $allowed_types = [ 'discussion', 'question', 'idea', 'project', 'event', 'creator' ];
    if ( ! in_array( $post_type, $allowed_types, true ) ) {
        $post_type = 'discussion';
    }

    if ( ! in_array( $visibility, [ 'public', 'members', 'groups' ], true ) ) {
        $visibility = 'public';
    }

    $upload_album = tb4cm_handle_media_upload_album( 'media_files', 10 );
    if ( is_wp_error( $upload_album ) ) {
        wp_send_json_error( [ 'message' => $upload_album->get_error_message() ], 422 );
    }

    if ( ! empty( $upload_album ) ) {
        $media_album = $upload_album;
        $media       = $media_album[0];
    } else {
        $upload_media = tb4cm_handle_media_upload( 'media_file' );
        if ( is_wp_error( $upload_media ) ) {
            wp_send_json_error( [ 'message' => $upload_media->get_error_message() ], 422 );
        }
        if ( is_array( $upload_media ) ) {
            $media_album = [ $upload_media ];
            $media       = $upload_media;
        } elseif ( $media_url ) {
            $url_album = tb4cm_validate_trusted_media_urls( $media_url, 10 );
            if ( is_wp_error( $url_album ) ) {
                wp_send_json_error( [ 'message' => $url_album->get_error_message() ], 422 );
            }
            $media_album = $url_album;
            $media       = $media_album[0];
        }
    }

    if ( '' === trim( wp_strip_all_tags( $content ) ) && empty( $media_album ) ) {
        wp_send_json_error( [ 'message' => __( 'กรุณาใส่ข้อความ, URL รูป/วิดีโอ หรืออัปโหลดไฟล์', 'thinkb4do-community' ) ], 422 );
    }

    $status = current_user_can( 'publish_posts' ) ? 'publish' : 'pending';

    $post_id = wp_insert_post( [
        'post_type'    => 'tb4_community_post',
        'post_title'   => $title,
        'post_content' => $content,
        'post_status'  => $status,
        'post_author'  => get_current_user_id(),
        'comment_status' => 'open',
    ], true );

    if ( is_wp_error( $post_id ) ) {
        wp_send_json_error( [ 'message' => $post_id->get_error_message() ], 500 );
    }

    foreach ( $media_album as $album_item ) {
        if ( ! empty( $album_item['attachment_id'] ) ) {
            wp_update_post( [
                'ID'          => absint( $album_item['attachment_id'] ),
                'post_parent' => $post_id,
            ] );
        }
    }

    update_post_meta( $post_id, 'tb4_post_type', $post_type );
    update_post_meta( $post_id, 'tb4_post_visibility', $visibility );
    update_post_meta( $post_id, 'tb4_post_review_status', 'publish' === $status ? 'approved' : 'pending' );
    update_post_meta( $post_id, 'tb4_post_quality_score', tb4cm_calculate_post_quality_score( $title, $content . ' ' . ( $media['url'] ?? '' ) ) );
    if ( $media ) {
        update_post_meta( $post_id, 'tb4_media_url', $media['url'] );
        update_post_meta( $post_id, 'tb4_media_album', wp_json_encode( array_values( $media_album ) ) );
        if ( ! empty( $media['attachment_id'] ) ) {
            update_post_meta( $post_id, 'tb4_media_attachment_id', absint( $media['attachment_id'] ) );
        }
    } else {
        delete_post_meta( $post_id, 'tb4_media_url' );
        delete_post_meta( $post_id, 'tb4_media_attachment_id' );
        delete_post_meta( $post_id, 'tb4_media_album' );
    }
    update_post_meta( $post_id, 'tb4_like_count', 0 );
    update_post_meta( $post_id, 'tb4_bookmark_count', 0 );
    update_post_meta( $post_id, 'tb4_view_count', 0 );

    if ( $topic ) {
        if ( ! term_exists( $topic, 'tb4_community_topic' ) ) {
            $label = tb4cm_topic_label( $topic );
            wp_insert_term( $label, 'tb4_community_topic', [ 'slug' => $topic ] );
        }
        wp_set_object_terms( $post_id, [ $topic ], 'tb4_community_topic', false );
    }

    tb4cm_set_post_tags_from_input( $post_id, $tags_input );
    update_post_meta( $post_id, 'tb4_post_feeling', $feeling );
    tb4cm_save_post_poll_from_input( $post_id, $poll_question, $poll_options );

    wp_send_json_success( [
        'message' => 'publish' === $status ? __( 'เผยแพร่โพสต์แล้ว', 'thinkb4do-community' ) : __( 'ส่งโพสต์สำเร็จ ขอบคุณที่แบ่งปัน', 'thinkb4do-community' ),
        'post_id' => $post_id,
        'status'  => $status,
        'url'     => get_permalink( $post_id ),
    ] );
}
add_action( 'wp_ajax_tb4_submit_community_post', 'tb4cm_submit_community_post' );

function tb4cm_react_community_post() {
    tb4cm_verify_ajax_nonce();

    $post_id  = absint( $_POST['post_id'] ?? 0 );
    $reaction = sanitize_key( wp_unslash( $_POST['reaction'] ?? 'like' ) );

    if ( ! $post_id || 'tb4_community_post' !== get_post_type( $post_id ) ) {
        wp_send_json_error( [ 'message' => __( 'ไม่พบโพสต์', 'thinkb4do-community' ) ], 404 );
    }

    $reaction = 'bookmark' === $reaction ? 'bookmark' : 'like';
    $meta_key = 'bookmark' === $reaction ? 'tb4_bookmark_count' : 'tb4_like_count';
    $count    = max( 0, absint( get_post_meta( $post_id, $meta_key, true ) ) );
    $state    = 'added';

    if ( is_user_logged_in() ) {
        $user_id       = get_current_user_id();
        $user_meta_key = 'bookmark' === $reaction ? 'tb4c_bookmarked_post_ids' : 'tb4c_liked_post_ids';
        $ids           = tb4cm_get_user_meta_ids( $user_id, $user_meta_key );
        $has_item      = in_array( $post_id, $ids, true );

        if ( $has_item ) {
            $ids   = array_values( array_diff( $ids, [ $post_id ] ) );
            $count = max( 0, $count - 1 );
            $state = 'removed';
        } else {
            $ids[] = $post_id;
            $ids   = array_values( array_unique( array_map( 'absint', $ids ) ) );
            $count++;
            $state = 'added';
        }

        update_user_meta( $user_id, $user_meta_key, $ids );
    } else {
        $count++;
    }

    update_post_meta( $post_id, $meta_key, $count );

    wp_send_json_success( [
        'post_id'  => $post_id,
        'reaction' => $reaction,
        'state'    => $state,
        'count'    => $count,
    ] );
}
add_action( 'wp_ajax_tb4_react_community_post', 'tb4cm_react_community_post' );
add_action( 'wp_ajax_nopriv_tb4_react_community_post', 'tb4cm_react_community_post' );


function tb4cm_save_member_area_profile() {
    tb4cm_verify_ajax_nonce();

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => __( 'กรุณาเข้าสู่ระบบก่อนแก้ไขพื้นที่สมาชิก', 'thinkb4do-community' ) ], 401 );
    }

    $user_id        = get_current_user_id();
    $display_name   = sanitize_text_field( wp_unslash( $_POST['display_name'] ?? '' ) );
    $bio            = sanitize_textarea_field( wp_unslash( $_POST['bio'] ?? '' ) );
    $role           = sanitize_text_field( wp_unslash( $_POST['member_role'] ?? '' ) );
    $location       = sanitize_text_field( wp_unslash( $_POST['member_location'] ?? '' ) );
    $interests      = sanitize_text_field( wp_unslash( $_POST['member_interests'] ?? '' ) );
    $contact_url    = esc_url_raw( wp_unslash( $_POST['contact_url'] ?? '' ) );
    $member_title   = sanitize_text_field( wp_unslash( $_POST['member_title'] ?? '' ) );
    $member_content = wp_kses_post( wp_unslash( $_POST['member_content'] ?? '' ) );

    if ( '' === $display_name ) {
        wp_send_json_error( [ 'message' => __( 'กรุณาใส่ชื่อที่ต้องการแสดง', 'thinkb4do-community' ) ], 422 );
    }

    $updated_user = wp_update_user( [
        'ID'           => $user_id,
        'display_name' => $display_name,
        'description'  => $bio,
    ] );

    if ( is_wp_error( $updated_user ) ) {
        wp_send_json_error( [ 'message' => $updated_user->get_error_message() ], 500 );
    }

    update_user_meta( $user_id, 'tb4c_member_role', $role );
    update_user_meta( $user_id, 'tb4c_member_location', $location );
    update_user_meta( $user_id, 'tb4c_member_interests', $interests );
    update_user_meta( $user_id, 'tb4c_member_contact_url', $contact_url );
    update_user_meta( $user_id, 'tb4c_member_title', $member_title ?: $display_name );
    update_user_meta( $user_id, 'tb4c_member_content', $member_content ?: $bio );

    $cover_attachment_id  = (int) get_user_meta( $user_id, 'tb4c_member_cover_id', true );
    $avatar_attachment_id = (int) get_user_meta( $user_id, 'tb4c_member_avatar_id', true );

    if ( ! empty( $_POST['remove_member_cover'] ) ) {
        delete_user_meta( $user_id, 'tb4c_member_cover_id' );
        $cover_attachment_id = 0;
    }

    if ( ! empty( $_POST['remove_member_avatar'] ) ) {
        delete_user_meta( $user_id, 'tb4c_member_avatar_id' );
        delete_user_meta( $user_id, 'tb4c_member_avatar_url' );
        $avatar_attachment_id = 0;
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    if ( ! empty( $_FILES['member_avatar']['name'] ) ) {
        $file_type = wp_check_filetype_and_ext( $_FILES['member_avatar']['tmp_name'], $_FILES['member_avatar']['name'] );
        $mime_type = $file_type['type'] ?? '';

        if ( 0 !== strpos( (string) $mime_type, 'image/' ) ) {
            wp_send_json_error( [ 'message' => __( 'กรุณาอัปโหลดไฟล์รูปภาพสำหรับรูปโปรไฟล์เท่านั้น', 'thinkb4do-community' ) ], 422 );
        }

        $uploaded_avatar_id = tb4cm_with_user_upload_folder( 'profile', static function() {
            return media_handle_upload( 'member_avatar', 0 );
        }, $user_id );

        if ( is_wp_error( $uploaded_avatar_id ) ) {
            wp_send_json_error( [ 'message' => $uploaded_avatar_id->get_error_message() ], 500 );
        }

        $avatar_attachment_id = (int) $uploaded_avatar_id;
        update_user_meta( $user_id, 'tb4c_member_avatar_id', $avatar_attachment_id );
        update_user_meta( $user_id, 'tb4c_member_avatar_url', esc_url_raw( wp_get_attachment_url( $avatar_attachment_id ) ) );
        tb4cm_record_user_media_asset( $user_id, $avatar_attachment_id, 'profile' );
    }

    if ( ! empty( $_FILES['member_cover']['name'] ) ) {
        $file_type = wp_check_filetype_and_ext( $_FILES['member_cover']['tmp_name'], $_FILES['member_cover']['name'] );
        $mime_type = $file_type['type'] ?? '';

        if ( 0 !== strpos( (string) $mime_type, 'image/' ) ) {
            wp_send_json_error( [ 'message' => __( 'กรุณาอัปโหลดไฟล์รูปภาพสำหรับ Cover เท่านั้น', 'thinkb4do-community' ) ], 422 );
        }

        $uploaded_cover_id = tb4cm_with_user_upload_folder( 'cover', static function() {
            return media_handle_upload( 'member_cover', 0 );
        }, $user_id );

        if ( is_wp_error( $uploaded_cover_id ) ) {
            wp_send_json_error( [ 'message' => $uploaded_cover_id->get_error_message() ], 500 );
        }

        $cover_attachment_id = (int) $uploaded_cover_id;
        update_user_meta( $user_id, 'tb4c_member_cover_id', $cover_attachment_id );
        tb4cm_record_user_media_asset( $user_id, $cover_attachment_id, 'cover' );
    }

    $score      = tb4cm_get_member_profile_completeness( $user_id, 0 )['score'];
    $cover_url  = tb4cm_get_member_cover_url( $user_id, false );
    $avatar_url = tb4cm_get_user_avatar_url( $user_id, [ 'size' => 256 ] );

    wp_send_json_success( [
        'message'        => __( 'บันทึกพื้นที่สมาชิกแล้ว และเชื่อมรูปโปรไฟล์/รูป Cover ทุกหน้าแล้ว', 'thinkb4do-community' ),
        'member_page_id' => 0,
        'profile_url'    => tb4cm_get_member_area_url(),
        'score'          => $score,
        'cover_url'      => $cover_url ? esc_url_raw( $cover_url ) : '',
        'avatar_url'     => $avatar_url ? esc_url_raw( $avatar_url ) : '',
        'asset_folder'   => tb4cm_get_user_asset_folder_label( $user_id ),
    ] );
}
add_action( 'wp_ajax_tb4c_save_member_area_profile', 'tb4cm_save_member_area_profile' );

/**
 * Social comment permissions and AJAX actions.
 */
function tb4cm_get_comment_object( $comment_id ) {
    $comment = get_comment( absint( $comment_id ) );
    if ( ! $comment || ! $comment->comment_ID ) {
        return null;
    }
    if ( 'tb4_community_post' !== get_post_type( (int) $comment->comment_post_ID ) ) {
        return null;
    }
    return $comment;
}

function tb4cm_user_can_edit_community_comment( $comment ) {
    $comment = is_numeric( $comment ) ? tb4cm_get_comment_object( $comment ) : $comment;
    if ( ! $comment || ! is_user_logged_in() ) {
        return false;
    }

    $user_id = get_current_user_id();
    if ( current_user_can( 'moderate_comments' ) || current_user_can( 'edit_comment', (int) $comment->comment_ID ) ) {
        return true;
    }

    return (int) $comment->user_id > 0 && (int) $comment->user_id === $user_id;
}

function tb4cm_user_can_delete_community_comment( $comment ) {
    $comment = is_numeric( $comment ) ? tb4cm_get_comment_object( $comment ) : $comment;
    if ( ! $comment || ! is_user_logged_in() ) {
        return false;
    }

    if ( tb4cm_user_can_edit_community_comment( $comment ) || current_user_can( 'delete_comment', (int) $comment->comment_ID ) ) {
        return true;
    }

    $post_author = (int) get_post_field( 'post_author', (int) $comment->comment_post_ID );
    return $post_author > 0 && $post_author === get_current_user_id();
}

function tb4cm_get_approved_comment_count( $post_id ) {
    $counts = wp_count_comments( absint( $post_id ) );
    return max( 0, (int) ( $counts->approved ?? 0 ) );
}

function tb4cm_get_comment_tree( $post_id, $number = 80 ) {
    $comments = get_comments( [
        'post_id' => absint( $post_id ),
        'status'  => 'approve',
        'order'   => 'ASC',
        'number'  => absint( $number ),
    ] );

    $tree = [];
    foreach ( $comments as $comment ) {
        $parent_id = max( 0, (int) $comment->comment_parent );
        if ( ! isset( $tree[ $parent_id ] ) ) {
            $tree[ $parent_id ] = [];
        }
        $tree[ $parent_id ][] = $comment;
    }
    return $tree;
}


function tb4cm_sanitize_media_album_meta( $value ) {
    if ( is_string( $value ) ) {
        $decoded = json_decode( $value, true );
        if ( is_array( $decoded ) ) {
            $value = $decoded;
        } else {
            return sanitize_textarea_field( $value );
        }
    }

    if ( ! is_array( $value ) ) {
        return '';
    }

    $clean = [];
    foreach ( $value as $item ) {
        if ( ! is_array( $item ) || empty( $item['url'] ) || empty( $item['type'] ) ) {
            continue;
        }
        $type = sanitize_key( $item['type'] );
        if ( ! in_array( $type, [ 'image', 'video' ], true ) ) {
            continue;
        }
        $clean[] = [
            'url'           => esc_url_raw( $item['url'] ),
            'type'          => $type,
            'attachment_id' => ! empty( $item['attachment_id'] ) ? absint( $item['attachment_id'] ) : 0,
            'source'        => ! empty( $item['source'] ) ? sanitize_key( $item['source'] ) : 'url',
            'poster_url'    => ! empty( $item['poster_url'] ) ? esc_url_raw( $item['poster_url'] ) : '',
        ];
    }

    return ! empty( $clean ) ? wp_json_encode( array_values( $clean ) ) : '';
}


function tb4cm_trim_media_url_candidate( $url ) {
    $url = trim( (string) $url );
    return rtrim( $url, " \t\n\r\0\x0B.,);]}>\"'" );
}

function tb4cm_host_is_trusted_media_source( $host ) {
    $host = strtolower( trim( (string) $host ) );
    if ( '' === $host ) {
        return false;
    }
    if ( 0 === strpos( $host, 'www.' ) ) {
        $host = substr( $host, 4 );
    }
    if ( in_array( $host, [ 'localhost' ], true ) || false !== strpos( $host, '..' ) || false === strpos( $host, '.' ) ) {
        return false;
    }
    if ( filter_var( $host, FILTER_VALIDATE_IP ) ) {
        return (bool) filter_var( $host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE );
    }
    if ( preg_match( '~(^|\.)local$~i', $host ) ) {
        return false;
    }
    return true;
}

function tb4cm_media_url_type( $url ) {
    $url  = (string) $url;
    $path = (string) wp_parse_url( $url, PHP_URL_PATH );

    if ( preg_match( '~\.(jpe?g|png|gif|webp|avif)$~i', $path ) ) {
        return 'image';
    }
    if ( preg_match( '~\.(mp4|m4v|webm|ogv|ogg)$~i', $path ) ) {
        return 'video';
    }
    if ( preg_match( '~(youtube\.com|youtu\.be|vimeo\.com)~i', $url ) ) {
        return 'video';
    }
    return '';
}

function tb4cm_validate_trusted_media_url( $url ) {
    $url = tb4cm_trim_media_url_candidate( $url );
    if ( '' === $url ) {
        return new WP_Error( 'tb4c_empty_media_url', __( 'กรุณาใส่ URL รูปภาพหรือวิดีโอ', 'thinkb4do-community' ) );
    }

    $url = esc_url_raw( $url, [ 'http', 'https' ] );
    if ( ! $url || ! wp_http_validate_url( $url ) ) {
        return new WP_Error( 'tb4c_invalid_media_url', __( 'URL นี้ไม่ถูกต้อง หรือไม่ใช่ http/https', 'thinkb4do-community' ) );
    }

    $host = wp_parse_url( $url, PHP_URL_HOST );
    if ( ! tb4cm_host_is_trusted_media_source( $host ) ) {
        return new WP_Error( 'tb4c_untrusted_media_host', __( 'URL นี้ดูไม่น่าเชื่อถือ กรุณาใช้ลิงก์จากแหล่งที่ตรวจสอบได้', 'thinkb4do-community' ) );
    }

    $type = tb4cm_media_url_type( $url );
    if ( ! $type ) {
        return new WP_Error( 'tb4c_unsupported_media_url', __( 'รองรับเฉพาะลิงก์รูปภาพ jpg/png/gif/webp/avif, วิดีโอ mp4/webm/ogg, YouTube หรือ Vimeo', 'thinkb4do-community' ) );
    }

    return [
        'url'  => $url,
        'type' => $type,
    ];
}


function tb4cm_extract_media_url_candidates( $raw ) {
    $raw = (string) $raw;
    if ( '' === trim( $raw ) ) {
        return [];
    }

    /* v4.2.207: normalize Gutenberg/oEmbed/raw iframe strings before extracting URLs.
     * This catches YouTube/Vimeo URLs stored in JSON, escaped slashes, entities, srcdoc,
     * and encoded oEmbed query params so outside covers can be rebuilt correctly.
     */
    $raw = html_entity_decode( $raw, ENT_QUOTES, 'UTF-8' );
    $raw = wp_unslash( $raw );
    $raw = str_replace( [ '\\/', '&amp;' ], [ '/', '&' ], $raw );
    $decoded = rawurldecode( $raw );
    if ( $decoded && $decoded !== $raw ) {
        $raw .= "\n" . $decoded;
    }

    if ( preg_match_all( "~https?://[^\\r\\n\\t <>\"']+~i", $raw, $matches ) ) {
        return array_values( array_unique( array_map( 'tb4cm_trim_media_url_candidate', $matches[0] ) ) );
    }

    return [ tb4cm_trim_media_url_candidate( $raw ) ];
}

function tb4cm_validate_trusted_media_urls( $raw, $limit = 10 ) {
    $urls  = tb4cm_extract_media_url_candidates( $raw );
    $items = [];
    $seen  = [];

    foreach ( $urls as $url ) {
        if ( count( $items ) >= $limit ) {
            break;
        }
        $media = tb4cm_validate_trusted_media_url( $url );
        if ( is_wp_error( $media ) ) {
            return $media;
        }
        $key = strtolower( $media['url'] );
        if ( isset( $seen[ $key ] ) ) {
            continue;
        }
        $seen[ $key ] = true;
        $items[] = $media + [ 'source' => 'url' ];
    }

    if ( empty( $items ) ) {
        return new WP_Error( 'tb4c_empty_media_url', __( 'กรุณาใส่ URL รูปภาพหรือวิดีโอ', 'thinkb4do-community' ) );
    }

    return $items;
}

function tb4cm_normalize_media_album( $value ) {
    if ( is_string( $value ) ) {
        $value = json_decode( $value, true );
    }
    if ( ! is_array( $value ) ) {
        return [];
    }

    $items = [];
    $seen  = [];
    foreach ( $value as $item ) {
        if ( ! is_array( $item ) || empty( $item['url'] ) || empty( $item['type'] ) ) {
            continue;
        }
        $url  = esc_url_raw( $item['url'] );
        $type = sanitize_key( $item['type'] );
        if ( ! $url || ! in_array( $type, [ 'image', 'video' ], true ) ) {
            continue;
        }
        $key = strtolower( $url );
        if ( isset( $seen[ $key ] ) ) {
            continue;
        }
        $seen[ $key ] = true;
        $items[] = [
            'url'           => $url,
            'type'          => $type,
            'attachment_id' => ! empty( $item['attachment_id'] ) ? absint( $item['attachment_id'] ) : 0,
            'source'        => ! empty( $item['source'] ) ? sanitize_key( $item['source'] ) : 'url',
            'poster_url'    => ! empty( $item['poster_url'] ) ? esc_url_raw( $item['poster_url'] ) : '',
        ];
    }

    return $items;
}


function tb4cm_youtube_video_id_from_url( $url ) {
    $url = html_entity_decode( rawurldecode( (string) $url ), ENT_QUOTES, 'UTF-8' );
    $url = trim( $url );

    if ( '' === $url ) {
        return '';
    }

    /* v4.2.206: accept raw iframe/oEmbed HTML, lazy iframe attributes, and thumbnail URLs.
     * Many posts arrive as WordPress oEmbed iframe markup rather than the original YouTube URL.
     */
    if ( preg_match( '~(?:youtube(?:-nocookie)?\.com/(?:embed|shorts|live)/|youtu\.be/|youtube\.com/watch\?[^\s"<>]*v=|ytimg\.com/vi/|img\.youtube\.com/vi/)([A-Za-z0-9_-]{6,})~i', $url, $quick ) ) {
        return $quick[1];
    }

    $host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
    $path = trim( (string) wp_parse_url( $url, PHP_URL_PATH ), '/' );

    if ( 0 === strpos( $host, 'www.' ) ) {
        $host = substr( $host, 4 );
    }

    if ( 'youtu.be' === $host && $path ) {
        $parts = explode( '/', $path );
        return preg_match( '~^[A-Za-z0-9_-]{6,}$~', $parts[0] ) ? $parts[0] : '';
    }

    if ( false !== strpos( $host, 'youtube.com' ) || false !== strpos( $host, 'youtube-nocookie.com' ) ) {
        $query = [];
        parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $query );
        if ( ! empty( $query['v'] ) && preg_match( '~^[A-Za-z0-9_-]{6,}$~', (string) $query['v'] ) ) {
            return (string) $query['v'];
        }
        if ( preg_match( '~(?:embed|shorts|live)/([A-Za-z0-9_-]{6,})~', $path, $match ) ) {
            return $match[1];
        }
    }

    return '';
}

function tb4cm_vimeo_video_id_from_url( $url ) {
    $host = strtolower( (string) wp_parse_url( (string) $url, PHP_URL_HOST ) );
    $path = trim( (string) wp_parse_url( (string) $url, PHP_URL_PATH ), '/' );
    if ( 0 === strpos( $host, 'www.' ) ) {
        $host = substr( $host, 4 );
    }
    if ( false === strpos( $host, 'vimeo.com' ) || '' === $path ) {
        return '';
    }
    if ( preg_match( '~(?:video/)?([0-9]{5,})~', $path, $match ) ) {
        return $match[1];
    }
    return '';
}

function tb4cm_oembed_thumbnail_url( $url ) {
    $url = esc_url_raw( (string) $url );
    if ( ! $url ) {
        return '';
    }

    $cache_key = 'tb4c_video_thumb_' . md5( $url );
    $cached    = get_transient( $cache_key );
    if ( is_string( $cached ) ) {
        return $cached;
    }

    $thumbnail = '';
    if ( function_exists( '_wp_oembed_get_object' ) ) {
        $oembed = _wp_oembed_get_object();
        if ( $oembed && method_exists( $oembed, 'get_data' ) ) {
            $data = $oembed->get_data( $url );
            if ( is_object( $data ) && ! empty( $data->thumbnail_url ) ) {
                $thumbnail = esc_url_raw( $data->thumbnail_url );
            }
        }
    }

    set_transient( $cache_key, $thumbnail, WEEK_IN_SECONDS );
    return $thumbnail;
}

function tb4cm_unique_media_urls( $urls ) {
    $clean = [];
    foreach ( (array) $urls as $url ) {
        $url = esc_url_raw( (string) $url );
        if ( $url && ! in_array( $url, $clean, true ) ) {
            $clean[] = $url;
        }
    }
    return $clean;
}

function tb4cm_vimeo_thumbnail_candidates( $thumbnail ) {
    $thumbnail = esc_url_raw( (string) $thumbnail );
    if ( ! $thumbnail ) {
        return [];
    }

    $candidates = [ $thumbnail ];

    /* Vimeo oEmbed thumbnails often contain a width suffix such as _640.
     * Try larger canonical suffixes first, then keep the original URL as a safe fallback.
     */
    if ( preg_match( '~_([0-9]{3,5})(\.[a-z0-9]+)(\?.*)?$~i', $thumbnail ) ) {
        foreach ( [ '1920', '1280', '960', '640' ] as $width ) {
            $candidates[] = preg_replace( '~_([0-9]{3,5})(\.[a-z0-9]+)(\?.*)?$~i', '_' . $width . '$2$3', $thumbnail );
        }
    }

    return tb4cm_unique_media_urls( $candidates );
}

function tb4cm_get_video_poster_urls( $media ) {
    if ( ! is_array( $media ) || 'video' !== sanitize_key( $media['type'] ?? '' ) ) {
        return [];
    }

    $urls = [];

    if ( ! empty( $media['poster_url'] ) ) {
        $urls[] = esc_url_raw( $media['poster_url'] );
    }

    if ( ! empty( $media['attachment_id'] ) ) {
        $attachment_id = absint( $media['attachment_id'] );
        $thumb_id      = (int) get_post_thumbnail_id( $attachment_id );
        if ( $thumb_id ) {
            foreach ( [ 'full', '2048x2048', 'large', 'medium_large' ] as $size ) {
                $thumb_url = wp_get_attachment_image_url( $thumb_id, $size );
                if ( $thumb_url ) {
                    $urls[] = $thumb_url;
                }
            }
        }

        foreach ( [ 'full', '2048x2048', 'large', 'medium_large' ] as $size ) {
            $image_url = wp_get_attachment_image_url( $attachment_id, $size );
            if ( $image_url ) {
                $urls[] = $image_url;
            }
        }
    }

    $url        = esc_url_raw( (string) ( $media['url'] ?? '' ) );
    $youtube_id = tb4cm_youtube_video_id_from_url( $url );
    if ( $youtube_id ) {
        /* v4.2.207: use an ordered candidate list and let JS verify image dimensions.
         * HD thumbnails can 404 or return a tiny placeholder, so the runtime tries the next one.
         */
        foreach ( [
            'https://i.ytimg.com/vi_webp/%s/maxresdefault.webp',
            'https://i.ytimg.com/vi/%s/hq720.jpg',
            'https://i.ytimg.com/vi/%s/maxresdefault.jpg',
            'https://img.youtube.com/vi/%s/maxresdefault.jpg',
            'https://i.ytimg.com/vi/%s/sddefault.jpg',
            'https://i.ytimg.com/vi/%s/hqdefault.jpg',
            'https://img.youtube.com/vi/%s/hqdefault.jpg',
            'https://i.ytimg.com/vi/%s/mqdefault.jpg',
            'https://i.ytimg.com/vi/%s/0.jpg',
        ] as $template ) {
            $urls[] = sprintf( $template, rawurlencode( $youtube_id ) );
        }
    }

    $oembed_thumb = tb4cm_oembed_thumbnail_url( $url );
    if ( $oembed_thumb ) {
        $urls[] = $oembed_thumb;
    }

    if ( tb4cm_vimeo_video_id_from_url( $url ) ) {
        $vimeo_thumb = $oembed_thumb ?: tb4cm_oembed_thumbnail_url( $url );
        if ( $vimeo_thumb ) {
            $urls = array_merge( $urls, tb4cm_vimeo_thumbnail_candidates( $vimeo_thumb ) );
        }
    }

    return tb4cm_unique_media_urls( $urls );
}

function tb4cm_get_video_poster_url( $media ) {
    $urls = tb4cm_get_video_poster_urls( $media );
    return ! empty( $urls[0] ) ? $urls[0] : '';
}

function tb4cm_render_video_cover_button( $media, $title = '' ) {
    if ( ! is_array( $media ) || 'video' !== sanitize_key( $media['type'] ?? '' ) ) {
        return '';
    }

    $posters    = tb4cm_get_video_poster_urls( $media );
    $media_url  = esc_url_raw( (string) ( $media['url'] ?? '' ) );
    $youtube_id = tb4cm_youtube_video_id_from_url( $media_url );
    $vimeo_id   = tb4cm_vimeo_video_id_from_url( $media_url );
    $title_att  = esc_attr( $title );
    $label      = $title ? sprintf( __( 'เล่นวิดีโอ %s', 'thinkb4do-community' ), $title ) : __( 'เล่นวิดีโอ', 'thinkb4do-community' );

    if ( $posters ) {
        $poster_layers = [];
        foreach ( $posters as $poster_url ) {
            $poster_layers[] = 'url(' . esc_url( $poster_url ) . ')';
        }
        $poster_layers[] = 'linear-gradient(135deg, #1E6B45 0%, #111111 100%)';
        $poster_html = '<span class="tb4c-video-cover-photo tb4c-video-cover-photo-hd" style="' . esc_attr( 'background-image:' . implode( ',', $poster_layers ) . ';' ) . '"></span>';
        $poster_html .= '<img class="tb4c-video-cover-img" src="' . esc_url( $posters[0] ) . '" alt="" loading="eager" decoding="async" draggable="false" data-tb4c-cover-candidates="' . esc_attr( wp_json_encode( array_values( $posters ) ) ) . '">';
    } else {
        $poster_html = '<span class="tb4c-video-cover-photo tb4c-video-cover-photo-fallback"></span>';
    }

    $play_icon = '<span class="tb4c-video-cover-play tb4c-video-cover-play-v445" aria-hidden="true"><span class="tb4c-video-cover-play-aura"></span><span class="tb4c-video-cover-play-shine"></span><span class="tb4c-video-cover-play-core"><svg class="tb4c-video-cover-play-icon" viewBox="0 0 120 120" focusable="false" aria-hidden="true"><path class="tb4c-video-cover-play-triangle" d="M43 31 L86 60 L43 89 Z"/></svg></span></span>';

    $attrs = [
        'type'                         => 'button',
        'class'                        => 'tb4c-video-cover',
        'data-tb4c-video-play'         => '',
        'data-tb4c-video-cover-title'  => $title_att,
        'data-tb4c-video-url'          => esc_attr( $media_url ),
        'aria-label'                   => esc_attr( $label ),
    ];
    if ( $posters ) {
        $attrs['data-tb4c-cover-candidates'] = esc_attr( wp_json_encode( array_values( $posters ) ) );
    }
    if ( $youtube_id ) {
        $attrs['data-tb4c-video-provider'] = 'youtube';
        $attrs['data-tb4c-youtube-id']     = esc_attr( $youtube_id );
    } elseif ( $vimeo_id ) {
        $attrs['data-tb4c-video-provider'] = 'vimeo';
        $attrs['data-tb4c-vimeo-id']       = esc_attr( $vimeo_id );
    }

    $attr_html = '';
    foreach ( $attrs as $name => $value ) {
        if ( '' === $value ) {
            $attr_html .= ' ' . $name;
        } else {
            $attr_html .= ' ' . $name . '="' . $value . '"';
        }
    }

    return '<button' . $attr_html . '>' . $poster_html . '<span class="tb4c-video-cover-shade"></span>' . $play_icon . '<span class="tb4c-video-cover-text">' . esc_html__( 'เล่นวิดีโอ', 'thinkb4do-community' ) . '</span></button>';
}


function tb4cm_allowed_upload_mimes() {
    return [
        'jpg|jpeg|jpe' => 'image/jpeg',
        'png'          => 'image/png',
        'gif'          => 'image/gif',
        'webp'         => 'image/webp',
        'avif'         => 'image/avif',
        'mp4|m4v'      => 'video/mp4',
        'mov'          => 'video/quicktime',
        'webm'         => 'video/webm',
        'ogv|ogg'      => 'video/ogg',
    ];
}

function tb4cm_request_has_media_upload( $field = 'media_file' ) {
    if ( empty( $_FILES[ $field ] ) || ! is_array( $_FILES[ $field ] ) ) {
        return false;
    }
    $error = (int) ( $_FILES[ $field ]['error'] ?? UPLOAD_ERR_NO_FILE );
    return UPLOAD_ERR_NO_FILE !== $error;
}

function tb4cm_handle_media_upload( $field = 'media_file' ) {
    if ( ! tb4cm_request_has_media_upload( $field ) ) {
        return null;
    }

    $file = $_FILES[ $field ];
    $error = (int) ( $file['error'] ?? UPLOAD_ERR_NO_FILE );
    if ( UPLOAD_ERR_OK !== $error ) {
        return new WP_Error( 'tb4c_upload_failed', __( 'อัปโหลดไฟล์ไม่สำเร็จ กรุณาลองใหม่', 'thinkb4do-community' ) );
    }

    $max_size = (int) wp_max_upload_size();
    $size     = (int) ( $file['size'] ?? 0 );
    if ( $size <= 0 ) {
        return new WP_Error( 'tb4c_upload_empty', __( 'ไฟล์ที่อัปโหลดว่างเปล่า', 'thinkb4do-community' ) );
    }
    if ( $max_size > 0 && $size > $max_size ) {
        return new WP_Error( 'tb4c_upload_too_large', sprintf( __( 'ไฟล์ใหญ่เกินขนาดที่เว็บอนุญาต (%s)', 'thinkb4do-community' ), size_format( $max_size ) ) );
    }

    $name     = sanitize_file_name( (string) ( $file['name'] ?? '' ) );
    $tmp_name = (string) ( $file['tmp_name'] ?? '' );
    $checked  = wp_check_filetype_and_ext( $tmp_name, $name, tb4cm_allowed_upload_mimes() );
    $type     = (string) ( $checked['type'] ?? '' );

    if ( ! $type || ( 0 !== strpos( $type, 'image/' ) && 0 !== strpos( $type, 'video/' ) ) ) {
        return new WP_Error( 'tb4c_upload_unsupported', __( 'รองรับเฉพาะไฟล์รูปภาพหรือวิดีโอที่ปลอดภัย เช่น jpg, png, webp, mp4, webm', 'thinkb4do-community' ) );
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $attachment_id = tb4cm_with_user_upload_folder( 'posts', static function() use ( $field ) {
        return media_handle_upload( $field, 0, [], [
            'test_form' => false,
            'mimes'     => tb4cm_allowed_upload_mimes(),
        ] );
    }, get_current_user_id() );

    if ( is_wp_error( $attachment_id ) ) {
        return $attachment_id;
    }

    tb4cm_record_user_media_asset( get_current_user_id(), $attachment_id, 'posts' );

    $url = wp_get_attachment_url( $attachment_id );
    if ( ! $url ) {
        return new WP_Error( 'tb4c_upload_no_url', __( 'อัปโหลดแล้วแต่ไม่พบ URL ไฟล์', 'thinkb4do-community' ) );
    }

    return [
        'url'           => esc_url_raw( $url ),
        'type'          => 0 === strpos( $type, 'image/' ) ? 'image' : 'video',
        'attachment_id' => absint( $attachment_id ),
        'source'        => 'upload',
        'poster_url'    => '',
    ];
}

function tb4cm_handle_media_upload_album( $field = 'media_files', $limit = 10 ) {
    if ( empty( $_FILES[ $field ] ) || ! is_array( $_FILES[ $field ] ) || empty( $_FILES[ $field ]['name'] ) ) {
        return [];
    }

    $files = $_FILES[ $field ];
    $names = is_array( $files['name'] ) ? $files['name'] : [ $files['name'] ];
    $album = [];
    $seen  = [];

    foreach ( $names as $index => $name ) {
        if ( count( $album ) >= $limit ) {
            break;
        }

        $error = isset( $files['error'][ $index ] ) ? (int) $files['error'][ $index ] : UPLOAD_ERR_NO_FILE;
        if ( UPLOAD_ERR_NO_FILE === $error ) {
            continue;
        }

        $temp_field = 'tb4c_media_album_' . (int) $index;
        $_FILES[ $temp_field ] = [
            'name'     => $files['name'][ $index ] ?? '',
            'type'     => $files['type'][ $index ] ?? '',
            'tmp_name' => $files['tmp_name'][ $index ] ?? '',
            'error'    => $error,
            'size'     => $files['size'][ $index ] ?? 0,
        ];

        $media = tb4cm_handle_media_upload( $temp_field );
        unset( $_FILES[ $temp_field ] );

        if ( is_wp_error( $media ) ) {
            return $media;
        }
        if ( is_array( $media ) && ! empty( $media['url'] ) ) {
            $key = strtolower( $media['url'] );
            if ( isset( $seen[ $key ] ) ) {
                continue;
            }
            $seen[ $key ] = true;
            $album[] = $media;
        }
    }

    return $album;
}

function tb4cm_detect_trusted_media_from_text( $text ) {
    $text = (string) $text;
    if ( '' === trim( $text ) || ! preg_match_all( '~https?://[^\s<>"\']+~i', $text, $matches ) ) {
        return null;
    }

    foreach ( $matches[0] as $candidate ) {
        $media = tb4cm_validate_trusted_media_url( $candidate );
        if ( ! is_wp_error( $media ) ) {
            return $media;
        }
    }

    return null;
}

function tb4cm_detect_trusted_media_album_from_text( $text, $limit = 10 ) {
    $items = [];
    $seen  = [];
    foreach ( tb4cm_extract_media_url_candidates( $text ) as $candidate ) {
        if ( count( $items ) >= $limit ) {
            break;
        }
        $media = tb4cm_validate_trusted_media_url( $candidate );
        if ( is_wp_error( $media ) ) {
            continue;
        }
        $key = strtolower( $media['url'] );
        if ( isset( $seen[ $key ] ) ) {
            continue;
        }
        $seen[ $key ] = true;
        $items[] = $media + [ 'source' => 'content' ];
    }
    return $items;
}


function tb4cm_get_post_media_album( $post_id, $raw_text = '' ) {
    $post_id = absint( $post_id );
    $album   = $post_id ? tb4cm_normalize_media_album( get_post_meta( $post_id, 'tb4_media_album', true ) ) : [];

    if ( ! empty( $album ) ) {
        return $album;
    }

    $stored = $post_id ? get_post_meta( $post_id, 'tb4_media_url', true ) : '';
    if ( $stored ) {
        $media = tb4cm_validate_trusted_media_url( $stored );
        if ( ! is_wp_error( $media ) ) {
            return [ $media + [ 'source' => 'url' ] ];
        }
    }

    /* v4.2.207: scan every safe post-side source, not only stripped visible text.
     * YouTube/Vimeo links can live in Gutenberg comments, oEmbed cache HTML, srcdoc,
     * excerpts, or legacy tb4 meta fields. This makes the video card rebuild the cover
     * from the real outside provider even when WordPress hides the original URL.
     */
    $sources = [];
    if ( '' !== trim( (string) $raw_text ) ) {
        $sources[] = (string) $raw_text;
    }
    if ( $post_id ) {
        $sources[] = (string) get_post_field( 'post_content', $post_id );
        $sources[] = (string) get_post_field( 'post_excerpt', $post_id );
        $all_meta  = get_post_meta( $post_id );
        foreach ( (array) $all_meta as $meta_key => $meta_values ) {
            if ( 0 === strpos( (string) $meta_key, '_edit_' ) ) {
                continue;
            }
            if ( false === stripos( (string) $meta_key, 'oembed' ) && false === stripos( (string) $meta_key, 'media' ) && false === stripos( (string) $meta_key, 'video' ) && false === stripos( (string) $meta_key, 'tb4' ) ) {
                continue;
            }
            foreach ( (array) $meta_values as $meta_value ) {
                if ( is_scalar( $meta_value ) && '' !== trim( (string) $meta_value ) ) {
                    $sources[] = (string) $meta_value;
                }
            }
        }
    }

    foreach ( array_unique( array_filter( $sources, static function( $value ) { return '' !== trim( (string) $value ); } ) ) as $source ) {
        $detected = tb4cm_detect_trusted_media_album_from_text( $source );
        if ( ! empty( $detected ) ) {
            return $detected;
        }
    }

    return [];
}

function tb4cm_get_post_media( $post_id, $raw_text = '' ) {
    $album = tb4cm_get_post_media_album( $post_id, $raw_text );
    return ! empty( $album[0] ) ? $album[0] : null;
}

function tb4cm_media_visible_text( $raw_text, $media = null ) {
    $visible = (string) $raw_text;
    if ( is_array( $media ) && ! empty( $media['url'] ) ) {
        $visible = str_replace( (string) $media['url'], '', $visible );
    }
    $visible = preg_replace( "/[ \t]+/", ' ', $visible );
    $visible = preg_replace( "/\n{3,}/", "\n\n", $visible );
    return trim( (string) $visible );
}

function tb4cm_detect_comment_media( $text ) {
    return tb4cm_detect_trusted_media_from_text( $text );
}

function tb4cm_comment_visible_text( $raw_text, $media = null ) {
    $visible = (string) $raw_text;
    if ( is_array( $media ) && ! empty( $media['url'] ) ) {
        $visible = str_replace( (string) $media['url'], '', $visible );
    }
    $visible = preg_replace( "/[ \t]+/", ' ', $visible );
    $visible = preg_replace( "/\n{3,}/", "\n\n", $visible );
    return trim( (string) $visible );
}

function tb4cm_render_post_media_item( $media, $index = 0, $title = '' ) {
    if ( ! is_array( $media ) || empty( $media['url'] ) || empty( $media['type'] ) ) {
        return '';
    }

    $url       = esc_url( $media['url'] );
    $type      = sanitize_key( $media['type'] );
    $title_att = esc_attr( $title );
    $index_att = esc_attr( (string) ( (int) $index + 1 ) );

    if ( 'image' === $type ) {
        return sprintf(
            '<button type="button" class="tb4c-album-item tb4c-album-image" data-tb4c-lightbox data-tb4c-lightbox-type="image" data-tb4c-lightbox-src="%1$s" data-tb4c-lightbox-title="%2$s" aria-label="%3$s"><img src="%1$s" alt="" loading="lazy" decoding="async"></button>',
            $url,
            $title_att,
            esc_attr( sprintf( __( 'ดูรูปที่ %s', 'thinkb4do-community' ), $index_att ) )
        );
    }

    if ( 'video' === $type ) {
        if ( preg_match( '~\.(mp4|m4v|webm|ogv|ogg)(\?.*)?$~i', $url ) ) {
            return sprintf(
                '<div class="tb4c-album-item tb4c-album-video tb4c-post-video-slot tb4c-video-has-cover" data-tb4c-video-cover-scope data-tb4c-video-slot data-tb4c-video-title="%2$s"><video controls playsinline preload="metadata" src="%1$s" poster="%5$s"></video>%6$s<button type="button" class="tb4c-album-expand" data-tb4c-lightbox data-tb4c-lightbox-type="video" data-tb4c-lightbox-src="%1$s" data-tb4c-lightbox-title="%2$s" aria-label="%3$s">%4$s<span>ขยาย</span></button></div>',
                $url,
                $title_att,
                esc_attr__( 'ขยายวิดีโอ', 'thinkb4do-community' ),
                tb4cm_icon_markup( 'ph-arrows-out-simple', '⤢' ),
                esc_url( tb4cm_get_video_poster_url( $media ) ),
                tb4cm_render_video_cover_button( $media, $title )
            );
        }

        $embed = wp_oembed_get( $url );
        if ( $embed ) {
            return sprintf(
                '<div class="tb4c-album-item tb4c-album-video tb4c-post-video-slot tb4c-video-has-cover" data-tb4c-video-cover-scope data-tb4c-video-slot data-tb4c-video-title="%2$s">%1$s%3$s</div>',
                $embed,
                $title_att,
                tb4cm_render_video_cover_button( $media, $title )
            );
        }

        return sprintf(
            '<a class="tb4c-album-item tb4c-album-video-fallback" href="%1$s" target="_blank" rel="noopener">%2$s<span>เปิดวิดีโอ</span></a>',
            $url,
            tb4cm_icon_markup( 'ph-play-circle', '▶' )
        );
    }

    return '';
}


function tb4cm_render_single_video_media_slot( $media, $title = '' ) {
    if ( ! is_array( $media ) || empty( $media['url'] ) || 'video' !== sanitize_key( $media['type'] ?? '' ) ) {
        return '';
    }

    $url       = esc_url( $media['url'] );
    $title_att = esc_attr( $title );
    $classes   = 'tb4c-thumb tb4c-ig-media tb4c-post-media-slot tb4c-post-album-slot tb4c-album-count-1 tb4c-post-video-slot tb4c-single-video-slot tb4c-video-has-cover';

    if ( preg_match( '~\.(mp4|m4v|webm|ogv|ogg)(\?.*)?$~i', $url ) ) {
        return sprintf(
            '<div class="%1$s" data-tb4c-video-cover-scope data-tb4c-album-count="1" data-tb4c-video-slot data-tb4c-video-title="%3$s"><video controls playsinline preload="metadata" src="%2$s" poster="%6$s"></video>%7$s<button type="button" class="tb4c-album-expand" data-tb4c-lightbox data-tb4c-lightbox-type="video" data-tb4c-lightbox-src="%2$s" data-tb4c-lightbox-title="%3$s" aria-label="%4$s">%5$s<span>ขยาย</span></button></div>',
            esc_attr( $classes ),
            $url,
            $title_att,
            esc_attr__( 'ขยายวิดีโอ', 'thinkb4do-community' ),
            tb4cm_icon_markup( 'ph-arrows-out-simple', '⤢' ),
            esc_url( tb4cm_get_video_poster_url( $media ) ),
            tb4cm_render_video_cover_button( $media, $title )
        );
    }

    $embed = wp_oembed_get( $url );
    if ( $embed ) {
        return sprintf(
            '<div class="%1$s" data-tb4c-video-cover-scope data-tb4c-album-count="1" data-tb4c-video-slot data-tb4c-video-title="%3$s">%2$s%4$s</div>',
            esc_attr( $classes ),
            $embed,
            $title_att,
            tb4cm_render_video_cover_button( $media, $title )
        );
    }

    return sprintf(
        '<a class="tb4c-thumb tb4c-ig-media tb4c-post-media-slot tb4c-album-video-fallback" href="%1$s" target="_blank" rel="noopener">%2$s<span>เปิดวิดีโอ</span></a>',
        $url,
        tb4cm_icon_markup( 'ph-play-circle', '▶' )
    );
}

function tb4cm_render_post_media_album( $album, $title = '' ) {
    $album = tb4cm_normalize_media_album( $album );
    if ( empty( $album ) ) {
        return '';
    }

    $count   = count( $album );
    $first_type = sanitize_key( $album[0]['type'] ?? '' );

    /* v4.2.30: a single video must render as one video slot only.
     * The previous nested-slot structure made some browsers/scripts attach mini-video
     * behavior to the inner node while the visible feed card stayed unchanged.
     */
    if ( 1 === $count && 'video' === $first_type ) {
        return tb4cm_render_single_video_media_slot( $album[0], $title );
    }

    $classes = [
        'tb4c-thumb',
        'tb4c-ig-media',
        'tb4c-post-media-slot',
        'tb4c-post-album-slot',
        'tb4c-album-count-' . min( 6, $count ),
    ];
    if ( 1 === $count && 'image' === $first_type ) {
        $classes[] = 'tb4c-post-image-slot';
    }
    if ( 1 === $count && 'video' === $first_type ) {
        $classes[] = 'tb4c-post-video-slot';
    }

    $html  = sprintf(
        '<div class="%1$s" data-tb4c-album-count="%2$s"%3$s>',
        esc_attr( implode( ' ', $classes ) ),
        esc_attr( (string) $count ),
        ( 1 === $count && 'video' === $first_type ) ? ' data-tb4c-video-slot data-tb4c-video-title="' . esc_attr( $title ) . '"' : ''
    );

    foreach ( $album as $index => $item ) {
        if ( $index >= 10 ) {
            break;
        }
        $html .= tb4cm_render_post_media_item( $item, $index, $title );
    }

    if ( $count > 1 ) {
        $html .= '<span class="tb4c-album-badge">' . esc_html( sprintf( _n( '%s ไฟล์', '%s ไฟล์', $count, 'thinkb4do-community' ), number_format_i18n( $count ) ) ) . '</span>';
    }

    $html .= '</div>';

    return $html;
}

function tb4cm_render_comment_media_slot( $media ) {
    if ( ! is_array( $media ) || empty( $media['url'] ) || empty( $media['type'] ) ) {
        return '';
    }

    $url  = esc_url( $media['url'] );
    $type = sanitize_key( $media['type'] );

    if ( 'image' === $type ) {
        return sprintf(
            '<a class="tb4c-comment-media-slot tb4c-comment-image-slot" href="%1$s" target="_blank" rel="noopener" data-tb4c-comment-media><img src="%1$s" alt="" loading="lazy" decoding="async"></a>',
            $url
        );
    }

    if ( 'video' === $type ) {
        if ( preg_match( '~\.(mp4|m4v|webm|ogv|ogg)(\?.*)?$~i', $url ) ) {
            return sprintf(
                '<div class="tb4c-comment-media-slot tb4c-comment-video-slot tb4c-video-has-cover" data-tb4c-video-cover-scope data-tb4c-comment-media><video controls playsinline preload="metadata" src="%1$s" poster="%2$s"></video>%3$s</div>',
                $url,
                esc_url( tb4cm_get_video_poster_url( $media ) ),
                tb4cm_render_video_cover_button( $media )
            );
        }

        $embed = wp_oembed_get( $url );
        if ( $embed ) {
            return '<div class="tb4c-comment-media-slot tb4c-comment-video-slot tb4c-video-has-cover" data-tb4c-video-cover-scope data-tb4c-comment-media>' . $embed . tb4cm_render_video_cover_button( $media ) . '</div>';
        }

        return sprintf(
            '<a class="tb4c-comment-media-slot tb4c-comment-video-fallback" href="%1$s" target="_blank" rel="noopener" data-tb4c-comment-media>%2$s<span>เปิดวิดีโอ</span></a>',
            $url,
            tb4cm_icon_markup( 'ph-play-circle', '▶' )
        );
    }

    return '';
}

function tb4cm_render_comment_item( $comment, $tree = [], $depth = 0 ) {
    $comment = is_numeric( $comment ) ? get_comment( absint( $comment ) ) : $comment;
    if ( ! $comment || ! $comment->comment_ID ) {
        return '';
    }

    $comment_id    = (int) $comment->comment_ID;
    $author_name   = get_comment_author( $comment );
    $raw_text      = trim( wp_strip_all_tags( (string) $comment->comment_content ) );
    $comment_media = tb4cm_detect_comment_media( $raw_text );
    $visible_text  = tb4cm_comment_visible_text( $raw_text, $comment_media );
    $media_html    = tb4cm_render_comment_media_slot( $comment_media );
    $can_edit      = tb4cm_user_can_edit_community_comment( $comment );
    $can_delete    = tb4cm_user_can_delete_community_comment( $comment );
    $children      = isset( $tree[ $comment_id ] ) && is_array( $tree[ $comment_id ] ) ? $tree[ $comment_id ] : [];
    $depth         = min( 3, max( 0, absint( $depth ) ) );
    $comment_time  = (int) get_comment_date( 'U', $comment );

    ob_start();
    ?>
    <li class="tb4c-comment-item tb4c-comment-thread-item tb4c-social-comment-item tb4c-comment-standard-layout tb4c-comment-inline-identity depth-<?php echo esc_attr( $depth ); ?>" id="comment-<?php echo esc_attr( $comment_id ); ?>" data-comment-id="<?php echo esc_attr( $comment_id ); ?>" data-parent-id="<?php echo esc_attr( (int) $comment->comment_parent ); ?>">
      <div class="tb4c-comment-avatar">
        <?php echo wp_kses_post( get_avatar( $comment, 44 ) ); ?>
      </div>
      <div class="tb4c-comment-body">
        <div class="tb4c-comment-content tb4c-comment-bubble-flat" data-tb4c-comment-bubble>
          <div class="tb4c-comment-topline">
            <div class="tb4c-comment-meta">
              <strong><?php echo esc_html( $author_name ); ?></strong>
              <span><?php echo esc_html( human_time_diff( $comment_time, current_time( 'timestamp' ) ) ); ?>ที่แล้ว</span>
            </div>
            <?php if ( $can_edit || $can_delete ) : ?>
              <div class="tb4c-comment-owner-tools" aria-label="จัดการความคิดเห็น">
                <?php if ( $can_edit ) : ?>
                  <button type="button" class="tb4c-comment-header-action tb4c-comment-action-edit" data-tb4c-comment-action="edit" data-comment-id="<?php echo esc_attr( $comment_id ); ?>">
                    <svg class="tb4c-comment-action-svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 20h4.6L19.2 9.4a2.1 2.1 0 0 0 0-3L17.6 4.8a2.1 2.1 0 0 0-3 0L4 15.4V20Z"></path><path d="M13.5 5.9l4.6 4.6"></path></svg>
                    <span>แก้ไข</span>
                  </button>
                <?php endif; ?>
                <?php if ( $can_delete ) : ?>
                  <button type="button" class="tb4c-comment-header-action tb4c-comment-action-delete tb4c-comment-danger" data-tb4c-comment-action="delete" data-comment-id="<?php echo esc_attr( $comment_id ); ?>">
                    <svg class="tb4c-comment-action-svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M5 7h14"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M8 7l1-3h6l1 3"></path><path d="M7 7l1 13h8l1-13"></path></svg>
                    <span>ลบ</span>
                  </button>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
          <div class="tb4c-comment-text <?php echo '' === $visible_text ? 'is-empty' : ''; ?>" data-tb4c-comment-text data-raw="<?php echo esc_attr( $raw_text ); ?>">
            <?php echo '' !== $visible_text ? wp_kses_post( wpautop( esc_html( $visible_text ) ) ) : ''; ?>
          </div>
          <?php echo $media_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
        <div class="tb4c-comment-actions" aria-label="การทำงานของความคิดเห็น">
          <button type="button" class="tb4c-comment-action tb4c-comment-action-reply" data-tb4c-comment-action="reply" data-comment-id="<?php echo esc_attr( $comment_id ); ?>" data-comment-author="<?php echo esc_attr( $author_name ); ?>">
            <?php echo tb4cm_icon_markup( 'ph-arrow-bend-up-left', '↩' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <span>ตอบกลับ</span>
          </button>
          <a class="tb4c-comment-action tb4c-comment-action-copy" href="<?php echo esc_url( get_comment_link( $comment ) ); ?>" data-tb4c-copy-link="<?php echo esc_url( get_comment_link( $comment ) ); ?>">
            <?php echo tb4cm_icon_markup( 'ph-link-simple', '🔗' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <span>คัดลอก</span>
          </a>
        </div>
        <ol class="tb4c-comment-replies" data-tb4c-replies-for="<?php echo esc_attr( $comment_id ); ?>">
          <?php foreach ( $children as $child ) : ?>
            <?php echo tb4cm_render_comment_item( $child, $tree, $depth + 1 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
          <?php endforeach; ?>
        </ol>
      </div>
    </li>
    <?php
    return trim( ob_get_clean() );
}

function tb4cm_submit_community_comment() {
    tb4cm_verify_ajax_nonce();

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => __( 'กรุณาเข้าสู่ระบบก่อนแสดงความคิดเห็น', 'thinkb4do-community' ) ], 401 );
    }

    $post_id   = absint( $_POST['post_id'] ?? 0 );
    $parent_id = absint( $_POST['parent_id'] ?? 0 );
    $content   = trim( wp_strip_all_tags( wp_unslash( $_POST['content'] ?? '' ) ) );

    if ( ! $post_id || 'tb4_community_post' !== get_post_type( $post_id ) ) {
        wp_send_json_error( [ 'message' => __( 'ไม่พบโพสต์สำหรับแสดงความคิดเห็น', 'thinkb4do-community' ) ], 404 );
    }

    if ( ! comments_open( $post_id ) ) {
        wp_send_json_error( [ 'message' => __( 'โพสต์นี้ปิดรับความคิดเห็นแล้ว', 'thinkb4do-community' ) ], 403 );
    }

    if ( '' === $content ) {
        wp_send_json_error( [ 'message' => __( 'กรุณาเขียนความคิดเห็นก่อนส่ง', 'thinkb4do-community' ) ], 422 );
    }

    if ( function_exists( 'mb_strlen' ) && mb_strlen( $content ) > 1200 ) {
        wp_send_json_error( [ 'message' => __( 'ความคิดเห็นยาวเกินไป กรุณาย่อให้กระชับขึ้น', 'thinkb4do-community' ) ], 422 );
    }

    if ( $parent_id ) {
        $parent_comment = tb4cm_get_comment_object( $parent_id );
        if ( ! $parent_comment || (int) $parent_comment->comment_post_ID !== $post_id ) {
            wp_send_json_error( [ 'message' => __( 'ไม่พบความคิดเห็นที่ต้องการตอบกลับ', 'thinkb4do-community' ) ], 422 );
        }
    }

    $user = wp_get_current_user();
    $comment_id = wp_new_comment( [
        'comment_post_ID'      => $post_id,
        'comment_parent'       => $parent_id,
        'comment_content'      => $content,
        'user_id'              => $user->ID,
        'comment_author'       => $user->display_name ?: $user->user_login,
        'comment_author_email' => $user->user_email,
        'comment_author_url'   => '',
        'comment_type'         => 'comment',
        'comment_approved'     => 1,
    ], true );

    if ( is_wp_error( $comment_id ) ) {
        wp_send_json_error( [ 'message' => $comment_id->get_error_message() ], 500 );
    }

    $comment = get_comment( $comment_id );
    $approved = $comment && '1' === (string) $comment->comment_approved;
    $preview_media = $comment ? tb4cm_detect_comment_media( wp_strip_all_tags( get_comment_text( $comment ) ) ) : null;
    $preview_text  = $comment ? tb4cm_comment_visible_text( wp_strip_all_tags( get_comment_text( $comment ) ), $preview_media ) : '';

    wp_send_json_success( [
        'message'    => $approved ? __( 'ส่งความคิดเห็นแล้ว', 'thinkb4do-community' ) : __( 'ส่งความคิดเห็นแล้ว รออนุมัติ', 'thinkb4do-community' ),
        'comment_id' => (int) $comment_id,
        'parent_id'  => $parent_id,
        'approved'   => $approved,
        'html'       => $comment ? tb4cm_render_comment_item( $comment, [] ) : '',
        'count'      => tb4cm_get_approved_comment_count( $post_id ),
        'preview'    => $comment ? [
            'id'          => (int) $comment_id,
            'author'      => get_comment_author( $comment ),
            'text'        => $preview_text ? wp_trim_words( $preview_text, 18 ) : __( 'แนบสื่อ', 'thinkb4do-community' ),
            'time'        => __( 'ตอนนี้', 'thinkb4do-community' ),
            'avatar_html' => get_avatar( $comment, 32 ),
        ] : [],
    ] );
}
add_action( 'wp_ajax_tb4c_submit_comment', 'tb4cm_submit_community_comment' );

function tb4cm_update_community_comment() {
    tb4cm_verify_ajax_nonce();

    $comment_id = absint( $_POST['comment_id'] ?? 0 );
    $content    = trim( wp_strip_all_tags( wp_unslash( $_POST['content'] ?? '' ) ) );
    $comment    = tb4cm_get_comment_object( $comment_id );

    if ( ! $comment ) {
        wp_send_json_error( [ 'message' => __( 'ไม่พบความคิดเห็น', 'thinkb4do-community' ) ], 404 );
    }
    if ( ! tb4cm_user_can_edit_community_comment( $comment ) ) {
        wp_send_json_error( [ 'message' => __( 'คุณไม่มีสิทธิ์แก้ไขความคิดเห็นนี้', 'thinkb4do-community' ) ], 403 );
    }
    if ( '' === $content ) {
        wp_send_json_error( [ 'message' => __( 'ความคิดเห็นต้องไม่ว่าง', 'thinkb4do-community' ) ], 422 );
    }

    $updated = wp_update_comment( [
        'comment_ID'      => $comment_id,
        'comment_content' => $content,
    ], true );

    if ( is_wp_error( $updated ) ) {
        wp_send_json_error( [ 'message' => $updated->get_error_message() ], 500 );
    }

    $media        = tb4cm_detect_comment_media( $content );
    $visible_text = tb4cm_comment_visible_text( $content, $media );

    wp_send_json_success( [
        'message'      => __( 'แก้ไขความคิดเห็นแล้ว', 'thinkb4do-community' ),
        'comment_id'   => $comment_id,
        'content'      => $content,
        'content_html' => '' !== $visible_text ? wp_kses_post( wpautop( esc_html( $visible_text ) ) ) : '',
        'media_html'   => tb4cm_render_comment_media_slot( $media ),
    ] );
}

add_action( 'wp_ajax_tb4c_update_comment', 'tb4cm_update_community_comment' );

function tb4cm_delete_community_comment() {
    tb4cm_verify_ajax_nonce();

    $comment_id = absint( $_POST['comment_id'] ?? 0 );
    $comment    = tb4cm_get_comment_object( $comment_id );

    if ( ! $comment ) {
        wp_send_json_error( [ 'message' => __( 'ไม่พบความคิดเห็น', 'thinkb4do-community' ) ], 404 );
    }
    if ( ! tb4cm_user_can_delete_community_comment( $comment ) ) {
        wp_send_json_error( [ 'message' => __( 'คุณไม่มีสิทธิ์ลบความคิดเห็นนี้', 'thinkb4do-community' ) ], 403 );
    }

    $post_id = (int) $comment->comment_post_ID;
    $deleted = function_exists( 'wp_trash_comment' ) ? wp_trash_comment( $comment_id ) : wp_delete_comment( $comment_id, false );

    if ( ! $deleted ) {
        wp_send_json_error( [ 'message' => __( 'ลบความคิดเห็นไม่สำเร็จ', 'thinkb4do-community' ) ], 500 );
    }

    wp_send_json_success( [
        'message'    => __( 'ลบความคิดเห็นแล้ว', 'thinkb4do-community' ),
        'comment_id' => $comment_id,
        'count'      => tb4cm_get_approved_comment_count( $post_id ),
    ] );
}
add_action( 'wp_ajax_tb4c_delete_comment', 'tb4cm_delete_community_comment' );

function tb4cm_update_community_post() {
    tb4cm_verify_ajax_nonce();

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => __( 'กรุณาเข้าสู่ระบบก่อนแก้ไขโพสต์', 'thinkb4do-community' ) ], 401 );
    }

    $post_id = absint( $_POST['post_id'] ?? 0 );
    if ( ! $post_id || 'tb4_community_post' !== get_post_type( $post_id ) ) {
        wp_send_json_error( [ 'message' => __( 'ไม่พบโพสต์ที่ต้องการแก้ไข', 'thinkb4do-community' ) ], 404 );
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        wp_send_json_error( [ 'message' => __( 'คุณไม่มีสิทธิ์แก้ไขโพสต์นี้', 'thinkb4do-community' ) ], 403 );
    }

    $title      = sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) );
    $content    = wp_kses_post( wp_unslash( $_POST['content'] ?? '' ) );
    $media_url   = trim( (string) wp_unslash( $_POST['media_url'] ?? '' ) );
    $post_type   = sanitize_key( wp_unslash( $_POST['post_type'] ?? 'discussion' ) );
    $visibility  = sanitize_key( wp_unslash( $_POST['visibility'] ?? 'public' ) );
    $topic       = sanitize_key( wp_unslash( $_POST['topic'] ?? $post_type ) );
    $tags_input  = (string) wp_unslash( $_POST['tags'] ?? '' );
    $new_tag_input = (string) wp_unslash( $_POST['new_tag'] ?? '' );
    if ( '' !== trim( wp_strip_all_tags( $new_tag_input ) ) ) {
        $tags_input = trim( $tags_input . ' ' . $new_tag_input );
    }
    $feeling     = sanitize_text_field( wp_unslash( $_POST['feeling'] ?? '' ) );
    $poll_question = sanitize_text_field( wp_unslash( $_POST['poll_question'] ?? '' ) );
    $poll_options  = (string) wp_unslash( $_POST['poll_options'] ?? '' );
    $media       = null;
    $media_album = [];

    $allowed_types = [ 'discussion', 'question', 'idea', 'project', 'event', 'creator' ];
    if ( ! in_array( $post_type, $allowed_types, true ) ) {
        $post_type = 'discussion';
    }
    if ( ! in_array( $visibility, [ 'public', 'members', 'groups' ], true ) ) {
        $visibility = 'public';
    }

    $upload_album = tb4cm_handle_media_upload_album( 'media_files', 10 );
    if ( is_wp_error( $upload_album ) ) {
        wp_send_json_error( [ 'message' => $upload_album->get_error_message() ], 422 );
    }

    if ( ! empty( $upload_album ) ) {
        $media_album = $upload_album;
        $media       = $media_album[0];
    } else {
        $upload_media = tb4cm_handle_media_upload( 'media_file' );
        if ( is_wp_error( $upload_media ) ) {
            wp_send_json_error( [ 'message' => $upload_media->get_error_message() ], 422 );
        }
        if ( is_array( $upload_media ) ) {
            $media_album = [ $upload_media ];
            $media       = $upload_media;
        } elseif ( $media_url ) {
            $url_album = tb4cm_validate_trusted_media_urls( $media_url, 10 );
            if ( is_wp_error( $url_album ) ) {
                wp_send_json_error( [ 'message' => $url_album->get_error_message() ], 422 );
            }
            $media_album = $url_album;
            $media       = $media_album[0];
        }
    }

    if ( '' === trim( wp_strip_all_tags( $content ) ) && empty( $media_album ) ) {
        wp_send_json_error( [ 'message' => __( 'กรุณาใส่ข้อความ, URL รูป/วิดีโอ หรืออัปโหลดไฟล์', 'thinkb4do-community' ) ], 422 );
    }

    $updated = wp_update_post( [
        'ID'           => $post_id,
        'post_title'   => $title,
        'post_content' => $content,
    ], true );

    if ( is_wp_error( $updated ) ) {
        wp_send_json_error( [ 'message' => $updated->get_error_message() ], 500 );
    }

    foreach ( $media_album as $album_item ) {
        if ( ! empty( $album_item['attachment_id'] ) ) {
            wp_update_post( [
                'ID'          => absint( $album_item['attachment_id'] ),
                'post_parent' => $post_id,
            ] );
        }
    }

    update_post_meta( $post_id, 'tb4_post_type', $post_type );
    update_post_meta( $post_id, 'tb4_post_visibility', $visibility );
    update_post_meta( $post_id, 'tb4_post_quality_score', tb4cm_calculate_post_quality_score( $title, $content . ' ' . ( $media['url'] ?? '' ) ) );

    if ( $media ) {
        update_post_meta( $post_id, 'tb4_media_url', $media['url'] );
        update_post_meta( $post_id, 'tb4_media_album', wp_json_encode( array_values( $media_album ) ) );
        if ( ! empty( $media['attachment_id'] ) ) {
            update_post_meta( $post_id, 'tb4_media_attachment_id', absint( $media['attachment_id'] ) );
        } else {
            delete_post_meta( $post_id, 'tb4_media_attachment_id' );
        }
    } else {
        delete_post_meta( $post_id, 'tb4_media_url' );
        delete_post_meta( $post_id, 'tb4_media_attachment_id' );
        delete_post_meta( $post_id, 'tb4_media_album' );
    }

    if ( $topic ) {
        if ( ! term_exists( $topic, 'tb4_community_topic' ) ) {
            wp_insert_term( tb4cm_topic_label( $topic ), 'tb4_community_topic', [ 'slug' => $topic ] );
        }
        wp_set_object_terms( $post_id, [ $topic ], 'tb4_community_topic', false );
    }

    tb4cm_set_post_tags_from_input( $post_id, $tags_input );
    update_post_meta( $post_id, 'tb4_post_feeling', $feeling );
    tb4cm_save_post_poll_from_input( $post_id, $poll_question, $poll_options );

    wp_send_json_success( [
        'message'   => __( 'บันทึกการแก้ไขโพสต์แล้ว', 'thinkb4do-community' ),
        'post_id'   => $post_id,
        'status'    => 'updated',
        'url'       => get_permalink( $post_id ),
        'media_url'   => $media['url'] ?? '',
        'media_count' => count( $media_album ),
    ] );
}
add_action( 'wp_ajax_tb4c_update_community_post', 'tb4cm_update_community_post' );

function tb4cm_delete_community_post() {
    tb4cm_verify_ajax_nonce();

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => __( 'กรุณาเข้าสู่ระบบก่อนจัดการโพสต์', 'thinkb4do-community' ) ], 401 );
    }

    $post_id = absint( $_POST['post_id'] ?? 0 );
    if ( ! $post_id || 'tb4_community_post' !== get_post_type( $post_id ) ) {
        wp_send_json_error( [ 'message' => __( 'ไม่พบโพสต์', 'thinkb4do-community' ) ], 404 );
    }
    if ( ! current_user_can( 'delete_post', $post_id ) ) {
        wp_send_json_error( [ 'message' => __( 'คุณไม่มีสิทธิ์ลบโพสต์นี้', 'thinkb4do-community' ) ], 403 );
    }

    $trashed = wp_trash_post( $post_id );
    if ( ! $trashed ) {
        wp_send_json_error( [ 'message' => __( 'ลบโพสต์ไม่สำเร็จ', 'thinkb4do-community' ) ], 500 );
    }

    wp_send_json_success( [
        'message' => __( 'ย้ายโพสต์ไปถังขยะแล้ว', 'thinkb4do-community' ),
        'url'     => home_url( '/community/' ),
    ] );
}
add_action( 'wp_ajax_tb4c_delete_post', 'tb4cm_delete_community_post' );


/**
 * Social follow helpers.
 */
function tb4cm_get_following_user_ids( $user_id = 0 ) {
    $user_id = $user_id ? absint( $user_id ) : get_current_user_id();
    if ( ! $user_id ) {
        return [];
    }

    $ids = get_user_meta( $user_id, 'tb4c_following_user_ids', true );
    if ( ! is_array( $ids ) ) {
        $ids = [];
    }

    return array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );
}

function tb4cm_is_following_user( $target_user_id, $user_id = 0 ) {
    $target_user_id = absint( $target_user_id );
    if ( ! $target_user_id ) {
        return false;
    }
    return in_array( $target_user_id, tb4cm_get_following_user_ids( $user_id ), true );
}

function tb4cm_get_user_follower_count( $user_id ) {
    return max( 0, absint( get_user_meta( absint( $user_id ), 'tb4c_follower_count', true ) ) );
}

function tb4cm_toggle_follow_user() {
    tb4cm_verify_ajax_nonce();

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => __( 'กรุณาเข้าสู่ระบบก่อนติดตามสมาชิก', 'thinkb4do-community' ) ], 401 );
    }

    $current_user_id = get_current_user_id();
    $target_user_id  = absint( $_POST['user_id'] ?? 0 );

    if ( ! $target_user_id || $target_user_id === $current_user_id || ! get_userdata( $target_user_id ) ) {
        wp_send_json_error( [ 'message' => __( 'ไม่พบสมาชิกที่ต้องการติดตาม', 'thinkb4do-community' ) ], 422 );
    }

    $following = tb4cm_get_following_user_ids( $current_user_id );
    $is_following = in_array( $target_user_id, $following, true );

    if ( $is_following ) {
        $following = array_values( array_diff( $following, [ $target_user_id ] ) );
        $state = 'follow';
        $count = max( 0, tb4cm_get_user_follower_count( $target_user_id ) - 1 );
    } else {
        $following[] = $target_user_id;
        $following = array_values( array_unique( array_map( 'absint', $following ) ) );
        $state = 'following';
        $count = tb4cm_get_user_follower_count( $target_user_id ) + 1;
    }

    update_user_meta( $current_user_id, 'tb4c_following_user_ids', $following );
    update_user_meta( $target_user_id, 'tb4c_follower_count', $count );

    wp_send_json_success( [
        'user_id' => $target_user_id,
        'state'   => $state,
        'label'   => 'following' === $state ? __( 'กำลังติดตาม', 'thinkb4do-community' ) : __( 'ติดตาม', 'thinkb4do-community' ),
        'count'   => $count,
    ] );
}
add_action( 'wp_ajax_tb4c_toggle_follow_user', 'tb4cm_toggle_follow_user' );

function tb4cm_get_suggested_members( $limit = 5 ) {
    $limit   = max( 1, absint( $limit ) );
    $exclude = [];
    if ( is_user_logged_in() ) {
        $exclude[] = get_current_user_id();
        $exclude = array_merge( $exclude, tb4cm_get_following_user_ids() );
    }
    $exclude   = array_values( array_unique( array_filter( array_map( 'absint', $exclude ) ) ) );
    $cache_key = 'tb4c_suggested_members_' . $limit . '_' . md5( implode( ',', $exclude ) );
    $cached    = wp_cache_get( $cache_key, 'thinkb4do-community' );
    if ( false !== $cached ) {
        return $cached;
    }

    $members = get_users( [
        'number'  => $limit,
        'exclude' => $exclude,
        'orderby' => 'registered',
        'order'   => 'DESC',
        'fields'  => [ 'ID', 'display_name', 'user_registered' ],
    ] );
    wp_cache_set( $cache_key, $members, 'thinkb4do-community', 300 );
    return $members;
}

function tb4cm_get_user_community_post_count( $user_id ) {
    $user_id = absint( $user_id );
    if ( ! $user_id ) {
        return 0;
    }

    $cache_key = 'tb4c_user_post_count_' . $user_id;
    $cached    = wp_cache_get( $cache_key, 'thinkb4do-community' );
    if ( false !== $cached ) {
        return (int) $cached;
    }

    $count = (int) count_user_posts( $user_id, 'tb4_community_post', true );
    wp_cache_set( $cache_key, $count, 'thinkb4do-community', 300 );
    return $count;
}

function tb4cm_social_profile_url( $user_id ) {
    $user_id = absint( $user_id );
    if ( is_user_logged_in() && get_current_user_id() === $user_id ) {
        return tb4cm_get_member_area_url();
    }
    return home_url( '/community/' );
}

function tb4cm_render_follow_button( $target_user_id, $args = [] ) {
    $target_user_id = absint( $target_user_id );
    if ( ! $target_user_id || ( is_user_logged_in() && get_current_user_id() === $target_user_id ) ) {
        return '';
    }

    $defaults = [
        'class' => 'tb4c-follow-btn',
        'small' => false,
    ];
    $args = wp_parse_args( $args, $defaults );
    $is_following = tb4cm_is_following_user( $target_user_id );
    $class = trim( $args['class'] . ( $args['small'] ? ' is-small' : '' ) . ( $is_following ? ' is-following' : '' ) );
    $label = $is_following ? __( 'กำลังติดตาม', 'thinkb4do-community' ) : __( 'ติดตาม', 'thinkb4do-community' );

    if ( ! is_user_logged_in() ) {
        return sprintf(
            '<a class="%1$s" href="%2$s"><i class="ph ph-plus"></i><span>%3$s</span></a>',
            esc_attr( $class ),
            esc_url( wp_login_url( tb4cm_get_current_url() ) ),
            esc_html( $label )
        );
    }

    return sprintf(
        '<button type="button" class="%1$s" data-tb4c-follow="%2$d" data-follow-label="%3$s" data-following-label="%4$s"><i class="ph %5$s"></i><span>%6$s</span></button>',
        esc_attr( $class ),
        $target_user_id,
        esc_attr__( 'ติดตาม', 'thinkb4do-community' ),
        esc_attr__( 'กำลังติดตาม', 'thinkb4do-community' ),
        $is_following ? 'ph-check' : 'ph-plus',
        esc_html( $label )
    );
}

function tb4cm_calculate_post_quality_score( $title, $content ) {
    $plain  = trim( wp_strip_all_tags( $content ) );
    $score  = 45;
    $score += min( 20, strlen( $title ) );
    $length = function_exists( 'mb_strlen' ) ? mb_strlen( $plain ) : strlen( $plain );
    $score += min( 25, (int) floor( $length / 20 ) );
    $score += false !== strpos( $plain, '?' ) || false !== strpos( $plain, 'ไหม' ) ? 5 : 0;
    $score += preg_match( '/https?:\/\//i', $plain ) ? 5 : 0;
    return min( 100, max( 1, (int) $score ) );
}


function tb4cm_get_initial( $text ) {
    $text = trim( wp_strip_all_tags( (string) $text ) );
    if ( '' === $text ) {
        return 'T';
    }
    if ( function_exists( 'mb_substr' ) ) {
        return mb_substr( $text, 0, 1 );
    }
    return substr( $text, 0, 1 );
}



/**
 * Shared composer modal for every public community page.
 * v4.2.54: restore the original popup composer, while keeping tags, tools, feeling, and poll systems inside it.
 */
function tb4cm_render_composer_modal() {
    static $tb4c_composer_modal_rendered = false;

    if ( $tb4c_composer_modal_rendered ) {
        return;
    }

    $tb4c_composer_modal_rendered = true;
    ?>
    <div class="tb4c-modal tb4c-canonical-composer-modal" id="tb4cComposerModal" aria-hidden="true" data-tb4c-composer-modal="canonical" data-tb4c-ui-role="composer-modal" data-tb4c-smart-composer data-tb4c-smart-composer-single data-tb4c-stable-composer>
      <div class="tb4c-modal-panel tb4c-composer-popup-panel" role="dialog" aria-modal="true" aria-labelledby="tb4cComposerTitle">
        <div class="tb4c-composer-titlebar">
          <div>
            <h2 id="tb4cComposerTitle"><?php esc_html_e( 'สร้างโพสต์ใหม่', 'thinkb4do-community' ); ?></h2>
            <p><?php esc_html_e( 'เขียนโพสต์ แชร์รูป/วิดีโอ แท็ก และโพลได้ในที่เดียว', 'thinkb4do-community' ); ?></p>
          </div>
          <button class="tb4c-modal-close tb4c-composer-close" type="button" data-tb4c-close-modal aria-label="<?php esc_attr_e( 'ปิด', 'thinkb4do-community' ); ?>">×</button>
        </div>
        <?php include TB4CM_DIR . 'templates/community-form.php'; ?>
      </div>
    </div>
    <?php
}

/**
 * Find the best public page URL from a list of common candidate slugs.
 */
function tb4cm_resolve_public_page_url( $candidates, $fallback = '/' ) {
    $candidates = is_array( $candidates ) ? $candidates : [ $candidates ];

    foreach ( $candidates as $candidate ) {
        $candidate = sanitize_title( (string) $candidate );
        if ( '' === $candidate ) {
            continue;
        }

        $page = get_page_by_path( $candidate );
        if ( $page instanceof WP_Post ) {
            return get_permalink( $page );
        }
    }

    return home_url( $fallback );
}

/**
 * Shared mobile navigation used by every public community page.
 * v4.2.44: general Instagram-like bottom bar with page icons and a dedicated Community tab.
 */

/**
 * Home Menu Feed helpers.
 * v4.2.73: bring interesting public areas from the main menu into the homepage.
 */
function tb4cm_normalize_home_menu_item( $item ) {
    $url      = '';
    $label    = '';
    $icon     = 'ph-squares-four';
    $fallback = '▦';
    $desc     = '';
    $object_id = 0;
    $object_type = '';

    if ( is_object( $item ) ) {
        $url         = isset( $item->url ) ? $item->url : '';
        $label       = isset( $item->title ) ? $item->title : '';
        $object_id   = isset( $item->object_id ) ? absint( $item->object_id ) : 0;
        $object_type = isset( $item->object ) ? sanitize_key( $item->object ) : '';
    } elseif ( is_array( $item ) ) {
        if ( isset( $item['url'] ) || isset( $item['href'] ) || isset( $item['link'] ) ) {
            $url = $item['url'] ?? $item['href'] ?? $item['link'];
        } elseif ( isset( $item[0] ) ) {
            $url = $item[0];
        }

        if ( isset( $item['title'] ) || isset( $item['label'] ) || isset( $item['name'] ) ) {
            $label = $item['title'] ?? $item['label'] ?? $item['name'];
        } elseif ( isset( $item[2] ) ) {
            $label = $item[2];
        } elseif ( isset( $item[1] ) && ! preg_match( '/^ph\-/i', (string) $item[1] ) ) {
            $label = $item[1];
        }

        if ( isset( $item['icon'] ) ) {
            $icon = sanitize_html_class( $item['icon'] );
        } elseif ( isset( $item[1] ) && preg_match( '/^ph\-/i', (string) $item[1] ) ) {
            $icon = sanitize_html_class( $item[1] );
        }

        if ( isset( $item['fallback'] ) ) {
            $fallback = wp_strip_all_tags( (string) $item['fallback'] );
        }

        if ( isset( $item['desc'] ) || isset( $item['description'] ) ) {
            $desc = $item['desc'] ?? $item['description'];
        } elseif ( isset( $item[3] ) ) {
            $desc = $item[3];
        }

        if ( isset( $item['object_id'] ) ) {
            $object_id = absint( $item['object_id'] );
        }
        if ( isset( $item['object'] ) ) {
            $object_type = sanitize_key( $item['object'] );
        }
    }

    $url = esc_url_raw( $url );
    if ( '' === $url ) {
        return null;
    }

    $label = trim( wp_strip_all_tags( (string) $label ) );
    if ( '' === $label ) {
        $label = wp_parse_url( $url, PHP_URL_PATH );
        $label = $label ? trim( str_replace( [ '/', '-' ], ' ', $label ) ) : __( 'เมนู', 'thinkb4do-community' );
    }

    if ( ! $object_id ) {
        $object_id = url_to_postid( $url );
    }

    if ( $object_id && '' === $object_type ) {
        $object_type = get_post_type( $object_id );
    }

    return [
        'url'         => $url,
        'label'       => $label,
        'icon'        => $icon ?: 'ph-squares-four',
        'fallback'    => $fallback ?: '▦',
        'desc'        => trim( wp_strip_all_tags( (string) $desc ) ),
        'object_id'   => $object_id,
        'object_type' => $object_type,
    ];
}

function tb4cm_get_theme_platform_menu_items_for_home_feed() {
    if ( ! function_exists( 'tb4_get_platform_menu_items' ) ) {
        return [];
    }

    try {
        $reflection = new ReflectionFunction( 'tb4_get_platform_menu_items' );
        if ( $reflection->getNumberOfRequiredParameters() > 0 ) {
            return [];
        }
        $items = tb4_get_platform_menu_items();
        return is_array( $items ) ? $items : [];
    } catch ( Throwable $e ) {
        return [];
    }
}

function tb4cm_get_home_menu_source_items( $limit = 8 ) {
    $limit = max( 4, min( 12, absint( $limit ) ) );
    $raw_items = [];

    $theme_items = tb4cm_get_theme_platform_menu_items_for_home_feed();
    if ( ! empty( $theme_items ) ) {
        $raw_items = array_merge( $raw_items, $theme_items );
    }

    $locations = get_nav_menu_locations();
    $preferred_locations = [ 'primary', 'menu-1', 'main', 'header', 'top' ];
    foreach ( $preferred_locations as $location ) {
        if ( ! empty( $locations[ $location ] ) ) {
            $menu_items = wp_get_nav_menu_items( $locations[ $location ], [ 'update_post_term_cache' => false ] );
            if ( ! empty( $menu_items ) && ! is_wp_error( $menu_items ) ) {
                $raw_items = array_merge( $raw_items, $menu_items );
            }
        }
    }

    if ( empty( $raw_items ) ) {
        foreach ( wp_get_nav_menus() as $menu ) {
            $menu_items = wp_get_nav_menu_items( $menu->term_id, [ 'update_post_term_cache' => false ] );
            if ( ! empty( $menu_items ) && ! is_wp_error( $menu_items ) ) {
                $raw_items = array_merge( $raw_items, $menu_items );
                if ( count( $raw_items ) >= $limit ) {
                    break;
                }
            }
        }
    }

    if ( empty( $raw_items ) ) {
        $raw_items = [
            [ 'url' => home_url( '/' ), 'icon' => 'ph-house', 'fallback' => '⌂', 'label' => 'หน้าแรก', 'desc' => 'ประตูหลักของ Thinkb4do' ],
            [ 'url' => home_url( '/community/' ), 'icon' => 'ph-users-three', 'fallback' => '👥', 'label' => 'ชุมชน', 'desc' => 'ฟีด ความคิดเห็น และโพสต์น่าสนใจ' ],
            [ 'url' => home_url( '/member-area/' ), 'icon' => 'ph-user-circle-gear', 'fallback' => '◎', 'label' => 'พื้นที่สมาชิก', 'desc' => 'จัดการโปรไฟล์ โพสต์ และฟีดของตัวเอง' ],
            [ 'url' => tb4cm_resolve_public_page_url( [ 'articles', 'article', 'blog', 'posts' ], '/articles/' ), 'icon' => 'ph-newspaper-clipping', 'fallback' => '📰', 'label' => 'บทความ', 'desc' => 'เรื่องอ่านและความรู้ล่าสุด' ],
            [ 'url' => tb4cm_resolve_public_page_url( [ 'products', 'product', 'shop', 'store' ], '/products/' ), 'icon' => 'ph-shopping-bag-open', 'fallback' => '🛍', 'label' => 'สินค้า', 'desc' => 'สินค้าและบริการที่เกี่ยวข้อง' ],
            [ 'url' => tb4cm_resolve_public_page_url( [ 'systems', 'system', 'platform', 'solutions' ], '/systems/' ), 'icon' => 'ph-squares-four', 'fallback' => '▦', 'label' => 'ระบบ', 'desc' => 'ระบบ เครื่องมือ และโซลูชัน' ],
        ];
    }

    $items = [];
    $seen  = [];
    foreach ( $raw_items as $raw_item ) {
        $item = tb4cm_normalize_home_menu_item( $raw_item );
        if ( ! $item ) {
            continue;
        }
        $key = trailingslashit( strtolower( remove_query_arg( [ 'utm_source', 'utm_medium', 'utm_campaign' ], $item['url'] ) ) );
        if ( isset( $seen[ $key ] ) ) {
            continue;
        }
        $seen[ $key ] = true;
        $items[] = $item;
        if ( count( $items ) >= $limit ) {
            break;
        }
    }

    return apply_filters( 'tb4c_home_menu_source_items', $items, $limit );
}

function tb4cm_home_menu_item_kind( $item ) {
    $path = strtolower( (string) wp_parse_url( $item['url'] ?? '', PHP_URL_PATH ) );
    $object_type = sanitize_key( $item['object_type'] ?? '' );
    $label_raw = wp_strip_all_tags( (string) ( $item['label'] ?? '' ) );
    $label = function_exists( 'mb_strtolower' ) ? mb_strtolower( $label_raw ) : strtolower( $label_raw );
    $label_slug = strtolower( sanitize_title( $label_raw ) );
    $label_all = $label . ' ' . $label_slug;

    if ( 'tb4_community_post' === $object_type || false !== strpos( $path, 'community' ) || false !== strpos( $label_all, 'community' ) || false !== strpos( $label_all, 'ชุมชน' ) ) {
        return 'community';
    }
    if ( 'tb4_member_page' === $object_type || false !== strpos( $path, 'member' ) || false !== strpos( $label_all, 'member' ) || false !== strpos( $label_all, 'สมาชิก' ) ) {
        return 'member';
    }
    if ( 'product' === $object_type || false !== strpos( $path, 'product' ) || false !== strpos( $path, 'shop' ) || false !== strpos( $label_all, 'product' ) || false !== strpos( $label_all, 'สินค้า' ) ) {
        return 'product';
    }
    if ( false !== strpos( $path, 'article' ) || false !== strpos( $path, 'blog' ) || false !== strpos( $path, 'post' ) || false !== strpos( $label_all, 'article' ) || false !== strpos( $label_all, 'บทความ' ) ) {
        return 'article';
    }
    if ( false !== strpos( $path, 'system' ) || false !== strpos( $path, 'platform' ) || false !== strpos( $path, 'solution' ) || false !== strpos( $label_all, 'system' ) || false !== strpos( $label_all, 'ระบบ' ) ) {
        return 'system';
    }
    return 'page';
}

function tb4cm_get_home_feed_post_preview( $post_id, $kind = 'page' ) {
    $post_id = absint( $post_id );
    if ( ! $post_id ) {
        return null;
    }

    $title = get_the_title( $post_id );
    $url   = get_permalink( $post_id );
    if ( ! $title || ! $url ) {
        return null;
    }

    $image = get_the_post_thumbnail_url( $post_id, 'medium_large' );
    if ( ! $image && 'community' === $kind && function_exists( 'tb4cm_get_post_media_album' ) ) {
        $album = tb4cm_get_post_media_album( $post_id, get_post_field( 'post_content', $post_id ) );
        if ( ! empty( $album[0] ) && is_array( $album[0] ) ) {
            $image = 'video' === ( $album[0]['type'] ?? '' ) && function_exists( 'tb4cm_get_video_poster_url' ) ? tb4cm_get_video_poster_url( $album[0] ) : ( $album[0]['url'] ?? '' );
        }
    }

    $plain = wp_strip_all_tags( get_post_field( 'post_excerpt', $post_id ) ?: get_post_field( 'post_content', $post_id ) );
    $views = absint( get_post_meta( $post_id, 'tb4_view_count', true ) );
    $likes = absint( get_post_meta( $post_id, 'tb4_like_count', true ) );
    $quality = absint( get_post_meta( $post_id, 'tb4_post_quality_score', true ) ?: get_post_meta( $post_id, 'tb4_quality_score', true ) );
    $comments = get_comments_number( $post_id );

    $post_type_object = get_post_type_object( get_post_type( $post_id ) );
    $post_type_label  = ( $post_type_object && ! empty( $post_type_object->labels->singular_name ) ) ? $post_type_object->labels->singular_name : __( 'รายการ', 'thinkb4do-community' );

    return [
        'title'    => $title,
        'url'      => $url,
        'image'    => $image,
        'excerpt'  => wp_trim_words( $plain, 22 ),
        'date'     => get_the_date( 'j M Y', $post_id ),
        'score'    => min( 100, max( 20, $quality + min( 18, $likes * 2 ) + min( 18, $comments * 2 ) + min( 18, (int) floor( $views / 8 ) ) ) ),
        'meta'     => sprintf( '%s · %s', get_the_date( 'j M Y', $post_id ), $post_type_label ),
        'views'    => $views,
        'likes'    => $likes,
        'comments' => $comments,
    ];
}

function tb4cm_get_home_menu_highlight_cards( $menu_items, $limit = 6 ) {
    $limit = max( 3, min( 10, absint( $limit ) ) );
    $cards = [];
    $used_urls = [];

    foreach ( (array) $menu_items as $item ) {
        if ( count( $cards ) >= $limit ) {
            break;
        }

        $kind = tb4cm_home_menu_item_kind( $item );
        $query_args = [
            'post_status'            => 'publish',
            'posts_per_page'         => 3,
            'ignore_sticky_posts'    => true,
            'no_found_rows'          => true,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => false,
        ];
        $card_label = $item['label'];
        $card_url = $item['url'];
        $card_desc = $item['desc'];
        $source_type = 'เมนู';

        if ( 'community' === $kind ) {
            $query_args['post_type'] = 'tb4_community_post';
            $query_args['meta_key'] = 'tb4_view_count';
            $query_args['orderby'] = [ 'meta_value_num' => 'DESC', 'date' => 'DESC' ];
            $source_type = 'ฟีดชุมชน';
        } elseif ( 'member' === $kind ) {
            $query_args['post_type'] = 'page';
            $query_args['name'] = 'member-area';
            $query_args['orderby'] = 'date';
            $query_args['order'] = 'DESC';
            $source_type = 'พื้นที่สมาชิก';
        } elseif ( 'product' === $kind && post_type_exists( 'product' ) ) {
            $query_args['post_type'] = 'product';
            $query_args['orderby'] = 'date';
            $query_args['order'] = 'DESC';
            $source_type = 'สินค้า';
        } elseif ( 'article' === $kind ) {
            $query_args['post_type'] = 'post';
            $query_args['orderby'] = 'date';
            $query_args['order'] = 'DESC';
            $source_type = 'บทความ';
        } elseif ( 'system' === $kind ) {
            $query_args['post_type'] = [ 'page', 'post' ];
            $query_args['s'] = 'system ระบบ platform solution';
            $query_args['orderby'] = 'date';
            $query_args['order'] = 'DESC';
            $source_type = 'ระบบ';
        } elseif ( ! empty( $item['object_id'] ) ) {
            $query_args['post_type'] = get_post_type( $item['object_id'] ) ?: 'page';
            $query_args['p'] = absint( $item['object_id'] );
            $source_type = 'หน้าเว็บ';
        } else {
            $query_args['post_type'] = [ 'page', 'post' ];
            $query_args['orderby'] = 'date';
            $query_args['order'] = 'DESC';
        }

        if ( ! post_type_exists( is_array( $query_args['post_type'] ) ? reset( $query_args['post_type'] ) : $query_args['post_type'] ) && ! is_array( $query_args['post_type'] ) ) {
            continue;
        }

        $query = new WP_Query( $query_args );
        $previews = [];
        if ( $query->have_posts() ) {
            foreach ( $query->posts as $post ) {
                $preview = tb4cm_get_home_feed_post_preview( $post->ID, $kind );
                if ( $preview && ! isset( $used_urls[ trailingslashit( $preview['url'] ) ] ) ) {
                    $previews[] = $preview;
                    $used_urls[ trailingslashit( $preview['url'] ) ] = true;
                }
            }
        }
        wp_reset_postdata();

        if ( empty( $previews ) ) {
            $previews[] = [
                'title'    => $card_label,
                'url'      => $card_url,
                'image'    => '',
                'excerpt'  => $card_desc ?: __( 'เปิดดูเนื้อหาและรายการที่เกี่ยวข้องจากเมนูนี้', 'thinkb4do-community' ),
                'date'     => '',
                'score'    => 72,
                'meta'     => $source_type,
                'views'    => 0,
                'likes'    => 0,
                'comments' => 0,
            ];
        }

        $top_preview = $previews[0];
        $cards[] = [
            'kind'       => $kind,
            'label'      => $card_label,
            'icon'       => $item['icon'],
            'fallback'   => $item['fallback'],
            'menu_url'   => $card_url,
            'desc'       => $card_desc,
            'source'     => $source_type,
            'preview'    => $top_preview,
            'items'      => $previews,
            'item_count' => count( $previews ),
        ];
    }

    return apply_filters( 'tb4c_home_menu_highlight_cards', $cards, $menu_items, $limit );
}

function tb4cm_render_mobile_nav( $active = 'community' ) {
    // v4.2.202: bottom mobile menu remains removed from active UI to keep the page clean and avoid duplicate navigation.
    // Returning here prevents old template calls or cached pages from rendering the lower menu again.
    if ( apply_filters( 'tb4c_disable_mobile_bottom_nav', true, $active ) ) {
        return;
    }

    static $tb4c_mobile_nav_rendered = false;

    if ( $tb4c_mobile_nav_rendered ) {
        return;
    }

    $tb4c_mobile_nav_rendered = true;
    $active = sanitize_key( $active );
    $active_map = [
        'feed'      => 'community',
        'discover'  => 'community',
        'people'    => 'community',
        'area'      => 'community',
        'community' => 'community',
        'home'      => 'home',
        'article'   => 'article',
        'product'   => 'product',
        'system'    => 'system',
    ];
    $active_key = $active_map[ $active ] ?? 'community';

    $items = [
        'home' => [
            'url'      => home_url( '/' ),
            'icon'     => 'ph-house',
            'fallback' => '⌂',
            'label'    => __( 'หน้าแรก', 'thinkb4do-community' ),
        ],
        'article' => [
            'url'      => tb4cm_resolve_public_page_url( [ 'articles', 'article', 'blog', 'posts' ], '/articles/' ),
            'icon'     => 'ph-newspaper-clipping',
            'fallback' => '📰',
            'label'    => __( 'บทความ', 'thinkb4do-community' ),
        ],
        'community' => [
            'url'      => home_url( '/community/' ),
            'icon'     => 'ph-users-three',
            'fallback' => '👥',
            'label'    => __( 'ชุมชน', 'thinkb4do-community' ),
        ],
        'product' => [
            'url'      => tb4cm_resolve_public_page_url( [ 'products', 'product', 'shop', 'store' ], '/products/' ),
            'icon'     => 'ph-shopping-bag-open',
            'fallback' => '🛍',
            'label'    => __( 'สินค้า', 'thinkb4do-community' ),
        ],
        'system' => [
            'url'      => tb4cm_resolve_public_page_url( [ 'systems', 'system', 'platform', 'solutions' ], '/systems/' ),
            'icon'     => 'ph-squares-four',
            'fallback' => '▦',
            'label'    => __( 'ระบบ', 'thinkb4do-community' ),
        ],
    ];

    echo '<nav class="tb4c-mobile-bottom-nav tb4c-global-mobile-nav tb4c-canonical-mobile-nav" data-tb4c-mobile-nav="canonical" data-tb4c-ui-role="mobile-nav" aria-label="' . esc_attr__( 'เมนูมือถือ', 'thinkb4do-community' ) . '">';
    foreach ( $items as $key => $item ) {
        $class = $active_key === $key ? 'is-active' : '';
        echo '<a class="' . esc_attr( trim( $class . ( 'community' === $key ? ' is-community' : '' ) ) ) . '" href="' . esc_url( $item['url'] ) . '" aria-label="' . esc_attr( $item['label'] ) . '">';
        echo tb4cm_icon_markup( $item['icon'], $item['fallback'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '<span>' . esc_html( $item['label'] ) . '</span></a>';
    }
    echo '</nav>';
}

function tb4cm_topic_label( $slug ) {
    $labels = [
        'discussion' => 'พูดคุย',
        'question'   => 'ถาม-ตอบ',
        'idea'       => 'ไอเดีย',
        'project'    => 'โปรเจกต์',
        'event'      => 'กิจกรรม',
        'creator'    => 'Creator',
        'popular'    => 'มาแรง',
        'latest'     => 'ล่าสุด',
    ];
    return $labels[ $slug ] ?? sanitize_text_field( $slug );
}

function tb4cm_dev_text( $key, $fallback = '' ) {
    if ( function_exists( 'tb4_dev_text' ) ) {
        return tb4_dev_text( $key, $fallback );
    }

    $defaults = [
        'community_page_title'  => 'ฟีดชุมชน Thinkb4do',
        'community_page_desc'   => 'พื้นที่ชุมชนสำหรับพูดคุย ถาม-ตอบ แชร์ไอเดีย โปรเจกต์ และกิจกรรมของสมาชิก',
        'community_empty_title' => 'ยังไม่มีโพสต์ในชุมชน',
        'community_empty_body'  => 'เริ่มจากการสร้างโพสต์แรก แล้วชวนสมาชิกเข้ามาพูดคุยกัน',
        'member_highlight'      => 'โปรไฟล์สมาชิก Thinkb4do',
        'member_trust'          => 'ข้อมูลเปิดเผยเท่าที่จำเป็น',
    ];

    return $defaults[ $key ] ?? $fallback;
}

function tb4cm_get_review_status_label( $status ) {
    $labels = [
        'pending'  => '<span class="review-status review-pending">⏳ รอตรวจสอบ</span>',
        'approved' => '<span class="review-status review-approved">✅ อนุมัติแล้ว</span>',
        'rejected' => '<span class="review-status review-rejected">❌ ส่งกลับแก้ไข</span>',
    ];
    return $labels[ $status ] ?? $labels['pending'];
}

function tb4cm_get_quality_score_color( $score ) {
    $score = (int) $score;
    if ( $score >= 80 ) {
        return '#1E6B45';
    }
    if ( $score >= 60 ) {
        return '#f59e0b';
    }
    return '#ef4444';
}


/**
 * Admin settings, sync tools and metaboxes.
 */
function tb4cm_admin_menu() {
    add_options_page(
        __( 'Thinkb4do Community', 'thinkb4do-community' ),
        __( 'Thinkb4do Community', 'thinkb4do-community' ),
        'manage_options',
        'thinkb4do-community',
        'tb4cm_render_admin_page'
    );
}
add_action( 'admin_menu', 'tb4cm_admin_menu' );


function tb4cm_get_runtime_compatibility_report() {
    global $wp_version;

    $checks = [
        [
            'label'  => 'WordPress Core',
            'status' => version_compare( $wp_version, '6.0', '>=' ) ? 'pass' : 'warn',
            'detail' => sprintf( 'ตรวจพบ WordPress %s / แนะนำ 6.0 ขึ้นไป', $wp_version ),
        ],
        [
            'label'  => 'PHP Runtime',
            'status' => version_compare( PHP_VERSION, '7.4', '>=' ) ? 'pass' : 'warn',
            'detail' => sprintf( 'ตรวจพบ PHP %s / แนะนำ 7.4 ขึ้นไป', PHP_VERSION ),
        ],
        [
            'label'  => 'REST API / Gutenberg',
            'status' => function_exists( 'register_block_type' ) ? 'pass' : 'warn',
            'detail' => function_exists( 'register_block_type' ) ? 'รองรับ Dynamic Block และ Shortcode Block' : 'ยังใช้ Shortcode ได้ แต่ไม่พบ register_block_type',
        ],
        [
            'label'  => 'Permalink / Routing',
            'status' => get_option( 'permalink_structure' ) ? 'pass' : 'warn',
            'detail' => get_option( 'permalink_structure' ) ? 'Pretty Permalink เปิดใช้งานแล้ว' : 'ยังใช้ default permalink ได้ แต่แนะนำเปิด Pretty Permalink เพื่อ URL สวย',
        ],
        [
            'label'  => 'Theme Independence',
            'status' => 'pass',
            'detail' => 'ระบบชุมชนทำงานเป็นปลั๊กอินอิสระ และเชื่อมธีมผ่าน filter bridge เท่านั้น',
        ],
        [
            'label'  => 'HTTPS / Mixed Content',
            'status' => is_ssl() ? 'pass' : 'warn',
            'detail' => is_ssl() ? 'เว็บไซต์ใช้ HTTPS แล้ว' : 'ยังไม่ใช่ HTTPS — ใช้ได้ แต่แนะนำเปิด SSL เพื่อความปลอดภัยของฟอร์มและ AJAX',
        ],
    ];

    return $checks;
}

function tb4cm_get_static_compatibility_matrix() {
    return [
        'อุปกรณ์' => [
            'Desktop / Laptop' => 'รองรับ: layout กว้าง, sidebar sticky, mouse/keyboard',
            'Tablet'           => 'รองรับ: grid ยุบเป็น 1-2 คอลัมน์, ปุ่มแตะง่าย',
            'Mobile'           => 'รองรับ: modal แบบ bottom sheet, safe-area, ปุ่มขั้นต่ำ 44px',
            'Touch Device'     => 'รองรับ: pointer coarse เพิ่มขนาดปุ่มและช่องกรอก',
        ],
        'ระบบปฏิบัติการ' => [
            'Windows' => 'รองรับ Chrome, Edge, Firefox',
            'macOS'   => 'รองรับ Safari, Chrome, Firefox, Edge',
            'iOS / iPadOS' => 'รองรับ Safari/Chrome WebView พร้อม safe-area',
            'Android' => 'รองรับ Chrome, Samsung Internet, WebView สมัยใหม่',
            'Linux'   => 'รองรับ Chromium/Chrome และ Firefox',
        ],
        'บราวเซอร์' => [
            'Chrome / Chromium' => 'รองรับเต็มรูปแบบ',
            'Microsoft Edge'    => 'รองรับเต็มรูปแบบ',
            'Firefox'           => 'รองรับเต็มรูปแบบ พร้อม scrollbar fallback',
            'Safari'            => 'รองรับ พร้อม -webkit fallback และ safe-area',
            'Samsung Internet'  => 'รองรับบน Android พร้อม touch fallback',
            'Legacy WebView'    => 'รองรับขั้นพื้นฐานผ่าน no-grid / no-fetch fallback',
        ],
        'ตัวสร้างหน้า' => [
            'Elementor' => 'ใช้ Shortcode Widget: [thinkb4do_community]',
            'Gutenberg' => 'ใช้ Shortcode Block หรือ Dynamic Block thinkb4do/community',
            'Classic Editor' => 'ใช้ Shortcode ได้โดยตรง',
            'Block Theme / Classic Theme' => 'Template routing ทำงานผ่านปลั๊กอิน ไม่ผูกกับธีมหลัก',
        ],
    ];
}

function tb4cm_render_status_badge( $status ) {
    $status = sanitize_key( $status );
    if ( 'pass' === $status ) {
        return '<span style="display:inline-flex;align-items:center;border-radius:999px;background:#e8f5ee;color:#1E6B45;padding:4px 10px;font-weight:700;">ผ่าน</span>';
    }
    return '<span style="display:inline-flex;align-items:center;border-radius:999px;background:#fff7ed;color:#F97316;padding:4px 10px;font-weight:700;">ควรตรวจ</span>';
}

function tb4cm_render_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $community_count = wp_count_posts( 'tb4_community_post' );
    $member_count    = (object) [ 'publish' => 0 ]; // v4.2.74: member pages removed.
    $sync_url        = wp_nonce_url( admin_url( 'admin-post.php?action=tb4cm_sync_pages' ), 'tb4cm_sync_pages' );
    ?>
    <div class="wrap tb4c-admin-wrap">
        <h1>Thinkb4do Community</h1>
        <p><strong>Settings | โดย Thinkb4do | ดูรายละเอียด</strong></p>
        <p>เวอร์ชัน 4.2.9 Comment Popup Polish: แสดงความคิดเห็นทันทีหลังส่ง ปรับสีปุ่มส่งให้ชัด และปรับ popup ให้อ่านง่ายขึ้น</p>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin:20px 0;">
            <div class="card" style="padding:16px;max-width:none;"><h2><?php echo esc_html( (int) ( $community_count->publish ?? 0 ) ); ?></h2><p>โพสต์ชุมชนที่เผยแพร่</p></div>
            <div class="card" style="padding:16px;max-width:none;"><h2><?php echo esc_html( (int) ( $community_count->pending ?? 0 ) ); ?></h2><p>โพสต์รอตรวจสอบ</p></div>
            <div class="card" style="padding:16px;max-width:none;"><h2><?php echo esc_html( (int) ( $member_count->publish ?? 0 ) ); ?></h2><p>พื้นที่สมาชิกที่เผยแพร่</p></div>
        </div>
        <h2>สถานะระบบ</h2>
        <table class="widefat striped">
            <tbody>
                <tr><td>แยกจากธีม</td><td><strong>พร้อมใช้งาน</strong> — ธีมดูแล layout/base token ส่วนปลั๊กอินดูแล social community</td></tr>
                <tr><td>Elementor</td><td>ใช้ Shortcode Widget: <code>[thinkb4do_community]</code></td></tr>
                <tr><td>Gutenberg</td><td>ใช้ Shortcode Block หรือ dynamic block: <code>thinkb4do/community</code></td></tr>
                <tr><td>หน้า Community</td><td><a class="button button-primary" href="<?php echo esc_url( $sync_url ); ?>">สร้าง/ซิงก์หน้าหลัก</a></td></tr>
            </tbody>
        </table>
        <h2>Compatibility Layer / รองรับทุกระบบ</h2>
        <table class="widefat striped">
            <thead><tr><th>รายการตรวจ</th><th>สถานะ</th><th>รายละเอียด</th></tr></thead>
            <tbody>
                <?php foreach ( tb4cm_get_runtime_compatibility_report() as $check ) : ?>
                    <tr>
                        <td><?php echo esc_html( $check['label'] ); ?></td>
                        <td><?php echo wp_kses_post( tb4cm_render_status_badge( $check['status'] ) ); ?></td>
                        <td><?php echo esc_html( $check['detail'] ); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p><strong>สรุป:</strong> เมื่อใช้กับ Thinkb4do Theme Core v2.3.1 Modern Refresh ปลั๊กอินจะไม่สร้าง header/container ซ้ำ และใช้ token --tb4-modern-* ของธีมเป็นหลัก ส่วน fallback ที่จำเป็นยังอยู่เฉพาะในขอบเขต .tb4c- เท่านั้น</p>

        <h2>ตารางรองรับอุปกรณ์ / OS / Browser / Builder</h2>
        <?php foreach ( tb4cm_get_static_compatibility_matrix() as $group => $items ) : ?>
            <h3><?php echo esc_html( $group ); ?></h3>
            <table class="widefat striped" style="margin-bottom:16px;">
                <tbody>
                    <?php foreach ( $items as $name => $detail ) : ?>
                        <tr><td style="width:240px;"><strong><?php echo esc_html( $name ); ?></strong></td><td><?php echo esc_html( $detail ); ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>

        <h2>Shortcode</h2>
        <p><code>[thinkb4do_community]</code> แสดงหน้าชุมชนเต็ม</p>
        <p><code>[thinkb4do_community_composer]</code> แสดงกล่องสร้างโพสต์</p>
        <p><code>[thinkb4do_member_pages]</code> ทางลัดเก่า — ตอนนี้พาไป <code>[thinkb4do_member_area]</code></p>
        <p><code>[thinkb4do_member_area]</code> แสดงพื้นที่สมาชิกส่วนตัว</p>

        <h2>โครงสร้างแพ็ก</h2>
        <table class="widefat striped">
            <tbody>
                <tr><td><code>thinkb4do-community.php</code></td><td>ไฟล์หลักของปลั๊กอิน: CPT, AJAX, Shortcode, Blocks, Admin, Theme Bridge</td></tr>
                <tr><td><code>templates/community-content.php</code></td><td>หน้า Feed ชุมชนหลัก ใช้ได้ทั้ง Template และ Shortcode</td></tr>
                <tr><td><code>templates/community-form.php</code></td><td>ฟอร์มสร้างโพสต์ชุมชน</td></tr>
                <tr><td><code>templates/community-composer.php</code></td><td>กล่อง Composer สำหรับเปิด Modal</td></tr>
                <tr><td><code>templates/member-area-content.php</code></td><td>พื้นที่สมาชิกส่วนตัวสำหรับ /member-area/ เช่น ภาพรวม โพสต์ ความคิดเห็น บันทึกไว้ และตั้งค่าโปรไฟล์</td></tr>
                <tr><td><code>templates/single-community-post.php</code></td><td>หน้าอ่านโพสต์ชุมชนเดี่ยว พร้อม view count และ comments</td></tr>
                <tr><td><code>templates/comments-community.php</code></td><td>เทมเพลตความคิดเห็นแบบ social media สำหรับรายการความคิดเห็นและฟอร์มแสดงความคิดเห็น</td></tr>
                <tr><td><code>assets/community.css</code></td><td>UI/UX social แบบ clean scoped ใช้ modern token ของธีม v2.3.1 และไม่ซ้ำ header/container ของธีม</td></tr>
                <tr><td><code>assets/community.js</code></td><td>Modal, AJAX submit, Like, Bookmark, Share, app shell state พร้อม fetch/XHR fallback และ browser feature detection</td></tr>
            </tbody>
        </table>
        <h2>Necessity Analyzer</h2>
        <table class="widefat striped">
            <tbody>
                <tr><td>ระบบโพสต์ชุมชน</td><td>จำเป็น 95%</td></tr>
                <tr><td>ระบบพื้นที่สมาชิก member-area</td><td>จำเป็น 90%</td></tr>
                <tr><td>ระบบ Like/Bookmark/Share</td><td>จำเป็น 82% — เพิ่มเป็น social action หลัก</td></tr>
                <tr><td>ระบบ Follow/Following</td><td>จำเป็น 86% — เพิ่มแล้วใน v1.3.0</td></tr>
                <tr><td>เมนูไอคอนสำหรับ Social Network</td><td>จำเป็น 88% — เพิ่มแล้วใน v1.4.0</td></tr>
                <tr><td>Thinkb4do Clean Responsive Social</td><td>จำเป็น 99% — ปรับใน v2.6.0 ให้ลดความรก คุมสัดส่วนทุกหน้า และเหมาะกับอุปกรณ์/บราวเซอร์หลักมากขึ้น</td></tr>
                <tr><td>App-like Social Experience</td><td>จำเป็น 94% — คง App Shell, View Switch, Bottom Sheet และ Mobile Bottom Navigation</td></tr>
                <tr><td>Theme-fit De-duplicate</td><td>จำเป็น 97% — ยังใช้ธีมดูแล layout/base token และไม่สร้าง header ซ้ำ</td></tr>
                <tr><td>Cross-page Thinkb4do Clean Consistency</td><td>จำเป็น 98% — ทำให้ Feed, People, Profile, Single Post, Composer, Comments และ Mobile Navigation ใช้สัดส่วนเรียบ โปร่ง และเป็นชุดเดียวกัน</td></tr>
                <tr><td>ระบบเรียลไทม์เต็มรูปแบบ</td><td>ยังไม่จำเป็น 45% — ควรทำหลังฐาน social network เสถียร</td></tr>
            </tbody>
        </table>
    </div>
    <?php
}

function tb4cm_sync_pages() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'Permission denied', 'thinkb4do-community' ) );
    }
    check_admin_referer( 'tb4cm_sync_pages' );

    tb4cm_create_or_update_page( 'community', 'ชุมชน', '<!-- wp:shortcode -->[thinkb4do_community]<!-- /wp:shortcode -->', 'tb4-community-template.php' );
    tb4cm_create_or_update_page( 'member-area', 'พื้นที่สมาชิก', '<!-- wp:shortcode -->[thinkb4do_member_area]<!-- /wp:shortcode -->', '' );

    wp_safe_redirect( admin_url( 'options-general.php?page=thinkb4do-community&synced=1' ) );
    exit;
}
add_action( 'admin_post_tb4c_sync_pages', 'tb4cm_sync_pages' );

function tb4cm_create_or_update_page( $slug, $title, $content, $template = '' ) {
    $page = get_page_by_path( $slug );
    $data = [
        'post_title'   => $title,
        'post_name'    => $slug,
        'post_content' => $content,
        'post_status'  => 'publish',
        'post_type'    => 'page',
    ];

    if ( $page ) {
        $data['ID'] = $page->ID;
        $page_id    = wp_update_post( $data, true );
    } else {
        $page_id = wp_insert_post( $data, true );
    }

    if ( ! is_wp_error( $page_id ) && $template ) {
        update_post_meta( $page_id, '_wp_page_template', $template );
    }

    return $page_id;
}

function tb4cm_add_meta_boxes() {
    add_meta_box( 'tb4c_community_status', 'Thinkb4do Community QC', 'tb4cm_render_community_meta_box', 'tb4_community_post', 'side', 'high' );
}
add_action( 'add_meta_boxes', 'tb4cm_add_meta_boxes' );

function tb4cm_render_community_meta_box( $post ) {
    wp_nonce_field( 'tb4c_save_meta', 'tb4c_meta_nonce' );
    $status = get_post_meta( $post->ID, 'tb4_post_review_status', true ) ?: 'pending';
    $score  = (int) get_post_meta( $post->ID, 'tb4_post_quality_score', true ) ?: 70;
    $pinned = (bool) get_post_meta( $post->ID, 'tb4_is_pinned', true );
    ?>
    <p><label>สถานะ</label><br><select name="tb4_post_review_status" style="width:100%;"><option value="pending" <?php selected( $status, 'pending' ); ?>>รอตรวจสอบ</option><option value="approved" <?php selected( $status, 'approved' ); ?>>อนุมัติ</option><option value="rejected" <?php selected( $status, 'rejected' ); ?>>ส่งกลับแก้ไข</option></select></p>
    <p><label>Quality Score</label><br><input type="number" min="0" max="100" name="tb4_post_quality_score" value="<?php echo esc_attr( $score ); ?>" style="width:100%;"></p>
    <p><label><input type="checkbox" name="tb4_is_pinned" value="1" <?php checked( $pinned ); ?>> ปักหมุดโพสต์นี้</label></p>
    <?php
}

function tb4cm_save_meta_boxes( $post_id ) {
    if ( ! isset( $_POST['tb4c_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tb4c_meta_nonce'] ) ), 'tb4c_save_meta' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    $post_type = get_post_type( $post_id );
    if ( 'tb4_community_post' === $post_type ) {
        update_post_meta( $post_id, 'tb4_post_review_status', sanitize_key( wp_unslash( $_POST['tb4_post_review_status'] ?? 'pending' ) ) );
        update_post_meta( $post_id, 'tb4_post_quality_score', min( 100, absint( $_POST['tb4_post_quality_score'] ?? 70 ) ) );
        update_post_meta( $post_id, 'tb4_is_pinned', isset( $_POST['tb4_is_pinned'] ) ? 1 : 0 );
    }
}
add_action( 'save_post', 'tb4cm_save_meta_boxes' );
