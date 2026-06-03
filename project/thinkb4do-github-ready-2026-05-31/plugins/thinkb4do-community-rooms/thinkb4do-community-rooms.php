<?php
/**
 * Plugin Name: Thinkb4do Community Rooms & Vote
 * Description: เพิ่ม "ห้องสนทนา" (Think Rooms) และระบบโหวต 4 แบบ — เห็นด้วย / น่าสนใจ / มีประโยชน์ / ต้องตรวจสอบ
 * Version: 1.0.0
 * Author: Thinkb4do
 * Text Domain: thinkb4do-community-rooms
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default Think Rooms — mapped onto the existing tb4_community_topic taxonomy
 * so they appear as taxonomy archives without needing a new tax.
 */
function tb4d_rooms_definitions() {
    return [
        'idea'    => [ 'name' => 'ห้องไอเดีย',   'desc' => 'พื้นที่แชร์ไอเดียและแรงบันดาลใจ' ],
        'qna'     => [ 'name' => 'ห้องคำถาม',    'desc' => 'ถามได้ทุกเรื่องที่อยากรู้' ],
        'review'  => [ 'name' => 'ห้องรีวิว',    'desc' => 'รีวิวสินค้า บริการ และประสบการณ์' ],
        'issue'   => [ 'name' => 'ห้องแจ้งปัญหา', 'desc' => 'รายงานปัญหาเพื่อให้ชุมชนช่วยกัน' ],
        'general' => [ 'name' => 'ห้องชุมชน',    'desc' => 'คุยกันสบาย ๆ เรื่องทั่วไป' ],
        'update'  => [ 'name' => 'ห้องอัปเดต',   'desc' => 'ข่าวสารและประกาศจากชุมชน' ],
    ];
}

/**
 * Seed default rooms once the topic taxonomy is registered.
 */
function tb4d_rooms_seed_terms() {
    if ( ! taxonomy_exists( 'tb4_community_topic' ) ) {
        return;
    }
    if ( get_option( 'tb4d_rooms_seeded_v1' ) ) {
        return;
    }
    foreach ( tb4d_rooms_definitions() as $slug => $room ) {
        $term_slug = 'room-' . $slug;
        if ( ! term_exists( $term_slug, 'tb4_community_topic' ) ) {
            wp_insert_term( $room['name'], 'tb4_community_topic', [
                'slug'        => $term_slug,
                'description' => $room['desc'],
            ] );
        }
    }
    update_option( 'tb4d_rooms_seeded_v1', 1 );
}
add_action( 'init', 'tb4d_rooms_seed_terms', 30 );

/**
 * Vote labels (Thinkb4do branded — never use "upvote/downvote").
 */
function tb4d_vote_labels() {
    return [
        'agree'       => [ 'label' => 'เห็นด้วย',     'weight' =>  1 ],
        'interesting' => [ 'label' => 'น่าสนใจ',      'weight' =>  1 ],
        'useful'      => [ 'label' => 'มีประโยชน์',  'weight' =>  1 ],
        'needs_check' => [ 'label' => 'ต้องตรวจสอบ', 'weight' => -1 ],
    ];
}

/**
 * Recompute the aggregate vote score for a post.
 */
function tb4d_vote_recalc_score( $post_id ) {
    $post_id = absint( $post_id );
    if ( ! $post_id ) { return 0; }
    $counts = (array) get_post_meta( $post_id, '_tb4d_vote_counts', true );
    $score  = 0;
    foreach ( tb4d_vote_labels() as $key => $row ) {
        $n = isset( $counts[ $key ] ) ? (int) $counts[ $key ] : 0;
        $score += $n * (int) $row['weight'];
    }
    update_post_meta( $post_id, '_tb4d_vote_score', $score );
    return $score;
}

/**
 * Render the vote bar for a post. Safe to call from any template.
 */
function tb4d_render_vote_bar( $post_id ) {
    $post_id = absint( $post_id );
    if ( ! $post_id ) { return ''; }
    $counts  = (array) get_post_meta( $post_id, '_tb4d_vote_counts', true );
    $score   = (int) get_post_meta( $post_id, '_tb4d_vote_score', true );
    $user_id = get_current_user_id();
    $mine    = $user_id ? (string) get_user_meta( $user_id, '_tb4d_vote_' . $post_id, true ) : '';

    ob_start();
    ?>
    <div class="tb4d-vote-bar" data-tb4d-vote-bar data-post-id="<?php echo esc_attr( $post_id ); ?>" role="group" aria-label="ให้ความเห็นกับโพสต์นี้">
      <?php foreach ( tb4d_vote_labels() as $key => $row ) :
          $n = isset( $counts[ $key ] ) ? (int) $counts[ $key ] : 0;
          $is_mine = ( $mine === $key );
      ?>
        <button type="button"
                class="tb4d-vote-btn<?php echo $is_mine ? ' is-mine' : ''; ?>"
                data-vote="<?php echo esc_attr( $key ); ?>"
                aria-pressed="<?php echo $is_mine ? 'true' : 'false'; ?>"
                aria-label="<?php echo esc_attr( $row['label'] ); ?>">
          <span class="tb4d-vote-label"><?php echo esc_html( $row['label'] ); ?></span>
          <span class="tb4d-vote-count"><?php echo esc_html( $n ); ?></span>
        </button>
      <?php endforeach; ?>
      <span class="tb4d-vote-score" aria-label="คะแนนรวม">คะแนน <?php echo esc_html( $score ); ?></span>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Inject vote bar into the existing feed card and single template.
 * Uses a post-render output filter via late action — we hook into 'the_content'
 * for singular community posts only.
 */
function tb4d_append_vote_bar_to_content( $content ) {
    if ( is_singular( 'tb4_community_post' ) && in_the_loop() && is_main_query() ) {
        $content .= tb4d_render_vote_bar( get_the_ID() );
    }
    return $content;
}
add_filter( 'the_content', 'tb4d_append_vote_bar_to_content', 20 );

/**
 * AJAX: cast or change a vote.
 */
function tb4d_ajax_vote() {
    // Light nonce check — reuse community nonce if present, otherwise require WP login + own nonce.
    $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
    $ok = wp_verify_nonce( $nonce, 'tb4d_vote' ) || wp_verify_nonce( $nonce, 'tb4c_nonce' ) || wp_verify_nonce( $nonce, 'tb4_nonce' );
    if ( ! $ok ) {
        wp_send_json_error( [ 'message' => 'ระบบขอให้ลองอีกครั้ง' ], 403 );
    }
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( [ 'message' => 'กรุณาเข้าสู่ระบบก่อนโหวต' ], 401 );
    }
    $post_id = absint( $_POST['post_id'] ?? 0 );
    $vote    = sanitize_key( $_POST['vote'] ?? '' );
    $labels  = tb4d_vote_labels();
    if ( ! $post_id || ! isset( $labels[ $vote ] ) || get_post_type( $post_id ) !== 'tb4_community_post' ) {
        wp_send_json_error( [ 'message' => 'คำขอไม่ถูกต้อง' ], 400 );
    }
    $user_id = get_current_user_id();
    $key     = '_tb4d_vote_' . $post_id;
    $prev    = (string) get_user_meta( $user_id, $key, true );
    $counts  = (array) get_post_meta( $post_id, '_tb4d_vote_counts', true );
    foreach ( $labels as $k => $_ ) {
        if ( ! isset( $counts[ $k ] ) ) { $counts[ $k ] = 0; }
    }

    if ( $prev === $vote ) {
        // toggle off
        $counts[ $vote ] = max( 0, (int) $counts[ $vote ] - 1 );
        delete_user_meta( $user_id, $key );
        $mine = '';
    } else {
        if ( '' !== $prev && isset( $counts[ $prev ] ) ) {
            $counts[ $prev ] = max( 0, (int) $counts[ $prev ] - 1 );
        }
        $counts[ $vote ] = (int) $counts[ $vote ] + 1;
        update_user_meta( $user_id, $key, $vote );
        $mine = $vote;
    }
    update_post_meta( $post_id, '_tb4d_vote_counts', $counts );
    $score = tb4d_vote_recalc_score( $post_id );

    wp_send_json_success( [
        'counts' => $counts,
        'score'  => $score,
        'mine'   => $mine,
    ] );
}
add_action( 'wp_ajax_tb4d_vote', 'tb4d_ajax_vote' );

/**
 * Tiny JS handler for vote buttons (delegated).
 */
function tb4d_rooms_vote_inline_js() {
    if ( is_admin() ) { return; }
    $nonce = wp_create_nonce( 'tb4d_vote' );
    $ajax  = esc_url( admin_url( 'admin-ajax.php' ) );
    ?>
<script>
(function(){
  if(window.__tb4dVoteBound) return; window.__tb4dVoteBound = true;
  var AJAX = <?php echo wp_json_encode( $ajax ); ?>;
  var NONCE = <?php echo wp_json_encode( $nonce ); ?>;
  document.addEventListener('click', function(e){
    var btn = e.target && e.target.closest ? e.target.closest('.tb4d-vote-btn') : null;
    if(!btn) return;
    var bar = btn.closest('[data-tb4d-vote-bar]');
    if(!bar) return;
    e.preventDefault();
    var postId = bar.getAttribute('data-post-id');
    var vote = btn.getAttribute('data-vote');
    var fd = new FormData();
    fd.append('action','tb4d_vote');
    fd.append('nonce',NONCE);
    fd.append('post_id',postId);
    fd.append('vote',vote);
    btn.disabled = true;
    fetch(AJAX,{method:'POST',credentials:'same-origin',body:fd})
      .then(function(r){return r.json();})
      .then(function(j){
        btn.disabled = false;
        if(!j || !j.success){ return; }
        var counts = j.data.counts || {};
        var mine = j.data.mine || '';
        bar.querySelectorAll('.tb4d-vote-btn').forEach(function(b){
          var k = b.getAttribute('data-vote');
          var c = b.querySelector('.tb4d-vote-count');
          if(c && counts[k] !== undefined) c.textContent = counts[k];
          if(k === mine){ b.classList.add('is-mine'); b.setAttribute('aria-pressed','true'); }
          else { b.classList.remove('is-mine'); b.setAttribute('aria-pressed','false'); }
        });
        var s = bar.querySelector('.tb4d-vote-score');
        if(s){ s.textContent = 'คะแนน ' + (j.data.score || 0); }
      })
      .catch(function(){ btn.disabled = false; });
  }, false);
})();
</script>
    <?php
}
add_action( 'wp_footer', 'tb4d_rooms_vote_inline_js', 30 );

/**
 * Room chip helper — show on cards if post is in a room-* term.
 */
function tb4d_get_post_room_chip( $post_id ) {
    $terms = get_the_terms( $post_id, 'tb4_community_topic' );
    if ( empty( $terms ) || is_wp_error( $terms ) ) { return ''; }
    foreach ( $terms as $t ) {
        if ( 0 === strpos( $t->slug, 'room-' ) ) {
            $url = get_term_link( $t );
            if ( is_wp_error( $url ) ) { return ''; }
            return '<a class="tb4d-room-chip" href="' . esc_url( $url ) . '">' . esc_html( $t->name ) . '</a>';
        }
    }
    return '';
}
