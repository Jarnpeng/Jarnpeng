<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TB4_Products_Plugin {
    private static $instance = null;
    private $meta_fields = [];
    private $type_labels = [];
    private $status_labels = [];

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function activate() {
        $plugin = self::instance();
        $plugin->register_types();
        flush_rewrite_rules();
    }

    public static function deactivate() {
        flush_rewrite_rules();
    }

    private function __construct() {
        $this->type_labels = [
            'recommended' => 'แนะนำ',
            'plugin'      => 'ระบบเสริม',
            'ai-tool'     => 'AI Tools',
            'wordpress'   => 'ระบบเว็บไซต์',
            'template'    => 'Template',
            'system'      => 'System',
            'vps-system'  => 'VPS & System',
            'service'     => 'บริการ',
        ];

        $this->status_labels = [
            'ready'      => 'พร้อมใช้',
            'beta'       => 'Beta',
            'soon'       => 'ใกล้เปิด',
            'draft-demo' => 'ข้อมูลตัวอย่าง',
            'research'   => 'กำลังวิจัย',
        ];

        $this->meta_fields = [
            '_tb4_product_badge'        => [ 'label' => 'ป้ายผลิตภัณฑ์', 'type' => 'text', 'placeholder' => 'ใหม่ / แนะนำ / กำลังพัฒนา' ],
            '_tb4_product_tagline'      => [ 'label' => 'คำโปรยสั้น', 'type' => 'text', 'placeholder' => 'เช่น เครื่องมือช่วยสร้างระบบสำหรับครีเอเตอร์' ],
            '_tb4_product_type'         => [ 'label' => 'ประเภท', 'type' => 'select', 'options' => $this->type_labels ],
            '_tb4_product_status'       => [ 'label' => 'สถานะ', 'type' => 'select', 'options' => $this->status_labels ],
            '_tb4_product_level'        => [ 'label' => 'ระดับผลิตภัณฑ์', 'type' => 'select', 'options' => [ 'starter' => 'Starter', 'pro' => 'Pro', 'studio' => 'Studio', 'enterprise' => 'Enterprise' ] ],
            '_tb4_product_rating'       => [ 'label' => 'คะแนนคุณภาพ', 'type' => 'text', 'placeholder' => '4.8' ],
            '_tb4_product_downloads'    => [ 'label' => 'ยอดดาวน์โหลด/ใช้งาน', 'type' => 'text', 'placeholder' => '120' ],
            '_tb4_product_license'      => [ 'label' => 'License / สิทธิ์ใช้งาน', 'type' => 'text', 'placeholder' => 'ตามรายละเอียดสินค้า / ทดลองใช้ / สำหรับสมาชิก' ],
            '_tb4_product_icon'         => [ 'label' => 'ไอคอน/อีโมจิ/ตัวย่อ', 'type' => 'text', 'placeholder' => 'AI / WEB / T' ],
            '_tb4_product_accent'       => [ 'label' => 'สีหัวการ์ด', 'type' => 'text', 'placeholder' => '#1E6B45' ],
            '_tb4_product_demo_url'     => [ 'label' => 'ลิงก์ดูตัวอย่าง', 'type' => 'url', 'placeholder' => 'https://...' ],
            '_tb4_product_purchase_url' => [ 'label' => 'ลิงก์ติดต่อ/ขอรายละเอียด', 'type' => 'url', 'placeholder' => 'https://...' ],
            '_tb4_product_cta_label'    => [ 'label' => 'ข้อความปุ่มหลัก', 'type' => 'text', 'placeholder' => 'ดูรายละเอียด / ขอเดโม / ติดต่อทีมงาน' ],
            '_tb4_product_affiliate_enabled' => [ 'label' => 'เปิดระบบ Affiliate / ลิงก์แนะนำ', 'type' => 'checkbox' ],
            '_tb4_product_show_partner_prices' => [ 'label' => 'แสดงราคาเปรียบเทียบจากพาร์ทเนอร์', 'type' => 'checkbox' ],
            '_tb4_product_affiliate_disclosure' => [ 'label' => 'ข้อความเปิดเผย Affiliate', 'type' => 'text', 'placeholder' => 'ลิงก์บางรายการอาจเป็นลิงก์แนะนำ โดย Thinkb4do' ],
            '_tb4_product_external_affiliate_enabled' => [ 'label' => 'เปิดเชื่อม Affiliate จากภายนอก', 'type' => 'checkbox' ],
            '_tb4_product_external_affiliate_feeds' => [ 'label' => 'Affiliate Feed URLs ภายนอก (JSON/CSV แยกบรรทัด)', 'type' => 'textarea', 'placeholder' => "https://partner.example.com/feed.json
https://partner.example.com/feed.csv" ],
            '_tb4_product_external_affiliate_keywords' => [ 'label' => 'คำค้น/รหัสสินค้าสำหรับคัดกรองข้อเสนอ', 'type' => 'text', 'placeholder' => 'AiRA, SEO, template, sku-123' ],
            '_tb4_product_external_affiliate_limit' => [ 'label' => 'จำนวนข้อเสนอภายนอกสูงสุด', 'type' => 'text', 'placeholder' => '6' ],
            '_tb4_product_affiliate_direct_links' => [ 'label' => 'Affiliate Links จากเว็บต่าง ๆ (ชื่อ | URL | หมายเหตุ | ป้าย | ราคา/ข้อเสนอ)', 'type' => 'textarea', 'placeholder' => "Shopee | https://... | เหมาะกับสินค้าทั่วไป | Marketplace | ยังไม่แสดงราคา
Lazada | https://... | ดูรายละเอียดจากร้านค้า | Partner | ยังไม่แสดงราคา" ],
            '_tb4_product_affiliate_supported_sites' => [ 'label' => 'กลุ่มเว็บ/เครือข่ายที่รองรับ', 'type' => 'textarea', 'placeholder' => "Marketplace: Shopee, Lazada, Amazon, eBay
Booking: Calendly, Google Calendar, Setmore, YouCanBookMe
Service: Partner Website, SaaS, Hosting, Course Platform" ],
            '_tb4_product_connection_status_note' => [ 'label' => 'ข้อความสถานะการเชื่อมต่อสำหรับผู้เข้าชม', 'type' => 'text', 'placeholder' => 'รองรับการเชื่อมต่อหลายแหล่ง พร้อมตรวจลิงก์ก่อนนำเสนอ' ],
            '_tb4_product_booking_enabled' => [ 'label' => 'เปิดระบบ Booking / นัดหมายจากภายนอก', 'type' => 'checkbox' ],
            '_tb4_product_booking_status' => [ 'label' => 'สถานะ Booking', 'type' => 'select', 'options' => [ 'ready' => 'เปิดให้จอง/นัดหมาย', 'soon' => 'เตรียมเปิดจอง', 'contact' => 'ติดต่อทีมงานก่อนจอง' ] ],
            '_tb4_product_booking_cta_label' => [ 'label' => 'ข้อความปุ่ม Booking', 'type' => 'text', 'placeholder' => 'จองคิว / นัดหมาย / ตรวจเวลาว่าง' ],
            '_tb4_product_booking_sources' => [ 'label' => 'Booking Links ภายนอก (ชื่อ | URL | ประเภท | หมายเหตุ | ป้าย)', 'type' => 'textarea', 'placeholder' => "Calendly | https://calendly.com/... | นัดหมาย | เลือกเวลาที่สะดวก | Booking
Partner Booking | https://... | สำรองบริการ | ตรวจรอบว่างจากผู้ให้บริการ | External" ],
            '_tb4_product_booking_feed_urls' => [ 'label' => 'Booking Feed URLs ภายนอก (JSON/CSV/XML แยกบรรทัด)', 'type' => 'textarea', 'placeholder' => "https://partner.example.com/booking.json
https://partner.example.com/booking.csv" ],
            '_tb4_product_booking_keywords' => [ 'label' => 'คำค้น/รหัสบริการสำหรับคัดกรอง Booking', 'type' => 'text', 'placeholder' => 'consult, design, aira, service-code' ],
            '_tb4_product_booking_limit' => [ 'label' => 'จำนวนรายการ Booking ภายนอกสูงสุด', 'type' => 'text', 'placeholder' => '4' ],
            '_tb4_product_booking_embed_url' => [ 'label' => 'ลิงก์หน้าจองแบบฝัง/แสดงตัวอย่าง (ไม่บังคับ)', 'type' => 'url', 'placeholder' => 'https://...' ],
            '_tb4_product_booking_requires_approval' => [ 'label' => 'Booking ต้องรอยืนยันจากทีมงานก่อน', 'type' => 'checkbox' ],
            '_tb4_product_affiliate_tracking_code' => [ 'label' => 'Tracking / Ref Code ของทีมงาน', 'type' => 'text', 'placeholder' => 'thinkb4do หรือรหัสพาร์ทเนอร์' ],
            '_tb4_product_affiliate_utm_campaign' => [ 'label' => 'UTM Campaign', 'type' => 'text', 'placeholder' => 'thinkb4do-products' ],
            '_tb4_product_affiliate_link_label' => [ 'label' => 'ข้อความปุ่มพาร์ทเนอร์', 'type' => 'text', 'placeholder' => 'ดูข้อเสนอ / ไปยังพาร์ทเนอร์' ],
            '_tb4_product_best_for'      => [ 'label' => 'เหมาะสำหรับ', 'type' => 'text', 'placeholder' => 'ครีเอเตอร์ / เจ้าของเว็บ / ทีมงาน / ธุรกิจขนาดเล็ก' ],
            '_tb4_product_trust_score'   => [ 'label' => 'Trust Score (%)', 'type' => 'text', 'placeholder' => '96' ],
            '_tb4_product_integrations'  => [ 'label' => 'ระบบที่เชื่อมต่อได้', 'type' => 'text', 'placeholder' => 'เว็บไซต์หลัก, หน้า Landing Page, ระบบสมาชิก, ระบบชำระเงิน' ],
            '_tb4_product_compare_features' => [ 'label' => 'จุดเด่นสำหรับตารางเปรียบเทียบ', 'type' => 'textarea', 'placeholder' => 'เช่น เร็วขึ้น, ใช้ง่าย, รองรับมือถือ, มีระบบรายงาน' ],
            '_tb4_product_detail_summary' => [ 'label' => 'รายละเอียดสินค้า: สรุปภาพรวม', 'type' => 'textarea', 'placeholder' => 'อธิบายว่าสินค้านี้คืออะไร ช่วยแก้ปัญหาอะไร และเหมาะกับใคร' ],
            '_tb4_product_key_features' => [ 'label' => 'รายละเอียดสินค้า: ฟีเจอร์หลัก (แยกบรรทัด)', 'type' => 'textarea', 'placeholder' => "ระบบจัดการงาน
รองรับหน้าเว็บหลายรูปแบบ
มีพื้นที่จัดการสำหรับทีมงาน" ],
            '_tb4_product_use_steps' => [ 'label' => 'รายละเอียดสินค้า: วิธีเริ่มใช้งาน (แยกบรรทัด)', 'type' => 'textarea', 'placeholder' => "1. อ่านรายละเอียดสินค้า
2. ตั้งค่าพื้นฐานตามคู่มือ
3. เปิดใช้งานหน้าเว็บ" ],
            '_tb4_product_specs' => [ 'label' => 'รายละเอียดสินค้า: สเปก/ข้อมูลเทคนิค (รูปแบบ หัวข้อ: ค่า)', 'type' => 'textarea', 'placeholder' => "รองรับ: เว็บไซต์มาตรฐาน
ภาษา: ไทย/อังกฤษ
อุปกรณ์: มือถือ แท็บเล็ต เดสก์ท็อป" ],
            '_tb4_product_gallery_urls' => [ 'label' => 'รายละเอียดสินค้า: Gallery Image URLs (แยกบรรทัด)', 'type' => 'textarea', 'placeholder' => "https://example.com/screen-1.jpg
https://example.com/screen-2.jpg" ],
            '_tb4_product_faq' => [ 'label' => 'รายละเอียดสินค้า: FAQ (คำถาม | คำตอบ แยกบรรทัด)', 'type' => 'textarea', 'placeholder' => "เหมาะกับใคร? | เหมาะกับครีเอเตอร์และเจ้าของเว็บไซต์
มีราคาไหม? | ตอนนี้ยังไม่แสดงราคา" ],
            '_tb4_product_support_note' => [ 'label' => 'รายละเอียดสินค้า: ข้อความสนับสนุน/หมายเหตุ', 'type' => 'textarea', 'placeholder' => 'เช่น อยู่ระหว่างเตรียมข้อมูลเชิงพาณิชย์ / ติดต่อทีมงานเพื่อขอข้อมูลเพิ่มเติม' ],
            '_tb4_product_partner1_name' => [ 'label' => 'พาร์ทเนอร์ 1: ชื่อร้าน/แพลตฟอร์ม', 'type' => 'text', 'placeholder' => 'Official / Partner A' ],
            '_tb4_product_partner1_url'  => [ 'label' => 'พาร์ทเนอร์ 1: Affiliate URL', 'type' => 'url', 'placeholder' => 'https://...' ],
            '_tb4_product_partner1_price'=> [ 'label' => 'พาร์ทเนอร์ 1: ราคา/ข้อเสนอ (ไม่บังคับ)', 'type' => 'text', 'placeholder' => 'ยังไม่แสดงราคา / ขอใบเสนอราคา / 1,xxx' ],
            '_tb4_product_partner1_note' => [ 'label' => 'พาร์ทเนอร์ 1: หมายเหตุ', 'type' => 'text', 'placeholder' => 'แนะนำ / ส่งเร็ว / เหมาะกับทีมเล็ก' ],
            '_tb4_product_partner2_name' => [ 'label' => 'พาร์ทเนอร์ 2: ชื่อร้าน/แพลตฟอร์ม', 'type' => 'text', 'placeholder' => 'Partner B' ],
            '_tb4_product_partner2_url'  => [ 'label' => 'พาร์ทเนอร์ 2: Affiliate URL', 'type' => 'url', 'placeholder' => 'https://...' ],
            '_tb4_product_partner2_price'=> [ 'label' => 'พาร์ทเนอร์ 2: ราคา/ข้อเสนอ (ไม่บังคับ)', 'type' => 'text', 'placeholder' => 'ยังไม่แสดงราคา / ขอใบเสนอราคา / 1,xxx' ],
            '_tb4_product_partner2_note' => [ 'label' => 'พาร์ทเนอร์ 2: หมายเหตุ', 'type' => 'text', 'placeholder' => 'คุ้มค่า / เหมาะกับเริ่มต้น' ],
            '_tb4_product_partner3_name' => [ 'label' => 'พาร์ทเนอร์ 3: ชื่อร้าน/แพลตฟอร์ม', 'type' => 'text', 'placeholder' => 'Partner C' ],
            '_tb4_product_partner3_url'  => [ 'label' => 'พาร์ทเนอร์ 3: Affiliate URL', 'type' => 'url', 'placeholder' => 'https://...' ],
            '_tb4_product_partner3_price'=> [ 'label' => 'พาร์ทเนอร์ 3: ราคา/ข้อเสนอ (ไม่บังคับ)', 'type' => 'text', 'placeholder' => 'ยังไม่แสดงราคา / ขอใบเสนอราคา / 1,xxx' ],
            '_tb4_product_partner3_note' => [ 'label' => 'พาร์ทเนอร์ 3: หมายเหตุ', 'type' => 'text', 'placeholder' => 'บริการเสริม / เหมาะกับองค์กร' ],
            '_tb4_product_featured'     => [ 'label' => 'ผลิตภัณฑ์แนะนำ', 'type' => 'checkbox' ],
        ];

        add_action( 'init', [ $this, 'register_types' ], 5 );
        add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
        add_action( 'save_post_tb4_product', [ $this, 'save_meta' ], 10, 2 );
        add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
        add_filter( 'template_include', [ $this, 'template_include' ] );
        add_action( 'template_redirect', [ $this, 'affiliate_redirect' ], 1 );
        add_shortcode( 'thinkb4do_products', [ $this, 'shortcode_products' ] );
        add_action( 'admin_menu', [ $this, 'admin_menu' ] );
        add_filter( 'manage_tb4_product_posts_columns', [ $this, 'admin_columns' ] );
        add_action( 'manage_tb4_product_posts_custom_column', [ $this, 'admin_column_content' ], 10, 2 );
    }

    public function register_types() {
        register_post_type( 'tb4_product', [
            'labels' => [
                'name'               => __( 'ผลิตภัณฑ์', 'thinkb4do-products' ),
                'singular_name'      => __( 'ผลิตภัณฑ์', 'thinkb4do-products' ),
                'add_new'            => __( 'เพิ่มผลิตภัณฑ์', 'thinkb4do-products' ),
                'add_new_item'       => __( 'เพิ่มผลิตภัณฑ์', 'thinkb4do-products' ),
                'edit_item'          => __( 'แก้ไขผลิตภัณฑ์', 'thinkb4do-products' ),
                'new_item'           => __( 'ผลิตภัณฑ์ใหม่', 'thinkb4do-products' ),
                'view_item'          => __( 'ดูผลิตภัณฑ์', 'thinkb4do-products' ),
                'search_items'       => __( 'ค้นหาผลิตภัณฑ์', 'thinkb4do-products' ),
                'not_found'          => __( 'ยังไม่มีผลิตภัณฑ์', 'thinkb4do-products' ),
                'menu_name'          => __( 'ผลิตภัณฑ์', 'thinkb4do-products' ),
            ],
            'public'        => true,
            'has_archive'   => true,
            'show_in_rest'  => true,
            'supports'      => [ 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields' ],
            'rewrite'       => [ 'slug' => 'products', 'with_front' => false ],
            'menu_icon'     => 'dashicons-products',
            'menu_position' => 26,
        ] );

        register_taxonomy( 'tb4_product_cat', 'tb4_product', [
            'labels' => [
                'name'          => __( 'หมวดหมู่ผลิตภัณฑ์', 'thinkb4do-products' ),
                'singular_name' => __( 'หมวดหมู่ผลิตภัณฑ์', 'thinkb4do-products' ),
            ],
            'hierarchical' => true,
            'show_in_rest' => true,
            'rewrite'      => [ 'slug' => 'product-cat', 'with_front' => false ],
        ] );
    }

    public function register_assets() {
        wp_register_style( 'tb4-products', TB4_PRODUCTS_PLUGIN_URL . 'assets/css/tb4-products.css', [], TB4_PRODUCTS_PLUGIN_VERSION );
        wp_register_script( 'tb4-products', TB4_PRODUCTS_PLUGIN_URL . 'assets/js/tb4-products.js', [], TB4_PRODUCTS_PLUGIN_VERSION, true );
    }

    private function enqueue_assets() {
        wp_enqueue_style( 'tb4-products' );
        wp_enqueue_script( 'tb4-products' );
    }

    public function template_include( $template ) {
        if ( is_post_type_archive( 'tb4_product' ) ) {
            $this->enqueue_assets();
            $archive = TB4_PRODUCTS_PLUGIN_DIR . 'templates/archive-tb4_product.php';
            return file_exists( $archive ) ? $archive : $template;
        }
        if ( is_singular( 'tb4_product' ) ) {
            $this->enqueue_assets();
            $single = TB4_PRODUCTS_PLUGIN_DIR . 'templates/single-tb4_product.php';
            return file_exists( $single ) ? $single : $template;
        }
        return $template;
    }

    public function add_meta_boxes() {
        add_meta_box( 'tb4_product_details', 'ข้อมูลผลิตภัณฑ์ Thinkb4do', [ $this, 'render_meta_box' ], 'tb4_product', 'normal', 'high' );
    }

    private function admin_meta_sections() {
        return [
            'basic' => [
                'title' => '1) ข้อมูลหลัก',
                'desc' => 'กรอกชื่อ คำโปรย ประเภท สถานะ และปุ่มหลักให้ทีมงานเริ่มใช้งานได้เร็ว',
                'fields' => [ '_tb4_product_badge', '_tb4_product_tagline', '_tb4_product_type', '_tb4_product_status', '_tb4_product_level', '_tb4_product_icon', '_tb4_product_accent', '_tb4_product_cta_label' ],
            ],
            'detail' => [
                'title' => '2) รายละเอียดสินค้า',
                'desc' => 'ข้อมูลมาตรฐานที่ลูกค้าควรรู้ก่อนตัดสินใจ โดยยังไม่ต้องเปิดการซื้อ',
                'fields' => [ '_tb4_product_detail_summary', '_tb4_product_key_features', '_tb4_product_use_steps', '_tb4_product_specs', '_tb4_product_support_note' ],
            ],
            'trust' => [
                'title' => '3) ความน่าเชื่อถือและการเปรียบเทียบ',
                'desc' => 'ใช้ช่วยให้หน้าผลิตภัณฑ์ดูครบและเปรียบเทียบได้ง่าย',
                'fields' => [ '_tb4_product_rating', '_tb4_product_downloads', '_tb4_product_license', '_tb4_product_best_for', '_tb4_product_trust_score', '_tb4_product_integrations', '_tb4_product_compare_features', '_tb4_product_featured' ],
            ],
            'affiliate' => [
                'title' => '4) Affiliate / รายได้เสริม',
                'desc' => 'เชื่อมข้อเสนอจากเว็บต่าง ๆ โดยควบคุมการแสดงราคาได้',
                'fields' => [ '_tb4_product_affiliate_enabled', '_tb4_product_show_partner_prices', '_tb4_product_affiliate_disclosure', '_tb4_product_affiliate_link_label', '_tb4_product_affiliate_tracking_code', '_tb4_product_affiliate_utm_campaign', '_tb4_product_affiliate_supported_sites', '_tb4_product_affiliate_direct_links', '_tb4_product_external_affiliate_enabled', '_tb4_product_external_affiliate_feeds', '_tb4_product_external_affiliate_keywords', '_tb4_product_external_affiliate_limit', '_tb4_product_partner1_name', '_tb4_product_partner1_url', '_tb4_product_partner1_price', '_tb4_product_partner1_note', '_tb4_product_partner2_name', '_tb4_product_partner2_url', '_tb4_product_partner2_price', '_tb4_product_partner2_note', '_tb4_product_partner3_name', '_tb4_product_partner3_url', '_tb4_product_partner3_price', '_tb4_product_partner3_note' ],
            ],
            'booking' => [
                'title' => '5) Booking ภายนอก',
                'desc' => 'ตั้งค่าลิงก์นัดหมาย จองคิว หรือบริการจากภายนอก',
                'fields' => [ '_tb4_product_booking_enabled', '_tb4_product_booking_status', '_tb4_product_booking_cta_label', '_tb4_product_booking_requires_approval', '_tb4_product_booking_sources', '_tb4_product_booking_feed_urls', '_tb4_product_booking_keywords', '_tb4_product_booking_limit', '_tb4_product_booking_embed_url' ],
            ],
            'media' => [
                'title' => '6) รูปภาพและ FAQ',
                'desc' => 'เติมภาพตัวอย่างและคำถามที่พบบ่อยเพื่อให้หน้ารายละเอียดสมบูรณ์ขึ้น',
                'fields' => [ '_tb4_product_demo_url', '_tb4_product_purchase_url', '_tb4_product_gallery_urls', '_tb4_product_faq', '_tb4_product_connection_status_note' ],
            ],
        ];
    }

    private function render_admin_field( $key, $field, $value ) {
        echo '<p class="tb4-team-field">';
        echo '<label class="tb4-team-label" for="' . esc_attr( $key ) . '">' . esc_html( $field['label'] ) . '</label>';
        if ( 'select' === $field['type'] ) {
            echo '<select id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '">';
            foreach ( $field['options'] as $option_key => $option_label ) {
                echo '<option value="' . esc_attr( $option_key ) . '" ' . selected( $value, $option_key, false ) . '>' . esc_html( $option_label ) . '</option>';
            }
            echo '</select>';
        } elseif ( 'checkbox' === $field['type'] ) {
            echo '<label class="tb4-team-checkbox"><input type="checkbox" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="1" ' . checked( $value, '1', false ) . '> <span>เปิดใช้งาน</span></label>';
        } elseif ( 'textarea' === $field['type'] ) {
            echo '<textarea id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" rows="4" placeholder="' . esc_attr( $field['placeholder'] ?? '' ) . '">' . esc_textarea( $value ) . '</textarea>';
        } else {
            $type = 'url' === $field['type'] ? 'url' : 'text';
            echo '<input type="' . esc_attr( $type ) . '" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" placeholder="' . esc_attr( $field['placeholder'] ?? '' ) . '">';
        }
        if ( ! empty( $field['placeholder'] ) ) {
            echo '<span class="tb4-team-help">ตัวอย่าง: ' . esc_html( wp_trim_words( str_replace( [ "\r", "\n" ], ' ', $field['placeholder'] ), 18 ) ) . '</span>';
        }
        echo '</p>';
    }

    public function render_meta_box( $post ) {
        wp_nonce_field( 'tb4_product_save_meta', 'tb4_product_nonce' );
        $sections = $this->admin_meta_sections();
        $filled = 0;
        $total = 0;
        foreach ( $this->meta_fields as $key => $field ) {
            if ( 'checkbox' === $field['type'] ) {
                continue;
            }
            $total++;
            if ( '' !== trim( (string) get_post_meta( $post->ID, $key, true ) ) ) {
                $filled++;
            }
        }
        $progress = $total ? min( 100, round( ( $filled / $total ) * 100 ) ) : 0;
        ?>
        <style>
            .tb4-team-panel{background:#f8fafc;border:1px solid #dbe3ea;border-radius:16px;padding:16px;margin:0 0 16px;color:#0f172a}
            .tb4-team-panel strong{color:#145c39}
            .tb4-team-progress{height:10px;background:#e5edf0;border-radius:999px;overflow:hidden;margin-top:10px}
            .tb4-team-progress span{display:block;height:100%;background:linear-gradient(90deg,#1E6B45,#F97316);border-radius:999px}
            .tb4-team-quick{display:flex;flex-wrap:wrap;gap:8px;margin-top:12px}
            .tb4-team-quick button,.tb4-team-section-toggle{border:1px solid #d7e2dd;background:#fff;border-radius:999px;padding:8px 12px;font-weight:700;color:#145c39;cursor:pointer}
            .tb4-team-quick button:hover,.tb4-team-section-toggle:hover{border-color:#1E6B45;box-shadow:0 4px 14px rgba(30,107,69,.12)}
            .tb4-team-sections{display:grid;gap:14px}
            .tb4-team-section{background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden}
            .tb4-team-section__head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 16px;background:linear-gradient(180deg,#fff,#f8fafc)}
            .tb4-team-section__head h3{margin:0;font-size:15px;color:#0f172a}
            .tb4-team-section__head p{margin:3px 0 0;color:#64748b}
            .tb4-team-section__body{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;padding:16px}
            .tb4-team-field{margin:0}
            .tb4-team-field label.tb4-team-label{display:block;font-weight:800;margin-bottom:7px;color:#1f2937}
            .tb4-team-field input[type=text],.tb4-team-field input[type=url],.tb4-team-field select,.tb4-team-field textarea{width:100%;max-width:100%;border:1px solid #d8e1dc;border-radius:12px;padding:10px 12px;box-shadow:none;background:#fff}
            .tb4-team-field textarea{min-height:92px}
            .tb4-team-help{display:block;margin-top:5px;color:#6b7280;font-size:12px;line-height:1.5}
            .tb4-team-checkbox{display:flex;align-items:center;gap:9px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:10px 12px;font-weight:700}
            .tb4-team-section[data-collapsed="1"] .tb4-team-section__body{display:none}
            @media (max-width: 900px){.tb4-team-section__body{grid-template-columns:1fr}.tb4-team-section__head{align-items:flex-start;flex-direction:column}.tb4-team-quick button{width:100%;text-align:center}}
        </style>
        <div class="tb4-team-panel">
            <strong>ศูนย์กรอกข้อมูลสินค้าแบบทีมงาน</strong><br>
            กรอกเฉพาะข้อมูลจำเป็นก่อน แล้วค่อยเติม Affiliate, Booking, รูปภาพ และ FAQ ภายหลังได้ ระบบยังคงค่าเริ่มต้นไม่แสดงราคา และหน้าสาธารณะยังใช้โหมดปลอดภัย
            <div class="tb4-team-progress" aria-label="ความครบถ้วนของข้อมูล"><span style="width:<?php echo esc_attr( $progress ); ?>%"></span></div>
            <p style="margin:8px 0 0;color:#64748b;">ความครบถ้วนโดยประมาณ: <strong><?php echo esc_html( $progress ); ?>%</strong></p>
            <div class="tb4-team-quick" data-tb4-admin-quick>
                <button type="button" data-preset="starter">เติมโครงสินค้าเริ่มต้น</button>
                <button type="button" data-preset="affiliate">เติมตัวอย่าง Affiliate</button>
                <button type="button" data-preset="booking">เติมตัวอย่าง Booking</button>
                <button type="button" data-preset="detail">เติมโครงรายละเอียด</button>
            </div>
        </div>
        <div class="tb4-team-sections" data-tb4-admin-sections>
            <?php foreach ( $sections as $section_id => $section ) : ?>
                <div class="tb4-team-section" data-section="<?php echo esc_attr( $section_id ); ?>">
                    <div class="tb4-team-section__head">
                        <div>
                            <h3><?php echo esc_html( $section['title'] ); ?></h3>
                            <p><?php echo esc_html( $section['desc'] ); ?></p>
                        </div>
                        <button type="button" class="tb4-team-section-toggle" aria-expanded="true">ย่อ/ขยาย</button>
                    </div>
                    <div class="tb4-team-section__body">
                        <?php foreach ( $section['fields'] as $key ) : ?>
                            <?php
                            if ( empty( $this->meta_fields[ $key ] ) ) {
                                continue;
                            }
                            $this->render_admin_field( $key, $this->meta_fields[ $key ], get_post_meta( $post->ID, $key, true ) );
                            ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <script>
        (function(){
            const root = document.querySelector('[data-tb4-admin-sections]');
            if(!root){return;}
            root.addEventListener('click', function(e){
                const btn = e.target.closest('.tb4-team-section-toggle');
                if(!btn){return;}
                const box = btn.closest('.tb4-team-section');
                const collapsed = box.getAttribute('data-collapsed') === '1';
                box.setAttribute('data-collapsed', collapsed ? '0' : '1');
                btn.setAttribute('aria-expanded', collapsed ? 'true' : 'false');
            });
            const quick = document.querySelector('[data-tb4-admin-quick]');
            const set = function(id, value){ const el = document.getElementById(id); if(el && !el.value){ el.value = value; el.dispatchEvent(new Event('change',{bubbles:true})); } };
            const check = function(id){ const el = document.getElementById(id); if(el){ el.checked = true; } };
            if(quick){
                quick.addEventListener('click', function(e){
                    const btn = e.target.closest('[data-preset]');
                    if(!btn){return;}
                    const p = btn.getAttribute('data-preset');
                    if(p === 'starter'){
                        set('_tb4_product_badge','ใหม่'); set('_tb4_product_tagline','สินค้า/บริการคุณภาพจาก Thinkb4do สำหรับช่วยให้ทีมงานทำงานง่ายขึ้น'); set('_tb4_product_cta_label','ดูรายละเอียด'); set('_tb4_product_best_for','ครีเอเตอร์ เจ้าของธุรกิจ และทีมงานขนาดเล็ก'); set('_tb4_product_license','ตามรายละเอียดสินค้า'); set('_tb4_product_trust_score','95');
                    }
                    if(p === 'affiliate'){
                        check('_tb4_product_affiliate_enabled'); set('_tb4_product_affiliate_disclosure','ลิงก์บางรายการอาจเป็นลิงก์แนะนำ โดย Thinkb4do'); set('_tb4_product_affiliate_link_label','ดูข้อเสนอ'); set('_tb4_product_affiliate_tracking_code','thinkb4do'); set('_tb4_product_affiliate_utm_campaign','thinkb4do-products'); set('_tb4_product_affiliate_direct_links','Partner Name | https://example.com/product | ตรวจรายละเอียดจากพาร์ทเนอร์ | Partner | ยังไม่แสดงราคา');
                    }
                    if(p === 'booking'){
                        check('_tb4_product_booking_enabled'); check('_tb4_product_booking_requires_approval'); set('_tb4_product_booking_cta_label','จองคิว / นัดหมาย'); set('_tb4_product_booking_sources','Booking Partner | https://example.com/booking | นัดหมาย | เลือกเวลาหรือติดต่อทีมงานก่อนยืนยัน | Booking');
                    }
                    if(p === 'detail'){
                        set('_tb4_product_detail_summary','อธิบายว่าสินค้านี้คืออะไร ช่วยแก้ปัญหาอะไร และเหมาะกับใคร'); set('_tb4_product_key_features','ใช้งานง่าย\nรองรับมือถือ\nเชื่อมต่อพาร์ทเนอร์ได้\nเหมาะสำหรับต่อยอดธุรกิจ'); set('_tb4_product_use_steps','1. อ่านรายละเอียดสินค้า\n2. เลือกช่องทางที่เหมาะสม\n3. ติดต่อทีมงานหรือดูข้อเสนอจากพาร์ทเนอร์'); set('_tb4_product_specs','อุปกรณ์: มือถือ แท็บเล็ต เดสก์ท็อป\nภาษา: ไทย/อังกฤษ\nสถานะราคา: ยังไม่แสดงราคา'); set('_tb4_product_faq','มีราคาแสดงไหม? | ค่าเริ่มต้นยังไม่แสดงราคา\nซื้อได้เลยไหม? | หากยังไม่พร้อม ระบบจะแสดงไอคอนล็อก');
                    }
                });
            }
        })();
        </script>
        <?php
        echo '<p style="margin:16px 0 0;color:#64748b;">Settings | โดย Thinkb4do | ดูรายละเอียด — ใช้ข้อมูลนี้แสดงบนหน้าผลิตภัณฑ์และหน้ารายละเอียด โดยไม่เปิดเผยข้อมูลจัดการภายในบนหน้าสาธารณะ</p>';
    }

    public function save_meta( $post_id, $post ) {
        if ( ! isset( $_POST['tb4_product_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tb4_product_nonce'] ) ), 'tb4_product_save_meta' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        foreach ( $this->meta_fields as $key => $field ) {
            if ( 'checkbox' === $field['type'] ) {
                update_post_meta( $post_id, $key, isset( $_POST[ $key ] ) ? '1' : '0' );
                continue;
            }
            $raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
            if ( 'url' === $field['type'] ) {
                $value = esc_url_raw( $raw );
            } elseif ( 'textarea' === $field['type'] ) {
                $value = sanitize_textarea_field( $raw );
            } else {
                $value = sanitize_text_field( $raw );
            }
            update_post_meta( $post_id, $key, $value );
        }
    }

    public function admin_menu() {
        add_submenu_page(
            'edit.php?post_type=tb4_product',
            'ศูนย์ตั้งค่าทีมงาน',
            'ตั้งค่าทีมงาน',
            'manage_options',
            'tb4-products-team-settings',
            [ $this, 'render_team_settings_page' ]
        );
        add_submenu_page(
            'edit.php?post_type=tb4_product',
            'Thinkb4do Products',
            'คู่มือผลิตภัณฑ์',
            'manage_options',
            'tb4-products-guide',
            [ $this, 'render_admin_page' ]
        );
    }

    private function default_team_options() {
        return [
            'hide_prices_default' => '1',
            'public_safe_mode' => '1',
            'default_cta_label' => 'ดูรายละเอียด',
            'default_affiliate_disclosure' => 'ลิงก์บางรายการอาจเป็นลิงก์แนะนำ โดย Thinkb4do',
            'default_affiliate_link_label' => 'ดูข้อเสนอ',
            'default_booking_label' => 'จองคิว / นัดหมาย',
            'default_tracking_code' => 'thinkb4do',
            'default_utm_campaign' => 'thinkb4do-products',
            'default_connection_note' => 'รองรับการเชื่อมต่อหลายแหล่ง พร้อมตรวจลิงก์ก่อนนำเสนอ',
            'default_supported_sites' => "Marketplace: Shopee, Lazada, Amazon, eBay\nBooking: Calendly, Google Calendar, Setmore, YouCanBookMe\nService: Partner Website, SaaS, Hosting, Course Platform",
            'team_note' => 'ตั้งค่ากลางเพื่อให้ทีมงานกรอกข้อมูลสินค้าได้เร็วขึ้น โดยไม่เปิดเผยข้อมูลจัดการภายในบนหน้าสาธารณะ',
        ];
    }

    private function team_options() {
        $saved = get_option( 'tb4_products_team_settings', [] );
        if ( ! is_array( $saved ) ) {
            $saved = [];
        }
        return wp_parse_args( $saved, $this->default_team_options() );
    }

    private function team_option( $key, $fallback = '' ) {
        $options = $this->team_options();
        return isset( $options[ $key ] ) ? $options[ $key ] : $fallback;
    }

    private function save_team_options_from_post() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return false;
        }
        if ( empty( $_POST['tb4_products_team_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tb4_products_team_nonce'] ) ), 'tb4_products_team_settings' ) ) {
            return false;
        }
        $defaults = $this->default_team_options();
        $options = [];
        foreach ( $defaults as $key => $default ) {
            if ( in_array( $key, [ 'hide_prices_default', 'public_safe_mode' ], true ) ) {
                $options[ $key ] = isset( $_POST[ $key ] ) ? '1' : '0';
                continue;
            }
            $raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
            if ( in_array( $key, [ 'default_supported_sites', 'team_note' ], true ) ) {
                $options[ $key ] = sanitize_textarea_field( $raw );
            } else {
                $options[ $key ] = sanitize_text_field( $raw );
            }
        }
        update_option( 'tb4_products_team_settings', $options, false );
        return true;
    }

    public function render_team_settings_page() {
        $saved = false;
        if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
            $saved = $this->save_team_options_from_post();
        }
        $options = $this->team_options();
        $archive = get_post_type_archive_link( 'tb4_product' ) ?: home_url( '/products/' );
        $count = wp_count_posts( 'tb4_product' );
        $published = isset( $count->publish ) ? (int) $count->publish : 0;
        $draft = isset( $count->draft ) ? (int) $count->draft : 0;
        $ready_total = $this->count_products_by_status( 'ready' );
        $affiliate_total = $this->count_products_by_meta( '_tb4_product_affiliate_enabled', '1' );
        $booking_total = $this->count_products_by_meta( '_tb4_product_booking_enabled', '1' );
        ?>
        <div class="wrap tb4-team-settings-wrap">
            <style>
                .tb4-team-settings-wrap{max-width:1180px}
                .tb4-team-hero{background:linear-gradient(135deg,#0f5132,#1E6B45);border-radius:22px;padding:24px;color:#fff;box-shadow:0 18px 42px rgba(30,107,69,.18);margin:18px 0}
                .tb4-team-hero h1{color:#fff;margin:0 0 8px;font-size:28px}
                .tb4-team-hero p{font-size:15px;margin:0;color:rgba(255,255,255,.82)}
                .tb4-team-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin:18px 0}
                .tb4-team-card{background:#fff;border:1px solid #e2e8f0;border-radius:18px;padding:18px;box-shadow:0 10px 30px rgba(15,23,42,.05)}
                .tb4-team-card strong{display:block;color:#1E6B45;font-size:28px;line-height:1}
                .tb4-team-card span{display:block;margin-top:8px;color:#475569;font-weight:700}
                .tb4-team-form{display:grid;grid-template-columns:1.15fr .85fr;gap:16px;align-items:start}
                .tb4-team-form .tb4-team-card h2{margin-top:0;color:#0f172a}
                .tb4-team-form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px}
                .tb4-team-form label{font-weight:800;color:#1f2937;display:block;margin-bottom:7px}
                .tb4-team-form input[type=text],.tb4-team-form textarea{width:100%;border:1px solid #d8e1dc;border-radius:12px;padding:10px 12px;background:#fff;box-shadow:none}
                .tb4-team-form textarea{min-height:108px}
                .tb4-team-switch{display:flex;align-items:center;gap:10px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:12px;margin-bottom:12px}
                .tb4-team-actions{display:flex;flex-wrap:wrap;gap:8px;margin-top:14px}
                .tb4-team-checklist{margin:0;padding:0;list-style:none}
                .tb4-team-checklist li{display:flex;gap:9px;align-items:flex-start;padding:10px 0;border-bottom:1px solid #edf2f7;color:#334155}
                .tb4-team-checklist li:last-child{border-bottom:none}
                .tb4-team-dot{width:10px;height:10px;border-radius:50%;margin-top:5px;background:#1E6B45;box-shadow:0 0 0 5px rgba(30,107,69,.09);flex:0 0 auto}
                @media (max-width: 960px){.tb4-team-grid,.tb4-team-form,.tb4-team-form-row{grid-template-columns:1fr}}
            </style>
            <div class="tb4-team-hero">
                <h1>ศูนย์ตั้งค่าทีมงาน Thinkb4do Products</h1>
                <p>ตั้งค่ากลางครั้งเดียว แล้วให้ทีมงานกรอกสินค้า, Affiliate, Booking และรายละเอียดได้เร็วขึ้น โดยยังคงโหมดไม่แสดงราคาเป็นค่าเริ่มต้น</p>
            </div>
            <?php if ( $saved ) : ?>
                <div class="notice notice-success is-dismissible"><p>บันทึกการตั้งค่าทีมงานเรียบร้อยแล้ว</p></div>
            <?php endif; ?>
            <div class="tb4-team-grid">
                <div class="tb4-team-card"><strong><?php echo esc_html( $published ); ?></strong><span>ผลิตภัณฑ์เผยแพร่</span></div>
                <div class="tb4-team-card"><strong><?php echo esc_html( $draft ); ?></strong><span>แบบร่าง</span></div>
                <div class="tb4-team-card"><strong><?php echo esc_html( $affiliate_total ); ?></strong><span>เปิด Affiliate</span></div>
                <div class="tb4-team-card"><strong><?php echo esc_html( $booking_total ); ?></strong><span>เปิด Booking</span></div>
            </div>
            <form method="post" class="tb4-team-form">
                <?php wp_nonce_field( 'tb4_products_team_settings', 'tb4_products_team_nonce' ); ?>
                <div class="tb4-team-card">
                    <h2>ค่าเริ่มต้นสำหรับทีมงาน</h2>
                    <label class="tb4-team-switch"><input type="checkbox" name="hide_prices_default" value="1" <?php checked( $options['hide_prices_default'], '1' ); ?>> <span>ไม่แสดงราคาเป็นค่าเริ่มต้น จนกว่าจะเปิดรายสินค้า</span></label>
                    <label class="tb4-team-switch"><input type="checkbox" name="public_safe_mode" value="1" <?php checked( $options['public_safe_mode'], '1' ); ?>> <span>ใช้โหมดข้อความสาธารณะปลอดภัย</span></label>
                    <div class="tb4-team-form-row">
                        <p><label for="default_cta_label">ข้อความปุ่มหลัก</label><input type="text" id="default_cta_label" name="default_cta_label" value="<?php echo esc_attr( $options['default_cta_label'] ); ?>"></p>
                        <p><label for="default_affiliate_link_label">ข้อความปุ่มพาร์ทเนอร์</label><input type="text" id="default_affiliate_link_label" name="default_affiliate_link_label" value="<?php echo esc_attr( $options['default_affiliate_link_label'] ); ?>"></p>
                    </div>
                    <div class="tb4-team-form-row">
                        <p><label for="default_booking_label">ข้อความปุ่ม Booking</label><input type="text" id="default_booking_label" name="default_booking_label" value="<?php echo esc_attr( $options['default_booking_label'] ); ?>"></p>
                        <p><label for="default_tracking_code">Tracking / Ref Code</label><input type="text" id="default_tracking_code" name="default_tracking_code" value="<?php echo esc_attr( $options['default_tracking_code'] ); ?>"></p>
                    </div>
                    <div class="tb4-team-form-row">
                        <p><label for="default_utm_campaign">UTM Campaign</label><input type="text" id="default_utm_campaign" name="default_utm_campaign" value="<?php echo esc_attr( $options['default_utm_campaign'] ); ?>"></p>
                        <p><label for="default_connection_note">ข้อความสถานะการเชื่อมต่อ</label><input type="text" id="default_connection_note" name="default_connection_note" value="<?php echo esc_attr( $options['default_connection_note'] ); ?>"></p>
                    </div>
                    <p><label for="default_affiliate_disclosure">ข้อความเปิดเผย Affiliate</label><textarea id="default_affiliate_disclosure" name="default_affiliate_disclosure"><?php echo esc_textarea( $options['default_affiliate_disclosure'] ); ?></textarea></p>
                    <p><label for="default_supported_sites">เว็บ/บริการที่รองรับเป็นค่าเริ่มต้น</label><textarea id="default_supported_sites" name="default_supported_sites"><?php echo esc_textarea( $options['default_supported_sites'] ); ?></textarea></p>
                    <p><label for="team_note">หมายเหตุสำหรับทีมงาน</label><textarea id="team_note" name="team_note"><?php echo esc_textarea( $options['team_note'] ); ?></textarea></p>
                    <p><button type="submit" class="button button-primary button-large">บันทึกการตั้งค่า</button></p>
                </div>
                <aside class="tb4-team-card">
                    <h2>ทางลัดทีมงาน</h2>
                    <div class="tb4-team-actions">
                        <a class="button button-primary" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=tb4_product' ) ); ?>">เพิ่มผลิตภัณฑ์</a>
                        <a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=tb4_product' ) ); ?>">ดูรายการสินค้า</a>
                        <a class="button" href="<?php echo esc_url( $archive ); ?>" target="_blank" rel="noopener">ดูหน้าสาธารณะ</a>
                    </div>
                    <h2 style="margin-top:22px;">Checklist ก่อนเผยแพร่</h2>
                    <ul class="tb4-team-checklist">
                        <li><span class="tb4-team-dot"></span><span>กรอกชื่อสินค้า คำโปรย และสถานะให้ชัดเจน</span></li>
                        <li><span class="tb4-team-dot"></span><span>ยังไม่เปิดราคา เว้นแต่ตั้งใจเปิดราคาเปรียบเทียบรายสินค้า</span></li>
                        <li><span class="tb4-team-dot"></span><span>ใส่ลิงก์พาร์ทเนอร์หรือ Booking ที่ตรวจแล้วเท่านั้น</span></li>
                        <li><span class="tb4-team-dot"></span><span>ทดสอบปุ่มดูรายละเอียด รายการที่สนใจ เปรียบเทียบ และไอคอนล็อก</span></li>
                        <li><span class="tb4-team-dot"></span><span>ตรวจข้อความสาธารณะไม่ให้เปิดเผยข้อมูลจัดการภายใน</span></li>
                    </ul>
                    <p style="margin-top:16px;color:#64748b;"><strong>สถานะพร้อมใช้งาน:</strong> <?php echo esc_html( $ready_total ); ?> รายการพร้อมใช้</p>
                </aside>
            </form>
        </div>
        <?php
    }

    private function count_products_by_meta( $meta_key, $meta_value = '1' ) {
        $query = new WP_Query( [
            'post_type' => 'tb4_product',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_query' => [ [ 'key' => $meta_key, 'value' => $meta_value ] ],
        ] );
        return (int) $query->found_posts;
    }

    public function render_admin_page() {
        $archive = get_post_type_archive_link( 'tb4_product' ) ?: home_url( '/products/' );
        $count = wp_count_posts( 'tb4_product' );
        $published = isset( $count->publish ) ? (int) $count->publish : 0;
        echo '<div class="wrap">';
        echo '<h1>Thinkb4do Products</h1>';
        echo '<p><strong>Settings | โดย Thinkb4do | ดูรายละเอียด</strong></p>';
        echo '<div style="background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:18px;max-width:1080px;">';
        echo '<h2>สถานะระบบผลิตภัณฑ์</h2>';
        echo '<p>เวอร์ชันนี้เพิ่มโหมดป้องกันการเปิดเผยข้อมูลระบบบนหน้าสาธารณะ พร้อมหน้าดูรายละเอียดสินค้าแบบพรีเมียม Gallery, ฟีเจอร์, สเปก, วิธีใช้งาน, FAQ, Affiliate หลายแหล่ง, Booking ภายนอก, ตารางเปรียบเทียบข้อเสนอ/ราคาแบบเปิด-ปิดได้, Trust Score, Product Fit และปุ่มซื้อ/ร้านค้าแบบไอคอนล็อกเมื่อยังไม่พร้อม</p>';
        echo '<ul style="list-style:disc;margin-left:20px;">';
        echo '<li>จำนวนผลิตภัณฑ์ที่เผยแพร่: <strong>' . esc_html( $published ) . '</strong></li>';
        echo '<li>ราคา: <strong>ค่าเริ่มต้นยังไม่แสดง</strong> แต่รองรับการเปิดราคาเปรียบเทียบรายพาร์ทเนอร์</li>';
        echo '<li>Affiliate: <strong>รองรับลิงก์แนะนำจากหลายเว็บ พาร์ทเนอร์ภายนอก Direct Links และข้อความเปิดเผยที่เหมาะสม</strong></li>';
        echo '<li>Booking Connect: <strong>รองรับลิงก์จอง/นัดหมายจากภายนอก Direct Links และ Feed ภายนอก</strong></li>';
        echo '<li>Partner Connect: <strong>รองรับ JSON/CSV/XML Feed พร้อม cache และ redirect tracking</strong></li>';
        echo '<li>Compare: <strong>มีระบบเลือกสินค้าเพื่อเปรียบเทียบในหน้าเว็บ</strong></li>';
        echo '<li>Public Safe Mode: <strong>ซ่อนคำอ้างอิงระบบภายในบนหน้าสาธารณะ</strong></li>';
        echo '</ul>';
        echo '<p><a class="button button-primary" href="' . esc_url( admin_url( 'post-new.php?post_type=tb4_product' ) ) . '">เพิ่มผลิตภัณฑ์</a> <a class="button" href="' . esc_url( $archive ) . '" target="_blank" rel="noopener">ดูหน้าผลิตภัณฑ์</a></p>';
        echo '</div>';
        echo '<div style="margin-top:16px;background:#f8fafc;border:1px solid #dbe3ea;border-radius:12px;padding:18px;max-width:1080px;">';
        echo '<h2>แนวทางป้องกันการก่อกวน</h2>';
        echo '<p>หน้าสาธารณะจะแสดงเฉพาะข้อมูลที่ลูกค้าควรรู้ เช่น ประโยชน์ ฟีเจอร์ สถานะ ราคาแบบเปิด-ปิด และพาร์ทเนอร์ โดยไม่เปิดเผยโครงสร้างระบบหรือข้อมูลจัดการภายใน</p>';
        echo '</div>';
        echo '</div>';
    }

    public function admin_columns( $columns ) {
        unset( $columns['date'] );
        $columns['tb4_type'] = 'ประเภท';
        $columns['tb4_status'] = 'สถานะ';
        $columns['tb4_featured'] = 'แนะนำ';
        $columns['tb4_connect'] = 'Affiliate / Booking';
        $columns['date'] = 'วันที่';
        return $columns;
    }

    public function admin_column_content( $column, $post_id ) {
        if ( 'tb4_type' === $column ) {
            echo esc_html( $this->label_for( 'type', get_post_meta( $post_id, '_tb4_product_type', true ) ) );
        }
        if ( 'tb4_status' === $column ) {
            echo esc_html( $this->label_for( 'status', get_post_meta( $post_id, '_tb4_product_status', true ) ) );
        }
        if ( 'tb4_featured' === $column ) {
            echo get_post_meta( $post_id, '_tb4_product_featured', true ) ? '★' : '—';
        }
        if ( 'tb4_connect' === $column ) {
            $affiliate = get_post_meta( $post_id, '_tb4_product_affiliate_enabled', true ) === '1' ? 'Affiliate ✓' : 'Affiliate —';
            $booking = get_post_meta( $post_id, '_tb4_product_booking_enabled', true ) === '1' ? 'Booking ✓' : 'Booking —';
            echo esc_html( $affiliate . ' / ' . $booking );
        }
    }

    public function shortcode_products( $atts = [] ) {
        $this->enqueue_assets();
        $atts = shortcode_atts( [ 'limit' => 12, 'featured' => '', 'layout' => 'archive', 'show_sidebar' => '1' ], $atts, 'thinkb4do_products' );
        $limit = max( 1, min( 60, (int) $atts['limit'] ) );
        $search = isset( $_GET['tb4_product_search'] ) ? sanitize_text_field( wp_unslash( $_GET['tb4_product_search'] ) ) : '';
        $cat = isset( $_GET['tb4_product_cat'] ) ? sanitize_text_field( wp_unslash( $_GET['tb4_product_cat'] ) ) : '';
        $status = isset( $_GET['tb4_product_status'] ) ? sanitize_text_field( wp_unslash( $_GET['tb4_product_status'] ) ) : '';
        $type = isset( $_GET['tb4_product_type'] ) ? sanitize_text_field( wp_unslash( $_GET['tb4_product_type'] ) ) : '';

        $args = [
            'post_type'      => 'tb4_product',
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            's'              => $search,
        ];

        $meta_query = [];
        if ( '1' === $atts['featured'] ) {
            $meta_query[] = [ 'key' => '_tb4_product_featured', 'value' => '1' ];
        }
        if ( $status ) {
            $meta_query[] = [ 'key' => '_tb4_product_status', 'value' => $status ];
        }
        if ( $type ) {
            $meta_query[] = [ 'key' => '_tb4_product_type', 'value' => $type ];
        }
        if ( $meta_query ) {
            $args['meta_query'] = $meta_query;
        }
        if ( $cat ) {
            $args['tax_query'] = [[ 'taxonomy' => 'tb4_product_cat', 'field' => 'slug', 'terms' => $cat ]];
        }

        $query = new WP_Query( $args );
        $terms = get_terms( [ 'taxonomy' => 'tb4_product_cat', 'hide_empty' => false ] );
        $archive = get_post_type_archive_link( 'tb4_product' ) ?: home_url( '/products/' );
        $products = [];
        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $products[] = $this->product_data( get_the_ID() );
            }
            wp_reset_postdata();
        }
        if ( empty( $products ) && ! $search && ! $cat && ! $status && ! $type ) {
            $products = $this->demo_products();
        }

        $published_count = wp_count_posts( 'tb4_product' );
        $published_total = isset( $published_count->publish ) ? (int) $published_count->publish : 0;
        $display_total = $published_total > 0 ? $published_total : count( $products );
        $term_total = is_wp_error( $terms ) ? 0 : count( $terms );
        $ready_total = $this->count_products_by_status( 'ready' );

        ob_start();
        ?>
        <section class="tb4-products-shell" data-tb4-products-shell>
            <div class="tb4-products-topbar">
                <div class="tb4-products-breadcrumb">หน้าหลัก <span>›</span> ผลิตภัณฑ์</div>
                <div class="tb4-products-top-actions">
                    <button type="button" class="tb4-products-soft-btn tb4-products-action-pill" data-tb4-scroll-compare aria-label="เปิดตารางเปรียบเทียบ">
                        <span class="tb4-products-btn-icon" aria-hidden="true">⇄</span><span class="tb4-products-btn-text">เปรียบเทียบ</span><strong data-tb4-compare-count>0</strong>
                    </button>
                    <button type="button" class="tb4-products-soft-btn tb4-products-action-pill" data-tb4-scroll-saved aria-label="เปิดรายการที่สนใจ">
                        <span class="tb4-products-btn-icon" aria-hidden="true">♡</span><span class="tb4-products-btn-text">รายการที่สนใจ</span><strong data-tb4-wishlist-count>0</strong>
                    </button>
                    <button type="button" class="tb4-products-icon-btn is-active" data-tb4-view="grid" aria-label="แสดงแบบการ์ด"><span aria-hidden="true">▦</span></button>
                    <button type="button" class="tb4-products-icon-btn" data-tb4-view="list" aria-label="แสดงแบบรายการ"><span aria-hidden="true">☷</span></button>
                </div>
            </div>

            <header class="tb4-products-hero">
                <div class="tb4-products-hero__copy">
                    <span class="tb4-products-kicker">Thinkb4do Product Portal</span>
                    <h1>ผลิตภัณฑ์ Thinkb4do สำหรับสร้างงานให้เป็นระบบขึ้น</h1>
                    <p>รวมเครื่องมือ ระบบเสริม ระบบ AI เทมเพลต และบริการดิจิทัลของ Thinkb4do พร้อมระบบ Affiliate จากหลายเว็บ พาร์ทเนอร์ภายนอก Booking ภายนอก เปรียบเทียบสินค้า และเปรียบเทียบข้อเสนอแบบเปิด-ปิดราคาได้</p>
                    <form class="tb4-products-search" action="<?php echo esc_url( $archive ); ?>" method="get">
                        <input type="search" name="tb4_product_search" value="<?php echo esc_attr( $search ); ?>" data-tb4-product-search placeholder="ค้นหาเครื่องมือ ระบบ AI Template VPS สินค้า...">
                        <button type="submit" aria-label="ค้นหา">ค้นหา</button>
                    </form>
                </div>
                <aside class="tb4-products-hero-card">
                    <span class="tb4-products-hero-card__label">ภาพรวม</span>
                    <strong><?php echo esc_html( $display_total ); ?></strong>
                    <span>ผลิตภัณฑ์ในระบบ</span>
                    <small>Affiliate Connect · Booking Ready · Compare · ค่าเริ่มต้นยังไม่แสดงราคา</small>
                </aside>
            </header>

            <nav class="tb4-products-filter-row" aria-label="ตัวกรองผลิตภัณฑ์">
                <a class="<?php echo ( ! $cat && ! $status && ! $type ) ? 'is-active' : ''; ?>" href="<?php echo esc_url( $archive ); ?>"><span class="tb4-products-filter-icon" aria-hidden="true">⌘</span><span>ทั้งหมด</span><small><?php echo esc_html( $display_total ); ?></small></a>
                <?php foreach ( $this->type_labels as $type_key => $type_label ) : ?>
                    <a class="<?php echo $type === $type_key ? 'is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'tb4_product_type', $type_key, $archive ) ); ?>"><span class="tb4-products-filter-icon" aria-hidden="true"><?php echo esc_html( $this->type_icon( $type_key ) ); ?></span><span><?php echo esc_html( $type_label ); ?></span></a>
                <?php endforeach; ?>
                <a class="<?php echo 'ready' === $status ? 'is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'tb4_product_status', 'ready', $archive ) ); ?>"><span class="tb4-products-filter-icon" aria-hidden="true">✓</span><span>พร้อมใช้</span></a>
                <a class="<?php echo 'beta' === $status ? 'is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'tb4_product_status', 'beta', $archive ) ); ?>"><span class="tb4-products-filter-icon" aria-hidden="true">β</span><span>Beta</span></a>
            </nav>

            <form class="tb4-products-controlbar" action="<?php echo esc_url( $archive ); ?>" method="get">
                <label>หมวดหมู่
                    <select name="tb4_product_cat">
                        <option value="">ทั้งหมด</option>
                        <?php if ( ! is_wp_error( $terms ) ) : foreach ( $terms as $term ) : ?>
                            <option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $cat, $term->slug ); ?>><?php echo esc_html( $term->name ); ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                </label>
                <label>ประเภท
                    <select name="tb4_product_type">
                        <option value="">ทั้งหมด</option>
                        <?php foreach ( $this->type_labels as $type_key => $type_label ) : ?>
                            <option value="<?php echo esc_attr( $type_key ); ?>" <?php selected( $type, $type_key ); ?>><?php echo esc_html( $type_label ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>สถานะ
                    <select name="tb4_product_status">
                        <option value="">ทั้งหมด</option>
                        <?php foreach ( $this->status_labels as $status_key => $status_label ) : ?>
                            <option value="<?php echo esc_attr( $status_key ); ?>" <?php selected( $status, $status_key ); ?>><?php echo esc_html( $status_label ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button type="submit">ตัวกรอง</button>
            </form>

            <div class="tb4-products-layout <?php echo '1' === $atts['show_sidebar'] ? '' : 'tb4-products-layout--full'; ?>">
                <div class="tb4-products-main">
                    <div class="tb4-products-section-head">
                        <div>
                            <span>สินค้าแนะนำสำหรับคุณ</span>
                            <h2>เลือกเครื่องมือที่เหมาะกับงานตอนนี้</h2>
                        </div>
                        <a href="<?php echo esc_url( $archive ); ?>">ดูทั้งหมด →</a>
                    </div>
                    <?php if ( $products ) : ?>
                    <div class="tb4-products-grid" data-tb4-products-grid>
                        <?php foreach ( $products as $item ) : echo $this->render_product_card( $item ); endforeach; ?>
                    </div>
                    <?php else : ?>
                    <div class="tb4-products-empty">
                        <h2>ไม่พบผลิตภัณฑ์ที่ตรงกับคำค้นหา</h2>
                        <p>ลองล้างตัวกรองหรือค้นหาด้วยคำอื่น</p>
                        <a href="<?php echo esc_url( $archive ); ?>">กลับไปดูทั้งหมด</a>
                    </div>
                    <?php endif; ?>

                    <div class="tb4-products-trustbar">
                        <div><strong>ใช้งานได้ทันที</strong><span>จัดการผ่านระบบกลาง</span></div>
                        <div><strong>อัปเดตสม่ำเสมอ</strong><span>พร้อมต่อยอดตามระบบ</span></div>
                        <div><strong>Partner Connect</strong><span>เชื่อมข้อเสนอจากพาร์ทเนอร์ภายนอก</span></div>
                        <div><strong>Compare Ready</strong><span>เทียบสินค้า/ข้อเสนอจากพาร์ทเนอร์</span></div>
                    </div>
                </div>

                <?php if ( '1' === $atts['show_sidebar'] ) : ?>
                <aside class="tb4-products-sidebar">
                    <div class="tb4-products-widget">
                        <div class="tb4-products-widget__head"><h3>ภาพรวมการใช้งาน</h3><a href="<?php echo esc_url( $archive ); ?>">ดูทั้งหมด →</a></div>
                        <p><span>ผลิตภัณฑ์ที่เผยแพร่</span><strong><?php echo esc_html( $display_total ); ?> รายการ</strong></p>
                        <p><span>หมวดหมู่</span><strong><?php echo esc_html( $term_total ); ?> หมวด</strong></p>
                        <p><span>พร้อมใช้งาน</span><strong><?php echo esc_html( $ready_total ?: 'กำลังจัดข้อมูล' ); ?></strong></p>
                        <p><span>ราคา</span><strong>เปิด/ปิดรายสินค้า</strong></p>
                        <p><span>พาร์ทเนอร์</span><strong>เชื่อมภายนอกได้</strong></p>
                        <p><span>Booking</span><strong>รองรับภายนอก</strong></p>
                    </div>
                    <div class="tb4-products-widget" id="tb4-products-compare-panel">
                        <div class="tb4-products-widget__head"><h3>เปรียบเทียบสินค้า</h3><span data-tb4-compare-count>0</span></div>
                        <div class="tb4-products-saved-list" data-tb4-compare-mini>ยังไม่มีสินค้าที่เลือกเปรียบเทียบ</div>
                    </div>
                    <div class="tb4-products-widget" id="tb4-products-saved-panel">
                        <div class="tb4-products-widget__head"><h3>รายการที่สนใจ</h3><span data-tb4-wishlist-count>0</span></div>
                        <div class="tb4-products-saved-list" data-tb4-saved-list>ยังไม่มีรายการที่บันทึก</div>
                    </div>
                    <div class="tb4-products-widget tb4-products-widget-soft">
                        <div class="tb4-products-widget__head"><h3>แนะนำสำหรับคุณ</h3><a href="<?php echo esc_url( $archive ); ?>">ดูทั้งหมด →</a></div>
                        <?php foreach ( array_slice( $products, 0, 3 ) as $item ) : echo $this->render_sidebar_item( $item ); endforeach; ?>
                    </div>
                </aside>
                <?php endif; ?>
            </div>
            <div class="tb4-products-compare-drawer" data-tb4-compare-drawer hidden>
                <div class="tb4-products-compare-drawer__head">
                    <div><span>Thinkb4do Compare</span><h3>ตารางเปรียบเทียบสินค้า</h3></div>
                    <button type="button" data-tb4-compare-close aria-label="ปิดตารางเปรียบเทียบ">×</button>
                </div>
                <div class="tb4-products-compare-table" data-tb4-compare-table></div>
                <div class="tb4-products-compare-drawer__foot">
                    <button type="button" data-tb4-compare-clear>ล้างรายการเปรียบเทียบ</button>
                    <small>ราคาเปรียบเทียบจะแสดงเฉพาะสินค้าที่เปิดสิทธิ์แสดงราคาไว้แล้วเท่านั้น</small>
                </div>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }


    private function public_text( $value ) {
        $value = (string) $value;
        $replacements = [
            'WordPress' => 'ระบบเว็บไซต์',
            'wordpress' => 'ระบบเว็บไซต์',
            'WP ' => 'Site ',
            ' WP' => ' Site',
            'ปลั๊กอิน' => 'ระบบเสริม',
            'Plugin' => 'Module',
            'plugin' => 'module',
            'หลังบ้าน' => 'พื้นที่จัดการ',
            'แอดมิน' => 'ทีมงาน',
            'admin' => 'team',
            'Admin' => 'Team',
            'database' => 'ข้อมูลระบบ',
            'Database' => 'ข้อมูลระบบ',
            'ฐานข้อมูล' => 'ข้อมูลระบบ',
            'server' => 'ระบบให้บริการ',
            'Server' => 'ระบบให้บริการ',
            'เซิร์ฟเวอร์' => 'ระบบให้บริการ',
            'endpoint' => 'จุดเชื่อมต่อ',
            'Endpoint' => 'จุดเชื่อมต่อ',
            'API Key' => 'รหัสเชื่อมต่อ',
            'API' => 'จุดเชื่อมต่อ',
            'api' => 'จุดเชื่อมต่อ',
            'REST' => 'ช่องทางเชื่อมต่อ',
            'Webhook' => 'ช่องทางแจ้งข้อมูล',
            'webhook' => 'ช่องทางแจ้งข้อมูล',
            'debug' => 'ตรวจสอบ',
            'Debug' => 'ตรวจสอบ',
        ];
        return str_replace( array_keys( $replacements ), array_values( $replacements ), $value );
    }

    private function product_data( $post_id ) {
        $terms = get_the_terms( $post_id, 'tb4_product_cat' );
        $cat = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : 'ผลิตภัณฑ์';
        $type = get_post_meta( $post_id, '_tb4_product_type', true ) ?: 'recommended';
        $status = get_post_meta( $post_id, '_tb4_product_status', true ) ?: 'ready';
        $tagline = get_post_meta( $post_id, '_tb4_product_tagline', true );
        return [
            'id'        => $post_id,
            'title'     => $this->public_text( get_the_title( $post_id ) ),
            'desc'      => $this->public_text( $tagline ?: ( get_the_excerpt( $post_id ) ?: wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ), 18 ) ) ),
            'url'       => get_permalink( $post_id ),
            'image'     => get_the_post_thumbnail_url( $post_id, 'medium_large' ),
            'cat'       => $this->public_text( $cat ),
            'badge'     => $this->public_text( get_post_meta( $post_id, '_tb4_product_badge', true ) ?: 'Thinkb4do' ),
            'type'      => $type,
            'typeLabel' => $this->public_text( $this->label_for( 'type', $type ) ),
            'status'    => $status,
            'statusLabel' => $this->public_text( $this->label_for( 'status', $status ) ),
            'level'     => get_post_meta( $post_id, '_tb4_product_level', true ) ?: 'pro',
            'rating'    => get_post_meta( $post_id, '_tb4_product_rating', true ) ?: '4.8',
            'downloads' => $this->public_text( get_post_meta( $post_id, '_tb4_product_downloads', true ) ?: 'กำลังเก็บสถิติ' ),
            'license'   => $this->public_text( get_post_meta( $post_id, '_tb4_product_license', true ) ?: 'ตามรายละเอียดสินค้า' ),
            'icon'      => $this->public_text( get_post_meta( $post_id, '_tb4_product_icon', true ) ?: 'T' ),
            'accent'    => get_post_meta( $post_id, '_tb4_product_accent', true ) ?: '#1E6B45',
            'demo'      => get_post_meta( $post_id, '_tb4_product_demo_url', true ),
            'action'    => get_post_meta( $post_id, '_tb4_product_purchase_url', true ),
            'cta'       => get_post_meta( $post_id, '_tb4_product_cta_label', true ) ?: $this->team_option( 'default_cta_label', 'ดูรายละเอียด' ),
            'affiliateEnabled' => get_post_meta( $post_id, '_tb4_product_affiliate_enabled', true ) === '1',
            'showPartnerPrices' => get_post_meta( $post_id, '_tb4_product_show_partner_prices', true ) === '1' && '1' !== $this->team_option( 'hide_prices_default', '1' ) ? true : get_post_meta( $post_id, '_tb4_product_show_partner_prices', true ) === '1',
            'affiliateDisclosure' => $this->public_text( get_post_meta( $post_id, '_tb4_product_affiliate_disclosure', true ) ?: $this->team_option( 'default_affiliate_disclosure', 'ลิงก์บางรายการอาจเป็นลิงก์แนะนำ โดย Thinkb4do' ) ),
            'externalAffiliateEnabled' => get_post_meta( $post_id, '_tb4_product_external_affiliate_enabled', true ) === '1',
            'affiliateLinkLabel' => $this->public_text( get_post_meta( $post_id, '_tb4_product_affiliate_link_label', true ) ?: $this->team_option( 'default_affiliate_link_label', 'ดูข้อเสนอ' ) ),
            'bestFor'   => $this->public_text( get_post_meta( $post_id, '_tb4_product_best_for', true ) ?: 'ผู้ใช้งาน Thinkb4do' ),
            'trustScore'=> get_post_meta( $post_id, '_tb4_product_trust_score', true ) ?: '95',
            'integrations' => $this->public_text( get_post_meta( $post_id, '_tb4_product_integrations', true ) ?: 'เว็บไซต์หลัก, หน้า Landing Page, ระบบสมาชิก' ),
            'compareFeatures' => $this->public_text( get_post_meta( $post_id, '_tb4_product_compare_features', true ) ?: 'ใช้งานง่าย, รองรับมือถือ, พร้อมต่อยอด' ),
            'connectionNote' => $this->public_text( get_post_meta( $post_id, '_tb4_product_connection_status_note', true ) ?: $this->team_option( 'default_connection_note', 'รองรับการเชื่อมต่อหลายแหล่ง พร้อมตรวจลิงก์ก่อนนำเสนอ' ) ),
            'bookingEnabled' => get_post_meta( $post_id, '_tb4_product_booking_enabled', true ) === '1',
            'bookingStatus' => get_post_meta( $post_id, '_tb4_product_booking_status', true ) ?: 'soon',
            'bookingLabel' => $this->public_text( get_post_meta( $post_id, '_tb4_product_booking_cta_label', true ) ?: $this->team_option( 'default_booking_label', 'จองคิว / นัดหมาย' ) ),
            'partners'  => $this->product_partners( $post_id ),
            'bookingSources' => $this->product_booking_sources( $post_id ),
        ];
    }


    private function product_manual_partners( $post_id ) {
        $partners = [];
        for ( $i = 1; $i <= 3; $i++ ) {
            $name = get_post_meta( $post_id, '_tb4_product_partner' . $i . '_name', true );
            $url = get_post_meta( $post_id, '_tb4_product_partner' . $i . '_url', true );
            $price = get_post_meta( $post_id, '_tb4_product_partner' . $i . '_price', true );
            $note = get_post_meta( $post_id, '_tb4_product_partner' . $i . '_note', true );
            if ( ! $name && ! $url ) {
                continue;
            }
            $partners[] = [
                'partnerKey' => 'manual-' . $i,
                'source' => 'manual',
                'name'  => $this->public_text( $name ?: 'Partner ' . $i ),
                'url'   => esc_url_raw( $url ),
                'price' => $this->public_text( $price ),
                'note'  => $this->public_text( $note ?: 'ดูรายละเอียดจากพาร์ทเนอร์' ),
                'badge' => 'พาร์ทเนอร์',
            ];
        }
        return $partners;
    }

    private function product_direct_affiliate_partners( $post_id ) {
        $raw = get_post_meta( $post_id, '_tb4_product_affiliate_direct_links', true );
        $lines = preg_split( '/
|
|
/', (string) $raw );
        $partners = [];
        $index = 0;
        foreach ( $lines as $line ) {
            $line = trim( $line );
            if ( '' === $line ) {
                continue;
            }
            $index++;
            $offer = null;
            if ( '{' === substr( $line, 0, 1 ) ) {
                $decoded = json_decode( $line, true );
                if ( is_array( $decoded ) ) {
                    $offer = $this->normalize_external_offer( $decoded, '' );
                }
            }
            if ( ! $offer ) {
                $parts = array_map( 'trim', explode( '|', $line ) );
                $offer = $this->normalize_external_offer( [
                    'partner' => $parts[0] ?? '',
                    'url'     => $parts[1] ?? '',
                    'note'    => $parts[2] ?? '',
                    'badge'   => $parts[3] ?? '',
                    'price'   => $parts[4] ?? '',
                ], '' );
            }
            if ( ! $offer || empty( $offer['url'] ) ) {
                continue;
            }
            $offer['source'] = 'direct';
            $offer['partnerKey'] = 'direct-' . $index;
            $offer['badge'] = $offer['badge'] ?: 'Web Connect';
            $partners[] = $offer;
        }
        return $partners;
    }

    private function product_external_partners( $post_id ) {
        if ( get_post_meta( $post_id, '_tb4_product_external_affiliate_enabled', true ) !== '1' ) {
            return [];
        }
        $feeds_raw = get_post_meta( $post_id, '_tb4_product_external_affiliate_feeds', true );
        $feeds = array_filter( array_map( 'esc_url_raw', preg_split( '/\r\n|\r|\n/', (string) $feeds_raw ) ) );
        if ( empty( $feeds ) ) {
            return [];
        }
        $keywords_raw = get_post_meta( $post_id, '_tb4_product_external_affiliate_keywords', true );
        $keywords = array_filter( array_map( 'trim', preg_split( '/,|\r\n|\r|\n/', (string) $keywords_raw ) ) );
        if ( empty( $keywords ) ) {
            $keywords = [ get_the_title( $post_id ) ];
        }
        $limit = absint( get_post_meta( $post_id, '_tb4_product_external_affiliate_limit', true ) );
        if ( $limit < 1 ) { $limit = 6; }
        if ( $limit > 20 ) { $limit = 20; }

        $cache_key = 'tb4_aff_ext_' . $post_id . '_' . md5( implode( '|', $feeds ) . '|' . implode( '|', $keywords ) . '|' . $limit );
        $cached = get_transient( $cache_key );
        if ( false !== $cached && is_array( $cached ) ) {
            return $cached;
        }

        $offers = [];
        foreach ( $feeds as $feed_url ) {
            $response = wp_remote_get( $feed_url, [
                'timeout' => 8,
                'redirection' => 3,
                'headers' => [
                    'Accept' => 'application/json,text/csv,text/plain;q=0.8,*/*;q=0.5',
                    'User-Agent' => 'Thinkb4do-Partner-Connect/2.1',
                ],
            ] );
            if ( is_wp_error( $response ) ) {
                continue;
            }
            $code = (int) wp_remote_retrieve_response_code( $response );
            if ( $code < 200 || $code >= 300 ) {
                continue;
            }
            $body = wp_remote_retrieve_body( $response );
            if ( ! $body ) {
                continue;
            }
            $parsed = $this->parse_external_affiliate_feed( $body, $feed_url );
            foreach ( $parsed as $offer ) {
                if ( ! $this->offer_matches_keywords( $offer, $keywords ) && count( $parsed ) > 1 ) {
                    continue;
                }
                $offer['source'] = 'external';
                $offer['partnerKey'] = 'external-' . ( count( $offers ) + 1 );
                $offer['badge'] = $offer['badge'] ?: 'Partner Connect';
                $offers[] = $offer;
                if ( count( $offers ) >= $limit ) {
                    break 2;
                }
            }
        }
        set_transient( $cache_key, $offers, 30 * MINUTE_IN_SECONDS );
        return $offers;
    }

    private function parse_external_affiliate_feed( $body, $feed_url = '' ) {
        $body = trim( (string) $body );
        if ( '' === $body ) {
            return [];
        }
        $rows = [];
        $json = json_decode( $body, true );
        if ( is_array( $json ) ) {
            if ( isset( $json['offers'] ) && is_array( $json['offers'] ) ) {
                $rows = $json['offers'];
            } elseif ( isset( $json['products'] ) && is_array( $json['products'] ) ) {
                $rows = $json['products'];
            } elseif ( isset( $json['items'] ) && is_array( $json['items'] ) ) {
                $rows = $json['items'];
            } elseif ( array_keys( $json ) === range( 0, count( $json ) - 1 ) ) {
                $rows = $json;
            }
        } elseif ( 0 === strpos( ltrim( $body ), '<' ) ) {
            $rows = $this->parse_external_affiliate_xml( $body );
        } else {
            $rows = $this->parse_external_affiliate_csv( $body );
        }
        $offers = [];
        foreach ( $rows as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }
            $offer = $this->normalize_external_offer( $row, $feed_url );
            if ( $offer ) {
                $offers[] = $offer;
            }
        }
        return $offers;
    }

    private function parse_external_affiliate_xml( $body ) {
        if ( ! function_exists( 'simplexml_load_string' ) ) {
            return [];
        }
        $old = libxml_use_internal_errors( true );
        $xml = simplexml_load_string( $body, 'SimpleXMLElement', LIBXML_NOCDATA );
        libxml_clear_errors();
        libxml_use_internal_errors( $old );
        if ( ! $xml ) {
            return [];
        }
        $data = json_decode( wp_json_encode( $xml ), true );
        $rows = [];
        $this->collect_external_offer_rows( $data, $rows );
        return $rows;
    }

    private function collect_external_offer_rows( $data, &$rows ) {
        if ( ! is_array( $data ) ) {
            return;
        }
        if ( $this->row_has_external_url( $data ) ) {
            $rows[] = $data;
            return;
        }
        foreach ( $data as $value ) {
            if ( is_array( $value ) ) {
                if ( array_keys( $value ) === range( 0, count( $value ) - 1 ) ) {
                    foreach ( $value as $child ) {
                        $this->collect_external_offer_rows( $child, $rows );
                    }
                } else {
                    $this->collect_external_offer_rows( $value, $rows );
                }
            }
        }
    }

    private function row_has_external_url( $row ) {
        foreach ( [ 'url', 'link', 'affiliate_url', 'deeplink', 'deep_link', 'tracking_url', 'product_url', 'destination_url', 'offer_url', 'click_url', 'purchase_url', 'booking_url', 'reserve_url' ] as $key ) {
            if ( ! empty( $row[ $key ] ) ) {
                return true;
            }
        }
        if ( ! empty( $row['link']['@attributes']['href'] ) ) {
            return true;
        }
        return false;
    }

    private function parse_external_affiliate_csv( $body ) {
        $lines = preg_split( '/\r\n|\r|\n/', trim( $body ) );
        if ( count( $lines ) < 2 ) {
            return [];
        }
        $headers = array_map( 'sanitize_key', str_getcsv( array_shift( $lines ) ) );
        $rows = [];
        foreach ( $lines as $line ) {
            if ( '' === trim( $line ) ) {
                continue;
            }
            $values = str_getcsv( $line );
            $row = [];
            foreach ( $headers as $index => $header ) {
                $row[ $header ] = $values[ $index ] ?? '';
            }
            $rows[] = $row;
        }
        return $rows;
    }

    private function normalize_external_offer( $row, $feed_url = '' ) {
        $name = $this->first_row_value( $row, [ 'partner', 'partner_name', 'merchant', 'merchant_name', 'advertiser', 'shop', 'store', 'network', 'source', 'brand', 'vendor', 'provider' ] );
        $title = $this->first_row_value( $row, [ 'title', 'name', 'product_name', 'product', 'service_name', 'item_name' ] );
        $url = $this->first_row_value( $row, [ 'url', 'link', 'affiliate_url', 'deeplink', 'deep_link', 'tracking_url', 'product_url', 'destination_url', 'offer_url', 'click_url', 'purchase_url', 'booking_url', 'reserve_url' ] );
        if ( ! $url && ! empty( $row['link']['@attributes']['href'] ) ) {
            $url = $row['link']['@attributes']['href'];
        }
        $price = $this->first_row_value( $row, [ 'price', 'sale_price', 'current_price', 'amount', 'offer', 'deal', 'value', 'price_text' ] );
        $note = $this->first_row_value( $row, [ 'note', 'description', 'summary', 'condition', 'short_description', 'subtitle', 'details' ] );
        $coupon = $this->first_row_value( $row, [ 'coupon', 'code', 'promo_code', 'voucher' ] );
        $badge = $this->first_row_value( $row, [ 'badge', 'label', 'category', 'type', 'network_type' ] );
        if ( ! $url ) {
            return null;
        }
        $name = $name ?: ( $title ?: wp_parse_url( $feed_url, PHP_URL_HOST ) );
        $note = $note ?: ( $title ?: 'ดูรายละเอียดจากพาร์ทเนอร์' );
        if ( $coupon ) {
            $note .= ' · Code: ' . $coupon;
        }
        return [
            'partnerKey' => '',
            'source' => 'external',
            'name'  => $this->public_text( sanitize_text_field( (string) $name ) ),
            'url'   => esc_url_raw( $url ),
            'price' => $this->public_text( sanitize_text_field( (string) $price ) ),
            'note'  => $this->public_text( sanitize_text_field( (string) $note ) ),
            'badge' => $this->public_text( sanitize_text_field( (string) $badge ) ),
        ];
    }

    private function first_row_value( $row, $keys ) {
        foreach ( $keys as $key ) {
            if ( isset( $row[ $key ] ) && '' !== $row[ $key ] ) {
                if ( is_array( $row[ $key ] ) ) {
                    if ( isset( $row[ $key ]['@attributes']['href'] ) ) {
                        return $row[ $key ]['@attributes']['href'];
                    }
                    if ( isset( $row[ $key ][0] ) && ! is_array( $row[ $key ][0] ) ) {
                        return $row[ $key ][0];
                    }
                    continue;
                }
                return $row[ $key ];
            }
        }
        return '';
    }

    private function offer_matches_keywords( $offer, $keywords ) {
        $haystack = mb_strtolower( implode( ' ', [ $offer['name'] ?? '', $offer['note'] ?? '', $offer['price'] ?? '', $offer['badge'] ?? '' ] ) );
        foreach ( $keywords as $keyword ) {
            $keyword = trim( mb_strtolower( (string) $keyword ) );
            if ( '' !== $keyword && false !== mb_strpos( $haystack, $keyword ) ) {
                return true;
            }
        }
        return false;
    }

    private function product_partners( $post_id ) {
        $partners = array_merge( $this->product_manual_partners( $post_id ), $this->product_direct_affiliate_partners( $post_id ), $this->product_external_partners( $post_id ) );
        $unique = [];
        $seen = [];
        foreach ( $partners as $partner ) {
            $signature = md5( strtolower( ( $partner['name'] ?? '' ) . '|' . ( $partner['url'] ?? '' ) ) );
            if ( isset( $seen[ $signature ] ) ) {
                continue;
            }
            $seen[ $signature ] = true;
            $unique[] = $partner;
        }
        return $unique;
    }

    public function public_product_partners( $post_id ) {
        return $this->product_partners( $post_id );
    }

    private function product_booking_sources( $post_id ) {
        if ( get_post_meta( $post_id, '_tb4_product_booking_enabled', true ) !== '1' ) {
            return [];
        }
        $sources = array_merge( $this->product_manual_booking_sources( $post_id ), $this->product_external_booking_sources( $post_id ) );
        $unique = [];
        $seen = [];
        foreach ( $sources as $source ) {
            $signature = md5( strtolower( ( $source['name'] ?? '' ) . '|' . ( $source['url'] ?? '' ) ) );
            if ( isset( $seen[ $signature ] ) ) {
                continue;
            }
            $seen[ $signature ] = true;
            $unique[] = $source;
        }
        return $unique;
    }

    private function product_manual_booking_sources( $post_id ) {
        $raw = get_post_meta( $post_id, '_tb4_product_booking_sources', true );
        $lines = preg_split( '/
|
|
/', (string) $raw );
        $sources = [];
        $index = 0;
        foreach ( $lines as $line ) {
            $line = trim( $line );
            if ( '' === $line ) {
                continue;
            }
            $index++;
            $source = null;
            if ( '{' === substr( $line, 0, 1 ) ) {
                $decoded = json_decode( $line, true );
                if ( is_array( $decoded ) ) {
                    $source = $this->normalize_booking_source( $decoded, '' );
                }
            }
            if ( ! $source ) {
                $parts = array_map( 'trim', explode( '|', $line ) );
                $source = $this->normalize_booking_source( [
                    'provider' => $parts[0] ?? '',
                    'booking_url' => $parts[1] ?? '',
                    'type' => $parts[2] ?? '',
                    'note' => $parts[3] ?? '',
                    'badge' => $parts[4] ?? '',
                ], '' );
            }
            if ( ! $source || empty( $source['url'] ) ) {
                continue;
            }
            $source['source'] = 'manual';
            $source['bookingKey'] = 'booking-direct-' . $index;
            $sources[] = $source;
        }
        return $sources;
    }

    private function product_external_booking_sources( $post_id ) {
        $feeds_raw = get_post_meta( $post_id, '_tb4_product_booking_feed_urls', true );
        $feeds = array_filter( array_map( 'esc_url_raw', preg_split( '/
|
|
/', (string) $feeds_raw ) ) );
        if ( empty( $feeds ) ) {
            return [];
        }
        $keywords_raw = get_post_meta( $post_id, '_tb4_product_booking_keywords', true );
        $keywords = array_filter( array_map( 'trim', preg_split( '/,|
|
|
/', (string) $keywords_raw ) ) );
        if ( empty( $keywords ) ) {
            $keywords = [ get_the_title( $post_id ) ];
        }
        $limit = absint( get_post_meta( $post_id, '_tb4_product_booking_limit', true ) );
        if ( $limit < 1 ) { $limit = 4; }
        if ( $limit > 12 ) { $limit = 12; }

        $cache_key = 'tb4_booking_ext_' . $post_id . '_' . md5( implode( '|', $feeds ) . '|' . implode( '|', $keywords ) . '|' . $limit );
        $cached = get_transient( $cache_key );
        if ( false !== $cached && is_array( $cached ) ) {
            return $cached;
        }

        $sources = [];
        foreach ( $feeds as $feed_url ) {
            $response = wp_remote_get( $feed_url, [
                'timeout' => 8,
                'redirection' => 3,
                'headers' => [
                    'Accept' => 'application/json,text/csv,application/xml,text/xml,text/plain;q=0.8,*/*;q=0.5',
                    'User-Agent' => 'Thinkb4do-Booking-Connect/2.1',
                ],
            ] );
            if ( is_wp_error( $response ) ) {
                continue;
            }
            $code = (int) wp_remote_retrieve_response_code( $response );
            if ( $code < 200 || $code >= 300 ) {
                continue;
            }
            $parsed = $this->parse_external_affiliate_feed( wp_remote_retrieve_body( $response ), $feed_url );
            foreach ( $parsed as $row ) {
                $source = $this->normalize_booking_source( $row, $feed_url );
                if ( ! $source ) {
                    continue;
                }
                if ( ! $this->offer_matches_keywords( [ 'name' => $source['name'], 'note' => $source['note'], 'badge' => $source['badge'], 'price' => $source['type'] ], $keywords ) && count( $parsed ) > 1 ) {
                    continue;
                }
                $source['source'] = 'external';
                $source['bookingKey'] = 'booking-external-' . ( count( $sources ) + 1 );
                $sources[] = $source;
                if ( count( $sources ) >= $limit ) {
                    break 2;
                }
            }
        }
        set_transient( $cache_key, $sources, 30 * MINUTE_IN_SECONDS );
        return $sources;
    }

    private function normalize_booking_source( $row, $feed_url = '' ) {
        $name = $this->first_row_value( $row, [ 'provider', 'partner', 'merchant', 'store', 'name', 'title', 'service_name', 'booking_name' ] );
        $url = $this->first_row_value( $row, [ 'booking_url', 'booking_link', 'reserve_url', 'reservation_url', 'appointment_url', 'calendar_url', 'schedule_url', 'url', 'link', 'affiliate_url', 'deeplink' ] );
        $type = $this->first_row_value( $row, [ 'booking_type', 'type', 'category', 'service_type' ] );
        $note = $this->first_row_value( $row, [ 'note', 'description', 'summary', 'details', 'condition' ] );
        $badge = $this->first_row_value( $row, [ 'badge', 'label', 'status' ] );
        if ( ! $url ) {
            return null;
        }
        return [
            'bookingKey' => '',
            'source' => 'external',
            'name' => $this->public_text( sanitize_text_field( (string) ( $name ?: wp_parse_url( $feed_url, PHP_URL_HOST ) ?: 'Booking Partner' ) ) ),
            'url' => esc_url_raw( $url ),
            'type' => $this->public_text( sanitize_text_field( (string) ( $type ?: 'Booking' ) ) ),
            'note' => $this->public_text( sanitize_text_field( (string) ( $note ?: 'เลือกเวลา/ช่องทางจากผู้ให้บริการภายนอก' ) ) ),
            'badge' => $this->public_text( sanitize_text_field( (string) ( $badge ?: 'Booking Connect' ) ) ),
        ];
    }

    public function public_product_booking_sources( $post_id ) {
        return $this->product_booking_sources( $post_id );
    }

    public function public_booking_out_url( $post_id, $booking_key ) {
        return $this->booking_out_url( $post_id, $booking_key );
    }

    public function public_affiliate_out_url( $post_id, $partner_key ) {
        return $this->affiliate_out_url( $post_id, $partner_key );
    }

    private function booking_out_url( $post_id, $booking_key ) {
        return add_query_arg( [
            'tb4_booking_out' => absint( $post_id ),
            'tb4_booking' => sanitize_key( $booking_key ),
        ], home_url( '/' ) );
    }

    private function affiliate_out_url( $post_id, $partner_key ) {
        return add_query_arg( [
            'tb4_aff_out' => absint( $post_id ),
            'tb4_partner' => sanitize_key( $partner_key ),
        ], home_url( '/' ) );
    }

    public function affiliate_redirect() {
        if ( ! empty( $_GET['tb4_booking_out'] ) && ! empty( $_GET['tb4_booking'] ) ) {
            $this->booking_redirect();
            return;
        }
        if ( empty( $_GET['tb4_aff_out'] ) || empty( $_GET['tb4_partner'] ) ) {
            return;
        }
        $post_id = absint( $_GET['tb4_aff_out'] );
        $partner_key = sanitize_key( wp_unslash( $_GET['tb4_partner'] ) );
        if ( ! $post_id || 'tb4_product' !== get_post_type( $post_id ) ) {
            return;
        }
        $partners = $this->product_partners( $post_id );
        foreach ( $partners as $partner ) {
            if ( ( $partner['partnerKey'] ?? '' ) !== $partner_key || empty( $partner['url'] ) ) {
                continue;
            }
            $target = $this->build_affiliate_url( $partner['url'], $post_id, 'affiliate' );
            if ( ! $target ) {
                return;
            }
            $this->record_affiliate_click( $post_id, $partner_key );
            wp_redirect( $target, 302, 'Thinkb4do Products' );
            exit;
        }
    }

    private function booking_redirect() {
        $post_id = absint( $_GET['tb4_booking_out'] );
        $booking_key = sanitize_key( wp_unslash( $_GET['tb4_booking'] ) );
        if ( ! $post_id || 'tb4_product' !== get_post_type( $post_id ) ) {
            return;
        }
        $sources = $this->product_booking_sources( $post_id );
        foreach ( $sources as $source ) {
            if ( ( $source['bookingKey'] ?? '' ) !== $booking_key || empty( $source['url'] ) ) {
                continue;
            }
            $target = $this->build_affiliate_url( $source['url'], $post_id, 'booking' );
            if ( ! $target ) {
                return;
            }
            $this->record_booking_click( $post_id, $booking_key );
            wp_redirect( $target, 302, 'Thinkb4do Products' );
            exit;
        }
    }

    private function build_affiliate_url( $url, $post_id, $medium = 'affiliate' ) {
        $url = esc_url_raw( $url );
        if ( ! $url ) {
            return '';
        }
        $campaign = sanitize_title( get_post_meta( $post_id, '_tb4_product_affiliate_utm_campaign', true ) ?: $this->team_option( 'default_utm_campaign', get_the_title( $post_id ) ) );
        $tracking = sanitize_text_field( get_post_meta( $post_id, '_tb4_product_affiliate_tracking_code', true ) ?: $this->team_option( 'default_tracking_code', '' ) );
        $args = [
            'utm_source' => 'thinkb4do',
            'utm_medium' => sanitize_key( $medium ?: 'affiliate' ),
            'utm_campaign' => $campaign ?: 'product',
        ];
        if ( $tracking ) {
            $args['ref'] = $tracking;
        }
        return esc_url_raw( add_query_arg( $args, $url ) );
    }

    private function record_affiliate_click( $post_id, $partner_key ) {
        $clicks = get_post_meta( $post_id, '_tb4_product_affiliate_clicks', true );
        if ( ! is_array( $clicks ) ) {
            $clicks = [];
        }
        $today = gmdate( 'Y-m-d' );
        if ( empty( $clicks[ $partner_key ] ) || ! is_array( $clicks[ $partner_key ] ) ) {
            $clicks[ $partner_key ] = [ 'total' => 0, 'daily' => [] ];
        }
        $clicks[ $partner_key ]['total'] = absint( $clicks[ $partner_key ]['total'] ?? 0 ) + 1;
        $clicks[ $partner_key ]['daily'][ $today ] = absint( $clicks[ $partner_key ]['daily'][ $today ] ?? 0 ) + 1;
        update_post_meta( $post_id, '_tb4_product_affiliate_clicks', $clicks );
    }

    private function record_booking_click( $post_id, $booking_key ) {
        $clicks = get_post_meta( $post_id, '_tb4_product_booking_clicks', true );
        if ( ! is_array( $clicks ) ) {
            $clicks = [];
        }
        $today = gmdate( 'Y-m-d' );
        if ( empty( $clicks[ $booking_key ] ) || ! is_array( $clicks[ $booking_key ] ) ) {
            $clicks[ $booking_key ] = [ 'total' => 0, 'daily' => [] ];
        }
        $clicks[ $booking_key ]['total'] = absint( $clicks[ $booking_key ]['total'] ?? 0 ) + 1;
        $clicks[ $booking_key ]['daily'][ $today ] = absint( $clicks[ $booking_key ]['daily'][ $today ] ?? 0 ) + 1;
        update_post_meta( $post_id, '_tb4_product_booking_clicks', $clicks );
    }

    private function partner_price_label( $item ) {
        $partners = $item['partners'] ?? [];
        if ( ( empty( $item['affiliateEnabled'] ) && empty( $item['externalAffiliateEnabled'] ) ) || empty( $partners ) ) {
            return 'ยังไม่มีพาร์ทเนอร์';
        }
        if ( empty( $item['showPartnerPrices'] ) ) {
            return 'ซ่อนราคาไว้';
        }
        foreach ( $partners as $partner ) {
            if ( ! empty( $partner['price'] ) ) {
                return 'มีราคาเปรียบเทียบ';
            }
        }
        return 'รอข้อมูลราคา';
    }

    private function demo_products() {
        return [
            [ 'id'=>0,'title'=>'AiRA Suite Command Center','desc'=>'ระบบควบคุมและจัดการงาน Thinkb4do แบบรวมศูนย์','url'=>'#','image'=>'','cat'=>'AI Tools','badge'=>'ตัวอย่าง','type'=>'ai-tool','typeLabel'=>'AI Tools','status'=>'beta','statusLabel'=>'Beta','level'=>'studio','rating'=>'4.9','downloads'=>'กำลังเตรียมข้อมูล','license'=>'สำหรับสมาชิก/ทีมงาน','icon'=>'AI','accent'=>'#042f2e','demo'=>'','action'=>'','cta'=>'สอบถาม / ขอรายละเอียด','isPlaceholder'=>true ],
            [ 'id'=>0,'title'=>'AI Writer Pro','desc'=>'เครื่องมือเขียนคอนเทนต์และเรียบเรียงข้อความสำหรับงานจริง','url'=>'#','image'=>'','cat'=>'AI Tools','badge'=>'ตัวอย่าง','type'=>'ai-tool','typeLabel'=>'AI Tools','status'=>'beta','statusLabel'=>'Beta','level'=>'pro','rating'=>'4.8','downloads'=>'กำลังเตรียมข้อมูล','license'=>'ตามรายละเอียดสินค้า','icon'=>'WR','accent'=>'#3b0764','demo'=>'','action'=>'','cta'=>'สอบถาม / ขอรายละเอียด','isPlaceholder'=>true ],
            [ 'id'=>0,'title'=>'SEO Booster Pro','desc'=>'ระบบเสริมช่วยจัดโครง SEO และปรับเนื้อหาให้อ่านง่าย','url'=>'#','image'=>'','cat'=>'ระบบเสริม','badge'=>'ตัวอย่าง','type'=>'plugin','typeLabel'=>'ระบบเสริม','status'=>'ready','statusLabel'=>'พร้อมใช้','level'=>'pro','rating'=>'4.9','downloads'=>'กำลังเตรียมข้อมูล','license'=>'ตามรายละเอียดสินค้า','icon'=>'SO','accent'=>'#083344','demo'=>'','action'=>'','cta'=>'สอบถาม / ขอรายละเอียด','isPlaceholder'=>true ],
            [ 'id'=>0,'title'=>'Site Speed Up','desc'=>'โมดูลเพิ่มความเร็วเว็บไซต์และลดความหน่วง','url'=>'#','image'=>'','cat'=>'ระบบเว็บไซต์','badge'=>'ตัวอย่าง','type'=>'wordpress','typeLabel'=>'ระบบเว็บไซต์','status'=>'ready','statusLabel'=>'พร้อมใช้','level'=>'pro','rating'=>'4.7','downloads'=>'กำลังเตรียมข้อมูล','license'=>'ตามรายละเอียดสินค้า','icon'=>'SS','accent'=>'#064e3b','demo'=>'','action'=>'','cta'=>'สอบถาม / ขอรายละเอียด','isPlaceholder'=>true ],
            [ 'id'=>0,'title'=>'Backup Master','desc'=>'ระบบสำรองข้อมูลและเตรียมความพร้อมก่อนอัปเกรด','url'=>'#','image'=>'','cat'=>'System','badge'=>'ตัวอย่าง','type'=>'system','typeLabel'=>'System','status'=>'beta','statusLabel'=>'Beta','level'=>'starter','rating'=>'4.9','downloads'=>'กำลังเตรียมข้อมูล','license'=>'ตามรายละเอียดสินค้า','icon'=>'BK','accent'=>'#f97316','demo'=>'','action'=>'','cta'=>'สอบถาม / ขอรายละเอียด','isPlaceholder'=>true ],
            [ 'id'=>0,'title'=>'Template Starter','desc'=>'ชุดเทมเพลตเริ่มต้นสำหรับสร้างหน้าเว็บให้เร็วขึ้น','url'=>'#','image'=>'','cat'=>'Template','badge'=>'ตัวอย่าง','type'=>'template','typeLabel'=>'Template','status'=>'ready','statusLabel'=>'พร้อมใช้','level'=>'starter','rating'=>'4.6','downloads'=>'กำลังเตรียมข้อมูล','license'=>'ตามรายละเอียดสินค้า','icon'=>'TP','accent'=>'#1d4ed8','demo'=>'','action'=>'','cta'=>'สอบถาม / ขอรายละเอียด','isPlaceholder'=>true ],
        ];
    }

    private function render_product_card( $item ) {
        $item = wp_parse_args( $item, [
            'affiliateEnabled' => false,
            'showPartnerPrices' => false,
            'affiliateDisclosure' => 'ลิงก์บางรายการอาจเป็นลิงก์แนะนำ โดย Thinkb4do',
            'externalAffiliateEnabled' => false,
            'affiliateLinkLabel' => 'ดูข้อเสนอ',
            'bestFor' => 'ผู้ใช้งาน Thinkb4do',
            'trustScore' => '95',
            'integrations' => 'เว็บไซต์หลัก, หน้า Landing Page, ระบบสมาชิก',
            'compareFeatures' => 'ใช้งานง่าย, รองรับมือถือ, พร้อมต่อยอด',
            'connectionNote' => 'รองรับการเชื่อมต่อหลายแหล่ง',
            'bookingEnabled' => false,
            'bookingStatus' => 'soon',
            'bookingLabel' => 'จองคิว / นัดหมาย',
            'bookingSources' => [],
            'partners' => [],
            'isPlaceholder' => false,
        ] );
        foreach ( [ 'title', 'desc', 'cat', 'badge', 'typeLabel', 'statusLabel', 'downloads', 'license', 'icon', 'cta', 'affiliateDisclosure', 'affiliateLinkLabel', 'bestFor', 'trustScore', 'integrations', 'compareFeatures', 'connectionNote', 'bookingLabel' ] as $safe_key ) {
            if ( isset( $item[ $safe_key ] ) ) {
                $item[ $safe_key ] = $this->public_text( $item[ $safe_key ] );
            }
        }
        $style = 'background:' . esc_attr( $item['accent'] ) . ';';
        $saved_id = $item['id'] ? (string) $item['id'] : sanitize_title( $item['title'] );
        $has_detail = ! empty( $item['url'] ) && '#' !== $item['url'];
        $action_url = $item['action'];
        $partner_count = count( $item['partners'] );
        $booking_count = count( $item['bookingSources'] );
        $is_ready = isset( $item['status'] ) && 'ready' === $item['status'];
        $is_placeholder = ! empty( $item['isPlaceholder'] );
        $can_purchase = $is_ready && ! $is_placeholder && ! empty( $action_url );
        $price_label = $this->partner_price_label( $item );
        $compare_price = ( ! empty( $item['showPartnerPrices'] ) && $partner_count ) ? $price_label : 'ยังไม่แสดงราคา';
        $card_classes = 'tb4-product-card' . ( $has_detail ? '' : ' is-detail-locked' ) . ( $can_purchase ? '' : ' is-action-locked' );
        $card_interaction_attrs = $has_detail ? 'tabindex="0" role="link"' : 'tabindex="-1" role="article"';
        ob_start();
        ?>
        <article class="<?php echo esc_attr( $card_classes ); ?>" <?php echo $card_interaction_attrs; ?> aria-label="<?php echo esc_attr( $has_detail ? 'ดูรายละเอียดสินค้า ' . $item['title'] : 'สินค้ากำลังเตรียมรายละเอียด ' . $item['title'] ); ?>" data-tb4-detail-url="<?php echo esc_url( $has_detail ? $item['url'] : '#' ); ?>" data-title="<?php echo esc_attr( strtolower( $item['title'] . ' ' . $item['desc'] . ' ' . $item['cat'] . ' ' . $item['typeLabel'] . ' ' . $item['statusLabel'] . ' ' . $item['bestFor'] ) ); ?>" data-cat="<?php echo esc_attr( $item['cat'] ); ?>" data-status="<?php echo esc_attr( $item['status'] ); ?>" data-saved-title="<?php echo esc_attr( $item['title'] ); ?>" data-saved-url="<?php echo esc_url( $has_detail ? $item['url'] : '#' ); ?>" data-compare-title="<?php echo esc_attr( $item['title'] ); ?>" data-compare-url="<?php echo esc_url( $has_detail ? $item['url'] : '#' ); ?>" data-compare-type="<?php echo esc_attr( $item['typeLabel'] ); ?>" data-compare-status="<?php echo esc_attr( $item['statusLabel'] ); ?>" data-compare-rating="<?php echo esc_attr( $item['rating'] ); ?>" data-compare-license="<?php echo esc_attr( $item['license'] ); ?>" data-compare-fit="<?php echo esc_attr( $item['bestFor'] ); ?>" data-compare-trust="<?php echo esc_attr( $item['trustScore'] ); ?>" data-compare-price="<?php echo esc_attr( $compare_price ); ?>">
            <?php if ( $has_detail ) : ?>
            <a class="tb4-product-card__visual" href="<?php echo esc_url( $item['url'] ); ?>" style="<?php echo esc_attr( $style ); ?>">
            <?php else : ?>
            <div class="tb4-product-card__visual" style="<?php echo esc_attr( $style ); ?>" aria-hidden="true">
            <?php endif; ?>
                <span class="tb4-product-card__badge"><?php echo esc_html( $item['badge'] ); ?></span>
                <?php if ( $item['image'] ) : ?>
                    <img src="<?php echo esc_url( $item['image'] ); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>" loading="lazy" decoding="async">
                <?php else : ?>
                    <span class="tb4-product-card__orb" aria-hidden="true"></span>
                    <strong><?php echo esc_html( $item['icon'] ); ?></strong>
                <?php endif; ?>
            <?php if ( $has_detail ) : ?>
            </a>
            <?php else : ?>
            </div>
            <?php endif; ?>
            <div class="tb4-product-card__body">
                <p class="tb4-product-card__cat"><?php echo esc_html( $item['cat'] ); ?></p>
                <h2>
                    <?php if ( $has_detail ) : ?>
                        <a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a>
                    <?php else : ?>
                        <span><?php echo esc_html( $item['title'] ); ?></span>
                    <?php endif; ?>
                </h2>
                <p class="tb4-product-card__desc"><?php echo esc_html( $item['desc'] ); ?></p>
                <div class="tb4-product-card__meta">
                    <span>★ <?php echo esc_html( $item['rating'] ); ?></span>
                    <em><?php echo esc_html( $item['statusLabel'] ); ?></em>
                    <small><?php echo esc_html( $item['typeLabel'] ); ?></small>
                </div>
                <?php if ( ( ! empty( $item['affiliateEnabled'] ) || ! empty( $item['externalAffiliateEnabled'] ) ) && $partner_count ) : ?>
                <div class="tb4-product-card__affiliate">
                    <span><?php echo ! empty( $item['externalAffiliateEnabled'] ) ? esc_html__( 'Partner Connect', 'thinkb4do-products' ) : esc_html__( 'Affiliate', 'thinkb4do-products' ); ?></span>
                    <strong><?php echo esc_html( $partner_count ); ?> แหล่ง</strong>
                    <em><?php echo esc_html( $price_label ); ?></em>
                </div>
                <?php endif; ?>
                <?php if ( ! empty( $item['bookingEnabled'] ) ) : ?>
                <div class="tb4-product-card__affiliate tb4-product-card__booking">
                    <span>Booking Connect</span>
                    <strong><?php echo esc_html( $booking_count ); ?> ช่องทาง</strong>
                    <em><?php echo esc_html( $booking_count ? 'รองรับการนัดหมายภายนอก' : 'รอช่องทางจอง' ); ?></em>
                </div>
                <?php endif; ?>
                <div class="tb4-product-card__actions">
                    <?php if ( $has_detail ) : ?>
                        <a class="tb4-product-card__detail-btn" href="<?php echo esc_url( $item['url'] ); ?>">ดูรายละเอียดสินค้า</a>
                    <?php else : ?>
                        <span class="tb4-product-card__detail-btn is-disabled" data-tb4-no-card-click aria-disabled="true">รอข้อมูลรายละเอียด</span>
                    <?php endif; ?>
                    <?php if ( $can_purchase ) : ?>
                        <a class="is-secondary" href="<?php echo esc_url( $action_url ); ?>"><?php echo esc_html( $item['cta'] ); ?></a>
                    <?php else : ?>
                        <span class="tb4-product-card__locked-action tb4-product-card__locked-icon" data-tb4-no-card-click aria-disabled="true" role="img" aria-label="ยังไม่เปิดให้ซื้อ" title="ยังไม่เปิดให้ซื้อ"><span class="tb4-icon-lock" aria-hidden="true"></span></span>
                    <?php endif; ?>
                    <?php if ( $item['demo'] ) : ?><a class="is-secondary" href="<?php echo esc_url( $item['demo'] ); ?>">ตัวอย่าง</a><?php endif; ?>
                    <button type="button" data-tb4-compare="<?php echo esc_attr( $saved_id ); ?>" aria-label="เพิ่มสินค้าเพื่อเปรียบเทียบ">⇄</button>
                    <button type="button" data-tb4-wishlist="<?php echo esc_attr( $saved_id ); ?>" aria-label="บันทึกผลิตภัณฑ์">♡</button>
                </div>
            </div>
        </article>
        <?php
        return ob_get_clean();
    }

    private function render_sidebar_item( $item ) {
        ob_start();
        ?>
        <a class="tb4-products-mini" href="<?php echo esc_url( $item['url'] ); ?>">
            <span style="background:<?php echo esc_attr( $item['accent'] ); ?>;"><?php echo esc_html( $item['icon'] ); ?></span>
            <strong><?php echo esc_html( $item['title'] ); ?></strong>
            <em><?php echo esc_html( $item['statusLabel'] ); ?></em>
        </a>
        <?php
        return ob_get_clean();
    }

    private function type_icon( $key ) {
        $icons = [
            'recommended' => '☆',
            'plugin'      => '◇',
            'ai-tool'     => '✦',
            'wordpress'   => '▣',
            'template'    => '▤',
            'system'      => '▥',
            'vps-system'  => '◈',
            'service'     => '○',
        ];
        return $icons[ $key ] ?? '•';
    }

    private function label_for( $group, $key ) {
        if ( 'type' === $group ) {
            return $this->type_labels[ $key ] ?? 'ผลิตภัณฑ์';
        }
        if ( 'status' === $group ) {
            return $this->status_labels[ $key ] ?? 'พร้อมใช้';
        }
        return $key;
    }

    private function count_products_by_status( $status ) {
        $query = new WP_Query( [
            'post_type'      => 'tb4_product',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'meta_query'     => [[ 'key' => '_tb4_product_status', 'value' => $status ]],
        ] );
        return (int) $query->found_posts;
    }
}
