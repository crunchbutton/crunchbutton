// MainHeaderService service
NGApp.factory( 'MainNavigationService', function( $http, $location, AccountService, AccountModalService ){ 
	
	var service = {
		page : '',
		buttons : {
			location: false,
			menu: true
		}
	};

	service.account = AccountService;
	service.modal = AccountModalService;

	service.link = function( path ){
		$location.path( path || '/' );
	}

	service.signin = function(){
		service.modal.signinOpen();
	}

	service.control = function(){
		switch (service.page) {
			case 'location':
				service.buttons.location = false;
				service.buttons.menu = true;
				break;

			default:
				service.buttons.location = true;
				service.buttons.menu = false;
				break;
		}
	}

	return service;

});
