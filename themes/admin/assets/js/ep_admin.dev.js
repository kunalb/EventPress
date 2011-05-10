
var ep = (function(){
	var self = this;
	var $ = jQuery;

	self.geocoder = {};
	self.map = {};
	self.marker = {}

	self.init = function() {
		if( typeof( google ) != 'undefined' ) {
			self.geocoder = new google.maps.Geocoder();
			self.latlng = new google.maps.LatLng(28.38, 77.12);
			var options = {
				zoom: 8,
				center: self.latlng,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			}
			self.map = new google.maps.Map(document.getElementById( 'ep-map-map' ), options);
			self.getGeoCode( $('#ep-venue-input').val() );
		}
	}

	self.getGeoCode = function( place ) {

		var address = place;
		if ( !self.geocoder )
			self.init(); 

		self.geocoder.geocode( { 'address' : address }, function ( results, stats ) {
			if ( google.maps.GeocoderStatus.OK == stats ) {
				self.map.setCenter(results[0].geometry.location);
				self.marker = new google.maps.Marker({
					map: self.map,
					position: results[0].geometry.location
				});
				$('#ep-map-latlong').val( results[0].geometry.location );
			} else {
				//ToDo Show an error!
			}
		});
	}

	self.setEvent = function() {
		$('#ep-venue-input').change(function(){
			self.latlng = self.getGeoCode( $(this).val() );
			self.map.setCenter( self.latlng );
			self.marker.setMap(null);
			self.marker = new google.maps.Marker({
				map: self.map,
				position: self.latlng
			});
		});
	}

	self.addDatePicker = function() {
		$('.datepicker').datetimepicker();
	}

	var rowindex = 0;
	var addfield = function() {
		$('#ep-reg-row-generic')
			.clone( true )
			.fadeIn()
			.attr( 'id', 'ep-reg-row-' + rowindex++ )
			.appendTo('#ep-add-form-row')
			.find('.ep-reg-element')
			.removeAttr('disabled');
		
		return false;
	}

	var changeextras = function() {
		if( $(this).val() != 'text' )
			$(this)
				.closest('.ep-reg-row')
				.find('.textbox-only')
				.css({'display': 'none'});
		else
			$(this)
				.closest('.ep-reg-row')
				.find('.textbox-only')
				.css({'display': 'table-row'});
	}

	var deleteme = function() {
		$(this)
			.closest('.ep-reg-row')
			.fadeOut( function() {
				$(this).remove();
			} );

		return false;
	}

	var regexpgen = function() {
		var result = '';

		switch( $(this).val() ) {
			case 'None':
				result = '';
				break;
			case 'Number':
				result = '^[0-9]*$';
				break;
			case 'Alphanumeric':
				result = '^[a-zA-Z0-9]*$';
				break;
			case 'Email':
				result = '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$';
				break;
		}

		if( $(this).val() != 'Other' )
			$(this)
				.closest('.ep-reg-row')
				.find('.ep-regexoption-value')
				.val( result );
	}

	var regexother = function() {
		$(this)
			.closest('.ep-reg-row')
			.find('.ep-regexoption')
			.val('Other');
	}

	self.regform = function() {
		//Disables the javascriptless checkbox. Just for kicks.
		$('#ep-add-new-nojs').attr('disabled', true); 

		//Add a new field
		$('#ep-new-field').click( addfield );

		//Hide extra fields
		$('.ep-reg-type')
			.change( changeextras )
	
		$.each( $('.ep-reg-type'), changeextras ); //For initialization

		//Delete link
		$('.ep-reg-delete').click( deleteme );

		//Initialize the clone for being javascript compatibility
		$('#ep-reg-row-generic')
			.css('display', 'none')
		$('#ep-reg-row-generic .ep-reg-element')
			.attr('disabled', 'disabled');

		$('.ep-regexoption').change( regexpgen );
		$('.ep-regexoption-value').change( regexother );
	}

	self.regcheckboxes = function() {
		$('.ep-regs-checkbox').change(function(e){
			if( $('.ep-regs-checkbox').is(':checked') ) {
				$('.ep-regs-checkboxes').attr('checked', true);
			} else {
				$('.ep-regs-checkboxes').attr('checked', false);
			}
		});
	}

	return self;
})();

jQuery(document).ready(function(){
	ep.addDatePicker();
	ep.init();
	ep.setEvent();
	ep.regform();
	ep.regcheckboxes();
});
