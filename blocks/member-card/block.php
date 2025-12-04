<?php

namespace Ldr;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Member_Card_Block {

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
                'name'              => 'member-card',
                'title'             => __( 'Team Member Card', 'ldr' ),
                'description'       => __( 'A team member data.', 'ldr' ),
                'render_template'   => $this->block_dir . 'template.php',
                'category'          => 'custom',
                'icon'              => file_get_contents( $this->block_dir . 'images/member-card.svg' ),
                'keywords'          => ['member', 'card'],
                'supports'          => [
                    'align'         => false,
                    'jsx' 			=> true,
                ],
                'enqueue_assets'    => function() {
                    wp_enqueue_style( 'member-card', $this->block_url . 'style.min.css', [], filemtime( $this->block_dir . 'style.min.css' ) );
                    wp_enqueue_script( 'member-card', $this->block_url . 'script.min.js', [], filemtime( $this->block_dir . 'script.min.js' ), true );
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
                'key' => 'group_member_card_settings',
                'title' => __( 'Team grid', 'ldr' ),
                'fields' => $fields,
                'location' => [
                    [
                        [
                            'param' => 'block',
                            'operator' => '==',
                            'value' => 'acf/member-card'
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

    // Select member
    protected function _acf_field_member_card_select() {

        $choices = Teams::instance()->prepare_team_members_radio_choices();

        return [
            'key' => 'field_member_card_select',
            'label' => __( 'Select member', 'ldr' ),
            'name' => 'member_card_select',
            'type' => 'select',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_team_grid_hide_filter',
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
            'multiple' => 0,
            'choices' => $choices,
            'allow_null' => 1,
            'ui' => 1,
            'ajax' => 1,
            'placeholder' => __( 'Type name or select', 'ldr' ),
        ];

    }

    // Hide profile image
    protected function _acf_field_member_card_hide_profile_image() {

        return [
            'key' => 'field_member_card_hide_profile_image',
            'label' => '',
            'name' => 'member_card_hide_profile_image',
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

    // Hide name
    protected function _acf_field_member_card_hide_name() {

        return [
            'key' => 'field_member_card_hide_name',
            'label' => '',
            'name' => 'member_card_hide_name',
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
            'message' => __( 'Hide name', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Profile image size
    protected function _acf_field_member_card_profile_image_size() {

        return [
            'key' => 'field_member_card_profile_image_size',
            'label' => __( 'Profile image size', 'ldr' ),
            'name' => 'member_card_profile_image_size',
            'type' => 'range',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_member_card_hide_profile_image',
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
    protected function _acf_field_member_card_profile_image_shape() {

        return [
            'key' => 'field_member_card_profile_image_shape',
            'label' => __( 'Image shape', 'ldr' ),
            'name' => 'member_card_profile_image_shape',
            'type' => 'button_group',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_member_card_hide_profile_image',
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
    protected function _acf_field_member_card_hide_contact_details() {

        return [
            'key' => 'field_member_card_hide_contact_details',
            'label' => '',
            'name' => 'member_card_hide_contact_details',
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

    // Hide member card label
    protected function _acf_field_member_card_hide_label() {

        return [
            'key' => 'field_member_card_hide_label',
            'label' => '',
            'name' => 'member_card_hide_label',
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
            'message' => __( 'Hide label', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Card label
    protected function _acf_field_member_card_label() {

        return [
            'key' => 'field_member_card_label',
            'label' => __( 'label', 'ldr' ),
            'name' => 'member_card_label',
            'type' => 'text',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_member_card_hide_label',
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
            'default_value' => __( '', 'ldr' ),
            'placeholder'   => __( 'Your label text...', 'ldr' ),
        ];

    }

    // Hide role
    protected function _acf_field_member_card_hide_role() {

        return [
            'key' => 'field_member_card_hide_role',
            'label' => '',
            'name' => 'member_card_hide_role',
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
    protected function _acf_field_member_card_hide_office() {

        return [
            'key' => 'field_member_card_hide_office',
            'label' => '',
            'name' => 'member_card_hide_office',
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
    protected function _acf_field_member_card_hide_excerpt() {

        return [
            'key' => 'field_member_card_hide_excerpt',
            'label' => '',
            'name' => 'member_card_hide_excerpt',
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
    protected function _acf_field_member_card_hide_link() {

        return [
            'key' => 'field_member_card_hide_link',
            'label' => '',
            'name' => 'member_card_hide_link',
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

    // Force right alignment
    protected function _acf_field_member_card_force_right_alignment() {

        return [
            'key' => 'field_member_card_force_right_alignment',
            'label' => '',
            'name' => 'member_card_force_right_alignment',
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
            'message' => __( 'Force right alignment', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Layout
    protected function _acf_field_member_card_layout() {

        return [
            'key' => 'field_member_card_layout',
            'label' => __( 'Layout', 'ldr' ),
            'name' => 'member_card_layout',
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
