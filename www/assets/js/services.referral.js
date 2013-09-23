// ReferralService service
NGApp.factory( 'ReferralService', function( $http, $rootScope ){

	var service = { invite_url : null, value : 0, invites : 0, limit : 0 };

	service.getInviteCode = function(){
		var url = App.service + 'referral/code';
		$http( { 
				url: url,
				method : 'POST',
				headers: {'Content-Type': 'application/x-www-form-urlencoded' }
			} ).success( function( data ) {
				service.invite_url = data.invite_url;
				$rootScope.$broadcast( 'referralCodeLoaded', true );
			}	).error(function( data, status ) { 
				console.log( { error : data } ); 
			} );
	}

	service.getValue = function(){
		var url = App.service + 'referral/value';
		$http( { 
				url: url,
				method : 'POST',
				headers: {'Content-Type': 'application/x-www-form-urlencoded' }
			} ).success( function( data ) {
				service.value = data.value;
				$rootScope.$broadcast( 'referralValueLoaded', true );
			}	).error(function( data, status ) { 
				console.log( { error : data } ); 
			} );
	}

	service.getStatus = function(){
		var url = App.service + 'referral/status';
		$http( { 
				url: url,
				method : 'POST',
				headers: {'Content-Type': 'application/x-www-form-urlencoded' }
			} ).success( function( data ) {
				service.enabled = data.enabled;
				service.invites = data.invites;
				service.value = data.value;
				service.limit = data.limit;
				service.invite_url = data.invite_url;
				$rootScope.$broadcast( 'referralStatusLoaded', true );
			}	).error(function( data, status ) { 
				console.log( { error : data } ); 
			} );
	}

	
	service.cleaned_url = function(){
		return service.invite_url && service.invite_url.replace('http://','');
	}

	$rootScope.$on( 'userAuth', function(e, data) {
		service.invite_url = null;
		service.value = 0;
		service.invites = 0;
		service.limit = 0;
	});

	return service;

} );
