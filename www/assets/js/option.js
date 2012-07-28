var Option = function(id) {
	this.type = 'Option';
	var self = this;

	if (typeof(id) == 'object') {
		for (x in id) {
			self[x] = id[x];
		}
	} else {
		App.request(App.service + '/option/' + id, function(json) {
			for (x in json) {
				self[x] = json[x];
			}
		});
	}
}

App.cached.Option = {};