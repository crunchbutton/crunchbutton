
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


NGApp.directive('profilePreference', function (AccountService, $http) {
	return {
		restrict: 'A',
		templateUrl: 'assets/view/general-profile-preference.html',
		scope: {
			content: '@',
			key: '@',
			type: '@'
		},
		controller: function ($scope) {
			$scope.value = AccountService.user.prefs[$scope.key];
			if ($scope.type == 'bool') {
				$scope.value = $scope.value == '1' ? true : false;
			}

			$scope.change = function(value) {
				console.log(value);

				if ($scope.type == 'bool') {
					value = value ? '1' : '0';
				}
				
				AccountService.user.prefs[$scope.key] = value;
				
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