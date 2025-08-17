<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://parsamirzaie.com
 * @since      1.0.0
 *
 * @package    Verify_Woo
 * @subpackage Verify_Woo/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<div class="tabs-container verify-woo-admin">
		<div class="tabs">
			<div class="tab-links">
				<div class="plugin-info">
						<div class="verify-woo-plugin-icon">
							<?php require_once $plugin_icon; ?>
						</div>
					<div class="plugin-details">
						<h2><?php esc_html_e( 'VerifyWoo', 'customer-verification-system-for-woocommerce' ); ?></h2>
					</div>
					<div class="plugin-version">
						
						<?php
						esc_html(
							printf(
								/* translators: %s: plugin version */
								esc_html__( 'Version %s', 'customer-verification-system-for-woocommerce' ),
								esc_html( $this->version )
							)
						);
						?>
					</div>
				</div>
				<?php $first = true; ?>
				<?php foreach ( $tabs_data as $tab_item ) : ?>
					<?php
						$active = $first ? 'active' : '';
						$slug   = esc_attr( $tab_item['slug'] );
						$name   = esc_html( $tab_item['name'] );
					?>
					<button class="tab-link <?php echo esc_attr( $active ); ?>" data-tab="<?php echo esc_attr( $slug ); ?>">
						<?php
						echo esc_html( $name );
						?>
					</button>
					<?php $first = false; ?>
				<?php endforeach; ?>
			</div>

			<div class="tab-contents">
				<?php $first = true; ?>
				<?php foreach ( $tabs_data as $tab_item ) : ?>
					<?php
						$active = $first ? 'active' : '';
						$slug   = esc_attr( $tab_item['slug'] );
					?>
					<div class="tab-content <?php echo esc_attr( $active ); ?>" id="<?php echo esc_attr( $slug ); ?>">
						<div class="verify-woo-notice">
							<?php Cvs_Woo_Admin_Notice::render_notices(); ?>
						</div>
						<?php
						do_action( "verify_woo_tab_{$slug}_content", $slug );

						if ( ! empty( $tab_item['content_file'] ) ) {
							include $tab_item['content_file'];
						}
						?>
					</div>
					<?php $first = false; ?>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</div>
