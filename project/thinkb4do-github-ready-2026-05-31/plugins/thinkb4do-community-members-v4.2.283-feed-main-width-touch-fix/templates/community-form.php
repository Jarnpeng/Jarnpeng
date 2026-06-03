<?php
/**
 * Community composer form partial.
 * v4.2.111: Smart Composer Clean Rebuild Final, one stable form for every creatable page with no typing-time reposition.
 * It keeps the original backend field names while making tools contextual,
 * reducing clutter, and keeping the panel inside header/footer safe area.
 *
 * @package Thinkb4doCommunity
 */
defined( 'ABSPATH' ) || exit;
?>
<?php
$tb4c_form_user   = is_user_logged_in() ? wp_get_current_user() : null;
$tb4c_form_avatar = ( $tb4c_form_user && $tb4c_form_user->ID ) ? get_avatar_url( $tb4c_form_user->ID, [ 'size' => 64 ] ) : '';
$tb4c_form_name   = ( $tb4c_form_user && $tb4c_form_user->ID ) ? $tb4c_form_user->display_name : __( 'สมาชิก Thinkb4do', 'thinkb4do-community' );
?>
<form class="tb4c-form tb4c-social-form tb4c-popup-composer-form tb4c-v42111-smart-form" id="tb4cCommunityForm" enctype="multipart/form-data" data-tb4c-community-form data-tb4c-smart-composer-form data-tb4c-smart-composer-all-pages-form data-tb4c-stable-composer-form>
  <input type="hidden" name="action" value="tb4_submit_community_post" data-tb4c-form-action>
  <input type="hidden" name="post_id" value="" data-tb4c-edit-post-id>
  <input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'tb4c_nonce' ) ); ?>">
  <input type="hidden" name="draft_session_id" value="">
  <input id="tb4c-post-title" type="hidden" name="title" maxlength="120" value="">

  <section class="tb4c-v42104-smart-author" aria-label="ข้อมูลผู้โพสต์และสถานะ Smart Composer">
    <span class="tb4c-avatar tb4c-v42104-smart-avatar">
      <?php if ( $tb4c_form_avatar ) : ?>
        <img src="<?php echo esc_url( $tb4c_form_avatar ); ?>" alt="">
      <?php else : ?>
        <?php echo esc_html( tb4cm_get_initial( $tb4c_form_name ) ); ?>
      <?php endif; ?>
    </span>
    <div class="tb4c-v42104-smart-author-copy">
      <strong><?php echo esc_html( $tb4c_form_name ); ?></strong>
      <small data-tb4c-smart-headline><?php esc_html_e( 'พร้อมสร้างโพสต์ที่อ่านง่าย', 'thinkb4do-community' ); ?></small>
      <div class="tb4c-v42104-smart-statusline">
        <span class="tb4c-draft-status" data-tb4c-draft-status><?php esc_html_e( 'บันทึกร่างอัตโนมัติ', 'thinkb4do-community' ); ?></span>
        <span class="tb4c-v42104-smart-meter" data-tb4c-smart-meter><?php esc_html_e( 'พร้อมเขียนโพสต์', 'thinkb4do-community' ); ?></span>
      </div>
    </div>
  </section>

  <section class="tb4c-v42104-smart-editor" aria-label="เขียนโพสต์">
    <label for="tb4c-post-content" class="tb4c-sr-only"><?php esc_html_e( 'รายละเอียดโพสต์', 'thinkb4do-community' ); ?></label>
    <textarea id="tb4c-post-content" name="content" rows="3" placeholder="คุณกำลังคิดอะไรอยู่? เล่าเรื่อง รูปภาพ วิดีโอ หรือไอเดียใหม่..."></textarea>
  </section>

  <nav class="tb4c-v42104-smart-toolbar" aria-label="เครื่องมือ Smart Composer">
    <button type="button" class="tb4c-v42104-smart-tool is-active" data-tb4c-smart-panel-btn="meta" aria-expanded="true">
      <?php echo tb4cm_icon_markup( 'ph-hash', '#' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
      <span>Tag</span>
    </button>
    <label for="tb4c-post-media-file" class="tb4c-v42104-smart-tool" data-tb4c-smart-upload tabindex="0">
      <?php echo tb4cm_icon_markup( 'ph-upload-simple', '↑' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
      <span>ไฟล์</span>
    </label>
    <button type="button" class="tb4c-v42104-smart-tool" data-tb4c-smart-panel-btn="media-url" aria-expanded="false">
      <?php echo tb4cm_icon_markup( 'ph-link-simple', '↗' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
      <span>URL</span>
    </button>
    <button type="button" class="tb4c-v42104-smart-tool" data-tb4c-smart-panel-btn="poll" aria-expanded="false">
      <?php echo tb4cm_icon_markup( 'ph-chart-bar', '▥' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
      <span>โพล</span>
    </button>
    <button type="button" class="tb4c-v42104-smart-tool" data-tb4c-smart-panel-btn="settings" aria-expanded="false">
      <?php echo tb4cm_icon_markup( 'ph-sliders-horizontal', '≡' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
      <span>ตั้งค่า</span>
    </button>
  </nav>

  <input id="tb4c-post-media-file" class="tb4c-upload-input tb4c-v497-file-input tb4c-v42104-file-input" type="file" name="media_files[]" accept="image/jpeg,image/png,image/gif,image/webp,image/avif,video/mp4,video/x-m4v,video/webm,video/ogg" multiple>

  <div class="tb4c-v42104-smart-panels">
    <section class="tb4c-v42104-smart-panel is-open" data-tb4c-smart-panel="meta" aria-label="Tag และประเภท">
      <div class="tb4c-v42104-smart-grid tb4c-v42104-smart-grid-3">
        <div class="tb4c-form-field">
          <label for="tb4c-post-tags">Tag</label>
          <input id="tb4c-post-tags" type="text" name="tags" maxlength="160" inputmode="text" placeholder="#พูดคุย #งาน #วิดีโอ" autocomplete="off">
        </div>
        <div class="tb4c-form-field">
          <label for="tb4c-post-type">ประเภท</label>
          <select id="tb4c-post-type" name="post_type" data-tb4c-smart-type>
            <option value="discussion">พูดคุย</option>
            <option value="question">ถาม-ตอบ</option>
            <option value="idea">ไอเดีย</option>
            <option value="project">โปรเจกต์</option>
            <option value="event">กิจกรรม</option>
            <option value="creator">Creator</option>
          </select>
        </div>
        <div class="tb4c-form-field">
          <label for="tb4c-post-new-tag">Tag สร้างใหม่</label>
          <input id="tb4c-post-new-tag" type="text" name="new_tag" maxlength="80" inputmode="text" placeholder="เช่น #ชุมชนใหม่" autocomplete="off">
        </div>
      </div>
    </section>

    <section class="tb4c-v42104-smart-panel" data-tb4c-smart-panel="media-url" aria-label="ลิงก์รูปภาพและวิดีโอ">
      <label for="tb4c-post-media-url">URL รูปภาพ/วิดีโอ/อัลบั้ม</label>
      <textarea id="tb4c-post-media-url" name="media_url" rows="3" inputmode="url" placeholder="วาง 1 ลิงก์ต่อ 1 บรรทัด เช่น รูปภาพ, mp4, YouTube หรือ Vimeo"></textarea>
    </section>

    <section class="tb4c-v42104-smart-panel" data-tb4c-smart-panel="poll" aria-label="โพลและโหวต">
      <label for="tb4c-post-poll-question">โพล / โหวต</label>
      <input id="tb4c-post-poll-question" type="text" name="poll_question" maxlength="140" placeholder="เช่น ควรพัฒนาฟีเจอร์ไหนก่อน?">
      <textarea id="tb4c-post-poll-options" name="poll_options" rows="3" placeholder="ใส่ตัวเลือก บรรทัดละ 1 ตัวเลือก&#10;ตัวเลือกที่ 1&#10;ตัวเลือกที่ 2"></textarea>
    </section>

    <section class="tb4c-v42104-smart-panel" data-tb4c-smart-panel="settings" aria-label="การมองเห็นและการตั้งค่าโพสต์">
      <div class="tb4c-v42104-smart-grid tb4c-v42104-smart-grid-2">
        <div class="tb4c-form-field">
          <label for="tb4c-visibility">การมองเห็น</label>
          <select id="tb4c-visibility" name="visibility">
            <option value="public">สาธารณะ</option>
            <option value="members">เฉพาะสมาชิก</option>
            <option value="groups">เฉพาะกลุ่ม</option>
          </select>
        </div>
      </div>
    </section>
  </div>

  <div class="tb4c-v42104-smart-upload-status" data-tb4c-smart-upload-status hidden></div>

  <div class="tb4c-form-actions tb4c-v497-feed-actions tb4c-v42104-smart-actions">
    <span class="tb4c-form-status" data-tb4c-form-status></span>
    <button class="tb4c-btn tb4c-btn-secondary" type="button" data-tb4c-close-modal>ยกเลิก</button>
    <button class="tb4c-btn tb4c-btn-primary" type="submit" data-tb4c-submit-label="โพสต์"><span class="tb4c-submit-text"><i class="ph ph-paper-plane-tilt"></i> โพสต์</span><span class="tb4c-submit-loader" aria-hidden="true"><i></i><i></i><i></i></span></button>
  </div>
</form>
