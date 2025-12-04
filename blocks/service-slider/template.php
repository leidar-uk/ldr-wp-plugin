<?php
/**
 * Block template: Service Slider
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $service_id The service ID this block is saved to.
 */

$id = 'ldr-service-slider-' . $block['id'];
$block_name = pathinfo( __DIR__, PATHINFO_FILENAME );
$block_url = plugin_dir_url( __FILE__ );
$block_dir = plugin_dir_path( __FILE__ );
$className = "ldr-block ldr-$block_name clearfix";
$glide_className = 'glide';

$hide_arrows = get_field( 'service_slider_hide_arrows' );
$hide_bullets = get_field( 'service_slider_hide_bullets' );
$omit_children = get_field( 'service_slider_omit_children' );

$service_args = [
    'post_type' => 'service',
    'post_status' => 'publish',
    'numberposts' => -1,
];
$filter_by_category = get_field( 'service_slider_filter_by_category' );
$selected_categories = get_field( 'service_slider_select_categories' );
$selected_services = get_field( 'service_slider_select_services' );

if( ! empty( $filter_by_category ) && ! empty( $selected_categories ) ) {
    $service_args['tax_query'] = [
        [
            'taxonomy' => 'groups',
            'field' => 'term_id',
            'terms' => array_map( function( $n ) { return (int) $n; }, $selected_categories )
        ]
    ];
}

if( ! empty( $omit_children ) ) {
    $service_args['post_parent'] = 0;
}

$services = get_posts( $service_args );
$service_num = count( $services );

$card_settings = [
    'cover_image_height' => get_field( 'service_slider_cover_image_height' ),
    'card_min_height' => get_field( 'service_slider_card_min_height' ),
    'cover_image_overlay_color' => get_field( 'service_slider_cover_image_overlay_color' ),
    'hide_border' => get_field( 'service_slider_hide_border' ),
    'hide_title' => get_field( 'service_slider_hide_title' ),
    'hide_description' => get_field( 'service_slider_hide_description' ),
    'hide_link' => get_field( 'service_slider_hide_link' ),
    'link_text' => get_field( 'service_slider_link_text' ),
    'layout' => get_field( 'service_slider_card_layout' )
];
$start_at = (int) get_field( 'service_slider_start_at' );
$per_view = (int) get_field( 'service_slider_per_view' );
$slider_data = [
    'type' => get_field( 'service_slider_type' ),
    'startAt' => ( $start_at > $service_num ) ? 0 : $start_at,
    'perView' => ( $per_view > $service_num ) ? 1 : $per_view,
    'autoplay' => $service_num > 1 ? (int) get_field( 'service_slider_autoplay' ) : 0,
    'hoverpause' => get_field( 'service_slider_hoverpause' ),
    'animationDuration' => (int) get_field( 'service_slider_animation_duration' ),
    'breakpoints' => [
        3000 => [
            'perView' => $per_view
        ],
        1200 => [
            'perView' => 2
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
<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $className ); ?>" data-slider-settings='<?php echo json_encode( $slider_data ); ?>'>
    
    <div class="<?php echo $glide_className; ?>">
        <div class="glide__track" data-glide-el="track">
            <?php if( $services ) : ?>
                <ul class="glide__slides">
                    <?php foreach( $services as $service ) : ?>
                        <li class="glide__slide">
                            <?php echo Ldr\Services::instance()->render_service_card_block( $service->ID, $card_settings ); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
             <?php endif; ?>
        </div>

        <?php if( ! $hide_bullets && $services ) : ?>
            <div class="glide__bullets" data-glide-el="controls[nav]">
                <?php for( $i = 0; $i < $service_num; $i++ ) : ?>
                    <button class="glide__bullet" data-glide-dir='=<?php echo $i; ?>'></button>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

        <?php if( ! $hide_arrows ) : ?>
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