<?php

namespace Ldr;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Expertise_Grid_Block {

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

        add_action( 'wp_ajax_load_expertise', [$this, 'load_expertise'] );
        add_action( 'wp_ajax_nopriv_load_expertise', [$this, 'load_expertise'] );
        add_action( 'enqueue_block_assets', [$this, 'enqueue_editor_styles'] );
        if ( function_exists( 'acf_register_block_type' ) ) {
            $this->ldr_register_acf_block();
            $this->ldr_register_field_group();
        } else {
            add_action( 'acf/init', [$this, 'ldr_register_acf_block'] );
            add_action( 'acf/init', [$this, 'ldr_register_field_group'] );
        }

    }

    /**
     * Loads the block styles inside the (iframed) block editor canvas.
     * ACF only enqueues them when the block is already in the content on load,
     * so a freshly inserted block would be rendered without the grid layout.
     * @return void
     */
    public function enqueue_editor_styles() {

        if( ! is_admin() ) {
            return;
        }

        wp_enqueue_style( 'expertise-grid', $this->block_url . 'style.min.css', [], filemtime( $this->block_dir . 'style.min.css' ) );

    }

    /**
     * Prepares WP_Query arguments
     * @param array $args
     * @return array
     */
    public function prepare_query_args( $args = [] ) {

        $selection = [];
        $expertise = get_posts( array_merge( [
            'ignore_sticky_posts' => true,
            'post_type' => 'expertise',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'paged' => 1,
        ], $args ) );

        $result = array_map( function( $n ) use( &$selection ) { $selection[$n->ID] = $n->post_title; return null; }, $expertise );

        return $selection;

    }

    /**
     * Query team members via Ajax
     * @return void
     */
    public function load_expertise() {

        $expertise_number = isset( $_POST['postsNumber'] ) ? (int) $_POST['postsNumber'] : -1;

        $args = [
            'post_status' => 'publish',
            'post_type' => 'expertise',
            'posts_per_page' => ( $expertise_number > -1 ) ? $expertise_number : -1,
            'orderby' => 'date',
            'order' => 'DESC',
            // 'ignore_sticky_posts' => 1,
            'paged' => isset( $_POST['paged'] ) ? (int) $_POST['paged'] : 1
        ];
        $custom_selection = isset( $_POST['customSelection'] ) ? array_map( 'intval', (array) $_POST['customSelection'] ) : [];
        $excluded_expertise = isset( $_POST['excludedExpertises'] ) ? array_map( 'intval', (array) $_POST['excludedExpertises'] ) : [];
        $card_settings = isset( $_POST['cardSettings'] ) ? (array) $_POST['cardSettings'] : [];
        $expertise_type = isset( $_POST['expertiseType'] ) ? sanitize_key( $_POST['expertiseType'] ) : 'all';

        // Show only entries assigned to the selected categories
        $expertise_categories = isset( $_POST['expertiseCategories'] ) ? array_filter( array_map( 'intval', (array) $_POST['expertiseCategories'] ) ) : [];

        if( ! empty( $expertise_categories ) ) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'expertise_category',
                    'field' => 'term_id',
                    'terms' => $expertise_categories,
                    'include_children' => true,
                ],
            ];
        }

        // Show only practices or only examples (post meta: expertise_type)
        if( in_array( $expertise_type, [ 'practice', 'example' ], true ) ) {
            $args['meta_query'] = [
                [
                    'key' => 'expertise_type',
                    'value' => $expertise_type,
                    'compare' => '=',
                ],
            ];
        }

        if( $expertise_number > 0 ) {
            $args['numberposts'] = $expertise_number;
        }

        if( ! empty( $custom_selection ) ) {
            $args['post__in'] = $custom_selection;
            $args['orderby'] = 'post__in';
        }

        if( ! empty( $excluded_expertise ) ) {
            $args['post__not_in'] = $excluded_expertise;
        }

        $expertise = array_map( function( $n ) use( $card_settings ) {
            return Expertise_Card_Block::instance()->render_expertise_card_block( $n->ID, $card_settings );
        }, get_posts( $args ) );

        echo implode( '', $expertise );
        wp_die();
        
    }

    /**
     * Gallery block based on the Masonry grid
     * @return void
     */
    public function ldr_register_acf_block() {

        if( function_exists( 'acf_register_block_type' ) ) {

            acf_register_block_type( [
                'name'              => 'expertise-grid',
                'title'             => __( 'Expertise Grid', 'ldr' ),
                'description'       => __( 'A grid of expertise expertise.', 'ldr' ),
                'render_template'   => $this->block_dir . 'template.php',
                'category'          => 'custom',
                'icon'              => file_get_contents( $this->block_dir . 'images/expertise-grid.svg' ),
                'keywords'          => ['expertise', 'grid'],
                'supports'          => [
                    'align'         => ['wide', 'none'],
                    'jsx' 			=> true,
                ],
                'enqueue_assets'    => function() {
                    wp_enqueue_style( 'expertise-grid', $this->block_url . 'style.min.css', [], filemtime( $this->block_dir . 'style.min.css' ) );
                    wp_enqueue_script( 'expertise-grid', $this->block_url . 'script.min.js', [], filemtime( $this->block_dir . 'script.min.js' ), true );
                }
            ] );

            wp_localize_script( 'expertise-grid', 'themeData', Init::instance()->prepare_theme_data_object() );

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
                'key' => 'group_expertise_grid_settings',
                'title' => __( 'Expertise grid', 'ldr' ),
                'fields' => $fields,
                'location' => [
                    [
                        [
                            'param' => 'block',
                            'operator' => '==',
                            'value' => 'acf/expertise-grid'
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

    // Number of expertise to load
    protected function _acf_field_expertise_grid_expertise_number() {

        return [
            'key' => 'field_expertise_grid_expertise_number',
            'label' => __( 'Expertise number', 'ldr' ),
            'name' => 'expertise_grid_expertise_number',
            'type' => 'number',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [],
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => -1,
            'min'           => -1,
            'max'           => '',
            'step'          => 1,
            'prepend'       => '',
            'append'        => '',
        ];

    }

    // Type of expertise to show
    protected function _acf_field_expertise_grid_expertise_type() {

        return [
            'key' => 'field_expertise_grid_expertise_type',
            'label' => __( 'Show', 'ldr' ),
            'name' => 'expertise_grid_expertise_type',
            'type' => 'select',
            'instructions' => __( 'Choose which type of expertise entries to display.', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => 'all',
            'multiple' => 0,
            'choices' => [
                'all' => __( 'All', 'ldr' ),
                'practice' => __( 'Practices only', 'ldr' ),
                'example' => __( 'Examples only', 'ldr' ),
            ],
            'allow_null' => 0,
            'ui' => 0,
            'return_format' => 'value',
        ];

    }

    // Expertise categories
    protected function _acf_field_expertise_grid_categories() {

        return [
            'key' => 'field_expertise_grid_categories',
            'label' => __( 'Categories', 'ldr' ),
            'name' => 'expertise_grid_categories',
            'type' => 'taxonomy',
            'instructions' => __( 'Show only expertise from the selected categories. Leave empty to show all.', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'taxonomy' => 'expertise_category',
            'field_type' => 'multi_select',
            'allow_null' => 1,
            'add_term' => 0,
            'save_terms' => 0,
            'load_terms' => 0,
            'return_format' => 'id',
            'multiple' => 1,
        ];

    }

    // Manually select expertise
    protected function _acf_field_expertise_grid_select_expertise() {

        return [
            'key' => 'field_expertise_grid_select_expertise',
            'label' => __( 'Select expertise', 'ldr' ),
            'name' => 'expertise_grid_select_expertise',
            'type' => 'select',
            'instructions' => '',
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_expertise_grid_exclude_expertise',
                        'operator' => '==empty',
                        'value' => ''
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
            'choices' => $this->prepare_query_args( [] ),
            'allow_null' => 1,
            'ui' => 1,
            'ajax' => 1,
            'placeholder' => __( 'Type name or select', 'ldr' ),
        ];

    }

    // Exclude expertise
    protected function _acf_field_expertise_grid_exclude_expertise() {

        return [
            'key' => 'field_expertise_grid_exclude_expertise',
            'label' => __( 'Exclude expertise', 'ldr' ),
            'name' => 'expertise_grid_exclude_expertise',
            'type' => 'select',
            'instructions' => __( 'Excludes selected expertise.', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_expertise_grid_select_expertise',
                        'operator' => '==empty',
                        'value' => ''
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
            'choices' => $this->prepare_query_args( [] ),
            'allow_null' => 1,
            'ui' => 1,
            'ajax' => 1,
            'placeholder' => __( 'Type or select', 'ldr' ),
        ];

    }

    // Cover image height
    protected function _acf_field_expertise_grid_cover_image_height() {

        return [
            'key' => 'field_expertise_grid_cover_image_height',
            'label' => __( 'Cover image height', 'ldr' ),
            'name' => 'expertise_grid_cover_image_height',
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
    protected function _acf_field_expertise_grid_card_min_height() {

        return [
            'key' => 'field_expertise_grid_card_min_height',
            'label' => __( 'Card minimum height', 'ldr' ),
            'name' => 'expertise_grid_card_min_height',
            'type' => 'number',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => 320,
            'min'           => 0,
            'max'           => '',
            'step'          => 1,
            'prepend'       => '',
            'append'        => 'px',
        ];

    }

    // Overlay color
    protected function _acf_field_expertise_grid_cover_image_overlay_color() {

        return [
            'key' => 'field_expertise_grid_cover_image_overlay_color',
            'label' => __( 'Cover image overlay color', 'ldr' ),
            'name' => 'expertise_grid_cover_image_overlay_color',
            'type' => 'color_picker',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_expertise_grid_cover_image_height',
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

    // Hide card border
    protected function _acf_field_expertise_grid_hide_border() {

        return [
            'key' => 'field_expertise_grid_hide_border',
            'label' => '',
            'name' => 'expertise_grid_hide_border',
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
    protected function _acf_field_expertise_grid_hide_description() {

        return [
            'key' => 'field_expertise_grid_hide_description',
            'label' => '',
            'name' => 'expertise_grid_hide_description',
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => 'ldr-acf--no-label',
                'id' => '',
            ],
            'default_value' => 1,
            'message' => __( 'Hide description', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Link text
    protected function _acf_field_expertise_grid_link_text() {

        return [
            'key' => 'field_expertise_grid_link_text',
            'label' => __( 'Link text', 'ldr' ),
            'name' => 'expertise_grid_link_text',
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
