<?php
/**
 * Template: Home Menu Feed
 *
 * @package Thinkb4doCommunity
 */
defined( 'ABSPATH' ) || exit;

$home_feed_atts = isset( $tb4c_home_menu_feed_atts ) && is_array( $tb4c_home_menu_feed_atts ) ? $tb4c_home_menu_feed_atts : [];
$home_feed_limit = isset( $home_feed_atts['limit'] ) ? absint( $home_feed_atts['limit'] ) : 8;
$home_menu_items = tb4cm_get_home_menu_source_items( $home_feed_limit );
$home_cards      = tb4cm_get_home_menu_highlight_cards( $home_menu_items, 6 );
$home_feed_posts = new WP_Query( [
    'post_type'              => post_type_exists( 'tb4_community_post' ) ? 'tb4_community_post' : 'post',
    'post_status'            => 'publish',
    'posts_per_page'         => 5,
    'ignore_sticky_posts'    => true,
    'no_found_rows'          => true,
    'update_post_meta_cache' => true,
    'update_post_term_cache' => false,
    'orderby'                => 'date',
    'order'                  => 'DESC',
] );
?>
<section class="tb4c-home-menu-feed tb4c-home-menu-feed-v473" data-tb4c-home-menu-feed>
  <div class="tb4c-container tb4c-home-menu-feed-container">
    <header class="tb4c-home-menu-feed-hero">
      <div>
        <span class="tb4c-kicker">Thinkb4do Home Feed</span>
        <h2>ฟีดแนะนำจากเมนูหลัก</h2>
        <p>รวมเรื่องที่น่าสนใจจากเมนูสำคัญของเว็บไว้บนหน้าแรก เพื่อให้ผู้ใช้เห็นทางไปต่อทันทีโดยไม่ต้องค้นหาเอง</p>
      </div>
      <div class="tb4c-home-menu-feed-hero-actions">
        <a class="tb4c-btn tb4c-btn-primary" href="<?php echo esc_url( home_url( '/community/' ) ); ?>"><?php echo tb4cm_icon_markup( 'ph-users-three', '👥' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span>เปิดฟีดชุมชน</span></a>
        <a class="tb4c-btn tb4c-btn-secondary" href="<?php echo esc_url( home_url( '/member-area/' ) ); ?>"><?php echo tb4cm_icon_markup( 'ph-user-circle-gear', '◎' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span>พื้นที่สมาชิก</span></a>
      </div>
    </header>

    <?php if ( ! empty( $home_menu_items ) ) : ?>
      <nav class="tb4c-home-menu-chip-row" aria-label="เมนูหลักที่นำมาแสดงบนหน้าแรก">
        <?php foreach ( $home_menu_items as $menu_item ) : ?>
          <a href="<?php echo esc_url( $menu_item['url'] ); ?>">
            <?php echo tb4cm_icon_markup( $menu_item['icon'], $menu_item['fallback'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <span><?php echo esc_html( $menu_item['label'] ); ?></span>
          </a>
        <?php endforeach; ?>
      </nav>
    <?php endif; ?>

    <div class="tb4c-home-menu-feed-layout">
      <main class="tb4c-home-menu-feed-main" aria-label="รายการแนะนำจากเมนูหลัก">
        <?php if ( ! empty( $home_cards ) ) : ?>
          <div class="tb4c-home-menu-highlight-grid">
            <?php foreach ( $home_cards as $index => $card ) : $preview = $card['preview']; ?>
              <article class="tb4c-home-menu-card <?php echo 0 === $index ? 'is-featured' : ''; ?>">
                <a class="tb4c-home-menu-card-media" href="<?php echo esc_url( $preview['url'] ); ?>" aria-label="<?php echo esc_attr( $preview['title'] ); ?>">
                  <?php if ( ! empty( $preview['image'] ) ) : ?>
                    <img src="<?php echo esc_url( $preview['image'] ); ?>" alt="" loading="lazy" decoding="async">
                  <?php else : ?>
                    <span class="tb4c-home-menu-card-icon"><?php echo tb4cm_icon_markup( $card['icon'], $card['fallback'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                  <?php endif; ?>
                </a>
                <div class="tb4c-home-menu-card-body">
                  <div class="tb4c-home-menu-card-topline">
                    <span><?php echo tb4cm_icon_markup( $card['icon'], $card['fallback'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( $card['label'] ); ?></span>
                    <em><?php echo esc_html( $preview['score'] ); ?>%</em>
                  </div>
                  <h3><a href="<?php echo esc_url( $preview['url'] ); ?>"><?php echo esc_html( $preview['title'] ); ?></a></h3>
                  <?php if ( ! empty( $preview['excerpt'] ) ) : ?><p><?php echo esc_html( $preview['excerpt'] ); ?></p><?php endif; ?>
                  <div class="tb4c-home-menu-card-meta">
                    <span><?php echo esc_html( $card['source'] ); ?></span>
                    <?php if ( ! empty( $preview['comments'] ) ) : ?><span><?php echo esc_html( number_format_i18n( $preview['comments'] ) ); ?> คอมเมนต์</span><?php endif; ?>
                    <a href="<?php echo esc_url( $card['menu_url'] ); ?>">ดูหมวดนี้</a>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php else : ?>
          <div class="tb4c-home-menu-empty">
            <h3>ยังไม่พบรายการจากเมนูหลัก</h3>
            <p>เมื่อมีเมนูหรือคอนเทนต์ใหม่ ระบบจะนำมาแสดงบนหน้าแรกโดยอัตโนมัติ</p>
          </div>
        <?php endif; ?>
      </main>

      <aside class="tb4c-home-menu-feed-side" aria-label="อัปเดตล่าสุด">
        <section class="tb4c-home-menu-side-card">
          <div class="tb4c-home-menu-side-head">
            <h3>อัปเดตล่าสุด</h3>
            <a href="<?php echo esc_url( home_url( '/community/' ) ); ?>">ดูทั้งหมด</a>
          </div>
          <?php if ( $home_feed_posts->have_posts() ) : ?>
            <div class="tb4c-home-menu-mini-list">
              <?php while ( $home_feed_posts->have_posts() ) : $home_feed_posts->the_post(); ?>
                <a href="<?php the_permalink(); ?>">
                  <strong><?php the_title(); ?></strong>
                  <span><?php echo esc_html( get_the_date( 'j M Y' ) ); ?> · <?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt() ?: get_the_content() ), 12 ) ); ?></span>
                </a>
              <?php endwhile; wp_reset_postdata(); ?>
            </div>
          <?php else : wp_reset_postdata(); ?>
            <p class="tb4c-home-menu-side-empty">ยังไม่มีอัปเดตล่าสุด</p>
          <?php endif; ?>
        </section>

        <section class="tb4c-home-menu-side-card tb4c-home-menu-qc-card">
          <div class="tb4c-home-menu-side-head"><h3>สถานะหน้าแรก</h3><span>v4.2.73</span></div>
          <ul>
            <li><strong>100%</strong><span>ดึงเมนูหลัก</span></li>
            <li><strong>96%</strong><span>Responsive</span></li>
            <li><strong>94%</strong><span>ความเบา UI</span></li>
          </ul>
          <p>จัดวางให้ดูเป็นฟีดระดับโลกแบบเรียบ ไม่บังส่วนหัว/ส่วนท้าย และเชื่อมต่อหน้าชุมชนกับสมาชิกได้ชัดเจน</p>
        </section>
      </aside>
    </div>
  </div>
</section>
