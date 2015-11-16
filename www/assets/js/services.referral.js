// ReferralService service
NGApp.factory( 'ReferralService', function( $http, $rootScope, $location, AccountService ){

	var service = { invite_url : null, value : 0, invites : 0, limit : 0, invitedUsers: false };

	service.check = function(){
		var param = $location.search();
		if( param.invite ){
			$.totalStorage( 'referral', param.invite );
			// Remove the invite from url
			$location.url( $location.path() );
		}
	}

	service.newReferredUsersByUser = function(){
		if( App.config.new_referral_users ){
			service.invitedUsers = App.config.new_referral_users;
			$rootScope.$broadcast( 'ReferralInvitedUsers', true );
		}
	}

	service.getInviteCode = function(){
		var url = App.service + 'referral/code';
		$http( {
				url: url,
				method : 'POST',
				headers: {'Content-Type': 'application/x-www-form-urlencoded' }
			} ).success( function( data ) {
				service.invite_url = data.invite_url;
				AccountService.user.invite_code = data.invite_code;
				$rootScope.$broadcast( 'referralCodeLoaded', true );
			}	).error(function( data, status ) {
				console.log( { error : data } );
			} );
	}

	service.sms = function(){
		var text = App.AB.get('share-text-referral').replace('%c', service.invite_code);
		if( App.iOS() ){
			return 'sms:&body=' + text + ' ' + service.invite_url;
		} else {
			// this appears to be the standard and should work for other non droid phones
			return 'sms:?body=' + text + ' ' + service.invite_url;
		}
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
		if( App.config.referral ){
			service.enabled = App.config.referral.enabled;
			service.invites = App.config.referral.invites;
			service.value = App.config.referral.value;
			service.limit = App.config.referral.limit;
			service.invite_code = App.config.referral.invite_code;
			service.invite_url = App.config.referral.invite_url;
			$rootScope.$broadcast( 'referralStatusLoaded', true );
		} else {
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
					service.invite_code = data.invite_code;
					service.invite_url = data.invite_url;
					$rootScope.$broadcast( 'referralStatusLoaded', true );
				}	).error(function( data, status ) {
					console.log( { error : data } );
				} );
		}
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
