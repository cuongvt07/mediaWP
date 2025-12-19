<?php

namespace JewelTheme\AdminBarEditor\Pro;

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
 * AdminBarEditorPro Class
 */
if ( ! class_exists('\JewelTheme\AdminBarEditor\Pro\AdminBarEditorPro' ) ) {

    class AdminBarEditorPro
    {

        private static $instance = null;


        /**
         * construct method
         *
         * @author Jewel Theme <support@jeweltheme.com>
         */

        public function __construct()
        {
            if (!$this->jlt_admin_bar_editor_installed() || !$this->jlt_admin_bar_editor_activated()) {
                add_action('admin_notices', array($this, 'jlt_admin_bar_editor_notice_missing_main_plugin'));
            } else {
                add_action('plugins_loaded', array($this, 'jlt_admin_bar_editor_dependencies'), -999999);
                add_action('plugins_loaded', array($this, 'jlt_admin_bar_editor_includes'), 0);
            }
        }

        /**
         * Dependecies
         *
         * @return void
         */
        public function jlt_admin_bar_editor_dependencies()
        {
            require_once JLT_ADMIN_BAR_PRO_DIR . 'autoloader-pro.php';
            require_once JLT_ADMIN_BAR_PRO_DIR . 'Pro/License/manager.php';
        }

        public function jlt_admin_bar_editor_includes()
        {
            if (!$this->jlt_admin_bar_editor_activated()) {
                return;
            }
            Assets_Pro::instance();
            Admin_Bar_Pro::instance();
        }


        /**
         * Check is Plugin Active
         *
         * @param [type] $plugin_path
         *
         * @return boolean
         */
        public function jlt_admin_bar_editor_activated($plugin_path = 'admin-bar/admin-bar.php')
        {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
            return is_plugin_active($plugin_path);
        }


        // Function to check if a plugin is installed
        public function jlt_admin_bar_editor_installed($plugin_slug = 'admin-bar/admin-bar.php')
        {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
            $plugins = get_plugins();
            foreach ($plugins as $plugin_file => $plugin_data) {
                if (strpos($plugin_file, $plugin_slug) !== false) {
                    return true;
                }
            }
            return false;
        }



        /**
         * Install Required Admin Bar Editor Core Plugin
         */
        public function jlt_admin_bar_editor_notice_missing_main_plugin()
        {

            $plugin = 'admin-bar/admin-bar.php';
            if (!$this->jlt_admin_bar_editor_installed()) {

                if (!current_user_can('install_plugins')) {
                    return;
                }

                $install_activation_url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=admin-bar'), 'install-plugin_admin-bar');
                $message = /* translators: 1: strong start tag, 2: strong end tag. */ sprintf(__('<b>Admin Bar Editor Pro</b> requires %1$s"Admin Bar Editor"%2$s plugin to be installed and activated. Please install Admin Bar Editor to continue.', 'admin-bar'), '<strong>', '</strong>');
                $button_text = __('Install Admin Bar Editor', 'admin-bar');
            } elseif (!$this->jlt_admin_bar_editor_activated($plugin)) {

                if (!current_user_can('activate_plugins')) {
                    return;
                }
                $install_activation_url = wp_nonce_url('plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin);
                $message = __('<b>Admin Bar Editor Pro</b> requires <strong>Admin Bar Editor</strong> plugin to be active. Please activate Admin Bar Editor to continue.', 'admin-bar');
                $button_text = __('Activate Admin Bar Editor', 'admin-bar');
            }

            $button = '<p><a href="' . esc_url($install_activation_url) . '" class="button-primary">' . esc_html($button_text) . '</a></p>';

            printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p>%2$s</div>', $message, $button);
        }



        public static function is_premium()
        {
            if (jlt_admin_bar_editor_license_client()->is_premium()) {
                return true;
            }
            return false;
        }
        public static function is_plan($plan_name)
        {
            if (jlt_admin_bar_editor_license_client()->is_plan($plan_name)) {
                return true;
            }
            return false;
        }


    	/**
		 * Returns the singleton instance of the class.
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof AdminBarEditorPro ) ) {
				self::$instance = new AdminBarEditorPro();
			}

			return self::$instance;
		}
    }

    // Get Instant of AdminBarEditorPro Class .
    AdminBarEditorPro::get_instance();
}
