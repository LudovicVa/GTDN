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

/**
** Declare a new record row
**/
function declareNewRow(row) {

	/**
	** Clone a row
	**/
	function cloneRow(class_form, row, id)
	{
		class_form.removeClass('editable-unsaved'); //switch to save
		var clone = row.clone(); // copy children too
				
		class_form.removeClass('add').addClass('editable-data'); //switch to editable
		class_form.editable('option', 'success', function(data) {
				if(!data.success) {
					return data.msg;
				}
			}
		);
		
		//Row operations
		row.find(".submit").remove();
		var clonehidden;
		//in case its a merchant, we have to activate the collapsing stuff
		if(row.attr("data-name") == 'merchant') {
			row.find('.accordion-toggle').attr('data-target', '#row' + id);
			clonehidden = row.next('.new-row-collapse').first().clone();
			//activate the hidden row
			var test = row.next('.new-row-collapse').find('.accordian-body');
			row.next('.new-row-collapse').find('.accordian-body').attr('id', 'row' + id);
			//add id to url for submit button
			row.next('.new-row-collapse').find('.submit').attr('data-url', row.next('.new-row-collapse').find('.submit').attr('data-url') + id);
		}
		
		row.removeAttr("id");						//remove id
		row.removeClass("new-row");					//remove class new-row
		row.removeAttr("data-id");					//remove useless attributes
		row.removeAttr("data-name");
		
				
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
	
	var class_form = row.find('a.add');
	var submit_button = row.find('.submit');
	var table = row.parents('table');
	var msg_div = row.parents('table').first().siblings('.alert');
	
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
			} else if(verif_type == 'password') {
				if(v.password != v.password_confirm) {
					return 'Password and its confirmation must be equal !';
				}
			}
		} 
	);
	
	class_form.editable('option', 'mode', 'inline');
	
	//Switch
	class_form.on('save', function(){
		var that = this;
		setTimeout(function() {
			var nextField = $(that).parents('td').first().next().find('a.add');
			nextField.editable('show');
		}, 200);
	});
	
	submit_button.click(function() {
		class_form.editable('submit', {
			url: submit_button.attr('data-url'),
			success: function(data) {
				if(data.success) {
					var msg = 'Record successfully added.';
					//msg += JSON.stringify(data, null, 2);
					class_form.editable('option', 'pk', data.id); 
										
					cloneRow(class_form, row, data.id);
					
					//button
					row.find(".delete_row").removeClass("hide");
					row.find(".delete_row").attr("data-pk", data.id);
					row.find(".delete_merchant").removeClass("hide");
					row.find(".delete_merchant").attr("data-pk", data.id);
					
					//Display message
					displayTemporaryMessage(msg_div,msg);					
				} else {
					displayError(msg_div,data.msg);
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
			}
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
		if(data.success) {
			var msg = 'Data successfully removed';
			
			//Display message
			displayTemporaryMessage(msg_div,msg);
			
			row.remove();
		} else {
			displayError(msg_div,data.msg);
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

	
//Password editable--------------------------------
	 "use strict";
    
    var Password = function (options) {
        this.init('address', options, Password.defaults);
    };

    //inherit from Abstract input
    $.fn.editableutils.inherit(Password, $.fn.editabletypes.abstractinput);

    $.extend(Password.prototype, {
        /**
        Renders input from tpl

        @method render() 
        **/        
        render: function() {
           this.$input = this.$tpl.find('input');
        },
        
        /**
        Default method to show value in element. Can be overwritten by display option.
        
        @method value2html(value, element) 
        **/
        value2html: function(value, element) {
            if(!value) {
                $(element).empty();
                return; 
            }
            var html = '********';
            $(element).html(html); 
        },
        
        /**
        Gets value from element's html
        
        @method html2value(html) 
        **/        
        html2value: function(html) {
          return null;  
        },
      
       /**
        Converts value to string. 
        It is used in internal comparing (not for sending to server).
        
        @method value2str(value)  
       **/
       value2str: function(value) {
           var str = '';
           if(value) {
               for(var k in value) {
                   str = str + k + ':' + value[k] + ';';  
               }
           }
           return str;
       }, 
       
       /*
        Converts string to value. Used for reading value from 'data-value' attribute.
        
        @method str2value(str)  
       */
       str2value: function(str) {
           /*
           this is mainly for parsing value defined in data-value attribute. 
           If you will always set value by javascript, no need to overwrite it
           */
           return str;
       },                
       
       /**
        Sets value of input.
        
        @method value2input(value) 
        @param {mixed} value
       **/         
       value2input: function(value) {
           if(!value) {
             return;
           }
           this.$input.filter('[name="password"]').val('');
           this.$input.filter('[name="password_confirm"]').val('');
       },       
       
        /**
        Returns value of input.
        
        @method input2value() 
       **/          
       input2value: function() { 
			return {
				password: this.$input.filter('[name="password"]').val(),
				password_confirm: this.$input.filter('[name="password_confirm"]').val()
			};
       },        
       
        /**
        Activates input: sets focus on the first field.
        
        @method activate() 
       **/        
       activate: function() {
            this.$input.filter('[name="password"]').focus();
       },  
       
       /**
        Attaches handler to submit form in case of 'showbuttons=false' mode
        
        @method autosubmit() 
       **/       
       autosubmit: function() {
           this.$input.keydown(function (e) {
                if (e.which === 13) {
                    $(this).closest('form').submit();
                }
           });
       }	   
    });

    Password.defaults = $.extend({}, $.fn.editabletypes.abstractinput.defaults, {
        tpl: '<div class="container">' +
			'<div class="editable-address row" style="margin:2px;"><span class="col-md-4">Password: </span><span class="col-md-8"><input type="password" name="password" class="input-small"></span></div>'+
             '<div class="editable-address row" style="margin:2px"><span class="col-md-4">Confirmation: </span><span class="col-md-8"><input type="password" name="password_confirm" class="input-small"></span></div>'+
			 '</div>',             
        inputclass: ''
    });

    $.fn.editabletypes.password = Password;
	
//when ready
	$(document).ready(function() {
		$('.editable-data').editable({
			success: function(data) {
				if(!data.success) {
					return data.msg;
				}
			}
		});
		
		$('.editable-password').editable({
			success: function(data) {
				if(!data.success) {
					return data.msg;
				}
			},
			validate: function(value) {
				if(value.password != value.password_confirm) {
					return 'Password and its confirmation must be equals !';
				}
			},
			mode: 'popup',
			emptytext: '********',
			emptyclass: '',
			placement:'left'
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