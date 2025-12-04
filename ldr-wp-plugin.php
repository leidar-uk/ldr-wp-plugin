<?php
/**
 * Plugin Name: Leidar Plugin
 * Plugin URI: https://leidar.com/
 * Description: A list of custom blocks built within the Advanced Custom Fields (ACF).
 * Version: 1.0.0
 * Requires at least: 6.8.3
 * Requires PHP: 8.3.9
 * Author: Slawek Jurczyk
 * Author URI: https://leidar.com/team/slawek-jurczyk
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI: https://leidar.com/
 * Text Domain: ldr
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/inc/ldr-blocks.class.php';

Ldr\Blocks::instance();
