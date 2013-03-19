App.recommend = {
	api : {
		add : 'suggestion/restaurant',
		relateuser : 'suggestion/relateuser'
	},
	itIsSending : false,
	recommendations : false
}

App.recommend.init = function(){
	$( document ).on( 'click', '.home-recommend-button', function() {
		App.recommend.send();
	} );	

	$( document ).on( 'keyup', '.home-recommend-text', function( e ) {
		if (e.which == 13) {
			App.recommend.send();
		}
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

	var pos = App.loc.pos();

	var content = 'Address entered: ' + pos.addressEntered + 
								'\n' + 
								'Address reverse: ' + pos.addressReverse +
								'\n' + 
								'City: ' + pos.city +
								'\n' + 
								'Region: ' + pos.region + 
								'\n' + 
								'Lat: ' + pos.lat + 
								'\n' + 
								'Lon: ' + pos.lon;
	var data = {
		name: $( '.home-recommend-text' ).val(),
		content : content
	};

	if (!App.recommend.itIsSending){
		App.recommend.showThankYou();	
		App.recommend.itIsSending = true;
		$.ajax({
			type: 'POST',
			dataType: 'json',
			data: data,
			url:  App.service + App.recommend.api.add,
			success: function( json ) {
				App.recommend.itIsSending = false;
				if( !App.recommend.recommendations ){
					App.recommend.recommendations = [];
				}
				App.recommend.recommendations.push( json.id_suggestion );
			}
		});
	}
}

App.recommend.relateUser = function(){
	if( App.recommend.recommendations ){
		var url = App.service + App.recommend.api.relateuser;
		$.each( App.recommend.recommendations, function(index, value) {
			var id_suggestion = value;
			var data = { id_suggestion : id_suggestion, id_user : App.config.user.id_user };
			$.ajax({
				type: 'POST',
				dataType: 'json',
				data: data,
				url:  url,
				success: function( json ) {}
			});
		} );
		App.recommend.recommendations = false;
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
