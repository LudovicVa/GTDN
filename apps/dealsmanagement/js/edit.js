require(['jquery', 'wity_ajax', 'date_picker'], function($, wity_ajax) {
	$(document).ready(function(){		
		var modal = $("#edition");
		var start_date = $('#start_time');
		var end_date = $('#end_time');
		
		//init timepicker
		start_date.datetimepicker({
            pick12HourFormat: true,
			startDate: moment()
        });	
		
		if(start_date.data("DateTimePicker").element.first().find('input').val() && moment(start_date.data("DateTimePicker").getDate()).isBefore()) {
			start_date.data("DateTimePicker").disable();
		}
		end_date.datetimepicker({
            pick12HourFormat: true,
			startDate: moment()
        });
		
		start_date.on('change.dp', function(value) {
			end_date.data("DateTimePicker").setStartDate(start_date.data("DateTimePicker").getDate());
		});
	});	
});