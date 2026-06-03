<?php
/**
 * Template: Member Area
 *
 * @package Thinkb4doCommunity
 */
defined( 'ABSPATH' ) || exit;

$area_url = tb4cm_get_member_area_url();

if ( ! is_user_logged_in() ) :
?>
<section class="tb4c-shell tb4c-member-area-page tb4c-member-area-guest tb4c-modern-refresh-fit-page tb4c-member-area-v86">
  <div class="tb4c-container tb4c-member-area-guest-card">
    <div class="tb4c-member-area-guest-icon"><?php echo tb4cm_icon_markup( 'ph-user-circle-gear', '◎' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
    <h1>พื้นที่สมาชิก Thinkb4do</h1>
    <p>เข้าสู่ระบบเพื่อดูโพสต์ของคุณ ความคิดเห็น สิ่งที่บันทึกไว้ คนที่ติดตาม และแก้ไขโปรไฟล์สมาชิกได้ในหน้าเดียว</p>
    <div class="tb4c-member-area-guest-actions">
      <a class="tb4c-btn tb4c-btn-primary" href="<?php echo esc_url( wp_login_url( $area_url ) ); ?>">เข้าสู่ระบบ</a>
      <a class="tb4c-btn tb4c-btn-secondary" href="<?php echo esc_url( wp_registration_url() ); ?>">สมัครสมาชิก</a>
    </div>
  </div>
</section>
<?php
return;
endif;

$user              = wp_get_current_user();
$user_id           = get_current_user_id();
$avatar            = function_exists( 'tb4cm_get_user_avatar_url' ) ? tb4cm_get_user_avatar_url( $user_id, [ 'size' => 256 ] ) : get_avatar_url( $user_id, [ 'size' => 256 ] );
$member_page_id    = 0;
$member_cover_id   = (int) get_user_meta( $user_id, 'tb4c_member_cover_id', true );
$member_cover_url  = function_exists( 'tb4cm_get_member_cover_url' ) ? tb4cm_get_member_cover_url( $user_id, true ) : ( $member_cover_id ? wp_get_attachment_image_url( $member_cover_id, 'large' ) : '' );
$member_cover_style = $member_cover_url ? 'style="--tb4c-member-cover-image:url(' . esc_url( $member_cover_url ) . ');"' : '';
$member_asset_folder = function_exists( 'tb4cm_get_user_asset_folder_label' ) ? tb4cm_get_user_asset_folder_label( $user_id ) : '';
$member_storage_usage = function_exists( 'tb4cm_get_user_storage_usage' ) ? tb4cm_get_user_storage_usage( $user_id ) : [];
$member_storage_package = function_exists( 'tb4cm_get_user_storage_package' ) ? tb4cm_get_user_storage_package( $user_id ) : [];
$member_storage_plans   = function_exists( 'tb4cm_get_storage_package_plans' ) ? tb4cm_get_storage_package_plans() : [];
$member_storage_data    = function_exists( 'tb4cm_get_user_storage_dashboard_data' ) ? tb4cm_get_user_storage_dashboard_data( $user_id ) : [];
$member_avatar_id    = (int) get_user_meta( $user_id, 'tb4c_member_avatar_id', true );
$profile_url       = tb4cm_get_member_area_url();
$contact_url       = get_user_meta( $user_id, 'tb4c_member_contact_url', true );
$member_title      = get_user_meta( $user_id, 'tb4c_member_title', true ) ?: $user->display_name;
$member_content    = get_user_meta( $user_id, 'tb4c_member_content', true ) ?: get_user_meta( $user_id, 'description', true );
$member_role       = get_user_meta( $user_id, 'tb4c_member_role', true );
$member_location   = get_user_meta( $user_id, 'tb4c_member_location', true );
$member_interests  = get_user_meta( $user_id, 'tb4c_member_interests', true );
$following_ids     = tb4cm_get_following_user_ids( $user_id );
$bookmarked_ids    = tb4cm_get_user_bookmarked_post_ids( $user_id );
$profile_complete  = tb4cm_get_member_profile_completeness( $user_id, $member_page_id );
$member_display_name = $user->display_name ? $user->display_name : $user->user_login;
$member_bio_short    = trim( wp_strip_all_tags( $member_content ? $member_content : get_user_meta( $user_id, 'description', true ) ) );
$member_bio_short    = $member_bio_short ? wp_trim_words( $member_bio_short, 20 ) : 'พื้นที่ส่วนตัวสำหรับจัดการโปรไฟล์ โพสต์ การติดตาม และบทสนทนาของคุณใน Thinkb4do';
$member_since        = mysql2date( 'M Y', $user->user_registered );
$member_handle       = $user->user_login ? '@' . sanitize_user( $user->user_login, true ) : '@member';
$profile_score       = min( 100, max( 0, (int) ( $profile_complete['score'] ?? 0 ) ) );
$member_extra_tab_items = function_exists( 'tb4cm_get_member_area_extra_tabs' ) ? tb4cm_get_member_area_extra_tabs( $user_id ) : [];
$tab               = isset( $_GET['tb4c_area'] ) ? sanitize_key( wp_unslash( $_GET['tb4c_area'] ) ) : 'overview';
$allowed_tabs      = array_values( array_unique( array_merge( [ 'overview', 'posts', 'comments', 'saved', 'following', 'groups', 'market', 'chat', 'products', 'usage', 'settings' ], array_keys( $member_extra_tab_items ) ) ) );
if ( ! in_array( $tab, $allowed_tabs, true ) ) {
    $tab = 'overview';
}
$post_status_filter = isset( $_GET['tb4c_post_status'] ) ? sanitize_key( wp_unslash( $_GET['tb4c_post_status'] ) ) : 'all';
$allowed_post_status_filters = [ 'all', 'publish', 'pending', 'draft', 'private' ];
if ( ! in_array( $post_status_filter, $allowed_post_status_filters, true ) ) {
    $post_status_filter = 'all';
}
$my_post_statuses = 'all' === $post_status_filter ? [ 'publish', 'pending', 'draft', 'private' ] : [ $post_status_filter ];
$posts_page       = max( 1, absint( $_GET['tb4c_posts_page'] ?? 1 ) );
$posts_per_page   = 'posts' === $tab ? 18 : 6;

$my_posts_query = new WP_Query( [
    'post_type'              => 'tb4_community_post',
    'post_status'            => $my_post_statuses,
    'author'                 => $user_id,
    'posts_per_page'         => $posts_per_page,
    'paged'                  => 'posts' === $tab ? $posts_page : 1,
    'orderby'                => 'date',
    'order'                  => 'DESC',
    'no_found_rows'          => ! in_array( $tab, [ 'overview', 'posts' ], true ),
    'update_post_meta_cache' => true,
    'update_post_term_cache' => true,
] );
$published_count = tb4cm_get_user_community_post_count( $user_id );
$all_my_posts = new WP_Query( [
    'post_type'      => 'tb4_community_post',
    'post_status'    => [ 'publish', 'pending', 'draft', 'private' ],
    'author'         => $user_id,
    'fields'         => 'ids',
    'posts_per_page' => 1,
    'no_found_rows'  => false,
] );
$total_my_posts  = (int) $all_my_posts->found_posts;
$comment_count   = tb4cm_get_user_comment_count( $user_id );
$follower_count  = tb4cm_get_user_follower_count( $user_id );
$total_views     = tb4cm_get_user_total_post_views( $user_id );
$member_focus_score  = min( 100, max( 8, (int) round( ( $profile_complete['score'] * 0.42 ) + min( $total_my_posts, 12 ) * 3 + min( $comment_count, 24 ) * 1.15 + min( count( $following_ids ), 24 ) * 1.05 ) ) );
$member_level_label  = $member_focus_score >= 85 ? 'Power Member' : ( $member_focus_score >= 60 ? 'Active Member' : 'Starter Member' );
$member_next_step    = $profile_complete['score'] < 80 ? 'เติมโปรไฟล์ให้ครบขึ้น เพื่อให้คนรู้จักคุณง่ายขึ้น' : ( $total_my_posts < 1 ? 'เริ่มโพสต์แรกเพื่อเปิดพื้นที่ของคุณ' : 'ต่อยอดโพสต์ที่น่าสนใจและเชื่อมกับสมาชิกใหม่' );
$profile_missing_items = [];
if ( ! empty( $profile_complete['items'] ) && is_array( $profile_complete['items'] ) ) {
    foreach ( $profile_complete['items'] as $profile_item ) {
        if ( empty( $profile_item['done'] ) && ! empty( $profile_item['label'] ) ) {
            $profile_missing_items[] = $profile_item['label'];
        }
        if ( count( $profile_missing_items ) >= 3 ) {
            break;
        }
    }
}
$my_comments     = get_comments( [
    'user_id'    => $user_id,
    'status'     => 'approve',
    'type'       => 'comment',
    'post_type'  => 'tb4_community_post',
    'number'     => 'comments' === $tab ? 10 : 3,
    'orderby'    => 'comment_date_gmt',
    'order'      => 'DESC',
] );

if ( ! function_exists( 'tb4cm_member_area_is_other_comment_author' ) ) {
function tb4cm_member_area_is_other_comment_author( $comment, $current_user_id, $current_user ) {
    if ( ! $comment instanceof WP_Comment ) {
        return false;
    }

    $reply_user_id = (int) $comment->user_id;
    if ( $reply_user_id > 0 && $reply_user_id === (int) $current_user_id ) {
        return false;
    }

    $current_email = $current_user instanceof WP_User ? strtolower( trim( (string) $current_user->user_email ) ) : '';
    $comment_email = strtolower( trim( (string) $comment->comment_author_email ) );
    if ( $current_email && $comment_email && $current_email === $comment_email ) {
        return false;
    }

    return true;
}
}

if ( ! function_exists( 'tb4cm_member_area_get_reply_notice' ) ) {
function tb4cm_member_area_get_reply_notice( $comment, $current_user_id, $current_user ) {
    if ( ! $comment instanceof WP_Comment ) {
        return [ 'has_reply' => false, 'count' => 0, 'latest' => null, 'is_direct' => false ];
    }

    $cache_key = 'tb4c_reply_notice_' . absint( $current_user_id ) . '_' . absint( $comment->comment_ID );
    $cached    = wp_cache_get( $cache_key, 'thinkb4do-community' );
    if ( false !== $cached ) {
        return $cached;
    }

    $direct_replies = get_comments( [
        'parent'     => (int) $comment->comment_ID,
        'status'     => 'approve',
        'type'       => 'comment',
        'number'     => 8,
        'orderby'    => 'comment_date_gmt',
        'order'      => 'DESC',
    ] );

    $filtered_direct = [];
    foreach ( $direct_replies as $reply ) {
        if ( tb4cm_member_area_is_other_comment_author( $reply, $current_user_id, $current_user ) ) {
            $filtered_direct[] = $reply;
        }
    }

    if ( ! empty( $filtered_direct ) ) {
        $result = [
            'has_reply' => true,
            'count'     => count( $filtered_direct ),
            'latest'    => $filtered_direct[0],
            'is_direct' => true,
        ];
        wp_cache_set( $cache_key, $result, 'thinkb4do-community', 300 );
        return $result;
    }

    $later_conversation_comments = get_comments( [
        'post_id'    => (int) $comment->comment_post_ID,
        'status'     => 'approve',
        'type'       => 'comment',
        'number'     => 8,
        'orderby'    => 'comment_date_gmt',
        'order'      => 'DESC',
        'date_query' => [
            [
                'column'    => 'comment_date_gmt',
                'after'     => $comment->comment_date_gmt,
                'inclusive' => false,
            ],
        ],
    ] );

    $filtered_later = [];
    foreach ( $later_conversation_comments as $later_comment ) {
        if ( (int) $later_comment->comment_ID === (int) $comment->comment_ID ) {
            continue;
        }
        if ( tb4cm_member_area_is_other_comment_author( $later_comment, $current_user_id, $current_user ) ) {
            $filtered_later[] = $later_comment;
        }
    }

    if ( ! empty( $filtered_later ) ) {
        $result = [
            'has_reply' => true,
            'count'     => count( $filtered_later ),
            'latest'    => $filtered_later[0],
            'is_direct' => false,
        ];
        wp_cache_set( $cache_key, $result, 'thinkb4do-community', 300 );
        return $result;
    }

    $result = [ 'has_reply' => false, 'count' => 0, 'latest' => null, 'is_direct' => false ];
    wp_cache_set( $cache_key, $result, 'thinkb4do-community', 300 );
    return $result;
}
}

$sidebar_conversation_comments = array_slice( $my_comments, 0, 3 );
$conversation_reply_notices    = [];
$conversation_reply_alerts     = 0;
foreach ( $sidebar_conversation_comments as $sidebar_comment ) {
    $reply_notice = tb4cm_member_area_get_reply_notice( $sidebar_comment, $user_id, $user );
    $conversation_reply_notices[ (int) $sidebar_comment->comment_ID ] = $reply_notice;
    if ( ! empty( $reply_notice['has_reply'] ) ) {
        $conversation_reply_alerts++;
    }
}
$saved_posts = [];
if ( ! empty( $bookmarked_ids ) ) {
    $saved_posts = get_posts( [
        'post_type'      => 'tb4_community_post',
        'post_status'    => 'publish',
        'post__in'       => $bookmarked_ids,
        'orderby'        => 'post__in',
        'posts_per_page' => 'saved' === $tab ? 12 : 4,
    ] );
}

$tab_items = array_merge( [
    'overview'  => [ 'label' => 'หน้าหลัก', 'desc' => 'โปรไฟล์', 'icon' => 'ph-house', 'fallback' => '⌂' ],
    'posts'     => [ 'label' => 'โพสต์', 'desc' => 'ฟีด', 'icon' => 'ph-note-pencil', 'fallback' => '✎' ],
    'comments'  => [ 'label' => 'คอมเมนต์', 'desc' => 'คุย', 'icon' => 'ph-chat-circle-text', 'fallback' => '💬' ],
    'saved'     => [ 'label' => 'บันทึก', 'desc' => 'เก็บไว้', 'icon' => 'ph-bookmark-simple', 'fallback' => '★' ],
    'following' => [ 'label' => 'ติดตาม', 'desc' => 'เครือข่าย', 'icon' => 'ph-users-three', 'fallback' => '👥' ],
    'groups'    => [ 'label' => 'กลุ่ม', 'desc' => 'ชุมชนย่อย', 'icon' => 'ph-users-four', 'fallback' => '◎' ],
    'market'    => [ 'label' => 'ตลาด', 'desc' => 'ซื้อขาย', 'icon' => 'ph-storefront', 'fallback' => '▤' ],
    'chat'      => [ 'label' => 'แชท', 'desc' => 'ข้อความ', 'icon' => 'ph-chats-circle', 'fallback' => '◉' ],
    'products'  => [ 'label' => 'สินค้า', 'desc' => 'สินค้า', 'icon' => 'ph-shopping-bag-open', 'fallback' => '▣' ],
    'usage'     => [ 'label' => 'พื้นที่', 'desc' => 'ใช้/แพ็กเกจ', 'icon' => 'ph-hard-drives', 'fallback' => '▥' ],
    'settings'  => [ 'label' => 'ตั้งค่า', 'desc' => 'โปรไฟล์', 'icon' => 'ph-gear-six', 'fallback' => '⚙' ],
], $member_extra_tab_items );

foreach ( $tab_items as $feature_key => $feature_item ) {
    $feature_key = sanitize_key( $feature_key );
    $is_unlocked = function_exists( 'tb4cm_member_area_feature_is_unlocked' ) ? tb4cm_member_area_feature_is_unlocked( $feature_key, $user_id ) : true;
    $tab_items[ $feature_key ]['locked']       = ! $is_unlocked;
    $tab_items[ $feature_key ]['purchase_url'] = function_exists( 'tb4cm_member_area_feature_purchase_url' ) ? tb4cm_member_area_feature_purchase_url( $feature_key, $user_id ) : home_url( '/products/' );
}

if ( ! function_exists( 'tb4cm_member_area_post_card' ) ) {
function tb4cm_member_area_post_card( $post_id ) {
    $status = get_post_status( $post_id );
    $likes  = absint( get_post_meta( $post_id, 'tb4_like_count', true ) );
    $views  = absint( get_post_meta( $post_id, 'tb4_view_count', true ) );
    $comments = get_comments_number( $post_id );
    $post_raw_text = get_post_field( 'post_content', $post_id );
    $media_album = function_exists( 'tb4cm_get_post_media_album' ) ? tb4cm_get_post_media_album( $post_id, $post_raw_text ) : [];
    $preview_media = ! empty( $media_album[0] ) ? $media_album[0] : null;
    $preview_url = get_the_post_thumbnail_url( $post_id, 'medium_large' );
    if ( ! $preview_url && is_array( $preview_media ) && ! empty( $preview_media['url'] ) ) {
        $preview_url = 'video' === ( $preview_media['type'] ?? '' ) && function_exists( 'tb4cm_get_video_poster_url' ) ? tb4cm_get_video_poster_url( $preview_media ) : $preview_media['url'];
    }
    ?>
    <article class="tb4c-member-area-list-card" data-post-id="<?php echo esc_attr( $post_id ); ?>">
      <?php if ( $preview_url ) : ?>
        <a class="tb4c-member-area-post-thumb" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" aria-label="ดูโพสต์">
          <img src="<?php echo esc_url( $preview_url ); ?>" alt="" loading="lazy" decoding="async">
          <?php if ( is_array( $preview_media ) && 'video' === ( $preview_media['type'] ?? '' ) ) : ?><span><?php echo tb4cm_icon_markup( 'ph-play-fill', '▶' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><?php endif; ?>
        </a>
      <?php endif; ?>
      <div>
        <div class="tb4c-member-area-list-meta">
          <span class="tb4c-status-pill is-<?php echo esc_attr( $status ); ?>"><?php echo esc_html( tb4cm_status_label( $status ) ); ?></span>
          <time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $post_id ) ); ?>"><?php echo esc_html( get_the_date( 'j M Y', $post_id ) ); ?></time>
        </div>
        <h3><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a></h3>
        <p><?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ), 26 ) ); ?></p>
        <div class="tb4c-member-area-mini-stats">
          <span><?php echo tb4cm_icon_markup( 'ph-heart', '♡' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( number_format_i18n( $likes ) ); ?></span>
          <span><?php echo tb4cm_icon_markup( 'ph-chat-circle', '💬' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( number_format_i18n( $comments ) ); ?></span>
          <span><?php echo tb4cm_icon_markup( 'ph-eye', '👁' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( number_format_i18n( $views ) ); ?></span>
        </div>
      </div>
      <div class="tb4c-member-area-card-actions">
        <a class="tb4c-btn tb4c-btn-secondary is-small" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">ดูโพสต์</a>
        <?php if ( current_user_can( 'edit_post', $post_id ) ) : ?>
          <a class="tb4c-btn tb4c-btn-secondary is-small" href="<?php echo esc_url( get_edit_post_link( $post_id ) ); ?>">แก้ไข</a>
        <?php endif; ?>
        <?php if ( current_user_can( 'delete_post', $post_id ) ) : ?>
          <button class="tb4c-btn tb4c-btn-ghost is-small" type="button" data-tb4c-delete-post="<?php echo esc_attr( $post_id ); ?>">ลบ</button>
        <?php endif; ?>
      </div>
    </article>
    <?php

}
}

if ( ! function_exists( 'tb4cm_member_area_feed_post_card' ) ) {
function tb4cm_member_area_feed_post_card( $post_id, $current_avatar = '' ) {
    $author_id   = (int) get_post_field( 'post_author', $post_id );
    $author_name = get_the_author_meta( 'display_name', $author_id ) ?: get_the_author_meta( 'user_login', $author_id );
    $avatar      = function_exists( 'tb4cm_get_user_avatar_url' ) ? tb4cm_get_user_avatar_url( $author_id, [ 'size' => 96 ] ) : get_avatar_url( $author_id, [ 'size' => 96 ] );
    $type        = get_post_meta( $post_id, 'tb4_post_type', true ) ?: 'discussion';
    $visibility  = get_post_meta( $post_id, 'tb4_post_visibility', true ) ?: 'public';
    $likes       = (int) get_post_meta( $post_id, 'tb4_like_count', true );
    $bookmarks   = (int) get_post_meta( $post_id, 'tb4_bookmark_count', true );
    $views       = (int) get_post_meta( $post_id, 'tb4_view_count', true );
    $status      = get_post_status( $post_id );
    $terms       = get_the_terms( $post_id, 'tb4_community_topic' );
    $tag_terms   = get_the_terms( $post_id, 'tb4_community_tag' );
    $post_text   = wp_strip_all_tags( get_post_field( 'post_content', $post_id ) );
    $post_title_for_compare = trim( wp_strip_all_tags( get_the_title( $post_id ) ) );
    $post_text_for_compare  = trim( wp_strip_all_tags( $post_text ) );
    if ( $post_text_for_compare && $post_title_for_compare && $post_text_for_compare === $post_title_for_compare ) {
        $post_text = '';
    }
    $media_album = function_exists( 'tb4cm_get_post_media_album' ) ? tb4cm_get_post_media_album( $post_id, $post_text ) : [];

    if ( empty( $media_album ) && has_post_thumbnail( $post_id ) ) {
        $media_album[] = [
            'type' => 'image',
            'url'  => get_the_post_thumbnail_url( $post_id, 'large' ),
        ];
    }

    $post_media_album_urls = ! empty( $media_album ) ? implode( "\n", wp_list_pluck( $media_album, 'url' ) ) : '';
    $first_media           = ! empty( $media_album[0] ) && is_array( $media_album[0] ) ? $media_album[0] : [];
    $first_media_url       = ! empty( $first_media['url'] ) ? $first_media['url'] : '';
    $post_feeling          = get_post_meta( $post_id, 'tb4_post_feeling', true );
    $tag_input_value       = function_exists( 'tb4cm_get_post_tag_input_value' ) ? tb4cm_get_post_tag_input_value( $post_id ) : '';
    $post_poll_question    = get_post_meta( $post_id, 'tb4_poll_question', true );
    $post_poll_options     = get_post_meta( $post_id, 'tb4_poll_options', true );
    $post_poll_options_value = is_array( $post_poll_options ) ? implode( "\n", $post_poll_options ) : '';
    ?>
    <article class="tb4c-post-card tb4c-social-post-card tb4c-ig-post tb4c-native-post tb4c-member-own-feed-card <?php echo ! empty( $media_album ) ? 'tb4c-has-media' : 'tb4c-text-only-post'; ?>" data-post-id="<?php echo esc_attr( $post_id ); ?>">
      <div class="tb4c-post-head">
        <a class="tb4c-avatar" href="<?php echo esc_url( tb4cm_social_profile_url( $author_id ) ); ?>">
          <?php if ( $avatar ) : ?><img src="<?php echo esc_url( $avatar ); ?>" alt=""><?php else : ?><?php echo esc_html( tb4cm_get_initial( $author_name ) ); ?><?php endif; ?>
        </a>
        <div class="tb4c-post-meta">
          <div class="tb4c-author-line">
            <div class="tb4c-author-row">
              <a href="<?php echo esc_url( tb4cm_social_profile_url( $author_id ) ); ?>"><strong><?php echo esc_html( $author_name ); ?></strong></a>
              <span class="tb4c-dot">•</span>
              <span><?php echo esc_html( human_time_diff( get_post_time( 'U', true, $post_id ), current_time( 'timestamp' ) ) ); ?>ที่แล้ว</span>
              <span class="tb4c-status-pill is-<?php echo esc_attr( $status ); ?>"><?php echo esc_html( tb4cm_status_label( $status ) ); ?></span>
            </div>
            <div class="tb4c-feed-owner-tools tb4c-feed-owner-tools-inline" aria-label="จัดการโพสต์ของฉัน">
              <?php if ( current_user_can( 'edit_post', $post_id ) ) : ?>
                <button type="button" data-tb4c-edit-post="<?php echo esc_attr( $post_id ); ?>" data-post-title="<?php echo esc_attr( get_the_title( $post_id ) ); ?>" data-post-content="<?php echo esc_attr( $post_text ); ?>" data-post-type="<?php echo esc_attr( $type ); ?>" data-post-visibility="<?php echo esc_attr( $visibility ); ?>" data-post-media-url="<?php echo esc_attr( $first_media_url ); ?>" data-post-media-album="<?php echo esc_attr( $post_media_album_urls ); ?>" data-post-tags="<?php echo esc_attr( $tag_input_value ); ?>" data-post-feeling="<?php echo esc_attr( $post_feeling ); ?>" data-post-poll-question="<?php echo esc_attr( $post_poll_question ); ?>" data-post-poll-options="<?php echo esc_attr( $post_poll_options_value ); ?>"><?php echo tb4cm_icon_markup( 'ph-pencil-simple-line', '✎' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span>แก้ไข</span></button>
              <?php endif; ?>
              <?php if ( current_user_can( 'delete_post', $post_id ) ) : ?>
                <button type="button" data-tb4c-delete-post="<?php echo esc_attr( $post_id ); ?>"><?php echo tb4cm_icon_markup( 'ph-trash', '×' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span>ลบ</span></button>
              <?php endif; ?>
            </div>
          </div>
          <div class="tb4c-author-audience-row tb4c-member-audience-row" aria-label="การมองเห็นโพสต์">
            <?php echo tb4cm_icon_markup( function_exists( 'tb4cm_visibility_icon_class' ) ? tb4cm_visibility_icon_class( $visibility ) : ( 'members' === $visibility ? 'ph-lock-key' : 'ph-globe-hemisphere-east' ), 'groups' === $visibility ? '👥' : ( 'members' === $visibility ? '🔒' : '🌐' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <span><?php echo esc_html( function_exists( 'tb4cm_visibility_label' ) ? tb4cm_visibility_label( $visibility ) : ( 'members' === $visibility ? 'เฉพาะสมาชิก' : ( 'groups' === $visibility ? 'เฉพาะกลุ่ม' : 'สาธารณะ' ) ) ); ?></span>
            <?php if ( ! empty( $post_feeling ) ) : ?><span class="tb4c-audience-feeling"><?php echo esc_html( $post_feeling ); ?></span><?php endif; ?>
          </div>
        </div>
      </div>

      <a class="tb4c-post-inline-text" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
        <h2><?php echo esc_html( get_the_title( $post_id ) ); ?></h2>
        <?php if ( $post_text ) : ?><p><?php echo esc_html( wp_trim_words( $post_text, 36 ) ); ?></p><?php endif; ?>
      </a>

      <?php echo function_exists( 'tb4cm_render_post_type_tag_strip' ) ? tb4cm_render_post_type_tag_strip( $post_id, $type, 6 ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

      <?php if ( has_post_thumbnail( $post_id ) ) : ?>
        <button type="button" class="tb4c-thumb tb4c-ig-media tb4c-post-media-slot tb4c-post-image-slot tb4c-featured-lightbox-trigger" draggable="false" data-tb4c-lightbox data-tb4c-lightbox-type="image" data-tb4c-lightbox-src="<?php echo esc_url( get_the_post_thumbnail_url( $post_id, 'large' ) ); ?>" data-tb4c-lightbox-title="<?php echo esc_attr( get_the_title( $post_id ) ); ?>"><?php echo get_the_post_thumbnail( $post_id, 'large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
      <?php elseif ( ! empty( $media_album ) && function_exists( 'tb4cm_render_post_media_album' ) ) : ?>
        <?php echo tb4cm_render_post_media_album( $media_album, get_the_title( $post_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
      <?php endif; ?>

      <?php echo function_exists( 'tb4cm_render_post_poll' ) ? tb4cm_render_post_poll( $post_id ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

      <div class="tb4c-post-actions tb4c-social-actions tb4c-ig-actions tb4c-ig-action-row">
        <button type="button" data-tb4c-react="like" data-post-id="<?php echo esc_attr( $post_id ); ?>" aria-label="ถูกใจ"><i class="ph ph-heart"></i> <span><?php echo esc_html( $likes ); ?></span><em>ถูกใจ</em></button>
        <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>#comments" aria-label="ความคิดเห็น"><i class="ph ph-chat-circle"></i> <?php echo esc_html( get_comments_number( $post_id ) ); ?><em>ความคิดเห็น</em></a>
        <button type="button" data-tb4c-copy-link="<?php echo esc_url( get_permalink( $post_id ) ); ?>" aria-label="แชร์"><i class="ph ph-paper-plane-tilt"></i><em>แชร์</em></button>
        <?php if ( ! empty( $media_album ) ) : ?><button type="button" class="tb4c-reel-action-btn" data-tb4c-open-reels data-tb4c-reel-post-id="<?php echo esc_attr( $post_id ); ?>" aria-label="เปิดดูแบบรีล"><i class="ph ph-film-strip"></i><em>Reel</em></button><?php endif; ?>
        <button type="button" data-tb4c-react="bookmark" data-post-id="<?php echo esc_attr( $post_id ); ?>" aria-label="บันทึก"><i class="ph ph-bookmark-simple"></i> <span><?php echo esc_html( $bookmarks ); ?></span><em>บันทึก</em></button>
        <span class="tb4c-view-chip tb4c-action-view-chip" aria-label="จำนวนการเข้าชม"><i class="ph ph-eye"></i> <?php echo esc_html( number_format_i18n( max( 0, $views ) ) ); ?></span>
      </div>

      <div class="tb4c-reaction-summary tb4c-ig-like-line tb4c-legacy-post-summary" aria-label="สรุปการตอบสนอง" hidden>
        <span><?php echo esc_html( number_format_i18n( max( 0, $likes ) ) ); ?> คนถูกใจ</span>
        <span><?php echo esc_html( number_format_i18n( max( 0, $views ) ) ); ?> การเข้าชม</span>
      </div>
      <div class="tb4c-native-reply-row tb4c-comment-preview-row">
        <a class="tb4c-comment-composer-preview tb4c-ig-comment-line" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>#comments">
          <span class="tb4c-comment-preview-avatar"><?php if ( $current_avatar ) : ?><img src="<?php echo esc_url( $current_avatar ); ?>" alt=""><?php else : ?>💬<?php endif; ?></span>
          <span class="tb4c-comment-preview-copy">แสดงความคิดเห็น หรือเช็กต่อในโพสต์นี้...</span>
        </a>
        <a class="tb4c-native-build-link" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>#comments"><?php echo tb4cm_icon_markup( 'ph-arrow-bend-up-right', '↗' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( get_comments_number( $post_id ) ); ?></span></a>
      </div>
    </article>
    <?php
}
}
?>

<?php
if ( ! function_exists( 'tb4cm_member_quick_composer_card' ) ) {
function tb4cm_member_quick_composer_card( $current_avatar = '', $display_name = '' ) {
    $display_name = $display_name ? $display_name : __( 'สมาชิก', 'thinkb4do-community' );
    ?>
    <section class="tb4c-member-fb-composer-card" aria-label="สร้างโพสต์ใหม่">
      <button type="button" class="tb4c-member-fb-composer-input" data-tb4c-open-composer aria-label="เปิดกล่องสร้างโพสต์">
        <span class="tb4c-member-fb-composer-avatar"><?php if ( $current_avatar ) : ?><img src="<?php echo esc_url( $current_avatar ); ?>" alt=""><?php else : ?><?php echo esc_html( tb4cm_get_initial( $display_name ) ); ?><?php endif; ?></span>
        <span class="tb4c-member-fb-composer-placeholder">คุณกำลังคิดอะไรอยู่...</span>
      </button>
      <div class="tb4c-member-fb-composer-actions" aria-label="ทางลัดสร้างโพสต์">
        <button type="button" data-tb4c-open-composer><?php echo tb4cm_icon_markup( 'ph-video-camera', '●' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span>วิดีโอถ่ายทอดสด</span></button>
        <button type="button" data-tb4c-open-composer><?php echo tb4cm_icon_markup( 'ph-image-square', '▧' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span>รูปภาพ/วิดีโอ</span></button>
        <button type="button" data-tb4c-open-composer><?php echo tb4cm_icon_markup( 'ph-film-strip', '▰' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span>คลิป Reels</span></button>
      </div>
    </section>
    <?php
}
}
?>

<section class="tb4c-shell tb4c-member-area-page tb4c-member-area-v65 tb4c-member-area-v66 tb4c-member-area-v68 tb4c-member-area-v69 tb4c-member-area-v70 tb4c-member-area-v71 tb4c-member-area-v72 tb4c-member-area-v75 tb4c-member-area-v76 tb4c-member-area-v78 tb4c-member-area-v79 tb4c-member-area-v80 tb4c-member-area-v81 tb4c-member-area-v82 tb4c-member-area-v83 tb4c-member-area-v84 tb4c-member-area-v86 tb4c-member-clean-workbench tb4c-modern-refresh-fit-page tb4c-member-essential-clean-page tb4c-member-ideal-page tb4c-member-workspace-v4235 tb4c-member-profile-preview-v4236 tb4c-member-curated-feed-v4237 tb4c-member-responsive-standard-page tb4c-member-thinkb4do-space-v4245 tb4c-member-clean-focus-v4247 tb4c-member-clean-canvas-v4249 tb4c-member-feed-mainlike-v4250 tb4c-member-fb-split-v4251 tb4c-member-mainfeed-card-v4252 tb4c-member-dedup-v4253 tb4c-member-beauty-v4254 tb4c-member-identity-v4255 tb4c-member-identity-v4256 tb4c-member-clean-menu-v4257 tb4c-member-world-clean-v4258 tb4c-member-facebook-style-v4259 tb4c-member-profile-menu-v4260 tb4c-member-clear-member-menu-v4261 tb4c-member-simple-menu-v4262 tb4c-member-standard-profile-v4264 tb4c-member-standard-profile-v4265 tb4c-member-standard-profile-v4266 tb4c-member-facebook-order-v4277 tb4c-member-post-layout-fix-v4280 tb4c-member-facebook-clean-light-v4281 tb4c-member-feed-main-post-menu-v4282 tb4c-member-feed-main-width-touch-v4283 tb4c-member-tab-<?php echo esc_attr( $tab ); ?>">
  <div class="tb4c-container tb4c-member-area-container">
    <div class="tb4c-ai-member-hub-grid tb4c-ai-member-hub-grid-clean-canvas" data-tb4c-ai-member-hub="1">
      <div class="tb4c-ai-member-center">
    <div class="tb4c-reference-page-head tb4c-member-reference-head" data-tb4c-smart-back-target aria-label="หัวข้อพื้นที่สมาชิก">
      <div class="tb4c-reference-title">
        <h1>สมาชิก Thinkb4do</h1>
        <p>พื้นที่สมาชิกส่วนตัวสำหรับโปรไฟล์ โพสต์ บันทึก และการติดตาม</p>
      </div>
    </div>

    <header class="tb4c-member-area-hero tb4c-member-area-hero-v65 tb4c-member-area-hero-v78 tb4c-member-showcase-hero">
      <div class="tb4c-member-area-hero-bg" <?php echo $member_cover_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> aria-hidden="true"><span></span><span></span></div>
      <a class="tb4c-member-cover-edit-badge" href="<?php echo esc_url( add_query_arg( [ 'tb4c_area' => 'settings' ], $area_url ) ); ?>#tb4cMemberCoverField" aria-label="เปลี่ยนภาพ Cover"><?php echo tb4cm_icon_markup( 'ph-image-square', '▧' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span>เปลี่ยน Cover</span></a>
      <div class="tb4c-member-area-avatar tb4c-member-area-avatar-v65 tb4c-member-area-avatar-v78" data-tb4c-member-avatar-preview>
        <?php if ( $avatar ) : ?><img src="<?php echo esc_url( $avatar ); ?>" alt="" data-tb4c-member-avatar-img><?php else : ?><?php echo esc_html( tb4cm_get_initial( $member_display_name ) ); ?><?php endif; ?>
        <a class="tb4c-member-area-avatar-sync-badge tb4c-member-camera-badge" href="<?php echo esc_url( add_query_arg( [ 'tb4c_area' => 'settings' ], $area_url ) ); ?>#tb4cMemberAvatarField" aria-label="เปลี่ยนรูปโปรไฟล์"><?php echo tb4cm_icon_markup( 'ph-camera', '▣' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
      </div>
      <div class="tb4c-member-area-title tb4c-member-area-title-v65">
        <div class="tb4c-member-title-line">
          <h1><?php echo esc_html( $member_display_name ); ?></h1>
          <span class="tb4c-member-verified" aria-label="สมาชิก Thinkb4do">✓</span>
        </div>
        <p class="tb4c-member-area-bio-line"><?php echo esc_html( $member_bio_short ); ?></p>
        <div class="tb4c-member-standard-meta tb4c-member-standard-meta-v4265" aria-label="ข้อมูลมาตรฐานสมาชิก">
          <span><?php echo tb4cm_icon_markup( 'ph-user-focus', '◎' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( $member_role ? $member_role : 'สมาชิก Thinkb4do' ); ?></span>
          <span><?php echo tb4cm_icon_markup( 'ph-calendar-check', '◷' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>เข้าร่วม <?php echo esc_html( $member_since ); ?></span>
          <?php if ( $member_location ) : ?><span><?php echo tb4cm_icon_markup( 'ph-map-pin', '⌖' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( $member_location ); ?></span><?php endif; ?>
          <?php if ( $member_interests ) : ?><span><?php echo tb4cm_icon_markup( 'ph-sparkle', '✦' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( $member_interests ); ?></span><?php endif; ?>
        </div>
      </div>
      <div class="tb4c-member-area-actions tb4c-member-area-actions-v65 tb4c-member-area-actions-v80">
        <a class="tb4c-btn tb4c-btn-secondary tb4c-member-edit-profile" href="<?php echo esc_url( add_query_arg( [ 'tb4c_area' => 'settings' ], $area_url ) ); ?>#tb4cMemberAvatarField"><?php echo tb4cm_icon_markup( 'ph-pencil-simple-line', '✎' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span>แก้ไขโปรไฟล์</span></a>
        <button class="tb4c-btn tb4c-btn-primary tb4c-member-composer-trigger" type="button" data-tb4c-open-composer><?php echo tb4cm_icon_markup( 'ph-plus', '+' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span>สร้างโพสต์</span></button>
      </div>
    </header>

    <?php if ( 'overview' !== $tab ) : ?>
    <section class="tb4c-member-showcase-stats" aria-label="สรุปข้อมูลสมาชิก">
      <div class="tb4c-member-showcase-stat"><i><?php echo tb4cm_icon_markup( 'ph-note-pencil', '✎' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></i><strong><?php echo esc_html( number_format_i18n( $total_my_posts ) ); ?></strong><span>โพสต์ทั้งหมด</span></div>
      <div class="tb4c-member-showcase-stat"><i><?php echo tb4cm_icon_markup( 'ph-chat-circle-text', '💬' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></i><strong><?php echo esc_html( number_format_i18n( $comment_count ) ); ?></strong><span>ความคิดเห็น</span></div>
      <div class="tb4c-member-showcase-stat"><i><?php echo tb4cm_icon_markup( 'ph-bookmark-simple', '★' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></i><strong><?php echo esc_html( number_format_i18n( count( $bookmarked_ids ) ) ); ?></strong><span>บันทึกไว้</span></div>
      <div class="tb4c-member-showcase-stat"><i><?php echo tb4cm_icon_markup( 'ph-users-three', '👥' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></i><strong><?php echo esc_html( number_format_i18n( $follower_count ) ); ?></strong><span>ผู้ติดตาม</span></div>
      <div class="tb4c-member-showcase-stat"><i><?php echo tb4cm_icon_markup( 'ph-eye', '👁' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></i><strong><?php echo esc_html( number_format_i18n( $total_views ) ); ?></strong><span>ยอดเข้าชม</span></div>
      <a class="tb4c-member-showcase-progress" href="<?php echo esc_url( add_query_arg( [ 'tb4c_area' => 'settings' ], $area_url ) ); ?>" aria-label="ความสมบูรณ์ของโปรไฟล์">
        <span><strong>ความสมบูรณ์ของโปรไฟล์</strong><em><?php echo esc_html( number_format_i18n( $profile_score ) ); ?>%</em></span>
        <i><b style="width:<?php echo esc_attr( $profile_score ); ?>%"></b></i>
        <small><?php echo esc_html( $profile_score >= 85 ? 'ใกล้สมบูรณ์แล้ว! เพิ่มเติมอีกเล็กน้อย' : 'เติมข้อมูลให้ครบ เพื่อให้สมาชิกคนอื่นรู้จักคุณมากขึ้น' ); ?> ›</small>
      </a>
    </section>
    <?php endif; ?>

    <nav class="tb4c-member-area-tabs tb4c-member-area-feature-tabs" aria-label="เมนูพื้นที่สมาชิก">
      <?php foreach ( $tab_items as $key => $item ) :
          $is_locked    = ! empty( $item['locked'] );
          $purchase_url = ! empty( $item['purchase_url'] ) ? $item['purchase_url'] : home_url( '/products/' );
          $tab_classes  = trim( ( $tab === $key ? 'is-active ' : '' ) . ( $is_locked ? 'is-locked' : '' ) );
      ?>
        <a class="<?php echo esc_attr( $tab_classes ); ?>" href="<?php echo esc_url( $is_locked ? $purchase_url : add_query_arg( [ 'tb4c_area' => $key ], $area_url ) ); ?>" <?php echo $is_locked ? 'aria-disabled="true" data-tb4c-feature-locked="1"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> data-tb4c-feature-key="<?php echo esc_attr( $key ); ?>" data-tb4c-feature-label="<?php echo esc_attr( $item['label'] ); ?>" data-tb4c-purchase-url="<?php echo esc_url( $purchase_url ); ?>">
          <?php echo tb4cm_icon_markup( $item['icon'], $item['fallback'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
          <span><?php echo esc_html( $item['label'] ); ?></span>
          <?php if ( ! empty( $item['desc'] ) ) : ?><small class="tb4c-member-tab-desc"><?php echo esc_html( $item['desc'] ); ?></small><?php endif; ?>
          <?php if ( $is_locked ) : ?><em class="tb4c-member-tab-lock">ล็อก</em><?php endif; ?>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="tb4c-member-feature-lock-toast" data-tb4c-member-feature-lock-toast aria-live="polite" aria-hidden="true"></div>

    <div class="tb4c-member-area-layout">
      <main class="tb4c-member-area-main">
        <?php if ( 'overview' === $tab ) : ?>
          <section class="tb4c-member-overview-split tb4c-member-overview-has-sidebar" aria-label="พื้นที่สมาชิก">
            <aside class="tb4c-member-overview-info-column" aria-label="ข้อมูลสมาชิก">
              <div class="tb4c-member-info-card tb4c-member-info-about-card">
                <div class="tb4c-member-info-card-head"><h2>เกี่ยวกับ</h2></div>
                <div class="tb4c-member-info-list tb4c-member-info-list-v4265">
                  <?php if ( $member_bio_short ) : ?><p class="tb4c-member-info-bio-text"><?php echo esc_html( $member_bio_short ); ?></p><?php endif; ?>
                  <?php if ( $member_role ) : ?><span><?php echo tb4cm_icon_markup( 'ph-user-focus', '◎' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><b>บทบาท</b><em><?php echo esc_html( $member_role ); ?></em></span><?php endif; ?>
                  <?php if ( $member_location ) : ?><span><?php echo tb4cm_icon_markup( 'ph-map-pin', '⌖' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><b>พื้นที่</b><em><?php echo esc_html( $member_location ); ?></em></span><?php endif; ?>
                  <?php if ( $member_interests ) : ?><span><?php echo tb4cm_icon_markup( 'ph-sparkle', '✦' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><b>สนใจ</b><em><?php echo esc_html( $member_interests ); ?></em></span><?php endif; ?>
                  <?php if ( $contact_url ) : ?><span><?php echo tb4cm_icon_markup( 'ph-link-simple', '↗' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><b>ลิงก์</b><em><a href="<?php echo esc_url( $contact_url ); ?>" target="_blank" rel="noopener">ช่องทางติดต่อ</a></em></span><?php endif; ?>
                  <span><?php echo tb4cm_icon_markup( 'ph-calendar-check', '◷' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><b>เข้าร่วม</b><em><?php echo esc_html( $member_since ); ?></em></span>
                  <a class="tb4c-btn tb4c-btn-secondary" style="width:100%;justify-content:center;margin-top:12px" href="<?php echo esc_url( add_query_arg( [ 'tb4c_area' => 'settings' ], $area_url ) ); ?>#tb4cMemberAvatarField"><?php echo tb4cm_icon_markup( 'ph-pencil-simple-line', '✎' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span>แก้ไขข้อมูล</span></a>
                </div>
              </div>
              <div class="tb4c-member-info-card tb4c-member-info-mini-stats-card">
                <div class="tb4c-member-info-card-head"><h2>สถิติ</h2><span class="tb4c-member-level-badge"><?php echo esc_html( $member_level_label ); ?></span></div>
                <div class="tb4c-member-fb-mini-stats">
                  <div><strong><?php echo esc_html( number_format_i18n( $total_my_posts ) ); ?></strong><span>โพสต์</span></div>
                  <div><strong><?php echo esc_html( number_format_i18n( $follower_count ) ); ?></strong><span>ผู้ติดตาม</span></div>
                  <div><strong><?php echo esc_html( number_format_i18n( count( $following_ids ) ) ); ?></strong><span>ติดตาม</span></div>
                </div>
              </div>
              <div class="tb4c-member-info-card tb4c-member-storage-mini-card">
                <div class="tb4c-member-info-card-head"><h2>พื้นที่ใช้งาน</h2><span class="tb4c-member-storage-mini-links"><a href="<?php echo esc_url( add_query_arg( [ 'tb4c_area' => 'usage', 'tb4c_storage_page' => 'my' ], $area_url ) ); ?>">พื้นที่ของฉัน</a><a href="<?php echo esc_url( add_query_arg( [ 'tb4c_area' => 'usage', 'tb4c_storage_page' => 'packages' ], $area_url ) ); ?>">เพิ่มแพ็กเกจ</a></span></div>
                <div class="tb4c-member-storage-mini-meter" aria-label="การใช้พื้นที่ของสมาชิก">
                  <span><strong><?php echo esc_html( function_exists( 'tb4cm_format_storage_size' ) ? tb4cm_format_storage_size( $member_storage_data['storage_used'] ?? 0 ) : size_format( absint( $member_storage_data['storage_used'] ?? 0 ) ) ); ?></strong><em><?php echo esc_html( number_format_i18n( (float) ( $member_storage_data['percent'] ?? 0 ), 1 ) ); ?>% ของพื้นที่</em></span>
                  <i><b style="width:<?php echo esc_attr( min( 100, (float) ( $member_storage_data['percent'] ?? 0 ) ) ); ?>%"></b></i>
                </div>
                <p class="tb4c-member-storage-mini-note">คงเหลือ <?php echo esc_html( function_exists( 'tb4cm_format_storage_size' ) ? tb4cm_format_storage_size( $member_storage_data['storage_remaining'] ?? 0 ) : size_format( absint( $member_storage_data['storage_remaining'] ?? 0 ) ) ); ?> · สถานะ <?php echo esc_html( $member_storage_data['status']['label'] ?? 'ปกติ' ); ?></p>
              </div>
            </aside>
            <div class="tb4c-member-overview-feed-column" aria-label="ฟีดสมาชิก">
              <?php tb4cm_member_quick_composer_card( $avatar, $member_display_name ); ?>
              <section class="tb4c-member-area-panel tb4c-member-area-feed-panel tb4c-member-area-overview-own-feed-panel tb4c-member-feed-curated-panel tb4c-member-dedup-feed-panel" aria-label="ฟีดของสมาชิก">
                <div class="tb4c-feed-section-line tb4c-member-overview-feed-section-line" aria-label="ลำดับฟีดสมาชิก">
                  <strong>ฟีดล่าสุด</strong>
                  <span>โพสต์จริง</span>
                </div>
                <?php if ( $my_posts_query->have_posts() ) : ?>
                  <div class="tb4c-feed-list tb4c-member-own-feed-list tb4c-member-overview-own-feed-list" id="tb4cMemberFeedList">
                    <?php while ( $my_posts_query->have_posts() ) : $my_posts_query->the_post(); include TB4CM_DIR . 'templates/parts/feed-post-card.php'; endwhile; wp_reset_postdata(); ?>
                  </div>
                  <?php
                  $tb4c_member_feed_max_pages = isset( $my_posts_query->max_num_pages ) ? (int) $my_posts_query->max_num_pages : 1;
                  if ( $tb4c_member_feed_max_pages > 1 ) :
                  ?>
                    <div class="tb4c-feed-load-more-wrap tb4c-member-feed-load-more-wrap" data-tb4c-member-feed-load-more-wrap>
                      <button
                        type="button"
                        class="tb4c-feed-load-more tb4c-member-feed-load-more"
                        data-tb4c-load-more-member-feed
                        data-list-id="tb4cMemberFeedList"
                        data-current-page="1"
                        data-next-page="2"
                        data-max-pages="<?php echo esc_attr( $tb4c_member_feed_max_pages ); ?>"
                        data-author-id="<?php echo esc_attr( $user_id ); ?>"
                        data-status-filter="<?php echo esc_attr( $post_status_filter ); ?>"
                        aria-controls="tb4cMemberFeedList"
                      >
                        <span class="tb4c-load-more-label">โหลดเพิ่มเติม</span>
                        <small><?php echo esc_html( sprintf( 'หน้า %1$d จาก %2$d', 1, $tb4c_member_feed_max_pages ) ); ?></small>
                      </button>
                    </div>
                  <?php endif; ?>
                <?php else : ?>
                  <div class="tb4c-member-area-empty"><h3>ยังไม่มีโพสต์</h3><p>เริ่มโพสต์แรกเพื่อแบ่งปันไอเดียกับชุมชน</p><button class="tb4c-btn tb4c-btn-primary tb4c-member-composer-trigger" type="button" data-tb4c-open-composer>สร้างโพสต์แรก</button></div>
                <?php endif; ?>
              </section>
            </div>
          </section>

        <?php endif; ?>

        <?php if ( 'products' === $tab ) : ?>
          <section class="tb4c-member-area-panel tb4c-member-products-panel tb4c-member-products-app-panel" aria-label="ผลิตภัณฑ์และแอพเสริม">
            <div class="tb4c-member-area-panel-head tb4c-member-products-head">
              <div>
                <h2>ผลิตภัณฑ์และแอพเสริม</h2>
                <p>รวมฟีเจอร์ใหม่ แอพเสริม การซื้อ/เปิดใช้งาน ส่งบิลทางอีเมล ส่งไฟล์ และเปิดแอพในพื้นที่สมาชิกแบบ Mac-like</p>
              </div>
              <a class="tb4c-btn tb4c-btn-secondary is-small" href="<?php echo esc_url( home_url( '/products/' ) ); ?>">ดูหน้าผลิตภัณฑ์</a>
            </div>
            <?php if ( shortcode_exists( 'thinkb4do_app_store' ) ) : ?>
              <?php echo do_shortcode( '[thinkb4do_app_store limit="12" context="member"]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php else : ?>
              <div class="tb4c-member-area-empty tb4c-member-products-empty">
                <h3>ยังไม่ได้เปิดระบบผลิตภัณฑ์</h3>
                <p>ติดตั้งหรือเปิดใช้งาน Thinkb4do Products เพื่อแสดง App Store, ระบบซื้อฟีเจอร์, ส่งบิลทางอีเมล และส่งไฟล์/ติดตั้งทันทีในหน้านี้</p>
                <a class="tb4c-btn tb4c-btn-primary" href="<?php echo esc_url( home_url( '/products/' ) ); ?>">เปิดหน้าผลิตภัณฑ์</a>
              </div>
            <?php endif; ?>
          </section>
        <?php endif; ?>

        <?php if ( 'posts' === $tab ) : ?>
          <section class="tb4c-member-area-panel tb4c-member-area-feed-panel">
            <?php tb4cm_member_quick_composer_card( $avatar, $member_display_name ); ?>
            <div class="tb4c-member-area-panel-head tb4c-member-area-feed-head">
              <div>
                <h2>ฟีดของฉัน</h2>
              </div>
              <button class="tb4c-btn tb4c-btn-primary is-small tb4c-member-composer-trigger" type="button" data-tb4c-open-composer>สร้างโพสต์</button>
            </div>
            <div class="tb4c-member-area-status-filter tb4c-member-feed-main-status-tabs" aria-label="กรองสถานะโพสต์ของฉัน">
              <?php
              $status_filter_labels = [
                  'all'     => 'ทั้งหมด',
                  'publish' => 'เผยแพร่',
                  'pending' => 'รอตรวจ',
                  'draft'   => 'ฉบับร่าง',
                  'private' => 'ส่วนตัว',
              ];
              foreach ( $status_filter_labels as $filter_key => $filter_label ) :
              ?>
                <a class="<?php echo esc_attr( $post_status_filter === $filter_key ? 'is-active' : '' ); ?>" href="<?php echo esc_url( add_query_arg( [ 'tb4c_area' => 'posts', 'tb4c_post_status' => $filter_key, 'tb4c_posts_page' => 1 ], $area_url ) ); ?>"><?php echo esc_html( $filter_label ); ?></a>
              <?php endforeach; ?>
              <?php
              $member_post_quick_links = [
                  'groups' => 'กลุ่ม',
                  'market' => 'ตลาด',
                  'chat'   => 'แชท',
              ];
              foreach ( $member_post_quick_links as $feature_key => $feature_label ) :
              ?>
                <a class="tb4c-member-status-quick-link" href="<?php echo esc_url( add_query_arg( [ 'tb4c_area' => $feature_key ], $area_url ) ); ?>"><?php echo esc_html( $feature_label ); ?></a>
              <?php endforeach; ?>
            </div>
            <?php if ( $my_posts_query->have_posts() ) : ?>
              <div class="tb4c-feed-list tb4c-member-own-feed-list">
                <?php while ( $my_posts_query->have_posts() ) : $my_posts_query->the_post(); tb4cm_member_area_feed_post_card( get_the_ID(), $avatar ); endwhile; wp_reset_postdata(); ?>
              </div>
              <?php if ( $my_posts_query->max_num_pages > 1 ) : ?>
                <nav class="tb4c-member-area-pagination" aria-label="หน้าโพสต์ของฉัน">
                  <?php
                  echo wp_kses_post( paginate_links( [
                      'base'      => esc_url_raw( add_query_arg( [ 'tb4c_area' => 'posts', 'tb4c_post_status' => $post_status_filter, 'tb4c_posts_page' => '%#%' ], $area_url ) ),
                      'format'    => '',
                      'current'   => $posts_page,
                      'total'     => (int) $my_posts_query->max_num_pages,
                      'prev_text' => 'ก่อนหน้า',
                      'next_text' => 'ถัดไป',
                  ] ) );
                  ?>
                </nav>
              <?php endif; ?>
            <?php else : ?>
              <div class="tb4c-member-area-empty"><h3>ยังไม่มีโพสต์ในสถานะนี้</h3><p>ลองเปลี่ยนตัวกรอง หรือสร้างโพสต์ใหม่เพื่อให้รายการมาแสดงตรงนี้</p><button class="tb4c-btn tb4c-btn-primary tb4c-member-composer-trigger" type="button" data-tb4c-open-composer>สร้างโพสต์</button></div>
            <?php endif; ?>
          </section>
        <?php endif; ?>

        <?php if ( 'comments' === $tab ) : ?>
          <section class="tb4c-member-area-panel">
            <div class="tb4c-member-area-panel-head"><h2>ความคิดเห็น</h2><span><?php echo esc_html( number_format_i18n( $comment_count ) ); ?> รายการ</span></div>
            <?php if ( ! empty( $my_comments ) ) : ?>
              <div class="tb4c-member-area-comment-list">
                <?php foreach ( $my_comments as $comment ) : ?>
                  <article class="tb4c-member-area-comment-card">
                    <p><?php echo esc_html( wp_trim_words( $comment->comment_content, 38 ) ); ?></p>
                    <div><time datetime="<?php echo esc_attr( get_comment_date( DATE_W3C, $comment ) ); ?>"><?php echo esc_html( get_comment_date( 'j M Y', $comment ) ); ?></time><a href="<?php echo esc_url( get_comment_link( $comment ) ); ?>">เปิดบทสนทนา</a></div>
                  </article>
                <?php endforeach; ?>
              </div>
            <?php else : ?>
              <div class="tb4c-member-area-empty"><h3>ยังไม่มีความคิดเห็น</h3><p>เมื่อคุณแสดงความคิดเห็นในโพสต์ รายการจะมาอยู่ตรงนี้</p></div>
            <?php endif; ?>
          </section>
        <?php endif; ?>

        <?php if ( 'saved' === $tab ) : ?>
          <section class="tb4c-member-area-panel">
            <div class="tb4c-member-area-panel-head"><h2>คลังบันทึก</h2><span><?php echo esc_html( number_format_i18n( count( $bookmarked_ids ) ) ); ?> รายการ</span></div>
            <?php if ( ! empty( $saved_posts ) ) : ?>
              <div class="tb4c-member-area-list">
                <?php foreach ( $saved_posts as $saved_post ) : tb4cm_member_area_post_card( $saved_post->ID ); endforeach; ?>
              </div>
            <?php else : ?>
              <div class="tb4c-member-area-empty"><h3>ยังไม่มีสิ่งที่บันทึกไว้</h3><p>กดปุ่มบันทึกใต้โพสต์ที่สนใจ แล้วกลับมาดูภายหลังได้จากหน้านี้</p></div>
            <?php endif; ?>
          </section>
        <?php endif; ?>

        <?php if ( 'following' === $tab ) : ?>
          <section class="tb4c-member-area-panel">
            <div class="tb4c-member-area-panel-head"><h2>เครือข่ายที่ติดตาม</h2><a href="<?php echo esc_url( home_url( '/community/' ) ); ?>">กลับไปชุมชน</a></div>
            <?php if ( ! empty( $following_ids ) ) : ?>
              <div class="tb4c-member-area-follow-grid">
                <?php foreach ( $following_ids as $following_id ) : $follow_user = get_userdata( $following_id ); if ( ! $follow_user ) { continue; } ?>
                  <article class="tb4c-member-area-follow-card">
                    <a class="tb4c-member-area-follow-avatar" href="<?php echo esc_url( tb4cm_social_profile_url( $following_id ) ); ?>"><img src="<?php echo esc_url( function_exists( 'tb4cm_get_user_avatar_url' ) ? tb4cm_get_user_avatar_url( $following_id, [ 'size' => 128 ] ) : get_avatar_url( $following_id, [ 'size' => 128 ] ) ); ?>" alt=""></a>
                    <h3><a href="<?php echo esc_url( tb4cm_social_profile_url( $following_id ) ); ?>"><?php echo esc_html( $follow_user->display_name ); ?></a></h3>
                    <p><?php echo esc_html( wp_trim_words( get_user_meta( $following_id, 'description', true ), 12 ) ); ?></p>
                    <?php echo tb4cm_render_follow_button( $following_id, [ 'small' => true ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                  </article>
                <?php endforeach; ?>
              </div>
            <?php else : ?>
              <div class="tb4c-member-area-empty"><h3>ยังไม่ได้ติดตามใคร</h3><p>ลองค้นพบสมาชิกที่น่าสนใจ แล้วกดติดตามเพื่อเห็นการเคลื่อนไหวของพวกเขา</p><a class="tb4c-btn tb4c-btn-primary" href="<?php echo esc_url( home_url( '/community/' ) ); ?>">กลับไปชุมชน</a></div>
            <?php endif; ?>
          </section>
        <?php endif; ?>



        <?php if ( 'groups' === $tab ) : ?>
          <section class="tb4c-member-area-panel tb4c-member-standard-simple-panel">
            <div class="tb4c-member-area-panel-head"><h2>กลุ่มของสมาชิก</h2><a href="<?php echo esc_url( home_url( '/community/' ) ); ?>">ค้นหากลุ่ม</a></div>
            <div class="tb4c-member-area-empty"><h3>ยังไม่มีกลุ่มที่แสดง</h3><p>เมื่อมีระบบกลุ่มหรือชุมชนย่อย รายการจะมาอยู่ตรงนี้ โดยไม่รบกวนหน้าฟีดหลัก</p></div>
          </section>
        <?php endif; ?>

        <?php if ( 'market' === $tab ) : ?>
          <section class="tb4c-member-area-panel tb4c-member-standard-simple-panel">
            <div class="tb4c-member-area-panel-head"><h2>ตลาดของสมาชิก</h2><a href="<?php echo esc_url( home_url( '/products/' ) ); ?>">ดูตลาด</a></div>
            <div class="tb4c-member-area-empty"><h3>ยังไม่มีรายการตลาด</h3><p>พื้นที่สำหรับสินค้า บริการ หรือโพสต์ซื้อขายของสมาชิกในอนาคต</p></div>
          </section>
        <?php endif; ?>

        <?php if ( 'chat' === $tab ) : ?>
          <section class="tb4c-member-area-panel tb4c-member-standard-simple-panel">
            <div class="tb4c-member-area-panel-head"><h2>แชท</h2><span>ข้อความสมาชิก</span></div>
            <div class="tb4c-member-area-empty"><h3>ยังไม่มีข้อความ</h3><p>เมื่อเปิดระบบแชท รายการสนทนาจะมาแสดงในหน้านี้</p></div>
          </section>
        <?php endif; ?>


        <?php if ( 'usage' === $tab ) : ?>
          <?php
          $storage_page = isset( $_GET['tb4c_storage_page'] ) ? sanitize_key( wp_unslash( $_GET['tb4c_storage_page'] ) ) : 'my';
          if ( ! in_array( $storage_page, [ 'my', 'usage', 'packages', 'history' ], true ) ) {
              $storage_page = 'my';
          }
          $storage_data            = ! empty( $member_storage_data ) && is_array( $member_storage_data ) ? $member_storage_data : [];
          $usage_total_bytes       = absint( $storage_data['storage_used'] ?? 0 );
          $usage_quota_bytes       = absint( $storage_data['storage_limit'] ?? 0 );
          $usage_remaining_bytes   = absint( $storage_data['storage_remaining'] ?? 0 );
          $usage_percent           = min( 100, max( 0, (float) ( $storage_data['percent'] ?? 0 ) ) );
          $usage_categories        = ! empty( $storage_data['categories'] ) && is_array( $storage_data['categories'] ) ? $storage_data['categories'] : [];
          $usage_latest_items      = ! empty( $storage_data['latest_items'] ) && is_array( $storage_data['latest_items'] ) ? $storage_data['latest_items'] : [];
          $usage_history_items     = ! empty( $storage_data['storage_history'] ) && is_array( $storage_data['storage_history'] ) ? $storage_data['storage_history'] : [];
          $storage_status          = ! empty( $storage_data['status'] ) && is_array( $storage_data['status'] ) ? $storage_data['status'] : [ 'key' => 'normal', 'label' => 'ปกติ', 'message' => 'พื้นที่ยังอยู่ในระดับปกติ', 'tone' => 'normal' ];
          $storage_current_key     = ! empty( $storage_data['current_package'] ) ? sanitize_key( $storage_data['current_package'] ) : ( ! empty( $member_storage_package['key'] ) ? sanitize_key( $member_storage_package['key'] ) : 'free' );
          $storage_current_label   = ! empty( $storage_data['current_package_label'] ) ? $storage_data['current_package_label'] : ( $member_storage_package['label'] ?? 'ฟรี' );
          $storage_expired_label   = ! empty( $storage_data['package_expired_at'] ) ? $storage_data['package_expired_at'] : 'ยังไม่กำหนดวันหมดอายุ';
          $format_storage          = function ( $bytes ) {
              return function_exists( 'tb4cm_format_storage_size' ) ? tb4cm_format_storage_size( $bytes ) : size_format( absint( $bytes ) );
          };
          ?>
          <section class="tb4c-member-area-panel tb4c-member-storage-panel tb4c-member-storage-v276" aria-label="เมนูการใช้พื้นที่สมาชิก">
            <div class="tb4c-member-area-panel-head tb4c-member-storage-head">
              <div>
                <h2>พื้นที่ของฉัน</h2>
                <p>ดูพื้นที่บัญชีของคุณ ใช้ไปแล้วเท่าไหร่ เหลือเท่าไหร่ และเลือกเพิ่มแพ็กเกจพื้นที่ได้จากหน้านี้</p>
              </div>
              <a class="tb4c-btn tb4c-btn-primary is-small" href="<?php echo esc_url( add_query_arg( [ 'tb4c_area' => 'usage', 'tb4c_storage_page' => 'packages' ], $area_url ) ); ?>">เพิ่มพื้นที่</a>
            </div>

            <nav class="tb4c-member-storage-subtabs tb4c-member-storage-subtabs-v276" aria-label="เมนูพื้นที่สมาชิก">
              <a class="<?php echo esc_attr( 'my' === $storage_page ? 'is-active' : '' ); ?>" href="<?php echo esc_url( add_query_arg( [ 'tb4c_area' => 'usage', 'tb4c_storage_page' => 'my' ], $area_url ) ); ?>">
                <?php echo tb4cm_icon_markup( 'ph-hard-drives', '▥' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                <span>พื้นที่ของฉัน</span>
                <small>สรุปบัญชี</small>
              </a>
              <a class="<?php echo esc_attr( 'usage' === $storage_page ? 'is-active' : '' ); ?>" href="<?php echo esc_url( add_query_arg( [ 'tb4c_area' => 'usage', 'tb4c_storage_page' => 'usage' ], $area_url ) ); ?>">
                <?php echo tb4cm_icon_markup( 'ph-chart-pie-slice', '◔' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                <span>การใช้พื้นที่</span>
                <small>รายละเอียด</small>
              </a>
              <a class="<?php echo esc_attr( 'packages' === $storage_page ? 'is-active' : '' ); ?>" href="<?php echo esc_url( add_query_arg( [ 'tb4c_area' => 'usage', 'tb4c_storage_page' => 'packages' ], $area_url ) ); ?>">
                <?php echo tb4cm_icon_markup( 'ph-package', '▣' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                <span>เพิ่มแพ็กเกจพื้นที่</span>
                <small>เลือกแพ็กเกจ</small>
              </a>
              <a class="<?php echo esc_attr( 'history' === $storage_page ? 'is-active' : '' ); ?>" href="<?php echo esc_url( add_query_arg( [ 'tb4c_area' => 'usage', 'tb4c_storage_page' => 'history' ], $area_url ) ); ?>">
                <?php echo tb4cm_icon_markup( 'ph-clock-counter-clockwise', '◷' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                <span>ประวัติการใช้พื้นที่</span>
                <small>รายการล่าสุด</small>
              </a>
            </nav>

            <?php if ( 'my' === $storage_page ) : ?>
              <div class="tb4c-member-storage-my-page">
                <div class="tb4c-member-storage-hero tb4c-member-storage-hero-v276 is-<?php echo esc_attr( sanitize_html_class( $storage_status['tone'] ?? 'normal' ) ); ?>">
                  <div class="tb4c-member-storage-ring" style="--tb4c-storage-percent:<?php echo esc_attr( $usage_percent ); ?>%;">
                    <strong><?php echo esc_html( number_format_i18n( $usage_percent, 1 ) ); ?>%</strong>
                    <span>ใช้ไปแล้ว</span>
                  </div>
                  <div class="tb4c-member-storage-summary">
                    <span class="tb4c-member-storage-user-id">บัญชีของคุณ · User ID <?php echo esc_html( number_format_i18n( $user_id ) ); ?></span>
                    <h3><?php echo esc_html( $format_storage( $usage_total_bytes ) ); ?> / <?php echo esc_html( $format_storage( $usage_quota_bytes ) ); ?></h3>
                    <p><?php echo esc_html( $storage_status['message'] ?? 'พื้นที่ยังอยู่ในระดับปกติ' ); ?></p>
                    <div class="tb4c-member-storage-meter"><i><b style="width:<?php echo esc_attr( $usage_percent ); ?>%"></b></i><span><?php echo esc_html( $format_storage( $usage_remaining_bytes ) ); ?> พื้นที่คงเหลือ</span></div>
                    <div class="tb4c-member-storage-quick-actions">
                      <a class="tb4c-btn tb4c-btn-primary is-small" href="<?php echo esc_url( add_query_arg( [ 'tb4c_area' => 'usage', 'tb4c_storage_page' => 'packages' ], $area_url ) ); ?>">เพิ่มพื้นที่</a>
                      <a class="tb4c-btn tb4c-btn-secondary is-small" href="<?php echo esc_url( add_query_arg( [ 'tb4c_area' => 'usage', 'tb4c_storage_page' => 'usage' ], $area_url ) ); ?>">ดูการใช้พื้นที่</a>
                    </div>
                  </div>
                </div>

                <div class="tb4c-member-storage-stat-grid" aria-label="สรุปพื้นที่ของฉัน">
                  <article><span>พื้นที่ทั้งหมดของบัญชี</span><strong><?php echo esc_html( $format_storage( $usage_quota_bytes ) ); ?></strong></article>
                  <article><span>ใช้ไปแล้ว</span><strong><?php echo esc_html( $format_storage( $usage_total_bytes ) ); ?></strong></article>
                  <article><span>พื้นที่คงเหลือ</span><strong><?php echo esc_html( $format_storage( $usage_remaining_bytes ) ); ?></strong></article>
                  <article><span>แพ็กเกจปัจจุบัน</span><strong><?php echo esc_html( $storage_current_label ); ?></strong></article>
                  <article><span>สถานะพื้นที่</span><strong><?php echo esc_html( $storage_status['label'] ?? 'ปกติ' ); ?></strong></article>
                  <article><span>วันหมดอายุแพ็กเกจ</span><strong><?php echo esc_html( $storage_expired_label ); ?></strong></article>
                </div>

                <?php if ( $usage_percent >= 80 && $usage_percent < 100 ) : ?>
                  <div class="tb4c-member-storage-alert is-warning">พื้นที่ใกล้เต็มแล้ว แนะนำให้ลบไฟล์ที่ไม่จำเป็น หรือเพิ่มแพ็กเกจพื้นที่</div>
                <?php elseif ( $usage_percent >= 100 ) : ?>
                  <div class="tb4c-member-storage-alert is-danger">พื้นที่เต็ม กรุณาเพิ่มแพ็กเกจพื้นที่</div>
                <?php endif; ?>
              </div>
            <?php endif; ?>

            <?php if ( 'usage' === $storage_page ) : ?>
              <div class="tb4c-member-storage-usage-page">
                <div class="tb4c-member-storage-usage-top">
                  <div>
                    <h3>การใช้พื้นที่</h3>
                    <p>รายละเอียดการใช้พื้นที่ของบัญชีนี้ แสดงเป็น MB / GB และคำนวณเปอร์เซ็นต์ไม่เกิน 100%</p>
                  </div>
                  <strong><?php echo esc_html( number_format_i18n( $usage_percent, 1 ) ); ?>%</strong>
                </div>
                <div class="tb4c-member-storage-meter tb4c-member-storage-meter-large" aria-label="Progress Bar การใช้พื้นที่ 0 ถึง 100 เปอร์เซ็นต์">
                  <i><b style="width:<?php echo esc_attr( $usage_percent ); ?>%"></b></i>
                  <span><?php echo esc_html( $format_storage( $usage_total_bytes ) ); ?> ใช้ไปแล้ว · เหลือ <?php echo esc_html( $format_storage( $usage_remaining_bytes ) ); ?></span>
                </div>

                <?php if ( $usage_percent >= 80 && $usage_percent < 100 ) : ?>
                  <div class="tb4c-member-storage-alert is-warning">พื้นที่ใกล้เต็มแล้ว</div>
                <?php elseif ( $usage_percent >= 100 ) : ?>
                  <div class="tb4c-member-storage-alert is-danger">พื้นที่เต็ม กรุณาเพิ่มแพ็กเกจพื้นที่</div>
                <?php endif; ?>

                <div class="tb4c-member-storage-grid" aria-label="แยกประเภทการใช้พื้นที่">
                  <?php foreach ( $usage_categories as $category_key => $category_data ) : ?>
                    <article class="tb4c-member-storage-context-card is-<?php echo esc_attr( sanitize_html_class( $category_key ) ); ?>">
                      <span><?php echo esc_html( $category_data['label'] ?? $category_key ); ?></span>
                      <strong><?php echo esc_html( $format_storage( absint( $category_data['bytes'] ?? 0 ) ) ); ?></strong>
                      <em><?php echo esc_html( number_format_i18n( absint( $category_data['count'] ?? 0 ) ) ); ?> รายการ</em>
                    </article>
                  <?php endforeach; ?>
                </div>

                <div class="tb4c-member-storage-latest-card">
                  <div class="tb4c-member-area-panel-head"><h3>ไฟล์ล่าสุด</h3><span><?php echo esc_html( number_format_i18n( absint( $member_storage_usage['attachment_count'] ?? 0 ) ) ); ?> ไฟล์ทั้งหมด</span></div>
                  <?php if ( ! empty( $usage_latest_items ) ) : ?>
                    <div class="tb4c-member-storage-file-list">
                      <?php foreach ( $usage_latest_items as $file_item ) : ?>
                        <a href="<?php echo esc_url( $file_item['url'] ?? '#' ); ?>" target="_blank" rel="noopener" class="tb4c-member-storage-file-row">
                          <span><?php echo tb4cm_icon_markup( 'ph-file', '▣' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                          <strong><?php echo esc_html( wp_trim_words( $file_item['title'] ?? 'ไฟล์สมาชิก', 8, '' ) ); ?></strong>
                          <em><?php echo esc_html( $format_storage( absint( $file_item['bytes'] ?? 0 ) ) ); ?> · <?php echo esc_html( $file_item['date'] ?? '' ); ?></em>
                        </a>
                      <?php endforeach; ?>
                    </div>
                  <?php else : ?>
                    <div class="tb4c-member-area-empty"><h3>ยังไม่มีไฟล์ในพื้นที่นี้</h3><p>เมื่ออัปโหลดรูป วิดีโอ หรือไฟล์แนบ ระบบจะแสดงการใช้พื้นที่ของบัญชีนี้ทันที</p></div>
                  <?php endif; ?>
                </div>
              </div>
            <?php endif; ?>

            <?php if ( 'packages' === $storage_page ) : ?>
              <div class="tb4c-member-storage-package-page">
                <div class="tb4c-member-storage-package-hero">
                  <div>
                    <span class="tb4c-member-storage-user-id">แพ็กเกจปัจจุบัน: <?php echo esc_html( $storage_current_label ); ?></span>
                    <h3>เพิ่มแพ็กเกจพื้นที่</h3>
                    <p>เลือกแพ็กเกจที่เหมาะกับการใช้งานของคุณ ปุ่มยังไม่เชื่อมจ่ายเงินจริง แต่เตรียมโครงสร้างไว้สำหรับต่อระบบชำระเงินหรือระบบอนุมัติภายหลัง</p>
                  </div>
                  <div class="tb4c-member-storage-package-current">
                    <strong><?php echo esc_html( number_format_i18n( $usage_percent, 1 ) ); ?>%</strong>
                    <span>ใช้ไปแล้ว</span>
                    <i><b style="width:<?php echo esc_attr( $usage_percent ); ?>%"></b></i>
                  </div>
                </div>

                <div class="tb4c-member-storage-package-grid" aria-label="แพ็กเกจพื้นที่">
                  <?php foreach ( $member_storage_plans as $plan_key => $plan ) : ?>
                    <?php
                    $plan_key     = sanitize_key( $plan_key );
                    $plan_quota   = ! empty( $plan['quota_mb'] ) ? absint( $plan['quota_mb'] ) * MB_IN_BYTES : 0;
                    $is_current   = $storage_current_key === $plan_key;
                    $is_recommend = ! empty( $plan['recommended'] );
                    $request_url  = function_exists( 'tb4cm_member_storage_package_request_url' ) ? tb4cm_member_storage_package_request_url( $plan_key, $user_id ) : home_url( '/contact/' );
                    ?>
                    <article class="tb4c-member-storage-package-card <?php echo esc_attr( $is_current ? 'is-current' : '' ); ?> <?php echo esc_attr( $is_recommend ? 'is-recommended' : '' ); ?>" data-tb4c-storage-package="<?php echo esc_attr( $plan_key ); ?>" data-tb4c-user-id="<?php echo esc_attr( $user_id ); ?>">
                      <div class="tb4c-member-storage-package-card-head">
                        <span><?php echo esc_html( $plan['badge'] ?? '' ); ?></span>
                        <?php if ( $is_current ) : ?><em>กำลังใช้งาน</em><?php elseif ( $is_recommend ) : ?><em>แนะนำ</em><?php endif; ?>
                      </div>
                      <h3><?php echo esc_html( $plan['label'] ?? $plan_key ); ?></h3>
                      <strong><?php echo esc_html( $plan_quota ? $format_storage( $plan_quota ) : '-' ); ?></strong>
                      <p><?php echo esc_html( $plan['desc'] ?? '' ); ?></p>
                      <div class="tb4c-member-storage-package-price"><?php echo esc_html( $plan['price'] ?? 'รอระบุราคา' ); ?></div>
                      <?php if ( ! empty( $plan['features'] ) && is_array( $plan['features'] ) ) : ?>
                        <ul>
                          <?php foreach ( $plan['features'] as $feature ) : ?>
                            <li><?php echo tb4cm_icon_markup( 'ph-check-circle', '✓' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $feature ); ?></span></li>
                          <?php endforeach; ?>
                        </ul>
                      <?php endif; ?>
                      <?php if ( $is_current ) : ?>
                        <button type="button" class="tb4c-btn tb4c-btn-secondary is-small" disabled>แพ็กเกจปัจจุบัน</button>
                      <?php else : ?>
                        <a class="tb4c-btn tb4c-btn-primary is-small" href="<?php echo esc_url( $request_url ); ?>" data-payment-ready="future">เลือกแพ็กเกจ</a>
                      <?php endif; ?>
                    </article>
                  <?php endforeach; ?>
                </div>

                <div class="tb4c-member-storage-package-steps">
                  <h3>ขั้นตอนการเพิ่มพื้นที่</h3>
                  <ol>
                    <li><b>เลือกแพ็กเกจ</b><span>เลือกพื้นที่ที่เหมาะกับการใช้งาน</span></li>
                    <li><b>ส่งคำขอ</b><span>ระบบเตรียมข้อมูลแพ็กเกจและบัญชีผู้ใช้ไว้ให้</span></li>
                    <li><b>เปิดใช้งานพื้นที่</b><span>เมื่ออนุมัติแล้ว พื้นที่จะอัปเดตตามแพ็กเกจ</span></li>
                  </ol>
                </div>
              </div>
            <?php endif; ?>

            <?php if ( 'history' === $storage_page ) : ?>
              <div class="tb4c-member-storage-history-page">
                <div class="tb4c-member-storage-usage-top">
                  <div>
                    <h3>ประวัติการใช้พื้นที่</h3>
                    <p>ดูรายการอัปโหลด ลบไฟล์ และการเพิ่มแพ็กเกจพื้นที่ของบัญชีนี้</p>
                  </div>
                  <a class="tb4c-btn tb4c-btn-secondary is-small" href="<?php echo esc_url( add_query_arg( [ 'tb4c_area' => 'usage', 'tb4c_storage_page' => 'packages' ], $area_url ) ); ?>">เพิ่มแพ็กเกจพื้นที่</a>
                </div>
                <div class="tb4c-member-storage-history-table" role="table" aria-label="ประวัติการใช้พื้นที่">
                  <div class="tb4c-member-storage-history-head" role="row">
                    <span role="columnheader">วันที่</span>
                    <span role="columnheader">ประเภทการใช้งาน</span>
                    <span role="columnheader">ขนาดพื้นที่</span>
                    <span role="columnheader">สถานะ</span>
                  </div>
                  <?php foreach ( $usage_history_items as $history_item ) : ?>
                    <div class="tb4c-member-storage-history-row" role="row">
                      <span role="cell"><?php echo esc_html( $history_item['date'] ?? '-' ); ?></span>
                      <strong role="cell"><?php echo esc_html( $history_item['type'] ?? 'อัปเดตพื้นที่' ); ?></strong>
                      <em role="cell"><?php echo esc_html( $format_storage( absint( $history_item['size'] ?? 0 ) ) ); ?></em>
                      <mark role="cell"><?php echo esc_html( $history_item['status'] ?? 'สำเร็จ' ); ?></mark>
                    </div>
                  <?php endforeach; ?>
                </div>
                <p class="tb4c-member-storage-footer-note">Settings | โดย Thinkb4do | ดูรายละเอียด</p>
              </div>
            <?php endif; ?>
          </section>
        <?php endif; ?>

        <?php if ( 'settings' === $tab ) : ?>
          <section class="tb4c-member-area-panel tb4c-member-area-settings-panel">
            <div class="tb4c-member-area-panel-head"><h2>ตั้งค่าโปรไฟล์</h2></div>
            <form id="tb4cMemberAreaProfileForm" class="tb4c-member-area-form" method="post" enctype="multipart/form-data">
              <input type="hidden" name="action" value="tb4cm_save_member_area_profile">
              <input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'tb4c_nonce' ) ); ?>">
              <div id="tb4cMemberAvatarField" class="tb4c-member-avatar-field is-full">
                <div class="tb4c-member-avatar-preview" data-tb4c-member-avatar-preview>
                  <?php if ( $avatar ) : ?><img src="<?php echo esc_url( $avatar ); ?>" alt="" data-tb4c-member-avatar-img><?php else : ?><span><?php echo esc_html( tb4cm_get_initial( $member_display_name ) ); ?></span><?php endif; ?>
                </div>
                <div class="tb4c-member-avatar-control">
                  <strong>รูปโปรไฟล์สมาชิก</strong>
                  <p>รูปนี้จะเชื่อมทุกหน้า: หัวโปรไฟล์, ฟีด, คอมเมนต์, ผู้ติดตาม และพื้นที่ชุมชน</p>
                  <label class="tb4c-member-file-picker tb4c-member-file-picker-avatar">
                    <input type="file" name="member_avatar" accept="image/*" data-tb4c-member-avatar-input data-tb4c-file-name-target="#tb4cMemberAvatarFileName">
                    <span><?php echo tb4cm_icon_markup( 'ph-image-square', '▧' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> เลือกรูปโปรไฟล์</span>
                    <em id="tb4cMemberAvatarFileName" data-tb4c-file-name>ยังไม่ได้เลือกรูป</em>
                  </label>
                  <?php if ( $member_avatar_id ) : ?><label class="tb4c-member-cover-remove tb4c-member-remove-check"><input type="checkbox" name="remove_member_avatar" value="1"><span>ลบรูปโปรไฟล์ปัจจุบัน</span></label><?php endif; ?>
                </div>
              </div>
              <div id="tb4cMemberCoverField" class="tb4c-member-cover-field is-full">
                <div class="tb4c-member-cover-preview" data-tb4c-member-cover-preview <?php echo $member_cover_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><span>Cover</span></div>
                <div class="tb4c-member-cover-control">
                  <strong>ภาพ Cover สมาชิก</strong>
                  <p>อัปโหลดภาพแนวนอนสำหรับหัวโปรไฟล์ แนะนำอัตราส่วนประมาณ 16:5 หรือ 1600×500 px หากยังไม่มี Cover ระบบจะดึงรูปภาพล่าสุดของคุณมาแสดงเป็นพื้นหลังชั่วคราว</p>
                  <label class="tb4c-member-file-picker tb4c-member-file-picker-cover">
                    <input type="file" name="member_cover" accept="image/*" data-tb4c-member-cover-input data-tb4c-file-name-target="#tb4cMemberCoverFileName">
                    <span><?php echo tb4cm_icon_markup( 'ph-panorama', '▭' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> เลือกรูป Cover</span>
                    <em id="tb4cMemberCoverFileName" data-tb4c-file-name>ยังไม่ได้เลือกรูป</em>
                  </label>
                  <?php if ( $member_cover_url ) : ?><label class="tb4c-member-cover-remove tb4c-member-remove-check"><input type="checkbox" name="remove_member_cover" value="1"><span>ลบ Cover ปัจจุบัน</span></label><?php endif; ?>
                </div>
              </div>
              <label><span>ชื่อที่แสดง</span><input name="display_name" value="<?php echo esc_attr( $user->display_name ); ?>" required></label>
              <label><span>ชื่อพื้นที่สมาชิก</span><input name="member_title" value="<?php echo esc_attr( $member_title ); ?>" required></label>
              <label><span>บทบาท/ความถนัด</span><input name="member_role" value="<?php echo esc_attr( $member_role ); ?>" placeholder="เช่น Creator, Designer, Developer"></label>
              <label><span>พื้นที่/จังหวัด</span><input name="member_location" value="<?php echo esc_attr( $member_location ); ?>" placeholder="เช่น กรุงเทพฯ"></label>
              <label><span>ความสนใจ</span><input name="member_interests" value="<?php echo esc_attr( $member_interests ); ?>" placeholder="เช่น คอนเทนต์, เว็บไซต์, ธุรกิจ, ดนตรี"></label>
              <label><span>ลิงก์ติดต่อ</span><input type="url" name="contact_url" value="<?php echo esc_attr( $contact_url ); ?>" placeholder="https://..."></label>
              <label class="is-full"><span>คำแนะนำตัวสั้น</span><textarea name="bio" rows="3"><?php echo esc_textarea( get_user_meta( $user_id, 'description', true ) ); ?></textarea></label>
              <div class="tb4c-member-area-form-actions"><button class="tb4c-btn tb4c-btn-primary" type="submit">บันทึกโปรไฟล์</button><span class="tb4c-form-status" data-tb4c-member-area-status></span></div>
            </form>
          </section>
        <?php endif; ?>

        <?php if ( ! in_array( $tab, [ 'overview', 'posts', 'comments', 'saved', 'following', 'groups', 'market', 'chat', 'products', 'usage', 'settings' ], true ) && isset( $tab_items[ $tab ] ) ) : ?>
          <?php if ( ! empty( $tab_items[ $tab ]['locked'] ) ) : ?>
            <section class="tb4c-member-area-panel tb4c-member-area-locked-feature-panel">
              <div class="tb4c-member-area-locked-feature-icon"><?php echo tb4cm_icon_markup( 'ph-lock-key', '🔒' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
              <h2><?php echo esc_html( $tab_items[ $tab ]['label'] ); ?> ยังไม่ได้เปิดใช้งาน</h2>
              <p><?php echo esc_html( $tab_items[ $tab ]['description'] ?? 'ฟีเจอร์นี้เป็นระบบเสริมสำหรับสมาชิก สามารถซื้อเปิดใช้งานเพิ่มเติมได้ในอนาคต' ); ?></p>
              <a class="tb4c-btn tb4c-btn-primary" href="<?php echo esc_url( $tab_items[ $tab ]['purchase_url'] ); ?>">ดูแพ็กเกจฟีเจอร์</a>
            </section>
          <?php else : ?>
            <?php do_action( 'tb4c_member_area_render_feature_tab', $tab, $user_id, $tab_items[ $tab ] ); ?>
          <?php endif; ?>
        <?php endif; ?>

      </main>
      <?php // v4.2.243: duplicate right summary/sidebar removed for cleaner member page. ?>
    </div>
      </div><!-- /.tb4c-ai-member-center -->
    </div><!-- /.tb4c-ai-member-hub-grid -->
  </div>
  <?php // v4.2.247: Clean Focus removes duplicate side widgets and keeps the member hub lighter. ?>
  <?php tb4cm_render_composer_modal(); ?>
</section>
