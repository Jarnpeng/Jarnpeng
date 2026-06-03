<form role="search" method="get" class="search-form tb4-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
  <label class="screen-reader-text tb4-search-label" for="tb4-search-field"><?php esc_html_e( 'ค้นหาเนื้อหา', 'thinkb4do' ); ?></label>
  <div class="search-bar tb4-search-bar">
    <i class="ph ph-magnifying-glass" aria-hidden="true"></i>
    <input id="tb4-search-field"
           type="search"
           class="search-field"
           placeholder="<?php echo esc_attr_x( 'ค้นหา เช่น บทความ ไอเดีย หรือระบบ', 'placeholder', 'thinkb4do' ); ?>"
           value="<?php echo esc_attr( get_search_query() ); ?>"
           name="s"
           aria-label="<?php esc_attr_e( 'ค้นหาเนื้อหา', 'thinkb4do' ); ?>">
    <button type="submit" class="search-submit btn btn-primary btn-sm" aria-label="ค้นหา">
      <i class="ph ph-magnifying-glass" aria-hidden="true"></i>
      <span><?php echo esc_html_x( 'ค้นหา', 'submit button', 'thinkb4do' ); ?></span>
    </button>
  </div>
</form>
