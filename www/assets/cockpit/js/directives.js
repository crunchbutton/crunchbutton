
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