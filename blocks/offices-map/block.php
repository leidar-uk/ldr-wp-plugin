<?php

namespace Ldr;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Offices_Map_Block {

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
                'name'              => 'offices-map',
                'title'             => __( 'Offices Map', 'ldr' ),
                'description'       => __( 'A map component with the Lediar offices.', 'ldr' ),
                'render_template'   => $this->block_dir . 'template.php',
                'category'          => 'custom',
                'icon'              => file_get_contents( $this->block_dir . 'images/offices-map.svg' ),
                'keywords'          => ['map', 'offices', 'location'],
                'enqueue_assets'    => function() {
                    wp_enqueue_style( 'offices-map', $this->block_url . 'style.min.css', [], filemtime( $this->block_dir . 'style.min.css' ) );
                    wp_enqueue_script( 'offices-map', $this->block_url . 'script.min.js', [], filemtime( $this->block_dir . 'script.min.js' ), true );
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
                'key' => 'group_offices_map_settings',
                'title' => __( 'Offices Map', 'ldr' ),
                'fields' => $fields,
                'location' => [
                    [
                        [
                            'param' => 'block',
                            'operator' => '==',
                            'value' => 'acf/offices-map'
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
            'key' => 'field_offices_map_height',
            'label' => __( 'Offices map height', 'ldr' ),
            'name' => 'offices_map_height',
            'type' => 'range',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => 44,
            'min'           => 0,
            'max'           => 100,
            'step'          => 1,
            'prepend'       => '',
            'append'        => 'vh',
        ];

    }

    // Lattitude
    protected function _acf_field_map_center_lattitude() {

        return [
            'key' => 'field_offices_map_center_lat',
            'label' => __( 'Map Center (lat)', 'ldr' ),
            'name' => 'offices_map_center_lat',
            'type' => 'number',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => 42.508530,
            'min'           => '',
            'max'           => '',
            'step'          => '',
            'placeholder'   => '',
            'prepend'       => '',
            'append'        => '',
        ];

    }

    // Longitude
    protected function _acf_field_map_center_longitude() {

        return [
            'key' => 'field_offices_map_center_long',
            'label' => __( 'Map Center (long)', 'ldr' ),
            'name' => 'offices_map_center_long',
            'type' => 'number',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => 30.076132,
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
            'key' => 'field_offices_map_zoom',
            'label' => __( 'Map zoom', 'ldr' ),
            'name' => 'offices_map_zoom',
            'type' => 'range',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => 3,
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
            'key' => 'field_offices_map_marker_image',
            'label' => __( 'Marker image', 'ldr' ),
            'name' => 'offices_map_marker_image',
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
            'default_value' => '/',
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
            'key' => 'field_offices_map_marker_size',
            'label' => __( 'Marker size', 'ldr' ),
            'name' => 'offices_map_marker_size',
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

    // Hide search
    protected function _acf_field_map_marker_show_popup() {

        return [
            'key' => 'field_map_marker_show_popup',
            'label' => '',
            'name' => 'map_marker_show_popup',
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
            'message' => __( 'Show pop-up', 'ldr' ),
            'ui' => 1,
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
