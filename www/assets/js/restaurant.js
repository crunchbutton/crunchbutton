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

	var complete = arguments[1] || null;
	self.loadError = arguments[2] || null;

	/**
	 * Turns serverside time to UTC so we don't use the server time
	 *
	 * @returns Date
	 */
	this._utcTime = function(serverTime)
	{
		return this._parseDate(serverTime.add( - this._tzoffset ).hours().toUTCString());
	}
	
	this._parseDate = function(dateStr) {
		if (!dateStr) {
			return null;
		}

		dateStr = dateStr.split(',');
		dateStr = (dateStr.length == 2 ? dateStr[1] : dateStr[0]).trim();
		return Date.parse(dateStr);
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


	this.closesIn = function( trueIfItIsOpen ) {

		// this overrides everything
		if(this.open_for_business === "0") {
			this._open = false;
			return false;
		}

		// If it doesn't have hours it is never open
		if(!this._hours) {
			this._open = false;
			return false;
		}

		var today = Date.today().toString('ddd').toLowerCase();

		if (this._hours == undefined ||  this._hours[today] == undefined) {
			this._open = false;
			return false;
		}
 
		// Check the yesterday time too because the restaurant could be opened from yesterday 11:30 AM to today 3:45 AM (Like Mirano - See #1707)
		var yesterday = Date.parse( 'yesterday' ).toString('ddd').toLowerCase();
		if( this._hours[yesterday] != undefined ){
			yesterdayHours = this._hours[yesterday];
			
			for (i in yesterdayHours) {
				// Ignores if it throws an error
				try{

					var openTime  = this._parseDate(yesterdayHours[i][0]);
					var closeTime = this._parseDate(yesterdayHours[i][1]);

					if (yesterdayHours[i][1] == '24:00') {
						closeTime = this._parseDate('00:00');
						if( closeTime ){
							closeTime.addDays(1);	
						}
					}

					// Convert the open hour to UTC just to compare, based on _tzoffset (TimZone OffSet)
					if (openTime) {
						// Change the time to one day before (yesterday)
						openTime.addDays( -1 );
						var openTime_utc = this._parseDate(openTime.add( - this._tzoffset ).hours().toUTCString());
					}

					// Convert the close hour to UTC just to compare, based on _tzoffset (TimZone OffSet)
					if (closeTime) {
						// Change the time to one day before (yesterday)
						closeTime.addDays( -1 );
						var closeTime_utc = this._parseDate(closeTime.add( - this._tzoffset ).hours().toUTCString());
					}

					var now_utc = this._utcNow();

					if( openTime_utc && closeTime_utc ){
						if( now_utc.between( openTime_utc, closeTime_utc ) ) {
							this._open = true;
							break;
						}
					}
				} catch(e){};
			}
		}

		var todayHours = this._hours[today];

		for (i in todayHours) {

			var openTime  = this._parseDate(todayHours[i][0]);
			var closeTime = this._parseDate(todayHours[i][1]);

			// there is no real 24:00 hours, it's 00:00 for tomorrow
			if (todayHours[i][1] == '24:00') {
				closeTime = this._parseDate('00:00');
				if( closeTime ){
					closeTime.addDays(1);	
				}
			}

			// Convert the open hour to UTC just to compare, based on _tzoffset (TimZone OffSet)
			if (openTime) {
				var openTime_utc = this._parseDate(openTime.add( - this._tzoffset ).hours().toUTCString());
			}
			// Convert the close hour to UTC just to compare, based on _tzoffset (TimZone OffSet)
			if (closeTime) {
				var closeTime_utc = this._parseDate(closeTime.add( - this._tzoffset ).hours().toUTCString());
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

			if( openTime_utc && closeTime_utc ){
				if ( now_utc.between( openTime_utc, closeTime_utc ) ) {
					this._open = true;
				}
			}
		}

		if( trueIfItIsOpen ){
			return true;
		}

		var today = Date.today().toString('ddd').toLowerCase();
		var tomorrow = Date.today().add(1).days().toString('ddd').toLowerCase();

		todayHours  = this._hours[today];

		// Check the time it will open and close tomorrow
		if( this._hours[ tomorrow ] ){
			var tomorrowHours = this._hours[tomorrow];
			var tomorrowItOpensAt = tomorrowHours[0][0];
			var tomorrowItClosesAt = tomorrowHours[0][1];
		} else {
			var tomorrowItOpensAt = false;
			var tomorrowItClosesAt = false;
		}

		for (i in todayHours) {
			var openTime  = Date.parse(todayHours[i][0]);
			var closeTime = Date.parse(todayHours[i][1]);
			
			var nextHour = parseInt( i ) + 1;
			if( todayHours[ nextHour ] ){
				var nextHourOpenTime = todayHours[ nextHour ][0];
				var nextHourCloseTime = todayHours[ nextHour ][1];
			} else {
				var nextHourOpenTime = false;
				var nextHourCloseTime = false;
			}

			var previousHour = parseInt( i ) - 1;
			if( todayHours[ previousHour ] ){
				var previousHourOpenTime = todayHours[ previousHour ][0];
				var previousHourCloseTime = todayHours[ previousHour ][1];
			} else {
				var previousHourOpenTime = false;
				var previousHourCloseTime = false;
			}

			// there is no real 24:00 hours, it's 00:00 for tomorrow
			if (todayHours[i][1] == '24:00' || todayHours[i][1] == '00:00') {
				closeTime = Date.parse('00:00');
				// if it opens tomorrow at 00:00 it means it will no close today at 00:00
				if( tomorrowItOpensAt == '00:00' || tomorrowItOpensAt == '0:00' ){
					closeTime = Date.parse( tomorrowItClosesAt );
				} 
				// else if the next hour starts at 00:00 it means it will no close today at 00:00
				else if( nextHourOpenTime == '00:00' || nextHourOpenTime == '0:00' ){
					closeTime = Date.parse( nextHourCloseTime );
				}
				// else if the previous hour starts at 00:00 it means it will no close today at 00:00
				else if( previousHourOpenTime == '00:00' || previousHourOpenTime == '0:00' ){
					closeTime = Date.parse( previousHourCloseTime );
				} 
				closeTime.addDays(1);
			}

			// if closeTime before openTime, then closeTime should be for tomorrow
			if (closeTime.compareTo(openTime) == -1) {
				closeTime.addDays(1);
			}

			closeTime = this._utcTime(closeTime);
			utcNow    = this._utcNow();

			var minutes = (closeTime.getTime() - utcNow.getTime()) /1000/60;
			minutes = Math.floor(minutes);
			if( minutes <= 15 ){
				this._closesIn = minutes;	
			}
			if( this._closesIn == 0 ){
				this._open = false;
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

	self.deliveryDiff = function(total) {
		var diff = parseFloat(self.delivery_min - total).toFixed(2);
		return diff;
	}

	self.meetDeliveryMin = function(total) {
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
		return this.closesIn( true );
	}

	self.deliveryHere = function( distance ){
		return ( distance <= this.delivery_radius )
	}

	self.closedMessage = function(){
		var hours = this._hours;
		var formated = [];

		// Convert to hours starting at monday
		var weekdays = [ 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun' ];
		var hoursStartFinish = [];
		for( var day in hours ) {
			var dayhours = weekdays.indexOf( day ) * 2400;
			for( var i in hours[ day ] ) {
				var _open = /(\d+):(\d+)/.exec( hours[ day ][ i ][0] );
				var _close = /(\d+):(\d+)/.exec( hours[ day ][ i ][1] );
				var open = ( dayhours + parseInt( _open[ 1 ], 10 ) * 100 + parseInt( _open[ 2 ], 10 ) );
				var close = ( dayhours + parseInt( _close[ 1 ], 10 ) * 100 + parseInt( _close[ 2 ], 10 ) );
				hoursStartFinish.push( { 'open' : open, 'close' : close } );
			}
		}

		// Sort
		hoursStartFinish.sort( function( a, b ) { return ( a.open < b.open ? -1 : ( a.open > b.open ? 1 : 0 ) ); } );

		// Merge
		for( var i = 0; i < hoursStartFinish.length-1; i++) {
			if( hoursStartFinish[ i + 1 ].open <= hoursStartFinish[ i ].close && hoursStartFinish[ i + 1 ].close - hoursStartFinish[ i ].open < 3600 ) {
				hoursStartFinish[ i ].close = hoursStartFinish[ i + 1 ].close;
				hoursStartFinish.splice(i+1,1);
				i--;
				continue;
			}
		}

		// Format
		for( var i in hoursStartFinish ) {
			segment = hoursStartFinish[ i ];
			var weekday = weekdays[ Math.floor( segment.open / 2400 ) ];
			while( segment.open >= 2400 ) {
				segment.open -= 2400;
				segment.close -= 2400;
			}
			
			format_time = function( time ) {
				var h = Math.floor( time / 100 );
				var m = time - ( 100 * h );
				var hour_formated = '';
				var mintute_formated = '';
				var ampm = '';
				switch( true ){
					case ( h === 0 ):
						hour_formated = '' + ( h + 12 ); ampm = 'am'; 
						break;
					case ( h === 12 ):
						ampm = 'pm';
						break;
					case ( h === 24 ):
						hour_formated = '12'; ampm = 'am'; 
						break;
					case ( h < 12 ):
						ampm = 'am';
						break;
					case ( h < 24 ):
						hour_formated = '' + ( h - 12 ); ampm = 'pm';
						break;
					default:
						hour_formated = '' + ( h - 24 ); ampm = 'am'; 
						break;
				}
				if( m ) { 
					mintute_formated = ':' + App.pad( m, 2 ); 
				}
				return '' + ( hour_formated || h ) + mintute_formated + ampm;
			};
			segment.formated = format_time( segment.open ) + ' - ' + format_time( segment.close );
			if( !formated[ weekday ] ){
				formated[ weekday ] = { formated : '' };
			}
			formated[ weekday ].formated += segment.formated + ', ';
		}

		// Merge days with same open/close hours
		var isOpenedAt = {};
		var sorted = [];
		for( var i in weekdays ){
			if( formated[ weekdays[ i ] ] ){
				var time = $.trim( formated[ weekdays[ i ] ].formated );
				time = time.substring( 0, time.length - 1 );
				var day = weekdays[ i ];
				var key = time.replace( /[\ \: \- ,]/g, '' );
				if( !isOpenedAt[ key ] ){
					isOpenedAt[ key ] = { days : {}, hours : {} };
				}
				isOpenedAt[ key ][ 'days' ][ day ] = day;
				isOpenedAt[ key ][ 'hour' ] = time;
			}
		}

		// Group the days e.g. 'Mon, Tue, Wed, Sat' will became 'Mon - Wed, Sat'
		var _groupedDays = {};
		for( var i in isOpenedAt ){
			var segment = isOpenedAt[ i ];
			var time = segment.hour;
			var days = [];
			for( var j in segment.days ){
				days.push( segment.days[ j ] );
			}
			var nextPosition = -1;
			var sequenceStartedAt = false;
			var groupedDays = {};
			var totalWeekdays = days.length;
			for ( var i = 0; i <= totalWeekdays; ++i ) {
				var weekday = $.trim( days[ i ] );
				var position = weekdays.indexOf( weekday );
				if( nextPosition != position ){
					sequenceStartedAt = weekday;
					groupedDays[ sequenceStartedAt ] = [];
					groupedDays[ sequenceStartedAt ] = [ weekday ];
				}
				if( nextPosition == position ){
					groupedDays[ sequenceStartedAt ].push( weekday );
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
			_groupedDays[ key ] = time;
		}

		var formated = [];
		for( var i in _groupedDays ){
			var sequence = i.split( ' ' );
			for( var j in sequence ){
				if( $.trim( sequence[ j ] ) != '' ){
					sequence[ j ] = sequence[ j ].charAt( 0 ).toUpperCase() + sequence[ j ].slice( 1 );	
				}
			}
			var days = sequence.join( ' ' ) + _groupedDays[i];
			formated.push( days );
		}
		return formated.join( '<br/>' );
		return formated.join( '<br/>' );
	}

	self.preset = function() {
		return self['_preset'];
	}

	self.finished = function(data) {
		for (x in data) {
			self[x] = data[x];
		}

		self.categories();
		
		if (App.isPhoneGap) {
			var img = self.image.replace(/^.*\/|\.[^.]*$/g, '');
			// restaurant page image
			self.img = App.imgServer + '630x280/' + img +  '.jpg?crop=1';
			// restaurants page thumbnail
			self.img64 = App.imgServer + '596x596/' + img +  '.jpg';
		}

		if (typeof complete == 'function') {
			complete.call(self);
		}

	}

	self.load(id);

}

App.cached.Restaurant = {};
