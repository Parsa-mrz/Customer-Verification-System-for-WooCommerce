<?php
/**
 * Handles authentication redirection for WooCommerce checkout.
 *
 * Ensures guest users attempting to access the checkout page
 * are redirected to the WooCommerce "My Account" login page.
 * Includes support for redirecting users back to checkout after login,
 * and sets a query flag to display a custom login message if needed.
 *
 * This enhances the guest-to-login flow for smoother UX.
 *
 * @link       https://parsamirzaie.com
 * @since      1.0.0
 * @package    Verify_Woo
 * @subpackage Verify_Woo/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Class Verify_Woo_Redirect
 *
 * Handles redirection of unauthenticated users from WooCommerce checkout
 * to the login page, appending a redirect parameter and custom message key.
 *
 * Example use:
 * Hook `maybe_redirect_to_login()` into `template_redirect` to activate behavior.
 *
 * Hooks into:
 * - `template_redirect`
 *
 * @since 1.0.0
 * @package Verify_Woo
 */
class Verify_Woo_Redirect {
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
		$admin_overview_options = get_option( Verify_Woo_Admin_Settings_Overview_Tab::OPTION_GROUP );
		if ( $admin_overview_options['checkout_redirect'] ) {
			if ( is_checkout() && ! is_user_logged_in() ) {
				$my_account_url = wc_get_page_permalink( 'myaccount' );

				$redirect_url = add_query_arg(
					array(
						'verifywoo_redirect_url' => rawurlencode( wc_get_checkout_url() ),
						'verifywoo_msg'          => 'login_checkout_required',
					),
					$my_account_url
				);
				wp_safe_redirect( $redirect_url );
				exit;
			}
		}
	}
}
