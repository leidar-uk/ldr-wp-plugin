<?php
/**
 * Block template: Box
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

$id = 'ldr-box-' . $block['id'];
$block_name = pathinfo( __DIR__, PATHINFO_FILENAME );
$block_url = plugin_dir_url( __FILE__ );
$block_dir = plugin_dir_path( __FILE__ );
$className = "ldr-block ldr-$block_name d-flex";
$container_class = 'container px-0 px-sm-3 d-flex flex-column';

$minimal_height = get_field( 'box_min_height' );
$ignore_max_height = get_field( 'box_ignore_max_height' );
$vertical_align = get_field( 'box_vertical_content_alignment' );
$fluid_container = get_field( 'box_fluid_container' );
$padding = get_field( 'box_container_padding' );
$background_color = get_field( 'box_background_color' );
$background_image = get_field( 'box_background_image' );
$overlay_color = get_field( 'box_overlay_color' );
$repeat_bg = get_field( 'box_repeat_bg' );
$container_style_args = [];
$container_style = [];
$box_overlay_style = 'style="';

if( $ignore_max_height ) {
    $className .= ' ignore-max-height';
}

if( $vertical_align ) {
    $container_class .= " $vertical_align";
}

if( $fluid_container ) {
    $container_class = str_replace( 'container px-0 px-sm-3', 'container-fluid p-0', $container_class );
}

if( $padding ) {
    $className .= " $padding";
    $container_class = str_replace( 'px-0 px-sm-3', $padding, $container_class );
}

if( $background_color ) {
    $container_style_args['background-color'] = $background_color;
}

if( $repeat_bg ) {
    $container_style_args['background-size'] = 'auto';
    $container_style_args['background-repeat'] = 'repeat';
}

if( $minimal_height ) {
    $container_style_args['min-height'] = "{$minimal_height}px";
}

if( $background_image ) {
    $container_style_args['background-image'] = "url({$background_image['url']})";
}

foreach( $container_style_args as $property => $css_value ) {
    $container_style[] = $property . ':' . $css_value;
}

if( $overlay_color ) {
    $box_overlay_style .= "background-color: {$overlay_color};";
    $box_overlay_style .= '"';
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
<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $className ); ?>" <?php echo 'style="' . implode( ';', $container_style ) . '"'; ?>>
    <?php if( $background_image ) : ?>
        <span role="img" aria-label="<?php echo $background_image['alt']; ?>"></span>
        <div class="color-overlay" <?php echo $box_overlay_style; ?>>&nbsp;</div>
    <?php endif; ?>
    <div class="<?php echo $container_class; ?>">
        <InnerBlocks />
    </div>
</div>