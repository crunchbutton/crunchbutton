// Support
NGApp.factory( 'SupportService', function( $http, AccountService ){

	var service = {
		minimized : true,
		maximized : false,
		thanks : true
	};

	// Create a copy of the user values
	service.account = AccountService;
	service.name = angular.copy( service.account.user.name );
	service.phone = angular.copy( service.account.user.phone );
	service.message = '';

	service.toggle = function(){
		service.minimized = !service.minimized;
		service.maximized = !service.maximized;
		if( service.maximized ){
			service.reset();
		}
	}

	service.reset = function(){
		service.message = '';
		service.thanks = false;
	}

	service.send = function(){

		service.purify();

		if ( service.name == '' ){
			alert( 'Please enter your name.' );
			$('input[name=support-name]').focus();
			return;
		}

		if ( service.phone == '' ){
			alert( 'Please enter your phone.' );
			$('input[name=support-phone]').focus();
			return;
		}

		if ( !App.phone.validate( service.phone ) ) {
			alert( 'Please enter a valid phone.' );
			$('input[name=support-phone]').focus();
			return;
		}

		if ( service.message == '' ){
			alert( 'Please enter the message.' );
			$('textarea[name=support-message]').focus();
			return;
		}

		if (!service.isSending){
			
			service.isSending = true;
			
			var url = App.service + 'support/sms';

			$http( {
				method: 'POST',
				url: url,
				data: $.param( { name: service.name, phone: service.phone, message: service.message } ),
				headers: {'Content-Type': 'application/x-www-form-urlencoded'}
				} ).success( function( data ) { service.thanks = true; service.isSending = false; }	);
		}
	}

	service.purify = function(){
		service.name = $.trim( service.name );
		service.phone = $.trim( service.phone );
		service.message = $.trim( service.message );
	}

	return service;

} );