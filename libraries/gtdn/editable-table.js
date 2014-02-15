function displayTemporaryMessage(msg_div,msg) {
	msg_div.addClass('alert-success')
			.removeClass('alert-danger')
			.html(msg).show();
						
	setTimeout(function() {
		msg_div.fadeTo(500, 0).slideUp(500, function(){
			$(this).removeAttr("style").hide(); 
			});
	}, 2000);	
}

function displayError(msg_div,msg) {
	msg_div.addClass('alert-danger')
			.removeClass('alert-success')
			.html(msg).show();
}

function success (data) {
	var msg = '';
	var error = false;
	if(data.notes instanceof Array) {
		for(var key in data.notes) {				
			if(data.notes[key].level == 'danger') {
				error = true;
				msg += data.notes[key].message + '\n';
			}
		}
	} else {
		error = true;
		msg += 'Bad response - Contact admin\n';
	}
	if(error) {
		return msg;
	}
}

/**
** Declare a new record row
**/
function declareNewRow(row) {	
	var class_form = row.find('a.add');
	var submit_button = row.find('.submit');
	var table = row.parents('table');
	var msg_div = row.parents('table').first().siblings('.alert');
	var hidden = row.next('.new-row-collapse').first();
	
	/**
	** Clone a row
	**/
	function cloneRow(class_form, row, id)
	{
		class_form.removeClass('editable-unsaved'); //switch to save
		var clone = row.clone(); // copy children too
				
		class_form.removeClass('add').addClass('editable-data'); //switch to editable
		class_form.editable('option', 'success', function(data) {
				return success(data);
			}
		);
		
		//Row operations
		row.find(".submit").remove();
		var clonehidden;
		//in case its a merchant, we have to activate the collapsing stuff
		if(hidden.length != 0) {
			row.find('.accordion-toggle').attr('data-target', '#row' + id);
			clonehidden = hidden.clone();
			//activate the hidden row
			hidden.find('.accordian-body').attr('id', 'row' + id);
			//add id to url for submit button
			hidden.find('.submit').each(function() {			
				$(this).attr('data-url', $(this).attr('data-url') + id);				
			});
		}
		
		row.removeAttr("id");						//remove id
		row.removeClass("new-row");					//remove class new-row
		row.removeAttr("data-id");					//remove useless attributes
		row.removeAttr("data-name");
		row.find('.id').html(id);					//add id value
				
		//in case its a merchant, we have to activate the collapsing stuff
		if(typeof(clonehidden) != 'undefined') { // add new row to end of table
			row.next('.new-row-collapse').after(clone);
			row.next('.new-row-collapse').removeClass('.new-row-collapse');
			clone.after(clonehidden);			
		} else {
			row.after(clone);
		}
		
		//Reset form
		clone.find('a.add').attr('class', 'add')
				.editable('setValue', null)
				.editable('option', 'pk', null);
				
		//redeclare
		declareNewRow(clone);
	}
	
	//declare as editable
	class_form.editable();
	
	class_form.editable('option', 'success', function(data) {
		return success (data);
	});
	
	class_form.editable('option', 'send', 'always');
	
	//Switch
	/*class_form.on('save', function(){
		var that = this;
		setTimeout(function() {
			var nextField = $(that).parents('td').first().next().find('a.add:not(.hide)');
			nextField.editable('show');
		}, 200);
	});*/
	
	submit_button.click(function() {
		class_form.editable('submit', {
			url: submit_button.attr('data-url'),
			success: function(data) {
				var msg = '';
				var error = false;
				var id, msg_success;
				if(data.notes instanceof Array) {		
					for(var key in data.notes) {				
						if(data.notes[key].level == 'danger') {
							error = true;
							msg += data.notes[key].message + '<br/>';
						} else if(data.notes[key].code == 'id') {
							id = data.notes[key].message;
						} else if(data.notes[key].code == 'success') {
							msg_success = data.notes[key].message;
						}
					}
				} else {
					error = true;
					msg += 'Bad response - Contact admin <br/>';
				}
				if(error) {
					displayError(msg_div, msg);
				} else {
					class_form.editable('option', 'pk', id); 
										
					cloneRow(class_form, row, id);
					
					//button
					row.find(".delete_row").removeClass("hide");
					row.find(".delete_row").attr("data-pk", id);
					row.find(".delete_merchant").removeClass("hide");
					row.find(".delete_merchant").attr("data-pk", id);
					
					//Display message
					displayTemporaryMessage(msg_div,msg_success);					
				} 
			},
			error: function(data) {
				var msg = '';
				if(data.errors) {                //validation error
					$.each(data.errors, function(k, v) { msg += k+": "+v+"<br>"; });  
				} else if(data.responseText) {   //ajax error
					msg = data.responseText; 
				}
				displayError(msg_div,msg);
			},
			data:submit_button.attr('data-options')
		}); 
	});
}

/**
** Delete a record
**/
function deleteRecord(row, msg_div, url, pk, name) {
	var ajaxOptions = {
			url: url,
			data: {pk: pk, name: name},
			type: 'POST'
		};                  

	ajaxOptions.success = function(data) {
		var msg = '';
		var error = false;
		var id, msg_success;
		if(data.notes instanceof Array) {		
			for(var key in data.notes) {				
				if(data.notes[key].level == 'danger') {
					error = true;
					msg += data.notes[key].message + '<br/>';
				} else if(data.notes[key].code == 'success') {
					msg_success = data.notes[key].message;
				}
			}
		} else {
			error = true;
			msg += 'Bad response - Contact admin <br/>';
		}
		if(!error) {			
			//Display message
			displayTemporaryMessage(msg_div,msg_success);
			
			//trigger delete event
			var hidden = row.next('.new-row-collapse').first();
			if(hidden != 'undefined') { hidden.remove() }
			row.remove();
		} else {
			displayError(msg_div, msg);
		}
	}
				  
	ajaxOptions.error = function(data) {
		displayError(msg_div,JSON.stringify(data));
	}							 
	
	// perform ajax request
	$.ajax(ajaxOptions);
}

//----------------------------------------------------------------------------------
//required part
require(['jquery', 'bootstrap3-editable'], function($) {
	$.fn.editable.defaults.mode = 'inline';
	$.fn.editable.defaults.savenochange = true;
	$.fn.editable.defaults.send = 'always';
	$.fn.editable.defaults.onblur = 'submit';

//when ready
	$(document).ready(function() {
		$('.editable-data').editable({
			success: function(data) {
				return success (data);
			},
			error: function(data) {
				return 'Bad request - Contact admin';
			}
		});     
		
		$('.delete_merchant').click(function() {
			var msg_div = $(this).parents('table').first().siblings('.alert');
			var that = $(this);
			$('#confirmation_window').find('#confirm').unbind('click').click(function() {
						deleteRecord(that.closest('tr'), msg_div, that.attr('data-url'), that.attr('data-pk'), that.attr('data-name'))
					}
			);
			$('#confirmation_window').modal('show');
			
		});
		
		$('.delete_row').click(function() {
			var msg_div = $(this).parents('table').first().siblings('.alert');
			deleteRecord($(this).closest('tr'), msg_div, $(this).attr('data-url'), $(this).attr('data-pk'), $(this).attr('data-name'));
		});
		
		$('.new-row').each(function() {
			declareNewRow($(this));
		});
	});
});