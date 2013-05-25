<?php

/* ghetto verion */
class Crunchbutton_Phaxio {
	public function __construct($params = []) {
		$env = c::env() == 'live' ? 'live' : 'dev';
		$cmd = 'curl https://api.phaxio.com/v1/send '
			.'-F "to='.$params['to'].'" '
			.'-F "filename=@'.$params['file'].'" ';
		if ($params['id_notification_log']) {
			$protocol = ( $env == 'live' ) ? 'https' : 'http';
			$cmd .= '-F "callback_url=' . $protocol . '://'.c::config()->host_callback.'/api/notification/'.$params['id_notification_log'].'/callback" ';
		}
		$cmd .= '-F "api_key='.c::config()->phaxio->{$env}->key.'" '
			.'-F "api_secret='.c::config()->phaxio->{$env}->secret.'"';

		Log::debug([
			'phaxio cmd' => $cmd,
			'action' => 'sending fax',
			'type' => 'notification'
		]);

		exec($cmd, $return);

		$return = json_decode(trim(join('',$return)));

		if ($return) {
			foreach ($return as $key => $value) {
				$this->{$key} = $value;
			}
		}

		$this->response = $return;
	}
}

