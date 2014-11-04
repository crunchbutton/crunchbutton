<?php

class Crunchbutton_Chat {
	public static function emit($payload = []) {

		if (!c::config()->site->config('chat-server')->val()) {
			throw new Exception('No chat server defined. (chat-server)');
		}
		
		if (!c::config()->site->config('chat-server-key')->val()) {
			throw new Exception('No chat server security key defined. (chat-server-key)');
		}
		
		if (!c::config()->site->config('chat-server-port')->val()) {
			throw new Exception('No chat server port defined. (chat-server-port)');
		}
		
		$payload['_key'] = c::config()->site->config('chat-server-key')->val();

		$ch = curl_init();
		    
		curl_setopt($ch, CURLOPT_URL, c::config()->site->config('chat-server')->val().'emit');
		
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Expect:']);
		curl_setopt($ch, CURLOPT_PORT, c::config()->site->config('chat-server-port')->val());
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
		
		$res = curl_exec($ch);
		curl_close($ch);
		
		return $res;
	}
}