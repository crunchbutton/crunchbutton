<?php

class Crunchbutton_Pexcard_Report_Transaction extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this->table( 'pexcard_report_transaction' )->idVar( 'id_pexcard_report_transaction' )->load( $id );
	}
	public static function byTransaction( $params ){
		$id_pexcard_transaction = $params[ 'id_pexcard_transaction' ];
		$transaction = self::q( 'SELECT * FROM pexcard_report_transaction WHERE id_pexcard_transaction = ?', [ $id_pexcard_transaction ] )->get( 0 );
		if ( !$transaction->id_pexcard_report_transaction ) {
			$transaction = new Pexcard_Report_Transaction( $params );
			$transaction->save();
		}
		return $transaction;
	}
}