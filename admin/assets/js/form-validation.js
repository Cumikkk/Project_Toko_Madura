$(function() {
	'use strict'
	
	$('select.select2:not(.select2-hidden-accessible)').select2({
		placeholder: 'Choose one',
		width: '100%'
	});
	$('#selectForm').parsley();
	$('#selectForm2').parsley();
});