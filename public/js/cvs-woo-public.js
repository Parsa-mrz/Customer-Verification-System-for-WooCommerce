(function ($) {
	'use strict';

	$(document).ready(function () {
		const notify = $('.alert');
		const signInButton = $(".signIn");
		const verifyButton = $(".verify");
		const signInLoader = $('.sign-in-loader');
		const verifyLoader = $('.verify-loader');
		const resendButton = $('.resend-otp-link');

		const urlParams = new URLSearchParams(window.location.search);
		const messageKey = urlParams.get('verifywoo_msg');
		const redirectUrl = urlParams.get('verifywoo_redirect_url');

		if (messageKey) {
			let message = '';
			switch (messageKey) {
				case 'login_checkout_required':
					message = 'You must log in before checking out.';
					showAlert('warning', message);
					break;
			}
		}

		const verifyOtpFormWrapper = $('.login-form.verify-otp-form');
		if (verifyOtpFormWrapper.length) {
			const otpInputs = document.querySelectorAll('.otp-input-group .otp-input');
			const combinedOtpInput = document.getElementById('combined_otp');

			function combineOtp() {
				let combinedValue = '';
				otpInputs.forEach(input => {
					combinedValue += input.value;
				});
				if (combinedOtpInput) {
					combinedOtpInput.value = combinedValue;
				}
			}

			otpInputs.forEach((input, index) => {
				input.addEventListener('input', function (e) {
					if (this.value.length > 1) {
						this.value = this.value.slice(0, 1);
					}
					combineOtp();

					if (this.value && index < otpInputs.length - 1) {
						otpInputs[index + 1].focus();
					} else if (this.value && index === otpInputs.length - 1) {
						verifyOtpHandler();
					}
				});

				input.addEventListener('keydown', function (e) {
					if (e.key === 'Backspace' && this.value === '' && index > 0) {
						otpInputs[index - 1].focus();
					}
				});

				input.addEventListener('paste', function (e) {
					e.preventDefault();
					const pasteData = e.clipboardData.getData('text').trim();
					if (pasteData.length > 0 && /^\d+$/.test(pasteData)) {
						for (let i = 0; i < otpInputs.length; i++) {
							if (i < pasteData.length) {
								otpInputs[i].value = pasteData[i];
							} else {
								otpInputs[i].value = '';
							}
						}
						combineOtp();

						const lastFilledIndex = Math.min(pasteData.length - 1, otpInputs.length - 1);
						if (lastFilledIndex < otpInputs.length - 1) {
							otpInputs[lastFilledIndex + 1].focus();
						} else {
							otpInputs[lastFilledIndex].focus();
							if (pasteData.length >= otpInputs.length) {
								verifyOtpHandler();
							}
						}
					}
				});
			});

			combineOtp();
		}

		const sendOtpHandler = function (event) {
			event.preventDefault();

			signInButton.find('.button-text').hide();
			signInButton.css('padding', 0);
			signInLoader.show();

			let user_phone = $('#user_phone').val().trim();
			let sendOtpForm = $('.send-otp-form');
			let verifyOtpForm = $('.verify-otp-form');

			$.ajax({
				url: verifyWooVars.ajax_url,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'verify_woo_send_otp',
					user_phone: user_phone,
					_nonce: verifyWooVars.nonce,
				},
				success: function (response) {
					if (response.success) {
						showAlert('success', response.data);
						sendOtpForm.fadeOut(300, function () {
							verifyOtpForm.fadeIn(300);
							$('.otp-input').val('');
							$('#combined_otp').val('');
							$('#otp-input-1').focus();
						});
						showCounter();
					} else {
						showAlert('error', response.data);
					}
					autoHideAlert();
				},
				error: function (jqXHR, textStatus, errorThrown) {
					showAlert('error', errorThrown);
					autoHideAlert();
				},
				complete: function () {
					signInButton.find('.button-text').show();
					signInLoader.hide();
				}
			});
		};

		signInButton.click(sendOtpHandler);
		resendButton.click(sendOtpHandler);

		const verifyOtpHandler = function (event) {
			if (event) event.preventDefault();

			verifyButton.find('.button-text').hide();
			verifyLoader.show();

			let otp = '';
			if ($('#combined_otp').length) {
				otp = $('#combined_otp').val();
			} else {
				return false;
			}

			let user_phone = $('#user_phone').val().trim();

			if (otp.length !== 4 || !/^\d{4}$/.test(otp)) {
				showAlert('error', 'Please enter the complete 4-digit OTP.');
				return;
			}

			$.ajax({
				url: verifyWooVars.ajax_url,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'verify_woo_check_otp',
					user_phone: user_phone,
					otp: otp,
					_nonce: verifyWooVars.nonce
				},
				success: function (response) {
					if (response.success) {
						showAlert('success', response.data.message);
						setTimeout(function () {
							window.location.href = redirectUrl ?? response.data.redirect;
						}, 400);
						$('.verify-otp-form').fadeOut();
					} else {
						showAlert('error', response.data);

						if (
							response.data.includes('expired') ||
							response.data.includes('Too many attempts') ||
							response.data.includes('Maximum attempts')
						) {
							$('.verify-otp-form').fadeOut(300, function () {
								$('.send-otp-form').fadeIn(300);
								if ($('.otp-input').length) {
									$('.otp-input').val('');
								}
								if ($('#combined_otp').length) {
									$('#combined_otp').val('');
								}
							});
						}
					}
				},
				error: function (jqXHR, textStatus, errorThrown) {
					console.error(errorThrown);
					showAlert('error', errorThrown)
					autoHideAlert();
				},
				complete: function () {
					verifyButton.find('.button-text').show();
					verifyLoader.hide();
				}
			});
		};

		$(".verify").click(verifyOtpHandler);

		function autoHideAlert() {
			setTimeout(function () {
				notify.fadeOut(500, function () {
					notify.removeClass('alert-success alert-error').empty().show();
				});
			}, 5000);
		}

		function showAlert(type, message) {
			notify.removeClass('alert-error alert-success')
				.addClass(type === 'success' ? 'alert-success' : 'alert-error')
				.html('<p>' + message + '</p>')
				.fadeIn();

			setTimeout(() => {
				notify.fadeOut();
			}, 4000);
		}

		function showCounter() {
			let resendLink = $('.resend-otp-link');
			let countdown = verifyWooVars.expire_time_otp;
			let interval = setInterval(() => {
				if (countdown > 0) {
					resendLink.text(`Resend OTP in ${countdown--}s`).addClass('disabled');
				} else {
					clearInterval(interval);
					resendLink.text('Resend OTP').removeClass('disabled');
				}
			}, 1000);
		}

	});
})(jQuery);
