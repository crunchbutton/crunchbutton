var Substitution = function(id) {
	this.type = 'Substitution';
	var self = this;

	if (typeof(id) == 'object') {
		for (x in id) {
			self[x] = id[x];
		}
	} else {
		App.request(App.service + '/substitution/' + id, function(json) {
			for (x in json) {
				self[x] = json[x];
			}
		});
	}
}

App.cached.Substitution = {};