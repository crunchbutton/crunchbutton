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

// Suggestion tool tip
NGApp.directive( 'suggestionToolTip', function () {
	return {
		restrict: 'A',
		templateUrl: 'assets/view/restaurant.suggestion.tooltip.html',
		scope: {
			type: '@'
		},
		controller: function ( $scope ) {
			$scope.show = false;
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

NGApp.directive('ngClickSelectAll', function() {
		return {
				restrict: 'A',
				link: function(scope, element, attrs) {
						element.bind('click', function () {
							element.select();
						});
				}
		};
});

// Blur event directive
NGApp.directive('ngBlur', function() {
		return {
				restrict: 'A',
				link: function(scope, element, attrs) {
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
