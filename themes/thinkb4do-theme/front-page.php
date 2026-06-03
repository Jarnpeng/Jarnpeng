<?php
/**
 * Thinkb4do Front Page v2.4.12 — Discovery Plugin Bridge
 * หน้า Discovery ถูกแยกออกจากธีมแล้ว ธีมทำหน้าที่เป็น bridge เท่านั้น
 *
 * @package Thinkb4do
 */

get_header();

if ( shortcode_exists( 'thinkb4do_home_discovery_feed' ) ) {
    echo do_shortcode( '[thinkb4do_home_discovery_feed source="theme-bridge"]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
} elseif ( shortcode_exists( 'thinkb4do_discovery' ) ) {
    echo do_shortcode( '[thinkb4do_discovery source="theme-bridge"]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
} else {
    ?>
    <main id="main" class="tb4-modern-main tb4-discovery-plugin-bridge tb4-plugin-owned-shell" role="main" style="background:#fff;min-height:70vh;">
        <section class="container" style="padding:64px 20px;max-width:960px;margin:0 auto;">
            <div style="border:1px solid #e5e7eb;border-radius:24px;padding:32px;background:#fff;box-shadow:0 20px 50px rgba(15,23,42,.08);font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen-Sans,Ubuntu,Cantarell,'Helvetica Neue',Arial,'Noto Sans Thai','Kanit',sans-serif;">
                <p style="color:#1E6B45;font-weight:800;margin:0 0 8px;">Thinkb4do Discovery</p>
                <h1 style="margin:0 0 12px;color:#111111;">หน้า Discovery ถูกแยกเป็นปลั๊กอินแล้ว</h1>
                <p style="color:#64748b;line-height:1.8;margin:0 0 18px;">กรุณาติดตั้งและเปิดใช้งานปลั๊กอิน <strong>Thinkb4do Discovery</strong> เพื่อแสดงหน้า Discovery แบบใหม่ โดยธีมจะคงหน้าที่ Body / Footer / Layout หลักไว้</p>
                <a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>" style="display:inline-flex;align-items:center;justify-content:center;border-radius:16px;background:#1E6B45;color:#fff;text-decoration:none;font-weight:800;padding:12px 16px;">ไปที่ Plugins</a>
            </div>
        </section>
    </main>
    <?php
}

get_footer();
