<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="color-scheme" content="light">
  <meta name="theme-color" content="#1E6B45">
  <link rel="profile" href="https://gmpg.org/xfn/11">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#main-content"><?php _e('ข้ามไปยังเนื้อหา','thinkb4do'); ?></a>

<?php
/**
 * Header/Menu ถูกแยกออกเป็นปลั๊กอิน: Thinkb4do Header Menu
 * Body Layout และ Footer ยังคงอยู่ในธีมนี้เหมือนเดิม
 * ถ้าปลั๊กอินปิดอยู่ ธีมจะแสดง fallback header แบบเบาเพื่อไม่ให้เว็บพัง
 */
if ( function_exists( 'tb4_header_menu_render' ) ) {
    tb4_header_menu_render();
} else {
    do_action( 'tb4_before_header' );
    ?>
    <header id="site-header" role="banner" data-tb4-live-component="header" data-tb4-live-title="ส่วนหัวเว็บ" class="tb4-header-fallback">
      <div class="container">
        <div class="header-inner">
          <?php if ( function_exists( 'tb4_render_site_logo' ) ) : ?>
            <?php tb4_render_site_logo( 'header' ); ?>
          <?php else : ?>
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo site-logo--header" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
              <span class="logo-icon" aria-hidden="true">T</span>
              <span class="site-logo-copy"><span class="logo-text"><?php bloginfo( 'name' ); ?></span></span>
            </a>
          <?php endif; ?>

          <nav class="main-nav" role="navigation" aria-label="เมนูหลัก">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Discovery', 'thinkb4do' ); ?></a>
            <a href="<?php echo esc_url( home_url( '/community/' ) ); ?>"><?php esc_html_e( 'ชุมชน', 'thinkb4do' ); ?></a>
            <a href="<?php echo esc_url( home_url( '/products/' ) ); ?>"><?php esc_html_e( 'ผลิตภัณฑ์', 'thinkb4do' ); ?></a>
          </nav>
        </div>
      </div>
    </header>
    <?php
    do_action( 'tb4_after_header' );
}
?>

<div id="main-content">
