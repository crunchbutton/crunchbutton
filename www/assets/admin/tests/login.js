$(function() {
	$test.tests['login'] = {
		name: 'Login',
		init: function(callback) {


			$t($test.pageDocument()).ajaxComplete(function(event, xhr, settings) {
				if (settings.url == '/api/user/auth') {
					var auth = JSON.parse(xhr.responseText);
					if (auth.id) {
						callback(true);
						return;
					}
				}
				callback(false);
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
		
		}
	};
});