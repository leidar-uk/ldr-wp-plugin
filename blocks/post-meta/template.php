<?php
/**
 * Block template: Post Meta
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

$id = 'ldr-post-meta-' . $block['id'];
$block_name = pathinfo( __DIR__, PATHINFO_FILENAME );
$block_url = plugin_dir_url( __FILE__ );
$block_dir = plugin_dir_path( __FILE__ );
$className = "ldr-block ldr-$block_name $block_name";

global $post;
$date = date( 'j F, Y', strtotime( $post->post_date ) );
$author_meta = get_user_meta( $post->post_author, '', true );
$author_name = $author_meta['first_name'][0] . ' ' . $author_meta['last_name'][0];
$category_ids = wp_get_post_categories( (int) $post->ID );
$categories = ! empty( $category_ids ) ? array_map( function( $n ) { return get_category( (int) $n )->name; }, $category_ids ) : null;

$manual_selection = get_field( 'post_meta_manual_selection' );
$hide_author = get_field( 'post_meta_hide_author' );
$hide_category = get_field( 'post_meta_hide_category' );
$selected_date = get_field( 'post_meta_select_date' );
$author_id = get_field( 'post_meta_select_author' );
$has_multiple_authors = is_array( $author_id );
$author_args = ! $has_multiple_authors ? [
    'p' => $author_id,
    'posts_per_page' => 1,
] : [
    'post__in' => $author_id,
    'posts_per_page' => count( $author_id ),
    'orderby' => 'post__in',
];
$author = Ldr\Teams::instance()->get_team_members( $author_args );

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
    <div class="d-flex flex-row justify-content-start align-items-center">
        <?php if( empty( $manual_selection ) ) : ?>

            <?php if( empty( $hide_author ) ) : ?>
                <span><?php echo sprintf( '%s %s', __( 'by', 'ldr' ), $author_name ); ?> &nbsp;/&nbsp;</span>
            <?php endif; ?>

                <?php echo $date; ?>

            <?php if( empty( $hide_category ) && ! empty( $categories ) ) : ?>
                <span><?php echo sprintf( '%s', implode( ',', $categories ) ); ?> &nbsp;/&nbsp;</span>
            <?php endif; ?>

        <?php else : ?>

            <?php if( ! empty( $author_id ) ) : ?>
                <?php if( ! $has_multiple_authors ) : ?>
                    <span><?php echo sprintf( '%s %s', __( 'by', 'ldr' ), $author[0]->post_title ); ?> &nbsp;/&nbsp;</span>
                <?php else : ?>
                    <span><?php echo sprintf( '%s %s', __( 'by', 'ldr' ), implode( __( ' and ', 'ldr' ), array_map( function( $n ) { return $n->post_title; }, $author ) ) ); ?> &nbsp;/&nbsp;</span>
                <?php endif; ?>
            <?php endif; ?>

            <?php echo ! empty( $selected_date ) ? $selected_date : $date; ?>

            <?php if( empty( $hide_category ) && ! empty( $categories ) ) : ?>
                <span><?php echo sprintf( '&nbsp;/&nbsp;%s', implode( ', ', $categories ) ); ?></span>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</div>