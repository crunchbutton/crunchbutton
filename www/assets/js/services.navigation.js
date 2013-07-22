// MainHeaderService service
NGApp.factory( 'MainNavigationService', function( $http, $location, AccountService, AccountModalService ){ 
	
	var service = {
		page : '',
		buttons : {
			location : true
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
		switch( service.page ){
			case 'location':
				service.buttons.location = false;
				break;
			default:
				service.buttons.location = true;
				break;
		}
	}

	return service;

} );
