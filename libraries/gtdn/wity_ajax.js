/*
* Handles typical Ajax request using WNotes response system. If any notes contains a danger, the error handler is called, otherwise success is called
*/
define('wity_ajax', ['jquery'], function ($) {		
		return function(option) {			
			//extends para
			var settings = $.extend({
				// These are the defaults.
				url: '#',
				type: 'POST',
				data: '',
				success: '.wity-alert',
				error:  '.wity-alert', 
				call_success: call_success,
				call_error: call_error
			},  options);
			
			var ajaxOptions = {
				url: settings.url,
				data: settings.data,
				type: settings.type
			}; 
			
			
			
			/**
			* Call success handler or set succes values in div
			**/
			function call_success(data_success) {
				//call the method
				if(typeof settings.success=== 'function') {
					settings.success(data_success);
				} else if(typeof settings.success === "string") {
					//it is a dom element, display success message
					msg = "";
					for(var data in data_success.notes['success'] ) {
						msg += data_success.notes['success'][data] + "<br\>";
					}
					$(settings.success).html(msg);
					$(settings.success).addClass('has-success').removeClass('has-error');
				}
			}
			
			/**
			* Call error handler or set error in div
			**/
			function call_error(data_error) {
				//call the method
				if(typeof settings.error === 'function') {
					settings.error(data_error);
				} else if(typeof settings.error === "string") {
					msg = "";
					for(var data in data_error) {
						msg += data_error[data] + "<br\>";
					}
					$(settings.error).html(msg);
					$(settings.error).removeClass('has-success').addClass('has-error');
				}
			}
			
			ajaxOptions.success = function(data) {				
				var errors = new Array();
				if(!(data instanceof Object)) {
					data = $.parseJSON(data);
				}
				if(data.notes instanceof Array) {		
					for(var key in data.notes) {				
						if(data.notes[key].level == 'danger') {
							errors[data.notes[key].code] = data.notes[key].message;
						}
					}
				} else {
					errors.push('Bad response - Contact admin');
				}
				if(errors.length == 0) {			
					//call success
					call_success(data);
				} else {
					call_error(errors);
				}
			}
						  
			ajaxOptions.error = function(data) {
				var errors = new Array();
				errors.push('Bad response - Contact admin');
				call_error(errors); 
			}		
		
			return $.ajax(ajaxOptions);
		}
	});
