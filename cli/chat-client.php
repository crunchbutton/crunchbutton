<?

	$ch = curl_init();
        
	curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1/emit');

	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
	curl_setopt($ch, CURLOPT_PORT, 9000);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);

	curl_setopt($ch, CURLOPT_POST, true);

	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
		'cmd' => 'ticket.message',
		'message' => 'TEST'
	]));

	curl_exec($ch);
	curl_close($ch);

    exit;
    
    
    
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("Unable to create socket\n");
//socket_connect($socket, '127.0.0.1', '9000');

 $time = time();
    while (!socket_connect($socket, '127.0.0.1', '9000'))
    {
      $err = socket_last_error($socket);
      if ($err == 115 || $err == 114)
      {
        if ((time() - $time) >= $timeout)
        {
          socket_close($socket);
          die("Connection timed out.\n");
        }
        sleep(1);
        continue;
      }
      die(socket_strerror($err) . "\n");
    }

exit;


$client = stream_socket_client("tcp://127.0.0.1:9000", $errno, $errorMessage);

if ($client === false) {
    throw new UnexpectedValueException("Failed to connect: $errorMessage");
}

fwrite($client, "GET / HTTP/1.0\r\nHost: localhost\r\nAccept: */*\r\n\r\n");
echo stream_get_contents($client);
fclose($client);