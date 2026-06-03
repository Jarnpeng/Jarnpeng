<?php /* Single post */ get_header(); ?>
<main id="main" class="tb4-single-main" style="padding:var(--space-3xl) 0;" data-tb4-live-component="single_main" data-tb4-live-title="โครงหน้าบทความ">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 300px;gap:var(--space-2xl);align-items:start;">
      <article id="post-<?php the_ID(); ?>" <?php post_class('tb4-single-article'); ?> data-tb4-live-component="single_content" data-tb4-live-title="เนื้อหาบทความ">
        <?php while(have_posts()): the_post(); ?>
        <div style="margin-bottom:var(--space-xl);">
          <?php $cats = get_the_category(); if ($cats) echo '<span class="badge badge-green" style="margin-bottom:var(--space-md);display:inline-flex;">' . esc_html($cats[0]->name) . '</span>'; ?>
          <h1 style="font-size:clamp(1.6rem,3vw,2.4rem);margin-bottom:var(--space-md);"><?php the_title(); ?></h1>
          <div style="display:flex;align-items:center;gap:var(--space-lg);color:var(--tb4-gray-500);font-size:.85rem;padding-bottom:var(--space-lg);border-bottom:1px solid var(--tb4-gray-100);">
            <div style="display:flex;align-items:center;gap:8px;">
              <?php echo get_avatar(get_the_author_meta('ID'),32,'','',['class'=>'','extra_attr'=>'style=border-radius:50%;']); ?>
              <span><?php the_author(); ?></span>
            </div>
            <span><?php echo get_the_date('j M Y'); ?></span>
            <span><?php echo get_the_modified_date('j M Y') !== get_the_date('j M Y') ? 'อัปเดต '.get_the_modified_date('j M Y') : ''; ?></span>
          </div>
        </div>
        <?php if(has_post_thumbnail()): ?><div style="margin-bottom:var(--space-xl);border-radius:var(--radius-lg);overflow:hidden;"><?php the_post_thumbnail('tb4-hero','style=width:100%;height:auto;'); ?></div><?php endif; ?>
        <div class="entry-content" style="max-width:68ch;line-height:1.85;" data-tb4-live-component="single_blocks" data-tb4-live-title="บล็อก Gutenberg / เนื้อหาย่อย"><?php the_content(); ?></div>
        <div style="margin-top:var(--space-xl);padding-top:var(--space-xl);border-top:1px solid var(--tb4-gray-100);"><?php the_tags('<div style="display:flex;flex-wrap:wrap;gap:8px;">','','</div>'); ?></div>
        <?php if ( function_exists( 'tb4_v182_render_current_page_extra_components' ) ) { tb4_v182_render_current_page_extra_components(); } ?>
        <?php comments_template(); ?>
        <?php endwhile; ?>
      </article>
      <?php get_sidebar(); ?>
    </div>
  </div>
</main>
<?php get_footer();
