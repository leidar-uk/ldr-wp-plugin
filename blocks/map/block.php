<?php

namespace Ldr;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Map_Block {

    /**
     * A class instance reference
     * @var object $instance
     */
    private static $instance = false;

    /**
     * Block URL (public URL for enqueueing assets)
     * @var string $block_url
     */
    protected $block_url;

    /**
     * Block path (filesystem path for includes/filename)
     * @var string $block_dir
     */
    protected $block_dir;

    /**
     * A class instance
     * @return object
     */
    public static function instance() {

        if( ! self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;

    }
    
    /**
     * Class constructor
     * @return void
     */
    function __construct() {

        $this->block_url = plugin_dir_url( __FILE__ );
        $this->block_dir = plugin_dir_path( __FILE__ );

        if ( function_exists( 'acf_register_block_type' ) ) {
            $this->ldr_register_acf_block();
            $this->ldr_register_field_group();
        } else {
            add_action( 'acf/init', [$this, 'ldr_register_acf_block'] );
            add_action( 'acf/init', [$this, 'ldr_register_field_group'] );
        }

    }

    /**
     * Gallery block based on the Masonry grid
     * @return void
     */
    public function ldr_register_acf_block() {

        if( function_exists( 'acf_register_block_type' ) ) {

            acf_register_block_type( [
                'name'              => 'map',
                'title'             => __( 'Map', 'ldr' ),
                'description'       => __( 'A map component.', 'ldr' ),
                'render_template'   => $this->block_dir . 'template.php',
                'category'          => 'custom',
                'icon'              => file_get_contents( $this->block_dir . 'images/map.svg' ),
                'keywords'          => ['map', 'location', 'leaflet'],
                'enqueue_assets'    => function() {
                    wp_enqueue_style( 'map', $this->block_url . 'style.min.css', [], filemtime( $this->block_dir . 'style.min.css' ) );
                    wp_enqueue_script( 'map', $this->block_url . 'script.min.js', [], filemtime( $this->block_dir . 'script.min.js' ), true );
                }
            ] );

        }

    }

    /**
     * Registers ACF field group
     * @return void
     */
    public function ldr_register_field_group() {

        if( function_exists( 'acf_add_local_field_group' ) ) {

            $field_methods = array_values( array_filter( get_class_methods( __CLASS__ ), function( $n ) { return strpos( $n, '_acf_field_' ) === 0; } ) );
            $fields = array_map( function( $n ) { return call_user_func( [$this, $n] ); }, $field_methods );

            acf_add_local_field_group( [
                'key' => 'group_map_settings',
                'title' => __( 'Map', 'ldr' ),
                'fields' => $fields,
                'location' => [
                    [
                        [
                            'param' => 'block',
                            'operator' => '==',
                            'value' => 'acf/map'
                        ]
                    ]
                ],
                'menu_order' => 0,
                'position' => 'side',
                'style' => 'default',
                'label_placement' => 'top',
                'instruction_placement' => 'field',
                'hide_on_screen' => [],
            ] );

        }

    }

    /**
     * ACF group fields
     * Each method returns an associative array with the field options
     */

    // Height
    protected function _acf_field_map_height() {

        return [
            'key' => 'field_map_height',
            'label' => __( 'Map height', 'ldr' ),
            'name' => 'map_height',
            'type' => 'range',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => 33,
            'min'           => 0,
            'max'           => 100,
            'step'          => 1,
            'prepend'       => '',
            'append'        => 'vh',
        ];

    }

    // Lattitude
    protected function _acf_field_map_lattitude() {

        return [
            'key' => 'field_map_lat',
            'label' => __( 'Lattitude', 'ldr' ),
            'name' => 'map_lat',
            'type' => 'number',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => 51.508530,
            'min'           => '',
            'max'           => '',
            'step'          => '',
            'placeholder'   => '',
            'prepend'       => '',
            'append'        => '',
        ];

    }

    // Longitude
    protected function _acf_field_map_longitude() {

        return [
            'key' => 'field_map_long',
            'label' => __( 'Longitude', 'ldr' ),
            'name' => 'map_long',
            'type' => 'number',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => -0.076132,
            'min'           => '',
            'max'           => '',
            'step'          => '',
            'placeholder'   => '',
            'prepend'       => '',
            'append'        => '',
        ];

    }

    // Zoom
    protected function _acf_field_map_zoom() {

        return [
            'key' => 'field_map_zoom',
            'label' => __( 'Map zoom', 'ldr' ),
            'name' => 'map_zoom',
            'type' => 'range',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => 16,
            'min'           => 0,
            'max'           => 20,
            'step'          => 1,
            'prepend'       => '',
            'append'        => '',
        ];

    }

    // Marker image
    protected function _acf_field_map_marker_image() {

        return [
            'key' => 'field_map_marker_image',
            'label' => __( 'Marker image', 'ldr' ),
            'name' => 'map_marker_image',
            'type' => 'image',
            'instructions' => __( 'Recommended maximum size: 64x64px.', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'return_format' => 'url',
            'preview_size'  => 'medium',
            'library'       => 'all',
            'min_width'     => 0,
            'min_height'    => 0,
            'min_size'      => 0,
            'max_width'     => 0,
            'max_height'    => 0,
            'max_size'      => 0,
            'mime_types'    => ''
        ];

    }

    // Marker size
    protected function _acf_field_map_marker_size() {

        return [
            'key' => 'field_map_marker_size',
            'label' => __( 'Marker size', 'ldr' ),
            'name' => 'map_marker_size',
            'type' => 'range',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => 32,
            'min'           => 0,
            'max'           => 64,
            'step'          => 1,
            'prepend'       => '',
            'append'        => 'px',
        ];

    }

    // Pup-up text
    protected function _acf_field_map_marker_popup_text() {

        return [
            'key' => 'field_map_marker_popup_text',
            'label' => __( 'Pop-up text', 'ldr' ),
            'name' => 'map_marker_popup_text',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit', 'ldr' ),
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
            'readonly' => 0,
            'disabled' => 0,
        ];

    }

    // Scrollwheel zoom
    protected function _acf_field_map_scroll_wheel_zoom() {

        return [
            'key' => 'field_map_scroll_wheel_zoom',
            'label' => '',
            'name' => 'map_scroll_wheel_zoom',
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => 'ldr-acf--no-label',
                'id' => '',
            ],
            'default_value' => 0,
            'message' => __( 'Scroll wheel zoom', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Grayscale map
    protected function _acf_field_map_grayscale() {

        return [
            'key' => 'field_map_grayscale',
            'label' => '',
            'name' => 'map_grayscale',
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => 'ldr-acf--no-label',
                'id' => '',
            ],
            'default_value' => 0,
            'message' => __( 'Grayscale map', 'ldr' ),
            'ui' => 1,
        ];

    }

}
