NGApp.factory( 'PermissionService', function( $resource, $routeParams ) {

	var service = { staff: {}, group: {} };

	// Create a private resource 'staff'
	var staff = $resource( App.service + 'permission/staff/:id_admin/:action', { id_admin: '@id_admin', action: '@action' }, {
				'load' : { 'method': 'GET', params : { action: '' } },
				'save' : { 'method': 'POST', params : { action: 'save' } }
			}
		);

	service.staff.load = function( callback ){
		staff.load( { 'id_admin': $routeParams.id }, function( data ){
			callback( data );
		} );
	}

	service.staff.save = function( params, callback ){
		if( !params.id_admin ){
			params.id_admin = $routeParams.id;
		}
		staff.save( params, function( data ){
			callback( data );
		} );
	}

	// Create a private resource 'group'
	var _group = $resource( App.service + 'permission/group/:id_group/:action', { id_group: '@id_group', action: '@action' }, {
				'load' : { 'method': 'GET', params : { action: '' } },
				'save' : { 'method': 'POST', params : { action: 'save' } }
			}
		);

	service.group.load = function( callback ){
		_group.load( { 'id_group': $routeParams.id }, function( data ){
			callback( data );
		} );
	}

	service.group.save = function( params, callback ){
		if( !params.id_group ){
			params.id_group = $routeParams.id;
		}
		_group.save( params, function( data ){
			callback( data );
		} );
	}

	service.hasGlobal = function( scope ){
		for( group in scope.list ){
			if( scope.list[ group ].group == 'global' && scope.list[ group ].permissions ){
				for( permission in scope.list[ group ].permissions ){
					if( scope.list[ group ].permissions[ permission ].name == 'global' ){
						if( scope.list[ group ].permissions[ permission ].has ){
							return true;
						} else {
							return false;
						}
					}
				}
			}
		}
	}

	service.checkGroupVisible = function( scope ){
		for( group in scope.list ){
			if( scope.list[ group ].permissions ){
				for( permission in scope.list[ group ].permissions ){
					if( scope.list[ group ].permissions[ permission ].name == scope.group[ scope.list[ group ].group ].doAllPermission ){
						if( scope.list[ group ].permissions[ permission ].has ){
							scope.group[ scope.list[ group ].group ].doAllChecked = true;
						} else {
							scope.group[ scope.list[ group ].group ].doAllChecked = false;
						}
					}
				}
			}
		}
		return scope.list;
	}

	service.startDependenciesCheck = function( scope ){
		scope.permissions = [];
		if( scope.list ){
			for( x in scope.list ){
				var permissions = scope.list[ x ];
				if( permissions.permissions ){
					service.processPermissions( permissions.permissions, scope );
				}
			}
		}
	}

	service.giveDependentPermission = function( name, scope ){
		for( group in scope.list ){

			if( scope.list[ group ][ 'permissions' ] ){
				for( permission in scope.list[ group ][ 'permissions' ] ){
					if( scope.list[ group ][ 'permissions' ][ permission ][ 'name' ] == name ){
						scope.list[ group ][ 'permissions' ][ permission ][ 'has' ] = true;
					}
				}
				if( scope.list[ group ][ 'permissions' ][ permission ][ 'additional' ] && scope.list[ group ][ 'permissions' ][ permission ][ 'additional' ][ 'permissions' ] ){
					for( additional in scope.list[ group ][ 'permissions' ][ permission ][ 'additional' ][ 'permissions' ] ){
						if( scope.list[ group ][ 'permissions' ][ permission ][ 'additional' ][ 'permissions' ][ additional ][ 'name' ] == name ){
							scope.list[ group ][ 'permissions' ][ permission ][ 'additional' ][ 'permissions' ][ additional ][ 'has' ] = true;
						}
					}
				}
			}
		}
	}

	service.processPermissions = function( permissions, scope ){

		for( y in permissions ){

			var check = true;

			var permission = permissions[ y ];
			if( ( permission.group && scope.group[ permission.group ]
						&& scope.group[ permission.group ].doAllChecked && permission.name != scope.group[ permission.group ].doAllPermission ) || ( service.hasGlobal( scope ) && permission.name != 'global' ) ){
				check = false;
			}

			permission.disabled = ( scope.group[ permission.group ].doAllChecked && permission.name != scope.group[ permission.group ].doAllPermission || service.hasGlobal( scope ) && permission.name != 'global' );

			if( check ){
				if( permission.type && permission.type == 'combo' && permission.permitted && permission.permitted.length > 0 ){
					for( x in permission.permitted ){
						var id = permission.permitted[ x ];
						var name = ( new String( permission.name ) ).replace( 'ID', id );
						scope.permissions.push( name );
						// check the additional permissions
						if( permission.additional && permission.additional.permissions ){
							for( y in permission.additional.permissions ){
								var additional_permission = permission.additional.permissions[ y ];
								if( additional_permission.has ){
									var name = ( new String( additional_permission.name ) ).replace( 'ID', id );
									scope.permissions.push( name );
								}
							}
						}
					}
				} else {
					if( permission.has && permission.name.indexOf( '-ID-' ) == -1 ){
						scope.permissions.push( permission.name );
					}
				}
			}

			if( permission.dependency && ( permission.has || ( permission.type && permission.type == 'combo' && permission.permitted && permission.permitted.length > 0 ) ) ){
				for( y in permission.dependency ){
					service.giveDependentPermission( permission.dependency[ y ], scope );
				}
			}
			if( permission.additional && permission.additional.permissions ){
				service.processPermissions( permission.additional.permissions, scope );
			}
		}
	}

	return service;

} );