<?php
if (!defined('ABSPATH')) {
    exit;
}

class AiRA_Export_Builder {

    const HISTORY_OPTION = 'aira_export_builder_history';
    const PAGE_SLUG = 'aira-export-builder';

    public function init() {
        add_action('admin_menu', array($this, 'register_menu'));
        add_action('admin_post_aira_builder_export_package', array($this, 'handle_builder_export'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('rest_api_init', array($this, 'register_rest_route'));
    }

    public function register_menu() {
        add_management_page(
            __('AiRA Export Builder', 'aira-suite-export-plugin'),
            __('AiRA Export Builder', 'aira-suite-export-plugin'),
            'manage_options',
            self::PAGE_SLUG,
            array($this, 'render_page')
        );
    }

    public function enqueue_assets($hook) {
        if ($hook !== 'tools_page_' . self::PAGE_SLUG) {
            return;
        }

        wp_register_style('aira-export-builder-inline', false, array(), AIRA_EXPORT_PLUGIN_VERSION);
        wp_enqueue_style('aira-export-builder-inline');
        wp_add_inline_style('aira-export-builder-inline', $this->get_css());

        wp_register_script('aira-export-builder-inline', false, array(), AIRA_EXPORT_PLUGIN_VERSION, true);
        wp_enqueue_script('aira-export-builder-inline');
        wp_add_inline_script('aira-export-builder-inline', $this->get_js());
    }

    private function get_css() {
        return '
            .aira-builder-wrap,.aira-builder-wrap *{box-sizing:border-box}.aira-builder-wrap{max-width:1180px;width:100%;font-size:14px;line-height:1.55;color:#111111}.aira-builder-head{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;flex-wrap:wrap;margin:8px 0 16px}.aira-builder-head h1{margin:0 0 6px;font-size:28px;line-height:1.2;font-weight:800;color:#111111}.aira-builder-muted{color:#50575e}.aira-builder-card{background:#fff;border:1px solid #dcdcde;border-radius:18px;padding:18px;margin:14px 0;box-shadow:0 8px 24px rgba(17,17,17,.06)}.aira-builder-topbar{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;border-bottom:1px solid #eef0f2;padding-bottom:12px;margin-bottom:14px}.aira-builder-title strong{display:block;font-size:15px}.aira-builder-badge{display:inline-flex;align-items:center;gap:6px;padding:6px 11px;border-radius:999px;border:1px solid #cfe8d9;background:#edf7ed;color:#1E6B45;font-size:12px;font-weight:800}.aira-builder-grid{display:grid;grid-template-columns:repeat(12,1fr);gap:14px}.aira-builder-col-8{grid-column:span 8}.aira-builder-col-4{grid-column:span 4}.aira-builder-panel{border:1px solid #e5e7eb;border-radius:16px;background:#f8fafc;padding:14px}.aira-builder-panel h2{margin:0 0 8px;font-size:16px;color:#111111}.aira-builder-list{border:1px solid #dcdcde;background:#fff;border-radius:14px;max-height:330px;overflow:auto;padding:8px;-webkit-overflow-scrolling:touch}.aira-builder-item{display:flex;gap:10px;align-items:flex-start;padding:10px;border-bottom:1px solid #f0f0f1;border-radius:10px;cursor:pointer}.aira-builder-item:last-child{border-bottom:none}.aira-builder-item:hover{background:#f6faf7}.aira-builder-item input{min-width:18px;min-height:18px;margin-top:2px}.aira-builder-item span{word-break:break-word;overflow-wrap:anywhere}.aira-builder-actions{display:flex;gap:8px;flex-wrap:wrap;margin:10px 0}.aira-builder-actions .button{min-height:34px}.aira-builder-field{width:100%;max-width:460px;min-height:38px}.aira-builder-radio-row{display:flex;gap:10px;flex-wrap:wrap;margin-top:8px}.aira-builder-radio{display:flex;gap:8px;align-items:flex-start;border:1px solid #e5e7eb;background:#fff;border-radius:14px;padding:10px;flex:1 1 220px}.aira-builder-summary{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.aira-builder-kpi{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:12px}.aira-builder-kpi strong{display:block;font-size:22px;line-height:1;color:#111111}.aira-builder-kpi span{display:block;margin-top:4px;color:#50575e;font-size:12px}.aira-builder-submit{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-top:14px}.aira-builder-submit .button-primary{min-height:42px;padding-left:18px;padding-right:18px;background:#1E6B45;border-color:#1E6B45}.aira-builder-notice{padding:12px 14px;border-radius:12px;border-left:4px solid #1E6B45;background:#f0fdf4;margin:12px 0}.aira-builder-notice.is-error{border-left-color:#d63638;background:#fff5f5}.aira-builder-note{border-left:4px solid #F97316;background:#fff7ed;border-radius:12px;padding:12px;margin-top:12px}.aira-builder-history{width:100%;border-collapse:collapse;min-width:680px}.aira-builder-history th,.aira-builder-history td{text-align:left;vertical-align:top;border-bottom:1px solid #edf0f2;padding:9px 10px}.aira-builder-history th{background:#f8fafc}.aira-builder-table-wrap{overflow:auto;border:1px solid #e5e7eb;border-radius:14px;background:#fff}.aira-builder-code{font-family:Consolas,Monaco,monospace;background:#f6f7f7;border-radius:6px;padding:2px 6px}.aira-builder-footer{margin-top:12px;padding-top:12px;border-top:1px solid #edf0f2;color:#50575e;font-size:12px}@media(max-width:900px){.aira-builder-col-8,.aira-builder-col-4{grid-column:span 12}.aira-builder-summary{grid-template-columns:1fr}}@media(max-width:600px){.aira-builder-wrap{margin-right:8px}.aira-builder-card{padding:14px;border-radius:14px}.aira-builder-head h1{font-size:23px}.aira-builder-radio-row{display:block}.aira-builder-radio{margin-bottom:8px}.aira-builder-actions .button,.aira-builder-submit .button{width:100%;justify-content:center}.aira-builder-list{max-height:260px}}
        ';
    }

    private function get_js() {
        return <<<'JS'
        (function(){
            var form=document.querySelector('[data-aira-builder-form]');
            if(!form){return;}
            function qs(sel,ctx){return (ctx||document).querySelector(sel);}
            function qsa(sel,ctx){return Array.prototype.slice.call((ctx||document).querySelectorAll(sel));}
            function checked(name){return qsa('input[name="'+name+'"]:checked', form).length;}
            function update(){
                var plugins=checked('builder_plugins[]');
                var themes=checked('builder_themes[]');
                var total=plugins+themes;
                var pluginEl=qs('[data-aira-builder-count="plugins"]');
                var themeEl=qs('[data-aira-builder-count="themes"]');
                var totalEl=qs('[data-aira-builder-count="total"]');
                var noteEl=qs('[data-aira-builder-preview-note]');
                if(pluginEl){pluginEl.textContent=plugins;}
                if(themeEl){themeEl.textContent=themes;}
                if(totalEl){totalEl.textContent=total;}
                if(noteEl){noteEl.textContent=total>0?'พร้อมสร้างแพ็ก ZIP จากรายการที่เลือก':'ยังไม่ได้เลือกปลั๊กอินหรือธีม';}
            }
            qsa('[data-aira-select]').forEach(function(btn){
                btn.addEventListener('click',function(e){
                    e.preventDefault();
                    var target=btn.getAttribute('data-aira-select');
                    var mode=btn.getAttribute('data-aira-mode')||'all';
                    var selector='input[data-aira-group="'+target+'"]';
                    qsa(selector,form).forEach(function(input){
                        if(mode==='clear'){input.checked=false;}
                        else if(mode==='active'){input.checked=input.getAttribute('data-aira-active')==='1';}
                        else if(mode==='active-theme'){input.checked=input.getAttribute('data-aira-active-theme')==='1'||input.getAttribute('data-aira-parent-theme')==='1';}
                        else{input.checked=true;}
                    });
                    update();
                });
            });
            qsa('input[type="checkbox"]',form).forEach(function(input){input.addEventListener('change',update);});
            form.addEventListener('submit',function(e){
                update();
                if((checked('builder_plugins[]')+checked('builder_themes[]'))<1){
                    e.preventDefault();
                    alert('ต้องเลือกปลั๊กอินหรือธีมอย่างน้อย 1 รายการก่อน Export');
                }
            });
            update();
        })();
JS;
    }

    public function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to view this page.', 'aira-suite-export-plugin'));
        }

        $plugins = $this->get_plugins_for_ui();
        $themes = $this->get_themes_for_ui();
        $history = $this->get_history();
        $status = isset($_GET['aira_builder_status']) ? sanitize_key(wp_unslash($_GET['aira_builder_status'])) : '';
        ?>
        <div class="wrap aira-builder-wrap">
            <div class="aira-builder-head">
                <div>
                    <h1><?php echo esc_html__('AiRA Export Builder', 'aira-suite-export-plugin'); ?></h1>
                    <p class="aira-builder-muted"><?php echo esc_html__('ต่อยอดระบบ Export ให้แพ็กปลั๊กอินและธีมรวมกันได้ พร้อม Preview, Manifest, Export Log และโหมดตัดไฟล์หนักแบบปลอดภัย', 'aira-suite-export-plugin'); ?></p>
                </div>
                <span class="aira-builder-badge"><?php echo esc_html__('Thinkb4do Package Bundle', 'aira-suite-export-plugin'); ?></span>
            </div>

            <?php $this->render_notice($status); ?>

            <div class="aira-builder-card">
                <div class="aira-builder-topbar">
                    <div class="aira-builder-title">
                        <strong><?php echo esc_html__('System Settings | ปิด', 'aira-suite-export-plugin'); ?></strong>
                        <span class="aira-builder-muted"><?php echo esc_html__('Settings | โดย Thinkb4do | ดูรายละเอียด', 'aira-suite-export-plugin'); ?></span>
                    </div>
                    <span class="aira-builder-badge"><?php echo esc_html($this->get_zip_engine_label()); ?></span>
                </div>

                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" data-aira-builder-form>
                    <?php wp_nonce_field('aira_builder_export_package_action', 'aira_builder_nonce'); ?>
                    <input type="hidden" name="action" value="aira_builder_export_package" />

                    <div class="aira-builder-grid">
                        <div class="aira-builder-col-8">
                            <div class="aira-builder-panel">
                                <h2><?php echo esc_html__('1) เลือก Plugin ที่จะใส่ในแพ็ก', 'aira-suite-export-plugin'); ?></h2>
                                <p class="aira-builder-muted"><?php echo esc_html__('เลือกปลั๊กอินจาก wp-content/plugins ระบบจะคัดลอกลงโฟลเดอร์ plugins ภายใน ZIP', 'aira-suite-export-plugin'); ?></p>
                                <div class="aira-builder-actions">
                                    <button type="button" class="button" data-aira-select="plugins" data-aira-mode="all"><?php echo esc_html__('เลือกทั้งหมด', 'aira-suite-export-plugin'); ?></button>
                                    <button type="button" class="button" data-aira-select="plugins" data-aira-mode="active"><?php echo esc_html__('เลือกเฉพาะที่เปิดอยู่', 'aira-suite-export-plugin'); ?></button>
                                    <button type="button" class="button" data-aira-select="plugins" data-aira-mode="clear"><?php echo esc_html__('ล้างที่เลือก', 'aira-suite-export-plugin'); ?></button>
                                </div>
                                <div class="aira-builder-list" role="group" aria-label="<?php echo esc_attr__('Installed plugins', 'aira-suite-export-plugin'); ?>">
                                    <?php if (empty($plugins)) : ?>
                                        <p class="aira-builder-muted"><?php echo esc_html__('ยังไม่พบปลั๊กอินที่อ่านได้', 'aira-suite-export-plugin'); ?></p>
                                    <?php else : ?>
                                        <?php foreach ($plugins as $plugin) : ?>
                                            <label class="aira-builder-item">
                                                <input type="checkbox" name="builder_plugins[]" value="<?php echo esc_attr($plugin['file']); ?>" data-aira-group="plugins" data-aira-active="<?php echo $plugin['active'] ? '1' : '0'; ?>" />
                                                <span>
                                                    <strong><?php echo esc_html($plugin['name']); ?></strong>
                                                    <br><small class="aira-builder-muted"><?php echo esc_html($plugin['file']); ?> | v<?php echo esc_html($plugin['version']); ?> | <?php echo esc_html($plugin['status_label']); ?></small>
                                                </span>
                                            </label>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="aira-builder-panel" style="margin-top:14px">
                                <h2><?php echo esc_html__('2) เลือก Theme ที่จะใส่ในแพ็ก', 'aira-suite-export-plugin'); ?></h2>
                                <p class="aira-builder-muted"><?php echo esc_html__('เลือกธีมจาก wp-content/themes หากเป็น Child Theme ให้เลือก Parent Theme ไปด้วยเพื่อให้ครบ', 'aira-suite-export-plugin'); ?></p>
                                <div class="aira-builder-actions">
                                    <button type="button" class="button" data-aira-select="themes" data-aira-mode="all"><?php echo esc_html__('เลือกทั้งหมด', 'aira-suite-export-plugin'); ?></button>
                                    <button type="button" class="button" data-aira-select="themes" data-aira-mode="active-theme"><?php echo esc_html__('เลือกธีมที่ใช้อยู่ + Parent', 'aira-suite-export-plugin'); ?></button>
                                    <button type="button" class="button" data-aira-select="themes" data-aira-mode="clear"><?php echo esc_html__('ล้างที่เลือก', 'aira-suite-export-plugin'); ?></button>
                                </div>
                                <div class="aira-builder-list" role="group" aria-label="<?php echo esc_attr__('Installed themes', 'aira-suite-export-plugin'); ?>">
                                    <?php if (empty($themes)) : ?>
                                        <p class="aira-builder-muted"><?php echo esc_html__('ยังไม่พบธีมที่อ่านได้', 'aira-suite-export-plugin'); ?></p>
                                    <?php else : ?>
                                        <?php foreach ($themes as $theme) : ?>
                                            <label class="aira-builder-item">
                                                <input type="checkbox" name="builder_themes[]" value="<?php echo esc_attr($theme['slug']); ?>" data-aira-group="themes" data-aira-active-theme="<?php echo $theme['active'] ? '1' : '0'; ?>" data-aira-parent-theme="<?php echo $theme['parent_of_active'] ? '1' : '0'; ?>" />
                                                <span>
                                                    <strong><?php echo esc_html($theme['name']); ?></strong>
                                                    <br><small class="aira-builder-muted"><?php echo esc_html($theme['slug']); ?> | v<?php echo esc_html($theme['version']); ?> | <?php echo esc_html($theme['status_label']); ?></small>
                                                </span>
                                            </label>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="aira-builder-col-4">
                            <div class="aira-builder-panel">
                                <h2><?php echo esc_html__('3) ตั้งค่าแพ็กก่อน Export', 'aira-suite-export-plugin'); ?></h2>
                                <p><label for="aira-builder-package-name"><strong><?php echo esc_html__('ชื่อไฟล์ ZIP', 'aira-suite-export-plugin'); ?></strong></label></p>
                                <input id="aira-builder-package-name" class="regular-text aira-builder-field" name="package_name" type="text" placeholder="thinkb4do-site-package" />
                                <p class="description"><?php echo esc_html__('ไม่ต้องใส่ .zip ระบบจะเติมให้เอง', 'aira-suite-export-plugin'); ?></p>

                                <p style="margin-top:14px"><strong><?php echo esc_html__('Export Mode', 'aira-suite-export-plugin'); ?></strong></p>
                                <div class="aira-builder-radio-row">
                                    <label class="aira-builder-radio">
                                        <input type="radio" name="package_mode" value="install_safe" checked />
                                        <span><strong><?php echo esc_html__('Install-safe', 'aira-suite-export-plugin'); ?></strong><br><small class="aira-builder-muted"><?php echo esc_html__('ตัด .git, node_modules, cache, backup, log และไฟล์ ZIP เก่า เพื่อลดหน่วง', 'aira-suite-export-plugin'); ?></small></span>
                                    </label>
                                    <label class="aira-builder-radio">
                                        <input type="radio" name="package_mode" value="full" />
                                        <span><strong><?php echo esc_html__('Full backup', 'aira-suite-export-plugin'); ?></strong><br><small class="aira-builder-muted"><?php echo esc_html__('เก็บไฟล์ให้ครบที่สุด ยกเว้น symlink และไฟล์ที่อ่านไม่ได้', 'aira-suite-export-plugin'); ?></small></span>
                                    </label>
                                </div>

                                <p style="margin-top:14px"><label><input type="checkbox" name="include_manifest" value="1" checked /> <?php echo esc_html__('ใส่ package-manifest.json', 'aira-suite-export-plugin'); ?></label></p>
                                <p><label><input type="checkbox" name="include_readme" value="1" checked /> <?php echo esc_html__('ใส่ README-AIRA-EXPORT.txt', 'aira-suite-export-plugin'); ?></label></p>
                            </div>

                            <div class="aira-builder-panel" style="margin-top:14px">
                                <h2><?php echo esc_html__('Live Preview', 'aira-suite-export-plugin'); ?></h2>
                                <div class="aira-builder-summary">
                                    <div class="aira-builder-kpi"><strong data-aira-builder-count="plugins">0</strong><span><?php echo esc_html__('Plugins', 'aira-suite-export-plugin'); ?></span></div>
                                    <div class="aira-builder-kpi"><strong data-aira-builder-count="themes">0</strong><span><?php echo esc_html__('Themes', 'aira-suite-export-plugin'); ?></span></div>
                                    <div class="aira-builder-kpi"><strong data-aira-builder-count="total">0</strong><span><?php echo esc_html__('รวมทั้งหมด', 'aira-suite-export-plugin'); ?></span></div>
                                    <div class="aira-builder-kpi"><strong><?php echo esc_html($this->has_zip_engine() ? 'พร้อม' : 'ไม่พร้อม'); ?></strong><span><?php echo esc_html__('ZIP Engine', 'aira-suite-export-plugin'); ?></span></div>
                                </div>
                                <p class="aira-builder-muted" data-aira-builder-preview-note><?php echo esc_html__('ยังไม่ได้เลือกปลั๊กอินหรือธีม', 'aira-suite-export-plugin'); ?></p>
                            </div>

                            <div class="aira-builder-submit">
                                <button type="submit" class="button button-primary"><?php echo esc_html__('Export Package Bundle ZIP', 'aira-suite-export-plugin'); ?></button>
                                <span class="aira-builder-muted"><?php echo esc_html__('ปุ่มนี้กดได้จริง เมื่อเลือกอย่างน้อย 1 รายการ', 'aira-suite-export-plugin'); ?></span>
                            </div>

                            <div class="aira-builder-note">
                                <strong><?php echo esc_html__('คำอธิบายก่อนสร้างแพ็ก', 'aira-suite-export-plugin'); ?></strong>
                                <p><?php echo esc_html__('หน้านี้ทำแพ็กรวมสำหรับ backup / ย้ายระบบ / ส่งให้ทีม dev ไม่ใช่ ZIP ติดตั้งเดี่ยวแบบ Plugin หรือ Theme ปกติ หากต้องการ ZIP ติดตั้งเดี่ยว ให้ใช้หน้า AiRA Export Center เดิม', 'aira-suite-export-plugin'); ?></p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="aira-builder-card">
                <h2><?php echo esc_html__('Export Log ล่าสุด', 'aira-suite-export-plugin'); ?></h2>
                <div class="aira-builder-table-wrap">
                    <table class="aira-builder-history">
                        <thead><tr><th><?php echo esc_html__('เวลา', 'aira-suite-export-plugin'); ?></th><th><?php echo esc_html__('ไฟล์', 'aira-suite-export-plugin'); ?></th><th><?php echo esc_html__('รายการ', 'aira-suite-export-plugin'); ?></th><th><?php echo esc_html__('ขนาด / สถานะ', 'aira-suite-export-plugin'); ?></th></tr></thead>
                        <tbody>
                        <?php if (empty($history)) : ?>
                            <tr><td colspan="4"><?php echo esc_html__('ยังไม่มีประวัติ Export จาก Builder', 'aira-suite-export-plugin'); ?></td></tr>
                        <?php else : ?>
                            <?php foreach ($history as $row) : ?>
                                <tr>
                                    <td><?php echo esc_html(isset($row['time']) ? $row['time'] : '-'); ?></td>
                                    <td><span class="aira-builder-code"><?php echo esc_html(isset($row['file']) ? $row['file'] : '-'); ?></span></td>
                                    <td><?php echo esc_html(isset($row['items']) ? $row['items'] : '-'); ?></td>
                                    <td><?php echo esc_html(isset($row['size']) ? $row['size'] : '-'); ?> | <?php echo esc_html(isset($row['status']) ? $row['status'] : 'done'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="aira-builder-footer"><?php echo esc_html__('ระบบนี้เพิ่มเป็น Phase 2: Combined Export Builder + Manifest + Log + Safe Exclude + Responsive Preview', 'aira-suite-export-plugin'); ?></div>
            </div>
        </div>
        <?php
    }

    private function render_notice($status) {
        if ($status === '') {
            return;
        }
        $messages = array(
            'invalid_nonce' => array('type' => 'error', 'text' => __('Security check failed. Please refresh and try again.', 'aira-suite-export-plugin')),
            'no_selection' => array('type' => 'error', 'text' => __('ต้องเลือกปลั๊กอินหรือธีมอย่างน้อย 1 รายการก่อน Export', 'aira-suite-export-plugin')),
            'no_zip_engine' => array('type' => 'error', 'text' => __('ไม่พบ ZIP engine: ต้องเปิด ZipArchive หรือมี PclZip ของ WordPress', 'aira-suite-export-plugin')),
            'staging_failed' => array('type' => 'error', 'text' => __('สร้างโฟลเดอร์ชั่วคราวไม่สำเร็จ อาจติดสิทธิ์ไฟล์ของโฮสต์', 'aira-suite-export-plugin')),
            'no_valid_items' => array('type' => 'error', 'text' => __('ไม่พบรายการที่ export ได้จริงจากสิ่งที่เลือก', 'aira-suite-export-plugin')),
            'zip_failed' => array('type' => 'error', 'text' => __('สร้าง ZIP ไม่สำเร็จ อาจเกิดจากสิทธิ์ไฟล์หรือขนาดไฟล์ใหญ่เกินไป', 'aira-suite-export-plugin')),
            'headers_sent' => array('type' => 'error', 'text' => __('เริ่มดาวน์โหลดไม่ได้ เพราะมี output ถูกส่งออกไปก่อน header', 'aira-suite-export-plugin')),
        );
        if (!isset($messages[$status])) {
            return;
        }
        $class = $messages[$status]['type'] === 'error' ? 'aira-builder-notice is-error' : 'aira-builder-notice';
        echo '<div class="' . esc_attr($class) . '">' . esc_html($messages[$status]['text']) . '</div>';
    }

    public function handle_builder_export() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to do this action.', 'aira-suite-export-plugin'));
        }

        if (!isset($_POST['aira_builder_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['aira_builder_nonce'])), 'aira_builder_export_package_action')) {
            $this->redirect_with_status('invalid_nonce');
        }

        if (!$this->has_zip_engine()) {
            $this->redirect_with_status('no_zip_engine');
        }

        $plugin_files = isset($_POST['builder_plugins']) ? (array) wp_unslash($_POST['builder_plugins']) : array();
        $theme_slugs = isset($_POST['builder_themes']) ? (array) wp_unslash($_POST['builder_themes']) : array();
        $plugin_files = array_values(array_unique(array_filter(array_map(array($this, 'sanitize_plugin_file'), $plugin_files))));
        $theme_slugs = array_values(array_unique(array_filter(array_map(array($this, 'sanitize_theme_slug'), $theme_slugs))));

        if (empty($plugin_files) && empty($theme_slugs)) {
            $this->redirect_with_status('no_selection');
        }

        $package_name = isset($_POST['package_name']) ? sanitize_file_name(wp_unslash($_POST['package_name'])) : '';
        if ($package_name === '') {
            $package_name = 'aira-site-package-' . gmdate('Y-m-d-His');
        }
        $package_name = sanitize_file_name(preg_replace('/\.zip$/i', '', $package_name));
        if ($package_name === '') {
            $package_name = 'aira-site-package-' . gmdate('Y-m-d-His');
        }

        $mode = isset($_POST['package_mode']) ? sanitize_key(wp_unslash($_POST['package_mode'])) : 'install_safe';
        if (!in_array($mode, array('install_safe', 'full'), true)) {
            $mode = 'install_safe';
        }
        $include_manifest = !empty($_POST['include_manifest']);
        $include_readme = !empty($_POST['include_readme']);

        $staging_dir = $this->make_staging_dir($package_name);
        if (!$staging_dir) {
            $this->redirect_with_status('staging_failed');
        }

        $stats = array(
            'files' => 0,
            'dirs' => 0,
            'bytes' => 0,
            'skipped' => 0,
            'skipped_examples' => array(),
            'plugins' => array(),
            'themes' => array(),
            'errors' => array(),
        );

        $this->copy_selected_plugins($plugin_files, trailingslashit($staging_dir) . 'plugins', $mode, $stats);
        $this->copy_selected_themes($theme_slugs, trailingslashit($staging_dir) . 'themes', $mode, $stats);

        if (empty($stats['plugins']) && empty($stats['themes'])) {
            $this->delete_tree($staging_dir);
            $this->redirect_with_status('no_valid_items');
        }

        if ($include_manifest) {
            $this->write_manifest($staging_dir, $package_name, $mode, $stats);
        }
        if ($include_readme) {
            $this->write_readme($staging_dir, $package_name, $mode, $stats);
        }

        $zip_file = $this->get_temp_zip_path($package_name);
        if (!$zip_file) {
            $this->delete_tree($staging_dir);
            $this->redirect_with_status('staging_failed');
        }

        $created = $this->create_zip_from_staging($zip_file, $staging_dir, $package_name);
        $this->delete_tree($staging_dir);

        if (!$created || !file_exists($zip_file) || filesize($zip_file) < 1) {
            @unlink($zip_file);
            $this->redirect_with_status('zip_failed');
        }

        $download_name = sanitize_file_name($package_name . '.zip');
        $this->add_history(array(
            'time' => current_time('mysql'),
            'file' => $download_name,
            'items' => count($stats['plugins']) . ' plugins / ' . count($stats['themes']) . ' themes / ' . intval($stats['files']) . ' files',
            'size' => $this->format_bytes(filesize($zip_file)),
            'status' => 'exported',
        ));

        $this->send_zip_download($zip_file, $download_name);
    }

    private function copy_selected_plugins($plugin_files, $target_dir, $mode, &$stats) {
        if (empty($plugin_files)) {
            return;
        }
        $plugins = $this->get_plugins_raw();
        $plugins_real = realpath(WP_PLUGIN_DIR);
        if (!$plugins_real) {
            return;
        }
        wp_mkdir_p($target_dir);

        foreach ($plugin_files as $plugin_file) {
            if (!isset($plugins[$plugin_file])) {
                continue;
            }
            $source_info = $this->resolve_plugin_source($plugin_file, $plugins_real);
            if (!$source_info) {
                continue;
            }
            $target = trailingslashit($target_dir) . $source_info['target_name'];
            $before_files = $stats['files'];
            $this->copy_path_to_staging($source_info['source'], $target, $mode, $stats);
            if ($stats['files'] > $before_files || is_file($target)) {
                $data = $plugins[$plugin_file];
                $stats['plugins'][] = array(
                    'name' => isset($data['Name']) ? wp_strip_all_tags($data['Name']) : $plugin_file,
                    'version' => isset($data['Version']) ? wp_strip_all_tags($data['Version']) : '',
                    'file' => $plugin_file,
                    'source' => $source_info['target_name'],
                    'active' => function_exists('is_plugin_active') ? is_plugin_active($plugin_file) : false,
                );
            }
        }
    }

    private function copy_selected_themes($theme_slugs, $target_dir, $mode, &$stats) {
        if (empty($theme_slugs)) {
            return;
        }
        $themes = wp_get_themes(array('errors' => null));
        if (empty($themes) || !is_array($themes)) {
            return;
        }
        wp_mkdir_p($target_dir);

        foreach ($theme_slugs as $slug) {
            if (!isset($themes[$slug]) || !is_object($themes[$slug])) {
                continue;
            }
            $theme = $themes[$slug];
            $theme_root = method_exists($theme, 'get_theme_root') ? $theme->get_theme_root() : get_theme_root($slug);
            $root_real = realpath($theme_root);
            $source = realpath($theme_root . DIRECTORY_SEPARATOR . $slug);
            if (!$root_real || !$source || !$this->is_path_inside($source, $root_real) || !is_dir($source)) {
                continue;
            }
            $target = trailingslashit($target_dir) . $slug;
            $before_files = $stats['files'];
            $this->copy_path_to_staging($source, $target, $mode, $stats);
            if ($stats['files'] > $before_files) {
                $stats['themes'][] = array(
                    'name' => wp_strip_all_tags($theme->get('Name')),
                    'version' => wp_strip_all_tags($theme->get('Version')),
                    'stylesheet' => $slug,
                    'template' => method_exists($theme, 'get_template') ? $theme->get_template() : '',
                    'source' => $slug,
                    'active' => get_stylesheet() === $slug,
                );
            }
        }
    }

    private function resolve_plugin_source($plugin_file, $plugins_real) {
        $plugin_path = realpath(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $plugin_file);
        if (!$plugin_path || !$this->is_path_inside($plugin_path, $plugins_real) || !is_file($plugin_path)) {
            return false;
        }

        $parts = explode('/', str_replace('\\', '/', $plugin_file));
        if (count($parts) > 1) {
            $root_name = sanitize_file_name($parts[0]);
            $source = realpath(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $root_name);
            if (!$source || !$this->is_path_inside($source, $plugins_real) || !is_dir($source)) {
                return false;
            }
            return array('source' => $source, 'target_name' => $root_name);
        }

        return array('source' => $plugin_path, 'target_name' => basename($plugin_file));
    }

    private function copy_path_to_staging($source, $target, $mode, &$stats) {
        if (is_link($source) || !file_exists($source)) {
            $stats['skipped']++;
            $this->remember_skipped($stats, basename($source));
            return;
        }

        if (is_file($source)) {
            if (!$this->is_readable_file($source) || $this->should_exclude($source, basename($source), $mode)) {
                $stats['skipped']++;
                $this->remember_skipped($stats, basename($source));
                return;
            }
            wp_mkdir_p(dirname($target));
            if (@copy($source, $target)) {
                $stats['files']++;
                $stats['bytes'] += (int) @filesize($source);
            } else {
                $stats['errors'][] = basename($source);
            }
            return;
        }

        if (!is_dir($source) || $this->should_exclude($source, basename($source), $mode)) {
            $stats['skipped']++;
            $this->remember_skipped($stats, basename($source));
            return;
        }

        wp_mkdir_p($target);
        $stats['dirs']++;
        $items = @scandir($source);
        if ($items === false) {
            $stats['skipped']++;
            $this->remember_skipped($stats, basename($source));
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $this->copy_path_to_staging($source . DIRECTORY_SEPARATOR . $item, $target . DIRECTORY_SEPARATOR . $item, $mode, $stats);
        }
    }

    private function should_exclude($path, $name, $mode) {
        if ($mode === 'full') {
            return false;
        }
        $lower = strtolower((string) $name);
        $exact = array('.git', '.svn', '.hg', '.cache', 'cache', 'caches', 'node_modules', 'backups', 'backup', 'tmp', 'temp', '__macosx', '.ds_store', 'thumbs.db');
        if (in_array($lower, $exact, true)) {
            return true;
        }
        if (preg_match('/\.(zip|tar|gz|rar|7z|log|tmp|bak)$/i', $lower)) {
            return true;
        }
        return false;
    }

    private function is_readable_file($path) {
        return is_file($path) && is_readable($path);
    }

    private function remember_skipped(&$stats, $name) {
        if (count($stats['skipped_examples']) < 20) {
            $stats['skipped_examples'][] = sanitize_text_field((string) $name);
        }
    }

    private function write_manifest($staging_dir, $package_name, $mode, $stats) {
        $manifest = array(
            'name' => $package_name,
            'generated_by' => 'AiRA Export Builder',
            'plugin_version' => AIRA_EXPORT_PLUGIN_VERSION,
            'created_at' => current_time('mysql'),
            'site' => home_url('/'),
            'mode' => $mode,
            'structure' => array('plugins' => 'plugins/', 'themes' => 'themes/'),
            'stats' => array(
                'files' => intval($stats['files']),
                'directories' => intval($stats['dirs']),
                'bytes' => intval($stats['bytes']),
                'size_readable' => $this->format_bytes($stats['bytes']),
                'skipped' => intval($stats['skipped']),
                'skipped_examples' => $stats['skipped_examples'],
            ),
            'plugins' => $stats['plugins'],
            'themes' => $stats['themes'],
            'notes' => array(
                'This is a Thinkb4do package bundle for backup, transfer, and developer handoff.',
                'For direct WordPress install ZIP files, use the original AiRA Export Center page.',
                'No database, uploads, users, passwords, tokens, or API keys are included by this builder.',
            ),
        );
        file_put_contents(trailingslashit($staging_dir) . 'package-manifest.json', wp_json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function write_readme($staging_dir, $package_name, $mode, $stats) {
        $lines = array();
        $lines[] = 'AiRA Export Builder Package';
        $lines[] = 'Package: ' . $package_name;
        $lines[] = 'Created: ' . current_time('mysql');
        $lines[] = 'Mode: ' . $mode;
        $lines[] = '';
        $lines[] = 'How to use:';
        $lines[] = '1. Unzip this package on your computer.';
        $lines[] = '2. Upload folders inside /plugins to wp-content/plugins if needed.';
        $lines[] = '3. Upload folders inside /themes to wp-content/themes if needed.';
        $lines[] = '4. Activate plugins/themes from WordPress admin after checking compatibility.';
        $lines[] = '';
        $lines[] = 'Important:';
        $lines[] = '- This bundle does not include database data, media uploads, users, passwords, API keys, or secrets.';
        $lines[] = '- For normal single install ZIP files, use Tools > AiRA Export Center.';
        $lines[] = '- If a Child Theme is included, make sure its Parent Theme is included or already installed.';
        $lines[] = '';
        $lines[] = 'Export summary:';
        $lines[] = '- Plugins: ' . count($stats['plugins']);
        $lines[] = '- Themes: ' . count($stats['themes']);
        $lines[] = '- Files: ' . intval($stats['files']);
        $lines[] = '- Size before ZIP: ' . $this->format_bytes($stats['bytes']);
        $lines[] = '- Skipped items: ' . intval($stats['skipped']);
        file_put_contents(trailingslashit($staging_dir) . 'README-AIRA-EXPORT.txt', implode("\n", $lines));
    }

    private function create_zip_from_staging($zip_file, $staging_dir, $package_name) {
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                return false;
            }
            $this->add_directory_to_zip($staging_dir, $zip, $package_name);
            $zip->close();
            return file_exists($zip_file) && filesize($zip_file) > 0;
        }

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
        $this->collect_files($staging_dir, $file_list);
        if (empty($file_list)) {
            return false;
        }
        $archive = new PclZip($zip_file);
        $result = $archive->create($file_list, PCLZIP_OPT_REMOVE_PATH, wp_normalize_path($staging_dir), PCLZIP_OPT_ADD_PATH, $package_name);
        return is_array($result) && !empty($result) && file_exists($zip_file) && filesize($zip_file) > 0;
    }

    private function add_directory_to_zip($directory, $zip, $internal_path) {
        if (is_link($directory) || !is_dir($directory)) {
            return;
        }
        $directory = wp_normalize_path($directory);
        $internal_path = trim(wp_normalize_path($internal_path), '/');
        $zip->addEmptyDir($internal_path);
        $items = @scandir($directory);
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

    private function collect_files($directory, &$files) {
        if (is_link($directory) || !is_dir($directory)) {
            return;
        }
        $items = @scandir($directory);
        if ($items === false) {
            return;
        }
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $directory . DIRECTORY_SEPARATOR . $item;
            if (is_link($path)) {
                continue;
            }
            if (is_dir($path)) {
                $this->collect_files($path, $files);
            } elseif (is_file($path) && is_readable($path)) {
                $files[] = $path;
            }
        }
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

    private function make_staging_dir($package_name) {
        $base = trailingslashit(get_temp_dir()) . 'aira-export-builder-' . wp_generate_password(8, false, false) . '-' . sanitize_file_name($package_name);
        if (!wp_mkdir_p($base)) {
            return false;
        }
        return $base;
    }

    private function get_temp_zip_path($package_name) {
        $temp_dir = get_temp_dir();
        if (!$temp_dir || !is_dir($temp_dir) || !is_writable($temp_dir)) {
            return false;
        }
        $filename = wp_unique_filename($temp_dir, sanitize_file_name($package_name . '.zip'));
        if (!$filename) {
            return false;
        }
        return trailingslashit($temp_dir) . $filename;
    }

    private function delete_tree($path) {
        if (empty($path) || !file_exists($path)) {
            return;
        }
        if (is_file($path) || is_link($path)) {
            @unlink($path);
            return;
        }
        $items = @scandir($path);
        if ($items !== false) {
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }
                $this->delete_tree($path . DIRECTORY_SEPARATOR . $item);
            }
        }
        @rmdir($path);
    }

    private function get_plugins_raw() {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        return function_exists('get_plugins') ? get_plugins() : array();
    }

    private function get_plugins_for_ui() {
        $plugins = $this->get_plugins_raw();
        $rows = array();
        foreach ($plugins as $file => $data) {
            $active = function_exists('is_plugin_active') ? is_plugin_active($file) : false;
            $network = function_exists('is_plugin_active_for_network') ? is_plugin_active_for_network($file) : false;
            $rows[] = array(
                'file' => $file,
                'name' => !empty($data['Name']) ? wp_strip_all_tags($data['Name']) : $file,
                'version' => !empty($data['Version']) ? wp_strip_all_tags($data['Version']) : '-',
                'active' => $active || $network,
                'status_label' => $network ? __('Network active', 'aira-suite-export-plugin') : ($active ? __('Active', 'aira-suite-export-plugin') : __('Inactive', 'aira-suite-export-plugin')),
            );
        }
        usort($rows, function($a, $b) { return strcasecmp($a['name'], $b['name']); });
        return $rows;
    }

    private function get_themes_for_ui() {
        $themes = wp_get_themes(array('errors' => null));
        $rows = array();
        $active_stylesheet = get_stylesheet();
        $active_template = get_template();
        foreach ($themes as $slug => $theme) {
            if (!is_object($theme)) {
                continue;
            }
            $active = ($slug === $active_stylesheet);
            $parent_of_active = ($slug === $active_template && $active_template !== $active_stylesheet);
            $status = $active ? __('Active theme', 'aira-suite-export-plugin') : ($parent_of_active ? __('Parent of active theme', 'aira-suite-export-plugin') : __('Installed', 'aira-suite-export-plugin'));
            $rows[] = array(
                'slug' => $slug,
                'name' => wp_strip_all_tags($theme->get('Name')),
                'version' => wp_strip_all_tags($theme->get('Version')),
                'active' => $active,
                'parent_of_active' => $parent_of_active,
                'status_label' => $status,
            );
        }
        usort($rows, function($a, $b) { return strcasecmp($a['name'], $b['name']); });
        return $rows;
    }

    private function sanitize_plugin_file($file) {
        $file = str_replace('\\', '/', sanitize_text_field((string) $file));
        $file = ltrim($file, '/');
        $file = str_replace('..', '', $file);
        if (function_exists('validate_file') && validate_file($file) !== 0) {
            return '';
        }
        if (!preg_match('/^[A-Za-z0-9_\.\-\/]+\.php$/', $file)) {
            return '';
        }
        return $file;
    }

    private function sanitize_theme_slug($slug) {
        $slug = sanitize_text_field((string) $slug);
        $slug = str_replace(array('..', '/', '\\'), '', $slug);
        return sanitize_file_name($slug);
    }

    private function has_zip_engine() {
        if (class_exists('ZipArchive') || class_exists('PclZip')) {
            return true;
        }
        return file_exists(ABSPATH . 'wp-admin/includes/class-pclzip.php');
    }

    private function get_zip_engine_label() {
        if (class_exists('ZipArchive')) {
            return __('ZIP Engine: ZipArchive พร้อมใช้งาน', 'aira-suite-export-plugin');
        }
        if (class_exists('PclZip') || file_exists(ABSPATH . 'wp-admin/includes/class-pclzip.php')) {
            return __('ZIP Engine: PclZip fallback พร้อมใช้งาน', 'aira-suite-export-plugin');
        }
        return __('ZIP Engine: ยังไม่พร้อม', 'aira-suite-export-plugin');
    }

    private function add_history($entry) {
        $history = get_option(self::HISTORY_OPTION, array());
        if (!is_array($history)) {
            $history = array();
        }
        array_unshift($history, $entry);
        $history = array_slice($history, 0, 20);
        update_option(self::HISTORY_OPTION, $history, false);
    }

    private function get_history() {
        $history = get_option(self::HISTORY_OPTION, array());
        return is_array($history) ? $history : array();
    }

    private function format_bytes($bytes) {
        $bytes = max(0, (float) $bytes);
        $units = array('B', 'KB', 'MB', 'GB');
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, $i === 0 ? 0 : 2) . ' ' . $units[$i];
    }

    private function redirect_with_status($status) {
        wp_safe_redirect(add_query_arg('aira_builder_status', sanitize_key($status), admin_url('tools.php?page=' . self::PAGE_SLUG)));
        exit;
    }

    private function is_path_inside($path, $base) {
        $path = wp_normalize_path($path);
        $base = untrailingslashit(wp_normalize_path($base));
        return $path === $base || strpos($path, trailingslashit($base)) === 0;
    }

    public function register_rest_route() {
        register_rest_route('aira-suite-export/v1', '/builder-status', array(
            'methods' => 'GET',
            'permission_callback' => function() { return current_user_can('manage_options'); },
            'callback' => function() {
                return rest_ensure_response(array(
                    'ok' => true,
                    'version' => AIRA_EXPORT_PLUGIN_VERSION,
                    'zip_engine' => $this->get_zip_engine_label(),
                    'history_count' => count($this->get_history()),
                    'page' => admin_url('tools.php?page=' . self::PAGE_SLUG),
                    'safe_mode' => true,
                ));
            },
        ));
    }
}
