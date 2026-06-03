<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }


function tb4_product_public_text( $value ) {
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

function tb4_product_public_html( $value ) {
    return tb4_product_public_text( $value );
}

function tb4_product_single_lines( $value ) {
    $lines = preg_split( '/\r\n|\r|\n/', (string) $value );
    $clean = [];
    foreach ( $lines as $line ) {
        $line = trim( wp_strip_all_tags( $line ) );
        if ( '' !== $line ) {
            $clean[] = $line;
        }
    }
    return $clean;
}

function tb4_product_single_specs( $value ) {
    $rows = [];
    foreach ( tb4_product_single_lines( $value ) as $line ) {
        $parts = array_map( 'trim', explode( ':', $line, 2 ) );
        $rows[] = [
            'label' => $parts[0] ?: 'ข้อมูล',
            'value' => $parts[1] ?? 'พร้อมใช้งาน',
        ];
    }
    return $rows;
}

function tb4_product_single_faqs( $value ) {
    $rows = [];
    foreach ( tb4_product_single_lines( $value ) as $line ) {
        $parts = array_map( 'trim', explode( '|', $line, 2 ) );
        $rows[] = [
            'q' => $parts[0] ?: 'คำถาม',
            'a' => $parts[1] ?? 'ติดต่อ Thinkb4do เพื่อขอรายละเอียดเพิ่มเติม',
        ];
    }
    return $rows;
}

function tb4_product_single_label_for( $group, $key ) {
    $types = [
        'recommended' => 'แนะนำ',
        'plugin'      => 'ระบบเสริม',
        'ai-tool'     => 'AI Tools',
        'wordpress'   => 'ระบบเว็บไซต์',
        'template'    => 'Template',
        'system'      => 'System',
        'vps-system'  => 'VPS & System',
        'service'     => 'บริการ',
    ];
    $statuses = [
        'ready'      => 'พร้อมใช้',
        'beta'       => 'Beta',
        'soon'       => 'ใกล้เปิด',
        'draft-demo' => 'ข้อมูลตัวอย่าง',
        'research'   => 'กำลังวิจัย',
    ];
    if ( 'type' === $group ) {
        return $types[ $key ] ?? 'ผลิตภัณฑ์';
    }
    if ( 'status' === $group ) {
        return $statuses[ $key ] ?? 'พร้อมใช้';
    }
    return $key;
}

get_header();
while ( have_posts() ) : the_post();
    $id = get_the_ID();
    $badge = tb4_product_public_text( get_post_meta( $id, '_tb4_product_badge', true ) ?: 'Thinkb4do' );
    $tagline = tb4_product_public_text( get_post_meta( $id, '_tb4_product_tagline', true ) );
    $detail_summary = tb4_product_public_text( get_post_meta( $id, '_tb4_product_detail_summary', true ) );
    $rating = get_post_meta( $id, '_tb4_product_rating', true ) ?: '4.8';
    $license = tb4_product_public_text( get_post_meta( $id, '_tb4_product_license', true ) ?: 'ตามรายละเอียดสินค้า' );
    $downloads = tb4_product_public_text( get_post_meta( $id, '_tb4_product_downloads', true ) ?: 'กำลังเก็บสถิติ' );
    $demo = get_post_meta( $id, '_tb4_product_demo_url', true );
    $action = get_post_meta( $id, '_tb4_product_purchase_url', true );
    $tb4_team_defaults = wp_parse_args( get_option( 'tb4_products_team_settings', [] ), [
        'hide_prices_default' => '1',
        'default_cta_label' => 'ดูรายละเอียด',
        'default_affiliate_disclosure' => 'ลิงก์บางรายการอาจเป็นลิงก์แนะนำ โดย Thinkb4do',
        'default_affiliate_link_label' => 'ดูข้อเสนอ',
        'default_booking_label' => 'จองคิว / นัดหมาย',
        'default_connection_note' => 'รองรับการเชื่อมต่อหลายแหล่ง พร้อมตรวจลิงก์ก่อนนำเสนอ',
    ] );
    $cta = tb4_product_public_text( get_post_meta( $id, '_tb4_product_cta_label', true ) ?: $tb4_team_defaults['default_cta_label'] );
    $accent = get_post_meta( $id, '_tb4_product_accent', true ) ?: '#1E6B45';
    $type_key = get_post_meta( $id, '_tb4_product_type', true ) ?: 'recommended';
    $status_key = get_post_meta( $id, '_tb4_product_status', true ) ?: 'ready';
    $type = tb4_product_public_text( tb4_product_single_label_for( 'type', $type_key ) );
    $status = tb4_product_public_text( tb4_product_single_label_for( 'status', $status_key ) );
    $level = get_post_meta( $id, '_tb4_product_level', true ) ?: 'pro';
    $affiliate_enabled = get_post_meta( $id, '_tb4_product_affiliate_enabled', true ) === '1';
    $show_partner_prices = get_post_meta( $id, '_tb4_product_show_partner_prices', true ) === '1';
    $affiliate_disclosure = tb4_product_public_text( get_post_meta( $id, '_tb4_product_affiliate_disclosure', true ) ?: $tb4_team_defaults['default_affiliate_disclosure'] );
    $external_affiliate_enabled = get_post_meta( $id, '_tb4_product_external_affiliate_enabled', true ) === '1';
    $affiliate_link_label = tb4_product_public_text( get_post_meta( $id, '_tb4_product_affiliate_link_label', true ) ?: $tb4_team_defaults['default_affiliate_link_label'] );
    $booking_enabled = get_post_meta( $id, '_tb4_product_booking_enabled', true ) === '1';
    $booking_status = get_post_meta( $id, '_tb4_product_booking_status', true ) ?: 'soon';
    $booking_cta_label = tb4_product_public_text( get_post_meta( $id, '_tb4_product_booking_cta_label', true ) ?: $tb4_team_defaults['default_booking_label'] );
    $booking_embed_url = esc_url_raw( get_post_meta( $id, '_tb4_product_booking_embed_url', true ) );
    $booking_requires_approval = get_post_meta( $id, '_tb4_product_booking_requires_approval', true ) === '1';
    $connection_status_note = tb4_product_public_text( get_post_meta( $id, '_tb4_product_connection_status_note', true ) ?: $tb4_team_defaults['default_connection_note'] );
    $best_for = tb4_product_public_text( get_post_meta( $id, '_tb4_product_best_for', true ) ?: 'ผู้ใช้งาน Thinkb4do' );
    $trust_score = get_post_meta( $id, '_tb4_product_trust_score', true ) ?: '95';
    $integrations = tb4_product_public_text( get_post_meta( $id, '_tb4_product_integrations', true ) ?: 'เว็บไซต์หลัก, หน้า Landing Page, ระบบสมาชิก' );
    $compare_features = tb4_product_public_text( get_post_meta( $id, '_tb4_product_compare_features', true ) ?: 'ใช้งานง่าย, รองรับมือถือ, พร้อมต่อยอด' );
    $support_note = tb4_product_public_text( get_post_meta( $id, '_tb4_product_support_note', true ) ?: 'ข้อมูลนี้ใช้สำหรับแนะนำสินค้าและช่วยให้ผู้ใช้ตัดสินใจจากรายละเอียดก่อน โดยยังไม่บังคับแสดงราคา' );
    $features = array_map( 'tb4_product_public_text', tb4_product_single_lines( get_post_meta( $id, '_tb4_product_key_features', true ) ) );
    $steps = array_map( 'tb4_product_public_text', tb4_product_single_lines( get_post_meta( $id, '_tb4_product_use_steps', true ) ) );
    $specs = tb4_product_single_specs( get_post_meta( $id, '_tb4_product_specs', true ) );
    $gallery_urls = tb4_product_single_lines( get_post_meta( $id, '_tb4_product_gallery_urls', true ) );
    $faqs = tb4_product_single_faqs( get_post_meta( $id, '_tb4_product_faq', true ) );
    if ( function_exists( 'tb4_products_plugin' ) && method_exists( tb4_products_plugin(), 'public_product_partners' ) ) {
        $partners = tb4_products_plugin()->public_product_partners( $id );
    } else {
        $partners = [];
    }
    if ( function_exists( 'tb4_products_plugin' ) && method_exists( tb4_products_plugin(), 'public_product_booking_sources' ) ) {
        $booking_sources = tb4_products_plugin()->public_product_booking_sources( $id );
    } else {
        $booking_sources = [];
    }
    $external_partner_count = 0;
    foreach ( $partners as $tb4_partner_count_item ) {
        if ( isset( $tb4_partner_count_item['source'] ) && 'external' === $tb4_partner_count_item['source'] ) {
            $external_partner_count++;
        }
    }
    $partner_url_count = 0;
    foreach ( $partners as $tb4_partner_url_item ) {
        if ( ! empty( $tb4_partner_url_item['url'] ) ) {
            $partner_url_count++;
        }
    }
    $booking_url_count = 0;
    $external_booking_count = 0;
    foreach ( $booking_sources as $tb4_booking_url_item ) {
        if ( ! empty( $tb4_booking_url_item['url'] ) ) {
            $booking_url_count++;
        }
        if ( isset( $tb4_booking_url_item['source'] ) && 'external' === $tb4_booking_url_item['source'] ) {
            $external_booking_count++;
        }
    }
    $is_ready_to_order = 'ready' === $status_key;
    $can_open_purchase = $is_ready_to_order && ! empty( $action );
    $can_open_store = $is_ready_to_order && ( $affiliate_enabled || $external_affiliate_enabled ) && $partner_url_count > 0;
    $can_open_booking = $is_ready_to_order && $booking_enabled && 'ready' === $booking_status && $booking_url_count > 0;
    $purchase_lock_label = $is_ready_to_order ? 'ยังไม่มีปุ่มซื้อสำหรับสินค้านี้' : 'ยังไม่เปิดให้ซื้อ';
    $store_lock_label = $is_ready_to_order ? 'ยังไม่มีร้านค้าสำหรับสินค้านี้' : 'ยังไม่เปิดร้านค้า';
    $booking_lock_label = $booking_enabled ? ( 'ready' === $booking_status ? 'ยังไม่มีช่องทางจอง' : 'ยังไม่เปิดให้จอง' ) : 'ยังไม่เปิดระบบจอง';
    $archive_url = get_post_type_archive_link( 'tb4_product' ) ?: home_url( '/products/' );
    $summary = tb4_product_public_text( $detail_summary ?: ( $tagline ?: ( get_the_excerpt() ?: wp_trim_words( wp_strip_all_tags( get_the_content() ), 34 ) ) ) );
    $gallery = [];
    if ( has_post_thumbnail() ) {
        $thumb = get_the_post_thumbnail_url( $id, 'large' );
        if ( $thumb ) { $gallery[] = $thumb; }
    }
    foreach ( $gallery_urls as $url ) {
        $url = esc_url_raw( $url );
        if ( $url ) { $gallery[] = $url; }
    }
    $gallery = array_values( array_unique( $gallery ) );
    if ( empty( $features ) ) {
        $features = [ 'ออกแบบให้ใช้งานง่ายและรองรับทุกอุปกรณ์', 'รองรับการต่อยอดกับระบบ Thinkb4do/AiRA', 'มีข้อมูลช่วยตัดสินใจก่อนติดต่อหรือใช้งานจริง' ];
    }
    if ( empty( $steps ) ) {
        $steps = [ 'อ่านรายละเอียดและตรวจความเหมาะสมของสินค้า', 'กดปุ่มสอบถามหรือดูตัวอย่างเมื่อพร้อม', 'ทีมงาน Thinkb4do ช่วยแนะนำขั้นตอนต่อไป' ];
    }
    if ( empty( $specs ) ) {
        $specs = [
            [ 'label' => 'รองรับ', 'value' => 'เว็บไซต์หลัก, หน้า Landing Page, ระบบสมาชิก' ],
            [ 'label' => 'อุปกรณ์', 'value' => 'มือถือ แท็บเล็ต เดสก์ท็อป' ],
            [ 'label' => 'สถานะราคา', 'value' => 'ยังไม่แสดงราคาเป็นค่าเริ่มต้น' ],
        ];
    }
    if ( empty( $faqs ) ) {
        $faqs = [
            [ 'q' => 'สินค้านี้มีราคาแสดงหรือยัง?', 'a' => 'ค่าเริ่มต้นยังไม่แสดงราคา ราคาจะแสดงเฉพาะกรณีที่ทีมงานเปิดแสดงราคาเปรียบเทียบจากพาร์ทเนอร์' ],
            [ 'q' => 'ต้องเริ่มจากตรงไหน?', 'a' => 'อ่านภาพรวม ฟีเจอร์ และวิธีใช้งาน จากนั้นกดสอบถามหรือดูตัวอย่างเพื่อรับข้อมูลต่อ' ],
        ];
    }
    foreach ( $specs as &$tb4_spec_row ) { $tb4_spec_row['label'] = tb4_product_public_text( $tb4_spec_row['label'] ); $tb4_spec_row['value'] = tb4_product_public_text( $tb4_spec_row['value'] ); } unset( $tb4_spec_row );
    foreach ( $faqs as &$tb4_faq_row ) { $tb4_faq_row['q'] = tb4_product_public_text( $tb4_faq_row['q'] ); $tb4_faq_row['a'] = tb4_product_public_text( $tb4_faq_row['a'] ); } unset( $tb4_faq_row );
    $related = new WP_Query( [
        'post_type'      => 'tb4_product',
        'post_status'    => 'publish',
        'posts_per_page' => 3,
        'post__not_in'   => [ $id ],
        'meta_query'     => [ [ 'key' => '_tb4_product_type', 'value' => $type_key ] ],
    ] );
?>
<main class="tb4-product-single" data-tb4-product-detail>
    <div class="tb4-product-single__breadcrumb">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>">หน้าหลัก</a><span>›</span><a href="<?php echo esc_url( $archive_url ); ?>">ผลิตภัณฑ์</a><span>›</span><strong><?php echo esc_html( tb4_product_public_text( get_the_title() ) ); ?></strong>
    </div>

    <section class="tb4-product-single__hero">
        <div class="tb4-product-single__copy">
            <span><?php echo esc_html( $badge ); ?></span>
            <h1><?php echo esc_html( tb4_product_public_text( get_the_title() ) ); ?></h1>
            <p><?php echo esc_html( $summary ); ?></p>
            <div class="tb4-product-single__meta">
                <strong><?php echo esc_html( $status ); ?></strong>
                <em>★ <?php echo esc_html( $rating ); ?></em>
                <small><?php echo esc_html( $type ); ?></small>
                <small>Trust <?php echo esc_html( $trust_score ); ?>%</small>
                <small><?php echo esc_html( strtoupper( $level ) ); ?></small>
            </div>
            <div class="tb4-product-single__actions">
                <?php if ( $can_open_purchase ) : ?>
                    <a class="is-primary" href="<?php echo esc_url( $action ); ?>"><?php echo esc_html( $cta ); ?></a>
                <?php else : ?>
                    <span class="is-primary is-disabled tb4-product-single__locked-icon" aria-disabled="true" role="img" aria-label="<?php echo esc_attr( $purchase_lock_label ); ?>" title="<?php echo esc_attr( $purchase_lock_label ); ?>"><span class="tb4-icon-lock" aria-hidden="true"></span></span>
                <?php endif; ?>
                <?php if ( $can_open_store ) : ?>
                    <a href="#tb4-product-detail-partners">ดูร้านค้า</a>
                <?php else : ?>
                    <span class="is-disabled tb4-product-single__locked-icon" aria-disabled="true" role="img" aria-label="<?php echo esc_attr( $store_lock_label ); ?>" title="<?php echo esc_attr( $store_lock_label ); ?>"><span class="tb4-icon-store" aria-hidden="true"></span></span>
                <?php endif; ?>
                <?php if ( $can_open_booking ) : ?>
                    <a href="#tb4-product-detail-booking"><?php echo esc_html( $booking_cta_label ); ?></a>
                <?php else : ?>
                    <span class="is-disabled tb4-product-single__locked-icon" aria-disabled="true" role="img" aria-label="<?php echo esc_attr( $booking_lock_label ); ?>" title="<?php echo esc_attr( $booking_lock_label ); ?>"><span class="tb4-icon-calendar" aria-hidden="true"></span></span>
                <?php endif; ?>
                <?php if ( $demo ) : ?><a href="<?php echo esc_url( $demo ); ?>" target="_blank" rel="noopener">ดูตัวอย่าง</a><?php endif; ?>
                <a href="#tb4-product-detail-overview">อ่านรายละเอียด</a>
                <a href="<?php echo esc_url( $archive_url ); ?>">กลับไปหน้าผลิตภัณฑ์</a>
            </div>
        </div>
        <div class="tb4-product-single__visual" style="background:<?php echo esc_attr( $accent ); ?>;">
            <?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'large' ); } else { echo '<strong>' . esc_html( mb_substr( tb4_product_public_text( get_the_title() ), 0, 2 ) ) . '</strong>'; } ?>
        </div>
    </section>

    <nav class="tb4-product-single__nav" aria-label="เมนูรายละเอียดสินค้า">
        <a href="#tb4-product-detail-overview">ภาพรวม</a>
        <a href="#tb4-product-detail-features">ฟีเจอร์</a>
        <a href="#tb4-product-detail-specs">สเปก</a>
        <a href="#tb4-product-detail-steps">วิธีใช้งาน</a>
        <a href="#tb4-product-detail-partners">พาร์ทเนอร์</a>
        <a href="#tb4-product-detail-booking">Booking</a>
        <a href="#tb4-product-detail-faq">FAQ</a>
    </nav>

    <div class="tb4-product-single__detail-layout">
        <article class="tb4-product-single__detail-main">
            <section id="tb4-product-detail-overview" class="tb4-product-single__panel">
                <div class="tb4-product-single__section-head">
                    <span>Product Detail</span>
                    <h2>ภาพรวมสินค้า</h2>
                    <p><?php echo esc_html( $support_note ); ?></p>
                </div>
                <div class="tb4-product-single__richtext">
                    <?php if ( trim( get_the_content() ) ) : ?>
                        <?php echo wp_kses_post( tb4_product_public_html( apply_filters( 'the_content', get_the_content() ) ) ); ?>
                    <?php else : ?>
                        <p><?php echo esc_html( $summary ); ?></p>
                    <?php endif; ?>
                </div>
                <div class="tb4-product-single__highlight-grid">
                    <div><span>เหมาะสำหรับ</span><strong><?php echo esc_html( $best_for ); ?></strong></div>
                    <div><span>License</span><strong><?php echo esc_html( $license ); ?></strong></div>
                    <div><span>เชื่อมต่อ</span><strong><?php echo esc_html( $integrations ); ?></strong></div>
                </div>
            </section>

            <section class="tb4-product-single__panel tb4-product-single__standard-detail" aria-label="รายละเอียดสินค้าตามมาตรฐาน">
                <div class="tb4-product-single__section-head">
                    <span>Product Standard</span>
                    <h2>รายละเอียดสินค้าตามมาตรฐาน</h2>
                    <p>สรุปข้อมูลหลักที่ผู้เข้าชมควรรู้ก่อนตัดสินใจ โดยไม่เปิดเผยข้อมูลที่ไม่จำเป็น</p>
                </div>
                <div class="tb4-product-single__standard-grid">
                    <div><span>สินค้าแก้ปัญหาอะไร</span><strong><?php echo esc_html( $summary ); ?></strong></div>
                    <div><span>เหมาะกับใคร</span><strong><?php echo esc_html( $best_for ); ?></strong></div>
                    <div><span>สถานะสินค้า</span><strong><?php echo esc_html( $status ); ?></strong></div>
                    <div><span>ระดับผลิตภัณฑ์</span><strong><?php echo esc_html( strtoupper( $level ) ); ?></strong></div>
                    <div><span>สถานะราคา</span><strong><?php echo $show_partner_prices ? esc_html__( 'แสดงตามพาร์ทเนอร์ที่เปิดไว้', 'thinkb4do-products' ) : esc_html__( 'ยังไม่แสดงราคา', 'thinkb4do-products' ); ?></strong></div>
                    <div><span>สถานะการซื้อ/ร้านค้า</span><strong><?php echo esc_html( $can_open_purchase || $can_open_store ? 'เปิดให้ดำเนินการต่อได้' : 'ล็อกไว้จนกว่าสินค้าพร้อม' ); ?></strong></div>
                    <div><span>สถานะ Booking</span><strong><?php echo esc_html( $can_open_booking ? 'เปิดช่องทางจองภายนอกแล้ว' : $booking_lock_label ); ?></strong></div>
                    <div><span>สถานะการเชื่อมต่อ</span><strong><?php echo esc_html( $connection_status_note ); ?></strong></div>
                </div>
            </section>

            <?php if ( $gallery ) : ?>
            <section class="tb4-product-single__panel tb4-product-single__gallery-panel">
                <div class="tb4-product-single__section-head">
                    <span>Preview</span>
                    <h2>ภาพตัวอย่างสินค้า</h2>
                    <p>กดที่ภาพเพื่อขยายดูตัวอย่างแบบเต็มจอ</p>
                </div>
                <div class="tb4-product-single__gallery">
                    <?php foreach ( $gallery as $image_url ) : ?>
                        <button type="button" data-tb4-gallery-open="<?php echo esc_url( $image_url ); ?>" aria-label="เปิดภาพตัวอย่าง">
                            <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( tb4_product_public_text( get_the_title() ) ); ?>" loading="lazy" decoding="async">
                        </button>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <section id="tb4-product-detail-features" class="tb4-product-single__panel">
                <div class="tb4-product-single__section-head">
                    <span>Key Features</span>
                    <h2>ฟีเจอร์หลัก</h2>
                    <p>สรุปความสามารถที่ผู้ใช้ควรรู้ก่อนตัดสินใจ</p>
                </div>
                <div class="tb4-product-single__feature-grid">
                    <?php foreach ( $features as $index => $feature ) : ?>
                    <div>
                        <em><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></em>
                        <strong><?php echo esc_html( $feature ); ?></strong>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section id="tb4-product-detail-specs" class="tb4-product-single__panel">
                <div class="tb4-product-single__section-head">
                    <span>Specification</span>
                    <h2>สเปกและข้อมูลสำคัญ</h2>
                    <p>จัดข้อมูลให้อ่านง่าย เพื่อเปรียบเทียบกับสินค้าอื่นได้ทันที</p>
                </div>
                <div class="tb4-product-single__spec-list">
                    <?php foreach ( $specs as $row ) : ?>
                        <p><span><?php echo esc_html( $row['label'] ); ?></span><strong><?php echo esc_html( $row['value'] ); ?></strong></p>
                    <?php endforeach; ?>
                    <p><span>ราคา</span><strong><?php echo $show_partner_prices ? esc_html__( 'แสดงตามพาร์ทเนอร์ที่เปิดไว้', 'thinkb4do-products' ) : esc_html__( 'ยังไม่แสดงราคา', 'thinkb4do-products' ); ?></strong></p>
                </div>
            </section>

            <section id="tb4-product-detail-steps" class="tb4-product-single__panel">
                <div class="tb4-product-single__section-head">
                    <span>How to Start</span>
                    <h2>วิธีเริ่มใช้งาน</h2>
                    <p>เรียงขั้นตอนให้ผู้ใช้ทั่วไปเข้าใจง่ายและทำตามได้</p>
                </div>
                <ol class="tb4-product-single__steps">
                    <?php foreach ( $steps as $step ) : ?>
                        <li><?php echo esc_html( $step ); ?></li>
                    <?php endforeach; ?>
                </ol>
            </section>

            <section class="tb4-product-single__compare">
                <div>
                    <span>Product Fit</span>
                    <h2>สรุปจุดเด่นเพื่อใช้เปรียบเทียบ</h2>
                    <p><?php echo esc_html( $compare_features ); ?></p>
                </div>
                <div>
                    <span>Affiliate / Partner Connect</span>
                    <h2>ระบบข้อเสนอจากพาร์ทเนอร์ภายนอก</h2>
                    <p><?php echo ( $affiliate_enabled || $external_affiliate_enabled ) ? esc_html__( 'เปิดระบบลิงก์แนะนำและรองรับพาร์ทเนอร์ภายนอกแล้ว', 'thinkb4do-products' ) : esc_html__( 'ยังไม่ได้เปิดระบบลิงก์แนะนำสำหรับสินค้านี้', 'thinkb4do-products' ); ?></p>
                </div>
                <div>
                    <span>Booking Connect</span>
                    <h2>ระบบจอง/นัดหมายจากภายนอก</h2>
                    <p><?php echo $booking_enabled ? esc_html( $connection_status_note ) : esc_html__( 'ยังไม่ได้เปิดช่องทางจองสำหรับสินค้านี้ แต่โครงหน้ารองรับแล้ว', 'thinkb4do-products' ); ?></p>
                </div>
            </section>

            <section id="tb4-product-detail-partners" class="tb4-product-single__affiliate">
                <div class="tb4-product-single__affiliate-head">
                    <div>
                        <span>Affiliate Partner Connect</span>
                        <h2>ดูรายละเอียดและข้อเสนอจากพาร์ทเนอร์</h2>
                        <p><?php echo esc_html( ( $affiliate_enabled || $external_affiliate_enabled ) ? $affiliate_disclosure : 'ยังไม่ได้เปิดระบบลิงก์แนะนำสำหรับสินค้านี้ แต่โครงหน้ารองรับแล้ว' ); ?></p>
                    </div>
                    <strong><?php echo esc_html( count( $partners ) ); ?> แหล่ง</strong>
                    <?php if ( $external_affiliate_enabled ) : ?><small class="tb4-product-single__partner-connect-badge">เชื่อมภายนอก <?php echo esc_html( $external_partner_count ); ?> แหล่ง</small><?php endif; ?>
                </div>
                <?php if ( ( $affiliate_enabled || $external_affiliate_enabled ) && $partners ) : ?>
                <div class="tb4-product-single__partner-list">
                    <?php foreach ( $partners as $partner ) : ?>
                    <div class="tb4-product-single__partner <?php echo ( isset( $partner['source'] ) && 'external' === $partner['source'] ) ? 'is-external' : ''; ?>">
                        <div>
                            <strong><?php echo esc_html( $partner['name'] ); ?></strong>
                            <span><?php echo esc_html( $partner['note'] ); ?></span>
                            <?php if ( ! empty( $partner['badge'] ) ) : ?><small><?php echo esc_html( tb4_product_public_text( $partner['badge'] ) ); ?></small><?php endif; ?>
                        </div>
                        <em><?php echo $show_partner_prices && ! empty( $partner['price'] ) ? esc_html( $partner['price'] ) : esc_html__( 'ยังไม่แสดงราคา', 'thinkb4do-products' ); ?></em>
                        <?php if ( ! empty( $partner['url'] ) && $can_open_store ) : ?>
                            <?php $tb4_partner_url = ( function_exists( 'tb4_products_plugin' ) && method_exists( tb4_products_plugin(), 'public_affiliate_out_url' ) && ! empty( $partner['partnerKey'] ) ) ? tb4_products_plugin()->public_affiliate_out_url( $id, $partner['partnerKey'] ) : $partner['url']; ?>
                            <a href="<?php echo esc_url( $tb4_partner_url ); ?>" target="_blank" rel="nofollow sponsored noopener"><?php echo esc_html( $affiliate_link_label ); ?></a>
                        <?php else : ?>
                            <span class="tb4-product-single__partner-muted is-icon-lock" aria-disabled="true" role="img" aria-label="<?php echo esc_attr( $can_open_store ? 'รอลิงก์' : $store_lock_label ); ?>" title="<?php echo esc_attr( $can_open_store ? 'รอลิงก์' : $store_lock_label ); ?>"><span class="tb4-icon-lock" aria-hidden="true"></span></span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else : ?>
                    <div class="tb4-product-single__empty-state">ยังไม่มีข้อเสนอจากพาร์ทเนอร์สำหรับสินค้านี้ <span class="tb4-product-single__partner-muted is-icon-lock" aria-disabled="true" role="img" aria-label="ยังไม่มีร้านค้า" title="ยังไม่มีร้านค้า"><span class="tb4-icon-store" aria-hidden="true"></span></span></div>
                <?php endif; ?>
            </section>

            <section id="tb4-product-detail-booking" class="tb4-product-single__affiliate tb4-product-single__booking-panel">
                <div class="tb4-product-single__affiliate-head">
                    <div>
                        <span>Booking Connect</span>
                        <h2>ช่องทางจอง/นัดหมายจากภายนอก</h2>
                        <p><?php echo esc_html( $booking_enabled ? $connection_status_note : 'ยังไม่ได้เปิดช่องทางจองสำหรับสินค้านี้' ); ?></p>
                    </div>
                    <strong><?php echo esc_html( count( $booking_sources ) ); ?> ช่องทาง</strong>
                    <?php if ( $external_booking_count ) : ?><small class="tb4-product-single__partner-connect-badge">เชื่อมภายนอก <?php echo esc_html( $external_booking_count ); ?> ช่องทาง</small><?php endif; ?>
                </div>
                <?php if ( $booking_enabled && $booking_sources ) : ?>
                <div class="tb4-product-single__partner-list tb4-product-single__booking-list">
                    <?php foreach ( $booking_sources as $booking_source ) : ?>
                    <div class="tb4-product-single__partner <?php echo ( isset( $booking_source['source'] ) && 'external' === $booking_source['source'] ) ? 'is-external' : ''; ?>">
                        <div>
                            <strong><?php echo esc_html( $booking_source['name'] ); ?></strong>
                            <span><?php echo esc_html( $booking_source['note'] ); ?></span>
                            <?php if ( ! empty( $booking_source['badge'] ) ) : ?><small><?php echo esc_html( tb4_product_public_text( $booking_source['badge'] ) ); ?></small><?php endif; ?>
                        </div>
                        <em><?php echo esc_html( $booking_source['type'] ?? 'Booking' ); ?></em>
                        <?php if ( ! empty( $booking_source['url'] ) && $can_open_booking ) : ?>
                            <?php $tb4_booking_url = ( function_exists( 'tb4_products_plugin' ) && method_exists( tb4_products_plugin(), 'public_booking_out_url' ) && ! empty( $booking_source['bookingKey'] ) ) ? tb4_products_plugin()->public_booking_out_url( $id, $booking_source['bookingKey'] ) : $booking_source['url']; ?>
                            <a href="<?php echo esc_url( $tb4_booking_url ); ?>" target="_blank" rel="nofollow sponsored noopener"><?php echo esc_html( $booking_cta_label ); ?></a>
                        <?php else : ?>
                            <span class="tb4-product-single__partner-muted is-icon-lock" aria-disabled="true" role="img" aria-label="<?php echo esc_attr( $booking_lock_label ); ?>" title="<?php echo esc_attr( $booking_lock_label ); ?>"><span class="tb4-icon-calendar" aria-hidden="true"></span></span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else : ?>
                    <div class="tb4-product-single__empty-state">ยังไม่มีช่องทางจองสำหรับสินค้านี้ <span class="tb4-product-single__partner-muted is-icon-lock" aria-disabled="true" role="img" aria-label="ยังไม่มีช่องทางจอง" title="ยังไม่มีช่องทางจอง"><span class="tb4-icon-calendar" aria-hidden="true"></span></span></div>
                <?php endif; ?>
                <?php if ( $booking_embed_url && $can_open_booking ) : ?>
                    <a class="tb4-product-single__booking-embed-link" href="<?php echo esc_url( $booking_embed_url ); ?>" target="_blank" rel="nofollow sponsored noopener">เปิดหน้าจองแบบเต็ม</a>
                <?php endif; ?>
                <?php if ( $booking_requires_approval ) : ?><p class="tb4-product-single__booking-note">หมายเหตุ: การจองอาจต้องรอการยืนยันจากทีมงานก่อนเริ่มดำเนินการ</p><?php endif; ?>
            </section>

            <section id="tb4-product-detail-faq" class="tb4-product-single__panel">
                <div class="tb4-product-single__section-head">
                    <span>FAQ</span>
                    <h2>คำถามที่พบบ่อย</h2>
                    <p>ช่วยลดคำถามซ้ำและทำให้หน้ารายละเอียดสินค้าน่าเชื่อถือขึ้น</p>
                </div>
                <div class="tb4-product-single__faq-list">
                    <?php foreach ( $faqs as $faq ) : ?>
                    <details>
                        <summary><?php echo esc_html( $faq['q'] ); ?></summary>
                        <p><?php echo esc_html( $faq['a'] ); ?></p>
                    </details>
                    <?php endforeach; ?>
                </div>
            </section>
        </article>

        <aside class="tb4-product-single__detail-sidebar">
            <div class="tb4-product-single__summary-card">
                <span>Quick Summary</span>
                <h2><?php echo esc_html( tb4_product_public_text( get_the_title() ) ); ?></h2>
                <p><?php echo esc_html( $tagline ?: $summary ); ?></p>
                <div class="tb4-product-single__trust-meter" style="--score:<?php echo esc_attr( min( 100, max( 0, (int) $trust_score ) ) ); ?>%">
                    <i></i><strong>Trust Score <?php echo esc_html( $trust_score ); ?>%</strong>
                </div>
                <ul>
                    <li><span>สถานะ</span><strong><?php echo esc_html( $status ); ?></strong></li>
                    <li><span>ประเภท</span><strong><?php echo esc_html( $type ); ?></strong></li>
                    <li><span>การใช้งาน</span><strong><?php echo esc_html( $downloads ); ?></strong></li>
                    <li><span>ราคา</span><strong><?php echo $show_partner_prices ? esc_html__( 'เปิดบางพาร์ทเนอร์', 'thinkb4do-products' ) : esc_html__( 'ยังไม่แสดง', 'thinkb4do-products' ); ?></strong></li>
                    <li><span>พาร์ทเนอร์</span><strong><?php echo $external_affiliate_enabled ? esc_html__( 'เชื่อมภายนอก', 'thinkb4do-products' ) : esc_html__( 'พร้อมรองรับ', 'thinkb4do-products' ); ?></strong></li>
                    <li><span>Booking</span><strong><?php echo $booking_enabled ? esc_html__( 'พร้อมรองรับภายนอก', 'thinkb4do-products' ) : esc_html__( 'ยังไม่เปิด', 'thinkb4do-products' ); ?></strong></li>
                </ul>
                <?php if ( $can_open_purchase ) : ?>
                    <a class="tb4-product-single__summary-cta" href="<?php echo esc_url( $action ); ?>"><?php echo esc_html( $cta ); ?></a>
                <?php else : ?>
                    <span class="tb4-product-single__summary-cta is-disabled tb4-product-single__locked-icon" aria-disabled="true" role="img" aria-label="<?php echo esc_attr( $purchase_lock_label ); ?>" title="<?php echo esc_attr( $purchase_lock_label ); ?>"><span class="tb4-icon-lock" aria-hidden="true"></span></span>
                <?php endif; ?>
                <?php if ( $can_open_store ) : ?>
                    <a class="tb4-product-single__summary-link" href="#tb4-product-detail-partners">ดูร้านค้า</a>
                <?php else : ?>
                    <span class="tb4-product-single__summary-link is-disabled tb4-product-single__locked-icon" aria-disabled="true" role="img" aria-label="<?php echo esc_attr( $store_lock_label ); ?>" title="<?php echo esc_attr( $store_lock_label ); ?>"><span class="tb4-icon-store" aria-hidden="true"></span></span>
                <?php endif; ?>
                <?php if ( $can_open_booking ) : ?>
                    <a class="tb4-product-single__summary-link" href="#tb4-product-detail-booking"><?php echo esc_html( $booking_cta_label ); ?></a>
                <?php else : ?>
                    <span class="tb4-product-single__summary-link is-disabled tb4-product-single__locked-icon" aria-disabled="true" role="img" aria-label="<?php echo esc_attr( $booking_lock_label ); ?>" title="<?php echo esc_attr( $booking_lock_label ); ?>"><span class="tb4-icon-calendar" aria-hidden="true"></span></span>
                <?php endif; ?>
                <?php if ( $demo ) : ?><a class="tb4-product-single__summary-link" href="<?php echo esc_url( $demo ); ?>" target="_blank" rel="noopener">ดูตัวอย่างสินค้า</a><?php endif; ?>
                <button type="button" class="tb4-product-single__copy-link" data-tb4-copy-link="<?php echo esc_url( get_permalink() ); ?>">คัดลอกลิงก์หน้านี้</button>
            </div>
        </aside>
    </div>

    <?php if ( $related->have_posts() ) : ?>
    <section class="tb4-product-single__related">
        <div class="tb4-product-single__section-head">
            <span>Related Products</span>
            <h2>สินค้าใกล้เคียง</h2>
            <p>แนะนำจากประเภทเดียวกัน เพื่อช่วยให้ผู้ใช้เปรียบเทียบต่อได้ง่าย</p>
        </div>
        <div class="tb4-product-single__related-grid">
            <?php while ( $related->have_posts() ) : $related->the_post(); ?>
                <a href="<?php the_permalink(); ?>">
                    <strong><?php echo esc_html( tb4_product_public_text( get_the_title() ) ); ?></strong>
                    <span><?php echo esc_html( tb4_product_public_text( get_post_meta( get_the_ID(), '_tb4_product_tagline', true ) ?: wp_trim_words( wp_strip_all_tags( get_the_excerpt() ), 14 ) ) ); ?></span>
                    <em>ดูรายละเอียด →</em>
                </a>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </section>
    <?php endif; ?>

    <div class="tb4-product-single__gallery-modal" data-tb4-gallery-modal hidden>
        <button type="button" data-tb4-gallery-close aria-label="ปิดภาพตัวอย่าง">×</button>
        <img src="" alt="ภาพตัวอย่างสินค้า">
    </div>
</main>
<?php endwhile; get_footer(); ?>
