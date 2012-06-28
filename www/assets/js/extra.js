var Extra = function(id) {
	this.type = 'Extra';
	var self = this;
	
	if (typeof(id) == 'object') {
		for (x in id) {
			self[x] = id[x];
		}
	} else {
		App.request(App.service + '/extra/' + id, function(json) {
			for (x in json) {
				self[x] = json[x];
			}
		});
	}
}

App.cached.Extra = {};