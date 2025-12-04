<?php

namespace Ldr;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Share_Block {

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
                'name'              => 'share',
                'title'             => __( 'Share', 'ldr' ),
                'description'       => __( 'Share buttons.', 'ldr' ),
                'render_template'   => $this->block_dir . 'template.php',
                'category'          => 'custom',
                'icon'              => file_get_contents( $this->block_dir . 'images/share.svg' ),
                'keywords'          => ['share', 'social'],
                'enqueue_assets'    => function() {
                    wp_enqueue_style( 'share', $this->block_url . 'style.min.css', [], filemtime( $this->block_dir . 'style.min.css' ) );
                    // wp_enqueue_script( 'share', $this->block_url . 'script.min.js', [], filemtime( $this->block_dir . 'script.min.js' ), true );
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
                'key' => 'group_share_settings',
                'title' => __( 'Share', 'ldr' ),
                'fields' => $fields,
                'location' => [
                    [
                        [
                            'param' => 'block',
                            'operator' => '==',
                            'value' => 'acf/share'
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

    // Prefix
    protected function _acf_field_share_prefix() {

        return [
            'key' => 'field_share_prefix',
            'label' => __( 'Prefix', 'ldr' ),
            'name' => 'share_prefix',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => 'ldr-acf--no-label',
                'id' => '',
            ],
            'default_value' => __( 'Share', '' ),
            'maxlength'     => '',
            'placeholder'   => '',
            'prepend'       => '',
            'append'        => '',
        ];

    }

    // Social channels
    protected function _acf_field_share_channels() {

        $choices = [
            'twitter' => 'Twitter',
            'linkedin' => 'LinkedIn',
            'facebook' => 'Facebook',
            'email' => __( 'Email', 'ldr' ),
            'whatsapp' => 'Whatsapp',
        ];

        return [
            'key' => 'field_share_channels',
            'label' => __( 'Select channels', 'ldr' ),
            'name' => 'share_channels',
            'type' => 'checkbox',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => '',
            'layout' => 'vertical',
            'choices' => $choices,
            'allow_custom' => 0,
            'save_custom' => 0,
            'toggle' => 1,
            'return_format' => 'value',
        ];

    }

    // horizontal content alignment
    protected function _acf_field_share_alignment() {

        return [
            'key' => 'field_share_alignment',
            'label' => __( 'Alignment', 'ldr' ),
            'name' => 'share_alignment',
            'type' => 'button_group',
            'instructions' => __( 'Horizontal alignment on desktop devices.', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => 'justify-content-xl-center',
            'return_format' => 'value',
            'allow_null' => 0,
            'layout' => 'horizontal',
            'choices' => [
                'justify-content-xl-start' => '<i class="bi bi-align-start"></i>',
                'justify-content-xl-center' => '<i class="bi bi-align-middle"></i>',
                'justify-content-xl-end' => '<i class="bi bi-align-end"></i>',
            ]
        ];

    }

}
