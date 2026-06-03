<?php
/**
 * Template: Home Discovery Feed — moved from theme into Community plugin v4.2.74.
 */
defined( 'ABSPATH' ) || exit;

$primary_url   = get_theme_mod( 'tb4_hero_primary_url', home_url( '/system-guide/' ) );
$secondary_url = get_theme_mod( 'tb4_hero_secondary_url', home_url( '/blog/' ) );

if ( ! function_exists( 'tb4_home_v247_clean_text' ) ) {
    function tb4_home_v247_clean_text( $text, $words = 22 ) {
        $text = wp_strip_all_tags( (string) $text );
        $text = preg_replace( '/\s+/u', ' ', $text );
        $text = trim( $text );

        if ( '' === $text ) {
            return '';
        }

        return wp_trim_words( $text, $words, '…' );
    }
}


if ( ! function_exists( 'tb4_home_v247_first_char' ) ) {
    function tb4_home_v247_first_char( $text ) {
        $text = trim( wp_strip_all_tags( (string) $text ) );

        if ( '' === $text ) {
            return 'T';
        }

        return function_exists( 'mb_substr' ) ? mb_substr( $text, 0, 1, 'UTF-8' ) : substr( $text, 0, 1 );
    }
}

if ( ! function_exists( 'tb4_home_v247_is_home_url' ) ) {
    function tb4_home_v247_is_home_url( $url ) {
        $home = untrailingslashit( home_url( '/' ) );
        $url  = untrailingslashit( (string) $url );

        return $url === $home || $url === home_url( '/' );
    }
}

if ( ! function_exists( 'tb4_home_v247_get_post_summary' ) ) {
    function tb4_home_v247_get_post_summary( $post_id ) {
        $excerpt = get_the_excerpt( $post_id );

        if ( ! $excerpt ) {
            $excerpt = get_post_field( 'post_content', $post_id );
        }

        return tb4_home_v247_clean_text( $excerpt, 22 );
    }
}

if ( ! function_exists( 'tb4_home_v247_get_menu_feed_items' ) ) {
    function tb4_home_v247_get_menu_feed_items( $limit = 8 ) {
        $locations = get_nav_menu_locations();
        $menu_id   = isset( $locations['primary'] ) ? (int) $locations['primary'] : 0;
        $items     = $menu_id ? wp_get_nav_menu_items( $menu_id ) : [];
        $feed      = [];

        if ( empty( $items ) || is_wp_error( $items ) ) {
            return [];
        }

        foreach ( $items as $menu_item ) {
            if ( ! empty( $menu_item->menu_item_parent ) ) {
                continue;
            }

            $url = ! empty( $menu_item->url ) ? $menu_item->url : '#';

            if ( '#' === $url || tb4_home_v247_is_home_url( $url ) ) {
                continue;
            }

            $object_id   = isset( $menu_item->object_id ) ? (int) $menu_item->object_id : 0;
            $label       = tb4_home_v247_clean_text( $menu_item->title, 8 );
            $description = tb4_home_v247_clean_text( $menu_item->description ?: $menu_item->attr_title, 22 );
            $image       = '';
            $meta        = __( 'เมนูหลัก', 'thinkb4do' );
            $badge       = __( 'แนะนำ', 'thinkb4do' );
            $score       = max( 1, 100 - (int) $menu_item->menu_order );

            if ( 'taxonomy' === $menu_item->type && $object_id ) {
                $term = get_term( $object_id, $menu_item->object );

                if ( $term && ! is_wp_error( $term ) ) {
                    $description = $description ?: tb4_home_v247_clean_text( term_description( $term, $menu_item->object ), 22 );
                    $meta        = sprintf( _n( '%s รายการ', '%s รายการ', (int) $term->count, 'thinkb4do' ), number_format_i18n( (int) $term->count ) );
                    $badge       = __( 'หมวดน่าสนใจ', 'thinkb4do' );
                    $score      += (int) $term->count;

                    $term_posts = new WP_Query([
                        'post_type'           => 'post',
                        'posts_per_page'      => 1,
                        'ignore_sticky_posts' => true,
                        'no_found_rows'       => true,
                        'tax_query'           => [[
                            'taxonomy' => $menu_item->object,
                            'field'    => 'term_id',
                            'terms'    => $object_id,
                        ]],
                    ]);

                    if ( $term_posts->have_posts() ) {
                        $term_posts->the_post();
                        $image = get_the_post_thumbnail_url( get_the_ID(), 'tb4-thumb' );
                        wp_reset_postdata();
                    }
                }
            } elseif ( 'post_type' === $menu_item->type && $object_id ) {
                $post_object = get_post( $object_id );

                if ( $post_object instanceof WP_Post ) {
                    $description = $description ?: tb4_home_v247_get_post_summary( $object_id );
                    $image       = get_the_post_thumbnail_url( $object_id, 'tb4-thumb' );
                    $post_type   = get_post_type_object( $post_object->post_type );
                    $meta        = $post_type && ! empty( $post_type->labels->singular_name ) ? $post_type->labels->singular_name : __( 'หน้าแนะนำ', 'thinkb4do' );
                    $badge       = ( 'page' === $post_object->post_type ) ? __( 'พื้นที่หลัก', 'thinkb4do' ) : __( 'เนื้อหาเด่น', 'thinkb4do' );
                    $score      += (int) get_comments_number( $object_id );
                }
            } elseif ( 'custom' === $menu_item->type ) {
                $badge = __( 'ทางลัด', 'thinkb4do' );
            }

            if ( '' === $description ) {
                $description = __( 'ทางเข้าหลักที่คัดไว้เพื่อให้เริ่มใช้งานและติดตามเรื่องสำคัญได้เร็วขึ้น', 'thinkb4do' );
            }

            $feed[] = [
                'title'       => $label,
                'url'         => $url,
                'description' => $description,
                'image'       => $image,
                'meta'        => $meta,
                'badge'       => $badge,
                'score'       => $score,
                'order'       => (int) $menu_item->menu_order,
            ];
        }

        usort( $feed, static function( $a, $b ) {
            if ( $a['score'] === $b['score'] ) {
                return $a['order'] <=> $b['order'];
            }

            return $b['score'] <=> $a['score'];
        } );

        return array_slice( $feed, 0, max( 1, (int) $limit ) );
    }
}

if ( ! function_exists( 'tb4_home_v247_fallback_menu_feed' ) ) {
    function tb4_home_v247_fallback_menu_feed() {
        return [
            [
                'title'       => __( 'ชุมชน', 'thinkb4do' ),
                'url'         => home_url( '/community/' ),
                'description' => __( 'พื้นที่ติดตามเรื่องใหม่ พูดคุย และดูความเคลื่อนไหวที่เกี่ยวข้องกับผู้ใช้', 'thinkb4do' ),
                'image'       => '',
                'meta'        => __( 'Social Feed', 'thinkb4do' ),
                'badge'       => __( 'น่าสนใจ', 'thinkb4do' ),
                'score'       => 98,
                'order'       => 1,
            ],
            [
                'title'       => __( 'ผลิตภัณฑ์', 'thinkb4do' ),
                'url'         => home_url( '/products/' ),
                'description' => __( 'รวมผลิตภัณฑ์ ต้นแบบ ระบบเปรียบเทียบ และแนวทางเชื่อมต่อบริการภายนอก', 'thinkb4do' ),
                'image'       => '',
                'meta'        => __( 'Product Hub', 'thinkb4do' ),
                'badge'       => __( 'อัปเดต', 'thinkb4do' ),
                'score'       => 94,
                'order'       => 2,
            ],
            [
                'title'       => __( 'ระบบ', 'thinkb4do' ),
                'url'         => home_url( '/system-guide/' ),
                'description' => __( 'เริ่มจากเป้าหมาย วางโครง และต่อยอดระบบให้ใช้งานจริงอย่างเป็นขั้นตอน', 'thinkb4do' ),
                'image'       => '',
                'meta'        => __( 'Thinkb4do System', 'thinkb4do' ),
                'badge'       => __( 'เริ่มต้น', 'thinkb4do' ),
                'score'       => 90,
                'order'       => 3,
            ],
        ];
    }
}



if ( ! function_exists( 'tb4_home_v248_get_media_cover' ) ) {
    function tb4_home_v248_get_media_cover( $post_id ) {
        $post_id = absint( $post_id );

        if ( ! $post_id ) {
            return '';
        }

        if ( has_post_thumbnail( $post_id ) ) {
            $thumb = get_the_post_thumbnail_url( $post_id, 'medium_large' );
            if ( $thumb ) {
                return $thumb;
            }
        }

        $attachment_id = absint( get_post_meta( $post_id, 'tb4_media_attachment_id', true ) );
        if ( $attachment_id ) {
            $attachment_url = wp_get_attachment_image_url( $attachment_id, 'medium_large' );
            if ( $attachment_url ) {
                return $attachment_url;
            }
        }

        $album_raw = get_post_meta( $post_id, 'tb4_media_album', true );
        $album     = [];

        if ( is_string( $album_raw ) && '' !== trim( $album_raw ) ) {
            $decoded = json_decode( wp_unslash( $album_raw ), true );
            if ( is_array( $decoded ) ) {
                $album = $decoded;
            }
        }

        if ( empty( $album ) && function_exists( 'tb4cm_get_post_media_album' ) ) {
            $album = tb4cm_get_post_media_album( $post_id, wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ) );
        }

        if ( ! empty( $album[0] ) && is_array( $album[0] ) ) {
            $first = $album[0];
            $type  = sanitize_key( $first['type'] ?? '' );
            $url   = ! empty( $first['url'] ) ? esc_url_raw( $first['url'] ) : '';

            if ( 'image' === $type && $url ) {
                return $url;
            }

            if ( 'video' === $type && function_exists( 'tb4cm_get_video_poster_url' ) ) {
                $poster = tb4cm_get_video_poster_url( $first );
                if ( $poster ) {
                    return $poster;
                }
            }

            if ( $url && preg_match( '/\.(jpe?g|png|webp|gif)(\?.*)?$/i', $url ) ) {
                return $url;
            }
        }

        $media_url = esc_url_raw( get_post_meta( $post_id, 'tb4_media_url', true ) );
        if ( $media_url && preg_match( '/\.(jpe?g|png|webp|gif)(\?.*)?$/i', $media_url ) ) {
            return $media_url;
        }

        return '';
    }
}

if ( ! function_exists( 'tb4_home_v248_get_album_count' ) ) {
    function tb4_home_v248_get_album_count( $post_id ) {
        $album_raw = get_post_meta( $post_id, 'tb4_media_album', true );
        if ( is_string( $album_raw ) && '' !== trim( $album_raw ) ) {
            $decoded = json_decode( wp_unslash( $album_raw ), true );
            if ( is_array( $decoded ) ) {
                return count( $decoded );
            }
        }
        return get_post_meta( $post_id, 'tb4_media_url', true ) ? 1 : 0;
    }
}

if ( ! function_exists( 'tb4_home_v248_topic_label' ) ) {
    function tb4_home_v248_topic_label( $type ) {
        $labels = [
            'discussion' => __( 'พูดคุย', 'thinkb4do' ),
            'question'   => __( 'ถาม-ตอบ', 'thinkb4do' ),
            'idea'       => __( 'ไอเดีย', 'thinkb4do' ),
            'project'    => __( 'โปรเจกต์', 'thinkb4do' ),
            'event'      => __( 'กิจกรรม', 'thinkb4do' ),
            'creator'    => __( 'Creator', 'thinkb4do' ),
        ];
        $type = sanitize_key( $type );
        return $labels[ $type ] ?? __( 'ชุมชน', 'thinkb4do' );
    }
}

if ( ! function_exists( 'tb4_home_v248_get_reel_items' ) ) {
    function tb4_home_v248_get_reel_items( $limit = 6 ) {
        if ( ! post_type_exists( 'tb4_community_post' ) ) {
            return [];
        }

        $query = new WP_Query( [
            'post_type'           => 'tb4_community_post',
            'post_status'         => 'publish',
            'posts_per_page'      => max( 8, (int) $limit * 2 ),
            'ignore_sticky_posts' => true,
            'no_found_rows'       => true,
            'orderby'             => 'date',
            'order'               => 'DESC',
        ] );

        $items = [];

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $post_id     = get_the_ID();
                $cover       = tb4_home_v248_get_media_cover( $post_id );
                $album_count = tb4_home_v248_get_album_count( $post_id );

                if ( ! $cover && $album_count < 1 ) {
                    continue;
                }

                $author_id   = (int) get_post_field( 'post_author', $post_id );
                $author_name = get_the_author_meta( 'display_name', $author_id ) ?: __( 'สมาชิก Thinkb4do', 'thinkb4do' );
                $type        = get_post_meta( $post_id, 'tb4_post_type', true ) ?: 'discussion';
                $summary     = get_the_excerpt( $post_id ) ?: get_post_field( 'post_content', $post_id );

                $items[] = [
                    'title'       => get_the_title(),
                    'url'         => get_permalink(),
                    'image'       => $cover,
                    'description' => tb4_home_v247_clean_text( $summary, 16 ),
                    'meta'        => sprintf( __( '%1$s · %2$s', 'thinkb4do' ), $author_name, human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) . __( 'ที่แล้ว', 'thinkb4do' ) ),
                    'badge'       => tb4_home_v248_topic_label( $type ),
                    'likes'       => (int) get_post_meta( $post_id, 'tb4_like_count', true ),
                    'comments'    => (int) get_comments_number( $post_id ),
                    'media_count' => max( 1, (int) $album_count ),
                ];

                if ( count( $items ) >= (int) $limit ) {
                    break;
                }
            }
            wp_reset_postdata();
        }

        return $items;
    }
}

if ( ! function_exists( 'tb4_home_v248_get_community_items' ) ) {
    function tb4_home_v248_get_community_items( $limit = 5 ) {
        if ( ! post_type_exists( 'tb4_community_post' ) ) {
            return [];
        }

        $query = new WP_Query( [
            'post_type'           => 'tb4_community_post',
            'post_status'         => 'publish',
            'posts_per_page'      => max( 1, (int) $limit ),
            'ignore_sticky_posts' => true,
            'no_found_rows'       => true,
            'orderby'             => 'date',
            'order'               => 'DESC',
        ] );

        $items = [];

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $post_id = get_the_ID();
                $type    = get_post_meta( $post_id, 'tb4_post_type', true ) ?: 'discussion';
                $summary = get_the_excerpt( $post_id ) ?: get_post_field( 'post_content', $post_id );

                $items[] = [
                    'title'       => get_the_title(),
                    'url'         => get_permalink(),
                    'image'       => tb4_home_v248_get_media_cover( $post_id ),
                    'description' => tb4_home_v247_clean_text( $summary, 20 ),
                    'badge'       => tb4_home_v248_topic_label( $type ),
                    'likes'       => (int) get_post_meta( $post_id, 'tb4_like_count', true ),
                    'comments'    => (int) get_comments_number( $post_id ),
                    'date'        => get_the_date( '', $post_id ),
                ];
            }
            wp_reset_postdata();
        }

        return $items;
    }
}

if ( ! function_exists( 'tb4_home_v248_product_status_label' ) ) {
    function tb4_home_v248_product_status_label( $status ) {
        $labels = [
            'ready'      => __( 'พร้อมใช้', 'thinkb4do' ),
            'beta'       => __( 'Beta', 'thinkb4do' ),
            'soon'       => __( 'ใกล้เปิด', 'thinkb4do' ),
            'draft-demo' => __( 'ข้อมูลตัวอย่าง', 'thinkb4do' ),
            'research'   => __( 'กำลังวิจัย', 'thinkb4do' ),
        ];
        $status = sanitize_key( $status );
        return $labels[ $status ] ?? __( 'อัปเดตใหม่', 'thinkb4do' );
    }
}

if ( ! function_exists( 'tb4_home_v248_get_product_items' ) ) {
    function tb4_home_v248_get_product_items( $limit = 6 ) {
        if ( ! post_type_exists( 'tb4_product' ) ) {
            return [];
        }

        $base_args = [
            'post_type'           => 'tb4_product',
            'post_status'         => 'publish',
            'posts_per_page'      => max( 1, (int) $limit ),
            'ignore_sticky_posts' => true,
            'no_found_rows'       => true,
            'orderby'             => 'date',
            'order'               => 'DESC',
        ];

        $query = new WP_Query( array_merge( $base_args, [
            'meta_query' => [
                [
                    'key'   => '_tb4_product_featured',
                    'value' => '1',
                ],
            ],
        ] ) );

        if ( ! $query->have_posts() ) {
            $query = new WP_Query( $base_args );
        }

        $items = [];

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $post_id = get_the_ID();
                $status  = get_post_meta( $post_id, '_tb4_product_status', true ) ?: 'ready';
                $tagline = get_post_meta( $post_id, '_tb4_product_tagline', true );
                $summary = $tagline ?: ( get_the_excerpt( $post_id ) ?: get_post_field( 'post_content', $post_id ) );
                $terms   = get_the_terms( $post_id, 'tb4_product_cat' );
                $cat     = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : __( 'ผลิตภัณฑ์', 'thinkb4do' );

                $items[] = [
                    'title'       => get_the_title(),
                    'url'         => get_permalink(),
                    'image'       => has_post_thumbnail( $post_id ) ? get_the_post_thumbnail_url( $post_id, 'medium_large' ) : '',
                    'description' => tb4_home_v247_clean_text( $summary, 18 ),
                    'badge'       => get_post_meta( $post_id, '_tb4_product_badge', true ) ?: tb4_home_v248_product_status_label( $status ),
                    'status'      => tb4_home_v248_product_status_label( $status ),
                    'rating'      => get_post_meta( $post_id, '_tb4_product_rating', true ),
                    'trust'       => get_post_meta( $post_id, '_tb4_product_trust_score', true ),
                    'icon'        => get_post_meta( $post_id, '_tb4_product_icon', true ) ?: 'T',
                    'category'    => $cat,
                ];
            }
            wp_reset_postdata();
        }

        return $items;
    }
}

if ( ! function_exists( 'tb4_home_v248_fallback_reels' ) ) {
    function tb4_home_v248_fallback_reels() {
        return [
            [ 'title' => __( 'Reel แนะนำจากชุมชน', 'thinkb4do' ), 'url' => home_url( '/community/' ), 'image' => '', 'description' => __( 'เมื่อมีรูปหรือวิดีโอในชุมชน ระบบจะดึงมาโชว์เป็นแถบ Reel หน้าแรก', 'thinkb4do' ), 'meta' => __( 'Community Reel', 'thinkb4do' ), 'badge' => __( 'Reel', 'thinkb4do' ), 'likes' => 0, 'comments' => 0, 'media_count' => 1 ],
            [ 'title' => __( 'คลิป/ภาพจากสมาชิก', 'thinkb4do' ), 'url' => home_url( '/community/' ), 'image' => '', 'description' => __( 'รองรับภาพ วิดีโอ และอัลบั้มจากโพสต์ชุมชน', 'thinkb4do' ), 'meta' => __( 'กำลังรอข้อมูลจริง', 'thinkb4do' ), 'badge' => __( 'ใหม่', 'thinkb4do' ), 'likes' => 0, 'comments' => 0, 'media_count' => 1 ],
        ];
    }
}

if ( ! function_exists( 'tb4_home_v248_fallback_community' ) ) {
    function tb4_home_v248_fallback_community() {
        return [
            [ 'title' => __( 'ชุมชน Thinkb4do', 'thinkb4do' ), 'url' => home_url( '/community/' ), 'image' => '', 'description' => __( 'พื้นที่รวมโพสต์ ถาม-ตอบ ไอเดีย โปรเจกต์ และกิจกรรมของสมาชิก', 'thinkb4do' ), 'badge' => __( 'วันนี้', 'thinkb4do' ), 'likes' => 0, 'comments' => 0, 'date' => __( 'พร้อมใช้งาน', 'thinkb4do' ) ],
            [ 'title' => __( 'พื้นที่สมาชิก', 'thinkb4do' ), 'url' => home_url( '/member-area/' ), 'image' => '', 'description' => __( 'รวมโพสต์และข้อมูลของตัวเองแบบดูง่ายขึ้น', 'thinkb4do' ), 'badge' => __( 'สมาชิก', 'thinkb4do' ), 'likes' => 0, 'comments' => 0, 'date' => __( 'แนะนำ', 'thinkb4do' ) ],
        ];
    }
}

if ( ! function_exists( 'tb4_home_v248_fallback_products' ) ) {
    function tb4_home_v248_fallback_products() {
        return [
            [ 'title' => __( 'AiRA Studio', 'thinkb4do' ), 'url' => home_url( '/products/' ), 'image' => '', 'description' => __( 'ระบบช่วยคิด ออกแบบ และต่อยอดงานดิจิทัลสำหรับทีมงาน', 'thinkb4do' ), 'badge' => __( 'แนะนำ', 'thinkb4do' ), 'status' => __( 'เตรียมเปิด', 'thinkb4do' ), 'rating' => '', 'trust' => '96', 'icon' => 'AI', 'category' => __( 'ระบบ', 'thinkb4do' ) ],
            [ 'title' => __( 'Think Control', 'thinkb4do' ), 'url' => home_url( '/products/' ), 'image' => '', 'description' => __( 'ชุดควบคุมระบบและฟีดหลักสำหรับเว็บไซต์ Thinkb4do', 'thinkb4do' ), 'badge' => __( 'ใหม่', 'thinkb4do' ), 'status' => __( 'กำลังพัฒนา', 'thinkb4do' ), 'rating' => '', 'trust' => '94', 'icon' => 'TC', 'category' => __( 'เครื่องมือ', 'thinkb4do' ) ],
        ];
    }
}

$menu_feed_items = tb4_home_v247_get_menu_feed_items( 8 );
if ( empty( $menu_feed_items ) ) {
    $menu_feed_items = tb4_home_v247_fallback_menu_feed();
}

$featured_item = $menu_feed_items[0];
$feed_cards    = array_slice( $menu_feed_items, 0, 6 );
$side_items    = array_slice( $menu_feed_items, 1, 4 );

$home_reel_items      = tb4_home_v248_get_reel_items( 6 );
$home_community_items = tb4_home_v248_get_community_items( 4 );
$home_product_items   = tb4_home_v248_get_product_items( 5 );

if ( empty( $home_reel_items ) ) {
    $home_reel_items = tb4_home_v248_fallback_reels();
}
if ( empty( $home_community_items ) ) {
    $home_community_items = tb4_home_v248_fallback_community();
}
if ( empty( $home_product_items ) ) {
    $home_product_items = tb4_home_v248_fallback_products();
}

$community_url = home_url( '/community/' );
$products_url  = post_type_exists( 'tb4_product' ) ? ( get_post_type_archive_link( 'tb4_product' ) ?: home_url( '/products/' ) ) : home_url( '/products/' );
?>

<main id="main-content" class="tb4-modern-main tb4-home-feed-main" role="main">
  <section class="tb4-home-feed-hero" aria-label="Thinkb4do หน้าแรก">
    <div class="container tb4-home-feed-hero-grid">
      <div class="tb4-home-feed-copy">
        <span class="tb4-home-feed-kicker">Thinkb4do · Home Discovery Feed</span>
        <h1>หน้าแรกที่รวม Reel ชุมชน และผลิตภัณฑ์ใหม่ไว้ในที่เดียว</h1>
        <p>ดึงเรื่องน่าสนใจจาก Reel, ฟีดชุมชน, ผลิตภัณฑ์ใหม่ และเมนูหลัก มาเรียงให้เห็นภาพรวมทันทีว่าเว็บกำลังมีอะไรเคลื่อนไหวและควรไปต่อที่ไหน</p>

        <form class="tb4-home-feed-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
          <input type="search" name="s" placeholder="ค้นหา Reel ชุมชน ผลิตภัณฑ์ ระบบ หรือบทความ" value="<?php echo esc_attr( get_search_query() ); ?>" aria-label="ค้นหาใน Thinkb4do">
          <button type="submit">ค้นหา</button>
        </form>

        <div class="tb4-home-feed-pills" aria-label="ทางลัดหน้าแรก">
          <?php foreach ( array_slice( $feed_cards, 0, 4 ) as $pill ) : ?>
            <a href="<?php echo esc_url( $pill['url'] ); ?>"><?php echo esc_html( $pill['title'] ); ?></a>
          <?php endforeach; ?>
        </div>
      </div>

      <aside class="tb4-home-feed-spotlight" aria-label="ฟีดเด่นจากเมนูหลัก">
        <span class="tb4-home-feed-badge"><?php echo esc_html( $featured_item['badge'] ); ?></span>
        <a class="tb4-home-feed-spotlight-media" href="<?php echo esc_url( $featured_item['url'] ); ?>" aria-label="<?php echo esc_attr( $featured_item['title'] ); ?>">
          <?php if ( ! empty( $featured_item['image'] ) ) : ?>
            <img src="<?php echo esc_url( $featured_item['image'] ); ?>" alt="<?php echo esc_attr( $featured_item['title'] ); ?>" loading="eager">
          <?php else : ?>
            <span><?php echo esc_html( tb4_home_v247_first_char( $featured_item['title'] ) ); ?></span>
          <?php endif; ?>
        </a>
        <div class="tb4-home-feed-spotlight-body">
          <small><?php echo esc_html( $featured_item['meta'] ); ?></small>
          <h2><a href="<?php echo esc_url( $featured_item['url'] ); ?>"><?php echo esc_html( $featured_item['title'] ); ?></a></h2>
          <p><?php echo esc_html( $featured_item['description'] ); ?></p>
          <a class="tb4-home-feed-cta" href="<?php echo esc_url( $featured_item['url'] ); ?>">ดูรายละเอียด</a>
        </div>
      </aside>
    </div>
  </section>

  <?php if ( function_exists( 'tb4_render_dbd_trust_strip' ) && get_theme_mod( 'tb4_dbd_show_home', true ) ) : ?>
    <div class="container tb4-home-feed-trust"><?php tb4_render_dbd_trust_strip( 'home' ); ?></div>
  <?php endif; ?>


  <section class="tb4-home-feed-section tb4-home-discovery-section" aria-label="Reel ชุมชน และผลิตภัณฑ์ใหม่">
    <div class="container">
      <div class="tb4-home-feed-section-head tb4-home-discovery-head">
        <div>
          <span>Live Discovery</span>
          <h2>กำลังน่าสนใจจาก Reel · ชุมชน · ผลิตภัณฑ์ใหม่</h2>
          <p>หน้าแรกจะดึงรายการจากระบบชุมชนและระบบผลิตภัณฑ์มาแสดงอัตโนมัติ ถ้ายังไม่มีข้อมูลจริง ระบบจะแสดงการ์ดแนะนำแทนเพื่อไม่ให้หน้าโล่ง</p>
        </div>
        <div class="tb4-home-discovery-actions">
          <a href="<?php echo esc_url( $community_url ); ?>">เปิดชุมชน</a>
          <a href="<?php echo esc_url( $products_url ); ?>">ดูผลิตภัณฑ์</a>
        </div>
      </div>

      <div class="tb4-home-reel-rail" aria-label="Reel จากชุมชน">
        <?php foreach ( $home_reel_items as $index => $reel ) : ?>
          <article class="tb4-home-reel-card<?php echo 0 === $index ? ' is-active' : ''; ?>">
            <a class="tb4-home-reel-media" href="<?php echo esc_url( $reel['url'] ); ?>" aria-label="<?php echo esc_attr( $reel['title'] ); ?>">
              <?php if ( ! empty( $reel['image'] ) ) : ?>
                <img src="<?php echo esc_url( $reel['image'] ); ?>" alt="<?php echo esc_attr( $reel['title'] ); ?>" loading="lazy">
              <?php else : ?>
                <span><?php echo esc_html( tb4_home_v247_first_char( $reel['title'] ) ); ?></span>
              <?php endif; ?>
              <i aria-hidden="true">▶</i>
            </a>
            <div class="tb4-home-reel-body">
              <small><?php echo esc_html( $reel['badge'] ); ?> · <?php echo esc_html( $reel['media_count'] ); ?> สื่อ</small>
              <h3><a href="<?php echo esc_url( $reel['url'] ); ?>"><?php echo esc_html( $reel['title'] ); ?></a></h3>
              <p><?php echo esc_html( $reel['description'] ); ?></p>
              <div class="tb4-home-reel-meta">
                <span><?php echo esc_html( $reel['meta'] ); ?></span>
                <em><?php echo esc_html( number_format_i18n( (int) $reel['likes'] ) ); ?> ถูกใจ · <?php echo esc_html( number_format_i18n( (int) $reel['comments'] ) ); ?> ความเห็น</em>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="tb4-home-feed-section tb4-home-live-sources" aria-label="ฟีดชุมชนและผลิตภัณฑ์ใหม่">
    <div class="container tb4-home-live-sources-grid">
      <div class="tb4-home-community-panel">
        <div class="tb4-home-source-head">
          <div>
            <span>Community Feed</span>
            <h2>โพสต์ชุมชนล่าสุด</h2>
          </div>
          <a href="<?php echo esc_url( $community_url ); ?>">ดูทั้งหมด</a>
        </div>
        <div class="tb4-home-community-list">
          <?php foreach ( $home_community_items as $item ) : ?>
            <article class="tb4-home-community-item">
              <a class="tb4-home-community-thumb" href="<?php echo esc_url( $item['url'] ); ?>" aria-hidden="true" tabindex="-1">
                <?php if ( ! empty( $item['image'] ) ) : ?>
                  <img src="<?php echo esc_url( $item['image'] ); ?>" alt="" loading="lazy">
                <?php else : ?>
                  <span><?php echo esc_html( tb4_home_v247_first_char( $item['title'] ) ); ?></span>
                <?php endif; ?>
              </a>
              <div>
                <small><?php echo esc_html( $item['badge'] ); ?> · <?php echo esc_html( $item['date'] ); ?></small>
                <h3><a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a></h3>
                <p><?php echo esc_html( $item['description'] ); ?></p>
                <em><?php echo esc_html( number_format_i18n( (int) $item['likes'] ) ); ?> ถูกใจ · <?php echo esc_html( number_format_i18n( (int) $item['comments'] ) ); ?> ความเห็น</em>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="tb4-home-products-panel">
        <div class="tb4-home-source-head">
          <div>
            <span>New Products</span>
            <h2>ผลิตภัณฑ์ใหม่ที่น่าสนใจ</h2>
          </div>
          <a href="<?php echo esc_url( $products_url ); ?>">ดูทั้งหมด</a>
        </div>
        <div class="tb4-home-products-grid">
          <?php foreach ( $home_product_items as $product ) : ?>
            <article class="tb4-home-product-card">
              <a class="tb4-home-product-visual" href="<?php echo esc_url( $product['url'] ); ?>" aria-label="<?php echo esc_attr( $product['title'] ); ?>">
                <?php if ( ! empty( $product['image'] ) ) : ?>
                  <img src="<?php echo esc_url( $product['image'] ); ?>" alt="<?php echo esc_attr( $product['title'] ); ?>" loading="lazy">
                <?php else : ?>
                  <span><?php echo esc_html( tb4_home_v247_clean_text( $product['icon'], 2 ) ); ?></span>
                <?php endif; ?>
              </a>
              <div class="tb4-home-product-body">
                <div class="tb4-home-product-topline">
                  <span><?php echo esc_html( $product['badge'] ); ?></span>
                  <small><?php echo esc_html( $product['category'] ); ?></small>
                </div>
                <h3><a href="<?php echo esc_url( $product['url'] ); ?>"><?php echo esc_html( $product['title'] ); ?></a></h3>
                <p><?php echo esc_html( $product['description'] ); ?></p>
                <div class="tb4-home-product-meta">
                  <strong><?php echo esc_html( $product['status'] ); ?></strong>
                  <?php if ( ! empty( $product['trust'] ) ) : ?><em>Trust <?php echo esc_html( $product['trust'] ); ?>%</em><?php endif; ?>
                  <?php if ( ! empty( $product['rating'] ) ) : ?><em>★ <?php echo esc_html( $product['rating'] ); ?></em><?php endif; ?>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>

  <section class="tb4-home-feed-section" aria-label="ฟีดจากเมนูหลัก">
    <div class="container">
      <div class="tb4-home-feed-section-head">
        <div>
          <span>Main Menu Feed</span>
          <h2>ฟีดจากเมนูหลักที่น่าสนใจ</h2>
          <p>ระบบจะอ่านเมนูหลักที่ตั้งไว้ แล้วนำรายการสำคัญมาเรียงเป็นการ์ดหน้าแรกโดยอัตโนมัติ</p>
        </div>
        <a href="<?php echo esc_url( $primary_url ); ?>">ดูภาพรวม</a>
      </div>

      <div class="tb4-home-feed-card-grid">
        <?php foreach ( $feed_cards as $index => $card ) : ?>
          <article class="tb4-home-feed-card<?php echo 0 === $index ? ' is-featured' : ''; ?>">
            <a class="tb4-home-feed-card-media" href="<?php echo esc_url( $card['url'] ); ?>" aria-hidden="true" tabindex="-1">
              <?php if ( ! empty( $card['image'] ) ) : ?>
                <img src="<?php echo esc_url( $card['image'] ); ?>" alt="" loading="lazy">
              <?php else : ?>
                <span><?php echo esc_html( tb4_home_v247_first_char( $card['title'] ) ); ?></span>
              <?php endif; ?>
            </a>
            <div class="tb4-home-feed-card-body">
              <div class="tb4-home-feed-card-topline">
                <span><?php echo esc_html( $card['badge'] ); ?></span>
                <small><?php echo esc_html( $card['meta'] ); ?></small>
              </div>
              <h3><a href="<?php echo esc_url( $card['url'] ); ?>"><?php echo esc_html( $card['title'] ); ?></a></h3>
              <p><?php echo esc_html( $card['description'] ); ?></p>
              <a class="tb4-home-feed-link" href="<?php echo esc_url( $card['url'] ); ?>">เปิดดู</a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="tb4-home-feed-section" aria-label="เรื่องล่าสุดและทางลัด">
    <div class="container tb4-home-feed-live-grid">
      <div class="tb4-home-feed-live-panel">
        <div class="tb4-home-feed-section-head compact">
          <div>
            <span>Latest Update</span>
            <h2>เรื่องล่าสุด</h2>
            <p>อัปเดตบทความ แนวคิด หรือประกาศที่เพิ่มเข้ามาล่าสุด</p>
          </div>
          <a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">ทั้งหมด</a>
        </div>

        <div class="tb4-home-feed-post-list">
          <?php
          $latest_posts = new WP_Query([
            'post_type'           => 'post',
            'posts_per_page'      => 3,
            'ignore_sticky_posts' => true,
            'no_found_rows'       => true,
          ]);

          if ( $latest_posts->have_posts() ) :
            while ( $latest_posts->have_posts() ) : $latest_posts->the_post();
          ?>
            <article class="tb4-home-feed-post">
              <a class="tb4-home-feed-post-thumb" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
                <?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'tb4-card' ); } else { echo '<span>' . esc_html( tb4_home_v247_first_char( get_the_title() ) ) . '</span>'; } ?>
              </a>
              <div>
                <small><?php echo esc_html( get_the_date() ); ?></small>
                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                <p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18, '…' ) ); ?></p>
              </div>
            </article>
          <?php
            endwhile;
            wp_reset_postdata();
          else :
            $fallback_posts = [
              [ 'คิดก่อนทำ เริ่มอย่างไรให้ไม่หลุดเป้าหมาย', 'แนวทางจัดลำดับความคิดก่อนเริ่มงานจริง' ],
              [ 'จัดระบบงานให้เบาและต่อยอดง่าย', 'ลดความซ้ำซ้อน แล้วเหลือเฉพาะส่วนสำคัญ' ],
              [ 'สร้างพื้นที่ออนไลน์ให้ดูน่าเชื่อถือ', 'ออกแบบให้คนเข้าใจง่ายตั้งแต่หน้าแรก' ],
            ];
            foreach ( $fallback_posts as $item ) :
          ?>
            <article class="tb4-home-feed-post">
              <div class="tb4-home-feed-post-thumb" aria-hidden="true"><span><?php echo esc_html( tb4_home_v247_first_char( $item[0] ) ); ?></span></div>
              <div>
                <small>Thinkb4do</small>
                <h3><?php echo esc_html( $item[0] ); ?></h3>
                <p><?php echo esc_html( $item[1] ); ?></p>
              </div>
            </article>
          <?php endforeach; endif; ?>
        </div>
      </div>

      <aside class="tb4-home-feed-side-panel" aria-label="เมนูที่ควรลองต่อ">
        <div class="tb4-home-feed-side-head">
          <span>Next to Explore</span>
          <h3>เมนูที่ควรลองต่อ</h3>
        </div>
        <div class="tb4-home-feed-side-list">
          <?php foreach ( $side_items as $item ) : ?>
            <a href="<?php echo esc_url( $item['url'] ); ?>">
              <strong><?php echo esc_html( $item['title'] ); ?></strong>
              <span><?php echo esc_html( $item['description'] ); ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </aside>
    </div>
  </section>

  <section class="tb4-home-feed-section" aria-label="ปิดท้าย">
    <div class="container">
      <div class="tb4-home-feed-cta-card">
        <div>
          <span>Thinkb4do</span>
          <h2>หน้าแรกพร้อมเป็นศูนย์รวม Reel ชุมชน ผลิตภัณฑ์ และเมนูหลัก</h2>
          <p>เมื่อมี Reel โพสต์ชุมชน ผลิตภัณฑ์ หรือเมนูหลักใหม่ หน้าแรกจะช่วยพาผู้ใช้ไปเจอสิ่งที่น่าสนใจได้เร็วขึ้นทันที</p>
        </div>
        <a href="<?php echo esc_url( $secondary_url ); ?>">ไปต่อ</a>
      </div>
    </div>
  </section>
</main>
