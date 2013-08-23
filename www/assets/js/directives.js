// Facebook button compoment
NGApp.directive( 'facebookSigninButton', function ( AccountFacebookService ) {
	return {
		restrict: 'A',
		templateUrl: 'assets/view/account.facebook.html',
		scope: {
			title: '@'
		},
		controller: function ( $scope ) {
			$scope.facebook = AccountFacebookService;
		}
	};;
});

// Press enter directive
NGApp.directive( 'ngEnter', function() {
		return function( scope, element, attrs ) {
				element.bind( 'keydown keypress', function( event ) {
					if( event.which === 13 ) {
						scope.$apply( function() {
							scope.$eval( attrs.ngEnter );
						} );
						event.preventDefault();
					}
				} );
		};
} );

// Blur event directive
NGApp.directive('ngBlur', function() {
		return {
				restrict: 'A',
				link: function postLink(scope, element, attrs) {
						element.bind('blur', function () {
								scope.$apply(attrs.ngBlur);
						});
				}
		};
});

NGApp.directive('ngInstant', function () {
	return function(scope, element, attrs) {
		element.bind(App.isMobile() ? 'touchstart' : 'click', function(e) {
			scope.$apply(attrs['ngInstant'], element);
			e.preventDefault();
			e.stopPropagation();
		});
	};
});
