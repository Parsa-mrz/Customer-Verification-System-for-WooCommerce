(function ($) {
	'use strict';

	$(document).ready(function () {
		//Switch tabs.
		const $tabLinks = $('.tab-link');
		const $tabContents = $('.tab-content');

		$tabLinks.on('click', function () {
			$tabLinks.removeClass('active');
			$(this).addClass('active');

			$tabContents.removeClass('active');
			$('#' + $(this).data('tab')).addClass('active');
		});
		//hide alert message.
		$('.message[data-timeout]').each(function () {
			var $el = $(this);
			var timeout = parseInt($el.data('timeout'), 10) * 1000;

			setTimeout(function () {
				$el.fadeOut(600, function () {
					$el.remove();
				});
			}, timeout);
		});

	});


})(jQuery);
