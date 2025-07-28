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

require_once PLUGIN_DIR . '/includes/sms/interfaces/sms-gateway-interface.php';
require_once PLUGIN_DIR . '/includes/sms/drivers/kavenegar/class-verify-woo-kavenegar-http-client.php';
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
	 * - 'kavenegar_sender' (string): The default sender number for SMS.
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
		$sender   = $settings['kavenegar_sender'] ?? '';
		$insecure = $settings['kavenegar_insecure'] ?? false;

		if ( empty( $api_key ) ) {
			error_log( esc_html__( 'Kavenegar API Key is missing in settings.', 'your-text-domain' ) );
		}
		if ( empty( $sender ) ) {
			error_log( esc_html__( 'Kavenegar Sender number is missing in settings.', 'your-text-domain' ) );
		}

		$this->sender = trim( $sender );

		try {
			$this->kavenegar_client = new Verify_Woo_Kavenegar_Http_Client( $api_key, (bool) $insecure );
		} catch ( \Exception $e ) {
			error_log(
				sprintf(
					esc_html__( 'Failed to initialize Kavenegar HTTP Client: %s', 'your-text-domain' ),
					$e->getMessage()
				)
			);
			$this->kavenegar_client = null;
		}
	}

	/**
	 * Returns the human-readable name of the SMS gateway.
	 *
	 * This method is part of the `SmsGatewayInterface` contract.
	 *
	 * @return string The name of the SMS gateway, which is "Kavenegar".
	 * @since 1.0.0
	 */
	public function get_name(): string {
		return 'Kavenegar';
	}

	/**
	 * Sends an SMS message to a specified recipient.
	 *
	 * This method fulfills the `send` contract of the `SmsGatewayInterface`.
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
			error_log( esc_html__( 'Kavenegar client not initialized. Cannot send SMS.', 'your-text-domain' ) );
			return false;
		}

		try {
			$date    = $options['date'] ?? null;
			$type    = $options['type'] ?? null;
			$localid = $options['localid'] ?? null;

			// KavenegarHttpClient expects single parameters for its sendSms method
			$response = $this->kavenegar_client->sendSms( $this->sender, $to, $message, $date, $type, $localid );

			// Check the response from the Kavenegar client if needed, it should be the 'entries' array
			if ( ! is_array( $response ) || empty( $response ) ) {
				error_log( esc_html__( 'Kavenegar send SMS: Unexpected empty response.', 'your-text-domain' ) );
				return false;
			}
			return true;

		} catch ( ApiException $e ) {
			error_log(
				sprintf(
					esc_html__( 'Kavenegar API Error (send): %1$s (Status: %2$d)', 'your-text-domain' ),
					$e->getMessage(),
					$e->getStatus()
				)
			);
			return false;
		} catch ( HttpException $e ) {
			error_log(
				sprintf(
					esc_html__( 'Kavenegar HTTP Error (send): %1$s (Code: %2$d)', 'your-text-domain' ),
					$e->getMessage(),
					$e->getCode()
				)
			);
			return false;
		} catch ( \Exception $e ) {
			error_log(
				sprintf(
					esc_html__( 'An unexpected error occurred during Kavenegar SMS sending: %s', 'your-text-domain' ),
					$e->getMessage()
				)
			);
			return false;
		}
	}

	/**
	 * Sends an SMS message using a pre-defined template pattern (Kavenegar Lookup service).
	 *
	 * This method fulfills the `sendByPattern` contract of the `SmsGatewayInterface`.
	 * It uses the Kavenegar Lookup service, which is suitable for sending
	 * verification codes, OTPs, or other templated messages.
	 *
	 * @param string $receptor The phone number of the recipient.
	 * @param string $template The name of the pre-registered template on your Kavenegar account.
	 * @param string $token    The value to replace the first token (`%token%`) in the template.
	 * @param string $token2   Optional. The value to replace the second token (`%token2%`) in the template.
	 * Defaults to an empty string if not provided.
	 * @param string $token3   Optional. The value to replace the third token (`%token3%`) in the template.
	 * Defaults to an empty string if not provided.
	 * @param string $type     Optional. The type of verification. Can be 'sms' (default) for text message,
	 * or 'call' for a voice call.
	 * @param string $token10  Optional. The value to replace the tenth token (`%token10%`) in the template.
	 * Defaults to an empty string.
	 * @param string $token20  Optional. The value to replace the twentieth token (`%token20%`) in the template.
	 * Defaults to an empty string.
	 * @return bool `true` if the pattern SMS was successfully sent; `false` otherwise.
	 * @since 1.0.0
	 */
	public function sendByPattern( string $receptor, string $template, string $token, string $token2 = '', string $token3 = '', string $type = 'sms', string $token10 = '', string $token20 = '' ): bool {
		if ( is_null( $this->kavenegar_client ) ) {
			error_log( esc_html__( 'Kavenegar client not initialized. Cannot send pattern SMS.', 'your-text-domain' ) );
			return false;
		}

		try {
			$response = $this->kavenegar_client->verifyLookup(
				$receptor,
				$template,
				$token,
				$token2,
				$token3,
				$type,
				$token10,
				$token20
			);

			if ( ! is_array( $response ) || empty( $response ) ) {
				error_log( esc_html__( 'Kavenegar sendByPattern: Unexpected empty response.', 'your-text-domain' ) );
				return false;
			}
			return true;

		} catch ( ApiException $e ) {
			error_log(
				sprintf(
					esc_html__( 'Kavenegar API Error (sendByPattern): %1$s (Status: %2$d)', 'your-text-domain' ),
					$e->getMessage(),
					$e->getStatus()
				)
			);
			return false;
		} catch ( HttpException $e ) {
			error_log(
				sprintf(
					esc_html__( 'Kavenegar HTTP Error (sendByPattern): %1$s (Code: %2$d)', 'your-text-domain' ),
					$e->getMessage(),
					$e->getCode()
				)
			);
			return false;
		} catch ( \Exception $e ) {
			error_log(
				sprintf(
					esc_html__( 'An unexpected error occurred during Kavenegar pattern SMS sending: %s', 'your-text-domain' ),
					$e->getMessage()
				)
			);
			return false;
		}
	}

	// You can implement other methods required by your SmsGatewayInterface here.
}
