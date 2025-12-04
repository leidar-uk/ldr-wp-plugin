<?php

namespace Ldr;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Pull_Quote_Block {

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
                'name'              => 'pull-quote',
                'title'             => __( 'Pull Quote', 'ldr' ),
                'description'       => __( 'A pull quote block.', 'ldr' ),
                'render_template'   => $this->block_dir . 'template.php',
                'category'          => 'custom',
                'icon'              => file_get_contents( $this->block_dir . 'images/pull-quote.svg' ),
                'keywords'          => ['pull-quote', 'message'],
                'enqueue_assets'    => function() {
                    wp_enqueue_style( 'pull-quote', $this->block_url . 'style.min.css', [], filemtime( $this->block_dir . 'style.min.css' ) );
                    // wp_enqueue_script( 'pull-quote', $this->block_url . 'script.min.js', [], filemtime( $this->block_dir . 'script.min.js' ), true );
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
                'key' => 'group_pull_quote_settings',
                'title' => __( 'Pull Quote', 'ldr' ),
                'fields' => $fields,
                'location' => [
                    [
                        [
                            'param' => 'block',
                            'operator' => '==',
                            'value' => 'acf/pull-quote'
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

    // Pull quote message
    protected function _acf_field_pull_quote_message() {

        return [
            'key' => 'field_pull_quote_message',
            'label' => __( 'Quote message', 'ldr' ),
            'name' => 'pull_quote_message',
            'type' => 'textarea',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => __( '', 'ldr' ),
            'placeholder' => __( 'Pull quote message goes here...', 'ldr' ),
            'maxlength' => '',
            'rows' => 5,
            'new_lines' => '',
            'readonly' => 0,
            'disabled' => 0,
        ];

    }

    // Author
    protected function _acf_field_pull_quote_author() {

        return [
            'key' => 'field_pull_quote_author',
            'label' => __( "Author's name", 'ldr' ),
            'name' => 'pull_quote_author',
            'type' => 'text',
            'instructions' => __( 'For use when treated as a blockquote. Otherwise leave empty.', 'ldr' ),
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => 'ldr-acf--no-label',
                'id' => '',
            ],
            'default_value' => __( '', 'ldr' ),
            'placeholder'   => __( '', 'ldr' ),
            'prepend'       => '',
            'append'        => '',
        ];

    }

}
