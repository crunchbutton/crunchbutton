App.tokenizeCard = function( card, complete ) {
console.log('App.tokenizeCard');
	var processor = ( App.config.processor && App.config.processor.type ) ? App.config.processor.type : false;
	console.debug('Processor: ', processor);

	switch(processor) {
		case 'stripe':
			App.tokenizeCard_stripe( card, complete );
			break;
		default:
			App.alert('There was an error communicating with our payment processor.');
			console.log( 'Processor error::', App.config.processor );
			break;
	}
};

App.tokenizeCard_stripe = function( card, complete ) {
	var res = {
		status: false
	};

	var card = {
		number: card.number,
		exp_month: card.expiration_month,
		exp_year: card.expiration_year
	};

	Stripe.card.createToken( card , function( status, response ){
		console.debug('Recieved response from stripe: ', status, response);
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
					console.debug('STRIPE ERROR', card, response);
					res.error = 'Unable to validate your card at this time';
					break;
			}
		} else {
			res = {
				id : response.card.id,
				uri: response.id,
				lastfour: response.card.last4,
				card_type: response.card.brand.toLowerCase(),
				month: response.card.exp_month,
				year: response.card.exp_year,
				status : true
			}
		}
		complete( res );
	} );
};

App.tokenizeCard_balanced = function(card, completed) {

	var handleResponse = function(response) {
		console.debug('Recieved response from balanced: ', response);
		var res = {
			status: false
		};

		// 1.1 to 1.0 migration
		if (response.status_code) {
			var version = '1.1';
			response.status = response.status_code;
		} else {
			var version = '1';
		}

		switch (response.status) {
			case 201:
				res.status = true;
				if (version == '1.1') {
					res.id = response.cards[0].id;
					res.uri = response.cards[0].href;
					if( response.card_type ){
						res.card_type = response.card_type;
					} else {
						res.card_type = getCardType(card.number);
					}
					if( response.last_four ){
						res.lastfour = response.last_four;
					} else {
						res.lastfour = card.number.substr(-4);
					}
				} else {
					res.id = response.data.id;
					res.uri = response.data.uri;
					res.card_type = response.data.card_type;
					res.lastfour = response.data.last_four;
				}

				res.month = card.expiration_month;
				res.year = card.expiration_year;
				break;

			case 400:
				var description = 'Missing card information';
				if( response.errors && response.errors[0] && response.errors[0].description ){
					if( response.errors[0].description.indexOf( '[number]' ) ){
						description = 'Not a valid credit card number';
					}
				}

				res.error = description;

				break;

			case 402:
				res.error = 'Unable to authorize card';
				break;

			case 404:
				res.error = 'Unexpected payment error';
				break;

			case 409:
				res.error = 'Unable to validate credit card';
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
				console.debug('BALANCED ERROR', card, response);
				res.error = 'Unable to validate your card at this time';
				break;
		}
		completed(res);
	};

	console.debug('Sending card to balanced...');

	balanced.card.create(card, handleResponse);
};
