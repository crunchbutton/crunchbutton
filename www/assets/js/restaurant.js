var Restaurant = function(id) {
	this.type = 'Restaurant';
	var self = this;

	self.dishes = function() {
		return self.loadType('Dish','dishes');
	}
	
	self.extras = function() {
		return self.loadType('Extra','extras');
	}
	
	self.sides = function() {
		return self.loadType('Side','sides');
	}
	
	self.top = function() {
		var dishes = self.dishes();
		for (x in dishes) {
			if (dishes[x].top) {
				return dishes[x];
			}
		}
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
		self.sides();
		self.extras();
	} else {
		App.request(App.service + '/restaurant/' + id, function(json) {
			for (x in json) {
				self[x] = json[x];
			}
			self.dishes();
			self.sides();
			self.extras();
		});
	}
}

App.cached.Restaurant = {};