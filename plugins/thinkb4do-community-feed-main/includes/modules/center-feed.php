<?php
/**
 * Center Feed module for Thinkb4do Community.
 *
 * @package Thinkb4doCommunity
 */
defined( 'ABSPATH' ) || exit;
?>
    <main class="tb4c-social-feed tb4c-premium-social-feed" data-tb4c-module="center-feed" aria-label="ฟีดชุมชน">
      <?php // v4.2.205: reference-style content head + clear feed menu; keeps left/center/right layout locked. ?>

      <header class="tb4c-reference-page-head tb4c-feed-reference-head" data-tb4c-smart-back-target aria-label="หัวข้อชุมชน">
        <div class="tb4c-reference-title">
          <h1>ชุมชน Thinkb4do</h1>
          <p>พื้นที่เรียนรู้ แบ่งปัน และเติบโตไปด้วยกัน</p>
        </div>
      </header>

      <nav class="tb4c-feed-reference-tabs" aria-label="เมนูฟีดหลัก">
        <a class="<?php echo 'all' === $active_topic ? 'is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( [ 'topic' => 'all' ], home_url( '/community/' ) ) ); ?>">วันนี้</a>
        <a href="#tb4cFeaturedReels" data-tb4c-scroll-target="tb4cFeaturedReels">รีลเด่น</a>
        <a class="<?php echo 'latest' === $active_topic ? 'is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( [ 'topic' => 'latest' ], home_url( '/community/' ) ) ); ?>">โพสต์ล่าสุด</a>
      </nav>

      <div class="tb4c-focus-section-head tb4c-focus-section-head-composer" aria-label="ส่วนสร้างโพสต์"><strong>สร้างโพสต์</strong></div>

      <div class="tb4c-composer-card tb4c-social-composer-card tb4c-ig-composer-card tb4c-inline-composer-return-card" data-tb4c-inline-return-composer aria-hidden="false">
        <?php include TB4CF_DIR . 'templates/community-composer.php'; ?>
      </div>

      <div class="tb4c-rail-shell tb4c-reel-shell tb4c-featured-reels-reference" id="tb4cFeaturedReels" data-tb4c-rail-shell data-tb4c-reel-standard>
        <div class="tb4c-reel-standard-head" aria-label="ส่วน Reels มาตรฐาน">
          <div>
            <strong>รีลเด่น</strong>
          </div>
          <button class="tb4c-reel-standard-open" type="button" data-tb4c-open-reels>ดูทั้งหมด</button>
        </div>
        <button class="tb4c-reel-rail-arrow tb4c-reel-rail-prev" type="button" data-tb4c-reel-rail-prev aria-label="เลื่อน Reel ก่อนหน้า">‹</button>
        <div id="tb4cStoryRail" class="tb4c-story-rail tb4c-social-story-rail tb4c-ig-story-rail tb4c-reel-rail tb4c-reel-cover-rail" aria-label="Reels และหัวข้อแนะนำ" tabindex="0">
          <!-- v4.2.265: คงปุ่ม Next ซ้าย/ขวาให้ราง Reel และยังเปิด Viewer ได้จากการ์ด Reel เดิม. -->
          <?php if ( ! empty( $rail_reel_items ) ) : ?>
            <?php foreach ( array_slice( $rail_reel_items, 0, 6 ) as $rail_reel ) : ?>
              <?php $rail_reel_style = ! empty( $rail_reel['cover'] ) ? '--tb4c-reel-cover: url(' . esc_url( $rail_reel['cover'] ) . ');' : ''; ?>
              <button class="tb4c-story-chip tb4c-reel-cover-chip tb4c-rail-reel-card" type="button" data-tb4c-open-reels data-tb4c-reel-post-id="<?php echo esc_attr( $rail_reel['post_id'] ); ?>" style="<?php echo esc_attr( $rail_reel_style ); ?>" aria-label="เปิด Reel: <?php echo esc_attr( $rail_reel['title'] ); ?>">
                <span class="tb4c-story-cover" aria-hidden="true"></span>
                <span class="tb4c-story-ring tb4c-story-author-ring">
                  <?php if ( ! empty( $rail_reel['author_avatar'] ) ) : ?><img src="<?php echo esc_url( $rail_reel['author_avatar'] ); ?>" alt=""><?php else : ?><?php echo esc_html( tb4cf_get_initial( $rail_reel['author_name'] ) ); ?><?php endif; ?>
                </span>
                <strong><?php echo esc_html( wp_trim_words( $rail_reel['title'], 3, '…' ) ); ?></strong>
              </button>
            <?php endforeach; ?>
          <?php else : ?>
            <span class="tb4c-story-chip tb4c-story-empty-note" aria-label="ยังไม่มี Reel เพิ่มเติม">
              <span class="tb4c-story-cover" aria-hidden="true"></span>
              <span class="tb4c-story-ring"><?php echo tb4cf_icon_markup( 'ph-sparkle', '✦' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
              <strong>รอคอนเทนต์</strong>
              <small>Reel ใหม่</small>
            </span>
          <?php endif; ?>
        </div>
        <button class="tb4c-reel-rail-arrow tb4c-reel-rail-next" type="button" data-tb4c-reel-rail-next aria-label="เลื่อน Reel ถัดไป">›</button>
      </div>

      <!-- v4.2.145: duplicate topic tab rail removed; left social nav is the canonical topic navigation. -->

      <div class="tb4c-feed-section-line" aria-label="ลำดับฟีด">
        <strong>โพสต์ล่าสุด</strong>
        <span>อัปเดตจากชุมชน</span>
      </div>

      <div class="tb4c-feed-list" id="tb4cFeedList">
        <?php if ( $feed->have_posts() ) : ?>
          <?php while ( $feed->have_posts() ) : $feed->the_post(); ?>
            <?php include TB4CF_DIR . 'templates/parts/feed-post-card.php'; ?>
          <?php endwhile; wp_reset_postdata(); ?>
        <?php else : ?>
          <div class="tb4c-empty-state tb4c-social-empty-state">
            <div>🌱</div>
              <h2><?php echo esc_html( tb4cf_dev_text( 'community_empty_title' ) ); ?></h2>
              <p>เริ่มบทสนทนาแรกของชุมชน แล้วชวนผู้ร่วมชุมชนเข้ามาแลกเปลี่ยนกัน</p>
              <?php if ( $is_logged_in ) : ?><button class="tb4c-btn tb4c-btn-primary" type="button" data-tb4c-open-composer>สร้างโพสต์แรก</button><?php endif; ?>
          </div>
        <?php endif; ?>
      </div>

      <?php
      $tb4c_feed_max_pages = isset( $feed->max_num_pages ) ? (int) $feed->max_num_pages : 1;
      if ( $tb4c_feed_max_pages > 1 ) :
      ?>
        <div class="tb4c-feed-load-more-wrap" data-tb4c-feed-load-more-wrap>
          <button
            type="button"
            class="tb4c-feed-load-more"
            id="tb4cFeedLoadMore"
            data-tb4c-load-more-feed
            data-current-page="1"
            data-next-page="2"
            data-max-pages="<?php echo esc_attr( $tb4c_feed_max_pages ); ?>"
            data-topic="<?php echo esc_attr( $active_topic ); ?>"
            data-search="<?php echo esc_attr( $search_query ); ?>"
            aria-controls="tb4cFeedList"
          >
            <span class="tb4c-load-more-label">โหลดเพิ่มเติม</span>
            <small><?php echo esc_html( sprintf( 'หน้า %1$d จาก %2$d', 1, $tb4c_feed_max_pages ) ); ?></small>
          </button>
        </div>
      <?php endif; ?>
    </main>
