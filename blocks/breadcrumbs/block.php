<?php

namespace Ldr;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Breadcrumbs_Block {

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
     * Breadcrumbs block based on the Masonry grid
     * @return void
     */
    public function ldr_register_acf_block() {

        if( function_exists( 'acf_register_block_type' ) ) {

            acf_register_block_type( [
                'name'              => 'breadcrumbs',
                'title'             => __( 'Breadcrumbs', 'ldr' ),
                'description'       => __( 'A page breadcrumbs.', 'ldr' ),
                'render_template'   => $this->block_dir . 'template.php',
                'category'          => 'custom',
                'icon'              => file_get_contents( $this->block_dir . 'images/breadcrumbs.svg' ),
                'keywords'          => ['breadcrumbs', 'path', 'links'],
                'enqueue_assets'    => function() {
                    wp_enqueue_style( 'breadcrumbs', $this->block_url . 'style.min.css', [], filemtime( $this->block_dir . 'style.min.css' ) );
                    // wp_enqueue_script( 'breadcrumbs', $this->block_url . 'script.min.js', [], filemtime( $this->block_dir . 'script.min.js' ), true );
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
                'key' => 'group_breadcrumbs_settings',
                'title' => __( 'Breadcrumbs', 'ldr' ),
                'fields' => $fields,
                'location' => [
                    [
                        [
                            'param' => 'block',
                            'operator' => '==',
                            'value' => 'acf/breadcrumbs'
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

    // Hide breadcrumbs title
    protected function _acf_field_breadcrumbs_hide_title() {

        return [
            'key' => 'field_breadcrumbs_hide_title',
            'label' => '',
            'name' => 'breadcrumbs_hide_title',
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => 'ldr-acf--no-label',
                'id' => '',
            ],
            'default_value' => 1,
            'message' => __( 'Hide title', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Breadcrumgs title
    protected function _acf_field_breadcrumbs_title() {

        return [
            'key' => 'field_breadcrumbs_title',
            'label' => __( 'Breadcrumbs title', 'ldr' ),
            'name' => 'breadcrumbs_title',
            'type' => 'text',
            'instructions' => __( 'Text before the breadcrumbs.', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_breadcrumbs_hide_title',
                        'operator' => '==',
                        'value' => 0
                    ]
                ]
            ],
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => __( 'You are here', 'ldr' ),
            'placeholder' => '',
            'maxlength' => '',
            'readonly' => 0,
            'disabled' => 0,
        ];

    }

    // Home link text
    protected function _acf_field_breadcrumbs_home_link_text() {

        return [
            'key' => 'field_breadcrumbs_home_link_text',
            'label' => __( 'Home link text', 'ldr' ),
            'name' => 'breadcrumbs_home_link_text',
            'type' => 'text',
            'instructions' => __( '', 'ldr' ),
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => __( 'Home', 'ldr' ),
            'placeholder' => '',
            'maxlength' => '',
            'readonly' => 0,
            'disabled' => 0,
        ];

    }

    // Alert type
    protected function _acf_field_breadcrumbs_delimiter() {

        return [
            'key' => 'field_breadcrumbs_delimiter',
            'label' => __( 'Delimiter', 'ldr' ),
            'name' => 'breadcrumbs_delimiter',
            'type' => 'button_group',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => 'slash',
            'return_value' => 'value',
            'layout' => 'horizontal',
            'allow_null' => 0,
            'ui' => 1,
            'choices' => [
                'slash' => '/',
                'chevron' => '<i class="bi bi-chevron-right"></i>',
            ],
        ];

    }

}
