<?php

class Crunchbutton_Log_Type extends Cana_Table {

	const TYPE_UNKNOWN = 'unknown';

	public function __construct($id = null) {
		parent::__construct(c::logDB());
		self::dbWrite(c::logDB());

		$this->table('log_type')->idVar('id_log_type')->load($id);
	}

	public static function getDB(){
		return c::logDB();
	}

	public static function byType( $name ){

		if( trim( $name ) == '' ){
			$name = self::TYPE_UNKNOWN;
		}

		$name = preg_replace( '/[^a-z\d- _]/i', '', $name );
		$name = preg_replace( '/[ _]/i', '-', $name );

		$type = self::q( 'SELECT * FROM log_type WHERE type = ? ORDER BY id_log_type DESC LIMIT 1 ', [ $name ], self::getDB() );
		$type = $type->get( 0 );

		if( $type->id_log_type ){
			return $type;
		} else {
			$type = new Crunchbutton_Log_Type;
			$type->type = $name;
			$type->save();
			return $type;
		}
		return false;
	}

}
