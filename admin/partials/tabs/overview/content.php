<?php
/**
 * Provide a admin area view for overview tab
 *
 * This file is used to markup the admin-facing aspects of overview tab.
 *
 * @link       https://parsamirzaie.com
 * @since      1.0.0
 *
 * @package    Verify_Woo
 * @subpackage Verify_Woo/admin/partials/tabs/overview
 */

?>
<form class="modern-toggle-form" action="options.php" method="POST">
	<?php
		settings_fields( 'verify_woo_settings_group' );
		do_settings_sections( 'verify_woo_settings_page' );
		submit_button(
			__( 'Save Settings', 'verify-woo' ),
			'primary cta-btn',
			'submit',
		);
		?>
</form>
