var shift = {};

shift.community = {};

shift.community.init = function(){

	$( '.chosen-select' ).select2();

	$( '#community-id' ).change( function( event ) {
		if( $.trim( $( '#community-id' ).val() ) != '' ){
			document.location.href = '/drivers/shift/community/' + $( '#community-id' ).val();	
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

shift.community.copyAll = function( id_community, week, year ){
	if( confirm( 'Confirm copy? This will remove all the hours from this week ' + week + '/' +  year + '!' ) ){
		$.ajax( {
			url: '/api/drivers/shift/community/copy-all',
			method: 'POST',
			data: { 'year' : year, 'week' : week, 'id_community' : id_community },
			dataType: 'json',
		} ).done( function( data ) {
			if( data.success ){
				location.reload();
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
				location.reload();
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
			location.reload();
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
			location.reload();
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