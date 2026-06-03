<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TBPBI_Admin {
    private static ?TBPBI_Admin $instance = null;
    private array $notices = array();

    public static function instance(): TBPBI_Admin {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_menu', array( $this, 'register_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'admin_init', array( $this, 'handle_actions' ) );
        add_action( 'admin_post_tbpbi_export_txt', array( $this, 'handle_export_txt' ) );
    }

    public function register_menu(): void {
        add_menu_page(
            __( 'Plugin Bug Inspector', 'thinkb4do-plugin-bug-inspector' ),
            __( 'Bug Inspector', 'thinkb4do-plugin-bug-inspector' ),
            'manage_options',
            'tbpbi',
            array( $this, 'render_page' ),
            'dashicons-search',
            81
        );
    }

    public function enqueue_assets( string $hook ): void {
        if ( 'toplevel_page_tbpbi' !== $hook ) {
            return;
        }

        wp_enqueue_style( 'tbpbi-admin', TBPBI_URL . 'assets/admin.css', array(), TBPBI_VERSION );
        wp_enqueue_script( 'tbpbi-admin', TBPBI_URL . 'assets/admin.js', array(), TBPBI_VERSION, true );
    }

    public function handle_actions(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        if ( isset( $_POST['tbpbi_action'] ) && 'scan' === sanitize_text_field( wp_unslash( $_POST['tbpbi_action'] ) ) ) {
            $this->handle_scan();
        }

        if ( isset( $_POST['tbpbi_action'] ) && 'settings' === sanitize_text_field( wp_unslash( $_POST['tbpbi_action'] ) ) ) {
            $this->handle_settings();
        }
    }

    private function handle_settings(): void {
        check_admin_referer( 'tbpbi_settings' );

        $safe_mode = isset( $_POST['tbpbi_safe_static_mode'] ) ? '1' : '0';
        $mask      = isset( $_POST['tbpbi_mask_sensitive_data'] ) ? '1' : '0';
        $xray      = isset( $_POST['tbpbi_deep_xray_mode'] ) ? '1' : '0';
        $cross_platform = isset( $_POST['tbpbi_cross_platform_mode'] ) ? '1' : '0';
        update_option( 'tbpbi_safe_static_mode', $safe_mode );
        update_option( 'tbpbi_mask_sensitive_data', $mask );
        update_option( 'tbpbi_deep_xray_mode', $xray );
        update_option( 'tbpbi_cross_platform_mode', $cross_platform );

        wp_safe_redirect(
            add_query_arg(
                array(
                    'page'          => 'tbpbi',
                    'tab'           => 'settings',
                    'tbpbi_notice'  => 'settings_saved',
                ),
                admin_url( 'admin.php' )
            )
        );
        exit;
    }

    private function handle_scan(): void {
        check_admin_referer( 'tbpbi_scan' );

        $source = isset( $_POST['tbpbi_source'] ) ? sanitize_key( wp_unslash( $_POST['tbpbi_source'] ) ) : 'installed';
        $scanner = new TBPBI_Scanner();
        $result  = null;

        if ( 'upload' === $source ) {
            $result = $this->scan_uploaded_zip( $scanner );
        } else {
            $result = $this->scan_installed_plugin( $scanner );
        }

        if ( is_wp_error( $result ) ) {
            wp_safe_redirect(
                add_query_arg(
                    array(
                        'page'         => 'tbpbi',
                        'tbpbi_notice' => rawurlencode( $result->get_error_message() ),
                    ),
                    admin_url( 'admin.php' )
                )
            );
            exit;
        }

        $report_builder = new TBPBI_Report_Builder();
        $report = $report_builder->build_text( $result );
        $scan_id = wp_generate_uuid4();
        set_transient(
            'tbpbi_scan_' . $scan_id,
            array(
                'result' => $result,
                'report' => $report,
            ),
            6 * HOUR_IN_SECONDS
        );
        update_user_meta( get_current_user_id(), 'tbpbi_last_scan_id', $scan_id );

        wp_safe_redirect(
            add_query_arg(
                array(
                    'page'          => 'tbpbi',
                    'tab'           => 'report',
                    'tbpbi_scan_id' => rawurlencode( $scan_id ),
                ),
                admin_url( 'admin.php' )
            )
        );
        exit;
    }

    private function scan_installed_plugin( TBPBI_Scanner $scanner ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        $plugins = get_plugins();
        $plugin_file = isset( $_POST['tbpbi_plugin_file'] ) ? sanitize_text_field( wp_unslash( $_POST['tbpbi_plugin_file'] ) ) : '';

        if ( ! $plugin_file || ! isset( $plugins[ $plugin_file ] ) ) {
            return new WP_Error( 'tbpbi_invalid_plugin', __( 'กรุณาเลือกปลั๊กอินที่ต้องการตรวจ', 'thinkb4do-plugin-bug-inspector' ) );
        }

        $absolute_main = wp_normalize_path( WP_PLUGIN_DIR . '/' . $plugin_file );
        $plugin_dir = dirname( $absolute_main );
        $root = '.' === dirname( $plugin_file ) ? $absolute_main : $plugin_dir;

        return $scanner->scan_directory(
            $root,
            array(
                'name'    => $plugins[ $plugin_file ]['Name'] ?? basename( $plugin_file ),
                'version' => $plugins[ $plugin_file ]['Version'] ?? '-',
                'source'  => 'installed:' . $plugin_file,
            )
        );
    }

    private function scan_uploaded_zip( TBPBI_Scanner $scanner ) {
        if ( empty( $_FILES['tbpbi_zip']['name'] ) || empty( $_FILES['tbpbi_zip']['tmp_name'] ) ) {
            return new WP_Error( 'tbpbi_no_zip', __( 'กรุณาอัปโหลดไฟล์ .zip ของปลั๊กอิน', 'thinkb4do-plugin-bug-inspector' ) );
        }

        $file_name = sanitize_file_name( wp_unslash( $_FILES['tbpbi_zip']['name'] ) );
        if ( strtolower( pathinfo( $file_name, PATHINFO_EXTENSION ) ) !== 'zip' ) {
            return new WP_Error( 'tbpbi_not_zip', __( 'รองรับเฉพาะไฟล์ .zip เท่านั้น', 'thinkb4do-plugin-bug-inspector' ) );
        }

        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        WP_Filesystem();

        $upload_dir = wp_upload_dir();
        if ( ! empty( $upload_dir['error'] ) ) {
            return new WP_Error( 'tbpbi_upload_dir_error', $upload_dir['error'] );
        }

        $scan_id = 'scan-' . time() . '-' . wp_generate_password( 8, false, false );
        $target_dir = trailingslashit( $upload_dir['basedir'] ) . 'tbpbi-scans/' . $scan_id;
        wp_mkdir_p( $target_dir );

        $uploaded_tmp = isset( $_FILES['tbpbi_zip']['tmp_name'] ) ? sanitize_text_field( wp_unslash( $_FILES['tbpbi_zip']['tmp_name'] ) ) : '';
        $tmp_zip = wp_tempnam( $file_name );
        if ( ! $uploaded_tmp || ! is_uploaded_file( $uploaded_tmp ) || ! $tmp_zip || ! move_uploaded_file( $uploaded_tmp, $tmp_zip ) ) {
            $this->delete_directory( $target_dir );
            return new WP_Error( 'tbpbi_move_failed', __( 'ไม่สามารถย้ายไฟล์อัปโหลดไปยังพื้นที่ชั่วคราวได้', 'thinkb4do-plugin-bug-inspector' ) );
        }

        $unzipped = unzip_file( $tmp_zip, $target_dir );
        @unlink( $tmp_zip );

        if ( is_wp_error( $unzipped ) ) {
            $this->delete_directory( $target_dir );
            return $unzipped;
        }

        $root_to_scan = $this->detect_zip_root( $target_dir );
        $result = $scanner->scan_directory(
            $root_to_scan,
            array(
                'name'    => basename( $file_name, '.zip' ),
                'version' => '-',
                'source'  => 'uploaded_zip:' . $file_name,
            )
        );

        $this->delete_directory( $target_dir );
        return $result;
    }

    private function detect_zip_root( string $target_dir ): string {
        $entries = glob( trailingslashit( $target_dir ) . '*' );
        if ( is_array( $entries ) && count( $entries ) === 1 && is_dir( $entries[0] ) ) {
            return wp_normalize_path( $entries[0] );
        }
        return wp_normalize_path( $target_dir );
    }

    public function handle_export_txt(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Permission denied.', 'thinkb4do-plugin-bug-inspector' ) );
        }

        $scan_id = isset( $_GET['scan_id'] ) ? sanitize_text_field( wp_unslash( $_GET['scan_id'] ) ) : '';
        if ( ! $scan_id ) {
            wp_die( esc_html__( 'Missing scan id.', 'thinkb4do-plugin-bug-inspector' ) );
        }

        check_admin_referer( 'tbpbi_export_' . $scan_id );
        $payload = get_transient( 'tbpbi_scan_' . $scan_id );
        if ( empty( $payload['report'] ) ) {
            wp_die( esc_html__( 'Report expired or not found.', 'thinkb4do-plugin-bug-inspector' ) );
        }

        $plugin_name = $payload['result']['plugin']['name'] ?? 'plugin';
        $filename = sanitize_title( $plugin_name ) . '-debug-report-' . gmdate( 'Ymd-His' ) . '.txt';
        TBPBI_Exporter::send_txt_download( $filename, $payload['report'] );
    }

    public function render_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Permission denied.', 'thinkb4do-plugin-bug-inspector' ) );
        }

        $tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'dashboard';
        $scan_id = isset( $_GET['tbpbi_scan_id'] ) ? sanitize_text_field( wp_unslash( $_GET['tbpbi_scan_id'] ) ) : get_user_meta( get_current_user_id(), 'tbpbi_last_scan_id', true );
        $payload = $scan_id ? get_transient( 'tbpbi_scan_' . $scan_id ) : false;

        echo '<div class="wrap tbpbi-wrap">';
        $this->render_header( $tab );
        $this->render_notice();

        if ( 'report' === $tab ) {
            $this->render_report( $payload, $scan_id );
        } elseif ( 'settings' === $tab ) {
            $this->render_settings();
        } else {
            $this->render_dashboard( $payload, $scan_id );
        }

        echo '</div>';
    }

    private function render_header( string $active_tab ): void {
        $tabs = array(
            'dashboard' => __( 'Dashboard', 'thinkb4do-plugin-bug-inspector' ),
            'report'    => __( 'Report', 'thinkb4do-plugin-bug-inspector' ),
            'settings'  => __( 'Settings', 'thinkb4do-plugin-bug-inspector' ),
        );

        echo '<div class="tbpbi-hero">';
        echo '<div><p class="tbpbi-eyebrow">Thinkb4do · AiRA Debug Utility</p><h1>Plugin Bug Inspector</h1><p>ตรวจบั๊ก ตรวจความสัมพันธ์ อ่านเป้าหมาย เจาะระบบ ตรวจทุกบราวเซอร์/อุปกรณ์/ระบบ และตรวจคุณภาพก่อน export เป็น Text</p></div>';
        echo '<div class="tbpbi-status"><strong>Local Scan + Compatibility</strong><span>ไม่ส่งโค้ดออกนอกเว็บ · ตรวจบราวเซอร์/อุปกรณ์/ระบบ</span></div>';
        echo '</div>';

        echo '<nav class="nav-tab-wrapper tbpbi-tabs">';
        foreach ( $tabs as $key => $label ) {
            $class = $active_tab === $key ? ' nav-tab-active' : '';
            echo '<a class="nav-tab' . esc_attr( $class ) . '" href="' . esc_url( add_query_arg( array( 'page' => 'tbpbi', 'tab' => $key ), admin_url( 'admin.php' ) ) ) . '">' . esc_html( $label ) . '</a>';
        }
        echo '</nav>';
    }

    private function render_notice(): void {
        if ( empty( $_GET['tbpbi_notice'] ) ) {
            return;
        }

        $notice = sanitize_text_field( wp_unslash( $_GET['tbpbi_notice'] ) );
        if ( 'settings_saved' === $notice ) {
            $notice = __( 'บันทึก Settings เรียบร้อยแล้ว', 'thinkb4do-plugin-bug-inspector' );
        }

        echo '<div class="notice notice-info is-dismissible"><p>' . esc_html( $notice ) . '</p></div>';
    }

    private function render_dashboard( $payload, string $scan_id ): void {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        $plugins = get_plugins();

        echo '<div class="tbpbi-grid">';
        echo '<section class="tbpbi-card tbpbi-card-main">';
        echo '<h2>1) เลือกปลั๊กอินที่ต้องการตรวจ</h2>';
        echo '<p>ระบบจะตรวจโครงสร้างไฟล์ ความปลอดภัยเบื้องต้น และความสัมพันธ์ภายในระบบแบบละเอียด เช่น include/require, function, class, hook, shortcode, AJAX, REST, asset handle, security data-flow, DB query, remote API, UI risk, Browser API fallback, responsive viewport และ cross-platform compatibility</p>';
        echo '<form method="post" enctype="multipart/form-data" class="tbpbi-form">';
        wp_nonce_field( 'tbpbi_scan' );
        echo '<input type="hidden" name="tbpbi_action" value="scan">';
        echo '<label><span>แหล่งข้อมูล</span><select name="tbpbi_source" id="tbpbi-source"><option value="installed">ปลั๊กอินที่ติดตั้งอยู่</option><option value="upload">อัปโหลด ZIP เพื่อตรวจ</option></select></label>';
        echo '<label class="tbpbi-installed-field"><span>เลือกปลั๊กอิน</span><select name="tbpbi_plugin_file">';
        foreach ( $plugins as $file => $data ) {
            echo '<option value="' . esc_attr( $file ) . '">' . esc_html( ( $data['Name'] ?? $file ) . ' — ' . $file ) . '</option>';
        }
        echo '</select></label>';
        echo '<label class="tbpbi-upload-field" style="display:none"><span>ไฟล์ ZIP</span><input type="file" name="tbpbi_zip" accept=".zip,application/zip"></label>';
        echo '<button type="submit" class="button button-primary button-hero">Scan Plugin</button>';
        echo '</form>';
        echo '</section>';

        echo '<aside class="tbpbi-card">';
        echo '<h2>2) สถานะล่าสุด</h2>';
        if ( ! empty( $payload['result'] ) ) {
            $result = $payload['result'];
            $score = $result['score']['overall'] ?? 0;
            $relationship_score = $result['relationship']['score'] ?? 0;
            echo '<div class="tbpbi-score"><span>' . esc_html( $score ) . '%</span><small>Overall</small></div>';
            $detail_score = $result['detail']['summary']['detail_score'] ?? 0;
            $standard_score = $result['quality_standard']['score'] ?? 0;
            echo '<p><strong>Relationship:</strong> ' . esc_html( $relationship_score ) . '% · <strong>Detail:</strong> ' . esc_html( $detail_score ) . '% · <strong>Standard:</strong> ' . esc_html( $standard_score ) . '%</p>';
            echo '<p><strong>Plugin:</strong> ' . esc_html( $result['plugin']['name'] ?? '-' ) . '</p>';
            echo '<p><strong>Critical:</strong> ' . esc_html( $result['score']['counts']['critical'] ?? 0 ) . ' · <strong>Warning:</strong> ' . esc_html( $result['score']['counts']['warning'] ?? 0 ) . ' · <strong>Notice:</strong> ' . esc_html( $result['score']['counts']['notice'] ?? 0 ) . '</p>';
            echo '<p><a class="button" href="' . esc_url( add_query_arg( array( 'page' => 'tbpbi', 'tab' => 'report', 'tbpbi_scan_id' => $scan_id ), admin_url( 'admin.php' ) ) ) . '">เปิดรายงานล่าสุด</a></p>';
        } else {
            echo '<p>ยังไม่มีรายงานล่าสุด กด Scan Plugin เพื่อเริ่มตรวจ</p>';
        }
        echo '</aside>';
        echo '</div>';

        echo '<section class="tbpbi-card">';
        echo '<h2>3) ตัวตรวจความสัมพันธ์ภายในระบบ</h2>';
        echo '<div class="tbpbi-checklist">';
        $items = array(
            'ไฟล์หลักปลั๊กอินและ Plugin Header',
            'include/require ไปยังไฟล์ที่หาย',
            'function/class/interface/trait ซ้ำ',
            'shortcode และ asset handle ซ้ำ',
            'AJAX action ที่ไม่ผูก nonce/capability',
            'REST route ที่ไม่มี permission_callback',
            'ไฟล์ PHP ที่อาจยังไม่ถูกเชื่อมเข้าระบบหลัก',
            'Security data-flow: request → sanitize → process → escape',
            'Database query และ $wpdb->prepare()',
            'Remote API: timeout, error handling, response code',
            'UI/UX risk: fixed/z-index/admin bar overlap',
            'System Goal Reader: อ่านเป้าหมายจาก header/readme/class/function/hook',
            'System X-Ray: entry point → module → dependency → data-flow',
            'Goal vs Code Gap: ตรวจว่าฟีเจอร์ที่ควรมีแต่ยังไม่พบ',
            'Quality Standard Audit: header/security/data/output/compatibility/release gate',
            'Browser/Device/System Compatibility: Chrome, Edge, Firefox, Safari, Samsung Internet, Android WebView',
            'Responsive + Safe Area: มือถือ แท็บเล็ต เดสก์ท็อป จอแคบ จอมี notch/gesture bar',
            'Fallback Guard: Clipboard API, Browser API, reduced motion, forced colors, print mode',
            'Release Gate: สรุปว่าควรปล่อยใช้งานจริงหรือยัง',
        );
        foreach ( $items as $item ) {
            echo '<span>✓ ' . esc_html( $item ) . '</span>';
        }
        echo '</div>';
        echo '</section>';
    }

    private function render_report( $payload, string $scan_id ): void {
        if ( empty( $payload['result'] ) || empty( $payload['report'] ) ) {
            echo '<section class="tbpbi-card"><h2>Report</h2><p>ยังไม่มีรายงาน หรือรายงานหมดอายุแล้ว กรุณากลับไป Scan ใหม่</p></section>';
            return;
        }

        $result = $payload['result'];
        $report = $payload['report'];
        $export_url = wp_nonce_url(
            add_query_arg(
                array(
                    'action'  => 'tbpbi_export_txt',
                    'scan_id' => $scan_id,
                ),
                admin_url( 'admin-post.php' )
            ),
            'tbpbi_export_' . $scan_id
        );

        echo '<section class="tbpbi-card">';
        echo '<div class="tbpbi-report-head"><div><h2>Debug Report</h2><p>' . esc_html( $result['plugin']['name'] ?? '-' ) . '</p></div><div class="tbpbi-actions"><a class="button button-primary" href="' . esc_url( $export_url ) . '">Export TXT</a><button type="button" class="button" id="tbpbi-copy-report">Copy Report</button></div></div>';

        $this->render_score_cards( $result );
        $this->render_quality_summary( $result );
        $this->render_compatibility_summary( $result );
        $this->render_goal_summary( $result );
        $this->render_xray_summary( $result );
        $this->render_relationship_summary( $result );
        $this->render_detail_summary( $result );
        $this->render_fix_queue( $result );
        $this->render_issue_table( $result );

        echo '<h3>Text Export Preview</h3>';
        echo '<textarea id="tbpbi-report-text" class="tbpbi-report-text" readonly>' . esc_textarea( $report ) . '</textarea>';
        echo '</section>';
    }

    private function render_score_cards( array $result ): void {
        $score = $result['score'] ?? array();
        echo '<div class="tbpbi-metrics">';
        foreach ( array( 'overall' => 'Overall', 'quality' => 'Quality', 'performance' => 'Performance', 'security' => 'Security', 'relationship' => 'Relationship', 'detail' => 'Detail Scan', 'goal' => 'Goal Reader', 'alignment' => 'Alignment', 'xray' => 'System X-Ray', 'standard' => 'Quality Standard', 'release_gate' => 'Release Gate' ) as $key => $label ) {
            echo '<div><strong>' . esc_html( $score[ $key ] ?? 0 ) . '%</strong><span>' . esc_html( $label ) . '</span></div>';
        }
        echo '</div>';
    }

    private function render_quality_summary( array $result ): void {
        $quality = $result['quality_standard'] ?? array();
        if ( empty( $quality ) ) {
            return;
        }
        $summary = $quality['summary'] ?? array();
        $gate = $quality['release_gate'] ?? array();
        echo '<div class="tbpbi-relation-box tbpbi-standard-box">';
        echo '<h3>Quality Standard Audit</h3>';
        echo '<p><strong>Release Gate:</strong> ' . esc_html( strtoupper( (string) ( $gate['status'] ?? 'unknown' ) ) ) . ' — ' . esc_html( $gate['message'] ?? '-' ) . '</p>';
        echo '<div class="tbpbi-relation-grid">';
        echo '<span><strong>' . esc_html( $quality['score'] ?? 0 ) . '%</strong>Quality Standard</span>';
        echo '<span><strong>' . esc_html( $summary['standards_passed'] ?? 0 ) . '/' . esc_html( $summary['standards_total'] ?? 0 ) . '</strong>Standards Passed</span>';
        echo '<span><strong>' . esc_html( $summary['critical'] ?? 0 ) . '</strong>Critical</span>';
        echo '<span><strong>' . esc_html( $summary['warning'] ?? 0 ) . '</strong>Warning</span>';
        echo '</div>';
        $standards = $quality['standards'] ?? array();
        if ( ! empty( $standards ) ) {
            echo '<h4>Standard Checklist</h4><div class="tbpbi-area-list">';
            foreach ( $standards as $standard ) {
                $label = ( ! empty( $standard['passed'] ) ? 'PASS' : 'REVIEW' ) . ' · ' . ( $standard['name'] ?? '-' ) . ' · ' . ( $standard['score'] ?? 0 ) . '%';
                echo '<span>' . esc_html( $label ) . '</span>';
            }
            echo '</div>';
        }
        echo '</div>';
    }

    private function render_compatibility_summary( array $result ): void {
        $quality = $result['quality_standard'] ?? array();
        if ( empty( $quality ) ) {
            return;
        }

        $metrics = $quality['metrics'] ?? array();
        $standard = $quality['standards']['cross_platform_compatibility'] ?? array();
        if ( empty( $standard ) ) {
            return;
        }
        $score = (int) ( $standard['score'] ?? 0 );
        $passed = ! empty( $standard['passed'] );

        $checks = array(
            array( 'label' => 'Chrome / Edge / Firefox', 'detail' => 'Modern admin CSS/JS + fallback', 'ok' => true ),
            array( 'label' => 'Safari / iOS / iPadOS', 'detail' => 'safe-area + clipboard guard', 'ok' => ! empty( $metrics['css_has_safe_area'] ) && ! ( ! empty( $metrics['js_clipboard_usage'] ) && empty( $metrics['js_clipboard_fallback'] ) ) ),
            array( 'label' => 'Android / Samsung Internet', 'detail' => 'responsive + touch size', 'ok' => ! empty( $metrics['css_has_responsive_media'] ) ),
            array( 'label' => 'Desktop / Tablet / Mobile', 'detail' => 'breakpoint + overflow control', 'ok' => ! empty( $metrics['css_has_responsive_media'] ) && ! empty( $metrics['css_has_word_wrap'] ) ),
            array( 'label' => 'Reduced Motion', 'detail' => 'OS accessibility support', 'ok' => ! empty( $metrics['css_has_reduced_motion'] ) ),
            array( 'label' => 'High Contrast', 'detail' => 'forced-colors support', 'ok' => ! empty( $metrics['css_has_forced_colors'] ) ),
            array( 'label' => 'Print / Export View', 'detail' => 'print media style', 'ok' => ! empty( $metrics['css_has_print_style'] ) ),
            array( 'label' => 'Browser API Guard', 'detail' => 'feature detect / fallback', 'ok' => empty( $metrics['js_browser_api_without_guard'] ) ),
        );

        echo '<div class="tbpbi-relation-box tbpbi-compat-box">';
        echo '<h3>Browser / Device / System Compatibility</h3>';
        echo '<p class="tbpbi-relation-score">' . esc_html( $score ) . '%</p>';
        echo '<p><strong>Status:</strong> ' . esc_html( $passed ? 'พร้อมใช้งานข้ามอุปกรณ์ระดับดี' : 'ควรตรวจบางจุดก่อนปล่อยจริง' ) . '</p>';
        echo '<div class="tbpbi-compat-grid">';
        foreach ( $checks as $check ) {
            $ok = ! empty( $check['ok'] );
            echo '<div class="tbpbi-compat-item">';
            echo '<strong>' . esc_html( $check['label'] ) . '</strong>';
            echo '<span class="' . esc_attr( $ok ? 'tbpbi-compat-ok' : 'tbpbi-compat-review' ) . '">' . esc_html( $ok ? 'PASS' : 'REVIEW' ) . '</span>';
            echo '<span>' . esc_html( $check['detail'] ) . '</span>';
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';
    }

    private function render_goal_summary( array $result ): void {
        $goal = $result['goal'] ?? array();
        if ( empty( $goal ) ) {
            return;
        }
        $goal_data = $goal['goal'] ?? array();
        $scores = $goal['scores'] ?? array();
        echo '<div class="tbpbi-relation-box tbpbi-goal-box">';
        echo '<h3>System Goal Reader</h3>';
        echo '<p><strong>ประเภทระบบ:</strong> ' . esc_html( $goal_data['system_type'] ?? '-' ) . '</p>';
        echo '<p><strong>เป้าหมายหลัก:</strong> ' . esc_html( $goal_data['primary_goal'] ?? '-' ) . '</p>';
        echo '<div class="tbpbi-relation-grid">';
        foreach ( array(
            'goal_clarity'      => 'Goal Clarity',
            'feature_alignment' => 'Feature Alignment',
            'confidence'        => 'Confidence',
            'gap_count'         => 'Goal Gaps',
        ) as $key => $label ) {
            $suffix = 'gap_count' === $key ? '' : '%';
            echo '<span><strong>' . esc_html( $scores[ $key ] ?? 0 ) . esc_html( $suffix ) . '</strong>' . esc_html( $label ) . '</span>';
        }
        echo '</div>';
        $features = $goal['features'] ?? array();
        if ( ! empty( $features ) ) {
            echo '<h4>Feature Signals</h4><div class="tbpbi-area-list">';
            foreach ( array_slice( $features, 0, 10 ) as $feature => $data ) {
                echo '<span><strong>' . esc_html( $feature ) . '</strong> ' . esc_html( $data['score'] ?? 0 ) . '%</span>';
            }
            echo '</div>';
        }
        echo '</div>';
    }

    private function render_xray_summary( array $result ): void {
        $xray = $result['xray'] ?? array();
        if ( empty( $xray ) ) {
            return;
        }
        $summary = $xray['summary'] ?? array();
        echo '<div class="tbpbi-relation-box tbpbi-xray-box">';
        echo '<h3>System X-Ray Analyzer</h3>';
        echo '<p class="tbpbi-relation-score">' . esc_html( $xray['score'] ?? 0 ) . '%</p>';
        echo '<div class="tbpbi-relation-grid">';
        foreach ( array(
            'modules'          => 'Modules',
            'entry_points'     => 'Entry Points',
            'dependencies'     => 'Dependencies',
            'defined_functions'=> 'Defined Functions',
            'called_functions' => 'Called Functions',
            'request_inputs'   => 'Request Inputs',
            'sanitizers'       => 'Sanitizers',
            'outputs'          => 'Outputs',
            'escapers'         => 'Escapers',
            'db_queries'       => 'DB Queries',
            'remote_calls'     => 'Remote Calls',
            'file_ops'         => 'File Ops',
        ) as $key => $label ) {
            echo '<span><strong>' . esc_html( $summary[ $key ] ?? 0 ) . '</strong>' . esc_html( $label ) . '</span>';
        }
        echo '</div>';
        $topology = $xray['topology'] ?? array();
        if ( ! empty( $topology ) ) {
            echo '<h4>Topology / Entry Points</h4><ul class="tbpbi-topology">';
            foreach ( array_slice( $topology, 0, 12 ) as $row ) {
                echo '<li><code>' . esc_html( $row ) . '</code></li>';
            }
            echo '</ul>';
        }
        echo '</div>';
    }

    private function render_relationship_summary( array $result ): void {
        $relationship = $result['relationship'] ?? array();
        $summary = $relationship['summary'] ?? array();
        echo '<div class="tbpbi-relation-box">';
        echo '<h3>Internal Relationship</h3>';
        echo '<p class="tbpbi-relation-score">' . esc_html( $relationship['score'] ?? 0 ) . '%</p>';
        echo '<div class="tbpbi-relation-grid">';
        foreach ( array(
            'includes'      => 'Include/Require',
            'functions'     => 'Functions',
            'classes'       => 'Classes',
            'actions'       => 'Actions',
            'filters'       => 'Filters',
            'shortcodes'    => 'Shortcodes',
            'ajax_actions'  => 'AJAX',
            'rest_routes'   => 'REST',
            'asset_handles' => 'Assets',
        ) as $key => $label ) {
            echo '<span><strong>' . esc_html( $summary[ $key ] ?? 0 ) . '</strong>' . esc_html( $label ) . '</span>';
        }
        echo '</div>';
        echo '</div>';
    }


    private function render_detail_summary( array $result ): void {
        $detail = $result['detail'] ?? array();
        $summary = $detail['summary'] ?? array();
        if ( empty( $summary ) ) {
            return;
        }

        echo '<div class="tbpbi-relation-box tbpbi-detail-box">';
        echo '<h3>Deep Detail Scan</h3>';
        echo '<p class="tbpbi-relation-score">' . esc_html( $summary['detail_score'] ?? 0 ) . '%</p>';
        echo '<div class="tbpbi-relation-grid">';
        foreach ( array(
            'total_lines'         => 'Total Lines',
            'request_touchpoints' => 'Request Inputs',
            'output_touchpoints'  => 'Outputs',
            'db_touchpoints'      => 'DB Queries',
            'remote_touchpoints'  => 'Remote/API',
            'asset_touchpoints'   => 'Assets',
            'admin_touchpoints'   => 'Admin Hooks',
        ) as $key => $label ) {
            echo '<span><strong>' . esc_html( $summary[ $key ] ?? 0 ) . '</strong>' . esc_html( $label ) . '</span>';
        }
        echo '</div>';

        $area_counts = $summary['area_counts'] ?? array();
        if ( ! empty( $area_counts ) ) {
            echo '<h4>Issue Area Summary</h4><div class="tbpbi-area-list">';
            foreach ( $area_counts as $area => $counts ) {
                echo '<span><strong>' . esc_html( $area ) . '</strong> total ' . esc_html( $counts['total'] ?? 0 ) . ' · C ' . esc_html( $counts['critical'] ?? 0 ) . ' · W ' . esc_html( $counts['warning'] ?? 0 ) . ' · N ' . esc_html( $counts['notice'] ?? 0 ) . '</span>';
            }
            echo '</div>';
        }
        echo '</div>';
    }

    private function render_fix_queue( array $result ): void {
        $queue = $result['detail']['fix_queue'] ?? array();
        echo '<h3>Fix Queue</h3>';
        if ( empty( $queue ) ) {
            echo '<p class="tbpbi-pass">ยังไม่มีคิวแก้ไขเร่งด่วนจาก detailed analyzer</p>';
            return;
        }

        echo '<table class="widefat striped tbpbi-table"><thead><tr><th>ลำดับ</th><th>ระดับ</th><th>ส่วนระบบ</th><th>จุดที่ต้องแก้</th><th>วิธีแก้</th></tr></thead><tbody>';
        foreach ( $queue as $index => $item ) {
            $severity = $item['severity'] ?? 'notice';
            echo '<tr>';
            echo '<td>' . esc_html( $index + 1 ) . '</td>';
            echo '<td><span class="tbpbi-badge tbpbi-' . esc_attr( $severity ) . '">' . esc_html( strtoupper( $severity ) ) . '</span></td>';
            echo '<td>' . esc_html( $item['area'] ?? '-' ) . '<br><small>Confidence ' . esc_html( $item['confidence'] ?? 0 ) . '%</small></td>';
            echo '<td><strong>' . esc_html( $item['title'] ?? '-' ) . '</strong><br><code>' . esc_html( ( $item['file'] ?? '-' ) . ':' . ( $item['line'] ?? 0 ) ) . '</code></td>';
            echo '<td>' . esc_html( $item['fix'] ?? '-' ) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }

    private function render_issue_table( array $result ): void {
        $issues = $result['issues'] ?? array();
        echo '<h3>Issues</h3>';
        if ( empty( $issues ) ) {
            echo '<p class="tbpbi-pass">ไม่พบปัญหาจาก static scanner เวอร์ชันนี้</p>';
            return;
        }

        echo '<table class="widefat striped tbpbi-table"><thead><tr><th>ระดับ</th><th>ปัญหา</th><th>พื้นที่</th><th>ไฟล์</th><th>คำอธิบาย / วิธีแก้</th></tr></thead><tbody>';
        foreach ( $issues as $issue ) {
            $severity = $issue['severity'] ?? 'notice';
            echo '<tr>';
            echo '<td><span class="tbpbi-badge tbpbi-' . esc_attr( $severity ) . '">' . esc_html( strtoupper( $severity ) ) . '</span></td>';
            echo '<td><strong>' . esc_html( $issue['title'] ?? '-' ) . '</strong><br><code>' . esc_html( $issue['code'] ?? '-' ) . '</code></td>';
            echo '<td>' . esc_html( $issue['area'] ?? 'general' ) . '<br><small>' . esc_html( $issue['priority'] ?? '-' ) . ' · ' . esc_html( $issue['confidence'] ?? '-' ) . '%</small></td>';
            echo '<td><code>' . esc_html( ( $issue['file'] ?? '-' ) . ':' . ( $issue['line'] ?? 0 ) ) . '</code></td>';
            echo '<td>' . esc_html( $issue['detail'] ?? '-' );
            if ( ! empty( $issue['fix'] ) ) {
                echo '<hr><strong>Fix:</strong> ' . esc_html( $issue['fix'] );
            }
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }

    private function render_settings(): void {
        echo '<section class="tbpbi-card">';
        echo '<h2>System Settings | ปิด</h2>';
        echo '<p>คำอธิบาย: ตั้งค่าระบบตรวจบั๊กปลั๊กอิน ตรวจความสัมพันธ์ภายในระบบแบบละเอียด, deep static scan, System Goal Reader, System X-Ray, security data-flow และ export รายงานเป็น text</p>';
        echo '<form method="post" class="tbpbi-form">';
        wp_nonce_field( 'tbpbi_settings' );
        echo '<input type="hidden" name="tbpbi_action" value="settings">';
        echo '<label class="tbpbi-toggle"><input type="checkbox" name="tbpbi_safe_static_mode" value="1" ' . checked( get_option( 'tbpbi_safe_static_mode', '1' ), '1', false ) . '> <span>Safe Static Mode — ตรวจแบบอ่านไฟล์ ไม่รันคำสั่ง shell</span></label>';
        echo '<label class="tbpbi-toggle"><input type="checkbox" name="tbpbi_mask_sensitive_data" value="1" ' . checked( get_option( 'tbpbi_mask_sensitive_data', '1' ), '1', false ) . '> <span>Mask API Key Before Export — ซ่อน key/token ในรายงาน</span></label>';
        echo '<label class="tbpbi-toggle"><input type="checkbox" name="tbpbi_deep_xray_mode" value="1" ' . checked( get_option( 'tbpbi_deep_xray_mode', '1' ), '1', false ) . '> <span>Deep X-Ray Mode — เจาะความสัมพันธ์ทั้งระบบแบบละเอียด</span></label>';
        echo '<label class="tbpbi-toggle"><input type="checkbox" name="tbpbi_cross_platform_mode" value="1" ' . checked( get_option( 'tbpbi_cross_platform_mode', '1' ), '1', false ) . '> <span>Cross-Platform Compatibility — ตรวจรองรับทุกบราวเซอร์ ทุกอุปกรณ์ ทุกระบบ และ fallback สำคัญ</span></label>';
        echo '<button type="submit" class="button button-primary">Save Settings</button>';
        echo '</form>';
        echo '<footer class="tbpbi-footer">Version ' . esc_html( TBPBI_VERSION ) . ' | โดย Thinkb4do | ดูรายละเอียด</footer>';
        echo '</section>';
    }

    private function delete_directory( string $dir ): void {
        if ( ! is_dir( $dir ) ) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ( $iterator as $file ) {
            if ( $file->isDir() ) {
                @rmdir( $file->getRealPath() );
            } else {
                @unlink( $file->getRealPath() );
            }
        }
        @rmdir( $dir );
    }
}
