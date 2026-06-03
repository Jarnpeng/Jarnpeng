<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TBPBI_Report_Builder {
    public function build_text( array $result ): string {
        $lines = array();
        $plugin = $result['plugin'] ?? array();
        $score = $result['score'] ?? array();
        $stats = $result['stats'] ?? array();
        $relationship = $result['relationship'] ?? array();
        $detail = $result['detail'] ?? array();
        $goal = $result['goal'] ?? array();
        $xray = $result['xray'] ?? array();
        $quality_standard = $result['quality_standard'] ?? array();
        $issues = $result['issues'] ?? array();

        $lines[] = 'Thinkb4do Plugin Bug Inspector Report';
        $lines[] = 'Version: ' . TBPBI_VERSION . ' — Goal Reader + X-Ray + Quality Standard Audit';
        $lines[] = str_repeat( '=', 72 );
        $lines[] = 'Plugin: ' . ( $plugin['name'] ?? '-' );
        $lines[] = 'Version: ' . ( $plugin['version'] ?? '-' );
        $lines[] = 'Source: ' . ( $plugin['source'] ?? '-' );
        $lines[] = 'Scan Date: ' . ( $result['scan']['date'] ?? '-' );
        $lines[] = 'Duration: ' . ( $result['scan']['duration_ms'] ?? 0 ) . ' ms';
        $lines[] = 'Safe Static Mode: ' . ( ! empty( $result['scan']['safe_mode'] ) ? 'ON' : 'OFF' );
        $lines[] = 'Mask Sensitive Data: ' . ( ! empty( $result['scan']['mask_secrets'] ) ? 'ON' : 'OFF' );
        $lines[] = 'Cross-Platform Audit: ' . ( ! empty( $result['scan']['cross_platform'] ) ? 'ON' : 'OFF' );
        $lines[] = '';

        $lines[] = '[Overall Score]';
        $lines[] = 'Overall: ' . ( $score['overall'] ?? 0 ) . '%';
        $lines[] = 'Quality: ' . ( $score['quality'] ?? 0 ) . '%';
        $lines[] = 'Performance: ' . ( $score['performance'] ?? 0 ) . '%';
        $lines[] = 'Security: ' . ( $score['security'] ?? 0 ) . '%';
        $lines[] = 'Relationship: ' . ( $score['relationship'] ?? ( $relationship['score'] ?? 0 ) ) . '%';
        $lines[] = 'Detail Scan: ' . ( $score['detail'] ?? ( $detail['summary']['detail_score'] ?? 0 ) ) . '%';
        $lines[] = 'Goal Reader: ' . ( $score['goal'] ?? ( $goal['scores']['confidence'] ?? 0 ) ) . '%';
        $lines[] = 'Feature Alignment: ' . ( $score['alignment'] ?? ( $goal['scores']['feature_alignment'] ?? 0 ) ) . '%';
        $lines[] = 'System X-Ray: ' . ( $score['xray'] ?? ( $xray['score'] ?? 0 ) ) . '%';
        $lines[] = 'Quality Standard: ' . ( $score['standard'] ?? ( $quality_standard['score'] ?? 0 ) ) . '%';
        $lines[] = 'Release Gate: ' . ( $score['release_gate'] ?? ( $quality_standard['release_gate']['score'] ?? 0 ) ) . '%';
        $lines[] = '';

        $lines[] = '[File Stats]';
        $lines[] = 'Total Files: ' . ( $stats['total_files'] ?? 0 );
        $lines[] = 'PHP Files: ' . ( $stats['php_files'] ?? 0 );
        $lines[] = 'JS Files: ' . ( $stats['js_files'] ?? 0 );
        $lines[] = 'CSS Files: ' . ( $stats['css_files'] ?? 0 );
        $lines[] = 'Size: ' . size_format( (int) ( $stats['size_bytes'] ?? 0 ) );
        $lines[] = '';

        $quality_summary = $quality_standard['summary'] ?? array();
        $release_gate = $quality_standard['release_gate'] ?? array();
        $lines[] = '[Quality Standard Audit]';
        $lines[] = 'Quality Standard Score: ' . ( $quality_standard['score'] ?? 0 ) . '%';
        $lines[] = 'Standards Passed: ' . ( $quality_summary['standards_passed'] ?? 0 ) . '/' . ( $quality_summary['standards_total'] ?? 0 );
        $lines[] = 'Release Gate: ' . strtoupper( (string) ( $release_gate['status'] ?? 'unknown' ) ) . ' — ' . ( $release_gate['message'] ?? '-' );
        if ( ! empty( $release_gate['blocked_codes'] ) ) {
            $lines[] = 'Blocked By: ' . implode( ', ', $release_gate['blocked_codes'] );
        }
        $standards = $quality_standard['standards'] ?? array();
        if ( empty( $standards ) ) {
            $lines[] = '- No standard audit generated.';
        } else {
            foreach ( $standards as $standard ) {
                $lines[] = '- ' . ( ! empty( $standard['passed'] ) ? 'PASS' : 'REVIEW' ) . ' | ' . ( $standard['name'] ?? '-' ) . ': ' . ( $standard['score'] ?? 0 ) . '% — ' . ( $standard['description'] ?? '-' );
            }
        }
        $lines[] = '';

        $compat_standard = $standards['cross_platform_compatibility'] ?? array();
        $compat_metrics = $quality_standard['metrics'] ?? array();
        if ( ! empty( $compat_standard ) ) {
            $lines[] = '[Browser / Device / System Compatibility]';
            $lines[] = 'Compatibility Score: ' . ( $compat_standard['score'] ?? 0 ) . '%';
            $lines[] = 'Responsive Media: ' . ( ! empty( $compat_metrics['css_has_responsive_media'] ) ? 'PASS' : 'REVIEW' );
            $lines[] = 'Safe Area / Notch: ' . ( ! empty( $compat_metrics['css_has_safe_area'] ) ? 'PASS' : 'REVIEW' );
            $lines[] = 'Reduced Motion: ' . ( ! empty( $compat_metrics['css_has_reduced_motion'] ) ? 'PASS' : 'REVIEW' );
            $lines[] = 'Forced Colors / High Contrast: ' . ( ! empty( $compat_metrics['css_has_forced_colors'] ) ? 'PASS' : 'REVIEW' );
            $lines[] = 'Print Mode: ' . ( ! empty( $compat_metrics['css_has_print_style'] ) ? 'PASS' : 'REVIEW' );
            $lines[] = 'Clipboard Fallback: ' . ( empty( $compat_metrics['js_clipboard_usage'] ) || ! empty( $compat_metrics['js_clipboard_fallback'] ) ? 'PASS' : 'REVIEW' );
            $lines[] = 'Browser API Guard Files: ' . count( $compat_metrics['js_browser_api_without_guard'] ?? array() );
            $lines[] = 'Target Matrix: Chrome, Edge, Firefox, Safari/iOS, Samsung Internet, Android WebView, Desktop, Tablet, Mobile, WordPress Admin';
            $lines[] = '';
        }

        $goal_data = $goal['goal'] ?? array();
        $goal_scores = $goal['scores'] ?? array();
        $lines[] = '[System Goal Reading]';
        $lines[] = 'System Type: ' . ( $goal_data['system_type'] ?? '-' );
        $lines[] = 'Primary Goal: ' . ( $goal_data['primary_goal'] ?? '-' );
        $lines[] = 'Goal Clarity: ' . ( $goal_scores['goal_clarity'] ?? 0 ) . '%';
        $lines[] = 'Feature Alignment: ' . ( $goal_scores['feature_alignment'] ?? 0 ) . '%';
        $lines[] = 'Confidence: ' . ( $goal_scores['confidence'] ?? 0 ) . '%';
        if ( ! empty( $goal_data['target_users'] ) ) {
            $lines[] = 'Target Users: ' . implode( ', ', $goal_data['target_users'] );
        }
        if ( ! empty( $goal_data['primary_features'] ) ) {
            $lines[] = 'Primary Features Detected: ' . implode( ', ', $goal_data['primary_features'] );
        }
        if ( ! empty( $goal['recommendation'] ) ) {
            $lines[] = 'Recommendation: ' . $goal['recommendation'];
        }
        $lines[] = '';

        $features = $goal['features'] ?? array();
        $lines[] = '[Feature Signals]';
        if ( empty( $features ) ) {
            $lines[] = '- No clear feature signals detected.';
        } else {
            foreach ( $features as $feature => $data ) {
                $lines[] = '- ' . $feature . ': ' . ( $data['score'] ?? 0 ) . '% | hits: ' . implode( ', ', array_slice( $data['hits'] ?? array(), 0, 12 ) );
            }
        }
        $lines[] = '';

        $gaps = $goal['gaps'] ?? array();
        $lines[] = '[Goal vs Code Gaps]';
        if ( empty( $gaps ) ) {
            $lines[] = '- No major gap detected between inferred goal and code signals.';
        } else {
            foreach ( $gaps as $gap ) {
                $lines[] = '- [' . strtoupper( $gap['severity'] ?? 'notice' ) . '] ' . ( $gap['feature'] ?? '-' ) . ': ' . ( $gap['message'] ?? '-' );
                $lines[] = '  Fix: ' . ( $gap['fix'] ?? '-' );
            }
        }
        $lines[] = '';

        $xray_summary = $xray['summary'] ?? array();
        $lines[] = '[System X-Ray: ทะลุทั้งระบบ]';
        $lines[] = 'X-Ray Score: ' . ( $xray['score'] ?? 0 ) . '%';
        $lines[] = 'Modules: ' . ( $xray_summary['modules'] ?? 0 );
        $lines[] = 'Entry Points: ' . ( $xray_summary['entry_points'] ?? 0 );
        $lines[] = 'Dependencies: ' . ( $xray_summary['dependencies'] ?? 0 );
        $lines[] = 'Defined Functions: ' . ( $xray_summary['defined_functions'] ?? 0 );
        $lines[] = 'Called Functions: ' . ( $xray_summary['called_functions'] ?? 0 );
        $lines[] = 'Request Inputs: ' . ( $xray_summary['request_inputs'] ?? 0 ) . ' | Sanitizers: ' . ( $xray_summary['sanitizers'] ?? 0 );
        $lines[] = 'Outputs: ' . ( $xray_summary['outputs'] ?? 0 ) . ' | Escapers: ' . ( $xray_summary['escapers'] ?? 0 );
        $lines[] = 'DB Queries: ' . ( $xray_summary['db_queries'] ?? 0 ) . ' | Remote Calls: ' . ( $xray_summary['remote_calls'] ?? 0 ) . ' | File Ops: ' . ( $xray_summary['file_ops'] ?? 0 );
        if ( ! empty( $xray_summary['orphan_files'] ) ) {
            $lines[] = 'Possible Orphan Files: ' . implode( ', ', array_slice( $xray_summary['orphan_files'], 0, 20 ) );
        }
        $lines[] = '';

        $lines[] = '[System Topology / Entry Points]';
        $topology = $xray['topology'] ?? array();
        if ( empty( $topology ) ) {
            $lines[] = '- No entry-point topology generated.';
        } else {
            foreach ( $topology as $row ) {
                $lines[] = '- ' . $row;
            }
        }
        $lines[] = '';



        $lines[] = '[Internal Relationship Check]';
        $summary = $relationship['summary'] ?? array();
        $lines[] = 'Relationship Score: ' . ( $relationship['score'] ?? 0 ) . '%';
        $lines[] = 'PHP Files: ' . ( $summary['php_files'] ?? 0 );
        $lines[] = 'Include/Require Links: ' . ( $summary['includes'] ?? 0 );
        $lines[] = 'Functions: ' . ( $summary['functions'] ?? 0 );
        $lines[] = 'Classes/Traits/Interfaces: ' . ( $summary['classes'] ?? 0 );
        $lines[] = 'Actions: ' . ( $summary['actions'] ?? 0 );
        $lines[] = 'Filters: ' . ( $summary['filters'] ?? 0 );
        $lines[] = 'Shortcodes: ' . ( $summary['shortcodes'] ?? 0 );
        $lines[] = 'AJAX Actions: ' . ( $summary['ajax_actions'] ?? 0 );
        $lines[] = 'REST Routes: ' . ( $summary['rest_routes'] ?? 0 );
        $lines[] = 'Asset Handles: ' . ( $summary['asset_handles'] ?? 0 );
        $lines[] = '';

        $detail_summary = $detail['summary'] ?? array();
        $lines[] = '[Deep Detail Scan]';
        $lines[] = 'Detail Score: ' . ( $detail_summary['detail_score'] ?? 0 ) . '%';
        $lines[] = 'Total Lines: ' . ( $detail_summary['total_lines'] ?? 0 );
        $lines[] = 'Request Touchpoints: ' . ( $detail_summary['request_touchpoints'] ?? 0 );
        $lines[] = 'Output Touchpoints: ' . ( $detail_summary['output_touchpoints'] ?? 0 );
        $lines[] = 'Database Touchpoints: ' . ( $detail_summary['db_touchpoints'] ?? 0 );
        $lines[] = 'Remote/API Touchpoints: ' . ( $detail_summary['remote_touchpoints'] ?? 0 );
        $lines[] = 'Asset Touchpoints: ' . ( $detail_summary['asset_touchpoints'] ?? 0 );
        $lines[] = 'Admin Touchpoints: ' . ( $detail_summary['admin_touchpoints'] ?? 0 );
        if ( ! empty( $detail_summary['largest_file'] ) ) {
            $largest = $detail_summary['largest_file'];
            $lines[] = 'Largest File: ' . ( $largest['file'] ?? '-' ) . ' (' . size_format( (int) ( $largest['size_bytes'] ?? 0 ) ) . ', ' . ( $largest['lines'] ?? 0 ) . ' lines)';
        }
        $lines[] = '';

        $lines[] = '[Issue Summary by Area]';
        $area_counts = $detail_summary['area_counts'] ?? array();
        if ( empty( $area_counts ) ) {
            $lines[] = '- No area-specific issues found.';
        } else {
            foreach ( $area_counts as $area => $counts ) {
                $lines[] = '- ' . $area . ': total ' . ( $counts['total'] ?? 0 ) . ' | critical ' . ( $counts['critical'] ?? 0 ) . ' | warning ' . ( $counts['warning'] ?? 0 ) . ' | notice ' . ( $counts['notice'] ?? 0 );
            }
        }
        $lines[] = '';

        $graph = $relationship['graph'] ?? array();
        $edges = $graph['edges'] ?? array();
        $lines[] = '[Relationship Graph: Include/Require]';
        if ( empty( $edges ) ) {
            $lines[] = '- No direct include/require edges detected.';
        } else {
            foreach ( $edges as $edge ) {
                $lines[] = '- ' . $edge['from'] . ' --' . $edge['type'] . '--> ' . $edge['to'];
            }
        }
        $lines[] = '';

        $lines[] = '[Fix Queue: เรียงจากควรแก้ก่อน]';
        $fix_queue = $detail['fix_queue'] ?? array();
        if ( empty( $fix_queue ) ) {
            $lines[] = '- No urgent fix queue from detailed analyzer.';
        } else {
            foreach ( $fix_queue as $index => $item ) {
                $lines[] = ( $index + 1 ) . '. [' . strtoupper( $item['severity'] ?? 'notice' ) . '] ' . ( $item['title'] ?? '-' );
                $lines[] = '   Area: ' . ( $item['area'] ?? '-' ) . ' | Confidence: ' . ( $item['confidence'] ?? 0 ) . '%';
                $lines[] = '   File: ' . ( $item['file'] ?? '-' ) . ':' . ( $item['line'] ?? 0 );
                $lines[] = '   Fix: ' . $this->mask_if_needed( $item['fix'] ?? '-' );
            }
        }
        $lines[] = '';

        $lines[] = '[Issues: Full Detail]';
        if ( empty( $issues ) ) {
            $lines[] = '✅ No issues found by this static scanner.';
        } else {
            foreach ( $issues as $index => $issue ) {
                $lines[] = ( $index + 1 ) . '. [' . strtoupper( $issue['severity'] ?? 'notice' ) . '] ' . ( $issue['title'] ?? '-' );
                $lines[] = '   Code: ' . ( $issue['code'] ?? '-' );
                $lines[] = '   Area: ' . ( $issue['area'] ?? 'general' ) . ' | Priority: ' . ( $issue['priority'] ?? '-' ) . ' | Confidence: ' . ( $issue['confidence'] ?? '-' ) . '%';
                $lines[] = '   File: ' . ( $issue['file'] ?? '-' ) . ':' . ( $issue['line'] ?? 0 );
                $lines[] = '   Detail: ' . $this->mask_if_needed( $issue['detail'] ?? '-' );
                if ( ! empty( $issue['fix'] ) ) {
                    $lines[] = '   Recommended Fix: ' . $this->mask_if_needed( $issue['fix'] );
                }
            }
        }
        $lines[] = '';

        $lines[] = '[File Inventory]';
        $inventory = $detail['inventory'] ?? array();
        if ( empty( $inventory ) ) {
            $lines[] = '- No inventory generated.';
        } else {
            foreach ( $inventory as $row ) {
                $hash = ! empty( $row['sha256'] ) ? substr( $row['sha256'], 0, 12 ) : '-';
                $lines[] = '- ' . $row['file'] . ' | ' . $row['extension'] . ' | ' . size_format( (int) $row['size_bytes'] ) . ' | ' . $row['lines'] . ' lines | sha256:' . $hash;
            }
        }
        $lines[] = '';

        $lines[] = '[System Closer]';
        $overall = (int) ( $score['overall'] ?? 0 );
        $relationship_score = (int) ( $relationship['score'] ?? 0 );
        $detail_score = (int) ( $detail_summary['detail_score'] ?? 0 );
        if ( $overall >= 85 && $relationship_score >= 85 && $detail_score >= 80 ) {
            $lines[] = 'สถานะ: ผ่านระดับใช้งานจริงเบื้องต้น';
            $lines[] = 'คำแนะนำ: ใช้งานต่อได้บน staging และควรทดสอบจริงก่อนใช้กับ production';
        } elseif ( $overall >= 70 ) {
            $lines[] = 'สถานะ: ใช้งานได้บางส่วน แต่ยังควรแก้ Warning/Critical ก่อน';
            $lines[] = 'คำแนะนำ: แก้รายการ Critical ก่อน จากนั้นค่อยตรวจ Warning, Relationship และ Detail Scan';
        } else {
            $lines[] = 'สถานะ: ยังไม่ควรใช้กับเว็บจริง';
            $lines[] = 'คำแนะนำ: แก้ Critical ทั้งหมด ตรวจ data-flow/security และ scan ใหม่';
        }
        $lines[] = '';

        $lines[] = 'Generated by Thinkb4do Plugin Bug Inspector v' . TBPBI_VERSION;

        return implode( "\n", $lines );
    }

    private function mask_if_needed( string $text ): string {
        if ( get_option( 'tbpbi_mask_sensitive_data', '1' ) !== '1' ) {
            return $text;
        }

        $patterns = array(
            '/sk-[A-Za-z0-9_\-]{10,}/' => 'sk-***MASKED***',
            '/AIza[0-9A-Za-z_\-]{10,}/' => 'AIza***MASKED***',
            '/(api[_\- ]?key|secret|token|bearer)\s*[=:]\s*[\'\"][^\'\"]{6,}[\'\"]/i' => '$1="***MASKED***"',
        );

        foreach ( $patterns as $pattern => $replacement ) {
            $text = preg_replace( $pattern, $replacement, $text );
        }

        return $text;
    }
}
