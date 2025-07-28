<?php
/**
 * Handles sending OTP codes for user authentication via WooCommerce.
 *
 * This class processes AJAX requests to send OTP codes to users' phone numbers,
 * handles rate limiting, OTP generation, and hooks into a custom SMS gateway via action.
 *
 * Used as part of the Verify Woo plugin's OTP login system.
 *
 * @link       https://parsamirzaie.com
 * @since      1.0.0
 * @package    Verify_Woo
 * @subpackage Verify_Woo/includes
 */

/**
 * Class Verify_Woo_Send_OTP
 *
 * Responsible for:
 * - Receiving AJAX requests to send OTP
 * - Validating request nonce and input
 * - Enforcing rate-limiting (to prevent abuse)
 * - Generating a 4-digit OTP
 * - Storing OTP data (code, timestamp, attempt count)
 * - Triggering hook to integrate with external SMS APIs
 *
 * Hooks:
 * - `verify_woo_otp_rate_limit_seconds` — Cooldown between OTPs
 * - `verify_woo_otp_expiration` — OTP lifespan
 * - `verify_woo_send_otp_sms` — Send OTP externally (via SMS, for example)
 *
 * @since 1.0.0
 * @package Verify_Woo
 */
class Verify_Woo_Send_OTP {

	/**
	 * AJAX callback: Sends an OTP code to the specified phone number.
	 *
	 * Validates nonce and input, enforces rate limiting,
	 * generates and stores OTP, triggers OTP sending hook.
	 *
	 * Sends JSON success or error response.
	 *
	 * @since 1.0.0
	 *
	 * @return void Outputs JSON and terminates script execution.
	 */
	public function wp_ajax_send_otp() {
		check_ajax_referer( 'verify_woo_otp_nonce', '_nonce', true );

		$admin_sms_gateway_options = get_option( Verify_Woo_Admin_Settings_Sms_Gateway_Tab::OPTION_GROUP );
		if ( ! $admin_sms_gateway_options['sms_activation'] ) {
			wp_send_json_error( __( 'Login to the system is currently unavailable.', 'verify-woo' ) );
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

		// Translators: %d is the user phone number.
		wp_send_json_success( sprintf( __( 'OTP Sent to %d Successfully!', 'verify-woo' ), $user_phone ) );
	}

	/**
	 * Determines if a new OTP can be requested for the given phone number.
	 *
	 * Enforces a cooldown period (default 120 seconds) between OTP requests.
	 *
	 * @since 1.0.0
	 *
	 * @param string $phone Phone number to check.
	 * @return array{
	 *     allowed: bool,  True if allowed to request new OTP.
	 *     wait?: int      Seconds remaining before next allowed request.
	 * }
	 */
	private function verify_woo_can_request_otp( $phone ) {
		$key  = 'verify_woo_otp_' . md5( $phone );
		$data = get_transient( $key );

		if ( $data && isset( $data['time'] ) ) {
			$elapsed = time() - $data['time'];

			/**
			 * Filter Hook: 'verify_woo_otp_rate_limit_seconds'
			 *
			 * Filters the cooldown time in seconds between OTP requests.
			 *
			 * Allows adjusting rate limit duration dynamically.
			 *
			 * Parameters passed to callback:
			 *
			 * @param int $seconds The default cooldown time in seconds (default 120).
			 *
			 * Returns:
			 * Modified cooldown time in seconds.
			 *
			 * Usage example:
			 * ```php
			 * add_filter( 'verify_woo_otp_rate_limit_seconds', function( $seconds ) {
			 *     return 60; // 1 minute instead of 2
			 * });
			 * ```
			 *
			 * @since 1.0.0
			 */
			$rate_limit = apply_filters( 'verify_woo_otp_rate_limit_seconds', OTP::EXPIRE_TIME->value );

			if ( $elapsed < $rate_limit ) {
				return array(
					'allowed' => false,
					'wait'    => $rate_limit - $elapsed,
				);
			}
		}

		return array( 'allowed' => true );
	}

	/**
	 * Generates and stores a new OTP for the given phone number.
	 *
	 * Stores OTP with timestamp and zero attempts, triggers sending via hook,
	 * and logs OTP for debugging (remove in production).
	 *
	 * @since 1.0.0
	 *
	 * @param string $phone Phone number to send OTP to.
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

		/**
		 * Filter: 'verify_woo_otp_expiration'
		 *
		 * Change how long OTP codes are valid (expiration time).
		 *
		 * @since 1.0.0
		 *
		 * @param int $expiration Default is 2 minutes in seconds.
		 *
		 * @return int New OTP expiration time in seconds.
		 */
		$expiration = apply_filters( 'verify_woo_otp_expiration', OTP::EXPIRE_TIME->value );

		set_transient( $key, $data, $expiration );

		/**
		 * Action Hook: 'verify_woo_send_otp_sms'
		 *
		 * Fires when an OTP code has been generated and is ready to be sent via SMS.
		 *
		 * Allows hooking into the process to send the OTP using a custom SMS gateway.
		 *
		 * Parameters passed to callback:
		 *
		 * @param string $phone    The phone number to which OTP should be sent.
		 * @param int    $otp_code The generated OTP code.
		 *
		 * Usage example:
		 * ```php
		 * add_action( 'verify_woo_send_otp_sms', 'send_sms_via_gateway', 10, 2 );
		 * function send_sms_via_gateway( $phone, $otp_code ) {
		 *     // Your SMS sending logic here
		 * }
		 * ```
		 *
		 * @since 1.0.0
		 */
		do_action( 'verify_woo_send_otp_sms', $phone, $otp_code );

		error_log( 'OTP for ' . $phone . ': ' . $otp_code );
		$this->send_otp( $phone, $otp_code );

		return $otp_code;
	}

	/**
	 * Sends the generated OTP code via the configured SMS gateway.
	 *
	 * This method retrieves the active SMS gateway settings and dispatches
	 * the OTP either as a direct message or using a pre-configured pattern (template).
	 *
	 * @since 1.0.0
	 *
	 * @param string $phone    The recipient's phone number.
	 * @param int    $otp_code The OTP code to be sent.
	 * @return bool True if the OTP was successfully dispatched, false otherwise.
	 */
	private function send_otp( $phone, $otp_code ) {
		$admin_sms_gateway_options = get_option( Verify_Woo_Admin_Settings_Sms_Gateway_Tab::OPTION_GROUP );
		$sms_gateway_factory       = new Verify_Woo_Sms_Factory();
		$sms_gateway_instance      = $sms_gateway_factory->driver( $admin_sms_gateway_options['sms_gateway'] );
		if ( $admin_sms_gateway_options['sms_gateway_pattern'] ) {
			$sms_gateway_instance->send_by_pattern(
				$phone,
				$admin_sms_gateway_options['sms_gateway_pattern'],
				array()
			);
			return true;
		} else {
			$sms_gateway_instance->send(
				$phone,
				$otp_code
			);
			return true;
		}
	}
}
