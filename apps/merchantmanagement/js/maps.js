require(['jquery', 'wity_ajax'], function($, wity_ajax) {
//state variables
	var lat, lng;
	var pk, url, modal;
	var msg_div_success;
	var button;
	var address;
	var geocoder = new google.maps.Geocoder();	
	var marker = new google.maps.Marker({
		  position: new google.maps.LatLng(lat, lng),
		  map: map,
		  title: 'Shop location',
		  draggable: true
	});
		
	function geocodeAndOpen() {
		$('#pleaseWaitDialog').modal('show');
		geocoder.geocode( { 'address': address}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				var location = results[0].geometry.location; 
				lat = location.lat();
				lng = location.lng();
				$('#pleaseWaitDialog').modal('hide');
				$(button.data('target')).modal('show');
			} else {
			  alert('Geocode was not successful for the following reason: ' + status);
			  $('#pleaseWaitDialog').modal('hide');
			}
		});
	}
	
	function openMap() {
		if(lat == 0 && lng == 0) { 
			var result = geocodeAndOpen(address);
			if(!result) {
				that.editable('show');
			}
		} else {
			$(button.data('target')).modal('show');
		}		
		msg_div_success = button.parents('table').first().siblings('.alert');
	}

	map.setZoom(13);
	
	 $('#map_modal').on('shown', function () {
		google.maps.event.trigger(map, "resize");
		map.panTo(new google.maps.LatLng(lat, lng));
		marker.setPosition(new google.maps.LatLng(lat, lng));
		modal = $(this);
	});	
	
	$("[data-name='address']").on('save', function(e, params) {
		var that = $(this);
		button = $(this).siblings('.open_maps').first();
		lat = 0;
		lng = 0;
		pk = button.data('pk');
		url = button.data('url');
		address = params.newValue;
		openMap();
	});
	
	$('.open_maps').on('click', function() {
		button = $(this);
		lat = button.data('lat');
		lng = button.data('lng');
		pk = button.data('pk');
		url = button.data('url');
		address = button.siblings("[data-name='address']").first().html();
		openMap();
	});
	
	var msg_div = $('#map_error');
	
	$('#map_reset').on('click', function() {
		map.panTo(new google.maps.LatLng(lat, lng));
		marker.setPosition(new google.maps.LatLng(lat, lng));
	});
	
	$('#map_revert_to_address').on('click', function() {
		var that = $(this);
		that.attr('disabled', true);
		if(address !== '') {
			geocoder.geocode( { 'address': address}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					var location = results[0].geometry.location; 
					lat = location.lat();
					lng = location.lng();
					$('#pleaseWaitDialog').modal('hide');
					$(button.data('target')).modal('show');
					that.attr('disabled', false);
				} else {
					alert('Geocode was not successful for the following reason: ' + status);
					$('#pleaseWaitDialog').modal('hide');
					that.attr('disabled', false);
				}
			});
		}
		map.panTo(new google.maps.LatLng(lat, lng));
		marker.setPosition(new google.maps.LatLng(lat, lng));
	});
	
	$('#map_confirm').on('click', function() {
		var position = marker.getPosition(new google.maps.LatLng(lat, lng));
		var that = $(this);
		that.attr('disabled', true);
		that.attr('disabled', true);
		var value =  position.lat() + ',' + position.lng();
		options = {
			url : url,
			success : function(data_success) {
				modal.modal('hide');
				that.attr('disabled', false);
				var msg = "Position saved";
				//Display msg
				msg_div_success.addClass('alert-success')
							.removeClass('alert-danger')
							.html(msg).show();							
				//button set lat lng
				button.data('lat', data_success['lat']).data('lng', data_success['lng']);
				
				setTimeout(function() {
					msg_div_success.fadeTo(500, 0).slideUp(500, function(){
						$(this).removeAttr("style").hide(); 
					});
				}, 2000);	
			},
			error : function(data_error) {
				that.attr('disabled', false);
				msg = "";
				for(var data in data_error) {
					msg += data_error[data] + "<br\>";
				}
				msg_div.html(msg);
				msg_div.show();
				msg_div.removeClass('alert-error').addClass('alert-success');
			},
			data : {pk: pk, name: 'latlong', value: value}
		};
		
		return wity_ajax(options);
		
	});	
});