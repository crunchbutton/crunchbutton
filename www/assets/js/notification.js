var Notification = function(id) {
	// this.type = 'Dish';  //why?
	var self = this;

	$.extend(this, Orm);

	// shouldn't this be part of the ORM?
	this.loadType = function(cls, data) {
		if (!self['__' + data]) {
			self['__' + data] = [];
			for (x in self['_' + data]) {
				self['__' + data][self['__' + data].length] = App.cache(cls, self['_' + data][x]);
			}
			self['_' + data] = null;
		}
		return self['__' + data];
	}

	// move this to a private method?
	if (typeof(id) == 'object') {
		for (x in id) {
			self[x] = id[x];
		}
		// self.dishes(); // huh?
	} else {
		App.request(App.service + '/notification/' + id, function(json) {
			for (x in json) {
				self[x] = json[x];
			}
			self.options();
		});
	}
}

// huh?
App.cached.Notification = {};