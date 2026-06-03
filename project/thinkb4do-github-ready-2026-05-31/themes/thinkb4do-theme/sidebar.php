<?php
/**
 * Smart Daily Sidebar
 *
 * Public-facing daily-life sidebar for events, food, holidays, followed members,
 * helpful links, and advertising interest. It intentionally avoids exposing
 * private contact details or internal system/backend information.
 *
 * @package Thinkb4do
 * @version 2.2.2
 */

defined( 'ABSPATH' ) || exit;

$tb4_sidebar_mode      = get_theme_mod( 'tb4_sidebar_mode', 'smart' );
$tb4_show_smart        = in_array( $tb4_sidebar_mode, [ 'smart', 'mixed' ], true );
$tb4_show_wp_widgets   = ( 'wordpress' === $tb4_sidebar_mode || 'mixed' === $tb4_sidebar_mode );
$tb4_daily_title       = get_theme_mod( 'tb4_sidebar_daily_title', 'ผู้ช่วยประจำวัน' );
$tb4_daily_status      = get_theme_mod( 'tb4_sidebar_daily_status', 'รวมไอเดียใกล้ตัวสำหรับวันนี้: ไปไหนดี กินอะไรดี มีอะไรน่าสนใจ และควรติดตามเรื่องไหน' );
$tb4_area_label        = get_theme_mod( 'tb4_sidebar_weather_location', 'ใกล้ฉัน' );
$tb4_daily_idea        = get_theme_mod( 'tb4_sidebar_weather_text', 'ดูไอเดียกิจกรรมใกล้ฉัน วางแผนวันหยุด เช็กอากาศก่อนออกเดินทาง และเลือกสิ่งที่เหมาะกับวันนี้' );
$tb4_daily_tip         = get_theme_mod( 'tb4_sidebar_tip', 'เลือกหนึ่งอย่างที่ช่วยให้วันนี้ดีขึ้น เช่น พักผ่อนให้พอ วางแผนมื้ออาหาร หรือหาแรงบันดาลใจจากกิจกรรมใกล้ตัว' );
$tb4_primary_cta       = get_theme_mod( 'tb4_sidebar_primary_cta', 'สำรวจไอเดียวันนี้' );
$tb4_primary_url       = get_theme_mod( 'tb4_sidebar_primary_url', apply_filters( 'tb4_community_url', home_url( '/system-guide/' ) ) );
$tb4_secondary_cta     = get_theme_mod( 'tb4_sidebar_secondary_cta', 'ค้นหาในเว็บไซต์' );
$tb4_event_text        = get_theme_mod( 'tb4_sidebar_event_text', 'วันหยุดนี้ลองเช็กกิจกรรมใกล้ตัว เช่น ตลาดสร้างสรรค์ นิทรรศการ เวิร์กช็อป หรือพื้นที่พักผ่อนใหม่ ๆ' );
$tb4_food_text         = get_theme_mod( 'tb4_sidebar_food_text', 'วันนี้กินอะไรดี? ลองเลือกจากอารมณ์วันนี้: เบา ๆ, อิ่มเร็ว, คาเฟ่ทำงาน, หรือร้านใกล้ฉัน' );
$tb4_ads_text          = get_theme_mod( 'tb4_sidebar_ads_text', 'สนใจลงโฆษณาหรือฝากแคมเปญกับ Thinkb4do สามารถเตรียมรายละเอียดสินค้า กลุ่มเป้าหมาย และงบประมาณเบื้องต้นไว้ได้' );
?>
<aside id="sidebar" class="tb4-smart-sidebar" role="complementary" aria-label="<?php esc_attr_e( 'Sidebar', 'thinkb4do' ); ?>">
  <?php if ( $tb4_show_smart ) : ?>
    <section class="tb4-smart-card tb4-smart-card-hero" aria-labelledby="tb4-smart-sidebar-title">
      <div class="tb4-smart-kicker">
        <span class="tb4-pulse-dot" aria-hidden="true"></span>
        <span><?php esc_html_e( 'Daily Life Hub', 'thinkb4do' ); ?></span>
      </div>
      <h3 id="tb4-smart-sidebar-title"><?php echo esc_html( $tb4_daily_title ); ?></h3>
      <p><?php echo esc_html( $tb4_daily_status ); ?></p>
      <div class="tb4-smart-score" aria-label="<?php esc_attr_e( 'Today idea score', 'thinkb4do' ); ?>">
        <strong>Today</strong>
        <span><?php esc_html_e( 'Ideas', 'thinkb4do' ); ?></span>
      </div>
    </section>

    <section class="tb4-smart-card tb4-daily-idea-card" aria-labelledby="tb4-daily-idea-title">
      <div class="tb4-card-head">
        <div>
          <span class="tb4-card-label"><?php esc_html_e( 'ใกล้ตัว', 'thinkb4do' ); ?></span>
          <h4 id="tb4-daily-idea-title"><?php esc_html_e( 'วันนี้ทำอะไรดี', 'thinkb4do' ); ?></h4>
        </div>
        <span class="tb4-weather-icon" aria-hidden="true">🌤️</span>
      </div>
      <div class="tb4-weather-location"><?php echo esc_html( $tb4_area_label ); ?></div>
      <p><?php echo esc_html( $tb4_daily_idea ); ?></p>
      <div class="tb4-weather-grid" role="list" aria-label="<?php esc_attr_e( 'Daily idea shortcuts', 'thinkb4do' ); ?>">
        <span role="listitem">อากาศ</span>
        <span role="listitem">กินอะไรดี</span>
        <span role="listitem">ไปไหนดี</span>
      </div>
    </section>

    <section class="tb4-smart-card tb4-event-card" aria-labelledby="tb4-event-title">
      <div class="tb4-card-head">
        <div>
          <span class="tb4-card-label"><?php esc_html_e( 'Weekend / Event', 'thinkb4do' ); ?></span>
          <h4 id="tb4-event-title"><?php esc_html_e( 'งานอีเวนต์ใกล้ฉัน', 'thinkb4do' ); ?></h4>
        </div>
        <span class="tb4-card-emoji" aria-hidden="true">📍</span>
      </div>
      <p><?php echo esc_html( $tb4_event_text ); ?></p>
      <div class="tb4-chip-row" role="list" aria-label="<?php esc_attr_e( 'Event ideas', 'thinkb4do' ); ?>">
        <a role="listitem" href="<?php echo esc_url( home_url( '/?s=' . rawurlencode( 'อีเวนต์ใกล้ฉัน' ) ) ); ?>">อีเวนต์</a>
        <a role="listitem" href="<?php echo esc_url( home_url( '/?s=' . rawurlencode( 'วันหยุดนี้เที่ยวไหนดี' ) ) ); ?>">วันหยุดนี้</a>
        <a role="listitem" href="<?php echo esc_url( home_url( '/?s=' . rawurlencode( 'กิจกรรมครอบครัว' ) ) ); ?>">ครอบครัว</a>
      </div>
    </section>

    <section class="tb4-smart-card tb4-food-card" aria-labelledby="tb4-food-title">
      <div class="tb4-card-head">
        <div>
          <span class="tb4-card-label"><?php esc_html_e( 'Food Guide', 'thinkb4do' ); ?></span>
          <h4 id="tb4-food-title"><?php esc_html_e( 'ร้านอาหารใกล้ฉัน', 'thinkb4do' ); ?></h4>
        </div>
        <span class="tb4-card-emoji" aria-hidden="true">🍽️</span>
      </div>
      <p><?php echo esc_html( $tb4_food_text ); ?></p>
      <div class="tb4-mini-link-grid">
        <a href="<?php echo esc_url( home_url( '/?s=' . rawurlencode( 'ร้านอาหารใกล้ฉัน' ) ) ); ?>">ร้านใกล้ฉัน</a>
        <a href="<?php echo esc_url( home_url( '/?s=' . rawurlencode( 'วันนี้กินอะไรดี' ) ) ); ?>">วันนี้กินอะไร</a>
        <a href="<?php echo esc_url( home_url( '/?s=' . rawurlencode( 'คาเฟ่ทำงาน' ) ) ); ?>">คาเฟ่ทำงาน</a>
        <a href="<?php echo esc_url( home_url( '/?s=' . rawurlencode( 'ของกินวันหยุด' ) ) ); ?>">วันหยุด</a>
      </div>
    </section>

    <section class="tb4-smart-card tb4-calendar-card" aria-labelledby="tb4-calendar-title">
      <div class="tb4-card-head">
        <div>
          <span class="tb4-card-label"><?php esc_html_e( 'Calendar', 'thinkb4do' ); ?></span>
          <h4 id="tb4-calendar-title"><?php esc_html_e( 'วันสำคัญ / สมาชิกที่ติดตาม', 'thinkb4do' ); ?></h4>
        </div>
        <span class="tb4-card-emoji" aria-hidden="true">🎂</span>
      </div>
      <div class="tb4-agenda-list">
        <a href="<?php echo esc_url( home_url( '/?s=' . rawurlencode( 'วันหยุดสำคัญ' ) ) ); ?>"><span>📅</span><strong>วันหยุดสำคัญ</strong><small>ดูไอเดียเตรียมตัวก่อนวันหยุด</small></a>
        <?php foreach ( apply_filters( 'tb4_sidebar_agenda_links', [] ) as $tb4_agenda_link ) : ?>
          <a href="<?php echo esc_url( $tb4_agenda_link['url'] ?? '#' ); ?>"><span><?php echo esc_html( $tb4_agenda_link['icon'] ?? '•' ); ?></span><strong><?php echo esc_html( $tb4_agenda_link['title'] ?? '' ); ?></strong><small><?php echo esc_html( $tb4_agenda_link['desc'] ?? '' ); ?></small></a>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="tb4-smart-card tb4-quick-tools" aria-labelledby="tb4-quick-tools-title">
      <div class="tb4-card-head">
        <div>
          <span class="tb4-card-label"><?php esc_html_e( 'Quick Actions', 'thinkb4do' ); ?></span>
          <h4 id="tb4-quick-tools-title"><?php esc_html_e( 'ทางลัดที่ใช้บ่อย', 'thinkb4do' ); ?></h4>
        </div>
      </div>
      <nav class="tb4-tool-list" aria-label="<?php esc_attr_e( 'Quick sidebar links', 'thinkb4do' ); ?>">
        <?php
        $tb4_tool_links = apply_filters( 'tb4_sidebar_tool_links', [
          [ 'url' => home_url( '/products/' ), 'icon' => '📦', 'title' => 'ผลิตภัณฑ์' ],
          [ 'url' => home_url( '/system-guide/' ), 'icon' => '🧭', 'title' => 'คู่มือระบบ' ],
          [ 'url' => home_url( '/legal-center/' ), 'icon' => '🛡️', 'title' => 'นโยบาย' ],
        ] );
        foreach ( $tb4_tool_links as $tb4_tool_link ) : ?>
          <a href="<?php echo esc_url( $tb4_tool_link['url'] ?? '#' ); ?>"><span><?php echo esc_html( $tb4_tool_link['icon'] ?? '•' ); ?></span><strong><?php echo esc_html( $tb4_tool_link['title'] ?? '' ); ?></strong></a>
        <?php endforeach; ?>
      </nav>
    </section>

    <section class="tb4-smart-card tb4-search-card" aria-labelledby="tb4-search-title">
      <h4 id="tb4-search-title"><?php echo esc_html( $tb4_secondary_cta ); ?></h4>
      <?php get_search_form(); ?>
    </section>

    <section class="tb4-smart-card tb4-tip-card" aria-labelledby="tb4-tip-title">
      <div class="tb4-card-head">
        <div>
          <span class="tb4-card-label"><?php esc_html_e( 'Thinkb4do Tip', 'thinkb4do' ); ?></span>
          <h4 id="tb4-tip-title"><?php esc_html_e( 'แนะนำวันนี้', 'thinkb4do' ); ?></h4>
        </div>
      </div>
      <p><?php echo esc_html( $tb4_daily_tip ); ?></p>
      <a class="tb4-sidebar-cta" href="<?php echo esc_url( $tb4_primary_url ); ?>"><?php echo esc_html( $tb4_primary_cta ); ?> <span aria-hidden="true">→</span></a>
    </section>

    <section class="tb4-smart-card tb4-ads-card" aria-labelledby="tb4-ads-title">
      <div class="tb4-card-head">
        <div>
          <span class="tb4-card-label"><?php esc_html_e( 'Sponsor', 'thinkb4do' ); ?></span>
          <h4 id="tb4-ads-title"><?php esc_html_e( 'สนใจลงโฆษณา', 'thinkb4do' ); ?></h4>
        </div>
        <span class="tb4-card-emoji" aria-hidden="true">📣</span>
      </div>
      <p><?php echo esc_html( $tb4_ads_text ); ?></p>
      <a class="tb4-sidebar-soft-cta" href="<?php echo esc_url( home_url( '/advertise/' ) ); ?>"><?php esc_html_e( 'ดูพื้นที่โฆษณา', 'thinkb4do' ); ?> <span aria-hidden="true">→</span></a>
    </section>

    <?php
    $tb4_recent_posts = new WP_Query( [
        'posts_per_page'      => 4,
        'post_status'         => 'publish',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    ] );
    ?>
    <section class="tb4-smart-card tb4-recent-card" aria-labelledby="tb4-recent-title">
      <div class="tb4-card-head">
        <div>
          <span class="tb4-card-label"><?php esc_html_e( 'Content', 'thinkb4do' ); ?></span>
          <h4 id="tb4-recent-title"><?php esc_html_e( 'สิ่งที่น่าอ่าน', 'thinkb4do' ); ?></h4>
        </div>
      </div>
      <?php if ( $tb4_recent_posts->have_posts() ) : ?>
        <div class="tb4-recent-list">
          <?php while ( $tb4_recent_posts->have_posts() ) : $tb4_recent_posts->the_post(); ?>
            <a class="tb4-recent-item" href="<?php the_permalink(); ?>">
              <span><?php echo esc_html( get_the_date( 'j M' ) ); ?></span>
              <strong><?php the_title(); ?></strong>
            </a>
          <?php endwhile; wp_reset_postdata(); ?>
        </div>
      <?php else : ?>
        <p class="tb4-muted"><?php esc_html_e( 'ยังไม่มีบทความเผยแพร่ เมื่อมีเนื้อหาใหม่จะแสดงที่นี่อัตโนมัติ', 'thinkb4do' ); ?></p>
      <?php endif; ?>
    </section>

    <section class="tb4-smart-card tb4-admin-note-card" aria-label="<?php esc_attr_e( 'Sidebar status', 'thinkb4do' ); ?>">
      <small>Settings | โดย Thinkb4do | ดูรายละเอียด</small>
    </section>
  <?php endif; ?>

  <?php if ( $tb4_show_wp_widgets ) : ?>
    <section class="tb4-wp-widget-zone" aria-label="<?php esc_attr_e( 'ส่วนเสริมไซด์บาร์', 'thinkb4do' ); ?>">
      <?php if ( is_active_sidebar( 'sidebar-main' ) ) : ?>
        <div class="tb4-wp-widget-title"><?php esc_html_e( 'ส่วนเสริมไซด์บาร์', 'thinkb4do' ); ?></div>
        <?php dynamic_sidebar( 'sidebar-main' ); ?>
      <?php elseif ( current_user_can( 'edit_theme_options' ) ) : ?>
        <div class="tb4-smart-card">
          <h4><?php esc_html_e( 'ยังไม่มีส่วนเสริมเพิ่มเติม', 'thinkb4do' ); ?></h4>
          <p class="tb4-muted"><?php esc_html_e( 'เพิ่มได้ที่หน้าตั้งค่าธีม → ส่วนเสริมไซด์บาร์', 'thinkb4do' ); ?></p>
        </div>
      <?php endif; ?>
    </section>
  <?php endif; ?>
</aside>
