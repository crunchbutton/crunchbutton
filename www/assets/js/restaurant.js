/**
 * Restaurant ORM
 *
 * @todo Use JS syntax to outline the structure
 */
var Restaurant = function(id) {
	this.type = 'Restaurant';
	this.id_var = 'id_restaurant';
	this.resource = 'restaurant';
	var
		self = this,
		complete;

	$.extend(self,Orm);

	if (arguments[1]) {
		complete = arguments[1];
	} else {
		complete = function() {};
	}

	self.categories = function() {
		return self.loadType('Category','categories');
	}

	self.top = function() {
		var categories = self.categories();

		for (var x in categories) {

			var dishes = categories[x].dishes();

			for (var xx in dishes) {
				if (dishes[xx].top == 1) {
					return dishes[xx];
				}
			}
		}
	}

	self.deliveryDiff = function() {
		var total = self.delivery_min_amt == 'subtotal' ? App.cart.subtotal() : App.cart.total();
		var diff = parseFloat(App.restaurant.delivery_min - total).toFixed(2);
		/* console.log(App.cart.subtotal(), App.cart.total(),self.delivery_min_amt, total, diff); */
		return diff;
	}

	self.meetDeliveryMin = function() {
		var total = self.delivery_min_amt == 'subtotal' ? App.cart.subtotal() : App.cart.total();
		return total < parseFloat(self.delivery_min) ? true : false;
	}

	self.dateFromItem = function(item, offset) {
		var
			theTime = item.split(':'),
			theDate = new Date();

		theDate.setHours(theTime[0]);
		theDate.setMinutes(theTime[1] + offset);
		return theDate;
	}

	/**
	 * Checks if a restaurant is open now for delivery
	 *
	 * Checks if now() is between openTime and closeTime. The closing time could
	 * be for tomorrow morning (1am) so we need to handle those cases too. See
	 * issue #605.
	 *
	 * Do not use logic in PHP as the page could have been open for a while.
	 *
	 * @todo add offset validation
	 *       offset = -(today.getTimezoneOffset()); // @todo: ensure this works on positive tz
	 */
	self.open = function() {
		var isOpen =  false;
		var today = Date.today().toString('ddd').toLowerCase();
		if (this._hours == undefined ||  this._hours[today] == undefined) {
			return false;
		}
		todayHours  = this._hours[today];
		for (i in todayHours) {
			var openTime  = Date.parse(todayHours[i][0]);
			var closeTime = Date.parse(todayHours[i][1]);
			// there is no real 24:00 hours, it's 00:00 for tomorrow
			if (todayHours[i][1] == '24:00') {
				closeTime = Date.parse('00:00');
				closeTime.addDays(1);
			}
			// if closeTime before openTime, then closeTime should be for tomorrow
			if (closeTime.compareTo(openTime) == -1) {
				closeTime.addDays(1);
			}
			if (Date.now().between(openTime, closeTime)) {
				isOpen = true;
				break;
			}
		}
		return isOpen;
	}

	self.preset = function() {
		return self['_preset'];
	}

	self.finished = function(data) {
		for (x in data) {
			self[x] = data[x];
		}

		self.categories();

		if (complete) {
			complete.call(self);
		}

	}

	self.load(id);
}

App.cached.Restaurant = {};