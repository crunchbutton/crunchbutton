<?php

die('this is just here for reference now');

require_once('../include/crunchbutton.php');

$host = 'localhost'; //host
$port = '9000'; //port
$null = NULL; //null var

//Create TCP/IP sream socket
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
//reuseable port
socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);

//bind socket to specified host
socket_bind($socket, 0, $port);

//listen to port
socket_listen($socket);

//create & add listning socket to the list
$clients = array($socket);

//start endless loop, so that our script doesn't stop
while (true) {
	//manage multipal connections
	$changed = $clients;
	//returns the socket resources in $changed array
	$sel = socket_select($changed, $null, $null, 0, 10);

	//check for new socket
	if (in_array($socket, $changed)) {
		$socket_new = socket_accept($socket); //accpet new socket

		$header = socket_read($socket_new, 1024); //read data sent by the socket
		$status = perform_handshaking($header, $socket_new, $host, $port); //perform websocket handshake

		if ($status) {
			$clients[] = $socket_new; //add socket to client array

			socket_getpeername($socket_new, $ip); //get ip address of connected socket
			$response = mask(json_encode(array('type'=>'system', 'message'=>$ip.' connected'))); //prepare json data
			send_message($response); //notify all users about new connection

			//make room for new socket
			$found_socket = array_search($socket, $changed);
			unset($changed[$found_socket]);
		} else {
			continue;
		}
	}

	//loop through all connected sockets
	foreach ($changed as $changed_socket) {

		//check for any incomming data
		while(socket_recv($changed_socket, $buf, 1024, 0) >= 1) {
			$received_text = unmask($buf);
			$payload = json_decode($received_text);

			var_dump($payload);

			// sending a mesage to a ticket
			switch ($payload->type) {
				case 'ticket.message':

					$sess = Session::token($payload->_token);
					if ($sess->id_admin) {
						$admin = new Admin($sess->id_admin);

						if ($admin->id_admin) {
							$support = Support::o($payload->ticket);

							if ($support->id_support) {
								$message = $support->addAdminMessage([
									'body' => $payload->body,
									'phone' => $admin->phone,
									'id_admin' => $admin->id_admin
								]);
								if ($message->id_support_message) {
									$message->notify();
								}
							}
						}
					}

					break;

				case 'ticket.typing.start':
				case 'ticket.typing.stop':
					break;
			}


/*
			$admin = Admin::o();

			$message = $this->addAdminMessage( [ 'body' => $body, 'phone' => $admin->phone, 'id_admin' => c::admin()->id_admin ] );
			if( $message->id_support_message ){
				$message->notify();
			}

//			if ($support->permissionToEdit()) {
//			send_mask
*/
			$support = null;

			//prepare data to be sent to client
			$response_text = mask(json_encode(array('type'=>'usermsg', 'name'=>$user_name, 'message'=>$user_message, 'color'=>$user_color)));
			send_message($response_text); //send data
			break 2;
		}

		$buf = @socket_read($changed_socket, 1024, PHP_NORMAL_READ);
		if ($buf === false) { // check disconnected client

			// remove client for $clients array
			$found_socket = array_search($changed_socket, $clients);
			socket_getpeername($changed_socket, $ip);
			unset($clients[$found_socket]);

			//notify all users about disconnected connection
			$response = mask(json_encode(array('type'=>'system', 'message'=>$ip.' disconnected')));
			send_message($response);
		}
	}
}
// close the listening socket
socket_close($sock);

function send_message($msg) {
	global $clients;
	foreach($clients as $changed_socket) {
		@socket_write($changed_socket,$msg,strlen($msg));
	}
	return true;
}


//Unmask incoming framed message
function unmask($text) {
	$length = ord($text[1]) & 127;
	if($length == 126) {
		$masks = substr($text, 4, 4);
		$data = substr($text, 8);
	}
	elseif($length == 127) {
		$masks = substr($text, 10, 4);
		$data = substr($text, 14);
	}
	else {
		$masks = substr($text, 2, 4);
		$data = substr($text, 6);
	}
	$text = "";
	for ($i = 0; $i < strlen($data); ++$i) {
		$text .= $data[$i] ^ $masks[$i%4];
	}
	return $text;
}

function send_mask($messageArray) {
	$response = mask(json_encode($messageArray));
	send_message($response);
}

//Encode message for transfer to client.
function mask($text)
{
	$b1 = 0x80 | (0x1 & 0x0f);
	$length = strlen($text);

	if($length <= 125)
		$header = pack('CC', $b1, $length);
	elseif($length > 125 && $length < 65536)
		$header = pack('CCn', $b1, 126, $length);
	elseif($length >= 65536)
		$header = pack('CCNN', $b1, 127, $length);
	return $header.$text;
}

//handshake new client.
function perform_handshaking($receved_header, $client_conn, $host, $port) {
	$headers = [];
	$query = [];
	$lines = preg_split("/\r\n/", $receved_header);

	$request = explode(' ', trim(array_shift($lines)));
	$request[1] = explode('?', $request[1]);
	$method = $request[0];
	$path = preg_replace('/^\/(.*)$/', '\\1', $request[1][0]);

	foreach($lines as $line) {
		$line = chop($line);
		if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
			$headers[$matches[1]] = $matches[2];
		}
	}

	if (($method == 'POST' || $method == 'PUT') && $headers['Content-Type'] == 'application/x-www-form-urlencoded') {
		parse_str(array_pop($lines), $query);
	} else {
		parse_str($request[1][1], $query);
	}

	$check = false;

	// if the connection is not a socket
	if ($headers['Upgrade'] != 'websocket') {

		// we are emiting from php
		if ($path == 'emit' && $query['_key'] == c::config()->site->config('chat-server-key')->val()) {
			echo "Emiting...\n";
			$msg = [];
			foreach ($query as $k => $v) {
				if ($k{0} != '_') {
					$msg[$k] = $v;
				}
			}
			send_mask($msg);
			var_dump($query);

			$upgrade =
				"HTTP/1.1 200 Ok\r\n".
				"\r\ntrue";
			socket_write($client_conn, $upgrade, strlen($upgrade));

		// someone came here from the browser
		} else {
			$upgrade =
				"HTTP/1.0 404 Not Found\r\n".
				"Content-Type:text/html; charset=UTF-8\r\n".
				"\r\nThis is not the page you are looking for... <a href='http://crunchbutton.com'>http://crunchbutton.com</a>\r\n\r\n".c::config()->site->config('chat-server-key')->val()."\r\n\r\n".$query['_key'];
			socket_write($client_conn, $upgrade, strlen($upgrade));
		}
		socket_close($client_conn);
		return false;
	}

	if ($query['_token']) {
		$sess = Session::token($query['_token']);
		if ($sess->id_admin) {
			$admin = new Admin($sess->id_admin);
			if ($admin->permission()->check(['global', 'support-all', 'support-view', 'support-crud' ])) {
				$check = true;
			}
		}
	}

	if (!$check) {
		socket_close($client_conn);
		return false;
	}

	$secKey = $headers['Sec-WebSocket-Key'];
	$secAccept = base64_encode(pack('H*', sha1($secKey . '_KEY_')));
	//hand shaking header
	$upgrade  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
	"Upgrade: websocket\r\n" .
	"User: ".$admin->id_admin."\r\n" .
	"Connection: Upgrade\r\n" .
	"WebSocket-Origin: $host\r\n" .
	"WebSocket-Location: ws://$host:$port/demo/shout.php\r\n".
	"Sec-WebSocket-Accept:$secAccept\r\n\r\n";
	socket_write($client_conn,$upgrade,strlen($upgrade));

	return true;
}

