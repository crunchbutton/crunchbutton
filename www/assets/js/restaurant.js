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

	/**
	 * Turns serverside time to UTC so we don't use the server time
	 *
	 * @returns Date
	 */
	this._utcTime = function(serverTime)
	{
		var utcTime = Date.parse( serverTime.add( - this._tzoffset                   ).hours().toUTCString() );
		return utcTime;
	}

	/**
	 * Turn's client browser time to UTC to compare it with a base and not client's side timezone
	 *
	 * @return Date
	 */
	this._utcNow = function()
	{
		var utcNow = Date.parse( Date.now().add( (new Date).getTimezoneOffset() / 60 ).hours().toUTCString() );
		return utcNow;
	}

	/**
	 * Returns the minutes if it's about to close
	 *
	 * @return int|boolean
	 */
	this.isAboutToClose = function() {
		/**
		 * How many minutes to closing time to trigger the notification
		 *
		 * @var int
		 */
		var minimumTime = 15;
		var today       = Date.today().toString('ddd').toLowerCase();
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

			closeTime = this._utcTime(closeTime);
			openTime  = closeTime.clone().addMinutes(-1 * minimumTime);
			utcNow    = this._utcNow();

			if (utcNow.between(openTime, closeTime)) {
				var minutes = (closeTime.getTime() - utcNow.getTime()) /1000/60;
				minutes = Math.floor(minutes);
				return minutes;
			}
		}
		return false;
	}

	self.categories = function() {
		return self.loadType('Category','categories');
	}

	self.notifications = function() {
		return self.loadType('Notification','notifications');
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

		// If it doesn't have hours it means it is always opened
		if( !this._hours ){
			return true;
		}

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

			// Convert the open hour to UTC just to compare, based on _tzoffset (TimZone OffSet)
			if( openTime ){
				var openTime_utc = Date.parse( openTime.add( - this._tzoffset ).hours().toUTCString() );
			}
			// Convert the close hour to UTC just to compare, based on _tzoffset (TimZone OffSet)
			if( closeTime ){
				var closeTime_utc = Date.parse( closeTime.add( - this._tzoffset ).hours().toUTCString() );
			}
			// Convert current user date to UTC.
			var now_utc = Date.parse( Date.now().add( (new Date).getTimezoneOffset() / 60 ).hours().toUTCString() );

			// if closeTime before openTime, then closeTime should be for tomorrow
			if( closeTime_utc ){
				if (closeTime_utc.compareTo(openTime_utc) == -1) {
					closeTime_utc.addDays(1);
				}
			}
			if( openTime_utc &&  closeTime_utc ){
				if (now_utc.between(openTime_utc, closeTime_utc)) {
					isOpen = true;
					break;
				}
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