<?php
/**
 * Admin Settings Information Tab Class.
 *
 * This file defines the `Verify_Woo_Admin_Settings_Information_Tab` class, which is responsible
 * for handling the "Information" tab on the Verify Woo plugin's administration settings page.
 * It manages the display of plugin version and other relevant details.
 *
 * @package    Verify_Woo
 * @subpackage Verify_Woo/admin
 * @author     Parsamirzaie
 * @link       https://parsamirzaie.com
 * @since      1.0.0
 */

/**
 * The Verify_Woo_Admin_Settings_Information_Tab class.
 *
 * This class is responsible for managing the information tab within the Verify Woo plugin's
 * admin settings page. It handles the registration of settings, sanitization of options,
 * and rendering of the information fields, primarily displaying the plugin's version.
 *
 * @since 1.0.0
 */
class Verify_Woo_Admin_Settings_Information_Tab {
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
	 * Initialize the class and set its properties.∂
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * The name of the option group for the information settings.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const OPTION_GROUP = 'verify_woo_information_settings';

	/**
	 * Registers the settings for the information tab.
	 *
	 * This method hooks into WordPress's settings API to define the settings group,
	 * section, and field for the plugin's information display.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			'verify_woo_settings_information_group',
			self::OPTION_GROUP,
			array( $this, 'sanitize_settings' )
		);

		add_settings_section(
			'verify_woo_information_section',
			__( 'Plugin Information', 'verify-woo' ),
			'',
			'verify_woo_settings_page_information'
		);

		add_settings_field(
			'information',
			'',
			array( $this, 'render_field' ),
			'verify_woo_settings_page_information',
			'verify_woo_information_section'
		);
	}

	/**
	 * Sanitizes the input for the information settings.
	 *
	 * This method is a callback for `register_setting` and is responsible for
	 * cleaning and validating the settings data before it is saved to the database.
	 * Currently, it handles a hypothetical 'activation' setting and adds a success notice.
	 *
	 * @since 1.0.0
	 * @param  array $input The unsanitized array of settings from the form.
	 * @return array        The sanitized array of settings.
	 */
	public function sanitize_settings( $input ) {
		$sanitized               = array();
		$sanitized['activation'] = ! empty( $input['activation'] ) ? true : false;

		Verify_Woo_Admin_Notice::add_success( __( 'Settings Saved', 'verify-woo' ) );

		return $sanitized;
	}

	/**
	 * Renders the information field on the settings page.
	 *
	 * This method retrieves the saved options and uses a factory class
	 * (Verify_Woo_Admin_Settings_Field_Factory) to display the plugin's version.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_field() {
		$options = get_option( self::OPTION_GROUP );

		Verify_Woo_Admin_Settings_Field_Factory::text(
			__( 'VerifyWoo', 'verify-woo' ),
			sprintf(
			/* translators: %s: plugin version */
				esc_html__( 'Version %s', 'verify-woo' ),
				esc_html( $this->version )
			),
			__( 'This is the current installed version of the plugin.', 'verify-woo' ),
			array(
				'subtitle' => 'verify-woo-notice-success verify-woo-admin-version',
			)
		);

		Verify_Woo_Admin_Settings_Field_Factory::text(
			__( 'Developer Information', 'verify-woo' ),
			'',
			Verify_Woo_Admin_Settings_Field_Factory::list(
				'ul',
				array(
					array(
						'url'   => 'https://parsamirzaie.com',
						'label' => __( 'Check My Website', 'verify-woo' ),
						'icon'  => 'dashicons-admin-site',
					),
					array(
						'url'   => 'https://github.com/Parsa-mrz',
						'label' => __( 'GitHub Profile', 'verify_woo' ),
						'icon'  => 'dashicons-admin-links',
					),
					array(
						'url'   => 'www.linkedin.com/in/parsa-mirzaie-85249a221',
						'label' => __( 'LinkedIn Profile', 'verify-woo' ),
						'icon'  => 'dashicons-format-aside',
					),
				)
			),
		);
	}
}
