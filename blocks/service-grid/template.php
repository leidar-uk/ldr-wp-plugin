<?php
/**
 * Block template: Service Grid
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $service_id The service ID this block is saved to.
 */

$id = 'ldr-service-grid-' . $block['id'];
$block_name = pathinfo( __DIR__, PATHINFO_FILENAME );
$block_url = plugin_dir_url( __FILE__ );
$block_dir = plugin_dir_path( __FILE__ );
$className = "ldr-block ldr-$block_name";

$term_query = new \WP_Term_Query( [
    'taxonomy' => 'groups',
    'hide_empty' => false
] );
$categories = array_filter( $term_query->terms, function( $n ) { return $n->slug !== 'default-undefined'; } );

$hide_filters = get_field( 'service_grid_hide_filter' );
$filtered_services = (int) get_field( 'service_grid_filter_services' );
$filtered_data = ! empty( $filtered_services ) ? ' data-filter="' . $filtered_services . '"' : '';
$custom_selection = get_field( 'service_grid_select_services' );
$selected_services = ! empty( $custom_selection ) ? " data-selected-services='" . json_encode( array_map( function( $n ) { return (int) $n; }, $custom_selection ) ) . "'" : '';
$exclude_sticky_services = (int) get_field( 'service_grid_exclude_sticky_services' );
$omit_children = get_field( 'service_grid_omit_children' );

$card_settings = [
    'cover_image_height' => (int) get_field( 'service_grid_cover_image_height' ),
    'card_min_height' => (int) get_field( 'service_grid_card_min_height' ),
    'cover_image_overlay_color' => get_field( 'service_grid_cover_image_overlay_color' ),
    'hide_border' => (int) get_field( 'service_grid_hide_border' ),
    'hide_description' => (int) get_field( 'service_grid_hide_description' ),
    'link_text' => get_field( 'service_grid_link_text' ),
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
<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $className ); ?>"<?php echo $filtered_data; ?><?php echo $selected_services; ?> data-card-settings='<?php echo json_encode( $card_settings ); ?>' data-exclude-sticky='<?php echo $exclude_sticky_services; ?>' data-omit-children='<?php echo $omit_children; ?>'>
    <?php if( ! $hide_filters ) : ?>
        <div class="grid-filter hstack gap-2 py-3 mb-5 border border-start-0 border-end-0 justify-content-end px-3">
            <span class="fw-bold"><?php _e( 'Filter by category', 'ldr' ); ?></span>
            <div class="dropdown">
                <button class="btn btn-sm btn-ldr-primary dropdown-toggle" type="button" id="filterByCategory" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php _e( 'Show all', 'ldr' ); ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterByCategory">
                    <li><button type="button" id="0" class="dropdown-item"><?php _e( 'Show all', 'ldr' ); ?></button></li>
                    <?php foreach( $categories as $category ) : ?>
                        <li><button type="button" id="<?php echo $category->term_id; ?>" class="dropdown-item"><?php echo $category->name; ?></button></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <div class="query-output">
        <div class="loader d-flex justify-content-center mb-4">
            <div class="spinner-border text-secondary" role="status">
                <span class="visually-hidden"><?php _e( 'Loading services', 'ldr' ); ?>...</span>
            </div>
        </div>
        <div class="grid-items"></div>
    </div>
</div>