// MainHeaderService service
NGApp.factory( 'MainNavigationService', function( $http, $location, AccountService, AccountModalService ){ 
	
	var service = {
		page : '',
		buttons : {
			location: false,
			menu: true
		}
	};
	
	service.menu = App.toggleMenu;
	service.account = AccountService;
	service.modal = AccountModalService;
	service.home = function() {
		if (App.isPhoneGap || App.isMobile()) {
			service.menu();
		} else {
			service.link();
		}
	}

	service.link = function( path ){
		$location.path( path || '/' );
		App.snap.close();
	}

	service.signin = function(){
		service.modal.signinOpen();
	}

	service.control = function() {
		switch (service.page) {
			case 'restaurant':
				service.buttons.location = false;
				service.buttons.back = true;
				service.buttons.menu = false;
				break;

			default:
				service.buttons.back = false;
				service.buttons.location = false;
				service.buttons.menu = true;
				break;
		}
	}

	return service;

});
