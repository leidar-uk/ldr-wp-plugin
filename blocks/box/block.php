<?php

namespace Ldr;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Box_Block {

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
     * Box block based on the Masonry grid
     * @return void
     */
    public function ldr_register_acf_block() {

        if( function_exists( 'acf_register_block_type' ) ) {

            acf_register_block_type( [
                'name'              => 'box',
                'title'             => __( 'Box', 'ldr' ),
                'description'       => __( 'A box component.', 'ldr' ),
                'render_template'   => $this->block_dir . 'template.php',
                'category'          => 'custom',
                'icon'              => file_get_contents( $this->block_dir . '/images/box.svg' ),
                'keywords'          => ['box'],
                'supports'          => [
                    'align'         => ['wide', 'full', 'center'],
                    'jsx' 			=> true,
                    'html'          => false,
                ],
                'enqueue_assets'    => function() {
                    wp_enqueue_style( 'box', $this->block_url . 'style.min.css', [], filemtime( $this->block_dir . 'style.min.css' ) );
                    // wp_enqueue_script( 'box', $this->block_url . 'script.min.js', [], filemtime( $this->block_dir . 'script.min.js' ), true );
                },
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
                'key' => 'group_box_settings',
                'title' => __( 'Box', 'ldr' ),
                'fields' => $fields,
                'location' => [
                    [
                        [
                            'param' => 'block',
                            'operator' => '==',
                            'value' => 'acf/box'
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

    // Minimal height
    protected function _acf_field_box_min_height() {

        return [
            'key' => 'field_box_min_height',
            'label' => __( 'Minimal height', 'ldr' ),
            'name' => 'box_min_height',
            'type' => 'number',
            'instructions' => __( 'The maximum height for mobile devices is automatically set to 400px.', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => 200,
            'step'          => 1,
            'prepend'       => '',
            'append'        => 'px',
        ];

    }

    // Ignore maximum height
    protected function _acf_field_box_ignore_max_height() {

        return [
            'key' => 'field_box_ignore_max_height',
            'label' => '',
            'name' => 'box_ignore_max_height',
            'type' => 'true_false',
            'instructions' => __( 'Ignores the maximum height on mobile devices.' ),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => 'ldr-acf--no-label',
                'id' => '',
            ],
            'default_value' => 0,
            'message' => __( 'Ignore max height', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Vertical content alignment
    protected function _acf_field_vertical_content_alignment() {

        return [
            'key' => 'field_box_vertical_content_alignment',
            'label' => __( 'Vertical content alignment', 'ldr' ),
            'name' => 'box_vertical_content_alignment',
            'type' => 'button_group',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => 'justify-content-center',
            'return_format' => 'value',
            'allow_null' => 0,
            'layout' => 'horizontal',
            'choices' => [
                'justify-content-start' => '<i class="bi bi-align-top"></i>',
                'justify-content-center' => '<i class="bi bi-align-center"></i>',
                'justify-content-end' => '<i class="bi bi-align-bottom"></i>',
            ]
        ];

    }

    // Fluid container
    protected function _acf_field_box_fluid_container() {

        return [
            'key' => 'field_box_fluid_container',
            'label' => '',
            'name' => 'box_fluid_container',
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
            'message' => __( 'Fluid container', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Container padding
    protected function _acf_field_box_container_padding() {

        return [
            'key' => 'field_box_container_padding',
            'label' => __( 'Padding', 'ldr' ),
            'name' => 'box_container_padding',
            'type' => 'button_group',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => 'p-0',
            'return_format' => 'value',
            'allow_null' => 0,
            'layout' => 'horizontal',
            'choices' => [
                'p-0' => '0',
                'px-4 py-3' => '1',
                'px-4 py-4' => '2',
                'px-4 py-5' => '3',
            ]
        ];

    }

    // Background color
    protected function _acf_field_box_background_color() {

        return [
            'key' => 'field_box_background_color',
            'label' => __( 'Background color', 'ldr' ),
            'name' => 'box_background_color',
            'type' => 'color_picker',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => '#ced4da',
            'enable_opacity' => 0,
            'return_format' => 'string',
            'ui' => 1,
        ];

    }

    // Background image
    protected function _acf_field_box_background_image() {

        return [
            'key' => 'field_box_background_image',
            'label' => __( 'Background image', 'ldr' ),
            'name' => 'box_background_image',
            'type' => 'image',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'return_format' => 'array',
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

    // Overlay color
    protected function _acf_field_box_overlay_color() {

        return [
            'key' => 'field_box_overlay_color',
            'label' => __( 'Overlay color', 'ldr' ),
            'name' => 'box_overlay_color',
            'type' => 'color_picker',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_box_background_image',
                        'operator' => '!=empty',
                        // 'value' => 1
                    ]
                ]
            ],
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => '#000000',
            'enable_opacity' => 1,
            'return_format' => 'string',
            'ui' => 1,
        ];

    }

    // Repeat background
    protected function _acf_field_box_repeat_bg() {

        return [
            'key' => 'field_box_repeat_bg',
            'label' => '',
            'name' => 'box_repeat_bg',
            'type' => 'true_false',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_box_background_image',
                        'operator' => '!=empty',
                        // 'value' => 1
                    ]
                ]
            ],
            'wrapper' => [
                'width' => '',
                'class' => 'ldr-acf--no-label',
                'id' => '',
            ],
            'default_value' => 0,
            'message' => __( 'Repeat background', 'ldr' ),
            'ui' => 1,
        ];

    }

}
