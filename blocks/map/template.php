<?php
/**
 * Block template: Map
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

$id = 'ldr-map-' . $block['id'];
$block_name = pathinfo( __DIR__, PATHINFO_FILENAME );
$block_url = plugin_dir_url( __FILE__ );
$block_dir = plugin_dir_path( __FILE__ );
$className = "ldr-block ldr-$block_name";

$map_height = get_field( 'map_height' );
$lat = get_field( 'map_lat' );
$long = get_field( 'map_long' );
$zoom = get_field( 'map_zoom' );
$marker = get_field( 'map_marker_image' );
$marker_size = get_field( 'map_marker_size' );
$pop_up_text = get_field( 'map_marker_popup_text' );
$scroll_wheel_zoom = get_field( 'map_scroll_wheel_zoom' );
$grayscale_map = get_field( 'map_grayscale' );
$map_settings = [
    'lat' => (float) $lat, 
    'long' => (float) $long, 
    'zoom' => (int) $zoom,
    'marker' => ! $marker ? $block_url . 'images/default-marker.png' : $marker,
    'marker_size' => (int) $marker_size,
    'pop_up_text' => $pop_up_text,
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
<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $className ); ?>" <?php echo $container_style; ?> data-map-settings='<?php echo json_encode( $map_settings ); ?>'>
    <div class="container px-0">
        <div class="ldr-map-container" <?php echo $map_style; ?>>&nbsp;</div>
    </div>
</div>