<?php

class Crunchbutton_Pexcard_Report_Order extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this->table( 'pexcard_report_order' )->idVar( 'id_pexcard_report_order' )->load( $id );
	}
	public static function byOrder( $params ){
		$id_order = $params[ 'id_order' ];
		$order = self::q( 'SELECT * FROM pexcard_report_order WHERE id_order = ?', [ $id_order ] )->get( 0 );
		if ( !$order->id_pexcard_report_order ) {
			$order = new Crunchbutton_Pexcard_Report_Order( $params );
			$order->save();
		}
		return $order;
	}
}

?>
