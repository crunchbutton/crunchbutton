NGApp.factory( 'DriverShiftsService', function( $rootScope, $resource ) {

	var service = {};

	// Create a private resource 'shifts'
	var shifts = $resource( App.service + 'driver/shifts', {}, {}	);

	service.list = function( callback ){
		shifts.query( {}, function( data ){
			callback( data ); } );
	}

	service.groupByDay = function( data, callback ){
		var groups = {};
		var order = 0;

		for ( var i = 0; i < data.length ; i++ ) {
			var day = data[ i ].date.day;
			var segment = data[ i ].date.start_end;
			if( !groups[ day ] ){
				groups[ day ] = { 'day' : day, 'order' : order, 'drivers' : [] };
				order++;
			}
			if( data[ i ].drivers && data[ i ].drivers.length ){
				for( var j = 0; j < data[ i ].drivers.length; j++ ){
					groups[ day ][ 'drivers' ].push( { 'hour': segment, 'id' : data[ i ].drivers[j].id, 'name' : data[ i ].drivers[j].name, 'phone' : data[ i ].drivers[j].phone } );
					if( data[ i ].drivers[j].id == $rootScope.account.user.id_admin ){
						groups[ day ][ 'mine' ] = true;
					}
				}
			} else {
				groups[ day ][ 'drivers' ].push( { 'hour': segment } );
			}
		}
		var sorted = [];
		angular.forEach( groups, function( group ){
			sorted[ group.order ] = group;
		} );
		callback( sorted );
	}

	return service;
} );


NGApp.factory( 'DriverShiftScheduleService', function( $rootScope, $resource ) {

	var service = {};

	var schedules = $resource( App.service + 'driver/shifts/schedule', {}, {
		'list' : { 'method': 'GET', params : {} },
		'dontWantToWork' : { 'method': 'POST', params : {} },
		'wantToWork' : { 'method': 'POST', params : {} },
		'rankingChange' : { 'method': 'POST', params : {} },
		'shiftsAvailableToWork' : { 'method': 'POST', params : {} },
		'save' : { 'method': 'POST', params : {} },
	}	);

	service.list = function( callback ){
		schedules.list( {}, function( data ){
			callback( data ); } );
	};

	service.shiftsAvailableToWork = function( shifts, callback ){
		schedules.rankingChange( { 'shifts' : shifts, action: 'shiftsAvailableToWork' }, function( json ){
				callback( json );
			} );
	};

	service.save = function( shifts, callback ){
		schedules.rankingChange( { 'shifts' : shifts, action: 'save' }, function( json ){
				callback( json );
			} );
	};

	service.rankingChange = function( id_community_shift, id_community_shift_change, callback ){
		schedules.rankingChange( { 'id_community_shift' : id_community_shift, 'id_community_shift_change' : id_community_shift_change, action: 'rankingChange' }, function( json ){
				callback( json );
			} );
	};

	service.dontWantToWork = function( id_community_shift, callback ){
		schedules.dontWantToWork( { 'id_community_shift' : id_community_shift, action: 'dontWantToWork' }, function( json ){
			callback( json );
		} );
	};

	service.wantToWork = function( id_community_shift, ranking, callback ){
		schedules.wantToWork( { 'id_community_shift' : id_community_shift, 'ranking' : ranking,  action: 'wantToWork' }, function( json ){
			callback( json );
		} );
	};

	return service;
} );