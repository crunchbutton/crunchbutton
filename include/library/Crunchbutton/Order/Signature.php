<?php

class Crunchbutton_Order_Signature extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('order_signature')
			->idVar('id_order_signature')
			->load($id);
	}

	public static function desactiveOlderSignatures( $id_order ) {
		if (!$id_order) {
			return false;
		}
		$signatures = self::q( 'SELECT * FROM order_signature WHERE id_order=?', [ $id_order ] );
		foreach( $signatures as $signature ){
			$signature->deactivate();
		}
	}

	public function deactivate() {
		$this->active = 0;
		$this->save();
	}

	public static function retrieve( $id_order ){
		$signature = self::q( 'SELECT * FROM order_signature WHERE id_order=? AND active = true ORDER BY id_order_signature DESC LIMIT 1', [ $id_order ] )->get( 0 );
		if( $signature->id_order_signature ){
			return c::crypt()->decrypt( $signature->content );
		}
		return false;
	}

	public static function store( $params ){
		self::desactiveOlderSignatures( $params[ 'id_order' ] );
		$content = c::crypt()->encrypt( $params[ 'signature' ] );
		$signature = new Crunchbutton_Order_Signature();
		$signature->id_order = $params[ 'id_order' ];
		$signature->id_admin = c::user()->id_admin;
		$signature->content = $content;
		$signature->active = true;
		$signature->date = date( 'YmdHis' );
		$signature->save();
		return true;
	}
}