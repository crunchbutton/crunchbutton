NGApp.factory('favicoService', function() {
	var favico = new Favico({
		animation : 'popFade',
		position : 'up'
	});
	
	var prev = 0;

	var badge = function(num) {
		if (parseInt(prev) == parseInt(num)) {
			return;
		}
		prev = num;
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