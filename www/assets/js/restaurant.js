var Restaurant = function(id) {
	this.type = 'Restaurant';
	var
		self = this,
		complete;
	
	if (arguments[1]) {
		complete = arguments[1];
	} else {
		complete = function() {};
	}

	self.dishes = function() {
		return self.loadType('Dish','dishes');
	}
	
	self.extras = function() {
		return self.loadType('Extra','extras');
	}
	
	self.sides = function() {
		return self.loadType('Side','sides');
	}
	
	self.top = function() {
		var dishes = self.dishes();
		for (x in dishes) {
			if (dishes[x].top) {
				return dishes[x];
			}
		}
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
		var
			hours = self._hours,
			today = new Date(),
			offset = -(today.getTimezoneOffset()); // @todo: ensure this works on positive tz

		for (x in hours) {
			console.log(hours);
			var
				open = self.dateFromItem(hours[x][0], offset),
				close = self.dateFromItem(hours[x][1], offset);
			if (today >= open && today <= close) {
				return true;
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
	
	self.defaultOrder = function() {
		if (!self['__defaultOrder']) {
			try {
				self['__defaultOrder'] = JSON.parse(self['_defaultOrder']);
			} catch (e) {
				self['__defaultOrder'] = null;
			}
			self['_defaultOrder'] = null;
		}
		return self['__defaultOrder'];

	}

	self.loadType = function(cls, data) {
		if (!self['__' + data]) {
			self['__' + data] = [];
			for (x in self['_' + data]) {
				self['__' + data][self['__' + data].length] = App.cache(cls, self['_' + data][x]);
			}
			self['_' + data] = null;
		}
		return self['__' + data];
	}
	
	self.finished = function(data) {
		for (x in data) {
			self[x] = data[x];
		}
		self.dishes();
		self.sides();
		self.extras();

		if (complete) {
			complete.call(self);
		}
	}
	
	if (typeof(id) == 'object') {
		self.finished(id);
	} else {
		App.request(App.service + '/restaurant/' + id, function(json) {
			self.finished(json);
		});
	}
}

App.cached.Restaurant = {};