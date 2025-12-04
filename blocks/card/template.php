<?php
/**
 * Block template: Card
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

$id = 'ldr-card-' . $block['id'];
$block_name = pathinfo( __DIR__, PATHINFO_FILENAME );
$block_url = plugin_dir_url( __FILE__ );
$block_dir = plugin_dir_path( __FILE__ );
$className = "ldr-block ldr-$block_name";

$minimal_height = get_field( 'card_min_height' );
$remove_border = get_field( 'card_remove_border' );
$hide_image = get_field( 'card_hide_image' );
$card_image = get_field( 'card_image' );
$overlay_color = get_field( 'card_image_overlay_color' );
$hide_title = get_field( 'card_hide_title' );
$card_title_text = get_field( 'card_title_text' );
$hide_body_text = get_field( 'card_hide_body_text' );
$card_body_text = get_field( 'card_body_text' );
$hide_button = get_field( 'card_hide_button' );
$card_link = get_field( 'card_link' );
$content_alignment = get_field( 'content_alignment' );
$card_container_class = 'card';
$card_body_class = 'card-body d-flex flex-column justify-content-between';
$card_title_class = 'card-title mb-3';
$card_body_text_class = 'card-text';
$container_style = 'style="';
$card_overlay_style = 'style="';
$button_class = 'btn btn-ldr-primary align-self-start mt-4';

if( $minimal_height ) {
    $container_style .= "min-height: {$minimal_height}px;";
    $container_style .= '"';
}

if( $remove_border ) {
    $card_container_class .= ' border-0 rounded-0';
    $card_body_class .= ' px-0';
}

if( $hide_title && $hide_body_text && $hide_button ) {
    $card_body_class .= ' p-0';
}

if( $hide_image ) {
    $card_title_class .= ' mt-0';
} else {
    $card_title_class .= ' mt-2';
}

if( $overlay_color ) {
    $card_overlay_style .= "background-color: {$overlay_color};";
    $card_overlay_style .= '"';
}

if( $content_alignment === 'start' ) {
    $card_title_class .= ' text-start';
    $card_body_text_class .= ' text-start';
}

if( $content_alignment === 'center' ) {
    $card_title_class .= ' text-center';
    $card_body_text_class .= ' text-center';
    $button_class = str_replace( 'align-self-start', 'align-self-center', $button_class );
}

if( $content_alignment === 'end' ) {
    $card_title_class .= ' text-end';
    $card_body_text_class .= ' text-end';
    $button_class = str_replace( 'align-self-start', 'align-self-end', $button_class );
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
<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $className ); ?>">
    <div class="<?php echo $card_container_class; ?>" <?php echo $container_style; ?>>
        <?php if( ! $hide_image ) : ?>
            <div class="card-image" style="background-image: url(<?php echo $card_image['sizes']['medium_large']; ?>);">
                <div class="color-overlay" <?php echo $card_overlay_style; ?>>&nbsp;</div>
            </div>
        <?php endif; ?>

        <div class="<?php echo $card_body_class; ?>">
            <div>
                <?php if( ! $hide_title ) : ?>
                    <h5 class="<?php echo $card_title_class; ?>"><?php echo $card_title_text; ?></h5>
                <?php endif; ?>
                <?php if( ! $hide_body_text ) : ?>
                    <p class="<?php echo $card_body_text_class; ?>"><?php echo $card_body_text; ?></p>
                <?php endif; ?>
            </div>

            <?php if( ! $hide_button ) : ?>
                <a href="<?php echo ! empty( $card_link['url'] ) ? $card_link['url'] : '/'; ?>" target="<?php echo ! empty( $card_link['target'] ) ? $card_link['target'] : '_self' ?>" class="<?php echo $button_class; ?>"><?php echo ! empty( $card_link['title'] ) ? $card_link['title'] : __( 'Read more', 'ldr' ); ?></a>
            <?php endif; ?>
        </div>

    </div>
</div>