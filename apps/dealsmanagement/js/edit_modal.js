require(['jquery', 'wity_ajax', 'date_picker'], function($, wity_ajax) {
	$(document).ready(function(){
		/*
			
		$("[rel='tooltip']").tooltip();	

		$('#hover-cap-4col .thumbnail').hover(
			function(){
				$(this).find('.caption').slideDown(250); //.fadeIn(250)
			},
			function(){
				$(this).find('.caption').slideUp(250); //.fadeOut(205)
			}
		);	*/
		
		var modal = $("#edition");
		var start_date = $('#start_date');
		var end_date = $('#end_date');
		
		//init timepicker
		start_date.datetimepicker({
            pick12HourFormat: true,
			startDate: moment()
        });
		end_date.datetimepicker({
            pick12HourFormat: true,
			startDate: moment()
        });
		
		start_date.on('change.dp', function(value) {
			end_date.data("DateTimePicker").setStartDate(start_date.data("DateTimePicker").getDate());
		});
		
		function load_data_into_modal(data) {
			$('#title').html(data.deal_name);
			$('#deal_name').val(data.deal_name);
			$('#price').val(data.price);
			$('#original_price').val(data.original_price);
			//date
			start_date.data("DateTimePicker").setDate(data.start_time);
			if(moment(data.start_time).isBefore()) {
				start_date.data("DateTimePicker").disable();
			}
			end_date.data("DateTimePicker").setDate(data.end_time);
		}
		
		$(".edit").click(function() {
			var that = $(this);
			that.button('loading');
			var pk =  that.data('pk');
			var url =  that.data('url');
			
			options = {
				url : url,
				success : function(data) {
					alert(JSON.stringify(data));
					that.button('reset');
					/*modal.modal("hide").css(
						{
							'margin-top': function () {
								return -($(this).height() / 2);
							},
							'margin-left': function () {
								return -($(this).width() / 2);
							}
						}).modal('toggle');*/
					load_data_into_modal(data.result);
					modal.modal('toggle');
				},
				error : function(data_error) {
					alert(data_error);		
				}
			};
		
			wity_ajax(options);
		});

	});	
});