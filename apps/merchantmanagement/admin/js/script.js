require(['jquery', 'bootstrap3-editable'], function($) {
		$(document).ready(function() {
			$('.editable-data').editable({
				success: function(data) {
					if(!data.success) {
						return data.msg;
					}
				}
			});
			$('.editable-data-popup').editable({
				success: function(data) {
					if(!data.success) {
						return data.msg;
					}
				},
				mode: 'popup'
			});
			$('.delete_row').click(function() {
				//$(

			});
		});
	});
	
function declare(id, type) {
	function cloneRow(class_form, row, table)
	{
		class_form.removeClass('editable-unsaved');
		var clone = row.clone(); // copy children too
				
		class_form.removeClass('new_' + type + '_' + id).addClass('editable-data-popup');
		
		row.find("td:last").remove();
		row.find("td:last").attr("colspan", 2);
		row.after(clone); // add new row to end of table
		row.removeAttr("id");
				
		$('.new_' + type + '_' + id).attr('class', 'new_' + type + '_' + id)
				.editable('setValue', null)
				.editable('option', 'pk', null);      
	}
	
	var class_form = $('.new_' + type + '_' + id);
	var submit_button = $('#new_' + type + '_submit_' +id);
	var row = $('#row_new_' + type + '_' +id);
	var table = $('#table_' + type);
	var msg_div =	$('#msg_' + type + '_' + id);
	
	class_form.editable();
	
	class_form.editable('option', 'validate',
		function(v) {
			var verif_type = $(this).attr('data-verif');
			if(verif_type == 'required') {
				if(v == '') { 
					return 'Required field!'
				}
			} else if(verif_type == 'email') {
				var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
				if( v == '' || !re.test(v)) {
					return 'Invalid email';
				}
			}
		} 
	);
	
	class_form.editable('option', 'mode', 'popup');
	
	//Switch
	class_form.on('save', function(){
		var that = this;
		setTimeout(function() {
			$(that).closest('td').next().find('.new_' + type + '_' + id).editable('show');
		}, 200);
	});
	
	submit_button.click(function() {
		class_form.editable('submit', {
			url: submit_button.attr('data-url'),
			success: function(data) {
				if(data.success) {
					var msg = 'Contact email successfully added.';
					class_form.editable('option', 'pk', data.id); 
					cloneRow(class_form, row, table);
					msg_div.addClass('alert-success')
						.removeClass('alert-danger')
						.removeClass('hide')
						.html(msg).show();
						
					setTimeout(function() {
						msg_div.fadeTo(500, 0).slideUp(500, function(){
							$(this).addClass('hide').removeAttr("style"); 
							});
					}, 2000);	
					
					//redeclare class_form
					declare(id, type); 
				} else {
					msg_div.removeClass('alert-success')
						.addClass('alert-danger')
						.removeClass('hide')
						.html(data.msg).show();
				}
			},
			error: function(data) {
				var msg = '';
				if(data.errors) {                //validation error
					$.each(data.errors, function(k, v) { msg += k+": "+v+"<br>"; });  
				} else if(data.responseText) {   //ajax error
					msg = data.responseText; 
				}
				msg_div.removeClass('alert-success')
					.addClass('alert-danger')
					.removeClass('hide').html(msg).show();
			}
		}); 
	});
}