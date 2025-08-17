<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://parsamirzaie.com
 * @since      1.0.0
 *
 * @package    Verify_Woo
 * @subpackage Verify_Woo/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Verify_Woo
 * @subpackage Verify_Woo/public
 * @author     Parsa Mirzaie <Mirzaie_parsa@protonmail.ch>
 */
class Cvs_Public {

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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cvs-woo-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cvs-woo-public.js', array( 'jquery' ), $this->version, false );
		wp_localize_script(
			$this->plugin_name,
			'verifyWooVars',
			array(
				'ajax_url'        => admin_url( 'admin-ajax.php' ),
				'nonce'           => wp_create_nonce( 'verify_woo_otp_nonce' ),
				'expire_time_otp' => apply_filters( 'verify_woo_otp_expiration', OTP::EXPIRE_TIME->value ),
			)
		);
	}

	/**
	 * Output the custom authentication form.
	 *
	 * This method includes and displays the authentication form template.
	 * The output is buffered and escaped to ensure safe rendering.
	 *
	 * @since 1.0.0
	 * @return ?string The path to the custom authentication form template.
	 */
	public function register_authentication_form() {
		ob_start();
		/**
		 * Filter the path to the custom login form template.
		 *
		 * Allows developers to override the path to the login form template
		 * used to replace WooCommerce's default login form.
		 *
		 * @since 1.0.0
		 *
		 * @param string $custom_template_path Full path to the custom login form.
		 */
		$custom_template = apply_filters( 'verify_woo_login_form_template_path', CVS_PLUGIN_DIR . '/public/partials/forms/cvs-woo-public-form-1.php' );

		if ( file_exists( $custom_template ) ) {
			return $custom_template;
		}
		echo wp_kses_post( ob_get_clean() );
	}
}
