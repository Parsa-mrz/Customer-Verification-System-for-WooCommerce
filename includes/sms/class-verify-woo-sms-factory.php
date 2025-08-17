<?php
/**
 * SMS Gateway Factory for Verify Woo.
 *
 * This file contains the implementation of the `Sms_Factory_Interface`.
 * It serves as a central point for creating and managing instances of
 * different SMS gateway drivers, such as Kavenegar. It handles the logic
 * for selecting the default driver and instantiating the correct class
 * based on plugin settings.
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
 * Verify_Woo_Sms_Factory Class.
 *
 * Implements `Sms_Factory_Interface` to provide a centralized way of
 * creating and retrieving SMS gateway driver instances within the plugin.
 * This factory is responsible for instantiating the correct driver based on
 * configuration and ensuring that drivers are properly initialized with settings.
 *
 * @since 1.0.0
 */
class Verify_Woo_Sms_Factory implements Sms_Factory_Interface {


	/**
	 * Array of supported SMS gateway drivers.
	 *
	 * This array maps driver names (keys) to their corresponding class names (values).
	 * Extend this array to add support for more SMS providers.
	 *
	 * @var array<string, class-string<Sms_Gateway_Interface>>
	 */
	protected array $supported_drivers = array(
		'kavenegar' => Verify_Woo_Kavenegar_Driver::class,
	);

	/**
	 * Get a driver instance.
	 *
	 * This method serves as the primary entry point for obtaining an `Sms_Gateway_Interface`
	 * instance. If a specific driver name is provided, it attempts to create that driver.
	 *
	 * @param string|null $driver_name Optional. The name of the SMS driver to retrieve. If `null`,
	 * the default driver will be used.
	 * @return Sms_Gateway_Interface An instance of the requested SMS gateway driver.
	 * @throws \InvalidArgumentException If the specified driver is not supported or not configured.
	 * @throws \RuntimeException If the driver class cannot be found or instantiated, or does not implement the required interface.
	 * @since 1.0.0
	 */
	public function driver( string $driver_name ): Sms_Gateway_Interface {

		if ( ! array_key_exists( $driver_name, $this->supported_drivers ) ) {
			error_log( sprintf( 'Verify_Woo_Sms_Factory: SMS driver "%s" is not supported.', $driver_name ) );
			throw new \InvalidArgumentException(
				sprintf(
					// Translators: %s is driver name.
					esc_html__( 'SMS driver [%s] is not supported.', 'customer-verification-system-for-woocommerce' ),
					esc_html( $driver_name )
				)
			);
		}

		return $this->createDriver( $driver_name );
	}

	/**
	 * Create a new driver instance.
	 *
	 * This protected method is responsible for instantiating the concrete SMS gateway
	 * driver class based on the provided driver name. It maps driver names to their
	 * respective class instances and ensures the driver is initialized with
	 * the necessary settings fetched from WordPress options.
	 *
	 * @param string $driver_name The name of the driver to create (e.g., 'kavenegar').
	 * @return Sms_Gateway_Interface An instance of the SMS gateway driver.
	 * @throws \RuntimeException If the driver class cannot be found or instantiated,
	 * or if the instantiated object does not implement the `Sms_Gateway_Interface`.
	 * @since 1.0.0
	 */
	protected function createDriver( string $driver_name ): Sms_Gateway_Interface {
		$driver_class    = $this->supported_drivers[ $driver_name ];
		$driver_instance = new $driver_class();

		if ( ! $driver_instance instanceof Sms_Gateway_Interface ) {
			error_log( sprintf( 'Verify_Woo_Sms_Factory: Created driver "%s" does not implement Sms_Gateway_Interface.', $driver_class ) );
			throw new \RuntimeException(
				sprintf(
					// Translators: %s is driver name.
					esc_html__( 'Driver [%s] does not implement required interface.', 'customer-verification-system-for-woocommerce' ),
					esc_html( $driver_class )
				)
			);
		}

		return $driver_instance;
	}
}
