require(['jquery', 'bootstrap3-editable'], function($) {
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
        tpl: '<div class="editable-address row" style="margin:2px;"><input type="password" name="password" class="form-control input-sm" placeholder="Password"></div>'+
             '<div class="editable-address row" style="margin:2px;"><input type="password" name="password_confirm" class="form-control input-sm" placeholder="Confirmation"></div>',            
        inputclass: '',
		clear: true
    });

    $.fn.editabletypes.password = Password;	
});