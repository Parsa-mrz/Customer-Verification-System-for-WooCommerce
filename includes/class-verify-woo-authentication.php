<?php
/**
 * Handles authentication customization for Woocommerce.
 *
 * @package VerifyWoo
 * @since   1.0.0
 */
class Verify_Woo_Authentication {

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
	 * Override the default WooCommerce login form template with a custom template.
	 *
	 * Hooked into `woocommerce_locate_template`.
	 *
	 * @since 1.0.0
	 *
	 * @param string $template       Path to the template found.
	 * @param string $template_name  Name of the template file.
	 * @param string $template_path  Path to the templates directory.
	 *
	 * @return string Path to the custom template if it matches; otherwise, original template.
	 */
	public function myplugin_disable_wc_login_form_template( $template, $template_name, $template_path ) {
		if ( 'myaccount/form-login.php' === $template_name ) {
			return PLUGIN_DIR . '/public/partials/forms/verify-woo-form-1.php';
		}
		return $template;
	}
}
