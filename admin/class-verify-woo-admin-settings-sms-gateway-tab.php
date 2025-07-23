<?php
/**
 * Admin Settings Class: SMS Gateway Tab
 *
 * This file contains the class responsible for registering and rendering the
 * "SMS Gateway" tab in the Verify-Woo plugin admin settings.
 *
 * It uses the WordPress Settings API to:
 * - Register a new settings group and section for SMS Gateway configuration.
 * - Provide a custom sanitize callback for validating input.
 * - Render the setting fields used to control the SMS gateway behavior.
 *
 * This class ensures that plugin settings related to SMS functionality are
 * modular, extendable, and compliant with WordPress coding standards.
 *
 * @package    Verify_Woo
 * @subpackage Verify_Woo/admin/settings
 * @author     Parsamirzaie
 * @link       https://parsamirzaie.com
 * @since      1.0.0
 */

/**
 * Handles the registration and rendering of the SMS Gateway settings tab in the WooCommerce admin.
 *
 * This class is responsible for:
 * - Registering a settings group, section, and field for SMS Gateway configurations.
 * - Sanitizing the input received from the settings form.
 * - Rendering the individual settings fields, including an activation toggle for SMS.
 *
 * @since 1.0.0
 */
class Verify_Woo_Admin_Settings_Sms_Gateway_Tab {

	/**
	 * The name of the option group for the sms gateway settings.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const OPTION_GROUP = 'verify_woo_sms_gateway_settings';

	/**
	 * Registers the settings, section, and field for the SMS Gateway tab.
	 *
	 * This method uses WordPress's Settings API to define how the settings are
	 * structured and handled. It registers:
	 * - A setting group 'verify_woo_settings_sms_gateway_group'.
	 * - A setting field 'verify_woo_sms_gateway_settings' with a custom sanitize callback.
	 * - A settings section 'verify_woo_sms_gateway_section' for SMS Gateway configurations.
	 * - A settings field 'activation' within the defined section, which will be rendered by `render_field()`.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			'verify_woo_settings_sms_gateway_group',
			self::OPTION_GROUP,
			array( $this, 'sanitize_settings' )
		);

		add_settings_section(
			'verify_woo_sms_gateway_section',
			__( 'SMS Gateway Settings', 'verify-woo' ),
			function () {
				echo '<p>' . esc_html__( 'Configure the gateway settings below.', 'verify-woo' ) . '</p>';
			},
			'verify_woo_settings_page_sms_gateway'
		);

		add_settings_field(
			'activation',
			'',
			array( $this, 'render_field' ),
			'verify_woo_settings_page_sms_gateway',
			'verify_woo_sms_gateway_section'
		);
	}

	/**
	 * Sanitizes the SMS Gateway settings input.
	 *
	 * This method is a callback for `register_setting` and is responsible for
	 * validating and sanitizing the data submitted from the settings form.
	 * Currently, it sanitizes the 'sms_activation' field.
	 *
	 * @since 1.0.0
	 *
	 * @param array $input The raw input array from the settings form.
	 * @return array The sanitized array of settings.
	 */
	public function sanitize_settings( $input ) {
		$sanitized                   = array();
		$sanitized['sms_activation'] = ! empty( $input['sms_activation'] ) ? true : false;
		return $sanitized;
	}

	/**
	 * Renders the activation field for SMS Gateway settings.
	 *
	 * This method retrieves the current settings and outputs the HTML for
	 * the 'Activate SMS' toggle switch and its description.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_field() {
		$options = get_option( self::OPTION_GROUP );
		Verify_Woo_Admin_Settings_Field_Factory::toggle(
			$options,
			'sms_activation',
			self::OPTION_GROUP,
			__( 'Activate SMS', 'verify-woo' ),
			__( 'Active sms gateway to send OTP to Users', 'verify-woo' )
		);
	}
}
