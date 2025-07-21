<?php
/**
 * Handles authentication redirection for WooCommerce checkout.
 *
 * This class ensures that users are redirected to the login page
 * (My Account page) if they attempt to access the checkout page
 * without being logged in. It also appends a custom message
 * and a redirect URL to bring the user back to the checkout page after login.
 *
 * @link       https://parsamirzaie.com
 * @since      1.0.0
 * @package    Verify_Woo
 * @subpackage Verify_Woo/includes
 */

/**
 * Class Verify_Woo_Auth_Redirect
 *
 * Redirects unauthenticated users trying to access WooCommerce checkout
 * to the login (My Account) page with optional redirect and custom message.
 *
 * @since      1.0.0
 * @package    Verify_Woo
 */
class Verify_Woo_Auth_Redirect {
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
	 * Redirects guest users from checkout to the login (My Account) page.
	 *
	 * If the user is not logged in and visits the checkout page,
	 * they are redirected to the My Account page with a redirect URL
	 * back to checkout and a message flag.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function maybe_redirect_to_login() {
		if ( is_checkout() && ! is_user_logged_in() ) {
			$my_account_url = wc_get_page_permalink( 'myaccount' );

			$redirect_url = add_query_arg(
				array(
					'verifywoo_redirect_url' => rawurlencode( wc_get_checkout_url() ),
					'verifywoo_msg'          => 'login_checkout_required',
				),
				$my_account_url
			);

			wp_redirect( $redirect_url );
			exit;
		}
	}
}
