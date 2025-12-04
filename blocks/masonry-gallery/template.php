<?php
/**
 * Block template: Masonry Gallery
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

$id = 'ldr-masonry-gallery-' . $block['id'];
$block_name = pathinfo( __DIR__, PATHINFO_FILENAME );
$block_url = plugin_dir_url( __FILE__ );
$block_dir = plugin_dir_path( __FILE__ );
$className = "ldr-block ldr-$block_name";

$has_title = get_field( 'show_gallery_title' );
$items = get_field( 'masonry_gallery_items' ) ?: [];

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
<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $className ); ?>">
    <div class="ldr-masonry-gallery__items mx-n2">
        <?php if( ! empty( $items ) ) : ?>
            <div class="sizer"></div>
            <?php foreach( $items as $item ) : ?>
                <div class="item p-2">
                    <div class="inner-wrapper position-relative overflow-hidden">
                        <img 
                            src="<?php echo $item['sizes']['medium_large']; ?>" 
                            width="<?php echo $item['sizes']['medium_large-width']; ?>" 
                            height="<?php echo $item['sizes']['medium_large-height']; ?>" 
                            alt="<?php echo $item['alt']; ?>"
                        />
                    </div>
                    <?php // var_dump( $item ); ?>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <?php _e( 'There is no items in the gallery...', 'ldr' ); ?>
        <?php endif; ?>
    </div>
</div>