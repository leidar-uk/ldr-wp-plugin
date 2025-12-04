<?php

namespace Ldr;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Office_Slider_Block {

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
                'name'              => 'office-slider',
                'title'             => __( 'Office Slider', 'ldr' ),
                'description'       => __( 'Animated office slider block.', 'ldr' ),
                'render_template'   => $this->block_dir . 'template.php',
                'category'          => 'custom',
                'icon'              => file_get_contents( $this->block_dir . 'images/office-slider.svg' ),
                'keywords'          => ['office', 'slider'],
                'enqueue_assets'    => function() {
                    wp_enqueue_style( 'office-slider', $this->block_url . 'style.min.css', [], filemtime( $this->block_dir . 'style.min.css' ) );
                    wp_enqueue_script( 'office-slider', $this->block_url . 'script.min.js', [], filemtime( $this->block_dir . 'script.min.js' ), true );
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
                'key' => 'group_office_slider_settings',
                'title' => __( 'Office Slider', 'ldr' ),
                'fields' => $fields,
                'location' => [
                    [
                        [
                            'param' => 'block',
                            'operator' => '==',
                            'value' => 'acf/office-slider'
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

    // Exclude members
    protected function _acf_field_office_slider_exclude_offices() {

        $choices = Offices::instance()->prepare_office_radio_choices();

        return [
            'key' => 'field_office_slider_exclude_offices',
            'label' => __( 'Exclude offices', 'ldr' ),
            'name' => 'office_slider_exclude_offices',
            'type' => 'select',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => '',
            'multiple' => 1,
            'choices' => $choices,
            'allow_null' => 1,
            'ui' => 1,
            'ajax' => 1,
            'placeholder' => __( 'Type name or select', 'ldr' ),
        ];

    }

    // Hide arrows
    protected function _acf_field_office_slider_hide_arrows() {

        return [
            'key' => 'field_office_slider_hide_arrows',
            'label' => '',
            'name' => 'office_slider_hide_arrows',
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
            'message' => __( 'Hide arrows', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Hide bullets
    protected function _acf_field_office_slider_hide_bullets() {

        return [
            'key' => 'field_office_slider_hide_bullets',
            'label' => '',
            'name' => 'office_slider_hide_bullets',
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
            'message' => __( 'Hide bullets', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Slider type
    protected function _acf_field_office_slider_type() {

        return [
            'key' => 'field_office_slider_type',
            'label' => __( 'Type', 'ldr' ),
            'name' => 'office_slider_type',
            'type' => 'button_group',
            'instructions' => __( 'Choose whether card is vertical or horizonal.', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => 'carousel',
            'return_format' => 'value',
            'allow_null' => 0,
            'layout' => 'horizontal',
            'choices' => [
                'carousel' => __( 'Carousel', 'ldr' ),
                'slider' => __( 'Slider', 'ldr' ),
            ]
        ];

    }

    // Slider start at
    public function _acf_field_office_slider_start_at() {

        return [
            'key' => 'field_office_slider_start_at',
            'label' => __( 'Start at', 'ldr' ),
            'name' => 'office_slider_start_at',
            'type' => 'number',
            'instructions' => __( 'Start at specific slide number.', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => 0,
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'min' => 0,
            'step' => 1,
            'readonly' => 0,
            'disabled' => 0,
        ];

    }

    // Slider per view
    public function _acf_field_office_slider_per_view() {

        return [
            'key' => 'field_office_slider_per_view',
            'label' => __( 'Per view', 'ldr' ),
            'name' => 'office_slider_per_view',
            'type' => 'number',
            'instructions' => __( 'A number of visible slides.', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => 1,
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'min' => 1,
            'step' => 1,
            'readonly' => 0,
            'disabled' => 0,
        ];

    }

    // Slider autoplay
    public function _acf_field_office_slider_autoplay() {

        return [
            'key' => 'field_office_slider_autoplay',
            'label' => __( 'Autoplay', 'ldr' ),
            'name' => 'office_slider_autoplay',
            'type' => 'number',
            'instructions' => __( 'Change slides after a specified interval.', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => 4000,
            'placeholder' => '',
            'prepend' => '',
            'append' => 'ms',
            'min' => 1000,
            'step' => 100,
            'readonly' => 0,
            'disabled' => 0,
        ];

    }

    // Slider pause on hover
    protected function _acf_field_office_slider_hoverpause() {

        return [
            'key' => 'field_office_slider_hoverpause',
            'label' => '',
            'name' => 'office_slider_hoverpause',
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
            'message' => __( 'Stop on mouseover', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Slider animation duration
    public function _acf_field_office_slider_animation_duration() {

        return [
            'key' => 'field_office_slider_animation_duration',
            'label' => __( 'Animation duration', 'ldr' ),
            'name' => 'office_slider_animation_duration',
            'type' => 'number',
            'instructions' => __( 'Duration of the animation.', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => 1000,
            'placeholder' => '',
            'prepend' => '',
            'append' => 'ms',
            'min' => 100,
            'step' => 100,
            'readonly' => 0,
            'disabled' => 0,
        ];

    }

    // Hide map
    protected function _acf_field_office_slider_hide_map() {

        return [
            'key' => 'field_office_slider_hide_map',
            'label' => '',
            'name' => 'office_slider_hide_map',
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
            'message' => __( 'Hide map', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Cover image height
    protected function _acf_field_office_slider_cover_image_height() {

        return [
            'key' => 'field_office_slider_cover_image_height',
            'label' => __( 'Cover image height', 'ldr' ),
            'name' => 'office_slider_cover_image_height',
            'type' => 'range',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_office_slider_hide_map',
                        'operator' => '==',
                        'value' => 1
                    ]
                ]
            ],
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => 240,
            'min'           => 0,
            'max'           => 480,
            'step'          => 1,
            'prepend'       => '',
            'append'        => 'px',
        ];

    }

    // Overlay color
    protected function _acf_field_office_slider_cover_image_overlay_color() {

        return [
            'key' => 'field_office_slider_cover_image_overlay_color',
            'label' => __( 'Cover image overlay color', 'ldr' ),
            'name' => 'office_slider_cover_image_overlay_color',
            'type' => 'color_picker',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_office_slider_hide_map',
                        'operator' => '==',
                        'value' => 1
                    ],
                    [
                        'field' => 'field_office_slider_cover_image_height',
                        'operator' => '>',
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

    // Map height
    protected function _acf_field_office_slider_map_height() {

        return [
            'key' => 'field_office_slider_map_height',
            'label' => __( 'Map height', 'ldr' ),
            'name' => 'office_slider_map_height',
            'type' => 'range',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_office_slider_hide_map',
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
            'default_value' => 240,
            'min'           => 50,
            'max'           => 480,
            'step'          => 1,
            'prepend'       => '',
            'append'        => 'px',
        ];

    }

    // Map zoom
    protected function _acf_field_office_slider_map_zoom() {

        return [
            'key' => 'field_office_slider_map_zoom',
            'label' => __( 'Map zoom', 'ldr' ),
            'name' => 'office_slider_map_zoom',
            'type' => 'range',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_office_slider_hide_map',
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
            'default_value' => 20,
            'min'           => 1,
            'max'           => 20,
            'step'          => 1,
            'prepend'       => '',
            'append'        => '',
        ];

    }

    // Scroll wheel zoom
    protected function _acf_field_office_slider_map_scroll_wheel_zoom() {

        return [
            'key' => 'field_office_slider_map_scroll_wheel_zoom',
            'label' => '',
            'name' => 'office_slider_map_scroll_wheel_zoom',
            'type' => 'true_false',
            'instructions' => __( 'Resize map up or down using mouse wheel.', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_office_slider_hide_map',
                        'operator' => '==',
                        'value' => 0
                    ]
                ]
            ],
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

    // Marker image
    protected function _acf_field_office_slider_map_marker_image() {

        return [
            'key' => 'field_office_slider_map_marker_image',
            'label' => __( 'Marker image', 'ldr' ),
            'name' => 'office_slider_map_marker_image',
            'type' => 'image',
            'instructions' => __( 'Recommended maximum size: 64x64px.', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_office_slider_hide_map',
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
    protected function _acf_field_office_slider_map_marker_image_size() {

        return [
            'key' => 'field_office_slider_map_marker_image_size',
            'label' => __( 'Marker size', 'ldr' ),
            'name' => 'office_slider_map_marker_image_size',
            'type' => 'range',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_office_slider_hide_map',
                        'operator' => '==',
                        'value' => 0
                    ],
                    [
                        'field' => 'field_office_slider_map_marker_image',
                        'operator' => '!=empty',
                    ]
                ]
            ],
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => 32,
            'min'           => 32,
            'max'           => 128,
            'step'          => 1,
            'prepend'       => '',
            'append'        => 'px',
        ];

    }

    // Layout
    protected function _acf_field_office_slider_card_layout() {

        return [
            'key' => 'field_office_slider_card_layout',
            'label' => __( 'Card layout', 'ldr' ),
            'name' => 'office_slider_card_layout',
            'type' => 'button_group',
            'instructions' => __( 'Choose whether card is vertical or horizonal.', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => 'column',
            'return_format' => 'value',
            'allow_null' => 0,
            'layout' => 'horizontal',
            'choices' => [
                'row' => '<i class="bi bi-box-arrow-right"></i>',
                'column' => '<i class="bi bi-box-arrow-down"></i>',
            ]
        ];

    }

    // Hide card border
    protected function _acf_field_office_slider_hide_border() {

        return [
            'key' => 'field_office_slider_hide_border',
            'label' => '',
            'name' => 'office_slider_hide_border',
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
            'message' => __( 'Hide card border', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Hide title
    protected function _acf_field_office_slider_hide_title() {

        return [
            'key' => 'field_office_slider_hide_title',
            'label' => '',
            'name' => 'office_slider_hide_title',
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

    // Custom title
    protected function _acf_field_office_slider_custom_title() {

        return [
            'key' => 'field_office_slider_custom_title',
            'label' => __( 'Custom title', 'ldr' ),
            'name' => 'office_slider_custom_title',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_office_slider_hide_title',
                        'operator' => '!=',
                        'value' => 1
                    ]
                ]
            ],
            'wrapper' => [
                'width' => '',
                'class' => 'ldr-acf--no-label',
                'id' => '',
            ],
            'default_value' => '',
            'maxlength'     => '',
            'placeholder'   => '',
            'prepend'       => '',
            'append'        => '',
        ];

    }

    // Hide description
    protected function _acf_field_office_slider_hide_description() {

        return [
            'key' => 'field_office_slider_hide_description',
            'label' => '',
            'name' => 'office_slider_hide_description',
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
            'message' => __( 'Hide description', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Hide link
    protected function _acf_field_office_slider_hide_link() {

        return [
            'key' => 'field_office_slider_hide_link',
            'label' => '',
            'name' => 'office_slider_hide_link',
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
            'message' => __( 'Hide page link', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Link text
    protected function _acf_field_office_slider_link_text() {

        return [
            'key' => 'field_office_slider_link_text',
            'label' => __( 'Link text', 'ldr' ),
            'name' => 'office_slider_link_text',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_office_slider_hide_link',
                        'operator' => '!=',
                        'value' => 1
                    ]
                ]
            ],
            'wrapper' => [
                'width' => '',
                'class' => 'ldr-acf--no-label',
                'id' => '',
            ],
            'default_value' => __( 'Find out more', 'ldr' ),
            'maxlength'     => '',
            'placeholder'   => '',
            'prepend'       => '',
            'append'        => '',
        ];

    }

    // Hide address
    protected function _acf_field_office_slider_hide_address() {

        return [
            'key' => 'field_office_slider_hide_address',
            'label' => '',
            'name' => 'office_slider_hide_address',
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
            'message' => __( 'Hide address', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Hide address link
    protected function _acf_field_office_slider_hide_address_link() {

        return [
            'key' => 'field_office_slider_hide_address_link',
            'label' => '',
            'name' => 'office_slider_hide_address_link',
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_office_slider_hide_address',
                        'operator' => '!=',
                        'value' => 1
                    ]
                ]
            ],
            'wrapper' => [
                'width' => '',
                'class' => 'ldr-acf--no-label',
                'id' => '',
            ],
            'default_value' => 0,
            'message' => __( 'Hide address link', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Hide coordinates
    protected function _acf_field_office_slider_hide_coords() {

        return [
            'key' => 'field_office_slider_hide_coords',
            'label' => '',
            'name' => 'office_slider_hide_coords',
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_office_slider_hide_address',
                        'operator' => '!=',
                        'value' => 1
                    ]
                ]
            ],
            'wrapper' => [
                'width' => '',
                'class' => 'ldr-acf--no-label',
                'id' => '',
            ],
            'default_value' => 0,
            'message' => __( 'Hide coordinates', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Hide contact details
    protected function _acf_field_office_slider_hide_contact_details() {

        return [
            'key' => 'field_office_slider_hide_contact_details',
            'label' => '',
            'name' => 'office_slider_hide_contact_details',
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
            'message' => __( 'Hide contact details', 'ldr' ),
            'ui' => 1,
        ];

    }


    // Hide contact label
    protected function _acf_field_office_hide_card_contact_label() {

        return [
            'key' => 'field_office_slider_hide_contact_label',
            'label' => '',
            'name' => 'office_slider_hide_contact_label',
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_office_slider_hide_contact_details',
                        'operator' => '!=',
                        'value' => 1
                    ]
                ]
            ],
            'wrapper' => [
                'width' => '',
                'class' => 'ldr-acf--no-label',
                'id' => '',
            ],
            'default_value' => 0,
            'message' => __( 'Hide contact label', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Custom contact label
    protected function _acf_field_office_slider_custom_contact_label() {

        return [
            'key' => 'field_office_slider_custom_contact_label',
            'label' => __( 'Custom contact label', 'ldr' ),
            'name' => 'office_slider_custom_contact_label',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_office_slider_hide_contact_details',
                        'operator' => '!=',
                        'value' => 1
                    ],
                    [
                        'field' => 'field_office_slider_hide_contact_label',
                        'operator' => '!=',
                        'value' => 1
                    ]
                ]
            ],
            'wrapper' => [
                'width' => '',
                'class' => 'ldr-acf--no-label',
                'id' => '',
            ],
            'default_value' => '',
            'maxlength'     => '',
            'placeholder'   => '',
            'prepend'       => '',
            'append'        => '',
        ];

    }

    // Hide contact avatar
    protected function _acf_field_office_slider_hide_contact_avatar() {

        return [
            'key' => 'field_office_slider_hide_contact_avatar',
            'label' => '',
            'name' => 'office_slider_hide_contact_avatar',
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_office_slider_hide_contact_details',
                        'operator' => '!=',
                        'value' => 1
                    ]
                ]
            ],
            'wrapper' => [
                'width' => '',
                'class' => 'ldr-acf--no-label',
                'id' => '',
            ],
            'default_value' => 0,
            'message' => __( 'Hide avatar', 'ldr' ),
            'ui' => 1,
        ];

    }

}
