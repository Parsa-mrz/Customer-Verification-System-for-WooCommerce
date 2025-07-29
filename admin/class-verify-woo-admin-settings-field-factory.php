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
	 * Renders a common setting row wrapper for consistent styling.
	 *
	 * This method takes a callable (e.g., an anonymous function) and executes it
	 * within the standard `div.verify-woo-setting-row` container.
	 *
	 * @since 1.0.0
	 * @param callable $content_callback A callable that contains the HTML content for the row.
	 * @return void Outputs the HTML directly.
	 */
	public static function render_setting_row( callable $content_callback ) {
		?>
		<div class="verify-woo-setting-row">
			<?php call_user_func( $content_callback ); ?>
		</div>
		<?php
	}

	/**
	 * Renders a toggle switch field for use in the admin settings UI.
	 *
	 * This method outputs a checkbox styled as a toggle, along with an optional
	 * label and description. It's commonly used for boolean settings like enabling
	 * or disabling features. It uses the `render_setting_row` wrapper internally.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $options      The full array of current options for the settings group.
	 * @param string $option_key   The specific key within the options array to render.
	 * @param string $option_group The name of the settings group (used for input name).
	 * @param string $title        The title/label to display next to the toggle.
	 * @param string $description  Optional description to show below the toggle.
	 *
	 * @return void Outputs the field HTML directly.
	 */
	public static function toggle( $options, $option_key, $option_group, $title, $description ) {
		self::render_setting_row(
			function () use ( $options, $option_key, $option_group, $title, $description ) {
				$value      = $options[ $option_key ] ?? false;
				$input_name = esc_attr( $option_group ) . '[' . esc_attr( $option_key ) . ']';
				$id         = 'verify_woo_toggle_' . esc_attr( sanitize_key( $option_key ) );
				?>
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
					<?php
			}
		);
	}

	/**
	 * Renders a custom dropdown field that mimics the toggle style.
	 * This requires accompanying CSS and JavaScript to function correctly.
	 * It uses the `render_setting_row` wrapper internally.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $options       Array of current options for the given settings group.
	 * @param string $option_key    The key within the options array to be rendered as a dropdown.
	 * @param string $option_group  The name of the settings group (used as the field name's array key).
	 * @param string $title         The display title for the setting.
	 * @param string $description   The description text shown under the dropdown.
	 * @param array  $choices       An associative array of values and their display labels.
	 *
	 * @return void Outputs the HTML directly.
	 */
	public static function drop_down( $options, $option_key, $option_group, $title, $description, $choices ) {
		self::render_setting_row(
			function () use ( $options, $option_key, $option_group, $title, $description, $choices ) {
				$value      = $options[ $option_key ] ?? '';
				$input_name = esc_attr( $option_group ) . '[' . esc_attr( $option_key ) . ']';
				$id         = 'verify_woo_dropdown_' . esc_attr( sanitize_key( $option_key ) );
				?>
			<div class="header">
				<div class="verify-woo-dropdown" data-dropdown-id="<?php echo esc_attr( $id ); ?>">
					<div class="dropdown-toggle">
						<span class="current-value">
							<?php
							echo esc_html( $choices[ $value ] ?? ( ! empty( $choices ) ? reset( $choices ) : '' ) );
							?>
						</span>
						</div>
					<div class="dropdown-options">
							<?php foreach ( $choices as $key => $label ) : ?>
							<div class="dropdown-option" data-value="<?php echo esc_attr( $key ); ?>">
								<?php echo esc_html( $label ); ?>
							</div>
						<?php endforeach; ?>
					</div>
					<select id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $input_name ); ?>" style="display: none;">
							<?php foreach ( $choices as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $value, $key ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<h3><?php echo esc_html( $title ); ?></h3>
			</div>
			<div class="description">
				<p><?php esc_html( $description ); ?></p>
			</div>
					<?php
			}
		);
	}

	/**
	 * Renders a standard input field (text, number, email, etc.).
	 * It uses the `render_setting_row` wrapper internally.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $options       Array of current options for the given settings group.
	 * @param string $option_key    The key within the options array to be rendered as an input.
	 * @param string $option_group  The name of the settings group (used as the field name's array key).
	 * @param string $title         The display title for the setting.
	 * @param string $description   The description text shown under the input.
	 * @param string $type          The HTML type attribute for the input (default: 'text').
	 * @param string $placeholder   Optional placeholder text for the input.
	 * @param string $size          Optional HTML size attribute for the input, hinting at visible width in characters.
	 *
	 * @return void Outputs the HTML directly.
	 */
	public static function input( $options, $option_key, $option_group, $title, $description, $type = 'text', $placeholder = '', $size = '20' ) {
		self::render_setting_row(
			function () use ( $options, $option_key, $option_group, $title, $description, $type, $placeholder, $size ) {
				$value      = $options[ $option_key ] ?? '';
				$input_name = esc_attr( $option_group ) . '[' . esc_attr( $option_key ) . ']';
				$id         = 'verify_woo_input_' . esc_attr( sanitize_key( $option_key ) );
				?>
			<div class="header">
				<input
					type="<?php echo esc_attr( $type ); ?>"
					id="<?php echo esc_attr( $id ); ?>"
					name="<?php echo esc_attr( $input_name ); ?>"
					value="<?php echo esc_attr( $value ); ?>"
					class="verify-woo-text-input"
					size="<?php echo esc_attr( $size ); ?>"
					<?php if ( ! empty( $placeholder ) ) : ?>
						placeholder="<?php echo esc_attr( $placeholder ); ?>"
					<?php endif; ?>
				>
				<h3><?php echo esc_html( $title ); ?></h3>
			</div>
			<div class="description">
				<p><?php echo esc_html( $description ); ?></p>
			</div>
					<?php
			}
		);
	}

	/**
	 * Renders a simple text block with an optional subtitle and description.
	 *
	 * This method is useful for displaying static information, headings, or
	 * descriptive content within the settings page without requiring user input.
	 * It wraps the content in a standard setting row for consistent styling.
	 *
	 * @since 1.0.0
	 *
	 * @param string $title        The main title or heading for the text block.
	 * @param string $subtitle     Optional subtitle displayed above the title. Defaults to an empty string.
	 * @param string $description  The content for the description area. This can contain HTML.
	 * @param array  $custom_class Optional. An associative array of custom CSS classes to apply
	 * to 'subtitle', 'title', and 'description' elements.
	 * Example: `['subtitle' => 'my-subtitle-class', 'title' => 'my-title-class']`.
	 * @return void Outputs the HTML directly.
	 */
	public static function text( $title, $subtitle = '', $description, $custom_class = array() ) {
		self::render_setting_row(
			function () use ( $title, $description, $subtitle, $custom_class ) {
				?>
				<div class="header">
					<span class="<?php echo esc_attr( $custom_class['subtitle'] ); ?>"><?php echo esc_html( $subtitle ); ?></span>
					<h3 class="<?php echo esc_attr( $custom_class['title'] ); ?>"><?php echo esc_html( $title ); ?></h3>
				</div>
				<div class="description <?php echo esc_attr( $custom_class['description'] ); ?>">
					<?php echo $description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<?php
			}
		);
	}

	/**
	 * Returns an HTML string for an unordered or ordered list with dynamic items and optional icons.
	 *
	 * Expected structure for $options array:
	 * [
	 * ['url' => 'https://example.com', 'label' => 'Example Link 1', 'icon' => 'dashicons-admin-links'],
	 * ['url' => 'https://another.com', 'label' => 'Another Link', 'icon' => 'dashicons-star-filled'],
	 * // ... more items
	 * ]
	 * The 'icon' key is optional. If provided, it should be a Dashicon class name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type    The type of list ('ul' or 'ol').
	 * @param array  $options An array of associative arrays, each with 'url', 'label', and optional 'icon' keys for list items.
	 * @return string The HTML string for the list.
	 */
	public static function list( $type = 'ul', $options = array() ) {
		$output = '';
		if ( is_admin() ) {
			wp_enqueue_style( 'dashicons' );
		}

		switch ( $type ) {
			case 'ul':
				$output .= '<ul class="verify-woo-list">';
				break;
			case 'ol':
				$output .= '<ol class="verify-woo-list">';
				break;
		}

		if ( ! empty( $options ) && is_array( $options ) ) {
			foreach ( $options as $item ) {
				if ( isset( $item['url'] ) && isset( $item['label'] ) ) {
					$output .= '<li>';
					if ( isset( $item['icon'] ) && ! empty( $item['icon'] ) ) {
						$output .= '<span class="dashicons ' . esc_attr( $item['icon'] ) . '"></span> ';
					}
					$output .= '<a href="' . esc_url( $item['url'] ) . '" target="_blank">';
					$output .= esc_html( $item['label'] );
					$output .= '</a>';
					$output .= '</li>';
				}
			}
		}

		switch ( $type ) {
			case 'ul':
				$output .= '</ul>';
				break;
			case 'ol':
				$output .= '</ol>';
				break;
		}

		return $output;
	}
}
