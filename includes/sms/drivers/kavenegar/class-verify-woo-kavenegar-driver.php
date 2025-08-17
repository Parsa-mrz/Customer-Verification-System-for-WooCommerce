<?php
/**
 * Kavenegar SMS Gateway Driver for Verify Woo.
 *
 * This file contains the implementation of the SmsGatewayInterface for the
 * Kavenegar SMS service. It acts as an adapter, translating generic SMS
 * sending requests into specific calls to the Kavenegar API via the
 * KavenegarHttpClient. It handles configuration retrieval, client initialization,
 * and error handling specific to SMS operations.
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
 * Kavenegar SMS Gateway Driver for Verify Woo.
 *
 * This class serves as the concrete implementation of the `Sms_Gateway_Interface`
 * for sending SMS messages using the Kavenegar API. It acts as a bridge between
 * your plugin's generic SMS functionality and the Kavenegar-specific communication logic
 * provided by `KavenegarHttpClient`.
 *
 * It manages the Kavenegar API key, sender number, and delegates the actual HTTP
 * requests and response handling to the `KavenegarHttpClient`. It also includes
 * robust error logging to the WordPress error log for debugging and operational monitoring.
 *
 * @since 1.0.0
 */
class Verify_Woo_Kavenegar_Driver implements Sms_Gateway_Interface {

	/**
	 * The Kavenegar HTTP client instance.
	 *
	 * This client is responsible for direct communication with the Kavenegar API.
	 * It is initialized in the constructor based on provided settings.
	 *
	 * @var Verify_Woo_Kavenegar_Http_Client Holds an instance of KavenegarHttpClient if successfully initialized,
	 * otherwise null if initialization failed.
	 */
	protected Verify_Woo_Kavenegar_Http_Client $kavenegar_client;

	/**
	 * The default sender number used for Kavenegar SMS messages.
	 *
	 * This number is configured in your plugin's settings and passed during
	 * the driver's instantiation.
	 *
	 * @var string
	 */
	protected string $sender;

	/**
	 * Constructor for Verify_Woo_Kavenegar_Driver.
	 *
	 * Initializes the SMS gateway driver by retrieving Kavenegar API settings
	 * (API key, sender, and insecure flag) and then instantiating the
	 * `KavenegarHttpClient`. It performs validation on essential settings
	 * and logs errors if the client cannot be successfully initialized.
	 *
	 * @param array $settings Optional. An associative array of Kavenegar API settings.
	 * Expected keys:
	 * - 'kavenegar_api_key' (string): Your Kavenegar API key.
	 * - 'kavenegar_sender_number' (string): The default sender number for SMS.
	 * - 'kavenegar_insecure' (bool, optional): Whether to use HTTP (true) or HTTPS (false).
	 * Defaults to `false`.
	 * If `$settings` is empty, it attempts to fetch settings from WordPress options
	 * using `Verify_Woo_Admin_Settings_Sms_Gateway_Tab::OPTION_GROUP`.
	 * @since 1.0.0
	 */
	public function __construct( array $settings = array() ) {
		if ( empty( $settings ) && class_exists( Verify_Woo_Admin_Settings_Sms_Gateway_Tab::class ) ) {
			$settings = get_option( Verify_Woo_Admin_Settings_Sms_Gateway_Tab::OPTION_GROUP, array() );
		}

		$api_key  = $settings['kavenegar_api_key'] ?? '';
		$sender   = $settings['kavenegar_sender_number'] ?? '';
		$insecure = $settings['kavenegar_insecure'] ?? false;

		if ( empty( $api_key ) ) {
			error_log( esc_html__( 'Kavenegar API Key is missing in settings.', 'verify-woo' ) );
		}
		if ( empty( $sender ) ) {
			error_log( esc_html__( 'Kavenegar Sender number is missing in settings.', 'verify-woo' ) );
		}

		$this->sender = trim( $sender );

		try {
			$this->kavenegar_client = new Verify_Woo_Kavenegar_Http_Client( $api_key, (bool) $insecure );
		} catch ( \Exception $e ) {
			error_log(
				sprintf(
					// Translators: %s is API error.
					esc_html__( 'Failed to initialize Kavenegar HTTP Client: %s', 'verify-woo' ),
					$e->getMessage()
				)
			);
			$this->kavenegar_client = null;
		}
	}

	/**
	 * Sends an SMS message to a specified recipient.
	 *
	 * This method fulfills the `send` contract of the `Sms_Gateway_Interface`.
	 * It delegates the actual sending logic to the `KavenegarHttpClient`.
	 *
	 * @param string $to      The phone number of the recipient in international format (e.g., +1234567890).
	 * @param string $message The content of the SMS message to be sent.
	 * @param array  $options Optional. An associative array of additional parameters for sending SMS.
	 * Common keys might include:
	 * - 'date' (int): Unix timestamp for delayed sending.
	 * - 'type' (int): Message type (e.g., `Kavenegar\Enums\General::SMS_TYPE_NORMAL`).
	 * - 'localid' (string|array): Your internal message ID(s) associated with the SMS.
	 * @return bool `true` if the SMS was successfully sent according to Kavenegar's response; `false` otherwise.
	 * @since 1.0.0
	 */
	public function send( string $to, string $message, array $options = array() ): bool {
		if ( is_null( $this->kavenegar_client ) ) {
			error_log( esc_html__( 'Kavenegar client not initialized. Cannot send SMS.', 'verify-woo' ) );
			return false;
		}

		try {
			$date    = $options['date'] ?? null;
			$type    = $options['type'] ?? null;
			$localid = $options['localid'] ?? null;

			$response = $this->kavenegar_client->send_sms( $this->sender, $to, $message, $date, $type, $localid );

			if ( ! is_array( $response ) || empty( $response ) ) {
				error_log( esc_html__( 'Kavenegar send SMS: Unexpected empty response.', 'verify-woo' ) );
				return false;
			}
			return true;

		} catch ( \Exception $e ) {
			error_log(
				sprintf(
					// Translators: %s is API error.
					esc_html__( 'An unexpected error occurred during Kavenegar SMS sending: %s', 'verify-woo' ),
					$e->getMessage()
				)
			);
			return false;
		}
	}

	/**
	 * Sends an SMS message using a pre-defined pattern or template via Kavenegar's Lookup service.
	 *
	 * This method fulfills the `send_by_pattern` contract of the `Sms_Gateway_Interface`.
	 * It maps the generic `pattern` and `data` parameters to Kavenegar's `verifyLookup`
	 * method, which utilizes tokens for templated messages.
	 *
	 * @param string $receptor The recipient's phone number in a valid format (e.g., E.164).
	 * @param string $pattern  The name of the pre-registered Kavenegar template to use.
	 * @param array  $data     An associative array of data to populate the placeholders within the pattern/template.
	 * Expected keys usually include 'token', 'token2', 'token3', 'token10', 'token20',
	 * and 'type'.
	 * @param array  $options  Optional. An associative array of additional provider-specific options.
	 * Currently, this method assumes `type` might be passed via `$data`
	 * but can be extended to use `$options` for other parameters.
	 * @return bool True if the message was successfully queued for sending (or sent) via pattern, false otherwise.
	 * @since 1.0.0
	 */
	public function send_by_pattern( string $receptor, string $pattern, array $data = array(), array $options = array() ): bool {
		if ( is_null( $this->kavenegar_client ) ) {
			error_log( esc_html__( 'Kavenegar client not initialized. Cannot send pattern SMS.', 'verify-woo' ) );
			return false;
		}

		try {
			$response = $this->kavenegar_client->verify_lookup(
				$receptor,
				$pattern,
				$data['token'],
				$data['token2'],
				$data['token3'],
				$data['type'],
				$data['token10'],
				$data['token20']
			);

			if ( ! is_array( $response ) || empty( $response ) ) {
				error_log( esc_html__( 'Kavenegar sendByPattern: Unexpected empty response.', 'verify-woo' ) );
				return false;
			}
			return true;

		} catch ( \Exception $e ) {
			error_log(
				sprintf(
					// Translators: %s is API error.
					esc_html__( 'An unexpected error occurred during Kavenegar pattern SMS sending: %s', 'verify-woo' ),
					$e->getMessage()
				)
			);
			return false;
		}
	}
}
