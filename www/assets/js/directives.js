// Facebook button compoment
NGApp.directive( 'facebookSigninButton', function ( AccountFacebookService ) {
	return {
		restrict: 'A',
		templateUrl: 'assets/view/account.facebook.html',
		scope: {
			title: '@'
		},
		controller: function ( $scope ) {
			$scope.facebook = AccountFacebookService;
		}
	};;
});

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


/*
TODO: This directives did not work! I need to verify why
// Validate login
NGApp.directive( 'validateLogin', function () {
		return {
			restrict: 'A',
			require: 'ngModel',
				link: function(scope, elm, attrs, ctrl){
					console.log('ctrl',ctrl);
					ctrl.$parsers.unshift( function( value ){
						valid = true;
						if( value == '' ){
							valid = false;
						} else {
							valid = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/.test( value );
						}

						// var valid = App.phone.validate( value );
						// if( !valid ){
							// Valid email
							
						// }
						ctrl.$setValidity( 'validateLogin', valid );
						return valid ? undefined : value;
					} );
				}
		};
} );


// Validate login
NGApp.directive( 'validateEmpty', function () {
		return {
			restrict: 'A',
			require: 'ngModel',
				link: function(scope, elm, attrs, ctrl){
					ctrl.$parsers.unshift( function( value ){
						valid = ( value != '' );
						ctrl.$setValidity( 'validateEmpty', valid );
						return valid ? undefined : value;
					} );
				}
		};
} );
*/
