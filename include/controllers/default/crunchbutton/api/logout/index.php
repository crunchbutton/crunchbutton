<?php

class Controller_api_logout extends Crunchbutton_Controller_Rest {
	public function init() {
		// Remove the cookies
		foreach( $_COOKIE as $key => $val ){
			setcookie( $key, '', time() -3600, '/' );
		}
		session_destroy();
	}
}