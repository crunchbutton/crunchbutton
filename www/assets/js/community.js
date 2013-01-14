var Community = function(id) {
	this.type = 'Community';
	this.id_var = 'id_community';
	this.resource = 'community';
	var self = this;

	$.extend(self,Orm);

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

			/**
			 * Bubble sort mechanism
			 *
			 * if a < b, returns negative
			 * if a = b, returns zero
			 * if a > b returns positive
			 *
			 * @return int
			 */
			self.__restaurants.sort(function(a, b) {
				var bubble; // = (b._open ? 1 : 0) - (a._open ? 1 : 0);
				console.log(a);
				if ((a.open() && b.open()) || (!a.open() && !b.open())) {
					bubble = parseInt(a.sort) - parseInt(b.sort);
				} else if (a.open() && !b.open()) {
					bubble = -1;
				} else if (!a.open() && b.open()) {
					bubble = 1;
				} else {
					console.log('Should not be here', a, b);
				}
				return bubble;
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

	self.load(id);
}

App.cached.Community = {};