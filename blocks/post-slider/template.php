<?php
/**
 * Block template: Post Slider
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

$id = 'ldr-post-slider-' . $block['id'];
$block_name = pathinfo( __DIR__, PATHINFO_FILENAME );
$block_url = plugin_dir_url( __FILE__ );
$block_dir = plugin_dir_path( __FILE__ );
$className = "ldr-block ldr-$block_name clearfix";
$glide_className = 'glide';

$hide_arrows = get_field( 'post_slider_hide_arrows' );
$hide_bullets = get_field( 'post_slider_hide_bullets' );

$is_static = (bool) get_field( 'post_slider_is_static' );
$numberposts = (int) get_field( 'post_slider_number_of_posts' );
$posts_number = ( $numberposts === 0 ) ? -1 : $numberposts;
$post_args = [
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => $is_static ? 1 : ($posts_number ? $posts_number : -1),
    'orderby' => 'date',
    'order' => 'DESC',
    'ignore_sticky_posts' => true,
];
$only_sticky = get_field( 'post_slider_only_sticky_posts' );
$sticky_posts = get_option( 'sticky_posts' );
$filter_by_author = get_field( 'post_slider_filter_by_author' );
$selected_author = get_field( 'post_slider_select_author' );
$filter_by_multiple_categories = get_field( 'post_slider_select_in_multiple_categories' );
$filter_by_category = get_field( 'post_slider_filter_by_category' );
$selected_categories = get_field( 'post_slider_select_categories' );
$selected_posts = get_field( 'post_slider_select_posts' );

if( ! empty( $only_sticky ) ) {
    $post_args['ignore_sticky_posts'] = false;
    $post_args['post__in'] = $sticky_posts;
}

if( ! empty( $filter_by_author ) && ! empty( $selected_author ) ) {
    $post_args['meta_query']['relation'] = 'OR';

    foreach( $selected_author as $author ) {
        $post_args['meta_query'][] = [
            'key' => 'post_author_select',
            'value' => $author,
            'compare' => 'LIKE',
        ];
    }
}

if( ! empty( $filter_by_category ) && ! empty( $selected_categories ) ) {
    if( ! $filter_by_multiple_categories ) {
        $post_args['category__in'] = $selected_categories;
    } else {
        $post_args['category__and'] = $selected_categories;
    }
}

$posts = get_posts( $post_args );
$post_num = count( $posts );

$card_settings = [
    'cover_image_height' => get_field( 'post_slider_cover_image_height' ),
    'card_min_height' => get_field( 'post_slider_card_min_height' ),
    'cover_image_overlay_color' => get_field( 'post_slider_cover_image_overlay_color' ),
    'hide_border' => get_field( 'post_slider_hide_border' ),
    'hide_title' => get_field( 'post_slider_hide_title' ),
    'hide_description' => get_field( 'post_slider_hide_description' ),
    'hide_link' => get_field( 'post_slider_hide_link' ),
    'link_text' => get_field( 'post_slider_link_text' ),
    'layout' => get_field( 'post_slider_card_layout' )
];
$start_at = (int) get_field( 'post_slider_start_at' );
$per_view = (int) get_field( 'post_slider_per_view' );
$slider_data = [
    'type' => get_field( 'post_slider_type' ),
    'startAt' => ( $start_at > $post_num ) ? 0 : $start_at,
    'perView' => ( $per_view > $post_num ) ? 1 : $per_view,
    'autoplay' => $post_num > 1 ? (int) get_field( 'post_slider_autoplay' ) : 0,
    'hoverpause' => get_field( 'post_slider_hoverpause' ),
    'animationDuration' => (int) get_field( 'post_slider_animation_duration' ),
    'breakpoints' => [
        3000 => [
            // 'perView' => ( $per_view >= $post_num ) || ( $post_num == 1 ) ? 1 : $per_view
            'perView' => $per_view
        ],
        1200 => [
            'perView' => 1
        ],
        992 => [
            'perView' => 1
        ],
        768 => [
            'perView' => 1
        ],
    ]
];

if( ! $hide_bullets ) {
    $glide_className .= ' pb-5 mb-5';
}

if( ! empty( $block['anchor'] ) ) {
    $id = $block['anchor'];
}

if( $hide_arrows ) {
    $className .= ' arrow-navigation-off';
}

if( ! empty( $block['className'] ) ) {
    $className .= ' ' . $block['className'];
}

if( ! empty( $block['align'] ) ) {
    $className .= ' align' . $block['align'];
}

?>
<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $className ); ?>" data-slider-settings='<?php echo json_encode( $slider_data ); ?>' data-is-static="<?php echo $is_static; ?>">
    
    <div class="<?php echo $glide_className; ?>">
        <div class="glide__track" data-glide-el="track">
            <?php if( $posts ) : ?>
                <ul class="glide__slides">
                    <?php foreach( $posts as $post ) : ?>
                        <li class="glide__slide">
                            <?php echo Ldr\Posts::instance()->render_post_card_block( $post->ID, $card_settings ); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
             <?php endif; ?>
        </div>

        <?php if( ! $hide_bullets && $posts && ! $is_static ) : ?>
            <div class="glide__bullets" data-glide-el="controls[nav]">
                <?php for( $i = 0; $i < $post_num; $i++ ) : ?>
                    <button class="glide__bullet" data-glide-dir='=<?php echo $i; ?>'></button>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

        <?php if( ! $hide_arrows && ! $is_static ) : ?>
            <div class="glide__arrows" data-glide-el="controls">
                <button class="glide__arrow glide__arrow--left" data-glide-dir="<?php esc_attr_e( '<' ); ?>" aria-label="<?php esc_attr_e( 'Slide left', 'ldr' ); ?>">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button class="glide__arrow glide__arrow--right" data-glide-dir="<?php esc_attr_e( '>' ); ?>" aria-label="<?php esc_attr_e( 'Slide right', 'ldr' ); ?>">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        <?php endif; ?>
    </div>

</div>