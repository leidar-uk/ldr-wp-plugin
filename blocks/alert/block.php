<?php

namespace Ldr;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Alert_Block {

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

        add_action( 'acf/init', [$this, 'ldr_register_acf_block'] );
        add_action( 'acf/init', [$this, 'ldr_register_field_group'] );  

    }

    /**
     * Alert block based on the Masonry grid
     * @return void
     */
    public function ldr_register_acf_block() {

        if( function_exists( 'acf_register_block_type' ) ) {

            acf_register_block_type( [
                'name'              => 'alert',
                'title'             => __( 'Alert', 'ldr' ),
                'description'       => __( 'An alert block.', 'ldr' ),
                'render_template'   => $this->block_dir . 'template.php',
                'category'          => 'custom',
                'icon'              => file_get_contents( $this->block_dir . 'images/alert.svg' ),
                'keywords'          => ['alert', 'notification'],
                'enqueue_assets'    => function() {
                    wp_enqueue_style( 'alert', $this->block_url . 'style.min.css', [], filemtime( $this->block_dir . 'style.min.css' ) );
                    // wp_enqueue_script( 'alert', $this->block_url . 'script.min.js', [], filemtime( $this->block_dir . 'script.min.js' ), true );
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
                'key' => 'group_alert_settings',
                'title' => __( 'Alert', 'ldr' ),
                'fields' => $fields,
                'location' => [
                    [
                        [
                            'param' => 'block',
                            'operator' => '==',
                            'value' => 'acf/alert'
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

    // Alert message
    protected function _acf_field_alert_message() {

        return [
            'key' => 'field_alert_message',
            'label' => __( 'Message', 'ldr' ),
            'name' => 'alert_message',
            'type' => 'textarea',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit', 'ldr' ),
            'placeholder' => '',
            'maxlength' => '',
            'rows' => 5,
            'new_lines' => '',
            'readonly' => 0,
            'disabled' => 0,
        ];

    }

    // Alert type
    protected function _acf_field_alert_type() {

        return [
            'key' => 'field_alert_type',
            'label' => __( 'Type', 'ldr' ),
            'name' => 'alert_type',
            'type' => 'button_group',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => 'info',
            'return_value' => 'value',
            'layout' => 'horizontal',
            'allow_null' => 0,
            'ui' => 1,
            'choices' => [
                'success' => __( 'Success', 'ldr' ),
                'danger' => __( 'Danger', 'ldr' ),
                'warning' => __( 'Warning', 'ldr' ),
                'info' => __( 'Info', 'ldr' ),
            ],
        ];

    }

    // Headline thickness
    protected function _acf_field_alert_has_icon() {

        return [
            'key' => 'field_alert_has_icon',
            'label' => '',
            'name' => 'alert_has_icon',
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
            'message' => __( 'Has icon', 'ldr' ),
            'ui' => 1,
        ];

    }

}
