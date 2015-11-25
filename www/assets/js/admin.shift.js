var shift = {};

shift.community = {};

shift.community.init = function(){

	$( '.chosen-select' ).select2();

	$( '#community-id' ).change( function( event ) {
		if( $.trim( $( '#community-id' ).val() ) != '' ){
			document.location.href = '/drivers/shift/community/' + $( '#community-id' ).val() + '/' + shift.community.start_date;
		}
	} );

	$( '.modal-hours-edit' ).click( function(e) {
		e.preventDefault();
		var url = $( this ).attr( 'href' );
		var title = $( this ).attr( 'title' );
		$.get( url, function( data ) {
			$( '#modal-hours' ).modal();
			$( '#modal-hours-title' ).html( title );
			$( '#modal-hours-body' ).html( data );
		} );
	} );

	$( '.modal-hours-open' ).click( function(e) {
		e.preventDefault();
		var url = $( this ).attr( 'href' );
		var title = $( this ).attr( 'title' );
		$.get( url, function( data ) {
			$( '#modal-hours' ).modal();
			$( '#modal-hours-title' ).html( title );
			$( '#modal-hours-body' ).html( data );
		} );
	} );

	shift.community.toggleTimezone();

}

shift.community.tz = 'pst';
shift.community.toggleTimezone = function(){
	$( '.pst-timezone' ).hide();
	$( '.community-timezone' ).hide();
	if( shift.community.tz == 'pst' ){
		shift.community.tz = 'community';
	} else {
		shift.community.tz = 'pst';
	}
	$( '.' + shift.community.tz + '-timezone' ).show();
};

shift.community.reload = function(){
	if( shift.community.ajax ){
		$( '#modal-hours' ).modal( 'hide' );
		community.shifts( shift.community.url );
	} else {
		location.reload();
	}
}

shift.community.copyAll = function( id_community, week, year ){
	if( confirm( 'Confirm copy? This will remove all the hours from this week ' + week + '/' +  year + '!' ) ){
		$.ajax( {
			url: '/api/drivers/shift/community/copy-all',
			method: 'POST',
			data: { 'year' : year, 'week' : week, 'id_community' : id_community },
			dataType: 'json',
		} ).done( function( data ) {
			if( data.success ){
				shift.community.reload();
			} else {
				alert( 'Oops, error! ' + data.error );
			}
		} );
	}
}

shift.community.remove = function(){
	var id_community_shift = $( '#form-id_community_shift' ).val();
	if( confirm( 'Confirm remove this shift?' ) ){
		$.ajax( {
			url: '/api/drivers/shift/community/remove',
			method: 'POST',
			data: { 'id_community_shift' : id_community_shift },
			dataType: 'json',
		} ).done( function( data ) {
			if( data.success ){
				shift.community.reload();
			} else {
				alert( 'Oops, error! ' + data.error );
			}
		} );
	}
};

shift.community.removeRecurringFather = function(){
	var id_community_shift = $( '#form-id_community_shift' ).val();
	if( confirm( 'Confirm remove it? It will remove the recurrence too.' ) ){
		$.ajax( {
			url: '/api/drivers/shift/community/remove',
			method: 'POST',
			data: { 'id_community_shift' : id_community_shift },
			dataType: 'json',
		} ).done( function( data ) {
			if( data.success ){
				shift.community.reload();
			} else {
				alert( 'Oops, error! ' + data.error );
			}
		} );
	}
};

shift.community.removeRecurring = function(){
	var id_community_shift = $( '#form-id_community_shift' ).val();
	if( confirm( 'Confirm remove it?' ) ){
		$.ajax( {
			url: '/api/drivers/shift/community/remove',
			method: 'POST',
			data: { 'id_community_shift' : id_community_shift, 'recurring' : 1 },
			dataType: 'json',
		} ).done( function( data ) {
			if( data.success ){
				shift.community.reload();
			} else {
				alert( 'Oops, error! ' + data.error );
			}
		} );
	}
};

shift.community.edit = function(){
	var id_community_shift = $( '#form-id_community_shift' ).val();
	var hours = $.trim( $( '#form-hours' ).val() );
	if( hours == '' ){
		alert( 'Please type the hours!' );
		$( '#form-hours' ).focus();
		return;
	}
	if( !shift.validate.segment( hours ) ){
		alert( 'Unable to figure out what this time means!' );
		$( '#form-hours' ).focus();
		return;
	}

	$.ajax( {
		url: '/api/drivers/shift/community/edit',
		method: 'POST',
		data: { 'id_community_shift' : id_community_shift, 'hours' : hours },
		dataType: 'json',
	} ).done( function( data ) {
		if( data.success ){
			shift.community.reload();
		} else {
			alert( 'Oops, error! ' + data.error );
		}
	} );
};

shift.community.add = function(){
	var id_community = $( '#form-id_community' ).val();
	var hours = $.trim( $( '#form-hours' ).val() );
	var recurring = ( $( '#form-recurring' ).is( ':checked' ) ) ? 1 : 0;

	if( hours == '' ){
		alert( 'Please type the hours!' );
		$( '#form-hours' ).focus();
		return;
	}
	if( !shift.validate.segment( hours ) ){
		alert( 'Unable to figure out what this time means!' );
		$( '#form-hours' ).focus();
		return;
	}
	var weekdays = [];
	$( '[name="form-weekdays"]' ).each( function(){
		var checkbox = $( this );
		if( checkbox.is( ':checked' ) ){
			weekdays.push( checkbox.val() );
		}
	} );
	$.ajax( {
		url: '/api/drivers/shift/community/add',
		method: 'POST',
		data: { 'id_community' : id_community, 'day' : $( '#form-day' ).val(), 'month' : $( '#form-month' ).val(), 'year' : $( '#form-year' ).val(), 'week' : $( '#form-week' ).val(), 'hours' : hours, 'weekdays' : weekdays, 'recurring' : recurring },
		dataType: 'json',
	} ).done( function( data ) {
		if( data.success ){
			shift.community.reload();
		} else {
			alert( 'Oops, error! ' + data.error );
		}
	} );
};


shift.validate = {};
shift.validate.segment = function( segment ){
	if( $.trim( segment ) == '' ){
		return true;
	}
	segment = segment.replace( /\(.*?\)/g, '' );
	if( /^ *(\d+)(?:\:(\d+))? *(am|pm) *(?:to|-) *(\d+)(?:\:(\d+))? *(am|pm) *$/i.exec( segment ) ) {
		return true;
	}
	return false;
}

shift.drivers = {};

shift.drivers.init = function(){

	$( '.chosen-select' ).select2();

	$( '.update-shift-driver' ).click( function() {
		shift.drivers.update( false );
	} );

	$( '.complete-shift-driver' ).click( function() {
		shift.drivers.update( true );
	} );

	$( '.available, .wantwork, .dontwantwork' ).sortable( {
		'connectWith': '.connected',
		'forcePlaceholderSize': true,
		'items': ':not(.locked)',
		'distance' : 0
		} ).bind( 'sortupdate', function() {
				shift.drivers.order();
				setTimeout( function(){
					shift.drivers.update();
				}, 500 );
			} );

	// sometimes the sortupdate event is not fired!
	shift.drivers.last_list = { wantWorkItems: [], dontWantWorkItems: [] };

	$('ul.wantwork li').each( function() {
		shift.drivers.last_list.wantWorkItems.push( $( this ).attr( 'id' ) );
	} );

	$('ul.dontwantwork li').each( function() {
		shift.drivers.last_list.dontWantWorkItems.push( $( this ).attr( 'id' ) );
	} );

	shift.drivers.watchChanges();
}

shift.drivers.watchChanges = function(){

	var wantWorkItems = [];
	var dontWantWorkItems = []

	$('ul.wantwork li').each( function() {
		wantWorkItems.push( $( this ).attr( 'id' ) );
	} );

	$('ul.dontwantwork li').each( function() {
		dontWantWorkItems.push( $( this ).attr( 'id' ) );
	} );

	if( !compareArrays( wantWorkItems, shift.drivers.last_list.wantWorkItems ) || !compareArrays( dontWantWorkItems, shift.drivers.last_list.dontWantWorkItems ) ){
		shift.drivers.update();
	}

	setTimeout( function(){
		shift.drivers.watchChanges()
	}, 1000 );
}

shift.drivers.order = function(){
	$( '.available .position, .wantwork .position, .dontwantwork .position' ).hide();
	var count = 1;
	$( '.wantwork .position' ).each( function( ){
		$( this ).html( count + ')&nbsp;' );
		$( this ).show();
		count++;
	} );
}

shift.drivers.update = function( completed ){
	var wantWorkItems = [];
	var dontWantWorkItems = [];
	var availableItems = [];
	var allItems = [];
	var hasAvailableItem = false;
	var shifts = $( '#shifts' ).val();
	$('ul.available li').each( function() {
		hasAvailableItem = true;
		availableItems.push( $( this ).attr( 'id' ) );
		allItems.push( $( this ).attr( 'id' ) );
	} );

	if( completed ){
		if( hasAvailableItem ){
			alert( 'Oops, you still have some available shifts to sort!' );
			return;
		}
		// See #3395
		// if( $.trim( shifts ) == '' ){
		// 	alert( 'Oops, you need to answer: "How many shifts would you like to work this week?" !' );
		// 	$( '#shifts' ).focus();
		// 	return;
		// }
	}
	if( !hasAvailableItem ){
		completed = true;
	}

	completed = ( completed ) ? 1 : 0;

	$('ul.wantwork li').each( function() {
		wantWorkItems.push( $( this ).attr( 'id' ) );
		allItems.push( $( this ).attr( 'id' ) );
	} );

	$('ul.dontwantwork li').each( function() {
		dontWantWorkItems.push( $( this ).attr( 'id' ) );
		allItems.push( $( this ).attr( 'id' ) );
	} );


	shift.drivers.last_list = { 'wantWorkItems' : wantWorkItems, 'dontWantWorkItems' : dontWantWorkItems };

	var year = shift.drivers.year;
	var week = shift.drivers.week;

	$.ajax( {
		url: '/api/drivers/shift/driver/',
		method: 'POST',
		data: { 'allItems' : allItems, 'dontWantWorkItems' : dontWantWorkItems, 'wantWorkItems' : wantWorkItems, 'availableItems' : availableItems, 'completed' : completed, 'shifts' : shifts, 'year' : year, 'week' : week },
		dataType: 'json',
	} ).done( function( data ) {
		if( data.success ){
			if( completed > 0 ){
				location.reload();
			} else {
				shift.drivers.order();
			}
		} else {
			alert( 'Oops, error! ' + data.error );
		}
	} );
}

shift.summary = { assign : {} };

shift.summary.init = function(){
	$( '.modal-shift-assign' ).click( function(e) {
		e.preventDefault();
		var url = $( this ).attr( 'href' );
		var title = $( this ).attr( 'title' );
		$.get( url, function( data ) {
			$( '#modal-shift' ).modal();
			$( '#modal-shift-title' ).html( title );
			$( '#modal-shift-body' ).html( data );
		} );
	} );

	$( '.hide-shift' ).click( function(e) {
		var checkbox = $(this);
		var id_community_shift = checkbox.attr( 'value' );
		if( checkbox.is(':checked') ){
			$( '#container_shift_' +  id_community_shift ).addClass( 'isHidden' );
		} else {
			$( '#container_shift_' +  id_community_shift ).removeClass( 'isHidden' );
		}

		$.ajax( {
			url: '/api/drivers/shift/hide-shift',
			method: 'POST',
			data: { 'id_community_shift' : id_community_shift, 'hide': ( checkbox.is(':checked') ? 1 : 0 ) },
			dataType: 'json',
		} ).done( function( data ) {
			if( data.success ){

			} else {
				alert( 'Oops, error! Please try again');
			}
		} );

	});

	shift.community.toggleTimezone();
}

shift.summary.assign.init = function(){
	$( '.icheck' ).iCheck( { checkboxClass: "icheckbox_flat-aero", radioClass: "iradio_flat-aero" } );
	$( '[name="form-id_admin_permanently"]' ).on( 'ifChanged', function( event, obj ){
		var checkbox = $( this );
		if( checkbox.is( ':checked' ) ){
			var id = checkbox.val();
			// $( '#form-id_admin-' + id ).iCheck( 'check' );
		}
	} );
	$( '[name="form-id_admin"]' ).on( 'ifChanged', function( event, obj ){
		var checkbox = $( this );
		if( !checkbox.is( ':checked' ) ){
			var id = checkbox.val();
			// $( '#form-id_admin_permanently-' + id ).iCheck( 'uncheck' );
		}
	} );
}

shift.summary.assign.save = function(){
	var id_community_shift = $( '#id_community_shift' ).val();
	var id_admin = [];
	var id_admin_permanently = [];
	$( '[name="form-id_admin"]' ).each( function(){
		var checkbox = $( this );
		if( checkbox.is( ':checked' ) ){
			id_admin.push( checkbox.val() );
		}
	} );
	$( '[name="form-id_admin_permanently"]' ).each( function(){
		var checkbox = $( this );
		if( checkbox.is( ':checked' ) ){
			id_admin_permanently.push( checkbox.val() );
		}
	} );

	$.ajax( {
		url: '/api/drivers/shift/driver/assign/',
		method: 'POST',
		data: { 'id_community_shift' : id_community_shift, 'id_admin' : id_admin, 'id_admin_permanently' : id_admin_permanently },
		dataType: 'json',
	} ).done( function( data ) {
		if( data.success ){
			location.reload();
		} else {
			alert( 'Oops, error! ' + data.error );
		}
	} );
}

shift.status = {};
shift.status.init = function(){
	$( '[name="form-receive-sms"]' ).on( 'ifChanged', function( event, obj ){
		var checkbox = $( this );
		var value = checkbox.is( ':checked' ) ? 1 : 0;
		var id_admin = checkbox.val();
		$.ajax( {
			url: '/api/drivers/shift/driver-schedule-sms-config/',
			method: 'POST',
			data: { 'value' : value, 'id_admin' : id_admin },
			dataType: 'json',
		} ).done( function( data ) {
			if( data.success ){
				$( '#saved-' + id_admin ).fadeTo( 'fast' , 1, function() {
					$( '#saved-' + id_admin ).fadeTo( 'fast', 0 );
				} );
			} else {
				alert( 'Oops, error! ' + data.error );
			}
		} );
	} );

	$( '.orders-per-hour' ).each( function(){
		var el = $( this );
		el.on( 'blur', function() {
			var el = $( this );
			var data = { 'id_admin': el.attr( 'id_admin' ), 'orders': el.val() };
			$( '#admin-orders-updating-' + el.attr( 'id_admin' ) ).show();
			$.ajax( {
				url: '/api/drivers/shift/driver-orders-per-hour/',
				method: 'POST',
				data: data,
				dataType: 'json',
			} ).done( function( data ) {
				$( '#admin-orders-updating-' + el.attr( 'id_admin' ) ).hide();
				if( data.success ){

				} else {
					alert( 'Oops, error! ' + data.error );
				}
			} );
		} );
	} );

	$( '.admin-note-text' ).each( function(){
		var el = $( this );
		var id_admin = $( this ).attr( 'id_admin' );
		var text = el.html().replace(/<br>/g, "\r");
		$( '#admin-note-' + id_admin ).val( text );
		$( '#admin-note-' + id_admin ).attr( 'placeholder', '' );
	} )

	$( '[name="form-note"]' ).on( 'blur', function(){
		var id_admin = $( this ).attr( 'id_admin' );
		shift.status.note( id_admin );
	} );

	$( '[name="form-note"]' ).autosize();

	$( '#show-completed' ).on( 'ifChanged', function( event, obj ){
		var checkbox = $( this );
		if( checkbox.is( ':checked' ) ){
			$( '.schedule-completed' ).show();
		} else {
			$( '.schedule-completed' ).hide();
		}
	} );

	$( '#show-not-completed' ).on( 'ifChanged', function( event, obj ){
		var checkbox = $( this );
		if( checkbox.is( ':checked' ) ){
			$( '.schedule-not-completed' ).show();
		} else {
			$( '.schedule-not-completed' ).hide();
		}
	} );
}
shift.status.note = function( id_admin ){
	var text = $( '#admin-note-' + id_admin ).val();
	$( '#admin-note-updated-' + id_admin ).hide();
	$( '#admin-note-updating-' + id_admin ).show();
	$.ajax( {
		url: '/api/drivers/shift/driver-note-update/',
		method: 'POST',
		data: { 'id_admin' : id_admin, 'text' : text },
		dataType: 'json',
	} ).done( function( data ) {
		$( '#admin-note-updated-' + id_admin ).show();
		$( '#admin-note-updating-' + id_admin ).hide();
		if( data.success ){
			$( '#note-updated-' + id_admin ).text( data.success.date );
			$( '#note-added_by-' + id_admin ).text( data.success.added_by );
		} else {
			alert( 'Oops, error! ' + data.error );
		}
	} );
}
