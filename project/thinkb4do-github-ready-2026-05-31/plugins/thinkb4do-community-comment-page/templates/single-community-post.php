<?php
/**
 * Template: Single Community Post
 * v1.0.2 — restore standard reply/edit/delete icons and Enter-to-send.
 *
 * @package Thinkb4doCommunity
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<?php /* CSS loaded by Thinkb4do Community Comment Page plugin. */ ?>

<?php
if ( have_posts() ) :
    while ( have_posts() ) :
        the_post();
        $post_id       = get_the_ID();
        $author_id     = (int) get_post_field( 'post_author', $post_id );
        $author_name   = get_the_author_meta( 'display_name', $author_id ) ?: get_the_author();
        $author_avatar = get_avatar_url( $author_id, [ 'size' => 96 ] );
        $type          = get_post_meta( $post_id, 'tb4_post_type', true ) ?: 'discussion';
        $visibility    = get_post_meta( $post_id, 'tb4_post_visibility', true ) ?: 'public';
        $likes         = (int) get_post_meta( $post_id, 'tb4_like_count', true );
        $bookmarks     = (int) get_post_meta( $post_id, 'tb4_bookmark_count', true );
        $views         = (int) get_post_meta( $post_id, 'tb4_view_count', true );
        $next_views    = $views + 1;
        $post_raw_text = wp_strip_all_tags( get_post_field( 'post_content', $post_id ) );
        $post_media_album = tb4ccp_get_post_media_album( $post_id, $post_raw_text );
        if ( empty( $post_media_album ) && has_post_thumbnail( $post_id ) ) {
            $post_media_album[] = [
                'type' => 'image',
                'url'  => get_the_post_thumbnail_url( $post_id, 'large' ),
            ];
        }
        $post_media            = ! empty( $post_media_album[0] ) ? $post_media_album[0] : null;
        $post_media_url        = is_array( $post_media ) && ! empty( $post_media['url'] ) ? $post_media['url'] : '';
        $post_media_album_urls = ! empty( $post_media_album ) ? implode( "\n", wp_list_pluck( $post_media_album, 'url' ) ) : '';
        $tag_input_value       = function_exists( 'tb4ccp_get_post_tag_input_value' ) ? tb4ccp_get_post_tag_input_value( $post_id ) : '';
        $post_feeling          = get_post_meta( $post_id, 'tb4_post_feeling', true );
        $post_poll_question    = get_post_meta( $post_id, 'tb4_poll_question', true );
        $post_poll_options     = get_post_meta( $post_id, 'tb4_poll_options', true );
        $post_poll_options_value = is_array( $post_poll_options ) ? implode( "\n", $post_poll_options ) : '';
        $comment_count         = function_exists( 'tb4ccp_get_approved_comment_count' ) ? tb4ccp_get_approved_comment_count( $post_id ) : get_comments_number( $post_id );
        $comment_tree          = function_exists( 'tb4ccp_get_comment_tree' ) ? tb4ccp_get_comment_tree( $post_id, 80 ) : [];
        $top_comments          = isset( $comment_tree[0] ) && is_array( $comment_tree[0] ) ? $comment_tree[0] : [];
        $current_user          = is_user_logged_in() ? wp_get_current_user() : null;
        $current_avatar        = $current_user ? get_avatar_url( $current_user->ID, [ 'size' => 44 ] ) : '';
        update_post_meta( $post_id, 'tb4_view_count', $next_views );
        ?>
        <main id="tb4c-sp268-root" data-tb4c-layout="single-hard-isolate-v4268">
          <div class="tb4c-sp268-wrap">
            <a class="tb4c-sp268-back" href="<?php echo esc_url( home_url( '/community/' ) ); ?>"><?php echo tb4ccp_icon_markup( 'ph-arrow-left', '‹' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span>กลับฟีด</span></a>

            <article class="tb4c-sp268-card" data-post-id="<?php echo esc_attr( $post_id ); ?>">
              <header class="tb4c-sp268-head">
                <a class="tb4c-sp268-avatar" href="<?php echo esc_url( tb4ccp_social_profile_url( $author_id ) ); ?>">
                  <?php if ( $author_avatar ) : ?>
                    <img src="<?php echo esc_url( $author_avatar ); ?>" alt="">
                  <?php else : ?>
                    <?php echo esc_html( tb4ccp_get_initial( $author_name ) ); ?>
                  <?php endif; ?>
                </a>

                <div class="tb4c-sp268-meta">
                  <div class="tb4c-sp268-author">
                    <a href="<?php echo esc_url( tb4ccp_social_profile_url( $author_id ) ); ?>"><strong><?php echo esc_html( $author_name ); ?></strong></a>
                    <span>•</span>
                    <span><?php echo esc_html( human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) ); ?>ที่แล้ว</span>
                  </div>
                  <div class="tb4c-sp268-visibility" aria-label="การมองเห็นโพสต์">
                    <?php echo tb4ccp_icon_markup( function_exists( 'tb4ccp_visibility_icon_class' ) ? tb4ccp_visibility_icon_class( $visibility ) : 'ph-globe-hemisphere-east', 'groups' === $visibility ? '👥' : '🌐' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <span><?php echo esc_html( function_exists( 'tb4ccp_visibility_label' ) ? tb4ccp_visibility_label( $visibility ) : ( 'groups' === $visibility ? 'เฉพาะกลุ่ม' : 'สาธารณะ' ) ); ?></span>
                  </div>
                </div>

                <?php if ( current_user_can( 'edit_post', $post_id ) || current_user_can( 'delete_post', $post_id ) ) : ?>
                  <div class="tb4c-sp268-tools" aria-label="จัดการโพสต์">
                    <?php if ( current_user_can( 'edit_post', $post_id ) ) : ?>
                      <button type="button" class="tb4c-sp268-tool-edit" aria-label="แก้ไขโพสต์" title="แก้ไขโพสต์" data-tb4c-sp-post-action="edit" data-tb4c-sp-edit-post="<?php echo esc_attr( $post_id ); ?>" data-post-title="<?php echo esc_attr( get_the_title() ); ?>" data-post-content="<?php echo esc_attr( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ) ); ?>" data-post-type="<?php echo esc_attr( $type ); ?>" data-post-visibility="<?php echo esc_attr( $visibility ); ?>" data-post-media-url="<?php echo esc_attr( $post_media_url ); ?>" data-post-media-album="<?php echo esc_attr( $post_media_album_urls ); ?>" data-post-tags="<?php echo esc_attr( $tag_input_value ); ?>" data-post-feeling="<?php echo esc_attr( $post_feeling ); ?>" data-post-poll-question="<?php echo esc_attr( $post_poll_question ); ?>" data-post-poll-options="<?php echo esc_attr( $post_poll_options_value ); ?>"><svg class="tb4c-sp268-tool-svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 20h4.5L19.2 9.3a2.1 2.1 0 0 0 0-3L17.7 4.8a2.1 2.1 0 0 0-3 0L4 15.5V20Z"></path><path d="M13.5 6l4.5 4.5"></path></svg><span>แก้ไข</span></button>
                    <?php endif; ?>
                    <?php if ( current_user_can( 'delete_post', $post_id ) ) : ?>
                      <button type="button" class="tb4c-sp268-tool-delete" aria-label="ลบโพสต์" title="ลบโพสต์" data-tb4ccp-delete-post="<?php echo esc_attr( $post_id ); ?>"><svg class="tb4c-sp268-tool-svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M5 7h14"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M8 7l1-3h6l1 3"></path><path d="M7 7l1 13h8l1-13"></path></svg><span>ลบ</span></button>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
              </header>

              <div class="tb4c-sp268-body">
                <h1 class="tb4c-sp268-title" data-tb4c-sp-post-title><?php the_title(); ?></h1>
                <div class="tb4c-sp268-content" data-tb4c-sp-post-content><?php the_content(); ?></div>

                <?php if ( current_user_can( 'edit_post', $post_id ) ) : ?>
                  <div class="tb4c-sp268-post-editbox" data-tb4c-sp-post-editbox hidden>
                    <div class="tb4c-sp268-post-editgrid">
                      <label class="tb4c-sp268-post-editfield">
                        <span>หัวข้อโพสต์</span>
                        <input type="text" value="<?php echo esc_attr( get_the_title() ); ?>" maxlength="160" data-tb4c-sp-edit-title>
                      </label>
                      <label class="tb4c-sp268-post-editfield">
                        <span>รายละเอียดโพสต์</span>
                        <textarea rows="4" maxlength="6000" data-tb4c-sp-edit-content><?php echo esc_textarea( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ) ); ?></textarea>
                      </label>
                    </div>
                    <div class="tb4c-sp268-post-edit-actions">
                      <button type="button" class="tb4c-sp268-save" data-tb4c-sp-post-action="save"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m5 12 4 4L19 6"></path></svg><span>บันทึก</span></button>
                      <button type="button" class="tb4c-sp268-cancel" data-tb4c-sp-post-action="cancel"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg><span>ยกเลิก</span></button>
                    </div>
                    <p class="tb4c-sp268-post-edit-status" data-tb4c-sp-post-status aria-live="polite"></p>
                  </div>
                <?php endif; ?>

                <div class="tb4c-sp268-tags">
                  <?php echo function_exists( 'tb4ccp_render_post_type_tag_strip' ) ? tb4ccp_render_post_type_tag_strip( $post_id, $type, 8 ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </div>

                <?php if ( has_post_thumbnail( $post_id ) ) : ?>
                  <div class="tb4c-sp268-media">
                    <button type="button" class="tb4c-thumb tb4c-post-media-slot tb4c-post-image-slot tb4c-featured-lightbox-trigger" data-tb4c-lightbox data-tb4c-lightbox-type="image" data-tb4c-lightbox-src="<?php echo esc_url( get_the_post_thumbnail_url( $post_id, 'large' ) ); ?>" data-tb4c-lightbox-title="<?php echo esc_attr( get_the_title() ); ?>"><?php the_post_thumbnail( 'large' ); ?></button>
                  </div>
                <?php elseif ( ! empty( $post_media_album ) ) : ?>
                  <div class="tb4c-sp268-media">
                    <?php echo tb4ccp_render_post_media_album( $post_media_album, get_the_title() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                  </div>
                <?php endif; ?>

                <?php echo function_exists( 'tb4ccp_render_post_poll' ) ? tb4ccp_render_post_poll( $post_id ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

                <div class="tb4c-sp268-actions">
                  <button type="button" data-tb4c-react="like" data-post-id="<?php echo esc_attr( $post_id ); ?>" aria-label="ถูกใจ"><i class="ph ph-heart"></i><span><?php echo esc_html( $likes ); ?></span></button>
                  <a href="#comments" aria-label="ความคิดเห็น"><i class="ph ph-chat-circle"></i><span><?php echo esc_html( $comment_count ); ?></span></a>
                  <button type="button" data-tb4ccp-copy-link="<?php echo esc_url( get_permalink() ); ?>" aria-label="แชร์"><i class="ph ph-paper-plane-tilt"></i><span>แชร์</span></button>
                  <button type="button" data-tb4c-react="bookmark" data-post-id="<?php echo esc_attr( $post_id ); ?>" aria-label="บันทึก"><i class="ph ph-bookmark-simple"></i><span><?php echo esc_html( $bookmarks ); ?></span></button>
                  <span class="tb4c-sp268-view" aria-label="ยอดเข้าชม"><i class="ph ph-eye"></i><span><?php echo esc_html( $next_views ); ?></span></span>
                </div>

                <section class="tb4c-sp268-comments" id="comments" aria-label="ความคิดเห็น" data-tb4c-comments-root data-post-id="<?php echo esc_attr( $post_id ); ?>">
                  <header class="tb4c-sp268-comments-head">
                    <div class="tb4c-sp268-comments-title"><?php echo tb4ccp_comment_action_svg( 'comment' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><strong>ความคิดเห็น</strong></div>
                    <span class="tb4c-sp268-comments-count" data-tb4c-comment-count><?php echo esc_html( number_format_i18n( (int) $comment_count ) ); ?> ความคิดเห็น</span>
                  </header>

                  <div class="tb4c-sp268-comment-scroll" data-tb4c-comment-scroll>
                    <ol class="tb4c-sp268-comment-list" data-tb4c-comment-list <?php echo empty( $top_comments ) ? 'hidden' : ''; ?>>
                      <?php foreach ( $top_comments as $comment ) : ?>
                        <?php echo function_exists( 'tb4ccp_render_comment_item' ) ? tb4ccp_render_comment_item( $comment, $comment_tree, 0 ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                      <?php endforeach; ?>
                    </ol>
                    <div class="tb4c-sp268-empty" data-tb4c-comments-empty <?php echo empty( $top_comments ) ? '' : 'hidden'; ?>>
                      <div>💬</div>
                      <strong>ยังไม่มีความคิดเห็น</strong>
                      <span>เป็นคนแรกที่แสดงความคิดเห็นสิ</span>
                    </div>
                  </div>

                  <?php if ( comments_open( $post_id ) ) : ?>
                    <?php if ( is_user_logged_in() ) : ?>
                      <form class="tb4c-sp268-form" id="tb4cCommentForm" data-tb4ccp-comment-form>
                        <input type="hidden" name="action" value="tb4c_submit_comment">
                        <input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'tb4c_nonce' ) ); ?>">
                        <input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">
                        <input type="hidden" name="parent_id" value="0" data-tb4ccp-comment-parent>
                        <div class="tb4c-sp268-reply" data-tb4ccp-reply-context hidden>
                          <span data-tb4ccp-reply-label>กำลังตอบกลับ</span>
                          <button type="button" data-tb4ccp-comment-action="cancel-reply" aria-label="ยกเลิกการตอบกลับ">×</button>
                        </div>
                        <div class="tb4c-sp268-inputrow">
                          <span class="tb4c-sp268-current-avatar">
                            <?php if ( $current_avatar ) : ?><img src="<?php echo esc_url( $current_avatar ); ?>" alt=""><?php else : ?>💬<?php endif; ?>
                          </span>
                          <label class="screen-reader-text" for="tb4cCommentText">ความคิดเห็น</label>
                          <textarea id="tb4cCommentText" name="content" rows="1" maxlength="1200" placeholder="เขียนความคิดเห็น..." data-tb4ccp-comment-textarea required></textarea>
                          <button type="submit" class="tb4c-sp268-send tb4c-comment-send" aria-label="ส่งความคิดเห็น" aria-live="polite"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 19V5"></path><path d="M5.5 11.5 12 5l6.5 6.5"></path></svg><span>ส่ง</span><b class="tb4c-sp268-send-dots" aria-hidden="true"><i></i><i></i><i></i></b></button>
                        </div>
                        <p class="tb4c-sp268-status" data-tb4ccp-comment-status aria-live="polite"></p>
                      </form>
                    <?php else : ?>
                      <a class="tb4c-sp268-inputrow" href="<?php echo esc_url( wp_login_url( get_permalink( $post_id ) . '#comments' ) ); ?>" style="text-decoration:none;color:#111;">
                        <span class="tb4c-sp268-current-avatar">💬</span>
                        <span>เข้าสู่ระบบเพื่อแสดงความคิดเห็น</span>
                        <?php echo tb4ccp_icon_markup( 'ph-arrow-right', '›' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                      </a>
                    <?php endif; ?>
                  <?php else : ?>
                    <div class="tb4c-sp268-empty">ปิดรับความคิดเห็นสำหรับโพสต์นี้แล้ว</div>
                  <?php endif; ?>
                </section>
              </div>
            </article>
          </div>
        </main>
        <?php
    endwhile;
endif;
?>
<?php /* JS loaded by Thinkb4do Community Comment Page plugin. */ ?>

<?php // Comment page plugin intentionally does not load the feed composer modal. ?>
<?php get_footer();
