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

NGApp.directive('ngScrollPosition', function ($window, $rootScope) {
	return function (scope, element, attrs) {
			var window = angular.element( $window );
			var raw = element[0];
			element.bind('scroll', function () {
				changeScrollTop( raw.scrollTop );
			});
			window.on('resize', function () {
				changeScrollTop( raw.scrollTop );
			});
			function changeScrollTop( value ){
				$rootScope.$broadcast( 'scrollUpdated', raw.scrollTop );
			}
		};
});

NGApp.directive('ngPositionFixed', function ($window, $rootScope) {
	return function (scope, element, attrs) {
			window = angular.element($window);
			$rootScope.$on( 'scrollUpdated', function(e, data) {
				var scrollTop = data;
				var window_height = window.document.height;
				var element_height = element.height();
				var top = window_height - element_height + scrollTop;
				element.css( { top: top } );
			});
		};
});