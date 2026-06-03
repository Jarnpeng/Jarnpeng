</div><!-- /#main-content -->

<?php $tb4_hide_site_footer = function_exists( 'tb4_should_hide_footer_on_community' ) && tb4_should_hide_footer_on_community(); ?>
<?php if ( ! $tb4_hide_site_footer ) : ?>
<!-- ==================== FOOTER ==================== -->
<footer data-tb4-live-component="footer" data-tb4-live-title="Footer / ท้ายเว็บ" id="site-footer" role="contentinfo">
  <div class="container">

    <!-- Top Grid -->
    <div class="footer-top">

      <!-- Brand -->
      <div class="footer-brand">
        <?php tb4_render_site_logo( 'footer' ); ?>
        <p><?php echo esc_html( get_theme_mod('tb4_tagline','Think before you do.') ); ?> — <?php echo esc_html( tb4_footer_summary_text() ); ?></p>
        <div class="footer-socials">
          <?php
          $socials = [
            'facebook' => ['ph-facebook-logo', 'Facebook'],
            'youtube'  => ['ph-youtube-logo',  'YouTube'],
            'tiktok'   => ['ph-tiktok-logo',   'TikTok'],
            'twitter'  => ['ph-x-logo',        'X'],
            'line'     => ['ph-chat-circle-text','Line'],
          ];
          foreach ( $socials as $key => [$icon, $label] ) {
            $url = get_theme_mod("tb4_social_{$key}", '#');
            if ($url && $url !== '#') {
              echo '<a href="' . esc_url($url) . '" class="footer-social" aria-label="' . esc_attr($label) . '" target="_blank" rel="noopener noreferrer"><i class="ph ' . esc_attr($icon) . '"></i></a>';
            }
          }
          ?>
        </div>
      </div>

      <!-- Col 2 -->
      <div class="footer-col">
        <h5>เกี่ยวกับเรา</h5>
        <div class="footer-links">
          <a href="<?php echo esc_url(home_url('/about/')); ?>">เกี่ยวกับ Thinkb4do</a>
          <a href="<?php echo esc_url(home_url('/contact/')); ?>">ติดต่อเรา</a>
          <a href="<?php echo esc_url(home_url('/careers/')); ?>">ร่วมงานกับเรา</a>
          <a href="<?php echo esc_url(home_url('/press/')); ?>">บันทึกการพัฒนา</a>
          <a href="<?php echo esc_url(home_url('/blog/')); ?>">บล็อก</a>
        </div>
      </div>

      <!-- Col 3 -->
      <div class="footer-col">
        <h5>ช่วยเหลือ</h5>
        <div class="footer-links">
          <a href="<?php echo esc_url(home_url('/help/')); ?>">ศูนย์ช่วยเหลือ</a>
          <a href="<?php echo esc_url(home_url('/faq/')); ?>">คำถามที่พบบ่อย</a>
          <a href="<?php echo esc_url(home_url('/terms/')); ?>">ข้อกำหนดการใช้งาน</a>
          <a href="<?php echo esc_url(home_url('/trust-registration/')); ?>">Trust Center</a>
          <a href="<?php echo esc_url(home_url('/trust-registration/')); ?>">ความน่าเชื่อถือ</a>
          <a href="<?php echo esc_url(home_url('/privacy/')); ?>">นโยบายความเป็นส่วนตัว</a>
          <a href="<?php echo esc_url(home_url('/report/')); ?>">รายงานปัญหา</a>
        </div>
      </div>

      <!-- Col 4 Newsletter -->
      <div class="footer-col">
        <h5><?php echo esc_html( tb4_dev_text( 'newsletter_title' ) ); ?></h5>
        <p class="tb4-footer-note"><?php echo esc_html( tb4_dev_text( 'footer_newsletter_body' ) ); ?></p>
        <div class="footer-newsletter">
          <input type="email" placeholder="อีเมลของคุณ" aria-label="อีเมลสำหรับรับข่าวสาร">
          <button type="button">รับข่าว</button>
        </div>
        <p class="tb4-footer-note tb4-footer-note-small"><?php echo esc_html( tb4_dev_text( 'footer_newsletter_note' ) ); ?></p>
      </div>
    </div>

    <?php if ( get_theme_mod( 'tb4_dbd_show_footer', true ) ) : ?>
      <?php tb4_render_dbd_registration_block( 'footer' ); ?>
    <?php endif; ?>

    <?php if ( get_theme_mod( 'tb4_compat_show_footer', false ) ) : ?>
      <div class="tb4-footer-compat" role="note">
        <i class="ph ph-devices" aria-hidden="true"></i>
        <span><?php echo esc_html( tb4_compat_setting( 'section_desc' ) ); ?></span>
      </div>
    <?php endif; ?>

    <!-- Bottom -->
    <div class="footer-bottom">
      <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. All rights reserved.</p>
      <div class="footer-bottom-links">
          <a href="<?php echo esc_url(home_url('/privacy/')); ?>">นโยบายความเป็นส่วนตัว</a>
        <a href="<?php echo esc_url(home_url('/terms/')); ?>">ข้อกำหนดการใช้งาน</a>
        <a href="<?php echo esc_url(home_url('/cookies/')); ?>">การตั้งค่า Cookie</a>
        <a href="<?php echo esc_url(home_url('/sitemap.xml')); ?>">Sitemap</a>
      </div>
    </div>
  </div>
</footer>
<!-- /FOOTER -->
<?php endif; ?>

<?php if ( function_exists( 'tb4_render_mobile_bottom_nav' ) && ( ! function_exists( 'tb4_theme_should_render_mobile_bottom_nav' ) || tb4_theme_should_render_mobile_bottom_nav() ) ) { tb4_render_mobile_bottom_nav(); } ?>

<?php wp_footer(); ?>
</body>
</html>
