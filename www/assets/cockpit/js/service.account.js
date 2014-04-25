NGApp.factory('AccountService', function($http, $rootScope) {
	var service = {};
	
	service.checkUser = function() {
		if (App.config.user.id_admin) {
			service.loggedin = true;
		}
	};
	


	
	return service;

});