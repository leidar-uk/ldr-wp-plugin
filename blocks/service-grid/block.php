<?php

namespace Ldr;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Service_Grid_Block {

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

        add_action( 'wp_ajax_load_services', [$this, 'load_services'] );
        add_action( 'wp_ajax_nopriv_load_services', [$this, 'load_services'] );
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
            'post_type' => 'service',
            'post_status' => 'publish',
            'numberposts' => -1
        ], $args ) );

        $result = array_map( function( $n ) use( &$selection ) { $selection[$n->ID] = $n->post_title; return null; }, $posts );

        return $selection;

    }

    /**
     * Query team members via Ajax
     * @return void
     */
    public function load_services() {

        $args = [];
        $query_args = [
            'post_status' => 'publish',
            'post_type' => 'service',
            'numberposts' => -1,
        ];
        $filter = (int) $_POST['filter'];
        $custom_selection = (array) $_POST['customSelection'];
        $card_settings = (array) $_POST['cardSettings'];
        $exclude_sticky = (bool) $_POST['excludeSticky'];
        $omit_children = (bool) $_POST['omitChildren'];

        if( ! empty( $custom_selection ) ) {
            $query_args['post__in'] = $custom_selection;
            $query_args['orderby'] = 'post__in';
        }

        if( $omit_children ) {
            $query_args['post_parent'] = 0;
        }

        if( $filter > 0 ) {
            $args = [
                'tax_query' => [
                    [
                        'taxonomy' => 'groups',
                        'field' => 'term_id',
                        'terms' => [$filter]
                    ]
                ]
            ];
        }

        $posts = array_map( function( $n ) use( $card_settings ) {
            return Services::instance()->render_service_card_block( $n->ID, $card_settings );
        }, get_posts( array_merge( $query_args, $args ) ) );

        echo implode( '', $posts );
        wp_die();
        
    }

    /**
     * Gallery block based on the Masonry grid
     * @return void
     */
    public function ldr_register_acf_block() {

        if( function_exists( 'acf_register_block_type' ) ) {

            acf_register_block_type( [
                'name'              => 'service-grid',
                'title'             => __( 'Service Grid', 'ldr' ),
                'description'       => __( 'A grid of services.', 'ldr' ),
                'render_template'   => $this->block_dir . 'template.php',
                'category'          => 'custom',
                'icon'              => file_get_contents( $this->block_dir . 'images/service-grid.svg' ),
                'keywords'          => ['service', 'grid'],
                'supports'          => [
                    'align'         => ['wide', 'none'],
                    'jsx' 			=> true,
                ],
                'enqueue_assets'    => function() {
                    wp_enqueue_style( 'service-grid', $this->block_url . 'style.min.css', [], filemtime( $this->block_dir . 'style.min.css' ) );
                    wp_enqueue_script( 'service-grid', $this->block_url . 'script.min.js', [], filemtime( $this->block_dir . 'script.min.js' ), true );
                }
            ] );

            wp_localize_script( 'service-grid', 'themeData', Init::instance()->prepare_theme_data_object() );

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
                'key' => 'group_service_grid_settings',
                'title' => __( 'Service grid', 'ldr' ),
                'fields' => $fields,
                'location' => [
                    [
                        [
                            'param' => 'block',
                            'operator' => '==',
                            'value' => 'acf/service-grid'
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

    // Hide filter
    protected function _acf_field_service_grid_hide_filter() {

        return [
            'key' => 'field_service_grid_hide_filter',
            'label' => '',
            'name' => 'service_grid_hide_filter',
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
            'message' => __( 'Hide filters', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Filter services
    protected function _acf_field_service_grid_filter_services() {

        $choices = [];
        $term_query = new \WP_Term_Query( [
            'taxonomy' => 'groups',
            'hide_empty' => false
        ] );
        $categories = array_filter( $term_query->terms, function( $n ) { return $n->slug !== 'default-undefined'; } );

        foreach( $categories as $category ) {
            $choices[$category->term_id] = $category->name;
        }

        return [
            'key' => 'field_service_grid_filter_services',
            'label' => __( 'Filter services', 'ldr' ),
            'name' => 'service_grid_filter_services',
            'type' => 'select',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_service_grid_hide_filter',
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

    // Ommit children
    protected function _acf_field_service_grid_omit_children() {

        return [
            'key' => 'field_service_grid_omit_children',
            'label' => '',
            'name' => 'service_grid_omit_children',
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_service_grid_hide_filter',
                        'operator' => '==',
                        'value' => 1,
                    ],
                ]
            ],
            'wrapper' => [
                'width' => '',
                'class' => 'ldr-acf--no-label',
                'id' => '',
            ],
            'default_value' => 0,
            'message' => __( 'Exclude sub-services', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Manually select services
    protected function _acf_field_service_grid_select_services() {

        return [
            'key' => 'field_service_grid_select_services',
            'label' => __( 'Select services', 'ldr' ),
            'name' => 'service_grid_select_services',
            'type' => 'select',
            'instructions' => '',
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_service_grid_hide_filter',
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
            'choices' => $this->prepare_query_args(),
            'allow_null' => 1,
            'ui' => 1,
            'ajax' => 1,
            'placeholder' => __( 'Type name or select', 'ldr' ),
        ];

    }

    // Cover image height
    protected function _acf_field_service_grid_cover_image_height() {

        return [
            'key' => 'field_service_grid_cover_image_height',
            'label' => __( 'Cover image height', 'ldr' ),
            'name' => 'service_grid_cover_image_height',
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
    protected function _acf_field_service_grid_card_min_height() {

        return [
            'key' => 'field_service_grid_card_min_height',
            'label' => __( 'Card minimum height', 'ldr' ),
            'name' => 'service_grid_card_min_height',
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
    protected function _acf_field_service_grid_cover_image_overlay_color() {

        return [
            'key' => 'field_service_grid_cover_image_overlay_color',
            'label' => __( 'Cover image overlay color', 'ldr' ),
            'name' => 'service_grid_cover_image_overlay_color',
            'type' => 'color_picker',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => 'field_service_grid_cover_image_height',
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
    protected function _acf_field_service_grid_hide_border() {

        return [
            'key' => 'field_service_grid_hide_border',
            'label' => '',
            'name' => 'service_grid_hide_border',
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
    protected function _acf_field_service_grid_hide_description() {

        return [
            'key' => 'field_service_grid_hide_description',
            'label' => '',
            'name' => 'service_grid_hide_description',
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
    protected function _acf_field_service_grid_link_text() {

        return [
            'key' => 'field_service_grid_link_text',
            'label' => __( 'Link text', 'ldr' ),
            'name' => 'service_grid_link_text',
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
