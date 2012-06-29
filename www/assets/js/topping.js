var Topping = function(id) {
	this.type = 'Topping';
	var self = this;
	
	if (typeof(id) == 'object') {
		for (x in id) {
			self[x] = id[x];
		}
	} else {
		App.request(App.service + '/topping/' + id, function(json) {
			for (x in json) {
				self[x] = json[x];
			}
		});
	}
}

App.cached.Topping = {};