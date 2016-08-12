// Support
NGApp.factory( 'SupportService', function( $http, AccountService, $rootScope){

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

	$rootScope.$on('userAuth', function(e, data) {
		if (!data) {
			return;
		}
		service.form.name = angular.copy( data.name );
		service.form.phone = angular.copy( data.phone );
	});

	service.dialog = function() {
		service.thanks = false;
		service.form.message = '';
		App.dialog.show('.support-container');
	}

	service.toggle = function(){
		service.minimized = !service.minimized;
		service.maximized = !service.maximized;

		if (service.maximized){
			$('.support-container').addClass('support-container-maximized');
			service.reset();
		} else {
			$('.support-container').removeClass('support-container-maximized');
		}
	}

	service.reset = function(){
		service.form.message = '';
		service.thanks = false;
	}

	service.send = function(){

		service.purify();

		var error = '';
		var errors = [];

		if (!service.form.name || !service.form.phone || !App.phone.validate(service.form.phone) || !service.form.message) {
			error += 'Please enter ';

			if (!service.form.name) {
				errors[errors.length] = 'your name';
			}

			if (!App.phone.validate(service.form.phone)) {
				errors[errors.length] = 'a valid phone number';
			}

			if (!service.form.message) {
				errors[errors.length] = 'a message';
			}

			for (var x in errors) {
				error += (x != 0 ? ', ' : '') + (errors.length > 1 && x == errors.length-1 ? 'and ' : '') + errors[x] + (x == errors.length-1 ? '.' : '');
			}
		}

		if (error) {
			service.error = error;

			if (!service.form.message){
				$('textarea[name=support-message]').focus();
			}

			if (!App.phone.validate( service.form.phone)) {
				$('input[name=support-phone]').focus();
			}

			if (!service.form.name){
				$('input[name=support-name]').focus();
			}

			return;

		} else {
			service.error = '';
		}

		// passes validation

		if (!service.isSending){

			service.isSending = true;

			var url = App.service + 'support/sms';

			$http( {
				method: 'POST',
				url: url,
				data: $.param( { name: service.form.name, phone: service.form.phone, message: service.form.message } ),
				headers: {'Content-Type': 'application/x-www-form-urlencoded'}
				} ).success( function( data ) {
					service.thanks = true;
					service.isSending = false;
					$rootScope.$safeApply();
			});
		}
	}

	service.purify = function(){
		service.form.name = $.trim( service.form.name );
		service.form.phone = $.trim( service.form.phone );
		service.form.message = $.trim( service.form.message );
	}

	return service;

} );