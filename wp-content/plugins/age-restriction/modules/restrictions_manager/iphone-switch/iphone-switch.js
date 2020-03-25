/*
Document   :  Iphone-Switch on/off
Author     :  Andrei Dinca, AA-Team http://codecanyon.net/user/AA-Team
*/

// Initialization and events code for the app
age_restrictionIphoneSwitch = (function ($) {
    "use strict";

    // public
    var debug_level = 0;

	// init function, autoload
	(function init() {

		// load the triggers
		$(document).ready(function() {
		});
	})();
	
	$.fn.age_restrictionIphoneSwitch = function() {
	
		// Iterate over checkboxes
		return this
		.each(function() {

			// Insert mark-up for switch
			$(this).before('<span class="age_restriction-iphone-switch">' + '<span class="mask" /><span class="background" />' + '</span>');
			// Hide checkbox
			$(this).hide();

			// Set inital state
			if (!$(this)[0].checked) {
				var left = "-25px";
				if ( $(this).prev().hasClass("large") ) left = "-56px";
					
				$(this).prev().find(".background").css({
					left : left
				});
			}

			// Toggle switch when clicked
			$(this).prev("span.age_restriction-iphone-switch").click(function() {
				// If on, slide switch off
				if ($(this).next()[0].checked) {
					var left = "-25px";
					if ( $(this).hasClass("large") ) left = "-56px";
					
					$(this).find(".background").animate({
						left : left
					}, 200);
					// Otherwise, slide switch on
				} else {
					$(this).find(".background").animate({
						left : "0px"
					}, 200);
				}
				// Toggle state of checkbox
				$(this).next()[0].checked = !$(this).next()[0].checked;
			});
		});
		// End each()

	};

	function triggers() {}

	// external usage
	return {
    }
})(jQuery);