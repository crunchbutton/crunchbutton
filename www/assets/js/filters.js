// Phone format filter
NGApp.filter('formatPhone', function() {
	return function( input ) {
		if( input != null ){
			input = input.replace(/^0|^1/,'');
			input = input.replace(/[^\d]*/gi,'');
			input = input.substr(0,10);

			if (input.length >= 7) {
				input = input.replace(/(\d{3})(\d{3})(.*)/, "$1-$2-$3");
			} else if (input.length >= 4) {
				input = input.replace(/(\d{3})(.*)/, "$1-$2");
			}
		}
		return input;
	};
});

// Price format filter
NGApp.filter('formatPrice', function() {
	return function( input ) {
		if( input != null ){
			input = parseFloat( input ).toFixed(2)
		}
		return input;
	};
});
