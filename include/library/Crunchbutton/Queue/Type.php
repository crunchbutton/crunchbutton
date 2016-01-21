<?php

class Crunchbutton_Queue_Type extends Cana_Table {

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('queue_type')
			->idVar('id_queue_type')
			->load($id);
	}

	public static function byType( $name ){
		$type = Crunchbutton_Queue_Type::q( 'SELECT * FROM queue_type WHERE type = ? ', [ $name ] );
		$type = $type->get( 0 );
		if( $type->id_queue_type ){
			return $type;
		} else {
			$type = new Crunchbutton_Queue_Type;
			$type->type = $name;
			$type->save();
			return $type;
		}
		return false;
	}

}
