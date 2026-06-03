<?php
/**
 * Social network community content partial.
 *
 * @package Thinkb4doCommunity
 */
defined( 'ABSPATH' ) || exit;

$is_logged_in = is_user_logged_in();
$current_user = $is_logged_in ? wp_get_current_user() : null;
$active_topic = isset( $_GET['topic'] ) ? sanitize_key( wp_unslash( $_GET['topic'] ) ) : 'all';
$search_query = isset( $_GET['tb4c_search'] ) ? sanitize_text_field( wp_unslash( $_GET['tb4c_search'] ) ) : '';

$tabs = [
    // v4.2.200: keep the left position but reduce navigation to primary filters only.
    'all'       => [ 'label' => 'วันนี้', 'icon' => 'ph-sparkle', 'symbol' => '✦' ],
    'latest'    => [ 'label' => 'ล่าสุด', 'icon' => 'ph-clock', 'symbol' => '◷' ],
    'popular'   => [ 'label' => 'มาแรง', 'icon' => 'ph-trend-up', 'symbol' => '↗' ],
];


$tb4c_feed_per_page = 6;
$tb4c_feed_paged    = 1;
$query_args         = function_exists( 'tb4cf_get_community_feed_query_args' )
    ? tb4cf_get_community_feed_query_args( $active_topic, $search_query, $tb4c_feed_paged, $tb4c_feed_per_page )
    : [
        'post_type'           => 'tb4_community_post',
        'posts_per_page'      => $tb4c_feed_per_page,
        'paged'               => $tb4c_feed_paged,
        'post_status'         => 'publish',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => false,
        'orderby'             => 'date',
        'order'               => 'DESC',
    ];

$feed = new WP_Query( $query_args );
// v4.2.200: removed unused header/member/suggested-count queries to keep the main feed lighter.
$trending_terms = get_terms( [
    'taxonomy'   => 'tb4_community_topic',
    'hide_empty' => false,
    'number'     => 4,
    'orderby'    => 'count',
    'order'      => 'DESC',
] );

$current_avatar = ( $is_logged_in && $current_user ) ? get_avatar_url( $current_user->ID, [ 'size' => 44 ] ) : '';
$reel_items = [];
$rail_reel_items = [];
$rail_reel_query = new WP_Query( [
    'post_type'           => 'tb4_community_post',
    'posts_per_page'      => 5,
    'post_status'         => 'publish',
    'ignore_sticky_posts' => true,
    'no_found_rows'       => true,
    'update_post_meta_cache' => true,
    'update_post_term_cache' => false,
    'orderby'             => 'date',
    'order'               => 'DESC',
] );

if ( $rail_reel_query->have_posts() ) {
    while ( $rail_reel_query->have_posts() ) {
        $rail_reel_query->the_post();
        $rail_post_id      = get_the_ID();
        $rail_author_id    = (int) get_post_field( 'post_author', $rail_post_id );
        $rail_author_name  = get_the_author_meta( 'display_name', $rail_author_id ) ?: get_the_author();
        $rail_author_avatar = get_avatar_url( $rail_author_id, [ 'size' => 56 ] );
        $rail_raw_text     = wp_strip_all_tags( get_the_content() );
        $rail_album        = tb4cf_get_post_media_album( $rail_post_id, $rail_raw_text );
        $rail_cover_url    = has_post_thumbnail( $rail_post_id ) ? get_the_post_thumbnail_url( $rail_post_id, 'medium_large' ) : '';

        if ( empty( $rail_album ) && $rail_cover_url ) {
            $rail_album[] = [
                'type' => 'image',
                'url'  => $rail_cover_url,
            ];
        }

        if ( ! $rail_cover_url && ! empty( $rail_album[0] ) && is_array( $rail_album[0] ) ) {
            if ( 'image' === ( $rail_album[0]['type'] ?? '' ) ) {
                $rail_cover_url = $rail_album[0]['url'] ?? '';
            } elseif ( function_exists( 'tb4cf_get_video_poster_url' ) ) {
                $rail_cover_url = tb4cf_get_video_poster_url( $rail_album[0] );
            }
        }

        if ( empty( $rail_album ) && ! $rail_cover_url ) {
            continue;
        }

        $rail_type           = get_post_meta( $rail_post_id, 'tb4_post_type', true ) ?: 'discussion';
        $rail_visibility     = get_post_meta( $rail_post_id, 'tb4_post_visibility', true ) ?: 'public';
        $rail_terms          = get_the_terms( $rail_post_id, 'tb4_community_topic' );
        $rail_tag_terms      = get_the_terms( $rail_post_id, 'tb4_community_tag' );
        $rail_caption_source = get_the_excerpt() ?: $rail_raw_text;
        $rail_caption_text   = tb4cf_media_visible_text( $rail_caption_source, ! empty( $rail_album[0] ) ? $rail_album[0] : null );
        $rail_caption_text   = wp_trim_words( wp_strip_all_tags( $rail_caption_text ), 34 );

        $rail_reel_items[] = [
            'post_id'       => $rail_post_id,
            'author_id'     => $rail_author_id,
            'author_name'   => $rail_author_name,
            'author_url'    => tb4cf_social_profile_url( $rail_author_id ),
            'author_avatar' => $rail_author_avatar,
            'title'         => get_the_title(),
            'caption'       => $rail_caption_text,
            'permalink'     => tb4cf_get_post_link( get_the_ID() ),
            'likes'         => (int) get_post_meta( $rail_post_id, 'tb4_like_count', true ),
            'comments'      => (int) get_comments_number(),
            'topic_label'   => tb4cf_topic_label( $rail_type ),
            'visibility'    => function_exists( 'tb4cf_visibility_label' ) ? tb4cf_visibility_label( $rail_visibility ) : ( 'groups' === $rail_visibility ? 'เฉพาะกลุ่ม' : 'สาธารณะ' ),
            'time_ago'      => human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) . 'ที่แล้ว',
            'media'         => $rail_album,
            'media_count'   => count( $rail_album ),
            'cover'         => $rail_cover_url,
            'topic_terms'   => ! empty( $rail_terms ) && ! is_wp_error( $rail_terms ) ? wp_list_pluck( array_slice( $rail_terms, 0, 4 ), 'name' ) : [],
            'tag_terms'     => ! empty( $rail_tag_terms ) && ! is_wp_error( $rail_tag_terms ) ? wp_list_pluck( array_slice( $rail_tag_terms, 0, 6 ), 'name' ) : [],
        ];
    }
    wp_reset_postdata();
}
$reel_items = $rail_reel_items;
$reel_item_ids = ! empty( $reel_items ) ? array_map( 'intval', wp_list_pluck( $reel_items, 'post_id' ) ) : [];
?>

<style id="tb4c-main-no-sidebar-42231-critical">
:root{--tb4c-feed-main-width:760px;}
#tb4c-community-app.tb4c-v42231-main-no-sidebar{width:100vw;max-width:100vw;margin-left:calc(50% - 50vw);margin-right:calc(50% - 50vw);box-sizing:border-box;background:#F7FAF8;color:#111111;overflow-x:hidden;padding:8px clamp(12px,2.3vw,36px) 42px;}
#tb4c-community-app.tb4c-v42231-main-no-sidebar *{box-sizing:border-box;}
#tb4c-community-app.tb4c-v42231-main-no-sidebar .tb4c-module-grid{width:min(100%,760px);margin:0 auto;display:grid;grid-template-columns:minmax(0,var(--tb4c-feed-main-width,760px));gap:0;align-items:start;justify-content:center;}
#tb4c-community-app.tb4c-v42231-main-no-sidebar .tb4c-social-feed{min-width:0;max-width:var(--tb4c-feed-main-width,760px);width:100%;display:grid;gap:10px;}
#tb4c-community-app.tb4c-v42231-main-no-sidebar .tb4c-social-left,#tb4c-community-app.tb4c-v42231-main-no-sidebar .tb4c-social-right{display:none!important;}
@media(max-width:860px){#tb4c-community-app.tb4c-v42231-main-no-sidebar{width:100%;max-width:100%;margin:0;padding:7px 9px 30px;}#tb4c-community-app.tb4c-v42231-main-no-sidebar .tb4c-module-grid{display:block;width:100%;max-width:var(--tb4c-feed-main-width,760px);}}
</style>

<style id="tb4c-click-layout-stable-4238-critical">
/* v4.2.238: Critical click/tap layout lock. Keeps feed cards from collapsing after poll/vote/action clicks. */
#tb4c-community-app[data-tb4c-layout*="42238"],
#tb4c-community-app[data-tb4c-layout*="42238"] *{box-sizing:border-box!important;writing-mode:horizontal-tb!important;text-orientation:mixed!important;}
#tb4c-community-app[data-tb4c-layout*="42238"]{width:100%!important;max-width:100%!important;min-width:0!important;overflow-x:hidden!important;}
#tb4c-community-app[data-tb4c-layout*="42238"] :where(.tb4c-container,.tb4c-module-grid,.tb4c-social-feed,.tb4c-feed-list){width:100%!important;max-width:var(--tb4c-feed-main-width,760px)!important;min-width:0!important;margin-left:auto!important;margin-right:auto!important;display:block!important;grid-template-columns:none!important;}
#tb4c-community-app[data-tb4c-layout*="42238"] :where(.tb4c-post-card,.tb4c-social-post-card,.tb4c-native-post,.tb4c-ig-post){display:block!important;position:relative!important;width:100%!important;max-width:100%!important;min-width:0!important;float:none!important;clear:both!important;grid-column:1 / -1!important;overflow:hidden!important;}
#tb4c-community-app[data-tb4c-layout*="42238"] :where(.tb4c-post-inline-text,.tb4c-post-type-tag-strip,.tb4c-type-tag-strip,.tb4c-ig-custom-tags,.tb4c-tags,.tb4c-poll-card,.tb4c-post-actions,.tb4c-social-actions,.tb4c-ig-action-row){width:100%!important;max-width:100%!important;min-width:0!important;float:none!important;clear:both!important;grid-column:1 / -1!important;}
#tb4c-community-app[data-tb4c-layout*="42238"] .tb4c-post-inline-text{display:block!important;text-decoration:none!important;color:inherit!important;overflow-wrap:break-word!important;word-break:normal!important;}
#tb4c-community-app[data-tb4c-layout*="42238"] .tb4c-post-inline-text :where(h1,h2,h3,p,span,strong){max-width:100%!important;white-space:normal!important;overflow-wrap:break-word!important;word-break:normal!important;}
#tb4c-community-app[data-tb4c-layout*="42238"] .tb4c-poll-card{display:block!important;padding:12px!important;margin:12px 0!important;border-radius:18px!important;background:#fff!important;border:1px solid rgba(30,107,69,.12)!important;box-shadow:none!important;}
#tb4c-community-app[data-tb4c-layout*="42238"] .tb4c-poll-options{display:grid!important;grid-template-columns:1fr!important;gap:8px!important;width:100%!important;min-width:0!important;}
#tb4c-community-app[data-tb4c-layout*="42238"] .tb4c-poll-option{position:relative!important;display:grid!important;grid-template-columns:minmax(0,1fr) auto!important;align-items:center!important;gap:10px!important;width:100%!important;min-width:0!important;min-height:44px!important;padding:10px 12px!important;border-radius:14px!important;overflow:hidden!important;text-align:left!important;white-space:normal!important;}
#tb4c-community-app[data-tb4c-layout*="42238"] .tb4c-poll-option i{position:absolute!important;inset:0 auto 0 0!important;z-index:0!important;height:100%!important;display:block!important;pointer-events:none!important;background:rgba(30,107,69,.10)!important;}
#tb4c-community-app[data-tb4c-layout*="42238"] .tb4c-poll-option :where(span,em){position:relative!important;z-index:1!important;min-width:0!important;white-space:normal!important;overflow-wrap:break-word!important;word-break:normal!important;}
#tb4c-community-app[data-tb4c-layout*="42238"] .tb4c-poll-option em{justify-self:end!important;white-space:nowrap!important;font-style:normal!important;font-weight:900!important;color:#1E6B45!important;}
#tb4c-community-app[data-tb4c-layout*="42238"] .tb4c-post-actions{display:grid!important;grid-template-columns:repeat(5,minmax(42px,1fr))!important;align-items:center!important;gap:0!important;overflow:hidden!important;}
#tb4c-community-app[data-tb4c-layout*="42238"] .tb4c-post-actions :where(button,a,span){min-width:0!important;max-width:100%!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;}
@media(max-width:767px){#tb4c-community-app[data-tb4c-layout*="42238"]{padding-left:0!important;padding-right:0!important;}#tb4c-community-app[data-tb4c-layout*="42238"] :where(.tb4c-container,.tb4c-module-grid,.tb4c-social-feed,.tb4c-feed-list){max-width:100%!important;padding-left:0!important;padding-right:0!important;}#tb4c-community-app[data-tb4c-layout*="42238"] .tb4c-post-card{border-left:0!important;border-right:0!important;border-radius:0!important;}#tb4c-community-app[data-tb4c-layout*="42238"] .tb4c-post-actions{grid-template-columns:repeat(5,minmax(36px,1fr))!important;}#tb4c-community-app[data-tb4c-layout*="42238"] .tb4c-post-actions em{display:none!important;}}
</style>
<section class="tb4c-shell tb4c-community-modular tb4c-social-shell tb4c-app-shell tb4c-native-shell tb4c-native-feed-page tb4c-v42231-main-no-sidebar" id="tb4c-community-app" data-tb4c-layout="main-no-sidebar-v42238-click-layout-stable" data-ui-version="4.2.266" data-tb4c-size-lock="true">



  <div class="tb4c-container tb4c-social-layout tb4c-app-layout tb4c-module-grid" data-tb4c-layout-controller="center-only">
    <?php
      // v4.2.231: Main Community renders center feed only.
      // Left / Right sidebars were split into separate desktop-only plugins.
      $tb4c_center_module = tb4cf_get_module_file( 'center-feed' );
      if ( $tb4c_center_module && file_exists( $tb4c_center_module ) ) {
          include $tb4c_center_module;
      }
    ?>
  </div>

  <?php // v4.2.200: lower mobile menu removed; top/header navigation remains canonical. ?>
  <?php // v4.2.200: floating create button removed; Composer is the single create entry. ?>


  <div class="tb4c-reel-viewer" id="tb4cReelViewer" aria-hidden="true">
      <div class="tb4c-reel-backdrop" data-tb4c-close-reels></div>
      <div class="tb4c-reel-panel" role="dialog" aria-modal="true" aria-label="Thinkb4do Reels">
        <div class="tb4c-reel-topbar">
          <div class="tb4c-reel-brand">
            <span class="tb4c-reel-brand-mark">T</span>
            <div>
              <strong>Thinkb4do Reels</strong>
              <small>คลิปสั้นสไตล์ชุมชนของเรา</small>
            </div>
          </div>
          <button type="button" class="tb4c-reel-close" data-tb4c-close-reels aria-label="ปิดรีล">×</button>
        </div>
        <button type="button" class="tb4c-reel-nav tb4c-reel-nav-prev" data-tb4c-reel-prev aria-label="Reel ก่อนหน้า">‹</button>
        <button type="button" class="tb4c-reel-nav tb4c-reel-nav-next" data-tb4c-reel-next aria-label="Reel ถัดไป">›</button>
        <div class="tb4c-reel-stage" data-tb4c-reel-stage>
          <?php if ( empty( $reel_items ) ) : ?>
            <article class="tb4c-reel-slide tb4c-reel-empty-slide" data-tb4c-reel-index="0">
              <div class="tb4c-reel-phone">
                <div class="tb4c-reel-phone-media tb4c-reel-empty-card">
                  <div class="tb4c-reel-shade"></div>
                  <div class="tb4c-reel-content">
                    <div class="tb4c-reel-text">
                      <h3><?php esc_html_e( 'ยังไม่มี Reels', 'thinkb4do-community' ); ?></h3>
                      <p><?php esc_html_e( 'เมื่อมีโพสต์รูปหรือวิดีโอ ระบบจะแสดงเป็น Reels ให้อัตโนมัติ', 'thinkb4do-community' ); ?></p>
                    </div>
                  </div>
                  <div class="tb4c-reel-side">
                    <button type="button" data-tb4c-open-composer aria-label="สร้างโพสต์">
                      <i class="ph ph-plus"></i><span><?php esc_html_e( 'โพสต์', 'thinkb4do-community' ); ?></span>
                    </button>
                  </div>
                </div>
              </div>
            </article>
          <?php endif; ?>
          <?php foreach ( $reel_items as $index => $reel ) : ?>
            <?php
              $reel_media = ! empty( $reel['media'][0] ) ? $reel['media'][0] : null;
              if ( ! is_array( $reel_media ) || empty( $reel_media['url'] ) ) {
                  continue;
              }
              $reel_media_type = sanitize_key( $reel_media['type'] ?? '' );
              $reel_media_url  = esc_url( $reel_media['url'] );
              $reel_tags       = [];
              foreach ( array_merge( $reel['topic_terms'], $reel['tag_terms'] ) as $reel_tag_name ) {
                  if ( ! $reel_tag_name ) {
                      continue;
                  }
                  $reel_tags[] = '#' . sanitize_text_field( $reel_tag_name );
              }
            ?>
            <article class="tb4c-reel-slide" data-tb4c-reel-post-id="<?php echo esc_attr( $reel['post_id'] ); ?>" data-tb4c-reel-index="<?php echo esc_attr( $index ); ?>">
              <div class="tb4c-reel-phone">
                <div class="tb4c-reel-phone-media">
                  <?php if ( 'image' === $reel_media_type ) : ?>
                    <img src="<?php echo $reel_media_url; ?>" alt="<?php echo esc_attr( $reel['title'] ); ?>" loading="lazy" decoding="async">
                  <?php elseif ( 'video' === $reel_media_type ) : ?>
                    <?php if ( preg_match( '~\.(mp4|m4v|webm|ogv|ogg)(\?.*)?$~i', $reel_media_url ) ) : ?>
                      <video class="tb4c-reel-video" playsinline preload="metadata" loop muted poster="<?php echo esc_url( tb4cf_get_video_poster_url( $reel_media ) ); ?>">
                        <source src="<?php echo $reel_media_url; ?>">
                      </video>
                      <button type="button" class="tb4c-reel-play-toggle" data-tb4c-reel-toggle-play aria-label="เล่นหรือหยุดวิดีโอ">
                        <span class="tb4c-reel-play-icon">▶</span>
                      </button>
                    <?php else : ?>
                      <div class="tb4c-reel-embed"><?php echo wp_oembed_get( $reel_media['url'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
                    <?php endif; ?>
                  <?php endif; ?>
                  <div class="tb4c-reel-shade"></div>
                  <div class="tb4c-reel-badges">
                    <span>Thinkb4do Reel</span>
                    <span><?php echo esc_html( $reel['topic_label'] ); ?></span>
                    <?php if ( ! empty( $reel['media_count'] ) && (int) $reel['media_count'] > 1 ) : ?><span><?php echo esc_html( (int) $reel['media_count'] ); ?> สื่อ</span><?php endif; ?>
                  </div>
                  <div class="tb4c-reel-content">
                    <div class="tb4c-reel-author-row">
                      <a class="tb4c-reel-author tb4c-member-link" href="<?php echo esc_url( $reel['author_url'] ); ?>" data-tb4c-member-link="<?php echo esc_attr( $reel['author_id'] ); ?>">
                        <span class="tb4c-reel-author-avatar">
                          <?php if ( ! empty( $reel['author_avatar'] ) ) : ?><img src="<?php echo esc_url( $reel['author_avatar'] ); ?>" alt=""><?php else : ?><?php echo esc_html( tb4cf_get_initial( $reel['author_name'] ) ); ?><?php endif; ?>
                        </span>
                        <span class="tb4c-reel-author-copy">
                          <strong><?php echo esc_html( $reel['author_name'] ); ?></strong>
                          <small><?php echo esc_html( $reel['time_ago'] ); ?> · <?php echo esc_html( $reel['visibility'] ); ?></small>
                        </span>
                      </a>
                    </div>
                    <div class="tb4c-reel-text">
                      <h3><?php echo esc_html( $reel['title'] ); ?></h3>
                      <?php if ( ! empty( $reel['caption'] ) ) : ?><p><?php echo esc_html( $reel['caption'] ); ?></p><?php endif; ?>
                      <?php if ( ! empty( $reel_tags ) ) : ?><div class="tb4c-reel-tags"><?php echo esc_html( implode( ' ', array_slice( $reel_tags, 0, 6 ) ) ); ?></div><?php endif; ?>
                    </div>
                  </div>
                  <div class="tb4c-reel-side">
                    <button type="button" data-tb4c-react="like" data-post-id="<?php echo esc_attr( $reel['post_id'] ); ?>" aria-label="ถูกใจ">
                      <i class="ph ph-heart"></i><span><?php echo esc_html( number_format_i18n( (int) $reel['likes'] ) ); ?></span>
                    </button>
                    <a href="<?php echo esc_url( tb4cf_get_comment_link( $reel['post_id'] ) ); ?>" aria-label="ความคิดเห็น">
                      <i class="ph ph-chat-circle"></i><span><?php echo esc_html( number_format_i18n( (int) $reel['comments'] ) ); ?></span>
                    </a>
                    <button type="button" data-tb4c-copy-link="<?php echo esc_url( $reel['permalink'] ); ?>" aria-label="แชร์">
                      <i class="ph ph-paper-plane-tilt"></i><span>แชร์</span>
                    </button>
                    <a href="<?php echo esc_url( $reel['permalink'] ); ?>" aria-label="เปิดโพสต์">
                      <i class="ph ph-arrow-up-right"></i><span>โพสต์</span>
                    </a>
                  </div>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </div>


  <?php tb4cf_render_composer_modal(); ?>

</section>
