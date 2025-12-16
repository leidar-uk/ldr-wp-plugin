<?php
/**
 * Block template: Team Grid
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

$id = 'ldr-team-grid-' . $block['id'];
$block_name = pathinfo( __DIR__, PATHINFO_FILENAME );
$block_url = plugin_dir_url( __FILE__ );
$block_dir = plugin_dir_path( __FILE__ );
$className = "ldr-block ldr-$block_name";

$offices = Ldr\Offices::instance()->prepare_office_radio_choices();

$hide_filters = get_field( 'team_grid_hide_filter' );
$filtered_members = (int) get_field( 'team_grid_filter_members' );
$filtered_data = ! empty( $filtered_members ) ? ' data-filter="' . $filtered_members . '"' : '';
$custom_selection = get_field( 'team_grid_select_members' );
$exclude_members = get_field( 'team_grid_exclude_members' );

$selected_members = ! empty( $custom_selection ) ? " data-selected-members='" . json_encode( array_map( function( $n ) { return (int) $n; }, $custom_selection ) ) . "'" : '';
$excluded_members = ! empty( $exclude_members ) ? " data-excluded-members='" . json_encode( array_map( function( $n ) { return (int) $n; }, $exclude_members ) ) . "'" : '';
$hide_contact_details = get_field( 'team_grid_hide_contact_details' );
$card_layout = get_field( 'team_grid_card_layout' );
$hide_location = get_field( 'team_grid_hide_location' );
$image_size = get_field( 'team_grid_profile_image_size' );

if( $image_size && ! empty( $image_size ) ) {
    $className .= ' has-profile-image-' . $image_size;
}

if( $hide_location ) {
    $className .= ' hide-location';
}

if( $card_layout === 'v' ) {
    $className .= ' is-member-card-vertical';
} 

if( $card_layout === 'h' ) {
    $className .= ' is-member-card-horizontal';
}

if( ! empty( $block['anchor'] ) ) {
    $id = $block['anchor'];
}

if( ! empty( $block['className'] ) ) {
    $className .= ' ' . $block['className'];
}

if( $hide_contact_details ) {
    $className .= ' has-hidden-contact-details';
}

if( ! empty( $block['align'] ) ) {
    $className .= ' align' . $block['align'];
}

?>
<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $className ); ?>"<?php echo $filtered_data; ?><?php echo $selected_members; ?><?php echo $excluded_members; ?>>
    <?php if( ! $hide_filters ) : ?>
        <div class="grid-filter hstack gap-2 py-3 mb-5 border border-start-0 border-end-0 justify-content-end px-3">
            <span class="fw-bold"><?php _e( 'Filter by office', 'ldr' ); ?></span>
            <div class="dropdown">
                <button class="btn btn-sm btn-ldr-primary dropdown-toggle" type="button" id="filterByOffice" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php _e( 'Show all', 'ldr' ); ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterByOffice">
                    <li><button type="button" id="0" class="dropdown-item"><?php _e( 'Show all', 'ldr' ); ?></button></li>
                    <?php foreach( $offices as $office_id => $office_name ) : ?>
                        <li><button type="button" id="<?php echo $office_id; ?>" class="dropdown-item"><?php echo $office_name; ?></button></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <div class="query-output">
        <div class="loader d-flex justify-content-center mb-4">
            <div class="spinner-border text-secondary" role="status">
                <span class="visually-hidden"><?php _e( 'Loading team members', 'ldr' ); ?>...</span>
            </div>
        </div>
        <div class="grid-items"></div>
    </div>
</div>