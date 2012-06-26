var Community = function(id) {
	this.type = 'Community';
	var self = this;

	self.restaurants = function(refresh) {
		if (!self._restaurants || refresh) {
			self._restaurants = [];
			App.request(App.service + '/project/' + self.id_project + '/deliveries',function(json) {
				for (x in json) {
					self._deliveries[self._deliveries.length] = App.cache('Delivery', json[x]);
				}
			});
		}
		return self._deliveries;
	}
	
	if (typeof(id) == 'object') {
		for (x in id) {
			self[x] = id[x];
		}
	} else {
		App.request(App.service + '/community/' + id, function(json) {
			for (x in json) {
				self[x] = json[x];
			}
		});
	}
}

App.cached.Project = {};