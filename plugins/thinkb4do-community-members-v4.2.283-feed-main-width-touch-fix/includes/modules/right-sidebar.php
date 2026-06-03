<?php
/**
 * Right Sidebar module for Thinkb4do Community.
 * v4.2.200: keep right position, remove people/follow duplicate section, keep only lightweight topic guide.
 *
 * @package Thinkb4doCommunity
 */
defined( 'ABSPATH' ) || exit;
?>
    <aside class="tb4c-social-right tb4c-right-sidebar-final tb4c-right-visible-final" data-tb4c-module="right-sidebar" aria-label="หัวข้อแนะนำ">
      <section class="tb4c-right-final-list tb4c-right-plain-list" id="community-rules">
        <div class="tb4c-right-final-head">
          <div>
            <h3>หัวข้อสำคัญ</h3>
          </div>
        </div>

        <div class="tb4c-right-final-section tb4c-right-final-section-only">
          <div class="tb4c-topic-compact-list tb4c-right-final-topics">
            <?php if ( ! empty( $trending_terms ) && ! is_wp_error( $trending_terms ) ) : ?>
              <?php foreach ( array_slice( $trending_terms, 0, 8 ) as $term ) : ?>
                <a class="tb4c-topic-link tb4c-right-final-topic" href="<?php echo esc_url( get_term_link( $term ) ); ?>">
                  <span>#<?php echo esc_html( $term->name ); ?></span>
                </a>
              <?php endforeach; ?>
            <?php else : ?>
              <p class="tb4c-muted-text tb4c-trending-empty">ยังไม่มีหัวข้อแนะนำ</p>
            <?php endif; ?>
          </div>
        </div>
      </section>
    </aside>
