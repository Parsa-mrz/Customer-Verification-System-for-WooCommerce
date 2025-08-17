<?php
/**
 * Provide a form-1 view for the plugin
 *
 * This file is used to html the form-1 aspects of the plugin.
 *
 * @link       https://parsamirzaie.com
 * @since      1.0.0
 *
 * @package    Verify_Woo
 * @subpackage Verify_Woo/public/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form>
	<div class="login-wrap">
		<div class="login-html">
			<div class="login-form send-otp-form">
					<div class="group">
						<input id="user_phone" autocomplete="Phone" name="phone" pattern="" placeholder="<?php echo esc_html__( 'Enter Your Phone Number', 'customer-verification-system-for-woocommerce' ); ?>" type="text" class="input">
					</div>
					<div class="group">
						<button type="submit" name="signIn" class="button signIn">
							<span class="button-text"><?php echo esc_html__( 'Sign In / Sign Up', 'customer-verification-system-for-woocommerce' ); ?></span>
							<span class="sign-in-loader" style="display:none;">
								<span>Loading...</span>
								<span class="Loader-root Loader-sm sign-in-loader" role="presentation">
									<svg class="Loader-circle" role="img" aria-labelledby="L2" focusable="false">
									<title id="L2">Loading…</title>
									<g role="presentation">
										<circle class="Loader-track" cx="50%" cy="50%" r="0.5em"></circle>
										<circle class="Loader-spin" cx="50%" cy="50%" r="0.5em"></circle>
									</g>
									</svg>
								</span>
							</span>
						</button>
					</div>
			</div>
			<div class="login-form verify-otp-form">
					<div class="group otp-input-group">
						<label for="otp-input-1" class="screen-reader-text"><?php echo esc_html__( 'Enter OTP', 'customer-verification-system-for-woocommerce' ); ?></label>
						<input id="otp-input-1" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="otp-input" data-index="0">
						<input id="otp-input-2" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="otp-input" data-index="1">
						<input id="otp-input-3" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="otp-input" data-index="2">
						<input id="otp-input-4" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="otp-input" data-index="3">
						<input type="hidden" name="otp" id="combined_otp">
					</div>
					<div class="group">
						<button type="submit" name="verify" class="button verify">
							<span class="button-text"><?php echo esc_html__( 'Verify OTP', 'customer-verification-system-for-woocommerce' ); ?></span>
							<span class="verify-loader" style="display:none;">
								<span>Loading...</span>
								<span class="Loader-root Loader-sm verify-loader" role="presentation">
									<svg class="Loader-circle" role="img" aria-labelledby="L3" focusable="false">
									<title id="L3">Loading…</title>
									<g role="presentation">
										<circle class="Loader-track" cx="50%" cy="50%" r="1em"></circle>
										<circle class="Loader-spin" cx="50%" cy="50%" r="1em"></circle>
									</g>
									</svg>
								</span>
							</span>
						</button>
					</div>
					<div class="group">
						<p class="resend-otp">
							<?php echo esc_html__( 'Didn\'t receive the code?', 'customer-verification-system-for-woocommerce' ); ?>
							<a href="#" class="resend-otp-link">
							</a>
						</p>
					</div>
			</div>
			<div class="alert"></div>
		</div>
	</div>
</form>
