<?php

namespace Ldr;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Accordion_Block {

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
     * Accordion block registration
     * @return void
     */
    public function ldr_register_acf_block() {

        if( function_exists( 'acf_register_block_type' ) ) {

            acf_register_block_type( [
                'name'              => 'accordion',
                'title'             => __( 'Accordion', 'ldr' ),
                'description'       => __( 'An accordion block.', 'ldr' ),
                'render_template'   => $this->block_dir . 'template.php',
                'category'          => 'custom',
                'icon'              => file_get_contents( $this->block_dir . 'images/accordion.svg' ),
                'keywords'          => ['accordion', 'notification'],
                'enqueue_assets'    => function() {
                    wp_enqueue_style( 'accordion',  $this->block_url . 'style.min.css', [], filemtime( $this->block_dir . 'style.min.css' ) );
                    // wp_enqueue_script( 'accordion', $this->block_url . 'script.min.js', [], filemtime( $this->block_dir . 'script.min.js' ), true );
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
                'key' => 'group_accordion_settings',
                'title' => __( 'Accordion', 'ldr' ),
                'fields' => $fields,
                'location' => [
                    [
                        [
                            'param' => 'block',
                            'operator' => '==',
                            'value' => 'acf/accordion'
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

    // Accorrion always open
    protected function _acf_field_accordion_always_open() {

        return [
            'key' => 'field_accordion_always_open',
            'label' => '',
            'name' => 'accordion_always_open',
            'type' => 'true_false',
            'instructions' => __( 'Make accordion items stay open when another item is opened.', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => 'ldr-acf--no-label',
                'id' => '',
            ],
            'default_value' => 0,
            'message' => __( 'Is always open', 'ldr' ),
            'ui' => 1,
        ];

    }
    
    // Accordion tab group
    protected function _acf_field_accordion_tab_groups() {

        return [
            'key' => 'field_accordion_tab_groups',
            'label' => __( 'Accordion tab groups', 'ldr' ),
            'name' => 'accordion_tab_groups',
            'type' => 'repeater',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'sub_fields' => [
                [
                    'key' => 'field_accordion_tab_header',
                    'label' => __( 'Tab header', 'ldr' ),
                    'name' => 'accordion_tab_header',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => [
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ],
                    'default_value' => __( 'Lorem ipsum', 'ldr' ),
                    'placeholder' => '',
                    'readonly' => 0,
                    'disabled' => 0,
                ],
                [
                    'key' => 'field_accordion_tab_subheader',
                    'label' => __( 'Tab subheader', 'ldr' ),
                    'name' => 'accordion_tab_subheader',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => [
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ],
                    'default_value' => __( '', 'ldr' ),
                    'placeholder' => '',
                    'readonly' => 0,
                    'disabled' => 0,
                ],
                [
                    'key' => 'field_accordion_tab_message',
                    'label' => __( 'Body message', 'ldr' ),
                    'name' => 'accordion_tab_message',
                    'type' => 'wysiwyg',
                    'instructions' => '',
                    'required' => 1,
                    'conditional_logic' => 0,
                    'wrapper' => [
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ],
                    'default_value' => __( '', 'ldr' ),
                    'placeholder' => __( 'Your tab content goes here...', 'ldr' ),
                    'tabs' => 'visual',
                    'toolbar' => 'basic',
                    'media_upload' => 0,
                    'delay' => 0,
                ],
                [
                    'key' => 'field_accordion_tab_is_open',
                    'label' => '',
                    'name' => 'accordion_tab_is_open',
                    'type' => 'true_false',
                    'instructions' => __( 'Keep this tab open.', 'ldr' ),
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => [
                        'width' => '',
                        'class' => 'ldr-acf--no-label',
                        'id' => '',
                    ],
                    'default_value' => 0,
                    'message' => __( 'Is open', 'ldr' ),
                    'ui' => 1,
                ]
            ],
            'collapsed' => false,
            'min_rows' => 0,
            'layout' => 'block',
            'button_label' => __( 'New tab', 'ldr' ),
            'pagination' => false,
            'rows_per_page' => 0
        ];

    }

}
