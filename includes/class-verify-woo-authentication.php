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
			wp_send_json_error( __( 'Phone or OTP is missing.', 'verify-woo' ) );
		}

		$check_otp = $this->verify_woo_check_otp( $user_phone, $otp );
		if ( ! $check_otp['success'] ) {
			wp_send_json_error( $check_otp['message'] );
		}

		$login_success = $this->check_user( $user_phone );

		if ( ! $login_success ) {
			wp_send_json_error( __( 'Something went wrong. Please try again later.', 'verify-woo' ) );
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
				'message'  => __( 'OTP verified and user logged in.', 'verify-woo' ),
				'redirect' => $redirect_url,
			)
		);
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

		return $otp_code;
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
				'message' => 'OTP expired. Please request a new one.',
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
				'message' => 'Too many attempts. Try again later.',
			);
		}

		if ( intval( $input_code ) !== intval( $data['otp'] ) ) {
			$data['attempts'] += 1;

			if ( $data['attempts'] >= 3 ) {
				delete_transient( $key );
				return array(
					'success' => false,
					'message' => 'Incorrect OTP. Maximum attempts reached.',
				);
			}

			return array(
				'success' => false,
				'message' => 'Incorrect OTP. Try again.',
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
		 * @param string $role  Default role is 'customer'.
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
			/**
			 * Filter the path to the custom login form template.
			 *
			 * Allows developers to override the path to the login form template
			 * used to replace WooCommerce's default login form.
			 *
			 * @since 1.0.0
			 *
			 * @param string $custom_template_path Full path to the custom login form.
			 */
			$custom_template = apply_filters( 'verify_woo_login_form_template_path', PLUGIN_DIR . '/public/partials/forms/verify-woo-form-1.php' );

			if ( file_exists( $custom_template ) ) {
				return $custom_template;
			}
		}
		return $template;
	}
}
