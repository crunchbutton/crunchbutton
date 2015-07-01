
NGApp.directive('chatSend', function(TicketViewService) {
	return {
		restrict: 'A',
		link: function(scope, element, attrs) {
			element.bind('keydown keypress', function (e) {
				if (e.which == 13) {
					TicketViewService.send(element.val());
					e.preventDefault();
					element.val('');
				} else {
					//TicketViewService.typing(element.val());
				}
			});
		}
	};
});

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
			};

			scope.uploader.onSuccessItem = function(fileItem, response, status, headers) {
				$rootScope.$broadcast( 'driverDocsUploaded', { id_driver_document: response.id_driver_document, response: response } );
				scope.uploader.clearQueue();
				l.stop();
			};

			scope.uploader.onErrorItem = function (fileItem, response, status, headers) {
				$rootScope.$broadcast( 'driverDocsUploadedError', {} );
				scope.uploader.clearQueue();
				l.stop();
			};

			return;


			scope.$watch( 'uploader.progress', function( newValue, oldValue, scope ) {
				return;
				console.log(newValue);
				if( !isNaN( uploader.progress ) ){
					var progress = ( uploader.progress / 100 );
					l.setProgress( progress );
				}
			});
			$timeout(l.stop, 100);
		}
	}
});


NGApp.directive( 'resourceUpload', function ($rootScope, FileUploader) {
	return {
		restrict: 'AE',
		replace: false,
		scope: true,
		link: function ( scope, elem, attrs, ctrl ) {
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
				url: '/api/community/resource/upload/',
				autoUpload: true
			});

			scope.uploader.onBeforeUploadItem = function() {
				l.start();
			};

			scope.uploader.onSuccessItem = function(fileItem, response, status, headers) {
				$rootScope.$broadcast( 'resourceUpload', { id_driver_document: response.id_driver_document, response: response } );
				scope.uploader.clearQueue();
				l.stop();
			};

			scope.uploader.onErrorItem = function (fileItem, response, status, headers) {
				$rootScope.$broadcast( 'resourceUploadError', {} );
				scope.uploader.clearQueue();
				l.stop();
			};

			return;


			scope.$watch( 'uploader.progress', function( newValue, oldValue, scope ) {
				return;
				console.log(newValue);
				if( !isNaN( uploader.progress ) ){
					var progress = ( uploader.progress / 100 );
					l.setProgress( progress );
				}
			});
			$timeout(l.stop, 100);
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
												'&nbsp;&nbsp;<span title="Reload" ng-show="_current.id == tab.id" class="clickable" ng-click="loadContent( tab );"><i ng-class="{\'fa-spin\':tab.isLoading}" class="fa fa-refresh"></i></span>' +
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
		template: '<a href ng-click="run()">' +
								'<button class="button button-small button-empty" ng-class="{\'button-green\':!isRunning}" >' +
									'<i class="fa fa-check" ng-if="!isRunning"></i><i class="fa fa-spinner fa-spin" ng-if="isRunning"></i>&nbsp;&nbsp;{{buttonTitle}}' +
								'</button>' +
							'</a>',
		link: function ( scope, elem, attrs, ctrl ) {
			scope.title = attrs.title;
			scope.isRunning = false;
			var expressionHandler = scope.method();
			scope.run = function(){
				if( !scope.isRunning ){
					scope.isRunning = true;
					expressionHandler( function(){ scope.isRunning = false; } );
				}
			}
		}
	}
});

NGApp.directive('imgListViewSrc', function( $parse ) {
	return {
		restrict: 'A',
		scope: {
			image:'=imgListViewSrc'
    },

		link: function(scope, element, attrs) {

			var error = function(){
				element.empty();
				element.append( '<div ng-if="!restaurant.hasImage" class="customer-image ' + attrs.imgNull + '"></div>' );
			}
			var success = function( src ){
				element.empty();
				element.append( '<img class="customer-image" src="' + src + '"></img>' );
			}

			var loading = function(){
				// implement some loading image here
			}

			if( scope.image ){
				var img = new Image();
				img.src = attrs.imgPath + scope.image;
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
});