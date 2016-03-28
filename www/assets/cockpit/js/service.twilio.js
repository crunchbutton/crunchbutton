NGApp.factory('TwilioService', function($resource, $rootScope, AccountService) {

	var service = { connection: null };
	var resource = $resource(App.service + 'twilio/client');

	service.isReady = false;
	service.init = function() {
		console.log('Twilio starting...');
		resource.get([], function(res) {
			service.token = res.token;
			Twilio.Device.setup(service.token, {debug: true, warnings: true});
		});
	};

	var checkStatus = function(){
		console.log('Twilio status: ', Twilio.Device.status() );
		if( Twilio.Device.status() == 'offline' ){
			service.init();
		}
		setTimeout(function() {
			checkStatus();
		}, 1000 * 180 );
	}

	setTimeout( function(){
		checkStatus();
	}, 1000 * 30 );


	service.call = function(phone) {
		if(!phone){
			$rootScope.closePopup();
			setTimeout( function(){
				App.alert( 'Please enter a phone number!<br>' );
			}, 500 );
			return;
		}
		if( !service.isReady ){
			$rootScope.closePopup();
			setTimeout( function(){
				App.alert('Twilio service is not ready yet. <br>Please try it again later.');
			}, 500 );
			return;
		}
		$rootScope.$broadcast('twilio-client-call-start');
		service.connection = Twilio.Device.connect({'PhoneNumber': phone});
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
		service.isReady = true;
		console.debug('Twilio client is ready');
	});

	Twilio.Device.error(function (error) {
		console.debug('Twilio error: ' + error.message);
		// try to re-start twilio
		if( error.code == 31204 ){
			service.init();
		}
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
/*
	Twilio.Device.presence(function (pres) {
		if (pres.available) {
			console.debug('Twilio user has connected.', pres);
			// $("<li>", {id: pres.from, text: pres.from}).click(function () {
			// 	$("#number").val(pres.from);
			// 	call();
			// }).prependTo("#people");

		} else {
			console.debug('Twilio user has been disconnected.', pres);
		}
	});
*/
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