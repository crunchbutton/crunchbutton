
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
				if (e.which == 39 && scope.query.page < scope.pages) {
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
				console.log('going to ', attrs.tabSelect)
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


NGApp.directive('profilePreference', function (AccountService, $http, $rootScope) {
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
			console.log(AccountService.user.prefs[$scope.key]);


//			$scope.value = AccountService.user.prefs[$scope.key];
//			AccountService.user.prefs['demo'] = true;
/*
			if ($scope.type == 'bool') {
				$scope.value = $scope.value == '1' ? true : false;
			}
*/
			$scope.change = function(value) {
				value = AccountService.user.prefs[$scope.key];
				console.log($scope.key, value);

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

		function createResource(url, options, actions) {
			var resource;
			var outstanding = [];
			actions = actions || {};

			Object.keys(actions).forEach(function(action) {
				var canceller = $q.defer();
				actions[action].timeout = canceller.promise;
				actions[action].Canceller = canceller;
			});

			resource = $resource(url, options, actions);

			var isLoading = {};

			Object.keys(actions).forEach(function(action) {
				var method = resource[action];

				resource[action] = function() {

					if (isLoading.action) {
						isLoading.action.reject('Aborted');
					}

					var deferred = $q.defer(),

					promise = method.apply(null, arguments).$promise;

					abortablePromiseWrap(promise, deferred, outstanding);

					isLoading.action = deferred;

					return {
						$promise: deferred.promise,

						abort: function() {
							deferred.reject('Aborted');
						},
						cancel: function() {
							actions[action].Canceller.resolve('Call cancelled');

							// Recreate canceler so that request can be executed again
							var canceller = $q.defer();
							actions[action].timeout = canceller.promise;
							actions[action].Canceller = canceller;
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
