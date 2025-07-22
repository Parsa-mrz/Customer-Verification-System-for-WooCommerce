<?php
/**
 * Enum for defining OTP-related constants used in the Verify Woo plugin.
 *
 * This enum encapsulates OTP configuration such as expiration time
 * and the maximum number of allowed verification attempts.
 *
 * Enums improve type safety and centralize config for easier maintenance.
 *
 * @package    Verify_Woo
 * @subpackage Verify_Woo/Enums
 * @author     Parsa Mirzaie <Mirzaie_parsa@protonmail.ch>
 * @since      1.0.0
 */

/**
 * Enum OTP
 *
 * Represents configuration values related to OTP verification.
 *
 * @since 1.0.0
 */
enum OTP: int {

	/**
	 * Expiration time in seconds for OTP validity.
	 *
	 * Default is 60 seconds (1 minute).
	 *
	 * @since 1.0.0
	 */
	case EXPIRE_TIME = 60;

	/**
	 * Maximum number of allowed OTP verification attempts.
	 *
	 * Default is 3 attempts.
	 *
	 * @since 1.0.0
	 */
	case MAX_ATTEMPTS = 3;
}
