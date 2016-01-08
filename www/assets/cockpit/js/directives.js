
NGApp.directive('chatSend', function(TicketViewService) {
	return {
		restrict: 'A',
		link: function(scope, element, attrs) {
			element.bind('keydown keypress', function (e) {
				if (e.which == 13) {
					if( element.val() != '' ){
						TicketViewService.send(element.val());
						e.preventDefault();
						element.val('');
					}
				} else {
					//TicketViewService.typing(element.val());
				}
			});
		}
	};
});

NGApp.directive('ticketMessagesScroll', function( $rootScope ) {
	return {
		link: function( scope, elem, attrs ) {
			elem.on( 'scroll', function(){
				var distance = 100;
				if( elem[0].scrollTop <= distance ){
					// $rootScope.$broadcast( 'loadMoreMessages' );
				}
			} );
		}
	};
} );

NGApp.directive('supportChatContents', function( $window, $rootScope ) {
	return {
		link: function( scope, elem, attrs ) {
			scope.$on( 'triggerSideViewTicket', function(e, data) {
				if( App.isMobile() ){
					setTimeout( function(){ fixHeight() }, 500 );
				}
			});

			var fixHeight = function(){
				var height = $window.innerHeight + 8;
				var heightLeft = height - parseInt( angular.element('.nav-top').height() ) -
																	parseInt( angular.element('.support-chat-header').height() ) -
																	parseInt( angular.element('.support-side-chat-row').height() );
				$( '.support-chat-contents' ).height( heightLeft );
			}

		}
	};
} );

NGApp.directive('fitHeight', function() {
	return {
		restrict: 'A',
		link: function(scope, element, attrs) {

			var setHeight = function() {
				angular.element(element).height(angular.element(window).height() - angular.element(element).position().top - 10);
			};

			angular.element(window).on('resize', setHeight);

			scope.$on('$destroy', function() {
				angular.element(window).off('resize', setHeight);
			});

			setHeight();

			scope.$watch('view', setHeight);
		}
	};
});

NGApp.directive('pageKey', function() {
	return {
		restrict: 'A',
		link: function(scope, element, attrs) {
			$(document.body).bind('keydown keypress', function (e) {
				if (document.activeElement.tagName == 'INPUT' || document.activeElement.tagName == 'SELECT' || document.activeElement.tagName == 'TEXTAREA') {
					if (e.which == 27 || e.which == 13) {
						document.activeElement.blur();
					}
					return;
				}

				// next
				if (e.which == 39 && (scope.query.page < scope.pages || scope.more)) {
					scope.$apply(function() {
						scope.setPage(scope.query.page+1);
					});

				// prev
				} else if (e.which == 37 && scope.query.page > 1) {
					scope.$apply(function() {
						scope.setPage(scope.query.page-1);
					});
				}
			});
		}
	};
});

NGApp.directive('tabSelect', function(MainNavigationService) {
	return {
		restrict: 'A',
		link: function(scope, element, attrs) {
			var go = function() {
				MainNavigationService.link(attrs.tabSelect);
				scope.$apply();
			};
			$(element).bind('dblclick', function (e) {
				go();

			});
			$(element).bind('keydown keypress', function (e) {
				if (e.which == 13) {
					go();
				}
			});
		}
	};
});


NGApp.directive('profilePreference', function (AccountService, $http, $rootScope ) {
	return {
		restrict: 'A',
		templateUrl: 'assets/view/general-profile-preference.html',
		scope: {
			content: '@',
			key: '@',
			type: '@'
		},
		controller: function ($scope) {
			$scope.account = AccountService;
			$scope.change = function() {

				var value = !AccountService.user.prefs[$scope.key];
				AccountService.user.prefs[$scope.key] = value;

				$rootScope.$broadcast('user-preference', {key: $scope.key, value: value});

				$http({
					method: 'POST',
					url: App.service + 'config',
					data: {key: $scope.key, value: value}
				});
			}
		}
	};;
});



/**
 * ResourceFactory creates cancelable resources.
 * Work based on: http://stackoverflow.com/a/25448672/1677187
 * which is based on: https://developer.rackspace.com/blog/cancelling-ajax-requests-in-angularjs-applications/
 */
/* global array */
NGApp.factory('ResourceFactory', ['$q', '$resource',

	function($q, $resource) {

		function abortablePromiseWrap(promise, deferred, outstanding) {

			promise.then(function() {
				deferred.resolve.apply(deferred, arguments);
			});

			promise.catch(function() {
				deferred.reject.apply(deferred, arguments);
			});

			/**
			 * Remove from the outstanding array
			 * on abort when deferred is rejected
			 * and/or promise is resolved/rejected.
			 */
			deferred.promise.finally(function() {
				array.remove(outstanding, deferred);
			});
			outstanding.push(deferred);
		}

		var cancelers = [];

		function createResource(url, options, actions) {

			var resource;
			var outstanding = [];
			actions = actions || {};

			Object.keys(actions).forEach(function(action) {
				var canceller = $q.defer();
				actions[action].timeout = canceller.promise;
				cancelers[action] = canceller;
			});

			resource = $resource(url, options, actions);

			var isLoading = null;

			Object.keys(actions).forEach(function(action) {

				var method = resource[action];

				resource[action] = function() {

					if (isLoading) {
						isLoading.reject('Aborted');
						cancelers[action].resolve();
						var canceller = $q.defer();
						actions[action].timeout = canceller.promise;
						cancelers[action] = canceller;
					}

					var deferred = $q.defer(),

					promise = method.apply(null, arguments).$promise;

					abortablePromiseWrap(promise, deferred, outstanding);

					isLoading = deferred;

					return {
						$promise: deferred.promise,
						abort: function() {
							// i dont think this is called unless we resolve the primise, which were not doing
							console.error('Resource Aborted');
							cancelers[action].resolve();
							var canceller = $q.defer();
							actions[action].timeout = canceller.promise;
							cancelers[action] = canceller;
							deferred.reject('Aborted');
						}
					};
				};
			});

			/**
			 * Abort all the outstanding requests on
			 * this $resource. Calls promise.reject() on outstanding [].
			 */
			resource.abortAll = function() {
				for (var i = 0; i < outstanding.length; i++) {
					outstanding[i].reject('Aborted all');
				}
				outstanding = [];
			};

			return resource;
		}

		return {
			createResource: function (url, options, actions) {
				return createResource(url, options, actions);
			}
		};
	}
]);

NGApp.filter('formatTimezone', function() {
	return function(timezone) {
		if (!timezone) {
			return '';
		}
		var tz = timezone.split('/');
		return tz[1].replace('_',' ');
	};
});


NGApp.directive( 'driverDocsUpload', function ($rootScope, FileUploader) {
	return {
		restrict: 'AE',
		replace: false,
		scope: true,
		link: function ( scope, elem, attrs, ctrl ) {

			var button = elem.find('button')[0];

			var message = elem.find( 'div' );

			scope.init = true;

			if (!window.Ladda) {
				return;
			}

			var l = Ladda.create(button);

			angular.element(button).on('click', function() {
				angular.element(elem.find('input')[0]).click();
			});

			scope.uploader = new FileUploader({
				url: '/api/driver/documents/upload/',
				autoUpload: true
			});

			scope.uploader.onBeforeUploadItem = function() {
				l.start();
				if( App.isMobile() ){
					message.html( '<i class="fa fa-refresh fa-spin"></i> Uploading' );
				}
			};

			scope.uploader.onSuccessItem = function(fileItem, response, status, headers) {
				if( response.success ){
					$rootScope.$broadcast( 'driverDocsUploaded', { success: true } );
				} else {
					$rootScope.$broadcast( 'driverDocsUploaded', { error: true } );
				}
				message.html( '' );
				scope.uploader.clearQueue();
				l.stop();
			};

			scope.uploader.onErrorItem = function (fileItem, response, status, headers) {
				$rootScope.$broadcast( 'driverDocsUploadedError', {} );
				scope.uploader.clearQueue();
				message.html( '' );
				l.stop();
			};

			scope.$watch( 'uploader.progress', function( newValue, oldValue, scope ) {
				if( App.isMobile() && newValue ){
					message.html( 'Uploading ' +  newValue + '%' );
					if( newValue >= 100 ){
						message.html( '<i class="fa fa-refresh fa-spin"></i> Processing' );
					}
				}

			});
		}
	}
});


NGApp.directive( 'resourceUpload', function ($rootScope, FileUploader) {
	return {
		restrict: 'AE',
		scope: true,
		link: function ( scope, elem, attrs, ctrl ) {

			var upload_button = elem.find('.upload')[0];
			var save_button = angular.element('.save-upload')[0];

			scope.init = true;

			if (!window.Ladda) {
				return;
			}

			var l = null;

			var path = attrs.path ? attrs.path : '/api/resource/upload/';

			angular.element(upload_button).on('click', function() {
				angular.element(elem.find('input')[0]).click();
			});

			scope.uploader = new FileUploader({
				url: path,
				autoUpload: false
			});

			$rootScope.$on( 'triggerStartUpload', function(e, data) {
				l = Ladda.create( save_button );
				scope.uploader.uploadAll();
				try{ l.start();} catch(e){}
			});

			scope.uploader.onAfterAddingFile = function(fileItem) {
				$rootScope.$broadcast( 'triggerUploadFileAdded', fileItem.file.name );
			};

			scope.uploader.onSuccessItem = function(fileItem, response, status, headers) {
				$rootScope.$broadcast( 'resourceUpload', { id_driver_document: response.id_driver_document, response: response } );
				scope.uploader.clearQueue();
				watchProgress = false;
				try{ l.stop();} catch(e){}
			};

			scope.uploader.onProgressAll = function( progress ) {
				var progress = ( scope.uploader.progress / 100 );
				try{ l.setProgress( progress );} catch(e){}
			};

			scope.uploader.onErrorItem = function (fileItem, response, status, headers) {
				$rootScope.$broadcast( 'resourceUploadError', {} );
				scope.uploader.clearQueue();
				watchProgress = false;
				l.stop();
			};
		}
	}
});


NGApp.directive( 'restaurantImageUpload', function ($rootScope, FileUploader) {
	return {
		restrict: 'AE',
		replace: false,
		scope: true,
		link: function ( scope, elem, attrs, ctrl ) {

			var id_restaurant = attrs.idRestaurant;
			var button = elem.find('button')[0];

			scope.init = true;

			if (!window.Ladda) {
				return;
			}

			var l = Ladda.create(button);

			angular.element(button).on('click', function() {
				angular.element(elem.find('input')[0]).click();
			});

			scope.uploader = new FileUploader({
				url: '/api/restaurant/' + id_restaurant + '/image',
				autoUpload: false
			});

			scope.uploader.onAfterAddingFile = function(){
				App.agreementBox(
				'I am certain that we have the rights to this image and it is not violating any copyright. There is no packaging or restaurant branding in the image. We either took this picture ourselves or are 100% certain we have the rights to it.',
				'Agreement',
				function(){
					scope.uploader.uploadAll();
				},
				function(){
					scope.uploader.clearQueue();
				} );
			}

			scope.uploader.onBeforeUploadItem = function() {
				l.start();
			};

			scope.uploader.onSuccessItem = function(fileItem, response, status, headers) {
				var hasImage = ( $('#restaurant-image-thumb').attr('hasImage') == 'true' );
				l.stop();
				if( hasImage ){
					App.alert( 'Image uploaded!' );
					var img = new Image;
					img.src = $('#restaurant-image-thumb').get(0).src + '?' + new Date();
					$('#restaurant-image-thumb').get(0).src = img.src;
				} else {
					window.location.reload();
				}


			};

			scope.uploader.onErrorItem = function (fileItem, response, status, headers) {
				//scope.uploader.clearQueue();
				l.stop();
			};
		}
	}
});


NGApp.directive('uiTab', function ( $routeParams ) {
		return {
				require: '^uiTabs',
				link: function ( scope, element, attrs, controller ) {
						var isDefault = attrs.default;
						if( $routeParams.tab ){
							if( $routeParams.tab == attrs.id ){
								isDefault = true;
							} else {
								isDefault = false;
							}
						}
						controller.addTab( {
								id: attrs.id,
								title: attrs.title,
								icon: attrs.icon,
								default: isDefault,
								path: attrs.path,
								method: attrs.method
						} );
				}
		};
});


NGApp.directive('uiTabs', function ( $compile, $timeout ) {
	var template = '<div>' +
										'<div class="ui-tab-header-wrap"><ul class="ui-tab-header">' +
											'<li ng-class="{\'ui-tab-header-active\': _current.id == tab.id}" ng-repeat="tab in _tabs">' +
												'<span class="clickable" ng-click="setCurrent( tab );"><i class="fa fa-{{tab.icon}}" ng-hide="!tab.icon"></i>{{ tab.title }}</span>' +
												'&nbsp;&nbsp;'+
												'<span ng-if="tab.method" ng-class="{ \'transparent\':( _current.id != tab.id ) }" class="clickable" ng-click="loadContent( tab );"><i ng-class="{\'fa-spin\':tab.isLoading}" class="fa fa-refresh"></i></span>' +
											'</li>' +
										'</ul></div>' +
										'<ul class="ui-tab-content">' +
											'<li ng-repeat="tab in _tabs" ng-if="_current.id == tab.id">' +
												'<ng-include src=tab.path></ng-include>' +
											'</li>' +
										'</ul>' +
									'</div>';
	return {
		restrict: 'E',
		scope: true,
		controller: function ( $scope ) {

				var current = null;
				var tabs = [];
				var preloadTimer = 300;

				this.getTabs = function () {
						return tabs;
				};
				this.addTab = function ( tab ) {
					if( tab.default ){
						this.setCurrent( tab );
					} else {
						if( tab.preload ){
							// pre load tab content
							var those = this;
							$timeout( function(){ those.loadContent( tab, true ); }, preloadTimer );
							preloadTimer += preloadTimer;
						}
					}
					tabs.push( tab );
				};
				this.loadContent = function( tab, force ){
					if( tab.method && ( !tab.method_called || force ) ){
						try{
							eval( '$scope.' + tab.method + '()' );
							tab.method_called = true;
						} catch(e){
							console.log( 'ui-tabs:error: ', e );
						}
					}
				};
				this.setCurrent = function( tab ){
					current = tab;
					this.loadContent( tab );
				}
				this.getCurrent = function(){
					return current;
				}
		},
		link: function (scope, element, attrs, controller) {

			scope.$watch( controller.getTabs, function ( tab ) {
				scope._tabs = tab;
			} );

			scope.$watch( controller.getCurrent, function ( tab ) {
				scope._current = tab;
			} );

			scope.setCurrent = function( tab ){
				controller.setCurrent( tab );
			};

			scope.loadContent = function( tab ){
				controller.loadContent( tab, true );
				tab.isLoading = true;
				$timeout( function(){ tab.isLoading = false; }, 1000 );
			}

			element.children().css( 'display', 'none' );
			var tabs = angular.element( template );
			element.append( tabs );
			$compile( tabs )( scope );
		}
	}
});

NGApp.directive( 'spinnerActionButton', function ( $parse ) {
	return {
		restrict: 'E',
		replace: true,
		scope: {
			buttonTitle: '@',
			isRunning: '@',
			method:'&action'
		},
		template: '<a href ng-click="run($event)">' +
								'<button class="button button-small button-empty" ng-class="{\'button-green\':!isRunning}" >' +
									'<i class="fa fa-check" ng-if="!isRunning"></i><i class="fa fa-spinner fa-spin" ng-if="isRunning"></i>&nbsp;&nbsp;{{buttonTitle}}' +
								'</button>' +
							'</a>',
		link: function ( scope, elem, attrs, ctrl ) {
			scope.title = attrs.title;
			scope.isRunning = false;
			var expressionHandler = scope.method();
			scope.run = function(e){
				if( !scope.isRunning ){
					scope.isRunning = true;
					expressionHandler( function(){ scope.isRunning = false; }, e);
				}
			}
		}
	}
});

NGApp.directive('eatClick', function() {
	return function(scope, element, attrs) {
		$(element).click(function(event) {
			event.preventDefault();
		});
	}
});

NGApp.directive('imgListViewSrc', function( $parse ) {
	return {
		restrict: 'A',
		scope: {
			image:'=imgListViewSrc'
		},

		link: function(scope, element, attrs) {

			scope.$watch('image', function() {
				update();
			});

			var error = function(){
				element.html( '<div ng-if="!restaurant.hasImage" class="customer-image ' + attrs.imgNull + '"></div>' );
			}
			var success = function( src ){
				element.html( '<img class="customer-image" src="' + src + '"/>' );
			}

			var loading = function(){
				// implement some loading image here
			}

			var update = function(){
				if( scope.image ){
					var img = new Image();
					img.src = scope.image;
					img.onload = function(){
						success( img.src );
					};
					img.onerror = function(){
						error();
					};
				} else {
					error();
				}
			}

		}
}
});


NGApp.directive( 'permalinkValidation', function () {
	return {
			require: 'ngModel',
			link: function(scope, elem, attr, ngModel) {
				scope.$watch( attr.ngModel, function() { validate(); } );
				var validate = function() {
					var val = elem.val();
					var isValid = true;
					if(!/^[-a-z\d]+$/.exec(val)){
						isValid = false;
					}
					ngModel.$setValidity( 'permalinkValidation', isValid );
				};
			}
	};
});

NGApp.directive( 'shiftHoursValidation', function () {
	return {
			require: 'ngModel',
			link: function(scope, elem, attr, ngModel) {
				scope.$watch( attr.ngModel, function() { validate(); } );
				var validate = function() {
					var val = elem.val();
					var isValid = false;
					if( val ){
						segment = val.replace( /\(.*?\)/g, '' );
						if( /^ *(\d+)(?:\:(\d+))? *(am|pm) *(?:to|-) *(\d+)(?:\:(\d+))? *(am|pm) *$/i.exec( segment ) ) {
							isValid = true;
						}
					}
					ngModel.$setValidity( 'shiftHoursValidation', isValid );
				};
			}
	};
});

NGApp.directive( 'restaurantHoursValidation', function () {
	return {
			require: 'ngModel',
			link: function(scope, elem, attr, ngModel) {
				scope.$watch( attr.ngModel, function() { validate(); } );
				var validate = function() {
					var val = elem.val();
					var isValid = false;
					if( /^(?: *|Closed)$/i.exec( val )){
						isValid = true;
					}
					if( !isValid ){
						isValid = true;
						val = val.replace(/\(.*?\)/g, '');
						segments = val.split(/(?:and|,)/);
						for(i in segments) {
							if(!/^ *(\d+)(?:\:(\d+))? *(am|pm) *(?:to|-) *(\d+)(?:\:(\d+))? *(am|pm) *$/i.exec(segments[i])) {
								isValid = false;
							}
						}
					};
					ngModel.$setValidity( 'restaurantHoursValidation', isValid );
				};
			}
	};
});

NGApp.directive( 'restaurantAddress', function () {
	return {
			restrict: 'A',
			require: 'ngModel',
			link: function ( scope, elem, attrs, ctrl ) {
				elem.on( 'blur', function ( evt ) {

					var address = elem.val();

					g = new google.maps.Geocoder();
					g.geocode({address:address},function(r,s) {
						if(s === 'ZERO_RESULTS') return;
						if(s !== 'OK') {
							App.alert('Geocoding error.');
							console.log(s);
							console.log(r);
							return;
						}
						if(!r || !r.length) { return; }
						console.log('scope',scope);
						scope.restaurant.address = r[0].formatted_address;
						scope.restaurant.loc_lat = r[0].geometry.location.lat();
						scope.restaurant.loc_long = r[0].geometry.location.lng();
					});

				} );
			}
	};
});

NGApp.directive( 'stickyBottom', function ( $document ) {
	return {
			restrict: 'A',
			link: function ( scope, elem, attrs, ctrl ) {

				$document.bind('scroll', function () {
					setHeight();
        });

				angular.element(elem).css( 'position', 'fixed' );
				var el_height = attrs.stickyBottom;

				if( !angular.element(elem).attr( 'wall' ) ){
					angular.element(elem).after( '<div style="height:' + el_height + 'px;">&nbsp;</div>' );
					angular.element(elem).attr( 'wall', true );
				}

				var setHeight = function() {
					var scrollTop = angular.element(document).scrollTop();
					var height = angular.element(document).innerHeight();
					var canvas = angular.element(window).height();
					var top = canvas + scrollTop - el_height;
					top = top + 'px';
					angular.element(elem).css( 'top', top );
					angular.element(elem).addClass('sticky-bottom')
				};

				angular.element(window).on('resize', setHeight);

				scope.$on('$destroy', function() {
					angular.element(window).off('resize', setHeight);
				});

				setHeight();
			}
	};
});

NGApp.directive( 'stickyHeader', function ( $rootScope, $document ) {
	return {
			restrict: 'A',

			link: function ( scope, elem, attrs, ctrl ) {

				var process = function(){

					setTimeout( function(){

						var table = angular.element( elem );

						var thead = table.find( 'thead' );
						var tbody = table.find( 'tbody' );

						table.css( 'width', '100%' );
						tbody.css( 'width', '100%' );
						thead.css( 'width', '100%' );

						if( App.isNarrowScreen() ){
							thead.css( 'display', 'inherit' );
							thead.css( 'width', 'auto' );

							tbody.css( 'display', 'inherit' );
							tbody.css( 'width', 'auto' );
							tbody.css( 'overflow-y', 'visible' );
							tbody.css( 'height', 'auto' );
							return;
						}

						var tableWidth = table.width();
						if( tableWidth < 200 ){
							return;
						}

						var tbodyLine = angular.element(tbody).find( 'tr' )[ 0 ];
						var tbodyColumns = angular.element(tbodyLine).find( 'td' );

						var theadLine = angular.element(thead).find( 'tr' )[ 0 ];
						var theadColumns = angular.element(theadLine).find( 'th' );

						var totalWidth = 0;

						var totalValidColumns = 0;

						for( i = 0; i < tbodyColumns.length; i++ ){
							var td = angular.element( tbodyColumns[ i ] );
							td.width( 'auto' );
							totalWidth += td.width();
							if( td.width() > 0 ){
								totalValidColumns++;
							}
						}

						var widthToAdd = 0;
						var widthLeft = tableWidth - totalWidth;
						totalValidColumns--;
						if( totalValidColumns ){
							widthToAdd = ( widthLeft / totalValidColumns );
						}

						for( i=0; i < tbodyColumns.length; i++ ){
							var td = angular.element( tbodyColumns[ i ] );
							if( i > 0 && td.width() > 0 ){
								td.width( td.width() + widthToAdd );
							}
						}

						for( i=0; i<tbodyColumns.length; i++ ){
							var th = angular.element( theadColumns[ i ] );
							var td = angular.element( tbodyColumns[ i ] );
							th.width( td.width() );
						}

						var docHeight = angular.element(document).innerHeight();

						var windowHeight = angular.element(window).height();
						var theadHeight = thead.height();
						var tbodyHeight = ( windowHeight - ( theadHeight + 270 ) + 'px' );

						thead.css( 'display', 'block' );
						thead.css( 'width', tableWidth );

						tbody.css( 'display', 'block' );
						tbody.css( 'width', tableWidth );
						tbody.css( 'overflow-y', 'scroll' );
						tbody.css( 'height', tbodyHeight );

					}, 100 );
				}

				$rootScope.$on( 'ng-repeat-finished', function(e, data) {
					justDoIt();
				});

				scope.$on( 'window-focus', function(e, data) {
					justDoIt();
				});

				scope.$on( 'support-toggle', function(e, data) {
					justDoIt();
				});

				var justDoIt = function(){
					process();
					// just to make sure
					setTimeout( function(){ process(); }, 200 );
				}

				angular.element( window ).on( 'resize', justDoIt );

				scope.$on( '$destroy', function() {
					angular.element( window ).off( 'resize', justDoIt );
				});
			}
	};
});
