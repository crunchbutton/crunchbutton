App.tokenizeCard = function(card, complete) {
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
		console.debug('Balanced tokenization response',response);
		complete(res);
	} );
};
