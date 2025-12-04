<?php
/**
 * Block template: Office Map
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

$id = 'ldr-offices-map-' . $block['id'];
$block_name = pathinfo( __DIR__, PATHINFO_FILENAME );
$block_url = plugin_dir_url( __FILE__ );
$block_dir = plugin_dir_path( __FILE__ );
$className = "ldr-block ldr-$block_name";

$map_height = get_field( 'offices_map_height' );
$lat = get_field( 'offices_map_center_lat' );
$long = get_field( 'offices_map_center_long' );
$zoom = get_field( 'offices_map_zoom' );
$marker = get_field( 'offices_map_marker_image' );
$marker_size = get_field( 'offices_map_marker_size' );
$show_popup = get_field( 'map_marker_show_popup' );
$scroll_wheel_zoom = get_field( 'map_scroll_wheel_zoom' );
$grayscale_map = get_field( 'map_grayscale' );
$global_office = get_posts( [
    'post_type' => 'office',
    'numberposts' => 1,
    'post_name__in' => ['global']
] );
$office_post_types = get_posts( [
    'post_type' => 'office',
    'post_status' => 'publish',
    'numberposts' => -1,
    'order' => 'ASC',
    'orderby' => 'title',
    'post__not_in' => [$global_office[0]->ID]
] );
$offices = array_map( function( $n ) {
    return [
        'address' => get_field( 'address', $n->ID ),
        'lat' => (float) get_field( 'latitude', $n->ID ),
        'long' => (float) get_field( 'longitude', $n->ID ),
    ];
}, $office_post_types );
$map_settings = [
    'map_center_lat' => (float) $lat, 
    'map_center_long' => (float) $long, 
    'zoom' => (int) $zoom,
    'marker' => ! $marker ? $block_url . 'images/default-marker.png' : $marker,
    'marker_size' => (int) $marker_size,
    'show_popup' => (bool) $show_popup,
    'offices' => $offices,
    'scroll_wheel_zoom' => (bool) $scroll_wheel_zoom,
];
$map_style = '';

if( $map_height ) {
    $map_style = 'style="';
    if( $map_height ) {
        $map_style .= "min-height: {$map_height}vh;";
    }
    $map_style .= '"';
}

if( ! empty( $block['anchor'] ) ) {
    $id = $block['anchor'];
}

if( ! empty( $block['className'] ) ) {
    $className .= ' ' . $block['className'];
}

if( $grayscale_map ) {
    $className .= ' grayscale-map';
}

if( ! empty( $block['align'] ) ) {
    $className .= ' align' . $block['align'];
}

?>
<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $className ); ?>" data-map-settings='<?php echo json_encode( $map_settings ); ?>'>
    <div class="container px-0">
        <div class="ldr-offices-map-container" <?php echo $map_style; ?>>&nbsp;</div>
    </div>
</div>