require(['jquery', 'bootstrap-modal-core'], function($, wity_ajax) {
	var map;
	var mapOptions = {			
		center: new google.maps.LatLng(-34.397, 150.644),
		zoom: 8
	};
	map = new google.maps.Map(document.getElementById("map-canvas"),mapOptions);

//state variables
	var lat, lng;
	var modal;
	var button, input_lat, input_lng;
	var address;
	var geocoder = new google.maps.Geocoder();	
	
	var marker = new google.maps.Marker({
		  position: new google.maps.LatLng(lat, lng),
		  map: map,
		  title: 'Shop location',
		  draggable: true
	});
	
	function geocodeAndOpen() {
		geocoder.geocode( { 'address': address}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				var location = results[0].geometry.location; 
				lat = location.lat();
				lng = location.lng();
				$(button.data('target')).modal('show');
			} else {
			  alert('Geocode was not successful for the following reason: ' + status);
			}
		});
	}
	
	function openMap() {
		if(lat == 0 && lng == 0) { 
			var result = geocodeAndOpen(address);
		} else {
			$(button.data('target')).modal('show');
		}		
	}

	map.setZoom(13);
	
	 $('#map_modal').on('shown', function () {
		google.maps.event.trigger(map, "resize");
		map.panTo(new google.maps.LatLng(lat, lng));
		marker.setPosition(new google.maps.LatLng(lat, lng));
		modal = $(this);
	});	
	
	//Open a map
	$('.open_maps').on('click', function() {
		button = $(this);
		input_lat = $(this).siblings("[name=lat]");
		input_lng = $(this).siblings("[name=lng]");
		lat = input_lat.val();
		lng = input_lng.val();
		address = button.parent().next().children("[name='address']").val();
		openMap();
	});
	
	//map_reset
	$('#map_reset').on('click', function() {
		map.panTo(new google.maps.LatLng(lat, lng));
		marker.setPosition(new google.maps.LatLng(lat, lng));
	});
	
	//Revert
	$('#map_revert_to_address').on('click', function() {
		var that = $(this);
		that.attr('disabled', true);
		if(address !== '') {
			geocoder.geocode({'address': address}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					var location = results[0].geometry.location; 
					lat = location.lat();
					lng = location.lng();
					$(button.data('target')).modal('show');
					that.attr('disabled', false);
				} else {
					alert('Address unknown, please change address');
					that.attr('disabled', false);
				}
			});
		}
		map.panTo(new google.maps.LatLng(lat, lng));
		marker.setPosition(new google.maps.LatLng(lat, lng));
	});
	
	//Confirmation
	$('#map_confirm').on('click', function() {
		var position = marker.getPosition(new google.maps.LatLng(lat, lng));
		input_lat.val(position.lat());
		input_lng.val(position.lng());
		modal.modal('hide');
	});
});