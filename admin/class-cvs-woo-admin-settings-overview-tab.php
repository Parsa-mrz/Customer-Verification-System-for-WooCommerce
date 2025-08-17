<?php
/**
 * Admin Settings Class: Overview Tab
 *
 * This file contains the class responsible for rendering and handling
 * the "Overview" tab settings in the Verify-Woo plugin admin page.
 *
 * @package    Verify_Woo
 * @subpackage Verify_Woo/admin
 * @author      Parsamirzaie
 * @link        https://parsamirzaie.com
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Class verify_Woo_Admin_Settings_overview
 *
 * Handles registration, sanitization, and rendering of the "Overview" tab settings
 * for the Verify-Woo plugin.
 *
 * Responsibilities:
 * - Register plugin settings using the Settings API.
 * - Provide sanitization logic for form inputs.
 * - Render toggle fields for enabling/disabling features.
 *
 * @since 1.0.0
 */
class Cvs_Woo_Admin_Settings_Overview_Tab {

	/**
	 * The name of the option group for the overview settings.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const OPTION_GROUP = 'verify_woo_overview_settings';

	/**
	 * Register settings, sections, and fields using WordPress Settings API.
	 *
	 * Hooks into WordPress admin to:
	 * - Register the setting group `verify_woo_settings_group`.
	 * - Create a settings section called `verify_woo_main_section`.
	 * - Add a checkbox field for "Activate Login Page" under the section.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			'verify_woo_settings_overview_group',
			self::OPTION_GROUP,
			array( $this, 'sanitize_settings' )
		);

		add_settings_section(
			'verify_woo_main_section',
			__( 'Main Settings', 'customer-verification-system-for-woocommerce' ),
			function () {
				echo '<p>' . esc_html__( 'Configure the login settings below.', 'customer-verification-system-for-woocommerce' ) . '</p>';
			},
			'verify_woo_settings_page_overview'
		);

		add_settings_field(
			'overview',
			'',
			array( $this, 'render_field' ),
			'verify_woo_settings_page_overview',
			'verify_woo_main_section'
		);
	}

	/**
	 * Sanitize the submitted plugin settings before saving to the database.
	 *
	 * Currently only sanitizes the "activation" toggle.
	 * Converts it to an integer (1 or 0).
	 *
	 * @since 1.0.0
	 *
	 * @param array $input The raw submitted input from the settings form.
	 *
	 * @return array $sanitized The cleaned version of the input to store.
	 */
	public function sanitize_settings( $input ) {
		$sanitized                      = array();
		$sanitized['activation']        = ! empty( $input['activation'] ) ? true : false;
		$sanitized['checkout_redirect'] = ! empty( $input['checkout_redirect'] ) ? true : false;

		Cvs_Woo_Admin_Notice::add_success( __( 'Settings Saved', 'customer-verification-system-for-woocommerce' ) );

		return $sanitized;
	}

	/**
	 * Render the "Activate Login Page" field in the admin UI.
	 *
	 * Outputs a modern toggle switch checkbox. Uses saved value from
	 * the `verify_woo_settings` option.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_field() {
		$options = get_option( self::OPTION_GROUP );
		Cvs_Woo_Admin_Settings_Field_Factory::toggle(
			$options,
			'activation',
			self::OPTION_GROUP,
			__( 'Enable Custom Login Page', 'customer-verification-system-for-woocommerce' ),
			__( 'Use a custom login page for WooCommerce, redirecting users from the default login', 'customer-verification-system-for-woocommerce' )
		);

		Cvs_Woo_Admin_Settings_Field_Factory::toggle(
			$options,
			'checkout_redirect',
			self::OPTION_GROUP,
			__( 'Enable Checkout Login Redirect', 'customer-verification-system-for-woocommerce' ),
			__( 'Redirect customers to the login page during checkout and back to checkout after successful login', 'customer-verification-system-for-woocommerce' )
		);
	}
}
