<?php

namespace Ldr;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Card_Block {

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
                'name'              => 'card',
                'title'             => __( 'Card', 'ldr' ),
                'description'       => __( 'A card block.', 'ldr' ),
                'render_template'   => $this->block_dir . 'template.php',
                'category'          => 'custom',
                'icon'              => file_get_contents( $this->block_dir . 'images/card.svg' ),
                'keywords'          => ['card'],
                'enqueue_assets'    => function() {
                    wp_enqueue_style( 'card', $this->block_url . 'style.min.css', [], filemtime( $this->block_dir . 'style.min.css' ) );
                    // wp_enqueue_script( 'card', $this->block_url . 'script.min.js', [], filemtime( $this->block_dir . 'script.min.js' ), true );
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
                'key' => 'group_card_settings',
                'title' => __( 'Card', 'ldr' ),
                'fields' => $fields,
                'location' => [
                    [
                        [
                            'param' => 'block',
                            'operator' => '==',
                            'value' => 'acf/card'
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
    protected function _acf_field_card_min_height() {

        return [
            'key' => 'field_card_min_height',
            'label' => __( 'Minimal height', 'ldr' ),
            'name' => 'card_min_height',
            'type' => 'number',
            'instructions' => __( '', 'ldr' ),
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

    // Remove border
    protected function _acf_field_card_remove_border() {

        return [
            'key' => 'field_card_remove_border',
            'label' => '',
            'name' => 'card_remove_border',
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
            'message' => __( 'Remove border', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Show image
    protected function _acf_field_card_hide_image() {

        return [
            'key' => 'field_card_hide_image',
            'label' => '',
            'name' => 'card_hide_image',
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
            'message' => __( 'Hide image', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Card image
    protected function _acf_field_card_image() {

        return [
            'key' => 'field_card_image',
            'label' => __( 'Card image', 'ldr' ),
            'name' => 'card_image',
            'type' => 'image',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_card_hide_image',
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
    protected function _acf_field_card_image_overlay_color() {

        return [
            'key' => 'field_card_image_overlay_color',
            'label' => __( 'Overlay color', 'ldr' ),
            'name' => 'card_image_overlay_color',
            'type' => 'color_picker',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_card_hide_image',
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
            'default_value' => 'rgba(0,0,0,0.5)',
            'enable_opacity' => 1,
            'return_format' => 'string',
            'ui' => 1,
        ];

    }

    // Hide title
    protected function _acf_field_card_hide_title() {

        return [
            'key' => 'field_card_hide_title',
            'label' => '',
            'name' => 'card_hide_title',
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
            'message' => __( 'Hide title', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Title text
    protected function _acf_field_card_title_text() {

        return [
            'key' => 'field_card_title_text',
            'label' => __( 'Title text', 'ldr' ),
            'name' => 'card_title_text',
            'type' => 'text',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_card_hide_title',
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
            'default_value' => __( 'Your title goes here...', 'ldr' ),
            'placeholder'   => '',
        ];

    }

    // Hide body text
    protected function _acf_field_card_hide_body_text() {

        return [
            'key' => 'field_card_hide_body_text',
            'label' => '',
            'name' => 'card_hide_body_text',
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
            'message' => __( 'Hide text', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Title text
    protected function _acf_field_card_body_text() {

        return [
            'key' => 'field_card_body_text',
            'label' => __( 'Body text', 'ldr' ),
            'name' => 'card_body_text',
            'type' => 'textarea',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_card_hide_body_text',
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
            'default_value' => __( 'Your text goes here...', 'ldr' ),
            'rows' => 5,
            'maxlength' => '',
            'placeholder' => '',
            'new_lines' => '',
            'readonly' => 0,
            'disabled' => 0,
        ];

    }

    // Hide button
    protected function _acf_field_card_hide_button() {

        return [
            'key' => 'field_card_hide_button',
            'label' => '',
            'name' => 'card_hide_button',
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
            'message' => __( 'Hide button', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Button link
    protected function _acf_field_card_link() {

        return [
            'key' => 'field_card_link',
            'label' => __( 'URL', 'ldr' ),
            'name' => 'card_link',
            'type' => 'link',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_card_hide_button',
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
            'return_value' => 'array',
        ];

    }

    // Content alignment
    protected function _acf_field_content_alignment() {

        return [
            'key' => 'field_content_alignment',
            'label' => __( 'Content alignment', 'ldr' ),
            'name' => 'content_alignment',
            'type' => 'button_group',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_card_hide_title',
                        'operator' => '==',
                        'value' => 0
                    ]
                ],
                [
                    [
                        'field' => 'field_card_hide_body_text',
                        'operator' => '==',
                        'value' => 0
                    ]
                ],    
                [
                    [
                        'field' => 'field_card_hide_button',
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
            'default_value' => 'left',
            'return_format' => 'value',
            'allow_null' => 0,
            'layout' => 'horizontal',
            'choices' => [
                'start' => '<i class="bi bi-text-left"></i>',
                'center' => '<i class="bi bi-text-center"></i>',
                'end' => '<i class="bi bi-text-right"></i>',
            ]
        ];

    }

}
