
NGApp.factory('ApplyService', function($resource) {

	var service = {};

	var apply = $resource(App.service + 'apply', {}, {
		
		'save' : {
			url: App.service + 'apply',
			method: 'POST',
			params : {}
		}
		
	});
	
	service.post = function(params, callback) {
		apply.save(params, function(data) {
			//alert('yo');
			callback(data);
		});
	}

	return service;

});