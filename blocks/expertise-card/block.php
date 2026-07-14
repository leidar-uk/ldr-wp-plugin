<?php

namespace Ldr;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Expertise_Card_Block {

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
     * Renders an expertise-card block for a given expertise post
     * @param int $post_id
     * @param array $card_args
     * @return void
     */
    public function render_expertise_card_block( $post_id, $card_args = [] ) {

        return render_block( [
            'blockName' => 'acf/expertise-card',
            'attrs' => [
                'id' => 'block_expertise_card_' . $post_id,
                'name' => 'acf/expertise-card',
                'data' => [
                    'expertise_card_select' => $post_id,
                    '_expertise_card_select' => 'field_expertise_card_select',
                    'expertise_card_cover_image_height' => ! empty( $card_args['cover_image_height'] ) ? (int) $card_args['cover_image_height'] : 240,
                    '_expertise_card_cover_image_height' => 'field_expertise_card_cover_image_height',
                    'expertise_card_min_height' => ! empty( $card_args['card_min_height'] ) ? (int) $card_args['card_min_height'] : 240,
                    '_expertise_card_min_height' => 'field_expertise_card_min_height',
                    'expertise_card_cover_image_overlay_color' => ! empty( $card_args['cover_image_overlay_color'] ) ? $card_args['cover_image_overlay_color'] : 'rgba(0,0,0,0.5)',
                    '_expertise_card_cover_image_overlay_color' => 'field_expertise_card_cover_image_overlay_color',
                    'expertise_card_layout' => ! empty( $card_args['layout'] ) ? $card_args['layout'] : 'column',
                    '_expertise_card_layout' => 'field_expertise_card_layout',
                    'expertise_card_hide_border' => ! empty( $card_args['hide_border'] ) ? (int) $card_args['hide_border'] : 0,
                    '_expertise_card_hide_border' => 'field_expertise_card_hide_border',
                    'expertise_card_hide_description' => ! empty( $card_args['hide_description'] ) ? (int) $card_args['hide_description'] : 0,
                    '_expertise_card_hide_description' => 'field_expertise_card_hide_description',
                    'expertise_card_link_text' => ! empty( $card_args['link_text'] ) ? $card_args['link_text'] : __( 'Find out more', 'ldr' ),
                    '_expertise_card_link_text' => 'field_expertise_card_link_text',
                ],
                'align' => '',
                'mode' => 'preview',
            ],
            'innerBlocks' => [],
            'innerHTML' => '',
            'innerContent' => [],
        ] );

    }

    /**
     * Gallery block based on the Masonry grid
     * @return void
     */
    public function ldr_register_acf_block() {

        if( function_exists( 'acf_register_block_type' ) ) {

            acf_register_block_type( [
                'name'              => 'expertise-card',
                'title'             => __( 'Expertise Card', 'ldr' ),
                'description'       => __( 'An expertise snippet in a form of a card.', 'ldr' ),
                'render_template'   => $this->block_dir . 'template.php',
                'category'          => 'custom',
                'icon'              => file_get_contents( $this->block_dir . 'images/expertise-card.svg' ),
                'keywords'          => ['expertise', 'card', 'info'],
                'supports'          => [
                    'jsx' => true,
                ],
                'enqueue_assets'    => function() {
                    wp_enqueue_style( 'expertise-card', $this->block_url . 'style.min.css', [], filemtime( $this->block_dir . 'style.min.css' ) );
                    // wp_enqueue_script( 'expertise-card', $this->block_url . 'script.min.js', [], filemtime( $this->block_dir . 'script.min.js' ), true );
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
                'key' => 'group_expertise_card_settings',
                'title' => __( 'Expertise Card', 'ldr' ),
                'fields' => $fields,
                'location' => [
                    [
                        [
                            'param' => 'block',
                            'operator' => '==',
                            'value' => 'acf/expertise-card'
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

    // Select expertise
    protected function _acf_field_expertise_card_select() {

        // $choices = Expertises::instance()->prepare_expertise_radio_choices();
        $choices = [];
        $expertise = get_posts( [
            'post_type' => 'expertise',
            'post_status' => 'publish',
            'posts_number' => -1,
            'ignore_sticky_posts' => false,
        ] );

        foreach( $expertise as $expertise ) {
            $choices[$expertise->ID] = $expertise->post_title;
        }

        return [
            'key' => 'field_expertise_card_select',
            'label' => __( 'Select expertise', 'ldr' ),
            'name' => 'expertise_card_select',
            'type' => 'select',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => 0,
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

    // Cover image height
    protected function _acf_field_expertise_card_cover_image_height() {

        return [
            'key' => 'field_expertise_card_cover_image_height',
            'label' => __( 'Cover image height', 'ldr' ),
            'name' => 'expertise_card_cover_image_height',
            'type' => 'range',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
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

    // Card minimum height
    protected function _acf_field_expertise_card_min_height() {

        return [
            'key' => 'field_expertise_card_min_height',
            'label' => __( 'Card minimum height', 'ldr' ),
            'name' => 'expertise_card_min_height',
            'type' => 'range',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
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
    protected function _acf_field_expertise_card_cover_image_overlay_color() {

        return [
            'key' => 'field_expertise_card_cover_image_overlay_color',
            'label' => __( 'Cover image overlay color', 'ldr' ),
            'name' => 'expertise_card_cover_image_overlay_color',
            'type' => 'color_picker',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_expertise_card_cover_image_height',
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

    // Layout
    protected function _acf_field_expertise_card_layout() {

        return [
            'key' => 'field_expertise_card_layout',
            'label' => __( 'Layout', 'ldr' ),
            'name' => 'expertise_card_layout',
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
    protected function _acf_field_expertise_card_hide_border() {

        return [
            'key' => 'field_expertise_card_hide_border',
            'label' => '',
            'name' => 'expertise_card_hide_border',
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

    // Hide description
    protected function _acf_field_expertise_card_hide_description() {

        return [
            'key' => 'field_expertise_card_hide_description',
            'label' => '',
            'name' => 'expertise_card_hide_description',
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

    // Link text
    protected function _acf_field_expertise_card_link_text() {

        return [
            'key' => 'field_expertise_card_link_text',
            'label' => __( 'Link text', 'ldr' ),
            'name' => 'expertise_card_link_text',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_expertise_card_hide_link',
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

}
