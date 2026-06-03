<?php
/**
 * Template Name: Trust Center
 * Description: หน้าข้อมูลความน่าเชื่อถือและข้อมูลจดทะเบียนของ Thinkb4do แบบสากล
 *
 * @package Thinkb4do
 * @version 1.4.5
 */
get_header();
?>

<main id="tb4-trust-page" class="section-pad-sm" aria-label="Trust Center">
<div class="container">
    <section class="tb4-trust-page-hero">
      <div>
        <span class="tb4-trust-kicker"><i class="ph ph-shield-check"></i> Trust Center</span>
        <h1>ข้อมูลความน่าเชื่อถือของ Thinkb4do</h1>
        <p>หน้านี้รวมสถานะการจดทะเบียน ความโปร่งใสของเว็บไซต์ ช่องทางตรวจสอบ และข้อมูลที่เกี่ยวข้องกับการใช้งานระบบ เพื่อให้ทีมงาน สมาชิก และผู้ติดตามตรวจสอบได้ง่ายโดยไม่ทำให้หน้าแรกดูรกเกินไป</p>
      </div>
      <div class="tb4-trust-page-summary" aria-label="สถานะปัจจุบัน">
        <span>สถานะปัจจุบัน</span>
        <strong><?php echo esc_html( tb4_dbd_status_label() ); ?></strong>
        <p><?php echo esc_html( 'registered' === tb4_sanitize_dbd_status( tb4_dbd_setting( 'status' ) ) ? 'ข้อมูลจดทะเบียนพร้อมให้ตรวจสอบตามลิงก์ที่ระบุ' : 'ยังไม่ใช้เลขสมมติหรือเครื่องหมายรับรองก่อนอนุมัติจริง' ); ?></p>
      </div>
    </section>

    <?php tb4_render_dbd_registration_block( 'trust_full' ); ?>

    <section class="tb4-trust-principles">
      <article>
        <i class="ph ph-check-circle"></i>
        <h2>บอกสถานะตามจริง</h2>
        <p>ถ้ายังไม่มีข้อมูลจริง ระบบจะแสดงว่าอยู่ระหว่างเตรียมข้อมูล แทนการใส่เลข โลโก้ หรือเครื่องหมายรับรองที่อาจทำให้เข้าใจผิด</p>
      </article>
      <article>
        <i class="ph ph-lock-key"></i>
        <h2>แยกข้อมูลกฎหมายออกจากหน้าแรก</h2>
        <p>หน้าแรกยังคงเน้นประสบการณ์ใช้งานและภาพลักษณ์แบรนด์ ส่วนรายละเอียดจดทะเบียนอยู่ในหน้านี้โดยเฉพาะ</p>
      </article>
      <article>
        <i class="ph ph-users-three"></i>
        <h2>รองรับทีมงานและสมาชิก</h2>
        <p>ข้อมูลความน่าเชื่อถือช่วยให้ผู้ร่วมงานรู้ว่าเว็บไซต์กำลังพัฒนาอย่างโปร่งใส และสามารถตรวจสอบสถานะได้ง่าย</p>
      </article>
    </section>

    <section class="tb4-trust-legal-links">
      <h2>เอกสารและข้อมูลที่เกี่ยวข้อง</h2>
      <div>
        <a href="<?php echo esc_url( home_url('/privacy/') ); ?>">นโยบายความเป็นส่วนตัว <i class="ph ph-arrow-right"></i></a>
        <a href="<?php echo esc_url( home_url('/terms/') ); ?>">ข้อกำหนดการใช้งาน <i class="ph ph-arrow-right"></i></a>
        <a href="<?php echo esc_url( home_url('/contact/') ); ?>">ติดต่อ / แจ้งปัญหา <i class="ph ph-arrow-right"></i></a>
      </div>
    </section>
  </div>
</main>

<?php get_footer(); ?>
