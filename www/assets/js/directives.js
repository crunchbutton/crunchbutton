NGApp.directive('geoComplete', function() {
	return {
		restrict: 'A',
		link: function(scope, element, attrs) {
			element.geocomplete()
				.bind('geocode:result', function(event, result) {
					console.log('Result: ' + result.formatted_address);
				}).bind('geocode:error', function(event, status) {
					console.log('ERROR: ' + status);
				}).bind('geocode:multiple', function(event, results) {
					console.log('Multiple: ' + results.length + ' results found');
				});
		}
	};
});

NGApp.directive('addToCart', function() {
	return {
		restrict: 'A',
		link: function(scope, element, attrs) {
			element.bind('click', function (e) {
				if ($('.is-ui2').get(0)) {
					setTimeout(function() {
						var el = $(element.get(0));
						var cart = $('.nav-cart');

						var animate = $('<div class="animate-cart-add">').css({
							top: el.position().top+20
						});
						animate.bind('transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd', function() {
							$(this).remove();
						});
						animate.appendTo('body');
						setTimeout(function() {
							animate.css({
								top: cart.position().top,
								left: cart.position().left,
								width: cart.width()/2,
								height: cart.height()/2,
								opacity: .3
							});	
						},1);
					},0);
				}
				
				setTimeout(function() {
					scope.order.cart.add(attrs.idDish);
					scope.$apply();
				},0);

			});
		}
	};
});


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

// Restaurant is closed
NGApp.directive( 'restaurantIsClosed', function () {
	return {
		restrict: 'A',
		templateUrl: 'assets/view/restaurant.closed.inline.html'
	};
});

NGApp.directive( 'preLoadImage', function() {
		return function( scope, element, attrs ) {
			if( attrs.preLoad ){
				var image = new Image();
				image.src = attrs.preLoad;
			}
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
		template: '<span class="custom-checkbox icon-check-sign"></span>',
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

NGApp.directive('ngKeyDown', function () {
	return {
		restrict: 'A',
		link: function (scope, elem, attr) {
			angular.element(elem).bind('keydown', function (evt) {
				scope.$eval(attr.ngKeyDown);
			});
		}
	};
});

NGApp.directive('ngKeyUp', function () {
	return {
		restrict: 'A',
		link: function (scope, elem, attr) {
			angular.element(elem).bind('keyup', function (evt) {
				scope.$eval(attr.ngKeyUp);
			});
		}
	};
});

NGApp.directive('ngScrollSpy', function () {
	return {
		restrict: 'A',
		link: function (scope, elem, attr) {
			var sp = {
				min: -300,
				max: 50,
				onEnter: function(element, position) {
					$('.nav-top').addClass('at-top');
				},
				onLeave: function(element, position) {
					$('.nav-top').removeClass('at-top');
				}
			};
			$(elem).scrollspy(sp);
			sp.container = $('.snap-content-inner');
			$(elem).scrollspy(sp);
			
			if (App.isPhoneGap) {
				var sp = {
					min: -130,
					max: -90,
					onEnter: function(element, position) {
						$('.page-location .snap-content-inner.bg').addClass('step-two');
					},
					onLeave: function(element, position) {
						$('.page-location .snap-content-inner.bg').removeClass('step-two');
					},
					container: $('.snap-content-inner')
				};
				$(elem).scrollspy(sp);

				var sp = {
					min: -300,
					max: -130,
					onEnter: function(element, position) {
						$('.page-location .snap-content-inner.bg').addClass('step-three');
					},
					onLeave: function(element, position) {
						$('.page-location .snap-content-inner.bg').removeClass('step-three');
					},
					container: $('.snap-content-inner')
				};
				$(elem).scrollspy(sp);
			}
		}
	};
});

NGApp.directive('ngSpinner', function () {
	return {
		restrict: 'A',
		link: function (scope, elem, attr) {
			setTimeout( function(){
				elem.data( 'spinner', Ladda.create( elem.get(0) ) );	
			}, 1 );
		}
	};
});

NGApp.directive('ngAutosize', function () {
	return {
		restrict: 'A',
		link: function (scope, elem, attr) {
			setTimeout( function(){
				elem.addClass('autosize');
				elem.data( 'autosize', elem.autosize({append: "\n"}) );	
			}, 1 );
		}
	};
});

NGApp.directive('ngSimulateReadOnly', function () {
	return {
		restrict: 'A',
		link: function (scope, elem, attr) {
			if( App.isMobile() || App.isPhoneGap ){
				angular.element(elem).bind('click keyup keydown change focus', function (evt) {
					elem.val(attr.ngSimulateReadOnly);
					elem.select();
				});
			} else {
				elem.attr( 'readonly', 'readonly' );
				angular.element(elem).bind('click', function (evt) {
					elem.select();
				});
			}
		}
	};
});

NGApp.directive( 'ngFormatPhone', function( $filter ) {
	return function( scope, element, attrs ) {
		element.bind( 'keyup', function( event ) {
			element.val( $filter( 'formatPhone' )( element.val() ) );
		} );
	};
} );


/* toggle elements, and then untoggle on next mouse click. used for menu */
NGApp.directive('ngToggle', function() {
	return {
		restrict: 'A',
		link: function (scope, elem, attr) {

			elem.bind('click', function(event) {
				setTimeout(function() {
					$(document).one('click', function(e) {
						if (e.target == event.target) {
							return;
						}
						scope.$apply(function() {
							scope[attr.ngToggle] = false;
						});
					});
				},0);
	
				scope.$apply(function() {
					scope[attr.ngToggle] = true;
				});
			});
		}
	};
});