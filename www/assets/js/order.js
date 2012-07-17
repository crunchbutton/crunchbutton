var Order = function(id) {
	this.type = 'Order';
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
	
	if (typeof(id) == 'object') {
		self.finished(id);
	} else {
		App.request(App.service + 'order/' + id, function(json) {
			self.finished(json);
		});
	}
}

App.cached.Order = {};