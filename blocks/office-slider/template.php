<?php
/**
 * Block template: Office Slider
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

$id = 'ldr-office-slider-' . $block['id'];
$block_name = pathinfo( __DIR__, PATHINFO_FILENAME );
$block_url = plugin_dir_url( __FILE__ );
$block_dir = plugin_dir_path( __FILE__ );
$className = "ldr-block ldr-$block_name clearfix";
$glide_className = 'glide';

$hide_arrows = get_field( 'office_slider_hide_arrows' );
$hide_bullets = get_field( 'office_slider_hide_bullets' );

$exclude_offices = get_field( 'office_slider_exclude_offices' );
$excluded_offices = ! empty( $exclude_offices ) ? [
    'post__not_in' => array_map( function( $n ) { return (int) $n; }, $exclude_offices )
] : [];
$offices = Ldr\Offices::instance()->get_offices( $excluded_offices );
$card_settings = [
    'cover_image_height' => get_field( 'office_slider_cover_image_height' ),
    'cover_image_overlay_color' => get_field( 'office_slider_cover_image_overlay_color' ),
    'hide_map' => (int) get_field( 'office_slider_hide_map' ),
    'map_height' => get_field( 'office_slider_map_height' ),
    'map_zoom' => get_field( 'office_slider_map_zoom' ),
    'map_scroll_wheel_zoom' => get_field( 'office_slider_map_scroll_wheel_zoom' ),
    'map_marker_image' => get_field( 'office_slider_map_marker_image' ),
    'map_marker_image_size' => get_field( 'office_slider_map_marker_image_size' ),
    'hide_contact_details' => get_field( 'office_slider_hide_contact_details' ),
    'hide_border' => get_field( 'office_slider_hide_border' ),
    'hide_title' => get_field( 'office_slider_hide_title' ),
    'custom_title' => get_field( 'office_slider_custom_title' ),
    'hide_description' => get_field( 'office_slider_hide_description' ),
    'hide_link' => get_field( 'office_slider_hide_link' ),
    'link_text' => get_field( 'office_slider_link_text' ),
    'hide_address' => get_field( 'office_slider_hide_address' ),
    'hide_address_link' => get_field( 'office_slider_hide_address_link' ),
    'hide_coords' => get_field( 'office_slider_hide_coords' ),
    'hide_contact_label' => get_field( 'office_slider_hide_contact_label' ),
    'custom_contact_label' => get_field( 'office_slider_custom_contact_label' ),
    'hide_contact_avatar' => get_field( 'office_slider_hide_contact_avatar' ),
    'layout' => get_field( 'office_slider_card_layout' )
];
$start_at = (int) get_field( 'office_slider_start_at' );
$per_view = (int) get_field( 'office_slider_per_view' );
$slider_data = [
    'type' => get_field( 'office_slider_type' ),
    'startAt' => $start_at >= count( $offices ) ? 0 : $start_at,
    'perView' => $per_view >= count( $offices ) ? 1 : $per_view,
    'autoplay' => (int) get_field( 'office_slider_autoplay' ),
    'hoverpause' => get_field( 'office_slider_hoverpause' ),
    'animationDuration' => (int) get_field( 'office_slider_animation_duration' ),
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
            <?php if( $offices ) : ?>
                <ul class="glide__slides">
                    <?php foreach( $offices as $office ) : ?>
                        <li class="glide__slide">
                            <?php echo Ldr\Offices::instance()->render_office_card_block( $office->ID, $card_settings ); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
             <?php endif; ?>
        </div>

        <?php if( ! $hide_bullets && $offices ) : ?>
            <div class="glide__bullets" data-glide-el="controls[nav]">
                <?php for( $i = 0; $i < count( $offices ); $i++ ) : ?>
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