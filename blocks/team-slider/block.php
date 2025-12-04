<?php

namespace Ldr;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Team_Slider_Block {

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
                'name'              => 'team-slider',
                'title'             => __( 'Team Slider', 'ldr' ),
                'description'       => __( 'Animated team slider block.', 'ldr' ),
                'render_template'   => $this->block_dir . 'template.php',
                'category'          => 'custom',
                'icon'              => file_get_contents( $this->block_dir . 'images/team-slider.svg' ),
                'keywords'          => ['team', 'slider'],
                'enqueue_assets'    => function() {
                    wp_enqueue_style( 'team-slider', $this->block_url . 'style.min.css', [], filemtime( $this->block_dir . 'style.min.css' ) );
                    wp_enqueue_script( 'team-slider', $this->block_url . 'script.min.js', [], filemtime( $this->block_dir . 'script.min.js' ), true );
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
                'key' => 'group_team_slider_settings',
                'title' => __( 'Team Slider', 'ldr' ),
                'fields' => $fields,
                'location' => [
                    [
                        [
                            'param' => 'block',
                            'operator' => '==',
                            'value' => 'acf/team-slider'
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

    // Hide arrows
    protected function _acf_field_team_slider_hide_arrows() {

        return [
            'key' => 'field_team_slider_hide_arrows',
            'label' => '',
            'name' => 'team_slider_hide_arrows',
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
    protected function _acf_field_team_slider_hide_bullets() {

        return [
            'key' => 'field_team_slider_hide_bullets',
            'label' => '',
            'name' => 'team_slider_hide_bullets',
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
    protected function _acf_field_team_slider_type() {

        return [
            'key' => 'field_team_slider_type',
            'label' => __( 'Type', 'ldr' ),
            'name' => 'team_slider_type',
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
    public function _acf_field_team_slider_start_at() {

        return [
            'key' => 'field_team_slider_start_at',
            'label' => __( 'Start at', 'ldr' ),
            'name' => 'team_slider_start_at',
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
    public function _acf_field_team_slider_per_view() {

        return [
            'key' => 'field_team_slider_per_view',
            'label' => __( 'Per view', 'ldr' ),
            'name' => 'team_slider_per_view',
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
    public function _acf_field_team_slider_autoplay() {

        return [
            'key' => 'field_team_slider_autoplay',
            'label' => __( 'Autoplay', 'ldr' ),
            'name' => 'team_slider_autoplay',
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
    protected function _acf_field_team_slider_hoverpause() {

        return [
            'key' => 'field_team_slider_hoverpause',
            'label' => '',
            'name' => 'team_slider_hoverpause',
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
    public function _acf_field_team_slider_animation_duration() {

        return [
            'key' => 'field_team_slider_animation_duration',
            'label' => __( 'Animation duration', 'ldr' ),
            'name' => 'team_slider_animation_duration',
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

    // Manual selection
    protected function _acf_field_team_slider_manual_selection() {

        return [
            'key' => 'field_team_slider_manual_selection',
            'label' => '',
            'name' => 'team_slider_manual_selection',
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
            'message' => __( 'Manual selection', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Manually select members
    protected function _acf_field_team_slider_select_members() {

        $choices = Teams::instance()->prepare_team_members_radio_choices();

        return [
            'key' => 'field_team_slider_select_members',
            'label' => __( 'Select members', 'ldr' ),
            'name' => 'team_slider_select_members',
            'type' => 'select',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_team_slider_manual_selection',
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
            'default_value' => '',
            'multiple' => 1,
            'choices' => $choices,
            'allow_null' => 1,
            'ui' => 1,
            'ajax' => 1,
            'placeholder' => __( 'Type name or select', 'ldr' ),
        ];

    }

    // Filter members
    protected function _acf_field_team_slider_filter_members() {

        $choices = Offices::instance()->prepare_office_radio_choices();

        return [
            'key' => 'field_team_slider_filter_members',
            'label' => __( 'Filter members', 'ldr' ),
            'name' => 'team_slider_filter_members',
            'type' => 'select',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_team_slider_manual_selection',
                        'operator' => '!=',
                        'value' => 1
                    ]
                ]
            ],
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => '',
            'multiple' => 0,
            'choices' => $choices,
            'allow_null' => 1,
            'ui' => 1,
            'ajax' => 1,
            'placeholder' => __( 'Type name or select', 'ldr' ),
        ];

    }

    // Exclude members
    protected function _acf_field_team_slider_exclude_members() {

        $choices = Teams::instance()->prepare_team_members_radio_choices();

        return [
            'key' => 'field_team_slider_exclude_members',
            'label' => __( 'Exclude members', 'ldr' ),
            'name' => 'team_slider_exclude_members',
            'type' => 'select',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_team_slider_manual_selection',
                        'operator' => '!=',
                        'value' => 1
                    ]
                ]
            ],
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

    // Hide profile image
    protected function _acf_field_team_slider_hide_profile_image() {

        return [
            'key' => 'field_team_slider_hide_profile_image',
            'label' => '',
            'name' => 'team_slider_hide_profile_image',
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
            'message' => __( 'Hide profile image', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Profile image size
    protected function _acf_field_team_slider_profile_image_size() {

        return [
            'key' => 'field_team_slider_profile_image_size',
            'label' => __( 'Profile image size', 'ldr' ),
            'name' => 'team_slider_profile_image_size',
            'type' => 'range',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_team_slider_hide_profile_image',
                        'operator' => '!=',
                        'value' => 1
                    ]
                ]
            ],
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => 128,
            'min'           => 64,
            'max'           => 480,
            'step'          => 1,
            'prepend'       => '',
            'append'        => 'px',
        ];

    }

    // Profile image shape
    protected function _acf_field_team_slider_profile_image_shape() {

        return [
            'key' => 'field_team_slider_profile_image_shape',
            'label' => __( 'Image shape', 'ldr' ),
            'name' => 'team_slider_profile_image_shape',
            'type' => 'button_group',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_team_slider_hide_profile_image',
                        'operator' => '!=',
                        'value' => 1
                    ]
                ]
            ],
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => 'circle',
            'return_format' => 'value',
            'allow_null' => 0,
            'layout' => 'horizontal',
            'choices' => [
                'circle' => '<i class="bi bi-circle-fill"></i>',
                'square' => '<i class="bi bi-square-fill"></i>',
            ]
        ];

    }

    // Hide contact details
    protected function _acf_field_team_slider_hide_contact_details() {

        return [
            'key' => 'field_team_slider_hide_contact_details',
            'label' => '',
            'name' => 'team_slider_hide_contact_details',
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

    // Hide role
    protected function _acf_field_team_slider_hide_role() {

        return [
            'key' => 'field_team_slider_hide_role',
            'label' => '',
            'name' => 'team_slider_hide_role',
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
            'message' => __( 'Hide role', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Hide office
    protected function _acf_field_team_slider_hide_office() {

        return [
            'key' => 'field_team_slider_hide_office',
            'label' => '',
            'name' => 'team_slider_hide_office',
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
            'message' => __( 'Hide office', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Hide excerpt
    protected function _acf_field_team_slider_hide_excerpt() {

        return [
            'key' => 'field_team_slider_hide_excerpt',
            'label' => '',
            'name' => 'team_slider_hide_excerpt',
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
            'message' => __( 'Hide excerpt', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Hide link
    protected function _acf_field_team_slider_hide_link() {

        return [
            'key' => 'field_team_slider_hide_link',
            'label' => '',
            'name' => 'team_slider_hide_link',
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
            'message' => __( 'Hide link', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Layout
    protected function _acf_field_team_slider_card_layout() {

        return [
            'key' => 'field_team_slider_card_layout',
            'label' => __( 'Card layout', 'ldr' ),
            'name' => 'team_slider_card_layout',
            'type' => 'button_group',
            'instructions' => __( 'Choose whether card is vertical or horizonal.', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => 'v',
            'return_format' => 'value',
            'allow_null' => 0,
            'layout' => 'horizontal',
            'choices' => [
                'h' => '<i class="bi bi-box-arrow-right"></i>',
                'v' => '<i class="bi bi-box-arrow-down"></i>',
            ]
        ];

    }

}
