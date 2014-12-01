NGApp.factory('favicoService', function() {
	var favico = new Favico({
		animation : 'popFade',
		position : 'up'
	});

	var badge = function(num) {
		favico.badge(num);
	};
	var reset = function() {
		favico.reset();
	};

	return {
		badge : badge,
		reset : reset
	};
});