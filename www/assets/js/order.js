var Order = function(id) {
	this.type = 'Order';
	this.id_var = 'id_order';
	this.resource = 'order';
	var self = this;
	
	$.extend(self,Orm);
	
	var complete = arguments[1] || null;
	self.loadError = arguments[2] || null;

	self.finished = function(data) {
		for (x in data) {
			self[x] = data[x];
		}

		if (typeof complete === 'function') {
			complete.call(self);
		}
	}
	
	self.load(id);
}

App.cached.Order = {};