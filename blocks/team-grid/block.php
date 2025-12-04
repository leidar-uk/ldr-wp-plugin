<?php

namespace Ldr;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Team_Grid_Block {

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

        add_action( 'wp_ajax_load_team_members', [$this, 'load_team_members'] );
        add_action( 'wp_ajax_nopriv_load_team_members', [$this, 'load_team_members'] );
        if ( function_exists( 'acf_register_block_type' ) ) {
            $this->ldr_register_acf_block();
            $this->ldr_register_field_group();
        } else {
            add_action( 'acf/init', [$this, 'ldr_register_acf_block'] );
            add_action( 'acf/init', [$this, 'ldr_register_field_group'] );
        }

    }

    /**
     * Query team members via Ajax
     * @return void
     */
    public function load_team_members( $test ) {

        $args = [];
        $query_args = [];
        $query_args_exclusion = [];
        $filter = (int) $_POST['filter'];
        $custom_selection = (array) $_POST['customSelection'];
        $excluded_members = (array) $_POST['excludedMembers'];

        if( $filter > 0 ) {
            $args = [
                'orderby' => 'menu_order title',
                'order' => 'ASC',
                'meta_query' => [
                    [
                        'key' => 'office',
                        'value' => $filter,
                        'type' => 'numeric',
                        'compare' => '='
                    ]
                ]
            ];
        } else {
            $args = [
                'orderby' => 'menu_order title',
                'order' => 'ASC'
            ];
        }

        if( ! empty( $custom_selection ) ) {
            $args['post__in'] = $custom_selection;
            $args['orderby'] = 'post__in';
        }

        if( ! empty( $excluded_members ) ) {
            $args['post__not_in'] = $excluded_members;
        }

        $team_members = array_map( function( $n ) {
            return Teams::instance()->render_team_member_card_block( $n->ID, ['hide_excerpt' => 1, 'hide_label' => 1] );
        }, Teams::instance()->get_team_members( $args ) );

        echo implode( '', $team_members );
        wp_die();
        
    }

    /**
     * Gallery block based on the Masonry grid
     * @return void
     */
    public function ldr_register_acf_block() {

        if( function_exists( 'acf_register_block_type' ) ) {

            acf_register_block_type( [
                'name'              => 'team-grid',
                'title'             => __( 'Team Grid', 'ldr' ),
                'description'       => __( 'A grid of team members.', 'ldr' ),
                'render_template'   => $this->block_dir . 'template.php',
                'category'          => 'custom',
                'icon'              => file_get_contents( $this->block_dir . 'images/team-grid.svg' ),
                'keywords'          => ['team', 'grid'],
                'supports'          => [
                    'align'         => false,
                    'jsx' 			=> true,
                ],
                'enqueue_assets'    => function() {
                    wp_enqueue_style( 'team-grid', $this->block_url . 'style.min.css', [], filemtime( $this->block_dir . 'style.min.css' ) );
                    wp_enqueue_script( 'team-grid', $this->block_url . 'script.min.js', [], filemtime( $this->block_dir . 'script.min.js' ), true );
                }
            ] );

            wp_localize_script( 'team-grid', 'themeData', Init::instance()->prepare_theme_data_object() );

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
                'key' => 'group_team_grid_settings',
                'title' => __( 'Team grid', 'ldr' ),
                'fields' => $fields,
                'location' => [
                    [
                        [
                            'param' => 'block',
                            'operator' => '==',
                            'value' => 'acf/team-grid'
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
    protected function _acf_field_team_grid_hide_filter() {

        return [
            'key' => 'field_team_grid_hide_filter',
            'label' => '',
            'name' => 'team_grid_hide_filter',
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

    // Filter members
    protected function _acf_field_team_grid_filter_members() {

        $choices = Offices::instance()->prepare_office_radio_choices();

        return [
            'key' => 'field_team_grid_filter_members',
            'label' => __( 'Filter members', 'ldr' ),
            'name' => 'team_grid_filter_members',
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

    // Manually select members
    protected function _acf_field_team_grid_select_members() {

        $choices = Teams::instance()->prepare_team_members_radio_choices();

        return [
            'key' => 'field_team_grid_select_members',
            'label' => __( 'Select members', 'ldr' ),
            'name' => 'team_grid_select_members',
            'type' => 'select',
            'instructions' => '',
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
            'multiple' => 1,
            'choices' => $choices,
            'allow_null' => 1,
            'ui' => 1,
            'ajax' => 1,
            'placeholder' => __( 'Type name or select', 'ldr' ),
        ];

    }

    // Exclude members
    protected function _acf_field_team_grid_exclude_members() {

        $choices = Teams::instance()->prepare_team_members_radio_choices();

        return [
            'key' => 'field_team_grid_exclude_members',
            'label' => __( 'Exclude members', 'ldr' ),
            'name' => 'team_grid_exclude_members',
            'type' => 'select',
            'instructions' => '',
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

    // Hide contact details
    protected function _acf_field_team_grid_hide_contact_details() {

        return [
            'key' => 'field_team_grid_hide_contact_details',
            'label' => '',
            'name' => 'team_grid_hide_contact_details',
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

    // Hide location
    protected function _acf_field_team_grid_hide_location() {

        return [
            'key' => 'field_team_grid_hide_location',
            'label' => '',
            'name' => 'team_grid_hide_location',
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
            'message' => __( 'Hide location', 'ldr' ),
            'ui' => 1,
        ];

    }

    // Profile image size
    protected function _acf_field_team_grid_profile_image_size() {

        return [
            'key' => 'field_team_grid_profile_image_size',
            'label' => __( 'Profile image size', 'ldr' ),
            'name' => 'team_grid_profile_image_size',
            'type' => 'button_group',
            'instructions' => __( '', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => 'lg',
            'return_format' => 'value',
            'allow_null' => 0,
            'layout' => 'horizontal',
            'choices' => [
                'sm' => 'SM',
                'md' => 'MD',
                'lg' => 'LG',
            ]
        ];

    }

    // Card layout
    protected function _acf_field_team_grid_card_layout() {

        return [
            'key' => 'field_team_grid_card_layout',
            'label' => __( 'Card layout', 'ldr' ),
            'name' => 'team_grid_card_layout',
            'type' => 'button_group',
            'instructions' => __( 'Choose whether cards are vertical or horizonal.', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => 'h',
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
