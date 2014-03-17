var shift = {};

shift.community = {};

shift.community.init = function(){

	$( '.chosen-select' ).select2();

	$( '#community-id' ).change( function( event ) {
		if( $.trim( $( '#community-id' ).val() ) != '' ){
			document.location.href = '/drivers/shift/community/' + $( '#community-id' ).val();	
		}		
	} );

	$( '.save-shift-community' ).on( 'click', function(){
		var isOk = true;
		// at first validate all fields
		$( '.hours' ).each( function(){
			if( !isOk ){ return; }
			var segment = $( this ).val();
			if( !shift.validate.segment( segment ) ){
				alert( 'Unable to figure out what this time means.' );
				$( this ).focus();
				isOk = false;
				return;
			}
		} );

		var count = 0;

		// if it is ok - post
		if( isOk ){
			var id_community = $( '#id_community' ).val();
			var url = App.service + 'drivers/shift/community';
			$( '.hours' ).each( function(){
				var day = $( this ).attr( 'day' );
				var segment = $( this ).val();
				var id_community = $( '#id_community' ).val();
				$.ajax( {
					type: 'POST',
					dataType: 'json',
					data: { 'id_community' : id_community, 'day' : day, 'segment' : segment },
					url: url,
					success: function( json ) {
						count++;
						if( count == 7 ){
							alert( 'Shift saved!' );
							document.location.reload();
						}
					},
					error: function( json ) {}
				} );
			} );
		}

	} );
}

shift.validate = {};
shift.validate.segment = function( segment ){
	if( $.trim( segment ) == '' ){
		return true;
	}
	segment = segment.replace( /\(.*?\)/g, '' );
	segments = segment.split( /(?:and|,)/ );
	for( i in segments ) {
		if( /^ *(\d+)(?:\:(\d+))? *(am|pm) *(?:to|-) *(\d+)(?:\:(\d+))? *(am|pm) *$/i.exec( segments[ i ] ) ) {
			return true;
		}
	}
	return false;
}



