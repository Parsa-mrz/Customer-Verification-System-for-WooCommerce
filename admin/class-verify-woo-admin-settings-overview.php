<?php
/**
 * Admin Settings Class: Overview Tab
 *
 * This file contains the class responsible for rendering and handling
 * the "Overview" tab settings in the Verify-Woo plugin admin page.
 *
 * @package    Verify_Woo
 * @subpackage Verify_Woo/admin
 * @author      Parsamirzaie
 * @link        https://parsamirzaie.com
 * @since       1.0.0
 */

/**
 * Class verify_Woo_Admin_Settings_overview
 *
 * Handles registration, sanitization, and rendering of the "Overview" tab settings
 * for the Verify-Woo plugin.
 *
 * Responsibilities:
 * - Register plugin settings using the Settings API.
 * - Provide sanitization logic for form inputs.
 * - Render toggle fields for enabling/disabling features.
 *
 * @since 1.0.0
 */
class Verify_Woo_Admin_Settings_Overview {

	/**
	 * Register settings, sections, and fields using WordPress Settings API.
	 *
	 * Hooks into WordPress admin to:
	 * - Register the setting group `verify_woo_settings_group`.
	 * - Create a settings section called `verify_woo_main_section`.
	 * - Add a checkbox field for "Activate Login Page" under the section.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			'verify_woo_settings_group',
			'verify_woo_settings',
			array( $this, 'sanitize_settings' )
		);

		add_settings_section(
			'verify_woo_main_section',
			__( 'Main Settings', 'verify-woo' ),
			function () {
				echo '<p>' . esc_html__( 'Configure the login settings below.', 'verify-woo' ) . '</p>';
			},
			'verify_woo_settings_page'
		);

		add_settings_field(
			'activation',
			'',
			array( $this, 'render_overview_field' ),
			'verify_woo_settings_page',
			'verify_woo_main_section'
		);
	}

	/**
	 * Sanitize the submitted plugin settings before saving to the database.
	 *
	 * Currently only sanitizes the "activation" toggle.
	 * Converts it to an integer (1 or 0).
	 *
	 * @since 1.0.0
	 *
	 * @param array $input The raw submitted input from the settings form.
	 *
	 * @return array $sanitized The cleaned version of the input to store.
	 */
	public function sanitize_settings( $input ) {
		$sanitized               = array();
		$sanitized['activation'] = ! empty( $input['activation'] ) ? 1 : 0;
		return $sanitized;
	}

	/**
	 * Render the "Activate Login Page" field in the admin UI.
	 *
	 * Outputs a modern toggle switch checkbox. Uses saved value from
	 * the `verify_woo_settings` option.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_overview_field() {
		$options = get_option( 'verify_woo_settings' );
		?>
		<div class="verify-woo-setting-row">
			<div class="header">
				<label class="toggle-switch">
				<input type="checkbox" name="verify_woo_settings[activation]" value="1" <?php checked( $options['activation'] ?? 0, 1 ); ?>>
				<span class="slider"></span>
				</label>
				<h3><?php esc_html_e( 'Activate Login Page', 'verify-woo' ); ?></h3>
			</div>

			<div class="description">
				<p><?php esc_html_e( 'This setting allows you to enable or disable the custom login page for WooCommerce. When activated, users will be redirected to the custom login page instead of the default WooCommerce login.', 'verify-woo' ); ?></p>
			</div>
		<?php
	}
}
