
NGApp.factory('DeployServices', function($rootScope, $resource, $routeParams) {

	var service = {
		server: {},
		version: {},
		git: {}
	};

	var server = $resource( App.service + 'deploy/servers/:id_deploy_server', { id_deploy_server: '@id_deploy_server'}, {
		'load' : {
			url: App.service + 'deploy/server/:id_deploy_server',
			method: 'GET',
			params : {}
		},
		'versions' : {
			url: App.service + 'deploy/server/:id_deploy_server/versions',
			method: 'GET',
			isArray:true
		}
	});
	
	var version = $resource( App.service + 'deploy/versions/:id_deploy_version', { id_deploy_version: '@id_deploy_version'}, {
		'load' : {
			url: App.service + 'deploy/version/:id_deploy_version',
			method: 'GET',
			params : {}
		},
		'save' : {
			url: App.service + 'deploy/version/:id_deploy_version',
			method: 'POST',
			params : {}
		}
	});
	
	var git = $resource( App.service + 'deploy/gitlog', {}, {
		'load' : {
			method: 'GET',
			params : {}
		}
	});
	
	service.server.list = function(params, callback) {
		server.query(params, function(data){
			callback(data);
		});
	}

	service.server.get = function(id_deploy_server, callback) {
		server.load({id_deploy_server: id_deploy_server}, function(data) {
			callback(data);
		});
	}
	
	service.server.versions = function(id_deploy_server, callback) {
		server.versions({id_deploy_server: id_deploy_server}, function(data) {
			callback(data);
		});
	}
	
	service.version.list = function(params, callback) {
		version.query(params, function(data){
			for (var x in data) {
				if (data[x].status == 'new' && data[x].timestamp * 1000 <= Date.now()) {
					data[x].status = 'deploying';
				}
			}
			callback(data);
		});
	}

	service.version.get = function(id_deploy_version, callback) {
		version.load({id_deploy_version: id_deploy_version}, function(data) {
			if (data.status == 'new' && data.timestamp * 1000 <= Date.now()) {
				data.status = 'deploying';
			}
			callback(data);
		});
	}
	
	service.version.post = function(params, callback) {
		version.save(params, function(data) {
			callback(data);
		});
	}
	
	service.git.list = function(params, callback) {
		git.query(params, function(data) {
			callback(data);
		});
	}

	return service;

});