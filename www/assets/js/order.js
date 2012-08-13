var Order = function(id) {
	this.type = 'Order';
	this.id_var = 'id_order';
	this.resource = 'order';
	var self = this;
	
	if (arguments[1]) {
		complete = arguments[1];
	} else {
		complete = function() {};
	}

	self.finished = function(data) {
		for (x in data) {
			self[x] = data[x];
		}

		if (complete) {
			complete.call(self);
		}
	}
	
	self.load(id);
}

App.cached.Order = {};