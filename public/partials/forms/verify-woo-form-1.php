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

?>
<form>
	<div class="login-wrap">
		<div class="login-html">
			<div class="login-form send-otp-form">
					<div class="group">
						<input id="user_phone" autocomplete="Phone" name="phone" pattern="" placeholder="<?php echo esc_html__( 'Enter Your Phone Number', 'verify-woo' ); ?>" type="text" class="input">
					</div>
					<div class="group">
						<input type="submit" name="signIn" class="button signIn" value="<?php echo esc_html__( 'Sign In / Sign Up', 'verify-woo' ); ?>">
					</div>
			</div>
			<div class="login-form verify-otp-form">
					<div class="group otp-input-group">
						<label for="otp-input-1" class="screen-reader-text"><?php echo esc_html__( 'Enter OTP', 'verify-woo' ); ?></label>
						<input id="otp-input-1" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="otp-input" data-index="0">
						<input id="otp-input-2" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="otp-input" data-index="1">
						<input id="otp-input-3" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="otp-input" data-index="2">
						<input id="otp-input-4" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="otp-input" data-index="3">
						<input type="hidden" name="otp" id="combined_otp">
					</div>
					<div class="group">
						<input type="submit" name="verify" class="button verify" value="<?php echo esc_html__( 'Verify OTP', 'verify-woo' ); ?>">
					</div>
					<div class="group">
						<p class="resend-otp">
							<?php echo esc_html__( 'Didn\'t receive the code?', 'verify-woo' ); ?>
							<a href="#" class="resend-otp-link">
							</a>
						</p>
					</div>
			</div>
			<div class="alert"></div>
		</div>
	</div>
</form>
