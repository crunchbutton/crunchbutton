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

	self.tagfy = function( tag ){
		if( tag ){
			this._tag = tag;
			return;
		}

		this._tag = '';
		// Add the tags
		if( !this._open ){
			this._tag = 'closed';
		}
		if( this._open && this._closesIn !== 'false' && this._closesIn <= 15 ){
			this._tag = 'closing';
		}
	}

	/* 
	** Open/Close check methods 
	*/
	// return true if the restaurant is open
	self.open = function( now, ignoreOpensClosesInCalc ) {	
		var now = ( now ) ? now : dateTime.getNow();
		self.processHours();
		var now_time = now.getTime();
		// loop to verify if it is open	
		self._open = false;
		for( x in this.hours ){
			if( this.hours[ x ].status == 'open' ){
				if( now_time >= this.hours[ x ]._from_time && now_time <= this.hours[ x ]._to_time ){
					self._open = true;
					if( ignoreOpensClosesInCalc ){
						return self._open;
					}
					// if it is open calc closes in
					self.closesIn( now );	
					return self._open;
				}
			}
		}
		if( ignoreOpensClosesInCalc ){
			return self._open;
		}
		// If it is closed calc opens in
		self.opensIn( now );
		return self._open;
	}

	self.closesIn = function( now ){
		var now = ( now ) ? now : dateTime.getNow();
		self._closesIn = false;
		self._closesIn_formatted = '';
		self.processHours();
		var now_time = now.getTime();
		for( x in this.hours ){
			if( this.hours[ x ].status == 'close' ){
				if( now_time <= this.hours[ x ]._from_time ){
					self._closesIn = timestampDiff( this.hours[ x ]._from_time, now_time );
					self._closesIn_formatted = formatTime( self._closesIn );
					return;
				}
			}
		}
	}

	self.opensIn = function( now ){
		var now = ( now ) ? now : dateTime.getNow();
		self._opensIn = false;
		self._opensIn_formatted = '';
		self.processHours();
		var now_time = now.getTime();
		for( x in this.hours ){
			if( this.hours[ x ].status == 'open' ){
				if( now_time <= this.hours[ x ]._from_time ){
					self._opensIn = timestampDiff( this.hours[ x ]._from_time, now_time );
					self._opensIn_formatted = formatTime( self._opensIn );
					return;
				}
			}
		}
	}

	// Create javascript date objects to faster comparision
	self.processHours = function(){
		if( !this._hours_processed ){
			for( x in this.hours ){
				this.hours[ x ]._from = Date.parse( this.hours[ x ].from );
				this.hours[ x ]._from_time = this.hours[ x ]._from.getTime();
				this.hours[ x ]._to = Date.parse( this.hours[ x ].to );
				this.hours[ x ]._to_time = this.hours[ x ]._to.getTime();
			}	
			this._hours_processed = true;
		}
	}

	self.load(id);

}

App.cached.Restaurant = {};
