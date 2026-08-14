$(function() {
	'use strict'
	$('.main-form-group .form-control').on('focusin focusout', function() {
		$(this).parent().toggleClass('focus');
	});
	$('select.select2:not(.select2-hidden-accessible)').select2({
			placeholder: 'Choose one'
		});
});