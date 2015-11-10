
NGApp.factory('TwilioService', function($resource, $rootScope, AccountService) {

	var service = { connection: null };
	var resource = $resource(App.service + 'twilio/client');

	service.init = function() {
		resource.get([], function(res) {
			service.token = res.token;
			Twilio.Device.setup(service.token);
		});

	};

	service.call = function(phone) {
		$rootScope.$broadcast('twilio-client-call-start');
		service.connection = Twilio.Device.connect({
			'PhoneNumber': phone
		});
	};

	service.hangup = function() {
		Twilio.Device.disconnectAll();
		service.connection = null;
	};

	service.mute = function() {
		if( service.connection ){
			$rootScope.isMute = ( $rootScope.isMute ) ? false : true;
			service.connection.mute( $rootScope.isMute );
		}
	};

	Twilio.Device.ready(function (device) {
		console.debug('Twilio client is ready');
	});

	Twilio.Device.error(function (error) {
		console.debug('Twilio error: ' + error.message);
	});

	Twilio.Device.connect(function (conn) {
		console.debug('Twilio successfully established call');
		$rootScope.$broadcast('twilio-client-call-connect');
	});

	Twilio.Device.disconnect(function (conn) {
		console.debug('Twilio call ended');
		$rootScope.$broadcast('twilio-client-call-end');
	});

	Twilio.Device.incoming(function (conn) {
		console.debug('Twilio incoming connection from ' + conn.parameters.From);
		//conn.accept();
	});

	Twilio.Device.presence(function (pres) {
		if (pres.available) {
			console.debug('Twilio user has connected.', pres);
			/*
			$("<li>", {id: pres.from, text: pres.from}).click(function () {
				$("#number").val(pres.from);
				call();
			}).prependTo("#people");
			*/
		} else {
			console.debug('Twilio user has been disconnected.', pres);
		}
	});

	$rootScope.$on('twilio-client-call-connect', function() {
		$rootScope.callStatus = 'connected';
	});
	$rootScope.$on('twilio-client-call-end', function() {
		$rootScope.callStatus = 'ended';
	});
	$rootScope.$on('twilio-client-call-start', function() {
		$rootScope.callStatus = 'connecting';
	});


	var load = function() {
		if (AccountService.permcheck(['global', 'support-all', 'support-view', 'support-crud'])) {
			service.init();
		}
		watching = null;
	};

	var watching = null;

	if (!AccountService.init) {
		// we got here before the auth service was complete.
		watching = $rootScope.$on('userAuth', load);
	}

	return service;

});