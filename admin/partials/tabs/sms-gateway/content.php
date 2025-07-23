<?php
/**
 * Provide a admin area view for sms gateway tab
 *
 * This file is used to markup the admin-facing aspects of sms gateway tab.
 *
 * @link       https://parsamirzaie.com
 * @since      1.0.0
 *
 * @package    Verify_Woo
 * @subpackage Verify_Woo/admin/partials/tabs/sms-gateway
 */

?>
<form class="modern-toggle-form" action="options.php" method="POST">
	<?php
		settings_fields( 'verify_woo_settings_sms_gateway_group' );
		do_settings_sections( 'verify_woo_settings_page_sms_gateway' );
		submit_button(
			__( 'Save Settings', 'verify-woo' ),
			'primary cta-btn',
			'submit',
		);
		?>
</form>
