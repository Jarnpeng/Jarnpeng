<?php
/**
 * Plugin Name: Thinkb4do Plugin Bug Inspector
 * Plugin URI: https://thinkb4do.com
 * Description: ตรวจบั๊กปลั๊กอิน WordPress แบบเจาะลึก อ่านเป้าหมายระบบ ตรวจความสัมพันธ์ภายใน ตรวจรองรับทุกบราวเซอร์/อุปกรณ์/ระบบ และ export รายงานเป็นไฟล์ .txt ได้
 * Version: 1.4.0
 * Author: Thinkb4do
 * Author URI: https://thinkb4do.com
 * Text Domain: thinkb4do-plugin-bug-inspector
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * License: GPLv2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'TBPBI_VERSION', '1.4.0' );
define( 'TBPBI_FILE', __FILE__ );
define( 'TBPBI_PATH', plugin_dir_path( __FILE__ ) );
define( 'TBPBI_URL', plugin_dir_url( __FILE__ ) );
define( 'TBPBI_BASENAME', plugin_basename( __FILE__ ) );

require_once TBPBI_PATH . 'includes/class-relationship-checker.php';
require_once TBPBI_PATH . 'includes/class-detail-analyzer.php';
require_once TBPBI_PATH . 'includes/class-system-goal-reader.php';
require_once TBPBI_PATH . 'includes/class-system-xray-analyzer.php';
require_once TBPBI_PATH . 'includes/class-quality-standard-checker.php';
require_once TBPBI_PATH . 'includes/class-scanner.php';
require_once TBPBI_PATH . 'includes/class-report-builder.php';
require_once TBPBI_PATH . 'includes/class-exporter.php';
require_once TBPBI_PATH . 'admin/class-admin.php';

add_action(
    'plugins_loaded',
    static function () {
        if ( is_admin() ) {
            TBPBI_Admin::instance();
        }
    }
);

register_activation_hook(
    __FILE__,
    static function () {
        add_option( 'tbpbi_safe_static_mode', '1' );
        add_option( 'tbpbi_mask_sensitive_data', '1' );
        add_option( 'tbpbi_deep_xray_mode', '1' );
        add_option( 'tbpbi_cross_platform_mode', '1' );
    }
);
