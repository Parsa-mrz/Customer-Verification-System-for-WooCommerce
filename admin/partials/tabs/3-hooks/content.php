<?php
/**
 * Provide a admin area view for hooks tab
 *
 * This file is used to markup the admin-facing aspects hooks tab.
 *
 * @link       https://parsamirzaie.com
 * @since      1.0.0
 *
 * @package    Verify_Woo
 * @subpackage Verify_Woo/admin/partials/tabs/hooks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form class="modern-toggle-form" action="options.php" method="POST">
	<?php
		settings_fields( 'verify_woo_settings_hooks_group' );
		do_settings_sections( 'verify_woo_settings_page_hooks' );
	?>
</form>
