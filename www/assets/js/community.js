var Community = function(id) {
	this.type = 'Community';
	var self = this;

	self.restaurants = function() {
		if (!self.__restaurants) {
			self.__restaurants = [];
			for (x in self._restaurants) {
				self.__restaurants[self.__restaurants.length] = App.cache('Restaurant', self._restaurants[x]);
			}
			self._restaurants = null;
		}
		return self.__restaurants;
	}
	
	if (typeof(id) == 'object') {
		for (x in id) {
			self[x] = id[x];
		}
	} else {
		App.request(App.service + 'community/' + id, function(json) {
			for (x in json) {
				self[x] = json[x];
			}
			self.restaurants();
			App.itemLoaded(self.type);
		});
	}
}

App.cached.Community = {};