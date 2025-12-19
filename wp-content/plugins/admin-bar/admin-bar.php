<?php

/**
 * Plugin Name: Admin Bar Editor
 * Plugin URI:  https://jeweltheme.com
 * Description: Hide Admin bar or toolbar based on user role and add custom links, reorder, change WordPress logo & howdy text. Change bar position, background color.
 * Version:     1.1.2.0
 * Author:      Jewel Theme
 * Author URI:  https://jeweltheme.com/admin-bar-editor
 * Text Domain: admin-bar
 * Domain Path: languages/
 * License:     GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package admin-bar
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$jlt_admin_bar_editor_plugin_data = get_file_data(
	__FILE__,
	array(
		'Version'     => 'Version',
		'Plugin Name' => 'Plugin Name',
		'Author'      => 'Author',
		'Description' => 'Description',
		'Plugin URI'  => 'Plugin URI',
	),
	false
);

// Define Constants.
if ( ! defined( 'JLT_ADMIN_BAR_EDITOR' ) ) {
	define( 'JLT_ADMIN_BAR_EDITOR', $jlt_admin_bar_editor_plugin_data['Plugin Name'] );
}

if ( ! defined( 'JLT_ADMIN_BAR_EDITOR_VER' ) ) {
	define( 'JLT_ADMIN_BAR_EDITOR_VER', $jlt_admin_bar_editor_plugin_data['Version'] );
}

if ( ! defined( 'JLT_ADMIN_BAR_EDITOR_AUTHOR' ) ) {
	define( 'JLT_ADMIN_BAR_EDITOR_AUTHOR', $jlt_admin_bar_editor_plugin_data['Author'] );
}

if ( ! defined( 'JLT_ADMIN_BAR_EDITOR_DESC' ) ) {
	define( 'JLT_ADMIN_BAR_EDITOR_DESC', $jlt_admin_bar_editor_plugin_data['Author'] );
}

if ( ! defined( 'JLT_ADMIN_BAR_EDITOR_URI' ) ) {
	define( 'JLT_ADMIN_BAR_EDITOR_URI', $jlt_admin_bar_editor_plugin_data['Plugin URI'] );
}

if ( ! defined( 'JLT_ADMIN_BAR_EDITOR_DIR' ) ) {
	define( 'JLT_ADMIN_BAR_EDITOR_DIR', __DIR__ );
}

if ( ! defined( 'JLT_ADMIN_BAR_EDITOR_FILE' ) ) {
	define( 'JLT_ADMIN_BAR_EDITOR_FILE', __FILE__ );
}

if ( ! defined( 'JLT_ADMIN_BAR_EDITOR_SLUG' ) ) {
	define( 'JLT_ADMIN_BAR_EDITOR_SLUG', dirname( plugin_basename( __FILE__ ) ) );
}

if ( ! defined( 'JLT_ADMIN_BAR_EDITOR_BASE' ) ) {
	define( 'JLT_ADMIN_BAR_EDITOR_BASE', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'JLT_ADMIN_BAR_EDITOR_PATH' ) ) {
	define( 'JLT_ADMIN_BAR_EDITOR_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
}

if ( ! defined( 'JLT_ADMIN_BAR_EDITOR_URL' ) ) {
	define( 'JLT_ADMIN_BAR_EDITOR_URL', trailingslashit( plugins_url( '/', __FILE__ ) ) );
}

if ( ! defined( 'JLT_ADMIN_BAR_EDITOR_INC' ) ) {
	define( 'JLT_ADMIN_BAR_EDITOR_INC', JLT_ADMIN_BAR_EDITOR_PATH . '/Inc/' );
}

if ( ! defined( 'JLT_ADMIN_BAR_EDITOR_LIBS' ) ) {
	define( 'JLT_ADMIN_BAR_EDITOR_LIBS', JLT_ADMIN_BAR_EDITOR_PATH . 'Libs' );
}

if ( ! defined( 'JLT_ADMIN_BAR_EDITOR_ASSETS' ) ) {
	define( 'JLT_ADMIN_BAR_EDITOR_ASSETS', JLT_ADMIN_BAR_EDITOR_URL . 'assets/' );
}

if ( ! defined( 'JLT_ADMIN_BAR_EDITOR_IMAGES' ) ) {
	define( 'JLT_ADMIN_BAR_EDITOR_IMAGES', JLT_ADMIN_BAR_EDITOR_ASSETS . 'images/' );
}

if ( ! class_exists( '\\JewelTheme\\AdminBarEditor\\AdminBarEditor' ) ) {
	// Autoload Files.
	include_once JLT_ADMIN_BAR_EDITOR_DIR . '/vendor/autoload.php';
	// Instantiate AdminBarEditor Class.
	include_once JLT_ADMIN_BAR_EDITOR_DIR . '/class-admin-bar.php';
}

if (! class_exists('\\JewelTheme\\AdminBarEditor\\AdminBarEditor')) {
	// Activation and Deactivation hooks.
	register_activation_hook(JLT_ADMIN_BAR_EDITOR_FILE, array('\\JewelTheme\\AdminBarEditor\\AdminBarEditor', 'jlt_admin_bar_editor_activate'));
}

