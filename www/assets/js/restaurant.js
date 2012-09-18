var Restaurant = function(id) {
	this.type = 'Restaurant';
	this.id_var = 'id_restaurant';
	this.resource = 'restaurant';
	var
		self = this,
		complete;
		
	$.extend(self,Orm);
	
	if (arguments[1]) {
		complete = arguments[1];
	} else {
		complete = function() {};
	}

	self.categories = function() {
		return self.loadType('Category','categories');
	}
	
	self.top = function() {
		var categories = self.categories();

		for (var x in categories) {

			var dishes = categories[x].dishes();

			for (var xx in dishes) {
				if (dishes[xx].top == 1) {
					return dishes[xx];
				}
			}
		}
	}
	
	self.meetDeliveryMin = function() {
		var total = self.delivery_min_amt == 'subtotal' ? App.cart.subtotal() : App.cart.total();
		return total < parseFloat(self.delivery_min) ? true : false;
	}
	
	self.dateFromItem = function(item, offset) {
		var
			theTime = item.split(':'),
			theDate = new Date();

		theDate.setHours(theTime[0]);
		theDate.setMinutes(theTime[1] + offset);
		return theDate;
	}
	
	self.open = function() {
		return this._open;
		var
			hours = self._hoursFormat,
			today = new Date(),
			offset = -(today.getTimezoneOffset()); // @todo: ensure this works on positive tz

		for (x in hours) {
			for (xx in hours[x]) {
				console.log(hours[x]);
				var
					open = self.dateFromItem(hours[x][xx][0], offset),
					close = self.dateFromItem(hours[x][xx][1], offset);
				if (today >= open && today <= close) {
					return true;
				}
			}
		}

		return false;
		/*
		var today = new DateTime('today', new DateTimeZone($this->timezone));
		var totay = new Date(Date.UTC(year, month, day, hour, minute, second))
		$day = strtolower($today->format('D'));

		foreach ($hours as $hour) {
			if ($hour->day != $day) {
				continue;
			}
			$open = new DateTime('today '.$hour->time_open, new DateTimeZone($this->timezone));
			$close = new DateTime('today '.$hour->time_close, new DateTimeZone($this->timezone));
			if ($today->getTimestamp() >= $open->getTimestamp() && $today->getTimestamp() <= $close->getTimestamp()) {
				return true;
			}
		}

		return false;
		*/
	}
	
	self.preset = function() {
		return self['_preset'];
	}

	self.finished = function(data) {
		for (x in data) {
			self[x] = data[x];
		}

		self.categories();

		if (complete) {
			complete.call(self);
		}

	}
	
	self.load(id);
}

App.cached.Restaurant = {};