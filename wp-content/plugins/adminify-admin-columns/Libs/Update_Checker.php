<?php
namespace WPAdminify\Modules\AdminColumns\Libs;

class Update_Checker {

	private $file;

	private $plugin;

	private $basename;

	private $active;

	private $store_uri;

	private $repository;

	public function __construct( $file ) {

		$this->file = $file;

		$this->store_uri = 'https://api.wpadminify.com/adminify-update-checker/';

		add_action( 'admin_init', array( $this, 'set_plugin_properties' ) );

		return $this;
	}

	public function set_plugin_properties() {
		$this->plugin	= get_plugin_data( $this->file );
		$this->basename = ADMINCOLUMNS_BASE;
		$this->active	= is_plugin_active( $this->basename );
	}

	private function get_repository_info() {
		if(empty($this->basename)){
			return;
		}

	    if ( is_null( $this->repository ) ) { // Do we have a response?

			$request_uri = add_query_arg([
				'plugin_slug' => current( explode('/', $this->basename ) ),
				'action' => 'get_repository_info'
			], $this->store_uri );

	        $response = json_decode( wp_remote_retrieve_body( wp_remote_get( $request_uri ) ), true ); // Get JSON and parse it

			if ( empty($response) ) {
				return;
			}

			$response['download_url'] = add_query_arg([
				'plugin_slug' => current( explode('/', $this->basename ) ),
				'action' => 'download_plugin',
				'version' => $response['name']
			], $this->store_uri );

	        $this->repository = $response; // Set it to our property
	    }
	}

	public function initialize() {
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'modify_transient' ) );
		add_filter( 'plugins_api', array( $this, 'plugin_popup' ), 10, 3);
		add_filter( 'upgrader_post_install', array( $this, 'after_install' ), 10, 3 );
	}

	public function modify_transient( $transient ) {

		if ( empty($transient->checked) ) return $transient;

		$this->get_repository_info(); // Get the repo info

		if ( empty( $this->repository ) ) return $transient;
			
		if ( version_compare( $transient->checked[ $this->basename ], $this->repository['tag_name'], '<' ) ) {

			$new_files = $this->repository['download_url']; // Get the ZIP

			$plugin = array(
				'url' => $this->plugin["PluginURI"],
				'slug' => current( explode('/', $this->basename ) ),
				'package' => $new_files,
				'new_version' => $this->repository['tag_name']
			);

			$transient->response[$this->basename] = (object) $plugin; // Return it in response
		}

		return $transient; // Return filtered transient
	}

	public function plugin_popup( $result, $action, $args ) {

		if( ! empty( $args->slug ) ) { // If there is a slug

			if( $args->slug == current( explode( '/' , $this->basename ) ) ) { // And it's our slug

				$this->get_repository_info(); // Get our repo info

				// Set it to an array
				$plugin = array(
					'name'				=> $this->plugin["Name"],
					'slug'				=> $this->basename,
					'requires'			=> '4.0',
					'tested'			=> '6.4.2',
					'rating'			=> '100.0',
					'num_ratings'		=> '10823',
					'downloaded'		=> '14249',
					'added'				=> '2024-01-30',
					'version'			=> $this->repository['tag_name'],
					'author'			=> $this->plugin["AuthorName"],
					'author_profile'	=> $this->plugin["AuthorURI"],
					'last_updated'		=> $this->repository['published_at'],
					'homepage'			=> $this->plugin["PluginURI"],
					'short_description' => $this->plugin["Description"],
					'sections'			=> array(
						'Description'	=> $this->plugin["Description"],
						'Updates'		=> $this->repository['body'],
					),
					'download_link'		=> $this->repository['download_url']
				);

				return (object) $plugin; // Return the data
			}

		}
		return $result; // Otherwise return default
	}

	public function after_install( $response, $hook_extra, $result ) {
		global $wp_filesystem; // Get global FS object

		$install_directory = plugin_dir_path( $this->file ); // Our plugin directory
		$wp_filesystem->move( $result['destination'], $install_directory ); // Move files to the plugin dir
		$result['destination'] = $install_directory; // Set the destination for the rest of the stack

		if ( $this->active ) { // If it was active
			activate_plugin( $this->basename ); // Reactivate
		}

		return $result;
	}
}