<?php

class Crunchbutton_Facebook extends Cana_Model {

	private $_facebook;
	private $_user;
	private $_permissions;
	private $auth;

	public function postOrderStatus( $uuid ){
			$status = $this->getOrderStatus( $uuid ); 
		if( $status ){
			return $this->postStatus( $status );
		} else {
			return false;
		}
	}

	public function isLogged(){
		return $this->user();
	}

	public function facebook(){
		if( !$this->_facebook ){
			$this->_facebook = new Cana_Facebook( [
				'appId'	=> Cana::config()->facebook->app,
				'secret' => Cana::config()->facebook->secret
			] );
		}
		return $this->_facebook;
	}

	public function hasPublishPermission(){
		return $this->hasPermission( 'publish_actions' );
	}

	public function postStatus( $status ){

		if( $this->hasPublishPermission() ){

			$mural = array(
				'message'     => $status[ 'message' ],
				'name'        => $status[ 'name' ],
				'caption'     => $status[ 'caption' ],
				'link'        => $status[ 'link' ],
				'description' => $status[ 'description' ],
				'picture'     =>$status[ 'picture' ],
				'actions'     => array(
					array(
					'name' => $status[ 'site_name' ],
					'link' => $status[ 'site_url' ],
					)
				)
			);

			try {
				$endpoint = $this->userID() . '/feed';
				$this->facebook()->api( $endpoint, 'POST', $mural );
				return true;
			} catch ( Cana_Facebook_Exception $e ) {
				$error    = [
					'type'       => 'facebook',
					'level'      => 'error',
					'params'     => $mural,
				];
				Crunchbutton_Log::error( $error );
				return false;
			}
		}
	}

	public function getOrderStatus( $uuid  ){
		$order = Order::uuid( $uuid );

		if( $order->id_order ){
			$restaurant = $order->restaurant();
			$restaurantName = $restaurant->name;
			$restaurantURL = 'http://'.$_SERVER['__HTTP_HOST']. '/food-delivery/' . $restaurant->permalink;
			$restaurantDescription = $restaurant->short_description;
			if( $restaurant->thumb() && $restaurant->thumb()->getFileName() != '' ){
				$restaurantImage = 'http://'.$_SERVER['__HTTP_HOST']. '/cache/images/' .  $restaurant->thumb()->getFileName();	
			} 
			
			$status = array( 
										'name' => $restaurantName,
										'caption' => $restaurantDescription,
										'link' => $restaurantURL,
										'picture' => $restaurantImage
									);

			$status[ 'message' ] = ( $status[ 'message' ] && $status[ 'message' ] != '' ) ? $status[ 'message' ] : c::config()->facebook->default->poststatus->message;
			$status[ 'name' ] = ( $status[ 'name' ] && $status[ 'name' ] != '' ) ? $status[ 'name' ] : c::config()->facebook->default->poststatus->name;
			$status[ 'caption' ] = ( $status[ 'caption' ] && $status[ 'caption' ] != '' ) ? $status[ 'caption' ] : c::config()->facebook->default->poststatus->caption;
			$status[ 'link' ] = ( $status[ 'link' ] && $status[ 'link' ] != '' ) ? $status[ 'link' ] : c::config()->facebook->default->poststatus->link;
			$status[ 'description' ] = ( $status[ 'description' ] && $status[ 'description' ] != '' ) ? $status[ 'description' ] : c::config()->facebook->default->poststatus->description;
			$status[ 'picture' ] = ( $status[ 'picture' ] && $status[ 'picture' ] != '' ) ? $status[ 'picture' ] : c::config()->facebook->default->poststatus->picture;
			$status[ 'site_name' ] = ( $status[ 'site_name' ] && $status[ 'site_name' ] != '' ) ? $status[ 'site_name' ] : c::config()->facebook->default->poststatus->site_name;
			$status[ 'site_url' ] = ( $status[ 'site_url' ] && $status[ 'site_url' ] != '' ) ? $status[ 'site_url' ] : c::config()->facebook->default->poststatus->site_url;
			return $status;
		} else {
			return false;
		}
	}

	public function setToken( $token ){
		$this->facebook()->setAccessToken( $token );
	}

	public function hasPermission( $permission ){
		if( $this->user() && $this->permissions() ){
			if( isset( $this->permissions()[ 'data' ] ) && isset( $this->permissions()[ 'data' ][0][ $permission ]  ) ){
				return true;
			}	
		}
		return false;
	}

	public function redirect_uri_api(){
		return 'http://'.$_SERVER['__HTTP_HOST'].'/api/facebook/url_auth';
	}

	public function user(){
		if( $this->auth ){
			if( !$this->_user ){
				$this->_user = $this->facebook()->getUser();
				if ( $this->_user ) {
					try {
						$user = $this->facebook()->api( '/' . $this->_user );
					} catch ( Cana_Facebook_Exception $e ) {
						$user = null;
					}
					$this->_user = $user;
				}
			}
			return $this->_user;	
		}
		return false;
	}

	public function userID(){
		return $this->user()[ 'id' ];
	}

	public function getLoginURL( $params = array() ){
		$params[ 'scope' ] = ( $params[ 'scope' ] && $params[ 'scope' ] != '' ) ? $params[ 'scope' ] : c::config()->facebook->default->scope;
		$params[ 'redirect_uri' ] = ( $params[ 'redirect_uri' ] && $params[ 'redirect_uri' ] != '' ) ? $params[ 'redirect_uri' ] : $this->redirect_uri_api();
		return $this->facebook()->getLoginURL( $params );
	}

	public function permissions(){
		if( $this->user() ){
			if( !$this->_permissions ){
			$this->_permissions = $this->facebook()->api( '/me/permissions' );	
		}
			return $this->_permissions;	
		}	
		return false;
	}

	public function __construct(){
		$token = $_COOKIE[ 'fbtoken' ];
		if( $token ){
			$this->setToken( $token );
			$this->auth = true;
		}
		
	}

}