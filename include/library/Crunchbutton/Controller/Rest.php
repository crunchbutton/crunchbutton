<?php

class Crunchbutton_Controller_Rest extends Cana_Controller_Rest {

	public function __construct() {
		$headers = getallheaders();
		$this->headerErrors = $headers['X-Requested-With'] == 'XMLHttpRequest' || $headers['Http-Error'] ? true : false;

		if ($this->headerErrors) {
			ini_set('display_errors', 0);

			register_shutdown_function(function() use ($q) {
				$e = error_get_last();


				// i think E_ERROR is the only one we care about
				switch($e['type']) {
					case E_ERROR:
					case E_PARSE:
					case E_CORE_ERROR:
					case E_CORE_WARNING:
					case E_COMPILE_ERROR:
					case E_USER_ERROR:
					case E_USER_NOTICE:
					case E_RECOVERABLE_ERROR:
					header('PHP-Fatal-Error: '. json_encode($e));
					header('HTTP/1.0 500 Server Error');
					break;
				}
			});

			c::app()->exceptionHandler(function($e) {
				$backtracels = [];
				foreach($e->getTrace() as $k=>$v){
					if ($v['function'] == "include" || $v['function'] == "include_once" || $v['function'] == "require_once" || $v['function'] == "require"){
						$backtracels[] = "#".$k." ".$v['function']."(".$v['args'][0].") called at [".$v['file'].":".$v['line']."]";
					} else {
						$backtracels[] = "#".$k." ".$v['function']."() called at [".$v['file'].":".$v['line']."]";
					}
				}

				$error = [
					'message' => $e->getMessage(),
					'type' => 'throw',
					'trace' => $backtracels
				];

				header('PHP-Fatal-Error: '. json_encode($error));
				header('HTTP/1.0 500 Server Error');
			});
		}

		$find = '/(api\.|log\.)/';
		if (preg_match($find, $_SERVER['SERVER_NAME'])) {
			$allow = preg_replace($find,'',$_SERVER['SERVER_NAME']);
			header('Access-Control-Allow-Origin: http'.($_SERVER['HTTPS'] == 'on' ? 's' : '').'://'.$allow);
			header('Access-Control-Allow-Credentials: true');
		} elseif (1==1 || c::config()->site->config('allow-cors')->val()) {
			header('Access-Control-Allow-Origin: *');
			header('Access-Control-Allow-Credentials: true');
			header('Access-Control-Allow-Headers: Accept, Origin, Content-Type, Http-Error, App-Token, App-Version');
			header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
		}

		if ($this->method() == 'options') {
			http_response_code(200);
			exit;
		}

		if (! headers_sent ()) {
			header('Content-Type: application/json');
		}
		Cana::view()->layout('layout/ajax');

		parent::__construct();
	}
}