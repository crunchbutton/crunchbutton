// Phone format filter
NGApp.filter( 'formatPhone', function() {
	return function( input ) {
		if( input != null ){
			if (typeof input == 'number') {
				input = input.toString();
			}
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

// Address format filter
NGApp.filter( 'formatAddress', function() {
	return function( input ) {
		if( input != null ){
			input = input.split("\n")[0].split(',')[0];
		}
		return input;
	};
});

// Price format filter
NGApp.filter( 'formatPrice', function() {
	return function( input ) {
		if( input != null ){
			input = parseFloat( input ).toFixed(2)
		}
		return input;
	};
});

// text format filter
NGApp.filter( 'nl2br', function() {
	return function( input ) {
		if( input != null ){
			return input.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br/>$2');
		}
		return input;
	};
});

NGApp.filter( 'clearAddress', function(){
	return function( input ) {
		if( input != null ){
			return input.replace(/#/g, '');
		}
		return input;
	};
} );

// text format filter
NGApp.filter( 'nl2cm', function() {
	return function( input ) {
		if( input != null ){
			return input.replace(/\r/g, '').replace(/\n/g,', ');
		}
		return input;
	};
});

// Phone format filter
NGApp.filter( 'callPhone', function() {
	return function( input ) {
		if( input != null ){
			if( App.isMobile() ){
				return '<a href="tel:' + App.phone.format( phone ).replace( /\-/g, '' ) + '">' + phone + '</a>';
			}
		}
		return input;
	};
});

NGApp.filter('iif', function () {
	return function(input, trueValue, falseValue) {
		return input ? trueValue : falseValue;
	};
});

NGApp.filter( 'tsToHour', function( $filter ){
	return function( input ) {
		if( input != null ){
			// client's timezone
			var tz = new Date().toString().match( /([-\+][0-9]+)\s/ )[ 1 ];
			return $filter( 'date' )( input, 'h:mm a', tz );
		}
		return input;
	};
} );
