<?php
		namespace JewelTheme\AdminBarEditor\Libs;

// No, Direct access Sir !!!
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Assets' ) ) {

	/**
	 * Assets Class
	 *
	 * Jewel Theme <support@jeweltheme.com>
	 * @version     1.0.2.3
	 */
	class Assets {

		/**
		 * Constructor method
		 *
		 * @author Jewel Theme <support@jeweltheme.com>
		 */
		public function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'jlt_admin_bar_editor_admin_enqueue_scripts' ), 99 );
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
		 * @method admin_enqueue_scripts()
		 */
		public function jlt_admin_bar_editor_admin_enqueue_scripts() {

			$admin_bar_page_slug  = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';
			if ( $admin_bar_page_slug ==='jlt_admin_bar_editor-settings') {
				// Fonts CSS
				wp_register_style('jlt-admin-bar-simple-line-icons', JLT_ADMIN_BAR_EDITOR_ASSETS . 'fonts/simple-line-icons/css/simple-line-icons.css', false, JLT_ADMIN_BAR_EDITOR_VER);
				wp_register_style('jlt-admin-bar-icomoon', JLT_ADMIN_BAR_EDITOR_ASSETS . 'fonts/icomoon/style.css', false, JLT_ADMIN_BAR_EDITOR_VER);
				wp_register_style('jlt-admin-bar-themify-icons', JLT_ADMIN_BAR_EDITOR_ASSETS . 'fonts/themify-icons/themify-icons.css', false, JLT_ADMIN_BAR_EDITOR_VER);

				// CSS Files .
				wp_enqueue_style('jlt-admin-bar-admin', JLT_ADMIN_BAR_EDITOR_ASSETS . 'css/admin-bar-admin.css', JLT_ADMIN_BAR_EDITOR_VER, 'all');


				// JS Files .
				wp_enqueue_script( 'jlt-admin-bar-admin', JLT_ADMIN_BAR_EDITOR_ASSETS . 'js/admin-bar-admin.js', array( 'jquery' ), JLT_ADMIN_BAR_EDITOR_VER, true );
				wp_enqueue_script( 'jlt-admin-bar-editor', JLT_ADMIN_BAR_EDITOR_ASSETS . 'js/admin-bar-editor.js', array(), JLT_ADMIN_BAR_EDITOR_VER, true );


				wp_localize_script(
					'jlt-admin-bar-admin',
					'JLT_ADMIN_BAR_EDITORCORE',
					array(
						'admin_ajax'        => admin_url( 'admin-ajax.php' ),
						'recommended_nonce' => wp_create_nonce( 'jlt_admin_bar_editor_recommended_nonce' ),
						'images'            => JLT_ADMIN_BAR_EDITOR_IMAGES,
						'is_premium'        => jlt_admin_bar_editor_is_premium(),
						'is_agency'         => jlt_admin_bar_editor_is_plan( 'agency' ),
					)
				);
			}

			wp_enqueue_style('jlt-admin-bar-sdk', JLT_ADMIN_BAR_EDITOR_ASSETS . 'css/admin-bar-sdk.min.css', array('dashicons'), JLT_ADMIN_BAR_EDITOR_VER, 'all');


		}
	}
}
