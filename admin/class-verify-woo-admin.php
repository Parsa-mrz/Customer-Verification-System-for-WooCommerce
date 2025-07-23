<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://parsamirzaie.com
 * @since      1.0.0
 *
 * @package    Verify_Woo
 * @subpackage Verify_Woo/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Verify_Woo
 * @subpackage Verify_Woo/admin
 * @author     Parsa Mirzaie <Mirzaie_parsa@protonmail.ch>
 */
class Verify_Woo_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.∂
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Verify_Woo_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Verify_Woo_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name . '-admin', plugin_dir_url( __FILE__ ) . 'css/verify-woo-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Verify_Woo_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Verify_Woo_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name . '-admin', plugin_dir_url( __FILE__ ) . 'js/verify-woo-admin.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Adds the plugin's administration menu to the WordPress dashboard.
	 *
	 * This function uses `add_menu_page` to create a top-level menu item
	 * for VerifyWoo settings.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function add_admin_menu() {
		add_menu_page(
			'VerifyWoo Settings',
			__( 'VerifyWoo', 'veirfy-woo' ),
			'manage_options',
			'verify_woo_settings_page',
			array( $this, 'render_settings' ),
			'dashicons-shield',
			47
		);
	}

	/**
	 * Renders the settings page for the VerifyWoo plugin in the admin area.
	 *
	 * This function retrieves available tabs, handles the current tab selection,
	 * and includes the content file for the selected tab. It also generates
	 * the navigation tabs.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function render_settings() {
		$tabs        = $this->get_tabs();
		$tabs_data   = array();
		$plugin_icon = PLUGIN_DIR . '/public/partials/verify-woo-pulblic-icon.php';

		foreach ( $tabs as $slug => $tab_info ) {
			$tabs_data[] = array(
				'slug'         => $slug,
				'name'         => $tab_info['name'],
				'content_file' => $this->get_tab_content_file( $tab_info['folder'] ),
			);
		}

		include_once PLUGIN_DIR . '/admin/partials/verify-woo-admin-display.php';
	}

	/**
	 * Retrieves an associative array of available settings tabs.
	 *
	 * This function scans the `admin/partials/tabs/` directory for subdirectories,
	 * treating each subdirectory name as a tab slug and generating a user-friendly
	 * tab name.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @return   string[] An associative array where keys are tab slugs and values are tab names.
	 */
	private function get_tabs(): array {
		$tabs          = array();
		$partials_path = PLUGIN_DIR . '/admin/partials/tabs/';
		$folders       = glob( $partials_path . '*', GLOB_ONLYDIR );

		usort(
			$folders,
			function ( $a, $b ) {
				preg_match( '/^(\d+)-/', basename( $a ), $match_a );
				preg_match( '/^(\d+)-/', basename( $b ), $match_ );
				return intval( $match_a[1] ?? 0 ) <=> intval( $match_[1] ?? 0 );
			}
		);

		foreach ( $folders as $folder ) {
			$original_slug = basename( $folder );
			$clean_slug    = preg_replace( '/^\d+-/', '', $original_slug );
			$display_name  = ucwords( str_replace( array( '-', '_' ), ' ', $clean_slug ) );

			$tabs[ $clean_slug ] = array(
				'name'   => $display_name,
				'folder' => $original_slug,
			);
		}

		return apply_filters( 'verify_woo_admin_settings_tabs', $tabs );
	}

	/**
	 * Gets the content file path for a given tab slug.
	 *
	 * @since 1.0.0
	 * @access private
	 * @param string $slug Tab slug.
	 * @return string|null The content file path or null if not found.
	 */
	private function get_tab_content_file( string $slug ): ?string {
		$file = PLUGIN_DIR . '/admin/partials/tabs/' . $slug . '/content.php';
		return file_exists( $file ) ? $file : null;
	}


	/**
	 * Adds a custom link (e.g., Sponsor) to the plugin row meta section
	 * on the Plugins admin screen.
	 *
	 * @since 1.0.0
	 *
	 * @param string[] $links           An array of the plugin's metadata links.
	 * @param string   $plugin_file_name Path to the plugin file relative to the plugins directory.
	 * @param array    $plugin_data     An array of plugin data. See get_plugin_data().
	 * @param string   $status          Status of the plugin (e.g., 'all', 'active', 'inactive').
	 *
	 * @return string[] Modified array of plugin meta links.
	 */
	public function add_plugin_row_meta( $links, $plugin_file_name, $plugin_data, $status ) {
		if ( 'verify-woo/verify-woo.php' === $plugin_file_name ) {
			$custom_link = '<a href="https://github.com/Parsa-mrz/" target="_blank">⭐️Sponsor</a>';
			$links[]     = $custom_link;
		}

		return $links;
	}
}
