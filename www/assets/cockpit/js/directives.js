
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
					TicketViewService.typing(element.val());
				}
			});
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
					data: {key: $scope.key, value: value},
					headers: {'Content-Type': 'application/x-www-form-urlencoded'}
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