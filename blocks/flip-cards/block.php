<?php

namespace Ldr;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Flip_Cards_Block {

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
     * A repeatable grid of cards that flip on click to reveal a list on the back.
     * @return void
     */
    public function ldr_register_acf_block() {

        if( function_exists( 'acf_register_block_type' ) ) {

            acf_register_block_type( [
                'name'              => 'flip-cards',
                'title'             => __( 'Flip Cards', 'ldr' ),
                'description'       => __( 'A grid of cards (max. 2 columns, 1 on mobile) that flip on click to reveal a list on the back.', 'ldr' ),
                'render_template'   => $this->block_dir . 'template.php',
                'category'          => 'custom',
                'icon'              => file_get_contents( $this->block_dir . 'images/flip-cards.svg' ),
                'keywords'          => ['flip', 'card', 'grid', 'practices'],
                'enqueue_assets'    => function() {
                    wp_enqueue_style( 'flip-cards', $this->block_url . 'style.min.css', [], filemtime( $this->block_dir . 'style.min.css' ) );
                    wp_enqueue_script( 'flip-cards', $this->block_url . 'script.min.js', [], filemtime( $this->block_dir . 'script.min.js' ), true );
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
                'key' => 'group_flip_cards_settings',
                'title' => __( 'Flip Cards', 'ldr' ),
                'fields' => $fields,
                'location' => [
                    [
                        [
                            'param' => 'block',
                            'operator' => '==',
                            'value' => 'acf/flip-cards'
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

    // Section title
    protected function _acf_field_flip_cards_title() {

        return [
            'key' => 'field_flip_cards_title',
            'label' => __( 'Section title', 'ldr' ),
            'name' => 'flip_cards_title',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => '',
            'placeholder' => __( 'e.g. Practices', 'ldr' ),
        ];

    }

    // Section subtitle
    protected function _acf_field_flip_cards_subtitle() {

        return [
            'key' => 'field_flip_cards_subtitle',
            'label' => __( 'Section subtitle', 'ldr' ),
            'name' => 'flip_cards_subtitle',
            'type' => 'textarea',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => '',
            'rows' => 2,
            'placeholder' => '',
            'new_lines' => '',
        ];

    }

    // Card minimum height
    protected function _acf_field_flip_cards_card_min_height() {

        return [
            'key' => 'field_flip_cards_card_min_height',
            'label' => __( 'Card minimum height', 'ldr' ),
            'name' => 'flip_cards_card_min_height',
            'type' => 'range',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => 320,
            'min' => 200,
            'max' => 600,
            'step' => 10,
            'prepend' => '',
            'append' => 'px',
        ];

    }

    // Cards
    protected function _acf_field_flip_cards_items() {

        return [
            'key' => 'field_flip_cards_items',
            'label' => __( 'Cards', 'ldr' ),
            'name' => 'flip_cards_items',
            'type' => 'repeater',
            'instructions' => __( 'The grid always lays out in a maximum of two columns, collapsing to a single column on mobile.', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'sub_fields' => [
                [
                    'key' => 'field_flip_card_front_heading',
                    'label' => __( 'Front: heading', 'ldr' ),
                    'name' => 'flip_card_front_heading',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 1,
                    'conditional_logic' => 0,
                    'wrapper' => [
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ],
                    'default_value' => '',
                    'placeholder' => '',
                ],
                [
                    'key' => 'field_flip_card_front_description',
                    'label' => __( 'Front: description', 'ldr' ),
                    'name' => 'flip_card_front_description',
                    'type' => 'textarea',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => [
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ],
                    'default_value' => '',
                    'rows' => 3,
                    'placeholder' => '',
                    'new_lines' => '',
                ],
                [
                    'key' => 'field_flip_card_front_image',
                    'label' => __( 'Front: background image', 'ldr' ),
                    'name' => 'flip_card_front_image',
                    'type' => 'image',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => [
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ],
                    'return_format' => 'array',
                    'preview_size' => 'medium',
                    'library' => 'all',
                    'min_width' => 0,
                    'min_height' => 0,
                    'min_size' => 0,
                    'max_width' => 0,
                    'max_height' => 0,
                    'max_size' => 0,
                    'mime_types' => '',
                ],
                [
                    'key' => 'field_flip_card_front_text_color',
                    'label' => __( 'Front: text colour', 'ldr' ),
                    'name' => 'flip_card_front_text_color',
                    'type' => 'button_group',
                    'instructions' => __( 'Choose based on the contrast against the background image.', 'ldr' ),
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => [
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ],
                    'default_value' => 'dark',
                    'return_format' => 'value',
                    'allow_null' => 0,
                    'layout' => 'horizontal',
                    'choices' => [
                        'dark' => __( 'Dark', 'ldr' ),
                        'light' => __( 'Light', 'ldr' ),
                    ],
                ],
                [
                    'key' => 'field_flip_card_front_button_text',
                    'label' => __( 'Front: button text', 'ldr' ),
                    'name' => 'flip_card_front_button_text',
                    'type' => 'text',
                    'instructions' => __( 'Leave the button link below empty to hide the button.', 'ldr' ),
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => [
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ],
                    'default_value' => __( 'Find out more', 'ldr' ),
                    'placeholder' => '',
                ],
                [
                    'key' => 'field_flip_card_front_button_link',
                    'label' => __( 'Front: button link', 'ldr' ),
                    'name' => 'flip_card_front_button_link',
                    'type' => 'link',
                    'instructions' => __( 'The practice page this card should link to.', 'ldr' ),
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => [
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ],
                    'return_value' => 'array',
                ],
                [
                    'key' => 'field_flip_card_back_heading',
                    'label' => __( 'Back: heading', 'ldr' ),
                    'name' => 'flip_card_back_heading',
                    'type' => 'text',
                    'instructions' => __( 'The front heading is always shown above this as a small label.', 'ldr' ),
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => [
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ],
                    'default_value' => '',
                    'placeholder' => '',
                ],
                [
                    'key' => 'field_flip_card_back_text_color',
                    'label' => __( 'Back: text colour', 'ldr' ),
                    'name' => 'flip_card_back_text_color',
                    'type' => 'button_group',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => [
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ],
                    'default_value' => 'dark',
                    'return_format' => 'value',
                    'allow_null' => 0,
                    'layout' => 'horizontal',
                    'choices' => [
                        'dark' => __( 'Dark', 'ldr' ),
                        'light' => __( 'Light', 'ldr' ),
                    ],
                ],
                [
                    'key' => 'field_flip_card_back_items',
                    'label' => __( 'Back: list items', 'ldr' ),
                    'name' => 'flip_card_back_items',
                    'type' => 'repeater',
                    'instructions' => __( "Leave an item's link empty to show it as plain text instead of a link.", 'ldr' ),
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => [
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ],
                    'sub_fields' => [
                        [
                            'key' => 'field_flip_card_back_item_text',
                            'label' => __( 'Text', 'ldr' ),
                            'name' => 'flip_card_back_item_text',
                            'type' => 'text',
                            'instructions' => '',
                            'required' => 1,
                            'conditional_logic' => 0,
                            'wrapper' => [
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ],
                            'default_value' => '',
                            'placeholder' => '',
                        ],
                        [
                            'key' => 'field_flip_card_back_item_link',
                            'label' => __( 'Link (optional)', 'ldr' ),
                            'name' => 'flip_card_back_item_link',
                            'type' => 'link',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => 0,
                            'wrapper' => [
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ],
                            'return_value' => 'array',
                        ],
                    ],
                    'collapsed' => 'field_flip_card_back_item_text',
                    'min_rows' => 0,
                    'layout' => 'block',
                    'button_label' => __( 'New list item', 'ldr' ),
                    'pagination' => false,
                    'rows_per_page' => 0,
                ],
            ],
            'collapsed' => 'field_flip_card_front_heading',
            'min_rows' => 0,
            'layout' => 'block',
            'button_label' => __( 'New card', 'ldr' ),
            'pagination' => false,
            'rows_per_page' => 0,
        ];

    }

}
