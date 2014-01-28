App.tokenizeCard = function( card, complete ) {
	var processor = ( App.config.processor && App.config.processor.type ) ? App.config.processor.type : false;
	switch( processor ){
		case 'stripe':
			App.tokenizeCard_stripe( card, complete );
			break;
		case 'balanced':
			App.tokenizeCard_balanced( card, complete );	
		break;
		default:
			console.log( 'Processor error::', App.config.processor );
		break;
	}	
};

App.tokenizeCard_stripe = function( card, complete ) {
	var res = { status: false };
	var card = { number: card.card_number, exp_month: card.expiration_month, exp_year: card.expiration_year };
	Stripe.card.createToken( card , function( status, response ){
		if ( response.error ) {
			switch( response.error.code ){
				case 'incorrect_number':
					res.error = 'This card number looks invalid';
					break;
				case 'invalid_number':
					res.error = 'The card number is not a valid credit card number.';
					break;
				case 'invalid_expiry_month':
					res.error = 'The card\'s expiration month is invalid.';
					break;
				case 'invalid_expiry_year':
					res.error = 'The card\'s expiration year is invalid.';
					break;
				case 'invalid_cvc':
					res.error = 'The card\'s security code is invalid.';
					break;
				case 'expired_card':
					res.error = 'The card has expired.';
					break;
				case 'incorrect_cvc':
					res.error = 'The card\'s security code is incorrect.';
					break;
				case 'card_declined':
					res.error = 'The card was declined.';
					break;
				case 'processing_error':
					res.error = 'An error occurred while processing the card.';
					break;
				default:
					res.error = 'Unable to validate your card at this time';
					break;
			}
		} else {
			res = {
				id : response.card.id,
				uri: response.id,
				lastfour: response.card.last4,
				card_type: response.card.type,
				month: response.card.exp_month,
				year: response.card.exp_year,
				status : true
			}
		}
		complete( res );
	} );
};

App.tokenizeCard_balanced = function(card, complete) {

	console.log( 'call App.tokenizeCard_balanced' );

	balanced.card.create(card, function(response) {
		var res = {
			status: false
		}; 

		switch (response.status) {
			case 201:
				res.status = true;
				res.id = response.data.id;
				res.uri = response.data.uri;
				res.card_type = response.data.card_type;
				res.lastfour = response.data.last_four;
				res.month = card.expiration_month;
				res.year = card.expiration_year;
				break;

			case 400:
				res.error = 'Missing fields';
				break;

			case 402:
				res.error = 'Unable to authorize';
				break;

			case 404:
				res.error = 'Unexpected error';
				break;
				
			case 409:
				res.error = 'Unable to validate';
				break;

			case 500:
				res.error = 'Error processing payment';
				break;
				
			// a lack of any response from the ios sdk
			case 999:
				res.error = 'Unable to reach payment server';
				break;
			
			// a response from the ios sdk that was both ilformated and didnt not validate
			case 666:
				res.error = 'Unable to validate your card';
				break;

			// who knows wtf this is
			default:
				res.error = 'Unable to validate your card at this time';
				break;
		}
		complete(res);
	} );
};
