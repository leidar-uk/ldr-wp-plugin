<?php
/**
 * Block template: Post Card
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

$id = 'ldr-post-card-' . $block['id'];
$block_name = pathinfo( __DIR__, PATHINFO_FILENAME );
$block_url = plugin_dir_url( __FILE__ );
$block_dir = plugin_dir_path( __FILE__ );
$className = "ldr-block ldr-$block_name";

$card_class = 'card p-0';
$cover_image_container_style = '';
$cover_image_overlay_style = '';
$card_body_class = 'card-body d-flex flex-column justify-content-between h-100';

$post_id = (int) get_field( 'post_card_select' );
$cover_image_height = get_field( 'post_card_cover_image_height' );
$cover_image_overlay_color = get_field( 'post_card_cover_image_overlay_color' );
$card_min_height = get_field( 'post_card_min_height' );
$layout = get_field( 'post_card_layout' );
$is_column = ( ( $layout === 'column' ) || ( ! $layout ) ) ? true : false;
$card_class .= $is_column ? ' is-column' : ' is-row';
$card_hide_border = get_field( 'post_card_hide_border' );

$post_name = get_the_title( $post_id );
$post_hide_description = get_field( 'post_card_hide_description' );
$post_description = get_the_excerpt( $post_id );
$post_cover_image = get_the_post_thumbnail_url( $post_id );

$post_link = get_permalink( $post_id );
$post_link_text = get_field( 'post_card_link_text' );

if( $card_hide_border ) {
    $card_class .= ' border-0 rounded-0';
    
    if( $is_column ) {
        $card_body_class .= ( $cover_image_height > 0 ) ? ' pb-0 px-0' : ' p-0';
    } else {
        $card_body_class .= ' py-4 py-md-0 ps-0 ps-md-4 pe-0';
    }
}

$cover_image_container_style .= "min-height: {$cover_image_height}px;";
$cover_image_container_style .= "background-image: url($post_cover_image);";
$cover_image_overlay_style .= "background-color: $cover_image_overlay_color";

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
<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $className ); ?>" style="min-height: <?php echo $card_min_height?>px;">

    <div class="<?php echo $card_class; ?>">

        <?php if( ! $is_column ) : ?>
            <div class="row g-0">
                <div class="col-md-6">
        <?php endif; ?>

            <?php if( $cover_image_height > 0 ) : ?>
                <div class="card-img-top" style="<?php echo $cover_image_container_style; ?>">
                    <div class="color-overlay" style="<?php echo $cover_image_overlay_style; ?>">&nbsp;</div>
                </div>
            <?php endif; ?>

        <?php if( ! $is_column ) : ?>
                </div>
                <div class="col-md-6">
        <?php endif; ?>

                    <div class="<?php echo $card_body_class; ?>">
                        <div>
                            <p class="h4 card-title mt-0<?php echo ( $post_description !== '' && ! $post_hide_description ) ? '' : ' mb-0'; ?>"><?php echo empty( $post_custom_name ) ? $post_name : $post_custom_name; ?></p>

                            <?php if( /* $post_description !== '' &&  */! $post_hide_description ) : ?>
                                <p class="card-text mb-2"><?php echo $post_description; ?></p>
                            <?php endif; ?>
                        </div>

                        <a href="<?php echo $post_link?>" class="btn btn-ldr-primary mt-4 align-self-start"><?php echo $post_link_text !== '' ? $post_link_text : __( 'Find out more', 'ldr' ); ?></a>
                    </div>

        <?php if( ! $is_column ) : ?>
                </div>
            </div>
        <?php endif; ?> 
        
    </div>

</div>