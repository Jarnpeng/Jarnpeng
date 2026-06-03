<?php
/**
 * Feed post card partial.
 *
 * Shared by the initial Community feed and AJAX load-more responses.
 *
 * @package Thinkb4doCommunity
 */
defined( 'ABSPATH' ) || exit;

$post_id = get_the_ID();
$tb4c_track_reels = isset( $reel_items, $reel_item_ids ) && is_array( $reel_items ) && is_array( $reel_item_ids );
              $author_id = (int) get_post_field( 'post_author', $post_id );
              $author_name = get_the_author_meta( 'display_name', $author_id ) ?: get_the_author();
              $author_member_url = tb4cf_social_profile_url( $author_id );
              $avatar = get_avatar_url( $author_id, [ 'size' => 56 ] );
              $type = get_post_meta( $post_id, 'tb4_post_type', true ) ?: 'discussion';
              $visibility = get_post_meta( $post_id, 'tb4_post_visibility', true ) ?: 'public';
              $likes = (int) get_post_meta( $post_id, 'tb4_like_count', true );
              $bookmarks = (int) get_post_meta( $post_id, 'tb4_bookmark_count', true );
              $views = (int) get_post_meta( $post_id, 'tb4_view_count', true );
              $terms = get_the_terms( $post_id, 'tb4_community_topic' );
              $tag_terms = get_the_terms( $post_id, 'tb4_community_tag' );
              $tag_input_value = function_exists( 'tb4cf_get_post_tag_input_value' ) ? tb4cf_get_post_tag_input_value( $post_id ) : '';
              $post_feeling = get_post_meta( $post_id, 'tb4_post_feeling', true );
              $post_poll_question = get_post_meta( $post_id, 'tb4_poll_question', true );
              $post_poll_options = get_post_meta( $post_id, 'tb4_poll_options', true );
              $post_poll_options_value = is_array( $post_poll_options ) ? implode( "
", $post_poll_options ) : '';
              $is_pinned = (bool) get_post_meta( $post_id, 'tb4_is_pinned', true );
              $post_raw_text = wp_strip_all_tags( get_the_content() );
              $post_media_album = tb4cf_get_post_media_album( $post_id, $post_raw_text );
              if ( empty( $post_media_album ) && has_post_thumbnail( $post_id ) ) {
                  $post_media_album[] = [
                      'type' => 'image',
                      'url'  => get_the_post_thumbnail_url( $post_id, 'large' ),
                  ];
              }
              $post_media = ! empty( $post_media_album[0] ) ? $post_media_album[0] : null;
              $post_media_url = is_array( $post_media ) && ! empty( $post_media['url'] ) ? $post_media['url'] : '';
              $post_media_type = is_array( $post_media ) && ! empty( $post_media['type'] ) ? $post_media['type'] : '';
              $post_media_album_urls = ! empty( $post_media_album ) ? implode( "\n", wp_list_pluck( $post_media_album, 'url' ) ) : '';
              $has_visual_media = has_post_thumbnail() || ! empty( $post_media_album );
              $post_caption_source = get_the_excerpt() ?: $post_raw_text;
              $post_caption_text = tb4cf_media_visible_text( $post_caption_source, $post_media );
              $post_caption_text = wp_trim_words( wp_strip_all_tags( $post_caption_text ), 34 );
              if ( $tb4c_track_reels && ! empty( $post_media_album ) && ! in_array( (int) $post_id, $reel_item_ids, true ) ) {
                  $reel_items[] = [
                      'post_id'        => $post_id,
                      'author_id'      => $author_id,
                      'author_name'    => $author_name,
                      'author_url'     => $author_member_url,
                      'author_avatar'  => $avatar,
                      'title'          => get_the_title(),
                      'caption'        => $post_caption_text,
                      'permalink'      => tb4cf_get_post_link( $post_id ),
                      'likes'          => $likes,
                      'comments'       => (int) get_comments_number(),
                      'topic_label'    => tb4cf_topic_label( $type ),
                      'visibility'     => function_exists( 'tb4cf_visibility_label' ) ? tb4cf_visibility_label( $visibility ) : ( 'groups' === $visibility ? 'เฉพาะกลุ่ม' : 'สาธารณะ' ),
                      'time_ago'       => human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) . 'ที่แล้ว',
                      'media'          => $post_media_album,
                      'media_count'    => count( $post_media_album ),
                      'cover'          => has_post_thumbnail( $post_id ) ? get_the_post_thumbnail_url( $post_id, 'medium_large' ) : ( 'image' === ( $post_media['type'] ?? '' ) ? ( $post_media['url'] ?? '' ) : ( function_exists( 'tb4cf_get_video_poster_url' ) ? tb4cf_get_video_poster_url( $post_media ) : '' ) ),
                      'topic_terms'    => ! empty( $terms ) && ! is_wp_error( $terms ) ? wp_list_pluck( array_slice( $terms, 0, 4 ), 'name' ) : [],
                      'tag_terms'      => ! empty( $tag_terms ) && ! is_wp_error( $tag_terms ) ? wp_list_pluck( array_slice( $tag_terms, 0, 6 ), 'name' ) : [],
                  ];
                  $reel_item_ids[] = (int) $post_id;
              }
          ?>
          <article class="tb4c-post-card tb4c-social-post-card tb4c-ig-post tb4c-native-post <?php echo $has_visual_media ? 'tb4c-has-media' : 'tb4c-text-only-post'; ?>" data-post-id="<?php echo esc_attr( $post_id ); ?>">
            <?php if ( $is_pinned ) : ?><div class="tb4c-pin"><i class="ph ph-push-pin"></i> ปักหมุด</div><?php endif; ?>
            <div class="tb4c-post-head">
              <a class="tb4c-avatar tb4c-member-link" href="<?php echo esc_url( $author_member_url ); ?>" data-tb4c-member-link="<?php echo esc_attr( $author_id ); ?>" title="ดูหน้าสมาชิกของ <?php echo esc_attr( $author_name ); ?>">
                <?php if ( $avatar ) : ?><img src="<?php echo esc_url( $avatar ); ?>" alt=""><?php else : ?><?php echo esc_html( tb4cf_get_initial( $author_name ) ); ?><?php endif; ?>
              </a>
              <div class="tb4c-post-meta">
                <div class="tb4c-author-line">
                  <div class="tb4c-author-row">
                    <a class="tb4c-author-name-link tb4c-member-link" href="<?php echo esc_url( $author_member_url ); ?>" data-tb4c-member-link="<?php echo esc_attr( $author_id ); ?>"><strong><?php echo esc_html( $author_name ); ?></strong></a>
                    <span class="tb4c-dot">•</span>
                    <span><?php echo esc_html( human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) ); ?>ที่แล้ว</span>
                  </div>
                </div>
                <div class="tb4c-author-audience-row" aria-label="การมองเห็นโพสต์">
                  <?php echo tb4cf_icon_markup( function_exists( 'tb4cf_visibility_icon_class' ) ? tb4cf_visibility_icon_class( $visibility ) : 'ph-globe-hemisphere-east', 'groups' === $visibility ? '👥' : '🌐' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                  <span><?php echo esc_html( function_exists( 'tb4cf_visibility_label' ) ? tb4cf_visibility_label( $visibility ) : ( 'groups' === $visibility ? 'เฉพาะกลุ่ม' : 'สาธารณะ' ) ); ?></span>
                </div>
              </div>
              <?php if ( current_user_can( 'edit_post', $post_id ) || current_user_can( 'delete_post', $post_id ) ) : ?>
                <div class="tb4c-feed-owner-tools tb4c-feed-owner-tools-inline" aria-label="จัดการโพสต์ของฉัน">
                  <?php if ( current_user_can( 'edit_post', $post_id ) ) : ?>
                    <button type="button" aria-label="แก้ไขโพสต์" title="แก้ไขโพสต์" data-tb4c-edit-post="<?php echo esc_attr( $post_id ); ?>" data-post-title="<?php echo esc_attr( get_the_title() ); ?>" data-post-content="<?php echo esc_attr( wp_strip_all_tags( get_the_content() ) ); ?>" data-post-type="<?php echo esc_attr( $type ); ?>" data-post-visibility="<?php echo esc_attr( $visibility ); ?>" data-post-media-url="<?php echo esc_attr( $post_media_url ); ?>" data-post-media-album="<?php echo esc_attr( $post_media_album_urls ); ?>" data-post-tags="<?php echo esc_attr( $tag_input_value ); ?>" data-post-feeling="<?php echo esc_attr( $post_feeling ); ?>" data-post-poll-question="<?php echo esc_attr( $post_poll_question ); ?>" data-post-poll-options="<?php echo esc_attr( $post_poll_options_value ); ?>"><?php echo tb4cf_icon_markup( 'ph-pencil-simple-line', '✎' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span>แก้ไข</span></button>
                  <?php endif; ?>
                  <?php if ( current_user_can( 'delete_post', $post_id ) ) : ?>
                    <button type="button" aria-label="ลบโพสต์" title="ลบโพสต์" data-tb4c-delete-post="<?php echo esc_attr( $post_id ); ?>"><?php echo tb4cf_icon_markup( 'ph-trash', '×' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span>ลบ</span></button>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </div>

            <a class="tb4c-post-inline-text" href="<?php echo esc_url( tb4cf_get_post_link( get_the_ID() ) ); ?>">
              <h2><?php the_title(); ?></h2>
              <?php if ( $post_caption_text ) : ?><p><?php echo esc_html( $post_caption_text ); ?></p><?php endif; ?>
            </a>

            <?php echo function_exists( 'tb4cf_render_post_type_tag_strip' ) ? tb4cf_render_post_type_tag_strip( $post_id, $type, 6 ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

            <?php if ( has_post_thumbnail() ) : ?>
              <button type="button" class="tb4c-thumb tb4c-ig-media tb4c-post-media-slot tb4c-post-image-slot tb4c-featured-lightbox-trigger" data-tb4c-lightbox data-tb4c-lightbox-type="image" data-tb4c-lightbox-src="<?php echo esc_url( get_the_post_thumbnail_url( $post_id, 'large' ) ); ?>" data-tb4c-lightbox-title="<?php echo esc_attr( get_the_title() ); ?>"><?php the_post_thumbnail( 'large' ); ?></button>
            <?php elseif ( ! empty( $post_media_album ) ) : ?>
              <?php echo tb4cf_render_post_media_album( $post_media_album, get_the_title() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php endif; ?>

            <?php echo function_exists( 'tb4cf_render_post_poll' ) ? tb4cf_render_post_poll( $post_id ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

            <div class="tb4c-post-actions tb4c-social-actions tb4c-ig-actions tb4c-ig-action-row tb4c-post-actions-balanced">
              <button type="button" data-tb4c-react="like" data-post-id="<?php echo esc_attr( $post_id ); ?>" aria-label="ถูกใจ"><i class="ph ph-heart"></i><span><?php echo esc_html( $likes ); ?></span></button>
              <a href="<?php echo esc_url( tb4cf_get_comment_link( get_the_ID() ) ); ?>" aria-label="ความคิดเห็น"><i class="ph ph-chat-circle"></i><span><?php echo esc_html( get_comments_number() ); ?></span></a>
              <button type="button" data-tb4c-copy-link="<?php echo esc_url( tb4cf_get_post_link( $post_id ) ); ?>" aria-label="แชร์"><i class="ph ph-paper-plane-tilt"></i><span>แชร์</span></button>
              <button type="button" data-tb4c-react="bookmark" data-post-id="<?php echo esc_attr( $post_id ); ?>" aria-label="บันทึก"><i class="ph ph-bookmark-simple"></i><span><?php echo esc_html( $bookmarks ); ?></span></button>
              <span class="tb4c-action-view-chip" aria-label="ยอดเข้าชม"><i class="ph ph-eye"></i><span><?php echo esc_html( $views ); ?></span></span>
            </div>
          </article>
