<?php

class Crunchbutton_Pexcard_Cache extends Cana_Table {

	public static function create( $data, $acctId ){

		$cache = new Crunchbutton_Pexcard_Cache;
		$cache->timestamp = date( 'Y-m-d H:i:s' );
		$cache->data = $data;
		$cache->acctId = $acctId;
		$cache->save();
		return $cache;
	}

	public static function byDate( $date, $acctId = 0 ){
		return self::byDay( $date, $acctId );
	}

	public static function byDay( $date, $acctId ) {
		$acctId = $acctId ? $acctId : 0;
		if( !$acctId ){
			$where = ' AND acctId = ? OR acctId IS NULL';
		} else {
			$where = ' AND acctId = ?';
		}
		$cache = self::q('SELECT * FROM pexcard_cache WHERE DATE( timestamp ) = ? ' . $where . ' ORDER BY id_pexcard_cache DESC LIMIT 1', [ $date, $acctId ] )->get(0);
		if ($cache->id_pexcard_cache) {
			return $cache;
		}
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('pexcard_cache')
			->idVar('id_pexcard_cache')
			->load($id);
	}
}

?>
