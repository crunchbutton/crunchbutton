<?php

use Httpful\Request;

class Crunchbutton_Pexcard_Resource extends Cana_Model {

	public static $api_url = 'https://coreapi.pexcard.com/v3/';
	public static $auth_token = '2bd13c07d029047ff5fb6045ee8d07';
	public static $room_id = '171095';
	public static $from = 'CBNotify';
	public static $color_notification = 'yellow';
	public static $color_urgent = 'red';

	public static function url( $point ){
		$urls = [
							'ping' => 'admin/ping',
							'cardlist' => 'admin/cardlist',
							'carddetails' => 'admin/carddetails',
							'fund' => 'admin/fund',
							'createcard' => 'admin/createcard',
							'changecardstatus' => 'admin/changecardstatus',
							'spendbytransactionreport' => 'admin/SpendByTransactionReport',
							'businessfundingreport' => 'admin/BusinessFundingReport',
							'cardfundingreport' => 'admin/CardFundingReport',
							'cardfundingreport' => 'admin/CardFundingReport',
		 				];
		if( $urls[ $point ] ){
			return Crunchbutton_Pexcard_Resource::$api_url . $urls[ $point ];
		}
		return false;
	}

	public function ping(){
		return Crunchbutton_Pexcard_Resource::request( 'ping', [], true, false );
	}

	public static function request( $point, $params = [], $auth = true, $json = true ){

		$user = '_USERNAME_';
		$pass = '_PASSWORD_';

		$url = Crunchbutton_Pexcard_Resource::url( $point );

		if( $url ){

			$request = \Httpful\Request::post( $url );

			if( $auth ){
				$params = array_merge( [ 'UserName' => $user, 'Password' => $pass ] );
			}

			if( count( $params ) ){
				$request->body( $params );
			}

			if( $json ){
				$response->expects( 'json' );
			}
			$request->sendsForm();

			return $request->send();
		}
		return false;
	}
}

?>
