<?php
/**
 * Block template: Post Grid
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

$id = 'ldr-post-grid-' . $block['id'];
$block_name = pathinfo( __DIR__, PATHINFO_FILENAME );
$block_url = plugin_dir_url( __FILE__ );
$block_dir = plugin_dir_path( __FILE__ );
$className = "ldr-block ldr-$block_name";

$categories = get_categories();

$hide_filters = get_field( 'post_grid_hide_filter' );
$posts_number = (int) get_field( 'post_grid_posts_number' );
$filtered_posts = (int) get_field( 'post_grid_filter_posts' );
$filtered_data = ! empty( $filtered_posts ) ? ' data-filter="' . $filtered_posts . '"' : '';
$custom_selection = get_field( 'post_grid_select_posts' );
$selected_posts = ! empty( $custom_selection ) ? " data-selected-posts='" . json_encode( array_map( function( $n ) { return (int) $n; }, $custom_selection ) ) . "'" : '';
$exclude_sticky_posts = (int) get_field( 'post_grid_exclude_sticky_posts' );

$card_settings = [
    'cover_image_height' => (int) get_field( 'post_grid_cover_image_height' ),
    'card_min_height' => (int) get_field( 'post_grid_card_min_height' ),
    'cover_image_overlay_color' => get_field( 'post_grid_cover_image_overlay_color' ),
    'hide_border' => (int) get_field( 'post_grid_hide_border' ),
    'hide_description' => (int) get_field( 'post_grid_hide_description' ),
    'link_text' => get_field( 'post_grid_link_text' ),
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
<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $className ); ?>"<?php echo $filtered_data; ?><?php echo $selected_posts; ?> data-card-settings='<?php echo json_encode( $card_settings ); ?>' data-exclude-sticky='<?php echo $exclude_sticky_posts; ?>' data-posts-number="<?php echo $posts_number; ?>">
    <?php if( ! $hide_filters ) : ?>
        <div class="grid-filter hstack gap-2 py-3 mb-5 border border-start-0 border-end-0 justify-content-end px-3">
            <span class="fw-bold"><?php _e( 'Filter by category', 'ldr' ); ?></span>
            <div class="dropdown d-flex flex-row justify-content-end align-items-center">
                <div>
                    <button class="btn btn-sm btn-ldr-primary dropdown-toggle" type="button" id="filterByCategory" data-bs-toggle="dropdown" aria-expanded="false"><?php _e( 'Show all', 'ldr' ); ?></button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterByCategory">
                        <li><button type="button" id="0" class="dropdown-item"><?php _e( 'Show all', 'ldr' ); ?></button></li>
                        <?php foreach( $categories as $category ) : ?>
                            <?php if( $category->slug !== 'article' ) : ?>
                                <li><button type="button" id="<?php echo $category->term_id; ?>" data-slug="<?php echo $category->slug; ?>" class="dropdown-item"><?php echo $category->name; ?></button></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="d-flex flex-row justify-content-end align-items-center">
                    <button class="btn btn-sm btn-ldr-primary ms-2 d-none reset-filter" type="button"><?php _e( 'Reset', 'ldr' ); ?></button>
                    <button class="btn btn-sm btn-ldr-primary ms-2 d-none copy-filtered-results" type="button"><?php _e( 'Copy to clipboard', 'ldr' ); ?></button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="query-output">
        <div class="grid-items"></div>
        <?php if( $posts_number > 0 ) : ?>
            <div class="d-flex justify-content-center align-items-center mt-5">
                <button type="button" class="btn btn-lg btn-primary px-4 py-3 load-more d-inline-block"><div class="loader d-inline-block me-2 mb-0"><div class="spinner-border spinner-border-sm text-white" role="status"><span class="visually-hidden"><?php _e( 'Loading posts', 'ldr' ); ?>...</span></div></div><?php _e( 'Load more', 'ldr' ); ?></button>
            </div>
        <?php else : ?>
            <div class="loader d-flex justify-content-center mt-5 mb-4">
                <div class="spinner-border text-secondary" role="status">
                    <span class="visually-hidden"><?php _e( 'Loading posts', 'ldr' ); ?>...</span>
                </div>
            </div>
        <?php endif;?>
    </div>
</div>