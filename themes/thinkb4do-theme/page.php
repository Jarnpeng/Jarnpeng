<?php get_header(); ?>
<?php
$current_page_post = get_post();
$is_plugin_owned_page = function_exists( 'tb4_is_plugin_owned_page_context' ) && tb4_is_plugin_owned_page_context();
$is_member_area_page = false;
if ( $current_page_post instanceof WP_Post ) {
    $is_member_area_page = is_page( 'member-area' )
        || has_shortcode( (string) $current_page_post->post_content, 'thinkb4do_member_area' )
        || ( function_exists( 'has_block' ) && has_block( 'thinkb4do/member-area', $current_page_post ) );
}

if ( $is_plugin_owned_page ) :
?>
<main id="main" class="tb4-page-main tb4-page-main--plugin-owned tb4-plugin-owned-shell" role="main" data-tb4-live-component="plugin_owned_page_shell" data-tb4-live-title="พื้นที่ปลั๊กอิน">
    <?php while ( have_posts() ) : the_post(); ?>
        <div id="post-<?php the_ID(); ?>" <?php post_class( 'tb4-plugin-owned-content' ); ?>>
            <?php the_content(); ?>
        </div>
    <?php endwhile; ?>
</main>
<?php
else :
$main_class      = 'tb4-page-main' . ( $is_member_area_page ? ' tb4-page-main--member-area' : '' );
$container_class = 'container' . ( $is_member_area_page ? ' tb4-page-container--member-area' : '' );
$article_class   = 'tb4-page-article' . ( $is_member_area_page ? ' tb4-page-article--member-area' : '' );
$entry_class     = 'entry-content' . ( $is_member_area_page ? ' tb4-page-entry--member-area' : '' );
$main_style      = $is_member_area_page ? 'padding:clamp(20px,2.8vw,44px) 0 0;' : 'padding:var(--space-3xl) 0;';
$container_style = $is_member_area_page ? 'max-width:none;width:100%;padding-left:0;padding-right:0;' : 'max-width:900px;';
?>
<main id="main" class="<?php echo esc_attr( $main_class ); ?>" style="<?php echo esc_attr( $main_style ); ?>" data-tb4-live-component="page_main" data-tb4-live-title="โครงหน้าหลัก">
  <div class="<?php echo esc_attr( $container_class ); ?>" style="<?php echo esc_attr( $container_style ); ?>">
    <?php while(have_posts()): the_post(); ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class( $article_class ); ?> data-tb4-live-component="page_content" data-tb4-live-title="เนื้อหาหน้านี้">
      <h1 style="margin-bottom:var(--space-xl);"><?php the_title(); ?></h1>
      <div class="<?php echo esc_attr( $entry_class ); ?>" style="line-height:1.85;" data-tb4-live-component="page_blocks" data-tb4-live-title="บล็อก Gutenberg / เนื้อหาย่อย"><?php the_content(); ?></div>
    </article>
    <?php endwhile; ?>
    <?php if ( function_exists( 'tb4_v182_render_current_page_extra_components' ) ) { tb4_v182_render_current_page_extra_components(); } ?>
  </div>
</main>
<?php endif; ?>
<?php get_footer();
