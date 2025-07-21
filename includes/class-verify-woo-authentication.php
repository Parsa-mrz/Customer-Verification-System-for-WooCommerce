<?php
/**
 * Handles custom authentication logic for WooCommerce via OTP.
 *
 * This file defines the core functionality for managing OTP-based login,
 * including rate limiting, verification, and WooCommerce form overrides.
 *
 * @link       https://parsamirzaie.com
 * @since      1.0.0
 * @package    Verify_Woo
 * @subpackage Verify_Woo/includes
 */

/**
 * Authentication handler class for Verify Woo.
 *
 * This class handles OTP generation and validation via AJAX for WooCommerce login flow.
 * It also replaces the default WooCommerce login template with a custom one.
 *
 * @since      1.0.0
 * @package    Verify_Woo
 * @subpackage Verify_Woo/includes
 * @author     Parsa Mirzaie <Mirzaie_parsa@protonmail.ch>
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
	 * AJAX handler to send OTP to the user phone number.
	 *
	 * Validates nonce and phone input, enforces rate limit (2 minutes),
	 * generates OTP, stores it with attempt counter and timestamp.
	 *
	 * @since 1.0.0
	 * @return void Sends JSON response success or error.
	 */
	public function wp_ajax_send_otp() {
		if ( ! isset( $_POST['_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_nonce'] ) ), 'verify_woo_otp_nonce' ) ) {
			wp_send_json_error( __( 'Invalid request (nonce failed).', 'verify-woo' ) );
		}

		if ( ! isset( $_POST['user_phone'] ) ) {
			wp_send_json_error( __( 'Phone number is required.', 'verify-woo' ) );
		}

		$user_phone = sanitize_text_field( wp_unslash( $_POST['user_phone'] ) );

		if ( empty( $user_phone ) ) {
			wp_send_json_error( __( 'Phone number is empty.', 'verify-woo' ) );
		}

		$rate = $this->verify_woo_can_request_otp( $user_phone );
		if ( ! $rate['allowed'] ) {
			// Translators: %d is the number of seconds the user must wait before retrying.
			wp_send_json_error( sprintf( __( 'Please wait %d seconds before trying again.', 'verify-woo' ), $rate['wait'] ) );
		}

		$this->verify_woo_generate_otp( $user_phone );

		wp_send_json_success( __( 'OTP Sent Successfully!', 'verify-woo' ) );
	}

	/**
	 * AJAX handler to verify submitted OTP against stored one.
	 *
	 * Validates nonce, inputs, checks OTP correctness, attempt count (max 3),
	 * expires OTP after max attempts or on success.
	 *
	 * @since 1.0.0
	 * @return void Sends JSON response success or error.
	 */
	public function wp_ajax_check_otp() {
		if ( ! isset( $_POST['_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_nonce'] ) ), 'verify_woo_otp_nonce' ) ) {
			wp_send_json_error( __( 'Invalid request (nonce failed).', 'verify-woo' ) );
		}

		$otp        = isset( $_POST['otp'] ) ? sanitize_text_field( wp_unslash( $_POST['otp'] ) ?? '' ) : '';
		$user_phone = isset( $_POST['user_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['user_phone'] ) ?? '' ) : '';

		if ( empty( $otp ) || empty( $user_phone ) ) {
			wp_send_json_error( __( 'Phone or OTP is missing.', 'verify-woo' ) );
		}

		$check_otp = $this->verify_woo_check_otp( $user_phone, $otp );
		if ( ! $check_otp['success'] ) {
			wp_send_json_error( $check_otp['message'] );
		}

		wp_send_json_success( __( 'OTP verified successfully.', 'verify-woo' ) );
	}

	/**
	 * Checks if user can request a new OTP (rate limiting).
	 *
	 * Allows new OTP request only if 2 minutes have passed since last OTP.
	 *
	 * @since 1.0.0
	 *
	 * @param string $phone User phone number.
	 * @return array{
	 *     allowed: bool,   Whether new OTP request is allowed.
	 *     wait?:  int      Seconds remaining before next allowed request.
	 * }
	 */
	private function verify_woo_can_request_otp( $phone ) {
		$key  = 'verify_woo_otp_' . md5( $phone );
		$data = get_transient( $key );

		if ( $data && isset( $data['time'] ) ) {
			$elapsed = time() - $data['time'];

			if ( $elapsed < 120 ) {
				return array(
					'allowed' => false,
					'wait'    => 120 - $elapsed,
				);
			}
		}

		return array( 'allowed' => true );
	}

	/**
	 * Generates a new OTP, stores it in a transient with attempt counter and timestamp.
	 *
	 * @since 1.0.0
	 *
	 * @param string $phone User phone number.
	 * @return int Generated OTP code.
	 */
	private function verify_woo_generate_otp( $phone ) {
		$otp_code = wp_rand( 1000, 9999 );
		$key      = 'verify_woo_otp_' . md5( $phone );

		$data = array(
			'otp'      => $otp_code,
			'attempts' => 0,
			'time'     => time(),
		);

		set_transient( $key, $data, 5 * MINUTE_IN_SECONDS );

		// todo: send by sms.
		error_log( 'OTP for ' . $phone . ': ' . $otp_code );

		return $otp_code;
	}

	/**
	 * Checks submitted OTP against stored OTP.
	 *
	 * Increments attempts on failure and expires OTP after 3 failed attempts.
	 *
	 * @since 1.0.0
	 *
	 * @param string $phone      User phone number.
	 * @param string $input_code OTP entered by the user.
	 * @return array{
	 *     success: bool,
	 *     message?: string
	 * }
	 */
	private function verify_woo_check_otp( $phone, $input_code ) {
		$key  = 'verify_woo_otp_' . md5( $phone );
		$data = get_transient( $key );

		if ( ! $data ) {
			return array(
				'success' => false,
				'message' => 'OTP expired. Please request a new one.',
			);
		}

		if ( $data['attempts'] >= 3 ) {
			delete_transient( $key );
			return array(
				'success' => false,
				'message' => 'Too many attempts. Try again later.',
			);
		}

		if ( $input_code != $data['otp'] ) {
			$data['attempts'] += 1;

			if ( $data['attempts'] >= 3 ) {
				delete_transient( $key );
				return array(
					'success' => false,
					'message' => 'Incorrect OTP. Maximum attempts reached.',
				);
			}

			set_transient( $key, $data, 5 * MINUTE_IN_SECONDS );
			return array(
				'success' => false,
				'message' => 'Incorrect OTP. Try again.',
			);
		}

		delete_transient( $key );
		return array( 'success' => true );
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
