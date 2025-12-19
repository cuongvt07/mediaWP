<?php

namespace JewelTheme\AdminBarEditor\Inc\Classes;

use JewelTheme\AdminBarEditor\Inc\Utils;

// no direct access allowed
if (!defined('ABSPATH'))  exit;

class AdminBarEditorApiEndPoints extends AdminBarEditorModel
{
    private $namespace = 'wp-adminify-adminbar-editor-api/';
    private $version = 'v1';

    public function __construct()
    {
        if(Utils::is_plugin_active('adminify/adminify.php')) {
            $this->sync_settings_from_adminify();
        }
        add_action('rest_api_init', [$this, 'add_endpoints']);
    }

    // Sync Data form Adminify
    public function sync_settings_from_adminify() {
        // Already Synceed? Bail Early
        if ( get_option( $this->prefix . '_is_synced', false ) ) return;

        $adminify_settings = get_option( '_wpadminify_backup' );

        if( empty($adminify_settings) || empty($adminify_settings['admin_bar_settings']) ) return;

        $adminify_admin_bar_settings = $adminify_settings['admin_bar_settings'];

        // Move settings conditional array
        $move_settings = [
           'admin_bar_light_bg_color'   => 'admin_bar_settings.admin_bar_bg_color',
           'admin_bar_position'         => 'admin_bar_settings.admin_bar_position',
           'admin_bar_text_color'       => 'admin_bar_settings.admin_bar_text_color.color',

           'admin_bar_light_bg'          => 'admin_bar_settings.admin_bar_bg_type',

           'admin_bar_light_bg_gradient.background-color'          => 'admin_bar_settings.admin_bar_bg_gradient.first_color',
           'admin_bar_light_bg_gradient.background-gradient-color' => 'admin_bar_settings.admin_bar_bg_gradient.second_color',
           'admin_bar_light_bg_gradient.background-gradient-direction' => 'admin_bar_settings.admin_bar_bg_gradient.direction',

           'admin_bar_link_dropdown_color.wrapper_bg' => 'admin_bar_settings.admin_bar_dropdown_color.wrapper_bg',
           'admin_bar_link_dropdown_color.bg_color' => 'admin_bar_settings.admin_bar_dropdown_color.bg_color',
           'admin_bar_link_dropdown_color.link_color' => 'admin_bar_settings.admin_bar_dropdown_color.link_color',
           'admin_bar_link_dropdown_color.hover_color' => 'admin_bar_settings.admin_bar_dropdown_color.hover_color',

           'admin_bar_link_color.bg_color' => 'admin_bar_settings.admin_bar_new_button_color.bg_color',
           'admin_bar_link_color.link_color' => 'admin_bar_settings.admin_bar_new_button_color.link_color',
           'admin_bar_link_color.hover_color' => 'admin_bar_settings.admin_bar_new_button_color.hover_color'

        ];

         $move_data = \JewelTheme\AdminBarEditor\Inc\Utils::moveNestedKeys($adminify_admin_bar_settings, $move_settings);

        $admin_bar_settings = get_option( $this->prefix );
        $admin_bar_settings['admin_bar_settings'] = $move_data['admin_bar_settings'];

        // Update Adminbar settings with adminify data
        update_option( $this->prefix, $admin_bar_settings );

        // Operation Done
        update_option( $this->prefix . '_is_synced', true );
    }

    /**
     * Generate API Namespace
     */
    public function api_namespace()
    {
        return $this->namespace . $this->version;
    }

    /**
     * Register Routes
     */
    public function add_endpoints()
    {
        register_rest_route(
            $this->api_namespace(),
            '/get-adminbar-menu-items/',
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            =>  [$this, 'get_adminbar_menu_items'],
                'permission_callback' =>  [$this, 'check_permission']
            ]
        );

        register_rest_route(
            $this->api_namespace(),
            '/save-adminbar-menu-items/',
            [
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            =>  [$this, 'save_adminbar_menu_items'],
                'permission_callback' =>  [$this, 'check_permission']
            ]
        );

        register_rest_route(
            $this->api_namespace(),
            '/reset-adminbar-menu-items/',
            [
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            =>  [$this, 'reset_adminbar_menu_items'],
                'permission_callback' =>  [$this, 'check_permission']
            ]
        );
        register_rest_route(
            $this->api_namespace(),
            '/export-adminbar-menu-items/',
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            =>  [$this, 'export_adminbar_menu_items'],
                'permission_callback' =>  [$this, 'check_permission']
            ]
        );
        register_rest_route(
            $this->api_namespace(),
            '/import-adminbar-menu-items/',
            [
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            =>  [$this, 'import_adminbar_menu_items'],
                'permission_callback' =>  [$this, 'check_permission']
            ]
        );
        register_rest_route(
            $this->api_namespace(),
            '/upload-adminbar-menu-icon/',
            [
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            =>  [$this, 'upload_adminbar_menu_icon'],
                'permission_callback' =>  [$this, 'check_permission']
            ]
        );
    }

    /**
     * Get admin bar items
     */
    public function get_adminbar_menu_items($request)
    {
        $custom_icons                     = $this->get_adminbar_menu_icons();
        $admin_bar_items                  = (new AdminBarEditorOptions())->get();

        $existing_admin_bar               = !empty($admin_bar_items['existing_admin_bar']) ? $admin_bar_items['existing_admin_bar'] : '';
        $saved_admin_bar                  = !empty($admin_bar_items['saved_admin_bar']) ? $admin_bar_items['saved_admin_bar'] : [];

        $parsed_admin_bar_backend         = empty($saved_admin_bar) ? $existing_admin_bar : Core::parse_menu_items($saved_admin_bar, $existing_admin_bar, 'backend');

        $nested_admin_bar                 = Core::format_to_nested($parsed_admin_bar_backend);
        $formated_admin_menu              = Core::associative_to_index_array($nested_admin_bar);

        $existing_admin_bar_frontend      = !empty( $admin_bar_items['existing_admin_bar_frontend'] ) ? $admin_bar_items['existing_admin_bar_frontend'] : [];
        $saved_admin_bar_frontend         = !empty($admin_bar_items['saved_admin_bar_frontend']) ? $admin_bar_items['saved_admin_bar_frontend'] : [];

        $parsed_admin_bar_frontend        = empty($saved_admin_bar_frontend) ? $existing_admin_bar_frontend : Core::parse_menu_items($saved_admin_bar_frontend, $existing_admin_bar_frontend, 'frontend');

        $nested_front_bar                 = Core::format_to_nested($parsed_admin_bar_frontend);
        $formated_front_menu              = Core::associative_to_index_array($nested_front_bar);

        $user_roles                       = !empty($admin_bar_items['user_roles']) ? $admin_bar_items['user_roles'] : [];

        $user_roles_array = array();

        if(!is_multisite() && isset($user_roles[0]) && $user_roles[0] == 'Super Admin'){
            unset($user_roles[0]); // Rmove Super Admin role
        }

        $user_roles_array = array_merge($user_roles_array, $user_roles);

        return [
            'is_pro_user'                   => jlt_admin_bar_editor_is_premium(),
            'upgrade_pro_notice'            => Utils::jlt_admin_bar_upgrade_pro(),
            'admin_bar_backend'             => $formated_admin_menu,
            'admin_bar_frontend'            => $formated_front_menu,
            'custom_icons'                  => $custom_icons,
            'user_roles'                    => $user_roles_array,
            'disable_backend_admin_bar'     => $admin_bar_items['disable_backend_admin_bar'],
            'disable_backend_conditions'    => $admin_bar_items['disable_backend_conditions'],
            'disable_frontend_admin_bar'    => $admin_bar_items['disable_frontend_admin_bar'],
            'disable_frontend_conditions'   => $admin_bar_items['disable_frontend_conditions'],
            'disable_frontend_all_users'    => !empty( $admin_bar_items['disable_frontend_all_users'] ) ? $admin_bar_items['disable_frontend_all_users'] : false,
            'disable_frontend_guest_users'  => !empty( $admin_bar_items['disable_frontend_guest_users'] ) ? $admin_bar_items['disable_frontend_guest_users'] : false,
            // 'admin_bar' => $admin_bar_items
            'admin_bar_settings'     => !empty( $admin_bar_items['admin_bar_settings'] ) ? $admin_bar_items['admin_bar_settings'] : '',
        ];
    }

    public function filter_attachment($value)
    {
        return strpos($value->guid, "adminify-custom-icon") !== false;
    }

    public function list_files_in_media_folder($base_dir) {
        $base_url = wp_upload_dir()['baseurl'];
        $attachments['images'] = null;
        if (!is_dir($base_dir)) {
            return array('error' => 'Uploads directory not found.');
        }

        // Helper function to recursively scan a directory
        function scan_directory($dir) {
            $files = [];
            foreach (scandir($dir) as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $file_path = $dir . DIRECTORY_SEPARATOR . $file;
                if (is_dir($file_path)) {
                    $files = array_merge($files, scan_directory($file_path));
                } else {
                    $files[] = $file_path;
                }
            }
            return $files;
        }

        // Scan the uploads directory
        $all_files = scan_directory($base_dir);

        // Filter out auto-generated files using regex
        $filtered_files = array_filter($all_files, function ($file) {
            // Match files without auto-generated suffixes
            return !preg_match('/-\d+x\d+\.|scaled\./', $file);
        });
        foreach ($filtered_files as $file_path) {
            $file_url = str_replace($base_dir, $base_url . '/adminify-custom-icons' , $file_path);
            $attachment_id = attachment_url_to_postid($file_url);

            if ($attachment_id) {
                $attachments['images'][$attachment_id] = $file_url;
            }
        }

        return $attachments;
    }

    public function get_adminbar_menu_icons()
    {
        $result['images'] = null;
        $upload_dir = \wp_upload_dir();
        $targeted_dir = $upload_dir['basedir'] . '/adminify-custom-icons';
        return $this->list_files_in_media_folder( $targeted_dir );
    }

    public function upload_adminbar_menu_icon($request)
    {
        require_once('wp-admin/includes/image.php');
        require_once('wp-admin/includes/file.php');
        require_once('wp-admin/includes/media.php');

        $result['status'] = false;

        if (count($_FILES) > 0) {
            $upload_dir = \wp_upload_dir();
            $targeted_dir = $upload_dir['basedir'] . '/adminify-custom-icons';
            if (!is_dir($targeted_dir)) \wp_mkdir_p($targeted_dir);

            add_filter('upload_dir', [$this, 'adminify_admin_bar_icon_custom_upload_dir']);

            $files = $_FILES['file_upload'];
            foreach ($files['name'] as $key => $value) {
                if ($files['name'][$key]) {
                    $file = array(
                        'name'     => $files['name'][$key],
                        'type'     => $files['type'][$key],
                        'tmp_name' => $files['tmp_name'][$key],
                        'error'    => $files['error'][$key],
                        'size'     => $files['size'][$key]
                    );
                    $_FILES = array("upload_file" => $file);
                    $attachment_id = \media_handle_upload("upload_file", 0);

                    if (is_wp_error($attachment_id)) {
                        // There was an error uploading the image.
                        $result['status'] = false;
                        $result['message'] = 'Error uploading file.';
                    } else {
                        // The image was uploaded successfully!
                        $result['status'] = true;
                        $result['message'] = 'File Uploaded successfully.';
                        $result['images'][$attachment_id] = \wp_get_attachment_url($attachment_id);
                    }
                }
            }
            remove_filter('upload_dir', [$this, 'adminify_admin_bar_icon_custom_upload_dir']);
        }

        wp_send_json(wp_json_encode($result));
    }


    public function adminify_admin_bar_icon_custom_upload_dir($dir_data)
    {
        // $dir_data already you might want to use
        $custom_dir = 'adminify-custom-icons';
        return [
            'path'    => $dir_data['basedir'] . '/' . $custom_dir,
            'url'     => $dir_data['baseurl'] . '/' . $custom_dir,
            'subdir'  => '/' . $custom_dir,
            'basedir' => $dir_data['basedir'],
            'baseurl' => $dir_data['baseurl'],
            'error'   => $dir_data['error'],
        ];
    }

    public function save_adminbar_menu_items($request)
    {
        $admin_bar_items  = (new AdminBarEditorOptions())->get();
        $result['success'] = false;

        $admin_bar_backend  = Core::assoc_to_flat_array($request['admin_bar_backend']);
        $admin_bar_frontend = Core::assoc_to_flat_array($request['admin_bar_frontend']);

        // Process menu items: remove href for non-custom items, clean logout URLs
        $admin_bar_backend = $this->process_menu_items_for_save($admin_bar_backend);
        $admin_bar_frontend = $this->process_menu_items_for_save($admin_bar_frontend);

        // Admin Bar Advanced Settings
        $admin_bar_items['admin_bar_settings'] = $request['admin_bar_settings'];

        $admin_bar_items['disable_backend_admin_bar'] = $request['disable_backend_admin_bar'];
        $admin_bar_items['disable_backend_conditions'] = $request['disable_backend_conditions'];

        $admin_bar_items['disable_frontend_admin_bar'] = $request['disable_frontend_admin_bar'];
        $admin_bar_items['disable_frontend_conditions'] = $request['disable_frontend_conditions'];

        $admin_bar_items['disable_frontend_all_users'] = $request['disable_frontend_all_users'];
        $admin_bar_items['disable_frontend_guest_users'] = $request['disable_frontend_guest_users'];

        if (!empty($admin_bar_backend) && (count($admin_bar_backend) > 1)) {
            $admin_bar_items['saved_admin_bar'] = $admin_bar_backend;
        }
        if (is_array($admin_bar_frontend) && !empty($admin_bar_frontend) && (count($admin_bar_frontend) > 1)) {
            $admin_bar_items['saved_admin_bar_frontend'] = $admin_bar_frontend;
        }

        update_option($this->prefix, $admin_bar_items);

        $result['success'] = true;
        $result['message'] = __('Settings Saved', 'admin-bar');

        wp_send_json(wp_json_encode($result));
    }


    public function reset_adminbar_menu_items($request)
    {

        $result['reset'] = false;
        if ($request['reset']) {
            update_option($this->prefix, []);
            $adminbar_editor_options = get_option($this->prefix);

            if (!$adminbar_editor_options) {
                $result['reset'] = true;
                $result['message'] = __('Settings reset', 'admin-bar');
            } else {
                $result['reset'] = false;
            }
        }

        wp_send_json(wp_json_encode($result));
    }

    public function export_adminbar_menu_items($request)
    {
        $result['status'] = false;
        $result['data']   = '';
        $adminbar_editor_options = get_option($this->prefix);
        if ($adminbar_editor_options) {
            $result['status'] = true;
            $result['data']   = $adminbar_editor_options;
        }
        wp_send_json(wp_json_encode($result));
    }

    public function import_adminbar_menu_items($request)
    {
        $result['status'] = false;
        // $new_options = $this->clean_ajax_input($request['data']);
        $new_options = $request['data'];

        if (($new_options == "") || !is_array($new_options)) {
            $result['message'] = __('No options supplied to save', 'admin-bar');
        }

        if (is_array($new_options)) {
            update_option($this->prefix, $new_options);
            $result['status'] = true;
            $result['message'] = __('Menu Imported', 'admin-bar');
        }

        wp_send_json(wp_json_encode($result));
    }


    /**
     * Sanitises and strips tags of input from ajax
     * @since 1.0.0
     * @variables $values = item to clean (array or string)
     */
    public function clean_ajax_input($values)
    {

        if (is_array($values)) {
            foreach ($values as $index => $in) {
                if (is_array($in)) {
                    $values[$index] = $this->clean_ajax_input($in);
                } else {
                    $values[$index] = strip_tags($in);
                }
            }
        } else {
            $values = strip_tags($values);
        }

        return $values;
    }

    /**
     * Process menu items before saving to database
     * - For non-custom items: remove href to always use WordPress defaults
     * - For custom items: keep the href value
     * - For logout items: strip nonce values
     * @param array $menu_items Admin bar menu items
     * @return array Modified menu items
     */
    private function process_menu_items_for_save($menu_items)
    {
        if (!is_array($menu_items)) {
            return $menu_items;
        }

        foreach ($menu_items as $key => &$item) {
            // Only keep href for custom menu items (newly_created = 1)
            if (isset($item['newly_created']) && $item['newly_created'] != 1) {
                // For non-custom items, remove the href completely
                // This ensures we always use WordPress default URLs
                unset($item['href']);
            } else if (isset($item['href'])) {
                // For custom items, keep the href but clean logout URLs
                if (isset($item['id']) && $item['id'] === 'logout') {
                    // Remove any nonce parameters from logout URLs
                    $item['href'] = preg_replace('/([?&])_wpnonce=[^&]*(&|$)/', '$1', $item['href']);
                    $item['href'] = rtrim($item['href'], '?&');
                }
            }

            // Process submenu items recursively
            if (isset($item['submenu']) && is_array($item['submenu'])) {
                $item['submenu'] = $this->process_menu_items_for_save($item['submenu']);
            }
        }

        return $menu_items;
    }

    /**
     * Make sure that user has administrative permission
     */
    public function check_permission()
    {
        return current_user_can('manage_options');
    }

    /**
     * Returns the full rest url of a given endpoint.
     *
     */
    public static function get_rest_url($endpoint)
    {
        $instance = new self();
        return \rest_url($instance->api_namespace() . $endpoint);
    }
}
