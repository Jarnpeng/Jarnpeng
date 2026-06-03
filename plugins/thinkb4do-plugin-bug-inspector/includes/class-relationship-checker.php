<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TBPBI_Relationship_Checker {
    /**
     * ตรวจความสัมพันธ์ภายในระบบ: ไฟล์, include/require, function, class, hook, shortcode, AJAX, REST และ asset handle
     *
     * @param array  $files รายชื่อไฟล์แบบ absolute path
     * @param string $root_path root directory ของปลั๊กอินที่ถูกตรวจ
     * @return array
     */
    public function analyze( array $files, string $root_path ): array {
        $root_path = wp_normalize_path( trailingslashit( $root_path ) );
        $data      = $this->collect_data( $files, $root_path );
        $issues    = array();

        $issues = array_merge( $issues, $this->check_missing_includes( $data, $root_path ) );
        $issues = array_merge( $issues, $this->check_duplicates( $data ) );
        $issues = array_merge( $issues, $this->check_ajax_security_relationships( $data ) );
        $issues = array_merge( $issues, $this->check_rest_relationships( $data ) );
        $issues = array_merge( $issues, $this->check_assets( $data ) );
        $issues = array_merge( $issues, $this->check_lonely_files( $data ) );

        $score = $this->calculate_relationship_score( $issues );

        return array(
            'score'   => $score,
            'summary' => array(
                'php_files'       => count( $data['php_files'] ),
                'includes'        => count( $data['includes'] ),
                'functions'       => count( $data['functions'] ),
                'classes'         => count( $data['classes'] ),
                'actions'         => count( $data['actions'] ),
                'filters'         => count( $data['filters'] ),
                'shortcodes'      => count( $data['shortcodes'] ),
                'ajax_actions'    => count( $data['ajax_actions'] ),
                'rest_routes'     => count( $data['rest_routes'] ),
                'asset_handles'   => count( $data['asset_handles'] ),
                'relationship_ok' => $score >= 80,
            ),
            'issues'  => $issues,
            'graph'   => $this->build_graph( $data ),
        );
    }

    private function collect_data( array $files, string $root_path ): array {
        $data = array(
            'php_files'     => array(),
            'all_files'     => array(),
            'includes'      => array(),
            'functions'     => array(),
            'classes'       => array(),
            'actions'       => array(),
            'filters'       => array(),
            'shortcodes'    => array(),
            'ajax_actions'  => array(),
            'rest_routes'   => array(),
            'asset_handles' => array(),
            'file_content'  => array(),
            'file_refs'     => array(),
        );

        foreach ( $files as $file ) {
            $file = wp_normalize_path( $file );
            $rel  = $this->relative_path( $file, $root_path );
            $data['all_files'][ $rel ] = $file;

            if ( strtolower( pathinfo( $file, PATHINFO_EXTENSION ) ) !== 'php' ) {
                continue;
            }

            $content = file_get_contents( $file );
            if ( false === $content ) {
                continue;
            }

            $data['php_files'][ $rel ]    = $file;
            $data['file_content'][ $rel ] = $content;

            $this->collect_includes( $content, $rel, $file, $root_path, $data );
            $this->collect_functions( $content, $rel, $data );
            $this->collect_classes( $content, $rel, $data );
            $this->collect_hooks( $content, $rel, $data );
            $this->collect_shortcodes( $content, $rel, $data );
            $this->collect_ajax( $content, $rel, $data );
            $this->collect_rest_routes( $content, $rel, $data );
            $this->collect_assets( $content, $rel, $data );
        }

        return $data;
    }

    private function collect_includes( string $content, string $rel, string $file, string $root_path, array &$data ): void {
        $pattern = '/\b(require|require_once|include|include_once)\s*\(?\s*(?:__DIR__\s*\.\s*|dirname\s*\(\s*__FILE__\s*\)\s*\.\s*|plugin_dir_path\s*\(\s*__FILE__\s*\)\s*\.\s*|[A-Z0-9_]+_PATH\s*\.\s*)?[\'\"]([^\'\"]+)[\'\"]/i';
        preg_match_all( $pattern, $content, $matches, PREG_OFFSET_CAPTURE );

        foreach ( $matches[2] as $index => $match ) {
            $keyword_offset = $matches[1][ $index ][1] ?? 0;
            $before_keyword = $keyword_offset > 0 ? substr( $content, max( 0, $keyword_offset - 1 ), 1 ) : '';
            if ( '' !== $before_keyword && ! preg_match( '/\s|[;({]/', $before_keyword ) ) {
                continue;
            }
            $raw_path = trim( $match[0] );
            $line     = $this->line_number( $content, $match[1] );
            $resolved = $this->resolve_include_path( $raw_path, $file, $root_path );
            $target   = $resolved ? $this->relative_path( $resolved, $root_path ) : $raw_path;

            $data['includes'][] = array(
                'type'     => $matches[1][ $index ][0],
                'source'   => $rel,
                'target'   => $target,
                'raw'      => $raw_path,
                'resolved' => $resolved,
                'line'     => $line,
            );
            $data['file_refs'][ $target ][] = $rel;
        }
    }

    private function collect_functions( string $content, string $rel, array &$data ): void {
        preg_match_all( '/\bfunction\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\(/', $content, $matches, PREG_OFFSET_CAPTURE );
        foreach ( $matches[1] as $match ) {
            $prefix = substr( $content, max( 0, $match[1] - 80 ), 80 );
            if ( preg_match( '/(public|private|protected|static|abstract|final)\s+function\s+$/i', $prefix ) ) {
                continue;
            }
            $name = $match[0];
            $data['functions'][] = array(
                'name' => $name,
                'file' => $rel,
                'line' => $this->line_number( $content, $match[1] ),
            );
        }
    }

    private function collect_classes( string $content, string $rel, array &$data ): void {
        preg_match_all( '/\b(?:class|trait|interface)\s+([a-zA-Z_][a-zA-Z0-9_]*)\b/', $content, $matches, PREG_OFFSET_CAPTURE );
        foreach ( $matches[1] as $match ) {
            $data['classes'][] = array(
                'name' => $match[0],
                'file' => $rel,
                'line' => $this->line_number( $content, $match[1] ),
            );
        }
    }

    private function collect_hooks( string $content, string $rel, array &$data ): void {
        preg_match_all( '/\badd_action\s*\(\s*[\'\"]([^\'\"]+)[\'\"]\s*,\s*([^\n\r;\)]+)/', $content, $matches, PREG_OFFSET_CAPTURE );
        foreach ( $matches[1] as $index => $match ) {
            $hook = $match[0];
            $data['actions'][] = array(
                'hook'     => $hook,
                'callback' => trim( $matches[2][ $index ][0] ),
                'file'     => $rel,
                'line'     => $this->line_number( $content, $match[1] ),
            );
        }

        preg_match_all( '/\badd_filter\s*\(\s*[\'\"]([^\'\"]+)[\'\"]\s*,\s*([^\n\r;\)]+)/', $content, $matches, PREG_OFFSET_CAPTURE );
        foreach ( $matches[1] as $index => $match ) {
            $hook = $match[0];
            $data['filters'][] = array(
                'hook'     => $hook,
                'callback' => trim( $matches[2][ $index ][0] ),
                'file'     => $rel,
                'line'     => $this->line_number( $content, $match[1] ),
            );
        }
    }

    private function collect_shortcodes( string $content, string $rel, array &$data ): void {
        preg_match_all( '/\badd_shortcode\s*\(\s*[\'\"]([^\'\"]+)[\'\"]\s*,\s*([^\n\r;\)]+)/', $content, $matches, PREG_OFFSET_CAPTURE );
        foreach ( $matches[1] as $index => $match ) {
            $data['shortcodes'][] = array(
                'name'     => $match[0],
                'callback' => trim( $matches[2][ $index ][0] ),
                'file'     => $rel,
                'line'     => $this->line_number( $content, $match[1] ),
            );
        }
    }

    private function collect_ajax( string $content, string $rel, array &$data ): void {
        preg_match_all( '/\badd_action\s*\(\s*[\'\"]wp_ajax_([^\'\"]+)[\'\"]\s*,\s*([^\n\r;\)]+)/', $content, $matches, PREG_OFFSET_CAPTURE );
        foreach ( $matches[1] as $index => $match ) {
            $data['ajax_actions'][] = array(
                'action'   => $match[0],
                'callback' => trim( $matches[2][ $index ][0] ),
                'nopriv'   => false,
                'file'     => $rel,
                'line'     => $this->line_number( $content, $match[1] ),
            );
        }

        preg_match_all( '/\badd_action\s*\(\s*[\'\"]wp_ajax_nopriv_([^\'\"]+)[\'\"]\s*,\s*([^\n\r;\)]+)/', $content, $matches, PREG_OFFSET_CAPTURE );
        foreach ( $matches[1] as $index => $match ) {
            $data['ajax_actions'][] = array(
                'action'   => $match[0],
                'callback' => trim( $matches[2][ $index ][0] ),
                'nopriv'   => true,
                'file'     => $rel,
                'line'     => $this->line_number( $content, $match[1] ),
            );
        }
    }

    private function collect_rest_routes( string $content, string $rel, array &$data ): void {
        preg_match_all( '/register_rest_route\s*\((.{0,1200}?)\);/s', $content, $matches, PREG_OFFSET_CAPTURE );
        foreach ( $matches[1] as $match ) {
            $args = $match[0];
            preg_match( '/[\'\"]([^\'\"]+)[\'\"]\s*,\s*[\'\"]([^\'\"]+)[\'\"]/', $args, $route_match );
            $namespace = $route_match[1] ?? 'unknown_namespace';
            $route     = $route_match[2] ?? 'unknown_route';

            $data['rest_routes'][] = array(
                'route'               => trailingslashit( $namespace ) . ltrim( $route, '/' ),
                'has_permission_cb'   => strpos( $args, 'permission_callback' ) !== false,
                'has_callback'        => strpos( $args, 'callback' ) !== false,
                'file'                => $rel,
                'line'                => $this->line_number( $content, $match[1] ),
            );
        }
    }

    private function collect_assets( string $content, string $rel, array &$data ): void {
        preg_match_all( '/\bwp_enqueue_(script|style)\s*\(\s*[\'\"]([^\'\"]+)[\'\"]/', $content, $matches, PREG_OFFSET_CAPTURE );
        foreach ( $matches[2] as $index => $match ) {
            $data['asset_handles'][] = array(
                'type' => $matches[1][ $index ][0],
                'name' => $match[0],
                'file' => $rel,
                'line' => $this->line_number( $content, $match[1] ),
            );
        }
    }

    private function check_missing_includes( array $data, string $root_path ): array {
        $issues = array();
        foreach ( $data['includes'] as $include ) {
            if ( ! $include['resolved'] || ! file_exists( $include['resolved'] ) ) {
                $issues[] = $this->issue(
                    'critical',
                    'missing_include_target',
                    'พบ include/require ชี้ไปยังไฟล์ที่ไม่มีอยู่จริง',
                    'ไฟล์ต้นทาง ' . $include['source'] . ' เรียกไฟล์ ' . $include['raw'] . ' แต่ระบบหาไฟล์ปลายทางไม่พบ',
                    $include['source'],
                    $include['line']
                );
            }
        }
        return $issues;
    }

    private function check_duplicates( array $data ): array {
        $issues = array();

        foreach ( array( 'functions' => 'function', 'classes' => 'class/interface/trait' ) as $key => $label ) {
            $seen = array();
            foreach ( $data[ $key ] as $item ) {
                $lower = strtolower( $item['name'] );
                $seen[ $lower ][] = $item;
            }
            foreach ( $seen as $name => $items ) {
                if ( count( $items ) > 1 ) {
                    $locations = array_map(
                        static function ( $i ) {
                            return $i['file'] . ':' . $i['line'];
                        },
                        $items
                    );
                    $issues[] = $this->issue(
                        'critical',
                        'duplicate_' . $key,
                        'พบ ' . $label . ' ซ้ำภายในปลั๊กอิน',
                        $items[0]['name'] . ' ซ้ำที่ ' . implode( ', ', $locations ),
                        $items[0]['file'],
                        $items[0]['line']
                    );
                }
            }
        }

        foreach ( array( 'shortcodes' => 'shortcode', 'asset_handles' => 'asset handle' ) as $key => $label ) {
            $seen = array();
            foreach ( $data[ $key ] as $item ) {
                $name = strtolower( ( $item['type'] ?? '' ) . ':' . $item['name'] );
                $seen[ $name ][] = $item;
            }
            foreach ( $seen as $name => $items ) {
                if ( count( $items ) > 1 ) {
                    $issues[] = $this->issue(
                        'warning',
                        'duplicate_' . $key,
                        'พบ ' . $label . ' ซ้ำ',
                        $items[0]['name'] . ' ถูกประกาศมากกว่า 1 จุด อาจทำให้ระบบชนกันหรือโหลดทับกัน',
                        $items[0]['file'],
                        $items[0]['line']
                    );
                }
            }
        }

        return $issues;
    }

    private function check_ajax_security_relationships( array $data ): array {
        $issues = array();

        foreach ( $data['ajax_actions'] as $ajax ) {
            $content = $data['file_content'][ $ajax['file'] ] ?? '';
            $has_nonce = ( strpos( $content, 'check_ajax_referer' ) !== false || strpos( $content, 'wp_verify_nonce' ) !== false );
            $has_cap   = ( strpos( $content, 'current_user_can' ) !== false );

            if ( ! $has_nonce ) {
                $issues[] = $this->issue(
                    'warning',
                    'ajax_missing_nonce_relationship',
                    'AJAX action อาจยังไม่มี nonce ตรวจสอบความตั้งใจของผู้ใช้',
                    'Action wp_ajax_' . $ajax['action'] . ' ควรเชื่อมกับ check_ajax_referer() หรือ wp_verify_nonce() เพื่อกัน CSRF',
                    $ajax['file'],
                    $ajax['line']
                );
            }

            if ( ! $ajax['nopriv'] && ! $has_cap ) {
                $issues[] = $this->issue(
                    'warning',
                    'ajax_missing_capability_relationship',
                    'AJAX action หลังบ้านอาจยังไม่มี capability check',
                    'Action wp_ajax_' . $ajax['action'] . ' ควรเชื่อมกับ current_user_can() ก่อนแก้ไขข้อมูลสำคัญ',
                    $ajax['file'],
                    $ajax['line']
                );
            }
        }

        return $issues;
    }

    private function check_rest_relationships( array $data ): array {
        $issues = array();
        foreach ( $data['rest_routes'] as $route ) {
            if ( ! $route['has_permission_cb'] ) {
                $issues[] = $this->issue(
                    'critical',
                    'rest_missing_permission_callback',
                    'REST route ไม่มี permission_callback',
                    'Route ' . $route['route'] . ' ควรกำหนด permission_callback เพื่อบอกว่าใครเรียกใช้ endpoint นี้ได้',
                    $route['file'],
                    $route['line']
                );
            }

            if ( ! $route['has_callback'] ) {
                $issues[] = $this->issue(
                    'warning',
                    'rest_missing_callback',
                    'REST route อาจไม่มี callback',
                    'Route ' . $route['route'] . ' ควรมี callback สำหรับประมวลผลคำขอ',
                    $route['file'],
                    $route['line']
                );
            }
        }
        return $issues;
    }

    private function check_assets( array $data ): array {
        $issues = array();
        foreach ( $data['asset_handles'] as $asset ) {
            if ( preg_match( '/\s|[^a-zA-Z0-9_\-\.]/', $asset['name'] ) ) {
                $issues[] = $this->issue(
                    'notice',
                    'asset_handle_name_risk',
                    'ชื่อ asset handle อาจไม่เหมาะสม',
                    'Handle ' . $asset['name'] . ' ควรใช้ตัวอักษร/ตัวเลข/ขีดกลาง/underscore เพื่อเลี่ยงปัญหาการอ้างอิง',
                    $asset['file'],
                    $asset['line']
                );
            }
        }
        return $issues;
    }

    private function check_lonely_files( array $data ): array {
        $issues = array();
        $php_files = array_keys( $data['php_files'] );

        foreach ( $php_files as $rel ) {
            if ( basename( $rel ) === 'index.php' || basename( $rel ) === 'uninstall.php' ) {
                continue;
            }

            $is_main_like = preg_match( '/Plugin\s+Name\s*:/i', $data['file_content'][ $rel ] ?? '' );
            if ( $is_main_like ) {
                continue;
            }

            $referenced = isset( $data['file_refs'][ $rel ] );
            if ( ! $referenced && strpos( $rel, 'vendor/' ) === false ) {
                $issues[] = $this->issue(
                    'notice',
                    'possibly_unlinked_php_file',
                    'ไฟล์ PHP นี้อาจยังไม่ถูกเชื่อมเข้าระบบหลัก',
                    $rel . ' ไม่พบการ include/require แบบตรงตัวจากไฟล์อื่น หากตั้งใจโหลดด้วย autoload ให้ข้ามข้อนี้ได้',
                    $rel,
                    1
                );
            }
        }

        return $issues;
    }

    private function build_graph( array $data ): array {
        $edges = array();
        foreach ( $data['includes'] as $include ) {
            $edges[] = array(
                'from' => $include['source'],
                'to'   => $include['target'],
                'type' => $include['type'],
            );
        }

        return array(
            'nodes' => array_values( array_keys( $data['php_files'] ) ),
            'edges' => $edges,
        );
    }

    private function calculate_relationship_score( array $issues ): int {
        $score = 100;
        foreach ( $issues as $issue ) {
            if ( 'critical' === $issue['severity'] ) {
                $score -= 12;
            } elseif ( 'warning' === $issue['severity'] ) {
                $score -= 6;
            } else {
                $score -= 2;
            }
        }
        return max( 0, min( 100, $score ) );
    }

    private function issue( string $severity, string $code, string $title, string $detail, string $file, int $line ): array {
        return array(
            'severity' => $severity,
            'code'     => $code,
            'title'    => $title,
            'detail'   => $detail,
            'file'     => $file,
            'line'     => $line,
        );
    }

    private function resolve_include_path( string $raw_path, string $source_file, string $root_path ): ?string {
        $raw_path = ltrim( $raw_path, '/\\' );
        $source_dir = dirname( $source_file );

        $candidates = array(
            wp_normalize_path( $source_dir . '/' . $raw_path ),
            wp_normalize_path( $root_path . $raw_path ),
        );

        foreach ( $candidates as $candidate ) {
            if ( file_exists( $candidate ) ) {
                return $candidate;
            }
        }

        return $candidates[0] ?? null;
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
