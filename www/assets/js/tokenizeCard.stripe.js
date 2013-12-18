App.tokenizeCard = function( card, complete ) {
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