<?php
/**
 * Interface SmsGatewayInterface
 *
 * This file defines the contract for an SMS gateway driver. Any class implementing
 * this interface must provide the necessary methods for sending SMS messages
 * through a specific provider (e.g., Kavenegar, Twilio, Nexmo). This ensures
 * a consistent API for your application to interact with various SMS services.
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
 * Interface Sms_Gateway_Interface
 *
 * This interface defines the contract for an SMS gateway, which is responsible for
 * the actual sending of SMS messages through a specific provider.
 * Implementations of this interface will encapsulate the provider-specific logic
 * for communicating with SMS APIs.
 *
 * @since 1.0.0
 */
interface Sms_Gateway_Interface {

	/**
	 * Sends an SMS message.
	 *
	 * @param string $receptor The recipient's phone number.
	 * @param string $message The message content.
	 * @param array  $options Provider-specific options (e.g., sender ID, delivery reports).
	 * @return bool
	 */
	public function send( string $receptor, string $message, array $options = array() ): bool;

	/**
	 * Sends an SMS message using a pre-defined pattern or template.
	 *
	 * This method is suitable for transactional messages (e.g., OTP, verification codes)
	 * where the message content is based on a template defined on the SMS provider's platform.
	 *
	 * @param string $receptor The recipient's phone number in a valid format (e.g., E.164).
	 * @param string $pattern  The identifier or name of the pre-registered message pattern/template to use.
	 * @param array  $data     An associative array of data to populate the placeholders within the pattern/template.
	 * Keys should correspond to the pattern's token names (e.g., ['token' => '12345', 'name' => 'John']).
	 * @param array  $options  Optional. An associative array of provider-specific options,
	 * similar to the `send` method's `$options`, but potentially
	 * including template-specific parameters.
	 * @return bool True if the message was successfully queued for sending (or sent), false otherwise.
	 */
	public function send_by_pattern( string $receptor, string $pattern, array $data = array(), array $options = array() ): bool;
}
