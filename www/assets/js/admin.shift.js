var shift = {};

shift.community = {};

shift.community.init = function(){

	$( '.chosen-select' ).select2();

	$( '#community-id' ).change( function( event ) {
		if( $.trim( $( '#community-id' ).val() ) != '' ){
			document.location.href = '/drivers/shift/community/' + $( '#community-id' ).val() + '/' + shift.community.year + '/' + shift.community.week;	
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
	console.log('shift.community.ajax',shift.community.ajax);
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
				alert( 'Ops, error! ' + data.error );
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
				alert( 'Ops, error! ' + data.error );
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
			alert( 'Ops, error! ' + data.error );
		}
	} );
};

shift.community.add = function(){
	var id_community = $( '#form-id_community' ).val();
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
		data: { 'id_community' : id_community, 'day' : $( '#form-day' ).val(), 'month' : $( '#form-month' ).val(), 'year' : $( '#form-year' ).val(), 'week' : $( '#form-week' ).val(), 'hours' : hours, 'weekdays' : weekdays },
		dataType: 'json',
	} ).done( function( data ) {
		if( data.success ){
			shift.community.reload();
		} else {
			alert( 'Ops, error! ' + data.error );
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

	$( '.sort-area' ).click( function() {
		$( 'html, body' ).animate( { scrollTop: 0 } );
		return false;
	} );

	$( '.chosen-select' ).select2();

	$( '.update-shift-driver' ).click( function() {
		shift.drivers.update( false );
	} );

	$( '.complete-shift-driver' ).click( function() {
		shift.drivers.update( true );
	} );
	

	$('.available, .wantwork, .dontwantwork').sortable( { 'connectWith': '.connected', 'distance' : 0, 'stop' : function(){
		$( '.available .position, .wantwork .position, .dontwantwork .position' ).hide();
		var count = 1;
		$( '.wantwork .position' ).each( function( ){
			$( this ).html( count + ')&nbsp;' );
			$( this ).show();
			count++;
		} );
	} } );

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
			alert( 'Ops, it seems you still have available items!' );
			return;
		}
		if( $.trim( shifts ) == '' ){
			alert( 'Ops, you need to answer: "How many shifts would you like to work this week?" !' );
			$( '#shifts' ).focus();
			return;
		}
	}
	if( parseInt( shifts ) > 0 && !hasAvailableItem ){
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
	$.ajax( {
		url: '/api/drivers/shift/driver/',
		method: 'POST',
		data: { 'allItems' : allItems, 'dontWantWorkItems' : dontWantWorkItems, 'wantWorkItems' : wantWorkItems, 'availableItems' : availableItems, 'completed' : completed, 'shifts' : shifts },
		dataType: 'json',
	} ).done( function( data ) {
		if( data.success ){
			location.reload();
		} else {
			alert( 'Ops, error! ' + data.error );
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
	shift.community.toggleTimezone();
}

shift.summary.assign.init = function(){	
	$( '.icheck' ).iCheck( { checkboxClass: "icheckbox_flat-aero", radioClass: "iradio_flat-aero" } );
}

shift.summary.assign.save = function(){
	var id_community_shift = $( '#id_community_shift' ).val();
	var id_admin = [];
	$( '[name="form-id_admin"]' ).each( function(){
		var checkbox = $( this );
		if( checkbox.is( ':checked' ) ){
			id_admin.push( checkbox.val() );	
		}
	} );
	$.ajax( {
		url: '/api/drivers/shift/driver/assign/',
		method: 'POST',
		data: { 'id_community_shift' : id_community_shift, 'id_admin' : id_admin },
		dataType: 'json',
	} ).done( function( data ) {
		if( data.success ){
			location.reload();
		} else {
			alert( 'Ops, error! ' + data.error );
		}
	} );
}
