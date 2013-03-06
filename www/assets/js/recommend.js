App.recommend = {
	api : {
		add : 'suggestion/restaurant'
	},
	itIsSending : false
}

App.recommend.init = function(){
	$( document ).on( 'click', '.home-recommend-button', function() {
		App.recommend.send();
	} );	
	$( document ).on( 'click', '.home-recommend-message-create-account', function(){
		App.signup.show( false );
	} );
}

App.recommend.send = function(){
	if ( $.trim( $( '.home-recommend-text' ).val() ) == '' ){
		alert( "Please enter the restaurant\'s name." );
		$( '.home-recommend-text' ).focus();
		return;
	}

	var content = 'Geocode city: ' + App.loc.reverseGeocodeCity + 
								'\n' + 
								'City name: ' + App.loc.city_name +
								'\n' + 
								'Lat: ' + App.loc.lat + 
								'\n' + 
								'Lon: ' + App.loc.lon;
	var data = {
		name: $( '.home-recommend-text' ).val(),
		content : content
	};

	if (!App.recommend.itIsSending){
		App.recommend.itIsSending = true;
		$.ajax({
			type: 'POST',
			dataType: 'json',
			data: data,
			url:  App.service + App.recommend.api.add,
			success: function(content) {
				App.recommend.itIsSending = false;
				App.recommend.showThankYou();	
			}
		});
	}
}

App.recommend.showThankYou = function(){
	$( '.home-recommend-form' ).animate( { 'opacity' : 0 }, function(){
		$( '.home-recommend-form' ).hide();
		$( '.home-recommend-thank-you' ).css( 'opacity', 0 );
		$( '.home-recommend-thank-you' ).show();
		$( '.home-recommend-thank-you' ).animate( { 'opacity' : 1 } );	
	} );
	
}
