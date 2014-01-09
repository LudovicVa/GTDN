require(['jquery', 'wysihtml5-bootstrap/bootstrap3-wysihtml5', 'bootstrap-switch'], function($) {
	var switches = $( 'input[type="checkbox"].switch' );
	var modal_buttons = $('a.edit');	
		
	//Edit button related operation
	switches.on( "switch-change", function() {
		var value = $(this).bootstrapSwitch('state');
		if(value) {
			$('a.edit[data-pk='+ $(this).attr('data-pk') +']').removeAttr("disabled").fadeTo(600, 1.0);
		} else {
			$('a.edit[data-pk='+ $(this).attr('data-pk') +']').attr("disabled", false).fadeTo(600, 0);
		}
	});
	
	switches.bootstrapSwitch();
	
	$.each(modal_buttons, function() {		
		var value = $('.switch[type="checkbox"][data-pk='+ $(this).attr('data-pk') +']').bootstrapSwitch('state');
		$(this).attr("disabled", !value);
		if(!value) {		
			$(this).hide();
		}
	});
});

