$(function() {
	$test.tests['order-user'] = {
		name: 'User Order',
		requires: ['login.logedin'],
		init: function(callback) {
		
			callback(true);
		},
		onFail: function() {
		
		}
	};
});