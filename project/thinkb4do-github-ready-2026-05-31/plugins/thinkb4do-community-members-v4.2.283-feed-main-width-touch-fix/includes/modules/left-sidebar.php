<?php
/**
 * Left Sidebar module for Thinkb4do Community.
 * v4.2.175: label markup is intentionally simple to avoid old tooltip / ellipsis CSS.
 *
 * @package Thinkb4doCommunity
 */
defined( 'ABSPATH' ) || exit;
?>
    <aside class="tb4c-social-left tb4c-left-sidebar-final" data-tb4c-module="left-sidebar" aria-label="เมนูชุมชน">
      <nav class="tb4c-left-final-menu" aria-label="ทางลัดชุมชน">
        <!-- v4.2.181: removed duplicate sidebar title/icon; header already identifies Community. -->
        <?php foreach ( $tabs as $slug => $tab ) :
            $url = add_query_arg( [ 'topic' => $slug ], home_url( '/community/' ) );
        ?>
          <a class="tb4c-left-final-link <?php echo $active_topic === $slug ? 'is-active' : ''; ?>" href="<?php echo esc_url( $url ); ?>" aria-label="<?php echo esc_attr( $tab['label'] ); ?>">
            <span class="tb4c-left-final-icon" aria-hidden="true"><?php echo tb4cm_icon_markup( $tab['icon'], $tab['symbol'], 'tb4c-left-final-icon-inner' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
            <span class="tb4c-left-label-text"><?php echo esc_html( $tab['label'] ); ?></span>
          </a>
        <?php endforeach; ?>
      </nav>
    </aside>
