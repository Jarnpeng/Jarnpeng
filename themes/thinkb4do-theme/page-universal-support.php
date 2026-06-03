<?php
/**
 * Template Name: Universal Support / รองรับทุกระบบ
 *
 * @package Thinkb4do
 * @version 1.4.7
 */
get_header();
?>
<main id="main-content" class="tb4-universal-page section-pad" aria-label="Universal Support">
<div class="container">
    <section class="tb4-trust-page-hero">
      <div>
        <span class="tb4-trust-kicker"><i class="ph ph-devices"></i> Universal Support</span>
        <h1>รองรับทุกอุปกรณ์ ทุกเบราว์เซอร์ และผู้ใช้งานหลายบริบท</h1>
        <p>หน้านี้ใช้บอกแนวทางรองรับระบบของ Thinkb4do อย่างตรงไปตรงมา ทั้งระดับสากลและท้องถิ่น โดยยังคงยึดหลักใช้งานง่าย ปลอดภัย และแก้ไขต่อได้จาก WordPress</p>
      </div>
      <div class="tb4-trust-visual" aria-hidden="true">
        <div class="tb4-trust-orb"><i class="ph ph-globe-hemisphere-east"></i></div>
        <div class="tb4-trust-mini-card"><span>Coverage</span><strong>Global + Local</strong></div>
      </div>
    </section>

    <?php tb4_render_compatibility_block( 'page' ); ?>

    <section class="tb4-trust-principles" style="margin-top:24px;">
      <article><i class="ph ph-monitor"></i><h3>Responsive</h3><p>ออกแบบให้ยืดหยุ่นตั้งแต่จอมือถือเล็กจนถึงจอเดสก์ท็อปขนาดใหญ่</p></article>
      <article><i class="ph ph-universal-access"></i><h3>Accessibility</h3><p>คำนึงถึงการอ่านง่าย การใช้แป้นพิมพ์ โหมดลดการเคลื่อนไหว และ contrast mode</p></article>
      <article><i class="ph ph-shield-check"></i><h3>Safe Fallback</h3><p>หากเบราว์เซอร์เก่าหรือฟีเจอร์บางอย่างไม่รองรับ ระบบยังคงแสดงเนื้อหาหลักได้</p></article>
      <article><i class="ph ph-translate"></i><h3>Local Ready</h3><p>ใช้ฟอนต์ที่รองรับภาษาไทยและเตรียมโครงสร้างสำหรับหลายภาษาในอนาคต</p></article>
    </section>
  </div>
</main>
<?php get_footer(); ?>
