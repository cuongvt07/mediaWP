<?php

namespace JewelTheme\AdminBarEditor\Inc\Classes;

use JewelTheme\AdminBarEditor\Inc\Classes\AdminBarEditorApiEndPoints;
use JewelTheme\AdminBarEditor\Inc\Classes\AdminBarEditorAssets;
use JewelTheme\AdminBarEditor\Inc\Classes\AdminBarEditorModel;
use JewelTheme\AdminBarEditor\Inc\Classes\AdminBarEditorOptions;
use JewelTheme\AdminBarEditor\Inc\Classes\Multisite_Helper;
use JewelTheme\AdminBarEditor\Inc\Utils;

// no direct access allowed
if (!defined('ABSPATH'))  exit;

/**
 * Jewel Theme
 * @package Jewel Theme: Admin Bar Editor
 *
 * @author Jewel Theme <support@jeweltheme.com>
 */

if (!class_exists('Core')) {
    class Core extends AdminBarEditorModel
    {
        public $adminify_ui;
        public $admin_bar_options;
        private $original_nodes = [];
        private $current_admin_bar_nodes = [];

        public function __construct()
        {

            $this->admin_bar_options  = (new AdminBarEditorOptions())->get();

            new AdminBarEditorApiEndPoints();
            new AdminBarEditorAssets();
            $this->initialization();

            add_action('init', array($this, 'jltwp_adminbar_frontend'));
            add_action('admin_init', array($this, 'jltwp_adminbar_backend'));
        }

        public function initialization(){
            add_action('wp_before_admin_bar_render', [$this, 'get_admin_bar_menu_list'], 999999);
            add_action('wp_before_admin_bar_render', [$this, 'admin_bar_menu_output'], 1000000);
        }

        // Get admin bar disable status based on disable conditions
        function get_adminbar_disable_status($disable_conditions)
        {
            global $current_user;

            $user_roles = $current_user->roles;

            if(!empty($current_user->data->display_name)){
                $user_name = $current_user->data->display_name;
            } else {
                $user_name = !empty($current_user->data->user_nicename) ? $current_user->data->user_nicename : '';
            }

            $disable_status = false;

            if (!empty($disable_conditions)) {
                foreach ($disable_conditions as $key => $cond) {
                    if ($cond['condition'] == 'user_role') {
                        if (!empty($cond['conditionValue']) && !empty($user_roles)) {
                            $matched_roles = array_intersect($user_roles, $cond['conditionValue']);

                            if (!empty($matched_roles)) {
                                $disable_status = true;
                                break;
                            }
                        }
                    } else if ($cond['condition'] == 'user_name') {
                        if (!empty($cond['conditionValue']) && in_array($user_name, $cond['conditionValue'])) {
                            $disable_status = true;
                            break;
                        }
                    } else if ($cond['condition'] == 'user_cap') {
                        if (!empty($cond['conditionValue'])) {
                            foreach ($cond['conditionValue'] as $key => $user_cap) {
                                if (current_user_can($user_cap)) {
                                    $disable_status = true;
                                    break;
                                }
                            }
                        }
                    }

                    if ($disable_status == true) {
                        break;
                    }
                }
            }

            return $disable_status;
        }

        /**
         * Hide Backend Admin Bar
         */
        public function jltwp_adminbar_backend()
        {

            $disable_backend_admin_bar = !empty($this->admin_bar_options['disable_backend_admin_bar']) ? $this->admin_bar_options['disable_backend_admin_bar'] : '';

            $disable_conditions = !empty($this->admin_bar_options['disable_backend_conditions']) ? $this->admin_bar_options['disable_backend_conditions'] : '';

            $is_disable_adminbar = $this->get_adminbar_disable_status($disable_conditions);

            if (is_admin() && $disable_backend_admin_bar && $is_disable_adminbar) {
                add_filter('show_admin_bar', '__return_false');
            }
        }


        public function sync_with_adminbar_remover(){

            // Already Synceed? Bail Early
            if (get_option($this->prefix . '_is_synced', false)) return;

            // Settings Class
            $admin_bar_prev_option = empty( get_option('show-ab') ) ? true : false;
            // Update New Options
            $this->admin_bar_options['disable_frontend_admin_bar'] = $admin_bar_prev_option;

            // Save The Settings
            update_option($this->prefix, $this->admin_bar_options);

            // Operation Done
            update_option($this->prefix . '_is_synced', true);
        }

        /**
         * Hide Frontend Admin Bar
         */
        public function jltwp_adminbar_frontend()
        {
            $disable_frontend_admin_bar = !empty($this->admin_bar_options['disable_frontend_admin_bar']) ? $this->admin_bar_options['disable_frontend_admin_bar'] : '';
            $disable_frontend_all_users = !empty($this->admin_bar_options['disable_frontend_all_users']) ? $this->admin_bar_options['disable_frontend_all_users'] : '';
            $disable_frontend_guest_users = !empty($this->admin_bar_options['disable_frontend_guest_users']) ? $this->admin_bar_options['disable_frontend_guest_users'] : '';
            $disable_conditions = !empty($this->admin_bar_options['disable_frontend_conditions']) ? $this->admin_bar_options['disable_frontend_conditions'] : [];

            $user = wp_get_current_user();
		    $user_roles = Utils::get_user_roles($user);
            $user_caps = Utils::get_user_capabilities($user);


            if( !empty($disable_frontend_all_users) ) {
                show_admin_bar( false );
            } else {
                
            }

            if( !empty($disable_frontend_guest_users) && !is_user_logged_in() ) {
                show_admin_bar( false );
            }
        }

        public function get_frontend_items()
        {
            global $wp_admin_bar;

            // Save frontend items whenever visit the frontend
            if (!is_admin() && is_admin_bar_showing()) {
                update_option('adminbar_frontend_items', $wp_admin_bar->get_nodes());
                return false;
            }

            $frontend_items = get_option('adminbar_frontend_items');

            // If no frontend item then request to save frontend items.
            if (is_admin() && is_admin_bar_showing() && empty($frontend_items)) {
                printf('<iframe style="display:none!important" src="%s"></iframe>', home_url());
                return false;
            }

            return $frontend_items;
        }

        public function get_admin_bar_menu_list()
        {
            global $wp_admin_bar;

            $syned = false;
            if( is_admin()){
                $previous_admin_bar = get_option('previous_admin_bar_backend');
                update_option('previous_admin_bar_backend', $wp_admin_bar);
            }else{
                $previous_admin_bar = get_option('previous_admin_bar_frontend');
                update_option('previous_admin_bar_frontend', $wp_admin_bar);
            }
            if($previous_admin_bar  === $wp_admin_bar){
                $syned = true;
                update_option($this->prefix . '_is_synced', true);
            }else{
                update_option($this->prefix . '_is_synced', false);
            }
            
            $this->current_admin_bar_nodes = $wp_admin_bar->get_nodes();
            // if (is_admin()) {
            //     $wp_admin_bar->remove_menu('menu-toggle');
            //     $wp_admin_bar->remove_menu('wp-logo');
            //     $wp_admin_bar->remove_menu('site-name');
            //     $wp_admin_bar->remove_menu('updates');
            //     $wp_admin_bar->remove_menu('comments');
            //     $wp_admin_bar->remove_menu('my-account');
            // }

            // $frontend_items = $this->get_frontend_items();
            // if ($frontend_items === false) return;

            $admin_bar_items                           = (new AdminBarEditorOptions())->get();

            $user_roles                                = $this->get_users_list();

            $admin_bars['existing_admin_bar']          = $this->nodes_to_array($wp_admin_bar->get_nodes(), 'backend');

            if (is_admin()) {
                $existing_admin_bar                        = self::format_to_nested($admin_bars['existing_admin_bar']);
                $existing_admin_bar                        = self::clean_array($existing_admin_bar);
                $admin_bars['existing_admin_bar']          = self::assoc_to_flat_array($existing_admin_bar);
            }

            // $admin_bars['existing_admin_bar_frontend'] = $this->nodes_to_array($frontend_items, 'frontend');
            $admin_bars['existing_admin_bar_frontend'] = $this->nodes_to_array($wp_admin_bar->get_nodes(), 'frontend');
            $admin_bars['user_roles']                  = $user_roles;
            
            // Admin Bar Styles
            $admin_bars['admin_bar_settings']       = !empty( $admin_bar_items['admin_bar_settings'] ) ? $admin_bar_items['admin_bar_settings'] : [];


            // Add disable adminbar keys
            $admin_bars['disable_backend_admin_bar'] = isset($admin_bar_items['disable_backend_admin_bar']) ? $admin_bar_items['disable_backend_admin_bar'] : 0;
            $admin_bars['disable_backend_conditions'] = isset($admin_bar_items['disable_backend_conditions']) ? $admin_bar_items['disable_backend_conditions'] : array();

            $admin_bars['disable_frontend_admin_bar'] = isset($admin_bar_items['disable_frontend_admin_bar']) ? $admin_bar_items['disable_frontend_admin_bar'] : 0;
            $admin_bars['disable_frontend_conditions'] = isset($admin_bar_items['disable_frontend_conditions']) ? $admin_bar_items['disable_frontend_conditions'] : array();

            // If new menu item added or removed by theme/plugin activation/deactivation
            if (!array_key_exists('existing_admin_bar', $admin_bar_items) || (array_keys($admin_bar_items['existing_admin_bar']) !== array_keys($admin_bars['existing_admin_bar']))) {

                $admin_bars['saved_admin_bar']          = !empty($admin_bar_items['saved_admin_bar']) ? $admin_bar_items['saved_admin_bar'] : [];

                $admin_bars['saved_admin_bar_frontend'] = !empty($admin_bar_items['saved_admin_bar_frontend']) ? $admin_bar_items['saved_admin_bar_frontend'] : [];
                update_option($this->prefix, $admin_bars);
            }


            $adminbar_remover_key_exists = (get_option('show-ab', null) !== null);
            if( $adminbar_remover_key_exists ){

                // Already Synceed? Bail Early
                if (get_option($this->prefix . '_is_synced', false)) return;

                // Settings Class
                $admin_bar_prev_option = empty( get_option('show-ab') ) ? true : false;

                // Update New Options
                $admin_bars['disable_frontend_admin_bar'] = $admin_bar_prev_option;

                // Save The Settings
                update_option($this->prefix, $admin_bars);

                // Operation Done
                update_option($this->prefix . '_is_synced', true);
            }

        }

        public static function clean_array($array)
        {
            $formated_array = [];
            $to_be_removed = ['comments', 'wp-logo', 'site-name', 'updates', 'menu-toggle', 'my-account'];

            foreach ($array as $key => $value) {
                if (in_array($value['parent'], $to_be_removed)) {
                    continue;
                }
                $formated_array[$key] = $value;
            }
            return  $formated_array;
        }


        public static function assoc_to_flat_array($menu_array)
        {
            $new_array = [];
            foreach ($menu_array as $root_menu) {

                $root_submenu = !empty($root_menu['submenu']) ? $root_menu['submenu'] : [];
                unset($root_menu['submenu']);
                $new_array[$root_menu['id']] = $root_menu;
                if (isset($root_submenu) && (count($root_submenu) > 0)) {
                    foreach ($root_submenu as $first_level_menu) {

                        $first_level_submenu = !empty($first_level_menu['submenu']) ? $first_level_menu['submenu'] : [];
                        unset($first_level_menu['submenu']);
                        $new_array[$first_level_menu['id']] = $first_level_menu;
                        if (isset($first_level_submenu) && (count($first_level_submenu) > 0)) {
                            foreach ($first_level_submenu as $second_level_menu) {

                                $second_level_submenu = !empty($second_level_menu['submenu']) ? $second_level_menu['submenu'] : [];
                                unset($second_level_menu['submenu']);
                                $new_array[$second_level_menu['id']] = $second_level_menu;
                                if (isset($second_level_submenu) && (count($second_level_submenu) > 0)) {
                                    foreach ($second_level_submenu as $third_level_menu) {

                                        $third_level_submenu = !empty($third_level_menu['submenu']) ? $third_level_menu['submenu'] : [];
                                        unset($third_level_menu['submenu']);
                                        $new_array[$third_level_menu['id']] = $third_level_menu;
                                        if (isset($third_level_submenu) && (count($third_level_submenu) > 0)) {
                                            foreach ($third_level_submenu as $fourth_level_menu) {

                                                $third_level_submenu = !empty($fourth_level_menu['submenu']) ? $fourth_level_menu['submenu'] : [];
                                                unset($fourth_level_menu['submenu']);
                                                $new_array[$fourth_level_menu['id']] = $fourth_level_menu;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return $new_array;
        }

        public static function parse_menu_items($args, $default, $request_from = 'backend')

        {

            $instance     = new self();
            $ms_helper    = new Multisite_Helper();
            $mulsite_site = $ms_helper->is_network_active();

            // parse menu
            if (empty($instance->adminify_ui)) {
                if ($request_from == 'backend') {
                    $new_array    = [];
                    foreach ($default as $key => $menu) {
                        // if($args[$key]['id'] == 'my-account'){
                        //     continue;
                        // }

                        if (array_search($key, array_keys($args)) && $key == $args[$key]['id']) {
                            // $new_array[$key] = $args[$key];
                        } else {
                            if (($mulsite_site) && ($key == 'my-sites')) {
                                $new_array[$key] = $args[$key];
                                unset($args[$key]);
                            } else {
                                $new_array[$key] = $menu;
                            }
                        }
                    }



                    $new_args = [];
                    foreach ($args as $key => $menu) {
                        if (str_contains($key, 'custom-menu-')) {
                            $new_args[$key] = $menu;
                        }
                        if (!array_key_exists($key, $default)) {
                            continue;
                        }
                        $new_args[$key] = $args[$key];
                    }

                    $pos  = array_search('comments', array_keys($new_array), true);
                    // $pos += 1;

                    unset($new_array['top-secondary']);

                    // $new_array = array_slice($new_array, 0, $pos, true) + $new_args + array_slice($new_array, $pos, count($new_array) - 1, true);
                    $new_array = $new_args + array_slice($new_array, $pos, count($new_array), true);

                    return $new_array;
                } else if ($request_from == 'frontend') {
                    $new_array    = [];
                    foreach ($args as $key => $menu) {
                        if (str_contains($key, 'custom-menu-')) {
                            $new_array[$key] = $menu;
                        }
                        if (!array_key_exists($key, $default)) {
                            unset($default[$key]);
                            continue;
                        }
                        $new_array[$key] = $args[$key];
                        unset($default[$key]);
                    }
                    if (!empty($default)) {
                        foreach ($default as $key => $value) {
                            $new_array[$key] = $value;
                        }
                    }
                    return $new_array;
                }
            } else {
                $new_array    = [];
                foreach ($args as $key => $menu) {
                    if (str_contains($key, 'custom-menu-')) {
                        $new_array[$key] = $menu;
                    }
                    if (!array_key_exists($key, $default)) {
                        unset($default[$key]);
                        continue;
                    }
                    $new_array[$key] = $args[$key];
                    unset($default[$key]);
                }
                if (!empty($default)) {
                    foreach ($default as $key => $value) {
                        $new_array[$key] = $value;
                    }
                }

                return $new_array;
            }
        }

        public function admin_bar_menu_output()
        {
            global $wp_admin_bar;

            $existing_menu = $wp_admin_bar->get_nodes();
            $this->original_nodes = $existing_menu;

            $admin_bar_items = (new AdminBarEditorOptions())->get();


            if (is_admin()) {
                $existing_admin_bar = !empty($admin_bar_items['existing_admin_bar']) ? $admin_bar_items['existing_admin_bar'] : [];

                $saved_admin_bar    = !empty($admin_bar_items['saved_admin_bar']) ? $admin_bar_items['saved_admin_bar'] : $existing_admin_bar;

                $parsed_menu        = empty($saved_admin_bar) ? $existing_admin_bar : self::parse_menu_items($saved_admin_bar, $existing_admin_bar, 'backend');
            } else {
                $existing_admin_bar_frontend = !empty($admin_bar_items['existing_admin_bar_frontend']) ? $admin_bar_items['existing_admin_bar_frontend'] : [];
                $saved_admin_bar_frontend    = !empty($admin_bar_items['saved_admin_bar_frontend']) ? $admin_bar_items['saved_admin_bar_frontend'] : $existing_admin_bar_frontend;
                $parsed_menu        = empty($saved_admin_bar_frontend) ? $existing_admin_bar_frontend : self::parse_menu_items($saved_admin_bar_frontend, $existing_admin_bar_frontend, 'frontend');
            }
            // Remove all nodes.
            foreach ($existing_menu as $node_id => $node) {
                if( array_key_exists($node->id, $parsed_menu ) ){
                    if( isset($node->href) && !empty($node->href)){
                        $parsed_menu[$node->id]['href'] = $node->href;
                    }
                }
                $wp_admin_bar->remove_node($node_id);
            }

            // Convert $parsed_menu to array of nodes.
            if (is_array($parsed_menu) && count($parsed_menu) > 0) {
                $this->generate_nodes($parsed_menu);
            }
        }


        /**
         * Generate WP_Admin_Bar nodes based on parsed menu.
         *
         * @param array $parsed_menu The parsed menu in flat array format.
         */
        public function generate_nodes($parsed_menu)
        {
            global $wp_admin_bar;

            $nodes = array();

            $user  = wp_get_current_user();
            $roles = $user->roles;
            if( in_array('my-account', array_keys($parsed_menu))){
                $account_items = $parsed_menu['my-account'];
                unset($parsed_menu['my-account']);
                $parsed_menu['my-account'] = $account_items;
            }

            foreach ($parsed_menu as $menu_id => $menu) {
                
                
                // if existing title_default then title_default
                // if not existing saved_title_default then saved_title_default
                $howdy_title = '';
                $custom_howdy_title = 0;
                if( is_admin()){
                    if( !empty($this->admin_bar_options['saved_admin_bar']['my-account']['title'] ) ){
                        $howdy_title = $this->admin_bar_options['saved_admin_bar']['my-account']['title'];
                    } elseif( !empty($this->admin_bar_options['existing_admin_bar']['my-account']['title_default'] ) ){
                        $howdy_title = $this->admin_bar_options['existing_admin_bar']['my-account']['title_default'];
                    }
                }else{
                    if( !empty($this->admin_bar_options['saved_admin_bar_frontend']['my-account']['title'] ) ){
                        $howdy_title = $this->admin_bar_options['saved_admin_bar_frontend']['my-account']['title'];
                    } elseif( !empty($this->admin_bar_options['existing_admin_bar_frontend']['my-account']['title_default'] ) ){
                        $howdy_title = $this->admin_bar_options['existing_admin_bar_frontend']['my-account']['title_default'];
                    }
                }
                if (strpos($howdy_title, '<span class="display-name"') !== false) {
                    $custom_howdy_title = 1;
                }

                if( $menu_id === 'my-account' ){
                    $current_user  = wp_get_current_user();
                    $user_id       = get_current_user_id();
                    $avatar        = get_avatar($user_id, 26);     // size 26x26 pixels
                    $display_name  = $current_user->display_name;
                    if($custom_howdy_title){
                        $menu['title'] = $howdy_title; 
                    }else{
                        $menu['title'] = $howdy_title . ' ' . $display_name . $avatar; 
                    }
                }
                if($menu_id === 'user-info') {
                    $current_user  = wp_get_current_user();
                    $user_id       = get_current_user_id();
                    $avatar        = get_avatar($user_id, 26);                        // size 26x26 pixels
                    $display_name  = $current_user->display_name;
                    $menu['title_default'] = $avatar . $display_name . '<span class="display-name edit-profile">Edit Profile</span>';
                    $menu['title_encoded_default'] = htmlspecialchars($menu['title_default'], ENT_QUOTES, 'UTF-8');
                }

                $args = array();

                $role_allowed = true;
                $user_allowed = true;

                if ($role_allowed && $user_allowed && $menu['menu_status']) {

                    foreach ($menu as $arg_key => $arg_value) {
                        if ( false === stripos($arg_key, '_default') && 'newly_created' !== $arg_key && 'icon' !== $arg_key ) {
                            $value = $arg_value;

                            // For non-custom menu items, always use fresh href from original nodes
                            if ($arg_key === 'href') {
                                if (isset($menu['newly_created']) && $menu['newly_created'] == 1) {
                                    // Custom menu item - use the saved href
                                    $value = $arg_value;
                                } else {
                                    // Non-custom menu item - get fresh URL from original nodes
                                    if (isset($this->original_nodes[$menu['id']])) {
                                        $value = $this->original_nodes[$menu['id']]->href;
                                    } elseif (isset($menu['href_default'])) {
                                        $value = $menu['href_default'];
                                    } else {
                                        $value = '';
                                    }
                                }
                            } elseif ('' === $value) {
                                if ($arg_key != 'hidden_for') {
                                    $value = $menu[$arg_key . '_default'];
                                }
                            }
                            $args[$arg_key] = $value;
                        }
                    }

                    if (isset($menu['icon']) && !empty($menu['icon'])) {
                        $icon_font = '';
                        if (str_contains($menu['icon'], 'icomoon-')) {
                            $icon_font = 'style="font-family:icomoon"';
                        }
                        if (str_contains($menu['icon'], 'ti-')) {
                            $icon_font = 'style="font-family:themify"';
                        }
                        if (str_contains($menu['icon'], 'icon-')) {
                            $icon_font = 'style="font-family:simple-line-icons"';
                        }
                        if (str_contains($menu['icon'], 'eicon-')) {
                            $icon_font = 'style="font-family:eicons"';
                        }
                        $class = 'ab-label';
                        if ($menu['id'] == 'wp-logo' && ($menu['title'] == '')) {
                            $class = 'screen-reader-text';
                        }
                        if (str_contains($args['title'], '<span')) {
                            $label = $args['title'];
                        } else {
                            $label = '<span class="' . $class . '"> ' . strip_tags($args['title']) . ' </span>';
                        }
                        $args['title'] = '
                            <div class="ab-item jlt-admin-bar-menu">
                                <span class="ab-icon ' . $menu['icon'] . '" ' . $icon_font . '></span>
                                ' . $label . '
                            </div>
                        ';

                        if (str_contains($menu['icon'], '/adminify-custom-icons/')) {
                            $args['title'] = '
                                <div class="ab-item jlt-admin-bar-menu">
                                    <img style="width:20px;position: relative;" src="' . $menu['icon'] . '"/>
                                    ' . $label . '
                                </div>
                            ';
                        }
                    } else {
                        if ($menu['title'] && str_contains($menu['title_default'], '<span')) {
                            $args['title'] =  '<span class="ab-icon" aria-hidden="true"></span> &nbsp;<span>' . $args['title'] . '</span>';
                        }
                    }

                    if (isset($menu['hidden_for']) && !empty($menu['hidden_for']) && is_array($menu['hidden_for'])) {
                        $users = [];

                        foreach ($menu['hidden_for'] as $key => $value) {
                            if (strtolower($value['value']) == 'seo manager') {
                                $users[] = 'wpseo_manager';
                            } elseif (strtolower($value['value']) == 'seo editor') {
                                $users[] = 'wpseo_editor';
                            } else {
                                $users[] = $value['value'];
                            }
                        }

                        if ($this->is_hidden($users)) {
                            $args = [];
                        }
                    }
                }

                $wp_admin_bar->add_node($args);
            }
        }

        public function is_hidden($disabled_for)
        {
            if (!is_array($disabled_for)) {
                return false;
            }

            $current_user = wp_get_current_user();
            $current_name = $current_user->display_name;
            $current_roles = $current_user->roles;
            $all_roles = wp_roles()->get_names();

            if (in_array($current_name, $disabled_for)) {
                return true;
            }


            ///MULTISITE SUPER ADMIN
            if (is_super_admin() && is_multisite()) {
                if (in_array('Super Admin', $disabled_for)) {
                    return true;
                } else {
                    return false;
                }
            }

            $disabled_for = array_map(function ($value) {
                return strtolower(str_replace(' ', '_', $value));
            }, $disabled_for);

            foreach ($current_roles as $role) {
                if (in_array($role, $disabled_for)) {
                    return true;
                }
            }
        }

        public function get_users_list()
        {
            global $wp_roles;
            $users = get_users();
            $roles = $wp_roles->roles;

            $new_roles_array = array();

            if (is_multisite()) {
                $new_roles_array[] = 'Super Admin';
            }

            foreach ($roles as $role) {
                $new_roles_array[] = $role['name'];
            }

            foreach ($users as $user) {
                $new_roles_array[] = $user->display_name;
            }

            return $new_roles_array;
        }

        /**
         * Turn admin bar items object to array
         *
         * @param array $nodes The admin bar menu.
         * @return array Array in expected format.
         */
        public function nodes_to_array($nodes, $type = 'backend')
        {
            $admin_bar_array = array();
            foreach ($nodes as $node_id => $node) {
                // For custom menu items, store the URL
                // For all other items, don't store URLs - they'll be fetched fresh
                $is_custom_menu = strpos($node_id, 'custom-menu-') === 0;
                $href_to_store = $is_custom_menu ? $node->href : '';
                
                $admin_bar_array[$node_id] = array(
                    'icon'                   => '',
                    'icon_default'           => $this->add_default_icon($node->id),
                    'id'                     => $node->id,
                    'id_default'             => $node->id,
                    'title'                  => '',
                    'title_default'          => $node->title,
                    'parent'                 => $node->parent,
                    'parent_default'         => $node->parent,
                    'href'                   => $href_to_store,
                    'href_default'           => $href_to_store, // Store URL only for custom items
                    'group'                  => $node->group,
                    'group_default'          => $node->group,
                    'meta'                   => $node->meta,
                    'meta_default'           => $node->meta,
                    'submenu'                => array(),
                    'hidden_for'             => '',
                    'newly_created'          => $is_custom_menu ? 1 : 0,
                    'menu_level'             => 0,
                    'menu_status'            => true,
                    'frontend_only'          => ($type == 'backend') ? 0 : 1,
                );
            }
            return $admin_bar_array;
        }

        public static function format_to_nested($flat_array)
        {

            if (isset($flat_array['menu-toggle'])) {
                unset($flat_array['menu-toggle']);
            }

            $nested_array = [];

            // Third, get the parent menu items.
            foreach ($flat_array as $menu_id => $menu) {

                // if ($menu['title'] && str_contains($menu['title_default'], '<span')) {
                //     $args['title'] =  '<span class="ab-icon" aria-hidden="true"></span> &nbsp;<span>' . $args['title'] . '</span>';
                // }

                if (!isset($menu['parent']) || !$menu['parent'] || !isset($flat_array[$menu['parent']])) {
                    $nested_array[$menu_id] = $menu;

                    $additional = array(
                        'title_encoded'         => isset($menu['title']) ? htmlentities2($menu['title']) : '',
                        'title_clean'           => isset($menu['title']) ? wp_strip_all_tags($menu['title']) : '',
                        'title_encoded_default' => isset($menu['title_default']) ? htmlentities2($menu['title_default']) : '',
                        'title_clean_default'   => isset($menu['title_default']) ? wp_strip_all_tags($menu['title_default']) : '',
                        'submenu'               => array(),
                        'menu_level'            => 0
                    );

                    $nested_array[$menu_id] = array_merge($nested_array[$menu_id], $additional);
                }
            }

            // Fourth, remove collected parent array from $flat_array.
            foreach ($nested_array as $key => $value) {
                if (isset($flat_array[$key])) {
                    unset($flat_array[$key]);
                }
            }

            // Fifth, get the 1st level submenu items.
            foreach ($flat_array as $menu_id => $menu) {

                //Howdy Text Change
                if ($menu_id == 'my-account') {
                    $menu['title_default'] = 'Howdy';
                }

                if (isset($nested_array[$menu['parent']])) {
                    $nested_array[$menu['parent']]['submenu'][$menu['id']] = $menu;

                    $additional = array(
                        'title_encoded'         => isset($menu['title']) ? htmlentities2($menu['title']) : '',
                        'title_clean'           => isset($menu['title']) ? wp_strip_all_tags($menu['title']) : '',
                        'title_encoded_default' => isset($menu['title_default']) ? htmlentities2($menu['title_default']) : '',
                        'title_clean_default'   => isset($menu['title_default']) ? wp_strip_all_tags($menu['title_default']) : '',
                        'submenu'               => array(),
                        'menu_level'            => 1
                    );

                    $nested_array[$menu['parent']]['submenu'][$menu['id']] = array_merge(
                        $nested_array[$menu['parent']]['submenu'][$menu['id']],
                        $additional
                    );

                    unset($flat_array[$menu_id]);
                }
            }

            // Sixth, get the 2nd level submenu items.
            if (!empty($flat_array)) {
                // Loop over flat_array.
                foreach ($flat_array as $menu_id => $menu) {
                    // Loop over nested_array.
                    foreach ($nested_array as $parent_id => $parent_array) {
                        $submenu_lv2_found = false;

                        if (!empty($parent_array['submenu'])) {
                            // Loop over parent array's submenu.
                            foreach ($parent_array['submenu'] as $submenu_lv1_id => $submenu_lv1_array) {
                                if ($menu['parent'] === $submenu_lv1_id) {
                                    if (!isset($nested_array[$parent_id]['submenu'][$submenu_lv1_id]['submenu'])) {
                                        $nested_array[$parent_id]['submenu'][$submenu_lv1_id]['submenu'] = array();
                                    }

                                    $nested_array[$parent_id]['submenu'][$submenu_lv1_id]['submenu'][$menu_id] = $menu;

                                    $additional = array(
                                        'title_encoded'         => isset($menu['title']) ? htmlentities2($menu['title']) : '',
                                        'title_clean'           => isset($menu['title']) ? wp_strip_all_tags($menu['title']) : '',
                                        'title_encoded_default' => isset($menu['title_default']) ? htmlentities2($menu['title_default']) : '',
                                        'title_clean_default'   => isset($menu['title_default']) ? wp_strip_all_tags($menu['title_default']) : '',
                                        'submenu'               => array(),
                                        'menu_level'            => 2
                                    );

                                    $nested_array[$parent_id]['submenu'][$submenu_lv1_id]['submenu'][$menu_id] = array_merge(
                                        $nested_array[$parent_id]['submenu'][$submenu_lv1_id]['submenu'][$menu_id],
                                        $additional
                                    );

                                    unset($flat_array[$menu_id]);
                                    $submenu_lv2_found = true;
                                    break;
                                }
                            }
                        }

                        if ($submenu_lv2_found) {
                            break;
                        }
                    }
                }
            }

            // Seventh, get the 3rd level submenu items.
            if (!empty($flat_array)) {
                // Loop over flat_array.
                foreach ($flat_array as $menu_id => $menu) {
                    // Loop over nested_array.
                    foreach ($nested_array as $parent_id => $parent_array) {
                        $submenu_lv3_found = false;

                        if (!empty($parent_array['submenu'])) {
                            // Loop over parent array's submenu.
                            foreach ($parent_array['submenu'] as $submenu_lv1_id => $submenu_lv1_array) {
                                if (!empty($submenu_lv1_array['submenu'])) {
                                    // Loop over submenu level 1's submenu.
                                    foreach ($submenu_lv1_array['submenu'] as $submenu_lv2_id => $submenu_lv2_array) {
                                        if ($menu['parent'] === $submenu_lv2_id) {
                                            if (!isset($nested_array[$parent_id]['submenu'][$submenu_lv1_id]['submenu'][$submenu_lv2_id]['submenu'])) {
                                                $nested_array[$parent_id]['submenu'][$submenu_lv1_id]['submenu'][$submenu_lv2_id]['submenu'] = array();
                                            }

                                            $nested_array[$parent_id]['submenu'][$submenu_lv1_id]['submenu'][$submenu_lv2_id]['submenu'][$menu_id] = $menu;

                                            $additional = array(
                                                'title_encoded'         => isset($menu['title']) ? htmlentities2($menu['title']) : '',
                                                'title_clean'           => isset($menu['title']) ? wp_strip_all_tags($menu['title']) : '',
                                                'title_encoded_default' => isset($menu['title_default']) ? htmlentities2($menu['title_default']) : '',
                                                'title_clean_default'   => isset($menu['title_default']) ? wp_strip_all_tags($menu['title_default']) : '',
                                                'submenu'               => array(),
                                                'menu_level'            => 3
                                            );

                                            $nested_array[$parent_id]['submenu'][$submenu_lv1_id]['submenu'][$submenu_lv2_id]['submenu'][$menu_id] = array_merge(
                                                $nested_array[$parent_id]['submenu'][$submenu_lv1_id]['submenu'][$submenu_lv2_id]['submenu'][$menu_id],
                                                $additional
                                            );

                                            unset($flat_array[$menu_id]);
                                            $submenu_lv3_found = true;
                                            break;
                                        }
                                    }
                                }

                                if ($submenu_lv3_found) {
                                    break;
                                }
                            }
                        }

                        if ($submenu_lv3_found) {
                            break;
                        }
                    }
                }
            }
            return $nested_array;
        }


        public static function associative_to_index_array($asoc_array)
        {

            $new_array = [];
            $i = 0;

            foreach ($asoc_array as $key  =>  $value) {
                if (isset($value['submenu']) && count($value['submenu']) > 0) {
                    $j = 0;
                    $sub_1 = [];
                    foreach ($value['submenu'] as $key1  =>  $value1) {

                        if (isset($value1['submenu']) &&  count($value1['submenu']) > 0) {
                            $k = 0;
                            $sub_2 = [];
                            foreach ($value1['submenu'] as $key2  =>  $value2) {
                                if (isset($value2['submenu']) &&  count($value2['submenu']) > 0) {
                                    $l = 0;
                                    $sub_3 = [];
                                    foreach ($value2['submenu'] as $key => $value3) {
                                        if (isset($value3['submenu']) &&  count($value3['submenu']) > 0) {
                                            $sub_3[$l] = $value3;
                                            $sub_3[$l]['submenu'] = array_values($value3['submenu']);
                                        } else {
                                            $sub_3[$l] = $value3;
                                        }
                                        $l++;
                                    }
                                    $sub_2[$k] = $value2;
                                    $sub_2[$k]['submenu'] = $sub_3;
                                } else {
                                    $sub_2[$k] = $value2;
                                }
                                $k++;
                            }

                            $sub_1[$j] = $value1;
                            $sub_1[$j]['submenu'] = $sub_2;
                        } else {
                            $sub_1[$j] = $value1;
                        }
                        // $new_array[$i]['submenu'][$j] = $value1;
                        $j++;
                    }
                    $new_array[$i] = $value;
                    $new_array[$i]['submenu'] = $sub_1;
                } else {
                    $new_array[$i] = $value;
                }
                $i++;
            }
            return $new_array;
        }

        public function add_default_icon($menu_id)
        {
            $icon_class = '';

            if ('wp-logo' === $menu_id) {
                $icon_class = 'dashicons dashicons-wordpress';
            } else if ('my-sites' === $menu_id) {
                $icon_class = 'dashicons dashicons-admin-multisite';
            } else if ('site-name' === $menu_id) {
                $icon_class = 'dashicons dashicons-admin-home';
            } else if ('site-name-frontend' === $menu_id) {
                $icon_class = 'dashicons dashicons-dashboard';
            } else if ('customize' === $menu_id) {
                $icon_class = 'dashicons dashicons-admin-customizer';
            } else if ('updates' === $menu_id) {
                $icon_class = 'dashicons dashicons-update';
            } else if ('comments' === $menu_id) {
                $icon_class = 'dashicons dashicons-admin-comments';
            } else if ('new-content' === $menu_id) {
                $icon_class = 'dashicons dashicons-plus';
            } else if ('edit' === $menu_id) {
                $icon_class = 'dashicons dashicons-edit';
            } else if ('site-editor' === $menu_id) {
                $icon_class = 'dashicons dashicons-admin-appearance';
            }
            return $icon_class;
        }
    }
}
