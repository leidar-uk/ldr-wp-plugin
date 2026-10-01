<?php
/**
 * Block template: Flip Cards
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

$id = 'ldr-flip-cards-' . $block['id'];
$block_name = pathinfo( __DIR__, PATHINFO_FILENAME );
$block_url = plugin_dir_url( __FILE__ );
$block_dir = plugin_dir_path( __FILE__ );
$className = "ldr-block ldr-$block_name";

$title = get_field( 'flip_cards_title' );
$subtitle = get_field( 'flip_cards_subtitle' );
$card_min_height = get_field( 'flip_cards_card_min_height' );
$cards = get_field( 'flip_cards_items' );

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
<section id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $className ); ?>">
    <div class="container">

        <?php if( $title || $subtitle ) : ?>
            <div class="row justify-content-lg-center px-0">
                <div class="col-12 col-lg-10">
                    <?php if( $title ) : ?>
                        <h2 class="h2 fw-bold lh-base mt-0"><?php echo esc_html( $title ); ?></h2>
                    <?php endif; ?>
                    <?php if( $subtitle ) : ?>
                        <p class="lead lh-base mt-0 mb-5"><?php echo esc_html( $subtitle ); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if( empty( $cards ) ) : ?>

            <?php if( $is_preview ) : ?>
                <div class="alert alert-warning mb-0"><?php esc_html_e( 'Add one or more cards to this block.', 'ldr' ); ?></div>
            <?php endif; ?>

        <?php else : ?>

            <div class="ldr-flip-cards__grid">
                <?php foreach( $cards as $index => $card ) :

                    $front_heading = $card['flip_card_front_heading'] ?? '';
                    $front_description = $card['flip_card_front_description'] ?? '';
                    $front_image = $card['flip_card_front_image'] ?? '';
                    $front_image_url = ! empty( $front_image['sizes']['large'] ) ? $front_image['sizes']['large'] : ( $front_image['url'] ?? '' );
                    $front_color_mode = ! empty( $card['flip_card_front_text_color'] ) ? $card['flip_card_front_text_color'] : 'dark';
                    $front_mode = 'text-' . $front_color_mode;
                    $front_button_text = ! empty( $card['flip_card_front_button_text'] ) ? $card['flip_card_front_button_text'] : __( 'Find out more', 'ldr' );
                    $front_button_link = $card['flip_card_front_button_link'] ?? '';

                    $back_heading = $card['flip_card_back_heading'] ?? '';
                    $back_mode = 'text-' . ( ! empty( $card['flip_card_back_text_color'] ) ? $card['flip_card_back_text_color'] : 'dark' );
                    $back_items = $card['flip_card_back_items'] ?? [];

                ?>
                    <div
                        class="ldr-flip-card"
                        data-flip-card="<?php echo esc_attr( $index ); ?>"
                        role="button"
                        tabindex="0"
                        aria-expanded="false"
                        style="min-height: <?php echo esc_attr( $card_min_height ? $card_min_height : 320 ); ?>px;"
                    >
                        <div class="ldr-flip-card__inner">
                            <div
                                class="ldr-flip-card__side ldr-flip-card__side--front<?php echo $front_image_url ? ' ldr-flip-card__side--with-image' : ''; ?>"
                                style="background-image: <?php echo $front_image_url ? sprintf( 'url(%1$s)', esc_url( $front_image_url ) ) : 'none'; ?>;"
                            >
                                <div class="ldr-flip-card__overlay">
                                    <div>
                                        <?php if( $front_heading ) : ?>
                                            <h4 class="ldr-flip-card__heading h1 fw-bold mt-0 <?php echo esc_attr( $front_mode ); ?>">
                                                <?php echo esc_html( $front_heading ); ?>
                                            </h4>
                                        <?php endif; ?>

                                        <?php if( $front_description ) : ?>
                                            <p class="ldr-flip-card__subheading mt-0 <?php echo esc_attr( $front_mode ); ?>">
                                                <?php echo esc_html( $front_description ); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>

                                    <div class="ldr-flip-card__footer mt-auto d-flex justify-content-between align-items-end">
                                        <small class="ldr-flip-card__turnover <?php echo esc_attr( $front_mode ); ?>"><i class="bi bi-arrow-repeat"></i> <?php esc_html_e( 'Turn over', 'ldr' ); ?></small>

                                        <?php if( ! empty( $front_button_link['url'] ) ) : ?>
                                            <a
                                                href="<?php echo esc_url( $front_button_link['url'] ); ?>"
                                                class="btn ldr-flip-card__cta ldr-flip-card__cta--<?php echo esc_attr( $front_color_mode ); ?>"
                                                data-no-flip
                                                <?php if( ! empty( $front_button_link['target'] ) ) : ?>
                                                    target="<?php echo esc_attr( $front_button_link['target'] ); ?>" rel="noopener noreferrer"
                                                <?php endif; ?>
                                            ><?php echo esc_html( $front_button_text ); ?></a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="ldr-flip-card__side ldr-flip-card__side--back">
                                <div class="ldr-flip-card__overlay">
                                    <div>
                                        <?php if( $front_heading ) : ?>
                                            <small class="d-block mb-2 fw-bold <?php echo esc_attr( $back_mode ); ?>"><?php echo esc_html( $front_heading ); ?></small>
                                        <?php endif; ?>
                                        <?php if( $back_heading ) : ?>
                                            <h4 class="ldr-flip-card__heading h4 fw-bold mt-0 <?php echo esc_attr( $back_mode ); ?>">
                                                <?php echo esc_html( $back_heading ); ?>
                                            </h4>
                                        <?php endif; ?>

                                        <?php if( ! empty( $back_items ) ) : ?>
                                            <ul class="ldr-flip-card__list <?php echo esc_attr( $back_mode ); ?>">
                                                <?php foreach( $back_items as $item ) :
                                                    $item_text = $item['flip_card_back_item_text'] ?? '';
                                                    $item_link = $item['flip_card_back_item_link'] ?? '';
                                                    $item_url = $item_link['url'] ?? '';
                                                    $item_target = $item_link['target'] ?? '';

                                                    if( ! $item_text ) {
                                                        continue;
                                                    }
                                                ?>
                                                    <li class="ldr-flip-card__list-item">
                                                        <?php if( $item_url ) : ?>
                                                            <a
                                                                href="<?php echo esc_url( $item_url ); ?>"
                                                                class="ldr-flip-card__list-link"
                                                                data-no-flip
                                                                <?php if( '_blank' === $item_target ) : ?>
                                                                    target="_blank" rel="noopener noreferrer"
                                                                <?php endif; ?>
                                                            ><?php echo esc_html( $item_text ); ?></a>
                                                        <?php else : ?>
                                                            <?php echo esc_html( $item_text ); ?>
                                                        <?php endif; ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                    <small class="ldr-flip-card__turnover mt-auto <?php echo esc_attr( $back_mode ); ?>"><i class="bi bi-arrow-repeat"></i> <?php esc_html_e( 'Turn over', 'ldr' ); ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

    </div>
</section>
