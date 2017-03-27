// Setup basic express server

require('newrelic');

var express = require('express');
var app = express();
var server = require('http').createServer(app);
var io = require('socket.io')(server);
var port = process.env.PORT || 3696;
var http = require('http');
var querystring = require('querystring');
var bodyParser = require('body-parser');

var key = '_KEY_';

server.listen(port, function () {
	console.log('Server listening at port %d', port);
});

app.use(bodyParser.json());

var sendBroadcast = function(payload, to, event) {

	var sockets = {};

	console.log('recieved broadcast...', to, event, payload);

	// @todo: prevent duplicate messages

	if (to.room) {
		if (typeof to.room != 'object') {
			to.room = [to.room];
		}

		for (var x in to.room) {
			io.to(to.room[x]).emit(to.room[x] + '.' + event, payload);
			continue;
			io.sockets.clients(to.room[x]).forEach(function(socket) {
				if (!sockets[socket.phpsessid]) {
					sockets[socket.phpsessid] = socket;
				}
			});

		}
	}

	if (to.admin) {
		if (typeof to.admin != 'object') {
			to.admin = [to.admin];
		}

		io.sockets.clients().forEach(function(socket) {
			if (to.admin.indexOf(socket.id_admin) > -1) {
				if (!sockets[socket.phpsessid]) {
					sockets[socket.phpsessid] = socket;
				}
			}
		});
	}

	for (var x in sockets) {
		sockets[x].emit(event, payload);
	}
}


// from php
app.post('/', function (req, res) {
	if (req.body._key != key) {
		res.status(401).end();
		return;
	}

	sendBroadcast(req.body.payload, req.body.to, req.body.event);

	res.send('{"status":"sent"}');
});

app.all('*', function (req, res) {
	res.redirect(302, 'https://cockpit.la/');
});

io.on('connection', function (socket) {
	var addedUser = false;
	console.log('user connected...');

	socket.events = {};

	// from socket
	socket.on('event.broadcast', function (payload) {
		sendBroadcast(payload.payload, payload.to, payload.event);
	});

	socket.on('event.message', function (payload) {
		console.log('recieved message...', payload);

		if (!payload.url) {
			return;
		}

		var post = querystring.stringify(payload.data);

		var options = {
			host: socket.apiHost || 'beta.cockpit.la',
			path: payload.url,
			port: '80',
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
				'Content-Length': post.length,
				'Cookie': 'PHPSESSID=' + socket.phpsessid + '; token=' + socket.token
			}
		};

		var complete = function(data) {
			console.log('api response: ', data);
			socket.emit('event.response', data);
		};

		var req = http.request(options, function(res) {
			console.log('statusCode: ', res.statusCode);
			console.log('headers: ', res.headers);

			res.setEncoding('utf8');
			var str = '';
			res.on('data', function (chunk) {
				str += chunk;
			});
			res.on('end', function () {
				complete(str);
			});
		});

		req.on('error', function (err) {
			console.log(err);
		});

		req.write(post);
		req.end();
	});

	// listen for events
	socket.on('event.subscribe', function (event) {
		console.log('subscribing to ', event);
		socket.join(event);
	});

	// stop listening for events
	socket.on('event.unsubscribe', function (event) {
		console.log('unsubscribing to ', event);
		socket.leave(event);
	});

	// set the auth to be used for authentication
	socket.on('auth', function (payload) {
		socket.phpsessid = payload.phpsessid;
		socket.token = payload.token;
		socket.apiHost = payload.host || 'beta.cockpit.la';

		var options = {
			host: socket.apiHost,
			path: '/api/config',
			port: '443',
			method: 'GET',
			headers: {
				'Cookie': 'PHPSESSID=' + socket.phpsessid + '; token=' + socket.token
			}
		};

		var complete = function(data) {
			console.log('config response: ', data);
			if (data.user && data.user.id_admin) {
				socket.id_admin = data.user.id_admin;
			}
		};

		var req = http.request(options, function(res) {
			console.log('statusCode: ', res.statusCode);
			console.log('headers: ', res.headers);

			var str = '';
			res.on('data', function (chunk) {
				str += chunk;
			});
			res.on('end', function () {
				complete(str);
			});

		});

		req.on('error', function (err) {
			console.log(err);
		});

	});
});