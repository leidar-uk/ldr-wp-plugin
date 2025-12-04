<?php
/**
 * Block template: Member Card
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

$id = 'ldr-member-card-' . $block['id'];
$block_name = pathinfo( __DIR__, PATHINFO_FILENAME );
$block_url = plugin_dir_url( __FILE__ );
$block_dir = plugin_dir_path( __FILE__ );
$className = "ldr-block ldr-$block_name";
$profile_image_class = 'profile-image';

$member_id = (int) get_field( 'member_card_select' );
$member = Ldr\Teams::instance()->get_team_members( [
    'p' => $member_id,
    'posts_per_page' => 1,
] );
$office_id = get_field( 'office', $member_id );
$member_office = Ldr\Offices::instance()->get_offices( [
    'p' => $office_id,
    'posts_per_page' => 1,
] );
$member_profile_image = get_the_post_thumbnail_url( $member_id );
$member_profile_image_id = get_post_thumbnail_id( $member_id );
$member_profile_image_alt = get_post_meta( $member_profile_image_id, '_wp_attachment_image_alt', true );
$member_profile_image_size = get_field( 'member_card_profile_image_size' );
$member_profile_image_shape = get_field( 'member_card_profile_image_shape' );
$member_phone_number = get_field( 'phone_number', $member_id );
$member_email_address = get_field( 'email_address', $member_id );
$member_role = get_field( 'role', $member_id );
$member_link = get_permalink( $member_id );
$layout = get_field( 'member_card_layout' );
$force_right = get_field( 'member_card_force_right_alignment' );
$hide_profile_image = get_field( 'member_card_hide_profile_image' );
$hide_name = get_field( 'member_card_hide_name' );
$hide_contact_details = get_field( 'member_card_hide_contact_details' );
$hide_label = get_field( 'member_card_hide_label' );
$card_label = get_field( 'member_card_label' );
$hide_role = get_field( 'member_card_hide_role' );
$hide_link = get_field( 'member_card_hide_link' );
$hide_office = get_field( 'member_card_hide_office' );
$hide_excerpt = get_field( 'member_card_hide_excerpt' );

if( $member_profile_image_shape === 'circle' ) {
    $profile_image_class .= ' rounded-circle';
}

if( $layout === 'v' ) {
    $className .= ' is-member-card-vertical';
} 

if( $layout === 'h' ) {
    $className .= ' is-member-card-horizontal';
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
    
    <?php if( ! $hide_label && ( $card_label !== '' ) ) : ?>
        <div class="card-header">
            <p class="h6 card-title mt-0 mb-4 pb-2 border border-top-0 border-start-0 border-end-0 fw-bold text-uppercase text-center text-md-start"><?php  echo $card_label; ?></p>
        </div>
    <?php endif; ?>

    <div class="card-content<?php echo $force_right ? ' text-start' : ''; ?>">

        <?php if( ! $hide_profile_image ) : ?>
            <span role="img" aria-label="<?php echo $member_profile_image_alt; ?>"></span>
            <div class="<?php echo $profile_image_class; ?>" style="background-image: url(<?php echo $member_profile_image; ?>); width: <?php echo $member_profile_image_size; ?>px; height: <?php echo $member_profile_image_size; ?>px;">&nbsp;</div>
        <?php endif; ?>

        <div class="profile-content d-flex flex-column justify-content-center">
            
            <?php if( ! $hide_name ) : ?>
                <h5 class="fw-bold mt-0 mb-3"><?php echo $member[0]->post_title; ?></h5>
            <?php endif; ?>

            <?php if( ! $hide_role && ! $hide_office ) : ?>
                <p class="mb-0 fst-italic">
                    <?php if( ! empty( $member_role ) ) : ?>
                        <?php echo $member_role; ?>, <span class="office-location"><?php _e( 'based in', 'ldr' ); ?> <?php echo $member_office[0]->post_title; ?></span>
                    <?php else : ?>
                        <span class="office-location"><?php echo $member_office[0]->post_title; ?></span>
                    <?php endif; ?>
                </p>
            <?php endif; ?>

            <?php if( $hide_role && ! $hide_office ) : ?>
                <p class="mb-0 fst-italic"><span class="office-location"><?php echo $member_office[0]->post_title; ?></span></p>
            <?php endif; ?>

            <?php if( ! $hide_role && $hide_office ) : ?>
                <p class="mb-0 fst-italic">
                <?php if( ! empty( $member_role ) ) : ?>
                    <?php echo $member_role; ?>
                <?php else : ?>
                    &nbsp;
                <?php endif; ?>
                </p>
            <?php endif; ?>

            <?php if( ! $hide_excerpt && ! empty( $member[0]->post_excerpt ) ) : ?>
                <p class="mt-3 mb-0"><?php echo $member[0]->post_excerpt; ?></p>
            <?php endif; ?> 

            <div class="contact-details d-flex flex-column">
                <?php if( ! $hide_contact_details && ( ! empty( $member_phone_number ) || ! empty( $member_email_address ) ) ) : ?>
                    <?php if( ! empty( $member_phone_number ) ) : ?>
                        <a href="tel:<?php echo str_replace( [' ','-','.'], '', $member_phone_number ); ?>" target="_blank" class="self-align-start"><?php _e( 'Tel', 'ldr' ); ?>: <?php echo $member_phone_number; ?></a>
                    <?php endif; ?>
                    <?php if( ! empty( $member_email_address ) ) : ?>
                        <a href="mailto:<?php echo $member_email_address; ?>" target="_blank" class="self-align-start"><?php echo $member_email_address; ?></a>
                    <?php endif; ?>
                    <?php if( empty( $member_phone_number ) || empty( $member_email_address ) ) : ?>
                        <div>&nbsp;</div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <?php if( ! $hide_link ) : ?>
                <a href="<?php echo $member_link; ?>" target="_self" class="bio-link btn btn-ldr-primary btn-sm mt-3"><?php _e( 'Bio', 'ldr' ); ?></a>
            <?php endif; ?>

        </div>

    </div>
</div>