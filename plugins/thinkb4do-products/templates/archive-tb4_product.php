<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
echo do_shortcode( '[thinkb4do_products layout="archive" limit="24"]' );
get_footer();
