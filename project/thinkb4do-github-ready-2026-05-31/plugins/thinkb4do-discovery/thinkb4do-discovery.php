<?php
/**
 * Plugin Name: Thinkb4do Discovery
 * Plugin URI: https://thinkb4do.com/
 * Description: Minimal development view with smooth public-safe background for Thinkb4do.
 * Version: 1.4.2
 * Author: Thinkb4do
 * Text Domain: thinkb4do-discovery
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

defined( 'ABSPATH' ) || exit;

define( 'TB4_DISCOVERY_PLUGIN_VERSION', '1.4.2' );
define( 'TB4_DISCOVERY_PLUGIN_FILE', __FILE__ );
define( 'TB4_DISCOVERY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'TB4_DISCOVERY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

final class TB4_Discovery_Plugin {
    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'init', [ $this, 'register_shortcodes' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'admin_menu', [ $this, 'register_admin_page' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'plugin_action_links' ] );
        add_filter( 'body_class', [ $this, 'body_class' ] );
        add_filter( 'tb4_minimal_header_links', [ $this, 'filter_header_links' ], 50 );
        add_filter( 'tb4_mobile_nav_items', [ $this, 'filter_mobile_nav_items' ], 50 );
        add_filter( 'tb4_app_grid_groups', [ $this, 'filter_app_grid_groups' ], 50 );
    }

    public static function activate() {
        self::maybe_create_discovery_page();
        flush_rewrite_rules();
    }

    public static function deactivate() {
        flush_rewrite_rules();
    }

    private static function maybe_create_discovery_page() {
        $existing = get_page_by_path( 'discovery' );
        if ( $existing instanceof WP_Post ) {
            update_option( 'tb4_discovery_page_id', absint( $existing->ID ) );
            return;
        }

        $page_id = wp_insert_post( [
            'post_title'   => 'Discovery',
            'post_name'    => 'discovery',
            'post_type'    => 'page',
            'post_status'  => 'publish',
            'post_content' => '[thinkb4do_discovery]',
            'meta_input'   => [ '_tb4_discovery_page' => '1' ],
        ] );

        if ( ! is_wp_error( $page_id ) && $page_id ) {
            update_option( 'tb4_discovery_page_id', absint( $page_id ) );
        }
    }

    public function register_shortcodes() {
        add_shortcode( 'thinkb4do_discovery', [ $this, 'render_discovery_shortcode' ] );
        add_shortcode( 'thinkb4do_home_discovery_feed', [ $this, 'render_discovery_shortcode' ] );
    }

    public function enqueue_assets() {
        if ( is_admin() ) {
            return;
        }

        if ( $this->is_discovery_context() || is_front_page() ) {
            wp_enqueue_style( 'tb4-discovery', TB4_DISCOVERY_PLUGIN_URL . 'assets/css/discovery.css', [], TB4_DISCOVERY_PLUGIN_VERSION );
            wp_enqueue_script( 'tb4-discovery', TB4_DISCOVERY_PLUGIN_URL . 'assets/js/discovery.js', [], TB4_DISCOVERY_PLUGIN_VERSION, true );
        }
    }

    public function body_class( $classes ) {
        if ( $this->is_discovery_context() || is_front_page() ) {
            $classes[] = 'tb4-discovery-view-active';
            $classes[] = 'tb4-discovery-premium-shell';
            $classes[] = 'tb4-discovery-dev-ready';
            $classes[] = 'tb4-discovery-single-scroll';
        }
        return $classes;
    }

    private function is_discovery_context() {
        if ( is_page( 'discovery' ) ) {
            return true;
        }
        $page_id = absint( get_option( 'tb4_discovery_page_id', 0 ) );
        return $page_id && is_page( $page_id );
    }

    public function render_discovery_shortcode( $atts = [] ) {
        $atts = shortcode_atts( [
            'source' => 'shortcode',
        ], $atts, 'thinkb4do_discovery' );

        ob_start();
        ?>
        <main id="tb4-discovery" class="tb4-discovery tb4-discovery-dev-ui" data-tb4-ready="0" aria-label="Thinkb4do.com">
            <?php $this->render_hero(); ?>
        </main>
        <?php
        return trim( ob_get_clean() );
    }

    private function render_hero() {
        ?>
        <section class="tb4-dev-hero" aria-labelledby="tb4-dev-title">
            <div class="tb4-dev-bg" aria-hidden="true">
                <span class="tb4-dev-glow tb4-dev-glow-one"></span>
                <span class="tb4-dev-glow tb4-dev-glow-two"></span>
                <span class="tb4-dev-glow tb4-dev-glow-three"></span>
                <span class="tb4-dev-soft-line tb4-dev-soft-line-one"></span>
                <span class="tb4-dev-soft-line tb4-dev-soft-line-two"></span>
            </div>
            <div class="tb4-dev-copy">
                <h1 id="tb4-dev-title">กำลังพัฒนาเว็บ</h1>
                <p>Thinkb4do.com</p>
            </div>
        </section>
        <?php
    }

    private function menu_icon_svg( $name ) {
        $icons = [
            'spark'   => '<svg viewBox="0 0 24 24" role="img" focusable="false" aria-hidden="true"><path d="M12 3.75l1.55 4.7 4.7 1.55-4.7 1.55L12 16.25l-1.55-4.7-4.7-1.55 4.7-1.55L12 3.75z"/><path d="M18.5 15.25l.68 2.07 2.07.68-2.07.68-.68 2.07-.68-2.07-2.07-.68 2.07-.68.68-2.07z"/></svg>',
            'people'  => '<svg viewBox="0 0 24 24" role="img" focusable="false" aria-hidden="true"><path d="M8.9 11.1a3.55 3.55 0 1 0 0-7.1 3.55 3.55 0 0 0 0 7.1z"/><path d="M3.75 19.25c.48-3.42 2.28-5.12 5.15-5.12s4.67 1.7 5.15 5.12"/><path d="M15.4 11.15a2.8 2.8 0 1 0 0-5.6"/><path d="M15.9 14.1c2.3.33 3.74 1.98 4.1 5.15"/></svg>',
            'product' => '<svg viewBox="0 0 24 24" role="img" focusable="false" aria-hidden="true"><path d="M5 8.2l7-4 7 4-7 4-7-4z"/><path d="M5 8.2v7.6l7 4 7-4V8.2"/><path d="M12 12.2v7.6"/></svg>',
        ];

        return $icons[ $name ] ?? $icons['spark'];
    }

    private function render_left_rail() {
        $items = [
            [ 'label' => 'Discovery', 'url' => $this->discovery_url(), 'desc' => 'หน้าเริ่มต้น', 'icon' => 'spark' ],
            [ 'label' => 'ชุมชน', 'url' => home_url( '/community/' ), 'desc' => 'พูดคุยและติดตาม', 'icon' => 'people' ],
            [ 'label' => 'ผลิตภัณฑ์', 'url' => $this->products_url(), 'desc' => 'ดูตัวเลือก', 'icon' => 'product' ],
        ];
        ?>
        <div class="tb4-discovery-card tb4-discovery-menu-card">
            <h2>ทางลัด</h2>
            <nav aria-label="เมนู Discovery">
                <?php foreach ( $items as $item ) : ?>
                    <a href="<?php echo esc_url( $item['url'] ); ?>">
                        <span class="tb4-discovery-menu-icon" aria-hidden="true"><?php echo $this->menu_icon_svg( $item['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                        <span><?php echo esc_html( $item['label'] ); ?></span>
                        <small><?php echo esc_html( $item['desc'] ); ?></small>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
        <?php
    }

    private function render_right_rail() {
        ?>
        <div class="tb4-discovery-card tb4-discovery-status-card">
            <h2>กำลังพัฒนาเว็บ</h2>
            <div class="tb4-discovery-meter" aria-label="สถานะหน้าเว็บ"><span style="width:86%"></span></div>
            <ul>
                <li><strong>วันนี้</strong><span>กำลังปรับหน้าให้เรียบขึ้น</span></li>
                <li><strong>AI Tone</strong><span>กำลังจัดบรรยากาศให้ฉลาดขึ้น</span></li>
                <li><strong>ทุกหน้าจอ</strong><span>กำลังปรับให้ใช้งานง่ายขึ้น</span></li>
                <li><strong>เนื้อหา</strong><span>กำลังจัดวางให้ดูสบายตา</span></li>
            </ul>
            <p class="tb4-discovery-note">กำลังพัฒนาเว็บ</p>
        </div>
        <?php
    }

    private function render_composer_hint() {
        ?>
        <section id="tb4-discovery-feed-start" class="tb4-discovery-composer-hint" aria-label="เริ่มต้นใช้งาน">
            <div class="tb4-discovery-avatar" aria-hidden="true">T</div>
            <div>
                <strong>กำลังพัฒนาเว็บ</strong>
                <p>เรากำลังปรับประสบการณ์ให้ลื่นไหล เรียบ และดูเป็น AI มากขึ้น</p>
            </div>
            <a href="<?php echo esc_url( home_url( '/community/' ) ); ?>">ไปต่อ</a>
        </section>
        <?php
    }

    private function render_latest_posts( $limit ) {
        $query = new WP_Query( [
            'post_type'           => 'post',
            'post_status'         => 'publish',
            'posts_per_page'      => $limit,
            'ignore_sticky_posts' => true,
            'no_found_rows'       => true,
        ] );

        $this->render_query_section( 'เรื่องล่าสุด', 'กำลังจัดวางเนื้อหาให้อ่านง่ายขึ้น', $query, 'กำลังเตรียมเนื้อหา', home_url( '/blog/' ), 'อัปเดต' );
    }

    private function render_community_preview( $limit ) {
        $post_types = array_values( array_filter( [ 'tb4_community', 'tb4_post', 'community_post', 'community' ], 'post_type_exists' ) );
        if ( empty( $post_types ) ) {
            $this->render_static_section(
                'ชุมชน',
                'กำลังปรับพื้นที่พูดคุยให้น่าใช้งานขึ้น',
                [
                    [ 'title' => 'พื้นที่ชุมชน', 'desc' => 'กำลังปรับหน้าให้ใช้งานง่ายขึ้น', 'url' => home_url( '/community/' ) ],
                    [ 'title' => 'เรื่องเล่าและสื่อ', 'desc' => 'กำลังจัดวางให้ดูสบายตาขึ้น', 'url' => home_url( '/community/' ) ],
                ],
                home_url( '/community/' ),
                'ชุมชน'
            );
            return;
        }

        $query = new WP_Query( [
            'post_type'           => $post_types,
            'post_status'         => 'publish',
            'posts_per_page'      => $limit,
            'ignore_sticky_posts' => true,
            'no_found_rows'       => true,
        ] );

        $this->render_query_section( 'ชุมชน', 'กำลังปรับพื้นที่พูดคุยให้น่าใช้งานขึ้น', $query, 'กำลังเตรียมพื้นที่ชุมชน', home_url( '/community/' ), 'ชุมชน' );
    }

    private function render_products_preview( $limit ) {
        if ( ! post_type_exists( 'tb4_product' ) ) {
            $this->render_static_section(
                'ผลิตภัณฑ์',
                'กำลังจัดหน้าให้ดูง่ายและเลือกต่อได้สบายขึ้น',
                [
                    [ 'title' => 'Think Control', 'desc' => 'กำลังปรับข้อมูลให้ดูง่ายขึ้น', 'url' => home_url( '/products/think-control/' ) ],
                    [ 'title' => 'AiRA Studio', 'desc' => 'กำลังปรับประสบการณ์ให้ทันสมัยขึ้น', 'url' => home_url( '/products/aira-studio/' ) ],
                ],
                $this->products_url(),
                'ผลิตภัณฑ์'
            );
            return;
        }

        $query = new WP_Query( [
            'post_type'           => 'tb4_product',
            'post_status'         => 'publish',
            'posts_per_page'      => $limit,
            'ignore_sticky_posts' => true,
            'no_found_rows'       => true,
        ] );

        $this->render_query_section( 'ผลิตภัณฑ์', 'กำลังจัดหน้าให้ดูง่ายและเลือกต่อได้สบายขึ้น', $query, 'กำลังเตรียมข้อมูลผลิตภัณฑ์', $this->products_url(), 'ผลิตภัณฑ์' );
    }

    private function render_query_section( $title, $desc, WP_Query $query, $empty_text, $more_url, $card_label = 'แนะนำ' ) {
        ?>
        <section class="tb4-discovery-section">
            <header class="tb4-discovery-section-head">
                <div>
                    <span class="tb4-discovery-section-kicker"><?php echo esc_html( $card_label ); ?></span>
                    <h2><?php echo esc_html( $title ); ?></h2>
                    <p><?php echo esc_html( $desc ); ?></p>
                </div>
                <a href="<?php echo esc_url( $more_url ); ?>">ดูต่อ</a>
            </header>

            <?php if ( $query->have_posts() ) : ?>
                <div class="tb4-discovery-grid">
                    <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                        <?php $this->render_post_card( get_post(), $card_label ); ?>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            <?php else : ?>
                <div class="tb4-discovery-empty"><?php echo esc_html( $empty_text ); ?></div>
                <?php wp_reset_postdata(); ?>
            <?php endif; ?>
        </section>
        <?php
    }

    private function render_static_section( $title, $desc, array $items, $more_url, $card_label = 'แนะนำ' ) {
        ?>
        <section class="tb4-discovery-section">
            <header class="tb4-discovery-section-head">
                <div>
                    <span class="tb4-discovery-section-kicker"><?php echo esc_html( $card_label ); ?></span>
                    <h2><?php echo esc_html( $title ); ?></h2>
                    <p><?php echo esc_html( $desc ); ?></p>
                </div>
                <a href="<?php echo esc_url( $more_url ); ?>">ดูต่อ</a>
            </header>
            <div class="tb4-discovery-grid">
                <?php foreach ( $items as $item ) : ?>
                    <article class="tb4-discovery-item tb4-discovery-static-item">
                        <a href="<?php echo esc_url( $item['url'] ); ?>">
                            <span class="tb4-discovery-card-icon" aria-hidden="true">✦</span>
                            <span class="tb4-discovery-type"><?php echo esc_html( $card_label ); ?></span>
                            <h3><?php echo esc_html( $item['title'] ); ?></h3>
                            <p><?php echo esc_html( $item['desc'] ); ?></p>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
        <?php
    }

    private function render_post_card( WP_Post $post, $card_label = 'แนะนำ' ) {
        $permalink = get_permalink( $post );
        $title     = get_the_title( $post ) ?: 'ไม่มีชื่อ';
        $excerpt   = has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_trim_words( wp_strip_all_tags( $post->post_content ), 22 );
        ?>
        <article class="tb4-discovery-item">
            <a href="<?php echo esc_url( $permalink ); ?>">
                <?php if ( has_post_thumbnail( $post ) ) : ?>
                    <span class="tb4-discovery-thumb"><?php echo get_the_post_thumbnail( $post, 'medium_large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                <?php else : ?>
                    <span class="tb4-discovery-thumb tb4-discovery-thumb-placeholder" aria-hidden="true">Think</span>
                <?php endif; ?>
                <span class="tb4-discovery-type"><?php echo esc_html( $card_label ); ?></span>
                <h3><?php echo esc_html( $title ); ?></h3>
                <p><?php echo esc_html( $excerpt ); ?></p>
            </a>
        </article>
        <?php
    }

    private function discovery_url() {
        return home_url( '/' );
    }

    private function products_url() {
        if ( post_type_exists( 'tb4_product' ) ) {
            $archive = get_post_type_archive_link( 'tb4_product' );
            if ( $archive ) {
                return $archive;
            }
        }
        return home_url( '/products/' );
    }

    public function filter_header_links( $links ) {
        if ( ! is_array( $links ) ) {
            $links = [];
        }
        return [
            [ 'url' => $this->discovery_url(), 'label' => 'Discovery', 'match' => [ '/' ] ],
            [ 'url' => home_url( '/community/' ), 'label' => 'ชุมชน', 'match' => [ '/community/', '/thinkb4do-community/' ] ],
            [ 'url' => $this->products_url(), 'label' => 'ผลิตภัณฑ์', 'match' => [ '/products/', '/product-cat/' ] ],
        ];
    }

    public function filter_mobile_nav_items( $items ) {
        return [
            [ 'type' => 'link', 'slug' => 'home', 'label' => 'Discovery', 'url' => $this->discovery_url() ],
            [ 'type' => 'link', 'slug' => 'community', 'label' => 'ชุมชน', 'url' => home_url( '/community/' ) ],
            [ 'type' => 'link', 'slug' => 'products', 'label' => 'ผลิตภัณฑ์', 'url' => $this->products_url() ],
        ];
    }

    public function filter_app_grid_groups( $groups ) {
        if ( ! is_array( $groups ) ) {
            $groups = [];
        }
        $groups['menu'] = [
            'title' => 'เมนูหลัก',
            'items' => [
                [ $this->discovery_url(), 'Discovery', 'กำลังพัฒนาเว็บ' ],
                [ home_url( '/community/' ), 'ชุมชน', 'กำลังพัฒนาเว็บ' ],
                [ $this->products_url(), 'ผลิตภัณฑ์', 'กำลังพัฒนาเว็บ' ],
            ],
        ];
        return $groups;
    }

    public function register_admin_page() {
        add_options_page( 'Thinkb4do Discovery', 'Thinkb4do Discovery', 'manage_options', 'thinkb4do-discovery', [ $this, 'render_admin_page' ] );
    }

    public function register_settings() {
        register_setting( 'tb4_discovery_settings', 'tb4_discovery_settings', [
            'type'              => 'array',
            'sanitize_callback' => [ $this, 'sanitize_settings' ],
            'default'           => $this->default_settings(),
        ] );
    }

    private function default_settings() {
        return [
            'show_hero'          => 1,
            'show_composer_hint' => 1,
            'show_posts'         => 1,
            'show_community'     => 1,
            'show_products'      => 1,
            'items_per_section'  => 6,
            'lite_mode'          => 1,
        ];
    }

    private function get_settings() {
        $settings = get_option( 'tb4_discovery_settings', [] );
        return wp_parse_args( is_array( $settings ) ? $settings : [], $this->default_settings() );
    }

    private function get_setting( $key, $default = null ) {
        $settings = $this->get_settings();
        return array_key_exists( $key, $settings ) ? $settings[ $key ] : $default;
    }

    public function sanitize_settings( $input ) {
        $input = is_array( $input ) ? $input : [];
        return [
            'show_hero'          => empty( $input['show_hero'] ) ? 0 : 1,
            'show_composer_hint' => empty( $input['show_composer_hint'] ) ? 0 : 1,
            'show_posts'         => empty( $input['show_posts'] ) ? 0 : 1,
            'show_community'     => empty( $input['show_community'] ) ? 0 : 1,
            'show_products'      => empty( $input['show_products'] ) ? 0 : 1,
            'items_per_section'  => max( 3, min( 12, absint( $input['items_per_section'] ?? 6 ) ) ),
            'lite_mode'          => empty( $input['lite_mode'] ) ? 0 : 1,
        ];
    }

    public function render_admin_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        $settings = $this->get_settings();
        ?>
        <div class="wrap tb4-discovery-admin">
            <h1>Thinkb4do Discovery</h1>
            <p><strong>Settings | โดย Thinkb4do | ดูรายละเอียด</strong></p>
            <p>หน้านี้ใช้ปรับการแสดงผลสำหรับผู้ดูแลเท่านั้น หน้า Public เวอร์ชันนี้แสดงข้อความสั้นแบบปลอดภัย</p>

            <form method="post" action="options.php">
                <?php settings_fields( 'tb4_discovery_settings' ); ?>
                <table class="form-table" role="presentation">
                    <?php
                    $toggles = [
                        'show_hero'          => 'แสดง Hero ด้านบน',
                        'show_composer_hint' => 'แสดงกล่องชวนเข้า Community',
                        'show_posts'         => 'แสดงบทความล่าสุด',
                        'show_community'     => 'แสดงตัวอย่างชุมชน',
                        'show_products'      => 'แสดงตัวอย่างผลิตภัณฑ์',
                        'lite_mode'          => 'เปิดโหมดเบา ลด animation หนัก',
                    ];
                    foreach ( $toggles as $key => $label ) :
                    ?>
                        <tr>
                            <th scope="row"><?php echo esc_html( $label ); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="tb4_discovery_settings[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( ! empty( $settings[ $key ] ) ); ?>>
                                    เปิดใช้งาน
                                </label>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <th scope="row">จำนวนรายการต่อหมวด</th>
                        <td>
                            <input type="number" min="3" max="12" name="tb4_discovery_settings[items_per_section]" value="<?php echo esc_attr( $settings['items_per_section'] ); ?>">
                            <p class="description">แนะนำ 6 รายการ เพื่อให้หน้าเบาและไม่หน่วง</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button( 'บันทึก Discovery Settings' ); ?>
            </form>

            <hr>
            <h2>สถานะการแสดงผล</h2>
            <ul>
                <li>หน้า Public แสดงข้อความ “กำลังพัฒนาเว็บ” เป็นหลัก</li>
                <li>ยังรองรับระบบเมนูเดิม แต่หน้า Public ไม่อธิบายรายละเอียดระบบ</li>
                <li>รองรับมือถือ แท็บเล็ต และเดสก์ท็อป</li>
                <li>โทนสีใช้เขียว ขาว ส้ม ดำ ตามแบรนด์ Thinkb4do แบบเรียบหรู</li>
            </ul>
        </div>
        <?php
    }

    public function plugin_action_links( $links ) {
        $settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=thinkb4do-discovery' ) ) . '">Settings</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }
}

register_activation_hook( __FILE__, [ 'TB4_Discovery_Plugin', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'TB4_Discovery_Plugin', 'deactivate' ] );

TB4_Discovery_Plugin::instance();
