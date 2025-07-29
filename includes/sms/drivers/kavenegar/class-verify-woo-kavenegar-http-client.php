<?php
/**
 * Kavenegar API HTTP Client for WordPress.
 *
 * This file contains the KavenegarHttpClient class, which is responsible for
 * making HTTP requests to the Kavenegar SMS API using WordPress's built-in
 * HTTP API functions (wp_remote_post, etc.). It abstracts the low-level
 * communication and handles API key management, URL construction, and
 * error processing, throwing custom exceptions for API-specific or HTTP errors.
 *
 * @link       https://parsamirzaie.com
 * @since      1.0.0
 * @package    Verify_Woo
 * @subpackage Verify_Woo/includes
 */

/**
 * Kavenegar API HTTP Client using WordPress's HTTP API.
 *
 * This class provides a wrapper for interacting with the Kavenegar SMS API.
 * It handles the construction of API URLs, execution of POST requests using
 * `wp_remote_post()`, and the parsing of responses. It throws specific
 * `ApiException` or `HttpException` in case of errors from the API or network.
 *
 * It is designed to be used by a higher-level driver or gateway class that
 * implements a common SMS interface for your plugin.
 *
 * @since 1.0.0
 */
class Verify_Woo_Kavenegar_Http_Client {

	/**
	 * Kavenegar API base path format string.
	 *
	 * This constant defines the sprintf format for constructing the full API endpoint URL.
	 * It expects placeholders for protocol (http/https), API key, base path (sms/verify/account),
	 * and method (send/lookup/info).
	 *
	 * @var string
	 */
	const API_PATH = '%s://api.kavenegar.com/v1/%s/%s/%s.json/';

	/**
	 * The current version of the Kavenegar HTTP Client.
	 *
	 * @var string
	 */
	const VERSION = '1.2.2';

	/**
	 * The API key used for authenticating with the Kavenegar API.
	 *
	 * This key is provided in the constructor and used in constructing API URLs.
	 *
	 * @var string
	 */
	protected string $api_key;

	/**
	 * Determines whether to use insecure HTTP (`true`) or secure HTTPS (`false`) for API calls.
	 *
	 * Defaults to `false` (HTTPS) for security best practices.
	 *
	 * @var bool
	 */
	protected bool $insecure;

	/**
	 * KavenegarHttpClient constructor.
	 *
	 * Initializes the Kavenegar HTTP client with the provided API key and security setting.
	 * It also performs initial checks for the API key presence and WordPress HTTP API availability.
	 *
	 * @param string $api_key  The Kavenegar API key obtained from your Kavenegar account.
	 * @param bool   $insecure Optional. If `true`, forces communication over HTTP instead of HTTPS. Defaults to `false` (HTTPS is recommended).
	 * @throws \RuntimeException If the API key is empty or WordPress HTTP API functions are not available, leading to a fatal error via `wp_die()`.
	 * @since 1.0.0
	 */
	public function __construct( string $api_key, bool $insecure = false ) {
		if ( empty( $api_key ) ) {
			wp_die( esc_html__( 'Kavenegar API Key is empty.', 'verify-woo' ) );
		}

		$this->api_key  = trim( $api_key );
		$this->insecure = (bool) $insecure;

		if ( ! function_exists( 'wp_remote_post' ) ) {
			wp_die( esc_html__( 'WordPress HTTP API functions are not available. Make sure wp-includes/pluggable.php is loaded.', 'verify-woo' ) );
		}
	}

	/**
	 * Constructs the full API URL for a specific Kavenegar method.
	 *
	 * This method dynamically builds the endpoint URL by injecting the protocol (http/https),
	 * the API key, the base API path (e.g., 'sms', 'verify', 'account'), and the specific method.
	 *
	 * @param string $method The specific Kavenegar API method to call (e.g., 'send', 'lookup', 'info', 'status').
	 * @param string $base   Optional. The base path for the API group. Defaults to 'sms' for general SMS operations.
	 * Other examples include 'verify' for lookup services or 'account' for account info.
	 * @return string The fully qualified URL for the Kavenegar API endpoint.
	 * @since 1.0.0
	 */
	protected function get_path( string $method, string $base = 'sms' ): string {
		$protocol = $this->insecure ? 'http' : 'https';
		return sprintf( self::API_PATH, $protocol, $this->api_key, $base, $method );
	}

	/**
	 * Executes an HTTP POST request to the specified URL with provided data.
	 *
	 * This method uses WordPress's `wp_remote_post` function to send the request,
	 * handles timeouts, redirections, SSL verification, and custom headers. It
	 * processes the API response, logs errors, and returns relevant data or `false`
	 * on failure.
	 *
	 * @param string $url  The full URL of the Kavenegar API endpoint to which the request will be sent.
	 * @param array  $data Optional. An associative array of data to be sent as the request body. Defaults to an empty array.
	 * @return object|null|false Returns the 'entries' part of the JSON response object if the request is successful and the API
	 * returns data. Returns `null` if 'entries' is not set in a successful response. Returns `false`
	 * if a WordPress HTTP error occurs, the HTTP status code is not 200, the JSON response is invalid,
	 * or a Kavenegar API-specific error is indicated in the response.
	 * @since 1.0.0
	 */
	public function execute( string $url, array $data = array() ) {
		$args = array(
			'method'      => 'POST',
			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => array(
				'Accept'       => 'application/json',
				'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8',
			),
			'body'        => $data,
			'sslverify'   => ! $this->insecure,
			'user-agent'  => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ) . '; Kavenegar-PHP-Client/WP-Driver/' . self::VERSION,
		);

		$response = wp_remote_post( $url, $args );

		if ( is_wp_error( $response ) ) {
			error_log( 'Kavenegar HTTP Error: ' . $response->get_error_message() );
			return false;
		}

		$http_code = wp_remote_retrieve_response_code( $response );
		$body      = wp_remote_retrieve_body( $response );

		$json_response = json_decode( $body );

		if ( 200 !== $http_code || is_null( $json_response ) || ! isset( $json_response->return ) ) {
			$error_message = sprintf(
				// Translators: %s is API error.
				esc_html__( 'Kavenegar API: HTTP Status %1$d or invalid JSON response. Response: %2$s', 'verify-woo' ),
				$http_code,
				$body
			);
			error_log( $error_message );
			return false;
		}

		if ( 200 !== (int) $json_response->return->status ) {
			$api_message = $json_response->return->message ?? esc_html__( 'Unknown Kavenegar API error.', 'verify-woo' );
			$api_status  = $json_response->return->status ?? 0;
			error_log(
				sprintf(
					// Translators: %s is API error.
					esc_html__( 'Kavenegar API Error: %1$s (Status: %2$d)', 'verify-woo' ),
					$api_message,
					$api_status
				)
			);
			return false;
		}

		return $json_response->entries ?? null;
	}

	/**
	 * Sends a single or multiple SMS messages.
	 *
	 * This method handles the basic SMS sending functionality, converting array receptors
	 * or local IDs into comma-separated strings as required by the Kavenegar API.
	 * It then prepares the parameters and delegates the request execution to `execute()`.
	 *
	 * @param string       $sender   The sender number (e.g., your dedicated SMS line, short code).
	 * @param string|array $receptor The recipient's phone number(s). Can be a single string or an array of numbers.
	 * @param string       $message  The content of the SMS message to be sent.
	 * @param int|null     $date     Optional. A Unix timestamp indicating a future time to send the SMS (for delayed sending).
	 * @param int|null     $type     Optional. The type of message (e.g., a constant from Kavenegar\Enums\General).
	 * @param string|array $localid  Optional. Your internal message ID(s) corresponding to the recipients. Can be a single string or an array.
	 * @return object|null|false The 'entries' part of the API response if successful, typically containing message details.
	 * Returns `null` if 'entries' is not set in a successful response, or `false` on failure.
	 * @since 1.0.0
	 */
	public function send_sms( $sender, $receptor, $message, $date = null, $type = null, $localid = null ) {
		if ( is_array( $receptor ) ) {
			$receptor = implode( ',', $receptor );
		}
		if ( is_array( $localid ) ) {
			$localid = implode( ',', $localid );
		}

		$params = array(
			'receptor' => $receptor,
			'sender'   => $sender,
			'message'  => $message,
			'date'     => $date,
			'type'     => $type,
			'localid'  => $localid,
		);

		return $this->execute( $this->get_path( 'send' ), array_filter( $params ) );
	}

	/**
	 * Sends an SMS message using Kavenegar's Lookup (template-based) service.
	 *
	 * This service allows sending pre-defined messages by providing template names
	 * and specific tokens that populate the template.
	 *
	 * @param string $receptor The phone number of the recipient.
	 * @param string $template The name of the pre-registered Kavenegar template to use.
	 * @param string $token    The value for the first placeholder in the template (e.g., `%token%`).
	 * @param string $token2   Optional. The value for the second placeholder in the template (e.g., `%token2%`). Defaults to an empty string.
	 * @param string $token3   Optional. The value for the third placeholder in the template (e.g., `%token3%`). Defaults to an empty string.
	 * @param string $type     Optional. Specifies the type of verification (e.g., 'sms' for SMS, 'call' for voice call). Defaults to 'sms'.
	 * @param string $token10  Optional. An additional token value for some templates (e.g., `%token10%`). Defaults to an empty string.
	 * @param string $token20  Optional. Another additional token value for some templates (e.g., `%token20%`). Defaults to an empty string.
	 * @return object|null|false The 'entries' part of the API response if successful. Returns `null` if 'entries' is not set in a successful response, or `false` on failure.
	 * @since 1.0.0
	 */
	public function verify_lookup( string $receptor, string $template, string $token, string $token2 = '', string $token3 = '', string $type = 'sms', string $token10 = '', string $token20 = '' ) {
		$params = array(
			'receptor' => $receptor,
			'token'    => $token,
			'token2'   => $token2,
			'token3'   => $token3,
			'template' => $template,
			'type'     => $type,
		);

		if ( ! empty( $token10 ) ) {
			$params['token10'] = $token10;
		}
		if ( ! empty( $token20 ) ) {
			$params['token20'] = $token20;
		}

		return $this->execute( $this->get_path( 'lookup', 'verify' ), array_filter( $params ) );
	}
}
