<?php
/**
 * Plugin Name: Thinkb4do Community Desktop Right Sidebar
 * Description: Sidebar ขวาสำหรับหน้า Community พร้อมได้รับการสนับสนุน กระแสโหวตแบบดึงโพสต์ยอดนิยมจากฟีด และ #ยอดนิยมจากโพสต์จริง
 * Version: 4.9.8
 * Author: Thinkb4do
 * Text Domain: thinkb4do-community-right-sidebar-desktop
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

final class ThinkB4Do_Community_Right_Sidebar_Desktop {
    private const OPTION_KEY = 'tcb4d_community_right_sidebar_settings';
    private const VERSION = '4.9.8';
    private const SHORTCODE = 'thinkb4do_community_right_sidebar';
    private const ADMIN_SLUG = 'thinkb4do-community-right-sidebar';
    private const FRONT_ID = 'tcb4d-community-right-sidebar';
    private const AD_PAGE_SLUG = 'advertise';
    private const AJAX_ACTION = 'tcb4d_cs_feed_summary';

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
        add_action('init', [$this, 'ensure_advertise_page'], 20);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets'], 20);
        add_action('wp_footer', [$this, 'render_sidebar_from_footer'], 18);
        add_filter('the_content', [$this, 'maybe_advertise_content'], 20);
        add_shortcode(self::SHORTCODE, [$this, 'shortcode_sidebar']);
        add_shortcode('thinkb4do_advertise_contact', [$this, 'advertise_contact_shortcode']);
        add_action('wp_ajax_' . self::AJAX_ACTION, [$this, 'ajax_summary']);
        add_action('wp_ajax_nopriv_' . self::AJAX_ACTION, [$this, 'ajax_summary']);
    }

    public static function activate() {
        $saved = get_option(self::OPTION_KEY);
        $defaults = self::defaults();
        if (!is_array($saved)) {
            add_option(self::OPTION_KEY, $defaults, '', false);
        } else {
            update_option(self::OPTION_KEY, wp_parse_args($saved, $defaults), false);
        }
    }

    public static function defaults() {
        return [
            'enabled' => 1,
            'display_mode' => 'community_only',
            'page_ids' => [],
            'url_keywords' => "/community/\n/chumchon/\n/ชุมชน/",
            'title' => 'พื้นที่ชุมชน',
            'sponsor_title' => 'ได้รับการสนับสนุน',
            'sponsor_more_label' => 'ดูทั้งหมด',
            'sponsor_1_title' => 'พื้นที่แนะนำสำหรับแบรนด์',
            'sponsor_1_description' => 'แนะนำสินค้า บริการ หรือแคมเปญที่เหมาะกับชุมชน',
            'sponsor_1_url' => '/advertise/',
            'sponsor_1_image' => '',
            'sponsor_1_start' => '',
            'sponsor_1_end' => '',
            'sponsor_2_title' => 'ร่วมสนับสนุนชุมชน',
            'sponsor_2_description' => 'เปิดพื้นที่ให้ผู้สนับสนุนสื่อสารอย่างเหมาะสม',
            'sponsor_2_url' => '/advertise/',
            'sponsor_2_image' => '',
            'sponsor_2_start' => '',
            'sponsor_2_end' => '',
            'sponsor_3_title' => '',
            'sponsor_3_description' => '',
            'sponsor_3_url' => '/advertise/',
            'sponsor_3_image' => '',
            'sponsor_3_start' => '',
            'sponsor_3_end' => '',
            'sponsor_4_title' => '',
            'sponsor_4_description' => '',
            'sponsor_4_url' => '/advertise/',
            'sponsor_4_image' => '',
            'sponsor_4_start' => '',
            'sponsor_4_end' => '',
            'sponsor_5_title' => '',
            'sponsor_5_description' => '',
            'sponsor_5_url' => '/advertise/',
            'sponsor_5_image' => '',
            'sponsor_5_start' => '',
            'sponsor_5_end' => '',
            'sponsor_6_title' => '',
            'sponsor_6_description' => '',
            'sponsor_6_url' => '/advertise/',
            'sponsor_6_image' => '',
            'sponsor_6_start' => '',
            'sponsor_6_end' => '',
            'vote_title' => 'กระแสโหวต',
            'vote_subtitle' => 'จากโพสต์โหวตยอดนิยม',
            'trending_title' => '#ยอดนิยม',
            'trending_subtitle' => 'จากผู้โพสต์',
            'scan_limit' => 20000,
            'desktop_min_width' => 1025,
            'width' => 216,
            'top_offset' => 126,
            'right_offset' => 46,
            'z_index' => 68,
            'reserve_space' => 0,
        ];
    }

    private function settings() {
        $saved = get_option(self::OPTION_KEY, []);
        $settings = wp_parse_args(is_array($saved) ? $saved : [], self::defaults());
        $settings['enabled'] = !empty($settings['enabled']) ? 1 : 0;
        $settings['display_mode'] = in_array($settings['display_mode'], ['community_only', 'selected_pages'], true) ? $settings['display_mode'] : 'community_only';
        $settings['page_ids'] = is_array($settings['page_ids']) ? array_values(array_filter(array_map('absint', $settings['page_ids']))) : [];
        $settings['sponsor_title'] = $settings['sponsor_title'] !== '' ? $settings['sponsor_title'] : 'ได้รับการสนับสนุน';
        if (in_array(trim((string) $settings['sponsor_title']), ['ตัวอย่างโฆษณา', 'โฆษณา'], true)) {
            $settings['sponsor_title'] = 'ได้รับการสนับสนุน';
        }
        $settings['vote_title'] = $settings['vote_title'] !== '' ? $settings['vote_title'] : 'กระแสโหวต';
        if (in_array(trim((string) $settings['vote_title']), ['กระแสโหวด', 'กระแวโหวด', 'กระแสโหวตยอดนิยม'], true)) {
            $settings['vote_title'] = 'กระแสโหวต';
        }
        $settings['vote_subtitle'] = $settings['vote_subtitle'] !== '' ? $settings['vote_subtitle'] : 'จากโพสต์โหวตยอดนิยม';
        if (in_array(trim((string) $settings['vote_subtitle']), ['คะแนนรวม', 'คะแนนโหวต', 'เฉพาะโพสต์โหวต', 'โพสต์เกี่ยวกับโหวต', 'จาก Poll', 'จากโพสต์ Poll ในฟีด'], true)) {
            $settings['vote_subtitle'] = 'จากโพสต์โหวตยอดนิยม';
        }
        $settings['trending_title'] = $settings['trending_title'] !== '' ? $settings['trending_title'] : '#ยอดนิยม';
        $settings['desktop_min_width'] = min(1800, max(900, absint($settings['desktop_min_width'])));
        $settings['width'] = min(240, max(190, absint($settings['width'])));
        $settings['top_offset'] = min(280, max(90, absint($settings['top_offset'])));
        $settings['right_offset'] = min(120, max(12, absint($settings['right_offset'])));
        $settings['z_index'] = min(999999, max(1, absint($settings['z_index'])));
        $settings['scan_limit'] = min(20000, max(50, absint($settings['scan_limit'])));
        return $settings;
    }

    public function register_settings() {
        register_setting(self::OPTION_KEY . '_group', self::OPTION_KEY, [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_settings'],
            'default' => self::defaults(),
        ]);
    }

    public function sanitize_settings($input) {
        $input = is_array($input) ? $input : [];
        $d = self::defaults();
        $clean = [];
        $clean['enabled'] = !empty($input['enabled']) ? 1 : 0;
        $clean['display_mode'] = in_array(($input['display_mode'] ?? ''), ['community_only', 'selected_pages'], true) ? $input['display_mode'] : 'community_only';
        $clean['page_ids'] = isset($input['page_ids']) && is_array($input['page_ids']) ? array_values(array_filter(array_map('absint', $input['page_ids']))) : [];
        $clean['url_keywords'] = $this->sanitize_multiline($input['url_keywords'] ?? $d['url_keywords']);
        $clean['title'] = sanitize_text_field($input['title'] ?? $d['title']);
        $clean['sponsor_title'] = sanitize_text_field($input['sponsor_title'] ?? $d['sponsor_title']);
        $clean['sponsor_more_label'] = sanitize_text_field($input['sponsor_more_label'] ?? $d['sponsor_more_label']);
        for ($i = 1; $i <= 6; $i++) {
            $clean["sponsor_{$i}_title"] = sanitize_text_field($input["sponsor_{$i}_title"] ?? $d["sponsor_{$i}_title"]);
            $clean["sponsor_{$i}_description"] = sanitize_text_field($input["sponsor_{$i}_description"] ?? $d["sponsor_{$i}_description"]);
            $clean["sponsor_{$i}_url"] = $this->sanitize_url_or_path($input["sponsor_{$i}_url"] ?? $d["sponsor_{$i}_url"]);
            $clean["sponsor_{$i}_image"] = esc_url_raw($input["sponsor_{$i}_image"] ?? $d["sponsor_{$i}_image"]);
            $clean["sponsor_{$i}_start"] = $this->sanitize_datetime($input["sponsor_{$i}_start"] ?? '');
            $clean["sponsor_{$i}_end"] = $this->sanitize_datetime($input["sponsor_{$i}_end"] ?? '');
        }
        $clean['vote_title'] = sanitize_text_field($input['vote_title'] ?? $d['vote_title']);
        $clean['vote_subtitle'] = sanitize_text_field($input['vote_subtitle'] ?? $d['vote_subtitle']);
        $clean['trending_title'] = sanitize_text_field($input['trending_title'] ?? $d['trending_title']);
        $clean['trending_subtitle'] = sanitize_text_field($input['trending_subtitle'] ?? $d['trending_subtitle']);
        $clean['scan_limit'] = $this->bounded_int($input['scan_limit'] ?? $d['scan_limit'], 50, 20000, $d['scan_limit']);
        $clean['desktop_min_width'] = $this->bounded_int($input['desktop_min_width'] ?? $d['desktop_min_width'], 900, 1800, $d['desktop_min_width']);
        $clean['width'] = $this->bounded_int($input['width'] ?? $d['width'], 190, 240, $d['width']);
        $clean['top_offset'] = $this->bounded_int($input['top_offset'] ?? $d['top_offset'], 90, 280, $d['top_offset']);
        $clean['right_offset'] = $this->bounded_int($input['right_offset'] ?? $d['right_offset'], 12, 120, $d['right_offset']);
        $clean['z_index'] = $this->bounded_int($input['z_index'] ?? $d['z_index'], 1, 999999, $d['z_index']);
        $clean['reserve_space'] = !empty($input['reserve_space']) ? 1 : 0;
        return $clean;
    }

    private function bounded_int($value, $min, $max, $default) {
        $value = absint($value);
        if ($value <= 0) {
            $value = (int) $default;
        }
        return min((int) $max, max((int) $min, $value));
    }

    private function sanitize_multiline($value) {
        $lines = preg_split('/\R/u', wp_unslash((string) $value));
        $clean = [];
        foreach ((array) $lines as $line) {
            $line = trim(sanitize_text_field($line));
            if ($line !== '') {
                $clean[] = $line;
            }
        }
        return implode("\n", array_unique($clean));
    }

    private function sanitize_url_or_path($url) {
        $url = trim((string) wp_unslash($url));
        if ($url === '') {
            return '/advertise/';
        }
        if (strpos($url, '/') === 0 && strpos($url, '//') !== 0) {
            return esc_url_raw($url);
        }
        return esc_url_raw($url);
    }

    private function sanitize_datetime($value) {
        $value = trim((string) wp_unslash($value));
        return preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $value) ? $value : '';
    }

    public function add_admin_page() {
        add_options_page(
            'Community Right Sidebar',
            'Community Right Sidebar',
            'manage_options',
            self::ADMIN_SLUG,
            [$this, 'render_admin_page']
        );
    }

    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $s = $this->settings();
        $pages = get_pages(['sort_column' => 'post_title', 'sort_order' => 'ASC']);
        ?>
        <div class="wrap">
            <h1>Community Right Sidebar</h1>
            <form method="post" action="options.php">
                <?php settings_fields(self::OPTION_KEY . '_group'); ?>
                <table class="form-table" role="presentation">
                    <tr><th scope="row">เปิดใช้งาน</th><td><label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[enabled]" value="1" <?php checked($s['enabled'], 1); ?>> เปิด Sidebar ขวา</label></td></tr>
                    <tr><th scope="row">หน้าแสดงผล</th><td>
                        <label><input type="radio" name="<?php echo esc_attr(self::OPTION_KEY); ?>[display_mode]" value="community_only" <?php checked($s['display_mode'], 'community_only'); ?>> เฉพาะหน้าหลัก Community</label><br>
                        <label><input type="radio" name="<?php echo esc_attr(self::OPTION_KEY); ?>[display_mode]" value="selected_pages" <?php checked($s['display_mode'], 'selected_pages'); ?>> เลือกหน้าเอง</label>
                        <p><select name="<?php echo esc_attr(self::OPTION_KEY); ?>[page_ids][]" multiple size="8" style="min-width:320px;max-width:100%;">
                            <?php foreach ($pages as $page): ?>
                                <option value="<?php echo esc_attr((string) $page->ID); ?>" <?php selected(in_array((int) $page->ID, $s['page_ids'], true)); ?>><?php echo esc_html($page->post_title . ' — /' . $page->post_name . '/'); ?></option>
                            <?php endforeach; ?>
                        </select></p>
                        <p class="description">ถ้ายังไม่เลือกหน้าเอง ระบบจะแสดงเฉพาะหน้าหลัก Community</p>
                    </td></tr>
                    <tr><th scope="row">คำที่ใช้จับหน้าหลัก</th><td><textarea name="<?php echo esc_attr(self::OPTION_KEY); ?>[url_keywords]" rows="4" cols="42"><?php echo esc_textarea($s['url_keywords']); ?></textarea></td></tr>
                    <tr><th scope="row">ตำแหน่ง Desktop</th><td>
                        กว้าง <input type="number" name="<?php echo esc_attr(self::OPTION_KEY); ?>[width]" value="<?php echo esc_attr((string) $s['width']); ?>" min="190" max="240" style="width:90px;"> px
                        บน <input type="number" name="<?php echo esc_attr(self::OPTION_KEY); ?>[top_offset]" value="<?php echo esc_attr((string) $s['top_offset']); ?>" min="90" max="280" style="width:90px;"> px
                        ขวา <input type="number" name="<?php echo esc_attr(self::OPTION_KEY); ?>[right_offset]" value="<?php echo esc_attr((string) $s['right_offset']); ?>" min="12" max="120" style="width:90px;"> px
                        <label style="margin-left:12px;"><input type="checkbox" name="<?php echo esc_attr(self::OPTION_KEY); ?>[reserve_space]" value="1" <?php checked(!empty($s['reserve_space'])); ?>> กันพื้นที่ด้านขวา</label>
                    </td></tr>
                    <tr><th scope="row">หัวข้อ</th><td>
                        <p>โหวต <input type="text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[vote_title]" value="<?php echo esc_attr($s['vote_title']); ?>" class="regular-text"> <input type="text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[vote_subtitle]" value="<?php echo esc_attr($s['vote_subtitle']); ?>"></p>
                        <p>ยอดนิยม <input type="text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[trending_title]" value="<?php echo esc_attr($s['trending_title']); ?>" class="regular-text"> <input type="text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[trending_subtitle]" value="<?php echo esc_attr($s['trending_subtitle']); ?>"></p>
                        <p>จำนวนโพสต์ที่อ่าน <input type="number" name="<?php echo esc_attr(self::OPTION_KEY); ?>[scan_limit]" value="<?php echo esc_attr((string) $s['scan_limit']); ?>" min="50" max="5000" style="width:100px;"></p>
                    </td></tr>
                    <tr><th scope="row">ได้รับการสนับสนุน</th><td>
                        <p>หัวข้อ <input type="text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[sponsor_title]" value="<?php echo esc_attr($s['sponsor_title']); ?>" class="regular-text"> ลิงก์รวม <input type="text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[sponsor_more_label]" value="<?php echo esc_attr($s['sponsor_more_label']); ?>"></p>
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                            <div style="padding:12px;margin:10px 0;border:1px solid #dcdcde;border-radius:10px;background:#fff;max-width:920px;">
                                <strong>รายการ <?php echo esc_html((string) $i); ?></strong>
                                <p>ชื่อ <input type="text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[sponsor_<?php echo esc_attr((string) $i); ?>_title]" value="<?php echo esc_attr($s["sponsor_{$i}_title"]); ?>" class="regular-text"></p>
                                <p>คำอธิบาย <input type="text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[sponsor_<?php echo esc_attr((string) $i); ?>_description]" value="<?php echo esc_attr($s["sponsor_{$i}_description"]); ?>" class="large-text"></p>
                                <p>ลิงก์ <input type="text" name="<?php echo esc_attr(self::OPTION_KEY); ?>[sponsor_<?php echo esc_attr((string) $i); ?>_url]" value="<?php echo esc_attr($s["sponsor_{$i}_url"]); ?>" class="regular-text"> ภาพ <input type="url" name="<?php echo esc_attr(self::OPTION_KEY); ?>[sponsor_<?php echo esc_attr((string) $i); ?>_image]" value="<?php echo esc_attr($s["sponsor_{$i}_image"]); ?>" class="regular-text"></p>
                                <p>เริ่ม <input type="datetime-local" name="<?php echo esc_attr(self::OPTION_KEY); ?>[sponsor_<?php echo esc_attr((string) $i); ?>_start]" value="<?php echo esc_attr($s["sponsor_{$i}_start"]); ?>"> สิ้นสุด <input type="datetime-local" name="<?php echo esc_attr(self::OPTION_KEY); ?>[sponsor_<?php echo esc_attr((string) $i); ?>_end]" value="<?php echo esc_attr($s["sponsor_{$i}_end"]); ?>"></p>
                            </div>
                        <?php endfor; ?>
                    </td></tr>
                </table>
                <?php submit_button('Save Settings'); ?>
            </form>
        </div>
        <?php
    }

    private function current_url_path() {
        $uri = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '';
        $path = (string) wp_parse_url($uri, PHP_URL_PATH);
        return '/' . trim($path, '/') . '/';
    }

    private function should_render() {
        if (is_admin() || wp_doing_ajax()) {
            return false;
        }
        $s = $this->settings();
        if (empty($s['enabled'])) {
            return false;
        }
        if (function_exists('is_page') && is_page(self::AD_PAGE_SLUG)) {
            return false;
        }
        if ($s['display_mode'] === 'selected_pages') {
            if (empty($s['page_ids'])) {
                return false;
            }
            return is_page($s['page_ids']);
        }
        $path = $this->current_url_path();
        $allowed_paths = [];
        $keywords = preg_split('/\R/u', (string) $s['url_keywords']);
        foreach ((array) $keywords as $keyword) {
            $keyword = trim((string) $keyword);
            if ($keyword === '') {
                continue;
            }
            $allowed_paths[] = '/' . trim($keyword, '/') . '/';
        }
        $allowed_paths = array_unique(array_filter(array_merge($allowed_paths, ['/community/', '/chumchon/', '/ชุมชน/'])));
        if (in_array($path, $allowed_paths, true)) {
            return true;
        }
        return function_exists('is_page') && (is_page('community') || is_page('chumchon') || is_page('ชุมชน'));
    }

    public function enqueue_frontend_assets() {
        if (!$this->should_render()) {
            return;
        }
        $s = $this->settings();
        $style_handle = self::ADMIN_SLUG . '-frontend';
        wp_register_style($style_handle, false, [], self::VERSION);
        wp_enqueue_style($style_handle);
        wp_add_inline_style($style_handle, $this->frontend_css($s));

        $script_handle = self::ADMIN_SLUG . '-frontend-js';
        wp_register_script($script_handle, false, [], self::VERSION, true);
        wp_enqueue_script($script_handle);
        wp_add_inline_script($script_handle, $this->frontend_js($s), 'before');
    }

    private function frontend_css($s) {
        $min = (int) $s['desktop_min_width'];
        $max = $min - 1;
        $width = (int) $s['width'];
        $top = (int) $s['top_offset'];
        $right = (int) $s['right_offset'];
        $z = (int) $s['z_index'];
        $reserve = !empty($s['reserve_space']) ? $width + $right + 20 : 0;
        return "
:root{--tcb4d-cs-width:{$width}px;--tcb4d-cs-top:{$top}px;--tcb4d-cs-right:{$right}px;--tcb4d-cs-z:{$z};}
.tcb4d-community-right-sidebar{position:fixed;top:var(--tcb4d-cs-top);right:var(--tcb4d-cs-right);width:var(--tcb4d-cs-width);z-index:var(--tcb4d-cs-z);font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen-Sans,Ubuntu,Cantarell,'Helvetica Neue',Arial,'Noto Sans Thai','Kanit',sans-serif;background:transparent!important;border:0!important;box-shadow:none!important;color:#111;box-sizing:border-box;}
.tcb4d-community-right-sidebar *{box-sizing:border-box;text-decoration:none!important;text-decoration-line:none!important;border-bottom-color:transparent!important;}
.tcb4d-cs-card{display:flex;flex-direction:column;gap:14px;width:100%;background:transparent!important;border:0!important;box-shadow:none!important;}
.tcb4d-cs-section{width:100%;background:transparent!important;border:0!important;box-shadow:none!important;}
.tcb4d-cs-box{padding:5px 0;border:0!important;border-radius:0;background:transparent!important;box-shadow:none!important;}
.tcb4d-cs-section-title{display:flex;align-items:baseline;justify-content:space-between;gap:8px;margin:0 0 9px;color:#111;font-size:13px;font-weight:900;line-height:1.2;}
.tcb4d-cs-section-title small,.tcb4d-cs-viewall{color:#1E6B45;font-size:10.5px;font-weight:850;line-height:1.1;white-space:nowrap;text-decoration:none;}
.tcb4d-cs-sponsored-list{display:flex;flex-direction:column;gap:10px;margin:0;padding:0;list-style:none;}
.tcb4d-cs-sponsored-item{display:grid;grid-template-columns:58px minmax(0,1fr);gap:10px;align-items:center;min-height:58px;text-decoration:none;color:#111;border-radius:14px;background:transparent!important;transition:transform .16s ease,color .16s ease;}
.tcb4d-cs-sponsored-item:hover,.tcb4d-cs-sponsored-item:focus-visible{transform:translateX(-2px);color:#1E6B45;outline:none;}
.tcb4d-cs-sponsored-thumb{width:58px;height:58px;border-radius:12px;overflow:hidden;background:rgba(30,107,69,.10);display:flex;align-items:center;justify-content:center;color:#1E6B45;font-size:10px;font-weight:900;line-height:1;}
.tcb4d-cs-sponsored-thumb img{display:block;width:100%;height:100%;object-fit:cover;}
.tcb4d-cs-sponsored-name{display:block;margin:0 0 2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#111;font-size:12px;font-weight:900;line-height:1.25;}
.tcb4d-cs-sponsored-text{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;margin:0;color:#475467;font-size:10.8px;font-weight:600;line-height:1.35;}
.tcb4d-cs-sponsored-link{display:inline-flex;margin-top:4px;color:#1E6B45;font-size:10.5px;font-weight:900;line-height:1;}
.tcb4d-cs-ranked{display:flex;flex-direction:column;gap:7px;margin:0;padding:0;list-style:none;}
.tcb4d-cs-rank-row{display:grid;grid-template-columns:22px minmax(0,1fr) auto;align-items:center;gap:8px;min-height:30px;padding:3px 0;border:0!important;}
.tcb4d-cs-vote-row{grid-template-columns:22px minmax(0,1fr) minmax(42px,56px) 34px;gap:6px;align-items:center;}
.tcb4d-cs-rank-badge{display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:999px;background:rgba(249,115,22,.16);color:#9A4B0A;font-size:10px;font-weight:900;}
.tcb4d-cs-vote-left{display:flex;flex-direction:column;min-width:0;gap:2px;}
.tcb4d-cs-vote-link,.tcb4d-cs-tag-link{display:block;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;word-break:normal;color:#111;text-decoration:none;font-size:12px;font-weight:900;line-height:1.22;}
.tcb4d-cs-vote-link:hover,.tcb4d-cs-tag-link:hover{color:#1E6B45;}
.tcb4d-cs-vote-meta{display:block;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#667085;font-size:9.3px;font-weight:850;line-height:1.1;}
.tcb4d-cs-vote-meter{position:relative;display:block;width:100%;height:6px;overflow:hidden;border-radius:999px;background:rgba(30,107,69,.12);}
.tcb4d-cs-vote-meter-fill{position:absolute;left:0;top:0;height:100%;width:var(--tcb4d-vote-pct,0%);min-width:6px;max-width:100%;border-radius:inherit;background:linear-gradient(90deg,#1E6B45 0%,#F97316 100%);box-shadow:0 0 0 1px rgba(255,255,255,.42) inset;transition:width .28s ease;}
.tcb4d-cs-rank-count{display:flex;flex-direction:column;align-items:flex-end;gap:1px;color:#667085;font-size:10px;font-weight:800;line-height:1.05;white-space:nowrap;}
.tcb4d-cs-rank-count strong{display:block;color:#111;font-size:11px;font-weight:950;line-height:1;}
.tcb4d-cs-rank-count small{display:block;color:#1E6B45;font-size:8.8px;font-weight:900;line-height:1;}
.tcb4d-cs-empty{margin:0;padding:8px 2px;color:#667085;font-size:11.5px;font-weight:700;line-height:1.45;}
@media(max-width:{$max}px){.tcb4d-community-right-sidebar{display:none!important;}}
" . ($reserve > 0 ? "@media(min-width:{$min}px){body{padding-right:{$reserve}px!important;}}" : '') . "
";
    }

    private function frontend_js($s) {
        $config = [
            'id' => self::FRONT_ID,
            'minWidth' => (int) $s['desktop_min_width'],
            'commentsUrl' => home_url('/community/#comments'),
            'tagBase' => home_url('/community/'),
            'summary' => $this->collect_summary($s),
        ];
        return 'window.TCB4D_CRS=' . wp_json_encode($config) . ";\n" . <<<'JS'
(function(){
  'use strict';
  var cfg = window.TCB4D_CRS || {};
  var sidebars = Array.prototype.slice.call(document.querySelectorAll('.tcb4d-community-right-sidebar'));
  if(!sidebars.length){return;}
  var timer = null;

  function text(el){return String((el && (el.innerText || el.textContent)) || '').replace(/\u00a0/g,' ').replace(/[ \t]+/g,' ').trim();}
  function raw(el){return String((el && (el.innerText || el.textContent)) || '').replace(/\u00a0/g,' ');}
  function numberFrom(v){var m=String(v||'').replace(/,/g,'').match(/\d{1,7}/);return m?parseInt(m[0],10):0;}
  function visible(el){
    if(!el || el.nodeType!==1){return false;}
    var st = getComputedStyle(el);
    if(!st || st.display==='none' || st.visibility==='hidden' || parseFloat(st.opacity||'1')===0){return false;}
    var r = el.getBoundingClientRect();
    return r.width>12 && r.height>12 && r.bottom>0 && r.top<(innerHeight+6000);
  }
  function hardBlocked(el){
    if(!el){return false;}
    // บล็อกเฉพาะพื้นที่ที่รู้แน่ว่าไม่ใช่ฟีด ไม่บล็อก ancestor กว้าง ๆ อย่าง .menu/.search เพราะบางธีมครอบทั้งหน้าไว้ด้วย class เหล่านี้
    if(el.closest('header,nav,footer,[role="dialog"],.modal,.popup,.composer,.post-composer,.create-post,.tcb4d-composer,.tcb4d-community-right-sidebar,.tcb4d-csbar,.comment-form,.reply-form,[aria-label*="ได้รับการสนับสนุน"],[aria-label*="กระแสโหวต"],[aria-label*="#ยอดนิยม"]')){return true;}
    var selfText = text(el);
    if((el.matches && el.matches('.widget,[class*="sponsor"],[class*="advert"],[id*="menu"]')) && selfText.length < 900){return true;}
    var node = el.closest('aside,[id*="sidebar"],[class*="sidebar"],.widget');
    if(!node || node === document.body || node === document.documentElement){return false;}
    if(node.matches('.tcb4d-community-right-sidebar,.tcb4d-csbar')){return true;}
    var r = node.getBoundingClientRect ? node.getBoundingClientRect() : null;
    return !!(r && r.width > 0 && r.width <= 420 && (r.left < 180 || r.right > innerWidth - 180));
  }
  function inFeedColumn(el){
    if(!visible(el) || hardBlocked(el)){return false;}
    var r = el.getBoundingClientRect();
    var mid = (r.left + r.right) / 2;
    var leftLimit = Math.max(180, innerWidth * 0.18);
    var rightLimit = innerWidth * 0.78;
    return mid >= leftLimit && mid <= rightLimit && r.width >= 140;
  }
  function isComposerText(t){
    return /(เขียนโพสต์ใหม่|คุณกำลังคิดอะไร|สร้างโพสต์|เริ่มโพสต์|write a post|create post|search|ค้นหา)/i.test(t) && t.length < 500;
  }
  function normalizeBlockText(t){return String(t||'').replace(/\s+/g,' ').trim();}
  function isBadWholeText(t){
    return /(ได้รับการสนับสนุน|กระแสโหวต|#ยอดนิยม|ดูเพิ่มเติม|ดูทั้งหมด|ติดต่อโฆษณา|Recents|นโยบาย|เงื่อนไขการใช้งาน)/i.test(t) && t.length < 900;
  }
  function voteTextLooksRelevant(t){
    t = String(t || '');
    if(t.length < 3 || t.length > 6000){return false;}
    return /(?:ประเภท|หัวข้อ|หมวดหมู่)\s*[:：]?\s*(โหวต|โหวด|โพล|poll|vote)|โหวต\s*\/\s*โหวต|โหวด\s*\/\s*โหวด|(?:^|\s)(โหวต|โหวด|โพล|แบบสำรวจ|ลงคะแนน|poll|vote|voting)(?:\s|$)|ตัวเลือก\s*\d+|เลือกได้\s*(?:สูงสุด|มากสุด)?|เห็นด้วย|ไม่เห็นด้วย|data-poll-id|data-vote-id/iu.test(t);
  }
  function cardKey(card){
    var id = card.getAttribute('data-post-id') || card.getAttribute('data-feed-id') || card.getAttribute('data-id') || card.id || '';
    if(id){return 'id:'+id;}
    return 'txt:'+normalizeBlockText(raw(card)).slice(0,220);
  }
  function climbToCard(el){
    var node = el, best = null;
    for(var i=0; node && i<12; i++, node=node.parentElement){
      if(hardBlocked(node) || !inFeedColumn(node)){continue;}
      var t = normalizeBlockText(raw(node));
      var r = node.getBoundingClientRect();
      if(t.length < 12 || isComposerText(t) || isBadWholeText(t)){continue;}
      if(r.width >= 260 && r.height >= 52 && r.height <= 950 && t.length <= 5000){
        best = node;
        if(node.matches('article,[role="article"],[data-post-id],[data-feed-id],[data-community-post],[data-fcom-post],.post-card,.feed-post,.community-post,.fcom-post,.activity-card,.topic-card')){
          return node;
        }
      }
    }
    return best;
  }
  function findFeedCards(){
    var cards = [], seen = Object.create(null);
    function add(el){
      var card = climbToCard(el);
      if(!card){return;}
      var t = normalizeBlockText(raw(card));
      if(t.length < 12 || isComposerText(t)){return;}
      var k = cardKey(card);
      if(seen[k]){return;}
      seen[k] = 1;
      cards.push(card);
    }
    document.querySelectorAll('article,[role="article"],[data-post-id],[data-feed-id],[data-community-post],[data-fcom-post],[data-post],[data-item-id],.post-card,.feed-post,.community-post,.fcom-post,.activity-card,.topic-card,.post,.entry,.card,.tcb4d-card,.tcb4d-post,.fcom-post-item,.feed-item,.community-card').forEach(add);
    document.querySelectorAll('a,button,span,strong,p,div').forEach(function(el){
      if(!inFeedColumn(el)){return;}
      var t = text(el);
      if(!/(#|＃)/i.test(t)){return;}
      add(el);
    });
    // เพิ่มตัวจับจากข้อความในฟีดโดยตรง เพราะบางธีมไม่ได้ใส่ class/data-poll ให้โพสต์ Poll หลังโพสต์เสร็จ
    document.querySelectorAll('article,section,li,div,p,span,strong,button,a').forEach(function(el){
      if(!inFeedColumn(el)){return;}
      var t = text(el);
      if(!voteTextLooksRelevant(t)){return;}
      add(el);
    });
    document.querySelectorAll('[data-poll-id],[data-vote-id],[data-vote-count],[data-total-votes],[data-votes],[data-upvotes],[data-downvotes],.poll,.poll-wrap,.poll-box,.poll-post,.poll-card,.poll-option,.poll-question,.vote-post,.vote-card,.vote-option,.vote-button,.vote-box').forEach(add);
    return cards;
  }
  function normalizeTag(tag){
    tag = String(tag||'').replace(/^#+/,'').replace(/[\u200B-\u200D\uFEFF]/g,'').trim();
    tag = tag.replace(/[.,;:!?()\[\]{}<>"'“”‘’]+$/g,'');
    if(!tag){return '';}
    if(/^[0-9a-f]{3}([0-9a-f]{3})?$/i.test(tag)){return '';}
    if(/^(fff|ffffff|000|000000|f97316|1e6b45|111111|ad|ads|css|html|body|community|feed|โพสต์)$/i.test(tag)){return '';}
    return tag.slice(0,48);
  }
  function tagsFromCard(card){
    var out = [], re = /(?:^|[^\p{L}\p{N}_&])(?:#|＃)([\p{L}\p{N}_\-]{2,48})/gu, m;
    var t = raw(card);
    while((m = re.exec(t))){
      var v = normalizeTag(m[1]);
      if(v){out.push(v);}
    }
    card.querySelectorAll('a[rel="tag"],a[href*="tag="],a[href*="/tag/"],.tag,.hashtag,[data-tag]').forEach(function(el){
      if(!inFeedColumn(el)){return;}
      var val = el.getAttribute('data-tag') || text(el);
      if(val.indexOf('#') === -1 && !el.matches('a[rel="tag"],.tag,.hashtag,[data-tag]')){return;}
      val.split(/\s+/).forEach(function(part){
        if(part.indexOf('#') !== -1 || el.matches('a[rel="tag"],.tag,.hashtag,[data-tag]')){
          var v = normalizeTag(part.replace(/^#|^＃/,''));
          if(v){out.push(v);}
        }
      });
    });
    return out;
  }
  function cleanLine(line){
    var s = String(line||'').replace(/https?:\/\/\S+/g,' ').replace(/(?:#|＃)[\p{L}\p{N}_\-]+/gu,' ').replace(/\s+/g,' ').trim();
    s = s.replace(/^(หัวข้อ|เรื่อง|คำถาม|title|question)\s*[:：]?\s*/i,'').trim();
    s = s.replace(/^[-–—:：|/\\\s]+|[-–—:：|/\\\s]+$/g,'').trim();
    if(s.length < 3){return '';}
    if(/^(admin|ผู้ดูแล|สาธารณะ|วันนี้|ล่าสุด|โพสต์ล่าสุด|สร้างโพสต์|เขียนโพสต์ใหม่|โหวต|คะแนนโหวต|กระแสโหวต|โพสต์โหวต|โพสต์เกี่ยวกับโหวต|vote|votes|poll|score|ดูทั้งหมด|ดูเพิ่มเติม|แสดงความคิดเห็น|ความคิดเห็น|รูปภาพ|วิดีโอ|แท็ก|ประเภท|พูดคุย|ทั่วไป)$/i.test(s)){return '';}
    if(/^(\d+\s*(วินาที|นาที|ชั่วโมง|วัน|เดือน|ปี)ที่แล้ว|\d+\s*(โหวต|คะแนน|โพสต์|ความคิดเห็น|แชร์|like|vote|votes)?)$/i.test(s)){return '';}
    if(/^(@|ประเภท\s*[:：]?)/i.test(s)){return '';}
    if(/[{}<>]|function\(|var\s+|const\s+|\.css|rgba?\(/i.test(s)){return '';}
    return s.slice(0,180);
  }
  function titleFromCard(card){
    var candidates = [];
    card.querySelectorAll('[data-post-title],[data-title],.post-title,.entry-title,.card-title,.topic-title,.vote-title,.poll-title,h1,h2,h3,h4,strong').forEach(function(el){
      if(!inFeedColumn(el)){return;}
      var v = el.getAttribute('data-title') || el.getAttribute('data-post-title') || text(el);
      if(v){candidates.push(v);}
    });
    raw(card).split(/[\n\r]+/).forEach(function(v){if(v){candidates.push(v);}});
    for(var i=0;i<candidates.length;i++){
      var v = cleanLine(candidates[i]);
      if(v){return v;}
    }
    return 'โพสต์โหวต';
  }
  function isVoteCard(card){
    // กระแสโหวตต้องมาจากโพสต์ในฟีดที่มี Poll/โหวตจริง ไม่อ่านกล่องสร้างโพสต์หรือเมนู
    if(!card || card.closest('[role="dialog"],.modal,.popup,.composer,.post-composer,.create-post,.tcb4d-composer,.comment-form,.reply-form')){return false;}
    if(card.matches('[data-poll-id],[data-vote-id],[data-post-type="poll"],[data-post-type="vote"],[data-type="poll"],[data-type="vote"],.poll-post,.poll-card,.vote-post,.vote-card')){return true;}
    if(card.querySelector('[data-poll-id],[data-vote-id],[data-vote-count],[data-total-votes],[data-votes],[data-upvotes],[data-downvotes],.poll,.poll-wrap,.poll-box,.poll-option,.poll-question,.vote-option,.vote-button,.vote-box')){return true;}
    var t = raw(card);
    return voteTextLooksRelevant(t);
  }
  function voteScore(card){
    var plus = 0, minus = 0, total = 0;
    card.querySelectorAll('[data-vote-count],[data-total-votes],[data-votes],[data-score],[data-upvotes],[data-downvotes],[data-plus-votes],[data-minus-votes],button,a,span,strong').forEach(function(el){
      ['data-upvotes','data-upvote-count','data-plus-votes','data-positive-votes'].forEach(function(a){plus=Math.max(plus, numberFrom(el.getAttribute(a)));});
      ['data-downvotes','data-downvote-count','data-minus-votes','data-negative-votes'].forEach(function(a){minus=Math.max(minus, numberFrom(el.getAttribute(a)));});
      ['data-vote-count','data-total-votes','data-votes','data-score'].forEach(function(a){total=Math.max(total, numberFrom(el.getAttribute(a)));});
      var label = (el.getAttribute('aria-label')||'') + ' ' + (el.getAttribute('title')||'') + ' ' + text(el);
      if(/(เห็นด้วย|โหวตบวก|บวก|up|plus|positive|agree|\+)/i.test(label)){plus=Math.max(plus, numberFrom(label));}
      if(/(ไม่เห็นด้วย|โหวตลบ|ลบ|down|minus|negative|disagree|\-)/i.test(label)){minus=Math.max(minus, numberFrom(label));}
      if(/(คะแนนโหวต|โหวต|votes?|poll)/i.test(label)){total=Math.max(total, numberFrom(label));}
    });
    var t = raw(card).replace(/,/g,'');
    var m;
    if((m = t.match(/(?:ผล\s*\+|เห็นด้วย|โหวตบวก|บวก|up|plus|positive|agree)\D{0,18}(\d{1,7})/iu))){plus=Math.max(plus,parseInt(m[1],10));}
    if((m = t.match(/(?:ผล\s*-|ไม่เห็นด้วย|โหวตลบ|ลบ|down|minus|negative|disagree)\D{0,18}(\d{1,7})/iu))){minus=Math.max(minus,parseInt(m[1],10));}
    if((m = t.match(/(?:คะแนนโหวต|คะแนนรวม|รวม|ทั้งหมด|โหวต|vote|votes|poll)\D{0,18}(\d{1,7})/iu))){total=Math.max(total,parseInt(m[1],10));}
    if(total < plus + minus){total = plus + minus;}
    if(total > 0 && plus <= 0 && minus <= 0){plus = total;}
    return {total:total, plus:plus, minus:minus};
  }
  function popularityScore(card){
    var total = 0;
    card.querySelectorAll('[data-reactions-count],[data-reaction-count],[data-comments-count],[data-comment-count],[data-likes-count],[data-like-count],[data-shares-count],[data-share-count],[data-views-count],[data-view-count],button,a,span,strong').forEach(function(el){
      ['data-reactions-count','data-reaction-count','data-comments-count','data-comment-count','data-likes-count','data-like-count','data-shares-count','data-share-count','data-views-count','data-view-count'].forEach(function(a){total += numberFrom(el.getAttribute(a));});
      var label = (el.getAttribute('aria-label')||'') + ' ' + (el.getAttribute('title')||'') + ' ' + text(el);
      if(/(ยอดนิยม|นิยม|ความคิดเห็น|คอมเมนต์|ตอบกลับ|ถูกใจ|ไลก์|แชร์|ดู|views?|comments?|reactions?|likes?|shares?)/i.test(label)){
        total += numberFrom(label);
      }
    });
    var t = raw(card).replace(/,/g,'');
    var re = /(?:ยอดนิยม|นิยม|ความคิดเห็น|คอมเมนต์|ตอบกลับ|ถูกใจ|ไลก์|แชร์|ดู|views?|comments?|reactions?|likes?|shares?)\D{0,18}(\d{1,7})/giu, m;
    while((m = re.exec(t))){total += parseInt(m[1],10)||0;}
    return {total:Math.max(0,total)};
  }
  function tagUrl(tag){return (cfg.tagBase || '/community/') + '?tag=' + encodeURIComponent(tag) + '#comments';}
  function mergeSummary(base, live){
    base = base || {}; live = live || {};
    var tagMap = Object.create(null), voteMap = Object.create(null);
    function putTag(item){
      if(!item || !item.tag){return;}
      var k = String(item.tag).toLowerCase();
      if(!tagMap[k]){tagMap[k]={tag:item.tag,count:0,url:item.url||tagUrl(item.tag)};}
      tagMap[k].count = Math.max(Number(tagMap[k].count)||0, Number(item.count)||0);
      if(item.url){tagMap[k].url=item.url;}
    }
    function putVote(item){
      if(!item || !item.title){return;}
      var k = String(item.url || item.title).toLowerCase();
      if(!voteMap[k] || (Number(item.total)||0) > (Number(voteMap[k].total)||0)){
        voteMap[k]={title:item.title,total:Number(item.total)||0,plus:Number(item.plus)||0,minus:Number(item.minus)||0,url:item.url||cfg.commentsUrl||'#comments',relatedOnly:!!(item.relatedOnly||item.related_only),source:item.source||item.score_source||''};
      }
    }
    (base.tags||[]).forEach(putTag); (live.tags||[]).forEach(putTag);
    (base.votes||[]).forEach(putVote); (live.votes||[]).forEach(putVote);
    var tags = Object.keys(tagMap).map(function(k){return tagMap[k];}).sort(function(a,b){return b.count-a.count || String(a.tag).localeCompare(String(b.tag));}).slice(0,5);
    var votes = Object.keys(voteMap).map(function(k){return voteMap[k];}).sort(function(a,b){return b.total-a.total || String(a.title).localeCompare(String(b.title));}).slice(0,5);
    return {tags:tags,votes:votes};
  }
  function commentUrl(card){
    var a = card.querySelector('a[href*="#comments"],a[href*="comment"],a[href*="reply"],a[href*="ตอบกลับ"]');
    if(a && a.href){return a.href;}
    var id = card.getAttribute('data-post-id') || card.getAttribute('data-feed-id') || card.getAttribute('data-id') || '';
    if(id){return (cfg.tagBase || '/community/') + '?feed_id=' + encodeURIComponent(id) + '#comments';}
    return cfg.commentsUrl || (location.pathname + '#comments');
  }
  function collectFeed(){
    var tagMap = Object.create(null), votes = [], cards = findFeedCards();
    cards.forEach(function(card){
      var key = cardKey(card), seenTags = Object.create(null);
      tagsFromCard(card).forEach(function(tag){
        var k = tag.toLowerCase();
        if(seenTags[k]){return;}
        seenTags[k] = 1;
        if(!tagMap[k]){tagMap[k]={tag:tag,count:0,url:tagUrl(tag),sources:Object.create(null)};}
        if(!tagMap[k].sources[key]){tagMap[k].sources[key]=1;tagMap[k].count++;}
      });
      if(isVoteCard(card)){
        var sc = voteScore(card);
        var relatedOnly = false, scoreSource = 'vote';
        if(sc.total <= 0){
          var pop = popularityScore(card);
          sc = {total:Math.max(1, pop.total || 0),plus:0,minus:0};
          relatedOnly = true;
          scoreSource = pop.total > 0 ? 'popular' : 'poll';
        }
        votes.push({title:titleFromCard(card),total:sc.total,plus:sc.plus,minus:sc.minus,url:commentUrl(card),relatedOnly:relatedOnly,source:scoreSource});
      }
    });
    // ถ้าไม่มี Poll ที่จับได้ ให้กระแสโหวตไม่ว่าง: ดึงโพสต์ฟีดยอดนิยม/ล่าสุดมาแสดงแทน เพื่อให้ผู้ใช้เห็นข้อมูลจริงจากฟีดทันที
    if(!votes.length){
      var used = Object.create(null);
      cards.forEach(function(card){
        var title = titleFromCard(card);
        if(!title || used[title.toLowerCase()]){return;}
        used[title.toLowerCase()] = 1;
        var pop = popularityScore(card);
        var total = Math.max(1, pop.total || 0);
        votes.push({title:title,total:total,plus:0,minus:0,url:commentUrl(card),relatedOnly:true,source:pop.total>0?'popular':'feed'});
      });
    }
    var tags = Object.keys(tagMap).map(function(k){return tagMap[k];}).sort(function(a,b){return b.count-a.count || a.tag.localeCompare(b.tag);}).slice(0,5);
    votes.sort(function(a,b){return b.total-a.total || String(a.title).localeCompare(String(b.title));});
    return {tags:tags, votes:votes.slice(0,5)};
  }
  function renderTags(list,items){
    list.innerHTML='';
    items.forEach(function(item,i){
      var li=document.createElement('li');li.className='tcb4d-cs-rank-row';
      li.innerHTML='<span class="tcb4d-cs-rank-badge"></span><span class="tcb4d-cs-rank-main"><a class="tcb4d-cs-tag-link" rel="tag"></a></span><span class="tcb4d-cs-rank-count"><strong></strong></span>';
      li.querySelector('.tcb4d-cs-rank-badge').textContent=String(i+1);
      var a=li.querySelector('a');a.href=item.url||tagUrl(item.tag);a.textContent='#'+item.tag;
      li.querySelector('strong').textContent=String(item.count||0)+' โพสต์';
      list.appendChild(li);
    });
  }
  function renderVotes(list,items){
    list.innerHTML='';
    var max = 0;
    items.forEach(function(item){max=Math.max(max, Number(item.total)||0);});
    items.forEach(function(item,i){
      var total=Number(item.total)||0, plus=Number(item.plus)||0, minus=Number(item.minus)||0, relatedOnly=!!item.relatedOnly;
      var pct=max>0?Math.round((total/max)*100):0;
      var li=document.createElement('li');li.className='tcb4d-cs-rank-row tcb4d-cs-vote-row';
      li.innerHTML='<span class="tcb4d-cs-rank-badge"></span><span class="tcb4d-cs-vote-left"><a class="tcb4d-cs-vote-link"></a><small class="tcb4d-cs-vote-meta"></small></span><span class="tcb4d-cs-vote-meter" aria-hidden="true"><i class="tcb4d-cs-vote-meter-fill"></i></span><span class="tcb4d-cs-rank-count"><strong></strong><small></small></span>';
      li.querySelector('.tcb4d-cs-rank-badge').textContent=String(i+1);
      var a=li.querySelector('a');a.href=item.url||cfg.commentsUrl||'#comments';a.textContent=item.title||'Poll';
      a.title=item.title||'Poll';
      li.querySelector('.tcb4d-cs-vote-meta').textContent=relatedOnly?(item.source==='popular'?'ยอดนิยมจากฟีด':(item.source==='feed'?'จากฟีด':'ข้อมูล Poll')):'+'+String(plus)+' / -'+String(minus);
      li.querySelector('.tcb4d-cs-vote-meter-fill').style.width=(total>0?Math.max(6,pct):0)+'%';
      li.querySelector('strong').textContent=String(total);
      li.querySelector('.tcb4d-cs-rank-count small').textContent=relatedOnly?(item.source==='popular'?'นิยม':'โพสต์'):'โหวต';
      list.appendChild(li);
    });
  }
  function apply(summary){
    sidebars.forEach(function(sb){
      var tl=sb.querySelector('[data-tcb4d-trends-list]'), te=sb.querySelector('[data-tcb4d-trends-empty]');
      var vl=sb.querySelector('[data-tcb4d-votes-list]'), ve=sb.querySelector('[data-tcb4d-votes-empty]');
      var tags=summary.tags||[], votes=summary.votes||[];
      if(tl){ if(tags.length){renderTags(tl,tags);tl.style.display='';if(te){te.style.display='none';}} else {tl.innerHTML='';tl.style.display='none';if(te){te.style.display='';}} }
      if(vl){ if(votes.length){renderVotes(vl,votes);vl.style.display='';if(ve){ve.style.display='none';}} else {vl.innerHTML='';vl.style.display='none';if(ve){ve.style.display='';}} }
    });
  }
  function syncVisibility(){sidebars.forEach(function(sb){sb.hidden = innerWidth < (cfg.minWidth || 1025);});}
  function scan(){syncVisibility(); if(innerWidth >= (cfg.minWidth || 1025)){apply(mergeSummary(cfg.summary || {tags:[],votes:[]}, collectFeed()));}}
  function queue(delay){clearTimeout(timer); timer=setTimeout(scan, delay || 120);}
  if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',function(){queue(20);});}else{queue(20);}
  window.addEventListener('load',function(){queue(80);});
  [250,700,1200,2200,4000,7000,11000,16000,24000,35000].forEach(function(ms){setTimeout(function(){queue(20);},ms);});
  window.addEventListener('resize',function(){queue(160);},{passive:true});
  window.addEventListener('scroll',function(){queue(220);},{passive:true});
  document.addEventListener('click',function(){setTimeout(function(){queue(30);},280);},true);
  if(window.MutationObserver){new MutationObserver(function(){queue(220);}).observe(document.body,{childList:true,subtree:true,characterData:true,attributes:true,attributeFilter:['class','id','data-post-id','data-feed-id','data-vote-count','data-upvotes','data-downvotes','data-total-votes','aria-label','title','href']});}
})();
JS;
    }

    public function render_sidebar_from_footer() {
        if ($this->should_render()) {
            echo $this->sidebar_markup(false); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
    }

    public function shortcode_sidebar() {
        return $this->sidebar_markup(true);
    }

    private function active_sponsors($s) {
        $now = current_time('timestamp');
        $items = [];
        for ($i = 1; $i <= 6; $i++) {
            $title = trim((string) $s["sponsor_{$i}_title"]);
            $desc = trim((string) $s["sponsor_{$i}_description"]);
            $img = trim((string) $s["sponsor_{$i}_image"]);
            if ($title === '' && $desc === '' && $img === '') {
                continue;
            }
            $start = $this->datetime_to_timestamp($s["sponsor_{$i}_start"] ?? '');
            $end = $this->datetime_to_timestamp($s["sponsor_{$i}_end"] ?? '');
            if ($start > 0 && $now < $start) {
                continue;
            }
            if ($end > 0 && $now > $end) {
                continue;
            }
            $items[] = [
                'title' => $title !== '' ? $title : 'พื้นที่แนะนำ',
                'description' => $desc !== '' ? $desc : 'ดูรายละเอียดเพิ่มเติม',
                'url' => trim((string) $s["sponsor_{$i}_url"]) !== '' ? $s["sponsor_{$i}_url"] : '/advertise/',
                'image' => $img,
            ];
        }
        if (count($items) > 2) {
            shuffle($items);
        }
        return array_slice($items, 0, 2);
    }

    private function datetime_to_timestamp($value) {
        $value = trim((string) $value);
        if ($value === '') {
            return 0;
        }
        try {
            $tz = function_exists('wp_timezone') ? wp_timezone() : new DateTimeZone(wp_timezone_string());
            $dt = DateTime::createFromFormat('Y-m-d\TH:i', $value, $tz);
            return $dt ? (int) $dt->getTimestamp() : 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    private function sidebar_markup($shortcode = false) {
        $s = $this->settings();
        $summary = $this->collect_summary($s);
        $votes = $summary['votes'];
        $tags = $summary['tags'];
        $max_vote_total = 0;
        foreach ((array) $votes as $vote_item) {
            $max_vote_total = max($max_vote_total, (int) ($vote_item['total'] ?? 0));
        }
        $sponsors = $this->active_sponsors($s);
        $id = $shortcode ? self::FRONT_ID . '-shortcode' : self::FRONT_ID;
        ob_start();
        ?>
        <aside id="<?php echo esc_attr($id); ?>" class="tcb4d-community-right-sidebar" aria-label="<?php echo esc_attr($s['title']); ?>" spellcheck="false" contenteditable="false">
            <div class="tcb4d-cs-card">
                <section class="tcb4d-cs-section tcb4d-cs-section-sponsored tcb4d-cs-box" aria-label="<?php echo esc_attr($s['sponsor_title']); ?>">
                    <div class="tcb4d-cs-section-title"><span><?php echo esc_html($s['sponsor_title']); ?></span><a class="tcb4d-cs-viewall" href="<?php echo esc_url(home_url('/advertise/')); ?>"><?php echo esc_html($s['sponsor_more_label']); ?></a></div>
                    <div class="tcb4d-cs-sponsored-list">
                        <?php foreach ($sponsors as $sp): ?>
                            <a class="tcb4d-cs-sponsored-item" href="<?php echo esc_url($this->abs_url($sp['url'])); ?>">
                                <span class="tcb4d-cs-sponsored-thumb" aria-hidden="true"><?php if ($sp['image'] !== ''): ?><img src="<?php echo esc_url($sp['image']); ?>" alt=""><?php else: ?>AD<?php endif; ?></span>
                                <span class="tcb4d-cs-sponsored-body"><strong class="tcb4d-cs-sponsored-name"><?php echo esc_html($sp['title']); ?></strong><span class="tcb4d-cs-sponsored-text"><?php echo esc_html($sp['description']); ?></span><span class="tcb4d-cs-sponsored-link">ดูเพิ่มเติม ›</span></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
                <section class="tcb4d-cs-section tcb4d-cs-section-votes tcb4d-cs-box" aria-label="<?php echo esc_attr($s['vote_title']); ?>">
                    <div class="tcb4d-cs-section-title"><span><?php echo esc_html($s['vote_title']); ?></span><small><?php echo esc_html($s['vote_subtitle']); ?></small></div>
                    <ol class="tcb4d-cs-ranked" data-tcb4d-votes-list <?php echo empty($votes) ? 'style="display:none"' : ''; ?>>
                        <?php foreach ($votes as $i => $vote): ?>
                            <?php $vote_pct = $max_vote_total > 0 ? max(6, min(100, round(((int) $vote['total'] / $max_vote_total) * 100))) : 0; ?>
                            <li class="tcb4d-cs-rank-row tcb4d-cs-vote-row"><span class="tcb4d-cs-rank-badge"><?php echo esc_html((string) ($i + 1)); ?></span><span class="tcb4d-cs-vote-left"><a class="tcb4d-cs-vote-link" href="<?php echo esc_url($vote['url']); ?>" title="<?php echo esc_attr($vote['title']); ?>"><?php echo esc_html($vote['title']); ?></a><small class="tcb4d-cs-vote-meta"><?php echo !empty($vote['related_only']) ? esc_html(($vote['source'] ?? '') === 'popular' ? 'ยอดนิยมจากฟีด' : (($vote['source'] ?? '') === 'feed' ? 'จากฟีด' : 'โพสต์เกี่ยวกับโหวต')) : '+' . esc_html(number_format_i18n((int) $vote['plus'])) . ' / -' . esc_html(number_format_i18n((int) $vote['minus'])); ?></small></span><span class="tcb4d-cs-vote-meter" aria-hidden="true"><i class="tcb4d-cs-vote-meter-fill" style="--tcb4d-vote-pct:<?php echo esc_attr((string) $vote_pct); ?>%"></i></span><span class="tcb4d-cs-rank-count"><strong><?php echo esc_html(number_format_i18n((int) $vote['total'])); ?></strong><small><?php echo !empty($vote['related_only']) ? esc_html(($vote['source'] ?? '') === 'popular' ? 'นิยม' : 'โพสต์') : esc_html('โหวต'); ?></small></span></li>
                        <?php endforeach; ?>
                    </ol>
                    <p class="tcb4d-cs-empty" data-tcb4d-votes-empty <?php echo !empty($votes) ? 'style="display:none"' : ''; ?>>ยังไม่มีโพสต์โหวตในฟีด</p>
                </section>
                <section class="tcb4d-cs-section tcb4d-cs-section-trending tcb4d-cs-box" aria-label="<?php echo esc_attr($s['trending_title']); ?>">
                    <div class="tcb4d-cs-section-title"><span><?php echo esc_html($s['trending_title']); ?></span><small><?php echo esc_html($s['trending_subtitle']); ?></small></div>
                    <ol class="tcb4d-cs-ranked" data-tcb4d-trends-list <?php echo empty($tags) ? 'style="display:none"' : ''; ?>>
                        <?php foreach ($tags as $i => $tag): ?>
                            <li class="tcb4d-cs-rank-row"><span class="tcb4d-cs-rank-badge"><?php echo esc_html((string) ($i + 1)); ?></span><span class="tcb4d-cs-rank-main"><a class="tcb4d-cs-tag-link" rel="tag" href="<?php echo esc_url($tag['url']); ?>">#<?php echo esc_html($tag['tag']); ?></a></span><span class="tcb4d-cs-rank-count"><strong><?php echo esc_html(number_format_i18n((int) $tag['count'])); ?> โพสต์</strong></span></li>
                        <?php endforeach; ?>
                    </ol>
                    <p class="tcb4d-cs-empty" data-tcb4d-trends-empty <?php echo !empty($tags) ? 'style="display:none"' : ''; ?>>ยังไม่มีโพสต์ที่มี #</p>
                </section>
            </div>
        </aside>
        <?php
        return (string) ob_get_clean();
    }

    private function abs_url($url) {
        $url = trim((string) $url);
        if ($url === '') {
            return home_url('/advertise/');
        }
        return (strpos($url, '/') === 0 && strpos($url, '//') !== 0) ? home_url($url) : $url;
    }

    public function ajax_summary() {
        check_ajax_referer(self::AJAX_ACTION, 'nonce');
        wp_send_json_success($this->collect_summary($this->settings()));
    }

    private function collect_summary($settings) {
        $tags = [];
        $votes = [];
        $limit = isset($settings['scan_limit']) ? min(20000, max(50, absint($settings['scan_limit']))) : 20000;

        // #ยอดนิยม และกระแสโหวตอ่านจากฟีดชุมชนจริง: ถ้าโพสต์มี Poll/โหวต ให้ส่งเข้ากระแสโหวตทันที
        $this->collect_from_community_tables($tags, $votes, $limit);
        $this->collect_from_fcom($tags, $votes, $limit);
        $this->collect_from_wp_community_posts($tags, $votes, $limit);
        $this->collect_from_wp_posts($tags, $votes, $limit);

        // fallback เฉพาะกรณีฟีดไม่มีข้อมูล แต่ระบบมีตาราง Poll แยกที่เชื่อมกับโพสต์ไว้แล้ว
        if (empty($votes)) {
            $this->collect_from_poll_sources($votes, $limit);
        }

        // ทางสำรองสุดท้าย: ถ้าฟีด/โพลยังอ่านไม่เจอ ให้ดึงโพสต์ยอดนิยมจริงมาแสดงแทน ไม่ปล่อยให้กระแสโหวตว่าง
        if (empty($votes)) {
            $this->collect_popular_feed_fallback($votes, min(80, $limit));
        }

        return [
            'tags' => $this->finalize_tags($tags),
            'votes' => $this->finalize_votes($votes),
        ];
    }

    private function collect_popular_feed_fallback(&$votes, $limit) {
        $this->collect_popular_from_fcom($votes, $limit);
        $this->collect_popular_from_wp_posts($votes, $limit);
    }

    private function collect_popular_from_fcom(&$votes, $limit) {
        global $wpdb;
        if (empty($wpdb)) {
            return;
        }
        $posts_table = $this->fcom_table('fcom_posts');
        if ($posts_table === '') {
            return;
        }
        $sql = 'SELECT id,title,slug,message,message_rendered,type,content_type,meta,reactions_count,comments_count,status,created_at FROM ' . $this->quote_table($posts_table) . " WHERE (status IS NULL OR status IN ('published','active','publish')) ORDER BY (COALESCE(reactions_count,0)+COALESCE(comments_count,0)) DESC, created_at DESC LIMIT " . (int) $limit;
        $rows = $wpdb->get_results($sql, ARRAY_A);
        foreach ((array) $rows as $row) {
            $id = absint($row['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            $text = $this->row_text($row, ['title', 'message', 'message_rendered', 'meta']);
            $title = $this->clean_title($row['title'] ?? '') ?: $this->vote_title_from_row($row, $text);
            if ($title === '') {
                continue;
            }
            $popular = $this->vote_popularity_score($row, $text);
            $total = max(1, (int) ($popular['total'] ?? 0));
            $votes[] = [
                'title' => $title,
                'total' => $total,
                'plus' => 0,
                'minus' => 0,
                'related_only' => true,
                'source' => $total > 1 ? 'popular' : 'feed',
                'url' => home_url('/community/?feed_id=' . $id . '#comments'),
                '_key' => 'popular-fcom:' . $id,
            ];
        }
    }

    private function collect_popular_from_wp_posts(&$votes, $limit) {
        global $wpdb;
        if (empty($wpdb) || empty($wpdb->posts)) {
            return;
        }
        $excluded = ['page','attachment','revision','nav_menu_item','wp_navigation','custom_css','customize_changeset','oembed_cache','user_request','wp_block','wp_template','wp_template_part','wp_global_styles'];
        $ph = implode(',', array_fill(0, count($excluded), '%s'));
        $sql = $wpdb->prepare("SELECT ID,post_title,post_content,post_excerpt,post_type,comment_count FROM {$wpdb->posts} WHERE post_status='publish' AND post_type NOT IN ($ph) AND post_name<>%s ORDER BY comment_count DESC, post_date_gmt DESC LIMIT %d", array_merge($excluded, [self::AD_PAGE_SLUG, (int) $limit]));
        foreach ((array) $wpdb->get_results($sql) as $post) {
            $title = $this->clean_title($post->post_title ?? '');
            if ($title === '') {
                $text = html_entity_decode(wp_strip_all_tags((string) ($post->post_excerpt ?? '') . ' ' . (string) ($post->post_content ?? '')), ENT_QUOTES, 'UTF-8');
                $title = $this->vote_title_from_row(['title' => ''], $text);
            }
            if ($title === '') {
                continue;
            }
            $score = max(0, (int) ($post->comment_count ?? 0));
            foreach ((array) get_post_meta((int) $post->ID) as $key => $values) {
                if (!preg_match('/(reaction|like|comment|reply|share|view|popular|trend|score|count|total)/i', (string) $key)) {
                    continue;
                }
                foreach ((array) $values as $value) {
                    $score += $this->number_from_value($value);
                }
            }
            $total = max(1, $score);
            $votes[] = [
                'title' => $title,
                'total' => $total,
                'plus' => 0,
                'minus' => 0,
                'related_only' => true,
                'source' => $total > 1 ? 'popular' : 'feed',
                'url' => get_permalink((int) $post->ID) ? get_permalink((int) $post->ID) . '#comments' : home_url('/community/#comments'),
                '_key' => 'popular-wp:' . (int) $post->ID,
            ];
        }
    }

    private function collect_from_poll_sources(&$votes, $limit) {
        $this->collect_from_poll_tables($votes, $limit);
        $this->collect_from_wp_poll_posts($votes, $limit);
    }

    private function collect_from_poll_tables(&$votes, $limit) {
        global $wpdb;
        if (empty($wpdb)) {
            return;
        }
        $tables = $wpdb->get_col('SHOW TABLES');
        foreach ((array) $tables as $table) {
            $table_lc = strtolower((string) $table);
            if (!preg_match('/(poll|vote)/i', $table_lc)) {
                continue;
            }
            if (preg_match('/(meta|option|setting|log|cache|session|notification|comment|reply)/i', $table_lc)) {
                continue;
            }
            $columns = $this->table_columns($table);
            if (empty($columns)) {
                continue;
            }
            $id_col = $this->first_existing_column($columns, ['id', 'poll_id', 'vote_id', 'post_id', 'question_id', 'item_id', 'object_id']);
            if ($id_col === '') {
                continue;
            }
            $title_cols = $this->existing_columns($columns, ['title', 'poll_title', 'vote_title', 'question', 'poll_question', 'post_title', 'subject', 'heading', 'name', 'label']);
            $type_cols = $this->existing_columns($columns, ['type', 'poll_type', 'vote_type', 'object_type', 'content_type', 'post_type']);
            $score_cols = $this->existing_columns($columns, ['votes', 'vote_count', 'votes_count', 'total_votes', 'score', 'count', 'counter', 'upvotes', 'up_votes', 'upvote_count', 'plus_votes', 'positive_votes', 'yes_votes', 'agree_votes', 'downvotes', 'down_votes', 'downvote_count', 'minus_votes', 'negative_votes', 'no_votes', 'disagree_votes']);
            $post_col = $this->first_existing_column($columns, ['post_id', 'object_id', 'feed_id', 'community_post_id']);
            if (empty($title_cols) && empty($score_cols) && $post_col === '') {
                continue;
            }
            $status_col = $this->first_existing_column($columns, ['status', 'post_status', 'state', 'visibility']);
            $order_col = $this->first_existing_column($columns, ['created_at', 'created', 'post_date', 'date_created', 'published_at', 'updated_at', $id_col]);
            $select_cols = array_values(array_unique(array_filter(array_merge([$id_col, $post_col], $title_cols, $type_cols, $score_cols, [$status_col, $order_col]))));
            $select = implode(',', array_map([$this, 'quote_identifier'], $select_cols));
            $where = '1=1';
            if ($status_col !== '') {
                $where .= ' AND (' . $this->quote_identifier($status_col) . " IS NULL OR " . $this->quote_identifier($status_col) . " IN ('published','publish','active','approved','public','1'))";
            }
            $order = $order_col !== '' ? ' ORDER BY ' . $this->quote_identifier($order_col) . ' DESC' : '';
            $rows = $wpdb->get_results('SELECT ' . $select . ' FROM ' . $this->quote_table($table) . ' WHERE ' . $where . $order . ' LIMIT ' . (int) $limit, ARRAY_A);
            foreach ((array) $rows as $row) {
                $poll_id = isset($row[$id_col]) ? (string) $row[$id_col] : '';
                if ($poll_id === '') {
                    continue;
                }
                $post_id = $post_col !== '' && isset($row[$post_col]) ? absint($row[$post_col]) : 0;
                $text = $this->row_text($row, $select_cols);
                $score = $this->row_vote_score($row, $text);
                $related = $this->poll_related_score($table, $poll_id, $post_id);
                if ((int) $related['total'] > (int) $score['total']) {
                    $score = $related;
                }
                if ((int) ($score['total'] ?? 0) <= 0) {
                    continue;
                }
                $title = $this->poll_title_from_row($row, $title_cols, $post_id, $text);
                $url = $post_id > 0 && get_permalink($post_id) ? get_permalink($post_id) . '#comments' : home_url('/community/?poll_id=' . rawurlencode($poll_id) . '#comments');
                $votes[] = [
                    'title' => $title !== '' ? $title : 'Poll',
                    'total' => (int) $score['total'],
                    'plus' => (int) $score['plus'],
                    'minus' => (int) $score['minus'],
                    'related_only' => false,
                    'source' => 'vote',
                    'url' => $url,
                    '_key' => 'polltbl:' . $table . ':' . $poll_id,
                ];
            }
        }
    }

    private function collect_from_wp_poll_posts(&$votes, $limit) {
        global $wpdb;
        if (empty($wpdb) || empty($wpdb->posts)) {
            return;
        }
        $post_types = $wpdb->get_col("SELECT DISTINCT post_type FROM {$wpdb->posts} WHERE post_status='publish'");
        $allowed = [];
        foreach ((array) $post_types as $type) {
            if (preg_match('/(poll|vote)/i', (string) $type)) {
                $allowed[] = (string) $type;
            }
        }
        if (!empty($allowed)) {
            $ph = implode(',', array_fill(0, count($allowed), '%s'));
            $sql = $wpdb->prepare("SELECT ID,post_title,post_content,post_excerpt,post_type,comment_count FROM {$wpdb->posts} WHERE post_status='publish' AND post_type IN ($ph) ORDER BY post_date_gmt DESC LIMIT %d", array_merge($allowed, [(int) $limit]));
            $posts = $wpdb->get_results($sql);
            foreach ((array) $posts as $post) {
                $this->add_wp_poll_vote_item($votes, $post);
            }
        }
        if (!empty($wpdb->postmeta)) {
            $ids = $wpdb->get_col($wpdb->prepare("SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key REGEXP %s LIMIT %d", '(poll|vote|upvote|downvote|score)', (int) $limit));
            foreach ((array) $ids as $post_id) {
                $post = get_post((int) $post_id);
                if ($post && $post->post_status === 'publish') {
                    $this->add_wp_poll_vote_item($votes, $post);
                }
            }
        }
    }

    private function add_wp_poll_vote_item(&$votes, $post) {
        $post_id = absint($post->ID ?? 0);
        if ($post_id <= 0) {
            return;
        }
        $text = html_entity_decode(wp_strip_all_tags((string) ($post->post_title ?? '') . ' ' . (string) ($post->post_excerpt ?? '') . ' ' . (string) ($post->post_content ?? '')), ENT_QUOTES, 'UTF-8');
        $score = $this->wp_vote_score($post_id, $text);
        if ((int) ($score['total'] ?? 0) <= 0) {
            $score = $this->vote_related_fallback_score(['ID' => $post_id, 'post_type' => $post->post_type ?? 'poll', 'comment_count' => $post->comment_count ?? 0], $text);
        }
        $votes[] = [
            'title' => $this->clean_title($post->post_title ?? '') ?: 'Poll',
            'total' => (int) $score['total'],
            'plus' => (int) $score['plus'],
            'minus' => (int) $score['minus'],
            'related_only' => !empty($score['related_only']),
            'source' => isset($score['source']) ? (string) $score['source'] : 'vote',
            'url' => get_permalink($post_id) ? get_permalink($post_id) . '#comments' : home_url('/community/#comments'),
            '_key' => 'wppoll:' . $post_id,
        ];
    }

    private function poll_related_score($parent_table, $poll_id, $post_id = 0) {
        global $wpdb;
        $out = ['total' => 0, 'plus' => 0, 'minus' => 0];
        if (empty($wpdb) || $poll_id === '') {
            return $out;
        }
        $tables = $wpdb->get_col('SHOW TABLES');
        foreach ((array) $tables as $table) {
            if ((string) $table === (string) $parent_table) {
                continue;
            }
            $table_lc = strtolower((string) $table);
            if (!preg_match('/(poll|vote|answer|option|choice|response)/i', $table_lc)) {
                continue;
            }
            if (preg_match('/(meta|setting|log|cache|session|notification|comment|reply)/i', $table_lc)) {
                continue;
            }
            $columns = $this->table_columns($table);
            if (empty($columns)) {
                continue;
            }
            $rel_col = $this->first_existing_column($columns, ['poll_id', 'vote_id', 'question_id', 'parent_id', 'post_id', 'object_id', 'item_id']);
            if ($rel_col === '') {
                continue;
            }
            $values = [(string) $poll_id];
            if ($post_id > 0) {
                $values[] = (string) $post_id;
            }
            $values = array_values(array_unique($values));
            foreach ($values as $value) {
                $count = (int) $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM ' . $this->quote_table($table) . ' WHERE ' . $this->quote_identifier($rel_col) . '=%s', $value));
                $score_cols = $this->existing_columns($columns, ['votes', 'vote_count', 'votes_count', 'total_votes', 'score', 'count', 'counter', 'upvotes', 'up_votes', 'upvote_count', 'plus_votes', 'positive_votes', 'yes_votes', 'agree_votes', 'downvotes', 'down_votes', 'downvote_count', 'minus_votes', 'negative_votes', 'no_votes', 'disagree_votes']);
                if (empty($score_cols)) {
                    if ($count > (int) $out['total']) {
                        $out = ['total' => $count, 'plus' => $count, 'minus' => 0];
                    }
                    continue;
                }
                $label_cols = $this->existing_columns($columns, ['type', 'value', 'option', 'option_text', 'choice', 'choice_text', 'answer', 'label', 'name']);
                $select_cols = array_values(array_unique(array_merge($score_cols, $label_cols)));
                $select = implode(',', array_map([$this, 'quote_identifier'], $select_cols));
                $rows = $wpdb->get_results($wpdb->prepare('SELECT ' . $select . ' FROM ' . $this->quote_table($table) . ' WHERE ' . $this->quote_identifier($rel_col) . '=%s LIMIT 1000', $value), ARRAY_A);
                $plus = 0;
                $minus = 0;
                $total = 0;
                foreach ((array) $rows as $row) {
                    $score = $this->row_vote_score($row, $this->row_text($row, $select_cols));
                    $label = strtolower($this->row_text($row, $label_cols));
                    $row_total = max((int) $score['total'], (int) $score['plus'] + (int) $score['minus']);
                    if ($row_total <= 0) {
                        $row_total = 1;
                    }
                    if (preg_match('/(down|minus|negative|dislike|disagree|no|ไม่เห็นด้วย|ลบ)/iu', $label)) {
                        $minus += $row_total;
                    } elseif (preg_match('/(up|plus|positive|like|agree|yes|เห็นด้วย|บวก)/iu', $label)) {
                        $plus += $row_total;
                    } else {
                        $plus += max((int) $score['plus'], $row_total);
                        $minus += (int) $score['minus'];
                    }
                    $total += $row_total;
                }
                if ($total > (int) $out['total']) {
                    $out = ['total' => $total, 'plus' => $plus > 0 ? $plus : $total, 'minus' => $minus];
                }
            }
        }
        return $out;
    }

    private function poll_title_from_row($row, $title_cols, $post_id, $text) {
        foreach ((array) $title_cols as $col) {
            $title = $this->clean_title($row[$col] ?? '');
            if ($title !== '') {
                return $title;
            }
        }
        if ($post_id > 0) {
            $title = $this->clean_title(get_the_title($post_id));
            if ($title !== '') {
                return $title;
            }
        }
        return $this->vote_title_from_row(['title' => ''], $text);
    }

    private function collect_from_community_tables(&$tags, &$votes, $limit) {
        global $wpdb;
        if (empty($wpdb)) {
            return;
        }
        $tables = $wpdb->get_col('SHOW TABLES');
        foreach ((array) $tables as $table) {
            $table_lc = strtolower((string) $table);
            if (!preg_match('/(community|fcom|feed|tcb4d|thinkb4do)/i', $table_lc)) {
                continue;
            }
            if (preg_match('/(comment|reply|reaction|like|meta|option|setting|log|cache|session|user|member|sponsor|advert|ads|stat|view|visit|notification)/i', $table_lc)) {
                continue;
            }
            if (preg_match('/fcom_posts$/i', $table_lc)) {
                continue;
            }
            $columns = $this->table_columns($table);
            if (empty($columns)) {
                continue;
            }
            $id_col = $this->first_existing_column($columns, ['id', 'post_id', 'feed_id', 'topic_id', 'item_id']);
            if ($id_col === '') {
                continue;
            }
            $title_cols = $this->existing_columns($columns, ['title', 'post_title', 'topic_title', 'question', 'subject', 'heading', 'name']);
            $text_cols = $this->existing_columns($columns, ['message', 'message_rendered', 'content', 'post_content', 'body', 'text', 'description', 'caption', 'excerpt', 'post_excerpt', 'summary', 'meta', 'data', 'payload']);
            $type_cols = $this->existing_columns($columns, ['type', 'post_type', 'content_type', 'feed_type', 'topic_type', 'category', 'mode', 'kind']);
            if (empty($title_cols) && empty($text_cols) && empty($type_cols)) {
                continue;
            }
            $score_cols = $this->existing_columns($columns, ['votes', 'vote_count', 'votes_count', 'total_votes', 'score', 'upvotes', 'up_votes', 'upvote_count', 'plus_votes', 'positive_votes', 'likes', 'downvotes', 'down_votes', 'downvote_count', 'minus_votes', 'negative_votes', 'dislikes']);
            $status_col = $this->first_existing_column($columns, ['status', 'post_status', 'state', 'visibility']);
            $order_col = $this->first_existing_column($columns, ['created_at', 'created', 'post_date', 'date_created', 'published_at', 'id']);
            $select_cols = array_values(array_unique(array_filter(array_merge([$id_col], $title_cols, $text_cols, $type_cols, $score_cols, [$status_col, $order_col]))));
            $select = implode(',', array_map([$this, 'quote_identifier'], $select_cols));
            $where = '1=1';
            if ($status_col !== '') {
                $where .= ' AND (' . $this->quote_identifier($status_col) . " IS NULL OR " . $this->quote_identifier($status_col) . " IN ('published','publish','active','approved','public','1'))";
            }
            $order = $order_col !== '' ? ' ORDER BY ' . $this->quote_identifier($order_col) . ' DESC' : '';
            $sql = 'SELECT ' . $select . ' FROM ' . $this->quote_table($table) . ' WHERE ' . $where . $order . ' LIMIT ' . (int) $limit;
            $rows = $wpdb->get_results($sql, ARRAY_A);
            foreach ((array) $rows as $row) {
                $source_id = isset($row[$id_col]) ? (string) $row[$id_col] : md5(wp_json_encode($row));
                $source_key = 'tbl:' . $table . ':' . $source_id;
                $text = $this->row_text($row, array_values(array_unique(array_merge($title_cols, $text_cols, $type_cols))));
                foreach (array_unique($this->extract_hashtags($text)) as $tag) {
                    $this->add_tag($tags, $tag, $source_key);
                }
                if ($this->is_vote_row($row, $text)) {
                    $score = $this->row_vote_score($row, $text);
                    if ((int) ($score['total'] ?? 0) <= 0) {
                        $score = $this->vote_related_fallback_score($row, $text);
                    }
                    $title = $this->vote_title_from_row($row, $text);
                    $votes[] = [
                        'title' => $title !== '' ? $title : 'โพสต์เกี่ยวกับโหวต',
                        'total' => (int) $score['total'],
                        'plus' => (int) $score['plus'],
                        'minus' => (int) $score['minus'],
                        'related_only' => !empty($score['related_only']),
                        'source' => isset($score['source']) ? (string) $score['source'] : 'vote',
                        'url' => home_url('/community/?feed_id=' . rawurlencode($source_id) . '#comments'),
                        '_key' => $source_key,
                    ];
                }
            }
        }
    }

    private function table_columns($table) {
        global $wpdb;
        $cols = [];
        $rows = $wpdb->get_results('SHOW COLUMNS FROM ' . $this->quote_table($table), ARRAY_A);
        foreach ((array) $rows as $row) {
            if (!empty($row['Field'])) {
                $cols[(string) $row['Field']] = true;
            }
        }
        return $cols;
    }

    private function existing_columns($columns, $wanted) {
        $out = [];
        foreach ((array) $wanted as $name) {
            foreach ($columns as $col => $_) {
                if (strtolower((string) $col) === strtolower((string) $name)) {
                    $out[] = (string) $col;
                    break;
                }
            }
        }
        return array_values(array_unique($out));
    }

    private function first_existing_column($columns, $wanted) {
        $found = $this->existing_columns($columns, $wanted);
        return !empty($found) ? (string) $found[0] : '';
    }

    private function quote_identifier($identifier) {
        return '`' . str_replace('`', '``', (string) $identifier) . '`';
    }

    private function row_vote_score($row, $text) {
        $plus = 0;
        $minus = 0;
        $total = 0;
        foreach ((array) $row as $key => $value) {
            $key_lc = strtolower((string) $key);
            if (preg_match('/(view|share|comment|cache|color|style|css|image|width|height)/i', $key_lc)) {
                continue;
            }
            $n = $this->number_from_value($value);
            if ($n <= 0) {
                continue;
            }
            if (preg_match('/(down|minus|negative|dislike|disagree|no|vote_down)/i', $key_lc)) {
                $minus = max($minus, $n);
            } elseif (preg_match('/(up|plus|positive|agree|yes|vote_up)/i', $key_lc)) {
                $plus = max($plus, $n);
            } elseif (preg_match('/(vote|votes|poll|score|total)/i', $key_lc)) {
                $total = max($total, $n);
            }
        }
        if ($plus <= 0 && preg_match('/(?:ผล\s*\+|เห็นด้วย|โหวตบวก|บวก|up|plus|positive|agree)\D{0,18}(\d{1,7})/iu', $text, $m)) {
            $plus = max($plus, (int) $m[1]);
        }
        if ($minus <= 0 && preg_match('/(?:ผล\s*-|ไม่เห็นด้วย|โหวตลบ|ลบ|down|minus|negative|disagree)\D{0,18}(\d{1,7})/iu', $text, $m)) {
            $minus = max($minus, (int) $m[1]);
        }
        if ($total <= 0 && preg_match('/(?:คะแนนโหวต|คะแนนรวม|รวม|ทั้งหมด|โหวต|vote|votes|poll)\D{0,18}(\d{1,7})/iu', $text, $m)) {
            $total = max($total, (int) $m[1]);
        }
        if ($total < $plus + $minus) {
            $total = $plus + $minus;
        }
        if ($total > 0 && $plus <= 0 && $minus <= 0) {
            $plus = $total;
        }
        return ['total' => (int) $total, 'plus' => (int) $plus, 'minus' => (int) $minus];
    }

    private function collect_from_wp_community_posts(&$tags, &$votes, $limit) {
        global $wpdb;
        if (empty($wpdb) || empty($wpdb->posts)) {
            return;
        }
        $post_types = $wpdb->get_col("SELECT DISTINCT post_type FROM {$wpdb->posts} WHERE post_status='publish'");
        $allowed = [];
        foreach ((array) $post_types as $type) {
            if (preg_match('/(community|feed|fcom|tcb4d|thinkb4do|topic|poll|vote)/i', (string) $type)) {
                $allowed[] = (string) $type;
            }
        }
        if (empty($allowed)) {
            return;
        }
        $ph = implode(',', array_fill(0, count($allowed), '%s'));
        $sql = $wpdb->prepare("SELECT ID,post_title,post_content,post_excerpt,post_type,comment_count FROM {$wpdb->posts} WHERE post_status='publish' AND post_type IN ($ph) ORDER BY post_date_gmt DESC LIMIT %d", array_merge($allowed, [(int) $limit]));
        foreach ((array) $wpdb->get_results($sql) as $post) {
            $text = html_entity_decode(wp_strip_all_tags((string) $post->post_title . ' ' . (string) $post->post_excerpt . ' ' . (string) $post->post_content), ENT_QUOTES, 'UTF-8');
            foreach (array_unique($this->extract_hashtags($text)) as $tag) {
                $this->add_tag($tags, $tag, 'wp:' . (int) $post->ID);
            }
            $is_vote = $this->is_vote_row(['type' => $post->post_type, 'content_type' => ''], $text) || $this->wp_post_has_vote_meta((int) $post->ID);
            if ($is_vote) {
                $score = $this->wp_vote_score((int) $post->ID, $text);
                if ((int) ($score['total'] ?? 0) <= 0) {
                    $score = $this->vote_related_fallback_score(['ID' => $post->ID, 'post_type' => $post->post_type], $text);
                }
                $votes[] = [
                    'title' => $this->clean_title($post->post_title) ?: $this->vote_title_from_row(['title' => ''], $text) ?: 'โพสต์เกี่ยวกับโหวต',
                    'total' => $score['total'],
                    'plus' => $score['plus'],
                    'minus' => $score['minus'],
                    'related_only' => !empty($score['related_only']),
                    'source' => isset($score['source']) ? (string) $score['source'] : 'vote',
                    'url' => get_permalink((int) $post->ID) ? get_permalink((int) $post->ID) . '#comments' : home_url('/community/#comments'),
                    '_key' => 'wp:' . (int) $post->ID,
                ];
            }
        }
    }

    private function table_exists($table) {
        global $wpdb;
        if (empty($wpdb)) {
            return false;
        }
        return (string) $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) === (string) $table;
    }

    private function fcom_table($suffix) {
        global $wpdb;
        if (empty($wpdb)) {
            return '';
        }
        $candidate = $wpdb->prefix . $suffix;
        if ($this->table_exists($candidate)) {
            return $candidate;
        }
        $like = '%' . $wpdb->esc_like($suffix);
        $found = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $like));
        return $found ? (string) $found : '';
    }

    private function quote_table($table) {
        return '`' . str_replace('`', '``', (string) $table) . '`';
    }

    private function collect_from_fcom(&$tags, &$votes, $limit) {
        global $wpdb;
        if (empty($wpdb)) {
            return;
        }
        $posts_table = $this->fcom_table('fcom_posts');
        if ($posts_table === '') {
            return;
        }
        $sql = 'SELECT id,title,slug,message,message_rendered,type,content_type,meta,reactions_count,comments_count,status,created_at FROM ' . $this->quote_table($posts_table) . " WHERE (status IS NULL OR status IN ('published','active','publish')) ORDER BY created_at DESC LIMIT " . (int) $limit;
        $rows = $wpdb->get_results($sql, ARRAY_A);
        foreach ((array) $rows as $row) {
            $id = absint($row['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            $source_key = 'fcom:' . $id;
            $text = $this->row_text($row, ['title', 'message', 'message_rendered', 'meta']);
            foreach (array_unique($this->extract_hashtags($text)) as $tag) {
                $this->add_tag($tags, $tag, $source_key);
            }
            if ($this->is_vote_row($row, $text)) {
                $score = $this->fcom_vote_score($id, $row);
                if ((int) ($score['total'] ?? 0) <= 0) {
                    $score = $this->vote_related_fallback_score($row, $text);
                }
                $title = $this->vote_title_from_row($row, $text);
                $votes[] = [
                    'title' => $title !== '' ? $title : 'โพสต์เกี่ยวกับโหวต',
                    'total' => (int) $score['total'],
                    'plus' => (int) $score['plus'],
                    'minus' => (int) $score['minus'],
                    'related_only' => !empty($score['related_only']),
                    'source' => isset($score['source']) ? (string) $score['source'] : 'vote',
                    'url' => home_url('/community/?feed_id=' . $id . '#comments'),
                    '_key' => $source_key,
                ];
            }
        }
    }

    private function row_text($row, $keys) {
        $parts = [];
        foreach ($keys as $key) {
            if (!array_key_exists($key, $row)) {
                continue;
            }
            $val = $row[$key];
            if ($key === 'meta') {
                $parts[] = $this->mixed_to_text(maybe_unserialize($val), 5);
            } else {
                $parts[] = (string) $val;
            }
        }
        return html_entity_decode(wp_strip_all_tags(implode(' ', $parts)), ENT_QUOTES, 'UTF-8');
    }

    private function mixed_to_text($value, $depth = 4) {
        if ($depth <= 0 || $value === null) {
            return '';
        }
        if (is_scalar($value)) {
            return (string) $value;
        }
        if (is_array($value) || is_object($value)) {
            $out = [];
            foreach ((array) $value as $k => $v) {
                if (preg_match('/(color|style|css|image|avatar|url|link|file|path|nonce|token|key|secret|password|session|cache)/i', (string) $k)) {
                    continue;
                }
                $out[] = $this->mixed_to_text($v, $depth - 1);
            }
            return implode(' ', $out);
        }
        return '';
    }

    private function is_vote_row($row, $text) {
        $type = strtolower((string) ($row['type'] ?? '') . ' ' . (string) ($row['content_type'] ?? '') . ' ' . (string) ($row['post_type'] ?? '') . ' ' . (string) ($row['feed_type'] ?? '') . ' ' . (string) ($row['topic_type'] ?? '') . ' ' . (string) ($row['category'] ?? '') . ' ' . (string) ($row['kind'] ?? ''));
        if (preg_match('/(vote|poll|voting|โหวต|โหวด|โพล)/iu', $type)) {
            return true;
        }
        foreach ((array) $row as $key => $value) {
            $key_lc = strtolower((string) $key);
            if (preg_match('/(vote|votes|poll|upvote|downvote|plus_votes|minus_votes|positive_votes|negative_votes|vote_count|total_votes)/i', $key_lc) && $this->number_from_value($value) > 0) {
                return true;
            }
        }
        return (bool) preg_match('/(?:ประเภท|หัวข้อ|หมวดหมู่)\s*[:：]?\s*(โหวต|โหวด|vote|poll)|(?:#|＃)\s*(โหวต|โหวด|vote|poll)|โพสต์(?:เกี่ยวกับ)?โหว[ตด]|คะแนนโหว[ตด]|ผล\s*\+|ผล\s*-|\bvote\b|\bvoting\b|\bpoll\b|โพล|แบบสำรวจ|ลงคะแนน|เห็นด้วย|ไม่เห็นด้วย/iu', $text);
    }

    private function vote_related_fallback_score($row, $text) {
        // ใช้เมื่อตรวจพบว่าเป็นโพสต์เกี่ยวกับโหวต แต่ยังไม่มีตัวเลขโหวตจริง
        // รอบนี้ให้ดึงค่าความนิยมจากฟีดก่อน เช่น reaction/comment/like/share/view เพื่อให้กระแสโหวตมีลำดับที่สมเหตุผล
        $popular = $this->vote_popularity_score($row, $text);
        if ((int) ($popular['total'] ?? 0) > 0) {
            return ['total' => (int) $popular['total'], 'plus' => 0, 'minus' => 0, 'related_only' => true, 'source' => 'popular'];
        }
        return ['total' => 1, 'plus' => 0, 'minus' => 0, 'related_only' => true, 'source' => 'poll'];
    }

    private function vote_popularity_score($row, $text) {
        $total = 0;
        foreach ((array) $row as $key => $value) {
            $key_lc = strtolower((string) $key);
            if (preg_match('/(^id$|_id$|post_id|user_id|author_id|parent_id|object_id|poll_id|vote_id|color|style|css|image|width|height|order|sort)/i', $key_lc)) {
                continue;
            }
            if (!preg_match('/(reaction|like|comment|reply|share|view|popular|trend|score|count|total)/i', $key_lc)) {
                continue;
            }
            $n = $this->number_from_value($value);
            if ($n > 0) {
                $total += $n;
            }
        }
        if (preg_match_all('/(?:ยอดนิยม|นิยม|ความคิดเห็น|คอมเมนต์|ตอบกลับ|ถูกใจ|ไลก์|แชร์|ดู|views?|comments?|reactions?|likes?|shares?)\D{0,18}(\d{1,7})/iu', (string) $text, $m)) {
            foreach ((array) $m[1] as $n) {
                $total += max(0, (int) $n);
            }
        }
        return ['total' => max(0, (int) $total)];
    }

    private function fcom_vote_score($post_id, $row) {
        global $wpdb;
        $plus = 0;
        $minus = 0;
        $reactions_table = $this->fcom_table('fcom_post_reactions');
        if ($reactions_table !== '') {
            $results = $wpdb->get_results($wpdb->prepare('SELECT type, COUNT(*) AS c FROM ' . $this->quote_table($reactions_table) . " WHERE object_id=%d AND object_type IN ('feed','post','poll','vote') GROUP BY type", $post_id), ARRAY_A);
            foreach ((array) $results as $r) {
                $type = strtolower((string) ($r['type'] ?? ''));
                $count = max(0, (int) ($r['c'] ?? 0));
                if (preg_match('/(down|minus|negative|dislike|disagree|no|vote_down)/i', $type)) {
                    $minus += $count;
                } elseif (preg_match('/(up|plus|positive|like|love|agree|yes|vote_up|vote|poll)/i', $type)) {
                    $plus += $count;
                }
            }
        }
        $total = $plus + $minus;
        if ($total <= 0) {
            $total = max(0, (int) ($row['reactions_count'] ?? 0));
            $plus = $total;
        }
        return ['total' => $total, 'plus' => $plus, 'minus' => $minus];
    }

    private function vote_title_from_row($row, $text) {
        $title = $this->clean_title($row['title'] ?? '');
        if ($title !== '') {
            return $title;
        }
        $lines = preg_split('/[\r\n]+/u', wp_strip_all_tags((string) $text));
        foreach ((array) $lines as $line) {
            $line = $this->clean_title($line);
            if ($line !== '') {
                return $line;
            }
        }
        return '';
    }

    private function clean_title($text) {
        $text = html_entity_decode(wp_strip_all_tags((string) $text), ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/https?:\/\/\S+/iu', ' ', $text);
        $text = preg_replace('/(?:#|＃)[\p{L}\p{N}_\-]+/u', ' ', $text);
        $text = preg_replace('/^(หัวข้อ|เรื่อง|คำถาม|title|question)\s*[:：]?\s*/iu', '', $text);
        $text = trim(preg_replace('/\s+/u', ' ', $text), " \t\n\r\0\x0B-–—:：|/\\");
        if ($text === '' || function_exists('mb_strlen') && mb_strlen($text, 'UTF-8') < 3) {
            return '';
        }
        if (preg_match('/^(admin|ผู้ดูแล|สาธารณะ|วันนี้|ล่าสุด|โหวต|คะแนนโหวต|กระแสโหวต|โพสต์โหวต|โพสต์เกี่ยวกับโหวต|vote|votes|poll|score|ดูทั้งหมด|ดูเพิ่มเติม|แสดงความคิดเห็น|ความคิดเห็น|รูปภาพ|วิดีโอ|แท็ก)$/iu', $text)) {
            return '';
        }
        if (preg_match('/^ประเภท\s*[:：]?\s*(โหวต|พูดคุย|ทั่วไป|vote|poll)$/iu', $text)) {
            return '';
        }
        if (preg_match('/^\d+\s*(โหวต|คะแนน|โพสต์|ความคิดเห็น|แชร์|like|vote|votes)?$/iu', $text)) {
            return '';
        }
        return function_exists('mb_substr') ? mb_substr($text, 0, 180, 'UTF-8') : substr($text, 0, 180);
    }

    private function collect_from_wp_posts(&$tags, &$votes, $limit) {
        global $wpdb;
        if (empty($wpdb) || empty($wpdb->posts)) {
            return;
        }
        $excluded = ['page','attachment','revision','nav_menu_item','wp_navigation','custom_css','customize_changeset','oembed_cache','user_request','wp_block','wp_template','wp_template_part','wp_global_styles'];
        $ph = implode(',', array_fill(0, count($excluded), '%s'));
        $sql = $wpdb->prepare("SELECT ID,post_title,post_content,post_excerpt,post_type,comment_count FROM {$wpdb->posts} WHERE post_status='publish' AND post_type NOT IN ($ph) AND post_name<>%s ORDER BY post_date_gmt DESC LIMIT %d", array_merge($excluded, [self::AD_PAGE_SLUG, (int) $limit]));
        foreach ((array) $wpdb->get_results($sql) as $post) {
            $text = html_entity_decode(wp_strip_all_tags((string) $post->post_title . ' ' . (string) $post->post_excerpt . ' ' . (string) $post->post_content), ENT_QUOTES, 'UTF-8');
            $is_vote = $this->is_vote_row(['type' => $post->post_type, 'content_type' => ''], $text) || $this->wp_post_has_vote_meta((int) $post->ID);
            if (!$is_vote) {
                continue;
            }
            foreach (array_unique($this->extract_hashtags($text)) as $tag) {
                $this->add_tag($tags, $tag, 'wp:' . (int) $post->ID);
            }
            $score = $this->wp_vote_score((int) $post->ID, $text);
            if ((int) ($score['total'] ?? 0) <= 0) {
                $score = $this->vote_related_fallback_score(['ID' => $post->ID, 'post_type' => $post->post_type], $text);
            }
            $votes[] = [
                'title' => $this->clean_title($post->post_title) ?: $this->vote_title_from_row(['title' => ''], $text) ?: 'โพสต์เกี่ยวกับโหวต',
                'total' => $score['total'],
                'plus' => $score['plus'],
                'minus' => $score['minus'],
                'related_only' => !empty($score['related_only']),
                'source' => isset($score['source']) ? (string) $score['source'] : 'vote',
                'url' => get_permalink((int) $post->ID) ? get_permalink((int) $post->ID) . '#comments' : home_url('/community/#comments'),
                '_key' => 'wp:' . (int) $post->ID,
            ];
        }
    }

    private function wp_post_has_vote_meta($post_id) {
        foreach ((array) get_post_meta($post_id) as $key => $values) {
            if (preg_match('/(vote|poll|upvote|downvote|score|plus|minus|positive|negative)/i', (string) $key)) {
                return true;
            }
            foreach ((array) $values as $value) {
                if (is_string($value) && preg_match('/(?:ประเภท|หัวข้อ)\s*[:：]?\s*โหว[ตด]|\b(vote|voting|poll)\b|โพล|แบบสำรวจ|ลงคะแนน|เห็นด้วย|ไม่เห็นด้วย/iu', $value)) {
                    return true;
                }
            }
        }
        return false;
    }

    private function wp_vote_score($post_id, $text) {
        $plus = 0;
        $minus = 0;
        $total = 0;
        foreach ((array) get_post_meta($post_id) as $key => $values) {
            $key = (string) $key;
            if (preg_match('/(view|share|comment|cache|color|style|css)/i', $key)) {
                continue;
            }
            foreach ((array) $values as $value) {
                $n = $this->number_from_value($value);
                if ($n <= 0) {
                    continue;
                }
                if (preg_match('/(down|minus|negative|dislike|disagree|no)/i', $key)) {
                    $minus = max($minus, $n);
                } elseif (preg_match('/(up|plus|positive|agree|yes)/i', $key)) {
                    $plus = max($plus, $n);
                } elseif (preg_match('/(vote|votes|poll|score|total)/i', $key)) {
                    $total = max($total, $n);
                }
            }
        }
        if ($plus <= 0 && preg_match('/(?:ผล\s*\+|เห็นด้วย|โหวตบวก|บวก|up|plus|positive)\D{0,14}(\d{1,7})/iu', $text, $m)) {
            $plus = max($plus, (int) $m[1]);
        }
        if ($minus <= 0 && preg_match('/(?:ผล\s*-|ไม่เห็นด้วย|โหวตลบ|ลบ|down|minus|negative)\D{0,14}(\d{1,7})/iu', $text, $m)) {
            $minus = max($minus, (int) $m[1]);
        }
        if ($total <= 0 && preg_match('/(?:คะแนนรวม|รวม|ทั้งหมด|โหวต|คะแนน|vote|votes|poll)\D{0,14}(\d{1,7})/iu', $text, $m)) {
            $total = max($total, (int) $m[1]);
        }
        if ($total < $plus + $minus) {
            $total = $plus + $minus;
        }
        if ($total > 0 && $plus <= 0 && $minus <= 0) {
            $plus = $total;
        }
        return ['total' => (int) $total, 'plus' => (int) $plus, 'minus' => (int) $minus];
    }

    private function number_from_value($value) {
        if (is_numeric($value)) {
            return max(0, (int) $value);
        }
        $decoded = maybe_unserialize($value);
        if (is_array($decoded)) {
            return count($decoded);
        }
        if (is_string($value) && preg_match('/(\d{1,7})/u', str_replace(',', '', $value), $m)) {
            return max(0, (int) $m[1]);
        }
        return 0;
    }

    private function extract_hashtags($text) {
        $text = html_entity_decode((string) $text, ENT_QUOTES, 'UTF-8');
        $out = [];
        if (preg_match_all('/(?:^|[^\p{L}\p{N}_&])(?:#|＃)([\p{L}\p{N}_\-]{2,48})/u', $text, $m)) {
            foreach ($m[1] as $tag) {
                $tag = $this->normalize_tag($tag);
                if ($tag !== '') {
                    $out[] = $tag;
                }
            }
        }
        return $out;
    }

    private function normalize_tag($tag) {
        $tag = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', (string) $tag);
        $tag = trim($tag, " #＃\t\n\r\0\x0B.,;:!?()[]{}<>\"'“”‘’");
        if ($tag === '') {
            return '';
        }
        if (preg_match('/^[0-9a-f]{3}([0-9a-f]{3})?$/i', $tag)) {
            return '';
        }
        if (preg_match('/^(fff|ffffff|000|000000|f97316|1e6b45|111111|ad|ads|css|html|body|wordpress|backend|shortcode|plugin|settings|cache|api|community|feed|โพสต์)$/iu', $tag)) {
            return '';
        }
        return function_exists('mb_substr') ? mb_substr($tag, 0, 48, 'UTF-8') : substr($tag, 0, 48);
    }

    private function add_tag(&$tags, $tag, $source) {
        $tag = $this->normalize_tag($tag);
        if ($tag === '') {
            return;
        }
        $key = function_exists('mb_strtolower') ? mb_strtolower($tag, 'UTF-8') : strtolower($tag);
        if (!isset($tags[$key])) {
            $tags[$key] = ['tag' => $tag, 'count' => 0, 'sources' => []];
        }
        if (empty($tags[$key]['sources'][$source])) {
            $tags[$key]['sources'][$source] = true;
            $tags[$key]['count']++;
        }
    }

    private function finalize_tags($tags) {
        uasort($tags, function ($a, $b) {
            if ((int) $a['count'] === (int) $b['count']) {
                return strnatcasecmp((string) $a['tag'], (string) $b['tag']);
            }
            return (int) $b['count'] <=> (int) $a['count'];
        });
        $out = [];
        foreach ($tags as $item) {
            $tag = (string) $item['tag'];
            $out[] = ['tag' => $tag, 'count' => max(1, (int) $item['count']), 'url' => home_url('/community/?tag=' . rawurlencode($tag) . '#comments')];
            if (count($out) >= 5) {
                break;
            }
        }
        return $out;
    }

    private function finalize_votes($votes) {
        $map = [];
        foreach ((array) $votes as $vote) {
            if ((int) ($vote['total'] ?? 0) <= 0) {
                continue;
            }
            $key = !empty($vote['_key']) ? (string) $vote['_key'] : md5((string) $vote['title'] . '|' . (string) $vote['url']);
            if (!isset($map[$key]) || (int) $vote['total'] > (int) $map[$key]['total']) {
                $map[$key] = $vote;
            }
        }
        $items = array_values($map);
        usort($items, function ($a, $b) {
            if ((int) $a['total'] === (int) $b['total']) {
                return strnatcasecmp((string) $a['title'], (string) $b['title']);
            }
            return (int) $b['total'] <=> (int) $a['total'];
        });
        $out = [];
        foreach ($items as $item) {
            $out[] = [
                'title' => $item['title'],
                'total' => max(0, (int) $item['total']),
                'plus' => max(0, (int) $item['plus']),
                'minus' => max(0, (int) $item['minus']),
                'related_only' => !empty($item['related_only']),
                'source' => isset($item['source']) ? (string) $item['source'] : 'vote',
                'url' => $item['url'],
            ];
            if (count($out) >= 5) {
                break;
            }
        }
        return $out;
    }

    public function ensure_advertise_page() {
        if (!function_exists('get_page_by_path') || !function_exists('wp_insert_post')) {
            return;
        }
        $existing = get_page_by_path(self::AD_PAGE_SLUG, OBJECT, 'page');
        $content = '[thinkb4do_advertise_contact]';
        if (!$existing) {
            wp_insert_post([
                'post_title' => 'ติดต่อโฆษณา',
                'post_name' => self::AD_PAGE_SLUG,
                'post_type' => 'page',
                'post_status' => 'publish',
                'post_content' => $content,
            ]);
        }
    }

    public function maybe_advertise_content($content) {
        if (is_page(self::AD_PAGE_SLUG)) {
            return $this->advertise_contact_shortcode([]);
        }
        return $content;
    }

    public function advertise_contact_shortcode($atts = []) {
        ob_start();
        ?>
        <section class="tcb4d-ad-contact" style="max-width:760px;margin:40px auto;padding:28px;border-radius:24px;background:#fff;border:1px solid #E6EEE8;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen-Sans,Ubuntu,Cantarell,'Helvetica Neue',Arial,'Noto Sans Thai','Kanit',sans-serif;">
            <p style="margin:0 0 8px;color:#1E6B45;font-weight:900;">ได้รับการสนับสนุน</p>
            <h1 style="margin:0 0 12px;font-size:32px;line-height:1.2;color:#111;">ติดต่อโฆษณา</h1>
            <p style="margin:0 0 18px;color:#475467;line-height:1.8;">พื้นที่สำหรับแบรนด์หรือผู้สนับสนุนที่ต้องการสื่อสารกับชุมชน Thinkb4do อย่างเหมาะสม</p>
            <a href="<?php echo esc_url(home_url('/contact/')); ?>" style="display:inline-flex;align-items:center;justify-content:center;padding:12px 18px;border-radius:999px;background:#1E6B45;color:#fff;text-decoration:none;font-weight:900;">ติดต่อทีมงาน</a>
        </section>
        <?php
        return (string) ob_get_clean();
    }
}

register_activation_hook(__FILE__, ['ThinkB4Do_Community_Right_Sidebar_Desktop', 'activate']);
ThinkB4Do_Community_Right_Sidebar_Desktop::instance();
