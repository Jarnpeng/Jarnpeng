<?php
/**
 * Comments Template
 * @package Thinkb4do
 */
if ( post_password_required() ) return;
?>
<section id="comments" style="margin-top:var(--space-2xl);padding-top:var(--space-xl);border-top:1px solid var(--tb4-gray-100);">

  <?php if ( have_comments() ) : ?>
    <h3 style="margin-bottom:var(--space-xl);">
      <?php comments_number('ความคิดเห็น', 'ความคิดเห็น 1 รายการ', 'ความคิดเห็น % รายการ'); ?>
    </h3>

    <ol style="list-style:none;padding:0;display:flex;flex-direction:column;gap:var(--space-md);">
      <?php wp_list_comments([
        'style'      => 'ol',
        'short_ping' => true,
        'callback'   => 'tb4_comment_template',
      ]); ?>
    </ol>

    <?php the_comments_pagination([
      'prev_text' => '← ก่อนหน้า',
      'next_text' => 'ถัดไป →',
    ]); ?>

  <?php endif; ?>

  <?php if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
    <p style="color:var(--tb4-gray-500);font-size:.9rem;">ปิดความคิดเห็นแล้ว</p>
  <?php endif; ?>

  <?php
  comment_form([
    'title_reply'          => 'แสดงความคิดเห็น',
    'label_submit'         => 'ส่งความคิดเห็น',
    'comment_notes_before' => '',
    'comment_field'        => '<p><label for="comment" style="display:block;font-weight:600;font-size:.9rem;margin-bottom:8px;">ความคิดเห็น <span style="color:var(--tb4-orange);">*</span></label>
      <textarea id="comment" name="comment" rows="5" required style="width:100%;padding:12px 16px;border:1.5px solid var(--tb4-gray-300);border-radius:var(--radius-md);font-family:var(--font-main);font-size:.95rem;outline:none;resize:vertical;transition:border-color .15s;" onfocus="this.style.borderColor=\'var(--tb4-green)\'" onblur="this.style.borderColor=\'var(--tb4-gray-300)\'"></textarea></p>',
    'submit_button'        => '<button name="%1$s" type="submit" id="%2$s" class="btn btn-primary btn-lg" value="%4$s">%4$s</button>',
  ]);
  ?>
</section>

<?php
function tb4_comment_template( $comment, $args, $depth ) {
  $tag = ( 'div' === $args['style'] ) ? 'div' : 'li';
  ?>
  <<?php echo $tag; ?> id="comment-<?php comment_ID(); ?>" style="background:#fff;border-radius:var(--radius-lg);padding:var(--space-lg);border:1px solid var(--tb4-gray-100);">
    <div style="display:flex;gap:var(--space-md);align-items:flex-start;">
      <?php echo get_avatar( $comment, 44, '', '', ['style'=>'border-radius:50%;flex-shrink:0;'] ); ?>
      <div style="flex:1;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;flex-wrap:wrap;gap:8px;">
          <div style="font-weight:600;font-size:.9rem;"><?php comment_author(); ?></div>
          <div style="font-size:.75rem;color:var(--tb4-gray-500);"><?php comment_date('j M Y · H:i'); ?></div>
        </div>
        <?php comment_text(); ?>
        <div style="margin-top:8px;">
          <?php comment_reply_link( array_merge( $args, [
            'depth'  => $depth,
            'before' => '<span style="font-size:.8rem;color:var(--tb4-green);cursor:pointer;">',
            'after'  => '</span>',
            'reply_text' => '↩ ตอบกลับ',
          ] ) ); ?>
        </div>
      </div>
    </div>
  </<?php echo $tag; ?>>
  <?php
}
?>
