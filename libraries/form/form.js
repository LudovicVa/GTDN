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

	function success(response) {
		try {
			response = $.parseJSON(response);
			var msg = '';
			var error = false;
			if(response.notes instanceof Array) {		
				for(var key in response.notes) {				
					if(response.notes[key].level == 'danger') {
						error = true;
						msg += response.notes[key].message + '<br/>';
					}
				}
				if(error) {
					return msg;
				}
			} else {
				throw 'bad response';
			}
		} catch (e) {			
			return 'Bad response from server';
		}
	}
	
	function change_ajax() {
		$(this).attr('disabled', true);
		var key = this.options.name;
		var data = {pk: this.options.pk};
		data[key] = $(this).val();
		
		var ajaxOptions = {
			url: this.options.url,
			data: data,
			type: this.options.type
		};             
		
		return $.ajax(ajaxOptions);
	}

	$.fn.wity_form = function(options) {
		 var global_options = $.extend({
            // These are the defaults.
			url: '',
			type: 'POST',
			success: success
        },  options, $(this).data() );
		
		var inputs = $(this).find('input');
		
		//add options to each inputs
		inputs.each(function() {
			this.options = $.extend({
				scope: this, 
				name: $(this).attr('name'),
				container: $(this).parents('.form-group'),
				msg_div: $(this).parents('.form-group').find('.help-block')
			}, global_options, $(this).data());		
			
			//in case we give a string => ajax request
			if(typeof this.options.url === 'function') {
				this.options.change = url;
			} else {
				this.options.change = change_ajax;
			}
			
			//error
			this.error = function(msg) {
				this.options.container.removeClass('has-success')
					.addClass('has-error');
				this.options.msg_div.html(msg);
				$(this).attr('disabled', false);
				$(this).focus();
			}
			
			//error
			this.success = function() {
				this.options.container.removeClass('has-error')
					.addClass('has-success');
				this.options.msg_div.html('');
				$(this).attr('disabled', false);
			}
		});
		
		//prepare
		//inputs.prepare();
		inputs.focusout(function () {
			//On change
			$.when(this.options.change.call(this.options.scope))
				.done($.proxy(function(response) {
					var res = typeof this.options.success === 'function' ? this.options.success.call(this.options.scope, response) : null;	
					
					//if res is a string, there is an error
					if(typeof res == 'string') {
						this.error(res);
					} else {
						this.success();
					}						
				}, this))
				.fail($.proxy(function(xhr) {
					this.error('Request failed');
					
					$(this).attr('disabled', false);
				}, this));
			
		});
		
		//object.parents('div.form-group').addClass('has-success')
		//bind to click
		//this.click(		
	}

//when ready
	$(document).ready(function() {	
		$('.wform').wity_form();
		/*change(function() {
			$(this).attr('disabled', true);
			$(this).parents('div.form-group').addClass('has-success');
		});     */
	});
});