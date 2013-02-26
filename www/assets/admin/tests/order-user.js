$(function() {
	$test.tests['order-user'] = {
		name: 'User Order',
		requires: ['login'],
		init: function(callback) {
			callback(false);
		},
		onFail: function() {
		
		}
	};
});