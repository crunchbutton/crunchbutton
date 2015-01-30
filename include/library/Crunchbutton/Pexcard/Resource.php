<?php

use Httpful\Request;

class Crunchbutton_Pexcard_Resource extends Cana_Table {

	// I created this method so I can fake live
	public function env(){
		// return 'live';
		return ( c::getEnv() == 'live' ) ? 'live' : 'beta';
	}

	public static function uri(){
		if( Crunchbutton_Pexcard_Resource::env() == 'live' ){
			return 'https://coreapi.pexcard.com/v3/';
		} else {
			return 'https://corebeta.pexcard.com/api/' . Crunchbutton_Pexcard_Resource::api_version() . '/';
		}
	}

	public function api_version(){
		return c::config()->pexcard->{Crunchbutton_Pexcard_Resource::env()}->apiversion;
	}


	public static function url( $point ){

		switch ( Crunchbutton_Pexcard_Resource::api_version() ) {

			case 'v3':
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
					];
				if( $urls[ $point ] ){
					return Crunchbutton_Pexcard_Resource::uri() . $urls[ $point ];
				}
				break;

			case 'v4':
				$urls = [
					'ping' => [ 'point' => 'Details/Ping', 'method' => 'GET'  ],

					'businessprofile' => [ 'point' => 'Business/Profile', 'method' => 'GET', 'auth' => 'token'  ],
					'businessadmin' => [ 'point' => 'Business/Admin', 'method' => 'GET', 'auth' => 'token'  ],

					'createcard' => [ 'point' => 'Card/Create', 'method' => 'POST', 'auth' => 'token'  ],

					'detailsaccount' => [ 'point' => 'Details/AccountDetails', 'method' => 'GET', 'auth' => 'token'  ],

					'cardlist' => 'admin/cardlist',
					'carddetails' => 'admin/carddetails',
					'fund' => 'admin/fund',
					'changecardstatus' => 'admin/changecardstatus',
					'spendbytransactionreport' => 'admin/SpendByTransactionReport',
					'businessfundingreport' => 'admin/BusinessFundingReport',
					'cardfundingreport' => 'admin/CardFundingReport',
					];
				if( $urls[ $point ] ){
					return $urls[ $point ];
				}
		}
		return false;
	}

	public function ping(){
		$ping = Crunchbutton_Pexcard_Resource::request( 'ping', [], true, false );
		if( $ping && $ping->body ){
			return $ping->body;
		}
		return false;
	}

	public static function request( $point, $params = [], $auth = true, $json = true ){

		switch ( Crunchbutton_Pexcard_Resource::api_version() ) {
			case 'v4':
				return Crunchbutton_Pexcard_Resource::request_v4( $point, $params, $auth, $json );
				break;
			default:
				return Crunchbutton_Pexcard_Resource::request_v3( $point, $params, $auth, $json );
				break;
		}
	}

	public static function token(){
		return c::config()->pexcard->{Crunchbutton_Pexcard_Resource::env()}->token;
	}

	// api version 4
	public static function request_v4( $point, $params = [], $auth = true, $json = true ){

		$user = c::config()->pexcard->{Crunchbutton_Pexcard_Resource::env()}->username;
		$pass = c::config()->pexcard->{Crunchbutton_Pexcard_Resource::env()}->password;

		$point = Crunchbutton_Pexcard_Resource::url( $point );

		$method = $point[ 'method' ];
		$auth = ( $point[ 'auth' ] ) ? $point[ 'auth' ] : $auth;

		$url = Crunchbutton_Pexcard_Resource::uri() . $point[ 'point' ];

		if( !$params ){
			$params = [];
		}

		if( strtolower( $method ) == 'get' ){
			foreach ( $params  as $key => $value ) {
				$url .= '/' . $value;
			}
			$params = [];
		}

		if( $url ){

			if( strtolower( $method ) == 'post' ){
				$request = \Httpful\Request::post( $url );
			} else {
				$request = \Httpful\Request::get( $url );
			}

			if( $auth ){

				switch ( $auth ) {

					case 'token':
						$request->addHeader( 'Authorization', 'token ' . Crunchbutton_Pexcard_Resource::token() );
						break;

					default:
						$params = array_merge( [ 'userName' => $user, 'password' => $pass ], $params );
						break;
				}
			}

			if( $params && count( $params ) ){
				$request->body( $params );
			}

			if( $json ){
				$request->expects( 'json' );
			}

			if( strtolower( $method ) == 'post' ){
				$request->sendsForm();
			}

			return $request->send();
		}
		return false;
	}

	// api version 3
	public static function request_v3( $point, $params = [], $auth = true, $json = true ){

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
