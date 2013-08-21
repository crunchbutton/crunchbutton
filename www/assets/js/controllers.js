// Account controllers
function AccountModalHeaderCtrl( $scope, $http, AccountModalService ) {
	$scope.modal = AccountModalService;
}


function AccountFacebookCtrl( $scope, $http, AccountModalService, AccountService, AccountHelpService ) {
	$scope.modal = AccountModalService;
}

function AccountSignInCtrl( $scope, $http, AccountModalService, AccountService, AccountHelpService ) {
	$scope.modal = AccountModalService;
	$scope.account = AccountService;
	$scope.help = AccountHelpService;
}

function AccountSignUpCtrl( $scope, $http, AccountModalService, AccountService ) {
	$scope.modal = AccountModalService;
	$scope.account = AccountService;
	// Watch the variable user
	$scope.$watch( 'account.user', function( newValue, oldValue, scope ) {
		$scope.account.user = newValue;
		if( newValue ){
			$scope.modal.header = false;
		}
	});
}

function AccountResetCtrl( $scope, $http, AccountResetService ) {
	$scope.reset = AccountResetService;
}

function GiftCardCtrl( $scope, $http, $rootScope, GiftCardService ) {
	$scope.giftcard = {};
	$scope.user = GiftCardService.account.user;
	$scope.modal = GiftCardService.modal;
	$scope.giftcard.value = GiftCardService.value;
	$rootScope.$on( 'GiftCardProcessed', function(e, data) {
		// Update the scope
		$scope.user = GiftCardService.account.user;
		$scope.giftcard.value = GiftCardService.value;
		$scope.modal = GiftCardService.modal;
	});
}

function MainHeaderCtrl($scope, MainNavigationService, OrderService) {
	$scope.navigation = MainNavigationService;
	$scope.order = OrderService;
	$scope.$watch('navigation.page', function( newValue, oldValue, scope ) {
		$scope.navigation.control();
	});
}

function RecommendRestaurantCtrl( $scope, $http, RecommendRestaurantService, AccountService, AccountModalService ) {
	$scope.recommend = RecommendRestaurantService;
	$scope.account = AccountService;
	$scope.modal = AccountModalService;
}

function RestaurantClosedCtrl($scope, $rootScope) {
	$rootScope.$on('restaurantClosedClick', function(e, r) {
		if ($scope.$$phase) {
			$scope.restaurant = r;
			App.dialog.show('.restaurant-closed-container');
		} else {
			$rootScope.$apply(function(scope) {
				scope.restaurant = r;
				App.dialog.show('.restaurant-closed-container');
			}); 
		}			
	});
}

function RecommendFoodCtrl( $scope, $http, RecommendFoodService ) {
	$scope.recommend = RecommendFoodService;
}

function SupportCtrl( $scope, $http, SupportService ) {
	$scope.support = SupportService;
}

function SideMenuCtrl() {

}

function NotificationAlertCtrl($scope, $rootScope) {
	$rootScope.$on('notificationAlert', function(e, title, message) {
		if ($scope.$$phase) {
			$scope.title = title;
			$scope.message = message;
			App.dialog.show('.notification-alert-container');
		} else {
			$rootScope.$apply(function(scope) {
				scope.title = title;
				scope.message = message;
				App.dialog.show('.notification-alert-container');
			}); 
		}			
	});
}