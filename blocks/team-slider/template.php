<?php
/**
 * Block template: Team Slider
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

$id = 'ldr-team-slider-' . $block['id'];
$block_name = pathinfo( __DIR__, PATHINFO_FILENAME );
$block_url = plugin_dir_url( __FILE__ );
$block_dir = plugin_dir_path( __FILE__ );
$className = "ldr-block ldr-$block_name clearfix";
$glide_className = 'glide';

$hide_arrows = get_field( 'team_slider_hide_arrows' );
$manual_selection = get_field( 'team_slider_manual_selection' );
$manually_selected_members = get_field( 'team_slider_select_members' );
$hide_bullets = get_field( 'team_slider_hide_bullets' );
$members_filter = get_field( 'team_slider_filter_members' );
$exclude_members = get_field( 'team_slider_exclude_members' );
$meta_query_args = ! empty( $members_filter ) ? [
    'meta_query' => [
        [
            'key' => 'office',
            'value' => (int) $members_filter,
            'compare' => '='
        ],
    ]
] : [];
$exclusion_query_args = ! empty( $exclude_members ) ? [
    'post__not_in' => array_map( function( $n ) { return (int) $n; }, $exclude_members )
] : [];
$manual_selection_query_args = ! empty( $manually_selected_members ) ? [
    'post__in' => array_map( function( $n ) { return (int) $n; }, $manually_selected_members ),
    'orderby' => 'post__in',
] : [];
$team_members = Ldr\Teams::instance()->get_team_members( ! $manual_selection ? array_merge( $meta_query_args, $exclusion_query_args ) : $manual_selection_query_args );
$card_settings = [
    'hide_profile_image' => (int) get_field( 'team_slider_hide_profile_image' ),
    'profile_image_size' => get_field( 'team_slider_profile_image_size' ),
    'image_shape' => get_field( 'team_slider_profile_image_shape' ),
    'hide_contact_details' => get_field( 'team_slider_hide_contact_details' ),
    'hide_role' => (int) get_field( 'team_slider_hide_role' ),
    'hide_office' => (int) get_field( 'team_slider_hide_office' ),
    'hide_excerpt' => (int) get_field( 'team_slider_hide_excerpt' ),
    'hide_link' => (int) get_field( 'team_slider_hide_link' ),
    'layout' => get_field( 'team_slider_card_layout' )
];
$start_at = (int) get_field( 'team_slider_start_at' );
$per_view = (int) get_field( 'team_slider_per_view' );
$slider_data = [
    'type' => get_field( 'team_slider_type' ),
    'startAt' => $start_at > count( $team_members ) ? 0 : $start_at,
    'perView' => $per_view > count( $team_members ) ? 1 : $per_view,
    'autoplay' => (int) get_field( 'team_slider_autoplay' ),
    'hoverpause' => get_field( 'team_slider_hoverpause' ),
    'animationDuration' => (int) get_field( 'team_slider_animation_duration' ),
    'breakpoints' => [
        3000 => [
            'perView' => $per_view
        ],
        1200 => [
            'perView' => 3
        ],
        992 => [
            'perView' => 2
        ],
        768 => [
            'perView' => 1
        ],
    ]
];

if( ! $hide_bullets ) {
    $glide_className .= ' pb-5 mb-5';
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
<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $className ); ?>" data-slider-settings='<?php echo json_encode( $slider_data ); ?>'>
    
    <div class="<?php echo $glide_className; ?>">
        <div class="glide__track" data-glide-el="track">
            <?php if( $team_members ) : ?>
                <ul class="glide__slides">
                    <?php foreach( $team_members as $member ) : ?>
                        <li class="glide__slide">
                            <?php echo Ldr\Teams::instance()->render_team_member_card_block( $member->ID, $card_settings ); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
             <?php endif; ?>
        </div>

        <?php if( ! $hide_bullets && $team_members ) : ?>
            <div class="glide__bullets" data-glide-el="controls[nav]">
                <?php for( $i = 0; $i < count( $team_members ); $i++ ) : ?>
                    <button class="glide__bullet" data-glide-dir='=<?php echo $i; ?>'></button>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

        <?php if( ! $hide_arrows ) : ?>
            <div class="glide__arrows" data-glide-el="controls">
                <button class="glide__arrow glide__arrow--left" data-glide-dir="<?php esc_attr_e( '<' ); ?>" aria-label="<?php esc_attr_e( 'Slide left', 'ldr' ); ?>">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button class="glide__arrow glide__arrow--right" data-glide-dir="<?php esc_attr_e( '>' ); ?>" aria-label="<?php esc_attr_e( 'Slide right', 'ldr' ); ?>">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        <?php endif; ?>
    </div>

</div>