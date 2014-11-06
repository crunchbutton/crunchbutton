// Setup basic express server
var express = require('express');
var app = express();
var server = require('http').createServer(app);
var io = require('socket.io')(server);
var port = process.env.PORT || 3000;
var http = require('http');
var querystring = require('querystring');

server.listen(port, function () {
	console.log('Server listening at port %d', port);
});

// Routing
app.use(express.static(__dirname + '/public'));

io.on('connection', function (socket) {
	var addedUser = false;
	console.log('user connected...');
	
	socket.events = {};

	// from php
	socket.on('event.broadcast', function (payload) {
		console.log('recieved broadcast...', payload);

		if (payload.room) {
			if (typeof payload.room != 'object') {
				payload.room = [payload.room];
			}

			for (var x in payload.room) {
				io.to(payload.room[x]).emit(payload.event, payload.data);
			}
		}

		if (payload.admin) {
			if (typeof payload.admin != 'object') {
				payload.admin = [payload.admin];
			}

			io.sockets.clients().forEach(function(socket) {
				if (payload.admin.indexOf(socket.id_admin) > -1) {
					socket.emit(payload.event, payload.data);
				}
			});

		}
	});

	// from socket
	socket.on('event.message', function (payload) {
		console.log('recieved message...', payload);
		
		if (!payload.url) {
			return;
		}
		
		var post = querystring.stringify(payload.data);

		var options = {
			host: 'beta.cockpit.la',
			path: payload.url,
			port: '80',
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
				'Content-Length': post.length,
				'Cookie': 'token=' + socket.token
			}
		};
		
		var complete = function(data) {
			console.log('api response: ', data);
			//io.to('ticket.' + data.id_support).emit('ticket.message', data);
		};

		var req = http.request(options, function(res) {
			console.log("statusCode: ", res.statusCode);
			console.log("headers: ", res.headers);
			
			res.setEncoding('utf8');
			var str = '';
			res.on('data', function (chunk) {
				str += chunk;
				console.log('chunk: ', chunk);
			});
			res.on('end', function () {
				complete(str);
			});
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

	// set the token to be used for authentication
	socket.on('token', function (token) {
		socket.token = token;
	});
});