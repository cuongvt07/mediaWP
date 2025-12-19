<?php
namespace JewelTheme\AdminBarEditor;

use JewelTheme\AdminBarEditor\Libs\Assets;
use JewelTheme\AdminBarEditor\Libs\Helper;
use JewelTheme\AdminBarEditor\Inc\Utils;
use JewelTheme\AdminBarEditor\Libs\Featured;
use JewelTheme\AdminBarEditor\Inc\Classes\Core;
use JewelTheme\AdminBarEditor\Inc\Classes\Recommended_Plugins;
use JewelTheme\AdminBarEditor\Inc\Classes\Notifications\Notifications;
use JewelTheme\AdminBarEditor\Inc\Classes\Pro_Upgrade;
use JewelTheme\AdminBarEditor\Inc\Classes\Upgrade_Plugin;
use JewelTheme\AdminBarEditor\Inc\Classes\Feedback;

/**
 * Main Class
 *
 * @admin-bar
 * Jewel Theme <support@jeweltheme.com>
 * @version     1.0.2.3
 */

// No, Direct access Sir !!!
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AdminBarEditor Class
 */
if ( ! class_exists( '\JewelTheme\AdminBarEditor\AdminBarEditor' ) ) {

	/**
	 * Class: AdminBarEditor
	 */
	final class AdminBarEditor {

		const VERSION            = JLT_ADMIN_BAR_EDITOR_VER;
		private static $instance = null;

		/**
		 * construct method
		 *
		 * @author Jewel Theme <support@jeweltheme.com>
		 */
		public function __construct() {
			add_action( 'plugins_loaded', array( $this, 'jlt_admin_bar_editor_plugins_loaded' ));

			// Body Class.
			add_filter( 'admin_body_class', array( $this, 'jlt_admin_bar_editor_body_class' ) );
			add_filter( 'body_class', array( $this, 'jlt_admin_bar_editor_body_class' ) );

			// This should run earlier .
			// add_action( 'plugins_loaded', [ $this, 'jlt_admin_bar_editor_maybe_run_upgrades' ], -100 ); .

			if (Utils::is_plugin_active('adminify/adminify.php') || Utils::is_plugin_active('adminify-pro/adminify.php')) {
				add_action('admin_menu', [$this, 'jltwp_adminify_adminbar_submenu'], 13);
			} else {
				add_action( 'admin_menu', array( $this, 'settings_menu' ), 32);
			}
		}

		/**
		 * Adminify Sub Menu
		 */
		public function jltwp_adminify_adminbar_submenu(){
			$submenu_position = apply_filters('jltwp_adminify_submenu_position', 3);

			// If WP Adminify Plugin Installed then show on Sub Menu
			add_submenu_page(
				'wp-adminify-settings',
				__('Adminbar Editor Settings', 'admin-bar'),
				__('Adminbar Editor', 'admin-bar'),
				'manage_options',
				'jlt_admin_bar_editor' . '-settings', // Page slug, will be displayed in URL
				array($this, 'settings_page'),
				$submenu_position
			);
		}

		/**
		 * Register Main Menu.
		 *
		 * @return void
		 *
		 * @author Jewel Theme <support@jeweltheme.com>
		 */
		public function settings_menu() {

			// If WP Adminify Plugin not Installed then show on Main Menu
			add_menu_page(
				__( 'Adminbar Editor', 'admin-bar' ),
				__( 'Adminbar Editor', 'admin-bar' ),
				'manage_options',
				'jlt_admin_bar_editor' . '-settings',
				array( $this, 'settings_page' ),
				JLT_ADMIN_BAR_EDITOR_IMAGES . 'menu-icon.svg',
				40
			);

			add_submenu_page(
				'jlt_admin_bar_editor' . '-settings',
				__( 'Adminbar Editor Settings', 'admin-bar' ),
				__( 'Settings', 'admin-bar' ),
				'manage_options',
				'jlt_admin_bar_editor' . '-settings',
				array( $this, 'settings_page' ),
				10
			);
		}

		function settings_page() {
			echo '<div id="jlt-admin-bar-editor-root"></div>';
		}


		/**
		 * plugins_loaded method
		 *
		 * @author Jewel Theme <support@jeweltheme.com>
		 */
		public function jlt_admin_bar_editor_plugins_loaded() {
			new Assets();
			new Recommended_Plugins();
			new Pro_Upgrade();
			new Notifications();
			new Featured();
			new Feedback();
			new Core();
		}

		/**
		 * Version Key
		 *
		 * @author Jewel Theme <support@jeweltheme.com>
		 */
		public static function plugin_version_key() {
			return Helper::jlt_admin_bar_editor_slug_cleanup() . '_version';
		}

		/**
		 * Activation Hook
		 *
		 * @author Jewel Theme <support@jeweltheme.com>
		 */
		public static function jlt_admin_bar_editor_activate() {
			$current_jlt_admin_bar_editor_version = get_option( self::plugin_version_key(), null );

			if ( get_option( 'jlt_admin_bar_editor_activation_time' ) === false ) {
				update_option( 'jlt_admin_bar_editor_activation_time', strtotime( 'now' ) );
			}

			if ( is_null( $current_jlt_admin_bar_editor_version ) ) {
				update_option( self::plugin_version_key(), self::VERSION );
			}

			$allowed = get_option( Helper::jlt_admin_bar_editor_slug_cleanup() . '_allow_tracking', 'no' );

			// if it wasn't allowed before, do nothing .
			if ( 'yes' !== $allowed ) {
				return;
			}
			// re-schedule and delete the last sent time so we could force send again .
			$hook_name = Helper::jlt_admin_bar_editor_slug_cleanup() . '_tracker_send_event';
			if ( ! wp_next_scheduled( $hook_name ) ) {
				wp_schedule_event( time(), 'weekly', $hook_name );
			}
		}


		/**
		 * Add Body Class
		 *
		 * @param [type] $classes .
		 *
		 * @author Jewel Theme <support@jeweltheme.com>
		 */
		public function jlt_admin_bar_editor_body_class( $classes ) {
			// $classes .= ' jlt-admin-bar_editor '; // TODO: Need to change all classes
			if( is_array($classes)){
				array_push($classes, 'jlt-admin-bar' );
			} else {
				$classes .= ' jlt-admin-bar ';
			}
			return $classes;
		}

		/**
		 * Run Upgrader Class
		 *
		 * @return void
		 */
		public function jlt_admin_bar_editor_maybe_run_upgrades() {
			if ( ! is_admin() && ! current_user_can( 'manage_options' ) ) {
				return;
			}

			// Run Upgrader .
			$upgrade = new Upgrade_Plugin();

			// Need to work on Upgrade Class .
			if ( $upgrade->if_updates_available() ) {
				$upgrade->run_updates();
			}
		}


		/**
		 * Initialization
		 *
		 * @author Jewel Theme <support@jeweltheme.com>
		 */
		public function jlt_admin_bar_editor_init() {
			$this->jlt_admin_bar_editor_load_textdomain();
		}


		/**
		 * Text Domain
		 *
		 * @author Jewel Theme <support@jeweltheme.com>
		 */
		public function jlt_admin_bar_editor_load_textdomain() {
			add_action('init', function () {
				$domain = 'admin-bar';
				$locale = apply_filters('jlt_admin_bar_editor_plugin_locale', get_locale(), $domain);

				load_textdomain($domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo');
				load_plugin_textdomain( $domain, false, dirname( JLT_ADMIN_BAR_EDITOR_BASE ) . '/languages/' );
			});
		}

		/**
		 * Returns the singleton instance of the class.
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof AdminBarEditor ) ) {
				self::$instance = new AdminBarEditor();
				self::$instance->jlt_admin_bar_editor_init();
			}

			return self::$instance;
		}
	}

	// Get Instant of AdminBarEditor Class .
	AdminBarEditor::get_instance();
}
