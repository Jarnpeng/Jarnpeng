<?php
/**
 * Template Name: หน้าไกด์ระบบ Thinkb4do
 * Description: หน้าไกด์สำหรับอธิบายว่าเว็บไซต์อยู่ระหว่างพัฒนาและส่วนใดเป็นข้อมูลตัวอย่าง
 *
 * @package Thinkb4do
 * @version 1.4.6
 */
get_header();
?>

<main id="tb4-system-guide" class="section-pad-sm" aria-label="ไกด์ระบบ Thinkb4do">
<div class="container">
    <section class="card" style="padding:clamp(24px,4vw,48px);background:linear-gradient(135deg,var(--tb4-green-light),#fff);border-color:var(--tb4-green-mid);">
      <span class="badge badge-orange" style="margin-bottom:12px;"><?php echo esc_html( tb4_dev_text( 'guide_badge' ) ); ?></span>
      <h1 style="margin-bottom:12px;"><?php echo esc_html( tb4_dev_text( 'guide_title' ) ); ?></h1>
      <p style="max-width:820px;font-size:1rem;"><?php echo esc_html( tb4_dev_text( 'guide_desc' ) ); ?></p>
    </section>

    <section style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:var(--space-lg);margin-top:var(--space-lg);" class="tb4-guide-front-grid">
      <?php
      $cards = [
        ['🛠️',tb4_dev_text('guide_card_1_title'),tb4_dev_text('guide_card_1_body')],
        ['🧪',tb4_dev_text('guide_card_2_title'),tb4_dev_text('guide_card_2_body')],
        ['📌',tb4_dev_text('guide_card_3_title'),tb4_dev_text('guide_card_3_body')],
      ];
      foreach ( $cards as [$icon,$title,$desc] ) : ?>
      <article class="card" style="padding:var(--space-lg);">
        <div style="font-size:2rem;margin-bottom:10px;" aria-hidden="true"><?php echo esc_html( $icon ); ?></div>
        <h2 style="font-size:1.1rem;margin-bottom:8px;"><?php echo esc_html( $title ); ?></h2>
        <p style="font-size:.9rem;"><?php echo esc_html( $desc ); ?></p>
      </article>
      <?php endforeach; ?>
    </section>

    <section class="card" style="padding:var(--space-lg);margin-top:var(--space-lg);">
      <h2 style="font-size:1.25rem;margin-bottom:12px;">สถานะระบบตอนนี้</h2>
      <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:var(--space-md);" class="tb4-guide-status-grid">
        <?php
        $rows = [
          ['ส่วนหัวเว็บไซต์','ใช้งานได้ และตั้งค่า Sticky Header ได้จาก Customizer'],
          ['โลโก้เว็บไซต์','เชื่อมกับ Media Library แล้ว สามารถเลือกไฟล์โลโก้จาก WordPress ได้'],
          ['ปุ่มแจ้งเตือน','กดได้แล้ว เป็น popup ตัวอย่างสำหรับแจ้งสถานะระบบ'],
          ['สินค้า/ผลิตภัณฑ์','ยังเป็นข้อมูลตัวอย่าง ยังไม่เปิดจำหน่ายจริง'],
          ['ชุมชน/สมาชิก','อยู่ระหว่างออกแบบและทดสอบโครงสร้าง'],
        ];
        foreach ( $rows as [$name,$status] ) : ?>
        <div style="border:1px solid var(--tb4-gray-100);border-radius:var(--radius-md);padding:14px 16px;">
          <strong style="display:block;margin-bottom:4px;"><?php echo esc_html( $name ); ?></strong>
          <span style="color:var(--tb4-gray-500);font-size:.9rem;"><?php echo esc_html( $status ); ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </section>

    <?php if ( get_theme_mod( 'tb4_dbd_show_guide', true ) ) : ?>
      <section style="margin-top:var(--space-lg);">
        <?php tb4_render_dbd_registration_block( 'guide' ); ?>
      </section>
    <?php endif; ?>

  </div>
</main>

<style>
@media (max-width: 860px) {
  .tb4-guide-front-grid,
  .tb4-guide-status-grid { grid-template-columns: 1fr !important; }
}
</style>

<?php get_footer(); ?>
