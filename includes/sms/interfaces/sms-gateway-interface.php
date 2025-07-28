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
}
