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
		var utcTime = Date.parse( serverTime.add( - this._tzoffset ).hours().toUTCString() );
		return utcTime;
	}

	/**
	 * Turn's client browser time to UTC to compare it with a base and not client's side timezone
	 *
	 * @return Date
	 */
	this._utcNow = function()
	{
		return Date.parse( dateTime.toString() );
	}

	/**
	 * Returns the minutes if it's about to close
	 *
	 * @return int|boolean
	 */
	this.isAboutToClose = function() { return false; }
		/**
		 * How many minutes to closing time to trigger the notification
		 *
		 * @var int
		 */
  /*
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
  */

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
			this._open = true;
			return true;
		}

		var isOpen =  false;
		var today = Date.today().toString('ddd').toLowerCase();
		if (this._hours == undefined ||  this._hours[today] == undefined) {
			this._open = false;
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
			// var now_utc = Date.parse( Date.now().add( (new Date).getTimezoneOffset() / 60 ).hours().toUTCString() );
			var now_utc = this._utcNow();

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
		this._open = isOpen;
		return isOpen;
	}

	self.deliveryHere = function( param ){
		var km = App.loc.distance( { from : { lat : param.lat, lon : param.lon }, to : { lat : this.loc_lat, lon : this.loc_long } } );
		var miles = App.loc.km2Miles( km );
		return ( miles <= this.delivery_radius )
	}

	self.closedMessage = function(){
		var isOpenedAt = {};
		var openTime = this._hours;
		var weekdayOrder = [ 'Sun','Mon','Tue','Wed','Thu','Fri','Sat' ];
		var patternHour = /([0])([1-9])(:)([0-9]{2})/;
		// Format the hour and group the day with the same hour
		for ( var day in openTime ) {
			var hours = openTime[ day ];
			var key = '';
			var openedHoursText = '';
			var openedHoursTextDivisor = '';
			for( var hour in hours ){
				 var open = hours[ hour ][ 0 ];
				 var close = hours[ hour ][ 1 ];
				 key += open + close;
				 var formatedOpen = App.formatTime( open ).toLowerCase().replace( patternHour, '\$2\$3\$4' ).replace( /:00/g, '' ).replace( ' ', '' );
				 var formatedClose = App.formatTime( close ).toLowerCase().replace( patternHour, '\$2\$3\$4' ).replace( /:00/g, '' ).replace( ' ', '' );
				 openedHoursText += openedHoursTextDivisor + formatedOpen + ' - ' + formatedClose;
				 openedHoursTextDivisor = ', ';
			}
			// Remove the ':' to create a cleaner object key
			key = key.replace( /\:/g, '' );
			if( !isOpenedAt[ key ] ){
				isOpenedAt[ key ] = { days : {}, hours : {} };
			}
			// Hours "12 am, 12 am" doesn't need to be shown
			openedHoursText = openedHoursText.replace( '- 12am, 12am ', '' );
			isOpenedAt[ key ][ 'days' ][ day ] = true;
			isOpenedAt[ key ][ 'hour' ] = openedHoursText;
		}
		// Create an object with all days and its hours
		var _opened = {};
		for( var hour in isOpenedAt ){
			var days = isOpenedAt[ hour ][ 'days' ];
			var commas = '';
			var keys = '';
			for( day in days ){
				keys += commas + App.capitalize( day );
				commas = ', ';
			}
			hours = isOpenedAt[hour][ 'hour' ];
			_opened[ keys ] = hours;
		}
		// Group the days e.g. 'Mon, Tue, Wed, Sat' will became 'Mon - Wed, Sat'
		var _groupedDays = {};
		for( var days in _opened ){
			var weekdays = days.split( ',' );
			var nextPosition = -1;
			var sequenceStartedAt = false;
			var groupedDays = {};
			var totalWeekdays = weekdays.length;
			for ( var i = 0; i <= totalWeekdays; ++i) {
				var weekday = $.trim( weekdays[ i ] );
				var position = weekdayOrder.indexOf( weekday );
				if( nextPosition != position ){
					sequenceStartedAt = weekday;
					groupedDays[sequenceStartedAt] = [];
					groupedDays[sequenceStartedAt] = [weekday];
				}
				if( nextPosition == position ){
					groupedDays[sequenceStartedAt].push( weekday );
				}
				nextPosition = position + 1;
			}
			var key = '';
			for( var group in groupedDays ){
				if( group != '' ){
					if( groupedDays[group].length == 1 ){
						if( key != '' ){ key += ', '; }
						key += groupedDays[group][0];
					}
					if( groupedDays[group].length == 2 ){
						if( key != '' ){ key += ', '; }
						key += groupedDays[group][0] + ', ' + groupedDays[group][1];
					}
					if( groupedDays[group].length > 2 ){
						if( key != '' ){ key += ', '; }
						key += groupedDays[group][0] + ' - ' + groupedDays[group][groupedDays[group].length-1];
					}		
				}
			}
			key += ': ';
			_groupedDays[ key ] = _opened[days];
		}
		// Sort the hours according to the weekdayOrder sequence Sun to Sat
		var ordered = [];
		for ( var index = 0; index < weekdayOrder.length; ++index) {
			var weekday = weekdayOrder[ index ];
			for( var day in _groupedDays ){
				var regexp = new RegExp( '^' + weekday, 'i' )
				if( regexp.test( day ) ){
					ordered.push( day + _groupedDays[ day ] );
				}
			}
		}
		return ordered.join( '\n' );
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
