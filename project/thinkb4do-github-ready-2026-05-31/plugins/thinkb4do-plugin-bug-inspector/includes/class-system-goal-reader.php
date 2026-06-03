<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Reads the intended goal of a plugin from headers, names, file layout, symbols and WP integration points.
 * This is intentionally static-only: it does not execute target plugin code.
 */
class TBPBI_System_Goal_Reader {
    /**
     * @param array  $files Absolute file paths.
     * @param string $root_path Absolute plugin root.
     * @param array  $meta Plugin metadata from WordPress or upload scanner.
     * @return array
     */
    public function analyze( array $files, string $root_path, array $meta = array() ): array {
        $root_path = wp_normalize_path( trailingslashit( $root_path ) );
        $signals   = $this->collect_signals( $files, $root_path, $meta );
        $features  = $this->classify_features( $signals );
        $goal      = $this->infer_goal( $features, $signals, $meta );
        $gaps      = $this->detect_gaps( $features, $signals, $goal );
        $scores    = $this->score_goal( $signals, $features, $gaps, $goal );
        $issues    = $this->build_issues( $gaps, $scores );

        return array(
            'goal'          => $goal,
            'scores'        => $scores,
            'signals'       => $signals,
            'features'      => $features,
            'gaps'          => $gaps,
            'issues'        => $issues,
            'recommendation'=> $this->build_recommendation( $goal, $scores, $gaps ),
        );
    }

    private function collect_signals( array $files, string $root_path, array $meta ): array {
        $signals = array(
            'header'          => array(
                'name'        => (string) ( $meta['name'] ?? '' ),
                'version'     => (string) ( $meta['version'] ?? '' ),
                'description' => (string) ( $meta['description'] ?? $meta['Description'] ?? '' ),
                'author'      => (string) ( $meta['author'] ?? $meta['Author'] ?? '' ),
            ),
            'tokens'          => array(),
            'folders'         => array(),
            'files'           => array(),
            'classes'         => array(),
            'functions'       => array(),
            'hooks'           => array(),
            'shortcodes'      => array(),
            'ajax'            => array(),
            'rest'            => array(),
            'assets'          => array(),
            'readme_keywords' => array(),
        );

        $token_text = implode( ' ', array_filter( $signals['header'] ) );

        foreach ( $files as $file ) {
            $file = wp_normalize_path( $file );
            $rel  = $this->relative_path( $file, $root_path );
            $signals['files'][] = $rel;

            $parts = explode( '/', $rel );
            if ( count( $parts ) > 1 ) {
                $signals['folders'][] = $parts[0];
            }
            $token_text .= ' ' . str_replace( array( '/', '-', '_', '.', '\\' ), ' ', $rel );

            if ( ! is_readable( $file ) ) {
                continue;
            }
            $content = file_get_contents( $file );
            if ( false === $content ) {
                continue;
            }

            if ( preg_match( '/Plugin\s+Name\s*:\s*(.+)/i', $content, $m ) ) {
                $signals['header']['name'] = trim( $m[1] );
                $token_text .= ' ' . $m[1];
            }
            if ( preg_match( '/Description\s*:\s*(.+)/i', $content, $m ) ) {
                $signals['header']['description'] = trim( $m[1] );
                $token_text .= ' ' . $m[1];
            }

            if ( 'php' === strtolower( pathinfo( $file, PATHINFO_EXTENSION ) ) ) {
                if ( preg_match_all( '/\bclass\s+([A-Za-z_][A-Za-z0-9_]*)/i', $content, $m ) ) {
                    $signals['classes'] = array_merge( $signals['classes'], $m[1] );
                    $token_text .= ' ' . implode( ' ', $m[1] );
                }
                if ( preg_match_all( '/\bfunction\s+([A-Za-z_][A-Za-z0-9_]*)\s*\(/i', $content, $m ) ) {
                    $signals['functions'] = array_merge( $signals['functions'], $m[1] );
                    $token_text .= ' ' . implode( ' ', $m[1] );
                }
                if ( preg_match_all( '/add_(action|filter)\s*\(\s*[\'\"]([^\'\"]+)/i', $content, $m ) ) {
                    $signals['hooks'] = array_merge( $signals['hooks'], $m[2] );
                    $token_text .= ' ' . implode( ' ', $m[2] );
                }
                if ( preg_match_all( '/add_shortcode\s*\(\s*[\'\"]([^\'\"]+)/i', $content, $m ) ) {
                    $signals['shortcodes'] = array_merge( $signals['shortcodes'], $m[1] );
                    $token_text .= ' shortcode ' . implode( ' ', $m[1] );
                }
                if ( preg_match_all( '/wp_ajax(?:_nopriv)?_([A-Za-z0-9_\-]+)/i', $content, $m ) ) {
                    $signals['ajax'] = array_merge( $signals['ajax'], $m[1] );
                    $token_text .= ' ajax ' . implode( ' ', $m[1] );
                }
                if ( preg_match_all( '/register_rest_route\s*\(\s*[\'\"]([^\'\"]+)[\'\"]\s*,\s*[\'\"]([^\'\"]+)/i', $content, $m, PREG_SET_ORDER ) ) {
                    foreach ( $m as $route ) {
                        $signals['rest'][] = $route[1] . $route[2];
                        $token_text .= ' rest api ' . $route[1] . ' ' . $route[2];
                    }
                }
                if ( preg_match_all( '/wp_enqueue_(?:script|style)\s*\(\s*[\'\"]([^\'\"]+)/i', $content, $m ) ) {
                    $signals['assets'] = array_merge( $signals['assets'], $m[1] );
                    $token_text .= ' assets ' . implode( ' ', $m[1] );
                }
            }

            if ( in_array( strtolower( basename( $file ) ), array( 'readme.txt', 'readme.md' ), true ) ) {
                $plain = wp_strip_all_tags( $content );
                $signals['readme_keywords'] = array_slice( $this->tokenize( $plain ), 0, 80 );
                $token_text .= ' ' . $plain;
            }
        }

        $signals['folders']   = array_values( array_unique( array_filter( $signals['folders'] ) ) );
        $signals['classes']   = array_values( array_unique( $signals['classes'] ) );
        $signals['functions'] = array_values( array_unique( $signals['functions'] ) );
        $signals['hooks']     = array_values( array_unique( $signals['hooks'] ) );
        $signals['shortcodes']= array_values( array_unique( $signals['shortcodes'] ) );
        $signals['ajax']      = array_values( array_unique( $signals['ajax'] ) );
        $signals['rest']      = array_values( array_unique( $signals['rest'] ) );
        $signals['assets']    = array_values( array_unique( $signals['assets'] ) );
        $signals['tokens']    = $this->tokenize( $token_text );

        return $signals;
    }

    private function classify_features( array $signals ): array {
        $tokens = $signals['tokens'];
        $text   = ' ' . implode( ' ', $tokens ) . ' ';
        $maps   = array(
            'plugin_debugging' => array( 'debug', 'bug', 'inspector', 'scan', 'scanner', 'syntax', 'error', 'issue', 'fix', 'report', 'quality', 'qa' ),
            'export_report'    => array( 'export', 'download', 'report', 'txt', 'text', 'markdown', 'copy', 'log' ),
            'relationship_map' => array( 'relationship', 'relation', 'graph', 'dependency', 'include', 'require', 'hook', 'trace', 'xray', 'x ray' ),
            'security_review'  => array( 'security', 'nonce', 'sanitize', 'escape', 'permission', 'capability', 'secret', 'token', 'apikey', 'api key' ),
            'admin_tool'       => array( 'admin', 'dashboard', 'settings', 'menu', 'manage', 'options' ),
            'api_integration'  => array( 'api', 'rest', 'endpoint', 'remote', 'webhook', 'connection', 'http' ),
            'ai_assistant'     => array( 'ai', 'aira', 'assistant', 'chat', 'prompt', 'memory', 'room', 'voice', 'agent' ),
            'frontend_ui'      => array( 'frontend', 'shortcode', 'widget', 'elementor', 'gutenberg', 'ui', 'ux', 'composer', 'popup' ),
            'database'         => array( 'database', 'wpdb', 'table', 'query', 'dbdelta', 'migration' ),
            'media_voice'      => array( 'voice', 'audio', 'camera', 'microphone', 'image', 'upload', 'media' ),
        );

        $features = array();
        foreach ( $maps as $feature => $needles ) {
            $hits = array();
            foreach ( $needles as $needle ) {
                if ( strpos( $text, ' ' . strtolower( $needle ) . ' ' ) !== false || strpos( $text, strtolower( $needle ) ) !== false ) {
                    $hits[] = $needle;
                }
            }
            if ( ! empty( $hits ) ) {
                $features[ $feature ] = array(
                    'score' => min( 100, 28 + ( count( $hits ) * 12 ) ),
                    'hits'  => array_values( array_unique( $hits ) ),
                );
            }
        }

        return $features;
    }

    private function infer_goal( array $features, array $signals, array $meta ): array {
        uasort(
            $features,
            static function ( $a, $b ) {
                return (int) ( $b['score'] ?? 0 ) <=> (int) ( $a['score'] ?? 0 );
            }
        );
        $feature_keys = array_keys( $features );
        $name = $signals['header']['name'] ?: ( $meta['name'] ?? 'Unknown Plugin' );
        $description = $signals['header']['description'] ?: ( $meta['description'] ?? '' );

        $system_type = 'WordPress Plugin';
        $primary_goal = 'ปลั๊กอิน WordPress สำหรับเพิ่มความสามารถเฉพาะทางให้เว็บไซต์';
        $target_users = array( 'ผู้ดูแลเว็บ WordPress' );

        if ( isset( $features['plugin_debugging'] ) && isset( $features['relationship_map'] ) ) {
            $system_type = 'Developer Tool / Plugin QA Inspector';
            $primary_goal = 'ตรวจบั๊กปลั๊กอิน WordPress อ่านโครงสร้าง ตรวจความสัมพันธ์ภายในระบบ และสร้างรายงานเพื่อใช้ซ่อม/พัฒนาต่อ';
            $target_users = array( 'ผู้ดูแลเว็บ', 'นักพัฒนาปลั๊กอิน', 'เจ้าของระบบ Thinkb4do/AiRA' );
        } elseif ( isset( $features['ai_assistant'] ) ) {
            $system_type = 'AI Assistant / Automation Plugin';
            $primary_goal = 'สร้างผู้ช่วย AI สำหรับสนทนา จัดการคำสั่ง ความจำ และระบบอัตโนมัติบน WordPress';
            $target_users = array( 'เจ้าของเว็บ', 'ทีมคอนเทนต์', 'ผู้ใช้หน้าเว็บ', 'ผู้ดูแลระบบ' );
        } elseif ( isset( $features['api_integration'] ) ) {
            $system_type = 'API Integration Plugin';
            $primary_goal = 'เชื่อมต่อ WordPress กับ API หรือบริการภายนอกอย่างเป็นระบบ';
            $target_users = array( 'ผู้ดูแลเว็บ', 'นักพัฒนา', 'ทีมระบบ' );
        }

        return array(
            'name'              => $name,
            'description'       => $description,
            'system_type'       => $system_type,
            'primary_goal'      => $primary_goal,
            'target_users'      => $target_users,
            'primary_features'  => array_slice( $feature_keys, 0, 8 ),
            'confidence_reason' => 'อ่านจาก Plugin Header, ชื่อไฟล์/โฟลเดอร์, class/function, hook, shortcode, AJAX, REST และ readme',
        );
    }

    private function detect_gaps( array $features, array $signals, array $goal ): array {
        $gaps = array();
        $required = array();

        if ( 'Developer Tool / Plugin QA Inspector' === $goal['system_type'] ) {
            $required = array(
                'plugin_debugging' => 'ควรมีตัวตรวจบั๊ก/สแกนไฟล์',
                'relationship_map' => 'ควรมีตัวตรวจความสัมพันธ์ภายในระบบ',
                'export_report'    => 'ควร export รายงานได้',
                'security_review'  => 'ควรตรวจ security และข้อมูลลับ',
                'admin_tool'       => 'ควรมีหน้าแอดมินควบคุม',
            );
        } elseif ( 'AI Assistant / Automation Plugin' === $goal['system_type'] ) {
            $required = array(
                'ai_assistant'    => 'ควรมีแกนสนทนา/ประมวลคำสั่ง',
                'api_integration' => 'ควรมีระบบ API Center',
                'admin_tool'      => 'ควรมีหน้าแอดมินตั้งค่า',
                'frontend_ui'     => 'ควรมี UI ฝั่งผู้ใช้หรือ composer',
            );
        } else {
            $required = array(
                'admin_tool' => 'ควรมีหน้าแอดมินตั้งค่าหรือจัดการระบบ',
            );
        }

        foreach ( $required as $key => $message ) {
            if ( empty( $features[ $key ] ) ) {
                $gaps[] = array(
                    'feature'  => $key,
                    'severity' => 'warning',
                    'message'  => $message . ' แต่ยังอ่านสัญญาณไม่พบชัดเจน',
                    'fix'      => 'เพิ่มไฟล์/คลาส/เมนู/ฟังก์ชันที่สื่อบทบาทนี้ให้ชัด และเชื่อมเข้ากับไฟล์หลัก',
                );
            }
        }

        if ( ! empty( $signals['ajax'] ) && empty( $features['security_review'] ) ) {
            $gaps[] = array(
                'feature'  => 'ajax_security_alignment',
                'severity' => 'warning',
                'message'  => 'พบ AJAX แต่เป้าหมายด้าน security ยังไม่เด่นพอ',
                'fix'      => 'เพิ่ม nonce, capability check และ error handling ในทุก AJAX action',
            );
        }

        if ( ! empty( $signals['rest'] ) && empty( $features['api_integration'] ) ) {
            $gaps[] = array(
                'feature'  => 'rest_goal_alignment',
                'severity' => 'notice',
                'message'  => 'พบ REST route แต่ Plugin Header/README ยังไม่อธิบายเป้าหมาย API ชัดเจน',
                'fix'      => 'อธิบาย REST/API responsibility ใน readme หรือ description',
            );
        }

        return $gaps;
    }

    private function score_goal( array $signals, array $features, array $gaps, array $goal ): array {
        $header_score = 0;
        if ( ! empty( $signals['header']['name'] ) ) {
            $header_score += 25;
        }
        if ( ! empty( $signals['header']['description'] ) ) {
            $header_score += 35;
        }
        if ( ! empty( $signals['readme_keywords'] ) ) {
            $header_score += 15;
        }
        if ( ! empty( $signals['functions'] ) || ! empty( $signals['classes'] ) ) {
            $header_score += 15;
        }
        if ( ! empty( $signals['hooks'] ) || ! empty( $signals['ajax'] ) || ! empty( $signals['rest'] ) ) {
            $header_score += 10;
        }
        $goal_clarity = min( 100, $header_score );

        $feature_alignment = min( 100, 45 + ( count( $features ) * 8 ) - ( count( $gaps ) * 9 ) );
        $feature_alignment = max( 0, $feature_alignment );

        $confidence = (int) round( ( $goal_clarity * 0.55 ) + ( $feature_alignment * 0.45 ) );

        return array(
            'goal_clarity'      => $goal_clarity,
            'feature_alignment' => $feature_alignment,
            'confidence'        => $confidence,
            'gap_count'         => count( $gaps ),
        );
    }

    private function build_issues( array $gaps, array $scores ): array {
        $issues = array();
        if ( (int) $scores['goal_clarity'] < 65 ) {
            $issues[] = array(
                'severity'   => 'warning',
                'code'       => 'goal_clarity_low',
                'title'      => 'อ่านเป้าหมายระบบได้ไม่ชัดพอ',
                'detail'     => 'Plugin Header, readme หรือชื่อ class/function ยังสื่อเป้าหมายระบบไม่ชัด ทำให้การประเมินระบบอาจคลาดเคลื่อน',
                'file'       => '-',
                'line'       => 0,
                'area'       => 'system_goal',
                'priority'   => 'P2',
                'confidence' => 82,
                'fix'        => 'เพิ่ม Description, readme และชื่อไฟล์/คลาสให้บอกหน้าที่หลักของระบบอย่างตรงไปตรงมา',
            );
        }

        foreach ( $gaps as $gap ) {
            $issues[] = array(
                'severity'   => $gap['severity'],
                'code'       => 'goal_gap_' . sanitize_key( $gap['feature'] ),
                'title'      => 'พบช่องว่างระหว่างเป้าหมายกับโค้ด: ' . $gap['feature'],
                'detail'     => $gap['message'],
                'file'       => '-',
                'line'       => 0,
                'area'       => 'system_goal',
                'priority'   => 'P2',
                'confidence' => 78,
                'fix'        => $gap['fix'],
            );
        }

        return $issues;
    }

    private function build_recommendation( array $goal, array $scores, array $gaps ): string {
        if ( empty( $gaps ) && (int) $scores['confidence'] >= 80 ) {
            return 'เป้าหมายระบบค่อนข้างชัด และฟีเจอร์หลักที่พบสอดคล้องกับเป้าหมาย สามารถใช้รายงานนี้เป็นฐานตรวจบั๊กต่อได้';
        }
        return 'ควรปรับคำอธิบายระบบและเชื่อมฟีเจอร์ที่ยังขาดให้ชัดขึ้นก่อนปิดงาน เพื่อให้ระบบอ่านเป้าหมายและตรวจความครบได้แม่นกว่าเดิม';
    }

    private function tokenize( string $text ): array {
        $text = strtolower( $text );
        $text = preg_replace( '/[^a-z0-9_\-ก-๙]+/u', ' ', $text );
        $parts = preg_split( '/\s+/', (string) $text, -1, PREG_SPLIT_NO_EMPTY );
        return array_values( array_unique( array_slice( $parts, 0, 1200 ) ) );
    }

    private function relative_path( string $file, string $root_path ): string {
        $file = wp_normalize_path( $file );
        if ( strpos( $file, $root_path ) === 0 ) {
            return ltrim( substr( $file, strlen( $root_path ) ), '/' );
        }
        return basename( $file );
    }
}
