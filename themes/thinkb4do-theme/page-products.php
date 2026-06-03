<?php
/**
 * Template Name: หน้าผลิตภัณฑ์
 * Product page bridge. The product system is now handled by the Thinkb4do Products plugin.
 * @package Thinkb4do
 */
get_header();
?>
<main id="main" class="tb4-products-plugin-bridge" style="background:#fff;min-height:70vh;">
<?php
if ( shortcode_exists( 'thinkb4do_products' ) ) {
    echo do_shortcode( '[thinkb4do_products layout="page" limit="24"]' );
} else {
    ?>
    <section class="container" style="padding:64px 20px;max-width:960px;margin:0 auto;">
        <div style="border:1px solid #e5e7eb;border-radius:24px;padding:32px;background:#fff;box-shadow:0 20px 50px rgba(15,23,42,.08);">
            <p style="color:#1E6B45;font-weight:800;margin-bottom:8px;">Thinkb4do Products</p>
            <h1 style="margin:0 0 12px;">ระบบผลิตภัณฑ์ถูกแยกเป็นปลั๊กอินแล้ว</h1>
            <p style="color:#64748b;line-height:1.8;">กรุณาติดตั้งและเปิดใช้งานปลั๊กอิน <strong>Thinkb4do Products</strong> เพื่อแสดงหน้าผลิตภัณฑ์แบบใหม่</p>
        </div>
    </section>
    <?php
}
?>
</main>
<?php get_footer(); ?>
