<?php
/**
 * Plugin Name: Thinkb4do Products
 * Plugin URI: https://thinkb4do.com
 * Description: ระบบผลิตภัณฑ์ Thinkb4do พร้อม Product Portal ระดับพรีเมียม หน้ารายละเอียดสินค้า Affiliate Connect หลายแหล่ง Booking Connect ภายนอก เปรียบเทียบสินค้า โหมดล็อกการซื้อด้วยไอคอน และ UI มือถือแบบพรีเมียม และศูนย์ตั้งค่าทีมงานที่ใช้งานง่าย พร้อม App Store ในพื้นที่สมาชิก ระบบชำระเงิน ส่งบิลทางอีเมล ส่งไฟล์ และติดตั้งทันที
 * Version: 2.2.0
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Author: Thinkb4do
 * Author URI: https://thinkb4do.com
 * License: GPL v2 or later
 * Text Domain: thinkb4do-products
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'TB4_PRODUCTS_PLUGIN_VERSION', '2.2.0' );
define( 'TB4_PRODUCTS_PLUGIN_FILE', __FILE__ );
define( 'TB4_PRODUCTS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'TB4_PRODUCTS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once TB4_PRODUCTS_PLUGIN_DIR . 'includes/class-tb4-products.php';
require_once TB4_PRODUCTS_PLUGIN_DIR . 'includes/class-tb4-products-app-store.php';

function tb4_products_plugin() {
    return TB4_Products_Plugin::instance();
}

add_action( 'plugins_loaded', 'tb4_products_plugin' );

register_activation_hook( __FILE__, [ 'TB4_Products_Plugin', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'TB4_Products_Plugin', 'deactivate' ] );
