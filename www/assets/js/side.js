var Side = function(id) {
	this.type = 'Side';
	var self = this;
	
	if (typeof(id) == 'object') {
		for (x in id) {
			self[x] = id[x];
		}
	} else {
		App.request(App.service + '/side/' + id, function(json) {
			for (x in json) {
				self[x] = json[x];
			}
		});
	}
}

App.cached.Side = {};