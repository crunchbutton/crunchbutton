
NGApp.factory('DateTimeService', function(AccountService) {
	var service = {
		tzServer: 'UTC',
		tzLocal: new Date().getTimezoneOffset(),

		// convert a server time to local time
		local: function(date) {
			var d = moment.tz(date, service.tzServer);
			d.zone(service.tzLocal);
			return d;
		},

		// convert a local time to server time
		server: function(date) {
			return service.convert(date, service.tzServer);
		},

		// convert a date to any timezone
		convert: function(date, zone) {
			return moment(date).tz(zone);
		},

		convertformat: function(date, zone, format) {
			if( moment(date).tz(zone) ){
				return moment.tz(date, 'UTC').tz(zone).format(format || 'ha z');
			}
			return null;
		}
	};
	return service;
});

NGApp.filter('localtime', function(DateTimeService) {
	return function(date, format) {
		return DateTimeService.local(date).format(format || 'YYYY-MM-DD HH:mm:ss Z');
	};
});

NGApp.filter('servertime', function(DateTimeService) {
	return function(date, format) {
		return DateTimeService.server(date).format(format || 'YYYY-MM-DD HH:mm:ss Z');
	};
});

NGApp.filter('timestamp', function(DateTimeService) {
	return function(date) {
		return moment(date).unix()*1000;
	};
});

NGApp.filter('convertimeformat', function(DateTimeService) {
	return DateTimeService.convertformat;
});
