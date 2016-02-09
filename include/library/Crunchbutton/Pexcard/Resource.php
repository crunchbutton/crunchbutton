<?php

use Httpful\Request;

class Crunchbutton_Pexcard_Resource extends Cana_Table {

	const CACHE_FILE_NAME = 'PexCard_';

	// I created this method so I can fake live
	public function env(){
		return 'live';
		return ( c::getEnv() == 'live' ) ? 'live' : 'beta';
	}

	public function cache( $AccountId = false ){
		$cache = Crunchbutton_Pexcard_Cache::byDate( date( 'Y-m-d' ), $AccountId );
		if( $cache->data ){
			$data = json_decode( $cache->data );
			return $data;
		}
		return false;
	}

	public function saveCache( $data, $AccountId = false ){
		$data = ( object ) [ 'body' => $data->body ];
		$data = json_encode( $data );
		Crunchbutton_Pexcard_Cache::create( $data, $AccountId );
		return $data;
	}

	public static function uri(){
		if( Crunchbutton_Pexcard_Resource::env() == 'live' ){
			return 'https://coreapi.pexcard.com/' . Crunchbutton_Pexcard_Resource::api_version() . '/';
		} else {
			return 'https://corebeta.pexcard.com/api/' . Crunchbutton_Pexcard_Resource::api_version() . '/';
		}
	}

	public function api_version(){
		return c::config()->pexcard->{Crunchbutton_Pexcard_Resource::env()}->apiversion;
	}

	public function username(){
		return c::config()->pexcard->{Crunchbutton_Pexcard_Resource::env()}->username;
	}

	public function password(){
		return c::config()->pexcard->{Crunchbutton_Pexcard_Resource::env()}->password;
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
					'businessprofile' => [ 'point' => 'Business/Profile', 'method' => 'GET', 'auth' => 'token'  ],
					'createcard' => [ 'point' => 'Card/Create', 'method' => 'POST', 'auth' => 'token'  ],
					'detailsaccount' => [ 'point' => 'Details/AccountDetails/:id', 'method' => 'GET', 'auth' => 'token'  ],
					'activatecard' => [ 'point' => 'Card/Activate/:id', 'method' => 'POST', 'auth' => 'token'  ],
					'zero' => [ 'point' => 'Card/Zero/:id', 'method' => 'POST', 'auth' => 'token'  ],
					'fund' => [ 'point' => 'Card/Fund/:id', 'method' => 'POST', 'auth' => 'token'  ],
					'changecardstatus' => [ 'point' => 'Card/Status/:id', 'method' => 'PUT', 'auth' => 'token' ],
					'spendbytransactionreport' => [ 'point' => 'Details/TransactionDetails?StartDate=:StartDate&EndDate=:EndDate&IncludePendings=:IncludePendings', 'method' => 'GET', 'auth' => 'token' ],
					'transactiondetails' => [ 'point' => 'Details/TransactionDetails/:id?StartDate=:StartDate&EndDate=:EndDate&IncludePendings=:IncludePendings', 'method' => 'GET', 'auth' => 'token' ],
					'allcardholdertransactions' => [ 'point' => 'Details/AllCardholderTransactions?StartDate=:StartDate&EndDate=:EndDate&IncludePendings=:IncludePendings', 'method' => 'GET', 'auth' => 'token' ],
					'token' => [ 'point' => 'Token', 'method' => 'POST', 'auth' => 'basic' ],
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
		return Crunchbutton_Pexcard_Token::getToken();
	}

	public function basic(){
		$clientid = c::config()->pexcard->{Crunchbutton_Pexcard_Resource::env()}->clientid;
		$clientsecret = c::config()->pexcard->{Crunchbutton_Pexcard_Resource::env()}->clientsecret;
		return  base64_encode( $clientid . ':' . $clientsecret );
	}

	// api version 4
	public static function request_v4( $point, $params = [], $auth = true, $json = true ){

		$_point = $point;
		$_params = $params;
		$_auth = $auth;
		$_json = $json;

		$point = Crunchbutton_Pexcard_Resource::url( $point );

		$method = $point[ 'method' ];
		$auth = ( $point[ 'auth' ] ) ? $point[ 'auth' ] : $auth;
		$point = $point[ 'point' ];


		if( !$params ){
			$params = [];
		}

		if( strpos( $point,  ':' ) ){
			foreach( $params as $key => $value ){
				$pattern = ":{$key}";
				$point = str_replace( $pattern, $value, $point );
			}
		}

		if( strtolower( $method ) == 'get' || strtolower( $method ) == 'put' ){
			foreach ( $params  as $key => $value ) {
				$url .= '/' . $value;
				break;
			}
			if( strtolower( $method ) == 'get' ){
				$params = [];
			}
		}

		$url = Crunchbutton_Pexcard_Resource::uri() . $point;

		if( $url ){

			switch ( strtolower( $method ) ) {
				case 'post':
					$request = \Httpful\Request::post( $url );
					break;
				case 'put':
					$request = \Httpful\Request::put( $url );
					break;
				case 'get':
					$request = \Httpful\Request::get( $url );
					break;
			}

			if( $auth ){

				switch ( $auth ) {

					case 'token':
						$request->addHeader( 'Authorization', 'token ' . Crunchbutton_Pexcard_Resource::token() );
						break;

					case 'basic':
						$request->addHeader( 'Authorization', 'basic ' . Crunchbutton_Pexcard_Resource::basic() );
						break;

					default:
						$params = array_merge( [ 'userName' => Crunchbutton_Pexcard_Resource::username(), 'password' => $pass ], Crunchbutton_Pexcard_Resource::password() );
						break;
				}
			}

			if( $params && count( $params ) ){
				if( strtolower( $method ) == 'put' ){
					$request->body( json_encode( $params ) );
				} else {
					$request->body( $params );
				}
			}

			if( $json ){
				$request->expects( 'json' );
			}

			if( strtolower( $method ) == 'post' ){
				$request->sendsForm();
			}

			if( strtolower( $method ) == 'put' ){
				$request->sendsJson();
			}

			$response = $request->send();

			if( $response && $response->body && $response->body->Message == 'Token expired or does not exist' ){
				// Desactive the current token
				Crunchbutton_Pexcard_Token::desactiveToken();
				// Create a new one
				Crunchbutton_Pexcard_Token::getToken();
				// Call the method again
				return Crunchbutton_Pexcard_Resource::request_v4( $_point, $_params, $_auth, $_json );
			}

			return $response;
		}
		return false;
	}

	// api version 3
	public static function request_v3( $point, $params = [], $auth = true, $json = true ){

		$url = Crunchbutton_Pexcard_Resource::url( $point );

		if( $url ){

			$request = \Httpful\Request::post( $url );

			if( $auth ){
				$params = array_merge( [ 'userName' => Crunchbutton_Pexcard_Resource::username(), 'password' => $pass ], Crunchbutton_Pexcard_Resource::password() );
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
