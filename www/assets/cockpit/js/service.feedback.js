
NGApp.factory('FeedbackService', function($resource) {

	var service = {};

	var feedback = $resource(App.service + 'driver/feedback', {}, {
		
		'save' : {
			url: App.service + 'driver/feedback',
			method: 'POST',
			params : {}
		}
		
	});
	
	service.post = function(params, callback) {
		feedback.save(params, function(data) {
			console.log(params);
			callback(data);
		});
	}

	return service;

});