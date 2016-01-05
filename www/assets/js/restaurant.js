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
	self.loadError = function() {
		console.debug('Restaurant does not exist');
		App.go('/location');
	};

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

	self.deliveryHere = function( distance ){
		return ( distance <= this.delivery_radius )
	}

	self.closedMessage = function(){
		if( self.closed_message != '' ){
			return self.closed_message;
		} else {
			if( self._closedDueTo != '' ){
				return self._closedDueTo;
			}
			return 'Temporarily Unavailable';
		}

	}

	self.preset = function() {
		return self['_preset'];
	}

	self.finished = function(data) {
		for (x in data) {
			self[x] = data[x];
		}
		if (App.minimalMode) {
			self.img = null;
			self.image = null;
			self.img64 = null;
		}

		self.categories();

		if (App.isPhoneGap) {
			// this shouldnt be used anymore, but old apps will still be pulling from this url
			// self.img = App.imgServer + '596x596/' + img +  '.jpg';
		}

		if (typeof complete == 'function') {
			complete.call(self);
		}

	}

	self.tagfy = function( tag, force ){

		// driver's restaurant should be always open
		if( self.driver_restaurant ){
			self._tag = '';
			return;
		}

		if( tag ){
			self._tag = tag;
			if( tag == 'opening' ){
				if( ( self._opensIn && self._opensIn_formatted != '' ) || force ){
					self._tag = tag;
					self._nextOpenTag();
				} else {
					self._tag = 'closed';
					if( !self.hours || self.hours.length == 0 ){
						self._tag = 'force_close';
					}
					self._nextOpenTag();
				}
			} else {
				self._tag = tag;
			}
			return;
		}

		self._tag = '';
		// Add the tags
		if( !self._open ){
			self._tag = 'closed';
			self._nextOpenTag();
			if( self._closedDueTo ){
				self._tag = 'force_close';
				self._nextOpenTag();
			}
		}
		if( self._open && self._closesIn !== 'false' && self._closesIn !== false && self._closesIn <= 15 ){
			self._tag = 'closing';
		}
		// if the restaurant does not have a closed message it has no hours for the week
		if( self.closed_message == '' ){
			self._tag = 'force_close';
			self._nextOpenTag();
		}
		// if it has not tag, check if it is takeout only
		if( self._tag == '' ){
			if( self.takeout == '1' && self.delivery != '1' ){
				self._tag = 'takeout';
			}
		}
	}

	self._nextOpenTag = function(){
		if( self.next_open_time_message && self.next_open_time_message.message && self._opensIn_formatted == '' ){
			self._tag = 'next_open';
		}
	}

	self.getNow = function( now ){
		if( now && now.getTime ){
			return now;
		}
		return dateTime.getNow();
	}

	self.openRestaurantPage = function( now ){
		// See 2662
		now = self.getNow( now );
		if( self.open( now, true ) ){
			return true;
		}
		self.opensIn( now );
		if( self._opensIn && self._opensIn < ( 3600 ) ){
			return true;
		}
		return false;
	}

	/*
	** Open/Close check methods
	*/
	// return true if the restaurant is open
	self.open = function( now, ignoreOpensClosesInCalc ) {

		if( !ignoreOpensClosesInCalc ){
			self.tagfy( 'opening', true );
		}

		// if the restaurant has no hours it probably will not be opened for the next 24 hours
		self._hasHours = false;

		now = self.getNow( now );

		self.processHours();
		var now_time = now.getTime();
		// loop to verify if it is open
		self._open = false;
		if( self.hours && self.hours.length > 0 ){
			for( x in self.hours ){
				self._hasHours = true;
				if( now_time >= self.hours[ x ]._from_time && now_time <= self.hours[ x ]._to_time ){
					if( self.hours[ x ].status == 'open' ){
						self._open = true;
						if( ignoreOpensClosesInCalc ){
							return self._open;
						}
						// if it is open calc closes in
						self.closesIn( now );
						self.tagfy();
						return self._open;
					} else if( self.hours[ x ].status == 'close' ){
						self._closedDueTo = ( self.hours[ x ].notes ) ? self.hours[ x ].notes : false;
						if( ignoreOpensClosesInCalc ){
							return self._open;
						}
						// If it is closed calc opens in
						self.opensIn( now );
						self.tagfy();
						return self._open;
					}
				}
			}
			// If it is closed calc opens in
			self.opensIn( now );
			self.tagfy();
			if( !self._hasHours ){
				self._closedDueTo = ( self._community_closed ? self._community_closed : ' ' ); // There is no reason, leave it blank
			}
			return self._open;
		} else {
			// if it doesn't have hours it is forced to be closed
			self._tag = 'force_close';
			self._nextOpenTag();
			self._closedDueTo = ( self._community_closed ? self._community_closed : ' ' ); // There is no reason, leave it blank
		}
	}

	self.closesIn = function( now ){
		now = self.getNow( now );
		self._closesIn = false;
		self._closesIn_formatted = '';
		self.processHours();
		var now_time = now.getTime();
		if( self.hours ){
			for( x in self.hours ){
				if( self.hours[ x ].status == 'close' ){
					if( now_time <= self.hours[ x ]._from_time ){
						self._closesIn = timestampDiff( self.hours[ x ]._from_time, now_time );
						self._closesIn_formatted = formatTime( self._closesIn );
						return;
					}
				}
			}
		}
		if( self._closesIn == 0 || self._closesIn === false ){
			self._open = false;
		}
	}

	self.opensIn = function( now ){
		now = self.getNow( now );
		self._opensIn = false;
		self._opensIn_formatted = '';
		self.processHours();
		var now_time = now.getTime();
		for( x in self.hours ){
			if( self.hours[ x ].status == 'open' ){
				if( now_time <= self.hours[ x ]._from_time ){
					self._opensIn = timestampDiff( self.hours[ x ]._from_time, now_time );
					if( self._opensIn <= 60 * 60 ){
						self._opensIn_formatted = formatTime( self._opensIn );
						return;
					}
				}
			}
		}
		// it means the restaurant will not be opened for the next 24 hours
		if( self.next_open_time ){
			self._opensIn = timestampDiff( Date.parse( self.next_open_time ), now_time );
			if( self._opensIn <= 60 * 60 ){
				self._opensIn_formatted = formatTime( self._opensIn, self.next_open_time_message );
			}
		}
	}

	// Create javascript date objects to faster comparision
	self.processHours = function(){
		if( !self._hours_processed ){
			for( x in self.hours ){
				self.hours[ x ]._from = Date.parse( self.hours[ x ].from );
				self.hours[ x ]._from_time = self.hours[ x ]._from.getTime();
				self.hours[ x ]._to = Date.parse( self.hours[ x ].to );
				self.hours[ x ]._to_time = self.hours[ x ]._to.getTime();
			}
			self._hours_processed = true;
		}
	}

	self.isActive = function( callback ){
		var url = App.service + 'restaurant/active/' + self.id_restaurant;
		App.http.get( url, {
			cache: false
		} ).success( function ( status ) {
			if( callback ){
				callback( status.active );
			};
		} ).error( function(){
			callback( false );
		} );
	}

	// Check the restaurant cache age and reload the hours if it is necessary
	self.reloadHours = function( forceLoad, callback ){
		var load = false;
		var now = ( Math.floor( new Date().getTime() / 1000 ) );
		if( forceLoad ){
			load = true;
		} else {
			var age = Math.floor( now - self.cachedAt ); // age in seconds
			load = ( age >= 60 );
		}
		if( load ){
			if( self.loadingHours ){
				return;
			}
			self.loadingHours = true;
			var url = App.service + 'restaurant/hours/' + self.id_restaurant + '/gmt';
			App.http.get( url, {
				cache: false
			} ).success( function ( hours ) {
				var gmt = hours.gmt;
				var hours = hours.hours;
				if( gmt ){
					dateTime.updateGMT( gmt );
				}
				self.loadingHours = false;
				if( hours && hours.constructor === Array ){
					self.cachedAt = now;
					self.hours = hours;
					self._hours_processed = false;
					self.processHours();
					if( callback ){
						if( typeof callback === 'function' ){
							callback( self );
						}
					}
				}
			} ).error( function(){ self.loadingHours = false; } );
		}
	}

	self.load(id);

}

App.cached.Restaurant = {};
