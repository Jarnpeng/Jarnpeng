<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * System X-Ray Analyzer: static whole-system trace for WordPress plugins.
 * It maps entry points, modules, dependencies, data touchpoints and orphan/disconnected areas.
 */
class TBPBI_System_Xray_Analyzer {
    private array $wp_function_allowlist = array(
        'add_action','add_filter','add_shortcode','register_rest_route','wp_enqueue_script','wp_enqueue_style','plugin_dir_path','plugin_dir_url','plugins_url','admin_url','home_url','site_url','esc_html','esc_attr','esc_url','wp_kses_post','sanitize_text_field','sanitize_key','wp_unslash','wp_verify_nonce','check_admin_referer','current_user_can','wp_die','is_wp_error','wp_remote_get','wp_remote_post','wp_remote_request','get_option','update_option','add_option','delete_option','set_transient','get_transient','delete_transient','wp_send_json','wp_send_json_success','wp_send_json_error','__','_e','printf','sprintf','count','empty','isset','array','array_merge','array_values','array_unique','in_array','strpos','str_replace','preg_match','preg_match_all','file_get_contents','file_exists','is_file','is_dir','basename','dirname','trailingslashit','wp_normalize_path'
    );

    public function analyze( array $files, string $root_path ): array {
        $root_path = wp_normalize_path( trailingslashit( $root_path ) );
        $map = $this->build_map( $files, $root_path );
        $issues = array();
        $issues = array_merge( $issues, $this->detect_orphans( $map ) );
        $issues = array_merge( $issues, $this->detect_unreferenced_project_functions( $map ) );
        $issues = array_merge( $issues, $this->detect_entrypoint_risks( $map ) );
        $issues = array_merge( $issues, $this->detect_data_flow_risks( $map ) );
        $summary = $this->build_summary( $map, $issues );

        return array(
            'score'        => $this->calculate_score( $issues, $summary ),
            'summary'      => $summary,
            'entry_points' => $map['entry_points'],
            'modules'      => $map['modules'],
            'dependencies' => $map['dependencies'],
            'data_flow'    => $map['data_flow'],
            'orphan_files' => $summary['orphan_files'],
            'issues'       => $issues,
            'topology'     => $this->build_topology_text( $map ),
        );
    }

    private function build_map( array $files, string $root_path ): array {
        $map = array(
            'modules'      => array(),
            'dependencies' => array(),
            'entry_points' => array(),
            'defined_functions' => array(),
            'called_functions'  => array(),
            'data_flow'    => array(
                'request_inputs' => array(),
                'sanitizers'     => array(),
                'outputs'        => array(),
                'escapers'       => array(),
                'db_queries'     => array(),
                'remote_calls'   => array(),
                'file_ops'       => array(),
            ),
        );

        foreach ( $files as $file ) {
            $file = wp_normalize_path( $file );
            if ( ! is_readable( $file ) ) {
                continue;
            }
            $rel = $this->relative_path( $file, $root_path );
            $ext = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
            $content = file_get_contents( $file );
            if ( false === $content ) {
                continue;
            }
            $lines = substr_count( $content, "\n" ) + 1;
            $module = array(
                'file'       => $rel,
                'type'       => $this->module_type( $rel, $content ),
                'ext'        => $ext,
                'lines'      => $lines,
                'classes'    => array(),
                'functions'  => array(),
                'hooks'      => array(),
                'ajax'       => array(),
                'rest'       => array(),
                'shortcodes' => array(),
                'assets'     => array(),
                'entry_role' => array(),
            );

            if ( 'php' === $ext ) {
                if ( preg_match( '/Plugin\s+Name\s*:/i', $content ) ) {
                    $module['entry_role'][] = 'plugin_main_header';
                    $map['entry_points'][] = array( 'type' => 'plugin_main', 'file' => $rel, 'name' => 'Plugin Header' );
                }
                if ( preg_match_all( '/\bclass\s+([A-Za-z_][A-Za-z0-9_]*)/i', $content, $m ) ) {
                    $module['classes'] = $m[1];
                }
                if ( preg_match_all( '/\bfunction\s+([A-Za-z_][A-Za-z0-9_]*)\s*\(/i', $content, $m ) ) {
                    $module['functions'] = $m[1];
                    foreach ( $m[1] as $fn ) {
                        $map['defined_functions'][ $fn ][] = $rel;
                    }
                }
                if ( preg_match_all( '/(?<!function\s)(?<!->)(?<!::)\b([A-Za-z_][A-Za-z0-9_]*)\s*\(/i', $content, $m ) ) {
                    foreach ( $m[1] as $fn ) {
                        if ( in_array( strtolower( $fn ), $this->wp_function_allowlist, true ) ) {
                            continue;
                        }
                        $map['called_functions'][ $fn ][] = $rel;
                    }
                }
                if ( preg_match_all( '/add_(action|filter)\s*\(\s*[\'\"]([^\'\"]+)/i', $content, $m, PREG_SET_ORDER ) ) {
                    foreach ( $m as $hook ) {
                        $module['hooks'][] = $hook[1] . ':' . $hook[2];
                        $map['entry_points'][] = array( 'type' => $hook[1], 'file' => $rel, 'name' => $hook[2] );
                        if ( 0 === strpos( $hook[2], 'wp_ajax_' ) ) {
                            $module['ajax'][] = str_replace( array( 'wp_ajax_nopriv_', 'wp_ajax_' ), '', $hook[2] );
                        }
                    }
                }
                if ( preg_match_all( '/add_shortcode\s*\(\s*[\'\"]([^\'\"]+)/i', $content, $m ) ) {
                    $module['shortcodes'] = $m[1];
                    foreach ( $m[1] as $shortcode ) {
                        $map['entry_points'][] = array( 'type' => 'shortcode', 'file' => $rel, 'name' => $shortcode );
                    }
                }
                if ( preg_match_all( '/register_rest_route\s*\(\s*[\'\"]([^\'\"]+)[\'\"]\s*,\s*[\'\"]([^\'\"]+)/i', $content, $m, PREG_SET_ORDER ) ) {
                    foreach ( $m as $route ) {
                        $route_name = $route[1] . $route[2];
                        $module['rest'][] = $route_name;
                        $map['entry_points'][] = array( 'type' => 'rest', 'file' => $rel, 'name' => $route_name );
                    }
                }
                if ( preg_match_all( '/wp_enqueue_(script|style)\s*\(\s*[\'\"]([^\'\"]+)/i', $content, $m, PREG_SET_ORDER ) ) {
                    foreach ( $m as $asset ) {
                        $module['assets'][] = $asset[1] . ':' . $asset[2];
                        $map['entry_points'][] = array( 'type' => 'asset_' . $asset[1], 'file' => $rel, 'name' => $asset[2] );
                    }
                }
                if ( preg_match_all( '/(?:include|include_once|require|require_once)\s*(?:\(?\s*)?([^;\n]+)/i', $content, $m, PREG_SET_ORDER ) ) {
                    foreach ( $m as $dep ) {
                        $target = $this->clean_dependency( $dep[1] );
                        if ( $target ) {
                            $map['dependencies'][] = array( 'from' => $rel, 'to' => $target );
                        }
                    }
                }
                $this->collect_data_flow( $content, $rel, $map );
            } elseif ( 'js' === $ext ) {
                if ( preg_match_all( '/addEventListener\s*\(\s*[\'\"]([^\'\"]+)/i', $content, $m ) ) {
                    foreach ( $m[1] as $event ) {
                        $map['entry_points'][] = array( 'type' => 'js_event', 'file' => $rel, 'name' => $event );
                    }
                }
                if ( preg_match_all( '/fetch\s*\(|jQuery\.ajax|\.ajax\s*\(/i', $content, $m, PREG_OFFSET_CAPTURE ) ) {
                    $map['data_flow']['remote_calls'][] = array( 'file' => $rel, 'line' => $this->line_number( $content, $m[0][0][1] ), 'type' => 'js_ajax_fetch' );
                }
            } elseif ( 'css' === $ext ) {
                if ( preg_match('/position\s*:\s*fixed/i', $content) || preg_match('/z-index\s*:\s*([1-9][0-9]{3,})/i', $content) ) {
                    $module['entry_role'][] = 'ui_overlay_risk';
                }
            }

            $map['modules'][ $rel ] = $module;
        }

        return $map;
    }

    private function collect_data_flow( string $content, string $rel, array &$map ): void {
        $patterns = array(
            'request_inputs' => '/\$_(POST|GET|REQUEST|FILES|COOKIE)\s*\[/i',
            'sanitizers'     => '/\b(wp_unslash|sanitize_[A-Za-z0-9_]+|absint|intval|floatval|sanitize_text_field|sanitize_key)\s*\(/i',
            'outputs'        => '/\b(echo|print|printf)\b|\?>/i',
            'escapers'       => '/\b(esc_html|esc_attr|esc_url|esc_textarea|wp_kses_post|wp_json_encode)\s*\(/i',
            'db_queries'     => '/\$wpdb->(query|get_results|get_row|get_var|insert|update|delete|prepare)\s*\(/i',
            'remote_calls'   => '/\b(wp_remote_get|wp_remote_post|wp_remote_request)\s*\(/i',
            'file_ops'       => '/\b(file_put_contents|fopen|fwrite|unlink|rename|copy|move_uploaded_file)\s*\(/i',
        );
        foreach ( $patterns as $key => $pattern ) {
            if ( preg_match_all( $pattern, $content, $m, PREG_OFFSET_CAPTURE ) ) {
                foreach ( $m[0] as $hit ) {
                    $map['data_flow'][ $key ][] = array(
                        'file' => $rel,
                        'line' => $this->line_number( $content, $hit[1] ),
                        'hit'  => substr( $hit[0], 0, 80 ),
                    );
                }
            }
        }
    }

    private function detect_orphans( array $map ): array {
        $issues = array();
        $referenced = array();
        foreach ( $map['dependencies'] as $dep ) {
            $to = $dep['to'];
            foreach ( array_keys( $map['modules'] ) as $rel ) {
                if ( false !== strpos( $to, basename( $rel ) ) || false !== strpos( $to, $rel ) ) {
                    $referenced[ $rel ] = true;
                }
            }
        }
        foreach ( $map['modules'] as $rel => $module ) {
            if ( 'php' !== $module['ext'] ) {
                continue;
            }
            if ( ! empty( $module['entry_role'] ) || ! empty( $module['hooks'] ) || ! empty( $module['shortcodes'] ) || ! empty( $module['rest'] ) ) {
                continue;
            }
            if ( isset( $referenced[ $rel ] ) ) {
                continue;
            }
            if ( preg_match( '/(^|\/)(index|uninstall)\.php$/i', $rel ) ) {
                continue;
            }
            $issues[] = $this->issue( 'notice', 'xray_possible_orphan_php_file', 'ไฟล์ PHP อาจยังไม่ถูกเชื่อมเข้าระบบ', 'ไม่พบว่าไฟล์นี้เป็น entry point หรือถูก include/require แบบชัดเจน อาจเป็นไฟล์สำรองหรือยังไม่ได้เชื่อม', $rel, 0, 'system_xray', 'P3', 70, 'ตรวจว่าไฟล์นี้ถูกโหลดจากไฟล์หลักหรือไม่ ถ้าใช้งานจริงควร require/include ให้ชัดเจน' );
        }
        return $issues;
    }

    private function detect_unreferenced_project_functions( array $map ): array {
        $issues = array();
        foreach ( $map['defined_functions'] as $fn => $files ) {
            if ( ! preg_match( '/^(tbpbi|aira|think|thinkb4do|tbd|wp_)/i', $fn ) ) {
                continue;
            }
            $calls = $map['called_functions'][ $fn ] ?? array();
            if ( empty( $calls ) && count( $files ) === 1 ) {
                $issues[] = $this->issue( 'notice', 'xray_unreferenced_project_function', 'พบฟังก์ชันโปรเจกต์ที่ยังไม่เห็นจุดเรียกใช้', 'ฟังก์ชัน ' . $fn . ' ถูกประกาศ แต่ X-Ray ยังไม่พบจุดเรียกใช้งานแบบตรง ๆ', $files[0], 0, 'system_xray', 'P3', 62, 'ถ้าเป็นฟังก์ชันสำรองให้คงไว้ได้ แต่ถ้าเป็นแกนหลักควรเชื่อมเข้ากับ hook/class หรือเรียกใช้ให้ชัดเจน' );
            }
        }
        return $issues;
    }

    private function detect_entrypoint_risks( array $map ): array {
        $issues = array();
        $has_admin = false;
        $has_ajax = false;
        $has_rest = false;
        foreach ( $map['entry_points'] as $entry ) {
            if ( false !== strpos( $entry['name'], 'admin_' ) || 'action' === $entry['type'] && false !== strpos( $entry['name'], 'admin' ) ) {
                $has_admin = true;
            }
            if ( false !== strpos( $entry['name'], 'wp_ajax_' ) || 'ajax' === $entry['type'] ) {
                $has_ajax = true;
            }
            if ( 'rest' === $entry['type'] ) {
                $has_rest = true;
            }
        }
        if ( $has_rest ) {
            foreach ( $map['modules'] as $rel => $module ) {
                if ( empty( $module['rest'] ) ) {
                    continue;
                }
                $content = @file_get_contents( $this->abs_from_rel( $rel ) );
                // abs_from_rel is unavailable in zip/temp context, so skip content re-read safely.
            }
        }
        if ( $has_ajax && empty( $map['data_flow']['sanitizers'] ) ) {
            $issues[] = $this->issue( 'warning', 'xray_ajax_without_visible_sanitize_layer', 'พบ AJAX แต่ไม่พบชั้น sanitize ที่เด่นชัด', 'ระบบมี AJAX entry point แต่ data-flow โดยรวมยังไม่เห็น sanitizer ชัดเจน', '-', 0, 'system_xray', 'P2', 76, 'เพิ่ม wp_unslash() + sanitize_* ใน handler ที่รับ POST/GET และเพิ่ม nonce/capability' );
        }
        if ( $has_admin && empty( $map['data_flow']['escapers'] ) && ! empty( $map['data_flow']['outputs'] ) ) {
            $issues[] = $this->issue( 'warning', 'xray_admin_output_without_visible_escape_layer', 'หน้าแอดมินมี output แต่ escaper ยังไม่เด่น', 'พบ output ในระบบ แต่ไม่พบ esc_html/esc_attr/esc_url/wp_kses_post มากพอใน data-flow', '-', 0, 'system_xray', 'P2', 72, 'ตรวจ output ทุกจุดและ escape ตามบริบทก่อนแสดงผล' );
        }
        return $issues;
    }

    private function detect_data_flow_risks( array $map ): array {
        $issues = array();
        if ( count( $map['data_flow']['request_inputs'] ) > 0 && count( $map['data_flow']['sanitizers'] ) === 0 ) {
            $first = $map['data_flow']['request_inputs'][0];
            $issues[] = $this->issue( 'warning', 'xray_request_without_sanitizer_layer', 'พบ request input แต่ไม่พบ sanitizer layer', 'มีการอ่าน POST/GET/REQUEST/FILES แต่ X-Ray ไม่พบ sanitize/wp_unslash/absint/intval ที่ชัดเจนในระบบ', $first['file'], (int) $first['line'], 'data_flow', 'P1', 82, 'เพิ่ม sanitize ตามชนิดข้อมูล และจัด pattern request → wp_unslash → sanitize → validate → process' );
        }
        if ( count( $map['data_flow']['outputs'] ) > 0 && count( $map['data_flow']['escapers'] ) === 0 ) {
            $first = $map['data_flow']['outputs'][0];
            $issues[] = $this->issue( 'warning', 'xray_output_without_escape_layer', 'พบ output แต่ไม่พบ escape layer', 'มีการ echo/print/printf แต่ไม่พบ esc_* หรือ wp_kses_post ที่ชัดเจนในระบบ', $first['file'], (int) $first['line'], 'data_flow', 'P1', 78, 'ใช้ esc_html(), esc_attr(), esc_url(), esc_textarea() หรือ wp_kses_post() ตามบริบท' );
        }
        if ( count( $map['data_flow']['remote_calls'] ) > 0 ) {
            $has_error_handling = false;
            foreach ( $map['called_functions'] as $fn => $files ) {
                if ( 'is_wp_error' === strtolower( $fn ) || 'wp_remote_retrieve_response_code' === strtolower( $fn ) ) {
                    $has_error_handling = true;
                    break;
                }
            }
            if ( ! $has_error_handling ) {
                $first = $map['data_flow']['remote_calls'][0];
                $issues[] = $this->issue( 'notice', 'xray_remote_call_error_handling_unclear', 'พบ remote/API call แต่ error handling ยังไม่ชัด', 'มีการเรียก API/remote request แต่ X-Ray ยังไม่เห็น is_wp_error หรือ response code handling ชัดเจน', $first['file'], (int) $first['line'], 'remote_api', 'P2', 68, 'เพิ่ม timeout, is_wp_error(), wp_remote_retrieve_response_code() และ fallback message' );
            }
        }
        return $issues;
    }

    private function build_summary( array $map, array $issues ): array {
        $counts = array( 'critical' => 0, 'warning' => 0, 'notice' => 0 );
        foreach ( $issues as $issue ) {
            $severity = $issue['severity'] ?? 'notice';
            if ( isset( $counts[ $severity ] ) ) {
                $counts[ $severity ]++;
            }
        }
        $orphan_files = array();
        foreach ( $issues as $issue ) {
            if ( 'xray_possible_orphan_php_file' === ( $issue['code'] ?? '' ) ) {
                $orphan_files[] = $issue['file'];
            }
        }
        return array(
            'modules'       => count( $map['modules'] ),
            'dependencies'  => count( $map['dependencies'] ),
            'entry_points'  => count( $map['entry_points'] ),
            'defined_functions' => count( $map['defined_functions'] ),
            'called_functions'  => count( $map['called_functions'] ),
            'request_inputs'=> count( $map['data_flow']['request_inputs'] ),
            'sanitizers'    => count( $map['data_flow']['sanitizers'] ),
            'outputs'       => count( $map['data_flow']['outputs'] ),
            'escapers'      => count( $map['data_flow']['escapers'] ),
            'db_queries'    => count( $map['data_flow']['db_queries'] ),
            'remote_calls'  => count( $map['data_flow']['remote_calls'] ),
            'file_ops'      => count( $map['data_flow']['file_ops'] ),
            'orphan_files'  => $orphan_files,
            'issue_counts'  => $counts,
        );
    }

    private function calculate_score( array $issues, array $summary ): int {
        $score = 100;
        foreach ( $issues as $issue ) {
            $severity = $issue['severity'] ?? 'notice';
            if ( 'critical' === $severity ) {
                $score -= 12;
            } elseif ( 'warning' === $severity ) {
                $score -= 6;
            } else {
                $score -= 2;
            }
        }
        if ( (int) $summary['modules'] > 3 && (int) $summary['entry_points'] === 0 ) {
            $score -= 10;
        }
        if ( count( $summary['orphan_files'] ) > 5 ) {
            $score -= 8;
        }
        return max( 0, min( 100, $score ) );
    }

    private function build_topology_text( array $map ): array {
        $items = array();
        foreach ( array_slice( $map['entry_points'], 0, 30 ) as $entry ) {
            $items[] = strtoupper( $entry['type'] ) . ' :: ' . $entry['name'] . ' => ' . $entry['file'];
        }
        return $items;
    }

    private function module_type( string $rel, string $content ): string {
        $rel_l = strtolower( $rel );
        if ( false !== strpos( $rel_l, '/admin/' ) || preg_match( '/add_menu_page|admin_menu|manage_options/i', $content ) ) {
            return 'admin';
        }
        if ( false !== strpos( $rel_l, '/assets/' ) || preg_match( '/wp_enqueue_script|wp_enqueue_style/i', $content ) ) {
            return 'asset';
        }
        if ( false !== strpos( $rel_l, 'api' ) || preg_match( '/register_rest_route|wp_remote_/i', $content ) ) {
            return 'api';
        }
        if ( false !== strpos( $rel_l, 'scanner' ) || false !== strpos( $rel_l, 'analyzer' ) || false !== strpos( $rel_l, 'checker' ) ) {
            return 'analyzer';
        }
        if ( preg_match( '/Plugin\s+Name\s*:/i', $content ) ) {
            return 'plugin_main';
        }
        return 'module';
    }

    private function clean_dependency( string $raw ): string {
        $raw = trim( $raw );
        $raw = trim( $raw, " \t\n\r\0\x0B()" );
        if ( preg_match( '/[\'\"]([^\'\"]+\.php)[\'\"]/i', $raw, $m ) ) {
            return $m[1];
        }
        if ( preg_match( '/([A-Za-z0-9_\-\/]+\.php)/i', $raw, $m ) ) {
            return $m[1];
        }
        return substr( $raw, 0, 140 );
    }

    private function issue( string $severity, string $code, string $title, string $detail, string $file, int $line, string $area, string $priority, int $confidence, string $fix ): array {
        return array(
            'severity'   => $severity,
            'code'       => $code,
            'title'      => $title,
            'detail'     => $detail,
            'file'       => $file,
            'line'       => $line,
            'area'       => $area,
            'priority'   => $priority,
            'confidence' => $confidence,
            'fix'        => $fix,
        );
    }

    private function relative_path( string $file, string $root_path ): string {
        $file = wp_normalize_path( $file );
        if ( strpos( $file, $root_path ) === 0 ) {
            return ltrim( substr( $file, strlen( $root_path ) ), '/' );
        }
        return basename( $file );
    }

    private function line_number( string $content, int $offset ): int {
        return substr_count( substr( $content, 0, $offset ), "\n" ) + 1;
    }

    private function abs_from_rel( string $rel ): string {
        return $rel;
    }
}
