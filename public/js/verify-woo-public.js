(function ($) {
	'use strict';

	jQuery(document).ready(function ($) {
		const notify = $('.alert');
		$(".signIn").click(function (event) {
			event.preventDefault();

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
						});
					} else {
						showAlert('error', response.data);
					}
					autoHideAlert();
				},
				error: function (jqXHR, textStatus, errorThrown) {
					showAlert('error', errorThrown);
					autoHideAlert();
				}
			});
		});

		$(".verify").click(function (event) {
			event.preventDefault();
			let otp = $('#otp-pass').val();
			let user_phone = $('#user_phone').val().trim();

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
							window.location.href = response.data.redirect;
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
							});
						}
					}
				},
				error: function (jqXHR, textStatus, errorThrown) {
					console.error(errorThrown);
					showAlert('error', errorThrown)
					autoHideAlert();
				}
			});
		});

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
	});
})(jQuery);
