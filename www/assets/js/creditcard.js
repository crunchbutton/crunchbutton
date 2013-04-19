App.creditCard = {
	cards : [
		{ 
			'name' : 'master-card',
			'pattern' : /^5[1-5]/,
			'size' : 16
		},
		{ 
			'name' : 'visa',
			'pattern' : /^4/,
			'size' : 16
		},
		{ 
			'name' : 'amex',
			'pattern' : /^3[47]/,
			'size' : 15
		},
		{ 
			'name' : 'discover',
			'pattern' : /^(6011|622(12[6-9]|1[3-9][0-9]|[2-8][0-9]{2}|9[0-1][0-9]|92[0-5]|64[4-9])|65)/,
			'size' : 16
		},
	]
}

App.creditCard.validate = function( cardValue ){
	var card = App.creditCard.detectType( cardValue );
	var isValid = false;
	console.log( card.size, cardValue.toString().length, card.length == cardValue.toString().length )
	if( card.size == cardValue.toString().length ){
		isValid = App.creditCard.luhn( cardValue );
	}
	if( isValid ){
		$( '[name="pay-card-number"]' ).addClass( 'credit-card-ok' );
	} else {
		$( '[name="pay-card-number"]' ).removeClass( 'credit-card-ok' );
	}
}

App.creditCard.luhn = function( cardValue ){
	var sum = 0;
	cardValueInverse = cardValue.toString().split( '' ).reverse();
	for (n = i = 0, size = cardValueInverse.length; i < size; n = ++i) {
		digit = cardValueInverse[ n ];
		digit = +digit;
		if (n % 2) {
			digit *= 2;
			if (digit < 10) {
				sum += digit;
			} else {
				sum += digit - 9;
			}
		} else {
			sum += digit;
		}
	}
	return sum % 10 === 0;
}

App.creditCard.changeIcons = function( cardValue ){
	var card = App.creditCard.detectType( cardValue );
	console.log('card',card);
	if( card ){		
		$( '.payment-card' ).addClass( 'to-grey' );
		$( '.payment-card' ).removeClass( 'to-color' );
		$( '.card-' + card.name ).removeClass( 'to-grey' );
		$( '.card-' + card.name ).addClass( 'to-color' );
		$('.to-color').animate( { 'background-position-x': '0'}, 100 );
		$('.to-grey').animate( { 'background-position-x': '-40px'}, 100 );
	} else {
		$( '.payment-card' ).animate( { 'background-position-x': '0'}, 100 );
	}
	App.creditCard.validate( cardValue );
}

App.creditCard.detectType = function( cardValue ){
	var cardType = false;
	$( App.creditCard.cards ).each( function( index, card ){
		if( card.pattern.test( cardValue ) ){
			cardType = card;
		}
	} );
	return cardType;
}