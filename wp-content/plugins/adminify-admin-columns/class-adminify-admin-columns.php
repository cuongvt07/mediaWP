<?php
namespace WPAdminify\Modules\AdminColumns;

use WPAdminify\Modules\AdminColumns\Libs\Helper;
use WPAdminify\Modules\AdminColumns\Inc\Classes\Notifications\Notifications;
use WPAdminify\Modules\AdminColumns\Inc\Classes\Pro_Upgrade;
use WPAdminify\Modules\AdminColumns\Inc\Classes\Upgrade_Plugin;
use WPAdminify\Modules\AdminColumns\Inc\Classes\Feedback;
use WPAdminify\Modules\AdminColumns\Inc\AdminColumns\Module_AdminColumns;

/**
 * Main Class
 *
 * @Adminify Admin Columns
 * Jewel Theme <support@jeweltheme.com>
 * @version     1.0.0
 */

// No, Direct access Sir !!!
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class: AdminColumns
 */
final class AdminColumns {

	const VERSION            = ADMINCOLUMNS_VER;
	private static $instance = null;
	public $url;

	/**
	 * what we collect construct method
	 *
	 * @author Jewel Theme <support@jeweltheme.com>
	 */
	public function __construct() {
		$this->includes();
		add_action( 'plugins_loaded', array( $this, 'jlt_admin_columns_plugins_loaded' ), 999 );

		// Body Class.
		add_filter( 'admin_body_class', array( $this, 'jlt_admin_columns_body_class' ) );
	}

	/**
	 * plugins_loaded method
	 *
	 * @author Jewel Theme <support@jeweltheme.com>
	 */
	public function jlt_admin_columns_plugins_loaded() {
		$this->jlt_admin_columns_activate();
	}

	/**
	 * Version Key
	 *
	 * @author Jewel Theme <support@jeweltheme.com>
	 */
	public static function plugin_version_key() {
		return Helper::jlt_admin_columns_slug_cleanup() . '_version';
	}

	/**
	 * Activation Hook
	 *
	 * @author Jewel Theme <support@jeweltheme.com>
	 */
	public static function jlt_admin_columns_activate() {
		$current_jlt_admin_columns_version = get_option( self::plugin_version_key(), null );

		if ( get_option( 'jlt_admin_columns_activation_time' ) === false ) {
			update_option( 'jlt_admin_columns_activation_time', strtotime( 'now' ) );
		}

		if ( is_null( $current_jlt_admin_columns_version ) ) {
			update_option( self::plugin_version_key(), self::VERSION );
		}

		$allowed = get_option( Helper::jlt_admin_columns_slug_cleanup() . '_allow_tracking', 'no' );

		// if it wasn't allowed before, do nothing .
		if ( 'yes' !== $allowed ) {
			return;
		}
		// re-schedule and delete the last sent time so we could force send again .
		$hook_name = Helper::jlt_admin_columns_slug_cleanup() . '_tracker_send_event';
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
	public function jlt_admin_columns_body_class( $classes ) {
		$classes .= ' adminify-admin-columns ';
		return $classes;
	}

	/**
	 * Include methods
	 *
	 * @author Jewel Theme <support@jeweltheme.com>
	 */
	public function includes() {
		new Pro_Upgrade();
		new Notifications();
		new Feedback();
		new Module_AdminColumns();
	}


	/**
	 * Initialization
	 *
	 * @author Jewel Theme <support@jeweltheme.com>
	 */
	public function jlt_admin_columns_init() {
		$this->jlt_admin_columns_load_textdomain();
	}

	/**
	 * Text Domain
	 *
	 * @author Jewel Theme <support@jeweltheme.com>
	 */
	public function jlt_admin_columns_load_textdomain() {
		$domain = 'adminify-admin-columns';
		$locale = apply_filters( 'jlt_admin_columns_plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, false, dirname( ADMINCOLUMNS_BASE ) . '/languages/' );
	}

	/**
	 * Returns the singleton instance of the class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof AdminColumns ) ) {
			self::$instance = new AdminColumns();
			self::$instance->jlt_admin_columns_init();
		}

		return self::$instance;
	}
}

AdminColumns::get_instance();
