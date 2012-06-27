var Dish = function(id) {
	this.type = 'Dish';
	var self = this;

	self.toppings = function() {
		if (!self.__toppings) {
			self.__toppings = [];
			for (x in self._toppings) {
				self.__toppings[self.__toppings.length] = App.cache('Toppings', self._toppings[x]);
			}
			self._toppings = null;
		}
		return self.__toppings;
	}
	
	if (typeof(id) == 'object') {
		for (x in id) {
			self[x] = id[x];
		}
	} else {
		App.request(App.service + '/dish/' + id, function(json) {
			for (x in json) {
				self[x] = json[x];
			}
		});
	}
}

App.cached.Dish = {};