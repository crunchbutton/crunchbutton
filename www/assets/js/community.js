var Community = function(id) {
	this.type = 'Community';
	var self = this;
	
	if (arguments[1]) {
		complete = arguments[1];
	} else {
		complete = function() {};
	}

	self.restaurants = function() {
		if (!self.__restaurants) {
			self.__restaurants = [];
			for (x in self._restaurants) {
				self.__restaurants[self.__restaurants.length] = App.cache('Restaurant', self._restaurants[x]);
			}
			self._restaurants = null;
			self.__restaurants.sort(function(a, b) {
				return (b._open ? 1 : 0) - (a._open ? 1 : 0);
			});
		}
		return self.__restaurants;
	}
	
	self.finished = function(data) {
		for (x in data) {
			self[x] = data[x];
		}
		self.restaurants();

		if (complete) {
			complete.call(self);
		}
	}
	
	if (typeof(id) == 'object') {
		self.finished(id);
	} else {
		App.request(App.service + 'community/' + id, function(json) {
			self.finished(json);
		});
	}
}

App.cached.Community = {};