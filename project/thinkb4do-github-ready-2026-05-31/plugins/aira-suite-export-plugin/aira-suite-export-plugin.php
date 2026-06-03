<?php
/**
 * Plugin Name: AiRA Suite Export Plugin
 * Plugin URI: https://thinkb4do.com
 * Description: Export one or multiple installed plugins from wp-content/plugins and themes from wp-content/themes into ZIP files from the WordPress admin area, plus combined plugin/theme package bundles with manifest and export log. Includes responsive cross-browser admin UI and ZipArchive/PclZip fallback support. Adds Smart Chat Live Link so this plugin can answer Smart Chat as its own specialist department. Adds Knowledge Center Live Link so this plugin can share safe context with the AiRA knowledge library and search it for future work. Full Ecosystem Repair Link: เชื่อม Smart Chat, Knowledge, Product, Repair, Scrap, Master Sync และ API Hub แบบ Manual Safe Mode. Adds AiRA System Monitor to show installed plugins, current role, active/inactive state, safe manual open/close controls, and status filters.
 * Version: 1.5.0
 * Author: Thinkb4do
 * Author URI: https://thinkb4do.com
 * Text Domain: aira-suite-export-plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

define('AIRA_EXPORT_PLUGIN_VERSION', '1.5.0');
define('AIRA_EXPORT_PLUGIN_FILE', __FILE__);
define('AIRA_EXPORT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AIRA_EXPORT_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once AIRA_EXPORT_PLUGIN_DIR . 'includes/class-aira-export-plugin.php';
require_once AIRA_EXPORT_PLUGIN_DIR . 'includes/class-aira-export-builder.php';

function aira_export_plugin_bootstrap() {
    $plugin = new AiRA_Export_Plugin();
    $plugin->init();

    $builder = new AiRA_Export_Builder();
    $builder->init();
}

aira_export_plugin_bootstrap();


/* AiRA Safe Restore Repair Layer - registry only, no API sync. */
if (!function_exists('aira_export_plugin_safe_repair_register_network')) {
    function aira_export_plugin_safe_repair_register_network() {
        $registry = get_option('aira_suite_network_registry', array());
        if (!is_array($registry)) $registry = array();
        $registry['aira_suite_export_plugin'] = array('name'=>'AiRA Suite Export Plugin','version'=>AIRA_EXPORT_PLUGIN_VERSION,'type'=>'export_tool','status'=>'connected','updated_at'=>current_time('mysql'));
        update_option('aira_suite_network_registry', $registry, false);
    }
}
register_activation_hook(__FILE__, 'aira_export_plugin_safe_repair_register_network');


/* === AiRA Smart Chat Live Link v1 === */
if (!function_exists('aira_smart_chat_live_link_export_profile')) {
    function aira_smart_chat_live_link_export_profile() {
        $profile = array('slug' => 'aira-suite-export-plugin', 'name' => 'AiRA Suite Export Plugin', 'short' => 'Export', 'avatar' => '⇩', 'department' => 'ฝ่ายแพ็กไฟล์และส่งออก', 'role' => 'ZIP Export / Package Check', 'specialty' => 'ตรวจแพ็กเกจ ZIP โครงสร้างไฟล์ manifest และเตรียม export หลังผู้ใช้ยืนยัน', 'responsibilities' => array('ตรวจโครงสร้างแพ็ก', 'ช่วย export ZIP', 'สรุปไฟล์ในแพ็ก', 'Export Plugin ZIP', 'Export Theme ZIP', 'Export Plugin + Theme Bundle ZIP', 'ดูสถานะปลั๊กอินที่ติดตั้ง/เปิด/ปิด', 'ไม่ลบหรือเขียนทับไฟล์เอง'), 'data_sources' => array('aira_suite_export_status', 'aira_suite_network_registry', '/wp-json/aira-suite-export/v1/plugin-monitor', 'wp-content/plugins', 'wp-content/themes', '/wp-json/aira-suite-export/v1/builder-status'), 'talks_to' => array('Command', 'Product', 'Bridge'), 'handoff_actions' => array('ให้ Smart Chat ขอ package checklist', 'รับงานจาก Product/Command เพื่อเตรียม ZIP', 'รายงานความพร้อมก่อนดาวน์โหลด', 'เปิดหน้า Monitor เพื่อตรวจว่าอะไรติดตั้งและเปิดอยู่'), 'live_link' => true, 'active' => true, 'source' => 'aira-suite-export-plugin', 'version' => AIRA_EXPORT_PLUGIN_VERSION);
        $profile['snapshot'] = aira_smart_chat_live_link_export_snapshot();
        return $profile;
    }
}

if (!function_exists('aira_smart_chat_live_link_export_snapshot')) {
    function aira_smart_chat_live_link_export_snapshot() {
        $snapshot = array();
        $sources = array('aira_suite_export_status', 'aira_suite_network_registry');
        foreach ($sources as $option) {
            $value = get_option($option, null);
            if ($value === null || $value === false) {
                $snapshot[$option] = 'not_found';
            } elseif (is_array($value)) {
                $snapshot[$option] = 'array_items_' . count($value);
            } elseif (is_string($value)) {
                $sensitive = (stripos($option, 'key') !== false || stripos($option, 'token') !== false || stripos($option, 'secret') !== false);
                $snapshot[$option] = $sensitive ? 'secret_present_len_' . strlen($value) : 'string_len_' . strlen($value);
            } else {
                $snapshot[$option] = gettype($value);
            }
        }
        return $snapshot;
    }
}

if (!function_exists('aira_smart_chat_live_link_export_matches')) {
    function aira_smart_chat_live_link_export_matches($agent, $prompt = '') {
        $prompt = strtolower((string) $prompt);
        $needles = array('aira-suite-export-plugin', '@export', 'export', strtolower('AiRA Suite Export Plugin'));
        if (is_array($agent)) {
            $agent_slug = isset($agent['slug']) ? strtolower((string) $agent['slug']) : '';
            $agent_short = isset($agent['short']) ? strtolower((string) $agent['short']) : '';
            if ($agent_slug === 'aira-suite-export-plugin' || $agent_short === 'export') { return true; }
        }
        foreach ($needles as $needle) {
            if ($needle !== '' && strpos($prompt, $needle) !== false) { return true; }
        }
        return false;
    }
}

add_filter('aira_smart_chat_agent_profiles', function($profiles) {
    if (!is_array($profiles)) { $profiles = array(); }
    $profiles['export'] = aira_smart_chat_live_link_export_profile();
    return $profiles;
}, 20, 1);

add_filter('aira_smart_chat_agent_response', function($response, $agent, $analysis, $prompt) {
    if (!aira_smart_chat_live_link_export_matches($agent, $prompt)) { return $response; }
    $profile = aira_smart_chat_live_link_export_profile();
    $lines = array('ผมคือ AiRA Suite Export Plugin — ฝ่ายแพ็กไฟล์และส่งออก', 'หน้าที่หลัก: ตรวจแพ็กเกจ ZIP โครงสร้างไฟล์ manifest และเตรียม export หลังผู้ใช้ยืนยัน', 'งานที่ช่วย Smart Chat ได้: ตรวจโครงสร้างแพ็ก / ช่วย export Plugin ZIP / ช่วย export Theme ZIP / ช่วย export Plugin + Theme Bundle ZIP / สรุปไฟล์ในแพ็ก / ดูสถานะปลั๊กอินติดตั้ง-เปิด-ปิด / ไม่ลบหรือเขียนทับไฟล์เอง', 'การประสานงานที่เหมาะสม: @Command / @Product / @Bridge', 'Endpoint Monitor: /wp-json/aira-suite-export/v1/plugin-monitor', 'โหมดปลอดภัย: ตอบสถานะและคำแนะนำ ไม่ติดตั้ง ไม่ลบ และไม่เขียนทับไฟล์จริงเอง');
    $snapshot = isset($profile['snapshot']) ? $profile['snapshot'] : array();
    if (!empty($snapshot)) {
        $parts = array();
        foreach ($snapshot as $option => $state) { $parts[] = $option . '=' . $state; }
        $lines[] = 'สถานะข้อมูลที่อ่านได้แบบปลอดภัย: ' . implode(' | ', $parts);
    }
    if (is_array($analysis) && !empty($analysis['intent'])) {
        $lines[] = 'เจตนาคำสั่งที่ Smart Chat ตรวจพบ: ' . implode(', ', (array) $analysis['intent']);
    }
    return array(
        'handled_by' => 'aira-suite-export-plugin',
        'title' => 'AiRA Suite Export Plugin',
        'avatar' => '⇩',
        'department' => 'ฝ่ายแพ็กไฟล์และส่งออก',
        'lines' => $lines,
        'actions' => array('ให้ Smart Chat ขอ package checklist', 'รับงานจาก Product/Command เพื่อเตรียม ZIP', 'รายงานความพร้อมก่อนดาวน์โหลด'),
        'profile' => $profile,
        'safe_mode' => true,
        'live_link' => true,
    );
}, 20, 4);

add_action('rest_api_init', function() {
    register_rest_route('aira-smart-chat-live/v1', '/export', array(
        'methods' => 'GET',
        'permission_callback' => function() { return current_user_can('manage_options'); },
        'callback' => function() {
            return rest_ensure_response(array(
                'ok' => true,
                'agent' => aira_smart_chat_live_link_export_profile(),
                'message' => 'Smart Chat live link is ready for AiRA Suite Export Plugin.',
            ));
        },
    ));
});

if (!function_exists('aira_smart_chat_live_link_export_activate')) {
    function aira_smart_chat_live_link_export_activate() {
        $agents = get_option('aira_smart_chat_live_agents', array());
        if (!is_array($agents)) { $agents = array(); }
        $agents['export'] = aira_smart_chat_live_link_export_profile();
        update_option('aira_smart_chat_live_agents', $agents, false);
    }
}
register_activation_hook(__FILE__, 'aira_smart_chat_live_link_export_activate');


/* AiRA Knowledge Center Live Link Layer v1.0.0
 * Safe Mode: no auto install, no file overwrite, no heavy sync on page load.
 */
if (!function_exists('aira_export_plugin_kc_safe_flatten')) {
    function aira_export_plugin_kc_safe_flatten($data, $depth = 0) {
        if ($depth > 2) return '';
        if (is_scalar($data) || $data === null) return (string) $data;
        if (!is_array($data)) return '';
        $parts = array();
        $i = 0;
        foreach ($data as $key => $value) {
            if ($i >= 18) { $parts[] = '...'; break; }
            $key_text = sanitize_text_field((string) $key);
            if (preg_match('/api[_-]?key|token|secret|password|nonce|credential|authorization/i', $key_text)) {
                $parts[] = $key_text . ': [ซ่อนข้อมูลลับ]';
                $i++;
                continue;
            }
            if (is_array($value)) {
                $nested = aira_export_plugin_kc_safe_flatten($value, $depth + 1);
                if ($nested !== '') $parts[] = $key_text . ': ' . $nested;
            } elseif (is_scalar($value)) {
                $v = wp_strip_all_tags((string) $value);
                if (function_exists('mb_substr')) $v = mb_substr($v, 0, 240); else $v = substr($v, 0, 240);
                if ($v !== '') $parts[] = $key_text . ': ' . $v;
            }
            $i++;
        }
        return implode("
", array_filter($parts));
    }
}

if (!function_exists('aira_export_plugin_kc_safe_option_report')) {
    function aira_export_plugin_kc_safe_option_report() {
        $option_names = array('aira_export_plugin_history', 'aira_suite_export_plugin_logs', 'aira_suite_export_plugin_status', 'aira_suite_network_registry');
        $lines = array();
        foreach ($option_names as $option_name) {
            $value = get_option($option_name, null);
            if ($value === null || $value === false || $value === '' || $value === array()) {
                $lines[] = $option_name . ': ยังไม่มีข้อมูล';
                continue;
            }
            if (is_array($value)) {
                $lines[] = $option_name . ': พบข้อมูล ' . count($value) . ' รายการ';
                $sample = aira_export_plugin_kc_safe_flatten(array_slice($value, 0, 3, true));
                if ($sample !== '') $lines[] = $sample;
            } else {
                $lines[] = $option_name . ': ' . aira_export_plugin_kc_safe_flatten($value);
            }
        }
        return implode("
", array_filter($lines));
    }
}

if (!function_exists('aira_export_plugin_kc_source_payload')) {
    function aira_export_plugin_kc_source_payload() {
        $now = current_time('mysql');
        $content = "ฝ่าย: AiRA Suite Export Plugin
";
        $content .= "หน้าที่: ฝ่ายแพ็กไฟล์และดาวน์โหลด ZIP ตรวจสิ่งที่ export ได้และส่งข้อมูลแพ็กเข้าคลังความรู้.
";
        $content .= "ความสามารถ: " . implode(', ', array('Export ZIP', 'ตรวจไฟล์แพ็ก', 'ส่งข้อมูลแพ็กให้ Knowledge Center', 'ค้นความรู้ก่อนส่งออก')) . "

";
        $content .= "สถานะข้อมูลที่อ่านได้แบบปลอดภัย:
" . aira_export_plugin_kc_safe_option_report();
        return array(
            'source_slug' => 'export',
            'source_name' => 'AiRA Suite Export Plugin',
            'plugin' => 'AiRA Suite Export Plugin',
            'updated_at' => $now,
            'entries' => array(
                array(
                    'id' => 'export-profile',
                    'room_id' => 'system-build-history',
                    'title' => 'AiRA Suite Export Plugin — ข้อมูลเฉพาะทางสำหรับ Knowledge Center',
                    'summary' => 'ฝ่ายแพ็กไฟล์และดาวน์โหลด ZIP ตรวจสิ่งที่ export ได้และส่งข้อมูลแพ็กเข้าคลังความรู้.',
                    'content' => $content,
                    'type' => 'tool',
                    'category' => 'export',
                    'tags' => array('AiRA', 'Knowledge Link', 'AiRA Suite Export Plugin', 'export'),
                    'source' => 'export',
                ),
            ),
        );
    }
}

if (!function_exists('aira_export_plugin_kc_register_source_payload')) {
    function aira_export_plugin_kc_register_source_payload($payloads, $args = array()) {
        if (!is_array($payloads)) $payloads = array();
        $payloads['export'] = aira_export_plugin_kc_source_payload();
        return $payloads;
    }
}
add_filter('aira_knowledge_center_source_payloads', 'aira_export_plugin_kc_register_source_payload', 10, 2);

if (!function_exists('aira_export_plugin_kc_refresh_source_option')) {
    function aira_export_plugin_kc_refresh_source_option() {
        if (!current_user_can('manage_options')) return;
        $payload = aira_export_plugin_kc_source_payload();
        $option_name = 'aira_kc_source_export';
        $hash_name = $option_name . '_hash';
        $hash = md5(wp_json_encode($payload));
        if (get_option($hash_name, '') !== $hash) {
            update_option($option_name, $payload, false);
            update_option($hash_name, $hash, false);
        }
    }
}
add_action('admin_init', 'aira_export_plugin_kc_refresh_source_option', 30);

if (!function_exists('aira_export_plugin_kc_query')) {
    function aira_export_plugin_kc_query($query = '', $limit = 6) {
        $args = array('query' => sanitize_text_field((string) $query), 'limit' => absint($limit));
        if (function_exists('aira_kc_query_knowledge')) {
            return aira_kc_query_knowledge($args);
        }
        return apply_filters('aira_knowledge_center_query', array(), $args);
    }
}

if (!function_exists('aira_export_plugin_kc_add_note')) {
    function aira_export_plugin_kc_add_note($title, $content, $room_id = 'system-build-history') {
        $item = array(
            'room_id' => sanitize_key($room_id),
            'title' => sanitize_text_field($title),
            'content' => wp_kses_post($content),
            'summary' => sanitize_text_field($title),
            'type' => 'tool',
            'category' => 'export',
            'tags' => array('AiRA', 'AiRA Suite Export Plugin', 'Knowledge Link'),
            'source' => 'export',
        );
        if (function_exists('aira_kc_add_knowledge')) {
            return aira_kc_add_knowledge($item);
        }
        return new WP_Error('aira_kc_unavailable', 'AiRA Knowledge Center ยังไม่พร้อมใช้งาน');
    }
}

add_action('rest_api_init', function() {
    register_rest_route('aira-knowledge-link/v1', '/export', array(
        'methods' => 'GET',
        'callback' => function($request) {
            return rest_ensure_response(array(
                'status' => 'connected',
                'plugin' => 'AiRA Suite Export Plugin',
                'source' => aira_export_plugin_kc_source_payload(),
                'knowledge_center' => function_exists('aira_kc_query_knowledge') ? 'available' : 'not_active',
                'safe_mode' => true,
            ));
        },
        'permission_callback' => function() { return current_user_can('manage_options'); },
    ));
});


/**
 * AiRA Full Ecosystem Repair Link
 * Added by Thinkb4do repair pack. Safe/manual integration only.
 */
if (!function_exists('aira_fr_export_meta')) {
    function aira_fr_export_meta() {
        return array(
            'slug' => 'aira-suite-export-plugin',
            'key' => 'export',
            'name' => 'AiRA Suite Export Plugin',
            'agent' => 'Export Plugin',
            'icon' => '⇩',
            'version' => AIRA_EXPORT_PLUGIN_VERSION,
            'role' => 'ฝ่ายแพ็ก ZIP และส่งออก',
            'summary' => 'ตรวจไฟล์ปลั๊กอิน เตรียม ZIP/Export ดูสถานะปลั๊กอินที่ติดตั้งอยู่ และควบคุมเปิด/ปิดแบบ Manual Safe Mode',
            'api_options' => array('aira_suite_export_api_hub','aira_suite_api_hub'),
            'aliases' => array('export_plugin','export','suite_export_plugin'),
            'safe_mode' => 1,
        );
    }
}

if (!function_exists('aira_fr_export_mask')) {
    function aira_fr_export_mask($value) {
        if (is_array($value)) {
            $out = array();
            foreach ($value as $k => $v) {
                $key = strtolower((string) $k);
                if (strpos($key, 'key') !== false || strpos($key, 'token') !== false || strpos($key, 'secret') !== false || strpos($key, 'password') !== false) {
                    $out[$k] = is_scalar($v) && !empty($v) ? '••••••••' : '';
                } else {
                    $out[$k] = aira_fr_export_mask($v);
                }
            }
            return $out;
        }
        return is_scalar($value) ? sanitize_text_field((string) $value) : '';
    }
}

if (!function_exists('aira_fr_export_snapshot')) {
    function aira_fr_export_snapshot() {
        $meta = aira_fr_export_meta();
        $api_hub = get_option('aira_suite_api_hub', array());
        $registry = get_option('aira_suite_network_registry', array());
        return array(
            'meta' => $meta,
            'status' => 'connected',
            'api_hub_present' => is_array($api_hub) && !empty($api_hub) ? 1 : 0,
            'registry_present' => is_array($registry) ? 1 : 0,
            'checked_at' => current_time('mysql'),
        );
    }
}

if (!function_exists('aira_fr_export_register_master_source')) {
    function aira_fr_export_register_master_source($sources) {
        if (!is_array($sources)) { $sources = array(); }
        $meta = aira_fr_export_meta();
        $sources[$meta['slug']] = array(
            'name' => $meta['name'],
            'version' => $meta['version'],
            'role' => $meta['role'],
            'summary' => $meta['summary'],
            'api_options' => $meta['api_options'],
            'status' => 'connected',
            'safe_mode' => 1,
        );
        return $sources;
    }
    add_filter('aira_master_sync_plugin_sources', 'aira_fr_export_register_master_source', 10, 1);
    add_filter('aira_master_sync_api_targets', 'aira_fr_export_register_master_source', 10, 1);
}

if (!function_exists('aira_fr_export_smart_profiles')) {
    function aira_fr_export_smart_profiles($profiles) {
        if (!is_array($profiles)) { $profiles = array(); }
        $meta = aira_fr_export_meta();
        $profiles[$meta['key']] = array(
            'slug' => $meta['slug'],
            'name' => $meta['agent'],
            'plugin' => $meta['name'],
            'icon' => $meta['icon'],
            'role' => $meta['role'],
            'summary' => $meta['summary'],
            'aliases' => $meta['aliases'],
            'status' => 'available',
        );
        return $profiles;
    }
    add_filter('aira_smart_chat_agent_profiles', 'aira_fr_export_smart_profiles', 10, 1);
}

if (!function_exists('aira_fr_export_smart_response')) {
    function aira_fr_export_smart_response($response, $target = '', $message = '') {
        $meta = aira_fr_export_meta();
        $target_key = strtolower(trim((string) $target));
        $aliases = array_map('strtolower', $meta['aliases']);
        if ($target_key !== '' && !in_array($target_key, $aliases, true) && $target_key !== strtolower($meta['key']) && $target_key !== strtolower($meta['slug'])) {
            return $response;
        }
        $text = trim((string) $message);
        return array(
            'agent' => $meta['agent'],
            'plugin' => $meta['name'],
            'icon' => $meta['icon'],
            'summary' => $meta['summary'],
            'answer' => 'รับเรื่องแล้วครับ — ฝ่าย ' . $meta['agent'] . ' จะดูเฉพาะงานของตัวเอง: ' . $meta['summary'] . ($text !== '' ? ' | คำถาม: ' . sanitize_text_field($text) : ''),
            'next_steps' => array(
                'ตรวจสถานะของปลั๊กอินนี้',
                'ดึงข้อมูลที่เกี่ยวข้องจาก Knowledge Center',
                'ส่งต่อ Repair/Product/Master Sync เฉพาะเมื่อจำเป็น',
            ),
            'safe_mode' => 1,
        );
    }
    add_filter('aira_smart_chat_agent_response', 'aira_fr_export_smart_response', 10, 3);
}

if (!function_exists('aira_fr_export_knowledge_source')) {
    function aira_fr_export_knowledge_source($sources) {
        if (!is_array($sources)) { $sources = array(); }
        $meta = aira_fr_export_meta();
        $sources[$meta['slug']] = array(
            'title' => $meta['name'],
            'type' => 'plugin_capability',
            'category' => 'AiRA Ecosystem',
            'content' => $meta['role'] . ' — ' . $meta['summary'],
            'tags' => array($meta['key'], 'aira', 'repair-linked', 'safe-mode'),
            'updated_at' => current_time('mysql'),
        );
        return $sources;
    }
    add_filter('aira_knowledge_center_live_sources', 'aira_fr_export_knowledge_source', 10, 1);
    add_filter('aira_knowledge_live_sources', 'aira_fr_export_knowledge_source', 10, 1);
}

if (!function_exists('aira_fr_export_ecosystem_source')) {
    function aira_fr_export_ecosystem_source($sources) {
        if (!is_array($sources)) { $sources = array(); }
        $meta = aira_fr_export_meta();
        $sources[$meta['slug']] = array(
            'name' => $meta['name'],
            'agent' => $meta['agent'],
            'role' => $meta['role'],
            'summary' => $meta['summary'],
            'status' => 'connected',
            'safe_mode' => 1,
        );
        return $sources;
    }
    add_filter('aira_product_store_ecosystem_sources', 'aira_fr_export_ecosystem_source', 10, 1);
    add_filter('aira_code_repair_ecosystem_sources', 'aira_fr_export_ecosystem_source', 10, 1);
    add_filter('aira_scrap_yard_ecosystem_sources', 'aira_fr_export_ecosystem_source', 10, 1);
}

if (!function_exists('aira_fr_export_rest_permission')) {
    function aira_fr_export_rest_permission() {
        return current_user_can('manage_options');
    }
}

if (!function_exists('aira_fr_export_rest_status')) {
    function aira_fr_export_rest_status($request = null) {
        return rest_ensure_response(aira_fr_export_snapshot());
    }
}

if (!function_exists('aira_fr_export_register_rest')) {
    function aira_fr_export_register_rest() {
        $meta = aira_fr_export_meta();
        register_rest_route('aira-full-repair/v1', '/' . $meta['key'], array(
            'methods' => 'GET',
            'callback' => 'aira_fr_export_rest_status',
            'permission_callback' => 'aira_fr_export_rest_permission',
        ));
    }
    add_action('rest_api_init', 'aira_fr_export_register_rest');
}

