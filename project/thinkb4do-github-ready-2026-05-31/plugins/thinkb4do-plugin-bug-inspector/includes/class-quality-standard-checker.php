<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ตรวจคุณภาพตามมาตรฐานก่อนใช้จริง
 * Static standard audit: WordPress readiness, security gate, maintainability,
 * compatibility, export safety, and release gate.
 */
class TBPBI_Quality_Standard_Checker {
    /**
     * @param array  $files Absolute file paths.
     * @param string $root_path Absolute root path.
     * @param array  $meta Plugin metadata.
     * @return array
     */
    public function analyze( array $files, string $root_path, array $meta = array() ): array {
        $root_path = wp_normalize_path( trailingslashit( $root_path ) );
        $issues    = array();
        $metrics   = $this->collect_metrics( $files, $root_path );
        $header    = $this->read_plugin_header_from_files( $files, $root_path );
        $standards = array();

        $standards['wordpress_header'] = $this->standard_wordpress_header( $header, $issues );
        $standards['direct_access_guard'] = $this->standard_direct_access_guard( $metrics, $issues );
        $standards['security_gate'] = $this->standard_security_gate( $metrics, $issues );
        $standards['ajax_rest_gate'] = $this->standard_ajax_rest_gate( $metrics, $issues );
        $standards['data_handling'] = $this->standard_data_handling( $metrics, $issues );
        $standards['output_escaping'] = $this->standard_output_escaping( $metrics, $issues );
        $standards['asset_loading'] = $this->standard_asset_loading( $metrics, $issues );
        $standards['compatibility'] = $this->standard_compatibility( $metrics, $header, $issues );
        if ( get_option( 'tbpbi_cross_platform_mode', '1' ) === '1' ) {
            $standards['cross_platform_compatibility'] = $this->standard_cross_platform_compatibility( $metrics, $issues );
        }
        $standards['maintainability'] = $this->standard_maintainability( $metrics, $issues );
        $standards['internationalization'] = $this->standard_i18n( $metrics, $issues );
        $standards['export_safety'] = $this->standard_export_safety( $metrics, $issues );

        $summary = $this->summarize_standards( $standards, $issues, $metrics );
        $release_gate = $this->build_release_gate( $summary, $standards, $issues );

        return array(
            'score'        => $summary['quality_standard_score'],
            'summary'      => $summary,
            'standards'    => $standards,
            'metrics'      => $metrics,
            'header'       => $header,
            'release_gate' => $release_gate,
            'issues'       => $issues,
        );
    }

    private function collect_metrics( array $files, string $root_path ): array {
        $metrics = array(
            'php_files'                    => 0,
            'js_files'                     => 0,
            'css_files'                    => 0,
            'json_files'                   => 0,
            'total_lines'                  => 0,
            'largest_php_file'             => array(),
            'php_without_abspath_guard'    => array(),
            'files_with_request_input'     => array(),
            'files_with_sanitizer'         => array(),
            'files_with_output'            => array(),
            'files_with_escaper'           => array(),
            'files_with_nonce'             => array(),
            'files_with_capability'        => array(),
            'files_with_ajax'              => array(),
            'files_with_rest'              => array(),
            'rest_without_permission'      => array(),
            'enqueue_calls'                => 0,
            'enqueue_without_version_hint' => 0,
            'remote_calls'                 => 0,
            'remote_without_timeout_hint'  => 0,
            'remote_without_error_hint'    => 0,
            'wpdb_calls'                   => 0,
            'wpdb_without_prepare_hint'    => 0,
            'secret_hits'                  => array(),
            'deprecated_hits'              => array(),
            'debug_hits'                   => array(),
            'todo_hits'                    => array(),
            'textdomain_hits'              => 0,
            'translation_calls'            => 0,
            'typed_property_files'         => array(),
            'global_function_names'        => array(),
            'class_names'                  => array(),
            'readme_found'                 => false,
            'uninstall_found'              => false,
            'license_found'                => false,
            'css_has_responsive_media'     => false,
            'css_has_reduced_motion'       => false,
            'css_has_safe_area'            => false,
            'css_has_print_style'          => false,
            'css_has_forced_colors'        => false,
            'css_has_word_wrap'            => false,
            'css_fixed_risk_files'         => array(),
            'js_clipboard_usage'           => array(),
            'js_clipboard_fallback'        => false,
            'js_modern_syntax_hits'        => array(),
            'js_browser_api_hits'          => array(),
            'js_browser_api_without_guard' => array(),
        );

        foreach ( $files as $file ) {
            $ext      = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
            $relative = $this->relative_path( $file, $root_path );
            $content  = is_file( $file ) ? file_get_contents( $file ) : false;
            if ( false === $content ) {
                continue;
            }

            if ( 'readme.txt' === strtolower( basename( $file ) ) ) {
                $metrics['readme_found'] = true;
                if ( preg_match( '/(^|\n)={0,3}\s*license\s*[:=]/i', $content ) || preg_match( '/GPL|MIT|Apache|BSD/i', $content ) ) {
                    $metrics['license_found'] = true;
                }
            }
            if ( 'uninstall.php' === strtolower( basename( $file ) ) ) {
                $metrics['uninstall_found'] = true;
            }

            $lines = substr_count( $content, "\n" ) + 1;
            $metrics['total_lines'] += $lines;

            if ( 'php' === $ext ) {
                $code_content = $this->code_without_strings_and_comments( $content );
                $metrics['php_files']++;
                if ( empty( $metrics['largest_php_file'] ) || $lines > (int) ( $metrics['largest_php_file']['lines'] ?? 0 ) ) {
                    $metrics['largest_php_file'] = array( 'file' => $relative, 'lines' => $lines );
                }

                if ( 'uninstall.php' !== strtolower( basename( $file ) ) && ! preg_match( "/defined\s*\(\s*['\"]ABSPATH['\"]\s*\)/", $content ) ) {
                    $metrics['php_without_abspath_guard'][] = $relative;
                }
                if ( preg_match( '/\$_(POST|GET|REQUEST|FILES|COOKIE)\s*\[/', $content ) ) {
                    $metrics['files_with_request_input'][] = $relative;
                }
                if ( preg_match( '/wp_unslash|sanitize_|absint\s*\(|intval\s*\(|floatval\s*\(|sanitize_text_field|sanitize_key|wp_kses/i', $content ) ) {
                    $metrics['files_with_sanitizer'][] = $relative;
                }
                if ( preg_match( '/echo\s+|print\s+|wp_send_json|wp_die\s*\(/i', $code_content ) ) {
                    $metrics['files_with_output'][] = $relative;
                }
                if ( preg_match( '/esc_html|esc_attr|esc_url|esc_textarea|wp_kses|wp_kses_post|wp_json_encode|sanitize_html_class/i', $content ) ) {
                    $metrics['files_with_escaper'][] = $relative;
                }
                if ( preg_match( '/wp_nonce_field|check_admin_referer|check_ajax_referer|wp_verify_nonce/i', $content ) ) {
                    $metrics['files_with_nonce'][] = $relative;
                }
                if ( preg_match( '/current_user_can|user_can|map_meta_cap/i', $content ) ) {
                    $metrics['files_with_capability'][] = $relative;
                }
                if ( preg_match( '/wp_ajax_|admin_post_|add_action\s*\(\s*[\'\"]wp_ajax_/i', $content ) ) {
                    $metrics['files_with_ajax'][] = $relative;
                }
                if ( preg_match( '/register_rest_route\s*\(/i', $content ) ) {
                    $metrics['files_with_rest'][] = $relative;
                    if ( ! preg_match( '/permission_callback/i', $content ) ) {
                        $metrics['rest_without_permission'][] = $relative;
                    }
                }

                $metrics['enqueue_calls'] += preg_match_all( '/wp_enqueue_(script|style)\s*\(/i', $content );
                $metrics['enqueue_without_version_hint'] += preg_match_all( '/wp_enqueue_(script|style)\s*\([^;\n]{0,240}\)/i', $content, $enqueue_matches );
                if ( ! empty( $enqueue_matches[0] ) ) {
                    foreach ( $enqueue_matches[0] as $call ) {
                        if ( substr_count( $call, ',' ) >= 3 ) {
                            $metrics['enqueue_without_version_hint']--;
                        }
                    }
                }

                $metrics['remote_calls'] += preg_match_all( '/wp_remote_(get|post|request)\s*\(/i', $content );
                if ( preg_match( '/wp_remote_(get|post|request)\s*\(/i', $content ) ) {
                    if ( ! preg_match( '/timeout\s*[\'\"]?\s*=>|timeout/i', $content ) ) {
                        $metrics['remote_without_timeout_hint']++;
                    }
                    if ( ! preg_match( '/is_wp_error|wp_remote_retrieve_response_code|wp_remote_retrieve_body/i', $content ) ) {
                        $metrics['remote_without_error_hint']++;
                    }
                }

                $metrics['wpdb_calls'] += preg_match_all( '/\$wpdb->(query|get_results|get_row|get_var|get_col|insert|update|delete)\s*\(/i', $content );
                if ( preg_match( '/\$wpdb->(query|get_results|get_row|get_var|get_col)\s*\(/i', $content ) && ! preg_match( '/\$wpdb->prepare\s*\(/i', $content ) ) {
                    $metrics['wpdb_without_prepare_hint']++;
                }

                if ( preg_match( '/(api[_\- ]?key|secret|token|bearer)\s*[=:]\s*[\'\"][^\'\"]{12,}[\'\"]|sk-[A-Za-z0-9_\-]{20,}|AIza[0-9A-Za-z_\-]{20,}/i', $content ) ) {
                    $metrics['secret_hits'][] = $relative;
                }
                if ( preg_match( '/\b(mysql_query|create_function|ereg|split|each)\s*\(/i', $code_content ) ) {
                    $metrics['deprecated_hits'][] = $relative;
                }
                if ( preg_match( '/var_dump\s*\(|print_r\s*\(|error_log\s*\(/i', $code_content ) ) {
                    $metrics['debug_hits'][] = $relative;
                }
                if ( preg_match( '/\b(TODO|FIXME|HACK)\b/i', $code_content ) ) {
                    $metrics['todo_hits'][] = $relative;
                }
                if ( preg_match( '/Text\s+Domain\s*:/i', $content ) ) {
                    $metrics['textdomain_hits']++;
                }
                $metrics['translation_calls'] += preg_match_all( '/__\s*\(|_e\s*\(|esc_html__\s*\(|esc_attr__\s*\(|esc_html_e\s*\(|esc_attr_e\s*\(/', $content );
                if ( preg_match( '/(?:public|protected|private)\s+[A-Za-z0-9_?\\\\]+\s+\$[A-Za-z0-9_]+\s*[;=]/', $content ) ) {
                    $metrics['typed_property_files'][] = $relative;
                }
                if ( preg_match_all( '/function\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\(/', $content, $function_matches ) ) {
                    foreach ( $function_matches[1] as $name ) {
                        if ( ! in_array( strtolower( $name ), array( '__construct', '__destruct' ), true ) ) {
                            $metrics['global_function_names'][] = $name;
                        }
                    }
                }
                if ( preg_match_all( '/class\s+([a-zA-Z_][a-zA-Z0-9_]*)\b/', $content, $class_matches ) ) {
                    foreach ( $class_matches[1] as $name ) {
                        $metrics['class_names'][] = $name;
                    }
                }
            } elseif ( 'js' === $ext ) {
                $metrics['js_files']++;
                if ( preg_match( '/console\.log\s*\(|debugger\s*;/', $content ) ) {
                    $metrics['debug_hits'][] = $relative;
                }
                if ( preg_match( '/navigator\.clipboard|clipboard\.writeText/i', $content ) ) {
                    $metrics['js_clipboard_usage'][] = $relative;
                    if ( preg_match( '/document\.execCommand\s*\(\s*[\'"]copy[\'"]|try\s*\{|catch\s*\(|clipboard\.writeText\s*\([^;]+\)\s*\.then/i', $content ) ) {
                        $metrics['js_clipboard_fallback'] = true;
                    }
                }
                if ( preg_match( '/(^|[^=])=>|\bconst\s+|\blet\s+|async\s+function|\bawait\s+|fetch\s*\(|Promise\.|ResizeObserver|IntersectionObserver|\?\./m', $content ) ) {
                    $metrics['js_modern_syntax_hits'][] = $relative;
                }
                if ( preg_match( '/navigator\.|window\.|document\.|localStorage|sessionStorage|ResizeObserver|IntersectionObserver|MutationObserver|matchMedia|fetch\s*\(/i', $content ) ) {
                    $metrics['js_browser_api_hits'][] = $relative;
                    if ( ! preg_match( '/typeof\s+(window|document|navigator)|try\s*\{|catch\s*\(|if\s*\([^)]*(window|document|navigator|localStorage|sessionStorage|ResizeObserver|IntersectionObserver|matchMedia|fetch)|\bin\s+(window|document|navigator)/i', $content ) ) {
                        $metrics['js_browser_api_without_guard'][] = $relative;
                    }
                }
            } elseif ( 'css' === $ext ) {
                $metrics['css_files']++;
                if ( preg_match( '/@media\s*\(/i', $content ) ) {
                    $metrics['css_has_responsive_media'] = true;
                }
                if ( preg_match( '/prefers-reduced-motion/i', $content ) ) {
                    $metrics['css_has_reduced_motion'] = true;
                }
                if ( preg_match( '/safe-area-inset|env\s*\(/i', $content ) ) {
                    $metrics['css_has_safe_area'] = true;
                }
                if ( preg_match( '/@media\s+print/i', $content ) ) {
                    $metrics['css_has_print_style'] = true;
                }
                if ( preg_match( '/forced-colors|prefers-contrast/i', $content ) ) {
                    $metrics['css_has_forced_colors'] = true;
                }
                if ( preg_match( '/overflow-wrap|word-break|word-wrap/i', $content ) ) {
                    $metrics['css_has_word_wrap'] = true;
                }
                if ( preg_match( '/position\s*:\s*fixed|z-index\s*:\s*(99999|999999)/i', $content ) ) {
                    $metrics['css_fixed_risk_files'][] = $relative;
                }
            } elseif ( 'json' === $ext ) {
                $metrics['json_files']++;
            }
        }

        $metrics['php_without_abspath_guard'] = array_values( array_unique( $metrics['php_without_abspath_guard'] ) );
        $metrics['files_with_request_input']  = array_values( array_unique( $metrics['files_with_request_input'] ) );
        $metrics['files_with_sanitizer']      = array_values( array_unique( $metrics['files_with_sanitizer'] ) );
        $metrics['files_with_output']         = array_values( array_unique( $metrics['files_with_output'] ) );
        $metrics['files_with_escaper']        = array_values( array_unique( $metrics['files_with_escaper'] ) );
        $metrics['secret_hits']               = array_values( array_unique( $metrics['secret_hits'] ) );
        $metrics['deprecated_hits']           = array_values( array_unique( $metrics['deprecated_hits'] ) );
        $metrics['debug_hits']                = array_values( array_unique( $metrics['debug_hits'] ) );
        $metrics['todo_hits']                 = array_values( array_unique( $metrics['todo_hits'] ) );
        $metrics['css_fixed_risk_files']      = array_values( array_unique( $metrics['css_fixed_risk_files'] ) );
        $metrics['js_clipboard_usage']        = array_values( array_unique( $metrics['js_clipboard_usage'] ) );
        $metrics['js_modern_syntax_hits']     = array_values( array_unique( $metrics['js_modern_syntax_hits'] ) );
        $metrics['js_browser_api_hits']       = array_values( array_unique( $metrics['js_browser_api_hits'] ) );
        $metrics['js_browser_api_without_guard'] = array_values( array_unique( $metrics['js_browser_api_without_guard'] ) );

        return $metrics;
    }

    private function read_plugin_header_from_files( array $files, string $root_path ): array {
        foreach ( $files as $file ) {
            if ( strtolower( pathinfo( $file, PATHINFO_EXTENSION ) ) !== 'php' ) {
                continue;
            }
            $content = is_file( $file ) ? file_get_contents( $file ) : false;
            if ( false === $content || ! preg_match( '/^[ \t\/*#@]*Plugin\s+Name\s*:/mi', substr( $content, 0, 8192 ) ) ) {
                continue;
            }
            $headers = array(
                'file'              => $this->relative_path( $file, $root_path ),
                'plugin_name'       => $this->header_value( $content, 'Plugin Name' ),
                'description'       => $this->header_value( $content, 'Description' ),
                'version'           => $this->header_value( $content, 'Version' ),
                'author'            => $this->header_value( $content, 'Author' ),
                'text_domain'       => $this->header_value( $content, 'Text Domain' ),
                'requires_at_least' => $this->header_value( $content, 'Requires at least' ),
                'requires_php'      => $this->header_value( $content, 'Requires PHP' ),
                'license'           => $this->header_value( $content, 'License' ),
            );
            return $headers;
        }

        return array();
    }

    private function header_value( string $content, string $name ): string {
        $pattern = '/^[ \t\/*#@]*' . preg_quote( $name, '/' ) . '\s*:\s*(.+)$/mi';
        if ( preg_match( $pattern, $content, $match ) ) {
            return trim( preg_replace( '/\s*\*\/$/', '', $match[1] ) );
        }
        return '';
    }

    private function standard_wordpress_header( array $header, array &$issues ): array {
        $required = array( 'plugin_name', 'description', 'version', 'author', 'text_domain', 'requires_at_least', 'requires_php' );
        $missing  = array();
        foreach ( $required as $key ) {
            if ( empty( $header[ $key ] ) ) {
                $missing[] = $key;
            }
        }
        if ( ! empty( $missing ) ) {
            $issues[] = $this->issue( 'warning', 'quality_header_incomplete', 'Plugin Header ยังไม่ครบตามมาตรฐานปล่อยใช้งาน', 'ควรเพิ่ม/ตรวจ header: ' . implode( ', ', $missing ), $header['file'] ?? '-', 1, 'standards', 90 );
        }
        if ( empty( $header['license'] ) ) {
            $issues[] = $this->issue( 'notice', 'quality_license_missing', 'ยังไม่พบ License ใน Plugin Header', 'ถ้าจะเผยแพร่หรือขายต่อ ควรระบุ License ให้ชัดเจน', $header['file'] ?? '-', 1, 'standards', 75 );
        }
        $score = 100 - ( count( $missing ) * 10 ) - ( empty( $header['license'] ) ? 5 : 0 );
        return $this->standard_row( 'WordPress Plugin Header', $score, empty( $missing ), 'ตรวจข้อมูลหัวปลั๊กอินที่ WordPress และผู้ดูแลเว็บต้องใช้' );
    }

    private function standard_direct_access_guard( array $metrics, array &$issues ): array {
        $missing = $metrics['php_without_abspath_guard'];
        if ( ! empty( $missing ) ) {
            $issues[] = $this->issue( 'warning', 'quality_missing_abspath_guard', 'บางไฟล์ PHP ไม่มี ABSPATH guard', 'ควรใส่ if ( ! defined( \'ABSPATH\' ) ) { exit; } ในไฟล์ PHP ทุกไฟล์ที่โหลดโดยตรงได้', implode( ', ', array_slice( $missing, 0, 6 ) ), 1, 'security', 92 );
        }
        $total = max( 1, (int) $metrics['php_files'] );
        $score = (int) round( 100 - ( count( $missing ) / $total * 100 ) );
        return $this->standard_row( 'Direct Access Guard', $score, empty( $missing ), 'ป้องกันการเรียกไฟล์ PHP ตรงจาก URL' );
    }

    private function standard_security_gate( array $metrics, array &$issues ): array {
        $deduct = 0;
        if ( ! empty( $metrics['secret_hits'] ) ) {
            $deduct += 40;
            $issues[] = $this->issue( 'critical', 'quality_secret_gate_failed', 'Security Gate ไม่ผ่าน: อาจมี secret/API key ในโค้ด', 'ต้องย้าย key/token ออกจากซอร์สโค้ดและ mask ก่อน export', implode( ', ', array_slice( $metrics['secret_hits'], 0, 6 ) ), 1, 'security', 100 );
        }
        if ( ! empty( $metrics['deprecated_hits'] ) ) {
            $deduct += 15;
            $issues[] = $this->issue( 'warning', 'quality_deprecated_php_found', 'พบ function เก่าหรือเสี่ยงไม่รองรับ PHP ใหม่', 'ควรเปลี่ยน mysql_query/create_function/ereg/split/each ไปใช้วิธีใหม่', implode( ', ', array_slice( $metrics['deprecated_hits'], 0, 6 ) ), 1, 'compatibility', 88 );
        }
        $has_admin_flow = ! empty( $metrics['files_with_ajax'] ) || ! empty( $metrics['files_with_request_input'] );
        if ( $has_admin_flow && empty( $metrics['files_with_capability'] ) ) {
            $deduct += 20;
            $issues[] = $this->issue( 'warning', 'quality_capability_gate_missing', 'พบ request/admin flow แต่ไม่พบ capability check ชัดเจน', 'ควรตรวจ current_user_can() ก่อนทำงานสำคัญ', '-', 0, 'security', 88 );
        }
        $score = max( 0, 100 - $deduct );
        return $this->standard_row( 'Security Gate', $score, $score >= 85, 'ตรวจ key หลุด, capability, deprecated security pattern' );
    }

    private function standard_ajax_rest_gate( array $metrics, array &$issues ): array {
        $deduct = 0;
        if ( ! empty( $metrics['files_with_ajax'] ) && empty( $metrics['files_with_nonce'] ) ) {
            $deduct += 35;
            $issues[] = $this->issue( 'warning', 'quality_ajax_nonce_missing', 'พบ AJAX/Admin post แต่ไม่พบ nonce ชัดเจน', 'ควรใช้ wp_nonce_field/check_admin_referer/check_ajax_referer/wp_verify_nonce', implode( ', ', array_slice( $metrics['files_with_ajax'], 0, 6 ) ), 1, 'security', 90 );
        }
        if ( ! empty( $metrics['rest_without_permission'] ) ) {
            $deduct += 40;
            $issues[] = $this->issue( 'critical', 'quality_rest_permission_gate_failed', 'REST Gate ไม่ผ่าน: REST route ไม่มี permission_callback', 'REST endpoint ทุกตัวควรมี permission_callback ที่เหมาะสม', implode( ', ', array_slice( $metrics['rest_without_permission'], 0, 6 ) ), 1, 'security', 100 );
        }
        $score = max( 0, 100 - $deduct );
        return $this->standard_row( 'AJAX / REST Gate', $score, $score >= 85, 'ตรวจ nonce และ permission ของ endpoint' );
    }

    private function standard_data_handling( array $metrics, array &$issues ): array {
        $request_files = $metrics['files_with_request_input'];
        $sanitize_files = $metrics['files_with_sanitizer'];
        $score = 100;
        if ( ! empty( $request_files ) && empty( $sanitize_files ) ) {
            $score = 45;
            $issues[] = $this->issue( 'warning', 'quality_data_sanitize_missing', 'Data Handling ยังไม่ผ่าน: รับข้อมูลแต่ไม่พบ sanitize', 'ข้อมูลจาก POST/GET/REQUEST/FILES/COOKIE ควร wp_unslash() และ sanitize ตามชนิดข้อมูล', implode( ', ', array_slice( $request_files, 0, 8 ) ), 1, 'security', 92 );
        } elseif ( count( $sanitize_files ) < count( $request_files ) / 2 ) {
            $score = 75;
            $issues[] = $this->issue( 'notice', 'quality_data_sanitize_low_coverage', 'Coverage การ sanitize อาจยังน้อย', 'ควรตรวจทุก request path ว่ามี sanitize ใกล้จุดใช้งาน', '-', 0, 'security', 70 );
        }
        return $this->standard_row( 'Data Handling', $score, $score >= 85, 'ตรวจ input → unslash → sanitize → process' );
    }

    private function standard_output_escaping( array $metrics, array &$issues ): array {
        $output_files = $metrics['files_with_output'];
        $escaper_files = $metrics['files_with_escaper'];
        $score = 100;
        if ( ! empty( $output_files ) && empty( $escaper_files ) ) {
            $score = 50;
            $issues[] = $this->issue( 'warning', 'quality_output_escape_missing', 'Output Escaping ยังไม่ผ่าน', 'ทุก output ควรใช้ esc_html/esc_attr/esc_url/esc_textarea/wp_kses_post ตามบริบท', implode( ', ', array_slice( $output_files, 0, 8 ) ), 1, 'security', 90 );
        } elseif ( count( $escaper_files ) < count( $output_files ) / 3 ) {
            $score = 78;
            $issues[] = $this->issue( 'notice', 'quality_output_escape_low_coverage', 'Coverage การ escape อาจยังน้อย', 'ควรตรวจ echo/print/wp_send_json ทุกจุดว่า escape ถูกบริบท', '-', 0, 'security', 70 );
        }
        return $this->standard_row( 'Output Escaping', $score, $score >= 85, 'ตรวจ process → render → escape output' );
    }

    private function standard_asset_loading( array $metrics, array &$issues ): array {
        $score = 100;
        if ( $metrics['enqueue_calls'] > 0 && $metrics['enqueue_without_version_hint'] > 0 ) {
            $score -= min( 45, $metrics['enqueue_without_version_hint'] * 12 );
            $issues[] = $this->issue( 'notice', 'quality_asset_version_hint', 'Asset บางตัวอาจไม่มี version/dependency ชัดเจน', 'ควร enqueue script/style พร้อม dependency และ version เพื่อลด cache เพี้ยน', '-', 0, 'performance', 72 );
        }
        return $this->standard_row( 'Asset Loading', max( 0, $score ), $score >= 85, 'ตรวจการโหลด JS/CSS ให้ cache และ dependency ชัดเจน' );
    }

    private function standard_compatibility( array $metrics, array $header, array &$issues ): array {
        $score = 100;
        $requires_php = $header['requires_php'] ?? '';
        if ( ! empty( $metrics['typed_property_files'] ) && $requires_php && version_compare( $requires_php, '7.4', '<' ) ) {
            $score -= 45;
            $issues[] = $this->issue( 'critical', 'quality_php_requirement_mismatch', 'Requires PHP ต่ำกว่า syntax ที่ใช้จริง', 'พบ typed property ซึ่งต้องใช้ PHP 7.4+ แต่ header ระบุ Requires PHP ต่ำกว่า 7.4', implode( ', ', array_slice( $metrics['typed_property_files'], 0, 6 ) ), 1, 'compatibility', 100 );
        }
        if ( empty( $requires_php ) ) {
            $score -= 10;
        }
        if ( empty( $header['requires_at_least'] ) ) {
            $score -= 10;
        }
        if ( ! empty( $metrics['deprecated_hits'] ) ) {
            $score -= 15;
        }
        return $this->standard_row( 'PHP / WordPress Compatibility', max( 0, $score ), $score >= 85, 'ตรวจ PHP/WordPress requirement เทียบกับโค้ดจริง' );
    }

    private function standard_cross_platform_compatibility( array $metrics, array &$issues ): array {
        $score = 100;

        if ( (int) $metrics['css_files'] > 0 && empty( $metrics['css_has_responsive_media'] ) ) {
            $score -= 18;
            $issues[] = $this->issue( 'notice', 'quality_responsive_media_missing', 'ยังไม่พบ responsive media query', 'ควรมี @media breakpoint เพื่อรองรับมือถือ แท็บเล็ต เดสก์ท็อป และหน้าจอแคบใน WordPress admin', '-', 0, 'cross_platform', 76 );
        }

        if ( (int) $metrics['css_files'] > 0 && empty( $metrics['css_has_word_wrap'] ) ) {
            $score -= 10;
            $issues[] = $this->issue( 'notice', 'quality_long_text_wrap_missing', 'ยังไม่พบ long-text wrapping ชัดเจน', 'ควรมี overflow-wrap/word-break เพื่อกันข้อความ error, path, code และ API response ล้นจอบนมือถือ', '-', 0, 'cross_platform', 72 );
        }

        if ( (int) $metrics['css_files'] > 0 && empty( $metrics['css_has_reduced_motion'] ) ) {
            $score -= 8;
            $issues[] = $this->issue( 'notice', 'quality_reduced_motion_missing', 'ยังไม่พบ prefers-reduced-motion', 'ควรลด animation/transition สำหรับผู้ใช้ที่ตั้งค่าลดการเคลื่อนไหวในระบบปฏิบัติการ', '-', 0, 'accessibility', 70 );
        }

        if ( (int) $metrics['css_files'] > 0 && empty( $metrics['css_has_safe_area'] ) ) {
            $score -= 8;
            $issues[] = $this->issue( 'notice', 'quality_safe_area_missing', 'ยังไม่พบ safe-area support', 'ควรใช้ env(safe-area-inset-*) ในส่วนที่ใกล้ขอบจอ เพื่อรองรับ iPhone/iPad/Android ที่มี gesture bar หรือ notch', '-', 0, 'cross_platform', 68 );
        }

        if ( (int) $metrics['css_files'] > 0 && empty( $metrics['css_has_forced_colors'] ) ) {
            $score -= 6;
        }

        if ( (int) $metrics['css_files'] > 0 && empty( $metrics['css_has_print_style'] ) ) {
            $score -= 4;
        }

        if ( ! empty( $metrics['js_clipboard_usage'] ) && empty( $metrics['js_clipboard_fallback'] ) ) {
            $score -= 20;
            $issues[] = $this->issue( 'warning', 'quality_clipboard_fallback_missing', 'ใช้ Clipboard API แต่ไม่พบ fallback ชัดเจน', 'navigator.clipboard อาจไม่ทำงานในบางบราวเซอร์/บาง context ควรมี document.execCommand fallback หรือแจ้งให้ copy เอง', implode( ', ', array_slice( $metrics['js_clipboard_usage'], 0, 6 ) ), 1, 'cross_platform', 84 );
        }

        if ( ! empty( $metrics['js_browser_api_without_guard'] ) ) {
            $score -= min( 18, count( $metrics['js_browser_api_without_guard'] ) * 6 );
            $issues[] = $this->issue( 'notice', 'quality_browser_api_guard_missing', 'พบ Browser API ที่ควรมี guard/fallback', 'ควรตรวจ typeof/feature detect หรือ try/catch ก่อนใช้ Browser API เพื่อรองรับ Safari/Firefox/Android WebView และ browser เก่า', implode( ', ', array_slice( $metrics['js_browser_api_without_guard'], 0, 6 ) ), 1, 'cross_platform', 74 );
        }

        if ( ! empty( $metrics['css_fixed_risk_files'] ) ) {
            $score -= min( 12, count( $metrics['css_fixed_risk_files'] ) * 4 );
        }

        return $this->standard_row( 'Browser / Device / System Compatibility', max( 0, $score ), $score >= 82, 'ตรวจ responsive, mobile viewport, safe-area, reduced motion, forced colors, print, Clipboard fallback และ Browser API guard' );
    }

    private function standard_maintainability( array $metrics, array &$issues ): array {
        $score = 100;
        $largest = $metrics['largest_php_file'];
        if ( ! empty( $largest['lines'] ) && (int) $largest['lines'] > 1200 ) {
            $score -= 30;
            $issues[] = $this->issue( 'notice', 'quality_large_php_file', 'ไฟล์ PHP ใหญ่มาก อาจดูแลยาก', 'ควรแยก module/class ย่อยถ้าไฟล์เกิน 1,200 บรรทัด', $largest['file'], 1, 'maintainability', 70 );
        } elseif ( ! empty( $largest['lines'] ) && (int) $largest['lines'] > 800 ) {
            $score -= 12;
            $issues[] = $this->issue( 'notice', 'quality_php_file_growing', 'ไฟล์ PHP เริ่มใหญ่', 'ควรวางแผนแยก module เพื่อให้ซ่อมง่ายในอนาคต', $largest['file'], 1, 'maintainability', 65 );
        }
        if ( ! empty( $metrics['debug_hits'] ) ) {
            $score -= min( 25, count( $metrics['debug_hits'] ) * 6 );
            $issues[] = $this->issue( 'notice', 'quality_debug_marker_found', 'พบ debug output/log marker', 'ควรลบ console.log/var_dump/print_r/error_log ที่ไม่จำเป็นก่อนปล่อยจริง', implode( ', ', array_slice( $metrics['debug_hits'], 0, 8 ) ), 1, 'maintainability', 70 );
        }
        if ( ! empty( $metrics['todo_hits'] ) ) {
            $score -= min( 18, count( $metrics['todo_hits'] ) * 4 );
        }
        return $this->standard_row( 'Maintainability', max( 0, $score ), $score >= 80, 'ตรวจขนาดไฟล์ จุด debug และจุดค้างงาน' );
    }

    private function standard_i18n( array $metrics, array &$issues ): array {
        $score = 100;
        if ( $metrics['php_files'] > 0 && 0 === (int) $metrics['textdomain_hits'] ) {
            $score -= 25;
            $issues[] = $this->issue( 'notice', 'quality_textdomain_missing', 'ยังไม่พบ Text Domain', 'ควรมี Text Domain ใน header เพื่อรองรับภาษา', '-', 0, 'standards', 72 );
        }
        if ( $metrics['php_files'] > 0 && (int) $metrics['translation_calls'] < 3 ) {
            $score -= 20;
            $issues[] = $this->issue( 'notice', 'quality_i18n_low_usage', 'การรองรับภาษายังน้อย', 'ข้อความในแอดมินควรใช้ __(), esc_html__(), esc_attr__() หรือฟังก์ชันแปลภาษาที่เหมาะสม', '-', 0, 'standards', 65 );
        }
        return $this->standard_row( 'Internationalization', max( 0, $score ), $score >= 75, 'ตรวจความพร้อมด้านภาษาและ text domain' );
    }

    private function standard_export_safety( array $metrics, array &$issues ): array {
        $score = 100;
        if ( ! empty( $metrics['secret_hits'] ) ) {
            $score -= 45;
        }
        if ( ! $metrics['readme_found'] ) {
            $score -= 8;
        }
        if ( ! $metrics['uninstall_found'] ) {
            $score -= 7;
            $issues[] = $this->issue( 'notice', 'quality_uninstall_missing', 'ยังไม่พบ uninstall.php', 'ถ้าปลั๊กอินบันทึก option/transient/table ควรมีแนวทางล้างข้อมูลตอนถอนติดตั้ง', '-', 0, 'lifecycle', 60 );
        }
        return $this->standard_row( 'Export / Release Safety', max( 0, $score ), $score >= 85, 'ตรวจความพร้อมก่อน export/ส่งต่อ/ปล่อยใช้งาน' );
    }

    private function summarize_standards( array $standards, array $issues, array $metrics ): array {
        $total = 0;
        $passed = 0;
        $critical = 0;
        $warning = 0;
        $notice = 0;
        foreach ( $standards as $standard ) {
            $total += (int) ( $standard['score'] ?? 0 );
            if ( ! empty( $standard['passed'] ) ) {
                $passed++;
            }
        }
        foreach ( $issues as $issue ) {
            $severity = $issue['severity'] ?? 'notice';
            if ( 'critical' === $severity ) {
                $critical++;
            } elseif ( 'warning' === $severity ) {
                $warning++;
            } else {
                $notice++;
            }
        }
        $count = max( 1, count( $standards ) );
        $score = (int) round( $total / $count );
        if ( $critical > 0 ) {
            $score = min( $score, 69 );
        }

        return array(
            'quality_standard_score' => max( 0, min( 100, $score ) ),
            'standards_total'        => count( $standards ),
            'standards_passed'       => $passed,
            'standards_failed'       => count( $standards ) - $passed,
            'critical'               => $critical,
            'warning'                => $warning,
            'notice'                 => $notice,
            'php_files'              => $metrics['php_files'],
            'total_lines'            => $metrics['total_lines'],
            'largest_php_file'       => $metrics['largest_php_file'],
            'css_has_responsive_media' => $metrics['css_has_responsive_media'],
            'css_has_reduced_motion'   => $metrics['css_has_reduced_motion'],
            'css_has_safe_area'        => $metrics['css_has_safe_area'],
            'css_has_forced_colors'    => $metrics['css_has_forced_colors'],
            'css_has_print_style'      => $metrics['css_has_print_style'],
            'js_clipboard_fallback'    => $metrics['js_clipboard_fallback'],
            'browser_api_guard_files'  => count( $metrics['js_browser_api_without_guard'] ),
        );
    }

    private function build_release_gate( array $summary, array $standards, array $issues ): array {
        $blocked_codes = array();
        foreach ( $issues as $issue ) {
            if ( 'critical' === ( $issue['severity'] ?? '' ) ) {
                $blocked_codes[] = $issue['code'] ?? 'critical_issue';
            }
        }

        $score = (int) ( $summary['quality_standard_score'] ?? 0 );
        $pass = $score >= 85 && empty( $blocked_codes );
        $status = $pass ? 'pass' : ( $score >= 70 ? 'conditional' : 'fail' );
        $message = 'pass' === $status
            ? 'ผ่านมาตรฐานสำหรับใช้งานบน staging/production แบบระมัดระวัง'
            : ( 'conditional' === $status ? 'ผ่านบางส่วน แต่ควรแก้รายการ warning/critical ก่อนปล่อยจริง' : 'ยังไม่ควรปล่อยใช้งานจริง' );

        return array(
            'status'        => $status,
            'score'         => $score,
            'blocked_codes' => array_values( array_unique( $blocked_codes ) ),
            'message'       => $message,
        );
    }

    private function standard_row( string $name, int $score, bool $passed, string $description ): array {
        return array(
            'name'        => $name,
            'score'       => max( 0, min( 100, $score ) ),
            'passed'      => $passed,
            'description' => $description,
        );
    }

    private function issue( string $severity, string $code, string $title, string $detail, string $file, int $line, string $area, int $confidence ): array {
        return array(
            'severity'   => $severity,
            'code'       => $code,
            'title'      => $title,
            'detail'     => $detail,
            'file'       => $file,
            'line'       => $line,
            'area'       => $area,
            'priority'   => 'critical' === $severity ? 'must_fix' : ( 'warning' === $severity ? 'should_fix' : 'review' ),
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
}
