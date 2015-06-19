NGApp.factory( 'TwitterService', function( $http, $location, $rootScope, AccountService, ReferralService) {

	var service = {}

	// This method pre load the order info that could be posted
	service.tweet = function( uuid, callback ){
		$http( {
				method: 'POST',
				url: App.service + 'twitter/reward/',
				data: $.param( { 'uuid' : uuid } ),
				headers: {'Content-Type': 'application/x-www-form-urlencoded'}
				}
			).success( function( json ) { if( callback ){ callback(); }  } );
	};

	service.buttonCreated = function( el ){}

	service.inviteUrl = function( invite_code ){
		return 'http://www.crunchbutton.com/invite/' + invite_code;
	}

	service.referralText = function( invite_code ){
		var text = App.AB.get('share-text-twitter').replace('%c', invite_code);
		return text;
	}

	service.referralHashtags = function(){
		return 'delivery';
	}


	return service;
});