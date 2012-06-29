var Dish = function(id) {
	this.type = 'Dish';
	var self = this;

	self.toppings = function() {
		return self.loadType('Topping','toppings');
	}
	
	self.substitutions = function() {
		return self.loadType('Substitution','substitutions');
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
		self.toppings();
		self.substitutions();
	} else {
		App.request(App.service + '/dish/' + id, function(json) {
			for (x in json) {
				self[x] = json[x];
			}
			self.toppings();
			self.substitutions();
		});
	}
}

App.cached.Dish = {};