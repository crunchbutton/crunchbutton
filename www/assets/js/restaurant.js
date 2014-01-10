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
		return self['closed_message'];
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

	self.tagfy = function( tag ){
		if( tag ){
			self._tag = tag;
			return;
		}

		self._tag = '';
		// Add the tags
		if( !self._open ){
			self._tag = 'closed';
			if( self.name,self._closedDueTo ){
				self._tag = 'force_close';
			}
		}
		if( self._open && self._closesIn !== 'false' && self._closesIn <= 15 ){
			self._tag = 'closing';
		}
		if( !self._hasHours ){
			self._tag = 'force_close';	
		}
	}

	/* 
	** Open/Close check methods 
	*/
	// return true if the restaurant is open
	self.open = function( now, ignoreOpensClosesInCalc ) {
		// if the restaurant has no hours it probably will not be opened for the next 24 hours
		self._hasHours = false;
		var now = ( now ) ? now : dateTime.getNow();
		self.processHours();
		var now_time = now.getTime();
		// loop to verify if it is open	
		self._open = false;
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
		return self._open;
	}

	self.closesIn = function( now ){
		var now = ( now ) ? now : dateTime.getNow();
		self._closesIn = false;
		self._closesIn_formatted = '';
		self.processHours();
		var now_time = now.getTime();
		for( x in self.hours ){
			if( self.hours[ x ].status == 'close' ){
				if( now_time <= self.hours[ x ]._from_time ){
					self._closesIn = timestampDiff( self.hours[ x ]._from_time, now_time );
					self._closesIn_formatted = formatTime( self._closesIn );
					return;
				}
			}
		}
		if( self._closesIn == 0 || self._closesIn === false ){
			self._open = false;
		}
	}

	self.opensIn = function( now ){
		var now = ( now ) ? now : dateTime.getNow();
		self._opensIn = false;
		self._opensIn_formatted = '';
		self.processHours();
		var now_time = now.getTime();
		for( x in self.hours ){
			if( self.hours[ x ].status == 'open' ){
				if( now_time <= self.hours[ x ]._from_time ){
					self._opensIn = timestampDiff( self.hours[ x ]._from_time, now_time );
					self._opensIn_formatted = formatTime( self._opensIn );
					return;
				}
			}
		}
	}

	// Create javascript date objects to faster comparision
	self.processHours = function(){
		if( !this._hours_processed ){
			for( x in self.hours ){
				self.hours[ x ]._from = Date.parse( self.hours[ x ].from );
				self.hours[ x ]._from_time = self.hours[ x ]._from.getTime();
				self.hours[ x ]._to = Date.parse( self.hours[ x ].to );
				self.hours[ x ]._to_time = self.hours[ x ]._to.getTime();
			}	
			this._hours_processed = true;
		}
	}

	self.load(id);

}

App.cached.Restaurant = {};
