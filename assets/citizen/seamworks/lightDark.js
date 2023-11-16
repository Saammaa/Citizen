!function ($, window, document) {
	"use strict";

	let $html = $('html');

	function init() {
		$('#theme-toggle').click(lightSwitch);
	}

	function lightSwitch(action) {
		if (action === 'off') {
			$html.addClass('skin-dark');
		} else if (action === 'on') {
			$html.removeClass('skin-dark');
		} else {
			$html.toggleClass('skin-dark');
		}

		if ($html.hasClass('skin-dark')) {
			localStorage.setItem('skinDark', 'yes');
			document.cookie = 'skinDark=y';
		} else {
			localStorage.setItem('skinDark', 'no');
			document.cookie = 'skinDark=n';
		}
	}

	init();
}(window.jQuery, window, document);