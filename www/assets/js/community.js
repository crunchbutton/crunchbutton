/**
 * Bubble sort mechanism
 *
 *  if both restaurants are open or both are closed, use sort value.
 *
 * if a < b, returns negative
 * if a = b, returns zero
 * if a > b returns positive
 *
 * @return int
 */
function restaurantSort(a, b) {
	var bubble;
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
}


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


			var openRestaurants   = [];
			var closedRestaurants = [];
			for (x in self._restaurants) {
				// self.__restaurants[self.__restaurants.length] = App.cache('Restaurant', self._restaurants[x]);
				var restaurant = App.cache('Restaurant', self._restaurants[x]);
				if (restaurant.open()) {
					openRestaurants[openRestaurants.length] = restaurant;
				} else {
					closedRestaurants[closedRestaurants.length] = restaurant;
				}
				// self.__restaurants[self.__restaurants.length] = restaurant;
			}

			openRestaurants.sort(restaurantSort);
			closedRestaurants.sort(restaurantSort);

			self.__restaurants = [];
			self._restaurants = null;

			for (x in openRestaurants) {
				self.__restaurants[self.__restaurants.length] = openRestaurants[x];
			}
			for (x in closedRestaurants) {
				self.__restaurants[self.__restaurants.length] = closedRestaurants[x];
			}
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