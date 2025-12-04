<?php
/**
 * Block template: Share
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

$id = 'ldr-share-' . $block['id'];
$block_name = pathinfo( __DIR__, PATHINFO_FILENAME );
$block_url = plugin_dir_url( __FILE__ );
$block_dir = plugin_dir_path( __FILE__ );
$className = "ldr-block ldr-$block_name $block_name";

global $post;
$page_url = get_the_permalink( $post );
$page_title = rawurlencode( $post->post_title );
$website_title = get_bloginfo( 'name' );
$subject_line = sprintf( '[%s %s] %s', __( 'New article from', 'ldr' ), $website_title, $page_title );
$twitter_handle = get_field( 'twitter_handle', 'option' );
$post_tags = get_the_tags( $post->ID );
$hashtags = ! empty( $post_tags ) ? implode( ',', array_map( function( $n ) { return $n->name; }, $post_tags ) ) : '';

$prefix = get_field( 'share_prefix' );
$channels = get_field( 'share_channels' );
$alignment = get_field( 'share_alignment' );
$share_data = [
    'linkedin' => [
        'icon' => 'linkedin',
        'channel_name' => 'LinkedIn',
        'url' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . $page_url,
    ],
    'twitter' => [
        'icon' => 'twitter',
        'channel_name' => 'Twitter',
        'url' => 'https://twitter.com/intent/tweet?url=' . $page_url . '&text=' . $page_title . '&via=' . $twitter_handle . '&hashtags=' . $hashtags,
    ],
    'facebook' => [
        'icon' => 'facebook',
        'channel_name' => 'Facebook',
        'url' => 'https://www.facebook.com/sharer.php?u=' . $page_url,
    ],
    'email' => [
        'icon' => 'envelope',
        'channel_name' => __( 'Email', 'ldr' ),
        'url' => 'mailto:?subject=' . $subject_line . '&body=' . $page_url,
    ],
    'whatsapp' => [
        'icon' => 'whatsapp',
        'channel_name' => 'Whatsapp',
        'url' => 'whatsapp://send?text=' . sprintf( '%s. %s: %s', $subject_line, __( 'To read the article visit', 'ldr' ), $page_url ),
    ],
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
<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $className ); ?>">
    <?php if( ! empty( $channels ) ) : ?>
        <div class="d-flex flex-column flex-lg-row justify-content-start <?php echo ! empty( $alignment ) ? $alignment : 'justify-content-xl-end';?> align-items-center align-items-lg-center">
            <?php if( ! empty( $prefix ) ) : ?>
                <span class="me-lg-3"><?php echo $prefix; ?><span class="d-none d-md-inline">:&nbsp;</span></span>
            <?php endif; ?>

            <div>
                <?php foreach( $channels as $channel ) : ?>
                    <?php if( 'whatsapp' === $channel ) : ?>
                        <a href="<?php echo $share_data[$channel]['url']; ?>" title="<?php echo $share_data[$channel]['channel_name']; ?>" target="_blank" class="btn btn-lg text-primary text-decoration-none shadow-none border-0 d-lg-none" aria-label="<?php echo sprintf( '%s %s', esc_attr__( 'Share on', 'ldr' ), $share_data[$channel]['channel_name'] ); ?>">
                            <i class="bi bi-<?php echo $share_data[$channel]['icon']; ?>"></i>
                        </a>
                    <?php else : ?>
                        <a href="<?php echo $share_data[$channel]['url']; ?>" title="<?php echo $share_data[$channel]['channel_name']; ?>" target="_blank" class="btn btn-lg text-primary text-decoration-none shadow-none border-0" aria-label="<?php echo sprintf( '%s %s', esc_attr__( 'Share on', 'ldr' ), $share_data[$channel]['channel_name'] ); ?>">
                            <i class="bi bi-<?php echo $share_data[$channel]['icon']; ?>"></i>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else : ?>
        <div class="alert alert-info" role="alert">
            <?php _e( 'Channel list is empty. Select channels from the list to reveal the buttons.', 'ldr' ); ?>
        </div>
    <?php endif; ?>
</div>