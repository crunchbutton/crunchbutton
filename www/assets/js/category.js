var Category = function(id) {
	this.type = 'Dish';
	var self = this;
	
	$.extend(self,Orm);

	self.dishes = function() {
		return self.loadType('Dish','dishes');
	}

	self.loadType = function(cls, data) {
		if (!self['__' + data]) {
			self['__' + data] = [];
			for (x in self['_' + data]) {
				self['__' + data][self['__' + data].length] = App.cache(cls, self['_' + data][x]);
			}
			self['_' + data] = null;
		}
		return self['__' + data];
	}

	if (typeof(id) == 'object') {
		for (x in id) {
			self[x] = id[x];
		}
		self.dishes();
	} else {
		App.request(App.service + '/category/' + id, function(json) {
			for (x in json) {
				self[x] = json[x];
			}
			self.options();
		});
	}
}

App.cached.Category = {};