var Dish = function(id) {
	this.type = 'Dish';
	this.id_var = 'id_dish';
	this.resource = 'dish';
	var self = this;

	self.options = function() {
		return self.loadType('Option','options');
	}
	
	self.finished = function(data) {
		for (x in data) {
			self[x] = data[x];
		}
		self.options();
	}
	
	self.load(id);
}

App.cached.Dish = {};