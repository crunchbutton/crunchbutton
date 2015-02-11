var Dish = function(id) {
	this.type = 'Dish';
	this.id_var = 'id_dish';
	this.resource = 'dish';
	var self = this;
	
	$.extend(self,Orm);

	self.options = function() {
		return self.loadType('Option','options');
	}
	
	self.finished = function(data) {
		for (x in data) {
			self[x] = data[x];
		}
		self.top = parseInt(self.top);
		self.options();
	}
	
	self.load(id);
}

App.cached.Dish = {};