<?php
if (!defined('ABSPATH')) {
    exit;
}

class AiRA_Export_Plugin {

    public function init() {
        add_action('admin_menu', array($this, 'register_menu'));
        add_action('admin_post_aira_export_selected_plugin', array($this, 'handle_export'));
        add_action('admin_post_aira_export_selected_theme', array($this, 'handle_theme_export'));
        add_action('admin_post_aira_toggle_plugin_status', array($this, 'handle_toggle_plugin'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('rest_api_init', array($this, 'register_monitor_rest_route'));
    }

    public function register_menu() {
        add_management_page(
            __('AiRA Export Center', 'aira-suite-export-plugin'),
            __('AiRA Export Center', 'aira-suite-export-plugin'),
            'manage_options',
            'aira-plugin-export',
            array($this, 'render_page')
        );
    }

    public function enqueue_assets($hook) {
        if ($hook !== 'tools_page_aira-plugin-export') {
            return;
        }

        wp_register_style('aira-export-admin-inline', false, array(), AIRA_EXPORT_PLUGIN_VERSION);
        wp_enqueue_style('aira-export-admin-inline');
        wp_add_inline_style('aira-export-admin-inline', $this->get_admin_css());

        wp_register_script('aira-export-admin-inline', false, array(), AIRA_EXPORT_PLUGIN_VERSION, true);
        wp_enqueue_script('aira-export-admin-inline');
        wp_add_inline_script('aira-export-admin-inline', $this->get_admin_js());
    }

    private function get_admin_css() {
        return '
            .aira-export-wrap,.aira-export-wrap *{box-sizing:border-box}
            .aira-export-wrap{max-width:1180px;width:100%;font-size:14px;line-height:1.55;-webkit-font-smoothing:antialiased;text-rendering:optimizeLegibility}
            .aira-page-head{display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-pack:justify;-ms-flex-pack:justify;justify-content:space-between;-webkit-box-align:start;-ms-flex-align:start;align-items:flex-start;gap:14px;-ms-flex-wrap:wrap;flex-wrap:wrap;margin:8px 0 16px}
            .aira-page-head h1{margin:0 0 4px;font-size:28px;line-height:1.2;font-weight:700;color:#111827}
            .aira-muted{color:#50575e}.aira-small{font-size:12px}.aira-strong{font-weight:700}.aira-code{font-family:Consolas,Monaco,monospace;background:#f6f7f7;padding:2px 6px;border-radius:6px}
            .aira-card{background:#fff;border:1px solid #dcdcde;border-radius:16px;padding:20px;margin-top:16px;box-shadow:0 8px 24px rgba(17,24,39,.06)}
            .aira-panel{background:#f8fafc;border:1px solid #e5e7eb;border-radius:14px;padding:14px}
            .aira-section-title{margin:0 0 8px;font-size:16px;line-height:1.35;font-weight:700;color:#111827}
            .aira-topbar{display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-pack:justify;-ms-flex-pack:justify;justify-content:space-between;-webkit-box-align:center;-ms-flex-align:center;align-items:center;gap:12px;-ms-flex-wrap:wrap;flex-wrap:wrap;padding-bottom:14px;border-bottom:1px solid #edf0f2}
            .aira-title-stack{min-width:220px}.aira-title-row{display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;gap:8px;-ms-flex-wrap:wrap;flex-wrap:wrap}.aira-title-row strong{font-size:15px;color:#111827}
            .aira-badge,.aira-pill{display:inline-block;vertical-align:middle;padding:5px 11px;border-radius:999px;font-weight:700;font-size:12px;line-height:1.25;white-space:nowrap}
            .aira-badge{background:#edf7ed;color:#1e6b34;border:1px solid #c7e8ce}.aira-pill{background:#fff7ed;color:#9a3412;border:1px solid #fed7aa}.aira-pill-ok{background:#ecfdf5;color:#047857;border-color:#a7f3d0}.aira-pill-warn{background:#fff7ed;color:#9a3412;border-color:#fed7aa}.aira-pill-soft{background:#f3f4f6;color:#374151;border-color:#e5e7eb}
            .aira-flex-grid{display:-webkit-box;display:-ms-flexbox;display:flex;gap:14px;-ms-flex-wrap:wrap;flex-wrap:wrap;margin-top:14px}.aira-flex-col{min-width:220px;-webkit-box-flex:1;-ms-flex:1 1 260px;flex:1 1 260px}
            .aira-status-card{min-height:92px;background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:14px}.aira-status-card strong{display:block;margin-bottom:5px;color:#111827}.aira-status-card p{margin:0;color:#50575e}
            .aira-form{margin-top:18px}.aira-form-table{width:100%;border-collapse:collapse}.aira-form-table th{width:210px;text-align:left;vertical-align:top;padding:18px 10px 18px 0;font-weight:700;color:#111827}.aira-form-table td{padding:14px 0}
            .aira-actions{display:-webkit-box;display:-ms-flexbox;display:flex;gap:8px;-ms-flex-wrap:wrap;flex-wrap:wrap;margin:0 0 14px}.aira-actions .button{min-height:36px;min-width:118px;display:-webkit-inline-box;display:-ms-inline-flexbox;display:inline-flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;-webkit-box-pack:center;-ms-flex-pack:center;justify-content:center}
            .aira-plugin-checklist{border:1px solid #dcdcde;border-radius:14px;padding:8px;max-height:390px;overflow:auto;background:#fcfcfc;-webkit-overflow-scrolling:touch}
            .aira-plugin-item{display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:start;-ms-flex-align:start;align-items:flex-start;gap:10px;padding:10px 8px;border-bottom:1px solid #f0f0f1;border-radius:10px;cursor:pointer;min-height:44px}.aira-plugin-item:hover{background:#f8fafc}.aira-plugin-item:last-child{border-bottom:none}.aira-plugin-item input{margin-top:2px;min-width:18px;min-height:18px}.aira-plugin-item span{word-break:break-word;overflow-wrap:anywhere}
            .aira-input{margin-top:10px;width:420px;max-width:100%;min-height:38px}.aira-count{font-weight:700;color:#111827}.aira-help{display:inline-flex;align-items:center;min-height:36px}
            .aira-progress-wrap{display:none;margin:14px 0 4px;background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:12px}.aira-progress-wrap.is-visible{display:block}.aira-progress-head{display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-pack:justify;-ms-flex-pack:justify;justify-content:space-between;gap:10px;margin-bottom:8px}.aira-progress-track{width:100%;height:12px;border-radius:999px;background:#eef2f7;overflow:hidden}.aira-progress-bar{width:0;height:100%;border-radius:999px;background:#16a34a;transition:width .25s ease}.aira-progress-note{margin-top:8px;color:#50575e;font-size:12px}
            .aira-note{background:#fff8e5;border-left:4px solid #dba617;padding:13px 14px;border-radius:10px;margin-top:14px}.aira-list{margin:8px 0 0 20px}.aira-list li{margin-bottom:7px}.aira-footer{margin-top:14px;font-size:13px;color:#50575e;padding-top:12px;border-top:1px solid #edf0f2}
            .aira-noscript{margin-top:12px;padding:10px 12px;border-left:4px solid #d63638;background:#fff5f5;border-radius:8px}.aira-screen-reader{position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden}
            .aira-submit-row{display:-webkit-box;display:-ms-flexbox;display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-top:12px}.aira-primary-note{margin:0;color:#50575e;font-size:12px}.aira-export-button{min-height:40px!important;padding-left:18px!important;padding-right:18px!important}
            .aira-inline-alert{display:none;margin:10px 0 0;padding:10px 12px;border-radius:10px;border-left:4px solid #d63638;background:#fff5f5;color:#7f1d1d}.aira-inline-alert.is-visible{display:block}.aira-inline-alert-ok{border-left-color:#16a34a;background:#f0fdf4;color:#14532d}
            .aira-monitor-summary{display:-webkit-box;display:-ms-flexbox;display:flex;gap:12px;-ms-flex-wrap:wrap;flex-wrap:wrap;margin:14px 0}.aira-monitor-kpi{-webkit-box-flex:1;-ms-flex:1 1 150px;flex:1 1 150px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:14px;padding:12px;text-decoration:none}.aira-monitor-kpi strong{display:block;font-size:20px;line-height:1.1;color:#111827}.aira-monitor-kpi span{display:block;margin-top:4px;color:#50575e;font-size:12px}.aira-monitor-kpi.is-current{border-color:#16a34a;box-shadow:0 0 0 2px rgba(22,163,74,.12);background:#f0fdf4}
            .aira-monitor-toolbar{display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-pack:justify;-ms-flex-pack:justify;justify-content:space-between;-webkit-box-align:center;-ms-flex-align:center;align-items:center;gap:12px;-ms-flex-wrap:wrap;flex-wrap:wrap;margin:12px 0 14px;padding:12px;background:#fff;border:1px solid #e5e7eb;border-radius:14px}.aira-monitor-filter-form{display:-webkit-box;display:-ms-flexbox;display:flex;gap:8px;-webkit-box-align:center;-ms-flex-align:center;align-items:center;-ms-flex-wrap:wrap;flex-wrap:wrap;margin:0}.aira-monitor-filter-form label{font-weight:700;color:#111827}.aira-monitor-filter-form select{min-height:36px;min-width:190px;max-width:100%}.aira-monitor-filter-chips{display:-webkit-box;display:-ms-flexbox;display:flex;gap:6px;-ms-flex-wrap:wrap;flex-wrap:wrap}.aira-monitor-chip{display:inline-flex;align-items:center;min-height:32px;padding:5px 11px;border-radius:999px;border:1px solid #dcdcde;background:#fff;color:#1d2327;text-decoration:none;font-size:12px;font-weight:700}.aira-monitor-chip:hover{background:#f6f7f7;color:#111827}.aira-monitor-chip.is-current{background:#111827;color:#fff;border-color:#111827}.aira-monitor-current{margin:8px 0 0;color:#50575e;font-size:12px}.aira-monitor-current strong{color:#111827}
            .aira-monitor-table-wrap{width:100%;overflow:auto;border:1px solid #e5e7eb;border-radius:14px;background:#fff;-webkit-overflow-scrolling:touch}.aira-monitor-table{width:100%;border-collapse:collapse;min-width:780px}.aira-monitor-table th,.aira-monitor-table td{padding:10px 12px;text-align:left;vertical-align:top;border-bottom:1px solid #edf0f2}.aira-monitor-table th{background:#f8fafc;color:#111827;font-weight:700;position:sticky;top:0;z-index:1}.aira-monitor-table tr:last-child td{border-bottom:none}.aira-monitor-name{font-weight:700;color:#111827}.aira-monitor-desc{margin-top:2px;color:#50575e;font-size:12px}.aira-monitor-actions{display:-webkit-box;display:-ms-flexbox;display:flex;gap:6px;flex-wrap:wrap}.aira-monitor-actions form{margin:0}.aira-monitor-actions .button{min-height:32px}.aira-state{display:inline-flex;align-items:center;gap:6px;padding:5px 10px;border-radius:999px;font-size:12px;font-weight:700;white-space:nowrap}.aira-state-active{background:#ecfdf5;color:#047857;border:1px solid #a7f3d0}.aira-state-network{background:#eef2ff;color:#3730a3;border:1px solid #c7d2fe}.aira-state-inactive{background:#f3f4f6;color:#374151;border:1px solid #e5e7eb}.aira-state-paused{background:#fff7ed;color:#9a3412;border:1px solid #fed7aa}.aira-state-protected{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}.aira-role-text{max-width:280px;overflow-wrap:anywhere}.aira-monitor-footnote{margin:10px 0 0;color:#50575e;font-size:12px}
            @media (max-width:960px){.aira-export-wrap{max-width:100%;padding-right:10px}.aira-card{padding:16px}.aira-page-head h1{font-size:24px}}
            @media (max-width:782px){.aira-page-head,.aira-topbar{display:block}.aira-title-row{margin-bottom:8px}.aira-flex-grid{display:block}.aira-flex-col{margin-bottom:12px}.aira-form-table,.aira-form-table tbody,.aira-form-table tr,.aira-form-table th,.aira-form-table td{display:block;width:100%;padding-left:0;padding-right:0}.aira-form-table th{padding-bottom:4px}.aira-actions .button{min-width:0}.aira-plugin-checklist{max-height:48vh}.aira-input{width:100%}.aira-submit-row .button-primary{width:100%;justify-content:center;text-align:center}.aira-monitor-summary{display:grid;grid-template-columns:1fr 1fr}.aira-monitor-toolbar{display:block}.aira-monitor-filter-form select,.aira-monitor-filter-form .button{width:100%}.aira-monitor-filter-chips{margin-top:10px}}
            @media (max-width:480px){.aira-export-wrap{font-size:13px}.aira-page-head h1{font-size:22px}.aira-card{padding:14px;border-radius:14px}.aira-actions{display:block}.aira-actions .button{width:100%;margin:0 0 8px}.aira-help{display:block}.aira-plugin-item{padding:10px 6px}.aira-progress-head{display:block}.aira-pill,.aira-badge{margin-top:6px}}
            @media (prefers-reduced-motion:reduce){.aira-progress-bar{transition:none}.aira-plugin-item{transition:none}}
            @media print{.aira-actions,.aira-submit-row,.aira-plugin-checklist,.aira-input,.notice,.update-nag{display:none!important}.aira-card{box-shadow:none;border-color:#bbb}.aira-export-wrap{max-width:100%}}
        ';
    }

    private function get_admin_js() {
        return <<<'JS'
(function(){
'use strict';
function ready(fn){if(document.readyState==='complete'||document.readyState==='interactive'){setTimeout(fn,0);}else if(document.addEventListener){document.addEventListener('DOMContentLoaded',fn,false);}else{window.attachEvent('onload',fn);}}
function qs(s,c){return (c||document).querySelector(s);}
function qsa(s,c){return (c||document).querySelectorAll(s);}
function on(el,ev,fn){if(!el){return;}if(el.addEventListener){el.addEventListener(ev,fn,false);}else if(el.attachEvent){el.attachEvent('on'+ev,fn);}}
function txt(el,v){if(el){el.innerHTML='';el.appendChild(document.createTextNode(v));}}
function addClass(el,c){if(!el){return;}if((' '+el.className+' ').indexOf(' '+c+' ')<0){el.className=el.className+' '+c;}}
function removeClass(el,c){if(!el){return;}el.className=(' '+el.className+' ').replace(' '+c+' ',' ').replace(/^\s+|\s+$/g,'');}
function detectBrowser(){var ua=navigator.userAgent||'';var name='Browser';if(ua.indexOf('Edg/')>-1){name='Microsoft Edge';}else if(ua.indexOf('OPR/')>-1||ua.indexOf('Opera')>-1){name='Opera';}else if(ua.indexOf('Firefox/')>-1){name='Firefox';}else if(ua.indexOf('Chrome/')>-1||ua.indexOf('CriOS/')>-1){name='Chrome';}else if(ua.indexOf('Safari/')>-1){name='Safari';}var os='Unknown OS';if(ua.indexOf('Windows')>-1){os='Windows';}else if(ua.indexOf('Mac OS X')>-1 && ua.indexOf('Mobile')===-1){os='macOS';}else if(ua.indexOf('Android')>-1){os='Android';}else if(ua.indexOf('iPhone')>-1||ua.indexOf('iPad')>-1||ua.indexOf('iPod')>-1){os='iOS / iPadOS';}else if(ua.indexOf('Linux')>-1){os='Linux';}var w=window.innerWidth||document.documentElement.clientWidth||screen.width;var device=w<600?'Mobile':(w<960?'Tablet':'Desktop');return name+' | '+os+' | '+device;}
function bindExportForm(form){
    if(!form){return;}
    var type=form.getAttribute('data-aira-export-type')||'plugin';
    var itemName=type==='theme'?'ธีม':'ปลั๊กอิน';
    var inputName=type==='theme'?'theme_slugs[]':'plugin_slugs[]';
    var wrap=qs('.aira-plugin-checklist',form);
    var boxes=wrap?qsa('input[name="'+inputName+'"]',wrap):[];
    var countEl=qs('[data-aira-count]',form);
    var selectAll=qs('[data-aira-select-all]',form);
    var clearAll=qs('[data-aira-clear-all]',form);
    var alertBox=qs('[data-aira-inline-alert]',form);
    var exportButton=qs('[data-aira-export-button]',form);
    function selectedCount(){var total=0;var i;for(i=0;i<boxes.length;i++){if(boxes[i].checked){total++;}}return total;}
    function updateCount(){txt(countEl,String(selectedCount()));if(alertBox&&selectedCount()>0){removeClass(alertBox,'is-visible');}}
    on(selectAll,'click',function(e){if(e&&e.preventDefault){e.preventDefault();}var i;for(i=0;i<boxes.length;i++){boxes[i].checked=true;}updateCount();});
    on(clearAll,'click',function(e){if(e&&e.preventDefault){e.preventDefault();}var i;for(i=0;i<boxes.length;i++){boxes[i].checked=false;}updateCount();});
    for(var i=0;i<boxes.length;i++){on(boxes[i],'change',updateCount);}
    on(form,'submit',function(e){
        if(selectedCount()<1){if(e&&e.preventDefault){e.preventDefault();}txt(alertBox,'ต้องเลือก'+itemName+'อย่างน้อย 1 ตัวก่อนกด Export');addClass(alertBox,'is-visible');if(alertBox&&alertBox.scrollIntoView){alertBox.scrollIntoView({block:'center'});}return false;}
        var progress=qs('[data-aira-progress]',form);var bar=qs('[data-aira-progress-bar]',form);var pct=qs('[data-aira-progress-percent]',form);var label=qs('[data-aira-progress-label]',form);
        if(exportButton){exportButton.setAttribute('aria-busy','true');}
        if(!progress||!bar){return true;}
        addClass(progress,'is-visible');
        var steps=[['20%','20','ตรวจคำสั่งและสิทธิ์ผู้ใช้'],['45%','45','รวบรวมโฟลเดอร์'+itemName+'ที่เลือก'],['75%','75','เตรียมไฟล์ ZIP สำหรับดาวน์โหลด'],['100%','100','ส่งไฟล์ดาวน์โหลดไปยังเบราว์เซอร์']];
        var n=0;function step(){if(n>=steps.length){return;}bar.style.width=steps[n][0];txt(pct,steps[n][1]+'%');txt(label,steps[n][2]);n++;if(n<steps.length){setTimeout(step,260);}}step();return true;
    });
    updateCount();
}
ready(function(){var info=qs('[data-aira-browser-info]');txt(info,detectBrowser());var forms=qsa('[data-aira-export-form]');for(var i=0;i<forms.length;i++){bindExportForm(forms[i]);}on(window,'resize',function(){txt(info,detectBrowser());});});
}());
JS;
    }

    public function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'aira-suite-export-plugin'));
        }

        $plugins = $this->get_plugins_grouped();
        $themes = $this->get_themes_grouped();
        $plugin_monitor_all = $this->get_plugins_monitor();
        $monitor_summary = $this->get_monitor_summary($plugin_monitor_all);
        $monitor_view = $this->get_current_monitor_view();
        $monitor_views = $this->get_monitor_view_options();
        $plugin_monitor = $this->filter_plugins_monitor($plugin_monitor_all, $monitor_view);
        $monitor_current_label = isset($monitor_views[$monitor_view]) ? $monitor_views[$monitor_view]['label'] : $monitor_views['all']['label'];
        $zip_engine = $this->get_zip_engine_label();
        $zip_ready = $this->has_zip_engine();
        $wp_version = get_bloginfo('version');
        $status = isset($_GET['aira_export_status']) ? sanitize_key(wp_unslash($_GET['aira_export_status'])) : '';
        ?>
        <div class="wrap aira-export-wrap">
            <div class="aira-page-head">
                <div>
                    <h1><?php echo esc_html__('AiRA Export Center', 'aira-suite-export-plugin'); ?></h1>
                    <p class="aira-muted"><?php echo esc_html__('Export installed plugins and themes into ZIP packages. Built for responsive WordPress admin screens across browsers, devices, and operating systems.', 'aira-suite-export-plugin'); ?></p>
                </div>
                <span class="aira-badge"><?php echo esc_html__('Cross-platform Ready', 'aira-suite-export-plugin'); ?></span>
            </div>

            <?php $this->render_admin_notice($status); ?>

            <div class="aira-card">
                <div class="aira-topbar">
                    <div class="aira-title-stack">
                        <div class="aira-title-row">
                            <strong><?php echo esc_html__('System Settings', 'aira-suite-export-plugin'); ?></strong>
                            <span class="aira-muted"> | <?php echo esc_html__('ปิด', 'aira-suite-export-plugin'); ?></span>
                        </div>
                        <span class="aira-muted"><?php echo esc_html__('Settings | โดย Thinkb4do | ดูรายละเอียด', 'aira-suite-export-plugin'); ?></span>
                    </div>
                    <?php if ($zip_ready) : ?>
                        <span class="aira-pill aira-pill-ok"><?php echo esc_html__('ZIP engine ready', 'aira-suite-export-plugin'); ?></span>
                    <?php else : ?>
                        <span class="aira-pill aira-pill-warn"><?php echo esc_html__('ZIP engine missing - button still shows error details after submit', 'aira-suite-export-plugin'); ?></span>
                    <?php endif; ?>
                </div>

                <div class="aira-flex-grid" aria-label="<?php echo esc_attr__('Compatibility status', 'aira-suite-export-plugin'); ?>">
                    <div class="aira-flex-col aira-status-card">
                        <strong><?php echo esc_html__('Browser / Device', 'aira-suite-export-plugin'); ?></strong>
                        <p data-aira-browser-info><?php echo esc_html__('Detecting browser...', 'aira-suite-export-plugin'); ?></p>
                    </div>
                    <div class="aira-flex-col aira-status-card">
                        <strong><?php echo esc_html__('WordPress / PHP', 'aira-suite-export-plugin'); ?></strong>
                        <p><?php echo esc_html('WP ' . $wp_version . ' | PHP ' . PHP_VERSION); ?></p>
                    </div>
                    <div class="aira-flex-col aira-status-card">
                        <strong><?php echo esc_html__('Export Engine', 'aira-suite-export-plugin'); ?></strong>
                        <p><?php echo esc_html($zip_engine); ?></p>
                    </div>
                    <div class="aira-flex-col aira-status-card">
                        <strong><?php echo esc_html__('Button Status', 'aira-suite-export-plugin'); ?></strong>
                        <p><?php echo esc_html__('Export buttons are always clickable. If no plugin/theme is selected, the page shows a clear warning instead of looking broken.', 'aira-suite-export-plugin'); ?></p>
                    </div>
                </div>

                <div class="aira-panel" style="margin-top:14px">
                    <h2 class="aira-section-title"><?php echo esc_html__('AiRA System Monitor — ดูว่าติดตั้งอะไรอยู่ / กำลังทำอะไร / เปิดหรือปิด', 'aira-suite-export-plugin'); ?></h2>
                    <p class="aira-muted"><?php echo esc_html__('หน้านี้อ่านจากปลั๊กอินที่ติดตั้งจริงใน wp-content/plugins และแสดงสถานะเปิด/ปิดแบบ Manual Safe Mode เพื่อไม่ให้ระบบเขียนทับหรือลบไฟล์เอง', 'aira-suite-export-plugin'); ?></p>

                    <div class="aira-monitor-summary" aria-label="<?php echo esc_attr__('Plugin monitor summary', 'aira-suite-export-plugin'); ?>">
                        <a class="aira-monitor-kpi <?php echo $monitor_view === 'all' ? 'is-current' : ''; ?>" href="<?php echo esc_url($this->get_monitor_filter_url('all')); ?>"><strong><?php echo esc_html($monitor_summary['installed']); ?></strong><span><?php echo esc_html__('ติดตั้งอยู่ทั้งหมด', 'aira-suite-export-plugin'); ?></span></a>
                        <a class="aira-monitor-kpi <?php echo $monitor_view === 'active' ? 'is-current' : ''; ?>" href="<?php echo esc_url($this->get_monitor_filter_url('active')); ?>"><strong><?php echo esc_html($monitor_summary['active']); ?></strong><span><?php echo esc_html__('เปิดใช้งานอยู่', 'aira-suite-export-plugin'); ?></span></a>
                        <a class="aira-monitor-kpi <?php echo $monitor_view === 'inactive' ? 'is-current' : ''; ?>" href="<?php echo esc_url($this->get_monitor_filter_url('inactive')); ?>"><strong><?php echo esc_html($monitor_summary['inactive']); ?></strong><span><?php echo esc_html__('ปิดอยู่', 'aira-suite-export-plugin'); ?></span></a>
                        <a class="aira-monitor-kpi <?php echo $monitor_view === 'network' ? 'is-current' : ''; ?>" href="<?php echo esc_url($this->get_monitor_filter_url('network')); ?>"><strong><?php echo esc_html($monitor_summary['network']); ?></strong><span><?php echo esc_html__('เปิดทั้งเครือข่าย', 'aira-suite-export-plugin'); ?></span></a>
                        <a class="aira-monitor-kpi <?php echo $monitor_view === 'paused' ? 'is-current' : ''; ?>" href="<?php echo esc_url($this->get_monitor_filter_url('paused')); ?>"><strong><?php echo esc_html($monitor_summary['paused']); ?></strong><span><?php echo esc_html__('หยุดชั่วคราว/ต้องตรวจ', 'aira-suite-export-plugin'); ?></span></a>
                        <a class="aira-monitor-kpi <?php echo $monitor_view === 'protected' ? 'is-current' : ''; ?>" href="<?php echo esc_url($this->get_monitor_filter_url('protected')); ?>"><strong><?php echo esc_html($monitor_summary['protected']); ?></strong><span><?php echo esc_html__('ตัวหลัก/ป้องกัน', 'aira-suite-export-plugin'); ?></span></a>
                    </div>

                    <div class="aira-monitor-toolbar" aria-label="<?php echo esc_attr__('Plugin monitor filters', 'aira-suite-export-plugin'); ?>">
                        <form method="get" class="aira-monitor-filter-form">
                            <input type="hidden" name="page" value="aira-plugin-export" />
                            <label for="aira-monitor-view"><?php echo esc_html__('เลือกดูสถานะ', 'aira-suite-export-plugin'); ?></label>
                            <select id="aira-monitor-view" name="aira_monitor_view" onchange="this.form.submit()">
                                <?php foreach ($monitor_views as $view_key => $view_data) : ?>
                                    <option value="<?php echo esc_attr($view_key); ?>" <?php selected($monitor_view, $view_key); ?>><?php echo esc_html($view_data['label']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="button"><?php echo esc_html__('ดูสถานะ', 'aira-suite-export-plugin'); ?></button>
                            <a class="button" href="<?php echo esc_url($this->get_monitor_filter_url('all')); ?>"><?php echo esc_html__('รีเซ็ต', 'aira-suite-export-plugin'); ?></a>
                        </form>
                        <div class="aira-monitor-filter-chips" aria-label="<?php echo esc_attr__('Quick status filters', 'aira-suite-export-plugin'); ?>">
                            <?php foreach ($monitor_views as $view_key => $view_data) : ?>
                                <a class="aira-monitor-chip <?php echo $monitor_view === $view_key ? 'is-current' : ''; ?>" href="<?php echo esc_url($this->get_monitor_filter_url($view_key)); ?>"><?php echo esc_html($view_data['short']); ?></a>
                            <?php endforeach; ?>
                        </div>
                        <p class="aira-monitor-current"><?php printf(esc_html__('กำลังแสดง: %1$s — พบ %2$d จากทั้งหมด %3$d รายการ', 'aira-suite-export-plugin'), '<strong>' . esc_html($monitor_current_label) . '</strong>', count($plugin_monitor), $monitor_summary['installed']); ?></p>
                    </div>

                    <div class="aira-monitor-table-wrap">
                        <table class="aira-monitor-table">
                            <thead>
                                <tr>
                                    <th><?php echo esc_html__('ปลั๊กอิน / ระบบ', 'aira-suite-export-plugin'); ?></th>
                                    <th><?php echo esc_html__('สถานะ', 'aira-suite-export-plugin'); ?></th>
                                    <th><?php echo esc_html__('กำลังทำอะไร / หน้าที่ตอนนี้', 'aira-suite-export-plugin'); ?></th>
                                    <th><?php echo esc_html__('เปิด / ปิด', 'aira-suite-export-plugin'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($plugin_monitor)) : ?>
                                    <tr><td colspan="4"><?php echo esc_html__('ยังไม่พบปลั๊กอินตามสถานะที่เลือก', 'aira-suite-export-plugin'); ?></td></tr>
                                <?php else : ?>
                                    <?php foreach ($plugin_monitor as $item) : ?>
                                        <tr>
                                            <td>
                                                <div class="aira-monitor-name"><?php echo esc_html($item['name']); ?></div>
                                                <div class="aira-monitor-desc"><?php echo esc_html($item['plugin_file']); ?> | v<?php echo esc_html($item['version']); ?></div>
                                            </td>
                                            <td><span class="aira-state <?php echo esc_attr($item['state_class']); ?>"><?php echo esc_html($item['state_label']); ?></span></td>
                                            <td class="aira-role-text"><?php echo esc_html($item['role']); ?></td>
                                            <td>
                                                <div class="aira-monitor-actions">
                                                    <?php if ($item['is_current_plugin']) : ?>
                                                        <span class="aira-state aira-state-protected"><?php echo esc_html__('ตัวหลัก ห้ามปิดจากหน้านี้', 'aira-suite-export-plugin'); ?></span>
                                                    <?php elseif ($item['is_network_active']) : ?>
                                                        <span class="aira-state aira-state-network"><?php echo esc_html__('เปิดทั้งเครือข่าย', 'aira-suite-export-plugin'); ?></span>
                                                    <?php elseif ($item['is_active']) : ?>
                                                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                                            <input type="hidden" name="aira_toggle_nonce" value="<?php echo esc_attr(wp_create_nonce('aira_toggle_plugin_status_action')); ?>" />
                                                            <input type="hidden" name="action" value="aira_toggle_plugin_status" />
                                                            <input type="hidden" name="plugin_file" value="<?php echo esc_attr($item['plugin_file']); ?>" />
                                                            <input type="hidden" name="target_action" value="deactivate" />
                                                            <button type="submit" class="button"><?php echo esc_html__('ปิด', 'aira-suite-export-plugin'); ?></button>
                                                        </form>
                                                    <?php else : ?>
                                                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                                            <input type="hidden" name="aira_toggle_nonce" value="<?php echo esc_attr(wp_create_nonce('aira_toggle_plugin_status_action')); ?>" />
                                                            <input type="hidden" name="action" value="aira_toggle_plugin_status" />
                                                            <input type="hidden" name="plugin_file" value="<?php echo esc_attr($item['plugin_file']); ?>" />
                                                            <input type="hidden" name="target_action" value="activate" />
                                                            <button type="submit" class="button button-primary"><?php echo esc_html__('เปิด', 'aira-suite-export-plugin'); ?></button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <p class="aira-monitor-footnote"><?php echo esc_html__('หมายเหตุ: ตัวกรองสถานะอ่านจากข้อมูลปลั๊กอินจริงของ WordPress ส่วนช่อง “กำลังทำอะไร” เป็นการสรุปหน้าที่จากชื่อ/คำอธิบายปลั๊กอิน ไม่ใช่การอ่าน process ภายในเซิร์ฟเวอร์แบบ real-time เพื่อความปลอดภัยและไม่เพิ่มภาระเว็บ', 'aira-suite-export-plugin'); ?></p>
                </div>

                <noscript>
                    <div class="aira-noscript">
                        <?php echo esc_html__('JavaScript is disabled. Export still works after selecting at least one plugin, but select-all, live count, and progress display will be inactive.', 'aira-suite-export-plugin'); ?>
                    </div>
                </noscript>

                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="aira-form" data-aira-export-form data-aira-export-type="plugin">
                    <?php wp_nonce_field('aira_export_selected_plugin_action', 'aira_export_nonce'); ?>
                    <input type="hidden" name="action" value="aira_export_selected_plugin" />

                    <table class="aira-form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row"><?php echo esc_html__('Select Plugin(s)', 'aira-suite-export-plugin'); ?></th>
                                <td>
                                    <div class="aira-actions" aria-label="<?php echo esc_attr__('Plugin selection actions', 'aira-suite-export-plugin'); ?>">
                                        <button type="button" class="button" data-aira-select-all><?php echo esc_html__('Select All', 'aira-suite-export-plugin'); ?></button>
                                        <button type="button" class="button" data-aira-clear-all><?php echo esc_html__('Clear All', 'aira-suite-export-plugin'); ?></button>
                                        <span class="aira-help aira-muted"><?php echo esc_html__('Selected:', 'aira-suite-export-plugin'); ?> <span class="aira-count" data-aira-count>0</span></span>
                                    </div>

                                    <div class="aira-plugin-checklist" role="group" aria-label="<?php echo esc_attr__('Installed plugins', 'aira-suite-export-plugin'); ?>">
                                        <?php if (empty($plugins)) : ?>
                                            <p class="aira-muted"><?php echo esc_html__('No installed plugin folders were found.', 'aira-suite-export-plugin'); ?></p>
                                        <?php else : ?>
                                            <?php foreach ($plugins as $slug => $label) : ?>
                                                <label class="aira-plugin-item" for="aira-plugin-<?php echo esc_attr($slug); ?>">
                                                    <input type="checkbox" name="plugin_slugs[]" id="aira-plugin-<?php echo esc_attr($slug); ?>" value="<?php echo esc_attr($slug); ?>" />
                                                    <span><?php echo esc_html($label); ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>

                                    <div class="aira-inline-alert" data-aira-inline-alert role="alert"></div>

                                    <label class="aira-screen-reader" for="aira-export-name"><?php echo esc_html__('Optional ZIP name', 'aira-suite-export-plugin'); ?></label>
                                    <input id="aira-export-name" type="text" name="export_name" class="regular-text aira-input" placeholder="<?php echo esc_attr__('Optional ZIP name, e.g. aira-bundle-apr-2026', 'aira-suite-export-plugin'); ?>" />
                                    <p class="description"><?php echo esc_html__('Step 1: select plugin(s). Step 2: optionally name the ZIP. Step 3: press Export Selected Plugin ZIP.', 'aira-suite-export-plugin'); ?></p>

                                    <div class="aira-progress-wrap" data-aira-progress aria-live="polite">
                                        <div class="aira-progress-head">
                                            <strong data-aira-progress-label><?php echo esc_html__('Waiting for command...', 'aira-suite-export-plugin'); ?></strong>
                                            <span class="aira-count" data-aira-progress-percent>0%</span>
                                        </div>
                                        <div class="aira-progress-track" aria-hidden="true"><div class="aira-progress-bar" data-aira-progress-bar></div></div>
                                        <div class="aira-progress-note"><?php echo esc_html__('Progress shows the export preparation steps before the browser starts downloading the ZIP file.', 'aira-suite-export-plugin'); ?></div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="aira-submit-row">
                        <button type="submit" name="aira_export_submit" value="1" class="button button-primary aira-export-button" data-aira-export-button><?php echo esc_html__('Export Selected Plugin ZIP', 'aira-suite-export-plugin'); ?></button>
                        <p class="aira-primary-note"><?php echo esc_html__('รองรับ Chrome, Edge, Firefox, Safari, Opera และหน้าจอ Desktop / Tablet / Mobile', 'aira-suite-export-plugin'); ?></p>
                    </div>
                </form>

                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="aira-form aira-theme-export-form" data-aira-export-form data-aira-export-type="theme">
                    <?php wp_nonce_field('aira_export_selected_theme_action', 'aira_export_theme_nonce'); ?>
                    <input type="hidden" name="action" value="aira_export_selected_theme" />

                    <table class="aira-form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row"><?php echo esc_html__('Select Theme(s)', 'aira-suite-export-plugin'); ?></th>
                                <td>
                                    <div class="aira-actions" aria-label="<?php echo esc_attr__('Theme selection actions', 'aira-suite-export-plugin'); ?>">
                                        <button type="button" class="button" data-aira-select-all><?php echo esc_html__('Select All', 'aira-suite-export-plugin'); ?></button>
                                        <button type="button" class="button" data-aira-clear-all><?php echo esc_html__('Clear All', 'aira-suite-export-plugin'); ?></button>
                                        <span class="aira-help aira-muted"><?php echo esc_html__('Selected:', 'aira-suite-export-plugin'); ?> <span class="aira-count" data-aira-count>0</span></span>
                                    </div>

                                    <div class="aira-plugin-checklist" role="group" aria-label="<?php echo esc_attr__('Installed themes', 'aira-suite-export-plugin'); ?>">
                                        <?php if (empty($themes)) : ?>
                                            <p class="aira-muted"><?php echo esc_html__('No installed theme folders were found.', 'aira-suite-export-plugin'); ?></p>
                                        <?php else : ?>
                                            <?php foreach ($themes as $slug => $label) : ?>
                                                <label class="aira-plugin-item" for="aira-theme-<?php echo esc_attr($slug); ?>">
                                                    <input type="checkbox" name="theme_slugs[]" id="aira-theme-<?php echo esc_attr($slug); ?>" value="<?php echo esc_attr($slug); ?>" />
                                                    <span><?php echo esc_html($label); ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>

                                    <div class="aira-inline-alert" data-aira-inline-alert role="alert"></div>

                                    <label class="aira-screen-reader" for="aira-theme-export-name"><?php echo esc_html__('Optional theme ZIP name', 'aira-suite-export-plugin'); ?></label>
                                    <input id="aira-theme-export-name" type="text" name="export_name" class="regular-text aira-input" placeholder="<?php echo esc_attr__('Optional ZIP name, e.g. thinkb4do-theme-backup', 'aira-suite-export-plugin'); ?>" />
                                    <p class="description"><?php echo esc_html__('Step 1: select theme(s). Step 2: optionally name the ZIP. Step 3: press Export Selected Theme ZIP. For child themes, export the parent theme too if you need a complete install package.', 'aira-suite-export-plugin'); ?></p>

                                    <div class="aira-progress-wrap" data-aira-progress aria-live="polite">
                                        <div class="aira-progress-head">
                                            <strong data-aira-progress-label><?php echo esc_html__('Waiting for command...', 'aira-suite-export-plugin'); ?></strong>
                                            <span class="aira-count" data-aira-progress-percent>0%</span>
                                        </div>
                                        <div class="aira-progress-track" aria-hidden="true"><div class="aira-progress-bar" data-aira-progress-bar></div></div>
                                        <div class="aira-progress-note"><?php echo esc_html__('Progress shows the theme export preparation steps before the browser starts downloading the ZIP file.', 'aira-suite-export-plugin'); ?></div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="aira-submit-row">
                        <button type="submit" name="aira_theme_export_submit" value="1" class="button button-primary aira-export-button" data-aira-export-button><?php echo esc_html__('Export Selected Theme ZIP', 'aira-suite-export-plugin'); ?></button>
                        <p class="aira-primary-note"><?php echo esc_html__('Export theme folders from wp-content/themes safely without deleting or overwriting files.', 'aira-suite-export-plugin'); ?></p>
                    </div>
                </form>

                <div class="aira-note">
                    <strong><?php echo esc_html__('คำอธิบาย..', 'aira-suite-export-plugin'); ?></strong>
                    <ul class="aira-list">
                        <li><?php echo esc_html__('แก้จุดที่ทำให้ปุ่ม Export เหมือนกดไม่ได้: ปุ่มไม่ถูกปิดใช้งานแล้ว และมีข้อความเตือนชัดเจนถ้ายังไม่ได้เลือกปลั๊กอินหรือธีม', 'aira-suite-export-plugin'); ?></li>
                        <li><?php echo esc_html__('เปลี่ยนระบบดาวน์โหลดให้ล้าง output buffer ก่อนส่ง ZIP เพื่อป้องกันไฟล์ไม่เริ่มดาวน์โหลดหรือไฟล์ ZIP เสีย', 'aira-suite-export-plugin'); ?></li>
                        <li><?php echo esc_html__('มี fallback สร้าง ZIP ด้วย PclZip ของ WordPress เมื่อเซิร์ฟเวอร์ไม่มี ZipArchive ทั้งฝั่ง Plugin และ Theme Export', 'aira-suite-export-plugin'); ?></li>
                        <li><?php echo esc_html__('เพิ่ม Theme Export จาก wp-content/themes โดยไม่ลบ/ไม่เขียนทับไฟล์เดิม และปรับ UI ให้ responsive ใช้งานง่ายทั้งคอม แท็บเล็ต มือถือ', 'aira-suite-export-plugin'); ?></li>
                    </ul>
                </div>

                <div class="aira-footer">
                    <?php echo esc_html__('Version', 'aira-suite-export-plugin'); ?> <?php echo esc_html(AIRA_EXPORT_PLUGIN_VERSION); ?> | <?php echo esc_html__('โดย Thinkb4do', 'aira-suite-export-plugin'); ?> | <?php echo esc_html__('ดูรายละเอียด', 'aira-suite-export-plugin'); ?>
                </div>
            </div>
        </div>
        <?php
    }

    private function render_admin_notice($status) {
        if ($status === '') {
            return;
        }

        $messages = array(
            'no_selection' => __('ต้องเลือกปลั๊กอินอย่างน้อย 1 ตัวก่อน Export', 'aira-suite-export-plugin'),
            'no_theme_selection' => __('ต้องเลือกธีมอย่างน้อย 1 ตัวก่อน Export', 'aira-suite-export-plugin'),
            'no_zip_engine' => __('ไม่พบระบบสร้าง ZIP: ให้เปิด PHP ZipArchive หรือใช้ไฟล์ PclZip ของ WordPress', 'aira-suite-export-plugin'),
            'plugins_dir_missing' => __('ไม่พบโฟลเดอร์ wp-content/plugins', 'aira-suite-export-plugin'),
            'themes_dir_missing' => __('ไม่พบโฟลเดอร์ธีมของ WordPress หรือ WordPress อ่าน theme root ไม่ได้', 'aira-suite-export-plugin'),
            'no_valid_plugins' => __('ไม่พบโฟลเดอร์ปลั๊กอินที่ถูกต้องจากรายการที่เลือก', 'aira-suite-export-plugin'),
            'no_valid_themes' => __('ไม่พบโฟลเดอร์ธีมที่ถูกต้องจากรายการที่เลือก', 'aira-suite-export-plugin'),
            'theme_multi_root_zip_failed' => __('ไม่สามารถ Export ธีมจากหลาย theme root พร้อมกันด้วย PclZip ได้ ให้เปิด ZipArchive หรือเลือกธีมจาก root เดียวกัน', 'aira-suite-export-plugin'),
            'temp_failed' => __('สร้างไฟล์ชั่วคราวไม่ได้: ตรวจสิทธิ์เขียนโฟลเดอร์ temp ของ WordPress/Hosting', 'aira-suite-export-plugin'),
            'zip_failed' => __('สร้าง ZIP ไม่สำเร็จ: อาจเกิดจากสิทธิ์ไฟล์, ขนาดไฟล์ใหญ่เกินไป หรือ ZIP engine บนโฮสต์มีปัญหา', 'aira-suite-export-plugin'),
            'headers_sent' => __('ส่งไฟล์ดาวน์โหลดไม่ได้ เพราะมี output ถูกส่งก่อน header แล้ว', 'aira-suite-export-plugin'),
            'invalid_nonce' => __('คำสั่งหมดอายุหรือไม่ถูกต้อง กรุณารีเฟรชหน้าแล้วลองใหม่', 'aira-suite-export-plugin'),
            'activated' => __('เปิดปลั๊กอินสำเร็จ', 'aira-suite-export-plugin'),
            'deactivated' => __('ปิดปลั๊กอินสำเร็จ', 'aira-suite-export-plugin'),
            'toggle_failed' => __('เปิด/ปิดปลั๊กอินไม่สำเร็จ: ให้ตรวจ error ของปลั๊กอินนั้นหรือสิทธิ์ผู้ใช้', 'aira-suite-export-plugin'),
            'toggle_protected' => __('ไม่ปิดปลั๊กอินตัวหลักจากหน้านี้ เพื่อไม่ให้หน้า Monitor หายไประหว่างใช้งาน', 'aira-suite-export-plugin'),
            'toggle_invalid' => __('คำสั่งเปิด/ปิดไม่ถูกต้อง หรือไม่พบปลั๊กอินที่เลือก', 'aira-suite-export-plugin'),
        );

        if (!isset($messages[$status])) {
            return;
        }

        $success_statuses = array('activated', 'deactivated');
        $notice_class = in_array($status, $success_statuses, true) ? 'notice-success' : 'notice-error';
        echo '<div class="notice ' . esc_attr($notice_class) . ' is-dismissible"><p><strong>AiRA Export:</strong> ' . esc_html($messages[$status]) . '</p></div>';
    }

    public function register_monitor_rest_route() {
        register_rest_route('aira-suite-export/v1', '/plugin-monitor', array(
            'methods' => 'GET',
            'permission_callback' => function() { return current_user_can('manage_options'); },
            'callback' => function($request) {
                $items = $this->get_plugins_monitor();
                $status = $request instanceof WP_REST_Request ? sanitize_key((string) $request->get_param('status')) : 'all';
                if (!$this->is_valid_monitor_view($status)) {
                    $status = 'all';
                }
                $filtered_items = $this->filter_plugins_monitor($items, $status);
                $views = $this->get_monitor_view_options();
                return rest_ensure_response(array(
                    'ok' => true,
                    'version' => AIRA_EXPORT_PLUGIN_VERSION,
                    'summary' => $this->get_monitor_summary($items),
                    'filter' => array(
                        'status' => $status,
                        'label' => isset($views[$status]) ? $views[$status]['label'] : $views['all']['label'],
                        'count' => count($filtered_items),
                        'available_statuses' => array_keys($views),
                    ),
                    'items' => $filtered_items,
                    'safe_mode' => true,
                    'endpoint' => '/wp-json/aira-suite-export/v1/plugin-monitor?status=' . rawurlencode($status),
                ));
            },
        ));
    }

    public function handle_toggle_plugin() {
        if (!current_user_can('activate_plugins')) {
            wp_die(esc_html__('You do not have permission to change plugin status.', 'aira-suite-export-plugin'));
        }

        if (!isset($_POST['aira_toggle_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['aira_toggle_nonce'])), 'aira_toggle_plugin_status_action')) {
            $this->redirect_with_status('invalid_nonce');
        }

        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugin_file = isset($_POST['plugin_file']) ? sanitize_text_field(wp_unslash($_POST['plugin_file'])) : '';
        $target_action = isset($_POST['target_action']) ? sanitize_key(wp_unslash($_POST['target_action'])) : '';
        $all_plugins = get_plugins();
        $current_plugin = plugin_basename(AIRA_EXPORT_PLUGIN_FILE);

        if ($plugin_file === '' || !isset($all_plugins[$plugin_file]) || !in_array($target_action, array('activate', 'deactivate'), true)) {
            $this->redirect_with_status('toggle_invalid');
        }

        if ($plugin_file === $current_plugin) {
            $this->redirect_with_status('toggle_protected');
        }

        if ($target_action === 'activate') {
            $result = activate_plugin($plugin_file, '', false, false);
            if (is_wp_error($result)) {
                $this->redirect_with_status('toggle_failed');
            }
            $this->redirect_with_status('activated');
        }

        if ($target_action === 'deactivate') {
            deactivate_plugins($plugin_file, false, false);
            $this->redirect_with_status('deactivated');
        }

        $this->redirect_with_status('toggle_invalid');
    }

    private function get_monitor_view_options() {
        return array(
            'all' => array(
                'label' => __('ทั้งหมด', 'aira-suite-export-plugin'),
                'short' => __('ทั้งหมด', 'aira-suite-export-plugin'),
            ),
            'active' => array(
                'label' => __('เปิดใช้งานอยู่', 'aira-suite-export-plugin'),
                'short' => __('เปิดอยู่', 'aira-suite-export-plugin'),
            ),
            'inactive' => array(
                'label' => __('ปิดอยู่', 'aira-suite-export-plugin'),
                'short' => __('ปิดอยู่', 'aira-suite-export-plugin'),
            ),
            'network' => array(
                'label' => __('เปิดทั้งเครือข่าย', 'aira-suite-export-plugin'),
                'short' => __('Network', 'aira-suite-export-plugin'),
            ),
            'paused' => array(
                'label' => __('หยุดชั่วคราว / ต้องตรวจ', 'aira-suite-export-plugin'),
                'short' => __('ต้องตรวจ', 'aira-suite-export-plugin'),
            ),
            'protected' => array(
                'label' => __('ตัวหลัก / ป้องกันการปิด', 'aira-suite-export-plugin'),
                'short' => __('ตัวหลัก', 'aira-suite-export-plugin'),
            ),
        );
    }

    private function is_valid_monitor_view($view) {
        $views = $this->get_monitor_view_options();
        return isset($views[$view]);
    }

    private function get_current_monitor_view() {
        $view = isset($_GET['aira_monitor_view']) ? sanitize_key(wp_unslash($_GET['aira_monitor_view'])) : 'all';
        return $this->is_valid_monitor_view($view) ? $view : 'all';
    }

    private function get_monitor_filter_url($view) {
        if (!$this->is_valid_monitor_view($view)) {
            $view = 'all';
        }

        return add_query_arg(
            array(
                'page' => 'aira-plugin-export',
                'aira_monitor_view' => $view,
            ),
            admin_url('tools.php')
        );
    }

    private function filter_plugins_monitor($items, $view) {
        if (!$this->is_valid_monitor_view($view) || $view === 'all') {
            return array_values((array) $items);
        }

        $filtered = array();
        foreach ((array) $items as $item) {
            if ($view === 'protected' && !empty($item['is_current_plugin'])) {
                $filtered[] = $item;
                continue;
            }

            if ($view === 'network' && !empty($item['is_network_active'])) {
                $filtered[] = $item;
                continue;
            }

            if ($view === 'paused' && !empty($item['is_paused'])) {
                $filtered[] = $item;
                continue;
            }

            if ($view === 'active' && !empty($item['is_active']) && empty($item['is_network_active']) && empty($item['is_paused'])) {
                $filtered[] = $item;
                continue;
            }

            if ($view === 'inactive' && empty($item['is_active']) && empty($item['is_paused'])) {
                $filtered[] = $item;
            }
        }

        return array_values($filtered);
    }

    private function get_plugins_monitor() {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $all_plugins = get_plugins();
        $items = array();
        $current_plugin = plugin_basename(AIRA_EXPORT_PLUGIN_FILE);

        foreach ($all_plugins as $plugin_file => $data) {
            $name = !empty($data['Name']) ? wp_strip_all_tags($data['Name']) : basename($plugin_file);
            $version = !empty($data['Version']) ? wp_strip_all_tags($data['Version']) : '—';
            $description = !empty($data['Description']) ? wp_strip_all_tags($data['Description']) : '';
            $slug_parts = explode('/', $plugin_file);
            $slug = isset($slug_parts[0]) ? $slug_parts[0] : $plugin_file;
            $is_active = function_exists('is_plugin_active') ? is_plugin_active($plugin_file) : in_array($plugin_file, (array) get_option('active_plugins', array()), true);
            $is_network_active = function_exists('is_plugin_active_for_network') ? is_plugin_active_for_network($plugin_file) : false;
            $is_paused = function_exists('is_plugin_paused') ? is_plugin_paused($plugin_file) : false;

            if ($is_paused) {
                $state_label = __('หยุดชั่วคราว / ต้องตรวจ', 'aira-suite-export-plugin');
                $state_class = 'aira-state-paused';
            } elseif ($is_network_active) {
                $state_label = __('เปิดทั้งเครือข่าย', 'aira-suite-export-plugin');
                $state_class = 'aira-state-network';
            } elseif ($is_active) {
                $state_label = __('เปิดอยู่', 'aira-suite-export-plugin');
                $state_class = 'aira-state-active';
            } else {
                $state_label = __('ปิดอยู่', 'aira-suite-export-plugin');
                $state_class = 'aira-state-inactive';
            }

            $items[] = array(
                'name' => $name,
                'slug' => $slug,
                'plugin_file' => $plugin_file,
                'version' => $version,
                'description' => $description,
                'state_label' => $state_label,
                'state_class' => $state_class,
                'state_key' => $is_paused ? 'paused' : ($is_network_active ? 'network' : ($is_active ? 'active' : 'inactive')),
                'is_active' => (bool) ($is_active || $is_network_active),
                'is_network_active' => (bool) $is_network_active,
                'is_paused' => (bool) $is_paused,
                'is_current_plugin' => ($plugin_file === $current_plugin),
                'role' => $this->infer_plugin_role($name, $slug, $description, ($is_active || $is_network_active), $is_paused),
            );
        }

        usort($items, function($a, $b) {
            if ($a['is_active'] !== $b['is_active']) {
                return $a['is_active'] ? -1 : 1;
            }
            return strcasecmp($a['name'], $b['name']);
        });

        return $items;
    }

    private function get_monitor_summary($items) {
        $summary = array('installed' => 0, 'active' => 0, 'inactive' => 0, 'network' => 0, 'paused' => 0, 'protected' => 0);
        foreach ((array) $items as $item) {
            $summary['installed']++;
            if (!empty($item['is_current_plugin'])) {
                $summary['protected']++;
            }
            if (!empty($item['is_paused'])) {
                $summary['paused']++;
            }
            if (!empty($item['is_network_active'])) {
                $summary['network']++;
            }
            if (!empty($item['is_active'])) {
                $summary['active']++;
            } else {
                $summary['inactive']++;
            }
        }
        return $summary;
    }

    private function infer_plugin_role($name, $slug, $description, $is_active, $is_paused) {
        $haystack = strtolower($name . ' ' . $slug . ' ' . $description);
        $role = '';

        if (strpos($haystack, 'smart chat') !== false || strpos($haystack, 'chat') !== false || strpos($haystack, 'companion') !== false) {
            $role = 'หน้าจอแชท / รับคำสั่ง / ประสานงานกับผู้ช่วย AI';
        } elseif (strpos($haystack, 'export') !== false || strpos($haystack, 'zip') !== false || strpos($haystack, 'package') !== false) {
            $role = 'แพ็กไฟล์ / ส่งออก ZIP / ตรวจโครงสร้างแพ็กเกจก่อนดาวน์โหลด';
        } elseif (strpos($haystack, 'product') !== false || strpos($haystack, 'store') !== false || strpos($haystack, 'installer') !== false) {
            $role = 'จัดการผลิตภัณฑ์ / ติดตั้ง / ประเมินสถานะความพร้อม';
        } elseif (strpos($haystack, 'bridge') !== false || strpos($haystack, 'sync') !== false || strpos($haystack, 'relationship') !== false || strpos($haystack, 'connect') !== false) {
            $role = 'เชื่อมระบบ / ซิงก์ข้อมูล / ประสานปลั๊กอินในเครือ';
        } elseif (strpos($haystack, 'api') !== false || strpos($haystack, 'webhook') !== false) {
            $role = 'เชื่อมต่อ API / ตรวจคีย์ / รายงานสถานะ endpoint';
        } elseif (strpos($haystack, 'repair') !== false || strpos($haystack, 'restore') !== false || strpos($haystack, 'backup') !== false || strpos($haystack, 'updraft') !== false) {
            $role = 'สำรองข้อมูล / กู้ระบบ / ซ่อมแซมเมื่อเว็บมีปัญหา';
        } elseif (strpos($haystack, 'security') !== false || strpos($haystack, 'firewall') !== false || strpos($haystack, 'wordfence') !== false) {
            $role = 'ดูแลความปลอดภัย / ตรวจความเสี่ยง / ป้องกันการโจมตี';
        } elseif (strpos($haystack, 'seo') !== false || strpos($haystack, 'rank math') !== false || strpos($haystack, 'yoast') !== false) {
            $role = 'ช่วย SEO / ตรวจโครงสร้างเนื้อหา / จัดการข้อมูลค้นหา';
        } elseif (strpos($haystack, 'woocommerce') !== false || strpos($haystack, 'shop') !== false || strpos($haystack, 'commerce') !== false) {
            $role = 'ระบบร้านค้า / สินค้า / คำสั่งซื้อ / การชำระเงิน';
        } elseif (strpos($haystack, 'elementor') !== false || strpos($haystack, 'gutenberg') !== false || strpos($haystack, 'blocks') !== false || strpos($haystack, 'theme') !== false) {
            $role = 'จัดหน้าเว็บ / ธีม / บล็อก / ส่วนแสดงผลหน้าเว็บ';
        } elseif ($description !== '') {
            if (function_exists('mb_substr') && function_exists('mb_strlen')) {
                $role = mb_substr($description, 0, 120);
                if (mb_strlen($description) > 120) {
                    $role .= '…';
                }
            } else {
                $role = substr($description, 0, 120);
                if (strlen($description) > 120) {
                    $role .= '...';
                }
            }
        } else {
            $role = 'ปลั๊กอินทั่วไป — ยังไม่มีคำอธิบายหน้าที่จากไฟล์หลัก';
        }

        if ($is_paused) {
            return 'หยุดชั่วคราวจากระบบ WordPress Recovery Mode — ควรตรวจ error ก่อนเปิดใช้งานใหม่: ' . $role;
        }

        return ($is_active ? 'กำลังทำงาน: ' : 'ติดตั้งแล้วแต่ยังปิดอยู่: ') . $role;
    }

    private function get_plugins_grouped() {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $all_plugins = get_plugins();
        $grouped = array();

        foreach ($all_plugins as $plugin_file => $data) {
            $parts = explode('/', $plugin_file);
            $slug = $parts[0];

            if (!isset($grouped[$slug])) {
                $name = !empty($data['Name']) ? $data['Name'] : $slug;
                $version = !empty($data['Version']) ? $data['Version'] : '—';
                $grouped[$slug] = $name . ' (' . $slug . ') - v' . $version;
            }
        }

        asort($grouped);
        return $grouped;
    }

    private function get_themes_grouped() {
        $themes = wp_get_themes(array('errors' => null));
        $grouped = array();
        $active_stylesheet = function_exists('get_stylesheet') ? get_stylesheet() : '';

        foreach ($themes as $stylesheet => $theme) {
            if (!is_object($theme)) {
                continue;
            }

            $name = $theme->get('Name');
            if ($name === '') {
                $name = $stylesheet;
            }

            $version = $theme->get('Version');
            if ($version === '') {
                $version = '—';
            }

            $label = wp_strip_all_tags($name) . ' (' . sanitize_file_name($stylesheet) . ') - v' . wp_strip_all_tags($version);
            $template = method_exists($theme, 'get_template') ? $theme->get_template() : '';
            if ($stylesheet === $active_stylesheet) {
                $label .= ' — ' . __('Active theme', 'aira-suite-export-plugin');
            }
            if ($template !== '' && $template !== $stylesheet) {
                $label .= ' — ' . sprintf(__('Child theme of %s', 'aira-suite-export-plugin'), $template);
            }

            $grouped[$stylesheet] = $label;
        }

        asort($grouped);
        return $grouped;
    }

    public function handle_export() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to do this action.', 'aira-suite-export-plugin'));
        }

        if (!isset($_POST['aira_export_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['aira_export_nonce'])), 'aira_export_selected_plugin_action')) {
            $this->redirect_with_status('invalid_nonce');
        }

        $plugin_slugs = isset($_POST['plugin_slugs']) ? (array) wp_unslash($_POST['plugin_slugs']) : array();
        $plugin_slugs = array_values(array_unique(array_filter(array_map(array($this, 'sanitize_plugin_slug'), $plugin_slugs))));
        $export_name = isset($_POST['export_name']) ? sanitize_file_name(wp_unslash($_POST['export_name'])) : '';

        if (empty($plugin_slugs)) {
            $this->redirect_with_status('no_selection');
        }

        if (!$this->has_zip_engine()) {
            $this->redirect_with_status('no_zip_engine');
        }

        $plugins_dir = WP_PLUGIN_DIR;
        $plugins_real = realpath($plugins_dir);

        if (!$plugins_real) {
            $this->redirect_with_status('plugins_dir_missing');
        }

        $valid_sources = array();

        foreach ($plugin_slugs as $plugin_slug) {
            if ($plugin_slug === '') {
                continue;
            }

            $source_dir = realpath($plugins_dir . DIRECTORY_SEPARATOR . $plugin_slug);

            if (!$source_dir || !$this->is_path_inside($source_dir, $plugins_real) || !is_dir($source_dir)) {
                continue;
            }

            $valid_sources[$plugin_slug] = $source_dir;
        }

        if (empty($valid_sources)) {
            $this->redirect_with_status('no_valid_plugins');
        }

        if (!empty($export_name)) {
            $archive_base = $export_name;
        } elseif (count($valid_sources) === 1) {
            $keys = array_keys($valid_sources);
            $archive_base = reset($keys);
        } else {
            $archive_base = 'aira-plugin-bundle-' . gmdate('Y-m-d-His');
        }

        $archive_base = sanitize_file_name($archive_base);
        if ($archive_base === '') {
            $archive_base = 'aira-plugin-export-' . gmdate('Y-m-d-His');
        }

        $zip_file = $this->get_temp_zip_path($archive_base);

        if (!$zip_file) {
            $this->redirect_with_status('temp_failed');
        }

        $created = false;
        if (class_exists('ZipArchive')) {
            $created = $this->create_zip_with_ziparchive($zip_file, $valid_sources);
        }

        if (!$created) {
            $created = $this->create_zip_with_pclzip($zip_file, array_values($valid_sources), $plugins_real);
        }

        if (!$created || !file_exists($zip_file) || filesize($zip_file) < 1) {
            @unlink($zip_file);
            $this->redirect_with_status('zip_failed');
        }

        $this->send_zip_download($zip_file, sanitize_file_name($archive_base . '.zip'));
    }

    public function handle_theme_export() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to do this action.', 'aira-suite-export-plugin'));
        }

        if (!isset($_POST['aira_export_theme_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['aira_export_theme_nonce'])), 'aira_export_selected_theme_action')) {
            $this->redirect_with_status('invalid_nonce');
        }

        $theme_slugs = isset($_POST['theme_slugs']) ? (array) wp_unslash($_POST['theme_slugs']) : array();
        $theme_slugs = array_values(array_unique(array_filter(array_map(array($this, 'sanitize_plugin_slug'), $theme_slugs))));
        $export_name = isset($_POST['export_name']) ? sanitize_file_name(wp_unslash($_POST['export_name'])) : '';

        if (empty($theme_slugs)) {
            $this->redirect_with_status('no_theme_selection');
        }

        if (!$this->has_zip_engine()) {
            $this->redirect_with_status('no_zip_engine');
        }

        $themes = wp_get_themes(array('errors' => null));
        if (empty($themes) || !is_array($themes)) {
            $this->redirect_with_status('themes_dir_missing');
        }

        $valid_sources = array();
        $valid_roots = array();

        foreach ($theme_slugs as $theme_slug) {
            if ($theme_slug === '' || !isset($themes[$theme_slug]) || !is_object($themes[$theme_slug])) {
                continue;
            }

            $theme = $themes[$theme_slug];
            $theme_root = method_exists($theme, 'get_theme_root') ? $theme->get_theme_root() : get_theme_root($theme_slug);
            $theme_root_real = realpath($theme_root);
            $source_dir = realpath($theme_root . DIRECTORY_SEPARATOR . $theme_slug);

            if (!$theme_root_real || !$source_dir || !$this->is_path_inside($source_dir, $theme_root_real) || !is_dir($source_dir)) {
                continue;
            }

            $valid_sources[$theme_slug] = $source_dir;
            $valid_roots[$theme_root_real] = true;
        }

        if (empty($valid_sources)) {
            $this->redirect_with_status('no_valid_themes');
        }

        if (!empty($export_name)) {
            $archive_base = $export_name;
        } elseif (count($valid_sources) === 1) {
            $keys = array_keys($valid_sources);
            $archive_base = reset($keys);
        } else {
            $archive_base = 'aira-theme-bundle-' . gmdate('Y-m-d-His');
        }

        $archive_base = sanitize_file_name($archive_base);
        if ($archive_base === '') {
            $archive_base = 'aira-theme-export-' . gmdate('Y-m-d-His');
        }

        $zip_file = $this->get_temp_zip_path($archive_base);

        if (!$zip_file) {
            $this->redirect_with_status('temp_failed');
        }

        $created = false;
        if (class_exists('ZipArchive')) {
            $created = $this->create_zip_with_ziparchive($zip_file, $valid_sources);
        }

        if (!$created) {
            if (count($valid_roots) !== 1) {
                @unlink($zip_file);
                $this->redirect_with_status('theme_multi_root_zip_failed');
            }
            $root_keys = array_keys($valid_roots);
            $created = $this->create_zip_with_pclzip($zip_file, array_values($valid_sources), reset($root_keys));
        }

        if (!$created || !file_exists($zip_file) || filesize($zip_file) < 1) {
            @unlink($zip_file);
            $this->redirect_with_status('zip_failed');
        }

        $this->send_zip_download($zip_file, sanitize_file_name($archive_base . '.zip'));
    }

    private function redirect_with_status($status) {
        wp_safe_redirect(add_query_arg('aira_export_status', sanitize_key($status), admin_url('tools.php?page=aira-plugin-export')));
        exit;
    }

    private function get_temp_zip_path($archive_base) {
        $temp_dir = get_temp_dir();
        if (!$temp_dir || !is_dir($temp_dir) || !is_writable($temp_dir)) {
            return false;
        }

        $filename = wp_unique_filename($temp_dir, sanitize_file_name($archive_base . '.zip'));
        if (!$filename) {
            return false;
        }

        return trailingslashit($temp_dir) . $filename;
    }

    private function send_zip_download($zip_file, $download_name) {
        if (headers_sent()) {
            @unlink($zip_file);
            $this->redirect_with_status('headers_sent');
        }

        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        nocache_headers();
        header('Content-Type: application/zip');
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename="' . $download_name . '"');
        header('Content-Length: ' . filesize($zip_file));
        header('Content-Transfer-Encoding: binary');
        header('Pragma: public');
        header('Expires: 0');
        header('X-Content-Type-Options: nosniff');

        readfile($zip_file);
        @unlink($zip_file);
        exit;
    }

    private function sanitize_plugin_slug($slug) {
        $slug = sanitize_text_field($slug);
        $slug = str_replace(array('..', '/', '\\'), '', $slug);
        return sanitize_file_name($slug);
    }

    private function has_zip_engine() {
        if (class_exists('ZipArchive')) {
            return true;
        }
        if (class_exists('PclZip')) {
            return true;
        }
        if (file_exists(ABSPATH . 'wp-admin/includes/class-pclzip.php')) {
            return true;
        }
        return false;
    }

    private function get_zip_engine_label() {
        if (class_exists('ZipArchive')) {
            return __('ZipArchive available', 'aira-suite-export-plugin');
        }
        if (class_exists('PclZip') || file_exists(ABSPATH . 'wp-admin/includes/class-pclzip.php')) {
            return __('PclZip fallback available', 'aira-suite-export-plugin');
        }
        return __('No ZIP engine found', 'aira-suite-export-plugin');
    }

    private function create_zip_with_ziparchive($zip_file, $valid_sources) {
        $zip = new ZipArchive();
        if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return false;
        }

        foreach ($valid_sources as $source_dir) {
            $this->add_directory_to_zip($source_dir, $zip, basename($source_dir));
        }

        $zip->close();
        return file_exists($zip_file) && filesize($zip_file) > 0;
    }

    private function create_zip_with_pclzip($zip_file, $source_dirs, $plugins_real) {
        if (!class_exists('PclZip')) {
            $pclzip_file = ABSPATH . 'wp-admin/includes/class-pclzip.php';
            if (file_exists($pclzip_file)) {
                require_once $pclzip_file;
            }
        }

        if (!class_exists('PclZip')) {
            return false;
        }

        $file_list = array();
        foreach ($source_dirs as $source_dir) {
            $this->collect_files_for_pclzip($source_dir, $file_list);
        }

        if (empty($file_list)) {
            return false;
        }

        $archive = new PclZip($zip_file);
        $result = $archive->create(
            $file_list,
            PCLZIP_OPT_REMOVE_PATH,
            wp_normalize_path($plugins_real)
        );

        return is_array($result) && !empty($result) && file_exists($zip_file) && filesize($zip_file) > 0;
    }

    private function collect_files_for_pclzip($directory, &$file_list) {
        if (is_link($directory) || !is_dir($directory)) {
            return;
        }

        $items = scandir($directory);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $full_path = $directory . DIRECTORY_SEPARATOR . $item;
            if (is_link($full_path)) {
                continue;
            }

            if (is_dir($full_path)) {
                $this->collect_files_for_pclzip($full_path, $file_list);
            } elseif (is_file($full_path) && is_readable($full_path)) {
                $file_list[] = $full_path;
            }
        }
    }

    private function add_directory_to_zip($directory, $zip, $internal_path) {
        if (is_link($directory) || !is_dir($directory)) {
            return;
        }

        $directory = wp_normalize_path($directory);
        $internal_path = trim(wp_normalize_path($internal_path), '/');

        $zip->addEmptyDir($internal_path);

        $items = scandir($directory);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $full_path = wp_normalize_path($directory . '/' . $item);
            $zip_path = $internal_path . '/' . $item;

            if (is_link($full_path)) {
                continue;
            }

            if (is_dir($full_path)) {
                $this->add_directory_to_zip($full_path, $zip, $zip_path);
            } elseif (is_file($full_path) && is_readable($full_path)) {
                $zip->addFile($full_path, $zip_path);
            }
        }
    }

    private function is_path_inside($path, $base) {
        $path = wp_normalize_path($path);
        $base = untrailingslashit(wp_normalize_path($base));
        return $path === $base || strpos($path, trailingslashit($base)) === 0;
    }
}
