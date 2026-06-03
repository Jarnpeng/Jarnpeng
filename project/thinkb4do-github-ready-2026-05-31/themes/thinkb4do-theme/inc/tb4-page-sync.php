<?php
/**
 * Thinkb4do Page + Component Sync
 *
 * Creates the required WordPress pages when the theme is uploaded/activated
 * and keeps the content editable in the normal WordPress editor.
 *
 * @package Thinkb4do
 * @version 2.4.12
 */

defined( 'ABSPATH' ) || exit;

function tb4_page_sync_default_blueprints() {
    $blueprints = [
        'home' => [
            'title'      => 'หน้าแรก',
            'slug'       => 'home',
            'menu_label' => 'หน้าแรก',
            'template'   => '',
            'front_page' => true,
            'group'      => 'core',
            'content'    => <<<'HTML'
<!-- wp:heading {"level":2} -->
<h2>ยินดีต้อนรับสู่ Thinkb4do</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>หน้านี้ถูกผูกเข้ากับธีม Thinkb4do แล้ว คุณสามารถแก้ไขต่อได้จาก WordPress Editor หรือ Customizer โดยไม่ต้องเขียนโค้ดใหม่</p>
<!-- /wp:paragraph -->
HTML,
        ],
        'system-guide' => [
            'title'      => 'ระบบ',
            'slug'       => 'system-guide',
            'menu_label' => 'ระบบ',
            'template'   => 'page-system-guide.php',
            'group'      => 'core',
            'content'    => <<<'HTML'
<!-- wp:heading -->
<h2>ระบบ Thinkb4do</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>พื้นที่อธิบายระบบหลัก สถานะการพัฒนา และแนวคิด Thinkb4do</p>
<!-- /wp:paragraph -->
HTML,
        ],
        'products' => [
            'title'      => 'ผลิตภัณฑ์',
            'slug'       => 'products',
            'menu_label' => 'ผลิตภัณฑ์',
            'template'   => 'page-products.php',
            'group'      => 'core',
            'content'    => <<<'HTML'
<!-- wp:heading -->
<h2>ผลิตภัณฑ์ Thinkb4do</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>พื้นที่รวบรวมผลิตภัณฑ์ ระบบ เกม โปรแกรม หรือเครื่องมือที่สร้างจากแนวคิด Thinkb4do</p>
<!-- /wp:paragraph -->
HTML,
        ],
        'blog' => [
            'title'      => 'บทความ',
            'slug'       => 'blog',
            'menu_label' => 'บทความ',
            'template'   => '',
            'posts_page' => true,
            'group'      => 'core',
            'content'    => <<<'HTML'
<!-- wp:heading -->
<h2>บทความและบันทึกการพัฒนา</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>พื้นที่รวมบทความ ข่าวสาร และบันทึกการพัฒนา</p>
<!-- /wp:paragraph -->
HTML,
        ],
        'trust-registration' => [
            'title'      => 'ความน่าเชื่อถือ',
            'slug'       => 'trust-registration',
            'menu_label' => 'ความน่าเชื่อถือ',
            'template'   => 'page-trust-registration.php',
            'group'      => 'core',
            'content'    => <<<'HTML'
<!-- wp:heading -->
<h2>ความน่าเชื่อถือและข้อมูลจดทะเบียน</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>ใช้หน้านี้สำหรับอธิบายสถานะข้อมูลธุรกิจ การเตรียมข้อมูลจดทะเบียน และนโยบายความโปร่งใส โดยไม่ใช้เครื่องหมายราชการก่อนมีสิทธิ์ใช้งานจริง</p>
<!-- /wp:paragraph -->
HTML,
        ],
        'universal-support' => [
            'title'      => 'รองรับทุกอุปกรณ์',
            'slug'       => 'universal-support',
            'menu_label' => 'รองรับทุกอุปกรณ์',
            'template'   => 'page-universal-support.php',
            'group'      => 'core',
            'content'    => <<<'HTML'
<!-- wp:heading -->
<h2>รองรับทุกอุปกรณ์และเบราว์เซอร์</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>หน้าอธิบายความพร้อมด้าน Responsive, เบราว์เซอร์, ระบบปฏิบัติการ และการเข้าถึงของเว็บไซต์</p>
<!-- /wp:paragraph -->
HTML,
        ],

        'advertise' => [
            'title'          => 'สนใจลงโฆษณา',
            'slug'           => 'advertise',
            'menu_label'     => 'ลงโฆษณา',
            'template'       => '',
            'group'          => 'marketing',
            'hide_from_menu' => true,
            'content'        => <<<'HTML'
<!-- wp:heading -->
<h2>สนใจลงโฆษณากับ Thinkb4do</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>หน้านี้เป็นพื้นที่สำหรับอธิบายแนวทางลงโฆษณา พื้นที่แนะนำสินค้า แคมเปญ หรือความร่วมมือ โดยยังไม่เปิดเผยข้อมูลติดต่อส่วนตัวบนหน้าเว็บสาธารณะ</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul><li>เตรียมชื่อแบรนด์หรือสินค้า</li><li>ระบุกลุ่มเป้าหมายที่ต้องการสื่อสาร</li><li>ระบุรูปแบบแคมเปญ เช่น แบนเนอร์ บทความ หรือพื้นที่แนะนำ</li><li>ระบุช่วงเวลาและงบประมาณเบื้องต้น</li></ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>สามารถแก้ไขข้อความหน้านี้ต่อได้จาก WordPress Editor ตามรูปแบบบริการจริงของเว็บไซต์</p>
<!-- /wp:paragraph -->
HTML,
        ],

        // Thai legal / policy component pages.
        'legal-center' => [
            'title'      => 'กฎหมายและนโยบาย',
            'slug'       => 'legal-center',
            'menu_label' => 'นโยบาย',
            'template'   => '',
            'group'      => 'legal',
            'content'    => <<<'HTML'
<!-- wp:heading -->
<h2>ศูนย์กฎหมายและนโยบาย Thinkb4do</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>หน้านี้รวมนโยบายพื้นฐานที่เว็บไซต์ควรมีเพื่อความโปร่งใส การคุ้มครองข้อมูลส่วนบุคคล เงื่อนไขการใช้งาน และช่องทางติดต่อ/ร้องเรียน โดยข้อความทั้งหมดเป็นโครงตัวอย่างสำหรับแก้ไขต่อใน WordPress และควรตรวจสอบกับผู้เชี่ยวชาญก่อนใช้เผยแพร่จริง</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[thinkb4do_legal_status]
<!-- /wp:shortcode -->

<!-- wp:list -->
<ul><li>นโยบายความเป็นส่วนตัว</li><li>นโยบายคุกกี้</li><li>ข้อกำหนดการใช้งาน</li><li>นโยบายคืนเงิน/ยกเลิกบริการ</li><li>คำขอใช้สิทธิข้อมูลส่วนบุคคล</li><li>ช่องทางติดต่อและร้องเรียน</li></ul>
<!-- /wp:list -->
HTML,
        ],
        'privacy-policy' => [
            'title'       => 'นโยบายความเป็นส่วนตัว',
            'slug'        => 'privacy-policy',
            'menu_label'  => 'ความเป็นส่วนตัว',
            'template'    => '',
            'group'       => 'legal',
            'menu_parent' => 'legal-center',
            'content'     => <<<'HTML'
<!-- wp:heading -->
<h2>นโยบายความเป็นส่วนตัว</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Thinkb4do ให้ความสำคัญกับข้อมูลส่วนบุคคลของผู้ใช้งาน หน้านี้ใช้เป็นโครงนโยบายตามแนวทาง PDPA สำหรับอธิบายการเก็บ ใช้ เปิดเผย เก็บรักษา และสิทธิของเจ้าของข้อมูล โปรดแก้ไขรายละเอียดให้ตรงกับการใช้งานจริงก่อนเผยแพร่</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>ข้อมูลที่อาจเก็บ</h3>
<!-- /wp:heading -->
<!-- wp:list -->
<ul><li>ข้อมูลบัญชี เช่น ชื่อ อีเมล และข้อมูลติดต่อ</li><li>ข้อมูลการใช้งานเว็บไซต์ เช่น หน้าเว็บที่เข้าชม อุปกรณ์ เบราว์เซอร์ และบันทึกความปลอดภัย</li><li>ข้อมูลที่ผู้ใช้ส่งเข้าระบบ เช่น ข้อความ ไฟล์ งานออกแบบ หรือข้อมูลผลิตภัณฑ์</li></ul>
<!-- /wp:list -->

<!-- wp:heading {"level":3} -->
<h3>วัตถุประสงค์</h3>
<!-- /wp:heading -->
<!-- wp:list -->
<ul><li>ให้บริการและดูแลบัญชีสมาชิก</li><li>ปรับปรุงความปลอดภัย ประสิทธิภาพ และประสบการณ์ใช้งาน</li><li>ติดต่อ แจ้งข่าวสาร หรือแจ้งสถานะระบบเท่าที่จำเป็น</li><li>ปฏิบัติตามกฎหมายหรือคำสั่งของหน่วยงานที่มีอำนาจ</li></ul>
<!-- /wp:list -->

<!-- wp:heading {"level":3} -->
<h3>สิทธิของเจ้าของข้อมูล</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>ผู้ใช้งานสามารถติดต่อเพื่อขอเข้าถึง แก้ไข ลบ จำกัดการใช้ คัดค้าน หรือถอนความยินยอมเกี่ยวกับข้อมูลส่วนบุคคลได้ตามเงื่อนไขของกฎหมายที่เกี่ยวข้อง</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[thinkb4do_legal_status]
<!-- /wp:shortcode -->
HTML,
        ],
        'cookie-policy' => [
            'title'       => 'นโยบายคุกกี้',
            'slug'        => 'cookie-policy',
            'menu_label'  => 'คุกกี้',
            'template'    => '',
            'group'       => 'legal',
            'menu_parent' => 'legal-center',
            'content'     => <<<'HTML'
<!-- wp:heading -->
<h2>นโยบายคุกกี้</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>เว็บไซต์อาจใช้คุกกี้หรือเทคโนโลยีที่คล้ายกันเพื่อให้ระบบทำงานได้ จำค่าการใช้งาน วิเคราะห์ประสิทธิภาพ และปรับปรุงบริการ ควรเชื่อมระบบขอความยินยอมคุกกี้จริงก่อนเปิดใช้งานการวิเคราะห์หรือการตลาด</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul><li>คุกกี้จำเป็น: ใช้เพื่อให้ระบบเข้าสู่ระบบ ความปลอดภัย และฟังก์ชันหลักทำงานได้</li><li>คุกกี้วิเคราะห์: ใช้เพื่อวัดผลการใช้งานและปรับปรุงระบบ</li><li>คุกกี้การตลาด: ใช้เฉพาะเมื่อมีการขอความยินยอมและเชื่อมบริการที่เกี่ยวข้อง</li></ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>ผู้ใช้สามารถจัดการคุกกี้ผ่านการตั้งค่าเบราว์เซอร์ หรือระบบตั้งค่าคุกกี้ของเว็บไซต์เมื่อเปิดใช้งานแล้ว</p>
<!-- /wp:paragraph -->
HTML,
        ],
        'terms-of-service' => [
            'title'       => 'ข้อกำหนดการใช้งาน',
            'slug'        => 'terms-of-service',
            'menu_label'  => 'ข้อกำหนด',
            'template'    => '',
            'group'       => 'legal',
            'menu_parent' => 'legal-center',
            'content'     => <<<'HTML'
<!-- wp:heading -->
<h2>ข้อกำหนดการใช้งาน</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>การใช้งานเว็บไซต์ Thinkb4do หมายถึงผู้ใช้รับทราบข้อกำหนดพื้นฐานของระบบ ข้อความนี้เป็นโครงเริ่มต้นและควรปรับให้ตรงกับบริการจริงก่อนเผยแพร่</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul><li>ห้ามใช้เว็บไซต์เพื่อกระทำผิดกฎหมาย ละเมิดสิทธิผู้อื่น หรือรบกวนระบบ</li><li>ผู้ใช้ต้องรับผิดชอบต่อข้อมูล ไฟล์ หรือเนื้อหาที่นำเข้าสู่ระบบ</li><li>ระบบบางส่วนอาจอยู่ระหว่างพัฒนา ทดลอง หรือเปลี่ยนแปลงได้</li><li>Thinkb4do อาจปรับปรุง แก้ไข หรือระงับบางฟังก์ชันเพื่อความปลอดภัยและคุณภาพบริการ</li></ul>
<!-- /wp:list -->
HTML,
        ],
        'refund-cancellation-policy' => [
            'title'       => 'นโยบายคืนเงินและยกเลิกบริการ',
            'slug'        => 'refund-cancellation-policy',
            'menu_label'  => 'คืนเงิน/ยกเลิก',
            'template'    => '',
            'group'       => 'legal',
            'menu_parent' => 'legal-center',
            'content'     => <<<'HTML'
<!-- wp:heading -->
<h2>นโยบายคืนเงินและยกเลิกบริการ</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>หน้านี้เป็นโครงตัวอย่างสำหรับบริการดิจิทัล ผลิตภัณฑ์ออนไลน์ หรือสมาชิกภาพ ควรแก้ไขให้ตรงกับวิธีชำระเงิน ระยะเวลาทดลอง เงื่อนไขสินค้า และข้อยกเว้นจริงก่อนเปิดจำหน่าย</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul><li>ผู้ใช้สามารถติดต่อเพื่อขอยกเลิกบริการผ่านช่องทางที่ประกาศไว้</li><li>กรณีคืนเงินจะพิจารณาตามประเภทบริการ ระยะเวลาการใช้งาน และเงื่อนไขของแต่ละผลิตภัณฑ์</li><li>ไฟล์ดิจิทัลหรือบริการที่ถูกใช้งานครบถ้วนแล้ว อาจมีเงื่อนไขคืนเงินแตกต่างจากบริการทั่วไป</li><li>การคืนเงินผ่านผู้ให้บริการชำระเงินภายนอกอาจใช้เวลาตามรอบดำเนินการของผู้ให้บริการนั้น</li></ul>
<!-- /wp:list -->
HTML,
        ],
        'data-request' => [
            'title'       => 'คำขอใช้สิทธิข้อมูลส่วนบุคคล',
            'slug'        => 'data-request',
            'menu_label'  => 'สิทธิข้อมูล',
            'template'    => '',
            'group'       => 'legal',
            'menu_parent' => 'legal-center',
            'content'     => <<<'HTML'
<!-- wp:heading -->
<h2>คำขอใช้สิทธิข้อมูลส่วนบุคคล</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>ผู้ใช้งานสามารถส่งคำขอเกี่ยวกับข้อมูลส่วนบุคคล เช่น ขอเข้าถึง แก้ไข ลบ ถอนความยินยอม หรือคัดค้านการประมวลผล โดยระบบควรตรวจสอบตัวตนก่อนดำเนินการทุกครั้ง</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[thinkb4do_legal_status]
<!-- /wp:shortcode -->

<!-- wp:paragraph -->
<p>ข้อมูลที่ควรแจ้งเมื่อส่งคำขอ: ชื่อบัญชี อีเมลที่ใช้สมัคร ประเภทคำขอ รายละเอียดคำขอ และหลักฐานยืนยันตัวตนเท่าที่จำเป็น</p>
<!-- /wp:paragraph -->
HTML,
        ],
        'complaint-contact' => [
            'title'       => 'ติดต่อและร้องเรียน',
            'slug'        => 'complaint-contact',
            'menu_label'  => 'ติดต่อ/ร้องเรียน',
            'template'    => '',
            'group'       => 'legal',
            'menu_parent' => 'legal-center',
            'content'     => <<<'HTML'
<!-- wp:heading -->
<h2>ติดต่อและร้องเรียน</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>หน้านี้ใช้แสดงช่องทางติดต่อเรื่องบริการ บัญชี ข้อมูลส่วนบุคคล ปัญหาการใช้งาน หรือข้อร้องเรียนเกี่ยวกับเว็บไซต์</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[thinkb4do_legal_status]
<!-- /wp:shortcode -->

<!-- wp:list -->
<ul><li>ระบุหัวข้อปัญหาให้ชัดเจน</li><li>แนบภาพหน้าจอหรือรายละเอียดอุปกรณ์เมื่อเป็นปัญหาทางเทคนิค</li><li>ห้ามส่งข้อมูลอ่อนไหวเกินจำเป็นผ่านช่องทางสาธารณะ</li></ul>
<!-- /wp:list -->
HTML,
        ],
        'intellectual-property-policy' => [
            'title'       => 'ทรัพย์สินทางปัญญา',
            'slug'        => 'intellectual-property-policy',
            'menu_label'  => 'ทรัพย์สินทางปัญญา',
            'template'    => '',
            'group'       => 'legal',
            'menu_parent' => 'legal-center',
            'content'     => <<<'HTML'
<!-- wp:heading -->
<h2>นโยบายทรัพย์สินทางปัญญา</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>หน้านี้ใช้กำหนดแนวทางเกี่ยวกับโลโก้ รูปภาพ ข้อความ โค้ด ผลงานผู้ใช้ และเนื้อหาที่สร้างหรืออัปโหลดบนระบบ Thinkb4do</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul><li>ผู้ใช้งานควรใช้เฉพาะเนื้อหาที่ตนมีสิทธิ์หรือได้รับอนุญาตให้ใช้</li><li>ห้ามอัปโหลดเนื้อหาที่ละเมิดลิขสิทธิ์ เครื่องหมายการค้า หรือสิทธิของบุคคลอื่น</li><li>หากพบการละเมิด สามารถติดต่อให้ตรวจสอบและดำเนินการตามขั้นตอนของเว็บไซต์</li></ul>
<!-- /wp:list -->
HTML,
        ],
        'business-disclosure' => [
            'title'       => 'ข้อมูลผู้ให้บริการและสถานะธุรกิจ',
            'slug'        => 'business-disclosure',
            'menu_label'  => 'ข้อมูลผู้ให้บริการ',
            'template'    => '',
            'group'       => 'legal',
            'menu_parent' => 'legal-center',
            'content'     => <<<'HTML'
<!-- wp:heading -->
<h2>ข้อมูลผู้ให้บริการและสถานะธุรกิจ</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>หน้านี้ใช้แสดงข้อมูลผู้ให้บริการ ช่องทางติดต่อ สถานะการเตรียมจดทะเบียน และข้อมูลประกอบความน่าเชื่อถือ โดยห้ามนำเครื่องหมายราชการหรือเครื่องหมายรับรองมาใช้ก่อนมีสิทธิ์จริง</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[thinkb4do_legal_status]
<!-- /wp:shortcode -->

<!-- wp:paragraph -->
<p>เมื่อได้รับอนุมัติหรือมีเลขทะเบียนจริง สามารถอัปเดตข้อมูลจาก WordPress Editor หรือ Customizer ได้ทันที</p>
<!-- /wp:paragraph -->
HTML,
        ],
    ];

    return apply_filters( 'tb4_page_sync_default_blueprints', $blueprints );
}

function tb4_page_sync_find_page_id_by_slug( $slug ) {
    $page = get_page_by_path( sanitize_title( $slug ), OBJECT, 'page' );
    return $page ? absint( $page->ID ) : 0;
}

function tb4_page_sync_assign_template( $page_id, $template ) {
    if ( ! $page_id || ! $template ) {
        return;
    }

    $available = wp_get_theme()->get_page_templates();
    $valid     = isset( $available[ $template ] ) || file_exists( get_template_directory() . '/' . $template );
    $valid     = apply_filters( 'tb4_page_sync_template_is_valid', $valid, $template, $page_id );

    if ( $valid ) {
        update_post_meta( $page_id, '_wp_page_template', $template );
    }
}

function tb4_page_sync_run( $force_update = false ) {
    if ( ! current_user_can( 'edit_theme_options' ) && ! did_action( 'after_switch_theme' ) ) {
        return new WP_Error( 'tb4_no_permission', __( 'ไม่มีสิทธิ์ซิงก์หน้า', 'thinkb4do' ) );
    }

    $blueprints        = tb4_page_sync_default_blueprints();
    $bindings          = get_option( 'tb4_page_bindings', [] );
    $created           = 0;
    $updated           = 0;
    $menu_items        = [];
    $legal_footer_items = [];

    foreach ( $blueprints as $key => $page ) {
        $slug         = sanitize_title( $page['slug'] );
        $page_id      = tb4_page_sync_find_page_id_by_slug( $slug );
        $page_existed = (bool) $page_id;
        $was_created  = false;
        $data         = [
            'post_title'   => sanitize_text_field( $page['title'] ),
            'post_name'    => $slug,
            'post_content' => wp_kses_post( $page['content'] ),
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'menu_order'   => count( $menu_items ) + 1,
        ];

        if ( $page_id ) {
            $existing_component_key = (string) get_post_meta( $page_id, '_tb4_component_key', true );
            $can_update_existing    = $existing_component_key === (string) $key && $force_update && get_theme_mod( 'tb4_page_sync_update_existing', false );

            // Existing pages without the Thinkb4do component marker may be owned by another plugin/user.
            // Do not overwrite their content during automatic sync.
            if ( $can_update_existing ) {
                $data['ID'] = $page_id;
                wp_update_post( wp_slash( $data ) );
                $updated++;
            }
        } else {
            $page_id = wp_insert_post( wp_slash( $data ), true );
            if ( ! is_wp_error( $page_id ) ) {
                $created++;
                $was_created = true;
            }
        }

        if ( is_wp_error( $page_id ) || ! $page_id ) {
            continue;
        }

        $existing_component_key = (string) get_post_meta( $page_id, '_tb4_component_key', true );
        $theme_may_manage_page  = $was_created || $existing_component_key === (string) $key;

        if ( $theme_may_manage_page ) {
            tb4_page_sync_assign_template( $page_id, $page['template'] ?? '' );
            update_post_meta( $page_id, '_tb4_component_key', $key );
            update_post_meta( $page_id, '_tb4_component_group', sanitize_key( $page['group'] ?? 'core' ) );
            update_post_meta( $page_id, '_tb4_component_role', ! empty( $page['menu_parent'] ) ? 'child-page' : 'main-page' );
            update_post_meta( $page_id, '_tb4_editable_from_wp', 'yes' );
            $bindings[ $key ] = absint( $page_id );
        } elseif ( $page_existed ) {
            // Bind for navigation reference only; do not touch template/meta/options of plugin-owned pages.
            $bindings[ $key ] = absint( $page_id );
        }

        $menu_item = [
            'key'    => sanitize_key( $key ),
            'id'     => absint( $page_id ),
            'label'  => sanitize_text_field( $page['menu_label'] ),
            'parent' => sanitize_key( $page['menu_parent'] ?? '' ),
            'group'  => sanitize_key( $page['group'] ?? 'core' ),
            'hide'   => ! empty( $page['hide_from_menu'] ),
        ];
        $menu_items[] = $menu_item;

        if ( 'legal' === ( $menu_item['group'] ?? '' ) && ! empty( $menu_item['parent'] ) ) {
            $legal_footer_items[] = $menu_item;
        }

        if ( $theme_may_manage_page && ! empty( $page['front_page'] ) ) {
            update_option( 'show_on_front', 'page' );
            update_option( 'page_on_front', absint( $page_id ) );
        }

        if ( $theme_may_manage_page && ! empty( $page['posts_page'] ) ) {
            update_option( 'page_for_posts', absint( $page_id ) );
        }

        if ( $theme_may_manage_page && 'privacy-policy' === $key ) {
            update_option( 'wp_page_for_privacy_policy', absint( $page_id ) );
        }
    }

    update_option( 'tb4_page_bindings', array_map( 'absint', $bindings ), false );
    tb4_page_sync_menu( $menu_items );
    tb4_page_sync_legal_footer_menu( $legal_footer_items );
    flush_rewrite_rules( false );

    return [
        'created'  => $created,
        'updated'  => $updated,
        'bindings' => $bindings,
    ];
}

function tb4_page_sync_get_menu_id( $menu_name ) {
    $menu_obj = wp_get_nav_menu_object( $menu_name );
    $menu_id  = $menu_obj ? absint( $menu_obj->term_id ) : wp_create_nav_menu( $menu_name );
    return ( is_wp_error( $menu_id ) || ! $menu_id ) ? 0 : absint( $menu_id );
}

function tb4_page_sync_menu_items_by_page( $menu_id ) {
    $existing       = wp_get_nav_menu_items( $menu_id );
    $exists_by_page = [];
    if ( $existing ) {
        foreach ( $existing as $item ) {
            if ( 'post_type' === $item->type && 'page' === $item->object ) {
                $exists_by_page[ absint( $item->object_id ) ] = $item;
            }
        }
    }
    return $exists_by_page;
}

function tb4_page_sync_ensure_menu_item( $menu_id, $item, $order, $parent_menu_item_id = 0, $force_parent = false ) {
    static $cache = [];

    if ( ! isset( $cache[ $menu_id ] ) ) {
        $cache[ $menu_id ] = tb4_page_sync_menu_items_by_page( $menu_id );
    }

    $page_id    = absint( $item['id'] );
    $existing   = $cache[ $menu_id ][ $page_id ] ?? null;
    $menu_db_id = $existing ? absint( $existing->ID ) : 0;

    if ( $existing && ! $force_parent ) {
        return $menu_db_id;
    }

    if ( $existing && $force_parent && absint( $existing->menu_item_parent ) === absint( $parent_menu_item_id ) ) {
        return $menu_db_id;
    }

    $new_id = wp_update_nav_menu_item( $menu_id, $menu_db_id, [
        'menu-item-title'     => sanitize_text_field( $item['label'] ),
        'menu-item-object'    => 'page',
        'menu-item-object-id' => $page_id,
        'menu-item-type'      => 'post_type',
        'menu-item-status'    => 'publish',
        'menu-item-position'  => absint( $order ) + 1,
        'menu-item-parent-id' => absint( $parent_menu_item_id ),
    ] );

    if ( ! is_wp_error( $new_id ) && $new_id ) {
        $cache[ $menu_id ][ $page_id ] = (object) [
            'ID'               => absint( $new_id ),
            'object_id'        => $page_id,
            'menu_item_parent' => absint( $parent_menu_item_id ),
        ];
        return absint( $new_id );
    }

    return $menu_db_id;
}

function tb4_page_sync_menu( $items ) {
    if ( empty( $items ) ) {
        return;
    }

    $menu_id = tb4_page_sync_get_menu_id( 'Thinkb4do Main' );
    if ( ! $menu_id ) {
        return;
    }

    $menu_item_ids_by_key = [];

    foreach ( $items as $order => $item ) {
        if ( ! empty( $item['parent'] ) || ! empty( $item['hide'] ) ) {
            continue;
        }
        $menu_item_ids_by_key[ $item['key'] ] = tb4_page_sync_ensure_menu_item( $menu_id, $item, $order, 0, false );
    }

    foreach ( $items as $order => $item ) {
        if ( empty( $item['parent'] ) || ! empty( $item['hide'] ) ) {
            continue;
        }
        $parent_menu_item_id = absint( $menu_item_ids_by_key[ $item['parent'] ] ?? 0 );
        tb4_page_sync_ensure_menu_item( $menu_id, $item, $order, $parent_menu_item_id, true );
    }

    $locations = get_theme_mod( 'nav_menu_locations', [] );
    if ( empty( $locations['primary'] ) ) {
        $locations['primary'] = $menu_id;
        set_theme_mod( 'nav_menu_locations', $locations );
    }
}

function tb4_page_sync_legal_footer_menu( $items ) {
    if ( empty( $items ) ) {
        return;
    }

    $menu_id = tb4_page_sync_get_menu_id( 'Thinkb4do Legal' );
    if ( ! $menu_id ) {
        return;
    }

    foreach ( $items as $order => $item ) {
        tb4_page_sync_ensure_menu_item( $menu_id, $item, $order, 0, false );
    }

    $locations = get_theme_mod( 'nav_menu_locations', [] );
    if ( empty( $locations['footer-2'] ) ) {
        $locations['footer-2'] = $menu_id;
        set_theme_mod( 'nav_menu_locations', $locations );
    }
}

function tb4_page_sync_maybe_auto_run() {
    if ( ! current_user_can( 'edit_theme_options' ) ) {
        return;
    }

    if ( ! get_theme_mod( 'tb4_page_sync_auto_enabled', true ) ) {
        return;
    }

    $last_version = get_option( 'tb4_page_sync_version', '' );
    if ( defined( 'TB4_VERSION' ) && TB4_VERSION !== $last_version ) {
        tb4_page_sync_run( false );
        update_option( 'tb4_page_sync_version', TB4_VERSION, false );
    }
}
add_action( 'admin_init', 'tb4_page_sync_maybe_auto_run', 20 );
add_action( 'after_switch_theme', function() {
    tb4_page_sync_run( false );
    if ( defined( 'TB4_VERSION' ) ) {
        update_option( 'tb4_page_sync_version', TB4_VERSION, false );
    }
}, 20 );

function tb4_page_sync_admin_menu() {
    add_theme_page(
        __( 'เชื่อมหน้า / Components', 'thinkb4do' ),
        __( 'เชื่อมหน้า / Components', 'thinkb4do' ),
        'edit_theme_options',
        'thinkb4do-page-sync',
        'tb4_page_sync_render_admin_page'
    );
}
add_action( 'admin_menu', 'tb4_page_sync_admin_menu', 30 );

function tb4_page_sync_render_admin_page() {
    if ( ! current_user_can( 'edit_theme_options' ) ) {
        wp_die( esc_html__( 'ไม่มีสิทธิ์เข้าหน้านี้', 'thinkb4do' ) );
    }

    $message = '';
    if ( isset( $_POST['tb4_page_sync_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tb4_page_sync_nonce'] ) ), 'tb4_page_sync_now' ) ) {
        $force  = ! empty( $_POST['tb4_force_update'] );
        $result = tb4_page_sync_run( $force );
        if ( is_wp_error( $result ) ) {
            $message = '<div class="notice notice-error"><p>' . esc_html( $result->get_error_message() ) . '</p></div>';
        } else {
            $message = '<div class="notice notice-success"><p>ซิงก์สำเร็จ: เพิ่มหน้าใหม่ ' . absint( $result['created'] ) . ' หน้า / อัปเดต ' . absint( $result['updated'] ) . ' หน้า</p></div>';
        }
    }

    $blueprints = tb4_page_sync_default_blueprints();
    $bindings   = get_option( 'tb4_page_bindings', [] );
    ?>
    <div class="wrap tb4-page-sync-wrap">
        <style>
            .tb4-page-sync-wrap{max-width:1180px;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",Arial,"Noto Sans Thai","Kanit",sans-serif}.tb4-sync-hero{background:linear-gradient(135deg,#123f2c,#1e6b45);color:#fff;border-radius:20px;padding:26px 30px;margin:20px 0}.tb4-sync-hero h1{color:#fff;margin:0 0 8px}.tb4-sync-hero p{max-width:760px;color:rgba(255,255,255,.84)}.tb4-sync-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px}.tb4-sync-card{background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:16px;box-shadow:0 8px 22px rgba(15,23,42,.05)}.tb4-sync-card strong{display:block;font-size:15px;margin-bottom:6px}.tb4-sync-card code{font-size:12px}.tb4-sync-ok{color:#15803d;font-weight:700}.tb4-sync-missing{color:#b45309;font-weight:700}.tb4-sync-actions{display:flex;gap:10px;flex-wrap:wrap;margin:18px 0}.tb4-sync-actions .button-primary{background:#1e6b45;border-color:#1e6b45}
        </style>
        <?php echo $message; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <div class="tb4-sync-hero">
            <h1>เชื่อมหน้า / Components เข้า WordPress</h1>
            <p>ใช้หน้านี้เพื่อผูกโครงหน้าและส่วนประกอบของธีมเข้ากับ WordPress ทันทีหลังอัปโหลดหรือเปิดใช้ธีม หน้าเหล่านี้แก้ไขต่อได้จาก Pages, Gutenberg Editor และ Customizer</p>
        </div>
        <form method="post" class="tb4-sync-actions">
            <?php wp_nonce_field( 'tb4_page_sync_now', 'tb4_page_sync_nonce' ); ?>
            <button class="button button-primary" type="submit">ซิงก์หน้าและเมนูตอนนี้</button>
            <label style="display:inline-flex;align-items:center;gap:6px"><input type="checkbox" name="tb4_force_update" value="1"> อัปเดตเนื้อหาตัวอย่างทับหน้าที่มีอยู่แล้ว</label>
            <a class="button" href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=tb4_page_sync_settings' ) ); ?>">เปิดหน้าปรับแต่งข้อมูล</a>
            <a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>">เปิดรายการ Pages</a>
        </form>
        <div class="tb4-sync-grid">
            <?php foreach ( $blueprints as $key => $page ) :
                $page_id = ! empty( $bindings[ $key ] ) ? absint( $bindings[ $key ] ) : tb4_page_sync_find_page_id_by_slug( $page['slug'] );
                ?>
                <div class="tb4-sync-card">
                    <strong><?php echo esc_html( $page['title'] ); ?></strong>
                    <p>Slug: <code><?php echo esc_html( $page['slug'] ); ?></code></p>
                    <p>Template: <code><?php echo esc_html( $page['template'] ?: 'default' ); ?></code></p>
                    <?php if ( $page_id ) : ?>
                        <p class="tb4-sync-ok">เชื่อมแล้ว: Page ID <?php echo absint( $page_id ); ?></p>
                        <p><a href="<?php echo esc_url( get_edit_post_link( $page_id ) ); ?>">แก้ไขหน้า</a> · <a href="<?php echo esc_url( get_permalink( $page_id ) ); ?>" target="_blank" rel="noopener">ดูหน้า</a></p>
                    <?php else : ?>
                        <p class="tb4-sync-missing">ยังไม่พบหน้า</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

function tb4_page_sync_customizer( $wp_customize ) {
    if ( ! isset( $wp_customize ) ) {
        return;
    }

    $wp_customize->add_section( 'tb4_page_sync_settings', [
        'title'    => __( 'ซิงก์หน้า / Components', 'thinkb4do' ),
        'panel'    => 'tb4_panel',
        'priority' => 36,
    ] );

    $wp_customize->add_setting( 'tb4_page_sync_auto_enabled', [
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( 'tb4_page_sync_auto_enabled', [
        'label'       => __( 'สร้าง/ผูกหน้าอัตโนมัติเมื่ออัปโหลดหรือเปิดใช้ธีม', 'thinkb4do' ),
        'description' => __( 'ระบบจะสร้างหน้าและเมนูที่จำเป็น หากยังไม่มีอยู่ใน WordPress', 'thinkb4do' ),
        'section'     => 'tb4_page_sync_settings',
        'type'        => 'checkbox',
    ] );

    $wp_customize->add_setting( 'tb4_page_sync_update_existing', [
        'default'           => false,
        'sanitize_callback' => 'wp_validate_boolean',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( 'tb4_page_sync_update_existing', [
        'label'       => __( 'อนุญาตให้อัปเดตเนื้อหาตัวอย่างทับหน้าที่มีอยู่แล้ว', 'thinkb4do' ),
        'description' => __( 'ค่าเริ่มต้นปิดไว้เพื่อไม่ให้เนื้อหาที่แก้ไว้หาย', 'thinkb4do' ),
        'section'     => 'tb4_page_sync_settings',
        'type'        => 'checkbox',
    ] );
}
add_action( 'customize_register', 'tb4_page_sync_customizer', 30 );

function tb4_page_sync_admin_bar_link( $wp_admin_bar ) {
    if ( ! current_user_can( 'edit_theme_options' ) || ! is_admin_bar_showing() ) {
        return;
    }

    $wp_admin_bar->add_node( [
        'id'    => 'tb4-page-sync',
        'title' => 'เชื่อมหน้า',
        'href'  => admin_url( 'themes.php?page=thinkb4do-page-sync' ),
        'meta'  => [ 'title' => 'เชื่อมหน้า / Components เข้า WordPress' ],
    ] );
}
add_action( 'admin_bar_menu', 'tb4_page_sync_admin_bar_link', 95 );


function tb4_legal_policy_sanitize_text( $value ) {
    return sanitize_text_field( $value );
}

function tb4_legal_policy_default( $key ) {
    $defaults = [
        'public_name'     => 'Thinkb4do.com',
        'business_status' => 'อยู่ระหว่างเตรียมข้อมูลจดทะเบียน / ยังไม่ใช้เครื่องหมายราชการหรือเครื่องหมายรับรองก่อนมีสิทธิ์จริง',
        'contact_email'   => '',
        'response_time'   => 'โดยทั่วไปภายใน 7 วันทำการ หรือตามความซับซ้อนของคำขอ',
        'last_reviewed'   => 'ยังไม่ระบุวันที่ตรวจทาน',
    ];
    return $defaults[ $key ] ?? '';
}

function tb4_legal_policy_value( $key ) {
    return get_theme_mod( 'tb4_legal_' . $key, tb4_legal_policy_default( $key ) );
}

function tb4_legal_policy_private_contact_notice() {
    return __( 'ซ่อนข้อมูลติดต่อส่วนตัวไว้ก่อน — ให้ใช้หน้า “ติดต่อและร้องเรียน” หรืออีเมลธุรกิจ/อีเมลสาธารณะเมื่อพร้อมเผยแพร่', 'thinkb4do' );
}

function tb4_legal_policy_is_private_email( $email ) {
    $email = sanitize_email( $email );
    if ( empty( $email ) ) {
        return true;
    }

    $admin_email = sanitize_email( get_option( 'admin_email' ) );
    if ( $admin_email && strtolower( $email ) === strtolower( $admin_email ) ) {
        return true;
    }

    $domain = strtolower( substr( strrchr( $email, '@' ) ?: '', 1 ) );
    $private_domains = [
        'gmail.com', 'googlemail.com', 'hotmail.com', 'outlook.com', 'live.com',
        'yahoo.com', 'icloud.com', 'me.com', 'msn.com', 'proton.me', 'protonmail.com'
    ];

    return in_array( $domain, $private_domains, true );
}

function tb4_legal_policy_public_contact_label() {
    $contact_email = sanitize_email( tb4_legal_policy_value( 'contact_email' ) );

    if ( ! empty( $contact_email ) && ! tb4_legal_policy_is_private_email( $contact_email ) ) {
        return antispambot( $contact_email );
    }

    return tb4_legal_policy_private_contact_notice();
}

function tb4_legal_status_shortcode() {
    $public_name     = tb4_legal_policy_value( 'public_name' );
    $business_status = tb4_legal_policy_value( 'business_status' );
    $contact_label   = tb4_legal_policy_public_contact_label();
    $response_time   = tb4_legal_policy_value( 'response_time' );
    $last_reviewed   = tb4_legal_policy_value( 'last_reviewed' );

    ob_start();
    ?>
    <div class="tb4-legal-status-card" style="border:1px solid #d9eee3;background:#f4fbf7;border-radius:18px;padding:18px 20px;margin:18px 0;font-family:var(--font-main,'Noto Sans Thai',sans-serif);">
        <strong style="display:block;font-size:1rem;color:#154d31;margin-bottom:8px;">สถานะข้อมูลกฎหมาย / นโยบาย</strong>
        <ul style="margin:0;padding-left:1.2rem;color:#374151;line-height:1.8;">
            <li><strong>ชื่อที่แสดงต่อสาธารณะ:</strong> <?php echo esc_html( $public_name ); ?></li>
            <li><strong>สถานะธุรกิจ:</strong> <?php echo esc_html( $business_status ); ?></li>
            <li><strong>ช่องทางติดต่อสาธารณะ:</strong> <?php echo esc_html( $contact_label ); ?></li>
            <li><strong>กรอบเวลาตอบกลับ:</strong> <?php echo esc_html( $response_time ); ?></li>
            <li><strong>ตรวจทานครั้งล่าสุด:</strong> <?php echo esc_html( $last_reviewed ); ?></li>
        </ul>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'thinkb4do_legal_status', 'tb4_legal_status_shortcode' );

function tb4_legal_policy_customizer( $wp_customize ) {
    if ( ! isset( $wp_customize ) ) {
        return;
    }

    $section = 'tb4_page_sync_settings';
    $controls = [
        'public_name' => [
            'label'       => __( 'ชื่อเว็บไซต์/ผู้ให้บริการที่แสดงในหน้านโยบาย', 'thinkb4do' ),
            'type'        => 'text',
            'sanitize'    => 'tb4_legal_policy_sanitize_text',
        ],
        'business_status' => [
            'label'       => __( 'สถานะธุรกิจ/จดทะเบียน', 'thinkb4do' ),
            'description' => __( 'ใช้ข้อความจริง เช่น อยู่ระหว่างเตรียมข้อมูลจดทะเบียน จนกว่าจะมีสิทธิ์ใช้ข้อมูลหรือเครื่องหมายทางราชการจริง', 'thinkb4do' ),
            'type'        => 'textarea',
            'sanitize'    => 'sanitize_textarea_field',
        ],
        'contact_email' => [
            'label'       => __( 'อีเมลธุรกิจ/อีเมลสาธารณะสำหรับนโยบาย', 'thinkb4do' ),
            'description' => __( 'เพื่อความปลอดภัย อย่าใช้ Gmail/อีเมลส่วนตัวหรืออีเมลผู้ดูแลระบบ หากยังไม่มีอีเมลสาธารณะ ให้ปล่อยช่องนี้ว่างไว้ ระบบจะซ่อนข้อมูลติดต่อส่วนตัวอัตโนมัติ', 'thinkb4do' ),
            'type'        => 'email',
            'sanitize'    => 'sanitize_email',
        ],
        'response_time' => [
            'label'       => __( 'กรอบเวลาตอบกลับคำขอ/ร้องเรียน', 'thinkb4do' ),
            'type'        => 'text',
            'sanitize'    => 'tb4_legal_policy_sanitize_text',
        ],
        'last_reviewed' => [
            'label'       => __( 'วันที่ตรวจทานนโยบายล่าสุด', 'thinkb4do' ),
            'type'        => 'text',
            'sanitize'    => 'tb4_legal_policy_sanitize_text',
        ],
    ];

    foreach ( $controls as $key => $control ) {
        $setting_id = 'tb4_legal_' . $key;
        $wp_customize->add_setting( $setting_id, [
            'default'           => tb4_legal_policy_default( $key ),
            'sanitize_callback' => $control['sanitize'],
            'transport'         => 'refresh',
        ] );
        $wp_customize->add_control( $setting_id, [
            'label'       => $control['label'],
            'description' => $control['description'] ?? '',
            'section'     => $section,
            'type'        => $control['type'],
        ] );
    }
}
add_action( 'customize_register', 'tb4_legal_policy_customizer', 31 );
