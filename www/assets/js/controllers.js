// Account controllers
function AccountModalHeaderCtrl( $scope, $http, AccountModalService ) {
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

function GiftCardCtrl( $scope, $http, GiftCardService ) {
	$scope.giftcard = GiftCardService;
	$scope.giftcard.processModal();
}

function MainHeaderCtrl( $scope, MainNavigationService ) {
	$scope.navigation = MainNavigationService;
	$scope.$watch('navigation.page', function( newValue, oldValue, scope ) {
		$scope.navigation.control();
	} );
}

function RecommendRestaurantCtrl( $scope, $http, RecommendRestaurantService, AccountService, AccountModalService ) {
	$scope.recommend = RecommendRestaurantService;
	$scope.account = AccountService;
	$scope.modal = AccountModalService;
}

function RecommendFoodCtrl( $scope, $http, RecommendFoodService ) {
	$scope.recommend = RecommendFoodService;
}

function SupportCtrl( $scope, $http, SupportService ) {
	$scope.support = SupportService;
}

function SideMenuCtrl( $scope, MainNavigationService, AccountSignOut ) {
	$scope.navigation = MainNavigationService;
	$scope.signout = AccountSignOut;
}