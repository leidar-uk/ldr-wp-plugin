<?php
/**
 * Block template: Alert
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

$id = 'ldr-alert-' . $block['id'];
$block_name = pathinfo( __DIR__, PATHINFO_FILENAME );
$block_url = plugin_dir_url( __FILE__ );
$block_dir = plugin_dir_path( __FILE__ );
$className = "ldr-block ldr-$block_name $block_name";

$message = get_field( 'alert_message' );
$type = get_field( 'alert_type' );
$has_icon = get_field( 'alert_has_icon' );

$className .= ! empty( $type ) ? " alert-{$type}" : ' alert-info';
$className .= ! empty( $has_icon ) ? " alert-has-icon" : '';

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
<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $className ); ?>" role="alert">
    <div class="d-flex">
        <?php if( ! empty( $has_icon ) ) : ?>
            <?php if( $type === 'success' ) : ?>
                <i class="bi bi-star me-3"></i>
            <?php elseif( $type === 'danger' ) : ?>
                <i class="bi bi-x-octagon me-3"></i>
            <?php elseif( $type === 'warning' ) : ?>
                <i class="bi bi-exclamation-triangle me-3"></i>
            <?php elseif( $type === 'info' ) : ?>
                <i class="bi bi-info-circle me-3"></i>
            <?php endif; ?>
        <?php endif; ?>
        <span><?php echo $message; ?></span>
    </div>
</div>