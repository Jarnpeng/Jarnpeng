<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TBPBI_Scanner {
    private TBPBI_Relationship_Checker $relationship_checker;
    private TBPBI_Detail_Analyzer $detail_analyzer;
    private TBPBI_System_Goal_Reader $goal_reader;
    private TBPBI_System_Xray_Analyzer $xray_analyzer;
    private TBPBI_Quality_Standard_Checker $quality_checker;

    public function __construct() {
        $this->relationship_checker = new TBPBI_Relationship_Checker();
        $this->detail_analyzer       = new TBPBI_Detail_Analyzer();
        $this->goal_reader           = new TBPBI_System_Goal_Reader();
        $this->xray_analyzer         = new TBPBI_System_Xray_Analyzer();
        $this->quality_checker       = new TBPBI_Quality_Standard_Checker();
    }

    /**
     * @param string $root_path absolute plugin directory
     * @param array  $meta plugin metadata
     * @return array
     */
    public function scan_directory( string $root_path, array $meta = array() ): array {
        $started_at = microtime( true );
        $single_file = null;
        $root_path   = wp_normalize_path( $root_path );

        if ( is_file( $root_path ) ) {
            $single_file = $root_path;
            $root_path   = wp_normalize_path( trailingslashit( dirname( $single_file ) ) );
        } else {
            $root_path = wp_normalize_path( trailingslashit( $root_path ) );
        }

        $files = $single_file ? array( $single_file ) : $this->collect_files( $root_path );
        $issues = array();
        $stats = array(
            'total_files' => count( $files ),
            'php_files'   => 0,
            'js_files'    => 0,
            'css_files'   => 0,
            'size_bytes'  => 0,
        );

        foreach ( $files as $file ) {
            $ext = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
            $stats['size_bytes'] += is_file( $file ) ? filesize( $file ) : 0;
            if ( 'php' === $ext ) {
                $stats['php_files']++;
                $issues = array_merge( $issues, $this->scan_php_file( $file, $root_path ) );
            } elseif ( 'js' === $ext ) {
                $stats['js_files']++;
                $issues = array_merge( $issues, $this->scan_js_file( $file, $root_path ) );
            } elseif ( 'css' === $ext ) {
                $stats['css_files']++;
                $issues = array_merge( $issues, $this->scan_css_file( $file, $root_path ) );
            }
        }

        $issues = array_merge( $issues, $this->check_plugin_structure( $root_path, $files ) );

        $relationship = $this->relationship_checker->analyze( $files, $root_path );
        $issues       = array_merge( $issues, $relationship['issues'] );

        $detail = $this->detail_analyzer->analyze( $files, $root_path );
        $issues = array_merge( $issues, $detail['issues'] );

        $goal = $this->goal_reader->analyze( $files, $root_path, $meta );
        $issues = array_merge( $issues, $goal['issues'] );

        if ( get_option( 'tbpbi_deep_xray_mode', '1' ) === '1' ) {
            $xray = $this->xray_analyzer->analyze( $files, $root_path );
            $issues = array_merge( $issues, $xray['issues'] );
        } else {
            $xray = array(
                'score'        => 100,
                'summary'      => array(),
                'entry_points' => array(),
                'modules'      => array(),
                'dependencies' => array(),
                'data_flow'    => array(),
                'orphan_files' => array(),
                'issues'       => array(),
                'topology'     => array(),
            );
        }

        $quality_standard = $this->quality_checker->analyze( $files, $root_path, $meta );
        $issues = array_merge( $issues, $quality_standard['issues'] );

        $score = $this->calculate_score( $issues, $relationship, $detail, $goal, $xray, $quality_standard );

        return array(
            'plugin'       => wp_parse_args(
                $meta,
                array(
                    'name'    => basename( untrailingslashit( $root_path ) ),
                    'version' => '-',
                    'source'  => 'directory',
                )
            ),
            'scan'         => array(
                'date'          => current_time( 'mysql' ),
                'duration_ms'   => (int) round( ( microtime( true ) - $started_at ) * 1000 ),
                'safe_mode'     => get_option( 'tbpbi_safe_static_mode', '1' ) === '1',
                'mask_secrets'  => get_option( 'tbpbi_mask_sensitive_data', '1' ) === '1',
                'deep_xray'     => get_option( 'tbpbi_deep_xray_mode', '1' ) === '1',
                'cross_platform' => get_option( 'tbpbi_cross_platform_mode', '1' ) === '1',
            ),
            'stats'        => $stats,
            'score'        => $score,
            'relationship' => $relationship,
            'detail'       => $detail,
            'goal'         => $goal,
            'xray'         => $xray,
            'quality_standard' => $quality_standard,
            'issues'       => $this->sort_issues( $issues ),
        );
    }

    private function collect_files( string $root_path ): array {
        $files = array();
        if ( ! is_dir( $root_path ) ) {
            return $files;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator( $root_path, FilesystemIterator::SKIP_DOTS )
        );

        foreach ( $iterator as $file_info ) {
            if ( ! $file_info->isFile() ) {
                continue;
            }

            $path = wp_normalize_path( $file_info->getPathname() );
            if ( $this->should_skip_file( $path ) ) {
                continue;
            }
            $files[] = $path;
        }

        sort( $files );
        return $files;
    }

    private function should_skip_file( string $path ): bool {
        $skip_parts = array(
            '/.git/',
            '/node_modules/',
            '/.cache/',
            '/vendor/bin/',
            '/reports/',
            '/tbpbi-scans/',
        );

        foreach ( $skip_parts as $part ) {
            if ( strpos( $path, $part ) !== false ) {
                return true;
            }
        }

        $ext = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
        $allowed = array( 'php', 'js', 'css', 'json', 'txt', 'md' );
        return $ext && ! in_array( $ext, $allowed, true );
    }

    private function check_plugin_structure( string $root_path, array $files ): array {
        $issues = array();
        $main_files = array();

        foreach ( $files as $file ) {
            if ( strtolower( pathinfo( $file, PATHINFO_EXTENSION ) ) !== 'php' ) {
                continue;
            }
            $content = file_get_contents( $file );
            if ( false !== $content && preg_match( '/^[ \t\/*#@]*Plugin\s+Name\s*:/mi', substr( $content, 0, 8192 ) ) ) {
                $main_files[] = $file;
                if ( ! preg_match( '/Version\s*:/i', $content ) ) {
                    $issues[] = $this->issue( 'notice', 'plugin_header_missing_version', 'Plugin header อาจไม่มี Version', 'ไฟล์หลักควรมี Version เพื่อช่วยตรวจการอัปเกรด', $file, $root_path, 1 );
                }
                if ( ! preg_match( '/Description\s*:/i', $content ) ) {
                    $issues[] = $this->issue( 'notice', 'plugin_header_missing_description', 'Plugin header อาจไม่มี Description', 'ไฟล์หลักควรมี Description เพื่อให้หน้า Plugins อ่านง่าย', $file, $root_path, 1 );
                }
            }
        }

        if ( empty( $main_files ) ) {
            $issues[] = array(
                'severity' => 'critical',
                'code'     => 'missing_plugin_header',
                'title'    => 'ไม่พบไฟล์หลักของปลั๊กอิน',
                'detail'   => 'ไม่พบ header Plugin Name: ในไฟล์ PHP ใด ๆ ของแพ็กนี้',
                'file'     => '-',
                'line'     => 0,
            );
        }

        if ( ! file_exists( $root_path . 'index.php' ) ) {
            $issues[] = array(
                'severity' => 'notice',
                'code'     => 'missing_index_php',
                'title'    => 'ไม่มี index.php กัน directory listing',
                'detail'   => 'แนะนำให้มี index.php เปล่าในโฟลเดอร์สำคัญเพื่อลดการเปิดดูไฟล์จากเว็บเซิร์ฟเวอร์บางแบบ',
                'file'     => 'index.php',
                'line'     => 1,
            );
        }

        return $issues;
    }

    private function scan_php_file( string $file, string $root_path ): array {
        $issues = array();
        $content = file_get_contents( $file );
        if ( false === $content ) {
            return array( $this->issue( 'warning', 'unreadable_file', 'อ่านไฟล์ไม่ได้', 'ระบบไม่สามารถอ่านไฟล์นี้ได้', $file, $root_path, 0 ) );
        }

        $issues = array_merge( $issues, $this->check_php_open_tag( $content, $file, $root_path ) );
        $issues = array_merge( $issues, $this->check_bracket_balance( $content, $file, $root_path ) );
        $issues = array_merge( $issues, $this->check_sensitive_patterns( $content, $file, $root_path ) );
        $issues = array_merge( $issues, $this->check_wordpress_security_patterns( $content, $file, $root_path ) );
        $issues = array_merge( $issues, $this->check_todo_markers( $content, $file, $root_path ) );

        return $issues;
    }

    private function scan_js_file( string $file, string $root_path ): array {
        $issues = array();
        $content = file_get_contents( $file );
        if ( false === $content ) {
            return $issues;
        }

        if ( preg_match( '/console\.log\s*\(/', $content, $m, PREG_OFFSET_CAPTURE ) ) {
            $issues[] = $this->issue( 'notice', 'js_console_log_found', 'พบ console.log ในไฟล์ JavaScript', 'ควรลบหรือปิด console.log ก่อนใช้งานจริง', $file, $root_path, $this->line_number( $content, $m[0][1] ) );
        }

        if ( preg_match( '/localStorage\.setItem\s*\([^;]*(api|key|token|secret)/i', $content, $m, PREG_OFFSET_CAPTURE ) ) {
            $issues[] = $this->issue( 'warning', 'js_possible_secret_localstorage', 'อาจมีการเก็บข้อมูลลับใน localStorage', 'ไม่ควรเก็บ API key/token/secret ใน localStorage ถ้าเป็นข้อมูลสำคัญ', $file, $root_path, $this->line_number( $content, $m[0][1] ) );
        }

        return $issues;
    }

    private function scan_css_file( string $file, string $root_path ): array {
        $issues = array();
        $content = file_get_contents( $file );
        if ( false === $content ) {
            return $issues;
        }

        if ( preg_match( '/z-index\s*:\s*999999|position\s*:\s*fixed/i', $content, $m, PREG_OFFSET_CAPTURE ) ) {
            $issues[] = $this->issue( 'notice', 'css_overlay_risk', 'CSS อาจมี overlay/fixed element ที่ทับ UI ได้', 'พบ position fixed หรือ z-index สูงมาก ควรตรวจไม่ให้ทับ admin bar/composer/popup', $file, $root_path, $this->line_number( $content, $m[0][1] ) );
        }

        return $issues;
    }

    private function check_php_open_tag( string $content, string $file, string $root_path ): array {
        if ( strpos( ltrim( $content ), '<?php' ) !== 0 ) {
            return array( $this->issue( 'warning', 'php_open_tag_risk', 'ไฟล์ PHP อาจไม่ได้เริ่มด้วย <?php', 'ควรเริ่มไฟล์ PHP ด้วย <?php เพื่อเลี่ยง output แปลกก่อน header', $file, $root_path, 1 ) );
        }
        return array();
    }

    private function check_bracket_balance( string $content, string $file, string $root_path ): array {
        $issues = array();
        $code_only = '';
        if ( function_exists( 'token_get_all' ) ) {
            $tokens = token_get_all( $content );
            foreach ( $tokens as $token ) {
                if ( is_array( $token ) ) {
                    if ( in_array( $token[0], array( T_COMMENT, T_DOC_COMMENT, T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE ), true ) ) {
                        continue;
                    }
                    $code_only .= $token[1];
                } else {
                    $code_only .= $token;
                }
            }
        } else {
            $code_only = preg_replace( '/[\"\'](?:\\.|[^\\\"\'])*[\"\']/', '', $content );
        }

        $pairs = array(
            '(' => ')',
            '[' => ']',
            '{' => '}',
        );
        foreach ( $pairs as $open => $close ) {
            $open_count  = substr_count( $code_only, $open );
            $close_count = substr_count( $code_only, $close );
            if ( $open_count !== $close_count ) {
                $issues[] = $this->issue( 'critical', 'bracket_balance_' . md5( $open ), 'จำนวนวงเล็บเปิด/ปิดไม่เท่ากัน', 'พบ ' . $open . ' จำนวน ' . $open_count . ' และ ' . $close . ' จำนวน ' . $close_count . ' ควรตรวจ syntax จุดนี้', $file, $root_path, 1 );
            }
        }
        return $issues;
    }

    private function check_sensitive_patterns( string $content, string $file, string $root_path ): array {
        $issues = array();
        $patterns = array(
            'api_key_literal' => '/(api[_\- ]?key|secret|token|bearer)\s*[=:]\s*[\'\"][^\'\"]{12,}[\'\"]/i',
            'openai_like_key' => '/sk-[A-Za-z0-9_\-]{20,}/',
            'google_like_key' => '/AIza[0-9A-Za-z_\-]{20,}/',
        );

        foreach ( $patterns as $code => $pattern ) {
            if ( preg_match( $pattern, $content, $m, PREG_OFFSET_CAPTURE ) ) {
                $issues[] = $this->issue( 'critical', $code, 'อาจพบ API key/token ฝังในโค้ด', 'ควรย้ายข้อมูลลับไปเก็บใน wp_options แบบเข้าถึงเฉพาะแอดมิน หรือ config ที่ปลอดภัย และเปิดการ mask ก่อน export', $file, $root_path, $this->line_number( $content, $m[0][1] ) );
            }
        }

        return $issues;
    }

    private function check_wordpress_security_patterns( string $content, string $file, string $root_path ): array {
        $issues = array();

        if ( preg_match( '/\$_(POST|GET|REQUEST)\s*\[/', $content ) && ! preg_match( '/wp_unslash|sanitize_|esc_|absint|intval|floatval|sanitize_text_field|sanitize_key/i', $content ) ) {
            $issues[] = $this->issue( 'warning', 'input_without_sanitize_hint', 'พบการรับค่า request แต่ไม่พบการ sanitize ชัดเจน', 'ควรใช้ wp_unslash() และ sanitize_* หรือ absint() กับข้อมูลจาก POST/GET/REQUEST', $file, $root_path, 1 );
        }

        if ( preg_match( '/echo\s+\$_(POST|GET|REQUEST)/', $content ) ) {
            $issues[] = $this->issue( 'critical', 'direct_echo_request', 'พบการ echo request โดยตรง', 'ควร sanitize และ escape output ด้วย esc_html(), esc_attr(), wp_kses_post() ตามบริบท', $file, $root_path, 1 );
        }

        if ( preg_match( '/register_rest_route\s*\(/', $content ) && strpos( $content, 'permission_callback' ) === false ) {
            $issues[] = $this->issue( 'critical', 'rest_permission_missing_file_level', 'ไฟล์นี้มี REST route แต่ไม่พบ permission_callback', 'REST API ควรมี permission_callback ทุก route', $file, $root_path, 1 );
        }

        return $issues;
    }

    private function check_todo_markers( string $content, string $file, string $root_path ): array {
        $code_only = $this->code_without_strings_and_comments( $content );
        if ( preg_match( '/\b(TODO|FIXME|HACK)\b/i', $code_only, $m, PREG_OFFSET_CAPTURE ) ) {
            return array( $this->issue( 'notice', 'todo_marker_found', 'พบ TODO/FIXME/HACK ในโค้ด', 'ควรทบทวนจุดที่ยังค้างก่อนปล่อยใช้งานจริง', $file, $root_path, $this->line_number( $code_only, $m[0][1] ) ) );
        }
        return array();
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

    private function calculate_score( array $issues, array $relationship = array(), array $detail = array(), array $goal = array(), array $xray = array(), array $quality_standard = array() ): array {
        $score = 100;
        $counts = array(
            'critical' => 0,
            'warning'  => 0,
            'notice'   => 0,
        );

        foreach ( $issues as $issue ) {
            $severity = $issue['severity'] ?? 'notice';
            if ( isset( $counts[ $severity ] ) ) {
                $counts[ $severity ]++;
            }
            if ( 'critical' === $severity ) {
                $score -= 10;
            } elseif ( 'warning' === $severity ) {
                $score -= 5;
            } else {
                $score -= 1;
            }
        }

        $score = max( 0, min( 100, $score ) );
        $relationship_score = (int) ( $relationship['score'] ?? $score );
        $detail_score       = (int) ( $detail['summary']['detail_score'] ?? $score );
        $goal_score         = (int) ( $goal['scores']['confidence'] ?? $score );
        $goal_clarity       = (int) ( $goal['scores']['goal_clarity'] ?? $score );
        $alignment_score    = (int) ( $goal['scores']['feature_alignment'] ?? $score );
        $xray_score         = (int) ( $xray['score'] ?? $score );
        $standard_score     = (int) ( $quality_standard['score'] ?? $score );
        $release_gate_score = (int) ( $quality_standard['release_gate']['score'] ?? $standard_score );
        $overall            = (int) round( ( $score * 0.25 ) + ( $relationship_score * 0.15 ) + ( $detail_score * 0.15 ) + ( $goal_score * 0.12 ) + ( $xray_score * 0.13 ) + ( $standard_score * 0.20 ) );

        return array(
            'overall'      => max( 0, min( 100, $overall ) ),
            'quality'      => max( 0, $score - ( $counts['notice'] > 10 ? 5 : 0 ) ),
            'performance'  => max( 0, 100 - ( $counts['warning'] * 4 ) - ( $counts['critical'] * 8 ) ),
            'security'     => max( 0, 100 - ( $counts['critical'] * 12 ) - ( $counts['warning'] * 4 ) ),
            'relationship' => $relationship_score,
            'detail'       => $detail_score,
            'goal'         => $goal_score,
            'goal_clarity' => $goal_clarity,
            'alignment'    => $alignment_score,
            'xray'         => $xray_score,
            'standard'     => $standard_score,
            'release_gate' => $release_gate_score,
            'counts'       => $counts,
        );
    }

    private function sort_issues( array $issues ): array {
        $rank = array(
            'critical' => 0,
            'warning'  => 1,
            'notice'   => 2,
        );

        usort(
            $issues,
            static function ( $a, $b ) use ( $rank ) {
                $ra = $rank[ $a['severity'] ?? 'notice' ] ?? 3;
                $rb = $rank[ $b['severity'] ?? 'notice' ] ?? 3;
                if ( $ra === $rb ) {
                    return strcmp( (string) ( $a['file'] ?? '' ), (string) ( $b['file'] ?? '' ) );
                }
                return $ra <=> $rb;
            }
        );

        return $issues;
    }

    private function issue( string $severity, string $code, string $title, string $detail, string $file, string $root_path, int $line ): array {
        return array(
            'severity' => $severity,
            'code'     => $code,
            'title'    => $title,
            'detail'   => $detail,
            'file'     => $this->relative_path( $file, $root_path ),
            'line'     => $line,
        );
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
