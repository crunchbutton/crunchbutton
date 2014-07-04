NGApp.controller('StaffListCtrl', function( $scope, StaffService ) {

	$scope.showForm = true;

	$scope.search = {};

	$scope.ready = true;
	$scope.search._types = StaffService.typeSearch();
	$scope.search._status = StaffService.statusSearch();
	$scope.search.type = 'all';
	$scope.search.status = 'all';
	$scope.search.name = '';
	$scope.page = 1;

	var list = function(){
		$scope.searched = true;
		$scope.isSearching = true;
		var search = { 'type': $scope.search.type, 'name': $scope.search.name, 'status': $scope.search.status, 'page': $scope.page }
		StaffService.list( search, function( data ){
			$scope.isSearching = false;
			$scope.pages = data.pages;
			$scope.next = data.next;
			$scope.prev = data.prev;
			$scope.staff = data.results;
			$scope.count = data.count;
		} );
	}

	var waiting = false;

	$scope.doSearch = function(){
		$scope.page = 1;
		list();
	}

	$scope.nextPage = function(){
		$scope.page = $scope.next;
		list();
	}

	$scope.prevPage = function(){
		$scope.page = $scope.prev;
		list();
	}

	$scope.payinfo = function( id_admin ){
		$scope.navigation.link( '/staff/payinfo/' + id_admin );
	}

});

NGApp.controller('StaffPayInfoCtrl', function( $scope, StaffPayInfoService ) {

	$scope.bank = { 'showForm': true };

	var load = function(){
		StaffPayInfoService.load( function( json ){
			if( json.id_admin ){
				$scope.payInfo = json;
				if( json.balanced_bank ){
					$scope.bank.showForm = false;
				}
				console.log('json',json);
				$scope.ready = true;
				$scope.payment = {};
				$scope.payment._methods = StaffPayInfoService.methodsPayment();
				$scope.payment._types = StaffPayInfoService.typesPayment();
			} else {
				App.alert( json.error );
			}
		} )
	}

	$scope.save = function(){
		if( $scope.form.$invalid ){
			App.alert( 'Please fill in all required fields' );
			$scope.submitted = true;
			return;
		}
		$scope.isSaving = true;
		StaffPayInfoService.save( $scope.payInfo, function( data ){
			$scope.isSaving = false;
			if( data.error ){
				App.alert( data.error);
				return;
			} else {
				$scope.payInfo = data;
				$scope.saved = true;
				setTimeout( function() { $scope.saved = false; }, 1500 );
			}
		} );
	}

	$scope.bankInfoTest = function(){
		$scope.bank.routing_number = '321174851';
		$scope.bank.account_number = '9900000000';
	}

	$scope.tokenize = function(){
		if( $scope.formBank.$invalid ){
			App.alert( 'Please fill in all required fields' );
			$scope.bankSubmitted = true;
			return;
		}
		$scope.isTokenizing = true;
		var payload = { name: $scope.payInfo.legal_name_payment,
										account_number: $scope.bank.account_number,
										routing_number: $scope.bank.routing_number };
		StaffPayInfoService.bankAccount( payload, function( json ){
			if( json.href ){
				json.legal_name_payment = $scope.payInfo.legal_name_payment;
				StaffPayInfoService.save_bank( json, function( data ){
					if( data.error ){
						App.alert( data.error);
						return;
					} else {
						load();
						$scope.isTokenizing = false;
						$scope.saved = true;
						setTimeout( function() { $scope.saved = false; }, 1500 );
					}
				} );

			} else {
				App.alert( 'Error!' );
				$scope.isTokenizing = false;
			}
		} );
	}

	$scope.list = function(){
		$scope.navigation.link( '/staff/list' );
	}

	if( $scope.account.isLoggedIn() ){
		load();
	}

});