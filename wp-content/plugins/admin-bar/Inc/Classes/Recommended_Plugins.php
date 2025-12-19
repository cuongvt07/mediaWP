<?php
namespace JewelTheme\AdminBarEditor\Inc\Classes;

use JewelTheme\AdminBarEditor\Libs\Recommended;

if ( ! class_exists( 'Recommended_Plugins' ) ) {
	/**
	 * Recommended Plugins class
	 *
	 * Jewel Theme <support@jeweltheme.com>
	 */
	class Recommended_Plugins extends Recommended {

		/**
		 * Constructor method
		 */
		public function __construct() {
			$this->menu_order = 15; // for submenu order value should be more than 10 .
			parent::__construct( $this->menu_order );
		}

		/**
		 * Menu list
		 */
		public function menu_items() {
			return array(
				array(
					'key'   => 'all',
					'label' => 'All',
				),
				array(
					'key'   => 'featured', // key should be used as category to the plugin list.
					'label' => 'Featured Item',
				),
				array(
					'key'   => 'popular',
					'label' => 'Popular',
				),
				array(
					'key'   => 'favorites',
					'label' => 'Favorites',
				),
			);
		}

		/**
		 * Plugins List
		 *
		 * @author Jewel Theme <support@jeweltheme.com>
		 */
		public function plugins_list() {
			return array(
                
			);
		}

		/**
		 * Admin submenu
		 */
		public function admin_menu() {
            // For submenu .
			$this->sub_menu = add_submenu_page(
				'jlt_admin_bar_editor',       // Ex. admin-bar-settings /  edit.php?post_type=page .
				__( 'Recommended', 'admin-bar' ),
				__( 'Recommended', 'admin-bar' ),
				'manage_options',
				'admin-bar-recommended-plugins',
				array( $this, 'render_recommended_plugins' )
			);
		}
	}
}