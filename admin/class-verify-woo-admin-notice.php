<?php
/**
 * Admin Notice Handler for Verify-Woo Plugin
 *
 * This file contains the `Verify_Woo_Admin_Notice` class responsible for managing
 * admin notices within the Verify-Woo plugin. It handles success and error messages
 * using WordPress transients, with optional auto-hide behavior.
 *
 * The notices are rendered within the plugin's custom admin interface and automatically
 * expire both in storage and visually (with JavaScript).
 *
 * @package    Verify_Woo
 * @subpackage Verify_Woo/admin
 * @author      Parsamirzaie
 * @link        https://parsamirzaie.com
 * @since       1.0.0
 */

/**
 * Class Verify_Woo_Admin_Notice
 *
 * Manages success and error notices within the Verify-Woo plugin's admin interface.
 * Notices are stored using WordPress transients and rendered in the plugin's settings tabs.
 *
 * Responsibilities:
 * - Add success or error notices from any plugin context.
 * - Store notices temporarily using a transient (`verify_woo_admin_notices`).
 * - Auto-expire notices after a defined time (`EXPIRE_TIME`).
 * - Output notices into the DOM with a `data-timeout` attribute for JavaScript auto-hide.
 *
 * @since 1.0.0
 */
class Verify_Woo_Admin_Notice {

	/**
	 * Transient key used to store admin notices.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public const TRANSIENT_KEY = 'verify_woo_admin_notices';

	/**
	 * Expiry time in seconds for stored notices.
	 * Also used in frontend to auto-hide notices.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public const EXPIRE_TIME = 3;

	/**
	 * Add a success notice message to be displayed.
	 *
	 * @since 1.0.0
	 *
	 * @param string $message The message to display.
	 * @return void
	 */
	public static function add_success( $message ) {
		self::add_notice( $message, 'success' );
	}

	/**
	 * Add an error notice message to be displayed.
	 *
	 * @since 1.0.0
	 *
	 * @param string $message The message to display.
	 * @return void
	 */
	public static function add_error( $message ) {
		self::add_notice( $message, 'error' );
	}

	/**
	 * Store the given message as a notice in the transient.
	 *
	 * Notices are saved as an array of associative arrays with `type` and `message`.
	 * The transient automatically expires after EXPIRE_TIME seconds.
	 *
	 * @since 1.0.0
	 *
	 * @param string $message The notice message.
	 * @param string $type    The type of notice. Either 'success' or 'error'.
	 * @return void
	 */
	protected static function add_notice( $message, $type = 'success' ) {
		$notices = get_transient( self::TRANSIENT_KEY );
		if ( ! is_array( $notices ) ) {
			$notices = array();
		}

		$notices[] = array(
			'type'    => $type,
			'message' => $message,
		);

		set_transient( self::TRANSIENT_KEY, $notices, self::EXPIRE_TIME );
	}

	/**
	 * Render all current admin notices and clear the transient.
	 *
	 * Each notice is output as a <span> element with classes and a `data-timeout`
	 * attribute (used by JavaScript to auto-hide).
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function render_notices() {
		$notices = get_transient( self::TRANSIENT_KEY );
		if ( ! empty( $notices ) ) {
			foreach ( $notices as $notice ) {
				$type_class = 'error' === $notice['type'] ? 'verify-woo-notice-error' : 'verify-woo-notice-success';
				echo '<span class="message ' . esc_attr( $type_class ) . '" data-timeout="' . esc_attr( self::EXPIRE_TIME ) . '">' . esc_html( $notice['message'] ) . '</span>';
			}
			delete_transient( self::TRANSIENT_KEY );
		}
	}
}
