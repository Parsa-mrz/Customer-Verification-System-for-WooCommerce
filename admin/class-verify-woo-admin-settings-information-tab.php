<?php


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


	public function sanitize_settings( $input ) {
		$sanitized               = array();
		$sanitized['activation'] = ! empty( $input['activation'] ) ? true : false;

		Verify_Woo_Admin_Notice::add_success( __( 'Settings Saved', 'verify-woo' ) );

		return $sanitized;
	}


	public function render_field() {
		$options = get_option( self::OPTION_GROUP );

		Verify_Woo_Admin_Settings_Field_Factory::version(
			sprintf(
			/* translators: %s: plugin version */
				esc_html__( 'Version %s', 'verify-woo' ),
				esc_html( $this->version )
			),
			__( 'VerifyWoo', 'verify-woo' ),
			__( 'This is the current installed version of the plugin.', 'verify-woo' )
		);
	}
}
