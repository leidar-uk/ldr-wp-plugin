<?php
/**
 * Block template: Pull Quote
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

$id = 'ldr-pull-quote-' . $block['id'];
$block_name = pathinfo( __DIR__, PATHINFO_FILENAME );
$block_url = plugin_dir_url( __FILE__ );
$block_dir = plugin_dir_path( __FILE__ );
$className = "ldr-block ldr-$block_name $block_name";

$message = get_field( 'pull_quote_message' );
$author = get_field( 'pull_quote_author' );
$placeholder = '<span class="fst-italic">' . __( 'Pull quote message goes here...', 'ldr' ) . '</span>';

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
    <p class="fst-italic has-large-font-size m-0"><?php echo ! $message ? $placeholder : $message; ?></p>
    <?php if( ! empty( $author ) ) : ?>
        <small><em><?php echo $author; ?></em></small>
    <?php endif; ?>
</div>