<?php
/**
 * Plugin Name: Thinkb4do Community Comment Page
 * Plugin URI: https://thinkb4do.com
 * Description: แยกหน้าแสดงความคิดเห็น/Single Community Post ออกจาก Feed Main ให้เป็นปลั๊กอินเฉพาะ พร้อม UI เท่าหน้าฟีด ปุ่มแสดงความคิดเห็น/ตอบกลับ/แก้ไข/ลบแบบมาตรฐาน และกันระบบความคิดเห็นซ้อน.
 * Version: 1.0.21
 * Author: Thinkb4do
 * Text Domain: thinkb4do-community-comment-page
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

defined( 'ABSPATH' ) || exit;

define( 'TB4CCP_VERSION', '1.0.21' );
define( 'TB4CCP_DIR', plugin_dir_path( __FILE__ ) );
define( 'TB4CCP_URL', plugin_dir_url( __FILE__ ) );

register_activation_hook( __FILE__, 'tb4ccp_activate' );
function tb4ccp_activate() {
    update_option( 'tb4ccp_version', TB4CCP_VERSION, false );
    flush_rewrite_rules();
}

register_deactivation_hook( __FILE__, 'tb4ccp_deactivate' );
function tb4ccp_deactivate() {
    flush_rewrite_rules();
}

function tb4ccp_is_comment_page() {
    return ! is_admin() && is_singular( 'tb4_community_post' );
}

function tb4ccp_template_include( $template ) {
    if ( is_singular( 'tb4_community_post' ) ) {
        $plugin_template = TB4CCP_DIR . 'templates/single-community-post.php';
        if ( file_exists( $plugin_template ) ) {
            return $plugin_template;
        }
    }
    return $template;
}
add_filter( 'template_include', 'tb4ccp_template_include', 20000 );

function tb4ccp_enqueue_assets() {
    if ( ! tb4ccp_is_comment_page() ) {
        return;
    }

    wp_enqueue_style(
        'thinkb4do-community-comment-page',
        TB4CCP_URL . 'assets/comment-page.css',
        [],
        TB4CCP_VERSION . '-' . filemtime( TB4CCP_DIR . 'assets/comment-page.css' )
    );

    // v1.0.21: เก็บรายละเอียดภาพ/คอมเมนต์ ย้ายไอคอนแก้ไขลบเข้าใน bubble และเอาพื้นหลังดำของภาพออก.
    wp_enqueue_style( 'dashicons' );

    if ( ! wp_style_is( 'tb4-icons', 'enqueued' ) ) {
        wp_enqueue_style(
            'tb4-icons',
            'https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css',
            [],
            '2.1.1'
        );
    }

    wp_enqueue_script(
        'thinkb4do-community-comment-page',
        TB4CCP_URL . 'assets/comment-page.js',
        [],
        TB4CCP_VERSION . '-' . filemtime( TB4CCP_DIR . 'assets/comment-page.js' ),
        true
    );

    wp_localize_script( 'thinkb4do-community-comment-page', 'tb4cCommunity', [
        'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
        'nonce'        => wp_create_nonce( 'tb4c_nonce' ),
        'isLoggedIn'   => is_user_logged_in(),
        'currentUserId' => get_current_user_id(),
        'loginUrl'     => wp_login_url( tb4ccp_get_current_url() ),
        'communityUrl' => home_url( '/community/' ),
        'version'      => TB4CCP_VERSION,
        'assetContext' => [ 'surface' => 'comment-page', 'owner' => 'thinkb4do-community-comment-page' ],
        'messages'     => [
            'needLogin' => __( 'กรุณาเข้าสู่ระบบก่อนใช้งานส่วนนี้', 'thinkb4do-community-comment-page' ),
            'saving'    => __( 'กำลังบันทึก...', 'thinkb4do-community-comment-page' ),
            'saved'     => __( 'บันทึกแล้ว', 'thinkb4do-community-comment-page' ),
            'failed'    => __( 'เกิดข้อผิดพลาด กรุณาลองใหม่', 'thinkb4do-community-comment-page' ),
        ],
    ] );

    wp_add_inline_script( 'thinkb4do-community-comment-page', 'document.documentElement.classList.add("tb4c-v4268-single-hard-isolate","tb4c-v4269-single-inline-edit-icons","tb4ccp-comment-page-plugin","tb4ccp-comment-page-v121");', 'before' );
}
add_action( 'wp_enqueue_scripts', 'tb4ccp_enqueue_assets', 99 );

function tb4ccp_body_classes( $classes ) {
    if ( is_singular( 'tb4_community_post' ) ) {
        $classes[] = 'tb4c-active-page';
        $classes[] = 'tb4c-v4268-single-hard-isolate-body';
        $classes[] = 'tb4ccp-comment-page-body';
    }
    return $classes;
}
add_filter( 'body_class', 'tb4ccp_body_classes', 99 );

function tb4ccp_get_current_url() {
    $scheme = is_ssl() ? 'https://' : 'http://';
    $host   = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
    $uri    = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
    return esc_url_raw( $scheme . $host . $uri );
}

function tb4ccp_icon_markup( $icon_class, $fallback = '•', $extra_class = '' ) {
    if ( function_exists( 'tb4cf_icon_markup' ) ) {
        return tb4cf_icon_markup( $icon_class, $fallback, $extra_class );
    }
    $icon_class = sanitize_html_class( $icon_class );
    $extra_class = trim( preg_replace( '/[^a-zA-Z0-9_\-\s]/', '', (string) $extra_class ) );
    return sprintf(
        '<span class="tb4c-ui-icon %1$s" aria-hidden="true"><i class="ph %2$s"></i><span class="tb4c-icon-fallback">%3$s</span></span>',
        esc_attr( $extra_class ),
        esc_attr( $icon_class ),
        esc_html( wp_strip_all_tags( (string) $fallback ) )
    );
}

function tb4ccp_get_initial( $name ) {
    if ( function_exists( 'tb4cf_get_initial' ) ) {
        return tb4cf_get_initial( $name );
    }
    $name = trim( wp_strip_all_tags( (string) $name ) );
    if ( '' === $name ) {
        return 'T';
    }
    return function_exists( 'mb_substr' ) ? mb_substr( $name, 0, 1 ) : substr( $name, 0, 1 );
}

function tb4ccp_social_profile_url( $user_id ) {
    if ( function_exists( 'tb4cf_social_profile_url' ) ) {
        return tb4cf_social_profile_url( $user_id );
    }
    return home_url( '/community/' );
}

function tb4ccp_visibility_icon_class( $visibility ) {
    if ( function_exists( 'tb4cf_visibility_icon_class' ) ) {
        return tb4cf_visibility_icon_class( $visibility );
    }
    return 'groups' === $visibility ? 'ph-users-three' : 'ph-globe-hemisphere-east';
}

function tb4ccp_visibility_label( $visibility ) {
    if ( function_exists( 'tb4cf_visibility_label' ) ) {
        return tb4cf_visibility_label( $visibility );
    }
    return 'groups' === $visibility ? 'เฉพาะกลุ่ม' : 'สาธารณะ';
}

function tb4ccp_get_post_media_album( $post_id, $raw_text = '' ) {
    if ( function_exists( 'tb4cf_get_post_media_album' ) ) {
        return tb4cf_get_post_media_album( $post_id, $raw_text );
    }
    $album = [];
    $json = get_post_meta( $post_id, 'tb4_media_album', true );
    if ( $json ) {
        $decoded = json_decode( (string) $json, true );
        if ( is_array( $decoded ) ) {
            foreach ( $decoded as $item ) {
                if ( is_array( $item ) && ! empty( $item['url'] ) ) {
                    $album[] = [ 'type' => sanitize_key( $item['type'] ?? tb4ccp_guess_media_type( $item['url'] ) ), 'url' => esc_url_raw( $item['url'] ) ];
                }
            }
        }
    }
    $url = get_post_meta( $post_id, 'tb4_media_url', true );
    if ( empty( $album ) && $url ) {
        $album[] = [ 'type' => tb4ccp_guess_media_type( $url ), 'url' => esc_url_raw( $url ) ];
    }
    return $album;
}

function tb4ccp_guess_media_type( $url ) {
    $path = strtolower( parse_url( (string) $url, PHP_URL_PATH ) ?: '' );
    if ( preg_match( '/\.(mp4|webm|mov|m4v)$/', $path ) || false !== strpos( $url, 'youtube.com' ) || false !== strpos( $url, 'youtu.be' ) || false !== strpos( $url, 'vimeo.com' ) ) {
        return 'video';
    }
    return 'image';
}

function tb4ccp_get_post_tag_input_value( $post_id ) {
    if ( function_exists( 'tb4cf_get_post_tag_input_value' ) ) {
        return tb4cf_get_post_tag_input_value( $post_id );
    }
    $terms = get_the_terms( $post_id, 'tb4_community_tag' );
    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        return '';
    }
    return implode( ' ', wp_list_pluck( $terms, 'name' ) );
}

function tb4ccp_render_post_type_tag_strip( $post_id, $type, $limit = 8 ) {
    if ( function_exists( 'tb4cf_render_post_type_tag_strip' ) ) {
        return tb4cf_render_post_type_tag_strip( $post_id, $type, $limit );
    }
    $labels = [
        'discussion' => 'พูดคุย',
        'question'   => 'ถาม-ตอบ',
        'idea'       => 'ไอเดีย',
        'project'    => 'โปรเจกต์',
        'event'      => 'กิจกรรม',
        'creator'    => 'Creator',
    ];
    $out = '<div class="tb4c-sp268-tags-inline">';
    $out .= '<span class="tb4c-post-type-chip">ประเภท ' . esc_html( $labels[ $type ] ?? 'พูดคุย' ) . '</span>';
    $terms = get_the_terms( $post_id, 'tb4_community_tag' );
    if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
        $i = 0;
        foreach ( $terms as $term ) {
            if ( $i >= absint( $limit ) ) {
                break;
            }
            $out .= '<span class="tb4c-privacy-chip">#' . esc_html( $term->name ) . '</span>';
            $i++;
        }
    }
    $out .= '</div>';
    return $out;
}

function tb4ccp_render_post_media_album( $album, $title = '' ) {
    if ( function_exists( 'tb4cf_render_post_media_album' ) ) {
        return tb4cf_render_post_media_album( $album, $title );
    }
    if ( empty( $album ) || ! is_array( $album ) ) {
        return '';
    }
    ob_start();
    echo '<div class="tb4c-media-album tb4ccp-media-album">';
    foreach ( $album as $item ) {
        $url = esc_url( $item['url'] ?? '' );
        if ( ! $url ) {
            continue;
        }
        $type = $item['type'] ?? tb4ccp_guess_media_type( $url );
        if ( 'video' === $type && preg_match( '/\.(mp4|webm|mov|m4v)$/i', parse_url( $url, PHP_URL_PATH ) ?: '' ) ) {
            echo '<video controls playsinline preload="metadata" src="' . $url . '"></video>';
        } elseif ( 'video' === $type ) {
            echo '<a class="tb4ccp-video-link" href="' . $url . '" target="_blank" rel="noopener">เปิดวิดีโอ</a>';
        } else {
            echo '<button type="button" class="tb4ccp-post-image-open" data-tb4ccp-media-open data-tb4ccp-media-type="image" data-tb4ccp-media-src="' . $url . '" data-tb4ccp-media-title="' . esc_attr( $title ) . '"><img src="' . $url . '" alt="' . esc_attr( $title ) . '" loading="lazy" decoding="async"></button>';
        }
    }
    echo '</div>';
    return trim( ob_get_clean() );
}

function tb4ccp_render_post_poll( $post_id ) {
    if ( function_exists( 'tb4cf_render_post_poll' ) ) {
        return tb4cf_render_post_poll( $post_id );
    }
    $question = get_post_meta( $post_id, 'tb4_poll_question', true );
    $options  = get_post_meta( $post_id, 'tb4_poll_options', true );
    if ( ! $question || ! is_array( $options ) || empty( $options ) ) {
        return '';
    }
    $out = '<div class="tb4c-poll-card"><strong>' . esc_html( $question ) . '</strong><div class="tb4c-poll-options">';
    foreach ( $options as $option ) {
        $out .= '<div class="tb4c-poll-option"><span>' . esc_html( $option ) . '</span><b>0%</b></div>';
    }
    return $out . '</div></div>';
}

function tb4ccp_get_approved_comment_count( $post_id ) {
    if ( function_exists( 'tb4cf_get_approved_comment_count' ) ) {
        return tb4cf_get_approved_comment_count( $post_id );
    }
    return (int) get_comments( [
        'post_id' => absint( $post_id ),
        'status'  => 'approve',
        'count'   => true,
        'type'    => 'comment',
    ] );
}

function tb4ccp_get_comment_tree( $post_id, $number = 80 ) {
    if ( function_exists( 'tb4cf_get_comment_tree' ) ) {
        return tb4cf_get_comment_tree( $post_id, $number );
    }
    $comments = get_comments( [
        'post_id' => absint( $post_id ),
        'status'  => 'approve',
        'number'  => absint( $number ),
        'orderby' => 'comment_date_gmt',
        'order'   => 'ASC',
        'type'    => 'comment',
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

function tb4ccp_get_comment_object( $comment_id ) {
    $comment = get_comment( absint( $comment_id ) );
    if ( ! $comment || ! $comment->comment_ID || 'tb4_community_post' !== get_post_type( (int) $comment->comment_post_ID ) ) {
        return null;
    }
    return $comment;
}

function tb4ccp_user_can_edit_community_comment( $comment ) {
    $comment = is_numeric( $comment ) ? tb4ccp_get_comment_object( $comment ) : $comment;
    if ( ! $comment || ! is_user_logged_in() ) {
        return false;
    }
    if ( current_user_can( 'moderate_comments' ) || current_user_can( 'edit_comment', (int) $comment->comment_ID ) ) {
        return true;
    }
    return (int) $comment->user_id > 0 && (int) $comment->user_id === get_current_user_id();
}

function tb4ccp_user_can_delete_community_comment( $comment ) {
    $comment = is_numeric( $comment ) ? tb4ccp_get_comment_object( $comment ) : $comment;
    if ( ! $comment || ! is_user_logged_in() ) {
        return false;
    }
    if ( tb4ccp_user_can_edit_community_comment( $comment ) || current_user_can( 'delete_comment', (int) $comment->comment_ID ) ) {
        return true;
    }
    return (int) get_post_field( 'post_author', (int) $comment->comment_post_ID ) === get_current_user_id();
}

function tb4ccp_detect_comment_media( $text ) {
    $text = trim( (string) $text );
    if ( ! preg_match( '~https?://\S+~', $text, $m ) ) {
        return null;
    }
    $url = esc_url_raw( rtrim( $m[0], '.,)' ) );
    if ( ! $url ) {
        return null;
    }
    return [ 'url' => $url, 'type' => tb4ccp_guess_media_type( $url ) ];
}

function tb4ccp_comment_visible_text( $raw_text, $media = null ) {
    $text = trim( wp_strip_all_tags( (string) $raw_text ) );
    if ( is_array( $media ) && ! empty( $media['url'] ) ) {
        $text = trim( str_replace( $media['url'], '', $text ) );
    }
    return $text;
}

function tb4ccp_render_comment_media_slot( $media ) {
    if ( ! is_array( $media ) || empty( $media['url'] ) ) {
        return '';
    }
    $url = esc_url( $media['url'] );
    if ( 'video' === ( $media['type'] ?? '' ) && preg_match( '/\.(mp4|webm|mov|m4v)$/i', parse_url( $url, PHP_URL_PATH ) ?: '' ) ) {
        return '<div class="tb4c-comment-media-slot tb4c-comment-video-slot"><video controls playsinline preload="metadata" src="' . $url . '"></video></div>';
    }
    if ( 'video' === ( $media['type'] ?? '' ) ) {
        return '<a class="tb4c-comment-media-slot tb4c-comment-video-fallback" href="' . $url . '" target="_blank" rel="noopener">เปิดวิดีโอ</a>';
    }
    return '<a class="tb4c-comment-media-slot tb4c-comment-image-slot" href="' . $url . '" target="_blank" rel="noopener" data-tb4ccp-media-open data-tb4ccp-media-type="image" data-tb4ccp-media-src="' . $url . '"><img src="' . $url . '" alt="" loading="lazy" decoding="async"></a>';
}


function tb4ccp_comment_action_svg( $name ) {
    // v1.0.19: ใช้ data target แยกจาก data-comment-id เพื่อไม่ให้ตัว dedupe ลบปุ่ม action ทิ้ง
    $name = sanitize_key( $name );
    $labels = [
        'comment' => 'ความคิดเห็น',
        'reply'   => 'ตอบกลับ',
        'copy'    => 'คัดลอก',
        'edit'    => 'แก้ไข',
        'delete'  => 'ลบ',
    ];
    if ( empty( $labels[ $name ] ) ) {
        return '';
    }

    $paths = [
        'comment' => '<path d="M21 12a8.6 8.6 0 0 1-9 8.5 9.5 9.5 0 0 1-3.6-.7L3 21l1.25-4.25A8.3 8.3 0 0 1 3 12a8.6 8.6 0 0 1 9-8.5A8.6 8.6 0 0 1 21 12Z"></path><path d="M8 10h8"></path><path d="M8 14h5"></path>',
        'reply'   => '<path d="M9 14 4 9l5-5"></path><path d="M4 9h10a6 6 0 0 1 6 6v5"></path>',
        'copy'    => '<rect x="8" y="8" width="11" height="11" rx="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v1"></path>',
        'edit'    => '<path d="M12 20h9"></path><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"></path>',
        'delete'  => '<path d="M3 6h18"></path><path d="M8 6V4h8v2"></path><path d="M19 6l-1 14H6L5 6"></path><path d="M10 11v5"></path><path d="M14 11v5"></path>',
    ];

    $svg = '<svg viewBox="0 0 24 24" focusable="false" aria-hidden="true">' . $paths[ $name ] . '</svg>';

    if ( 'comment' === $name ) {
        return '<span class="tb4ccp-action-icon tb4ccp-action-icon-comment" aria-hidden="true">' . $svg . '</span>';
    }

    if ( in_array( $name, [ 'edit', 'delete' ], true ) ) {
        return '<span class="tb4ccp-safe-icon tb4ccp-safe-icon-' . esc_attr( $name ) . '" aria-hidden="true">' . $svg . '</span><span class="tb4ccp-safe-sr">' . esc_html( $labels[ $name ] ) . '</span>';
    }

    return '<span class="tb4ccp-row-icon tb4ccp-row-icon-' . esc_attr( $name ) . '" aria-hidden="true">' . $svg . '</span><span class="tb4ccp-row-label">' . esc_html( $labels[ $name ] ) . '</span>';
}




function tb4ccp_render_comment_item( $comment, $tree = [], $depth = 0 ) {
    $comment = is_numeric( $comment ) ? get_comment( absint( $comment ) ) : $comment;
    if ( ! $comment || ! $comment->comment_ID ) {
        return '';
    }

    $comment_id    = (int) $comment->comment_ID;
    $author_name   = get_comment_author( $comment );
    $raw_text      = trim( wp_strip_all_tags( (string) $comment->comment_content ) );
    $comment_media = tb4ccp_detect_comment_media( $raw_text );
    $visible_text  = tb4ccp_comment_visible_text( $raw_text, $comment_media );
    $media_html    = tb4ccp_render_comment_media_slot( $comment_media );
    $can_edit      = tb4ccp_user_can_edit_community_comment( $comment );
    $can_delete    = tb4ccp_user_can_delete_community_comment( $comment );
    $children      = isset( $tree[ $comment_id ] ) && is_array( $tree[ $comment_id ] ) ? $tree[ $comment_id ] : [];
    $depth         = min( 3, max( 0, absint( $depth ) ) );
    $comment_time  = (int) get_comment_date( 'U', $comment );

    ob_start();
    ?>
    <li class="tb4c-comment-item tb4c-comment-thread-item tb4c-social-comment-item tb4c-comment-standard-layout tb4c-comment-inline-identity depth-<?php echo esc_attr( $depth ); ?> <?php echo ( $can_edit || $can_delete ) ? 'tb4ccp-has-owner-column' : ''; ?>" id="comment-<?php echo esc_attr( $comment_id ); ?>" data-comment-id="<?php echo esc_attr( $comment_id ); ?>" data-parent-id="<?php echo esc_attr( (int) $comment->comment_parent ); ?>">
      <div class="tb4c-comment-avatar"><?php echo wp_kses_post( get_avatar( $comment, 44 ) ); ?></div>
      <div class="tb4c-comment-body">
        <div class="tb4c-comment-content tb4c-comment-bubble-flat" data-tb4c-comment-bubble>
          <div class="tb4c-comment-topline">
            <div class="tb4c-comment-meta">
              <strong><?php echo esc_html( $author_name ); ?></strong>
              <span><?php echo esc_html( human_time_diff( $comment_time, current_time( 'timestamp' ) ) ); ?>ที่แล้ว</span>
            </div>
          </div>
          <?php if ( $can_edit || $can_delete ) : ?>
            <div class="tb4ccp-safe-owner-rail" data-tb4ccp-owner-tools aria-label="จัดการความคิดเห็น">
              <?php if ( $can_edit ) : ?>
                <button type="button" class="tb4ccp-safe-owner-btn tb4ccp-safe-owner-edit" data-tb4ccp-comment-action="edit" data-tb4ccp-target-comment="<?php echo esc_attr( $comment_id ); ?>" aria-label="แก้ไขความคิดเห็น" title="แก้ไข">
                  <?php echo tb4ccp_comment_action_svg( 'edit' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </button>
              <?php endif; ?>
              <?php if ( $can_delete ) : ?>
                <button type="button" class="tb4ccp-safe-owner-btn tb4ccp-safe-owner-delete" data-tb4ccp-comment-action="delete" data-tb4ccp-target-comment="<?php echo esc_attr( $comment_id ); ?>" aria-label="ลบความคิดเห็น" title="ลบ">
                  <?php echo tb4ccp_comment_action_svg( 'delete' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </button>
              <?php endif; ?>
            </div>
          <?php endif; ?>
          <div class="tb4c-comment-text <?php echo '' === $visible_text ? 'is-empty' : ''; ?>" data-tb4c-comment-text data-raw="<?php echo esc_attr( $raw_text ); ?>">
            <?php echo '' !== $visible_text ? wp_kses_post( wpautop( esc_html( $visible_text ) ) ) : ''; ?>
          </div>
          <?php echo $media_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
        <div class="tb4ccp-comment-action-row" data-tb4ccp-action-row aria-label="การทำงานของความคิดเห็น" data-comment-depth="<?php echo esc_attr( $depth ); ?>">
          <a class="tb4ccp-row-action tb4ccp-copy-action" href="<?php echo esc_url( get_comment_link( $comment ) ); ?>" data-tb4ccp-copy-link="<?php echo esc_url( get_comment_link( $comment ) ); ?>" aria-label="คัดลอกลิงก์ความคิดเห็น">
            <?php echo tb4ccp_comment_action_svg( 'copy' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
          </a>
          <?php if ( 0 === $depth ) : ?>
            <button type="button" class="tb4ccp-row-action tb4ccp-reply-action" data-tb4ccp-comment-action="reply" data-tb4ccp-target-comment="<?php echo esc_attr( $comment_id ); ?>" data-comment-author="<?php echo esc_attr( $author_name ); ?>" aria-label="ตอบกลับความคิดเห็นนี้">
              <?php echo tb4ccp_comment_action_svg( 'reply' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </button>
          <?php endif; ?>
        </div>
        <ol class="tb4c-comment-replies" data-tb4c-replies-for="<?php echo esc_attr( $comment_id ); ?>">
          <?php foreach ( $children as $child ) : ?>
            <?php echo tb4ccp_render_comment_item( $child, $tree, $depth + 1 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
          <?php endforeach; ?>
        </ol>
      </div>
    </li>
    <?php
    return trim( ob_get_clean() );
}

function tb4ccp_verify_ajax_nonce() {
    if ( ! check_ajax_referer( 'tb4c_nonce', 'nonce', false ) ) {
        wp_send_json_error( [ 'message' => __( 'เซสชันหมดอายุ กรุณารีเฟรชหน้าอีกครั้ง', 'thinkb4do-community-comment-page' ) ], 403 );
    }
}

function tb4ccp_submit_community_comment() {
    tb4ccp_verify_ajax_nonce();
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => __( 'กรุณาเข้าสู่ระบบก่อนแสดงความคิดเห็น', 'thinkb4do-community-comment-page' ) ], 401 );
    }
    $post_id   = absint( $_POST['post_id'] ?? 0 );
    $parent_id = absint( $_POST['parent_id'] ?? 0 );
    $content   = trim( wp_strip_all_tags( wp_unslash( $_POST['content'] ?? '' ) ) );
    if ( ! $post_id || 'tb4_community_post' !== get_post_type( $post_id ) ) {
        wp_send_json_error( [ 'message' => __( 'ไม่พบโพสต์สำหรับแสดงความคิดเห็น', 'thinkb4do-community-comment-page' ) ], 404 );
    }
    if ( ! comments_open( $post_id ) ) {
        wp_send_json_error( [ 'message' => __( 'โพสต์นี้ปิดรับความคิดเห็นแล้ว', 'thinkb4do-community-comment-page' ) ], 403 );
    }
    if ( '' === $content ) {
        wp_send_json_error( [ 'message' => __( 'กรุณาเขียนความคิดเห็นก่อนส่ง', 'thinkb4do-community-comment-page' ) ], 422 );
    }
    if ( $parent_id ) {
        $parent_comment = tb4ccp_get_comment_object( $parent_id );
        if ( ! $parent_comment || (int) $parent_comment->comment_post_ID !== $post_id ) {
            wp_send_json_error( [ 'message' => __( 'ไม่พบความคิดเห็นที่ต้องการตอบกลับ', 'thinkb4do-community-comment-page' ) ], 422 );
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
    wp_send_json_success( [
        'message'    => __( 'ส่งความคิดเห็นแล้ว', 'thinkb4do-community-comment-page' ),
        'comment_id' => (int) $comment_id,
        'parent_id'  => $parent_id,
        'approved'   => true,
        'html'       => $comment ? tb4ccp_render_comment_item( $comment, [], $parent_id ? 1 : 0 ) : '',
        'count'      => tb4ccp_get_approved_comment_count( $post_id ),
    ] );
}

function tb4ccp_update_community_comment() {
    tb4ccp_verify_ajax_nonce();
    $comment_id = absint( $_POST['comment_id'] ?? 0 );
    $content = trim( wp_strip_all_tags( wp_unslash( $_POST['content'] ?? '' ) ) );
    $comment = tb4ccp_get_comment_object( $comment_id );
    if ( ! $comment ) {
        wp_send_json_error( [ 'message' => __( 'ไม่พบความคิดเห็น', 'thinkb4do-community-comment-page' ) ], 404 );
    }
    if ( ! tb4ccp_user_can_edit_community_comment( $comment ) ) {
        wp_send_json_error( [ 'message' => __( 'คุณไม่มีสิทธิ์แก้ไขความคิดเห็นนี้', 'thinkb4do-community-comment-page' ) ], 403 );
    }
    if ( '' === $content ) {
        wp_send_json_error( [ 'message' => __( 'ความคิดเห็นต้องไม่ว่าง', 'thinkb4do-community-comment-page' ) ], 422 );
    }
    $updated = wp_update_comment( [ 'comment_ID' => $comment_id, 'comment_content' => $content ], true );
    if ( is_wp_error( $updated ) ) {
        wp_send_json_error( [ 'message' => $updated->get_error_message() ], 500 );
    }
    $media = tb4ccp_detect_comment_media( $content );
    $visible_text = tb4ccp_comment_visible_text( $content, $media );
    wp_send_json_success( [
        'message'      => __( 'แก้ไขความคิดเห็นแล้ว', 'thinkb4do-community-comment-page' ),
        'comment_id'   => $comment_id,
        'content'      => $content,
        'content_html' => '' !== $visible_text ? wp_kses_post( wpautop( esc_html( $visible_text ) ) ) : '',
        'media_html'   => tb4ccp_render_comment_media_slot( $media ),
    ] );
}

function tb4ccp_delete_community_comment() {
    tb4ccp_verify_ajax_nonce();
    $comment_id = absint( $_POST['comment_id'] ?? 0 );
    $comment = tb4ccp_get_comment_object( $comment_id );
    if ( ! $comment ) {
        wp_send_json_error( [ 'message' => __( 'ไม่พบความคิดเห็น', 'thinkb4do-community-comment-page' ) ], 404 );
    }
    if ( ! tb4ccp_user_can_delete_community_comment( $comment ) ) {
        wp_send_json_error( [ 'message' => __( 'คุณไม่มีสิทธิ์ลบความคิดเห็นนี้', 'thinkb4do-community-comment-page' ) ], 403 );
    }
    $post_id = (int) $comment->comment_post_ID;
    $deleted = function_exists( 'wp_trash_comment' ) ? wp_trash_comment( $comment_id ) : wp_delete_comment( $comment_id, false );
    if ( ! $deleted ) {
        wp_send_json_error( [ 'message' => __( 'ลบความคิดเห็นไม่สำเร็จ', 'thinkb4do-community-comment-page' ) ], 500 );
    }
    wp_send_json_success( [ 'message' => __( 'ลบความคิดเห็นแล้ว', 'thinkb4do-community-comment-page' ), 'comment_id' => $comment_id, 'count' => tb4ccp_get_approved_comment_count( $post_id ) ] );
}

function tb4ccp_update_community_post() {
    tb4ccp_verify_ajax_nonce();
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => __( 'กรุณาเข้าสู่ระบบก่อนแก้ไขโพสต์', 'thinkb4do-community-comment-page' ) ], 401 );
    }
    $post_id = absint( $_POST['post_id'] ?? 0 );
    if ( ! $post_id || 'tb4_community_post' !== get_post_type( $post_id ) ) {
        wp_send_json_error( [ 'message' => __( 'ไม่พบโพสต์ที่ต้องการแก้ไข', 'thinkb4do-community-comment-page' ) ], 404 );
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        wp_send_json_error( [ 'message' => __( 'คุณไม่มีสิทธิ์แก้ไขโพสต์นี้', 'thinkb4do-community-comment-page' ) ], 403 );
    }
    $title = sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) );
    $content = wp_kses_post( wp_unslash( $_POST['content'] ?? '' ) );
    if ( '' === trim( wp_strip_all_tags( $title . $content ) ) ) {
        wp_send_json_error( [ 'message' => __( 'กรุณาใส่หัวข้อหรือรายละเอียดก่อนบันทึก', 'thinkb4do-community-comment-page' ) ], 422 );
    }
    $updated = wp_update_post( [ 'ID' => $post_id, 'post_title' => $title ?: wp_trim_words( wp_strip_all_tags( $content ), 8, '' ), 'post_content' => $content ], true );
    if ( is_wp_error( $updated ) ) {
        wp_send_json_error( [ 'message' => $updated->get_error_message() ], 500 );
    }
    $post_type = sanitize_key( wp_unslash( $_POST['post_type'] ?? '' ) );
    $visibility = sanitize_key( wp_unslash( $_POST['visibility'] ?? '' ) );
    if ( $post_type ) {
        update_post_meta( $post_id, 'tb4_post_type', $post_type );
    }
    if ( in_array( $visibility, [ 'public', 'groups' ], true ) ) {
        update_post_meta( $post_id, 'tb4_post_visibility', $visibility );
    }
    wp_send_json_success( [
        'message'      => __( 'บันทึกการแก้ไขโพสต์แล้ว', 'thinkb4do-community-comment-page' ),
        'post_id'      => $post_id,
        'status'       => 'updated',
        'url'          => get_permalink( $post_id ),
        'title'        => get_the_title( $post_id ),
        'content'      => wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ),
        'content_html' => wp_kses_post( apply_filters( 'the_content', get_post_field( 'post_content', $post_id ) ) ),
    ] );
}

function tb4ccp_delete_community_post() {
    tb4ccp_verify_ajax_nonce();
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => __( 'กรุณาเข้าสู่ระบบก่อนจัดการโพสต์', 'thinkb4do-community-comment-page' ) ], 401 );
    }
    $post_id = absint( $_POST['post_id'] ?? 0 );
    if ( ! $post_id || 'tb4_community_post' !== get_post_type( $post_id ) ) {
        wp_send_json_error( [ 'message' => __( 'ไม่พบโพสต์', 'thinkb4do-community-comment-page' ) ], 404 );
    }
    if ( ! current_user_can( 'delete_post', $post_id ) ) {
        wp_send_json_error( [ 'message' => __( 'คุณไม่มีสิทธิ์ลบโพสต์นี้', 'thinkb4do-community-comment-page' ) ], 403 );
    }
    $trashed = wp_trash_post( $post_id );
    if ( ! $trashed ) {
        wp_send_json_error( [ 'message' => __( 'ลบโพสต์ไม่สำเร็จ', 'thinkb4do-community-comment-page' ) ], 500 );
    }
    wp_send_json_success( [ 'message' => __( 'ย้ายโพสต์ไปถังขยะแล้ว', 'thinkb4do-community-comment-page' ), 'url' => home_url( '/community/' ) ] );
}

function tb4ccp_register_ajax_handlers() {
    add_action( 'wp_ajax_tb4c_submit_comment', 'tb4ccp_submit_community_comment', 1 );
    add_action( 'wp_ajax_tb4c_update_comment', 'tb4ccp_update_community_comment', 1 );
    add_action( 'wp_ajax_tb4c_delete_comment', 'tb4ccp_delete_community_comment', 1 );
    add_action( 'wp_ajax_tb4c_update_community_post', 'tb4ccp_update_community_post', 1 );
    add_action( 'wp_ajax_tb4c_delete_post', 'tb4ccp_delete_community_post', 1 );
}
add_action( 'init', 'tb4ccp_register_ajax_handlers', 100 );

function tb4ccp_admin_notice_dependency() {
    if ( ! current_user_can( 'activate_plugins' ) || post_type_exists( 'tb4_community_post' ) ) {
        return;
    }
    echo '<div class="notice notice-warning"><p><strong>Thinkb4do Community Comment Page:</strong> ต้องใช้ร่วมกับปลั๊กอิน Feed Main หรือปลั๊กอินที่ลงทะเบียนโพสต์ชนิด <code>tb4_community_post</code></p></div>';
}
add_action( 'admin_notices', 'tb4ccp_admin_notice_dependency' );
