<?php
/**
 * Template: Single Community Post
 *
 * @package Thinkb4doCommunity
 */
defined( 'ABSPATH' ) || exit;
get_header();

$post_id = get_the_ID();
$author_id = (int) get_post_field( 'post_author', $post_id );
$author_name = get_the_author_meta( 'display_name', $author_id ) ?: get_the_author();
$type = get_post_meta( $post_id, 'tb4_post_type', true ) ?: 'discussion';
$visibility = get_post_meta( $post_id, 'tb4_post_visibility', true ) ?: 'public';
$likes = (int) get_post_meta( $post_id, 'tb4_like_count', true );
$bookmarks = (int) get_post_meta( $post_id, 'tb4_bookmark_count', true );
$views = (int) get_post_meta( $post_id, 'tb4_view_count', true );
update_post_meta( $post_id, 'tb4_view_count', $views + 1 );
$terms = get_the_terms( $post_id, 'tb4_community_topic' );
$tag_terms = get_the_terms( $post_id, 'tb4_community_tag' );
$post_feeling = get_post_meta( $post_id, 'tb4_post_feeling', true );
$author_avatar = get_avatar_url( $author_id, [ 'size' => 96 ] );
$post_raw_text = wp_strip_all_tags( get_post_field( 'post_content', $post_id ) );
$post_media_album = tb4cm_get_post_media_album( $post_id, $post_raw_text );
$single_card_classes = 'tb4c-post-card tb4c-social-post-card tb4c-single-post-card tb4c-ig-single-post tb4c-native-single-post tb4c-single-wide-post-card tb4c-v42220-reference-comment-card ' . ( has_post_thumbnail( $post_id ) || ! empty( $post_media_album ) ? 'tb4c-single-has-media' : 'tb4c-single-text-post' );
?>
<main class="tb4c-shell tb4c-social-shell tb4c-single-social-page tb4c-ig-shell tb4c-ig-single-page tb4c-ig-closer-page tb4c-native-shell tb4c-native-single-page tb4c-think-social-page tb4c-korea-social-page tb4c-kr-own-page tb4c-clean-social-page tb4c-dribbble-ref-page tb4c-webapp-page tb4c-simple-social-page tb4c-web-layout-fit-page tb4c-modern-refresh-fit-page tb4c-v42220-reference-comment-page">
  <div class="tb4c-container tb4c-single-layout">
    <article class="<?php echo esc_attr( $single_card_classes ); ?>">
      <a class="tb4c-back-link" href="<?php echo esc_url( home_url( '/community/' ) ); ?>"><?php echo tb4cm_icon_markup( 'ph-arrow-left', '‹' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span>กลับฟีด</span></a>
      <?php // v4.2.202: duplicate single-post shortcut toolbar removed; action row below is the canonical action area. ?>

      <div class="tb4c-post-head tb4c-single-post-head">
        <a class="tb4c-avatar" href="<?php echo esc_url( tb4cm_social_profile_url( $author_id ) ); ?>">
          <?php if ( $author_avatar ) : ?><img src="<?php echo esc_url( $author_avatar ); ?>" alt=""><?php else : ?><?php echo esc_html( tb4cm_get_initial( $author_name ) ); ?><?php endif; ?>
        </a>
        <div class="tb4c-post-meta">
          <div class="tb4c-author-row">
            <a href="<?php echo esc_url( tb4cm_social_profile_url( $author_id ) ); ?>"><strong><?php echo esc_html( $author_name ); ?></strong></a>
            <span class="tb4c-dot">•</span>
            <span><?php echo esc_html( human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) ); ?>ที่แล้ว</span>
          </div>
          <div class="tb4c-author-audience-row tb4c-single-audience-row" aria-label="การมองเห็นโพสต์">
            <?php echo tb4cm_icon_markup( function_exists( 'tb4cm_visibility_icon_class' ) ? tb4cm_visibility_icon_class( $visibility ) : ( 'members' === $visibility ? 'ph-lock-key' : 'ph-globe-hemisphere-east' ), 'groups' === $visibility ? '👥' : ( 'members' === $visibility ? '🔒' : '🌐' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <span><?php echo esc_html( function_exists( 'tb4cm_visibility_label' ) ? tb4cm_visibility_label( $visibility ) : ( 'members' === $visibility ? 'เฉพาะสมาชิก' : ( 'groups' === $visibility ? 'เฉพาะกลุ่ม' : 'สาธารณะ' ) ) ); ?></span>
          </div>
        </div>
        <?php if ( current_user_can( 'edit_post', $post_id ) || current_user_can( 'delete_post', $post_id ) ) : ?>
          <div class="tb4c-feed-owner-tools tb4c-feed-owner-tools-inline tb4c-single-owner-tools-inline tb4c-v42229-post-owner-slot tb4c-v42230-post-owner-right" aria-label="จัดการโพสต์">
            <?php if ( current_user_can( 'edit_post', $post_id ) ) : ?>
              <a class="tb4c-owner-icon-btn" href="<?php echo esc_url( get_edit_post_link( $post_id ) ); ?>" aria-label="แก้ไขโพสต์" title="แก้ไขโพสต์"><?php echo tb4cm_icon_markup( 'ph-pencil-simple-line', '✎' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span>แก้ไข</span></a>
            <?php endif; ?>
            <?php if ( current_user_can( 'delete_post', $post_id ) ) : ?>
              <button type="button" aria-label="ลบโพสต์" title="ลบโพสต์" data-tb4c-delete-post="<?php echo esc_attr( $post_id ); ?>"><?php echo tb4cm_icon_markup( 'ph-trash', '×' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span>ลบ</span></button>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>

      <?php if ( has_post_thumbnail() ) : ?>
        <div class="tb4c-ig-single-media-column">
          <button type="button" class="tb4c-thumb tb4c-single-media tb4c-ig-media tb4c-featured-lightbox-trigger" data-tb4c-lightbox data-tb4c-lightbox-type="image" data-tb4c-lightbox-src="<?php echo esc_url( get_the_post_thumbnail_url( $post_id, 'large' ) ); ?>" data-tb4c-lightbox-title="<?php echo esc_attr( get_the_title() ); ?>"><?php the_post_thumbnail( 'large' ); ?></button>
        </div>
      <?php elseif ( ! empty( $post_media_album ) ) : ?>
        <div class="tb4c-ig-single-media-column tb4c-single-album-column">
          <?php echo tb4cm_render_post_media_album( $post_media_album, get_the_title() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
      <?php endif; ?>

      <div class="tb4c-ig-single-detail-column">
        <h1 class="tb4c-single-title"><?php the_title(); ?></h1>
        <div class="tb4c-single-content">
          <?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?>
        </div>

        <?php echo function_exists( 'tb4cm_render_post_poll' ) ? tb4cm_render_post_poll( $post_id ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
      <?php echo function_exists( 'tb4cm_render_post_type_tag_strip' ) ? tb4cm_render_post_type_tag_strip( $post_id, $type, 8 ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

      <div class="tb4c-reaction-summary tb4c-legacy-post-summary" aria-label="สรุปการตอบสนอง" hidden>
        <span class="tb4c-reaction-faces"><span class="tb4c-reaction-face">👍</span><span class="tb4c-reaction-face">💚</span><span class="tb4c-reaction-face">✨</span></span>
        <span><?php echo esc_html( number_format_i18n( max( 0, $likes ) ) ); ?> คนถูกใจ · <?php echo esc_html( get_comments_number() ); ?> ความคิดเห็น</span>
      </div>

      <div class="tb4c-post-actions tb4c-social-actions tb4c-single-actions tb4c-ig-actions tb4c-post-actions-balanced">
        <button type="button" data-tb4c-react="like" data-post-id="<?php echo esc_attr( $post_id ); ?>" aria-label="ถูกใจ"><i class="ph ph-heart"></i><span><?php echo esc_html( $likes ); ?></span></button>
        <a href="#comments" aria-label="ความคิดเห็น"><i class="ph ph-chat-circle"></i><span><?php echo esc_html( get_comments_number() ); ?></span></a>
        <button type="button" data-tb4c-copy-link="<?php echo esc_url( get_permalink() ); ?>" aria-label="แชร์"><i class="ph ph-share-network"></i><span>แชร์</span></button>
        <button type="button" data-tb4c-react="bookmark" data-post-id="<?php echo esc_attr( $post_id ); ?>" aria-label="บันทึก"><i class="ph ph-bookmark-simple"></i><span><?php echo esc_html( $bookmarks ); ?></span></button>
        <span class="tb4c-action-view-chip" aria-label="ยอดเข้าชม"><i class="ph ph-eye"></i><span><?php echo esc_html( $views + 1 ); ?></span></span>
      </div>

      <?php include TB4CM_DIR . 'templates/comments-community.php'; ?>
      </div>
    </article>

  </div>
</main>
<?php // v4.2.202: lower mobile menu removed from active UI. ?>
<?php tb4cm_render_composer_modal(); ?>
<?php get_footer();
