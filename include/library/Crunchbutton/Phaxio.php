<?php

/* ghetto verion */
class Crunchbutton_Phaxio {
	public function __construct($params = []) {
		$cmd = 'curl https://api.phaxio.com/v1/send '
			.'-F "to='.$params['to'].'" '
			.'-F "filename=@'.$params['file'].'"'
			.'-F "api_key='.c::config()->phaxio->key.'"'
			.'-F "api_secret='.c::config()->phaxio->secret.'"';
		exec($cmd, $return);
		$return = json_decode($return);

		$this->response = $return;
	}
}

