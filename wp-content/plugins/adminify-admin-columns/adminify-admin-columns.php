<?php

/**
 * Plugin Name: Adminify Admin Columns
 * Plugin URI:  https://wpadminify.com/modules/admin-columns/
 * Description: Admin Columns Editor for Customizing Post/Page/Users/Comments Columns
 * Version:     1.0.5
 * Author:      Jewel Theme
 * Author URI:  https://jeweltheme.com
 * Text Domain: adminify-admin-columns
 * Domain Path: languages/
 * License:     GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package adminify-admin-columns
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
$jlt_admin_columns_plugin_data = get_file_data( __FILE__, array(
    'Version'     => 'Version',
    'Plugin Name' => 'Plugin Name',
    'Author'      => 'Author',
    'Description' => 'Description',
    'Plugin URI'  => 'Plugin URI',
), false );
// Define Constants.
if ( !defined( 'ADMINCOLUMNS' ) ) {
    define( 'ADMINCOLUMNS', $jlt_admin_columns_plugin_data['Plugin Name'] );
}
if ( !defined( 'ADMINCOLUMNS_VER' ) ) {
    define( 'ADMINCOLUMNS_VER', $jlt_admin_columns_plugin_data['Version'] );
}
if ( !defined( 'ADMINCOLUMNS_AUTHOR' ) ) {
    define( 'ADMINCOLUMNS_AUTHOR', $jlt_admin_columns_plugin_data['Author'] );
}
if ( !defined( 'ADMINCOLUMNS_DESC' ) ) {
    define( 'ADMINCOLUMNS_DESC', $jlt_admin_columns_plugin_data['Author'] );
}
if ( !defined( 'ADMINCOLUMNS_URI' ) ) {
    define( 'ADMINCOLUMNS_URI', $jlt_admin_columns_plugin_data['Plugin URI'] );
}
if ( !defined( 'ADMINCOLUMNS_DIR' ) ) {
    define( 'ADMINCOLUMNS_DIR', __DIR__ );
}
if ( !defined( 'ADMINCOLUMNS_FILE' ) ) {
    define( 'ADMINCOLUMNS_FILE', __FILE__ );
}
if ( !defined( 'ADMINCOLUMNS_SLUG' ) ) {
    define( 'ADMINCOLUMNS_SLUG', dirname( plugin_basename( __FILE__ ) ) );
}
if ( !defined( 'ADMINCOLUMNS_BASE' ) ) {
    define( 'ADMINCOLUMNS_BASE', plugin_basename( __FILE__ ) );
}
if ( !defined( 'ADMINCOLUMNS_PATH' ) ) {
    define( 'ADMINCOLUMNS_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
}
if ( !defined( 'ADMINCOLUMNS_URL' ) ) {
    define( 'ADMINCOLUMNS_URL', trailingslashit( plugins_url( '/', __FILE__ ) ) );
}
if ( !defined( 'ADMINCOLUMNS_INC' ) ) {
    define( 'ADMINCOLUMNS_INC', ADMINCOLUMNS_PATH . '/Inc/' );
}
if ( !defined( 'ADMINCOLUMNS_LIBS' ) ) {
    define( 'ADMINCOLUMNS_LIBS', ADMINCOLUMNS_PATH . 'Libs' );
}
if ( !defined( 'ADMINCOLUMNS_ASSETS' ) ) {
    define( 'ADMINCOLUMNS_ASSETS', ADMINCOLUMNS_URL . 'assets/' );
}
if ( !defined( 'ADMINCOLUMNS_IMAGES' ) ) {
    define( 'ADMINCOLUMNS_IMAGES', ADMINCOLUMNS_ASSETS . 'images/' );
}
if ( !function_exists( 'jlt_admin_columns_fs' ) ) {
    // Create a helper function for easy SDK access.
    function jlt_admin_columns_fs() {
        global $jlt_admin_columns_fs;
        if ( !isset( $jlt_admin_columns_fs ) ) {
            // Include Freemius SDK.
            if ( file_exists( dirname( dirname( __FILE__ ) ) . '/adminify/Libs/freemius/start.php' ) ) {
                // Try to load SDK from parent plugin folder.
                require_once dirname( dirname( __FILE__ ) ) . '/adminify/Libs/freemius/start.php';
            } else {
                if ( file_exists( dirname( dirname( __FILE__ ) ) . '/adminify-pro/Libs/freemius/start.php' ) ) {
                    // Try to load SDK from premium parent plugin folder.
                    require_once dirname( dirname( __FILE__ ) ) . '/adminify-pro/Libs/freemius/start.php';
                } else {
                    require_once dirname( __FILE__ ) . '/Libs/freemius/start.php';
                }
            }
            $jlt_admin_columns_fs = fs_dynamic_init( array(
                'id'             => '15244',
                'slug'           => 'adminify-admin-columns',
                'type'           => 'plugin',
                'public_key'     => 'pk_97af6d53789cd91ceb3362f64209d',
                'is_premium'     => false,
                'has_paid_plans' => false,
                'parent'         => array(
                    'id'         => '7829',
                    'slug'       => 'adminify-admin-columns',
                    'public_key' => 'pk_a0ea61beae7126eb845f7e58a03e5',
                    'name'       => 'WP Adminify',
                ),
                'menu'           => array(
                    'account' => false,
                    'support' => false,
                ),
                'is_live'        => true,
            ) );
        }
        return $jlt_admin_columns_fs;
    }

}
// Maybe Load the Plugin
include_once ADMINCOLUMNS_DIR . '/loader.php';