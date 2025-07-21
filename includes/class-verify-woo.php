<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://parsamirzaie.com
 * @since      1.0.0
 *
 * @package    Verify_Woo
 * @subpackage Verify_Woo/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Verify_Woo
 * @subpackage Verify_Woo/includes
 * @author     Parsa Mirzaie <Mirzaie_parsa@protonmail.ch>
 */
class Verify_Woo {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Verify_Woo_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;


	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'VERIFY_WOO_VERSION' ) ) {
			$this->version = VERIFY_WOO_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'verify-woo';
		$this->define_constants();
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Verify_Woo_Loader. Orchestrates the hooks of the plugin.
	 * - Verify_Woo_i18n. Defines internationalization functionality.
	 * - Verify_Woo_Admin. Defines all hooks for the admin area.
	 * - Verify_Woo_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once PLUGIN_DIR . '/includes/class-verify-woo-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once PLUGIN_DIR . '/includes/class-verify-woo-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once PLUGIN_DIR . '/admin/class-verify-woo-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once PLUGIN_DIR . '/public/class-verify-woo-public.php';

		/**
		 * The class responsible for defining all actions that occur in the authentication
		 */
		require_once PLUGIN_DIR . '/includes/class-verify-woo-authentication.php';

				/**
		 * The class responsible for defining all actions that occur in the auth redirect
		 */
		require_once PLUGIN_DIR . '/includes/class-verify-woo-auth-redirect.php';

		$this->loader = new Verify_Woo_Loader();
	}

	/**
	 * Define Constants.
	 *
	 * @since   1.0.0
	 */
	private function define_constants() {
		define( 'PLUGIN_DIR', untrailingslashit( plugin_dir_path( __DIR__ ) ) );
		define( 'PLUGIN_URL', untrailingslashit( plugin_dir_url( __DIR__ ) ) );
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Verify_Woo_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Verify_Woo_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin          = new Verify_Woo_Admin( $this->get_plugin_name(), $this->get_version() );
		$plugin_authentication = new Verify_Woo_Authentication( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_menu' );
		$this->loader->add_filter( 'plugin_row_meta', $plugin_admin, 'add_plugin_row_meta', 10, 4 );
		$this->loader->add_action( 'wp_ajax_nopriv_verify_woo_send_otp', $plugin_authentication, 'wp_ajax_send_otp' );
		$this->loader->add_action( 'wp_ajax_verify_woo_send_otp', $plugin_authentication, 'wp_ajax_send_otp' );
		$this->loader->add_action( 'wp_ajax_nopriv_verify_woo_check_otp', $plugin_authentication, 'wp_ajax_check_otp' );
		$this->loader->add_action( 'wp_ajax_verify_woo_check_otp', $plugin_authentication, 'wp_ajax_check_otp' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public         = new Verify_Woo_Public( $this->get_plugin_name(), $this->get_version() );
		$plugin_authentication = new Verify_Woo_Authentication( $this->get_plugin_name(), $this->get_version() );
		$plugin_auth_redirect  = new Verify_Woo_Auth_Redirect( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_filter( 'woocommerce_locate_template', $plugin_authentication, 'myplugin_disable_wc_login_form_template', 100, 3 );
		$this->loader->add_action( 'woocommerce_login_form', $plugin_public, 'register_authentication_form' );
		$this->loader->add_action( 'template_redirect', $plugin_auth_redirect, 'maybe_redirect_to_login' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Verify_Woo_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
