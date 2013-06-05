
var Location = function(params) {

	var self = this;

	this.verified = true;
	this.city = null;
	this.region = null;
	this.lat = null;
	this.lon = null;
	this.type = null;
	this.address = null;

	// parse the city name from the result set
	self.setCityFromResult = function(results) {
		if (!results) {
			return;
		}
		switch (results[0].types[0]) {
			default:
			case 'administrative_area_level_1':
				self.city = results[0].address_components[0].long_name;
				break;
			case 'locality':
				self.city = results[0].address_components[0].long_name;
				App.loc.realLoc.region = results[0].address_components[2].short_name;
				break;
			case 'street_address':
				self.city = results[0].address_components[2].long_name;
				self.region = results[0].address_components[4].short_name;
				break;
			case 'postal_code':
			case 'route':
				self.city = results[0].address_components[1].long_name;
				self.region = results[0].address_components[3].short_name;
				break;
		}

		// @todo: do we need this?
		for (var i = 0; i < results[0].address_components.length; i++) {
			for (var j = 0; j < results[0].address_components[i].types.length; j++) {
				if (results[0].address_components[i].types[j] == 'locality') {
					self.city = results[0].address_components[i].long_name;
				}
			}
		}
	};
	
	// get address from lat/lon
	self.setAddressFromResult = function(results) {
		if (!results) {
			return;
		}
		self.type = 'reverse';
		self.address = results[0].formatted_address;
	};
	
	self.pos = function() {
		if (!self.realLoc) {
			return self.aproxLoc;
		} else {
			return self.realLoc;		
		}
	};
	
	// calculate the distance of this object to another set of cords
	self.distance = function(from) {
		return App.loc.distance({
			to: {
				lat: self.lat,
				lon: self.lon
			},
			from: from
		});
	};

	// get the location city
	self.city = function() {
		return (self.pos() && self.pos().city) ? self.pos().city : '';
	};

	// get the preposition for the location
	self.prep = function() {
		return (self.pos() && self.pos().prep) ? self.pos().prep : 'in';
	};

	// get the best posible address
	self.address = function() {
		return self.pos() ? (self.pos().addressEntered || self.pos().addressReverse || self.pos().addressAlias) : '';
	};

	for (var x in params) {
		this[x] = params[x];
	}
	
	if (params && params.results) {
		self.setCityFromResult(params.results);
		self.setAddressFromResult(params.results);
	}

}
