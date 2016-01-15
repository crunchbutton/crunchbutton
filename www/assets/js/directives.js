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
				var add = false;
				if (OrderService.restaurant._open) {
					add = true;
				}
				if (OrderService.restaurant.force_pre_order) {
					add = true;
				}
				if( !add ){
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
						var pos;
						if (App.rootScope.isMobileWidth) {
							pos = {
								top: 15,
								left: $(document).width()-50,
								width: 44,
								height: 28,
								opacity: .3
							};
						} else {
							pos = {
								top: 300,
								left: $(document).width()*.7,
								width: 44,
								height: 28,
								opacity: .3
							};
						}
						animate.css(pos);
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

// Restaurant is closed
NGApp.directive( 'restaurantIsClosed', function () {
	return {
		restrict: 'A',
		templateUrl: '/assets/view/restaurant.closed.inline.html'
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

NGApp.directive('ngSpinner', function ($compile) {
	return {
		restrict: 'A',
		link: function (scope, elem, attr) {
			if (App.minimalMode || !attr.ngSpinner) {
				return;
			}

			var color = attr.spinnerColor || '#000';
			var scale = attr.spinnerScale || 1;
			var css = null;
			try {
				if (attr.spinnerCss) {
					css = JSON.parse(attr.spinnerCss);
				}
			} catch(e) {
				console.error('Invalid css for ng-spinner');
			}

			var spun = false;

			var spin = '<svg width="'+(elem.width())+'px" height="'+(elem.height())+'px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" class="uil-default"><rect x="0" y="0" width="100" height="100" fill="none" class="bk"></rect><rect  x="46.5" y="40" width="7" height="20" rx="5" ry="5" fill="'+color+'" transform="rotate(0 50 50) translate(0 -30)">  <animate attributeName="opacity" from="1" to="0" dur="1s" begin="0s" repeatCount="indefinite"/></rect><rect  x="46.5" y="40" width="7" height="20" rx="5" ry="5" fill="'+color+'" transform="rotate(30 50 50) translate(0 -30)">  <animate attributeName="opacity" from="1" to="0" dur="1s" begin="0.08333333333333333s" repeatCount="indefinite"/></rect><rect  x="46.5" y="40" width="7" height="20" rx="5" ry="5" fill="'+color+'" transform="rotate(60 50 50) translate(0 -30)">  <animate attributeName="opacity" from="1" to="0" dur="1s" begin="0.16666666666666666s" repeatCount="indefinite"/></rect><rect  x="46.5" y="40" width="7" height="20" rx="5" ry="5" fill="'+color+'" transform="rotate(90 50 50) translate(0 -30)">  <animate attributeName="opacity" from="1" to="0" dur="1s" begin="0.25s" repeatCount="indefinite"/></rect><rect  x="46.5" y="40" width="7" height="20" rx="5" ry="5" fill="'+color+'" transform="rotate(120 50 50) translate(0 -30)">  <animate attributeName="opacity" from="1" to="0" dur="1s" begin="0.3333333333333333s" repeatCount="indefinite"/></rect><rect  x="46.5" y="40" width="7" height="20" rx="5" ry="5" fill="'+color+'" transform="rotate(150 50 50) translate(0 -30)">  <animate attributeName="opacity" from="1" to="0" dur="1s" begin="0.4166666666666667s" repeatCount="indefinite"/></rect><rect  x="46.5" y="40" width="7" height="20" rx="5" ry="5" fill="'+color+'" transform="rotate(180 50 50) translate(0 -30)">  <animate attributeName="opacity" from="1" to="0" dur="1s" begin="0.5s" repeatCount="indefinite"/></rect><rect  x="46.5" y="40" width="7" height="20" rx="5" ry="5" fill="'+color+'" transform="rotate(210 50 50) translate(0 -30)">  <animate attributeName="opacity" from="1" to="0" dur="1s" begin="0.5833333333333334s" repeatCount="indefinite"/></rect><rect  x="46.5" y="40" width="7" height="20" rx="5" ry="5" fill="'+color+'" transform="rotate(240 50 50) translate(0 -30)">  <animate attributeName="opacity" from="1" to="0" dur="1s" begin="0.6666666666666666s" repeatCount="indefinite"/></rect><rect  x="46.5" y="40" width="7" height="20" rx="5" ry="5" fill="'+color+'" transform="rotate(270 50 50) translate(0 -30)">  <animate attributeName="opacity" from="1" to="0" dur="1s" begin="0.75s" repeatCount="indefinite"/></rect><rect  x="46.5" y="40" width="7" height="20" rx="5" ry="5" fill="'+color+'" transform="rotate(300 50 50) translate(0 -30)">  <animate attributeName="opacity" from="1" to="0" dur="1s" begin="0.8333333333333334s" repeatCount="indefinite"/></rect><rect  x="46.5" y="40" width="7" height="20" rx="5" ry="5" fill="'+color+'" transform="rotate(330 50 50) translate(0 -30)">  <animate attributeName="opacity" from="1" to="0" dur="1s" begin="0.9166666666666666s" repeatCount="indefinite"/></rect></svg>';
			var spinner = angular.element('<div class="inline-spinner"></div>');
			var scaler = angular.element('<div class="inline-spinner-scaler"></div>');
			var user = angular.element('<div class="inline-spinner-user"></div>');
			var spinwrap = angular.element('<div class="inline-spinner-spinwrap"></div>');
			var spinnerWrapper = angular.element('<div class="inline-spinner-wrapper"></div>');

			scope.$watch(attr.ngSpinner, function(val) {
				if (val) {
					if (!spun) {
						spun = true;
						spinner.append(spin);
					}
					spinnerWrapper.addClass('loading');
				} else {
					spinnerWrapper.removeClass('loading');
				}
			});

			scaler.css({
				'transform': 'scale('+scale+')',
				'margin-top': '-' + elem.height() + 'px',
				'height': elem.height(),
				'width': elem.width()
			});

			if (css) {
				user.css(css);
			}

			spinwrap.append(user);
			user.append(scaler);
			scaler.append(spinner);

			elem.replaceWith(spinnerWrapper);
			spinnerWrapper.append(elem);
			spinnerWrapper.append(spinwrap);
			elem.addClass('inline-spinner-content');
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
			}, 100 );
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
					var part = place.name.split( ',' )[0].toLowerCase().replace(/( )?the/);
					var val = '';

					if (App && App.aliases && App.aliases[part]) {
						val = App.aliases[part].name_alt;
					} else if (App && App.communities && App.communities[part.replace(/ /g, '-')]) {
						val = App.communities[part.replace(/ /g, '-')].name;
					} else {
						val = el.value;
					}

					val = val.replace(/, United States/,'').replace(/, USA/,'');

					scope.$apply( function() {
						scope.ngModel = val;
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

NGApp.directive( 'twitterButton', function( AccountService ) {
	return {
			restrict: 'AE',
			replace: true,
			template: '<div></div>',
			link: function (scope, elem, attrs, ctrl) {
				scope.$watch( 'twitterUrl', function( newValue, oldValue, scope ){
					if( newValue != oldValue ){
						return;
					}
					elem[0].innerHTML = '';
					var url = scope.twitterUrl ? scope.twitterUrl : attrs.url ? attrs.url : 'http://crunchbutton.com';
					var text = scope.twitterText ? scope.twitterText : attrs.text ? attrs.text : 'Crunchbutton';
					var hashtags = scope.twitterHashtags ? scope.twitterHashtags : attrs.hashtags ? attrs.hashtags : 'crunchbutton';
					twttr.widgets.createShareButton(
						url,
						elem[0],
						function (el) {
							if( scope.twitterButtonCreated ){
								scope.twitterButtonCreated( el );
							}
						},
						{ count: ( attrs.count ? attrs.count : 'none' ), text: text, hashtags: hashtags }
					);
					twttr.events.bind( 'tweet', function ( event ) {
						if( scope.twitterTweet ){
							scope.twitterTweet( event );
						}
					} );
				} );
			}
		}
} );

NGApp.directive('passwordStrength', function() {
	return {
		restrict: 'A',
		require: '?ngModel',
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

			scope.password_strength = 0;;

			var validate = function() {
				var score = 0;
				var pass = ngModel.$viewValue;
				if ( pass ){
					// award every unique letter until 5 repetitions
					var letters = new Object();
					for (var i=0; i<pass.length; i++) {
							letters[pass[i]] = (letters[pass[i]] || 0) + 1;
							score += 5.0 / letters[pass[i]];
					}

					// bonus points for mixing it up
					var variations = {
							digits: /\d/.test(pass),
							lower: /[a-z]/.test(pass),
							upper: /[A-Z]/.test(pass),
							nonWords: /\W/.test(pass),
					}

					variationCount = 0;
					for (var check in variations) {
							variationCount += (variations[check] == true) ? 1 : 0;
					}
					score += (variationCount - 1) * 10;
				}

				scope.password_strength = parseInt(score);

				if ( scope.password_strength > 80){
					scope.password_strength_message = "strong";
				} else
				if ( scope.password_strength >= 60){
					scope.password_strength_message = "good";
				} else
				if ( scope.password_strength >= 30){
					scope.password_strength_message = "weak";
				}
				console.log('scope.password_strength',scope.password_strength);
				if( score ){
					ngModel.$setValidity( 'passwordStrength', ( parseInt( score ) >= 45 || ( pass.length >= 7 ) ) );
				}
			};
		}
	}
});
