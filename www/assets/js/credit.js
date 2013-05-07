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

App.credit.tooltip = {};

App.credit.tooltip.init = function(){

	$(document).on('touchclick', '.giftcard-badge', function() {
		App.credit.tooltip.show();
	});

	$(document).on('touchclick', 'body', function() {
		App.credit.tooltip.hide()
	});

}

App.credit.tooltip.message = function( message ){
	$( '.giftcard-badge-tooltip' ).html( message );
}

App.credit.tooltip.show = function(){
	if ( $( '.giftcard-badge-tooltip' ).is( ':visible' ) ) {
		return;
	}
	setTimeout(function() {
		$( '.giftcard-badge-tooltip' ).show();
	}, 100 );
}

App.credit.tooltip.hide = function(){
	$( '.giftcard-badge-tooltip' ).hide();
}

App.credit.hide = function(){
	$( '.gift-card-message' ).hide();
}

App.credit.show = function(){
	var gift = App.credit.restaurant[App.restaurant.id];

	if( parseFloat( gift ) ){
		var giftValue = App.ceil( gift ).toFixed( 2 );
		var text = 'You have a '+ ( ( App.config.ab && App.config.ab.dollarSign == 'show') ? '$' : '' ) + giftValue + ' gift card!';
		App.credit.tooltip.message( text );		
		$( '.gift-card-message' ).html( text );
		$( '.gift-card-message' ).css( 'opacity', 0 );
		$( '.gift-card-message' ).show();
		$( '.gift-card-message' ).animate( { 'opacity' : 1 }, 100 );

		var integerGiftValue = parseInt( giftValue );
		if( integerGiftValue < giftValue ){
			giftValue = integerGiftValue + '+';	
		} else {
			giftValue = integerGiftValue;
		}
		$( '.giftcard-badge-value' ).html( '$' + giftValue );
		$( '.giftcard-badge' ).css( 'display', 'table' );
	} else {
		$( '.gift-card-message' ).hide();
		$( '.giftcard-badge' ).css( 'display', 'none' );
	}
	
}