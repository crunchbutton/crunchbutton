<?php

class Crunchbutton_Blocked extends Cana_Table {

	const TYPE_USER = 'user';
	const TYPE_PHONE = 'phone';

	const MESSAGE_CONFIG_KEY = 'blocked-customer-message';

	public static function blockUser( $id_user, $comment = null ){
		if( self::isUserBlocked( $id_user ) ){
			return;
		}
		$block = new Crunchbutton_Blocked;
		$block->id_user = $id_user;
		$block->date = date( 'Y-m-d H:i:s' );
		$block->id_admin = c::user()->id_admin;
		$block->comment = $comment;
		$block->save();
		return $block;
	}

	public static function blockPhoneNumber( $phone, $comment = null ){
		$phone = Crunchbutton_Phone::byPhone($phone);
		if( $phone->id_phone ){
			return self::blockPhone( $phone->id_phone, $comment );
		}
	}

	public static function blockPhone( $id_phone, $comment = null ){
		if( self::isPhoneBlocked( $id_phone ) ){
			return;
		}
		$block = new Crunchbutton_Blocked;
		$block->id_phone = $id_phone;
		$block->date = date( 'Y-m-d H:i:s' );
		$block->id_admin = c::user()->id_admin;
		$block->comment = $comment;
		$block->save();
		return $block;
	}

	public static function isPhoneBlocked( $id_phone ){
		$blocked = Crunchbutton_Blocked::q( 'SELECT * FROM blocked WHERE id_phone = ? ', [ $id_phone ] )->get( 0 );
		if( $blocked->id_blocked ){
			return true;
		}
		return false;
	}

	public static function isPhoneNumberBlocked( $phone ){
		$phone = Crunchbutton_Phone::byPhone($phone);
		if( $phone->id_phone ){
			return self::isPhoneBlocked( $phone->id_phone );
		}
		return false;
	}

	public static function isUserBlocked( $id_user ){
		$blocked = Crunchbutton_Blocked::q( 'SELECT * FROM blocked WHERE id_user = ? ', [ $id_user ] )->get( 0 );
		if( $blocked->id_blocked ){
			return true;
		}
		return false;
	}

	public static function unBlockUser( $id_user ){
		Cana::dbWrite()->query('DELETE FROM blocked WHERE id_user = ?' , [ $id_user ] );
	}

	public static function unBlockPhone( $id_phone ){
		Cana::dbWrite()->query('DELETE FROM blocked WHERE id_phone = ?' , [ $id_phone ] );
	}

	public static function unBlockPhoneNumer( $phone ){
		$phone = Crunchbutton_Phone::byPhone($phone);
		if( $phone->id_phone ){
			Cana::dbWrite()->query('DELETE FROM blocked WHERE id_phone = ?' , [ $phone->id_phone ] );
		}
	}

	public function type(){
		if( $this->id_user ){
			return self::TYPE_USER;
		} else if( $this->id_phone ){
			return self::TYPE_PHONE;
		}
	}

	public function admin(){
		if( !$this->_admin ){
			$this->_admin = Crunchbutton_Admin::o( $this->id_admin );
		}
		return $this->_user;
	}

	public function user(){
		if( $this->type() == self::TYPE_USER && !$this->_user ){
			$this->_user = Crunchbutton_User::o( $this->id_user );
		}
		return $this->_user;
	}

	public function phone(){
		if( $this->type() == self::TYPE_PHONE && !$this->_phone ){
			$this->_phone = Crunchbutton_Phone::o( $this->id_phone );
		}
		return $this->_phone;
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('blocked')
			->idVar('id_blocked')
			->load($id);
	}

	public function date(){
		if (!isset($this->_date)) {
			$this->_date = new DateTime( $this->date, new DateTimeZone( c::config()->timezone ) );
		}
		return $this->_date;
	}

	public function save($new = false){
		if( $this->id_phone || $this->id_user ){
			parent::save();
		}
		return false;
	}

	public static function getMessage(){
		$config = Crunchbutton_Config::getConfigByKey( self::MESSAGE_CONFIG_KEY );
		return $config->value;
	}

	public static function updateMessage( $message ){
		$config = Crunchbutton_Config::getConfigByKey( self::MESSAGE_CONFIG_KEY );
		$config->value = $message;
		$config->save();
		return $config->value;
	}

}
