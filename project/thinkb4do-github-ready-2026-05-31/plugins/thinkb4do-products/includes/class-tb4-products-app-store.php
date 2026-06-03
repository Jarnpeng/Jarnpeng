<?php
/**
 * Thinkb4do Products App Store Bridge.
 * Adds a Mac-like member app shelf, checkout handoff, email order summary, and delivery/install metadata.
 *
 * @package Thinkb4doProducts
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function tb4_products_app_bridge_register_order_type() {
    register_post_type( 'tb4_app_order', [
        'labels' => [
            'name'          => 'App Orders',
            'singular_name' => 'App Order',
            'menu_name'     => 'App Orders',
            'add_new_item'  => 'เพิ่มรายการสั่งซื้อแอพ',
            'edit_item'     => 'แก้ไขรายการสั่งซื้อแอพ',
        ],
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => 'edit.php?post_type=tb4_product',
        'supports'     => [ 'title' ],
        'capability_type' => 'post',
    ] );
}
add_action( 'init', 'tb4_products_app_bridge_register_order_type', 8 );

function tb4_products_app_bridge_meta_fields() {
    return [
        '_tb4_product_app_enabled' => [
            'label' => 'เปิดเป็นแอพในพื้นที่สมาชิก',
            'type'  => 'checkbox',
            'help'  => 'เมื่อเปิด ระบบจะแสดงสินค้านี้ในเมนูผลิตภัณฑ์/แอพของสมาชิก',
        ],
        '_tb4_product_app_subtitle' => [
            'label'       => 'คำอธิบายสั้นบนหน้าแอพ',
            'type'        => 'text',
            'placeholder' => 'เช่น เพิ่มประสิทธิภาพการทำงานของสมาชิกในคลิกเดียว',
        ],
        '_tb4_product_app_run_mode' => [
            'label'   => 'รูปแบบการเปิดใช้งาน',
            'type'    => 'select',
            'options' => [
                'detail'   => 'เปิดหน้ารายละเอียดสินค้า',
                'url'      => 'เปิดลิงก์แอพภายนอก',
                'iframe'   => 'แสดงตัวอย่างแบบฝัง',
                'shortcode'=> 'แสดงผลจาก Shortcode',
                'module'   => 'รอเชื่อม Module ภายใน',
            ],
        ],
        '_tb4_product_app_run_url' => [
            'label'       => 'ลิงก์เปิดแอพ / ลิงก์ติดตั้ง / ลิงก์ตัวอย่าง',
            'type'        => 'url',
            'placeholder' => 'https://...',
        ],
        '_tb4_product_app_shortcode' => [
            'label'       => 'Shortcode สำหรับแสดงหน้าแอพ',
            'type'        => 'text',
            'placeholder' => '[your_shortcode]',
        ],
        '_tb4_product_payment_enabled' => [
            'label' => 'เปิดระบบชำระเงินสำหรับแอพนี้',
            'type'  => 'checkbox',
            'help'  => 'ใช้ร่วมกับ Checkout URL จากผู้ให้บริการชำระเงินภายนอก',
        ],
        '_tb4_product_payment_provider' => [
            'label'   => 'ช่องทางชำระเงิน',
            'type'    => 'select',
            'options' => [
                'manual'     => 'Manual / แจ้งทีมงาน',
                'stripe'     => 'Stripe Checkout',
                'paypal'     => 'PayPal',
                'bank'       => 'โอนเงิน',
                'woo'        => 'WooCommerce / ระบบร้านค้า',
                'external'   => 'External Checkout',
            ],
        ],
        '_tb4_product_checkout_url' => [
            'label'       => 'Checkout URL / Payment Link',
            'type'        => 'url',
            'placeholder' => 'https://...',
            'help'        => 'เมื่อกดซื้อ ระบบจะสร้างรายการและพาไปยังลิงก์นี้',
        ],
        '_tb4_product_invoice_email_enabled' => [
            'label' => 'ส่งบิล/ใบสรุปรายการทางอีเมล',
            'type'  => 'checkbox',
        ],
        '_tb4_product_delivery_mode' => [
            'label'   => 'รูปแบบส่งมอบหลังสั่งซื้อ',
            'type'    => 'select',
            'options' => [
                'none'    => 'ยังไม่ส่งมอบอัตโนมัติ',
                'email'   => 'ส่งไฟล์/ลิงก์ทางอีเมล',
                'instant' => 'ติดตั้ง/เปิดใช้งานทันที',
                'both'    => 'ส่งอีเมล + เปิดใช้งานทันที',
            ],
        ],
        '_tb4_product_delivery_file_url' => [
            'label'       => 'ลิงก์ไฟล์/แพ็กสำหรับส่งทางอีเมล',
            'type'        => 'url',
            'placeholder' => 'https://...',
        ],
        '_tb4_product_paid_delivery_lock' => [
            'label' => 'ล็อกไฟล์จนกว่าจะยืนยันการชำระเงิน',
            'type'  => 'checkbox',
            'help'  => 'แนะนำให้เปิด หากเป็นสินค้าขายจริง',
        ],
        '_tb4_product_auto_install_enabled' => [
            'label' => 'เปิดปุ่มติดตั้ง/เปิดใช้งานทันที',
            'type'  => 'checkbox',
        ],
        '_tb4_product_installed_label' => [
            'label'       => 'ข้อความสถานะเมื่อติดตั้ง/เปิดใช้งาน',
            'type'        => 'text',
            'placeholder' => 'พร้อมเปิดใช้งานในพื้นที่สมาชิก',
        ],
    ];
}

function tb4_products_app_bridge_add_meta_box() {
    add_meta_box( 'tb4_product_app_store_bridge', 'App Store / Payment / Delivery', 'tb4_products_app_bridge_render_meta_box', 'tb4_product', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'tb4_products_app_bridge_add_meta_box', 20 );

function tb4_products_app_bridge_render_field( $key, $field, $value ) {
    echo '<p class="tb4-app-field">';
    echo '<label for="' . esc_attr( $key ) . '">' . esc_html( $field['label'] ) . '</label>';
    if ( 'checkbox' === $field['type'] ) {
        echo '<span class="tb4-app-check"><input type="checkbox" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="1" ' . checked( $value, '1', false ) . '> เปิดใช้งาน</span>';
    } elseif ( 'select' === $field['type'] ) {
        echo '<select id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '">';
        foreach ( $field['options'] as $option_key => $option_label ) {
            echo '<option value="' . esc_attr( $option_key ) . '" ' . selected( $value, $option_key, false ) . '>' . esc_html( $option_label ) . '</option>';
        }
        echo '</select>';
    } else {
        $type = 'url' === $field['type'] ? 'url' : 'text';
        echo '<input type="' . esc_attr( $type ) . '" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" placeholder="' . esc_attr( $field['placeholder'] ?? '' ) . '">';
    }
    if ( ! empty( $field['help'] ) ) {
        echo '<span class="tb4-app-help">' . esc_html( $field['help'] ) . '</span>';
    }
    echo '</p>';
}

function tb4_products_app_bridge_render_meta_box( $post ) {
    wp_nonce_field( 'tb4_product_app_bridge_save', 'tb4_product_app_bridge_nonce' );
    $fields = tb4_products_app_bridge_meta_fields();
    ?>
    <style>
      .tb4-app-bridge-panel{background:linear-gradient(135deg,#f8fffb,#fff7ed);border:1px solid #dbe8df;border-radius:18px;padding:16px;color:#102317}
      .tb4-app-bridge-head{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;margin-bottom:12px}
      .tb4-app-bridge-head h3{margin:0;color:#123524;font-size:17px}.tb4-app-bridge-head p{margin:4px 0 0;color:#64748b}
      .tb4-app-bridge-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
      .tb4-app-field{margin:0}.tb4-app-field label{display:block;font-weight:800;margin-bottom:7px;color:#17251e}
      .tb4-app-field input[type=text],.tb4-app-field input[type=url],.tb4-app-field select{width:100%;border:1px solid #d5e1db;border-radius:12px;padding:10px 12px;background:#fff;box-shadow:none}
      .tb4-app-check{display:flex;gap:8px;align-items:center;background:#fff;border:1px solid #dbe8df;border-radius:12px;padding:10px 12px;font-weight:700;color:#145c39}
      .tb4-app-help{display:block;margin-top:6px;color:#64748b;font-size:12px;line-height:1.45}
      .tb4-app-bridge-flow{display:flex;flex-wrap:wrap;gap:8px;margin:12px 0 0}.tb4-app-bridge-flow span{border:1px solid #dbe8df;background:#fff;border-radius:999px;padding:8px 10px;font-weight:800;color:#145c39}
      @media(max-width:900px){.tb4-app-bridge-grid{grid-template-columns:1fr}.tb4-app-bridge-head{display:block}}
    </style>
    <div class="tb4-app-bridge-panel">
      <div class="tb4-app-bridge-head">
        <div><h3>ระบบแอพ / ชำระเงิน / ส่งมอบไฟล์</h3><p>ตั้งค่าสินค้าให้กลายเป็นแอพในพื้นที่สมาชิก คลิกแล้วเปิดหน้าต่างแอพแบบ Mac พร้อมรองรับ Checkout, อีเมล, ไฟล์ และปุ่มติดตั้งทันที</p></div>
        <strong>Settings | โดย Thinkb4do | ดูรายละเอียด</strong>
      </div>
      <div class="tb4-app-bridge-flow"><span>1 เลือกแอพ</span><span>2 ชำระเงิน/ขอเปิดใช้</span><span>3 ส่งบิลทางอีเมล</span><span>4 ส่งไฟล์/ติดตั้งทันที</span></div>
      <div class="tb4-app-bridge-grid">
        <?php foreach ( $fields as $key => $field ) : tb4_products_app_bridge_render_field( $key, $field, get_post_meta( $post->ID, $key, true ) ); endforeach; ?>
      </div>
    </div>
    <?php
}

function tb4_products_app_bridge_save_meta( $post_id, $post ) {
    if ( ! $post || 'tb4_product' !== $post->post_type ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( empty( $_POST['tb4_product_app_bridge_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tb4_product_app_bridge_nonce'] ) ), 'tb4_product_app_bridge_save' ) ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    foreach ( tb4_products_app_bridge_meta_fields() as $key => $field ) {
        if ( 'checkbox' === $field['type'] ) {
            update_post_meta( $post_id, $key, isset( $_POST[ $key ] ) ? '1' : '0' );
            continue;
        }
        $raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
        if ( 'url' === $field['type'] ) {
            update_post_meta( $post_id, $key, esc_url_raw( $raw ) );
        } else {
            update_post_meta( $post_id, $key, sanitize_text_field( $raw ) );
        }
    }
}
add_action( 'save_post_tb4_product', 'tb4_products_app_bridge_save_meta', 20, 2 );

function tb4_products_app_bridge_public_text( $value ) {
    $value = (string) $value;
    $replacements = [
        'WordPress' => 'ระบบเว็บไซต์',
        'wordpress' => 'ระบบเว็บไซต์',
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
        'API Key' => 'รหัสเชื่อมต่อ',
        'API' => 'จุดเชื่อมต่อ',
        'api' => 'จุดเชื่อมต่อ',
        'endpoint' => 'จุดเชื่อมต่อ',
        'Endpoint' => 'จุดเชื่อมต่อ',
        'webhook' => 'ช่องทางแจ้งข้อมูล',
        'Webhook' => 'ช่องทางแจ้งข้อมูล',
        'debug' => 'ตรวจสอบ',
        'Debug' => 'ตรวจสอบ',
    ];
    return str_replace( array_keys( $replacements ), array_values( $replacements ), $value );
}

function tb4_products_app_bridge_get_product_apps( $limit = 12 ) {
    $args = [
        'post_type'      => 'tb4_product',
        'post_status'    => 'publish',
        'posts_per_page' => max( 1, min( 48, absint( $limit ) ) ),
        'meta_query'     => [
            [ 'key' => '_tb4_product_app_enabled', 'value' => '1' ],
        ],
        'orderby' => 'menu_order date',
        'order'   => 'DESC',
    ];
    $posts = get_posts( $args );
    if ( empty( $posts ) ) {
        $posts = get_posts( [
            'post_type'      => 'tb4_product',
            'post_status'    => 'publish',
            'posts_per_page' => max( 1, min( 12, absint( $limit ) ) ),
            'orderby'        => 'date',
            'order'          => 'DESC',
        ] );
    }
    return $posts;
}

function tb4_products_app_bridge_product_payload( $post_id ) {
    $post_id = absint( $post_id );
    $type = get_post_meta( $post_id, '_tb4_product_type', true ) ?: 'recommended';
    $status = get_post_meta( $post_id, '_tb4_product_status', true ) ?: 'ready';
    $icon = get_post_meta( $post_id, '_tb4_product_icon', true ) ?: 'T';
    $accent = get_post_meta( $post_id, '_tb4_product_accent', true ) ?: '#1E6B45';
    $checkout_url = get_post_meta( $post_id, '_tb4_product_checkout_url', true ) ?: get_post_meta( $post_id, '_tb4_product_purchase_url', true );
    $detail_url = get_permalink( $post_id );
    $app_url = get_post_meta( $post_id, '_tb4_product_app_run_url', true );
    return [
        'id'              => $post_id,
        'title'           => tb4_products_app_bridge_public_text( get_the_title( $post_id ) ),
        'subtitle'        => tb4_products_app_bridge_public_text( get_post_meta( $post_id, '_tb4_product_app_subtitle', true ) ?: get_post_meta( $post_id, '_tb4_product_tagline', true ) ),
        'desc'            => tb4_products_app_bridge_public_text( wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ), 28 ) ),
        'type'            => $type,
        'status'          => $status,
        'statusLabel'     => 'ready' === $status ? 'พร้อมใช้' : ( 'beta' === $status ? 'Beta' : 'กำลังเตรียม' ),
        'icon'            => tb4_products_app_bridge_public_text( $icon ),
        'accent'          => sanitize_hex_color( $accent ) ?: '#1E6B45',
        'image'           => get_the_post_thumbnail_url( $post_id, 'medium_large' ),
        'detailUrl'       => $detail_url,
        'checkoutUrl'     => esc_url_raw( $checkout_url ),
        'paymentEnabled'  => get_post_meta( $post_id, '_tb4_product_payment_enabled', true ) === '1',
        'paymentProvider' => get_post_meta( $post_id, '_tb4_product_payment_provider', true ) ?: 'manual',
        'invoiceEmail'    => get_post_meta( $post_id, '_tb4_product_invoice_email_enabled', true ) === '1',
        'deliveryMode'    => get_post_meta( $post_id, '_tb4_product_delivery_mode', true ) ?: 'none',
        'deliveryUrl'     => esc_url_raw( get_post_meta( $post_id, '_tb4_product_delivery_file_url', true ) ),
        'deliveryLocked'  => get_post_meta( $post_id, '_tb4_product_paid_delivery_lock', true ) === '1',
        'autoInstall'     => get_post_meta( $post_id, '_tb4_product_auto_install_enabled', true ) === '1',
        'runMode'         => get_post_meta( $post_id, '_tb4_product_app_run_mode', true ) ?: 'detail',
        'runUrl'          => esc_url_raw( $app_url ?: $detail_url ),
        'shortcode'       => get_post_meta( $post_id, '_tb4_product_app_shortcode', true ),
        'installedLabel'  => tb4_products_app_bridge_public_text( get_post_meta( $post_id, '_tb4_product_installed_label', true ) ?: 'พร้อมเปิดใช้งานในพื้นที่สมาชิก' ),
    ];
}

function tb4_products_app_bridge_demo_payloads() {
    return [
        [ 'id' => 'demo-aira', 'title' => 'AiRA Workspace', 'subtitle' => 'แอพช่วยคิดงานและจัดระบบ', 'desc' => 'ตัวอย่างแอพสำหรับเพิ่มประสิทธิภาพการใช้งานในพื้นที่สมาชิก', 'statusLabel' => 'ตัวอย่าง', 'icon' => 'AI', 'accent' => '#1E6B45', 'image' => '', 'detailUrl' => '#', 'checkoutUrl' => '', 'paymentEnabled' => false, 'invoiceEmail' => true, 'deliveryMode' => 'instant', 'deliveryLocked' => false, 'autoInstall' => true, 'runMode' => 'detail', 'runUrl' => '#', 'installedLabel' => 'พร้อมใช้งานเมื่อมีสินค้าเผยแพร่จริง' ],
        [ 'id' => 'demo-speed', 'title' => 'Speed Booster', 'subtitle' => 'แอพช่วยลดความหน่วง', 'desc' => 'ตัวอย่างสินค้าเสริมสำหรับเปิดขายหรือเปิดใช้งานภายหลัง', 'statusLabel' => 'รอข้อมูล', 'icon' => 'SB', 'accent' => '#F97316', 'image' => '', 'detailUrl' => '#', 'checkoutUrl' => '', 'paymentEnabled' => true, 'invoiceEmail' => true, 'deliveryMode' => 'email', 'deliveryLocked' => true, 'autoInstall' => false, 'runMode' => 'detail', 'runUrl' => '#', 'installedLabel' => 'รอเปิดใช้งานจริง' ],
    ];
}

function tb4_products_app_bridge_enqueue_assets() {
    wp_enqueue_style( 'tb4-products' );
    wp_enqueue_script( 'tb4-products' );
    wp_localize_script( 'tb4-products', 'TB4ProductsApp', [
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'tb4_products_app_order' ),
        'isLoggedIn' => is_user_logged_in(),
        'loginUrl' => wp_login_url( home_url( add_query_arg( null, null ) ) ),
    ] );
}

function tb4_products_app_store_shortcode( $atts = [] ) {
    $atts = shortcode_atts( [ 'limit' => 12, 'context' => 'member' ], $atts, 'thinkb4do_app_store' );
    tb4_products_app_bridge_enqueue_assets();
    $posts = tb4_products_app_bridge_get_product_apps( $atts['limit'] );
    $apps = [];
    foreach ( $posts as $post ) {
        $apps[] = tb4_products_app_bridge_product_payload( $post->ID );
    }
    if ( empty( $apps ) ) {
        $apps = tb4_products_app_bridge_demo_payloads();
    }
    $first = $apps[0];
    ob_start();
    ?>
    <section class="tb4-app-store-shell" data-tb4-app-store>
      <header class="tb4-app-store-hero">
        <div>
          <span>Thinkb4do App Store</span>
          <h2>ผลิตภัณฑ์และแอพเสริมสำหรับเพิ่มประสิทธิภาพการใช้งาน</h2>
          <p>เลือกแอพ คลิกเพื่อเปิดหน้าต่างแบบ Mac ซื้อฟีเจอร์ใหม่ ส่งบิลทางอีเมล ส่งไฟล์ หรือเปิดใช้งานทันทีตามการตั้งค่าของสินค้า</p>
        </div>
        <div class="tb4-app-store-status">
          <strong><?php echo esc_html( count( $apps ) ); ?></strong>
          <span>แอพพร้อมแสดง</span>
          <small>Settings | โดย Thinkb4do | ดูรายละเอียด</small>
        </div>
      </header>
      <div class="tb4-app-store-layout">
        <aside class="tb4-app-dock" aria-label="แอพที่ใช้บ่อย">
          <?php foreach ( $apps as $index => $app ) : ?>
            <button type="button" class="<?php echo 0 === $index ? 'is-active' : ''; ?>" data-tb4-open-app="<?php echo esc_attr( $app['id'] ); ?>" title="<?php echo esc_attr( $app['title'] ); ?>" style="--tb4-app-accent:<?php echo esc_attr( $app['accent'] ); ?>">
              <?php if ( ! empty( $app['image'] ) ) : ?><img src="<?php echo esc_url( $app['image'] ); ?>" alt=""><?php else : ?><span><?php echo esc_html( substr( $app['icon'], 0, 3 ) ); ?></span><?php endif; ?>
            </button>
          <?php endforeach; ?>
        </aside>
        <div class="tb4-app-window" data-tb4-app-window>
          <div class="tb4-app-window-bar"><span></span><span></span><span></span><strong data-tb4-app-window-title><?php echo esc_html( $first['title'] ); ?></strong></div>
          <div class="tb4-app-window-body">
            <div class="tb4-app-window-copy">
              <div class="tb4-app-window-icon" data-tb4-app-window-icon style="--tb4-app-accent:<?php echo esc_attr( $first['accent'] ); ?>"><?php echo esc_html( substr( $first['icon'], 0, 3 ) ); ?></div>
              <span data-tb4-app-window-status><?php echo esc_html( $first['statusLabel'] ); ?></span>
              <h3 data-tb4-app-window-heading><?php echo esc_html( $first['title'] ); ?></h3>
              <p data-tb4-app-window-desc><?php echo esc_html( $first['subtitle'] ?: $first['desc'] ); ?></p>
              <div class="tb4-app-window-flow">
                <em>ซื้อฟีเจอร์</em><em>ส่งบิลอีเมล</em><em>ส่งไฟล์/ติดตั้ง</em><em>เปิดแอพที่นี่</em>
              </div>
              <div class="tb4-app-window-actions">
                <a href="<?php echo esc_url( $first['detailUrl'] ); ?>" data-tb4-app-detail>ดูรายละเอียด</a>
                <button type="button" data-tb4-app-checkout>ซื้อ/เปิดใช้งาน</button>
                <button type="button" data-tb4-app-run>เปิดแอพ</button>
              </div>
              <p class="tb4-app-window-note" data-tb4-app-note><?php echo esc_html( $first['installedLabel'] ); ?></p>
            </div>
            <div class="tb4-app-window-preview" data-tb4-app-preview>
              <div class="tb4-app-preview-screen"><strong>Live App Area</strong><span>คลิกแอพจาก Dock เพื่อเปิดการทำงานในหน้านี้</span></div>
            </div>
          </div>
        </div>
      </div>
      <div class="tb4-app-grid" aria-label="รายการแอพทั้งหมด">
        <?php foreach ( $apps as $app ) : ?>
          <article class="tb4-app-card" data-tb4-app-card="<?php echo esc_attr( $app['id'] ); ?>" data-app='<?php echo esc_attr( wp_json_encode( $app ) ); ?>' style="--tb4-app-accent:<?php echo esc_attr( $app['accent'] ); ?>">
            <button type="button" data-tb4-open-app="<?php echo esc_attr( $app['id'] ); ?>">
              <span class="tb4-app-card-icon"><?php if ( ! empty( $app['image'] ) ) : ?><img src="<?php echo esc_url( $app['image'] ); ?>" alt=""><?php else : ?><?php echo esc_html( substr( $app['icon'], 0, 3 ) ); ?><?php endif; ?></span>
              <strong><?php echo esc_html( $app['title'] ); ?></strong>
              <small><?php echo esc_html( $app['subtitle'] ?: $app['statusLabel'] ); ?></small>
            </button>
          </article>
        <?php endforeach; ?>
      </div>
      <div class="tb4-app-order-status" data-tb4-app-order-status aria-live="polite"></div>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode( 'thinkb4do_app_store', 'tb4_products_app_store_shortcode' );

function tb4_products_app_bridge_ajax_order() {
    check_ajax_referer( 'tb4_products_app_order', 'nonce' );
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => 'กรุณาเข้าสู่ระบบก่อนซื้อหรือเปิดใช้งานแอพ', 'loginUrl' => wp_login_url() ], 401 );
    }
    $product_id = absint( $_POST['productId'] ?? 0 );
    if ( ! $product_id || 'tb4_product' !== get_post_type( $product_id ) ) {
        wp_send_json_error( [ 'message' => 'ไม่พบสินค้า/แอพที่เลือก' ], 404 );
    }
    $app = tb4_products_app_bridge_product_payload( $product_id );
    $user = wp_get_current_user();
    $order_title = 'TB4-' . gmdate( 'Ymd-His' ) . ' - ' . $app['title'];
    $order_id = wp_insert_post( [
        'post_type'   => 'tb4_app_order',
        'post_status' => 'private',
        'post_title'  => $order_title,
        'post_author' => get_current_user_id(),
    ], true );
    if ( is_wp_error( $order_id ) ) {
        wp_send_json_error( [ 'message' => 'สร้างรายการไม่สำเร็จ กรุณาลองอีกครั้ง' ], 500 );
    }
    update_post_meta( $order_id, '_tb4_order_product_id', $product_id );
    update_post_meta( $order_id, '_tb4_order_user_id', get_current_user_id() );
    update_post_meta( $order_id, '_tb4_order_user_email', $user->user_email );
    update_post_meta( $order_id, '_tb4_order_status', $app['paymentEnabled'] ? 'pending_payment' : 'ready' );
    update_post_meta( $order_id, '_tb4_order_checkout_url', $app['checkoutUrl'] );
    update_post_meta( $order_id, '_tb4_order_delivery_mode', $app['deliveryMode'] );

    $delivery_url = '';
    $can_auto_deliver = ! $app['deliveryLocked'] && in_array( $app['deliveryMode'], [ 'email', 'both', 'instant' ], true ) && ! empty( $app['deliveryUrl'] );
    if ( $can_auto_deliver ) {
        $delivery_url = $app['deliveryUrl'];
    }

    $subject = 'Thinkb4do: บิล/ใบสรุปรายการ ' . $app['title'];
    $lines = [
        'สวัสดี ' . ( $user->display_name ?: $user->user_login ),
        '',
        'ระบบได้รับรายการเปิดใช้งานผลิตภัณฑ์: ' . $app['title'],
        'เลขรายการ: ' . $order_id,
        'สถานะ: ' . ( $app['paymentEnabled'] ? 'รอชำระเงิน/ยืนยันรายการ' : 'พร้อมเปิดใช้งาน' ),
        'รายละเอียดสินค้า: ' . $app['detailUrl'],
    ];
    if ( $app['checkoutUrl'] ) {
        $lines[] = 'ลิงก์ชำระเงิน/เปิดใช้งาน: ' . $app['checkoutUrl'];
    }
    if ( $delivery_url ) {
        $lines[] = 'ลิงก์ไฟล์/ติดตั้ง: ' . $delivery_url;
    } elseif ( $app['deliveryLocked'] ) {
        $lines[] = 'การส่งไฟล์ถูกล็อกไว้จนกว่าจะยืนยันการชำระเงินเรียบร้อย';
    }
    $lines[] = '';
    $lines[] = 'Thinkb4do — Think before you do.';
    if ( $app['invoiceEmail'] || $app['paymentEnabled'] || $delivery_url ) {
        wp_mail( $user->user_email, $subject, implode( "\n", $lines ) );
        $admin_email = get_option( 'admin_email' );
        if ( $admin_email ) {
            wp_mail( $admin_email, '[Team Copy] ' . $subject, implode( "\n", $lines ) );
        }
    }

    wp_send_json_success( [
        'message'     => $delivery_url ? 'สร้างรายการแล้ว และส่งบิล/ไฟล์ไปทางอีเมลแล้ว' : 'สร้างรายการแล้ว ระบบส่งบิล/รายละเอียดไปทางอีเมลแล้ว',
        'orderId'     => $order_id,
        'checkoutUrl' => $app['checkoutUrl'],
        'runUrl'      => $app['runUrl'],
        'deliveryUrl' => $delivery_url,
        'autoInstall' => $app['autoInstall'],
        'runMode'     => $app['runMode'],
    ] );
}
add_action( 'wp_ajax_tb4_products_app_order', 'tb4_products_app_bridge_ajax_order' );

function tb4_products_app_bridge_community_tabs( $tabs, $user_id ) {
    if ( isset( $tabs['products'] ) ) {
        return $tabs;
    }
    $tabs['products'] = [
        'label'       => 'ผลิตภัณฑ์',
        'icon'        => 'ph-shopping-bag-open',
        'fallback'    => '⌘',
        'description' => 'แอพและฟีเจอร์เสริมสำหรับเพิ่มประสิทธิภาพการใช้งานในพื้นที่สมาชิก',
        'purchasable' => false,
        'default_open'=> true,
    ];
    return $tabs;
}
add_filter( 'tb4c_member_area_extra_tabs', 'tb4_products_app_bridge_community_tabs', 10, 2 );

function tb4_products_app_bridge_render_community_tab( $tab, $user_id, $tab_item ) {
    if ( 'products' !== $tab ) {
        return;
    }
    echo do_shortcode( '[thinkb4do_app_store limit="12" context="member"]' );
}
add_action( 'tb4c_member_area_render_feature_tab', 'tb4_products_app_bridge_render_community_tab', 10, 3 );

register_activation_hook( TB4_PRODUCTS_PLUGIN_FILE, function() {
    tb4_products_app_bridge_register_order_type();
    flush_rewrite_rules();
} );
