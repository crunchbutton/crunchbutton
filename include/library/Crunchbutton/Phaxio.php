<?php

/* ghetto verion */
class Crunchbutton_Phaxio {
	public function __construct($params = []) {
		$env = c::env() == 'live' ? 'live' : 'dev';
		$cmd = 'curl https://api.phaxio.com/v1/send '
			.'-F "to='.$params['to'].'" '
			.'-F "filename=@'.$params['file'].'" '
			.'-F "callback_url=http://'.$_SERVER['__HTTP_HOST'].'/api/notification/'.$params['id_notification_log'].'/callback" '
			.'-F "api_key='.c::config()->phaxio->{$env}->key.'" '
			.'-F "api_secret='.c::config()->phaxio->{$env}->secret.'"';

		exec($cmd, $return);

		$return = json_decode($return[1]);
		foreach ($return as $key => $value) {
			$this->{$key} = $value;
		}

		$this->response = $return;
	}
}

