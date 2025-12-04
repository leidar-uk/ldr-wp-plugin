<?php
/**
 * Block template: Tags
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

$id = 'ldr-tags-' . $block['id'];
$block_name = pathinfo( __DIR__, PATHINFO_FILENAME );
$block_url = plugin_dir_url( __FILE__ );
$block_dir = plugin_dir_path( __FILE__ );
$className = "ldr-block ldr-$block_name $block_name";

$label = get_field( 'tags_label' );
$show_borders = get_field( 'tags_show_borders' );
$the_tags = get_the_tags( $post_id );
$tags = $the_tags ? array_map( function( $n ) {
    return '<a href="' . get_term_link( $n->term_id ) . '"><span class="badge text-bg-primary">' . $n->name . '</span></a>';
}, $the_tags ) : [];

$className .= $show_borders ? ' py-3 border border-start-0 border-end-0' : '';

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
    <div class="d-flex flex-column flex-md-column text-center text-md-start">
        <?php if( ! empty( $tags ) ) : ?>
            <?php if( ! empty( $label ) ) : ?>
                <span class="d-inline-block mb-2"><?php echo $label; ?>:</span>
            <?php endif; ?>
            <div>
                <?php echo implode( '', $tags ); ?>
            </div>
        <?php endif; ?>
    </div>
</div>