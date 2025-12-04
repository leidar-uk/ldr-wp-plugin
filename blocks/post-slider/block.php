<?php

namespace Ldr;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Post_Slider_Block {

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
     * Prepares WP_Query arguments
     * @param array $args
     * @return array
     */
    public function prepare_query_args( $args = [] ) {

        $selection = [];
        $posts = get_posts( array_merge( [
            'ignore_sticky_posts' => true,
            'post_type' => 'post',
            'post_status' => 'publish',
            'numberposts' => -1
        ], $args ) );

        $result = array_map( function( $n ) use( &$selection ) { $selection[$n->ID] = $n->post_title; return null; }, $posts );

        return $selection;

    }

    /**
     * Gallery block based on the Masonry grid
     * @return void
     */
    public function ldr_register_acf_block() {

        if( function_exists( 'acf_register_block_type' ) ) {

            acf_register_block_type( [
                'name'              => 'post-slider',
                'title'             => __( 'Post Slider', 'ldr' ),
                'description'       => __( 'Animated post slider block.', 'ldr' ),
                'render_template'   => $this->block_dir . 'template.php',
                'category'          => 'custom',
                'icon'              => file_get_contents( $this->block_dir . 'images/post-slider.svg' ),
                'keywords'          => ['post', 'slider', 'article'],
                'enqueue_assets'    => function() {
                    wp_enqueue_style( 'post-slider', $this->block_url . 'style.min.css', [], filemtime( $this->block_dir . 'style.min.css' ) );
                    wp_enqueue_script( 'post-slider', $this->block_url . 'script.min.js', [], filemtime( $this->block_dir . 'script.min.js' ), true );
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
                'key' => 'group_post_slider_settings',
                'title' => __( 'Post Slider', 'ldr' ),
                'fields' => $fields,
                'location' => [
                    [
                        [
                            'param' => 'block',
                            'operator' => '==',
                            'value' => 'acf/post-slider'
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

    // Is animated
    protected function _acf_field_post_slider_is_static() {

        return [
            'key' => 'field_post_slider_is_static',
            'label' => '',
            'name' => 'post_slider_is_static',
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
            'message' => __( 'Is static', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Number of posts
    public function _acf_field_post_slider_number_of_posts() {

        return [
            'key' => 'field_post_slider_number_of_posts',
            'label' => __( 'Number of posts', 'ldr' ),
            'name' => 'post_slider_number_of_posts',
            'type' => 'number',
            'instructions' => __( 'Start at specific slide number.', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_post_slider_is_static',
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
            'default_value' => -1,
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'min' => -1,
            'step' => 1,
            'readonly' => 0,
            'disabled' => 0,
        ];

    }

    // Sticky posts
    protected function _acf_field_post_slider_only_sticky_posts() {

        return [
            'key' => 'field_post_slider_only_sticky_posts',
            'label' => '',
            'name' => 'post_slider_only_sticky_posts',
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
            'message' => __( 'Only sticky posts', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Filter by author
    protected function _acf_field_post_slider_filter_by_author() {

        return [
            'key' => 'field_post_slider_filter_by_author',
            'label' => '',
            'name' => 'post_slider_filter_by_author',
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_post_slider_only_sticky_posts',
                        'operator' => '!=',
                        'value' => 1,
                    ],
                    [
                        'field' => 'field_post_slider_filter_by_category',
                        'operator' => '!=',
                        'value' => 1,
                    ]
                ]
            ],
            'wrapper' => [
                'width' => '',
                'class' => 'ldr-acf--no-label',
                'id' => '',
            ],
            'default_value' => 0,
            'message' => __( 'Filter by author', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Filter by category
    protected function _acf_field_post_slider_filter_by_category() {

        return [
            'key' => 'field_post_slider_filter_by_category',
            'label' => '',
            'name' => 'post_slider_filter_by_category',
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_post_slider_only_sticky_posts',
                        'operator' => '!=',
                        'value' => 1,
                    ],
                    [
                        'field' => 'field_post_slider_filter_by_author',
                        'operator' => '!=',
                        'value' => 1,
                    ]
                ]
            ],
            'wrapper' => [
                'width' => '',
                'class' => 'ldr-acf--no-label',
                'id' => '',
            ],
            'default_value' => 0,
            'message' => __( 'Filter by category', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Select author
    protected function _acf_field_post_slider_select_author() {

        $choices = [];
        $authors = [];
        $all_posts = get_posts( [
            'post_type' => 'post',
            'post_status' => 'publish',
            'numberposts' => -1
        ] );
        $posts_authors = array_map( function( $n ) use( &$authors ) { $authors[] = (int) get_post_meta( $n->ID, 'post_author_select', true )[0]; return null; }, $all_posts );
        $unique_authors = array_unique( $authors );

        foreach( $unique_authors as $author_id ) {
            $choices[$author_id] = get_post( $author_id )->post_title;
        }

        return [
            'key' => 'field_post_slider_select_author',
            'label' => __( 'Filter posts by author', 'ldr' ),
            'name' => 'post_slider_select_author',
            'type' => 'select',
            'instructions' => __( 'Shows posts from the selected author only.', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_post_slider_filter_by_author',
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
            'placeholder' => __( 'Type or select', 'ldr' ),
        ];

    }

    // Show only selected categories
    protected function _acf_field_post_slider_select_in_multiple_category() {

        return [
            'key' => 'field_post_slider_select_in_multiple_categories',
            'label' => '',
            'name' => 'post_slider_select_in_multiple_categories',
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_post_slider_filter_by_author',
                        'operator' => '!=',
                        'value' => 1
                    ],
                    [
                        'field' => 'field_post_slider_filter_by_category',
                        'operator' => '==',
                        'value' => 1
                    ]
                ],
            ],
            'wrapper' => [
                'width' => '',
                'class' => 'ldr-acf--no-label',
                'id' => '',
            ],
            'default_value' => 0,
            'message' => __( 'Select multiple', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Select category
    protected function _acf_field_post_slider_select_category() {

        $choices = [];
        $categories = get_categories();

        foreach( $categories as $category ) {
            $choices[$category->term_id] = $category->name;
        }

        return [
            'key' => 'field_post_slider_select_categories',
            'label' => __( 'Filter posts by categories', 'ldr' ),
            'name' => 'post_slider_select_categories',
            'type' => 'select',
            'instructions' => __( 'Shows posts from the selected categories.', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_post_slider_filter_by_category',
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
            'placeholder' => __( 'Type or select', 'ldr' ),
        ];

    }

    // Select posts
    protected function _acf_field_post_slider_select_posts() {

        $sticky_posts = get_option( 'sticky_posts' );

        return [
            'key' => 'field_post_slider_select_posts',
            'label' => __( 'Select posts', 'ldr' ),
            'name' => 'post_slider_select_posts',
            'type' => 'select',
            'instructions' => __( 'Excludes sticky posts.', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_post_slider_only_sticky_posts',
                        'operator' => '!=',
                        'value' => 1,
                    ],
                    [
                        'field' => 'field_post_slider_filter_by_author',
                        'operator' => '!=',
                        'value' => 1,
                    ],
                    [
                        'field' => 'field_post_slider_filter_by_category',
                        'operator' => '!=',
                        'value' => 1,
                    ],
                    [
                        'field' => 'field_post_slider_is_static',
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
            'choices' => $this->prepare_query_args( [ 
                'ignore_sticky_posts' => true,
                'post__not_in' => $sticky_posts,
            ] ),
            'allow_null' => 1,
            'ui' => 1,
            'ajax' => 1,
            'placeholder' => __( 'Type or select', 'ldr' ),
        ];

    }

    // Hide arrows
    protected function _acf_field_post_slider_hide_arrows() {

        return [
            'key' => 'field_post_slider_hide_arrows',
            'label' => '',
            'name' => 'post_slider_hide_arrows',
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_post_slider_is_static',
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
            'message' => __( 'Hide arrows', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Hide bullets
    protected function _acf_field_post_slider_hide_bullets() {

        return [
            'key' => 'field_post_slider_hide_bullets',
            'label' => '',
            'name' => 'post_slider_hide_bullets',
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_post_slider_is_static',
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
            'message' => __( 'Hide bullets', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Slider type
    protected function _acf_field_post_slider_type() {

        return [
            'key' => 'field_post_slider_type',
            'label' => __( 'Type', 'ldr' ),
            'name' => 'post_slider_type',
            'type' => 'button_group',
            'instructions' => __( 'Choose whether card is vertical or horizonal.', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_post_slider_is_static',
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
    public function _acf_field_post_slider_start_at() {

        return [
            'key' => 'field_post_slider_start_at',
            'label' => __( 'Start at', 'ldr' ),
            'name' => 'post_slider_start_at',
            'type' => 'number',
            'instructions' => __( 'Start at specific slide number.', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_post_slider_is_static',
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
    public function _acf_field_post_slider_per_view() {

        return [
            'key' => 'field_post_slider_per_view',
            'label' => __( 'Per view', 'ldr' ),
            'name' => 'post_slider_per_view',
            'type' => 'number',
            'instructions' => __( 'A number of visible slides.', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_post_slider_is_static',
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
            'default_value' => 1,
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'min' => 1,
            'max' => 5,
            'step' => 1,
            'readonly' => 0,
            'disabled' => 0,
        ];

    }

    // Slider autoplay
    public function _acf_field_post_slider_autoplay() {

        return [
            'key' => 'field_post_slider_autoplay',
            'label' => __( 'Autoplay', 'ldr' ),
            'name' => 'post_slider_autoplay',
            'type' => 'number',
            'instructions' => __( 'Change slides after a specified interval.', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_post_slider_is_static',
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
    protected function _acf_field_post_slider_hoverpause() {

        return [
            'key' => 'field_post_slider_hoverpause',
            'label' => '',
            'name' => 'post_slider_hoverpause',
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_post_slider_is_static',
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
            'message' => __( 'Stop on mouseover', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Slider animation duration
    public function _acf_field_post_slider_animation_duration() {

        return [
            'key' => 'field_post_slider_animation_duration',
            'label' => __( 'Animation duration', 'ldr' ),
            'name' => 'post_slider_animation_duration',
            'type' => 'number',
            'instructions' => __( 'Duration of the animation.', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_post_slider_is_static',
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

    // Cover image height
    protected function _acf_field_post_slider_cover_image_height() {

        return [
            'key' => 'field_post_slider_cover_image_height',
            'label' => __( 'Cover image height', 'ldr' ),
            'name' => 'post_slider_cover_image_height',
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
    protected function _acf_field_post_slider_card_min_height() {

        return [
            'key' => 'field_post_slider_card_min_height',
            'label' => __( 'Card minimum height', 'ldr' ),
            'name' => 'post_slider_card_min_height',
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
    protected function _acf_field_post_slider_cover_image_overlay_color() {

        return [
            'key' => 'field_post_slider_cover_image_overlay_color',
            'label' => __( 'Cover image overlay color', 'ldr' ),
            'name' => 'post_slider_cover_image_overlay_color',
            'type' => 'color_picker',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_post_slider_cover_image_height',
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
            'default_value' => 'rgba(53,109,220,0.05)',
            'enable_opacity' => 1,
            'return_format' => 'string',
            'ui' => 1,
        ];

    }

    // Layout
    protected function _acf_field_post_slider_card_layout() {

        return [
            'key' => 'field_post_slider_card_layout',
            'label' => __( 'Card layout', 'ldr' ),
            'name' => 'post_slider_card_layout',
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
    protected function _acf_field_post_slider_hide_border() {

        return [
            'key' => 'field_post_slider_hide_border',
            'label' => '',
            'name' => 'post_slider_hide_border',
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
    protected function _acf_field_post_slider_hide_description() {

        return [
            'key' => 'field_post_slider_hide_description',
            'label' => '',
            'name' => 'post_slider_hide_description',
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
    protected function _acf_field_post_slider_link_text() {

        return [
            'key' => 'field_post_slider_link_text',
            'label' => __( 'Link text', 'ldr' ),
            'name' => 'post_slider_link_text',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => 'ldr-acf--no-label',
                'id' => '',
            ],
            'default_value' => __( 'Read more', 'ldr' ),
            'maxlength'     => '',
            'placeholder'   => '',
            'prepend'       => '',
            'append'        => '',
        ];

    }

}
