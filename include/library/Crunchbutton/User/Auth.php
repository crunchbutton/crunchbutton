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
				type="'.$type.'"
				AND auth="'.$id.'"
			LIMIT 1
		');
		return new Crunchbutton_User_Auth($row);
	}
	
	public static function localLogin( $email, $password ) {
		$password = static::passwordEncrypt( $password );
		$query = sprintf(" SELECT * 
												FROM user_auth
												WHERE
													type='local'
													AND email='%s'
													AND auth='%s'
													AND active=1
												LIMIT 1",
		mysql_real_escape_string( $email ),
		mysql_real_escape_string( $password ) );
		$row = Cana::db()->get( $query );
		if( $row->_items && $row->_items[0] ){
				$row = $row->_items[0];
		}
		return new Crunchbutton_User_Auth($row);
	}

	public static function byUser($user) {
		 $res = Cana::db()->query('
			SELECT * 
			FROM user_auth
			WHERE
				id_user="'.$user.'"
				AND active=1
		');
		$auths = [];
		while ($row = $res->fetch()) {
			$auths[$row->id_user_auth] = new Crunchbutton_User_Auth($row);
		}
		return $auths;
	}

	public function user() {
		if (!isset($this->_user)) {
			return new Crunchbutton_User($this->id_user);
		}
		return $this->_user;
	}

	public function checkEmailExists( $email ){
		$row = Cana::db()->get('
			SELECT * 
			FROM user_auth
			WHERE
				email="' . $email . '"
				AND active=1
		');
		if( $row->_items && $row->_items[0] ){
				$row = $row->_items[0];
				return  new Crunchbutton_User_Auth($row);;
		}
		return false;
	}

	public function userHasFacebookAuth( $id_user ){
		$row = Cana::db()->get('
			SELECT * 
			FROM user_auth
			WHERE
				id_user="' . $id_user . '"
				AND type = "facebook"
				AND active=1
		');
		if( $row->_items && $row->_items[0] ){
				$row = $row->_items[0];
				return  true;
		}
		return false;
	}

	public function validateResetCode( $code ){
		$query = sprintf(" SELECT * 
												FROM user_auth
												WHERE
													type='local'
													AND reset_code='%s'
													AND active=1
												LIMIT 1",
		mysql_real_escape_string( $code ) );
		$row = Cana::db()->get( $query );
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