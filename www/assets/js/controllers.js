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

function MainHeaderCtrl( $scope, MainNavigationService ) {
	$scope.navigation = MainNavigationService;
}

function RecommendCtrl( $scope, $http, RecommendRestaurantService, AccountService, AccountModalService ) {
	$scope.recommend = RecommendRestaurantService;
	$scope.account = AccountService;
	$scope.modal = AccountModalService;
}

function SupportCtrl( $scope, $http, SupportService ) {
	$scope.support = SupportService;
}
