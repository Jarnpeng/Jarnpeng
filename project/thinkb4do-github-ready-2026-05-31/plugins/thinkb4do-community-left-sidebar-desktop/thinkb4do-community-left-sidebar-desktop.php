<?php
/**
 * Plugin Name: Thinkb4do Community Desktop Left Sidebar
 * Description: แยก Sidebar ซ้าย สำหรับ Community เป็นปลั๊กอินเฉพาะส่วน แสดงเฉพาะ Desktop พร้อมเมนูหลัก 2 รายการ Recents ดึงตาม user ID สกรอลล์หลังหัวข้อที่ 10 และมีนโยบายพร้อมลิงก์สำคัญของเว็บไว้ส่วนล่าง
 * Version: 1.13.0
 * Author: Thinkb4do
 * Text Domain: thinkb4do-community-left-sidebar-desktop
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

final class ThinkB4Do_Community_Left_Sidebar_Desktop {
    private const OPTION_KEY = 'tcb4d_community_left_sidebar_settings';
    private const VERSION = '1.13.0';
    private const SIDE = 'left';
    private const SHORTCODE = 'thinkb4do_community_left_sidebar';
    private const ADMIN_SLUG = 'thinkb4do-community-left-sidebar';
    private const FRONT_ID = 'tcb4d-community-left-sidebar';
    private const ACTIVE_CLASS = 'tcb4d-community-left-sidebar-active';
    private const RESERVE_CLASS = 'tcb4d-community-left-sidebar-reserve';
    private const RUNTIME_CLASS = 'tcb4d-community-left-sidebar-runtime-visible';

    private static $instance = null;

    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', [$this, 'add_admin_page']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('init', [$this, 'maybe_upgrade_settings'], 5);
        add_action('add_meta_boxes', [$this, 'add_recents_meta_box']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('wp_footer', [$this, 'render_sidebar_from_footer'], 18);
        add_filter('body_class', [$this, 'add_body_classes']);
        add_shortcode(self::SHORTCODE, [$this, 'shortcode_sidebar']);
        add_action('save_post', [$this, 'save_recents_meta_box'], 10, 3);
        add_action('save_post', [$this, 'refresh_aira_topic_summary_on_save'], 20, 3);
        add_action('before_delete_post', [$this, 'delete_aira_topic_summary_meta']);
        add_action('trashed_post', [$this, 'delete_aira_topic_summary_meta']);
    }

    public static function activate() {
        $existing = get_option(self::OPTION_KEY);
        if (!is_array($existing)) {
            add_option(self::OPTION_KEY, self::defaults());
        }
    }

    public static function defaults() {
        return [
            'enabled' => 1,
            'display_mode' => 'community_auto',
            'page_ids' => [],
            'url_keywords' => "/community/\n/chumchon/\n/ชุมชน/",
            'title' => 'Community',
            'subtitle' => 'พื้นที่ชุมชน Thinkb4do',
            'menu_items' => "ฟีดชุมชน|auto:community|home\nสมาชิก|auto:members|users",
            'topics_enabled' => 1,
            'topics_source' => 'user_posts',
            'topics_title' => 'Recents',
            'topic_post_types' => "auto
post
community
community_post
tcb4d_community
tcb4d_community_post
tcb4d_post
thinkb4do_community
thinkb4do_community_post
thinkb4do_post",
            'topics_limit' => 20,
            'conversation_badge' => 1,
            'recents_actions' => 1,
            'topic_items' => "ตัวอย่างหัวข้อโพสต์|/community/|folder|2
รายการล่าสุด|/community/?sort=latest|clock|0",
            'lower_info_enabled' => 1,
            'lower_info_title' => 'นโยบาย',
            'lower_info_text' => 'พื้นที่ชุมชนนี้ใช้สำหรับแบ่งปัน สนทนา และติดตามเนื้อหาอย่างเหมาะสมตามแนวทางของ Thinkb4do',
            'lower_policy_links' => "นโยบาย|/policy/
เงื่อนไข|/terms/
ติดต่อ|/contact/",
            'open_close_enabled' => 1,
            'default_collapsed' => 0,
            'desktop_min_width' => 1025,
            'width' => 216,
            'top_offset' => 126,
            'bottom_offset' => 0,
            'left_offset' => 42,
            'z_index' => 68,
            'reserve_space' => 0,
            'show_footer' => 0,
            'plugin_version' => self::VERSION,
            'theme_style' => 'native_blend',
            'manual_shortcode_only' => 0,
        ];
    }

    public function settings() {
        $saved = get_option(self::OPTION_KEY, []);
        $settings = wp_parse_args(is_array($saved) ? $saved : [], self::defaults());

        // Native Blend lock: ตำแหน่งต้องอยู่ใต้ส่วนหัว + แถบเขียวเสมอ และไม่ดึงหัว/ท้าย Sidebar เดิมกลับมา
        $settings['top_offset'] = max(126, absint($settings['top_offset']));
        $settings['bottom_offset'] = 0;
        $settings['width'] = min(232, max(190, absint($settings['width'])));
        $settings['left_offset'] = min(72, max(36, absint($settings['left_offset'])));
        $settings['show_footer'] = 0;
        $settings['theme_style'] = $settings['theme_style'] ?: 'native_blend';
        $settings['topics_enabled'] = !empty($settings['topics_enabled']) ? 1 : 0;
        $settings['topics_source'] = in_array(($settings['topics_source'] ?? 'user_posts'), ['user_posts', 'manual_items'], true) ? $settings['topics_source'] : 'user_posts';
        $settings['topic_post_types'] = $settings['topic_post_types'] ?? self::defaults()['topic_post_types'];
        $settings['topics_limit'] = min(30, max(3, absint($settings['topics_limit'] ?? 20)));
        // แสดงได้ประมาณ 10 หัวข้อก่อน แล้วให้เลื่อนภายใน Recents แทนการดันลงไปทับนโยบาย
        if (in_array(($settings['topics_title'] ?? ''), ['หัวข้อที่โพสต์', 'AiRA สรุปหัวข้อ'], true)) {
            $settings['topics_title'] = 'Recents';
        }
        $settings['conversation_badge'] = !empty($settings['conversation_badge']) ? 1 : 0;
        $settings['recents_actions'] = !empty($settings['recents_actions']) ? 1 : 0;
        $settings['lower_info_enabled'] = !empty($settings['lower_info_enabled']) ? 1 : 0;
        $settings['lower_info_title'] = sanitize_text_field($settings['lower_info_title'] ?? self::defaults()['lower_info_title']);
        $settings['lower_info_text'] = $this->clean_footer_text($settings['lower_info_text'] ?? self::defaults()['lower_info_text']);
        $settings['lower_policy_links'] = $this->sanitize_multiline_text($settings['lower_policy_links'] ?? self::defaults()['lower_policy_links']);
        $settings['open_close_enabled'] = !empty($settings['open_close_enabled']) ? 1 : 0;
        $settings['default_collapsed'] = !empty($settings['default_collapsed']) ? 1 : 0;

        return $settings;
    }

    public function add_admin_page() {
        add_options_page(
            'Thinkb4do Community Desktop Left Sidebar',
            'Community Left Sidebar',
            'manage_options',
            self::ADMIN_SLUG,
            [$this, 'render_admin_page']
        );
    }

    public function register_settings() {
        register_setting(
            self::OPTION_KEY . '_group',
            self::OPTION_KEY,
            [
                'type' => 'array',
                'sanitize_callback' => [$this, 'sanitize_settings'],
                'default' => self::defaults(),
            ]
        );
    }



    public function maybe_upgrade_settings() {
        $saved = get_option(self::OPTION_KEY, []);
        if (!is_array($saved)) {
            update_option(self::OPTION_KEY, self::defaults());
            return;
        }

        $version = isset($saved['plugin_version']) ? (string) $saved['plugin_version'] : '';
        if ($version === self::VERSION) {
            return;
        }

        $upgraded = wp_parse_args($saved, self::defaults());
        // v1.7.0+: ตามคำสั่งล่าสุด เมนูหลักต้องเหลือเฉพาะ ฟีดชุมชน / สมาชิก
        // v1.8.0: เพิ่ม Auto Detect เพื่อแก้อาการโพสต์แล้ว Recents ไม่ขึ้น เพราะเว็บอาจใช้ custom post type คนละชื่อ
        // v1.9.0: เพิ่มส่วนล่างเป็นคำอธิบาย/นโยบาย แบบโปร่งใสและแก้ไขได้จากหลังบ้าน
        // v1.10.0: Recents ดึงเฉพาะตาม user ID และเพิ่มสกรอลล์ภายใน ไม่ให้เกินโซนคำอธิบาย/นโยบาย
        // v1.11.0: บังคับพื้นที่ Recents ให้เริ่มสกรอลล์หลังประมาณหัวข้อที่ 10
        // v1.12.0: ปรับส่วนล่างให้มีลิงก์ Feature / แนะนำ / นโยบาย
        // v1.13.0: เปลี่ยนหัวข้อส่วนล่างเป็นนโยบาย และใช้เฉพาะลิงก์สำคัญของเว็บ
        $upgraded['menu_items'] = self::defaults()['menu_items'];
        $upgraded['topics_title'] = 'Recents';
        $upgraded['topics_source'] = 'user_posts';
        $upgraded['topics_enabled'] = 1;
        $upgraded['recents_actions'] = 1;
        $upgraded['topic_post_types'] = self::defaults()['topic_post_types'];
        $upgraded['topics_limit'] = max(20, absint($upgraded['topics_limit'] ?? 0));
        $upgraded['lower_info_enabled'] = $upgraded['lower_info_enabled'] ?? self::defaults()['lower_info_enabled'];
        $old_lower_title = trim((string) ($upgraded['lower_info_title'] ?? ''));
        $upgraded['lower_info_title'] = in_array($old_lower_title, ['คำอธิบาย', 'คำอธิบาย / นโยบาย', ''], true) ? self::defaults()['lower_info_title'] : $upgraded['lower_info_title'];
        $upgraded['lower_info_text'] = $upgraded['lower_info_text'] ?? self::defaults()['lower_info_text'];
        $old_lower_links = trim((string) ($upgraded['lower_policy_links'] ?? ''));
        if ($old_lower_links === '' || strpos($old_lower_links, 'Feature|/features/') !== false || strpos($old_lower_links, 'แนะนำ|/recommended/') !== false || strpos($old_lower_links, 'นโยบายชุมชน|/community-policy/') !== false || strpos($old_lower_links, 'เงื่อนไขการใช้งาน|/terms/') !== false) {
            $upgraded['lower_policy_links'] = self::defaults()['lower_policy_links'];
        }
        $upgraded['plugin_version'] = self::VERSION;
        update_option(self::OPTION_KEY, $upgraded);
    }

    public function sanitize_settings($input) {
        $input = is_array($input) ? $input : [];
        $defaults = self::defaults();
        $clean = [];

        $clean['enabled'] = !empty($input['enabled']) ? 1 : 0;
        $clean['manual_shortcode_only'] = !empty($input['manual_shortcode_only']) ? 1 : 0;
        $clean['topics_enabled'] = !empty($input['topics_enabled']) ? 1 : 0;
        $clean['conversation_badge'] = !empty($input['conversation_badge']) ? 1 : 0;
        $clean['recents_actions'] = !empty($input['recents_actions']) ? 1 : 0;
        $clean['lower_info_enabled'] = !empty($input['lower_info_enabled']) ? 1 : 0;
        $clean['open_close_enabled'] = !empty($input['open_close_enabled']) ? 1 : 0;
        $clean['default_collapsed'] = !empty($input['default_collapsed']) ? 1 : 0;

        $allowed_modes = ['community_auto', 'selected_pages', 'all_site', 'all_except_home', 'manual_shortcode'];
        $clean['display_mode'] = in_array(($input['display_mode'] ?? ''), $allowed_modes, true) ? $input['display_mode'] : $defaults['display_mode'];

        $allowed_themes = ['native_blend', 'soft_ai', 'minimal', 'solid'];
        $clean['theme_style'] = in_array(($input['theme_style'] ?? ''), $allowed_themes, true) ? $input['theme_style'] : 'native_blend';

        $allowed_topic_sources = ['user_posts', 'manual_items'];
        $clean['topics_source'] = in_array(($input['topics_source'] ?? ''), $allowed_topic_sources, true) ? $input['topics_source'] : 'user_posts';

        $clean['title'] = sanitize_text_field($input['title'] ?? $defaults['title']);
        $clean['subtitle'] = sanitize_text_field($input['subtitle'] ?? $defaults['subtitle']);
        $clean['url_keywords'] = $this->sanitize_multiline_text($input['url_keywords'] ?? $defaults['url_keywords']);
        $clean['menu_items'] = $this->sanitize_multiline_text($input['menu_items'] ?? $defaults['menu_items']);
        $clean['topics_title'] = sanitize_text_field($input['topics_title'] ?? $defaults['topics_title']);
        $clean['topic_post_types'] = $this->sanitize_multiline_text($input['topic_post_types'] ?? $defaults['topic_post_types']);
        $clean['topics_limit'] = $this->bounded_int($input['topics_limit'] ?? $defaults['topics_limit'], 3, 30, $defaults['topics_limit']);
        $clean['topic_items'] = $this->sanitize_multiline_text($input['topic_items'] ?? $defaults['topic_items']);
        $clean['lower_info_title'] = sanitize_text_field($input['lower_info_title'] ?? $defaults['lower_info_title']);
        $clean['lower_info_text'] = $this->clean_footer_text($input['lower_info_text'] ?? $defaults['lower_info_text']);
        $clean['lower_policy_links'] = $this->sanitize_multiline_text($input['lower_policy_links'] ?? $defaults['lower_policy_links']);

        $page_ids = $input['page_ids'] ?? [];
        $clean['page_ids'] = is_array($page_ids) ? array_values(array_filter(array_map('absint', $page_ids))) : [];

        $clean['desktop_min_width'] = $this->bounded_int($input['desktop_min_width'] ?? $defaults['desktop_min_width'], 900, 1800, $defaults['desktop_min_width']);
        $clean['width'] = $this->bounded_int($input['width'] ?? $defaults['width'], 190, 232, $defaults['width']);
        $clean['top_offset'] = $this->bounded_int($input['top_offset'] ?? $defaults['top_offset'], 126, 260, $defaults['top_offset']);
        $clean['bottom_offset'] = 0;
        $clean['left_offset'] = $this->bounded_int($input['left_offset'] ?? $defaults['left_offset'], 36, 72, $defaults['left_offset']);
        $clean['z_index'] = $this->bounded_int($input['z_index'] ?? $defaults['z_index'], 1, 999999, $defaults['z_index']);
        $clean['reserve_space'] = !empty($input['reserve_space']) ? 1 : 0;
        $clean['show_footer'] = 0;
        $clean['plugin_version'] = self::VERSION;

        return $clean;
    }

    private function sanitize_multiline_text($value) {
        $lines = preg_split('/\R/u', wp_unslash((string) $value));
        $lines = is_array($lines) ? $lines : [];
        $clean = [];
        foreach ($lines as $line) {
            $line = trim(wp_strip_all_tags($line));
            if ($line !== '') {
                $clean[] = $line;
            }
        }
        return implode("\n", $clean);
    }

    private function bounded_int($value, $min, $max, $fallback) {
        $value = absint($value);
        if ($value < $min || $value > $max) {
            return $fallback;
        }
        return $value;
    }

    public function enqueue_frontend_assets() {
        if (!$this->should_render()) {
            return;
        }

        $settings = $this->settings();
        $handle = self::ADMIN_SLUG . '-frontend';
        wp_register_style($handle, false, [], self::VERSION);
        wp_enqueue_style($handle);

        $min = (int) $settings['desktop_min_width'];
        $max = max(0, $min - 1);
        $side_offset = (int) $settings['left_offset'];
        $reserve = (int) $settings['width'] + $side_offset + 20;
        $css = str_replace(
            ['VAR_MAXpx','VAR_ACTIVE_CLASS','VAR_RESERVE_CLASS'],
            [$max . 'px', self::ACTIVE_CLASS, self::RESERVE_CLASS],
            $this->base_css()
        );
        $css .= sprintf(
            ':root{--tcb4d-cs-width:%1$dpx;--tcb4d-cs-top:%2$dpx;--tcb4d-cs-bottom:%3$dpx;--tcb4d-cs-side-offset:%4$dpx;--tcb4d-cs-z:%5$d;}@media(min-width:%6$dpx){body.%7$s.%8$s{padding-left:%9$dpx!important;}}',
            (int) $settings['width'],
            (int) $settings['top_offset'],
            (int) $settings['bottom_offset'],
            $side_offset,
            (int) $settings['z_index'],
            $min,
            self::ACTIVE_CLASS,
            self::RESERVE_CLASS,
            $reserve
        );
        wp_add_inline_style($handle, $css);

        $script_handle = self::ADMIN_SLUG . '-frontend-js';
        wp_register_script($script_handle, false, [], self::VERSION, true);
        wp_enqueue_script($script_handle);
        $js = str_replace(
            ['VAR_ID','VAR_MIN','VAR_RUNTIME_CLASS'],
            [self::FRONT_ID, (string) $min, self::RUNTIME_CLASS],
            $this->base_js()
        );
        wp_add_inline_script($script_handle, $js);
    }

    private function base_css() {
        return '
.tcb4d-csbar{position:fixed;top:max(var(--tcb4d-cs-top),126px);bottom:var(--tcb4d-cs-bottom);width:var(--tcb4d-cs-width);z-index:var(--tcb4d-cs-z);font-family:var(--font-main,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",Arial,"Noto Sans Thai","Kanit",sans-serif);box-sizing:border-box;pointer-events:none;transition:width .18s ease,opacity .18s ease;}
.tcb4d-csbar[data-side="left"]{left:var(--tcb4d-cs-side-offset);}
.tcb4d-csbar[data-side="right"]{right:var(--tcb4d-cs-side-offset);}
.tcb4d-csbar *{box-sizing:border-box;}
.tcb4d-cs-card{height:100%;min-height:0;display:flex;flex-direction:column;gap:8px;padding:8px 0 0 0;border:0!important;border-radius:0!important;background:transparent!important;box-shadow:none!important;backdrop-filter:none!important;overflow:hidden;pointer-events:auto;}
.tcb4d-cs-head,.tcb4d-cs-footer,.tcb4d-cs-mark,.tcb4d-cs-title-wrap{display:none!important;}
.tcb4d-cs-nav{display:flex;flex:0 0 auto;flex-direction:column;gap:17px;align-items:flex-start;background:transparent!important;border:0!important;box-shadow:none!important;}
.tcb4d-cs-link{display:inline-flex;align-items:center;gap:16px;min-height:28px;max-width:100%;padding:4px 6px;border:0!important;border-radius:999px;background:transparent!important;box-shadow:none!important;color:#111;text-decoration:none;outline:none;transition:transform .16s ease,color .16s ease,opacity .16s ease;}
.tcb4d-cs-link:hover,.tcb4d-cs-link:focus-visible{transform:translateX(2px);color:#1E6B45;background:transparent!important;}
.tcb4d-csbar[data-side="right"] .tcb4d-cs-link:hover,.tcb4d-csbar[data-side="right"] .tcb4d-cs-link:focus-visible{transform:translateX(-2px);}
.tcb4d-cs-link.is-active{color:#1E6B45;}
.tcb4d-cs-icon{width:24px;height:24px;display:inline-flex;align-items:center;justify-content:center;flex:0 0 24px;color:#111;}
.tcb4d-cs-icon svg{display:block;width:22px;height:22px;stroke:currentColor;stroke-width:1.85;stroke-linecap:round;stroke-linejoin:round;}
.tcb4d-cs-link:hover .tcb4d-cs-icon,.tcb4d-cs-link:focus-visible .tcb4d-cs-icon,.tcb4d-cs-link.is-active .tcb4d-cs-icon{color:#1E6B45;}
.tcb4d-cs-label{font-size:15px;font-weight:700;line-height:1.22;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:#0f5f3e;letter-spacing:.01em;}
.tcb4d-cs-link.is-active .tcb4d-cs-label{color:#1E6B45;}
.tcb4d-cs-fallback-icon{display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;font-size:17px;line-height:1;}
.tcb4d-cs-topics{width:min(100%,204px);margin-top:8px;padding-top:12px;border-top:1px solid rgba(30,107,69,.13);background:transparent!important;box-shadow:none!important;display:flex;flex:0 1 auto;min-height:0;flex-direction:column;overflow:hidden;}
.tcb4d-cs-section-title{display:block;margin:0 0 7px 6px;font-size:12px;font-weight:800;line-height:1.2;color:rgba(17,17,17,.56);letter-spacing:.015em;}
.tcb4d-cs-topic-list{display:flex;flex-direction:column;gap:2px;margin:0;padding:0 3px 0 0;list-style:none;min-height:0;max-height:clamp(160px,calc(100vh - var(--tcb4d-cs-top) - 220px),334px);overflow-y:auto;overflow-x:hidden;scrollbar-width:thin;scrollbar-color:rgba(30,107,69,.35) transparent;}
.tcb4d-cs-topic-list::-webkit-scrollbar{width:5px;}
.tcb4d-cs-topic-list::-webkit-scrollbar-track{background:transparent;}
.tcb4d-cs-topic-list::-webkit-scrollbar-thumb{background:rgba(30,107,69,.28);border-radius:999px;}
.tcb4d-cs-topic-list::-webkit-scrollbar-thumb:hover{background:rgba(30,107,69,.45);}
.tcb4d-cs-topic-row{position:relative;display:flex;align-items:center;gap:4px;width:100%;max-width:100%;}
.tcb4d-cs-topic-link{display:flex;align-items:center;justify-content:space-between;gap:10px;min-width:0;flex:1 1 auto;max-width:100%;padding:7px 6px;border:0!important;border-radius:0;background:transparent!important;box-shadow:none!important;color:rgba(17,17,17,.78);font-size:13.5px;font-weight:600;line-height:1.25;text-decoration:none;white-space:nowrap;overflow:hidden;transition:color .16s ease,transform .16s ease;}
.tcb4d-cs-topic-link:hover,.tcb4d-cs-topic-link:focus-visible{color:#1E6B45;background:transparent!important;transform:translateX(2px);outline:none;}
.tcb4d-cs-topic-link.is-active{color:#1E6B45;background:transparent!important;}
.tcb4d-cs-topic-label{min-width:0;flex:1 1 auto;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.tcb4d-cs-topic-meta{margin-left:auto;display:inline-flex;align-items:center;gap:4px;flex:0 0 auto;}
.tcb4d-cs-topic-badge{min-width:18px;height:18px;padding:0 5px;display:inline-flex;align-items:center;justify-content:center;border:1px solid rgba(30,107,69,.18);border-radius:999px;background:transparent!important;color:#1E6B45;font-size:11px;font-weight:800;line-height:1;}
.tcb4d-cs-topic-link:hover .tcb4d-cs-topic-badge,.tcb4d-cs-topic-link:focus-visible .tcb4d-cs-topic-badge{border-color:rgba(30,107,69,.34);color:#1E6B45;background:transparent!important;}
.tcb4d-cs-topic-actions{display:inline-flex;align-items:center;gap:2px;opacity:0;transform:translateX(-2px);transition:opacity .14s ease,transform .14s ease;}
.tcb4d-cs-topic-row:hover .tcb4d-cs-topic-actions,.tcb4d-cs-topic-row:focus-within .tcb4d-cs-topic-actions{opacity:1;transform:translateX(0);}
.tcb4d-cs-topic-action{width:20px;height:20px;display:inline-flex;align-items:center;justify-content:center;border:0!important;border-radius:999px;background:transparent!important;box-shadow:none!important;color:rgba(17,17,17,.46);text-decoration:none;line-height:1;}
.tcb4d-cs-topic-action:hover,.tcb4d-cs-topic-action:focus-visible{color:#1E6B45;background:transparent!important;outline:none;}
.tcb4d-cs-topic-action.is-danger:hover,.tcb4d-cs-topic-action.is-danger:focus-visible{color:#b42318;}
.tcb4d-cs-topic-action svg{width:14px;height:14px;display:block;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;fill:none;}
.tcb4d-cs-lower-info{width:min(100%,204px);flex:0 0 auto;margin-top:0;padding:12px 6px 8px;border-top:1px solid rgba(30,107,69,.12);background:transparent!important;box-shadow:none!important;color:rgba(17,17,17,.62);}
.tcb4d-cs-lower-title{display:block;margin:0 0 6px;font-size:11.5px;font-weight:800;line-height:1.2;color:rgba(17,17,17,.58);letter-spacing:.015em;}
.tcb4d-cs-lower-text{margin:0;font-size:11.5px;font-weight:500;line-height:1.45;color:rgba(17,17,17,.58);}
.tcb4d-cs-policy-list{display:flex;flex-wrap:wrap;align-items:center;gap:4px 8px;margin:8px 0 0;padding:0;list-style:none;}
.tcb4d-cs-policy-link{display:inline-flex;align-items:center;max-width:100%;padding:0;border:0!important;background:transparent!important;box-shadow:none!important;color:#1E6B45;font-size:11.5px;font-weight:800;line-height:1.35;text-decoration:none;opacity:.86;}
.tcb4d-cs-policy-link:hover,.tcb4d-cs-policy-link:focus-visible{opacity:1;color:#F97316;background:transparent!important;outline:none;text-decoration:none;}
.tcb4d-cs-policy-sep{display:inline-flex;margin-left:8px;color:rgba(17,17,17,.28);font-size:11px;font-weight:700;}
.tcb4d-cs-toggle{position:absolute;top:3px;right:-28px;width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;border:0!important;border-radius:999px;background:transparent!important;color:rgba(17,17,17,.55);box-shadow:none!important;backdrop-filter:none!important;cursor:pointer;pointer-events:auto;transition:transform .16s ease,color .16s ease,opacity .16s ease;}
.tcb4d-cs-toggle:hover,.tcb4d-cs-toggle:focus-visible{transform:translateX(1px);color:#1E6B45;background:transparent!important;box-shadow:none!important;outline:none;}
.tcb4d-cs-toggle svg{width:16px;height:16px;display:block;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;fill:none;transition:transform .18s ease;}
.tcb4d-csbar.is-collapsed{width:48px;}
.tcb4d-csbar.is-collapsed .tcb4d-cs-card{align-items:flex-start;}
.tcb4d-csbar.is-collapsed .tcb4d-cs-nav{gap:17px;}
.tcb4d-csbar.is-collapsed .tcb4d-cs-link{gap:0;padding:4px 6px;}
.tcb4d-csbar.is-collapsed .tcb4d-cs-label,.tcb4d-csbar.is-collapsed .tcb4d-cs-topics,.tcb4d-csbar.is-collapsed .tcb4d-cs-lower-info{display:none!important;}
.tcb4d-csbar.is-collapsed .tcb4d-cs-toggle svg{transform:rotate(180deg);}
body .tcb4d-csbar .tcb4d-cs-card,body .tcb4d-csbar .tcb4d-cs-link,body .tcb4d-csbar .tcb4d-cs-topic-link,body .tcb4d-csbar .tcb4d-cs-toggle,body .tcb4d-csbar .tcb4d-cs-lower-info,body .tcb4d-csbar .tcb4d-cs-policy-link{background:transparent!important;box-shadow:none!important;border-color:transparent!important;}
@media(max-width:VAR_MAXpx){.tcb4d-csbar{display:none!important;}body.VAR_ACTIVE_CLASS.VAR_RESERVE_CLASS{padding-left:0!important;padding-right:0!important;}}
';
    }

    private function base_js() {
        return '
(function(){
  var id = "VAR_ID";
  var minWidth = VAR_MIN;
  var sidebar = document.getElementById(id);
  if(!sidebar){ return; }
  var toggle = sidebar.querySelector("[data-tcb4d-cs-toggle]");
  var storageKey = "tcb4d_left_sidebar_collapsed";
  function getStored(){
    try { return window.localStorage ? window.localStorage.getItem(storageKey) : null; } catch(e){ return null; }
  }
  function setStored(value){
    try { if(window.localStorage){ window.localStorage.setItem(storageKey, value ? "1" : "0"); } } catch(e){}
  }
  function setCollapsed(collapsed, persist){
    sidebar.classList.toggle("is-collapsed", !!collapsed);
    sidebar.setAttribute("data-collapsed", collapsed ? "true" : "false");
    if(toggle){
      toggle.setAttribute("aria-expanded", collapsed ? "false" : "true");
      toggle.setAttribute("aria-label", collapsed ? "เปิดแถบเมนู" : "ปิดแถบเมนู");
      toggle.setAttribute("title", collapsed ? "เปิดแถบเมนู" : "ปิดแถบเมนู");
    }
    if(persist){ setStored(!!collapsed); }
  }
  if(!toggle){
    setCollapsed(false, false);
  }
  var stored = toggle ? getStored() : null;
  var initial = sidebar.getAttribute("data-default-collapsed") === "true";
  if(stored === "1"){ initial = true; }
  if(stored === "0"){ initial = false; }
  setCollapsed(initial, false);
  if(toggle){
    toggle.addEventListener("click", function(){
      setCollapsed(!sidebar.classList.contains("is-collapsed"), true);
    });
  }
  sidebar.addEventListener("click", function(event){
    var action = event.target.closest ? event.target.closest("[data-tcb4d-confirm]") : null;
    if(!action){ return; }
    var message = action.getAttribute("data-tcb4d-confirm") || "ยืนยันการลบรายการนี้?";
    if(!window.confirm(message)){ event.preventDefault(); }
  });
  function sync(){
    var isDesk = window.innerWidth >= minWidth;
    sidebar.hidden = !isDesk;
    document.body.classList.toggle("VAR_RUNTIME_CLASS", isDesk);
  }
  sync();
  window.addEventListener("resize", sync, {passive:true});
})();
';
    }

    public function add_body_classes($classes) {
        if ($this->should_render()) {
            $settings = $this->settings();
            $classes[] = self::ACTIVE_CLASS;
            $classes[] = 'tcb4d-community-sidebar-desktop-only';
            $classes[] = 'tcb4d-community-left-sidebar-desktop-only';
            if (!empty($settings['reserve_space'])) {
                $classes[] = self::RESERVE_CLASS;
            }
            $classes[] = 'tcb4d-community-sidebar-theme-' . sanitize_html_class($settings['theme_style']);
        }
        return $classes;
    }

    public function render_sidebar_from_footer() {
        $settings = $this->settings();
        if (!empty($settings['manual_shortcode_only']) || $settings['display_mode'] === 'manual_shortcode') {
            return;
        }
        if (!$this->should_render()) {
            return;
        }
        echo $this->sidebar_markup(false); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    public function shortcode_sidebar() {
        if (!$this->settings()['enabled']) {
            return '';
        }
        return $this->sidebar_markup(true);
    }

    private function sidebar_markup($shortcode = false) {
        $settings = $this->settings();
        $items = $this->parse_menu_items($settings['menu_items']);
        $topics = $this->topic_items($settings);
        $policy_links = $this->parse_policy_links($settings['lower_policy_links'] ?? '');
        $theme = sanitize_html_class($settings['theme_style']);
        $id = $shortcode ? self::FRONT_ID . '-shortcode' : self::FRONT_ID;
        $default_collapsed = (!empty($settings['open_close_enabled']) && !empty($settings['default_collapsed'])) ? 'true' : 'false';

        ob_start();
        ?>
        <aside id="<?php echo esc_attr($id); ?>" class="tcb4d-csbar tcb4d-community-left-sidebar tcb4d-theme-<?php echo esc_attr($theme); ?>" data-side="<?php echo esc_attr(self::SIDE); ?>" data-desktop-only="true" data-default-collapsed="<?php echo esc_attr($default_collapsed); ?>" aria-label="<?php echo esc_attr($settings['title']); ?>">
            <?php if (!empty($settings['open_close_enabled'])): ?>
                <button class="tcb4d-cs-toggle" type="button" data-tcb4d-cs-toggle aria-expanded="true" aria-label="ปิดแถบเมนู" title="ปิดแถบเมนู">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 18 9 12l6-6"/></svg>
                </button>
            <?php endif; ?>
            <div class="tcb4d-cs-card">
                <nav class="tcb4d-cs-nav" aria-label="Community navigation">
                    <?php foreach ($items as $item): ?>
                        <?php $is_current = $this->is_current_item($item['url']); ?>
                        <a class="tcb4d-cs-link<?php echo $is_current ? ' is-active' : ''; ?>" href="<?php echo esc_url($item['url']); ?>"<?php echo $is_current ? ' aria-current="page"' : ''; ?>>
                            <span class="tcb4d-cs-icon" aria-hidden="true"><?php echo $this->render_icon($item['icon'], $item['label']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                            <span class="tcb4d-cs-label"><?php echo esc_html($item['label']); ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <?php if (!empty($settings['topics_enabled']) && !empty($topics)): ?>
                    <section class="tcb4d-cs-topics" aria-label="<?php echo esc_attr($settings['topics_title']); ?>">
                        <span class="tcb4d-cs-section-title"><?php echo esc_html($settings['topics_title']); ?></span>
                        <ul class="tcb4d-cs-topic-list">
                            <?php foreach ($topics as $topic): ?>
                                <?php $is_current_topic = $this->is_current_item($topic['url']); ?>
                                <li>
                                    <div class="tcb4d-cs-topic-row">
                                        <a class="tcb4d-cs-topic-link<?php echo $is_current_topic ? ' is-active' : ''; ?>" href="<?php echo esc_url($topic['url']); ?>" title="<?php echo esc_attr($topic['description'] ?? $topic['label']); ?>"<?php echo $is_current_topic ? ' aria-current="page"' : ''; ?>>
                                            <span class="tcb4d-cs-topic-label"><?php echo esc_html($topic['label']); ?></span>
                                            <?php if (!empty($settings['conversation_badge']) && !empty($topic['badge'])): ?>
                                                <span class="tcb4d-cs-topic-badge" aria-label="<?php echo esc_attr(sprintf('มีการสนทนา %s รายการ', (string) $topic['badge'])); ?>"><?php echo esc_html($this->badge_text($topic['badge'])); ?></span>
                                            <?php endif; ?>
                                        </a>
                                        <?php if (!empty($settings['recents_actions']) && !empty($topic['actions'])): ?>
                                            <span class="tcb4d-cs-topic-actions" aria-label="จัดการหัวข้อ">
                                                <?php if (!empty($topic['actions']['edit'])): ?>
                                                    <a class="tcb4d-cs-topic-action" href="<?php echo esc_url($topic['actions']['edit']); ?>" title="แก้ไขหัวข้อ/โพสต์" aria-label="แก้ไขหัวข้อ/โพสต์">
                                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4.5 19.5h4.1L19 9.1a2.1 2.1 0 0 0 0-3l-1.1-1.1a2.1 2.1 0 0 0-3 0L4.5 15.4v4.1Z"/><path d="m13.8 6.1 4.1 4.1"/></svg>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (!empty($topic['actions']['delete'])): ?>
                                                    <a class="tcb4d-cs-topic-action is-danger" href="<?php echo esc_url($topic['actions']['delete']); ?>" title="ลบโพสต์นี้" aria-label="ลบโพสต์นี้" data-tcb4d-confirm="ลบโพสต์นี้หรือไม่? หัวข้อ Recents จะหายตามโพสต์ทันที">
                                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 7h14"/><path d="M9 7V5.6c0-.7.5-1.1 1.2-1.1h3.6c.7 0 1.2.4 1.2 1.1V7"/><path d="M8 10.2v7.4c0 .9.7 1.6 1.6 1.6h4.8c.9 0 1.6-.7 1.6-1.6v-7.4"/><path d="M10.7 11.4v5"/><path d="M13.3 11.4v5"/></svg>
                                                    </a>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php endif; ?>

                <?php if (!empty($settings['lower_info_enabled'])): ?>
                    <section class="tcb4d-cs-lower-info" aria-label="นโยบายและลิงก์สำคัญของเว็บ">
                        <?php if (!empty($settings['lower_info_title'])): ?>
                            <span class="tcb4d-cs-lower-title"><?php echo esc_html($settings['lower_info_title']); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($settings['lower_info_text'])): ?>
                            <p class="tcb4d-cs-lower-text"><?php echo esc_html($settings['lower_info_text']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($policy_links)): ?>
                            <ul class="tcb4d-cs-policy-list" aria-label="ลิงก์สำคัญของเว็บ">
                                <?php foreach ($policy_links as $index => $policy): ?>
                                    <li>
                                        <a class="tcb4d-cs-policy-link" href="<?php echo esc_url($policy['url']); ?>"><?php echo esc_html($policy['label']); ?></a>
                                        <?php if ($index < count($policy_links) - 1): ?><span class="tcb4d-cs-policy-sep" aria-hidden="true">/</span><?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>
            </div>
        </aside>
        <?php
        return (string) ob_get_clean();
    }

    private function parse_policy_links($raw) {
        $links = [];
        $lines = preg_split('/\R/u', (string) $raw);
        foreach ((array) $lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $parts = array_map('trim', explode('|', $line));
            $label = sanitize_text_field($parts[0] ?? '');
            $url = $parts[1] ?? '';
            if ($label === '') {
                continue;
            }
            if ($url === '') {
                $url = '#';
            }
            $links[] = [
                'label' => $label,
                'url' => $this->resolve_menu_url($url),
            ];
        }
        return array_slice($links, 0, 4);
    }

    private function clean_footer_text($text) {
        $text = wp_strip_all_tags((string) $text, true);
        $text = preg_replace('/\s+/u', ' ', $text);
        $text = trim($text);
        if (function_exists('mb_substr')) {
            return mb_substr($text, 0, 180, 'UTF-8');
        }
        return substr($text, 0, 180);
    }

    private function topic_items($settings) {
        $source = $settings['topics_source'] ?? 'user_posts';
        if ($source === 'manual_items') {
            return $this->parse_menu_items($settings['topic_items'] ?? '');
        }

        $items = $this->user_post_topic_items($settings);
        if (!empty($items)) {
            return $items;
        }

        // ถ้ายังไม่มีโพสต์จริง ไม่ดึงตัวอย่างขึ้นมาแทน เพื่อไม่ให้ผู้ใช้เข้าใจผิดว่าเชื่อมโพสต์แล้ว
        if (($settings['topics_source'] ?? 'user_posts') === 'user_posts') {
            return [];
        }

        return $this->parse_menu_items($settings['topic_items'] ?? '');
    }

    private function user_post_topic_items($settings) {
        if (!class_exists('WP_Query')) {
            return [];
        }

        // Recents ต้องอิง user ID เท่านั้น เพื่อไม่ให้ Sidebar ดึงหัวข้อของสมาชิกคนอื่นขึ้นมาปน
        if (!is_user_logged_in()) {
            return [];
        }
        $current_user_id = absint(get_current_user_id());
        if ($current_user_id <= 0) {
            return [];
        }

        $post_types = $this->valid_topic_post_types($settings['topic_post_types'] ?? 'auto');
        if (empty($post_types)) {
            return [];
        }

        $base_args = [
            'post_type' => $post_types,
            'posts_per_page' => min(30, max(3, absint($settings['topics_limit'] ?? 20))),
            'orderby' => 'modified',
            'order' => 'DESC',
            'no_found_rows' => true,
            'ignore_sticky_posts' => true,
            'post_status' => ['publish', 'private', 'pending', 'draft'],
        ];

        // ชั้นหลัก: ดึงจาก post_author = user ID ของผู้ล็อกอิน
        $author_args = $base_args;
        $author_args['author'] = $current_user_id;
        $query = new WP_Query($author_args);

        // ชั้นสำรองที่ยังยึด user ID: รองรับระบบชุมชนที่เก็บเจ้าของโพสต์ไว้ใน post meta แทน post_author
        if (empty($query->posts)) {
            $meta_args = $base_args;
            $meta_args['meta_query'] = [
                'relation' => 'OR',
                [
                    'key' => 'user_id',
                    'value' => $current_user_id,
                    'compare' => '=',
                    'type' => 'NUMERIC',
                ],
                [
                    'key' => '_user_id',
                    'value' => $current_user_id,
                    'compare' => '=',
                    'type' => 'NUMERIC',
                ],
                [
                    'key' => 'author_id',
                    'value' => $current_user_id,
                    'compare' => '=',
                    'type' => 'NUMERIC',
                ],
                [
                    'key' => '_author_id',
                    'value' => $current_user_id,
                    'compare' => '=',
                    'type' => 'NUMERIC',
                ],
                [
                    'key' => 'tcb4d_user_id',
                    'value' => $current_user_id,
                    'compare' => '=',
                    'type' => 'NUMERIC',
                ],
                [
                    'key' => 'thinkb4do_user_id',
                    'value' => $current_user_id,
                    'compare' => '=',
                    'type' => 'NUMERIC',
                ],
            ];
            $query = new WP_Query($meta_args);
        }

        if (empty($query->posts)) {
            return [];
        }

        $items = [];
        foreach ($query->posts as $post) {
            $hidden = (string) get_post_meta($post->ID, '_tcb4d_aira_recent_hidden', true);
            if ($hidden === '1') {
                continue;
            }
            $summary = $this->aira_recent_title($post);
            $original_title = trim(wp_strip_all_tags((string) get_the_title($post)));
            $actions = [];
            if (current_user_can('edit_post', $post->ID)) {
                $edit_link = get_edit_post_link($post->ID, 'raw');
                if ($edit_link) {
                    $actions['edit'] = $edit_link;
                }
            }
            if (current_user_can('delete_post', $post->ID)) {
                $delete_link = get_delete_post_link($post->ID, '', false);
                if ($delete_link) {
                    $actions['delete'] = $delete_link;
                }
            }
            $items[] = [
                'label' => $summary !== '' ? $summary : ($original_title !== '' ? $original_title : 'ไม่มีหัวข้อ'),
                'url' => get_permalink($post),
                'icon' => 'spark',
                'badge' => absint(get_comments_number($post)),
                'description' => sprintf('AiRA ตั้งหัวข้อจากโพสต์: %s', $summary !== '' ? $summary : $original_title),
                'actions' => $actions,
            ];
        }
        wp_reset_postdata();

        return $items;
    }

    private function aira_recent_title($post) {
        $post = get_post($post);
        if (!$post) {
            return '';
        }

        $custom_title = $this->clean_topic_text((string) get_post_meta($post->ID, '_tcb4d_aira_recent_custom_title', true));
        if ($custom_title !== '') {
            return $this->trim_topic_text($custom_title, 58);
        }

        $hash = $this->aira_topic_source_hash($post);
        $cached_summary = (string) get_post_meta($post->ID, '_tcb4d_aira_recent_title', true);
        if ($cached_summary === '') {
            $cached_summary = (string) get_post_meta($post->ID, '_tcb4d_aira_topic_summary', true);
        }
        $cached_hash = (string) get_post_meta($post->ID, '_tcb4d_aira_recent_hash', true);
        if ($cached_hash === '') {
            $cached_hash = (string) get_post_meta($post->ID, '_tcb4d_aira_topic_hash', true);
        }

        if ($cached_summary !== '' && hash_equals($cached_hash, $hash)) {
            return $cached_summary;
        }

        $summary = $this->build_aira_topic_summary($post);
        if ($summary !== '') {
            update_post_meta($post->ID, '_tcb4d_aira_recent_title', $summary);
            update_post_meta($post->ID, '_tcb4d_aira_recent_hash', $hash);
        }

        return $summary;
    }

    private function aira_topic_source_hash($post) {
        $payload = [
            'title' => (string) $post->post_title,
            'excerpt' => (string) $post->post_excerpt,
            'content' => (string) $post->post_content,
        ];
        return md5(wp_json_encode($payload));
    }

    private function build_aira_topic_summary($post) {
        $title = trim(wp_strip_all_tags((string) get_the_title($post)));
        $excerpt = $this->clean_topic_text((string) $post->post_excerpt);
        $content = $this->clean_topic_text((string) $post->post_content);

        if ($title !== '' && !$this->is_generic_topic_title($title)) {
            return $this->trim_topic_text($title, 58);
        }

        if ($excerpt !== '') {
            return $this->trim_topic_text($excerpt, 58);
        }

        if ($content !== '') {
            return $this->trim_topic_text($content, 58);
        }

        return 'ไม่มีหัวข้อ';
    }

    private function clean_topic_text($text) {
        $text = strip_shortcodes((string) $text);
        $text = wp_strip_all_tags($text, true);
        $text = preg_replace('#https?://\S+#iu', ' ', $text);
        $text = preg_replace('/\s+/u', ' ', (string) $text);
        return trim((string) $text);
    }

    private function trim_topic_text($text, $limit = 58) {
        $text = trim((string) $text);
        if ($text === '') {
            return '';
        }

        $sentence_parts = preg_split('/(?<=[.!?。！？]|ครับ|ค่ะ|คะ)\s+/u', $text, 2);
        if (!empty($sentence_parts[0])) {
            $text = trim($sentence_parts[0]);
        }

        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            if (mb_strlen($text, 'UTF-8') > $limit) {
                return rtrim(mb_substr($text, 0, $limit, 'UTF-8')) . '…';
            }
            return $text;
        }

        if (strlen($text) > $limit) {
            return rtrim(substr($text, 0, $limit)) . '…';
        }
        return $text;
    }

    private function is_generic_topic_title($title) {
        $title = trim((string) $title);
        $lower = function_exists('mb_strtolower') ? mb_strtolower($title, 'UTF-8') : strtolower($title);
        $generic = ['untitled', 'ไม่มีชื่อ', 'ไม่มีหัวข้อ', 'new post', 'โพสต์ใหม่', 'draft'];
        return in_array($lower, $generic, true);
    }

    public function refresh_aira_topic_summary_on_save($post_id, $post, $update) {
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id) || !$post instanceof WP_Post) {
            return;
        }

        $settings = $this->settings();
        $valid_types = $this->valid_topic_post_types($settings['topic_post_types'] ?? 'post');
        if (!in_array($post->post_type, $valid_types, true)) {
            return;
        }

        if ($post->post_status === 'trash' || $post->post_status === 'auto-draft') {
            $this->delete_aira_topic_summary_meta($post_id);
            return;
        }

        $custom_title = $this->clean_topic_text((string) get_post_meta($post_id, '_tcb4d_aira_recent_custom_title', true));
        if ($custom_title !== '') {
            return;
        }

        $summary = $this->build_aira_topic_summary($post);
        if ($summary !== '') {
            update_post_meta($post_id, '_tcb4d_aira_recent_title', $summary);
            update_post_meta($post_id, '_tcb4d_aira_recent_hash', $this->aira_topic_source_hash($post));
        }
    }

    public function delete_aira_topic_summary_meta($post_id) {
        delete_post_meta($post_id, '_tcb4d_aira_topic_summary');
        delete_post_meta($post_id, '_tcb4d_aira_topic_hash');
        delete_post_meta($post_id, '_tcb4d_aira_recent_title');
        delete_post_meta($post_id, '_tcb4d_aira_recent_hash');
        delete_post_meta($post_id, '_tcb4d_aira_recent_custom_title');
        delete_post_meta($post_id, '_tcb4d_aira_recent_hidden');
    }


    public function add_recents_meta_box() {
        $settings = $this->settings();
        $post_types = $this->valid_topic_post_types($settings['topic_post_types'] ?? 'post');
        foreach ($post_types as $post_type) {
            add_meta_box(
                'tcb4d-aira-recents-title',
                'AiRA Recents',
                [$this, 'render_recents_meta_box'],
                $post_type,
                'side',
                'default'
            );
        }
    }

    public function render_recents_meta_box($post) {
        if (!$post instanceof WP_Post) {
            return;
        }
        wp_nonce_field('tcb4d_aira_recents_meta', 'tcb4d_aira_recents_nonce');
        $custom_title = (string) get_post_meta($post->ID, '_tcb4d_aira_recent_custom_title', true);
        $hidden = (string) get_post_meta($post->ID, '_tcb4d_aira_recent_hidden', true);
        $auto_title = $this->build_aira_topic_summary($post);
        ?>
        <p style="margin-top:0"><strong>หัวข้อที่ AiRA ตั้งให้:</strong><br><?php echo esc_html($auto_title !== '' ? $auto_title : 'ยังไม่มีหัวข้อ'); ?></p>
        <p>
            <label for="tcb4d-aira-recent-custom-title"><strong>แก้ชื่อใน Recents</strong></label>
            <input id="tcb4d-aira-recent-custom-title" type="text" class="widefat" name="tcb4d_aira_recent_custom_title" value="<?php echo esc_attr($custom_title); ?>" placeholder="เว้นว่าง = ให้ AiRA ตั้งให้อัตโนมัติ">
        </p>
        <p>
            <label><input type="checkbox" name="tcb4d_aira_recent_hidden" value="1" <?php checked($hidden, '1'); ?>> ซ่อน/ลบออกจาก Recents แต่ไม่ลบโพสต์</label>
        </p>
        <p class="description">ถ้าลบโพสต์จริง หัวข้อ Recents จะหายตามโพสต์ทันที</p>
        <?php
    }

    public function save_recents_meta_box($post_id, $post, $update) {
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id) || !$post instanceof WP_Post) {
            return;
        }
        if (!isset($_POST['tcb4d_aira_recents_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['tcb4d_aira_recents_nonce'])), 'tcb4d_aira_recents_meta')) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $custom_title = isset($_POST['tcb4d_aira_recent_custom_title']) ? $this->clean_topic_text(wp_unslash((string) $_POST['tcb4d_aira_recent_custom_title'])) : '';
        if ($custom_title !== '') {
            update_post_meta($post_id, '_tcb4d_aira_recent_custom_title', $this->trim_topic_text($custom_title, 58));
        } else {
            delete_post_meta($post_id, '_tcb4d_aira_recent_custom_title');
        }

        if (!empty($_POST['tcb4d_aira_recent_hidden'])) {
            update_post_meta($post_id, '_tcb4d_aira_recent_hidden', '1');
        } else {
            delete_post_meta($post_id, '_tcb4d_aira_recent_hidden');
        }
    }

    private function valid_topic_post_types($raw) {
        $lines = preg_split('/\R/u', (string) $raw);
        $post_types = [];
        $auto_detect = false;

        foreach ((array) $lines as $line) {
            $post_type = sanitize_key(trim($line));
            if ($post_type === 'auto' || $post_type === '*') {
                $auto_detect = true;
                continue;
            }
            if ($post_type !== '' && post_type_exists($post_type)) {
                $post_types[] = $post_type;
            }
        }

        if ($auto_detect || empty($post_types)) {
            $post_types = array_merge($post_types, $this->auto_detect_topic_post_types());
        }

        if (post_type_exists('post')) {
            array_unshift($post_types, 'post');
        }

        $post_types = array_values(array_unique(array_filter($post_types)));
        $post_types = apply_filters('tcb4d_community_left_sidebar_topic_post_types', $post_types);
        $post_types = array_values(array_filter(array_map('sanitize_key', (array) $post_types), 'post_type_exists'));

        return $post_types;
    }

    private function auto_detect_topic_post_types() {
        $excluded = ['attachment', 'nav_menu_item', 'wp_block', 'wp_template', 'wp_template_part', 'wp_global_styles', 'wp_navigation', 'custom_css', 'customize_changeset', 'revision', 'oembed_cache', 'user_request'];
        $detected = [];
        $objects = get_post_types([], 'objects');

        foreach ((array) $objects as $name => $object) {
            $name = sanitize_key($name);
            if ($name === '' || in_array($name, $excluded, true)) {
                continue;
            }

            $label_blob = trim((string) ($object->label ?? '') . ' ' . (string) ($object->labels->name ?? '') . ' ' . (string) ($object->labels->singular_name ?? '') . ' ' . $name);
            $label_blob = function_exists('mb_strtolower') ? mb_strtolower($label_blob, 'UTF-8') : strtolower($label_blob);
            $looks_like_community_post = (strpos($label_blob, 'community') !== false || strpos($label_blob, 'ชุมชน') !== false || strpos($label_blob, 'post') !== false || strpos($label_blob, 'โพสต์') !== false || strpos($label_blob, 'feed') !== false || strpos($label_blob, 'reel') !== false || strpos($label_blob, 'member') !== false);

            if ($name === 'post' || (!empty($object->public) && $looks_like_community_post)) {
                $detected[] = $name;
            }
        }

        return $detected;
    }

    private function badge_text($number) {
        $number = absint($number);
        if ($number > 99) {
            return '99+';
        }
        return (string) $number;
    }

    private function parse_menu_items($raw) {
        $lines = preg_split('/\R/u', (string) $raw);
        $items = [];
        foreach ((array) $lines as $line) {
            $parts = array_map('trim', explode('|', $line));
            if (count($parts) >= 2 && $parts[0] !== '' && $parts[1] !== '') {
                $items[] = [
                    'label' => $parts[0],
                    'url' => $this->resolve_menu_url($parts[1], $parts[0]),
                    'icon' => $parts[2] ?? '',
                    'badge' => isset($parts[3]) ? absint($parts[3]) : 0,
                ];
            }
        }
        return $items;
    }

    private function resolve_menu_url($url, $label = '') {
        $url = trim((string) $url);
        $label_text = trim(wp_strip_all_tags((string) $label));
        $lower = function_exists('mb_strtolower') ? mb_strtolower($url . ' ' . $label_text, 'UTF-8') : strtolower($url . ' ' . $label_text);

        if ($url === '') {
            return '#';
        }

        if (strpos($url, 'auto:') === 0) {
            $token = sanitize_key(substr($url, 5));
            if ($token === 'community') {
                return $this->community_page_url();
            }
            if ($token === 'community_create' || $token === 'create_post') {
                return add_query_arg('create', 'post', $this->community_page_url());
            }
            if ($token === 'notifications' || $token === 'community_notifications') {
                return add_query_arg('tab', 'notifications', $this->community_page_url());
            }
            if ($token === 'members') {
                return $this->members_page_url();
            }
        }

        if (strpos($lower, 'create=post') !== false || strpos($lower, 'โพสต์ใหม่') !== false || strpos($lower, 'post new') !== false) {
            return add_query_arg('create', 'post', $this->community_page_url());
        }
        if (strpos($lower, 'tab=notifications') !== false || strpos($lower, 'แจ้งเตือน') !== false || strpos($lower, 'notification') !== false) {
            return add_query_arg('tab', 'notifications', $this->community_page_url());
        }
        if ($this->url_path_matches($url, ['/members/', '/member/']) || strpos($lower, 'สมาชิก') !== false || strpos($lower, 'members') !== false) {
            return $this->members_page_url();
        }
        if ($this->url_path_matches($url, ['/community/', '/chumchon/', '/ชุมชน/']) || strpos($lower, 'ฟีดชุมชน') !== false || strpos($lower, 'community') !== false) {
            return $this->carry_query_to_url($url, $this->community_page_url());
        }

        if (preg_match('#^https?://#i', $url) || strpos($url, '//') === 0 || strpos($url, '#') === 0) {
            return $url;
        }

        return home_url('/' . ltrim($url, '/'));
    }

    private function community_page_url() {
        $found = apply_filters('tcb4d_community_left_sidebar_page_url', '', 'community');
        if (is_string($found) && $found !== '') {
            return $found;
        }
        return $this->find_real_page_url(
            ['community', 'chumchon', 'community-feed', 'thinkb4do-community'],
            ['ชุมชน', 'ฟีดชุมชน', 'Community', 'Thinkb4do Community'],
            home_url('/community/')
        );
    }

    private function members_page_url() {
        $found = apply_filters('tcb4d_community_left_sidebar_page_url', '', 'members');
        if (is_string($found) && $found !== '') {
            return $found;
        }
        return $this->find_real_page_url(
            ['members', 'member', 'profile', 'account', 'user', 'users'],
            ['สมาชิก', 'พื้นที่สมาชิก', 'Members', 'Member', 'Profile'],
            home_url('/members/')
        );
    }

    private function find_real_page_url($slug_candidates, $title_candidates, $fallback) {
        foreach ((array) $slug_candidates as $slug) {
            $page = get_page_by_path($slug);
            if ($page instanceof WP_Post && $page->post_status === 'publish') {
                return get_permalink($page);
            }
        }

        $pages = get_pages([
            'post_status' => 'publish',
            'sort_column' => 'menu_order,post_title',
            'sort_order' => 'ASC',
            'number' => 100,
        ]);

        foreach ((array) $pages as $page) {
            $page_title = trim(wp_strip_all_tags((string) $page->post_title));
            foreach ((array) $title_candidates as $title) {
                if ($page_title === (string) $title) {
                    return get_permalink($page);
                }
            }
        }

        foreach ((array) $pages as $page) {
            $haystack = (function_exists('mb_strtolower') ? mb_strtolower($page->post_title . ' ' . $page->post_name, 'UTF-8') : strtolower($page->post_title . ' ' . $page->post_name));
            foreach (array_merge((array) $slug_candidates, (array) $title_candidates) as $needle) {
                $needle = function_exists('mb_strtolower') ? mb_strtolower((string) $needle, 'UTF-8') : strtolower((string) $needle);
                if ($needle !== '' && strpos($haystack, $needle) !== false) {
                    return get_permalink($page);
                }
            }
        }

        return $fallback;
    }

    private function url_path_matches($url, $paths) {
        $path = wp_parse_url((string) $url, PHP_URL_PATH);
        if (!$path) {
            return false;
        }
        $path = trailingslashit('/' . trim($path, '/'));
        foreach ((array) $paths as $candidate) {
            if ($path === trailingslashit('/' . trim((string) $candidate, '/'))) {
                return true;
            }
        }
        return false;
    }

    private function carry_query_to_url($original_url, $base_url) {
        $query = wp_parse_url((string) $original_url, PHP_URL_QUERY);
        if (!$query) {
            return $base_url;
        }
        parse_str($query, $args);
        if (empty($args)) {
            return $base_url;
        }
        return add_query_arg(array_map('sanitize_text_field', $args), $base_url);
    }

    private function is_current_item($url) {
        $request_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        if ($request_uri === '') {
            return false;
        }
        $target_path = wp_parse_url((string) $url, PHP_URL_PATH);
        $target_query = wp_parse_url((string) $url, PHP_URL_QUERY);
        $current_path = wp_parse_url($request_uri, PHP_URL_PATH);
        $current_query = wp_parse_url($request_uri, PHP_URL_QUERY);
        if (!$target_path || !$current_path) {
            return false;
        }
        if (untrailingslashit($current_path) !== untrailingslashit($target_path)) {
            return false;
        }
        if ($target_query === null || $target_query === '') {
            return true;
        }
        $needle = rtrim((string) $target_query, '=');
        return $needle !== '' && strpos((string) $current_query, $needle) !== false;
    }

    private function icon_token($icon, $label) {
        $raw = trim(wp_strip_all_tags((string) $icon . ' ' . (string) $label));
        $text = function_exists('mb_strtolower') ? mb_strtolower($raw, 'UTF-8') : strtolower($raw);

        if (strpos($text, 'home') !== false || strpos($text, 'ฟีด') !== false || strpos($text, '🏠') !== false) { return 'home'; }
        if (strpos($text, 'post') !== false || strpos($text, 'โพสต์') !== false || strpos($text, 'เขียน') !== false || strpos($text, '✍') !== false) { return 'edit'; }
        if (strpos($text, 'member') !== false || strpos($text, 'สมาชิก') !== false || strpos($text, '👥') !== false) { return 'users'; }
        if (strpos($text, 'notification') !== false || strpos($text, 'แจ้ง') !== false || strpos($text, '🔔') !== false) { return 'bell'; }
        if (strpos($text, 'search') !== false || strpos($text, 'ค้น') !== false || strpos($text, '🔎') !== false || strpos($text, '🔍') !== false) { return 'search'; }
        if (strpos($text, 'tag') !== false || strpos($text, 'category') !== false || strpos($text, 'หมวด') !== false || strpos($text, '🏷') !== false) { return 'tag'; }
        if (strpos($text, 'save') !== false || strpos($text, 'บันทึก') !== false || strpos($text, '🔖') !== false) { return 'bookmark'; }
        if (strpos($text, 'popular') !== false || strpos($text, 'ยอดนิยม') !== false || strpos($text, '⭐') !== false) { return 'star'; }
        if (strpos($text, 'clock') !== false || strpos($text, 'latest') !== false || strpos($text, 'ล่าสุด') !== false || strpos($text, 'เวลา') !== false) { return 'clock'; }
        if (strpos($text, 'folder') !== false || strpos($text, 'project') !== false || strpos($text, 'โปรเจกต์') !== false || strpos($text, 'แฟ้ม') !== false) { return 'folder'; }
        if (strpos($text, 'spark') !== false || strpos($text, 'ติดตาม') !== false || strpos($text, '✨') !== false) { return 'spark'; }
        return 'fallback';
    }

    private function render_icon($icon, $label) {
        $token = $this->icon_token($icon, $label);
        $icons = [
            'home' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 10.8 12 3.5l9 7.3"/><path d="M5.2 10.4v9.1h5v-5.2h3.6v5.2h5v-9.1"/></svg>',
            'edit' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4.5 19.5h4.1L19 9.1a2.1 2.1 0 0 0 0-3l-1.1-1.1a2.1 2.1 0 0 0-3 0L4.5 15.4v4.1Z"/><path d="m13.8 6.1 4.1 4.1"/></svg>',
            'users' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9.5 11.2a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/><path d="M3.5 19.7c.7-3.3 3-5.2 6-5.2s5.3 1.9 6 5.2"/><path d="M16.5 10.9a2.8 2.8 0 1 0-1.2-5.3"/><path d="M17.2 14.5c1.9.4 3.2 1.9 3.7 4.3"/></svg>',
            'bell' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6.7 10.2a5.3 5.3 0 0 1 10.6 0c0 4.2 1.7 5.2 2.1 6.1H4.6c.4-.9 2.1-1.9 2.1-6.1Z"/><path d="M9.8 18.5a2.3 2.3 0 0 0 4.4 0"/></svg>',
            'search' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.4"/><path d="m16.1 16.1 4.4 4.4"/></svg>',
            'tag' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4.8 13.3 10.9 19.4a2.2 2.2 0 0 0 3.1 0l5.9-5.9a2.2 2.2 0 0 0 .6-1.5V5.8a2.2 2.2 0 0 0-2.2-2.2h-6.2a2.2 2.2 0 0 0-1.5.6l-5.8 5.9a2.2 2.2 0 0 0 0 3.2Z"/><circle cx="16.3" cy="7.8" r="1"/></svg>',
            'bookmark' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6.7 4.5h10.6a1.3 1.3 0 0 1 1.3 1.3v14.1l-6.6-3.6-6.6 3.6V5.8a1.3 1.3 0 0 1 1.3-1.3Z"/></svg>',
            'star' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m12 3.7 2.5 5 5.5.8-4 3.9.9 5.5-4.9-2.6-4.9 2.6.9-5.5-4-3.9 5.5-.8 2.5-5Z"/></svg>',
            'clock' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="8.2"/><path d="M12 7.7v4.7l3.2 1.9"/></svg>',
            'folder' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4.5 6.8h5.2l1.7 2h8.1v8.8a1.9 1.9 0 0 1-1.9 1.9H6.4a1.9 1.9 0 0 1-1.9-1.9V6.8Z"/><path d="M4.5 9.1h15"/></svg>',
            'spark' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3.8l1.5 4.6 4.7 1.6-4.7 1.6L12 16.2l-1.5-4.6L5.8 10l4.7-1.6L12 3.8Z"/><path d="M18.5 14.2l.7 2.1 2.1.7-2.1.7-.7 2.1-.7-2.1-2.1-.7 2.1-.7.7-2.1Z"/></svg>',
        ];
        if (isset($icons[$token])) {
            return $icons[$token];
        }
        $fallback = trim((string) $icon);
        return '<span class="tcb4d-cs-fallback-icon">' . esc_html($fallback !== '' ? $fallback : '•') . '</span>';
    }

    private function should_render() {
        $settings = $this->settings();
        if (empty($settings['enabled']) || is_admin() || wp_doing_ajax()) {
            return false;
        }

        $mode = $settings['display_mode'];
        if ($mode === 'manual_shortcode') {
            return false;
        }
        if ($mode === 'all_site') {
            return true;
        }
        if ($mode === 'all_except_home') {
            return !is_front_page() && !is_home();
        }
        if ($mode === 'selected_pages') {
            return is_page($settings['page_ids']);
        }

        return $this->matches_community_url($settings['url_keywords']);
    }

    private function matches_community_url($keywords) {
        $request_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        if ($request_uri === '') {
            return false;
        }
        $lines = preg_split('/\R/u', (string) $keywords);
        foreach ((array) $lines as $line) {
            $line = trim($line);
            if ($line !== '' && stripos($request_uri, $line) !== false) {
                return true;
            }
        }
        return false;
    }

    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $settings = $this->settings();
        $pages = get_pages(['sort_column' => 'post_title', 'sort_order' => 'ASC']);
        ?>
        <div class="wrap">
            <h1>Thinkb4do Community Desktop Left Sidebar</h1>
            <p><strong>Native Blend v1.13.0:</strong> Sidebar แบบโปร่งใส วางใต้ส่วนหัวและแถบเขียว เมนูหลักเหลือ ฟีดชุมชน / สมาชิก, Recents ดึงตาม user ID เท่านั้น, สกรอลล์หลังหัวข้อที่ 10 และส่วนล่างเป็นนโยบายพร้อมลิงก์สำคัญของเว็บ</p>
            <form method="post" action="options.php">
                <?php settings_fields(self::OPTION_KEY . '_group'); ?>
                <table class="form-table" role="presentation">
                    <tr><th scope="row">เปิดใช้งาน</th><td><label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[enabled]" value="1" <?php checked($settings['enabled'], 1); ?>> เปิด Sidebar</label></td></tr>
                    <tr><th scope="row">โหมดแสดงผล</th><td>
                        <select name="<?php echo esc_attr(self::OPTION_KEY); ?>[display_mode]">
                            <option value="community_auto" <?php selected($settings['display_mode'], 'community_auto'); ?>>Community Auto จาก URL keywords</option>
                            <option value="selected_pages" <?php selected($settings['display_mode'], 'selected_pages'); ?>>เลือกเฉพาะหน้า</option>
                            <option value="all_site" <?php selected($settings['display_mode'], 'all_site'); ?>>ทั้งเว็บ</option>
                            <option value="all_except_home" <?php selected($settings['display_mode'], 'all_except_home'); ?>>ทั้งเว็บ ยกเว้นหน้าแรก</option>
                            <option value="manual_shortcode" <?php selected($settings['display_mode'], 'manual_shortcode'); ?>>Shortcode เท่านั้น</option>
                        </select>
                    </td></tr>
                    <tr><th scope="row">เลือกหน้า</th><td>
                        <select multiple size="8" style="min-width:320px" name="<?php echo esc_attr(self::OPTION_KEY); ?>[page_ids][]">
                            <?php foreach ($pages as $page): ?>
                                <option value="<?php echo esc_attr($page->ID); ?>" <?php selected(in_array((int) $page->ID, array_map('intval', $settings['page_ids']), true)); ?>><?php echo esc_html($page->post_title); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">ใช้เมื่อเลือกโหมด “เลือกเฉพาะหน้า”</p>
                    </td></tr>
                    <tr><th scope="row">URL keywords</th><td><textarea name="<?php echo esc_attr(self::OPTION_KEY); ?>[url_keywords]" rows="5" cols="52"><?php echo esc_textarea($settings['url_keywords']); ?></textarea><p class="description">หนึ่งบรรทัดต่อหนึ่ง keyword เช่น /community/ หรือ /ชุมชน/</p></td></tr>
                    <tr><th scope="row">ชื่อระบบ</th><td><input class="regular-text" type="text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[title]" value="<?php echo esc_attr($settings['title']); ?>"><p class="description">ใช้เป็น aria-label เท่านั้น ไม่แสดงหัว Sidebar หน้าเว็บ</p></td></tr>
                    <tr><th scope="row">คำอธิบาย</th><td><input class="regular-text" type="text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[subtitle]" value="<?php echo esc_attr($settings['subtitle']); ?>"></td></tr>
                    <tr><th scope="row">เมนูหลัก</th><td><textarea name="<?php echo esc_attr(self::OPTION_KEY); ?>[menu_items]" rows="7" cols="70"><?php echo esc_textarea($settings['menu_items']); ?></textarea><p class="description">รูปแบบ: ชื่อเมนู|URL|icon token เช่น home, users หรือใช้ auto:community, auto:members เพื่อเชื่อมหน้าจริงอัตโนมัติ</p></td></tr>
                    <tr><th scope="row">หัวข้อจากโพสต์</th><td><label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[topics_enabled]" value="1" <?php checked($settings['topics_enabled'], 1); ?>> เปิดหัวข้อที่ต่อจากเมนูหลัก</label></td></tr>
                    <tr><th scope="row">แหล่งหัวข้อ</th><td>
                        <select name="<?php echo esc_attr(self::OPTION_KEY); ?>[topics_source]">
                            <option value="user_posts" <?php selected($settings['topics_source'], 'user_posts'); ?>>ดึงจากโพสต์ผู้ใช้เป็น Recents</option>
                            <option value="manual_items" <?php selected($settings['topics_source'], 'manual_items'); ?>>กำหนดเอง</option>
                        </select>
                        <p class="description">ดึงเฉพาะโพสต์ที่ผูกกับ user ID ของสมาชิกที่ล็อกอินเท่านั้น; AiRA จะตั้งหัวข้อ Recents จากโพสต์ และสามารถแก้ชื่อ/ซ่อนในกล่อง AiRA Recents ของหน้าแก้ไขโพสต์</p>
                    </td></tr>
                    <tr><th scope="row">ชื่อหัวข้อ</th><td><input class="regular-text" type="text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[topics_title]" value="<?php echo esc_attr($settings['topics_title']); ?>"><p class="description">ค่าเริ่มต้น: Recents</p></td></tr>
                    <tr><th scope="row">Post types สำหรับหัวข้อ</th><td><textarea name="<?php echo esc_attr(self::OPTION_KEY); ?>[topic_post_types]" rows="5" cols="52"><?php echo esc_textarea($settings['topic_post_types']); ?></textarea><p class="description">หนึ่งบรรทัดต่อหนึ่ง post type ระบบจะใช้เฉพาะ post type ที่มีอยู่จริง</p></td></tr>
                    <tr><th scope="row">จำนวนหัวข้อที่ดึง</th><td><input type="number" min="3" max="30" name="<?php echo esc_attr(self::OPTION_KEY); ?>[topics_limit]" value="<?php echo esc_attr($settings['topics_limit']); ?>"> รายการ<p class="description">หน้าเว็บจะแสดงประมาณ 10 หัวข้อก่อน แล้วหัวข้อที่เกินจะเลื่อนด้วยสกรอลล์ใน Recents ไม่ดันลงไปทับนโยบาย</p></td></tr>
                    <tr><th scope="row">แจ้งเตือนการสนทนา</th><td><label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[conversation_badge]" value="1" <?php checked($settings['conversation_badge'], 1); ?>> แสดงจำนวนคอมเมนต์/การสนทนาเป็น Badge ด้านขวาของหัวข้อ</label></td></tr>
                    <tr><th scope="row">จัดการ Recents</th><td><label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[recents_actions]" value="1" <?php checked($settings['recents_actions'], 1); ?>> แสดงปุ่มแก้ไข/ลบแบบเล็กเมื่อชี้หัวข้อ</label><p class="description">แก้ไขจะไปหน้าโพสต์จริง และลบจะย้ายโพสต์เข้าถังขยะ หัวข้อจึงหายตามโพสต์</p></td></tr>
                    <tr><th scope="row">รายการหัวข้อสำรอง</th><td><textarea name="<?php echo esc_attr(self::OPTION_KEY); ?>[topic_items]" rows="7" cols="70"><?php echo esc_textarea($settings['topic_items']); ?></textarea><p class="description">ใช้เมื่อเลือก “กำหนดเอง” หรือเมื่อยังไม่มีโพสต์; รูปแบบ: ชื่อหัวข้อ|URL|icon token|เลขแจ้งเตือน เช่น หัวข้อ A|/community/a/|folder|3</p></td></tr>
                    <tr><th scope="row">ส่วนล่างนโยบาย</th><td><label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[lower_info_enabled]" value="1" <?php checked($settings['lower_info_enabled'], 1); ?>> เปิดส่วนล่างของ Sidebar</label><p class="description">ส่วนนี้จะอยู่ล่างสุดของ Sidebar แบบโปร่งใส ไม่มีกล่องพื้นหลัง</p></td></tr>
                    <tr><th scope="row">หัวข้อส่วนล่าง</th><td><input class="regular-text" type="text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[lower_info_title]" value="<?php echo esc_attr($settings['lower_info_title']); ?>"></td></tr>
                    <tr><th scope="row">ข้อความนโยบายส่วนล่าง</th><td><textarea name="<?php echo esc_attr(self::OPTION_KEY); ?>[lower_info_text]" rows="3" cols="70"><?php echo esc_textarea($settings['lower_info_text']); ?></textarea><p class="description">ข้อความสั้น ๆ เกี่ยวกับนโยบายหรือแนวทางการใช้งานชุมชน</p></td></tr>
                    <tr><th scope="row">ลิงก์ส่วนล่าง</th><td><textarea name="<?php echo esc_attr(self::OPTION_KEY); ?>[lower_policy_links]" rows="4" cols="70"><?php echo esc_textarea($settings['lower_policy_links']); ?></textarea><p class="description">ค่าเริ่มต้นใช้เฉพาะลิงก์สำคัญของเว็บ: นโยบาย / เงื่อนไข / ติดต่อ รูปแบบ: ชื่อลิงก์|URL หนึ่งรายการต่อหนึ่งบรรทัด เช่น นโยบาย|/policy/</p></td></tr>
                    <tr><th scope="row">Open/Close bar</th><td><label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[open_close_enabled]" value="1" <?php checked($settings['open_close_enabled'], 1); ?>> เปิดปุ่มพับ/เปิด Sidebar แบบมาตรฐาน</label><br><label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[default_collapsed]" value="1" <?php checked($settings['default_collapsed'], 1); ?>> เริ่มต้นแบบพับไว้ก่อน</label></td></tr>
                    <tr><th scope="row">Desktop เท่านั้น</th><td><input type="number" min="900" max="1800" name="<?php echo esc_attr(self::OPTION_KEY); ?>[desktop_min_width]" value="<?php echo esc_attr($settings['desktop_min_width']); ?>"> px ขึ้นไป</td></tr>
                    <tr><th scope="row">ความกว้าง</th><td><input type="number" min="190" max="232" name="<?php echo esc_attr(self::OPTION_KEY); ?>[width]" value="<?php echo esc_attr($settings['width']); ?>"> px</td></tr>
                    <tr><th scope="row">ระยะบน</th><td><input type="number" min="126" max="260" name="<?php echo esc_attr(self::OPTION_KEY); ?>[top_offset]" value="<?php echo esc_attr($settings['top_offset']); ?>"> px <p class="description">ล็อกขั้นต่ำ 126px เพื่อให้อยู่ใต้ส่วนหัวและแถบเขียว</p></td></tr>
                    <tr><th scope="row">ระยะจากขอบซ้าย</th><td><input type="number" min="36" max="72" name="<?php echo esc_attr(self::OPTION_KEY); ?>[left_offset]" value="<?php echo esc_attr($settings['left_offset']); ?>"> px</td></tr>
                    <tr><th scope="row">Z-index</th><td><input type="number" min="1" max="999999" name="<?php echo esc_attr(self::OPTION_KEY); ?>[z_index]" value="<?php echo esc_attr($settings['z_index']); ?>"></td></tr>
                    <tr><th scope="row">เว้นพื้นที่เนื้อหา</th><td><label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[reserve_space]" value="1" <?php checked($settings['reserve_space'], 1); ?>> เว้นพื้นที่ด้านซ้ายให้เนื้อหา ถ้า Sidebar ทับ Layout</label></td></tr>
                    <tr><th scope="row">Theme</th><td>
                        <select name="<?php echo esc_attr(self::OPTION_KEY); ?>[theme_style]">
                            <option value="native_blend" <?php selected($settings['theme_style'], 'native_blend'); ?>>Native Blend ตามภาพ</option>
                            <option value="soft_ai" <?php selected($settings['theme_style'], 'soft_ai'); ?>>Soft AI</option>
                            <option value="minimal" <?php selected($settings['theme_style'], 'minimal'); ?>>Minimal</option>
                            <option value="solid" <?php selected($settings['theme_style'], 'solid'); ?>>Solid</option>
                        </select>
                    </td></tr>
                    <tr><th scope="row">Shortcode</th><td><code>[<?php echo esc_html(self::SHORTCODE); ?>]</code></td></tr>
                </table>
                <?php submit_button('บันทึกการตั้งค่า'); ?>
            </form>
            <hr>
            <h2>สถานะระบบ</h2>
            <p><strong>ตำแหน่ง:</strong> อยู่ใต้ส่วนหัว + แถบเขียว</p>
            <p><strong>พื้นหลัง:</strong> โปร่งใส กลืนกับสภาพแวดล้อมเว็บ</p>
            <p><strong>Recents:</strong> AiRA ตั้งหัวข้อจากโพสต์ผู้ใช้ เชื่อมไปยังโพสต์จริง แก้ชื่อ/ซ่อนจากกล่อง AiRA Recents ในหน้าแก้ไขโพสต์ และหายตามเมื่อโพสต์ถูกลบ/ย้ายถังขยะ</p><p><strong>ส่วนล่าง:</strong> แสดงนโยบายพร้อมลิงก์สำคัญของเว็บ เช่น นโยบาย / เงื่อนไข / ติดต่อ แบบโปร่งใส ไม่มีกล่องพื้นหลัง และแก้ข้อความ/ลิงก์ได้จากหลังบ้าน</p>
            <p><strong>Open/Close bar:</strong> ปรับเป็นไอคอนโปร่งใส กลมกลืนกับเมนู พร้อมจำสถานะล่าสุดด้วย localStorage</p>
            <p><strong>หัว/ท้าย Sidebar:</strong> ซ่อนแล้ว ไม่มีโลโก้ T + ไม่มีแถว Settings ด้านล่างบนหน้าเว็บ</p>
            <p><strong>Settings | โดย Thinkb4do | ดูรายละเอียด:</strong> แสดงเฉพาะในหน้าตั้งค่าหลังบ้าน ไม่แสดงใน Sidebar หน้าเว็บ</p>
        </div>
        <?php
    }
}

register_activation_hook(__FILE__, ['ThinkB4Do_Community_Left_Sidebar_Desktop', 'activate']);
add_action('plugins_loaded', ['ThinkB4Do_Community_Left_Sidebar_Desktop', 'instance']);
