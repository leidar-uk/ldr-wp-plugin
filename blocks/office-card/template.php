<?php
/**
 * Block template: Office card
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

$id = 'ldr-office-card-' . $block['id'];
$block_name = pathinfo( __DIR__, PATHINFO_FILENAME );
$block_url = plugin_dir_url( __FILE__ );
$block_dir = plugin_dir_path( __FILE__ );
$className = "ldr-block ldr-$block_name";

$card_class = 'card p-0';
$map_container_style = '';
$cover_image_container_style = '';
$cover_image_overlay_style = '';
$card_body_class = 'card-body d-flex flex-column justify-content-between h-100';
$avatar_container_style = '';

$office_id = (int) get_field( 'office_card_select' );
$hide_map = get_field( 'office_card_hide_map' );
$map_height = get_field( 'office_card_map_height' );
$map_zoom = get_field( 'office_card_map_zoom' );
$scroll_wheel_zoom = get_field( 'office_card_map_scroll_wheel_zoom' );
$marker_image = get_field( 'office_card_map_marker_image' );
$marker_path = ! $marker_image ? $block_dir . 'images/default-marker.png' : $marker_image;
$marker_size = get_field( 'office_card_map_marker_image_size' );
$cover_image_height = get_field( 'office_card_cover_image_height' );
$cover_image_overlay_color = get_field( 'office_card_cover_image_overlay_color' );
$layout = get_field( 'office_card_layout' );
$is_column = ( ( $layout === 'column' ) || ( ! $layout ) ) ? true : false;
$card_class .= $is_column ? ' is-column' : ' is-row';
$card_hide_border = get_field( 'office_card_hide_border' );

$office_hide_name = get_field( 'office_card_hide_title' );
$office_name = get_the_title( $office_id );
$office_custom_name = get_field( 'office_card_custom_title' );
$office_hide_description = get_field( 'office_card_hide_description' );
$office_description = get_the_excerpt( $office_id );
$office_cover_image = get_the_post_thumbnail_url( $office_id );
$office_hide_link = get_field( 'office_card_hide_link' );
$office_link = get_permalink( $office_id );
$office_link_text = get_field( 'office_card_link_text' );
$office_hide_address = get_field( 'office_card_hide_address' );
$office_hide_address_link = get_field( 'office_card_hide_address_link' );
$office_address = get_field( 'address', $office_id );
$office_hide_coords = get_field( 'office_card_hide_coords' );
$office_latitude = (float) get_field( 'latitude', $office_id );
$office_longitude = (float) get_field( 'longitude', $office_id );
$office_phone_number = get_field( 'phone_number', $office_id );
$office_email_address = get_field( 'email_address', $office_id );
$office_has_key_contact = get_field( 'select_contact_person', $office_id );
$office_hide_contact_details = get_field( 'office_card_hide_contact_details' );
$office_hide_avatar = get_field( 'office_card_hide_contact_avatar' );
$office_hide_contact_label = get_field( 'office_card_hide_contact_label' );
$office_custom_contact_label = get_field( 'office_card_custom_contact_label' );
$key_contact_id = (int) get_field( 'contact_person', $office_id );
$key_contact_name = get_the_title( $key_contact_id );
$key_contact_avatar = get_the_post_thumbnail_url( $key_contact_id );
$key_contact_avatar_id = get_post_thumbnail_id( $key_contact_id );
$key_contact_avatar_alt = get_post_meta( $key_contact_avatar_id, '_wp_attachment_image_alt', true );
$key_contact_phone_number = get_field( 'phone_number', $key_contact_id );
$key_contact_email_address = get_field( 'email_address', $key_contact_id );
$key_contact_role = get_field( 'role', $key_contact_id );

$map_settings = [
    'latLong' => [$office_latitude, $office_longitude],
    'zoom' => (int) $map_zoom,
    'scrollWheelZoom' => (bool) $scroll_wheel_zoom,
    'markerUrl' => $marker_path,
    'markerSize' => ! $marker_size ? 64 : (int) $marker_size,
    'address' => $office_address,
    'mapHeight' => (int) $map_height,
];

if( $card_hide_border ) {
    $card_class .= ' border-0 rounded-0';
    
    if( $is_column ) {
        $card_body_class .= ( $cover_image_height > 0 ) ? ' pb-0 px-0' : ' p-0';
    } else {
        $card_body_class .= ' py-4 py-md-0 ps-0 ps-md-4 pe-0';
    }
}

if( ! $hide_map ) {
    $map_container_style .= "min-height: {$map_height}px;";
} else {
    $cover_image_container_style .= "min-height: {$cover_image_height}px;";
    $cover_image_container_style .= "background-image: url($office_cover_image);";
    $cover_image_overlay_style .= "background-color: $cover_image_overlay_color";
}

if( ! $office_hide_avatar ) {
    $avatar_container_style .= "background-image: url($key_contact_avatar);";
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

    <div class="<?php echo $card_class; ?>">

        <?php if( ! $is_column ) : ?>
            <div class="row g-0">
                <div class="col-md-6">
        <?php endif; ?>

            <?php if( ! $hide_map ) : ?>
                <div class="card-map-top" style="<?php echo $map_container_style; ?>" data-map-settings='<?php echo json_encode( $map_settings ); ?>'></div>
            <?php else : ?>
                <?php if( $cover_image_height > 0 ) : ?>
                    <div class="card-img-top" style="<?php echo $cover_image_container_style; ?>">
                        <div class="color-overlay" style="<?php echo $cover_image_overlay_style; ?>">&nbsp;</div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

        <?php if( ! $is_column ) : ?>
                </div>
                <div class="col-md-6">
        <?php endif; ?>

        <div class="<?php echo $card_body_class; ?>">
            <div>

                <?php if( ! $office_hide_name ) : ?>
                    <p class="h6 card-title mt-0 pb-2 border border-top-0 border-start-0 border-end-0 fw-bold text-uppercase"><?php echo empty( $office_custom_name ) ? $office_name : $office_custom_name; ?></p>
                <?php endif; ?>

                <?php if( $office_description !== '' && ! $office_hide_description ) : ?>
                    <p class="card-text mb-2"><?php echo $office_description; ?></p>
                <?php endif; ?>

                <?php if( ! $office_hide_address ) : ?>
                    <p class="mb-2">
                        <?php echo $office_address; ?>
                        <?php if( ! $office_hide_address_link && $office_latitude && $office_longitude && $office_hide_coords  ) : ?>
                            &nbsp;
                            <a href="https://www.openstreetmap.org/?mlat=<?php echo $office_latitude; ?>&mlon=<?php echo $office_longitude; ?>#map=<?php echo $map_zoom ? $map_zoom : 16; ?>/<?php echo $office_latitude; ?>/<?php echo $office_longitude; ?>" target="_blank">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        <?php endif; ?>
                    </p>
                    <?php if( $office_latitude && $office_longitude && ! $office_hide_coords  ) : ?>
                        <p class="mb-2">
                            <?php if( ! $office_hide_address_link ) : ?>
                                <a href="https://www.openstreetmap.org/?mlat=<?php echo $office_latitude; ?>&mlon=<?php echo $office_longitude; ?>#map=<?php echo $map_zoom ? $map_zoom : 16; ?>/<?php echo $office_latitude; ?>/<?php echo $office_longitude; ?>" target="_blank">
                            <?php endif; ?>
                                Lat: <?php echo $office_latitude; ?>, Long: <?php echo $office_longitude; ?> 
                                <?php if( ! $office_hide_address_link ) : ?>&nbsp;<i class="bi bi-box-arrow-up-right"></i><?php endif; ?>
                            <?php if( ! $office_hide_address_link ) : ?>
                                </a>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                <?php endif; ?>

            <?php if( $office_hide_link ) : ?>
            </div>
            <div>
            <?php endif; ?>
            
                <?php if( ! $office_hide_contact_details ) : ?>
                    <?php if( ! $office_hide_contact_label ) : ?>
                        <p class="h6 card-title mt-3 mb-0 pb-2 fw-bold text-uppercase"><?php echo empty( $office_custom_contact_label ) ? __( 'Contact details', 'ldr' ) : $office_custom_contact_label; ?></p>
                    <?php endif; ?>

                    <?php if( ! $office_has_key_contact && ( $office_phone_number || $office_email_address ) ) : ?>
                        <p class="mb-2">
                            <?php if( $office_phone_number ) : ?>
                                <?php _e( 'Tel', 'ldr' ); ?>: <a href="tel:<?php echo $office_phone_number; ?>" target="_blank"><?php echo $office_phone_number; ?></a>
                            <?php endif; ?>
                            <?php if( $office_email_address ) : ?>
                                <a href="mailto:<?php echo $office_email_address; ?>" target="_blank"><?php echo $office_email_address; ?></a>
                            <?php endif; ?>
                        </p>
                    <?php else : ?>
                        <div class="contact-details d-flex flex-column flex-md-row justify-content-center justify-content-md-start align-items-md-center gap-3 py-3 py-md-3 border border-start-0 border-end-0">
                            <?php if( ! $office_hide_avatar ) : ?>
                                <span role="img" aria-label="<?php echo $key_contact_avatar_alt; ?>"></span>
                                <div class="contact-avatar rounded-circle flex-shrink-0" style="<?php echo $avatar_container_style; ?>">&nbsp;</div>
                            <?php endif; ?>
                            <div>
                                <p class="mb-0"><?php echo $key_contact_name?></p>
                                <?php if( $key_contact_role ) : ?>
                                    <p class="mb-0"><?php echo $key_contact_role?></p>
                                <?php endif; ?>
                                <?php if( $key_contact_phone_number ) : ?>
                                    <p class="mb-0"><?php _e( 'Tel', 'ldr' ); ?>: <a href="tel:<?php echo $key_contact_phone_number; ?>" terget="_blank"><?php echo $key_contact_phone_number; ?></a></p>
                                <?php endif; ?>
                                <?php if( $key_contact_email_address ) : ?>
                                    <p class="mb-0"><a href="mailto:<?php echo $key_contact_email_address; ?>" terget="_blank"><?php echo $key_contact_email_address; ?></a></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

            <?php if( ! $office_hide_link ) : ?>
            </div>
            <div>
            <?php endif; ?>

                <?php if( ! $office_hide_link ) : ?>
                    <a href="<?php echo $office_link?>" class="btn btn-ldr-primary mt-4"><?php echo $office_link_text !== '' ? $office_link_text : __( 'Find out more', 'ldr' ); ?></a>
                <?php endif; ?>

            </div>
        </div>

        <?php if( ! $is_column ) : ?>
                </div>
            </div>
        <?php endif; ?> 
        
    </div>

</div>