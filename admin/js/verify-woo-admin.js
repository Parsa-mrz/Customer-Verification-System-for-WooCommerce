(function ($) {
	'use strict';

	$(document).ready(function () {
		const $tabLinks = $('.tab-link');
		const $tabContents = $('.tab-content');

		$tabLinks.on('click', function () {
			$tabLinks.removeClass('active');
			$(this).addClass('active');

			$tabContents.removeClass('active');
			$('#' + $(this).data('tab')).addClass('active');
		});

	});


})(jQuery);
