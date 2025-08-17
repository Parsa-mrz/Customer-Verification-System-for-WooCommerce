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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
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
			__( 'SMS Gateway Settings', 'customer-verification-system-for-woocommerce' ),
			function () {
				echo '<p>' . esc_html__( 'Configure the gateway settings below.', 'customer-verification-system-for-woocommerce' ) . '</p>';
			},
			'verify_woo_settings_page_sms_gateway'
		);

		add_settings_field(
			'sms_gateway',
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
		$sanitized['sms_gateway']    = ! empty( $input['sms_gateway'] ) ? sanitize_text_field( $input['sms_gateway'] ) : '';

		if ( 'kavenegar' === $sanitized['sms_gateway'] && $sanitized['sms_activation'] ) {
			$sanitized['kavenegar_api_key']       = isset( $input['kavenegar_api_key'] ) ? sanitize_text_field( $input['kavenegar_api_key'] ) : '';
			$sanitized['kavenegar_sender_number'] = isset( $input['kavenegar_sender_number'] ) ? sanitize_text_field( $input['kavenegar_sender_number'] ) : '';
		}

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
			__( 'Activate SMS', 'customer-verification-system-for-woocommerce' ),
			__( 'Active sms gateway to send OTP to Users', 'customer-verification-system-for-woocommerce' )
		);
		if ( $options['sms_activation'] ) {
			Verify_Woo_Admin_Settings_Field_Factory::render_setting_row(
				function () use ( $options ) {
						$this->render_sms_gateway_dropdown( $options );
					if ( 'kavenegar' === $options['sms_gateway'] ) {
						$this->render_kavenegar_fields( $options );
					}
				}
			);
		}
	}

	/**
	 * Renders the SMS Gateway dropdown field.
	 *
	 * @since 1.0.0
	 * @param array $options Current settings options.
	 * @return void
	 */
	private function render_sms_gateway_dropdown( $options ) {
		Verify_Woo_Admin_Settings_Field_Factory::drop_down(
			$options,
			'sms_gateway',
			self::OPTION_GROUP,
			__( 'SMS Gateway', 'customer-verification-system-for-woocommerce' ),
			__( 'Select the SMS gateway you want to use for sending messages.', 'customer-verification-system-for-woocommerce' ),
			array(
				'select'    => __( 'Select SMS Gateway', 'customer-verification-system-for-woocommerce' ),
				'kavenegar' => __( 'Kavenegar', 'customer-verification-system-for-woocommerce' ),
			)
		);
	}

	/**
	 * Renders the Kavenegar specific input fields.
	 *
	 * @since 1.0.0
	 * @param array $options Current settings options.
	 * @return void
	 */
	private function render_kavenegar_fields( $options ) {
		Verify_Woo_Admin_Settings_Field_Factory::input(
			$options,
			'kavenegar_api_key',
			self::OPTION_GROUP,
			__( 'Kavenegar API Key', 'customer-verification-system-for-woocommerce' ),
			__( 'Enter your KaveNegar API key to enable SMS sending.', 'customer-verification-system-for-woocommerce' ),
			'text',
			__( 'Enter your KaveNegar API Key', 'customer-verification-system-for-woocommerce' ),
			'50'
		);

		Verify_Woo_Admin_Settings_Field_Factory::input(
			$options,
			'sms_gateway_pattern',
			self::OPTION_GROUP,
			__( 'Kavenegar Pattern Code', 'customer-verification-system-for-woocommerce' ),
			__( 'If using Kavenegar\'s Lookup service, enter the specific pattern code (template ID) for sending OTPs. Leave blank to send standard SMS messages.', 'customer-verification-system-for-woocommerce' ),
			'text',
			__( 'e.g., verify_code_template', 'customer-verification-system-for-woocommerce' ),
			'50'
		);

		Verify_Woo_Admin_Settings_Field_Factory::input(
			$options,
			'kavenegar_sender_number',
			self::OPTION_GROUP,
			__( 'Kavenegar Sender Number', 'customer-verification-system-for-woocommerce' ),
			__( 'Enter the sender number you registered with KaveNegar.', 'customer-verification-system-for-woocommerce' ),
			'text',
			__( 'Enter your KaveNegar Sender Number', 'customer-verification-system-for-woocommerce' ),
			'50'
		);
	}
}
