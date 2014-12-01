<?php

use Httpful\Request;

class Crunchbutton_Pexcard_Resource extends Cana_Table {

	// I created this method so I can fake live
	public function env(){
		return ( c::getEnv() == 'live' ) ? 'live' : 'beta';
	}

	public static function uri(){
		if( Crunchbutton_Pexcard_Resource::env() == 'live' ){
			return 'https://coreapi.pexcard.com/v3/';
		} else {
			return 'https://corebeta.pexcard.com/api/v3/';
		}
	}

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
			return Crunchbutton_Pexcard_Resource::uri() . $urls[ $point ];
		}
		return false;
	}

	public function ping(){
		return Crunchbutton_Pexcard_Resource::request( 'ping', [], true, false );
	}

	public static function request( $point, $params = [], $auth = true, $json = true ){

		$user = c::config()->pexcard->{Crunchbutton_Pexcard_Resource::env()}->username;
		$pass = c::config()->pexcard->{Crunchbutton_Pexcard_Resource::env()}->password;

		$url = Crunchbutton_Pexcard_Resource::url( $point );

		if( $url ){

			$request = \Httpful\Request::post( $url );

			if( $auth ){
				$params = array_merge( [ 'userName' => $user, 'password' => $pass ], $params );
			}

			if( count( $params ) ){
				$request->body( $params );
			}

			if( $json ){
				$request->expects( 'json' );
			}
			$request->sendsForm();

			return $request->send();
		}
		return false;
	}
}

?>
