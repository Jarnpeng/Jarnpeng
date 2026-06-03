<?php
/**
 * Community quick composer.
 *
 * @package Thinkb4doCommunity
 */
defined( 'ABSPATH' ) || exit;
$is_logged_in = is_user_logged_in();
$current_user = $is_logged_in ? wp_get_current_user() : null;
?>
<div class="tb4c-quick-composer tb4c-app-composer tb4c-social-publisher tb4c-ig-publisher tb4c-popup-composer-trigger-card">
  <div class="tb4c-avatar">
    <?php if ( $is_logged_in ) : ?>
      <?php $avatar = get_avatar_url( $current_user->ID, [ 'size' => 48 ] ); ?>
      <?php if ( $avatar ) : ?><img src="<?php echo esc_url( $avatar ); ?>" alt=""><?php else : ?><?php echo esc_html( tb4cm_get_initial( $current_user->display_name ) ); ?><?php endif; ?>
    <?php else : ?>T<?php endif; ?>
  </div>
  <button class="tb4c-composer-trigger" type="button" data-tb4c-open-composer>
    <?php echo $is_logged_in ? 'เขียนโพสต์ใหม่...' : 'เข้าสู่ระบบเพื่อโพสต์หรือคอมเมนต์'; ?>
  </button>
</div>
