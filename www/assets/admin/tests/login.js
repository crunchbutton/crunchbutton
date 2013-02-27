$(function() {

	$test.tests['login'] = {
		name: 'Login',
		maxExecution: 5000,
		init: function(callback) {

			$t($test.pageDocument()).ajaxComplete(function(event, xhr, settings) {

				if (settings.url === '/api/user/auth') {
					$t($test.pageDocument()).unbind('ajaxComplete');
					var auth = JSON.parse(xhr.responseText);
					if (auth.id) {
						$(document).trigger('test-state-change', 'login.logedin');
						callback(true);
						return;
					} else {
						callback(false);
					}
				}
			});

			$t('.signin-icon').click();

			setTimeout(function() {
				$t('.signin-form [name="signin-email"]').val($test.config.user.username);
				$t('.signin-form [name="signin-password"]').val($test.config.user.password);
				setTimeout(function() {
					$t('.signin-form-button').click();
				}, 200);
			}, 200);
		},
		onFail: function() {
		
		},
		sates: {
			logedin: function(callback) {
				$test.tests['login'].init(callback);
			}
		}
	};
});