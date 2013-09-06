//CreditCardService Service
NGApp.factory( 'CreditCardService', function( $http ){
	var service = {};

	service.cards = [
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
		];

	service.validate = function( cardValue ){
		var card = service.detectType( cardValue );
		var isValid = false;
		if( card.size == cardValue.toString().length ){
			isValid = service.luhn( cardValue );
		}
		if( isValid ){
			$( '[name="pay-card-number"]' ).addClass( 'credit-card-ok' );
		} else {
			$( '[name="pay-card-number"]' ).removeClass( 'credit-card-ok' );
		}
	}

	service.luhn = function( cardValue ){
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

	service.changeIcons = function( cardValue ){
		var card = service.detectType( cardValue );
		if( card ){		
			$( '.payment-card' ).addClass( 'grey' );
			$( '.card-' + card.name ).removeClass( 'grey' );
		} else {
			$( '.payment-card' ).removeClass( 'grey' );
		}
		service.validate( cardValue );
	}

	service.detectType = function( cardValue ){
		var cardType = false;
		$( service.cards ).each( function( index, card ){
			if( card.pattern.test( cardValue ) ){
				cardType = card;
			}
		} );
		return cardType;
	}

	return service;
} );