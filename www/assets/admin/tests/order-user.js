$(function() {

	$test.tests['order-user'] = {
		name: 'User Order',
		maxExecution: 10000,
		requires: ['login.logedin'],
		init: function(callback) {
			console.log($test.states)
			setTimeout(function() {
				callback(true);			
			}, 2000)

		},
		onFail: function() {
		
		}
	};
});