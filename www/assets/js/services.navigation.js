// MainHeaderService service
NGApp.factory( 'MainNavigationService', function( $http, $location, AccountService, AccountModalService ){ 
	
	var service = {
		page : ''
	};

	service.account = AccountService;
	service.modal = AccountModalService;

	service.link = function( path ){
		$location.path( path || '/' );
	}

	service.signin = function(){
		service.modal.signinOpen();
	}
	
	return service;

} );