var Restaurant = function(id) {
	this.type = 'Restaurant';
	var self = this;

	self.dishes = function() {
		if (!self.__dishes) {
			self.__dishes = [];
			for (x in self._dishes) {
				self.__dishes[self.__dishes.length] = App.cache('Dish', self._dishes[x]);
			}
			self._dishes = null;
		}
		return self.__dishes;
	}
	
	if (typeof(id) == 'object') {
		for (x in id) {
			self[x] = id[x];
		}
	} else {
		App.request(App.service + '/restaurant/' + id, function(json) {
			for (x in json) {
				self[x] = json[x];
			}
		});
	}
}

App.cached.Restaurant = {};