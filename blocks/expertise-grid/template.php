<?php
/**
 * Block template: Post Grid
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $expertise_id The expertise ID this block is saved to.
 */

$id = 'ldr-expertise-grid-' . $block['id'];
$block_name = pathinfo( __DIR__, PATHINFO_FILENAME );
$block_url = plugin_dir_url( __FILE__ );
$block_dir = plugin_dir_path( __FILE__ );
$className = "ldr-block ldr-$block_name";

$categories = get_categories();

$hide_filters = get_field( 'expertise_grid_hide_filter' );
$expertise_number = (int) get_field( 'expertise_grid_expertise_number' );
$filtered_expertise = (int) get_field( 'expertise_grid_filter_expertise' );
$filtered_data = ! empty( $filtered_expertise ) ? ' data-filter="' . $filtered_expertise . '"' : '';
$custom_selection = get_field( 'expertise_grid_select_expertise' );
$selected_expertise = ! empty( $custom_selection ) ? " data-selected-expertise='" . json_encode( array_map( function( $n ) { return (int) $n; }, $custom_selection ) ) . "'" : '';
$omit_expertise = get_field( 'expertise_grid_exclude_expertise' );
$excluded_expertise = ! empty( $omit_expertise ) ? " data-excluded-expertise='" . json_encode( array_map( function( $n ) { return (int) $n; }, $omit_expertise ) ) . "'" : '';
$exclude_sticky_expertise = (int) get_field( 'expertise_grid_exclude_sticky_expertise' );

$card_settings = [
    'cover_image_height' => (int) get_field( 'expertise_grid_cover_image_height' ),
    'card_min_height' => (int) get_field( 'expertise_grid_card_min_height' ),
    'cover_image_overlay_color' => get_field( 'expertise_grid_cover_image_overlay_color' ),
    'hide_border' => (int) get_field( 'expertise_grid_hide_border' ),
    'hide_description' => (int) get_field( 'expertise_grid_hide_description' ),
    'link_text' => get_field( 'expertise_grid_link_text' ),
    'layout' => 'column'
];

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
<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $className ); ?>"<?php echo $filtered_data; ?><?php echo $selected_expertise; ?> data-card-settings='<?php echo json_encode( $card_settings ); ?>' <?php echo $excluded_expertise; ?> data-expertise-number="<?php echo $expertise_number; ?>">
    <div class="query-output">
        <div class="grid-items"></div>
        <?php if( $expertise_number > 0 ) : ?>
            <div class="d-flex justify-content-center align-items-center mt-5">
                <button type="button" class="btn btn-lg btn-primary px-4 py-3 load-more d-inline-block"><div class="loader d-inline-block me-2 mb-0"><div class="spinner-border spinner-border-sm text-white" role="status"><span class="visually-hidden"><?php _e( 'Loading expertise', 'ldr' ); ?>...</span></div></div><?php _e( 'Load more', 'ldr' ); ?></button>
            </div>
        <?php else : ?>
            <div class="loader d-flex justify-content-center mt-5 mb-4">
                <div class="spinner-border text-secondary" role="status">
                    <span class="visually-hidden"><?php _e( 'Loading expertise', 'ldr' ); ?>...</span>
                </div>
            </div>
        <?php endif;?>
    </div>
</div>