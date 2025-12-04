<?php
/**
 * Block template: Accordion
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

$id = 'ldr-accordion-' . $block['id'];
$block_name = pathinfo( __DIR__, PATHINFO_FILENAME );
$block_url = plugin_dir_url( __FILE__ );
$block_dir = plugin_dir_path( __FILE__ );
$className = "ldr-block ldr-$block_name";

$tab_groups = get_field( 'accordion_tab_groups' );
$always_open = get_field( 'accordion_always_open' );

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
    <?php if( ! empty( $tab_groups ) ) : ?>
        <div class="accordion border border-1" id="acc-<?php echo $block['id'];?>">
            <?php foreach( $tab_groups as $tab_id => $tab ) : ?>
                <div class="accordion-item">
                    <div class="accordion-header">
                        <button class="accordion-button fw-bold<?php echo empty( $tab['accordion_tab_is_open'] ) ? ' collapsed' : ''; ?><?php echo ! empty( $tab['accordion_tab_subheader'] ) ? ' has-subheader' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#acc-tab-<?php echo $tab_id; ?>" aria-expanded="<?php echo ! empty( $tab['accordion_tab_is_open'] ) ? 'true' : 'false'; ?>" aria-controls="acc-tab-<?php echo $tab_id; ?>">
                            <?php echo $tab['accordion_tab_header']; ?>
                            <?php if( ! empty( $tab['accordion_tab_subheader'] ) ) : ?>
                                <div class="fw-normal fst-italic">
                                    <?php echo $tab['accordion_tab_subheader']; ?>
                                </div>
                            <?php endif; ?>
                        </button>
                    </div>
                    <div id="acc-tab-<?php echo $tab_id; ?>" class="accordion-collapse collapse<?php echo ! empty( $tab['accordion_tab_is_open'] ) ? ' show' : ''; ?>" aria-labelledby="acc-tab-<?php echo $tab_id; ?>" <?php echo empty( $always_open ) ? 'data-bs-parent="#acc-' . $block['id'] . '"' : ''; ?>>
                        <div class="accordion-body">
                            <?php echo $tab['accordion_tab_message']; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>