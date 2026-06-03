<?php
/**
 * Archive / Search / Category template
 * @package Thinkb4do
 */
get_header();
?>
<main id="main" class="tb4-archive-main" style="padding:var(--space-3xl) 0;background:var(--tb4-gray-100);min-height:60vh;" data-tb4-live-component="archive_main" data-tb4-live-title="หน้าคลังบทความ / ค้นหา">
  <div class="container">

    <!-- Archive Header -->
    <div data-tb4-live-component="archive_header" data-tb4-live-title="หัวหน้าคลังบทความ" style="background:#fff;border-radius:var(--radius-xl);padding:var(--space-xl) var(--space-2xl);margin-bottom:var(--space-xl);border:1px solid var(--tb4-gray-100);">
      <?php if ( is_search() ) : ?>
        <div style="font-size:.85rem;color:var(--tb4-gray-500);margin-bottom:6px;">ผลการค้นหา</div>
        <h1 style="font-size:1.8rem;">"<?php the_search_query(); ?>"</h1>
        <p style="color:var(--tb4-gray-500);margin-top:6px;">พบ <?php echo $wp_query->found_posts; ?> รายการ</p>
      <?php elseif ( is_category() ) : ?>
        <div style="font-size:.85rem;color:var(--tb4-green);font-weight:600;margin-bottom:6px;text-transform:uppercase;letter-spacing:.05em;">หมวดหมู่</div>
        <h1 style="font-size:1.8rem;"><?php single_cat_title(); ?></h1>
        <?php if ( category_description() ) : ?>
          <p style="color:var(--tb4-gray-500);margin-top:8px;"><?php echo category_description(); ?></p>
        <?php endif; ?>
      <?php elseif ( is_tag() ) : ?>
        <div style="font-size:.85rem;color:var(--tb4-orange);font-weight:600;margin-bottom:6px;"># แท็ก</div>
        <h1 style="font-size:1.8rem;"><?php single_tag_title(); ?></h1>
      <?php elseif ( is_author() ) : ?>
        <div style="display:flex;align-items:center;gap:var(--space-md);">
          <?php echo get_avatar( get_the_author_meta('ID'), 64, '', '', ['style'=>'border-radius:50%;flex-shrink:0;'] ); ?>
          <div>
            <div style="font-size:.85rem;color:var(--tb4-gray-500);margin-bottom:4px;">Creator</div>
            <h1 style="font-size:1.6rem;"><?php the_author(); ?></h1>
          </div>
        </div>
      <?php else : ?>
        <h1 style="font-size:1.8rem;"><?php the_archive_title(); ?></h1>
      <?php endif; ?>
    </div>

    <div style="display:grid;grid-template-columns:1fr 300px;gap:var(--space-xl);align-items:start;">

      <!-- Posts -->
      <div>
        <?php if ( have_posts() ) : ?>
          <div class="tb4-archive-post-list" data-tb4-live-component="archive_post_list" data-tb4-live-title="รายการบทความในคลัง" style="display:flex;flex-direction:column;gap:var(--space-md);">
            <?php while ( have_posts() ) : the_post(); ?>
            <article class="card" style="display:flex;gap:var(--space-lg);padding:var(--space-lg);" data-tb4-live-component="archive_post_card_<?php the_ID(); ?>" data-tb4-live-title="การ์ดบทความ">
              <?php if ( has_post_thumbnail() ) : ?>
              <a href="<?php the_permalink(); ?>" style="flex-shrink:0;width:180px;height:120px;border-radius:var(--radius-md);overflow:hidden;display:block;">
                <?php the_post_thumbnail('tb4-card', ['style'=>'width:100%;height:100%;object-fit:cover;']); ?>
              </a>
              <?php endif; ?>
              <div style="flex:1;min-width:0;">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;flex-wrap:wrap;">
                  <?php $cats = get_the_category(); if ($cats) : ?>
                    <span class="badge badge-green"><?php echo esc_html($cats[0]->name); ?></span>
                  <?php endif; ?>
                  <span style="font-size:.78rem;color:var(--tb4-gray-500);"><?php echo get_the_date('j M Y'); ?></span>
                </div>
                <h2 style="font-size:1.05rem;font-weight:600;margin-bottom:6px;line-height:1.4;">
                  <a href="<?php the_permalink(); ?>" style="color:var(--tb4-black);"><?php the_title(); ?></a>
                </h2>
                <p style="font-size:.875rem;color:var(--tb4-gray-700);line-height:1.5;margin-bottom:10px;"><?php echo wp_trim_words(get_the_excerpt(), 22); ?></p>
                <div style="display:flex;align-items:center;justify-content:space-between;gap:var(--space-md);">
                  <div style="display:flex;align-items:center;gap:6px;font-size:.8rem;color:var(--tb4-gray-500);">
                    <?php echo get_avatar(get_the_author_meta('ID'), 22, '', '', ['style'=>'border-radius:50%;']); ?>
                    <?php the_author(); ?>
                  </div>
                  <a href="<?php the_permalink(); ?>" class="btn btn-secondary btn-sm">อ่านต่อ →</a>
                </div>
              </div>
            </article>
            <?php endwhile; ?>
          </div>

          <?php if ( function_exists( 'tb4_v182_render_current_page_extra_components' ) ) { tb4_v182_render_current_page_extra_components(); } ?>
          <!-- Pagination -->
          <div style="margin-top:var(--space-xl);">
            <?php the_posts_pagination([
              'mid_size'  => 2,
              'prev_text' => '← ก่อนหน้า',
              'next_text' => 'ถัดไป →',
              'before_page_number' => '',
            ]); ?>
          </div>

        <?php else : ?>
          <div style="text-align:center;padding:var(--space-3xl);background:#fff;border-radius:var(--radius-lg);">
            <p style="font-size:3rem;margin-bottom:var(--space-md);">🔍</p>
            <h2 style="font-size:1.4rem;margin-bottom:var(--space-sm);">ไม่พบเนื้อหา</h2>
            <p style="color:var(--tb4-gray-500);">ลองใช้คำค้นหาอื่น หรือดูเนื้อหาหมวดหมู่อื่น</p>
            <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-primary" style="margin-top:var(--space-lg);">กลับหน้าแรก</a>
          </div>
        <?php endif; ?>
      </div>

      <!-- Sidebar -->
      <?php get_sidebar(); ?>

    </div>
  </div>
</main>
<?php get_footer(); ?>
