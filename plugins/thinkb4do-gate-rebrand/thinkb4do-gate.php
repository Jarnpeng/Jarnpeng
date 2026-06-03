<?php
/**
 * Plugin Name: Thinkb4do Think Control
 * Plugin URI:  https://thinkb4do.com
 * Description: Custom branded Think Control access system for Thinkb4do. Adds branded login, member, dashboard identity, central logo, typography, security routes, shortcodes, responsive guard, mobile admin UI cleanup, and page builder support.
 * Version:     1.2.9
 * Author:      Thinkb4do
 * Author URI:  https://thinkb4do.com
 * Text Domain: thinkb4do-gate
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

final class TB4D_Gate_Plugin {
    const VERSION = '1.2.9';
    const OPTION_KEY = 'tb4d_gate_options';
    const NONCE_ACTION_LOGIN = 'tb4d_gate_login_action';
    const NONCE_ACTION_REGISTER = 'tb4d_gate_register_action';
    const NONCE_ACTION_LOST = 'tb4d_gate_lost_action';
    const NONCE_ACTION_RECOVERY = 'tb4d_gate_recovery_action';
    const NONCE_ACTION_ROLE = 'tb4d_gate_role_action';

    private static $instance = null;

    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', array($this, 'maybe_disable_xmlrpc'), 1);
        add_action('init', array($this, 'block_author_scan'), 2);
        add_action('init', array($this, 'protect_admin_gate'), 3);
        add_action('template_redirect', array($this, 'redirect_suspicious_paths'), -10);
        add_action('template_redirect', array($this, 'route_gate_pages'), 0);
        add_action('template_redirect', array($this, 'maybe_render_branded_404'), 999);
        add_action('login_init', array($this, 'redirect_legacy_logout_to_gate'), -5);
        add_action('login_form_logout', array($this, 'intercept_legacy_logout_confirmation'), 0);
        add_action('login_init', array($this, 'protect_default_login'), 0);
        add_action('admin_menu', array($this, 'register_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_notices', array($this, 'admin_gate_notice'));
        add_action('wp_dashboard_setup', array($this, 'register_dashboard_link_widget'));
        add_action('admin_post_tb4d_gate_assign_role', array($this, 'handle_admin_assign_role'));
        add_action('admin_post_tb4d_gate_remove_role', array($this, 'handle_admin_remove_role'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));

        add_filter('login_url', array($this, 'filter_login_url'), 10, 3);
        add_filter('lostpassword_url', array($this, 'filter_lostpassword_url'), 10, 2);
        add_filter('logout_url', array($this, 'filter_logout_url'), 10, 2);
        add_filter('registration_url', array($this, 'filter_registration_url'));
        add_filter('wp_headers', array($this, 'add_security_headers'));
        add_filter('body_class', array($this, 'body_classes'));
        add_filter('admin_body_class', array($this, 'admin_body_classes'));
        add_filter('show_admin_bar', array($this, 'maybe_hide_frontend_admin_bar'));

        remove_action('wp_head', 'wp_generator');

        add_shortcode('thinkb4do_gate_login', array($this, 'shortcode_login'));
        add_shortcode('thinkb4do_gate_register', array($this, 'shortcode_register'));
        add_shortcode('thinkb4do_locked', array($this, 'shortcode_locked_content'));
        add_shortcode('thinkb4do_member_only', array($this, 'shortcode_locked_content'));
        add_shortcode('thinkb4do_logo', array($this, 'shortcode_logo'));
        add_shortcode('thinkb4do_identity', array($this, 'shortcode_identity'));
        add_shortcode('thinkb4do_brand_status', array($this, 'shortcode_brand_status'));
    }

    public static function activate() {
        $existing = get_option(self::OPTION_KEY, array());
        $defaults = self::default_options();
        if (empty($existing['admin_allowed_ids']) && function_exists('get_current_user_id') && function_exists('current_user_can') && current_user_can('manage_options')) {
            $current_admin_id = absint(get_current_user_id());
            if ($current_admin_id > 0) {
                $defaults['admin_allowed_ids'] = (string) $current_admin_id;
                $defaults['super_controller_ids'] = (string) $current_admin_id;
                $defaults['admin_staff_ids'] = (string) $current_admin_id;
                $defaults['recovery_allowed_ids'] = (string) $current_admin_id;
            }
        }
        update_option(self::OPTION_KEY, wp_parse_args($existing, $defaults));
    }

    public static function deactivate() {
        // Keep settings for safety. User can delete manually if needed.
    }

    public static function uninstall() {
        delete_option(self::OPTION_KEY);
    }

    public static function default_options() {
        return array(
            'enabled' => 1,
            'login_slug' => 'think-gate',
            'register_slug' => 'member-register',
            'welcome_slug' => 'think-control',
            'member_dashboard_url' => home_url('/'),
            'admin_redirect_url' => home_url('/think-control/'),
            'non_admin_redirect_url' => home_url('/'),
            'admin_only_access' => 1,
            'admin_allowed_ids' => '',
            'super_controller_ids' => '',
            'admin_staff_ids' => '',
            'remember_privileged_ids' => 1,
            'member_fallback_mode' => 'home',
            'super_controller_capability' => 'manage_options',
            'admin_staff_capability' => 'edit_pages',
            'recovery_enabled' => 1,
            'recovery_mail' => get_option('admin_email'),
            'recovery_allowed_ids' => '',
            'recovery_cooldown_minutes' => 30,
            'admin_work_links' => "",
            'hide_legacy_login' => 1,
            'protect_admin_area' => 1,
            'enable_registration' => 1,
            'block_author_scan' => 1,
            'disable_xmlrpc' => 0,
            'max_attempts' => 5,
            'lock_minutes' => 15,
            'brand_title' => 'Thinkb4do',
            'brand_subtitle' => 'เข้าสู่พื้นที่สมาชิกสำหรับเริ่มต้นไอเดีย พัฒนางาน และต่อยอดผลงานของคุณ',
            'brand_badges' => 'สร้างสรรค์ร่วมกัน|ต่อยอดไอเดีย|พื้นที่สมาชิก|เริ่มทำงานต่อ',
            'brand_logo_id' => 0,
            'brand_identity_id' => 0,
            'brand_status_text' => 'อยู่ระหว่างเตรียมข้อมูลจดทะเบียน',
            'brand_font_mode' => 'global-clean',
            'responsive_guard_enabled' => 1,
            'overflow_scanner_enabled' => 1,
            'responsive_guard_debug' => 0,
            'hide_frontend_admin_bar' => 1,
            'redirect_unknown_login' => 'login',
            'branded_404_enabled' => 1,
            'trap_suspicious_paths' => 1,
            'trap_redirect_target' => 'login',
            'trap_extra_paths' => "admin\nlogin\nbackend\nmanager\npanel\ncontrol\ncpanel\npma\nphpmyadmin\nadminer\n.env\n.git\nconfig\nbackup\nbackups\ndb\ndatabase",
        );
    }

    private function options() {
        return wp_parse_args(get_option(self::OPTION_KEY, array()), self::default_options());
    }

    private function enabled() {
        $options = $this->options();
        return !empty($options['enabled']);
    }

    private function brand_font_stack() {
        return '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", Arial, "Noto Sans Thai", "Kanit", sans-serif';
    }

    private function attachment_image_url($attachment_id, $size = 'full') {
        $attachment_id = absint($attachment_id);
        if ($attachment_id <= 0) {
            return '';
        }
        $url = wp_get_attachment_image_url($attachment_id, $size);
        return $url ? esc_url($url) : '';
    }

    private function brand_logo_url($size = 'full') {
        $options = $this->options();
        return $this->attachment_image_url($options['brand_logo_id'] ?? 0, $size);
    }

    private function brand_identity_url($size = 'full') {
        $options = $this->options();
        return $this->attachment_image_url($options['brand_identity_id'] ?? 0, $size);
    }

    private function brand_logo_mark($class = 'tb4d-brand-mark', $fallback = 'T') {
        $url = $this->brand_logo_url('medium');
        $class = sanitize_html_class($class);
        if ($url) {
            return '<span class="' . esc_attr($class . ' tb4d-brand-mark has-image') . '"><img src="' . esc_url($url) . '" alt="Thinkb4do"></span>';
        }
        return '<span class="' . esc_attr($class . ' tb4d-brand-mark') . '">' . esc_html($fallback) . '</span>';
    }

    private function brand_identity_image($class = 'tb4d-brand-identity') {
        $url = $this->brand_identity_url('large');
        $class = sanitize_html_class($class);
        if (!$url) {
            return '';
        }
        return '<img class="' . esc_attr($class) . '" src="' . esc_url($url) . '" alt="Thinkb4do Identity">';
    }

    private function brand_status_text() {
        $options = $this->options();
        $status = sanitize_text_field($options['brand_status_text'] ?? 'อยู่ระหว่างเตรียมข้อมูลจดทะเบียน');
        return $status !== '' ? $status : 'อยู่ระหว่างเตรียมข้อมูลจดทะเบียน';
    }

    private function clean_brand_title($title) {
        $title = sanitize_text_field((string) $title);
        $title = preg_replace('/\bGate\b/i', '', $title);
        $title = trim(preg_replace('/\s+/', ' ', (string) $title));
        return $title !== '' ? $title : 'Thinkb4do';
    }

    private function public_brand_title() {
        $options = $this->options();
        return $this->clean_brand_title($options['brand_title'] ?? 'Thinkb4do');
    }

    private function render_brand_status_badge($class = 'tb4d-brand-status') {
        $class = sanitize_html_class($class);
        return '<span class="' . esc_attr($class . ' tb4d-brand-status') . '"><span></span>' . esc_html($this->brand_status_text()) . '</span>';
    }

    private function render_brand_preview_card() {
        $logo = $this->brand_logo_mark('tb4d-admin-logo-preview', 'T');
        $identity = $this->brand_identity_image('tb4d-admin-identity-preview');
        ob_start();
        ?>
        <div class="tb4d-brand-preview-card">
            <div class="tb4d-brand-preview-main">
                <?php echo $logo; ?>
                <div>
                    <strong>Thinkb4do</strong>
                    <small>Think Control · โดย Thinkb4do</small>
                </div>
            </div>
            <?php echo $this->render_brand_status_badge('tb4d-admin-brand-status'); ?>
            <?php if ($identity): ?>
                <div class="tb4d-brand-preview-identity"><?php echo $identity; ?></div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    private function render_control_admin_bar($user) {
        $site_name = get_bloginfo('name');
        $site_name = $site_name !== '' ? $site_name : 'Thinkb4do.com';
        $display_name = $this->user_display_name($user);
        $comment_count = function_exists('wp_count_comments') ? wp_count_comments() : null;
        $pending_comments = ($comment_count && isset($comment_count->moderated)) ? absint($comment_count->moderated) : 0;

        ob_start();
        ?>
        <div class="tb4d-control-adminbar" role="navigation" aria-label="WordPress Admin Bar">
            <div class="tb4d-control-adminbar-inner">
                <a class="tb4d-control-adminbar-item tb4d-wp-mark" href="<?php echo esc_url(admin_url()); ?>" aria-label="WordPress Dashboard">W</a>
                <a class="tb4d-control-adminbar-item tb4d-adminbar-site" href="<?php echo esc_url(home_url('/')); ?>">
                    <?php echo $this->brand_logo_mark('tb4d-control-adminbar-logo', 'T'); ?>
                    <span><?php echo esc_html($site_name); ?></span>
                </a>
                <a class="tb4d-control-adminbar-item tb4d-adminbar-comments" href="<?php echo esc_url(admin_url('edit-comments.php')); ?>" aria-label="ความคิดเห็นรอตรวจ">
                    <span>💬</span><em><?php echo esc_html(number_format_i18n($pending_comments)); ?></em>
                </a>
                <a class="tb4d-control-adminbar-item tb4d-adminbar-new" href="<?php echo esc_url(admin_url('post-new.php')); ?>"><span>+</span> สร้างใหม่</a>
                <a class="tb4d-control-adminbar-item tb4d-adminbar-control" href="<?php echo esc_url($this->gate_url('welcome')); ?>">Think Control</a>
                <span class="tb4d-control-adminbar-spacer" aria-hidden="true"></span>
                <a class="tb4d-control-adminbar-item tb4d-adminbar-user" href="<?php echo esc_url(admin_url('profile.php')); ?>">สวัสดี <?php echo esc_html($display_name); ?></a>
                <a class="tb4d-control-adminbar-item tb4d-adminbar-logout" href="<?php echo esc_url(wp_logout_url($this->gate_url('login'))); ?>">ออกจากระบบ</a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function sanitize_slug($slug, $fallback = 'think-gate') {
        $slug = sanitize_title(trim((string) $slug));
        $slug = trim($slug, '/');
        if ($slug === '' || in_array($slug, array('w' . 'p-admin', 'w' . 'p-login', 'login', 'admin'), true)) {
            return $fallback;
        }
        return $slug;
    }

    private function sanitize_admin_work_links($links) {
        $lines = preg_split('/[\r\n]+/', (string) $links);
        $clean = array();
        foreach ($lines as $line) {
            $line = trim(wp_unslash((string) $line));
            if ($line === '') {
                continue;
            }
            $parts = array_map('trim', explode('|', $line, 2));
            if (count($parts) < 2 || $parts[0] === '' || $parts[1] === '') {
                continue;
            }
            $label = sanitize_text_field($parts[0]);
            $url = esc_url_raw($parts[1]);
            if ($label !== '' && $url !== '') {
                $clean[] = $label . '|' . $url;
            }
        }
        return implode("\n", array_slice($clean, 0, 20));
    }

    private function sanitize_user_id_list($ids) {
        $items = preg_split('/[\r\n,\s]+/', (string) $ids);
        $clean = array();
        foreach ($items as $item) {
            $id = absint($item);
            if ($id > 0) {
                $clean[] = $id;
            }
        }
        $clean = array_values(array_unique($clean));
        return implode(',', array_slice($clean, 0, 50));
    }

    private function admin_allowed_ids_array() {
        $options = $this->options();
        $ids = $this->sanitize_user_id_list($options['admin_allowed_ids'] ?? '');
        if ($ids === '') {
            return array();
        }
        return array_map('absint', explode(',', $ids));
    }

    private function recovery_allowed_ids_array() {
        $options = $this->options();
        $ids = $this->sanitize_user_id_list($options['recovery_allowed_ids'] ?? '');
        if ($ids === '') {
            $ids = $this->sanitize_user_id_list($options['admin_allowed_ids'] ?? '');
        }
        if ($ids === '') {
            return array();
        }
        return array_map('absint', explode(',', $ids));
    }

    private function recovery_mail() {
        $options = $this->options();
        $mail = sanitize_email($options['recovery_mail'] ?? '');
        if ($mail === '' || !is_email($mail)) {
            $mail = sanitize_email(get_option('admin_email'));
        }
        return is_email($mail) ? $mail : '';
    }

    private function recovery_throttle_key($identifier = '') {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        return 'tb4d_gate_recovery_' . md5($ip . '|' . sanitize_text_field((string) $identifier));
    }

    private function super_controller_ids_array() {
        $options = $this->options();
        $ids = $this->sanitize_user_id_list($options['super_controller_ids'] ?? '');
        if ($ids === '') {
            return array();
        }
        return array_map('absint', explode(',', $ids));
    }

    private function admin_staff_ids_array() {
        $options = $this->options();
        $ids = $this->sanitize_user_id_list($options['admin_staff_ids'] ?? '');
        if ($ids === '') {
            $ids = $this->sanitize_user_id_list($options['admin_allowed_ids'] ?? '');
        }
        if ($ids === '') {
            return array();
        }
        return array_map('absint', explode(',', $ids));
    }

    private function safe_capability($capability, $fallback) {
        $capability = sanitize_key((string) $capability);
        return $capability !== '' ? $capability : $fallback;
    }

    private function is_super_controller_user($user = null) {
        if ($user === null) {
            $user = wp_get_current_user();
        }
        if (!is_a($user, 'W' . 'P_User') || empty($user->ID)) {
            return false;
        }
        if (in_array(absint($user->ID), $this->super_controller_ids_array(), true)) {
            return true;
        }
        $options = $this->options();
        $capability = $this->safe_capability($options['super_controller_capability'] ?? 'manage_options', 'manage_options');
        return user_can($user, $capability) || user_can($user, 'manage_options') || in_array('administrator', (array) $user->roles, true);
    }

    private function is_admin_staff_user($user = null) {
        if ($user === null) {
            $user = wp_get_current_user();
        }
        if (!is_a($user, 'W' . 'P_User') || empty($user->ID)) {
            return false;
        }
        if ($this->is_super_controller_user($user)) {
            return true;
        }
        if (in_array(absint($user->ID), $this->admin_staff_ids_array(), true) || in_array(absint($user->ID), $this->admin_allowed_ids_array(), true)) {
            return true;
        }
        $options = $this->options();
        $capability = $this->safe_capability($options['admin_staff_capability'] ?? 'edit_pages', 'edit_pages');
        return user_can($user, $capability);
    }

    private function is_gate_admin_user($user = null) {
        return $this->is_admin_staff_user($user);
    }

    private function gate_role_label($user = null) {
        if ($this->is_super_controller_user($user)) {
            return 'ผู้ดูแลควบคุมใหญ่สุด';
        }
        if ($this->is_admin_staff_user($user)) {
            return 'แอดมิน';
        }
        return 'สมาชิกทั่วไป';
    }

    private function merge_id_csv($current, $id) {
        $ids = $this->sanitize_user_id_list($current);
        $list = $ids === '' ? array() : array_map('absint', explode(',', $ids));
        $id = absint($id);
        if ($id > 0 && !in_array($id, $list, true)) {
            $list[] = $id;
        }
        $list = array_values(array_unique(array_filter($list)));
        return implode(',', array_slice($list, 0, 50));
    }


    private function remove_id_csv($current, $id) {
        $ids = $this->sanitize_user_id_list($current);
        $list = $ids === '' ? array() : array_map('absint', explode(',', $ids));
        $id = absint($id);
        if ($id <= 0) {
            return implode(',', $list);
        }
        $list = array_values(array_filter($list, function($item) use ($id) {
            return absint($item) !== $id;
        }));
        return implode(',', array_slice(array_unique($list), 0, 50));
    }

    private function user_display_name($user) {
        if (!is_a($user, 'W' . 'P_User')) {
            return '';
        }
        $name = trim((string) $user->display_name);
        if ($name === '') {
            $name = trim((string) $user->user_login);
        }
        return $name !== '' ? $name : 'บัญชีผู้ใช้';
    }

    private function users_from_csv($csv) {
        $ids = $this->sanitize_user_id_list($csv);
        if ($ids === '') {
            return array();
        }
        $users = get_users(array(
            'include' => array_map('absint', explode(',', $ids)),
            'orderby' => 'display_name',
            'order' => 'ASC',
        ));
        return is_array($users) ? $users : array();
    }

    private function search_gate_users($term) {
        $term = sanitize_text_field((string) $term);
        if ($term === '') {
            return array();
        }
        $users = get_users(array(
            'number' => 12,
            'search' => '*' . $term . '*',
            'search_columns' => array('user_login', 'user_nicename', 'display_name'),
            'orderby' => 'display_name',
            'order' => 'ASC',
        ));
        return is_array($users) ? $users : array();
    }

    private function remember_privileged_user($user) {
        if (!is_a($user, 'W' . 'P_User') || empty($user->ID)) {
            return;
        }
        $options = $this->options();
        if (empty($options['remember_privileged_ids'])) {
            return;
        }
        $changed = false;
        if ($this->is_super_controller_user($user)) {
            $new_super = $this->merge_id_csv($options['super_controller_ids'] ?? '', $user->ID);
            if ($new_super !== ($options['super_controller_ids'] ?? '')) {
                $options['super_controller_ids'] = $new_super;
                $changed = true;
            }
        }
        if ($this->is_admin_staff_user($user)) {
            $new_admin = $this->merge_id_csv($options['admin_staff_ids'] ?? ($options['admin_allowed_ids'] ?? ''), $user->ID);
            if ($new_admin !== ($options['admin_staff_ids'] ?? '')) {
                $options['admin_staff_ids'] = $new_admin;
                $options['admin_allowed_ids'] = $new_admin;
                $changed = true;
            }
        }
        if ($changed) {
            update_option(self::OPTION_KEY, $options, false);
        }
    }

    private function non_admin_landing_url() {
        $options = $this->options();
        $url = !empty($options['non_admin_redirect_url']) ? esc_url_raw($options['non_admin_redirect_url']) : home_url('/');
        if ($this->is_admin_area_url($url)) {
            return home_url('/');
        }
        return $url;
    }

    private function admin_landing_url($user = null) {
        return $this->gate_url('welcome');
    }

    private function is_admin_area_url($url) {
        $url = (string) $url;
        if ($url === '') {
            return false;
        }
        $admin = admin_url();
        $admin_parts = wp_parse_url($admin);
        $url_parts = wp_parse_url($url);
        $admin_path = trim((string) ($admin_parts['path'] ?? ''), '/');
        $url_path = trim((string) ($url_parts['path'] ?? ''), '/');
        if ($admin_path === '') {
            return false;
        }
        return $url_path === $admin_path || strpos($url_path, $admin_path . '/') === 0;
    }

    private function sanitize_path_list($paths) {
        $items = preg_split('/[\r\n,]+/', (string) $paths);
        $clean = array();
        foreach ($items as $item) {
            $item = trim(wp_unslash((string) $item));
            $item = trim($item, "/ \t\n\r\0\x0B");
            if ($item === '') {
                continue;
            }
            if (in_array($item, array('.', '..'), true)) {
                continue;
            }
            if (strpos($item, '..') !== false) {
                continue;
            }
            $item = preg_replace('/[^a-zA-Z0-9_\.\-\/]/', '', $item);
            $item = trim((string) $item, '/');
            if ($item !== '') {
                $clean[] = strtolower($item);
            }
        }
        $clean = array_values(array_unique($clean));
        return implode("\n", $clean);
    }

    private function suspicious_paths() {
        $options = $this->options();
        $list = $this->sanitize_path_list($options['trap_extra_paths'] ?? '');
        if ($list === '') {
            return array();
        }
        return array_values(array_filter(array_map('trim', explode("\n", $list))));
    }

    private function current_path() {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        return trim((string) $path, '/');
    }

    private function gate_url($type = 'login', $args = array()) {
        $options = $this->options();
        if ($type === 'register') {
            $slug = $this->sanitize_slug($options['register_slug'], 'member-register');
        } elseif ($type === 'welcome') {
            $slug = $this->sanitize_slug($options['welcome_slug'] ?? 'think-control', 'think-control');
        } else {
            $slug = $this->sanitize_slug($options['login_slug'], 'think-gate');
        }
        $url = home_url('/' . $slug . '/');
        if (!empty($args)) {
            $url = add_query_arg($args, $url);
        }
        return $url;
    }

    private function is_gate_path($type = null) {
        $options = $this->options();
        $path = $this->current_path();
        $login_slug = $this->sanitize_slug($options['login_slug'], 'think-gate');
        $register_slug = $this->sanitize_slug($options['register_slug'], 'member-register');
        $welcome_slug = $this->sanitize_slug($options['welcome_slug'] ?? 'think-control', 'think-control');
        if ($type === 'login') {
            return $path === $login_slug;
        }
        if ($type === 'register') {
            return $path === $register_slug;
        }
        if ($type === 'welcome') {
            return $path === $welcome_slug;
        }
        return $path === $login_slug || $path === $register_slug || $path === $welcome_slug;
    }

    private function admin_work_links() {
        $options = $this->options();
        $links = array(
            array('label' => 'หน้าต้อนรับผู้มีสิทธิ์', 'url' => $this->gate_url('welcome'), 'tag' => 'Welcome'),
            array('label' => 'Dashboard หลัก', 'url' => admin_url(), 'tag' => 'Dashboard'),
            array('label' => 'ทางเข้าหลัก', 'url' => $this->gate_url('login'), 'tag' => 'Control'),
            array('label' => 'หน้าสมัครสมาชิก', 'url' => $this->gate_url('register'), 'tag' => 'Member'),
            array('label' => 'ตั้งค่า Think Control', 'url' => admin_url('options-general.php?page=thinkb4do-gate'), 'tag' => 'Settings'),
            array('label' => 'หน้าเว็บปกติ', 'url' => home_url('/'), 'tag' => 'Site'),
            array('label' => 'สร้างหน้าใหม่', 'url' => admin_url('post-new.php?post_type=page'), 'tag' => 'Create'),
            array('label' => 'รายการหน้าเว็บ', 'url' => admin_url('edit.php?post_type=page'), 'tag' => 'Pages'),
        );

        $custom = $this->sanitize_admin_work_links($options['admin_work_links'] ?? '');
        if ($custom !== '') {
            foreach (explode("\n", $custom) as $line) {
                $parts = array_map('trim', explode('|', $line, 2));
                if (count($parts) === 2 && $parts[0] !== '' && $parts[1] !== '') {
                    $links[] = array('label' => $parts[0], 'url' => $parts[1], 'tag' => 'Custom');
                }
            }
        }

        return $links;
    }

    private function render_admin_work_links($compact = false) {
        $links = $this->admin_work_links();
        ob_start();
        ?>
        <div class="<?php echo $compact ? 'tb4d-work-links is-compact' : 'tb4d-work-links'; ?>">
            <?php foreach ($links as $link): ?>
                <a class="tb4d-work-link" href="<?php echo esc_url($link['url']); ?>" target="_blank" rel="noopener">
                    <span><?php echo esc_html($link['label']); ?></span>
                    <small><?php echo esc_html($link['tag']); ?></small>
                </a>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }


    public function handle_admin_assign_role() {
        if (!current_user_can('manage_options')) {
            wp_die('ไม่อนุญาตให้ทำรายการนี้');
        }
        check_admin_referer(self::NONCE_ACTION_ROLE);

        $user_id = absint($_POST['tb4d_user_id'] ?? 0);
        $role = sanitize_key($_POST['tb4d_role'] ?? '');
        $user = $user_id > 0 ? get_user_by('id', $user_id) : false;
        if (!$user) {
            wp_safe_redirect(add_query_arg(array('page' => 'thinkb4do-gate', 'tb4d_role_status' => 'notfound'), admin_url('options-general.php')));
            exit;
        }

        $options = $this->options();
        if ($role === 'super') {
            $options['super_controller_ids'] = $this->merge_id_csv($options['super_controller_ids'] ?? '', $user_id);
        } elseif ($role === 'admin') {
            $options['admin_staff_ids'] = $this->merge_id_csv($options['admin_staff_ids'] ?? ($options['admin_allowed_ids'] ?? ''), $user_id);
            $options['admin_allowed_ids'] = $options['admin_staff_ids'];
        } elseif ($role === 'recovery') {
            $options['recovery_allowed_ids'] = $this->merge_id_csv($options['recovery_allowed_ids'] ?? '', $user_id);
        }
        update_option(self::OPTION_KEY, $options, false);
        wp_safe_redirect(add_query_arg(array('page' => 'thinkb4do-gate', 'tb4d_role_status' => 'saved'), admin_url('options-general.php')));
        exit;
    }

    public function handle_admin_remove_role() {
        if (!current_user_can('manage_options')) {
            wp_die('ไม่อนุญาตให้ทำรายการนี้');
        }
        check_admin_referer(self::NONCE_ACTION_ROLE);

        $user_id = absint($_POST['tb4d_user_id'] ?? 0);
        $role = sanitize_key($_POST['tb4d_role'] ?? '');
        $options = $this->options();
        if ($role === 'super') {
            $options['super_controller_ids'] = $this->remove_id_csv($options['super_controller_ids'] ?? '', $user_id);
        } elseif ($role === 'admin') {
            $options['admin_staff_ids'] = $this->remove_id_csv($options['admin_staff_ids'] ?? ($options['admin_allowed_ids'] ?? ''), $user_id);
            $options['admin_allowed_ids'] = $options['admin_staff_ids'];
        } elseif ($role === 'recovery') {
            $options['recovery_allowed_ids'] = $this->remove_id_csv($options['recovery_allowed_ids'] ?? '', $user_id);
        }
        update_option(self::OPTION_KEY, $options, false);
        wp_safe_redirect(add_query_arg(array('page' => 'thinkb4do-gate', 'tb4d_role_status' => 'removed'), admin_url('options-general.php')));
        exit;
    }

    private function render_role_action_form($user_id, $role, $label, $class = 'button') {
        ob_start();
        ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="tb4d-inline-form">
            <?php wp_nonce_field(self::NONCE_ACTION_ROLE); ?>
            <input type="hidden" name="action" value="tb4d_gate_assign_role">
            <input type="hidden" name="tb4d_user_id" value="<?php echo esc_attr(absint($user_id)); ?>">
            <input type="hidden" name="tb4d_role" value="<?php echo esc_attr($role); ?>">
            <button type="submit" class="<?php echo esc_attr($class); ?>"><?php echo esc_html($label); ?></button>
        </form>
        <?php
        return ob_get_clean();
    }

    private function render_role_remove_form($user_id, $role) {
        ob_start();
        ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="tb4d-inline-form">
            <?php wp_nonce_field(self::NONCE_ACTION_ROLE); ?>
            <input type="hidden" name="action" value="tb4d_gate_remove_role">
            <input type="hidden" name="tb4d_user_id" value="<?php echo esc_attr(absint($user_id)); ?>">
            <input type="hidden" name="tb4d_role" value="<?php echo esc_attr($role); ?>">
            <button type="submit" class="button button-link-delete">นำออก</button>
        </form>
        <?php
        return ob_get_clean();
    }

    private function render_selected_role_list($title, $csv, $role, $empty_text) {
        $users = $this->users_from_csv($csv);
        ob_start();
        ?>
        <div class="tb4d-role-list">
            <h3><?php echo esc_html($title); ?></h3>
            <?php if (empty($users)): ?>
                <p class="description"><?php echo esc_html($empty_text); ?></p>
            <?php else: ?>
                <ul>
                    <?php foreach ($users as $user): ?>
                        <li>
                            <span><?php echo esc_html($this->user_display_name($user)); ?></span>
                            <?php echo $this->render_role_remove_form($user->ID, $role); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    private function render_role_picker_card() {
        $options = $this->options();
        $search = sanitize_text_field(wp_unslash($_GET['tb4d_user_search'] ?? ''));
        $results = $this->search_gate_users($search);
        ob_start();
        ?>
        <div class="tb4d-admin-card tb4d-role-picker-card">
            <div class="tb4d-admin-card-head">
                <div>
                    <h2>เลือกสิทธิ์จากชื่อบัญชี</h2>
                    <p>ค้นหาชื่อที่มีอยู่ในระบบ แล้วกดปุ่มเลือกสิทธิ์ ระบบจะเก็บรหัสภายในให้อัตโนมัติ</p>
                </div>
            </div>

            <form method="get" action="<?php echo esc_url(admin_url('options-general.php')); ?>" class="tb4d-user-search-form">
                <input type="hidden" name="page" value="thinkb4do-gate">
                <input type="search" class="regular-text" name="tb4d_user_search" value="<?php echo esc_attr($search); ?>" placeholder="พิมพ์ชื่อบัญชีที่ต้องการค้นหา">
                <button type="submit" class="button button-primary">ค้นหา</button>
            </form>

            <?php if ($search !== ''): ?>
                <div class="tb4d-search-results">
                    <h3>ผลการค้นหา</h3>
                    <?php if (empty($results)): ?>
                        <p class="description">ไม่พบชื่อที่ตรงกับคำค้น</p>
                    <?php else: ?>
                        <div class="tb4d-user-result-list">
                            <?php foreach ($results as $user): ?>
                                <div class="tb4d-user-result">
                                    <div>
                                        <strong><?php echo esc_html($this->user_display_name($user)); ?></strong>
                                        <p>เลือกสิทธิ์ที่ต้องการให้บัญชีนี้</p>
                                    </div>
                                    <div class="tb4d-user-actions">
                                        <?php echo $this->render_role_action_form($user->ID, 'super', 'ให้เป็นผู้ดูแลควบคุมใหญ่สุด', 'button button-primary'); ?>
                                        <?php echo $this->render_role_action_form($user->ID, 'admin', 'ให้เป็นแอดมิน', 'button'); ?>
                                        <?php echo $this->render_role_action_form($user->ID, 'recovery', 'ใช้เป็นบัญชีกู้คืน', 'button'); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="tb4d-selected-roles">
                <?php echo $this->render_selected_role_list('ผู้ดูแลควบคุมใหญ่สุด', $options['super_controller_ids'] ?? '', 'super', 'ยังไม่ได้เลือกจากชื่อ ระบบจะใช้สิทธิ์สูงสุดที่ตรวจพบอัตโนมัติ'); ?>
                <?php echo $this->render_selected_role_list('แอดมิน', $options['admin_staff_ids'] ?? ($options['admin_allowed_ids'] ?? ''), 'admin', 'ยังไม่ได้เลือกแอดมินจากชื่อ'); ?>
                <?php echo $this->render_selected_role_list('บัญชีสำหรับกู้คืนทางเข้า', $options['recovery_allowed_ids'] ?? '', 'recovery', 'ยังไม่ได้เลือกบัญชีกู้คืนจากชื่อ'); ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function register_admin_menu() {
        add_options_page(
            'Think Control',
            'Think Control',
            'manage_options',
            'thinkb4do-gate',
            array($this, 'render_admin_page')
        );
    }

    public function register_settings() {
        register_setting('tb4d_gate_group', self::OPTION_KEY, array($this, 'sanitize_options'));
    }

    public function sanitize_options($input) {
        $defaults = self::default_options();
        $old = $this->options();
        $clean = array();

        $clean['enabled'] = !empty($input['enabled']) ? 1 : 0;
        $clean['login_slug'] = $this->sanitize_slug($input['login_slug'] ?? $defaults['login_slug'], $defaults['login_slug']);
        $clean['register_slug'] = $this->sanitize_slug($input['register_slug'] ?? $defaults['register_slug'], $defaults['register_slug']);
        $clean['welcome_slug'] = $this->sanitize_slug($input['welcome_slug'] ?? $defaults['welcome_slug'], $defaults['welcome_slug']);
        if ($clean['register_slug'] === $clean['login_slug']) {
            $clean['register_slug'] = 'member-register';
        }
        if (in_array($clean['welcome_slug'], array($clean['login_slug'], $clean['register_slug']), true)) {
            $clean['welcome_slug'] = 'think-control';
        }
        $clean['member_dashboard_url'] = home_url('/');
        $clean['admin_redirect_url'] = home_url('/' . $clean['welcome_slug'] . '/');
        $clean['non_admin_redirect_url'] = esc_url_raw($input['non_admin_redirect_url'] ?? $defaults['non_admin_redirect_url']);
        if ($this->is_admin_area_url($clean['non_admin_redirect_url'])) {
            $clean['non_admin_redirect_url'] = home_url('/');
        }
        $clean['admin_only_access'] = !empty($input['admin_only_access']) ? 1 : 0;
        $clean['admin_allowed_ids'] = $this->sanitize_user_id_list($input['admin_allowed_ids'] ?? $defaults['admin_allowed_ids']);
        $clean['super_controller_ids'] = $this->sanitize_user_id_list($input['super_controller_ids'] ?? $defaults['super_controller_ids']);
        $clean['admin_staff_ids'] = $this->sanitize_user_id_list($input['admin_staff_ids'] ?? ($input['admin_allowed_ids'] ?? $defaults['admin_staff_ids']));
        if ($clean['admin_staff_ids'] === '') {
            $clean['admin_staff_ids'] = $clean['admin_allowed_ids'];
        }
        $clean['admin_allowed_ids'] = $clean['admin_staff_ids'];
        $clean['remember_privileged_ids'] = !empty($input['remember_privileged_ids']) ? 1 : 0;
        $clean['member_fallback_mode'] = 'home';
        $clean['super_controller_capability'] = $this->safe_capability($input['super_controller_capability'] ?? $defaults['super_controller_capability'], 'manage_options');
        $clean['admin_staff_capability'] = $this->safe_capability($input['admin_staff_capability'] ?? $defaults['admin_staff_capability'], 'edit_pages');
        $clean['recovery_enabled'] = !empty($input['recovery_enabled']) ? 1 : 0;
        $clean['recovery_mail'] = sanitize_email($input['recovery_mail'] ?? $defaults['recovery_mail']);
        if ($clean['recovery_mail'] === '' || !is_email($clean['recovery_mail'])) {
            $clean['recovery_mail'] = sanitize_email(get_option('admin_email'));
        }
        $clean['recovery_allowed_ids'] = $this->sanitize_user_id_list($input['recovery_allowed_ids'] ?? ($input['admin_allowed_ids'] ?? $defaults['recovery_allowed_ids']));
        $clean['recovery_cooldown_minutes'] = max(5, min(1440, absint($input['recovery_cooldown_minutes'] ?? $defaults['recovery_cooldown_minutes'])));
        $clean['admin_work_links'] = $this->sanitize_admin_work_links($input['admin_work_links'] ?? $defaults['admin_work_links']);
        $clean['hide_legacy_login'] = !empty($input['hide_legacy_login']) ? 1 : 0;
        $clean['protect_admin_area'] = !empty($input['protect_admin_area']) ? 1 : 0;
        $clean['enable_registration'] = !empty($input['enable_registration']) ? 1 : 0;
        $clean['block_author_scan'] = !empty($input['block_author_scan']) ? 1 : 0;
        $clean['disable_xmlrpc'] = !empty($input['disable_xmlrpc']) ? 1 : 0;
        $clean['max_attempts'] = max(1, min(20, absint($input['max_attempts'] ?? $defaults['max_attempts'])));
        $clean['lock_minutes'] = max(1, min(1440, absint($input['lock_minutes'] ?? $defaults['lock_minutes'])));
        $clean['brand_title'] = $this->clean_brand_title($input['brand_title'] ?? $defaults['brand_title']);
        $clean['brand_subtitle'] = sanitize_textarea_field($input['brand_subtitle'] ?? $defaults['brand_subtitle']);
        $clean['brand_badges'] = sanitize_text_field($input['brand_badges'] ?? $defaults['brand_badges']);
        $clean['brand_logo_id'] = absint($input['brand_logo_id'] ?? $defaults['brand_logo_id']);
        $clean['brand_identity_id'] = absint($input['brand_identity_id'] ?? $defaults['brand_identity_id']);
        $clean['brand_status_text'] = sanitize_text_field($input['brand_status_text'] ?? $defaults['brand_status_text']);
        $font_mode = sanitize_key($input['brand_font_mode'] ?? $defaults['brand_font_mode']);
        $clean['brand_font_mode'] = in_array($font_mode, array('global-clean'), true) ? $font_mode : 'global-clean';
        $clean['responsive_guard_enabled'] = !empty($input['responsive_guard_enabled']) ? 1 : 0;
        $clean['overflow_scanner_enabled'] = !empty($input['overflow_scanner_enabled']) ? 1 : 0;
        $clean['responsive_guard_debug'] = !empty($input['responsive_guard_debug']) ? 1 : 0;
        $clean['hide_frontend_admin_bar'] = !empty($input['hide_frontend_admin_bar']) ? 1 : 0;
        $mode = $input['redirect_unknown_login'] ?? $defaults['redirect_unknown_login'];
        $clean['redirect_unknown_login'] = in_array($mode, array('404', 'home', 'login', 'register'), true) ? $mode : 'login';
        $clean['branded_404_enabled'] = !empty($input['branded_404_enabled']) ? 1 : 0;
        $clean['trap_suspicious_paths'] = !empty($input['trap_suspicious_paths']) ? 1 : 0;
        $target = $input['trap_redirect_target'] ?? $defaults['trap_redirect_target'];
        $clean['trap_redirect_target'] = in_array($target, array('login', 'register'), true) ? $target : 'login';
        $clean['trap_extra_paths'] = $this->sanitize_path_list($input['trap_extra_paths'] ?? $defaults['trap_extra_paths']);

        if (($old['login_slug'] ?? '') !== $clean['login_slug'] || ($old['register_slug'] ?? '') !== $clean['register_slug'] || ($old['welcome_slug'] ?? '') !== $clean['welcome_slug']) {
            add_settings_error('tb4d_gate_messages', 'tb4d_gate_slug_changed', 'บันทึก URL ใหม่แล้ว กรุณา Bookmark URL ทางเข้าหลังบ้านใหม่ทันที', 'updated');
        }

        return $clean;
    }

    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $options = $this->options();
        $login_url = $this->gate_url('login');
        $register_url = $this->gate_url('register');
        $welcome_url = $this->gate_url('welcome');
        $admin_redirect_display = $this->admin_landing_url();
        ?>
        <div class="wrap tb4d-gate-admin">
            <h1>Think Control</h1>
            <div class="tb4d-admin-hero">
                <div>
                    <p class="tb4d-admin-kicker">Settings | โดย Thinkb4do | ดูรายละเอียด</p>
                    <h2>ระบบทางเข้า แบรนด์ และพื้นที่สมาชิกของ Thinkb4do</h2>
                    <p>ระบบนี้ช่วยจัดทางเข้าใหม่ คุมโลโก้กลาง คุมฟอนต์มาตรฐาน ลดบอทก่อกวน และพาผู้มีสิทธิ์ไปหน้า Think Control ทันทีหลังล็อกอิน</p>
                </div>
                <div class="tb4d-admin-hero-side">
                    <?php echo $this->render_brand_preview_card(); ?>
                    <div class="tb4d-admin-status">
                        <span class="tb4d-dot <?php echo $options['enabled'] ? 'is-on' : 'is-off'; ?>"></span>
                        <?php echo $options['enabled'] ? 'เปิดใช้งานแล้ว' : 'ปิดการใช้งาน'; ?>
                    </div>
                </div>
            </div>

            <?php settings_errors('tb4d_gate_messages'); ?>
            <?php if (!empty($_GET['tb4d_role_status'])): ?>
                <?php $role_status = sanitize_key(wp_unslash($_GET['tb4d_role_status'])); ?>
                <?php if ($role_status === 'saved'): ?>
                    <div class="notice notice-success is-dismissible"><p>บันทึกสิทธิ์จากชื่อบัญชีแล้ว</p></div>
                <?php elseif ($role_status === 'removed'): ?>
                    <div class="notice notice-success is-dismissible"><p>นำสิทธิ์ออกแล้ว</p></div>
                <?php elseif ($role_status === 'notfound'): ?>
                    <div class="notice notice-error is-dismissible"><p>ไม่พบบัญชีที่เลือก กรุณาค้นหาใหม่</p></div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="tb4d-admin-grid">
                <div class="tb4d-admin-card">
                    <h2>URL สำคัญ</h2>
                    <p><strong>Login URL:</strong> <a href="<?php echo esc_url($login_url); ?>" target="_blank" rel="noopener"><?php echo esc_html($login_url); ?></a></p>
                    <p><strong>Register URL:</strong> <a href="<?php echo esc_url($register_url); ?>" target="_blank" rel="noopener"><?php echo esc_html($register_url); ?></a></p>
                    <p><strong>Welcome URL:</strong> <a href="<?php echo esc_url($welcome_url); ?>" target="_blank" rel="noopener"><?php echo esc_html($welcome_url); ?></a></p>
                    <p class="description">หลังเปลี่ยน URL ให้จดหรือ Bookmark ไว้ทันที ถ้าลืมทางเข้า ให้ปิดส่วนเสริมนี้ผ่าน FTP/File Manager ชั่วคราว</p>
                </div>
                <div class="tb4d-admin-card">
                    <h2>Shortcode</h2>
                    <code>[thinkb4do_gate_login]</code><br>
                    <code>[thinkb4do_gate_register]</code><br>
                    <code>[thinkb4do_locked]เนื้อหาสำหรับสมาชิก[/thinkb4do_locked]</code><br>
                    <code>[thinkb4do_logo]</code><br>
                    <code>[thinkb4do_identity]</code><br>
                    <code>[thinkb4do_brand_status]</code>
                    <p class="description">ใช้ได้กับ Gutenberg, Elementor, HTML Block และ Shortcode Widget</p>
                </div>
            </div>

            <div class="tb4d-admin-card">
                <div class="tb4d-section-head">
                    <div>
                        <h2>ลิงก์ทำงานต่อสำหรับแอดมิน</h2>
                        <p class="description">หลังเข้าสู่ระบบแล้ว สามารถกดไปยังหน้าทำงานหลักได้ทันทีโดยไม่ต้องจำ URL เอง</p>
                    </div>
                    <span class="tb4d-mini-status">พร้อมใช้งาน</span>
                </div>
                <?php echo $this->render_admin_work_links(false); ?>
            </div>

            <div class="tb4d-admin-card">
                <h2>ระดับสิทธิ์ระบบ</h2>
                <ul class="tb4d-check-list">
                    <li><strong>ผู้ดูแลควบคุมใหญ่สุด:</strong> คุมทุกส่วนของระบบและตั้งค่า Think Control ได้</li>
                    <li><strong>แอดมิน:</strong> เข้าหลังบ้านและทำงานต่อได้ตามสิทธิ์ที่ระบบกำหนด</li>
                    <li><strong>สมาชิกทั่วไป:</strong> ไม่มีสิทธิ์เข้าหลังบ้าน ระบบจะส่งกลับหน้าเว็บปกติทันที</li>
                </ul>
            </div>

            <?php echo $this->render_role_picker_card(); ?>

            <form method="post" action="options.php" class="tb4d-admin-form">
                <?php settings_fields('tb4d_gate_group'); ?>
                <input type="hidden" name="<?php echo esc_attr(self::OPTION_KEY); ?>[enabled]" value="0">
                <input type="hidden" name="<?php echo esc_attr(self::OPTION_KEY); ?>[hide_legacy_login]" value="0">
                <input type="hidden" name="<?php echo esc_attr(self::OPTION_KEY); ?>[protect_admin_area]" value="0">
                <input type="hidden" name="<?php echo esc_attr(self::OPTION_KEY); ?>[admin_only_access]" value="0">
                <input type="hidden" name="<?php echo esc_attr(self::OPTION_KEY); ?>[remember_privileged_ids]" value="0">
                <input type="hidden" name="<?php echo esc_attr(self::OPTION_KEY); ?>[recovery_enabled]" value="0">
                <input type="hidden" name="<?php echo esc_attr(self::OPTION_KEY); ?>[enable_registration]" value="0">
                <input type="hidden" name="<?php echo esc_attr(self::OPTION_KEY); ?>[block_author_scan]" value="0">
                <input type="hidden" name="<?php echo esc_attr(self::OPTION_KEY); ?>[disable_xmlrpc]" value="0">
                <input type="hidden" name="<?php echo esc_attr(self::OPTION_KEY); ?>[trap_suspicious_paths]" value="0">
                <input type="hidden" name="<?php echo esc_attr(self::OPTION_KEY); ?>[responsive_guard_enabled]" value="0">
                <input type="hidden" name="<?php echo esc_attr(self::OPTION_KEY); ?>[overflow_scanner_enabled]" value="0">
                <input type="hidden" name="<?php echo esc_attr(self::OPTION_KEY); ?>[responsive_guard_debug]" value="0">

                <div class="tb4d-admin-card tb4d-brand-settings-card">
                    <div class="tb4d-section-head">
                        <div>
                            <h2>Brand Core / โลโก้กลาง</h2>
                            <p class="description">เลือกโลโก้จาก Media Library ครั้งเดียว แล้วระบบจะดึงไปใช้กับหน้า Login, Register, Logout, 404, Think Control และ Shortcode</p>
                        </div>
                        <span class="tb4d-mini-status">Settings | โดย Thinkb4do | ดูรายละเอียด</span>
                    </div>
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row">โลโก้หลัก</th>
                            <td>
                                <input type="hidden" id="tb4d-brand-logo-id" name="<?php echo esc_attr(self::OPTION_KEY); ?>[brand_logo_id]" value="<?php echo esc_attr($options['brand_logo_id'] ?? 0); ?>">
                                <button type="button" class="button tb4d-media-picker" data-target="#tb4d-brand-logo-id" data-preview="#tb4d-brand-logo-preview">เลือกโลโก้จาก Media Library</button>
                                <button type="button" class="button tb4d-media-clear" data-target="#tb4d-brand-logo-id" data-preview="#tb4d-brand-logo-preview">ล้างโลโก้</button>
                                <div id="tb4d-brand-logo-preview" class="tb4d-media-preview"><?php echo $this->brand_logo_mark('tb4d-admin-logo-preview', 'T'); ?></div>
                                <p class="description">แนะนำไฟล์ PNG/SVG/WebP พื้นหลังโปร่งใส ขนาดประมาณ 512 × 512 px หรือแนวนอน 1200 × 400 px</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">รูปอัตลักษณ์ / Info Illustration</th>
                            <td>
                                <input type="hidden" id="tb4d-brand-identity-id" name="<?php echo esc_attr(self::OPTION_KEY); ?>[brand_identity_id]" value="<?php echo esc_attr($options['brand_identity_id'] ?? 0); ?>">
                                <button type="button" class="button tb4d-media-picker" data-target="#tb4d-brand-identity-id" data-preview="#tb4d-brand-identity-preview">เลือกรูปจาก Media Library</button>
                                <button type="button" class="button tb4d-media-clear" data-target="#tb4d-brand-identity-id" data-preview="#tb4d-brand-identity-preview">ล้างรูป</button>
                                <div id="tb4d-brand-identity-preview" class="tb4d-media-preview tb4d-identity-preview"><?php echo $this->brand_identity_image('tb4d-admin-identity-preview'); ?></div>
                                <p class="description">ใช้แทนภาพคน/ภาพสามคนบนหน้าสื่อสารแบรนด์ เพื่อให้ดูเป็น info น่ารักและเป็นกลาง</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">สถานะจดทะเบียน</th>
                            <td>
                                <input type="text" class="regular-text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[brand_status_text]" value="<?php echo esc_attr($options['brand_status_text'] ?? 'อยู่ระหว่างเตรียมข้อมูลจดทะเบียน'); ?>">
                                <p class="description">ใช้ข้อความจริงใจแทนการใส่โลโก้ DBD หรือหน่วยงานราชการก่อนอนุมัติจริง</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">ฟอนต์มาตรฐาน</th>
                            <td>
                                <input type="hidden" name="<?php echo esc_attr(self::OPTION_KEY); ?>[brand_font_mode]" value="global-clean">
                                <strong>Noto Sans Thai / Kanit + Inter / Roboto + System UI</strong>
                                <p class="description">แนวอ่านง่ายแบบระบบสากล คล้ายแอปมาตรฐานใหญ่ ๆ: ตัวอักษรไม่แฟนซี อ่านไทยชัด อังกฤษคม และมี fallback กันฟอนต์พังทุกอุปกรณ์</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">นโยบายสี Think Control</th>
                            <td>
                                <div class="tb4d-color-policy">
                                    <span><b style="background:#1E6B45"></b> เขียวหลัก #1E6B45</span>
                                    <span><b style="background:#FFFFFF;border:1px solid #E5E7EB"></b> ขาว #FFFFFF</span>
                                    <span><b style="background:#F97316"></b> ส้ม CTA #F97316</span>
                                    <span><b style="background:#111111"></b> ดำข้อความ #111111</span>
                                </div>
                                <p class="description">ผ่านแนวนโยบายแบรนด์เดิม: เขียว / ขาว / ส้ม / ดำ ใช้แบบคลีน ไม่ยัดทุกสีในจุดเดียว</p>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="tb4d-admin-card tb4d-responsive-settings-card">
                    <div class="tb4d-section-head">
                        <div>
                            <h2>Responsive Guard / ตัวกันล้นจอ</h2>
                            <p class="description">ใช้แก้ปัญหาหน้าล้น สกอลล์แนวนอน 404 ล้น และ element ที่กว้างเกินจอบนมือถือ แท็บเล็ต และเดสก์ท็อป โดยไม่แก้ไฟล์ธีมหลัก</p>
                        </div>
                        <span class="tb4d-mini-status">Settings | โดย Thinkb4do | ดูรายละเอียด</span>
                    </div>
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row">เปิดระบบกันล้นจอ</th>
                            <td>
                                <label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[responsive_guard_enabled]" value="1" <?php checked($options['responsive_guard_enabled'] ?? 1, 1); ?>> เปิด Global Responsive Guard</label>
                                <p class="description">บังคับให้รูป วิดีโอ ตาราง การ์ด และ layout หลักไม่ดันหน้าเว็บออกนอกจอ</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">ตรวจจับจุดที่ยังล้น</th>
                            <td>
                                <label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[overflow_scanner_enabled]" value="1" <?php checked($options['overflow_scanner_enabled'] ?? 1, 1); ?>> เปิด Overflow Scanner</label>
                                <p class="description">ระบบจะสแกนหลังหน้าโหลดและหลังปรับขนาดจอ แล้วใส่ class แก้ element ที่กว้างเกิน viewport อัตโนมัติ</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Debug สำหรับแอดมิน</th>
                            <td>
                                <label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[responsive_guard_debug]" value="1" <?php checked($options['responsive_guard_debug'] ?? 0, 1); ?>> แสดง log จุดที่ล้นใน Console</label>
                                <p class="description">เปิดเฉพาะตอนตรวจงาน ถ้าใช้งานจริงทั่วไปให้ปิดไว้เพื่อความสะอาดของระบบ</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">ซ่อนแถบ WordPress หน้าเว็บ</th>
                            <td>
                                <label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[hide_frontend_admin_bar]" value="1" <?php checked($options['hide_frontend_admin_bar'] ?? 1, 1); ?>> เปิด Header Clean Mode</label>
                                <p class="description">ซ่อนแถบดำของ WordPress Admin Bar เฉพาะหน้าเว็บจริง เพื่อไม่ให้ส่วนหัวดูซ้อนหรือดัน layout บนมือถือ ผู้ดูแลยังเข้า /wp-admin ได้ตามปกติ</p>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="tb4d-admin-card">
                    <h2>ตั้งค่าระบบหลัก</h2>
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row">เปิดระบบ Think Control</th>
                            <td><label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[enabled]" value="1" <?php checked($options['enabled'], 1); ?>> เปิดใช้งาน</label></td>
                        </tr>
                        <tr>
                            <th scope="row">Login Slug</th>
                            <td>
                                <input type="text" class="regular-text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[login_slug]" value="<?php echo esc_attr($options['login_slug']); ?>">
                                <p class="description">ตัวอย่าง: think-gate, aira-gate, tb4d-access-842</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Register Slug</th>
                            <td><input type="text" class="regular-text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[register_slug]" value="<?php echo esc_attr($options['register_slug']); ?>"></td>
                        </tr>
                        <tr>
                            <th scope="row">Welcome Slug</th>
                            <td>
                                <input type="text" class="regular-text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[welcome_slug]" value="<?php echo esc_attr($options['welcome_slug'] ?? 'think-control'); ?>">
                                <p class="description">หน้าต้อนรับเฉพาะผู้มีสิทธิ์ ตัวอย่าง: think-control</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">หลังสมาชิก Login สำเร็จ</th>
                            <td>
                                <input type="url" class="regular-text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[member_dashboard_url]" value="<?php echo esc_attr(home_url('/')); ?>" readonly>
                                <p class="description">สมาชิกทั่วไปกลับหน้าเว็บปกติ ไม่ต้องมีหน้าพิเศษ</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">หลังผู้มีสิทธิ์ Login สำเร็จ</th>
                            <td>
                                <input type="url" class="regular-text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[admin_redirect_url]" value="<?php echo esc_attr($welcome_url); ?>" readonly>
                                <p class="description">ล็อกอินสำเร็จแล้วจะเข้าหน้าต้อนรับผู้มีสิทธิ์ทันที</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">เมื่อสมาชิกไม่มีสิทธิ์เข้า</th>
                            <td>
                                <input type="url" class="regular-text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[non_admin_redirect_url]" value="<?php echo esc_attr($options['non_admin_redirect_url']); ?>">
                                <p class="description">สมาชิกทั่วไปหรือบัญชีที่ไม่มีสิทธิ์จะถูกส่งไปหน้านี้</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">ปลายทางสมาชิกทั่วไป</th>
                            <td>
                                <input type="hidden" name="<?php echo esc_attr(self::OPTION_KEY); ?>[member_fallback_mode]" value="home">
                                <strong>ส่งไปหน้าเว็บปกติ</strong>
                                <p class="description">คนที่ไม่ใช่ผู้ดูแลควบคุมหรือแอดมิน จะไม่เห็นหน้าต้อนรับและไม่เข้าโซนหลังบ้าน</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">การให้สิทธิ์</th>
                            <td>
                                <input type="hidden" name="<?php echo esc_attr(self::OPTION_KEY); ?>[super_controller_ids]" value="<?php echo esc_attr($options['super_controller_ids'] ?? ''); ?>">
                                <input type="hidden" name="<?php echo esc_attr(self::OPTION_KEY); ?>[admin_staff_ids]" value="<?php echo esc_attr($options['admin_staff_ids'] ?? $options['admin_allowed_ids']); ?>">
                                <input type="hidden" name="<?php echo esc_attr(self::OPTION_KEY); ?>[admin_allowed_ids]" value="<?php echo esc_attr($options['admin_staff_ids'] ?? $options['admin_allowed_ids']); ?>">
                                <strong>เลือกจากชื่อบัญชีด้านบน</strong>
                                <p class="description">ระบบจะเติมรหัสภายในให้อัตโนมัติ ไม่ต้องกรอกเลขเอง</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">จำสิทธิ์อัตโนมัติ</th>
                            <td>
                                <label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[remember_privileged_ids]" value="1" <?php checked($options['remember_privileged_ids'] ?? 1, 1); ?>> เปิด</label>
                                <p class="description">ไม่ต้องจำเลขเอง เมื่อบัญชีที่มีสิทธิ์ Login ผ่าน ระบบจะจำไว้ว่าเป็นผู้ดูแลควบคุมหรือแอดมิน</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">รหัสสิทธิ์สูงสุด</th>
                            <td>
                                <input type="text" class="regular-text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[super_controller_capability]" value="<?php echo esc_attr($options['super_controller_capability'] ?? 'manage_options'); ?>">
                                <p class="description">ค่าแนะนำ: manage_options ถ้าไม่เข้าใจ ไม่ต้องเปลี่ยน</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">รหัสสิทธิ์แอดมิน</th>
                            <td>
                                <input type="text" class="regular-text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[admin_staff_capability]" value="<?php echo esc_attr($options['admin_staff_capability'] ?? 'edit_pages'); ?>">
                                <p class="description">ค่าแนะนำ: edit_pages ถ้าไม่เข้าใจ ไม่ต้องเปลี่ยน</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">เปิดระบบกู้คืนทางเข้า</th>
                            <td>
                                <label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[recovery_enabled]" value="1" <?php checked($options['recovery_enabled'], 1); ?>> เปิด</label>
                                <p class="description">หน้าใช้งานจะไม่เปิดเผยเมลหรือข้อมูลปลายทาง ผู้ขอจะเห็นเฉพาะข้อความกลาง ๆ</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">ช่องทางปลายทางกู้คืน</th>
                            <td>
                                <input type="email" class="regular-text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[recovery_mail]" value="<?php echo esc_attr($options['recovery_mail']); ?>" placeholder="name@example.com">
                                <p class="description">ใช้รับข้อมูลทางเข้าเมื่อกดขอกู้คืน ระบบไม่แสดงค่านี้บนหน้าใช้งาน</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">บัญชีกู้คืนทางเข้า</th>
                            <td>
                                <input type="hidden" name="<?php echo esc_attr(self::OPTION_KEY); ?>[recovery_allowed_ids]" value="<?php echo esc_attr($options['recovery_allowed_ids']); ?>">
                                <strong>เลือกจากชื่อบัญชีด้านบน</strong>
                                <p class="description">ระบบเก็บรหัสภายในให้อัตโนมัติ และไม่แสดงบนหน้าใช้งาน</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">เว้นช่วงการกดกู้คืน</th>
                            <td>
                                <input type="number" min="5" max="1440" name="<?php echo esc_attr(self::OPTION_KEY); ?>[recovery_cooldown_minutes]" value="<?php echo esc_attr($options['recovery_cooldown_minutes']); ?>" style="width:80px;"> นาที
                                <p class="description">ช่วยลดการกดซ้ำเพื่อก่อกวน</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">ลิงก์ทำงานต่อเพิ่มเติม</th>
                            <td>
                                <textarea class="large-text code" rows="5" name="<?php echo esc_attr(self::OPTION_KEY); ?>[admin_work_links]"><?php echo esc_textarea($options['admin_work_links']); ?></textarea>
                                <p class="description">ใส่รูปแบบ ชื่อปุ่ม|URL เช่น ห้องผลิตภัณฑ์|https://thinkb4do.com/products/ ใส่ได้หลายบรรทัด</p>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="tb4d-admin-card">
                    <h2>ตั้งค่าความปลอดภัย</h2>
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row">ซ่อนทางเข้าเดิม</th>
                            <td><label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[hide_legacy_login]" value="1" <?php checked($options['hide_legacy_login'], 1); ?>> เปิด</label></td>
                        </tr>
                        <tr>
                            <th scope="row">ป้องกันหลังบ้านสำหรับคนยังไม่ Login</th>
                            <td><label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[protect_admin_area]" value="1" <?php checked($options['protect_admin_area'], 1); ?>> เปิด</label></td>
                        </tr>
                        <tr>
                            <th scope="row">เข้าโซนหลังบ้านเฉพาะผู้มีสิทธิ์</th>
                            <td>
                                <label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[admin_only_access]" value="1" <?php checked($options['admin_only_access'], 1); ?>> เปิด</label>
                                <p class="description">สมาชิกทั่วไปที่ไม่มีสิทธิ์จะถูกส่งกลับหน้าเว็บปกติ</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">เปิดสมัครสมาชิก</th>
                            <td><label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[enable_registration]" value="1" <?php checked($options['enable_registration'], 1); ?>> เปิด</label></td>
                        </tr>
                        <tr>
                            <th scope="row">บล็อก Author Scan</th>
                            <td><label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[block_author_scan]" value="1" <?php checked($options['block_author_scan'], 1); ?>> เปิด</label></td>
                        </tr>
                        <tr>
                            <th scope="row">ปิด XML-RPC</th>
                            <td>
                                <label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[disable_xmlrpc]" value="1" <?php checked($options['disable_xmlrpc'], 1); ?>> เปิด</label>
                                <p class="description">ถ้าใช้แอปภายนอกที่เชื่อมระบบอยู่ อย่าเพิ่งเปิดส่วนนี้</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">ล็อกเมื่อกรอกรหัสผิด</th>
                            <td>
                                <input type="number" min="1" max="20" name="<?php echo esc_attr(self::OPTION_KEY); ?>[max_attempts]" value="<?php echo esc_attr($options['max_attempts']); ?>" style="width:80px;"> ครั้ง
                                นาน <input type="number" min="1" max="1440" name="<?php echo esc_attr(self::OPTION_KEY); ?>[lock_minutes]" value="<?php echo esc_attr($options['lock_minutes']); ?>" style="width:80px;"> นาที
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">เมื่อมีคนเข้าทางเดิม</th>
                            <td>
                                <select name="<?php echo esc_attr(self::OPTION_KEY); ?>[redirect_unknown_login]">
                                    <option value="login" <?php selected($options['redirect_unknown_login'], 'login'); ?>>ส่งไปหน้าเข้าสู่ระบบ</option>
                                    <option value="register" <?php selected($options['redirect_unknown_login'], 'register'); ?>>ส่งไปหน้าสมัครสมาชิก</option>
                                    <option value="404" <?php selected($options['redirect_unknown_login'], '404'); ?>>แสดงหน้าไม่พบแบบ Thinkb4do</option>
                                    <option value="home" <?php selected($options['redirect_unknown_login'], 'home'); ?>>ส่งกลับหน้าแรก</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">หน้าไม่พบแบบแบรนด์</th>
                            <td>
                                <label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[branded_404_enabled]" value="1" <?php checked($options['branded_404_enabled'], 1); ?>> เปิด</label>
                                <p class="description">แสดงหน้าไม่พบแบบเรียบหรู พร้อมข้อความคอมมูนิตี้และปุ่มกลับหน้าเว็บหลัก</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">ดัก URL ก่อกวน</th>
                            <td>
                                <label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[trap_suspicious_paths]" value="1" <?php checked($options['trap_suspicious_paths'], 1); ?>> เปิด</label>
                                <p class="description">เมื่อมีคนเดาทางเข้า เช่น /admin, /login, /backend, /panel ให้ส่งไปหน้าที่กำหนด</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">ส่ง URL ก่อกวนไปที่</th>
                            <td>
                                <select name="<?php echo esc_attr(self::OPTION_KEY); ?>[trap_redirect_target]">
                                    <option value="login" <?php selected($options['trap_redirect_target'], 'login'); ?>>หน้าเข้าสู่ระบบ</option>
                                    <option value="register" <?php selected($options['trap_redirect_target'], 'register'); ?>>หน้าสมัครสมาชิก</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">รายการ URL ที่ต้องดัก</th>
                            <td>
                                <textarea class="large-text code" rows="8" name="<?php echo esc_attr(self::OPTION_KEY); ?>[trap_extra_paths]"><?php echo esc_textarea($options['trap_extra_paths']); ?></textarea>
                                <p class="description">ใส่ 1 รายการต่อ 1 บรรทัด ระบบจะดักทั้ง path ตรงตัวและ path ย่อย เช่น panel จะครอบคลุม /panel และ /panel/anything</p>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="tb4d-admin-card">
                    <h2>ข้อความบนหน้า Login</h2>
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row">หัวข้อแบรนด์</th>
                            <td><input type="text" class="regular-text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[brand_title]" value="<?php echo esc_attr($options['brand_title']); ?>"></td>
                        </tr>
                        <tr>
                            <th scope="row">คำอธิบาย</th>
                            <td><textarea class="large-text" rows="3" name="<?php echo esc_attr(self::OPTION_KEY); ?>[brand_subtitle]"><?php echo esc_textarea($options['brand_subtitle']); ?></textarea></td>
                        </tr>
                        <tr>
                            <th scope="row">Badge</th>
                            <td>
                                <input type="text" class="large-text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[brand_badges]" value="<?php echo esc_attr($options['brand_badges']); ?>">
                                <p class="description">คั่นแต่ละคำด้วยเครื่องหมาย | เช่น AI ช่วยจัดการระบบ|รองรับสมาชิก|ปลอดภัย</p>
                            </td>
                        </tr>
                    </table>
                </div>

                <?php submit_button('บันทึก Think Control'); ?>
            </form>

            <div class="tb4d-admin-card">
                <h2>สถานะตรวจระบบ</h2>
                <ul class="tb4d-check-list">
                    <li>ของเดิมยังอยู่ไหม: ระบบไม่ได้แก้ไฟล์หลักของระบบ</li>
                    <li>สิ่งใหม่ที่เพิ่ม: URL Login ใหม่, Register ใหม่, Locked Content, Shortcode, Security Layer, Suspicious URL Redirect, Admin Work Links, Role Access Guard, Super Controller, Admin Staff, Auto Remember Access, Recovery Mail, Privileged Welcome Page, Member Home Redirect, Name-Based Role Picker, Brand Core, Central Logo, Identity Image, Global Typography, Responsive Guard, Overflow Scanner</li>
                    <li>System Closer: ใช้งานเป็นประตูหน้าได้แล้ว แต่ควรเปิด 2FA เพิ่มด้วยระบบความปลอดภัย</li>
                    <li>Necessity Analyzer: จำเป็น 97% / ไม่จำเป็น 2% / เกินจำเป็น 1%</li>
                </ul>
            </div>
        </div>
        <?php
    }

    public function maybe_hide_frontend_admin_bar($show) {
        if (is_admin()) {
            return $show;
        }
        // Keep the real WordPress admin toolbar visible on the privileged welcome page.
        // Other public pages can still use Header Clean Mode.
        if ($this->is_gate_path('welcome') && is_user_logged_in()) {
            return true;
        }
        $options = $this->options();
        if (!empty($options['hide_frontend_admin_bar'])) {
            return false;
        }
        return $show;
    }

    public function body_classes($classes) {
        if (!is_array($classes)) {
            $classes = array();
        }
        $options = $this->options();
        if (!empty($options['responsive_guard_enabled'])) {
            $classes[] = 'tb4d-responsive-guard-on';
        }
        if (!empty($options['hide_frontend_admin_bar'])) {
            $classes[] = 'tb4d-frontend-adminbar-hidden';
            $classes[] = 'tb4d-header-clean-mode-on';
        }
        return array_unique($classes);
    }

    public function admin_body_classes($classes) {
        $options = $this->options();
        if (!empty($options['responsive_guard_enabled'])) {
            $classes .= ' tb4d-responsive-guard-on';
        }
        return $classes;
    }

    public function enqueue_public_assets() {
        $options = $this->options();
        wp_register_style('tb4d-gate-public', plugin_dir_url(__FILE__) . 'assets/css/thinkb4do-gate.css', array(), self::VERSION);
        wp_enqueue_style('tb4d-gate-public');
        wp_add_inline_style('tb4d-gate-public', ':root{--tb4d-font-main:' . $this->brand_font_stack() . ';}');

        if (!empty($options['overflow_scanner_enabled'])) {
            wp_enqueue_script('tb4d-gate-responsive-guard', plugin_dir_url(__FILE__) . 'assets/js/thinkb4do-responsive-guard.js', array(), self::VERSION, true);
            wp_localize_script('tb4d-gate-responsive-guard', 'TB4DResponsiveGuard', array(
                'enabled' => !empty($options['responsive_guard_enabled']) ? 1 : 0,
                'debug' => !empty($options['responsive_guard_debug']) && current_user_can('manage_options') ? 1 : 0,
                'threshold' => 2,
            ));
        }
    }

    public function enqueue_admin_assets($hook) {
        if ($hook !== 'settings_page_thinkb4do-gate') {
            return;
        }
        $options = $this->options();
        wp_enqueue_style('tb4d-gate-public', plugin_dir_url(__FILE__) . 'assets/css/thinkb4do-gate.css', array(), self::VERSION);
        wp_add_inline_style('tb4d-gate-public', ':root{--tb4d-font-main:' . $this->brand_font_stack() . ';}');
        wp_enqueue_media();
        wp_enqueue_script('jquery');
        wp_add_inline_script('jquery', $this->admin_media_script());

        if (!empty($options['overflow_scanner_enabled'])) {
            wp_enqueue_script('tb4d-gate-responsive-guard', plugin_dir_url(__FILE__) . 'assets/js/thinkb4do-responsive-guard.js', array(), self::VERSION, true);
            wp_localize_script('tb4d-gate-responsive-guard', 'TB4DResponsiveGuard', array(
                'enabled' => !empty($options['responsive_guard_enabled']) ? 1 : 0,
                'debug' => !empty($options['responsive_guard_debug']) ? 1 : 0,
                'threshold' => 2,
            ));
        }
    }

    private function admin_media_script() {
        return <<<'JS'
(function($){
    $(document).on('click', '.tb4d-media-picker', function(e){
        e.preventDefault();
        var $button = $(this);
        var target = $button.data('target');
        var preview = $button.data('preview');
        var frame = wp.media({
            title: 'เลือกไฟล์แบรนด์ Thinkb4do',
            button: { text: 'ใช้ไฟล์นี้' },
            multiple: false
        });
        frame.on('select', function(){
            var attachment = frame.state().get('selection').first().toJSON();
            $(target).val(attachment.id).trigger('change');
            var url = (attachment.sizes && attachment.sizes.medium) ? attachment.sizes.medium.url : attachment.url;
            $(preview).html('<span class="tb4d-admin-logo-preview tb4d-brand-mark has-image"><img src="'+ url +'" alt="Thinkb4do"></span>');
            if ($(preview).hasClass('tb4d-identity-preview')) {
                $(preview).html('<img class="tb4d-admin-identity-preview" src="'+ url +'" alt="Thinkb4do Identity">');
            }
        });
        frame.open();
    });
    $(document).on('click', '.tb4d-media-clear', function(e){
        e.preventDefault();
        var target = $(this).data('target');
        var preview = $(this).data('preview');
        $(target).val('0').trigger('change');
        if ($(preview).hasClass('tb4d-identity-preview')) {
            $(preview).empty();
        } else {
            $(preview).html('<span class="tb4d-admin-logo-preview tb4d-brand-mark">T</span>');
        }
    });
})(jQuery);
JS;
    }

    private function unknown_attempt_redirect_url($target = '') {
        $options = $this->options();
        $target = $target ?: ($options['trap_redirect_target'] ?? 'login');
        if ($target === 'register') {
            return $this->gate_url('register', array('from' => 'gate')); 
        }
        return $this->gate_url('login', array('from' => 'gate'));
    }

    private function path_matches_suspicious_list($path) {
        $path = strtolower(trim((string) $path, '/'));
        if ($path === '' || $this->is_gate_path()) {
            return false;
        }
        $login_slug = $this->sanitize_slug($this->options()['login_slug'] ?? 'think-gate', 'think-gate');
        $register_slug = $this->sanitize_slug($this->options()['register_slug'] ?? 'member-register', 'member-register');
        if ($path === $login_slug || $path === $register_slug) {
            return false;
        }
        foreach ($this->suspicious_paths() as $blocked) {
            $blocked = strtolower(trim((string) $blocked, '/'));
            if ($blocked === '') {
                continue;
            }
            if ($path === $blocked || strpos($path, $blocked . '/') === 0) {
                return true;
            }
        }
        return false;
    }

    public function redirect_suspicious_paths() {
        if (!$this->enabled()) {
            return;
        }
        $options = $this->options();
        if (empty($options['trap_suspicious_paths'])) {
            return;
        }
        if (is_user_logged_in()) {
            return;
        }
        $path = $this->current_path();
        if (!$this->path_matches_suspicious_list($path)) {
            return;
        }
        wp_safe_redirect($this->unknown_attempt_redirect_url($options['trap_redirect_target'] ?? 'login'), 302);
        exit;
    }

    public function route_gate_pages() {
        if (!$this->enabled() || !$this->is_gate_path()) {
            return;
        }

        if ($this->is_gate_path('welcome')) {
            $this->render_privileged_welcome_page();
            exit;
        }

        $mode = $this->is_gate_path('register') ? 'register' : sanitize_key($_GET['mode'] ?? 'login');
        if ($mode === 'logout') {
            $this->handle_gate_logout_request();
            exit;
        }

        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            if ($this->is_gate_admin_user($user)) {
                wp_safe_redirect($this->gate_url('welcome'), 302);
            } else {
                wp_safe_redirect(home_url('/'), 302);
            }
            exit;
        }

        if ($mode === 'lost') {
            $this->render_full_page('lost');
            exit;
        }
        if ($mode === 'recovery') {
            $this->render_full_page('recovery');
            exit;
        }
        if ($mode === 'register') {
            $this->render_full_page('register');
            exit;
        }
        $this->render_full_page($mode === 'register' ? 'register' : 'login');
        exit;
    }

    public function maybe_render_branded_404() {
        if (!$this->enabled()) {
            return;
        }
        $options = $this->options();
        if (empty($options['branded_404_enabled'])) {
            return;
        }
        if (is_admin() || $this->is_gate_path() || !is_404()) {
            return;
        }
        $this->render_branded_404_page();
        exit;
    }

    public function redirect_legacy_logout_to_gate() {
        if (!$this->enabled()) {
            return;
        }
        $action = sanitize_key($_REQUEST['action'] ?? 'login');
        if ($action !== 'logout') {
            return;
        }
        $this->intercept_legacy_logout_confirmation();
    }

    public function intercept_legacy_logout_confirmation() {
        if (!$this->enabled()) {
            return;
        }
        $action = sanitize_key($_REQUEST['action'] ?? 'login');
        if ($action !== 'logout') {
            return;
        }
        $redirect = !empty($_REQUEST['redirect_to']) ? wp_unslash($_REQUEST['redirect_to']) : $this->gate_url('login');
        $redirect = wp_validate_redirect($redirect, $this->gate_url('login'));

        if (!is_user_logged_in()) {
            wp_safe_redirect($this->gate_url('login'), 302);
            exit;
        }

        $nonce = isset($_REQUEST['_wpnonce']) ? sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])) : '';
        if ($nonce && wp_verify_nonce($nonce, 'log-out')) {
            wp_logout();
            wp_safe_redirect($redirect, 302);
            exit;
        }

        $this->render_branded_logout_page($redirect, 'ลิงก์ออกจากระบบเดิมหมดอายุแล้ว กรุณากดยืนยันจากหน้านี้อีกครั้ง');
        exit;
    }

    private function handle_gate_logout_request() {
        $redirect = !empty($_REQUEST['redirect_to']) ? wp_unslash($_REQUEST['redirect_to']) : $this->gate_url('login');
        $redirect = wp_validate_redirect($redirect, $this->gate_url('login'));

        if (!is_user_logged_in()) {
            wp_safe_redirect($this->gate_url('login'), 302);
            exit;
        }

        if (!empty($_GET['tb4d_gate_confirm_logout'])) {
            $nonce = isset($_REQUEST['_wpnonce']) ? sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])) : '';
            if (wp_verify_nonce($nonce, 'log-out')) {
                wp_logout();
                wp_safe_redirect($redirect, 302);
                exit;
            }
            $this->render_branded_logout_page($redirect, 'คำขอยืนยันหมดอายุ กรุณากดปุ่มออกจากระบบอีกครั้ง');
            exit;
        }

        $this->render_branded_logout_page($redirect);
        exit;
    }

    private function render_branded_logout_page($redirect = '', $notice = '') {
        $redirect = wp_validate_redirect($redirect, $this->gate_url('login'));
        $confirm_url = wp_nonce_url($this->gate_url('login', array(
            'mode' => 'logout',
            'tb4d_gate_confirm_logout' => 1,
            'redirect_to' => $redirect,
        )), 'log-out');
        $continue_url = $this->gate_url('welcome');
        if (!$this->is_gate_admin_user(wp_get_current_user())) {
            $continue_url = home_url('/');
        }
        nocache_headers();
        status_header(200);
        ?><!doctype html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta name="robots" content="noindex,nofollow">
            <title><?php echo esc_html('ออกจากระบบ | Thinkb4do'); ?></title>
            <?php $this->print_inline_styles(); ?>
        </head>
        <body class="tb4d-gate-body">
            <main class="tb4d-logout-page" role="main">
                <header class="tb4d-logout-header">
                    <a class="tb4d-logout-brand tb4d-brand-link" href="<?php echo esc_url(home_url('/')); ?>" aria-label="Thinkb4do Home"><?php echo $this->brand_logo_mark('tb4d-header-logo-mark', 'T'); ?><span>Thinkb4do.com</span></a>
                    <a class="tb4d-logout-home" href="<?php echo esc_url(home_url('/')); ?>">กลับหน้าหลัก</a>
                </header>

                <section class="tb4d-logout-card" aria-labelledby="tb4d-logout-title">
                    <div class="tb4d-logout-icon" aria-hidden="true">✓</div>
                    <p class="tb4d-logout-kicker">Thinkb4do Community</p>
                    <h1 id="tb4d-logout-title">ต้องการออกจากระบบใช่ไหม?</h1>
                    <p class="tb4d-logout-lead">ขอบคุณที่เป็นส่วนหนึ่งของชุมชน Thinkb4do คุณสามารถกลับมาสร้างสรรค์และทำงานต่อได้ทุกเมื่อ</p>
                    <?php if ($notice): ?>
                        <div class="tb4d-logout-notice"><?php echo esc_html($notice); ?></div>
                    <?php endif; ?>
                    <div class="tb4d-logout-actions">
                        <a class="tb4d-logout-btn tb4d-logout-btn-primary" href="<?php echo esc_url($confirm_url); ?>">ออกจากระบบ</a>
                        <a class="tb4d-logout-btn tb4d-logout-btn-ghost" href="<?php echo esc_url($continue_url); ?>">ทำงานต่อ</a>
                    </div>
                    <div class="tb4d-logout-divider"><span>ขอให้เป็นวันที่ดีในการลงมือทำ</span></div>
                    <div class="tb4d-logout-recommend">
                        <a href="<?php echo esc_url(home_url('/')); ?>"><span>⌂</span><strong>หน้าหลัก</strong><small>เริ่มต้นใหม่</small></a>
                        <a href="<?php echo esc_url(home_url('/blog/')); ?>"><span>✦</span><strong>บทความ</strong><small>อ่านไอเดีย</small></a>
                        <a href="<?php echo esc_url(home_url('/contact/')); ?>"><span>✉</span><strong>ติดต่อเรา</strong><small>ขอความช่วยเหลือ</small></a>
                    </div>
                </section>

                <section class="tb4d-logout-quote" aria-label="ข้อความชุมชน">
                    <p>ทุกไอเดียเริ่มต้นจากก้าวเล็ก ๆ แล้วพัฒนาไปพร้อมกับชุมชนของเรา</p>
                </section>
            </main>
        </body>
        </html><?php
    }

    private function render_branded_404_page() {
        nocache_headers();
        status_header(404);
        ?><!doctype html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta name="robots" content="noindex,follow">
            <title><?php echo esc_html('ไม่พบหน้า | Thinkb4do'); ?></title>
            <?php $this->print_inline_styles(); ?>
        </head>
        <body class="tb4d-gate-body">
            <main class="tb4d-notfound-page" role="main">
                <header class="tb4d-notfound-header">
                    <a class="tb4d-notfound-brand tb4d-brand-link" href="<?php echo esc_url(home_url('/')); ?>" aria-label="Thinkb4do Home"><?php echo $this->brand_logo_mark('tb4d-header-logo-mark', 'T'); ?><span>Thinkb4do.com</span></a>
                    <a class="tb4d-notfound-home" href="<?php echo esc_url(home_url('/')); ?>">กลับหน้าหลัก</a>
                </header>

                <section class="tb4d-notfound-hero" aria-labelledby="tb4d-404-title">
                    <div class="tb4d-notfound-orb" aria-hidden="true">
                        <span>404</span>
                    </div>
                    <p class="tb4d-notfound-kicker">Thinkb4do Community</p>
                    <h1 id="tb4d-404-title">ไม่พบหน้าที่คุณกำลังมองหา</h1>
                    <p class="tb4d-notfound-lead">บางครั้งไอเดียก็พาเราออกนอกเส้นทาง ลองกลับสู่พื้นที่หลัก หรือค้นหาสิ่งที่คุณต้องการอีกครั้ง</p>

                    <form class="tb4d-notfound-search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                        <label class="screen-reader-text" for="tb4d-notfound-search-input">ค้นหาเนื้อหา</label>
                        <input id="tb4d-notfound-search-input" type="search" name="s" placeholder="ค้นหา เช่น บทความ ไอเดีย เครื่องมือ...">
                        <button type="submit">ค้นหา</button>
                    </form>
                </section>

                <section class="tb4d-notfound-actions" aria-label="เมนูแนะนำ">
                    <a href="<?php echo esc_url(home_url('/')); ?>"><span>⌂</span><strong>หน้าหลัก</strong><small>เริ่มต้นใหม่</small></a>
                    <a href="<?php echo esc_url(home_url('/blog/')); ?>"><span>✦</span><strong>บทความ</strong><small>อ่านไอเดีย</small></a>
                    <a href="<?php echo esc_url(home_url('/about/')); ?>"><span>◎</span><strong>เกี่ยวกับเรา</strong><small>รู้จักชุมชน</small></a>
                    <a href="<?php echo esc_url(home_url('/contact/')); ?>"><span>✉</span><strong>ติดต่อเรา</strong><small>ขอความช่วยเหลือ</small></a>
                </section>

                <section class="tb4d-notfound-community-card">
                    <div class="tb4d-notfound-compass" aria-hidden="true">⌖</div>
                    <div>
                        <h2>ยังหาไม่เจอใช่ไหม?</h2>
                        <p>กลับไปหน้าแรกเพื่อสำรวจพื้นที่สร้างสรรค์ หรือใช้ช่องค้นหาเพื่อค้นหาเนื้อหาที่เกี่ยวข้องกับคุณ</p>
                        <a class="tb4d-notfound-button" href="<?php echo esc_url(home_url('/')); ?>">กลับไปหน้าหลัก</a>
                    </div>
                </section>

                <footer class="tb4d-notfound-footer">
                    <strong>Thinkb4do.com</strong>
                    <span>พื้นที่ของคนคิดก่อนลงมือทำ</span>
                </footer>
            </main>
        </body>
        </html><?php
    }

    private function render_privileged_welcome_page() {
        if (!is_user_logged_in()) {
            wp_safe_redirect($this->gate_url('login', array('redirect_to' => $this->gate_url('welcome'))));
            exit;
        }
        $user = wp_get_current_user();
        if (!$this->is_gate_admin_user($user)) {
            wp_safe_redirect(home_url('/'), 302);
            exit;
        }

        // This page should show the native WordPress admin bar, not a custom imitation.
        if (function_exists('show_admin_bar')) {
            show_admin_bar(true);
        }
        if (function_exists('_wp_admin_bar_init')) {
            _wp_admin_bar_init();
        }

        nocache_headers();
        status_header(200);
        ?><!doctype html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta name="robots" content="noindex,nofollow">
            <title><?php echo esc_html($this->page_title('welcome')); ?></title>
            <?php $this->print_inline_styles(); ?>
            <?php wp_head(); ?>
        </head>
        <body class="tb4d-gate-body tb4d-welcome-has-native-adminbar admin-bar">
            <?php echo $this->render_privileged_welcome_shell($user); ?>
            <?php wp_footer(); ?>
        </body>
        </html><?php
    }

    private function render_privileged_welcome_shell($user) {
        $role_label = $this->gate_role_label($user);
        $is_super = $this->is_super_controller_user($user);
        $display_name = $this->user_display_name($user);
        $headline = $is_super ? 'ยินดีต้อนรับกลับเข้าสู่พื้นที่ควบคุมพิเศษ' : 'ยินดีต้อนรับกลับเข้าสู่พื้นที่ทำงาน';
        $subline = $is_super
            ? 'คุณมีสิทธิ์ควบคุมสูงสุด สามารถตรวจสถานะ จัดการสิทธิ์ และเลือกงานสำคัญต่อได้ทันที'
            : 'คุณมีสิทธิ์ทำงานในระบบ เลือกเมนูที่เกี่ยวข้องและทำงานต่อได้อย่างปลอดภัย';
        $rank_text = $is_super ? 'ผู้ดูแลควบคุมใหญ่สุด' : 'แอดมิน';
        $options = $this->options();
        $super_count = count($this->super_controller_ids_array());
        $admin_count = count($this->admin_staff_ids_array());
        $recovery_count = count($this->recovery_allowed_ids_array());
        $now_text = function_exists('wp_date') ? wp_date('j M Y H:i') : date_i18n('j M Y H:i');

        ob_start();
        ?>
        <main class="tb4d-control-shell tb4d-apple-welcome">
            <aside class="tb4d-control-sidebar" aria-label="เมนูพื้นที่ควบคุม">
                <a class="tb4d-control-brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="Thinkb4do Home">
                    <?php echo $this->brand_logo_mark('tb4d-control-brand-mark', 'T'); ?>
                    <span>
                        <strong>Think Control</strong>
                        <small>โดย Thinkb4do</small>
                    </span>
                </a>

                <nav class="tb4d-control-nav">
                    <a class="is-active" href="<?php echo esc_url($this->gate_url('welcome')); ?>"><span>⌂</span>หน้าต้อนรับ</a>
                    <a href="<?php echo esc_url(admin_url()); ?>"><span>▦</span>Dashboard หลัก</a>
                    <?php if ($is_super): ?>
                        <a href="<?php echo esc_url(admin_url('options-general.php?page=thinkb4do-gate#tb4d-role-picker')); ?>"><span>◎</span>ผู้ใช้งานและสิทธิ์</a>
                        <a href="<?php echo esc_url(admin_url('options-general.php?page=thinkb4do-gate')); ?>"><span>⚙</span>ตั้งค่า Think Control</a>
                    <?php endif; ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>"><span>◌</span>หน้าเว็บปกติ</a>
                    <a href="<?php echo esc_url(wp_logout_url($this->gate_url('login'))); ?>"><span>↩</span>ออกจากระบบ</a>
                </nav>

                <div class="tb4d-control-sidebar-card">
                    <span class="tb4d-status-dot"></span>
                    <strong>ระบบพร้อมใช้งาน</strong>
                    <small>เฉพาะผู้มีสิทธิ์เท่านั้นที่เห็นหน้านี้</small>
                </div>
            </aside>

            <section class="tb4d-control-main">
                <header class="tb4d-control-topbar">
                    <div>
                        <small>Authorized Workspace</small>
                        <h1>ยินดีต้อนรับกลับ</h1>
                    </div>
                    <div class="tb4d-control-user-pill">
                        <span class="tb4d-control-avatar"><?php echo esc_html(mb_substr($display_name, 0, 1)); ?></span>
                        <span>
                            <strong><?php echo esc_html($display_name); ?></strong>
                            <small><?php echo esc_html($rank_text); ?> · ออนไลน์</small>
                        </span>
                    </div>
                </header>

                <section class="tb4d-control-hero">
                    <div class="tb4d-control-emblem" aria-hidden="true">♕</div>
                    <div>
                        <p class="tb4d-control-kicker">Think Control</p>
                        <h2><?php echo esc_html($headline); ?></h2>
                        <p><?php echo esc_html($subline); ?></p>
                        <div class="tb4d-control-hero-actions">
                            <a class="tb4d-control-btn is-primary" href="<?php echo esc_url(admin_url()); ?>">กลับ Dashboard หลัก</a>
                            <a class="tb4d-control-btn" href="<?php echo esc_url(home_url('/')); ?>">ดูหน้าเว็บปกติ</a>
                        </div>
                    </div>
                </section>

                <section class="tb4d-control-status-grid" aria-label="สถานะผู้มีสิทธิ์">
                    <article class="tb4d-control-status-card">
                        <span class="tb4d-card-icon is-blue">✓</span>
                        <small>ระดับสิทธิ์ของคุณ</small>
                        <strong><?php echo esc_html($role_label); ?></strong>
                        <em>ตรวจสิทธิ์ผ่าน</em>
                    </article>
                    <article class="tb4d-control-status-card">
                        <span class="tb4d-card-icon is-green">●</span>
                        <small>สถานะระบบ</small>
                        <strong>ปลอดภัย</strong>
                        <em>ทำงานปกติ</em>
                    </article>
                    <article class="tb4d-control-status-card">
                        <span class="tb4d-card-icon is-orange">◎</span>
                        <small>ผู้มีสิทธิ์ทั้งหมด</small>
                        <strong><?php echo esc_html(number_format_i18n($super_count + $admin_count)); ?> บัญชี</strong>
                        <em>ควบคุม <?php echo esc_html(number_format_i18n($super_count)); ?> · แอดมิน <?php echo esc_html(number_format_i18n($admin_count)); ?></em>
                    </article>
                    <article class="tb4d-control-status-card">
                        <span class="tb4d-card-icon is-purple">↗</span>
                        <small>เข้าสู่ระบบล่าสุด</small>
                        <strong><?php echo esc_html($now_text); ?></strong>
                        <em>บัญชีกู้คืน <?php echo esc_html(number_format_i18n($recovery_count)); ?></em>
                    </article>
                </section>

                <section class="tb4d-control-content-grid">
                    <div class="tb4d-control-left-stack">
                        <section class="tb4d-control-panel">
                            <div class="tb4d-control-section-head">
                                <span>⌁</span>
                                <div>
                                    <h3>ทางลัดสำหรับคุณ</h3>
                                    <p>เลือกงานสำคัญที่ต้องทำต่อได้ทันที</p>
                                </div>
                            </div>
                            <?php echo $this->render_privileged_command_panel($is_super); ?>
                        </section>

                        <section class="tb4d-control-panel">
                            <div class="tb4d-control-section-head">
                                <span>☰</span>
                                <div>
                                    <h3>ลิงก์ทำงานต่อที่ตั้งไว้</h3>
                                    <p>ปุ่มลัดเพิ่มเติมจากหน้าตั้งค่า</p>
                                </div>
                            </div>
                            <?php echo $this->render_admin_work_links(true); ?>
                        </section>
                    </div>

                    <div class="tb4d-control-right-stack">
                        <?php echo $this->render_privileged_next_steps($is_super); ?>
                        <?php echo $this->render_privileged_alerts($is_super, !empty($options['trap_suspicious_paths'])); ?>
                    </div>
                </section>
            </section>
        </main>
        <?php
        return ob_get_clean();
    }

    private function render_privileged_signal_grid($user, $is_super) {
        return '';
    }

    private function render_privileged_command_panel($is_super) {
        $cards = $is_super ? array(
            array('title' => 'ตั้งค่า Think Control', 'desc' => 'จัดการทางเข้า สิทธิ์ การกู้คืน และการป้องกัน', 'url' => admin_url('options-general.php?page=thinkb4do-gate'), 'tag' => 'CONTROL', 'icon' => '⚙'),
            array('title' => 'เลือกสิทธิ์บัญชี', 'desc' => 'ค้นหาชื่อและกำหนดระดับผู้มีสิทธิ์', 'url' => admin_url('options-general.php?page=thinkb4do-gate#tb4d-role-picker'), 'tag' => 'ACCESS', 'icon' => '◎'),
            array('title' => 'สร้างหน้าใหม่', 'desc' => 'เริ่มทำหน้าสำคัญของเว็บต่อทันที', 'url' => admin_url('post-new.php?post_type=page'), 'tag' => 'CREATE', 'icon' => '+'),
            array('title' => 'ตรวจรายการหน้า', 'desc' => 'ดูหน้าเว็บทั้งหมดและแก้ไขงานต่อ', 'url' => admin_url('edit.php?post_type=page'), 'tag' => 'PAGES', 'icon' => '▦'),
        ) : array(
            array('title' => 'Dashboard หลัก', 'desc' => 'กลับไปทำงานที่ได้รับสิทธิ์ต่อ', 'url' => admin_url(), 'tag' => 'WORK', 'icon' => '⌂'),
            array('title' => 'สร้างหน้าใหม่', 'desc' => 'เพิ่มหน้าเว็บหรือหน้าเนื้อหาที่ต้องใช้', 'url' => admin_url('post-new.php?post_type=page'), 'tag' => 'CREATE', 'icon' => '+'),
            array('title' => 'ตรวจรายการหน้า', 'desc' => 'ดูและแก้ไขหน้าที่เกี่ยวข้อง', 'url' => admin_url('edit.php?post_type=page'), 'tag' => 'PAGES', 'icon' => '▦'),
            array('title' => 'หน้าเว็บปกติ', 'desc' => 'กลับไปตรวจผลลัพธ์ด้านหน้าเว็บ', 'url' => home_url('/'), 'tag' => 'SITE', 'icon' => '↗'),
        );
        ob_start();
        ?>
        <div class="tb4d-control-action-grid">
            <?php foreach ($cards as $card): ?>
                <a class="tb4d-control-action-card" href="<?php echo esc_url($card['url']); ?>">
                    <span class="tb4d-action-icon"><?php echo esc_html($card['icon']); ?></span>
                    <small><?php echo esc_html($card['tag']); ?></small>
                    <strong><?php echo esc_html($card['title']); ?></strong>
                    <em><?php echo esc_html($card['desc']); ?></em>
                </a>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    private function render_privileged_next_steps($is_super) {
        $steps = $is_super ? array(
            'ตรวจสถานะ Think Control และทางเข้าใหม่',
            'เลือกหรือถอดสิทธิ์จากชื่อบัญชี',
            'ตรวจลิงก์ทำงานต่อของทีม',
            'สำรองข้อมูลการตั้งค่าก่อนปรับระบบใหญ่',
        ) : array(
            'เลือกงานที่ได้รับสิทธิ์',
            'ตรวจหน้าเว็บที่เกี่ยวข้อง',
            'กลับ Dashboard หลักเพื่อทำงานต่อ',
            'กลับหน้าเว็บปกติเมื่อตรวจงานเสร็จ',
        );
        ob_start();
        ?>
        <section class="tb4d-control-panel tb4d-control-steps">
            <div class="tb4d-control-section-head">
                <span>✓</span>
                <div>
                    <h3>ลำดับงานแนะนำ</h3>
                    <p>ทำตามลำดับนี้เพื่อไม่หลุดขั้นตอน</p>
                </div>
            </div>
            <ol>
                <?php foreach ($steps as $index => $step): ?>
                    <li><span><?php echo esc_html($index + 1); ?></span><?php echo esc_html($step); ?></li>
                <?php endforeach; ?>
            </ol>
        </section>
        <?php
        return ob_get_clean();
    }

    private function render_privileged_alerts($is_super, $trap_enabled) {
        $alerts = array(
            array('type' => 'success', 'title' => 'เข้าสู่ระบบสำเร็จ', 'desc' => $is_super ? 'ตรวจพบสิทธิ์ควบคุมสูงสุด' : 'ตรวจพบสิทธิ์แอดมิน'),
            array('type' => $trap_enabled ? 'success' : 'warning', 'title' => 'ระบบดัก URL ก่อกวน', 'desc' => $trap_enabled ? 'เปิดใช้งานอยู่' : 'ยังไม่ได้เปิดใช้งาน'),
            array('type' => 'neutral', 'title' => 'สมาชิกทั่วไป', 'desc' => 'ส่งกลับหน้าเว็บปกติ ไม่แสดงหน้านี้'),
        );
        ob_start();
        ?>
        <section class="tb4d-control-panel tb4d-control-alerts">
            <div class="tb4d-control-section-head">
                <span>●</span>
                <div>
                    <h3>การแจ้งเตือนล่าสุด</h3>
                    <p>สรุปสถานะสำคัญหลังเข้าระบบ</p>
                </div>
            </div>
            <div class="tb4d-alert-list">
                <?php foreach ($alerts as $alert): ?>
                    <div class="tb4d-alert-item is-<?php echo esc_attr($alert['type']); ?>">
                        <span></span>
                        <div>
                            <strong><?php echo esc_html($alert['title']); ?></strong>
                            <small><?php echo esc_html($alert['desc']); ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }

    private function render_full_page($mode = 'login') {
        $message = '';
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($mode === 'register') {
                list($message, $error) = $this->handle_register_post();
            } elseif ($mode === 'lost') {
                list($message, $error) = $this->handle_lost_password_post();
            } elseif ($mode === 'recovery') {
                list($message, $error) = $this->handle_recovery_post();
            } else {
                list($message, $error) = $this->handle_login_post();
            }
        }

        nocache_headers();
        status_header(200);
        ?><!doctype html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta name="robots" content="noindex,nofollow">
            <title><?php echo esc_html($this->page_title($mode)); ?></title>
            <?php $this->print_inline_styles(); ?>
        </head>
        <body class="tb4d-gate-body">
            <?php echo $this->render_auth_shell($mode, $message, $error);  ?>
        </body>
        </html><?php
    }

    private function page_title($mode) {
        if ($mode === 'register') {
            return 'สมัครสมาชิก | Thinkb4do';
        }
        if ($mode === 'lost') {
            return 'ลืมรหัสผ่าน | Thinkb4do';
        }
        if ($mode === 'recovery') {
            return 'กู้คืนทางเข้า | Thinkb4do';
        }
        if ($mode === 'logout') {
            return 'ออกจากระบบ | Thinkb4do';
        }
        if ($mode === 'welcome') {
            return 'ต้อนรับผู้มีสิทธิ์ | Thinkb4do';
        }
        return 'เข้าสู่ระบบ | Thinkb4do';
    }

    private function render_auth_shell($mode = 'login', $message = '', $error = '') {
        $options = $this->options();
        $badges = array_filter(array_map('trim', explode('|', (string) $options['brand_badges'])));
        ob_start();
        ?>
        <main class="tb4d-auth-page">
            <section class="tb4d-auth-brand" aria-label="Thinkb4do">
                <a class="tb4d-auth-logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="Thinkb4do Home">
                    <?php echo $this->brand_logo_mark('tb4d-auth-logo-mark', 'T'); ?>
                    <span>Thinkb4do</span>
                </a>
                <p class="tb4d-auth-kicker">AI System • Member Portal • Creative Tools</p>
                <h1><?php echo esc_html($this->public_brand_title()); ?></h1>
                <p class="tb4d-auth-lead"><?php echo esc_html($options['brand_subtitle']); ?></p>
                <div class="tb4d-auth-badges">
                    <?php foreach ($badges as $badge): ?>
                        <span><?php echo esc_html($badge); ?></span>
                    <?php endforeach; ?>
                </div>
                <div class="tb4d-auth-trust">
                    <strong>ชุมชนคนลงมือสร้าง</strong>
                    <span>พื้นที่ของสมาชิก Thinkb4do สำหรับเริ่มต้นไอเดีย พัฒนางาน และต่อยอดผลงานสร้างสรรค์ไปด้วยกัน</span>
                    <?php echo $this->render_brand_status_badge('tb4d-auth-status'); ?>
                </div>
                <?php /* ซ่อนโลโก้/ภาพอัตลักษณ์ขนาดใหญ่ในหน้าเข้าสู่ระบบ เพื่อให้หน้าสะอาดและไม่ซ้ำกับโลโก้หัวเว็บ */ ?>
            </section>

            <section class="tb4d-auth-card" aria-label="Authentication Form">
                <?php if (is_user_logged_in()): ?>
                    <?php echo $this->render_logged_in_card();  ?>
                <?php elseif ($mode === 'register'): ?>
                    <?php echo $this->render_register_form($message, $error);  ?>
                <?php elseif ($mode === 'lost'): ?>
                    <?php echo $this->render_lost_form($message, $error);  ?>
                <?php elseif ($mode === 'recovery'): ?>
                    <?php echo $this->render_recovery_form($message, $error);  ?>
                <?php else: ?>
                    <?php echo $this->render_login_form($message, $error);  ?>
                <?php endif; ?>
            </section>
        </main>
        <?php
        return ob_get_clean();
    }

    private function render_notice($message, $error) {
        $html = '';
        if (!empty($message)) {
            $html .= '<div class="tb4d-form-notice is-success">' . esc_html($message) . '</div>';
        }
        if (!empty($error)) {
            $html .= '<div class="tb4d-form-notice is-error">' . esc_html($error) . '</div>';
        }
        return $html;
    }

    private function render_login_form($message = '', $error = '') {
        $redirect_to = esc_url_raw($_GET['redirect_to'] ?? '');
        if (empty($redirect_to)) {
            $redirect_to = $this->default_login_redirect_url();
        }
        ob_start();
        ?>
        <div class="tb4d-auth-card-head">
            <p class="tb4d-auth-card-kicker">เข้าสู่ระบบ</p>
            <h2>ยินดีต้อนรับกลับ</h2>
            <p>กรอกข้อมูลเพื่อเข้าสู่พื้นที่จัดการของคุณ</p>
        </div>
        <?php echo $this->render_notice($message, $error);  ?>
        <form class="tb4d-auth-form" method="post" action="<?php echo esc_url($this->gate_url('login')); ?>">
            <?php wp_nonce_field(self::NONCE_ACTION_LOGIN, 'tb4d_gate_nonce'); ?>
            <input type="hidden" name="tb4d_gate_action" value="login">
            <input type="hidden" name="redirect_to" value="<?php echo esc_attr($redirect_to); ?>">
            <label>
                <span>อีเมลหรือชื่อผู้ใช้</span>
                <input type="text" name="log" autocomplete="username" required placeholder="your@email.com">
            </label>
            <label>
                <span>รหัสผ่าน</span>
                <input type="password" name="pwd" autocomplete="current-password" required placeholder="••••••••">
            </label>
            <div class="tb4d-auth-row">
                <label class="tb4d-checkbox"><input type="checkbox" name="rememberme" value="forever"> จดจำฉันไว้</label>
                <a href="<?php echo esc_url($this->gate_url('login', array('mode' => 'lost'))); ?>">ลืมรหัสผ่าน?</a>
            </div>
            <p class="tb4d-auth-mini-link"><a href="<?php echo esc_url($this->gate_url('login', array('mode' => 'recovery'))); ?>">ลืมทางเข้า?</a></p>
            <button type="submit" class="tb4d-btn tb4d-btn-primary">เข้าสู่ระบบ</button>
        </form>
        <p class="tb4d-auth-switch">ยังไม่มีบัญชี? <a href="<?php echo esc_url($this->gate_url('register')); ?>">สมัครสมาชิก</a></p>
        <?php
        return ob_get_clean();
    }

    private function render_register_form($message = '', $error = '') {
        $options = $this->options();
        if (empty($options['enable_registration'])) {
            return '<div class="tb4d-auth-card-head"><h2>ปิดรับสมัครสมาชิกชั่วคราว</h2><p>กรุณาติดต่อผู้ดูแลเว็บไซต์</p></div><a class="tb4d-btn tb4d-btn-primary" href="' . esc_url($this->gate_url('login')) . '">กลับไปเข้าสู่ระบบ</a>';
        }
        ob_start();
        ?>
        <div class="tb4d-auth-card-head">
            <p class="tb4d-auth-card-kicker">สมัครสมาชิก</p>
            <h2>เริ่มต้นกับ Thinkb4do</h2>
            <p>สร้างบัญชีเพื่อใช้งานระบบ เครื่องมือ และบริการสำหรับสมาชิก</p>
        </div>
        <?php echo $this->render_notice($message, $error);  ?>
        <form class="tb4d-auth-form" method="post" action="<?php echo esc_url($this->gate_url('register')); ?>">
            <?php wp_nonce_field(self::NONCE_ACTION_REGISTER, 'tb4d_gate_nonce'); ?>
            <input type="hidden" name="tb4d_gate_action" value="register">
            <label>
                <span>ชื่อผู้ใช้</span>
                <input type="text" name="user_login" autocomplete="username" required placeholder="thinkb4do_user">
            </label>
            <label>
                <span>อีเมล</span>
                <input type="email" name="user_email" autocomplete="email" required placeholder="your@email.com">
            </label>
            <label>
                <span>รหัสผ่าน</span>
                <input type="password" name="user_pass" autocomplete="new-password" required minlength="8" placeholder="อย่างน้อย 8 ตัวอักษร">
            </label>
            <button type="submit" class="tb4d-btn tb4d-btn-primary">สร้างบัญชี</button>
        </form>
        <p class="tb4d-auth-switch">มีบัญชีแล้ว? <a href="<?php echo esc_url($this->gate_url('login')); ?>">เข้าสู่ระบบ</a></p>
        <?php
        return ob_get_clean();
    }

    private function render_lost_form($message = '', $error = '') {
        ob_start();
        ?>
        <div class="tb4d-auth-card-head">
            <p class="tb4d-auth-card-kicker">กู้คืนบัญชี</p>
            <h2>ลืมรหัสผ่าน?</h2>
            <p>กรอกอีเมลหรือชื่อผู้ใช้ ระบบจะส่งลิงก์ตั้งรหัสผ่านใหม่ให้</p>
        </div>
        <?php echo $this->render_notice($message, $error);  ?>
        <form class="tb4d-auth-form" method="post" action="<?php echo esc_url($this->gate_url('login', array('mode' => 'lost'))); ?>">
            <?php wp_nonce_field(self::NONCE_ACTION_LOST, 'tb4d_gate_nonce'); ?>
            <input type="hidden" name="tb4d_gate_action" value="lost">
            <label>
                <span>อีเมลหรือชื่อผู้ใช้</span>
                <input type="text" name="user_login" required placeholder="your@email.com">
            </label>
            <button type="submit" class="tb4d-btn tb4d-btn-primary">ส่งลิงก์ตั้งรหัสผ่านใหม่</button>
        </form>
        <p class="tb4d-auth-switch"><a href="<?php echo esc_url($this->gate_url('login')); ?>">กลับไปเข้าสู่ระบบ</a></p>
        <?php
        return ob_get_clean();
    }

    private function render_recovery_form($message = '', $error = '') {
        $options = $this->options();
        if (empty($options['recovery_enabled'])) {
            return '<div class="tb4d-auth-card-head"><h2>ปิดระบบกู้คืนชั่วคราว</h2><p>กรุณาติดต่อเจ้าของระบบโดยตรง</p></div><a class="tb4d-btn tb4d-btn-primary" href="' . esc_url($this->gate_url('login')) . '">กลับไปเข้าสู่ระบบ</a>';
        }
        ob_start();
        ?>
        <div class="tb4d-auth-card-head">
            <p class="tb4d-auth-card-kicker">กู้คืนทางเข้า</p>
            <h2>ขอข้อมูลทางเข้า</h2>
            <p>กดส่งคำขอ หากระบบตรวจพบเงื่อนไขที่ตรงกัน จะส่งรายละเอียดไปยังช่องทางกู้คืนที่บันทึกไว้</p>
        </div>
        <?php echo $this->render_notice($message, $error);  ?>
        <form class="tb4d-auth-form" method="post" action="<?php echo esc_url($this->gate_url('login', array('mode' => 'recovery'))); ?>">
            <?php wp_nonce_field(self::NONCE_ACTION_RECOVERY, 'tb4d_gate_nonce'); ?>
            <input type="hidden" name="tb4d_gate_action" value="recovery">
            <button type="submit" class="tb4d-btn tb4d-btn-primary">ส่งคำขอกู้คืน</button>
        </form>
        <p class="tb4d-auth-switch"><a href="<?php echo esc_url($this->gate_url('login')); ?>">กลับไปเข้าสู่ระบบ</a></p>
        <?php
        return ob_get_clean();
    }

    private function render_logged_in_card() {
        $user = wp_get_current_user();
        $dashboard = $this->default_login_redirect_url();
        $is_admin_user = $this->is_gate_admin_user($user);
        $role_label = $this->gate_role_label($user);
        ob_start();
        ?>
        <div class="tb4d-auth-card-head">
            <p class="tb4d-auth-card-kicker">เชื่อมต่อสำเร็จ</p>
            <h2>คุณเข้าสู่ระบบแล้ว</h2>
            <p>บัญชี: <?php echo esc_html($user->display_name ?: $user->user_login); ?></p>
            <p>สิทธิ์ปัจจุบัน: <?php echo esc_html($role_label); ?></p>
        </div>
        <div class="tb4d-form-notice is-success">สถานะ: Login สำเร็จ · ตรวจสิทธิ์ผ่าน · ความเสถียร 97%</div>
        <?php if ($is_admin_user): ?>
            <a class="tb4d-btn tb4d-btn-primary" href="<?php echo esc_url($this->gate_url('welcome')); ?>">ไปหน้าต้อนรับผู้มีสิทธิ์</a>
            <a class="tb4d-btn tb4d-btn-ghost" href="<?php echo esc_url($this->main_dashboard_url()); ?>">กลับ Dashboard หลัก</a>
        <?php else: ?>
            <a class="tb4d-btn tb4d-btn-primary" href="<?php echo esc_url(home_url('/')); ?>">ไปหน้าเว็บปกติ</a>
        <?php endif; ?>
        <a class="tb4d-btn tb4d-btn-ghost" href="<?php echo esc_url(wp_logout_url($this->gate_url('login'))); ?>">ออกจากระบบ</a>
        <?php
        return ob_get_clean();
    }

    private function handle_login_post() {
        if (empty($_POST['tb4d_gate_action']) || $_POST['tb4d_gate_action'] !== 'login') {
            return array('', '');
        }
        if (!isset($_POST['tb4d_gate_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['tb4d_gate_nonce'])), self::NONCE_ACTION_LOGIN)) {
            return array('', 'คำขอไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง');
        }
        $lock_error = $this->check_rate_limit();
        if ($lock_error) {
            return array('', $lock_error);
        }

        $creds = array(
            'user_login' => sanitize_text_field(wp_unslash($_POST['log'] ?? '')),
            'user_password' => (string) wp_unslash($_POST['pwd'] ?? ''),
            'remember' => !empty($_POST['rememberme']),
        );

        $user = wp_signon($creds, is_ssl());
        if (is_wp_error($user)) {
            $this->record_failed_attempt();
            return array('', 'อีเมล/ชื่อผู้ใช้ หรือรหัสผ่านไม่ถูกต้อง');
        }

        $this->clear_failed_attempts();
        $this->remember_privileged_user($user);
        if ($this->is_gate_admin_user($user)) {
            $redirect_to = $this->gate_url('welcome');
        } else {
            $redirect_to = $this->non_admin_landing_url();
        }
        wp_safe_redirect(wp_validate_redirect($redirect_to, $this->default_login_redirect_url($user)));
        exit;
    }

    private function handle_register_post() {
        if (empty($_POST['tb4d_gate_action']) || $_POST['tb4d_gate_action'] !== 'register') {
            return array('', '');
        }
        if (!isset($_POST['tb4d_gate_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['tb4d_gate_nonce'])), self::NONCE_ACTION_REGISTER)) {
            return array('', 'คำขอไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง');
        }
        $options = $this->options();
        if (empty($options['enable_registration'])) {
            return array('', 'ระบบสมัครสมาชิกถูกปิดอยู่');
        }

        $username = sanitize_user(wp_unslash($_POST['user_login'] ?? ''), true);
        $email = sanitize_email(wp_unslash($_POST['user_email'] ?? ''));
        $password = (string) wp_unslash($_POST['user_pass'] ?? '');

        if (strlen($username) < 3) {
            return array('', 'ชื่อผู้ใช้ต้องมีอย่างน้อย 3 ตัวอักษร');
        }
        if (!is_email($email)) {
            return array('', 'รูปแบบอีเมลไม่ถูกต้อง');
        }
        if (username_exists($username)) {
            return array('', 'ชื่อผู้ใช้นี้ถูกใช้แล้ว');
        }
        if (email_exists($email)) {
            return array('', 'อีเมลนี้ถูกใช้แล้ว');
        }
        if (strlen($password) < 8) {
            return array('', 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร');
        }

        $user_id = wp_create_user($username, $password, $email);
        if (is_wp_error($user_id)) {
            return array('', $user_id->get_error_message());
        }

        wp_new_user_notification($user_id, null, 'admin');
        return array('สมัครสมาชิกสำเร็จ กรุณาเข้าสู่ระบบเพื่อใช้งานต่อ', '');
    }

    private function handle_lost_password_post() {
        if (empty($_POST['tb4d_gate_action']) || $_POST['tb4d_gate_action'] !== 'lost') {
            return array('', '');
        }
        if (!isset($_POST['tb4d_gate_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['tb4d_gate_nonce'])), self::NONCE_ACTION_LOST)) {
            return array('', 'คำขอไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง');
        }
        $login = sanitize_text_field(wp_unslash($_POST['user_login'] ?? ''));
        if ($login === '') {
            return array('', 'กรุณากรอกอีเมลหรือชื่อผู้ใช้');
        }
        $_POST['user_login'] = $login;
        $result = retrieve_password();
        if (is_wp_error($result)) {
            return array('', 'ไม่พบบัญชีนี้ หรือระบบส่งอีเมลยังไม่พร้อม');
        }
        return array('ส่งลิงก์ตั้งรหัสผ่านใหม่แล้ว กรุณาตรวจสอบอีเมล', '');
    }

    private function handle_recovery_post() {
        if (empty($_POST['tb4d_gate_action']) || $_POST['tb4d_gate_action'] !== 'recovery') {
            return array('', '');
        }
        if (!isset($_POST['tb4d_gate_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['tb4d_gate_nonce'])), self::NONCE_ACTION_RECOVERY)) {
            return array('', 'คำขอไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง');
        }

        $options = $this->options();
        $generic = 'รับคำขอแล้ว หากข้อมูลตรง ระบบจะส่งรายละเอียดไปยังช่องทางกู้คืนที่ตั้งไว้';
        if (empty($options['recovery_enabled'])) {
            return array($generic, '');
        }
        $cooldown = max(5, min(1440, absint($options['recovery_cooldown_minutes'] ?? 30)));
        $key = $this->recovery_throttle_key('request');
        if (get_transient($key)) {
            return array($generic, '');
        }
        set_transient($key, 1, $cooldown * MINUTE_IN_SECONDS);

        $mail = $this->recovery_mail();
        if ($mail !== '') {
            $super_names = array_map(array($this, 'user_display_name'), $this->users_from_csv($options['super_controller_ids'] ?? ''));
            $admin_names = array_map(array($this, 'user_display_name'), $this->users_from_csv($options['admin_staff_ids'] ?? ($options['admin_allowed_ids'] ?? '')));
            $recovery_names = array_map(array($this, 'user_display_name'), $this->users_from_csv($options['recovery_allowed_ids'] ?? ''));
            $subject = 'Think Control: ข้อมูลกู้คืนทางเข้า';
            $body = implode("
", array(
                'Think Control - ข้อมูลกู้คืนทางเข้า',
                '',
                'URL ทางเข้าหลัก: ' . $this->gate_url('login'),
                'URL สมัครสมาชิก: ' . $this->gate_url('register'),
                'ผู้ดูแลควบคุมใหญ่สุดที่จำไว้: ' . (!empty($super_names) ? implode(', ', $super_names) : 'ยังไม่ระบุ'),
                'แอดมินที่จำไว้: ' . (!empty($admin_names) ? implode(', ', $admin_names) : 'ยังไม่ระบุ'),
                'บัญชีกู้คืนที่จำไว้: ' . (!empty($recovery_names) ? implode(', ', $recovery_names) : 'ยังไม่ระบุ'),
                'เวลาแจ้งเตือน: ' . current_time('mysql'),
                'IP ผู้ขอ: ' . sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? ''),
                '',
                'หมายเหตุ: ข้อความนี้ไม่มีรหัสผ่าน และไม่ควรส่งต่อให้ผู้อื่น',
            ));
            wp_mail($mail, $subject, $body);
        }

        return array($generic, '');
    }

    private function default_login_redirect_url($user = null) {
        $options = $this->options();
        if (is_a($user, 'W' . 'P_User') && $this->is_gate_admin_user($user)) {
            return $this->admin_landing_url($user);
        }
        if (is_user_logged_in()) {
            $current = wp_get_current_user();
            if ($this->is_gate_admin_user($current)) {
                return $this->admin_landing_url($user);
            }
        }
        return $this->non_admin_landing_url();
    }

    private function attempt_key() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        return 'tb4d_gate_attempts_' . md5($ip . '|' . $ua);
    }

    private function check_rate_limit() {
        $options = $this->options();
        $data = get_transient($this->attempt_key());
        $attempts = is_array($data) ? absint($data['attempts'] ?? 0) : 0;
        if ($attempts >= absint($options['max_attempts'])) {
            return sprintf('ล็อกอินผิดหลายครั้ง ระบบล็อกชั่วคราว %d นาที', absint($options['lock_minutes']));
        }
        return '';
    }

    private function record_failed_attempt() {
        $options = $this->options();
        $key = $this->attempt_key();
        $data = get_transient($key);
        $attempts = is_array($data) ? absint($data['attempts'] ?? 0) : 0;
        $attempts++;
        set_transient($key, array('attempts' => $attempts, 'time' => time()), absint($options['lock_minutes']) * MINUTE_IN_SECONDS);
    }

    private function clear_failed_attempts() {
        delete_transient($this->attempt_key());
    }

    public function protect_default_login() {
        if (!$this->enabled()) {
            return;
        }
        $options = $this->options();
        if (empty($options['hide_legacy_login'])) {
            return;
        }
        $action = sanitize_key($_REQUEST['action'] ?? 'login');
        if (in_array($action, array('logout', 'rp', 'resetpass', 'postpass'), true)) {
            return;
        }
        if (!empty($_GET['tb4d_gate_allow'])) {
            return;
        }
        if ($options['redirect_unknown_login'] === 'login') {
            wp_safe_redirect($this->gate_url('login', array('from' => 'legacy')));
            exit;
        }
        if ($options['redirect_unknown_login'] === 'register') {
            wp_safe_redirect($this->gate_url('register', array('from' => 'legacy')));
            exit;
        }
        if ($options['redirect_unknown_login'] === 'home') {
            wp_safe_redirect(home_url('/'));
            exit;
        }
        $this->render_branded_404_page();
        exit;
    }

    public function protect_admin_gate() {
        if (!$this->enabled()) {
            return;
        }
        $options = $this->options();
        if (empty($options['protect_admin_area'])) {
            return;
        }
        if (!is_admin() || wp_doing_ajax() || wp_doing_cron()) {
            return;
        }
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($uri, 'admin-ajax.php') !== false || strpos($uri, 'admin-post.php') !== false) {
            return;
        }
        if (is_user_logged_in()) {
            if (empty($options['admin_only_access']) || $this->is_gate_admin_user()) {
                return;
            }
            wp_safe_redirect($this->non_admin_landing_url(), 302);
            exit;
        }
        wp_safe_redirect($this->gate_url('login', array('redirect_to' => admin_url())));
        exit;
    }

    public function maybe_disable_xmlrpc() {
        $options = $this->options();
        if (!empty($options['disable_xmlrpc'])) {
            add_filter('xmlrpc_enabled', '__return_false');
        }
    }

    public function block_author_scan() {
        $options = $this->options();
        if (!$this->enabled() || empty($options['block_author_scan'])) {
            return;
        }
        if (isset($_GET['author']) && !is_admin()) {
            status_header(404);
            nocache_headers();
            exit;
        }
    }

    public function filter_login_url($login_url, $redirect = '', $force_reauth = false) {
        if (!$this->enabled()) {
            return $login_url;
        }
        $args = array();
        if (!empty($redirect)) {
            $args['redirect_to'] = $redirect;
        }
        if ($force_reauth) {
            $args['reauth'] = 1;
        }
        return $this->gate_url('login', $args);
    }

    public function filter_lostpassword_url($lostpassword_url, $redirect = '') {
        if (!$this->enabled()) {
            return $lostpassword_url;
        }
        $args = array('mode' => 'lost');
        if (!empty($redirect)) {
            $args['redirect_to'] = $redirect;
        }
        return $this->gate_url('login', $args);
    }

    public function filter_registration_url($url) {
        if (!$this->enabled()) {
            return $url;
        }
        return $this->gate_url('register');
    }

    public function filter_logout_url($logout_url, $redirect) {
        if (!$this->enabled()) {
            return $logout_url;
        }
        $redirect = $redirect ? $redirect : $this->gate_url('login');
        $redirect = wp_validate_redirect($redirect, $this->gate_url('login'));
        return wp_nonce_url($this->gate_url('login', array(
            'mode' => 'logout',
            'redirect_to' => $redirect,
        )), 'log-out');
    }

    public function add_security_headers($headers) {
        if (!$this->enabled()) {
            return $headers;
        }
        $headers['X-Frame-Options'] = 'SAMEORIGIN';
        $headers['X-Content-Type-Options'] = 'nosniff';
        $headers['Referrer-Policy'] = 'strict-origin-when-cross-origin';
        return $headers;
    }

    public function shortcode_login($atts = array()) {
        if (is_user_logged_in()) {
            return '<div class="tb4d-auth-shortcode">' . $this->render_logged_in_card() . '</div>';
        }
        $message = '';
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['tb4d_gate_action']) && $_POST['tb4d_gate_action'] === 'login') {
            list($message, $error) = $this->handle_login_post();
        }
        return '<div class="tb4d-auth-shortcode">' . $this->render_login_form($message, $error) . '</div>';
    }

    public function shortcode_register($atts = array()) {
        if (is_user_logged_in()) {
            return '<div class="tb4d-auth-shortcode">' . $this->render_logged_in_card() . '</div>';
        }
        $message = '';
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['tb4d_gate_action']) && $_POST['tb4d_gate_action'] === 'register') {
            list($message, $error) = $this->handle_register_post();
        }
        return '<div class="tb4d-auth-shortcode">' . $this->render_register_form($message, $error) . '</div>';
    }

    public function shortcode_locked_content($atts = array(), $content = null) {
        $atts = shortcode_atts(array(
            'message' => 'พื้นที่นี้เปิดให้เฉพาะสมาชิก Thinkb4do',
            'button' => 'เข้าสู่ระบบ',
        ), $atts, 'thinkb4do_locked');

        if (is_user_logged_in()) {
            return do_shortcode($content ?? '');
        }
        ob_start();
        ?>
        <div class="tb4d-locked-box">
            <div class="tb4d-locked-icon">🔒</div>
            <h3><?php echo esc_html($atts['message']); ?></h3>
            <p>กรุณาเข้าสู่ระบบหรือสมัครสมาชิกเพื่อดูเนื้อหา ระบบ เครื่องมือ และไฟล์ที่เกี่ยวข้องกับบัญชีของคุณ</p>
            <div class="tb4d-locked-actions">
                <a class="tb4d-btn tb4d-btn-primary" href="<?php echo esc_url($this->gate_url('login')); ?>"><?php echo esc_html($atts['button']); ?></a>
                <a class="tb4d-btn tb4d-btn-ghost" href="<?php echo esc_url($this->gate_url('register')); ?>">สมัครสมาชิก</a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function shortcode_logo($atts = array()) {
        $atts = shortcode_atts(array(
            'size' => 'medium',
            'class' => 'tb4d-shortcode-logo',
            'show_text' => 'yes',
        ), $atts, 'thinkb4do_logo');
        $url = $this->brand_logo_url($atts['size']);
        $class = sanitize_html_class($atts['class']);
        if (!$url) {
            return '<span class="' . esc_attr($class . ' tb4d-shortcode-logo is-fallback') . '">T<span>Thinkb4do</span></span>';
        }
        $text = strtolower((string) $atts['show_text']) === 'no' ? '' : '<span>Thinkb4do</span>';
        return '<span class="' . esc_attr($class . ' tb4d-shortcode-logo') . '"><img src="' . esc_url($url) . '" alt="Thinkb4do">' . $text . '</span>';
    }

    public function shortcode_identity($atts = array()) {
        $atts = shortcode_atts(array(
            'size' => 'large',
            'class' => 'tb4d-shortcode-identity',
        ), $atts, 'thinkb4do_identity');
        $url = $this->brand_identity_url($atts['size']);
        $class = sanitize_html_class($atts['class']);
        if (!$url) {
            return '';
        }
        return '<img class="' . esc_attr($class . ' tb4d-shortcode-identity') . '" src="' . esc_url($url) . '" alt="Thinkb4do Identity">';
    }

    public function shortcode_brand_status($atts = array()) {
        $atts = shortcode_atts(array(
            'class' => 'tb4d-shortcode-brand-status',
        ), $atts, 'thinkb4do_brand_status');
        return $this->render_brand_status_badge($atts['class']);
    }

    public function register_dashboard_link_widget() {
        if (!$this->enabled() || !$this->is_gate_admin_user()) {
            return;
        }
        wp_add_dashboard_widget(
            'tb4d_gate_dashboard_links',
            'Think Control',
            array($this, 'render_dashboard_link_widget')
        );
    }

    public function render_dashboard_link_widget() {
        ?>
        <div class="tb4d-dashboard-widget">
            <p><strong>ทางลัดสำหรับผู้มีสิทธิ์</strong></p>
            <p>ล็อกอินสำเร็จแล้วจะไปหน้าต้อนรับผู้มีสิทธิ์อัตโนมัติ และกลับ Dashboard หลักได้จากหน้านั้น</p>
            <p>
                <a class="button button-primary" href="<?php echo esc_url($this->gate_url('welcome')); ?>">ไปหน้าต้อนรับผู้มีสิทธิ์</a>
                <a class="button" href="<?php echo esc_url(admin_url()); ?>">Dashboard หลัก</a>
            </p>
        </div>
        <?php
    }

    public function admin_gate_notice() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $screen = get_current_screen();
        if (!$screen || $screen->id === 'settings_page_thinkb4do-gate') {
            return;
        }
        $options = $this->options();
        if (!$this->enabled()) {
            return;
        }
        echo '<div class="notice notice-info is-dismissible"><p><strong>Think Control:</strong> <a href="' . esc_url($this->gate_url('welcome')) . '">หน้าต้อนรับผู้มีสิทธิ์</a> · <a href="' . esc_url($this->gate_url('login')) . '" target="_blank" rel="noopener">ทางเข้าหลัก</a> · <a href="' . esc_url(admin_url()) . '">Dashboard หลัก</a></p></div>';
    }

    private function print_inline_styles() {
        $css_path = plugin_dir_path(__FILE__) . 'assets/css/thinkb4do-gate.css';
        echo '<style id="tb4d-gate-inline-css">';
        echo ':root{--tb4d-font-main:' . $this->brand_font_stack() . ';}';
        if (file_exists($css_path)) {
            echo file_get_contents($css_path); 
        }
        echo '</style>';
    }
}

register_activation_hook(__FILE__, array('TB4D_Gate_Plugin', 'activate'));
register_deactivation_hook(__FILE__, array('TB4D_Gate_Plugin', 'deactivate'));
register_uninstall_hook(__FILE__, array('TB4D_Gate_Plugin', 'uninstall'));

TB4D_Gate_Plugin::instance();
