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

NGApp.directive( 'preLoadImage', function() {
		return function( scope, element, attrs ) {
			var image = new Image();
			image.src = attrs.preLoad;
		};
} );

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

// Custom Checkbox - See #1577
NGApp.directive( 'customCheckbox', function () {

	// Hack to not work at desktop and don't trow any error
	if( !App.isMobile() ){ return { restrict: 'E' };}

	return {
		restrict: 'A',
		require: 'ngModel',
		replace: true,
		template: '<span class="custom-checkbox"></span>',

		link: function (scope, elem, attrs, ctrl) {
			var label = angular.element('label[for="' + attrs.id + '"]');
			label.bind('click', function () {
				scope.$apply(function () {
					elem.toggleClass( 'checked' );
					ctrl.$setViewValue( elem.hasClass( 'checked' ) );
				});
			});

			elem.bind('click', function () {
				scope.$apply(function () {
					elem.toggleClass( 'checked' );
					ctrl.$setViewValue( elem.hasClass( 'checked' ) );
				});
			});

			ctrl.$render = function () {
				if (!elem.hasClass('checked') && ctrl.$viewValue) {
					elem.addClass('checked');
				} else if (elem.hasClass('checked') && !ctrl.$viewValue) {
					elem.removeClass('checked');
				}
			};			
		}
	}
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
