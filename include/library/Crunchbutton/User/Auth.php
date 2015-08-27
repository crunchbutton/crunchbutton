<?php

class Crunchbutton_User_Auth extends Cana_Table {

	/**
	* This method returns the encrypted password
	* It uses a salt in order to make it impossible to be decrypted
	**/
	public static function passwordEncrypt( $password ){
		$salt = 'Crunchbutton';
		return md5( $salt . $password );
	}

	public static function resetCodeGenerator(){
		$random_id_length = 6;
		$rnd_id = crypt( uniqid( rand(), 1 ) );
		$rnd_id = strip_tags( stripslashes( $rnd_id ) );
		$rnd_id = str_replace( '.', '', $rnd_id );
		$rnd_id = strrev( str_replace( '/', '', $rnd_id ) );
		$rnd_id = substr( $rnd_id, 0, $random_id_length );
		return strtolower( $rnd_id );
	}

	public static function byTypeId($type, $id) {
		 $row = Cana::db()->get('
			SELECT *
			FROM user_auth
			WHERE
				type=?
				AND auth=?
			LIMIT 1
		', [$type, $id]);
		return new Crunchbutton_User_Auth($row);
	}

	public static function localLogin( $email, $password ) {
		$password = static::passwordEncrypt( $password );
		$query = "
			SELECT *
			FROM user_auth
			WHERE
				type='local'
				AND email=:email
				AND auth=:password
				AND active=true
			LIMIT 1
		";

		$row = Cana::db()->get($query, ['email' => $email, 'password' => $password]);
		if( $row->_items && $row->_items[0] ){
				$row = $row->_items[0];
		}
		return new Crunchbutton_User_Auth($row);
	}

	public static function byUser($id_user) {
		 $res = Cana::db()->query('
			SELECT *
			FROM user_auth
			WHERE
				id_user=?
				AND active=true
		', [$id_user]);
		$auths = [];
		while ($row = $res->fetch()) {
			$auths[$row->id_user_auth] = new Crunchbutton_User_Auth($row);
		}
		$res->closeCursor();
		return $auths;
	}

	public static function byUserExport( $id_user ){
		$auths = static::byUser( $id_user );
		$json = array();
		foreach( $auths as $auth ){
			$data = array();
			$data[ 'type' ] = $auth->type;
			$data[ 'data' ] = $auth->email;
			if( $auth->type == 'local' ){
				$data[ 'kind' ] = ( static::checkKind( $auth->email ) ? 'email' : 'phone' );
			} else {
				$data[ 'kind' ] = '';
			}
			$json[] = $data;
		}
		return $json;
	}

	public static function checkKind( $email ){
		return filter_var( $email, FILTER_VALIDATE_EMAIL );
	}

	public function user() {
		if (!isset($this->_user)) {
			return new Crunchbutton_User($this->id_user);
		}
		return $this->_user;
	}

	public function checkPhoneExists( $phone ){
		return Crunchbutton_User_Auth::checkEmailExists( $phone );
	}

	public function checkEmailExists( $email ){
		$row = Cana::db()->get('
			SELECT *
			FROM user_auth
			WHERE
				email=?
				AND active=true
		', [$email]);
		if( $row->_items && $row->_items[0] ){
				$row = $row->_items[0];
				return  new Crunchbutton_User_Auth($row);;
		}
		return false;
	}

	public static function userHasFacebookAuth( $id_user ){
		$row = Cana::db()->get("
			SELECT *
			FROM user_auth
			WHERE
				id_user=?
				AND type = 'facebook'
				AND active=true
		", [$id_user]);
		if( $row->_items && $row->_items[0] ){
				$row = $row->_items[0];
				return  $row->id_user_auth;
		}
		return false;
	}

	public static function userHasAuth( $id_user ){
		$row = Cana::db()->get('
			SELECT *
			FROM user_auth
			WHERE
				id_user=?
				AND active=true
		', [$id_user]);
		if( $row->_items && $row->_items[0] ){
				$row = $row->_items[0];
				return  true;
		}
		return false;
	}

	public static function userHasEmailAuth( $id_user ){
		$row = Cana::db()->get('
			SELECT *
			FROM user_auth
			WHERE
				id_user=?
				AND email LIKE "%@%"
				AND active=true
		', [$id_user]);
		if( $row->_items && $row->_items[0] ){
				$row = $row->_items[0];
				return $row->id_user_auth;
		}
		return false;
	}

	// This function creates a user_auth
	public static function createPhoneAuth( $user_id, $phone ){
		$id_user_auth = User_Auth::userHasEmailAuth( $user_id );
		if( $id_user_auth ){
			$phoneExists = User_Auth::checkPhoneExists( $phone );
			if( !$phoneExists ){
				$user_auth = User_Auth::o( $id_user_auth );
				$user_auth_phone = new User_Auth;
				$user_auth_phone->id_user = $user_auth->id_user;
				$user_auth_phone->type = $user_auth->type;
				$user_auth_phone->auth = $user_auth->auth;
				$user_auth_phone->active = $user_auth->active;
				$user_auth_phone->hash = $user_auth->hash;
				$user_auth_phone->email = $phone;
				$user_auth_phone->save();
				if( $user_auth_phone->id_user_auth ){
					return true;
				}
			}
		}
		return false;
	}

	public static function createPhoneAuthFromFacebook( $user_id, $phone ){
		$id_user_auth = User_Auth::userHasFacebookAuth( $user_id );
		if( $id_user_auth ){
			$phoneExists = User_Auth::checkPhoneExists( $phone );
			if( !$phoneExists ){
				$user_auth = User_Auth::o( $id_user_auth );
				$user_auth_phone = new User_Auth;
				$user_auth_phone->id_user = $user_auth->id_user;
				$user_auth_phone->type = 'local';
				$user_auth_phone->active = $user_auth->active;
				$user_auth_phone->hash = '';
				$user_auth_phone->email = $phone;
				$user_auth_phone->save();
				if( $user_auth_phone->id_user_auth ){
					return true;
				}
			}
		}
		return false;
	}

	public function validateResetCode( $code ){
		$query = "
			SELECT *
			FROM user_auth
			WHERE
				type='local'
				AND reset_code=:code
				AND active=true
			LIMIT 1
		";
		$row = Cana::db()->get( $query, ['code' => $code]);
		if( $row->_items && $row->_items[0] ){
				$row = $row->_items[0];
				return  new Crunchbutton_User_Auth($row);;
		}
		return false;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('user_auth')
			->idVar('id_user_auth')
			->load($id);
	}
}