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

        // ACF's per-block 'enqueue_assets' style only reliably reaches the
        // outer wp-admin document, not the iframed block-editor canvas the
        // block preview actually renders in. Inline every block's own
        // compiled CSS straight into the editor's iframe styles instead,
        // so block previews look right in the editor for ALL custom blocks.
        add_filter( 'block_editor_settings_all', [ $this, 'inject_custom_block_editor_styles' ] );
    }

    /**
     * Prepares theme data for use in front-end
     * @param array $data
     * @return array
     */
    public function prepare_theme_data_object( $data = [] ) {

        $defaults = [
            'homeUrl' => home_url(),
            'themeUri' => get_template_directory_uri(),
            'wpAjax' => admin_url( 'admin-ajax.php' ),
        ];

        return array_merge( $defaults, $data );

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
     * Inlines every custom block's compiled style.min.css into the block
     * editor settings, so it lands inside the editor-canvas iframe (where
     * block previews render) rather than only the outer admin document.
     * @param array $editor_settings
     * @return array
     */
    public function inject_custom_block_editor_styles( $editor_settings ) {

        $blocks_dir = plugin_dir_path( dirname( __FILE__ ) ) . 'blocks';

        if ( ! is_dir( $blocks_dir ) ) {
            return $editor_settings;
        }

        if ( ! isset( $editor_settings['styles'] ) || ! is_array( $editor_settings['styles'] ) ) {
            $editor_settings['styles'] = [];
        }

        $dirs = array_filter( scandir( $blocks_dir ), function( $n ) {
            return substr( $n, 0, 1 ) !== '.';
        } );

        foreach ( $dirs as $dir ) {

            $css_file = "{$blocks_dir}/{$dir}/style.min.css";

            if ( ! is_readable( $css_file ) ) {
                continue;
            }

            $css = file_get_contents( $css_file );

            if ( $css ) {
                $editor_settings['styles'][] = [
                    'css'      => $css,
                    'baseURL'  => plugin_dir_url( dirname( __FILE__ ) ) . "blocks/{$dir}/style.min.css",
                ];
            }

        }

        return $editor_settings;

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
            'wp-pdf-reader/reader',
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