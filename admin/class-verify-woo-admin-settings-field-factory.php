<?php
/**
 * Admin Settings Field Factory
 *
 * This file defines a reusable factory class responsible for rendering
 * common form field types within the Verify-Woo admin settings interface.
 *
 * @package    Verify_Woo
 * @subpackage Verify_Woo/admin
 * @author     Parsamirzaie
 * @link       https://parsamirzaie.com
 * @since      1.0.0
 */

/**
 * Class Verify_Woo_Admin_Settings_Field_Factory
 *
 * A utility class that provides reusable methods for rendering
 * admin setting fields in a consistent and maintainable way.
 */
class Verify_Woo_Admin_Settings_Field_Factory {

	/**
	 * Renders a toggle switch field for use in the admin settings UI.
	 *
	 * This method outputs a checkbox styled as a toggle, along with an optional
	 * label and description. It's commonly used for boolean settings like enabling
	 * or disabling features.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $options       Array of current options for the given settings group.
	 * @param string $option_key    The key within the options array to be rendered as a toggle.
	 * @param string $option_group  The name of the settings group (used as the field name's array key).
	 * @param string $title         The display title for the setting (shown as the label).
	 * @param string $description   The description text shown under the toggle.
	 *
	 * @return void Outputs the HTML directly.
	 */
	public static function toggle( $options, $option_key, $option_group, $title, $description ) {
		$value      = $options[ $option_key ] ?? false;
		$input_name = esc_attr( $option_group ) . '[' . esc_attr( $option_key ) . ']';
		$id         = 'verify_woo_toggle_' . esc_attr( sanitize_key( $option_key ) );
		?>
		<div class="verify-woo-setting-row">
			<div class="header">
				<label class="toggle-switch" for="<?php echo esc_attr( $id ); ?>">
					<input
						type="checkbox"
						id="<?php echo esc_attr( $id ); ?>"
						name="<?php echo esc_attr( $input_name ); ?>"
						value="1"
						<?php checked( $value, true ); ?>
					>
					<span class="slider"></span>
				</label>
				<h3><?php echo esc_html( $title ); ?></h3>
			</div>

			<div class="description">
				<p><?php echo esc_html( $description ); ?></p>
			</div>
		</div>
		<?php
	}
}
