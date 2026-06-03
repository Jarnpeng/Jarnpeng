<?php
/**
 * Main template — blog/archive fallback
 */
get_header();
?>
<main id="main" class="tb4-index-main" style="padding: var(--space-3xl) 0;" data-tb4-live-component="index_main" data-tb4-live-title="หน้ารวมบทความ">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 320px;gap:var(--space-2xl);align-items:start;">
      <div>
        <?php if ( have_posts() ) : ?>
          <div class="section-heading">
            <h1><?php
              if (is_home()) { _e('บทความล่าสุด','thinkb4do'); }
              elseif (is_archive()) { the_archive_title(); }
              elseif (is_search()) { printf(__('ผลการค้นหา: %s','thinkb4do'), get_search_query()); }
              else { the_title(); }
            ?></h1>
          </div>
          <div class="tb4-post-list" style="display:flex;flex-direction:column;gap:var(--space-lg);" data-tb4-live-component="post_list" data-tb4-live-title="รายการบทความ">
            <?php while (have_posts()) : the_post(); ?>
            <article class="post-card" style="display:flex;gap:var(--space-lg);" data-tb4-live-component="post_card_<?php the_ID(); ?>" data-tb4-live-title="การ์ดบทความ">
              <?php if (has_post_thumbnail()) : ?>
              <a href="<?php the_permalink(); ?>" style="flex-shrink:0;width:200px;height:130px;border-radius:var(--radius-md);overflow:hidden;display:block;">
                <?php the_post_thumbnail('tb4-card','style=width:100%;height:100%;object-fit:cover;'); ?>
              </a>
              <?php endif; ?>
              <div style="flex:1;">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                  <?php $cats = get_the_category(); if ($cats) echo '<span class="badge badge-green">' . esc_html($cats[0]->name) . '</span>'; ?>
                  <span style="font-size:.78rem;color:var(--tb4-gray-500);"><?php echo get_the_date('j M Y'); ?></span>
                </div>
                <h2 style="font-size:1.1rem;margin-bottom:6px;"><a href="<?php the_permalink(); ?>" style="color:var(--tb4-black);"><?php the_title(); ?></a></h2>
                <p style="font-size:.88rem;margin-bottom:var(--space-sm);"><?php echo wp_trim_words(get_the_excerpt(),25); ?></p>
                <div style="display:flex;align-items:center;gap:var(--space-md);">
                  <div style="display:flex;align-items:center;gap:6px;font-size:.8rem;">
                    <?php echo get_avatar(get_the_author_meta('ID'),24,'','',['class'=>'','extra_attr'=>'style=border-radius:50%;']); ?>
                    <?php the_author(); ?>
                  </div>
                  <a href="<?php the_permalink(); ?>" class="btn btn-secondary btn-sm">อ่านต่อ</a>
                </div>
              </div>
            </article>
            <?php endwhile; ?>
          </div>
          <?php if ( function_exists( 'tb4_v182_render_current_page_extra_components' ) ) { tb4_v182_render_current_page_extra_components(); } ?>
          <div style="margin-top:var(--space-xl);"><?php the_posts_pagination(['mid_size'=>2,'prev_text'=>'← ก่อนหน้า','next_text'=>'ถัดไป →']); ?></div>
        <?php else : ?>
          <div style="text-align:center;padding:var(--space-3xl) 0;">
            <p style="font-size:3rem;margin-bottom:var(--space-md);">📄</p>
            <h2>ไม่พบเนื้อหา</h2>
            <p>ลองค้นหาด้วยคำอื่น หรือกลับหน้าแรก</p>
            <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-primary" style="margin-top:var(--space-lg);">กลับหน้าแรก</a>
          </div>
        <?php endif; ?>
      </div>
      <?php get_sidebar(); ?>
    </div>
  </div>
</main>
<?php get_footer(); ?>
