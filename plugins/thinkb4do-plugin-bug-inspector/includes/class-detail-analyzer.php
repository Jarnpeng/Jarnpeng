<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TBPBI_Detail_Analyzer {
    /**
     * วิเคราะห์รายละเอียดเชิงลึกแบบ Static: security, performance, lifecycle, data-flow, assets และมาตรฐาน WordPress
     *
     * @param array  $files absolute file paths.
     * @param string $root_path plugin root path.
     * @return array
     */
    public function analyze( array $files, string $root_path ): array {
        $root_path = wp_normalize_path( trailingslashit( $root_path ) );
        $data      = $this->collect_inventory( $files, $root_path );
        $issues    = array();

        $issues = array_merge( $issues, $this->check_lifecycle( $data, $root_path ) );
        $issues = array_merge( $issues, $this->check_php_file_guards( $data ) );
        $issues = array_merge( $issues, $this->check_security_data_flow( $data ) );
        $issues = array_merge( $issues, $this->check_database_safety( $data ) );
        $issues = array_merge( $issues, $this->check_remote_requests( $data ) );
        $issues = array_merge( $issues, $this->check_assets_detail( $data, $root_path ) );
        $issues = array_merge( $issues, $this->check_i18n( $data ) );
        $issues = array_merge( $issues, $this->check_file_size_and_complexity( $data ) );
        $issues = array_merge( $issues, $this->check_wordpress_admin_ui_risks( $data ) );

        $area_counts = $this->count_by_area( $issues );
        $summary     = array(
            'php_files'             => count( $data['php_files'] ),
            'js_files'              => count( $data['js_files'] ),
            'css_files'             => count( $data['css_files'] ),
            'json_files'            => count( $data['json_files'] ),
            'text_files'            => count( $data['text_files'] ),
            'largest_file'          => $data['largest_file'],
            'total_lines'           => $data['total_lines'],
            'request_touchpoints'   => $data['request_touchpoints'],
            'output_touchpoints'    => $data['output_touchpoints'],
            'db_touchpoints'        => $data['db_touchpoints'],
            'remote_touchpoints'    => $data['remote_touchpoints'],
            'asset_touchpoints'     => $data['asset_touchpoints'],
            'admin_touchpoints'     => $data['admin_touchpoints'],
            'area_counts'           => $area_counts,
            'detail_score'          => $this->calculate_detail_score( $issues ),
        );

        return array(
            'summary'   => $summary,
            'issues'    => $issues,
            'inventory' => $data['inventory'],
            'fix_queue' => $this->build_fix_queue( $issues ),
        );
    }

    private function collect_inventory( array $files, string $root_path ): array {
        $data = array(
            'inventory'           => array(),
            'php_files'           => array(),
            'js_files'            => array(),
            'css_files'           => array(),
            'json_files'          => array(),
            'text_files'          => array(),
            'file_content'        => array(),
            'largest_file'        => array( 'file' => '-', 'size_bytes' => 0, 'lines' => 0 ),
            'total_lines'         => 0,
            'request_touchpoints' => 0,
            'output_touchpoints'  => 0,
            'db_touchpoints'      => 0,
            'remote_touchpoints'  => 0,
            'asset_touchpoints'   => 0,
            'admin_touchpoints'   => 0,
        );

        foreach ( $files as $file ) {
            $file = wp_normalize_path( $file );
            if ( ! is_file( $file ) ) {
                continue;
            }

            $rel      = $this->relative_path( $file, $root_path );
            $ext      = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
            $size     = (int) filesize( $file );
            $content  = file_get_contents( $file );
            $content  = false === $content ? '' : $content;
            $code_content = $this->code_without_strings_and_comments( $content );
            $lines    = '' === $content ? 0 : substr_count( $content, "\n" ) + 1;
            $checksum = function_exists( 'hash_file' ) ? (string) hash_file( 'sha256', $file ) : '';

            $row = array(
                'file'       => $rel,
                'extension'  => $ext,
                'size_bytes' => $size,
                'lines'      => $lines,
                'sha256'     => $checksum,
            );
            $data['inventory'][] = $row;
            $data['file_content'][ $rel ] = $content;
            $data['total_lines'] += $lines;

            if ( $size > (int) $data['largest_file']['size_bytes'] ) {
                $data['largest_file'] = array( 'file' => $rel, 'size_bytes' => $size, 'lines' => $lines );
            }

            if ( 'php' === $ext ) {
                $data['php_files'][ $rel ] = $row;
                $data['request_touchpoints'] += preg_match_all( '/\$_(POST|GET|REQUEST|COOKIE|FILES)\s*\[/i', $code_content, $m );
                $data['output_touchpoints']  += preg_match_all( '/\b(echo|print|printf|wp_send_json|wp_send_json_success|wp_send_json_error)\b/i', $code_content, $m );
                $data['db_touchpoints']      += preg_match_all( '/\$wpdb->(query|get_results|get_row|get_var|get_col|insert|update|delete)\s*\(/i', $content, $m );
                $data['remote_touchpoints']  += preg_match_all( '/wp_remote_(get|post|request)|curl_exec|file_get_contents\s*\(\s*[\'\"]https?:\/\//i', $content, $m );
                $data['asset_touchpoints']   += preg_match_all( '/wp_(enqueue|register)_(script|style)\s*\(/i', $content, $m );
                $data['admin_touchpoints']   += preg_match_all( '/add_(menu|submenu)_page|admin_init|admin_menu|admin_enqueue_scripts|manage_options/i', $content, $m );
            } elseif ( 'js' === $ext ) {
                $data['js_files'][ $rel ] = $row;
            } elseif ( 'css' === $ext ) {
                $data['css_files'][ $rel ] = $row;
            } elseif ( 'json' === $ext ) {
                $data['json_files'][ $rel ] = $row;
            } elseif ( in_array( $ext, array( 'txt', 'md' ), true ) ) {
                $data['text_files'][ $rel ] = $row;
            }
        }

        return $data;
    }

    private function check_lifecycle( array $data, string $root_path ): array {
        $issues = array();
        $combined = implode( "\n", $data['file_content'] );
        $main_file = $this->detect_main_file( $data );

        if ( false === strpos( $combined, 'register_activation_hook' ) ) {
            $issues[] = $this->issue( 'notice', 'lifecycle_missing_activation_hook', 'ยังไม่พบ register_activation_hook', 'ถ้าปลั๊กอินต้องสร้าง option/table/cache ตอนติดตั้ง ควรมี activation hook เพื่อเตรียมระบบให้พร้อม', $main_file, 1, 'lifecycle', 'เพิ่ม register_activation_hook() ในไฟล์หลัก หรือยืนยันว่าไม่ต้องมี setup ตอน activate', 'medium', 72 );
        }

        if ( false === strpos( $combined, 'register_deactivation_hook' ) ) {
            $issues[] = $this->issue( 'notice', 'lifecycle_missing_deactivation_hook', 'ยังไม่พบ register_deactivation_hook', 'ถ้าปลั๊กอินมี cron/transient/session ชั่วคราว ควรเคลียร์ตอน deactivation', $main_file, 1, 'lifecycle', 'เพิ่ม deactivation hook เพื่อเคลียร์ scheduled events หรือ transient ที่ไม่จำเป็น', 'low', 70 );
        }

        if ( ! file_exists( $root_path . 'uninstall.php' ) && false === strpos( $combined, 'register_uninstall_hook' ) ) {
            $issues[] = $this->issue( 'notice', 'lifecycle_missing_uninstall_cleanup', 'ยังไม่พบระบบ cleanup ตอน uninstall', 'ถ้าปลั๊กอินสร้างข้อมูลถาวร ควรมี uninstall.php หรือ register_uninstall_hook เพื่อให้ผู้ใช้ลบข้อมูลได้สะอาด', $main_file, 1, 'lifecycle', 'สร้าง uninstall.php พร้อมตรวจ defined( WP_UNINSTALL_PLUGIN ) ก่อนลบ option/table', 'medium', 80 );
        }

        if ( false === strpos( $combined, 'load_plugin_textdomain' ) && preg_match( '/__\s*\(|_e\s*\(|esc_html__\s*\(|esc_attr__\s*\(/', $combined ) ) {
            $issues[] = $this->issue( 'notice', 'lifecycle_i18n_loader_missing', 'มีฟังก์ชันแปลภาษาแต่ยังไม่พบ load_plugin_textdomain', 'ถ้าต้องรองรับหลายภาษา ควรโหลด text domain ให้ครบ', $main_file, 1, 'i18n', 'เพิ่ม load_plugin_textdomain() ใน plugins_loaded หรือ init', 'low', 68 );
        }

        return $issues;
    }

    private function check_php_file_guards( array $data ): array {
        $issues = array();
        foreach ( $data['php_files'] as $rel => $row ) {
            $content = $data['file_content'][ $rel ] ?? '';
            if ( false === strpos( $content, 'defined( \'ABSPATH\' )' ) && false === strpos( $content, 'defined( "ABSPATH" )' ) && false === strpos( $content, 'WP_UNINSTALL_PLUGIN' ) ) {
                $issues[] = $this->issue( 'warning', 'php_file_missing_direct_access_guard', 'ไฟล์ PHP ไม่มีตัวกัน direct access', 'ควรมี if ( ! defined( \'ABSPATH\' ) ) { exit; } เพื่อไม่ให้เรียกไฟล์ตรงจาก URL', $rel, 1, 'security', 'ใส่ ABSPATH guard ไว้ต้นไฟล์ PHP ทุกไฟล์ ยกเว้น uninstall.php ที่ใช้ WP_UNINSTALL_PLUGIN', 'high', 88 );
            }
        }
        return $issues;
    }

    private function check_security_data_flow( array $data ): array {
        $issues = array();
        foreach ( $data['php_files'] as $rel => $row ) {
            $content = $data['file_content'][ $rel ] ?? '';
            $scan_content = $this->code_without_strings_and_comments( $content );
            $request_hits = $this->match_all_with_lines( '/\$_(POST|GET|REQUEST|COOKIE|FILES)\s*\[[^\]]+\]/i', $scan_content );
            foreach ( $request_hits as $hit ) {
                $window = $this->window( $content, $hit['offset'], 900 );
                if ( ! preg_match( '/wp_unslash|sanitize_|absint|intval|floatval|boolval|sanitize_text_field|sanitize_key|sanitize_email|wp_kses/i', $window ) ) {
                    $issues[] = $this->issue( 'warning', 'request_value_needs_sanitize_trace', 'พบ request input ที่ยังไม่เห็นการ sanitize ใกล้จุดใช้งาน', 'ตัวแปร ' . $hit['text'] . ' ควรผ่าน wp_unslash() และ sanitize_* ตามชนิดข้อมูล', $rel, $hit['line'], 'security', 'แยกอ่านค่า → wp_unslash() → sanitize_* → validate ก่อนใช้งาน', 'high', 82 );
                }
            }

            $echo_hits = $this->match_all_with_lines( '/\b(echo|print|printf)\b([^;]{0,220});/i', $scan_content );
            foreach ( $echo_hits as $hit ) {
                $text = $hit['text'];
                $source_window = $this->window( $content, $hit['offset'], 220 );
                if ( false !== strpos( $source_window, 'WordPress.Security.EscapeOutput.OutputNotEscaped' ) ) {
                    continue;
                }
                $has_dynamic_value = strpos( $text, '$' ) !== false || preg_match( '/\.\s*[a-zA-Z_][a-zA-Z0-9_]*\s*\(/', $text );
                if ( ! $has_dynamic_value ) {
                    continue;
                }
                if ( ! preg_match( '/esc_html|esc_attr|esc_url|esc_textarea|wp_kses|wp_kses_post|number_format_i18n|checked\s*\(|selected\s*\(|wp_json_encode/i', $text ) ) {
                    $issues[] = $this->issue( 'warning', 'output_needs_escape_trace', 'พบ output ที่อาจยังไม่ escape', 'ควร escape output ทุกครั้งตามบริบท เช่น esc_html(), esc_attr(), esc_url(), wp_kses_post()', $rel, $hit['line'], 'security', 'ครอบค่าที่แสดงผลด้วย escape function ให้ตรงตำแหน่ง HTML/attribute/URL', 'medium', 76 );
                }
            }

            if ( preg_match( '/update_option\s*\(/i', $content ) && ! preg_match( '/current_user_can\s*\(/i', $content ) ) {
                $issues[] = $this->issue( 'warning', 'settings_save_without_capability_trace', 'พบ update_option แต่ไม่พบ current_user_can ในไฟล์เดียวกัน', 'การบันทึกค่า settings ควรตรวจสิทธิ์ผู้ใช้ก่อนเสมอ', $rel, 1, 'security', 'เช็ก current_user_can( \'manage_options\' ) ก่อน update_option()', 'high', 78 );
            }

            if ( preg_match( '/wp_ajax_nopriv_/i', $content ) && ! preg_match( '/check_ajax_referer|wp_verify_nonce/i', $content ) ) {
                $issues[] = $this->issue( 'critical', 'public_ajax_without_nonce_detail', 'พบ public AJAX แต่ยังไม่พบ nonce', 'wp_ajax_nopriv_* เปิดให้ผู้ไม่ล็อกอินเรียกได้ จึงควรมี nonce/rate limit/validation', $rel, 1, 'security', 'เพิ่ม check_ajax_referer() และ validate input ทุกตัวก่อนประมวลผล', 'high', 90 );
            }
        }
        return $issues;
    }

    private function check_database_safety( array $data ): array {
        $issues = array();
        foreach ( $data['php_files'] as $rel => $row ) {
            $content = $data['file_content'][ $rel ] ?? '';
            $db_hits = $this->match_all_with_lines( '/\$wpdb->(query|get_results|get_row|get_var|get_col)\s*\((.*?)\);/is', $content );
            foreach ( $db_hits as $hit ) {
                if ( false === stripos( $hit['text'], 'prepare(' ) && preg_match( '/SELECT|INSERT|UPDATE|DELETE|WHERE/i', $hit['text'] ) ) {
                    $issues[] = $this->issue( 'critical', 'wpdb_query_without_prepare_trace', 'พบ SQL query ที่อาจไม่ได้ใช้ $wpdb->prepare()', 'ถ้ามีตัวแปรประกอบใน SQL ต้องใช้ $wpdb->prepare() เพื่อป้องกัน SQL injection', $rel, $hit['line'], 'database', 'เปลี่ยนเป็น $wpdb->prepare( $sql, $value ) หรือใช้ insert/update/delete API ที่ validate แล้ว', 'high', 86 );
                }
            }

            if ( preg_match( '/dbDelta\s*\(/i', $content ) && false === strpos( $content, 'wp-admin/includes/upgrade.php' ) ) {
                $issues[] = $this->issue( 'notice', 'dbdelta_upgrade_file_not_included', 'พบ dbDelta แต่ไม่เห็นการ include upgrade.php', 'ก่อนใช้ dbDelta ควร require_once ABSPATH . \'wp-admin/includes/upgrade.php\'', $rel, 1, 'database', 'include upgrade.php ก่อนเรียก dbDelta()', 'medium', 74 );
            }
        }
        return $issues;
    }

    private function check_remote_requests( array $data ): array {
        $issues = array();
        foreach ( $data['php_files'] as $rel => $row ) {
            $content = $data['file_content'][ $rel ] ?? '';
            if ( preg_match( '/wp_remote_(get|post|request)\s*\(/i', $content ) ) {
                if ( ! preg_match( '/is_wp_error\s*\(/i', $content ) ) {
                    $issues[] = $this->issue( 'warning', 'remote_request_without_wp_error_check', 'พบ remote request แต่ไม่พบ is_wp_error()', 'ควรตรวจ error response จาก wp_remote_* ก่อนอ่าน body/status', $rel, 1, 'integration', 'เพิ่ม if ( is_wp_error( $response ) ) { ... } ก่อนใช้งาน response', 'high', 84 );
                }
                if ( ! preg_match( '/wp_remote_retrieve_response_code|wp_remote_retrieve_body/i', $content ) ) {
                    $issues[] = $this->issue( 'notice', 'remote_request_without_response_reader', 'พบ remote request แต่ไม่เห็นตัวอ่าน response มาตรฐาน', 'ควรใช้ wp_remote_retrieve_response_code() และ wp_remote_retrieve_body() เพื่ออ่านค่าชัดเจน', $rel, 1, 'integration', 'อ่าน status code และ body ด้วย WordPress HTTP API helper', 'medium', 72 );
                }
                if ( ! preg_match( '/timeout\s*=>/i', $content ) ) {
                    $issues[] = $this->issue( 'notice', 'remote_request_without_timeout', 'remote request อาจยังไม่กำหนด timeout', 'ถ้าไม่กำหนด timeout ระบบอาจรู้สึกหน่วงเมื่อ API ภายนอกตอบช้า', $rel, 1, 'performance', 'ใส่ timeout เช่น 10-20 วินาทีตามประเภทงาน', 'medium', 78 );
                }
            }
        }
        return $issues;
    }

    private function check_assets_detail( array $data, string $root_path ): array {
        $issues = array();
        foreach ( $data['php_files'] as $rel => $row ) {
            $content = $data['file_content'][ $rel ] ?? '';
            $asset_hits = $this->match_all_with_lines( '/wp_(enqueue|register)_(script|style)\s*\(([^;]+)\);/is', $content );
            foreach ( $asset_hits as $hit ) {
                if ( ! preg_match( '/array\s*\(/i', $hit['text'] ) && substr_count( $hit['text'], ',' ) < 3 ) {
                    $issues[] = $this->issue( 'notice', 'asset_missing_version_or_deps_hint', 'การ enqueue asset อาจยังไม่กำหนด dependency/version ครบ', 'ควรกำหนด dependencies และ version เพื่อลด cache เพี้ยนและโหลดซ้ำ', $rel, $hit['line'], 'assets', 'ใช้ wp_enqueue_script( handle, src, deps, version, true ) หรือ wp_enqueue_style(..., version)', 'low', 65 );
                }

                if ( preg_match( '/plugins_url\s*\(\s*[\'\"]([^\'\"]+)/i', $hit['text'], $m ) ) {
                    $candidate = ltrim( $m[1], '/' );
                    if ( false === strpos( $candidate, '$' ) && false === strpos( $candidate, '..' ) && ! file_exists( $root_path . $candidate ) ) {
                        $issues[] = $this->issue( 'warning', 'asset_file_maybe_missing', 'asset path อาจชี้ไปไฟล์ที่ไม่มีจริง', 'พบ path ' . $candidate . ' แต่ไม่พบไฟล์ในแพ็กที่ตรวจ', $rel, $hit['line'], 'assets', 'ตรวจ path JS/CSS ให้ตรงกับไฟล์จริง และใช้ plugin_dir_url( __FILE__ ) ให้สัมพันธ์กับตำแหน่งไฟล์', 'medium', 70 );
                    }
                }
            }
        }

        foreach ( $data['js_files'] as $rel => $row ) {
            $content = $data['file_content'][ $rel ] ?? '';
            if ( preg_match( '/fetch\s*\(|XMLHttpRequest|axios\./i', $content ) && ! preg_match( '/catch\s*\(|try\s*\{|\.catch\s*\(/i', $content ) ) {
                $issues[] = $this->issue( 'warning', 'js_request_without_error_handling', 'JS มี request แต่ไม่เห็น error handling', 'ควรมี catch/try เพื่อแจ้งผู้ใช้เมื่อ API ล้มเหลว ไม่ปล่อยให้ปุ่มค้างหรือ UI เงียบ', $rel, 1, 'frontend', 'เพิ่ม try/catch หรือ .catch() พร้อมแสดงสถานะ error บนหน้า UI', 'medium', 78 );
            }
            if ( preg_match( '/innerHTML\s*=|insertAdjacentHTML\s*\(/i', $content ) && ! preg_match( '/DOMPurify|textContent|esc_html|wp_kses/i', $content ) ) {
                $issues[] = $this->issue( 'warning', 'js_innerhtml_xss_risk', 'JS ใช้ innerHTML/insertAdjacentHTML ที่อาจเสี่ยง XSS', 'ถ้าข้อมูลมาจาก user/API ควร sanitize หรือใช้ textContent แทน', $rel, 1, 'frontend', 'เปลี่ยนเป็น textContent เมื่อเป็นข้อความ หรือ sanitize HTML ก่อน render', 'high', 82 );
            }
        }

        return $issues;
    }

    private function check_i18n( array $data ): array {
        $issues = array();
        foreach ( $data['php_files'] as $rel => $row ) {
            $content = $data['file_content'][ $rel ] ?? '';
            $hits = $this->match_all_with_lines( '/(__|_e|esc_html__|esc_attr__|esc_html_e|esc_attr_e)\s*\(([^;]+)\)/i', $content );
            foreach ( $hits as $hit ) {
                if ( substr_count( $hit['text'], ',' ) < 1 ) {
                    $issues[] = $this->issue( 'notice', 'i18n_missing_text_domain_hint', 'ข้อความแปลภาษาอาจยังไม่มี text domain', 'ฟังก์ชันแปลภาษาควรใส่ text domain เพื่อรองรับ translation', $rel, $hit['line'], 'i18n', 'ใส่ text domain เช่น __( \'Text\', \'thinkb4do-plugin-bug-inspector\' )', 'low', 66 );
                }
            }
        }
        return $issues;
    }

    private function check_file_size_and_complexity( array $data ): array {
        $issues = array();
        foreach ( $data['inventory'] as $row ) {
            if ( 'php' === $row['extension'] && (int) $row['lines'] > 900 ) {
                $issues[] = $this->issue( 'notice', 'php_file_too_large_for_maintainability', 'ไฟล์ PHP ใหญ่มาก อาจดูแลยาก', 'ไฟล์เกิน 900 บรรทัด ควรแยก class/module เพื่อให้ debug ง่ายและลดผลกระทบเวลาซ่อม', $row['file'], 1, 'quality', 'แยกไฟล์ตามหน้าที่ เช่น Admin, Scanner, Exporter, API, Renderer', 'medium', 72 );
            }
            if ( 'js' === $row['extension'] && (int) $row['size_bytes'] > 200 * 1024 ) {
                $issues[] = $this->issue( 'notice', 'js_file_large_no_bundle_hint', 'ไฟล์ JS ใหญ่ อาจกระทบ performance', 'ถ้า JS ใหญ่มาก ควรแยกโหลดเฉพาะหน้าที่ใช้ หรือ minify/build production', $row['file'], 1, 'performance', 'แยก bundle ตามหน้า หรือโหลดแบบ conditional เฉพาะ admin page ที่ต้องใช้', 'medium', 70 );
            }
        }
        return $issues;
    }

    private function check_wordpress_admin_ui_risks( array $data ): array {
        $issues = array();
        foreach ( $data['css_files'] as $rel => $row ) {
            $content = $data['file_content'][ $rel ] ?? '';
            if ( preg_match( '/position\s*:\s*fixed/i', $content ) && ! preg_match( '/#wpadminbar|admin-bar|wp-admin/i', $content ) ) {
                $issues[] = $this->issue( 'notice', 'fixed_ui_without_adminbar_awareness', 'CSS fixed อาจไม่คำนึงถึง WordPress admin bar', 'องค์ประกอบ fixed เช่น header/composer/popup ควรเว้น top/bottom ตาม admin bar และพื้นที่พิมพ์', $rel, 1, 'ui_ux', 'ใช้ CSS variable สำหรับ --wp-admin--admin-bar--height หรือคำนวณ top/bottom ให้ไม่ซ้อน', 'medium', 76 );
            }
            if ( preg_match( '/z-index\s*:\s*(9999|99999|999999|2147483647)/i', $content ) ) {
                $issues[] = $this->issue( 'notice', 'very_high_z_index_overlay_risk', 'พบ z-index สูงมาก', 'z-index สูงมากอาจทับ popup, admin bar, composer หรือ notice ของ WordPress', $rel, 1, 'ui_ux', 'จัด layer scale ให้ชัด เช่น 10/100/1000 และหลีกเลี่ยงเลขสูงเกินจำเป็น', 'low', 74 );
            }
        }
        return $issues;
    }

    private function detect_main_file( array $data ): string {
        foreach ( $data['php_files'] as $rel => $row ) {
            $content = $data['file_content'][ $rel ] ?? '';
            if ( preg_match( '/Plugin\s+Name\s*:/i', $content ) ) {
                return $rel;
            }
        }
        foreach ( $data['php_files'] as $rel => $row ) {
            return $rel;
        }
        return '-';
    }

    private function count_by_area( array $issues ): array {
        $counts = array();
        foreach ( $issues as $issue ) {
            $area = $issue['area'] ?? 'general';
            if ( ! isset( $counts[ $area ] ) ) {
                $counts[ $area ] = array( 'critical' => 0, 'warning' => 0, 'notice' => 0, 'total' => 0 );
            }
            $severity = $issue['severity'] ?? 'notice';
            if ( ! isset( $counts[ $area ][ $severity ] ) ) {
                $counts[ $area ][ $severity ] = 0;
            }
            $counts[ $area ][ $severity ]++;
            $counts[ $area ]['total']++;
        }
        ksort( $counts );
        return $counts;
    }

    private function calculate_detail_score( array $issues ): int {
        $score = 100;
        foreach ( $issues as $issue ) {
            $severity = $issue['severity'] ?? 'notice';
            if ( 'critical' === $severity ) {
                $score -= 9;
            } elseif ( 'warning' === $severity ) {
                $score -= 4;
            } else {
                $score -= 1;
            }
        }
        return max( 0, min( 100, $score ) );
    }

    private function build_fix_queue( array $issues ): array {
        $rank = array( 'critical' => 0, 'warning' => 1, 'notice' => 2 );
        usort(
            $issues,
            static function ( $a, $b ) use ( $rank ) {
                $ra = $rank[ $a['severity'] ?? 'notice' ] ?? 3;
                $rb = $rank[ $b['severity'] ?? 'notice' ] ?? 3;
                if ( $ra === $rb ) {
                    return (int) ( $b['confidence'] ?? 0 ) <=> (int) ( $a['confidence'] ?? 0 );
                }
                return $ra <=> $rb;
            }
        );

        $queue = array();
        foreach ( array_slice( $issues, 0, 12 ) as $issue ) {
            $queue[] = array(
                'severity'   => $issue['severity'] ?? 'notice',
                'area'       => $issue['area'] ?? 'general',
                'title'      => $issue['title'] ?? '-',
                'file'       => $issue['file'] ?? '-',
                'line'       => $issue['line'] ?? 0,
                'fix'        => $issue['fix'] ?? '-',
                'confidence' => $issue['confidence'] ?? 0,
            );
        }
        return $queue;
    }

    private function match_all_with_lines( string $pattern, string $content ): array {
        $hits = array();
        preg_match_all( $pattern, $content, $matches, PREG_OFFSET_CAPTURE );
        if ( empty( $matches[0] ) ) {
            return $hits;
        }
        foreach ( $matches[0] as $match ) {
            $hits[] = array(
                'text'   => $match[0],
                'offset' => $match[1],
                'line'   => $this->line_number( $content, $match[1] ),
            );
        }
        return $hits;
    }

    private function window( string $content, int $offset, int $radius ): string {
        $start = max( 0, $offset - $radius );
        return substr( $content, $start, $radius * 2 );
    }

    private function issue( string $severity, string $code, string $title, string $detail, string $file, int $line, string $area, string $fix, string $priority, int $confidence ): array {
        return array(
            'severity'   => $severity,
            'code'       => $code,
            'title'      => $title,
            'detail'     => $detail,
            'file'       => $file,
            'line'       => $line,
            'area'       => $area,
            'fix'        => $fix,
            'priority'   => $priority,
            'confidence' => $confidence,
        );
    }

    private function code_without_strings_and_comments( string $content ): string {
        if ( ! function_exists( 'token_get_all' ) ) {
            return $content;
        }

        $clean = '';
        foreach ( token_get_all( $content ) as $token ) {
            if ( is_array( $token ) ) {
                if ( in_array( $token[0], array( T_COMMENT, T_DOC_COMMENT, T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE ), true ) ) {
                    $clean .= str_repeat( ' ', strlen( $token[1] ) );
                } else {
                    $clean .= $token[1];
                }
            } else {
                $clean .= $token;
            }
        }
        return $clean;
    }

    private function relative_path( string $file, string $root_path ): string {
        $file      = wp_normalize_path( $file );
        $root_path = wp_normalize_path( trailingslashit( $root_path ) );
        if ( strpos( $file, $root_path ) === 0 ) {
            return ltrim( substr( $file, strlen( $root_path ) ), '/' );
        }
        return basename( $file );
    }

    private function line_number( string $content, int $offset ): int {
        return substr_count( substr( $content, 0, $offset ), "\n" ) + 1;
    }
}
