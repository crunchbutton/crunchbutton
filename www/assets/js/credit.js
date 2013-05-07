App.credit = {
	api : {
		byRestaurant : 'user/credit/'
	},
	restaurant : {}
}

App.credit.getCredit = function( complete ){
	if( App.restaurant.id ){
		var url = App.service + App.credit.api.byRestaurant + App.restaurant.id;
		$.getJSON( url, function( json ) {
			if( json.credit ){
				App.credit.restaurant[App.restaurant.id] = json.credit;
			} else {
				App.credit.restaurant[App.restaurant.id] = 0;
			}
			if( complete ){
				complete();
			}
		} );	
	}
}

App.credit.hide = function(){
	$( '.gift-card-message' ).hide();
}

App.credit.show = function(){
	var gift = App.credit.restaurant[App.restaurant.id];

	if( parseFloat( gift ) ){
		var text = 'You have a '+ ( ( App.config.ab && App.config.ab.dollarSign == 'show') ? '$' : '' ) + App.ceil( gift ).toFixed( 2 ) + ' gift card!';
		$( '.gift-card-message' ).html( text );
		$( '.gift-card-message' ).css( 'opacity', 0 );
		$( '.gift-card-message' ).show();
		$( '.gift-card-message' ).animate( { 'opacity' : 1 }, 100 );
	} else {
		$( '.gift-card-message' ).hide();
	}
	
}