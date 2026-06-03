<?php
/**
 * Thinkb4do Community custom comments partial.
 * v4.2.226: Final flat comments; no nested card wrapper and no dotted composer line.
 *
 * @package Thinkb4doCommunity
 */
defined( 'ABSPATH' ) || exit;

$post_id       = get_the_ID();
$comment_count = tb4cm_get_approved_comment_count( $post_id );
$current_user  = is_user_logged_in() ? wp_get_current_user() : null;
$avatar_url    = $current_user ? get_avatar_url( $current_user->ID, [ 'size' => 44 ] ) : '';
$comment_tree  = tb4cm_get_comment_tree( $post_id, 80 );
$top_comments  = isset( $comment_tree[0] ) && is_array( $comment_tree[0] ) ? $comment_tree[0] : [];
?>
<section class="tb4c-comments-inline tb4c-selectable-comments tb4c-comment-layout-fit tb4c-comments-feed-tone" id="comments" aria-label="ความคิดเห็น" data-tb4c-comments-root data-post-id="<?php echo esc_attr( $post_id ); ?>">
  <header class="tb4c-comments-feed-head">
    <div class="tb4c-comments-feed-title">
      <?php echo tb4cm_icon_markup( 'ph-chat-circle-text', '💬' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
      <strong>ความคิดเห็น</strong>
    </div>
    <span class="tb4c-comments-feed-count" data-tb4c-comment-count><?php echo esc_html( number_format_i18n( (int) $comment_count ) ); ?> ความคิดเห็น</span>
  </header>

  <div class="tb4c-comment-feed-list-wrap" data-tb4c-comment-scroll>
    <ol class="tb4c-comment-list tb4c-comment-feed-list" data-tb4c-comment-list <?php echo empty( $top_comments ) ? 'hidden' : ''; ?>>
      <?php foreach ( $top_comments as $comment ) : ?>
        <?php echo tb4cm_render_comment_item( $comment, $comment_tree, 0 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
      <?php endforeach; ?>
    </ol>

    <div class="tb4c-comments-empty tb4c-comments-feed-empty tb4c-comments-flat-empty" data-tb4c-comments-empty <?php echo empty( $top_comments ) ? '' : 'hidden'; ?>>
      <div>💬</div>
      <strong>ยังไม่มีความคิดเห็น</strong>
      <span>เป็นคนแรกที่แสดงความคิดเห็นสิ</span>
    </div>
  </div>

  <?php if ( comments_open( $post_id ) ) : ?>
    <?php if ( is_user_logged_in() ) : ?>
      <form class="tb4c-comment-inline-form tb4c-comment-feed-form" id="tb4cCommentForm" data-tb4c-comment-form>
        <input type="hidden" name="action" value="tb4c_submit_comment">
        <input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'tb4c_nonce' ) ); ?>">
        <input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">
        <input type="hidden" name="parent_id" value="0" data-tb4c-comment-parent>

        <div class="tb4c-reply-context" data-tb4c-reply-context hidden>
          <span data-tb4c-reply-label>กำลังตอบกลับ</span>
          <button type="button" data-tb4c-comment-action="cancel-reply" aria-label="ยกเลิกการตอบกลับ">×</button>
        </div>

        <div class="tb4c-comment-input-row tb4c-comment-feed-input-row">
          <span class="tb4c-avatar tb4c-avatar-mini tb4c-comment-current-avatar">
            <?php if ( $avatar_url ) : ?>
              <img src="<?php echo esc_url( $avatar_url ); ?>" alt="">
            <?php else : ?>
              💬
            <?php endif; ?>
          </span>
          <label class="screen-reader-text" for="tb4cCommentText">ความคิดเห็น</label>
          <textarea id="tb4cCommentText" name="content" rows="1" maxlength="1200" placeholder="เขียนความคิดเห็น..." data-tb4c-comment-textarea required></textarea>
          <button type="submit" class="tb4c-comment-send" aria-label="ส่งความคิดเห็น"><svg class="tb4c-comment-send-svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 19V5"></path><path d="M5.5 11.5 12 5l6.5 6.5"></path></svg><span>ส่ง</span></button>
        </div>
        <p class="tb4c-form-status" data-tb4c-comment-status aria-live="polite"></p>
      </form>
    <?php else : ?>
      <a class="tb4c-comment-login-card tb4c-comment-fit-login tb4c-comment-feed-login" href="<?php echo esc_url( wp_login_url( get_permalink( $post_id ) . '#comments' ) ); ?>">
        <span class="tb4c-avatar tb4c-avatar-mini">💬</span>
        <span>เข้าสู่ระบบเพื่อแสดงความคิดเห็น</span>
        <?php echo tb4cm_icon_markup( 'ph-arrow-right', '›' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
      </a>
    <?php endif; ?>
  <?php else : ?>
    <div class="tb4c-comments-closed">ปิดรับความคิดเห็นสำหรับโพสต์นี้แล้ว</div>
  <?php endif; ?>
</section>
