// Support
NGApp.factory( 'SupportService', function( $http, AccountService ){

	var service = {
		minimized : true,
		maximized : false,
		thanks : false,
		form : {}
	};

	// Create a copy of the user values
	service.account = AccountService;
	service.form.name = angular.copy( service.account.user.name );
	service.form.phone = angular.copy( service.account.user.phone );
	service.form.message = '';

	service.toggle = function(){
		service.minimized = !service.minimized;
		service.maximized = !service.maximized;
		if( service.maximized ){
			service.reset();
		}
	}

	service.reset = function(){
		service.form.message = '';
		service.thanks = false;
	}

	service.send = function(){

		service.purify();

		if ( service.form.name == '' ){
			alert( 'Please enter your name.' );
			$('input[name=support-name]').focus();
			return;
		}

		if ( service.form.phone == '' ){
			alert( 'Please enter your phone.' );
			$('input[name=support-phone]').focus();
			return;
		}

		if ( !App.phone.validate( service.form.phone ) ) {
			alert( 'Please enter a valid phone.' );
			$('input[name=support-phone]').focus();
			return;
		}

		if ( service.form.message == '' ){
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
				data: $.param( { name: service.form.name, phone: service.form.phone, message: service.form.message } ),
				headers: {'Content-Type': 'application/x-www-form-urlencoded'}
				} ).success( function( data ) { service.thanks = true; service.isSending = false; }	);
		}
	}

	service.purify = function(){
		service.form.name = $.trim( service.form.name );
		service.form.phone = $.trim( service.form.phone );
		service.form.message = $.trim( service.form.message );
	}

	return service;

} );