<?php
/**
 * Interface SmsFactoryInterface
 *
 * This file defines the contract for an SMS factory. An SMS factory is
 * responsible for creating and providing instances of various SMS gateway drivers
 * that adhere to the `Sms_Gateway_Interface`. This pattern allows for
 * centralized management and selection of SMS providers within an application.
 *
 * @link       https://parsamirzaie.com
 * @since      1.0.0
 * @package    Verify_Woo
 * @subpackage Verify_Woo/includes
 */

/**
 * Interface Sms_Factory_Interface
 *
 * This interface defines the contract for an SMS factory.
 *
 * An SMS factory acts as a central access point for obtaining different
 * SMS gateway driver instances. It decouples the client code (which needs to send an SMS)
 * from the concrete implementation of a specific SMS provider. This allows for
 * easy switching between different SMS gateways based on configuration without
 * modifying the core application logic.
 *
 * @since 1.0.0
 */
interface Sms_Factory_Interface {
	/**
	 * Get a driver instance.
	 *
	 * @param string|null $driver The driver name, or null for the default driver.
	 * @return Sms_Gateway_Interface
	 * @throws \InvalidArgumentException
	 */
	public function driver( string $driver = null ): SmsGatewayInterface;
}
