<?php
/**
 * Class for validating and handling OTP-based login via AJAX in WooCommerce.
 *
 * This file defines the core functionality of the Verify Woo plugin that handles:
 * - OTP validation and verification
 * - Automatic user login or registration
 * - Security checks using nonces and sanitized input
 * - Integration with WordPress and WooCommerce login system
 * - Hookable filters and actions for full extensibility
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
 * Handles OTP validation and user login/registration via AJAX.
 *
 * This class contains methods to:
 * - Verify the OTP using a transient-based system
 * - Automatically log in existing users
 * - Register new users if allowed
 * - Return appropriate JSON responses
 *
 * Provides several hooks to customize behavior such as max OTP attempts,
 * redirect URLs, user role assignment, and more.
 *
 * @since 1.0.0
 * @package Verify_Woo
 */
class Cvs_Woo_Validate_OTP {
	/**
	 * AJAX callback: Validates submitted OTP and logs in the user.
	 *
	 * Checks nonce and input, verifies OTP correctness and attempts,
	 * logs in existing user or auto-registers if allowed,
	 * and returns JSON success with redirect URL or error.
	 *
	 * @since 1.0.0
	 *
	 * @return void Outputs JSON and terminates script execution.
	 */
	public function wp_ajax_check_otp() {
		check_ajax_referer( 'verify_woo_otp_nonce', '_nonce', true );

		$otp        = isset( $_POST['otp'] ) ? sanitize_text_field( wp_unslash( $_POST['otp'] ) ?? '' ) : '';
		$user_phone = isset( $_POST['user_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['user_phone'] ) ?? '' ) : '';

		if ( empty( $otp ) || empty( $user_phone ) ) {
			wp_send_json_error( __( 'Phone or OTP is missing.', 'customer-verification-system-for-woocommerce' ) );
		}

		$check_otp = $this->verify_woo_check_otp( $user_phone, $otp );
		if ( ! $check_otp['success'] ) {
			wp_send_json_error( $check_otp['message'] );
		}

		$login_success = $this->check_user( $user_phone );

		if ( ! $login_success ) {
			wp_send_json_error( __( 'Something went wrong. Please try again later.', 'customer-verification-system-for-woocommerce' ) );
		}

		/**
		 * Filter: 'verify_woo_login_redirect_url'
		 *
		 * Modify the URL where users are redirected after successful OTP login.
		 *
		 * @since 1.0.0
		 *
		 * @param string $redirect_url Default WooCommerce My Account page URL.
		 *
		 * @return string New redirect URL.
		 */
		$redirect_url = apply_filters( 'verify_woo_login_redirect_url', wc_get_page_permalink( 'myaccount' ) );

		wp_send_json_success(
			array(
				'message'  => __( 'OTP verified and user logged in.', 'customer-verification-system-for-woocommerce' ),
				'redirect' => $redirect_url,
			)
		);
	}
	/**
	 * Validates the user-submitted OTP against the stored OTP.
	 *
	 * Increments attempt count on failure, expires OTP after max attempts (default 3),
	 * returns success or error messages accordingly.
	 *
	 * @since 1.0.0
	 *
	 * @param string $phone      Phone number associated with OTP.
	 * @param string $input_code OTP code submitted by user.
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
				'message' => __( 'OTP expired. Please request a new one.', 'customer-verification-system-for-woocommerce' ),
			);
		}

		/**
		 * Filter: 'verify_woo_max_otp_attempts'
		 *
		 * Set the maximum number of attempts allowed for OTP verification.
		 *
		 * @since 1.0.0
		 *
		 * @param int $max_attempts Default is 3.
		 *
		 * @return int The new max attempt limit.
		 */
		$max_attempts = apply_filters( 'verify_woo_max_otp_attempts', OTP::MAX_ATTEMPTS->value );

		if ( $data['attempts'] >= $max_attempts ) {
			delete_transient( $key );
			return array(
				'success' => false,
				'message' => __( 'Too many attempts. Try again later.', 'customer-verification-system-for-woocommerce' ),
			);
		}

		if ( intval( $input_code ) !== intval( $data['otp'] ) ) {
			$data['attempts'] += 1;

			if ( $data['attempts'] >= 3 ) {
				delete_transient( $key );
				return array(
					'success' => false,
					'message' => __( 'Incorrect OTP. Maximum attempts reached.', 'customer-verification-system-for-woocommerce' ),
				);
			}

			return array(
				'success' => false,
				'message' => __( 'Incorrect OTP. Try again.', 'customer-verification-system-for-woocommerce' ),
			);
		}

		delete_transient( $key );
		return array( 'success' => true );
	}
	/**
	 * Checks if a user exists by phone number, logs them in,
	 * or auto-registers a new user if enabled.
	 *
	 * @since 1.0.0
	 *
	 * @param string $phone Phone number to check or register.
	 * @return bool True on successful login or registration, false otherwise.
	 */
	private function check_user( $phone ) {
		$clean_phone = preg_replace( '/[^0-9]/', '', $phone );

		/**
		 * Filter: 'verify_woo_username_prefix'
		 *
		 * Allows modifying the prefix used when creating a new username from a phone number.
		 *
		 * @since 1.0.0
		 *
		 * @param string $prefix      The default prefix, e.g., 'customer_'.
		 * @param string $clean_phone The sanitized phone number.
		 *
		 * @return string Modified prefix.
		 */
		$prefix = apply_filters( 'verify_woo_username_prefix', 'customer_', $clean_phone );

		$username = sanitize_user( "$prefix$clean_phone", true );

		$user = get_user_by( 'login', $username );

		if ( $user ) {
			/**
			 * Action Hook: 'verify_woo_before_login_existing_user'
			 *
			 * Fires right before an existing user is logged in via OTP.
			 *
			 * Allows adding custom logic before login (e.g., logging, extra validation).
			 *
			 * Parameters passed to callback:
						 *
			 * @param WP_User $user The user object being logged in.
			 *
			 * Usage example:
			 * ```php
			 * add_action( 'verify_woo_before_login_existing_user', 'custom_before_login', 10, 1 );
			 * function custom_before_login( $user ) {
			 *     // Custom pre-login actions
			 * }
			 * ```
			 *
			 * @since 1.0.0
			 */
			do_action( 'verify_woo_before_login_existing_user', $user );

			wp_set_current_user( $user->ID );
			wp_set_auth_cookie( $user->ID );
			return true;
		}

		/**
		 * Filter: 'verify_woo_auto_register_enabled'
		 *
		 * Control whether new users can be auto-registered via OTP.
		 *
		 * @since 1.0.0
		 *
		 * @param bool   $enabled True to allow auto-registration.
		 * @param string $phone   Phone number attempting login.
		 *
		 * @return bool Modified flag to allow or deny auto-registration.
		 */
		if ( ! apply_filters( 'verify_woo_auto_register_enabled', true, $phone ) ) {
			return false;
		}

		/**
		 * Filter: 'verify_woo_new_user_role'
		 *
		 * Modify the role assigned to newly registered users.
		 *
		 * @since 1.0.0
		 *
		 * @param string|array $roles  Default role is 'customer'.
		 * @param string $phone The phone number used for registration.
		 *
		 * @return string The user role.
		 */
		$roles = apply_filters( 'verify_woo_new_user_role', 'customer', $phone );

		$user_data = array(
			'user_login' => $username,
			'user_pass'  => wp_generate_password(),
			'role'       => $roles,
		);

		/**
		 * Filter: 'verify_woo_new_user_data'
		 *
		 * Change the data array used to register new users.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $user_data {
		 *     @type string $user_login The generated username.
		 *     @type string $user_pass  Random password.
		 *     @type string $role       Role of the new user.
		 * }
		 * @param string $phone The phone number used to register.
		 *
		 * @return array Modified user data array.
		 */
		$user_data = apply_filters( 'verify_woo_new_user_data', $user_data, $phone );

		$user_id = wp_insert_user( $user_data );

		if ( is_wp_error( $user_id ) ) {
			return false;
		}

		update_user_meta( $user_id, 'verify_woo_phone_number', $phone );

		/**
		 * Action Hook: 'verify_woo_after_register_user'
		 *
		 * Fires immediately after a new user is registered via OTP auto-registration.
		 *
		 * Useful for adding additional user meta or triggering welcome emails.
		 *
		 * Parameters passed to callback:
		 *
		 * @param int    $user_id The ID of the newly registered user.
		 * @param string $phone   The phone number used for registration.
		 *
		 * Usage example:
		 * ```php
		 * add_action( 'verify_woo_after_register_user', 'send_welcome_email', 10, 2 );
		 * function send_welcome_email( $user_id, $phone ) {
		 *     // Send welcome email or other post-registration logic
		 * }
		 * ```
		 *
		 * @since 1.0.0
		 */
		do_action( 'verify_woo_after_register_user', $user_id, $phone );

		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id );

		return true;
	}
}
