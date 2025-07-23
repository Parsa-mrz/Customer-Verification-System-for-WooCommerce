<form class="modern-toggle-form" action="options.php">
	<?php
		settings_fields( 'verify_woo_settings_group' );
		do_settings_sections( 'verify_woo_settings_page' );
		submit_button();
	?>
</form>
