<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
* Function: Ajax Call for Install and Activate WP Adminify Plugin
*/
function jlt_admin_columns_install_and_activate_plugin() {

   // Include necessary WordPress files
   require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
   require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
   require_once ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php';
   require_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';

   if ( isset( $_POST['plugin'] ) ) {

	   $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

	   if ( ! wp_verify_nonce( $nonce, 'jlt_admin_columns_recommended_nonce' ) ) {
		   wp_send_json_error( array( 'mess' => esc_html__( 'Nonce is invalid', 'adminify-admin-columns' ) ) );
	   }

	   if ( ( is_multisite() && is_network_admin() ) || ! current_user_can( 'install_plugins' ) ) {
		   wp_send_json_error( array( 'mess' => esc_html__( 'Invalid Access', 'adminify-admin-columns' ) ) );
	   }

	   $plugin = sanitize_text_field( wp_unslash( $_POST['plugin'] ) );

	   if ( empty( $plugin ) ) {
		   wp_send_json_error( array( 'mess' => esc_html__( 'Invalid plugin', 'adminify-admin-columns' ) ) );
	   }

	   $type     = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : 'install';
	   $skin     = new \WP_Ajax_Upgrader_Skin();
	   $upgrader = new \Plugin_Upgrader( $skin );

	   if ( 'install' === $type ) {
		   $result = $upgrader->install( $plugin );
		   if ( is_wp_error( $result ) ) {
			   wp_send_json_error(
				   array(
					   'mess' => $result->get_error_message(),
				   )
			   );
		   }
		   $args        = array(
			   'slug'   => $upgrader->result['destination_name'],
			   'fields' => array(
				   'short_description' => true,
				   'icons'             => true,
				   'banners'           => false,
				   'added'             => false,
				   'reviews'           => false,
				   'sections'          => false,
				   'requires'          => false,
				   'rating'            => false,
				   'ratings'           => false,
				   'downloaded'        => false,
				   'last_updated'      => false,
				   'added'             => false,
				   'tags'              => false,
				   'compatibility'     => false,
				   'homepage'          => false,
				   'donate_link'       => false,
			   ),
		   );
		   $plugin_data = plugins_api( 'plugin_information', $args );

		   if ( $plugin_data && ! is_wp_error( $plugin_data ) ) {
			   $install_status = \install_plugin_install_status( $plugin_data );
			   activate_plugin( $install_status['file'] );
		   }
		   wp_die();  // die();
	   }
   }
}

/**
* Function: Parent Plugin Installed.
*/
function jlt_admin_columns_is_parent_plugin_installed() {
   $get_plugins = get_plugins();
   $get_plugins = array_keys( $get_plugins );

   foreach ( $get_plugins as $basename ) {
	   if ( 0 === strpos( $basename, 'adminify/' ) || 0 === strpos( $basename, 'adminify-pro/' ) ) {
		   return true;
	   }
   }
   return false;
}

/**
* Function: Check WP Adminify Loaded
*/
function jlt_admin_columns_fs_not_loaded_notice() {
   add_action( 'admin_notices', 'jlt_admin_columns_notice_missing_parent_plugin' );
   add_action( 'network_admin_notices', 'jlt_admin_columns_notice_missing_parent_plugin' );
}

/**
* Function: Admin Notice for WP Adminify plugin
*/
function jlt_admin_columns_notice_missing_parent_plugin() {

   if ( ( is_multisite() || ! is_network_admin() ) && !current_user_can('install_plugins') ) {
	   // Bail early if requirements not met.
	   return;
   }

   $get_plugins = get_plugins();
   $get_plugins = array_keys( $get_plugins );

   foreach ( $get_plugins as $basename ) {
	   if ( 0 === strpos( $basename, 'adminify/' ) || 0 === strpos( $basename, 'adminify-pro/' ) ) {
		   $jltwp_adminify = $basename;
	   }
   }

   if ( jlt_admin_columns_is_parent_plugin_installed() ) {
	   // Activate Plugin .
	   $classes               = 'activate-adminify-now';
	   $install_active_url    = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $jltwp_adminify . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $jltwp_adminify );
	   $message               = sprintf( __( '<b>%1$s</b> requires <strong>WP Adminify</strong> plugin to be active. Please activate WP Adminify to continue.', 'adminify-admin-columns' ), 'Adminify Admin Columns' );
	   $button_text           = esc_html__( 'Activate WP Adminify', 'adminify-admin-columns' );
	   $button                = '<p><a href="' . esc_url( $install_active_url ) . '" class="button-primary">' . esc_html( $button_text ) . '</a></p>';

   } else {

	   // Install Plugin .
	   // /* translators: 1: strong start tag, 2: strong end tag. */
	   $message     = sprintf( __( '<b>%1$s</b> requires <strong>"WP Adminify"</strong> plugin to be installed and activated. Please install WP Adminify to continue.', 'adminify-admin-columns' ), 'Adminify Admin Columns' );
	   $button_text = esc_html__( 'Install WP Adminify', 'adminify-admin-columns' );
	   $classes     = 'install-adminify-adminify-admin-columns-now';
	   $button      = '<p><a class="install-now button-primary" data-plugin="' . esc_url( 'https://downloads.wordpress.org/plugin/adminify.zip' ) . '">' . esc_html( $button_text ) . '</a></p>';
   }
   printf( '<div class="notice notice-warning %3$s"><p>%1$s</p>%2$s</div>', $message, $button, $classes );
}


function jlt_admin_columns_fs_is_parent_active_and_loaded() {
   // Check if the parent's init SDK method exists.
   return function_exists( 'jltwp_adminify' );
}

function jlt_admin_columns_fs_is_parent_active() {
   $active_plugins = get_option( 'active_plugins', array() );

   if ( is_multisite() ) {
	   $network_active_plugins = get_site_option( 'active_sitewide_plugins', array() );
	   $active_plugins         = array_merge( $active_plugins, array_keys( $network_active_plugins ) );
   }

   foreach ( $active_plugins as $basename ) {
	   if (
		   0 === strpos( $basename, 'adminify/' ) ||
		   0 === strpos( $basename, 'adminify-pro/' )
	   ) {
		   return true;
	   }
   }

   return false;
}

/**
* Freemius Iniit Hooks
*/
function jlt_admin_columns_fs_init() {
   if ( jlt_admin_columns_fs_is_parent_active_and_loaded() ) {
	   // Init Freemius.
	   jlt_admin_columns_fs();

	   // Signal that the add-on's SDK was initiated.
	   do_action( 'jlt_admin_columns_fs_loaded' );

   } else {
	   // Parent is inactive, add your error handling here.
	   do_action( 'jlt_admin_columns_fs_not_loaded' );
   }
}

if ( ! function_exists( 'jlt_admin_columns_is_plugin_active' ) ) {
   function jlt_admin_columns_is_plugin_active( $plugin_basename ) {
	   include_once ABSPATH . 'wp-admin/includes/plugin.php';
	   return is_plugin_active( $plugin_basename );
   }
}

/*
 * @version       1.0.0
 * @package       AdminColumns
 * @license       Copyright AdminColumns
 */

if ( ! function_exists( 'jlt_admin_columns_option' ) ) {
	/**
	 * Get setting database option
	 *
	 * @param string $section default section name jlt_admin_columns_general .
	 * @param string $key .
	 * @param string $default .
	 *
	 * @return string
	 */
	function jlt_admin_columns_option( $section = 'jlt_admin_columns_general', $key = '', $default = '' ) {
		$settings = get_option( $section );

		return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
	}
}

if ( ! function_exists( 'jlt_admin_columns_exclude_pages' ) ) {
	/**
	 * Get exclude pages setting option data
	 *
	 * @return string|array
	 *
	 * @version 1.0.0
	 */
	function jlt_admin_columns_exclude_pages() {
		return jlt_admin_columns_option( 'jlt_admin_columns_triggers', 'exclude_pages', array() );
	}
}

if ( ! function_exists( 'jlt_admin_columns_exclude_pages_except' ) ) {
	/**
	 * Get exclude pages except setting option data
	 *
	 * @return string|array
	 *
	 * @version 1.0.0
	 */
	function jlt_admin_columns_exclude_pages_except() {
		return jlt_admin_columns_option( 'jlt_admin_columns_triggers', 'exclude_pages_except', array() );
	}
}

/**
* Hook: Ajax Call for Install and Activate WP Adminify Plugin
*/
add_action( 'wp_ajax_jlt_admin_columns_install_plugin', 'jlt_admin_columns_install_and_activate_plugin' );






// define all helper functions to create list table
function jltwp_adminify_admin_columns_create_post_table($screen_id)
{
	require_once ABSPATH . 'wp-admin/includes/class-wp-posts-list-table.php';

	return new WP_Posts_List_Table(['screen' => $screen_id]);
}

function jltwp_adminify_admin_columns_create_user_table($screen_id)
{
	require_once ABSPATH . 'wp-admin/includes/class-wp-users-list-table.php';

	return new WP_Users_List_Table(['screen' => $screen_id]);
}

function jltwp_adminify_admin_columns_create_comment_table($screen_id)
{
	require_once ABSPATH . 'wp-admin/includes/class-wp-comments-list-table.php';

	$table = new WP_Comments_List_Table(['screen' => $screen_id]);

	// Since 4.4 the `floated_admin_avatar` filter is added in the constructor of the `\WP_Comments_List_Table` class.
	remove_filter('comment_author', [$table, 'floated_admin_avatar']);

	return $table;
}

function jltwp_adminify_admin_columns_create_media_table($screen_id)
{
	require_once ABSPATH . 'wp-admin/includes/class-wp-media-list-table.php';

	return new WP_Media_List_Table(['screen' => $screen_id]);
}

function jltwp_adminify_admin_columns_create_taxonomy_table($screen_id)
{
	require_once ABSPATH . 'wp-admin/includes/class-wp-terms-list-table.php';

	return new WP_Terms_List_Table(['screen' => $screen_id]);
}
function jltwp_adminify_admin_columns_create_network_user_table($screen_id)
{
	require_once ABSPATH . 'wp-admin/includes/class-wp-ms-users-list-table.php';

	return new WP_MS_Users_List_Table(['screen' => $screen_id]);
}



// get the columns list of any post or users, comments etc.
function jltwp_adminify_admin_columns_data($type)
{
	// $type can be post, page, product, user, shop_order, shop_coupon, 'edit-comments', 'users', 'upload'

	$builtin_non_posttype_data = ['users', 'edit-comments', 'upload'];
	$table                     = '';

	if (in_array($type, $builtin_non_posttype_data)) {
		switch ($type) {
			case 'users':
				$table = jltwp_adminify_admin_columns_create_user_table($type);
				break;
			case 'edit-comments':
				$table = jltwp_adminify_admin_columns_create_comment_table($type);
				break;
			case 'upload':
				$table = jltwp_adminify_admin_columns_create_media_table($type);
				break;
		}
	} else {
		$type_post = post_type_exists($type);
		$type_tax  = taxonomy_exists(str_replace('edit-', '', $type));

		if ($type_post) {
			$table = jltwp_adminify_admin_columns_create_post_table($type); // for all kind of post_type
		}
		if ($type_tax) {
			$table = jltwp_adminify_admin_columns_create_taxonomy_table($type); // for all kind of taxonomy
		}
	}

	// handle woocommerce
	if (function_exists('WC')) {
		switch ($type) {
			case 'product':
				include_once dirname(WC_PLUGIN_FILE) . '/includes/admin/list-tables/class-wc-admin-list-table-products.php';
				new WC_Admin_List_Table_Products();
				break;
			case 'shop_order':
				include_once dirname(WC_PLUGIN_FILE) . '/includes/admin/list-tables/class-wc-admin-list-table-orders.php';
				new WC_Admin_List_Table_Orders();
				break;
			case 'shop_coupon':
				include_once dirname(WC_PLUGIN_FILE) . '/includes/admin/list-tables/class-wc-admin-list-table-coupons.php';
				new WC_Admin_List_Table_Coupons();
				break;
		}
	}

	$col_names = [];

	if ($table instanceof WP_List_Table) {
		$col_names = $table->get_columns();
		if (array_key_exists('cb', $col_names)) {
			unset($col_names['cb']);
		}
	}

	return (array) $col_names;
}

// add_action('current_screen', 'jltwp_adminify_admin_columns_data', 99);


function adminify_post_types()
{
	$sections = [];

	$update_column_settings = get_option('_wpadminify_admin_columns_settings');

	foreach ($update_column_settings  as $p => $post_type) {
		if ($post_type == 'attachment') {
			continue;
		}

		$defaults = [];

		foreach ($post_type as $column => $column_label) {
			$column_meta = adminify_get_column_meta($post_type, $column, $column_label);

			$defaults[] = [
				'type'  => $column,
				'label' => $column_label,
				'width' => $column_meta['width'],
			];
		}

		$sections[] = [
			'title'  => WPAdminify\Inc\Utils::id_to_string($p),
			'fields' => [
				[
					'id'      => 'admin-columns-group-' . $p,
					'type'    => 'group',
					'title'   => '',
					'fields'  => [

						'field_type' => [
							'id'          => 'field_type',
							'type'        => 'select',
							'title'       => 'Column Type',
							'chosen'      => true,
							'placeholder' => 'Select Column Type',
							'options'     => [
								'title'      => esc_html__('Title', 'adminify-admin-columns'),
								'author'     => esc_html__('Author', 'adminify-admin-columns'),
								'categories' => esc_html__('Categories', 'adminify-admin-columns'),
								'tags'       => esc_html__('Tags', 'adminify-admin-columns'),
								'comments'   => esc_html__('Comments', 'adminify-admin-columns'),
								'date'       => esc_html__('Date', 'adminify-admin-columns'),
								'id'         => esc_html__('ID', 'adminify-admin-columns'),
							],
						],

						'label'      => [
							'id'    => 'label',
							'type'  => 'text',
							'title' => esc_html__('Label', 'adminify-admin-columns'),
						],

						'width'      => [
							'id'    => 'width',
							'type'  => 'slider',
							'title' => esc_html__('Width', 'adminify-admin-columns'),
							'unit'  => '%',
						],

					],

					'default' => $defaults,

				],
			],
		];
	}

	return $sections;
}



/*
 * Get registered post_types
 */
function adminify__get_post_types()
{
	$post_types = [];

	foreach (WPAdminify\Inc\Utils::get_post_types() as $post_type) {
		if ($post_type->name == 'attachment') {
			continue;
		}

		$post_types[$post_type->name] = $post_type->labels->singular_name;
	}

	return $post_types;
}

/*
 * Get registered post_types
 */
function adminify__get_taxonomies()
{
	$taxonomies = [];

	foreach (WPAdminify\Inc\Utils::get_taxonomies() as $taxonomy) {
		$taxonomies[$taxonomy->name] = $taxonomy->labels->singular_name;
	}

	return $taxonomies;
}

/*
 * Get saved version of both visible & non visible columns of a specific post_type
 */
function adminify__get_post_type_all_columns($post_type)
{
	return (array) get_option('_adminify_admin_columns_' . $post_type, []);
}

/*
 * Get both visible & non visible columns of a specific post_type
 */
function _adminify__get_post_type_all_columns($post_type)
{
	$column_groups = [
		[
			'group'   => esc_html__('Default', 'adminify-admin-columns'),
			'options' => (array) jltwp_adminify_admin_columns_data($post_type),
		],
	];

	// Set Default Columns for First Installation
	update_option('_adminify_admin_columns_' . $post_type, $column_groups);

	// Added custom taxonomy support
	$taxonomies       = get_taxonomies(['object_type' => [$post_type]], 'objects');
	$taxonomies       = wp_list_pluck($taxonomies, 'label', 'name');
	$taxonomy_columns = [];
	if (!empty($taxonomies)) {
		foreach ($taxonomies as $tax_name => $tax_label) {

			// Skip the default category & tag for post
			if ($post_type == 'post' && ($tax_name == 'category' || $tax_name == 'post_tag')) {
				continue;
			}

			$_tax_name = 'taxonomy-' . $tax_name;
			if (!in_array($_tax_name, $taxonomy_columns)) {
				$taxonomy_columns[$_tax_name] = $tax_label;
			}
		}
	}

	$column_groups[] = [
		'group'   => esc_html__('Taxonomy', 'adminify-admin-columns'),
		'options' => $taxonomy_columns,
	];

	$custom_admin_columns  = adminify_get_custom_admin_columns();
	$_custom_admin_columns = [];

	foreach ($custom_admin_columns as $column) {
		$_custom_admin_columns[$column['name']] = $column['label'];
	}

	$column_groups[] = [
		'group'   => esc_html__('Custom', 'adminify-admin-columns'),
		'options' => $_custom_admin_columns,
	];

	$acf_fields = WPAdminify\Modules\AdminColumns\Inc\AdminColumns\Module_AdminColumns::get_acf_fields($post_type);
	if (!empty($acf_fields)) {
		$column_groups[] = [
			'group'   => esc_html__('ACF', 'adminify-admin-columns'),
			'options' => $acf_fields,
		];
	}

	$pods_fields = WPAdminify\Modules\AdminColumns\Inc\AdminColumns\Module_AdminColumns::get_pods_fields($post_type);
	if (!empty($pods_fields)) {
		$column_groups[] = [
			'group'   => esc_html__('Pods', 'adminify-admin-columns'),
			'options' => $pods_fields,
		];
	}

	$column_groups[] = [
		'title' => esc_html__('Function', 'adminify-admin-columns'),
		'name'  => 'function',
	];

	$column_groups[] = [
		'title' => esc_html__('Shortcode', 'adminify-admin-columns'),
		'name'  => 'shortcode',
	];

	return $column_groups;
}

/*
 * Get saved version of both visible & non visible columns of a specific taxonomy
 */
function adminify__get_taxonomy_all_columns($taxonomy)
{
	return (array) get_option('_adminify_admin_taxonomy_columns_' . $taxonomy, []);
}

/*
 * Get both visible & non visible columns of a specific taxonomy
 */
function _adminify__get_taxonomy_all_columns($taxonomy)
{
	$columns = (array) jltwp_adminify_admin_columns_data('edit-' . $taxonomy);
	update_option('_adminify_admin_taxonomy_columns_' . esc_attr($taxonomy), $columns);
	return $columns;
}

/*
 * Get visible columns of all post_types
 */
function adminify__get_post_types_columns()
{
	$post_types = adminify__get_post_types();

	$post_types_columns = [];

	foreach ($post_types as $post_type => $post_type_title) {
		$column_data = [
			'name'            => $post_type,
			'title'           => $post_type_title,
			'columns'         => adminify_columns_group_to_options(_adminify__get_post_type_all_columns($post_type)),
			'display_columns' => adminify_prepare_post_type_column_meta($post_type),
			'fields'          => adminify_get_post_type_fields($post_type),
		];

		if (!in_array($post_type, ['post', 'page'])) {
			$column_data['is_pro'] = true;
		}

		$post_types_columns[] = $column_data;
	}

	return $post_types_columns;
}

function _adminify__get_taxonomy_post_type($taxonomy)
{
	$tax = get_taxonomy($taxonomy);

	if ($tax && !empty($tax->object_type)) {
		return $tax->object_type[0];
	}

	return '';
}

/*
 * Get visible columns of all taxonomies
 */
function adminify__get_taxonomies_columns()
{
	$taxonomies = adminify__get_taxonomies();

	$taxonomies_columns = [];

	foreach ($taxonomies as $taxonomy => $taxonomy_title) {
		$column_data = [
			'name'            => $taxonomy,
			'title'           => $taxonomy_title,
			'object_type'     => _adminify__get_taxonomy_post_type($taxonomy),
			'columns'         => _adminify__get_taxonomy_all_columns($taxonomy),
			'display_columns' => adminify_prepare_taxonomy_column_meta($taxonomy),
			'fields'          => adminify_get_taxonomy_fields($taxonomy),
		];

		if (!in_array($taxonomy, ['category', 'post_tag'])) {
			$column_data['is_pro'] = true;
		}

		$taxonomies_columns[] = $column_data;
	}

	return $taxonomies_columns;
}

/*
 * Get specific column meta data
 */
function adminify_get_column_meta($post_type, $column, $column_label)
{
	$column_default_meta = [
		'label'  => $column_label,
		'width'  => [
			'value' => 'auto',
			'unit'  => '%',
		],
		'fields' => ['type', 'label', 'width'],
	];

	/*
	 * You can extend the fields based on post type and column
	 * Make sure you have added the new field type in this function: adminify_get_post_type_fields
	*/

	// if ( $post_type == 'page' && $column == 'taxonomy-folder' ) {
	// $column_default_meta['fields'][] = 'new';
	// }

	$data = (array) get_option('_adminify_admin_columns_meta_data', []);

	$column_meta = [];

	if (!empty($data) && !empty($data[$post_type]) && !empty($data[$post_type][$column])) {
		$column_meta = $data[$post_type][$column];
	}

	return array_merge_recursive($column_default_meta, $column_meta);
}

function adminify_columns_validation($columns_meta)
{
	foreach ($columns_meta as &$column_meta) {
		$column_meta['label'] = wp_kses_post($column_meta['label']);
		$column_meta['name'] = sanitize_key($column_meta['name']);
		if (!empty($column_meta['width'])) {
			$column_meta['width'] = array_map('sanitize_text_field', $column_meta['width']);
		}
	}
	return $columns_meta;
}

/*
 * Get post_type columns meta
 */
function adminify_prepare_post_type_column_meta($post_type)
{
	$columns_meta = get_option('_adminify_admin_columns_meta_' . $post_type, null);

	if (!is_null($columns_meta)) {
		$columns_meta = adminify_columns_validation($columns_meta);
		return $columns_meta;
	}

	$columns = adminify_columns_group_to_options(adminify__get_post_type_all_columns($post_type));

	$_columns = [];

	foreach ($columns as $column => $column_label) {
		$_columns[] = ['name' => $column] + (array) adminify_get_column_meta($post_type, $column, $column_label);
	}

	return $_columns;
}

/*
 * Get taxonomy columns meta
 */
function adminify_prepare_taxonomy_column_meta($taxonomy)
{
	$columns_meta = get_option('_adminify_admin_taxonomy_columns_meta_' . $taxonomy, null);

	if (!is_null($columns_meta)) {
		$columns_meta = adminify_columns_validation($columns_meta);
		return (array) $columns_meta;
	}

	$columns = adminify__get_taxonomy_all_columns($taxonomy);

	$_columns = [];

	foreach ($columns as $column => $column_label) {
		$_columns[] = ['name' => $column] + (array) adminify_get_column_meta($taxonomy, $column, $column_label);
	}

	return $_columns;
}

/*
 * Get post_type fields
 */
function adminify_get_post_type_fields($post_type)
{
	$columns = _adminify__get_post_type_all_columns($post_type);

	return [
		[
			'id'          => 'type',
			'title'       => esc_html__('Column Type', 'adminify-admin-columns'),
			'chosen'      => true,
			'placeholder' => 'Select Column Type',
			'options'     => $columns,
		],
		[
			'id'    => 'field_name',
			'title' => esc_html__('Field Name', 'adminify-admin-columns'),
		],
		[
			'id'    => 'function_name',
			'title' => esc_html__('Function Name', 'adminify-admin-columns'),
		],
		[
			'id'    => 'shortcode_name',
			'title' => esc_html__('Shortcode Name', 'adminify-admin-columns'),
		],
		[
			'id'    => 'label',
			'title' => esc_html__('Label', 'adminify-admin-columns'),
		],
		[
			'id'    => 'width',
			'title' => esc_html__('Width', 'adminify-admin-columns'),
			'unit'  => '%',
		],

	];
}

/*
 * Get taxonomy fields
 */
function adminify_get_taxonomy_fields($taxonomy)
{
	$columns = array_map('sanitize_text_field', _adminify__get_taxonomy_all_columns($taxonomy));

	return [
		[
			'id'          => 'type',
			'type'        => 'select',
			'title'       => esc_html__('Column Type', 'adminify-admin-columns'),
			'chosen'      => true,
			'placeholder' => esc_html__('Select Column Type', 'adminify-admin-columns'),
			'options'     => $columns,
		],
		[
			'id'    => 'label',
			'type'  => 'text',
			'title' => esc_html__('Label', 'adminify-admin-columns'),
		],
		[
			'id'    => 'width',
			'type'  => 'slider',
			'title' => esc_html__('Width', 'adminify-admin-columns'),
			'unit'  => '%',
		],

		/*
		 * Register additional fields
		 */
		// [
		// 'id'      => 'new',
		// 'type'    => 'text',
		// 'title'   => 'New'
		// ]

	];
}

function adminify_columns_group_to_options(array $data)
{
	$_data = [];
	foreach ($data as $group) {
		if (!empty($group['group'])) {
			$_data = array_merge($_data, $group['options']);
		} else {
			$_data[$group['name']] = $group['title'];
		}
	}
	return $_data;
}

function adminify_get_custom_admin_columns()
{
	return [
		[
			'name'     => 'word_cound',
			'label'    => esc_html__('Word Count', 'adminify-admin-columns'),
			'callback' => 'adminify_admin_column__word_count',
		],
		[
			'name'     => 'permalink',
			'label'    => esc_html__('Permalink', 'adminify-admin-columns'),
			'callback' => 'adminify_admin_column__permalink',
		],
		[
			'name'     => 'status',
			'label'    => esc_html__('Status', 'adminify-admin-columns'),
			'callback' => 'adminify_admin_column__status',
		],
	];
}

function adminify_admin_column__word_count($object_id)
{
	return str_word_count(get_the_content($object_id));
}

function adminify_admin_column__permalink($object_id)
{
	return get_the_permalink($object_id);
}

function adminify_admin_column__status($object_id)
{
	return get_post_status($object_id);
}
