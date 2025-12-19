<?php
		namespace WPAdminify\Modules\AdminColumns\Libs;

// No, Direct access Sir !!!
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Assets' ) ) {

	/**
	 * Assets Class
	 *
	 * Jewel Theme <support@jeweltheme.com>
	 * @version     1.0.0
	 */
	class Assets {

		/**
		 * Constructor method
		 *
		 * @author Jewel Theme <support@jeweltheme.com>
		 */
		public function __construct() {
			// add_action( 'wp_enqueue_scripts', array( $this, 'jlt_admin_columns_enqueue_scripts' ), 100 );
			add_action( 'admin_enqueue_scripts', array( $this, 'jlt_admin_columns_admin_enqueue_scripts' ), 100 );
		}


		/**
		 * Get environment mode
		 *
		 * @author Jewel Theme <support@jeweltheme.com>
		 */
		public function get_mode() {
			return defined( 'WP_DEBUG' ) && WP_DEBUG ? 'development' : 'production';
		}

		/**
		 * Enqueue Scripts
		 *
		 * @method wp_enqueue_scripts()
		 */
		public function jlt_admin_columns_enqueue_scripts() {

			// CSS Files .
			wp_enqueue_style( 'adminify-admin-columns-frontend', ADMINCOLUMNS_ASSETS . 'css/adminify-admin-columns-frontend.css', ADMINCOLUMNS_VER, 'all' );

			// JS Files .
			wp_enqueue_script( 'adminify-admin-columns-frontend', ADMINCOLUMNS_ASSETS . 'js/adminify-admin-columns-frontend.js', array( 'jquery' ), ADMINCOLUMNS_VER, true );
		}


		/**
		 * Enqueue Scripts
		 *
		 * @method admin_enqueue_scripts()
		 */
		public function jlt_admin_columns_admin_enqueue_scripts() {
			// CSS Files .
			wp_enqueue_style( 'adminify-admin-columns-admin', ADMINCOLUMNS_ASSETS . 'css/adminify-admin-columns-sdk.min.css', array( 'dashicons' ), ADMINCOLUMNS_VER, 'all' );

			// JS Files .
			// wp_enqueue_script( 'adminify-admin-columns-admin', ADMINCOLUMNS_ASSETS . 'js/adminify-admin-columns-admin.js', array( 'jquery' ), ADMINCOLUMNS_VER, true );
			// wp_localize_script(
			// 	'adminify-admin-columns-admin',
			// 	'ADMINCOLUMNSCORE',
			// 	array(
			// 		'admin_ajax'        => admin_url( 'admin-ajax.php' ),
			// 		'recommended_nonce' => wp_create_nonce( 'jlt_admin_columns_recommended_nonce' ),
			// 	)
			// );
		}
	}
}
