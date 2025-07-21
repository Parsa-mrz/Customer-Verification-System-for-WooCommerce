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
<form action="">
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
					<div class="group">
						<input id="otp-pass" placeholder="<?php echo esc_html__( 'Enter OTP', 'verify-woo' ); ?>" name="otp" type="number" class="input">
					</div>
					<div class="group">
						<input type="submit" name="verify" class="button verify" value="<?php echo esc_html__( 'Verify OTP', 'verify-woo' ); ?>">
					</div>
			</div>
			<div class="alert"></div>
		</div>
	</div>
</form>
