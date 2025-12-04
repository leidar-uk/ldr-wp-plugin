<?php
/**
 * Blocks
 * 
 * Loads and organises custom blocks
 * 
 * @package Leidar_Plugin
 * @since 1.0.0
 */

namespace Ldr;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Blocks {

    /**
     * @var Blocks|null
     */
    private static $instance = null;

    /**
     * Singleton
     */
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct() {
        
        add_action( 'init', [ $this, 'load_custom_acf_blocks' ] );

        add_filter( 'block_categories_all', [$this, 'extend_block_categories'], 10, 2);
        add_filter( 'allowed_block_types_all', [ $this, 'organise_allowed_blocks' ], 10, 2 );
    }

    /**
     * Load all custom block classes from plugin/blocks/*
     */
    public function load_custom_acf_blocks() {

        $blocks_dir = plugin_dir_path( dirname( __FILE__ ) ) . 'blocks';

        if ( ! is_dir( $blocks_dir ) ) {
            return;
        }

        $dirs = array_filter( scandir( $blocks_dir ), function( $n ) {
            return substr( $n, 0, 1 ) !== '.';
        } );
            
        foreach ( $dirs as $dir ) {
            $file = "{$blocks_dir}/{$dir}/block.php";
            $class_name = __NAMESPACE__ . '\\' . str_replace( ' ', '_', ucwords( str_replace( '-', ' ', $dir ) ) ) . '_Block';
            
            if ( file_exists( $file ) ) {
                require_once $file;
                
                if ( class_exists( $class_name ) && method_exists( $class_name, 'instance' ) ) {
                    $class_name::instance();
                }
            }
        }

    }

    /**
     * Add custom block categories.
     * @param array $block_categories
     * @param \WP_Block_Editor_Context $block_editor_context
     * @return array
     */
    public function extend_block_categories( $block_categories, $block_editor_context ) {

        array_unshift( $block_categories, [
            'slug'	=> 'custom',
            'title' => __( 'Custom blocks', 'ldr' ),
            'icon' => null,
        ] );

        return $block_categories;

    }

    /**
     * Returns an array of custom built blocks
     * @return array
     */
    public function get_custom_blocks() {

        $blocks_dir = plugin_dir_path( dirname( __FILE__ ) ) . 'blocks';
        $dirs = is_dir( $blocks_dir )
            ? array_filter( scandir( $blocks_dir ), function( $n ) { return strpos( $n, '.' ) === false; } )
            : [];

        $custom_blocks = array_map(
            function( $n ) { return 'acf/' . $n; },
            $dirs
        );

        return $custom_blocks;

    }

    /**
     * Filter callback used on 'allowed_block_types_all'
     * @param bool|string[] $allowed_block_types
     * @param \WP_Block_Editor_Context $block_editor_context
     * @return array
     */
    public function organise_allowed_blocks( $allowed_block_types, $block_editor_context ) {

        $custom_blocks = $this->get_custom_blocks();

        $core_blocks = [
            'core/columns',
            'core/buttons',
            'core/group',
            'core/row',
            'core/spacer',
            'core/paragraph',
            'core/heading',
            'core/list',
            'core/list-item',
            'core/table',
            'core/verse',
            'core/quote',
            'core/image',
            'core/audio',
            'core/video',
            'core/file',
            'core/html',
            'core/shortcode',
            'core/post-title',
            'core/post-excerpt',
            'core/post-featured-image',
            'core/embed',
            'core/separator',
        ];

        $allowed_blocks = array_merge( $custom_blocks, $core_blocks );

        if ( isset( $block_editor_context->post ) && $block_editor_context->post ) {
            $post_type = $block_editor_context->post->post_type;

            // Allow all blocks in the Site Editor.
            if ( in_array( $post_type, ['wp_template', 'wp_template_part'], true ) ) {
                return true;
            }

            // Restrict blocks for regular content.
            if ( in_array( $post_type, array( 'post', 'page' ), true ) ) {
                return $allowed_blocks;
            }
        }

        // Fallback: detect the Site Editor screen directly if available.
        if ( function_exists( 'get_current_screen' ) ) {
            $screen = get_current_screen();
            if ( $screen && 'site-editor' === $screen->id ) {
                return $allowed_blocks;
            }
        }

        // Fallback: some contexts expose a name like 'core/edit-site'.
        if ( isset( $block_editor_context->name ) && 'core/edit-site' === $block_editor_context->name ) {
            return $allowed_blocks;
        }

        return $custom_blocks;

    }
}