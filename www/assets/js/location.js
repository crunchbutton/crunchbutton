
var Location = function( params ) {

	var self = this;
	
	this._properties = {
		verified: true,
		city: null,
		region: null,
		lat: null,
		lon: null,
		type: null,
		subtype: null,
		address: null,
		prep: null
	};

	// parse the city name from the result set
	self.setCityFromResult = function(results) {
		if (!results) {
			return;
		}

		var result = results[0] || results;

		switch ( result.types ) {
			default:
			case 'administrative_area_level_1':
				self._properties.city = result.address_components[0].long_name;
				self._properties.detail = 1;
				break;
			case 'locality':
				self._properties.city = result.address_components[0].long_name;
				self._properties.region = result.address_components[2].short_name;
				self._properties.detail = 2;
				break;
			case 'street_address':
				self._properties.city = result.address_components[2].long_name;
				self._properties.region = result.address_components[4].short_name;
				self._properties.detail = 5;
				break;
			case 'postal_code':
				self._properties.city = result.address_components[1].long_name;
				self._properties.region = result.address_components[3].short_name;
				self._properties.detail = 3;
				break;
			case 'route':
				self._properties.city = result.address_components[1].long_name;
				self._properties.region = result.address_components[3].short_name;
				self._properties.detail = 4;
				break;
		}

		for (var i = 0; i < result.address_components.length; i++) {
			for (var j = 0; j < result.address_components[i].types.length; j++) {
				if (result.address_components[i].types[j] == 'locality') {
					self._properties.city = result.address_components[i].long_name;
				}
			}
		}
	};
	
	// get address from lat/lon
	self.setAddressFromResult = function(results) {
		if (!results) {
			return;
		}
		var result = results[0] || results;
		self._properties.address = result.formatted_address;	
	};
	
	// get zip form results
	self.setZipFromResult = function(results) {
		if (!results) {
			return;
		}
		var result = results[0] || results;
		for (var x in result.address_components) {
			if ( result.address_components[x].types[0] == 'postal_code'){
				self._properties.zip = result.address_components[x].short_name;
				return;
			}
		}	
	};
	
	// calculate the distance of this object to another set of cords
	self.distance = function(from) {
		return App.loc.distance({
			to: {
				lat: self.lat(),
				lon: self.lon()
			},
			from: from
		});
	};

	// get the location city
	self.city = function() {
		return self._properties.city || '';
	};

	// get the preposition for the location
	self.prep = function() {
		return self._properties.prep || 'in';
	};

	// get the best posible address
	self.address = function() {
		return self._properties.address || '';
	};

	// format address for view
	self.formatted = function(location) {
		return self.address().replace(', USA', '');
	};

	// Return the zip code of a location
	self.zip = function() {
		return self._properties.zip || '';
	};
	
	// return latutude
	self.lat = function() {
		return self._properties.lat || 0;
	};

	// return longitude	
	self.lon = function() {
		return self._properties.lon || 0;	
	};
	
	// determine if useful for specific task
	self.valid = function() {
		switch (arguments[0]) {
			case 'restaurants':
				return (self.lat() || self.lon()) ? true : false;
				break;

			case 'order':
				return (self.lat() || self.lon()) && self.verified() && self.detail() >= 5 ? true : false;
				break;

			default:
				return false;
				break;
		}
	};
	
	// determine if verified
	self.verified = function() {
		return self._properties.verified;
	};
	
	// return the type of location
	self.type = function() {
		return self._properties.type;
	};
	
	// return the level of detail
	self.detail = function() {
		return self._properties.detail;
	};
	
	self.region = function(){
		return self._properties.region;
	}

	self.entered = function(){
		return self._properties.entered;
	}

	for (var x in params) {
		this._properties[x] = params[x];
	}
	
	if (params && params.results) {
		self.setCityFromResult(params.results);
		self.setAddressFromResult(params.results);
		self.setZipFromResult(params.results);
	}

}
