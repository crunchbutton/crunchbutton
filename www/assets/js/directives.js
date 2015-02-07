NGApp.directive( 'ngResizeText', [ function() {
	return {
		restrict: 'A',
		link: function(scope, element, attrs) {

			elements = $(element);

			var resize = function() {
				var el, _len;

				if (elements.length < 0) {
					return;
				}

				elements.css('font-size','').removeClass('resized');

				var _results = [];
				for (var _i = 0, _len = elements.length; _i < _len; _i++) {
					el = elements[_i];
					_results.push((function(el) {
						var resizeText = function() {
							var elNewFontSize;
							elNewFontSize = (parseInt($(el).css('font-size').slice(0, -2)) - 1) + 'px';
							return $(el).css('font-size', elNewFontSize);
						};
						var _results1 = [];
						while (el.scrollHeight > el.offsetHeight) {
							_results1.push(resizeText());
						}
						return _results1;
					})(el));
				}

				elements.addClass('resized');
				return _results;
			};

			setTimeout(resize,1);

			$(window).bind('resize',resize);

			scope.$on('$destroy', function() {
				$(window).unbind('resize',resize);
			});


		}
	};
}]);

NGApp.directive( 'ngBindHtmlUnsafe', [ function() {
	return function( scope, element, attr ) {
		element.addClass( 'ng-binding' ).data( '$binding', attr.ngBindHtmlUnsafe );
		scope.$watch( attr.ngBindHtmlUnsafe, function ngBindHtmlUnsafeWatchAction( value ) {
			element.html( value || '' );
		} );
	}
} ] );

NGApp.directive('addToCart', function(OrderService) {
	return {
		restrict: 'A',
		link: function(scope, element, attrs) {
			element.bind('click', function (e) {
				if (!OrderService.restaurant._open) {
					return;
				}

				if (App.isUI2()) {
					setTimeout(function() {
						var el = $(element.get(0));

						var animate = $('<div class="animate-cart-add">').css({
							top: el.position().top+20
						});
						animate.bind('transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd', function() {
							$(this).remove();
						});
						animate.appendTo('body');
							animate.css({
								top: 15,
								left: $(document).width()-50,
								width: 44,
								height: 28,
								opacity: .3
							});
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

// Loading with spinner
NGApp.directive('spinnerLoading', function() {
		return {
				restrict: 'AE',
				replace: true,
				template: '<div class="content-padding"><div class="divider"><div ng-show="!ready"><span><i class="fa fa-circle-o-notch fa-spin"></i> Loading</span></div></div></div>'
		}
});

// Hack to keep the content width 100% at cockpit2
NGApp.directive('hackExpandContent', function() {
		return {
				restrict: 'AE',
				replace: true,
				template: '<div class="divider"><table class="tb-hack"><tr><td class="td-large"></td><td></td></tr></table></div>'
		}
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
	//if( !App.isMobile() ){ return { restrict: 'E' };}

	return {
		restrict: 'A',
		require: 'ngModel',
		replace: true,
		template: '<span class="custom-checkbox fa fa-square"></span>',
		link: function (scope, elem, attrs, ctrl) {
			var label = angular.element('label[for="' + attrs.id + '"]');
			label.bind('click', function () {
				scope.$apply(function () {
					elem.toggleClass( 'checked' );
					ctrl.$setViewValue( elem.hasClass( 'checked' ) );
					if( elem.hasClass('checked') ){
						elem.removeClass('fa-square');
						elem.addClass('fa-check-square');
					} else {
						elem.addClass('fa-square');
						elem.removeClass('fa-check-square');
					}
				});
			});

			elem.bind('click', function () {
				scope.$apply(function () {
					elem.toggleClass( 'checked' );
					ctrl.$setViewValue( elem.hasClass( 'checked' ) );
					if( elem.hasClass('checked') ){
						elem.removeClass('fa-square');
						elem.addClass('fa-check-square');
					} else {
						elem.addClass('fa-square');
						elem.removeClass('fa-check-square');
					}
				});
			});

			ctrl.$render = function () {
				if (!elem.hasClass('checked') && ctrl.$viewValue) {
					elem.addClass('checked');
					elem.removeClass('fa-square');
					elem.addClass('fa-check-square');
				} else if (elem.hasClass('checked') && !ctrl.$viewValue) {
					elem.removeClass('checked');
					elem.addClass('fa-square');
					elem.removeClass('fa-check-square');
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
			if (typeof attrs.instantPassThru === 'undefined') {
				e.preventDefault();
				e.stopPropagation();
			}
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
					min: -300,
					max: -40,
					onEnter: function(element, position) {
						$('body').addClass('hidden-logo');
					},
					onLeave: function(element, position) {
						$('body').removeClass('hidden-logo');
					},
					container: $('.snap-content-inner')
				};
				$(elem).scrollspy(sp);

				var sp = {
					min: -135,
					max: -90,
					onEnter: function(element, position) {
						$('.snap-content-inner.bg').addClass('step-two');
					},
					onLeave: function(element, position) {
						$('.snap-content-inner.bg').removeClass('step-two');
					},
					container: $('.snap-content-inner')
				};
				$(elem).scrollspy(sp);

				var sp = {
					min: -300,
					max: -130,
					onEnter: function(element, position) {
						$('.snap-content-inner.bg').addClass('step-three');
					},
					onLeave: function(element, position) {
						$('.snap-content-inner.bg').removeClass('step-three');
					},
					container: $('.snap-content-inner')
				};
				$(elem).scrollspy(sp);
			}
		}
	};
});

NGApp.directive( 'ngBindOnce', function( $timeout ) {
	return {
		scope: true,
		link: function( $scope, $element ) {
				$timeout( function() { $scope.$destroy(); }, 0 );
			}
	}
} );

NGApp.directive('ngSpinner', function () {
	return {
		restrict: 'A',
		link: function (scope, elem, attr) {
			setTimeout( function(){
				var spinner = Ladda.create(elem.get(0));
				elem.data( 'spinner', spinner);
				if ( attr.spinnerAutostart && attr.spinnerAutostart != 'false' ) {
					$( elem ).click(function() {
						spinner.start();
					} );
				}
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
				elem.data( 'autosize', elem.autosize() );
			}, 800 );
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

NGApp.directive( 'modalReset', function( $rootScope ) {
	return {
		restrict: 'A',
		link: function( scope, element, attrs ){
			scope.$on( 'modalClosed', function( e, data ) {
				if( attrs.modalReset ){
					scope.$eval( attrs.modalReset );
				}
			} );
		}
	}
} );

NGApp.directive( 'geoComplete', function() {
	return {
		restrict: 'A',
		scope: { ngModel : '=', geoCompleteEnter : '&' },
		link: function( scope, element, attrs ) {
			var el = document.getElementById( attrs.id );
			if( typeof google == 'object' && google.maps && google.maps.places && google.maps.places.Autocomplete ){
				var autoComplete = new google.maps.places.Autocomplete( el, { types: [ 'establishment','geocode' ] } );
				 google.maps.event.addListener( autoComplete, 'place_changed', function() {
					$('body').scrollTop(0);
					var place = autoComplete.getPlace();
					scope.$apply( function() {
						scope.ngModel = el.value;
						// we need to give some time to scope
						setTimeout( function(){
							scope.geoCompleteEnter();
						}, 5 );
					} );
				} );
			}
		}
	};
});

NGApp.directive( 'phoneValidate', function () {
	return {
			restrict: 'A',
			require: 'ngModel',
			link: function ( scope, elem, attrs, ctrl ) {
				elem.on( 'blur', function ( evt ) {
					scope.$apply( function () {
						var val = elem.val();
						var isValid = false;
						var phoneVal = val.replace( /[^0-9]/g, '' );
						if ( phoneVal && phoneVal.length == 10) {
							var phoneVal = phoneVal.split(''), prev;
							for (x in phoneVal) {
								if (!prev) {
									prev = phoneVal[x];
									continue;
								}
								if (phoneVal[x] != prev) {
									isValid = true;
								}
							}
						}
						ctrl.$setValidity( 'phoneValidate', isValid );
					} );
				} );
			}
	};
});

NGApp.directive( 'socialSecurityNumberValidate', function () {
	return {
			restrict: 'A',
			require: 'ngModel',
			link: function ( scope, elem, attrs, ctrl ) {
				elem.on( 'blur', function ( evt ) {
					scope.$apply( function () {
						var val = elem.val().replace(/[^0-9]/g,'');
						var regex = new RegExp("^(?!(219)(09)(9999)|(078)(05)(1120))(?!666|000|9\\d{2})\\d{3}(?!00)\\d{2}(?!0{4})\\d{4}$");
						var isValid = regex.test(val);
						ctrl.$setValidity( 'socialSecurityNumberValidate', isValid );
					} );
				} );
			}
	};
});




NGApp.directive('isBiggerThanZero', function() {
	return {
		restrict: 'A',
		require: 'ngModel',
		link: function(scope, elem, attrs, ngModel) {

			// observe the other value and re-validate on change
			attrs.$observe( 'isBiggerThanZero', function () {
				validate();
			});

			scope.$watch(attrs.ngModel, function() { validate(); } );

			var validate = function() {
				var isValid = false;
				// if it is false it means it should not be validated
				if( attrs.isBiggerThanZero === 'true' || attrs.isBiggerThanZero === true ){
					if( parseInt( ngModel.$viewValue ) > 0 ){
						isValid = true;
					}
				} else {
					isValid = true;
				}
				ngModel.$setValidity('isBiggerThanZero', isValid );
			};
		}
	}
});

NGApp.directive('equals', function() {
	return {
		restrict: 'A', // only activate on element attribute
		require: '?ngModel', // get a hold of NgModelController
		link: function(scope, elem, attrs, ngModel) {

			if(!ngModel) return; // do nothing if no ng-model

			elem.bind( 'blur', function ( evt ) {
				scope.$apply( function () {
					validate();
				} );
			} );

			elem.bind( 'change', function ( evt ) {
				scope.$apply( function () {
					validate();
				} );
			} );

			// watch own value and re-validate on change
			scope.$watch(attrs.ngModel, function() {
				validate();
			});

			// observe the other value and re-validate on change
			attrs.$observe('equals', function (val) {
				validate();
			});

			attrs.$observe(ngModel, function (val) {
				validate();
			});

			var validate = function() {
				var val1 = ngModel.$viewValue;
				var val2 = attrs.equals;
				if( val1 && val2 ){
					ngModel.$setValidity('equals', val1 === val2);
				} else {
					ngModel.$setValidity('equals', true );
				}
			};
		}
	}
});


NGApp.directive( 'isUnique', function( $resource, $timeout ) {
	return {
			restrict: 'A',
			require: 'ngModel',
			link: function ( scope, elem, attrs, ctrl ) {

				elem.on( 'blur', function ( evt ) {

					scope.$apply( function () {
						var val = elem.val();

						var verify = false;
						switch( attrs.isUnique ){
							case 'email':
							case 'phone':
							case 'login':
								verify = true;
								break;
						}

						if( !verify ){
							ctrl.$setValidity('isUnique', false );
							return;
						}

						if( val == '' ){
							return;
						}

						if( scope.driver && scope.driver.name ){
							var name = scope.driver.name;
						} else {
							var name = null;
						}

						if( scope.driver && scope.driver.id_admin ){
							var id_admin = scope.driver.id_admin;
						} else {
							var id_admin = null;
						}

						var unique = $resource( App.service + 'unique/:property/:value', { property: '@property', value: '@value' }, { 'check' : { 'method': 'POST', params : {} } } );
						unique.check( { property: attrs.isUnique, value: val, name: name, id_admin: id_admin }, function( json ){
							if( json && json.canIUse ){
								ctrl.$setValidity( 'isUnique', true );
							} else {
								ctrl.$setValidity( 'isUnique', false );
							}
						} );
					} );
				} );
			}
	};
});


NGApp.directive('splashPositionFix', function() {
	return {

		restrict: 'A',
		link: function( $scope, elem, attrs, ctrl ) {

			$scope.$watch( 'windowHeight', function( newValue, oldValue, scope ) {
				if( newValue != oldValue ){
					setTimeout( function(){
						App.rootScope.reload();
					}, 100 );
				}
			});

			var bottomText = false;
			var greenOrange = false;

			var fixPosition = function(){

				var element = function( selector ){
					var element = angular.element( selector );
					return {
						top: function(){
							var marginTop = parseInt( element.css( 'marginTop' ) );
							return parseInt( element.offset().top + marginTop );
						},
						reset : function(){
							element.css( { 'marginTop' : 0 } );
							element.css( { 'top' : element.offset().top } );
						},
						height: function(){
							if ( !element.attr( 'fixed-height' ) ){
								element.attr( 'fixed-height', element.height() );
								element._height = element.height();
							}
							return parseInt( element.attr( 'fixed-height' ) );
						},
						bottom: function(){
							return parseInt( this.top() ) + parseInt( this.height() );
						},
						setTop: function( top ){
							element.css( { 'marginTop' : top } );
						}
					}
				};

				// initialize objects
				if( !bottomText && !greenOrange ){
					bottomText = element( '.splash-bottom' );
					greenOrange = element( '.about-green-orange-scene' );
				}
				bottomText.reset();
				greenOrange.reset();

				var distance = bottomText.top() - greenOrange.bottom();
				var maxDistance = 70;
				var minDistance = 15;
				var step = 5;

				var windowHeight = $scope.windowHeight - maxDistance;

				var calcPosition = function(){
					var ok = false;
					var top = maxDistance;
					var watchDog = 100;
					while( !ok ){
						var test_top = bottomText.bottom() + top + minDistance;
						if( test_top <= windowHeight ){
							bottomText.setTop( top > 0 ? top : 0 );
							ok = true;
						}
						top -= step;
						watchDog--;
						if( watchDog <= 0 ){ ok = true; continue; }
					}
				}
				var distance = bottomText.top() - greenOrange.bottom();
				if( distance < maxDistance ){
					calcPosition();
				}
				// get the distance again
				var distance = bottomText.top() - greenOrange.bottom();
				if( distance < minDistance ){
					greenOrange.setTop( -minDistance );
				}
				return;
			}
			setTimeout( function(){ fixPosition();}, 10 );
		}
	}
});

NGApp.directive( 'ignoreMouseWheel', function( $rootScope ) {
	return {
		restrict: 'A',
		link: function( scope, element, attrs ){
			element.bind('mousewheel', function ( event ) {
				element.blur();
			} );
		}
	}
} );

NGApp.directive( 'positiveOrNegativeColor', function( $rootScope ) {
	return {
		restrict: 'A',
		link: function( scope, element, attrs ){
			attrs.$observe( 'positiveOrNegativeColor', function( value ) {
				element.removeClass( 'positive negative neutral' );
				var value = parseFloat( attrs.positiveOrNegativeColor );
				if( value > 0 ){
					element.addClass( 'positive' );
				} else if( value < 0 ){
					element.addClass( 'negative' );
				} else {
					element.addClass( 'neutral' );
				}
			} );
		}
	}
} );

NGApp.directive( 'navigationBack', function() {
	return {
			restrict: 'AE',
			replace: true,
			template: '<div ng-click="navigation.back();" class="back-button orange link"><i class="fa fa-chevron-left"></i> Back</div>',
			link: function (scope, elem, attrs, ctrl) {
					var navigation = scope.navigation;
			}
		}
} );
