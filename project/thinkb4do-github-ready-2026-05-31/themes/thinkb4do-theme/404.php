<?php
/**
 * 404 template — Thinkb4do mobile-safe version.
 *
 * Keeps the page honest, lightweight, and safe on small screens.
 * Avoids oversized cards / text that previously overflowed on mobile.
 */
get_header();
?>
<main id="main" class="tb4-404-page tb4-404-v156" role="main" data-tb4-theme="1.5.8">
  <section class="tb4-404-hero" aria-labelledby="tb4-404-title">
    <div class="container tb4-404-container">
      <div class="tb4-404-icon" aria-hidden="true">
        <i class="ph ph-crosshair"></i>
      </div>

      <p class="tb4-404-kicker">ไม่พบหน้าที่คุณค้นหา</p>
      <h1 id="tb4-404-title">ยังหาไม่เจอใช่ไหม?</h1>
      <p class="tb4-404-copy" aria-label="คำอธิบายหน้าไม่พบข้อมูล">
        <span class="tb4-404-copy-line">หน้านี้อาจถูกย้าย หรืออยู่ระหว่างจัดระบบใหม่</span>
        <span class="tb4-404-copy-line">กลับไปหน้าแรก หรือค้นหาเนื้อหาที่เกี่ยวข้อง</span>
      </p>

      <div class="tb4-404-search" aria-label="ค้นหาในเว็บไซต์">
        <form role="search" method="get" class="tb4-404-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
          <label class="screen-reader-text" for="tb4-404-search-field"><?php esc_html_e( 'ค้นหาเนื้อหา', 'thinkb4do' ); ?></label>
          <div class="tb4-404-search-box">
            <div class="tb4-404-search-control">
              <i class="ph ph-magnifying-glass" aria-hidden="true"></i>
              <input id="tb4-404-search-field" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="ค้นหา เช่น บทความ ไอเดีย หรือระบบ" aria-label="ค้นหาเนื้อหา">
            </div>
            <button type="submit" class="btn btn-primary tb4-404-search-submit">
              <i class="ph ph-magnifying-glass" aria-hidden="true"></i>
              <span>ค้นหา</span>
            </button>
          </div>
        </form>
      </div>

      <div class="tb4-404-actions">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary btn-lg">
          <i class="ph ph-house"></i>
          กลับไปหน้าหลัก
        </a>
        <a href="<?php echo esc_url( home_url( '/system-guide/' ) ); ?>" class="btn btn-outline btn-lg">
          ดูไกด์ระบบ
          <i class="ph ph-arrow-right"></i>
        </a>
      </div>
    </div>
  </section>

  <section class="tb4-404-links" aria-labelledby="tb4-404-links-title">
    <div class="container">
      <h2 id="tb4-404-links-title" class="screen-reader-text">เมนูลัด</h2>
      <div class="tb4-404-grid">
        <a class="tb4-404-card" href="<?php echo esc_url( home_url( '/' ) ); ?>">
          <span><i class="ph ph-house"></i></span>
          <strong>หน้าหลัก</strong>
          <em>เริ่มต้นใหม่</em>
        </a>
        <a class="tb4-404-card" href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">
          <span><i class="ph ph-sparkle"></i></span>
          <strong>บทความ</strong>
          <em>อ่านไอเดีย</em>
        </a>
        <a class="tb4-404-card" href="<?php echo esc_url( home_url( '/about/' ) ); ?>">
          <span><i class="ph ph-target"></i></span>
          <strong>เกี่ยวกับเรา</strong>
          <em>รู้จักแนวคิด</em>
        </a>
        <a class="tb4-404-card" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">
          <span><i class="ph ph-envelope-simple"></i></span>
          <strong>ติดต่อเรา</strong>
          <em>ขอความช่วยเหลือ</em>
        </a>
      </div>
    </div>
  </section>
</main>
<?php get_footer();
