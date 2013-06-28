<?php

class Crunchbutton_Promo_Group extends Cana_Table
{
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('promo_group')
			->idVar('id_promo_group')
			->load($id);
	}
	
	public function date() {
		if (!isset($this->_date)) {
			$this->_date = new DateTime($this->date, new DateTimeZone(c::config()->timezone));
			$this->_date->setTimezone(new DateTimeZone( c::config()->timezone ));
		}
		return $this->_date;
	}

	public function giftcards(){
		if ( !isset( $this->_giftcards ) ) {
			$query = "SELECT p.* FROM promo p INNER JOIN promo_group_promo pgp ON p.id_promo = pgp.id_promo AND pgp.id_promo_group = {$this->id_promo_group}";
			$this->_giftcards = Promo::q( $query, $this->db() );
		}
		return $this->_giftcards;
	}

	public function giftcards_redeemed(){
		if ( !isset( $this->_giftcards_redeemed ) ) {
			$query = "SELECT p.* FROM promo p 
									INNER JOIN promo_group_promo pgp ON p.id_promo = pgp.id_promo AND pgp.id_promo_group = {$this->id_promo_group}
									INNER JOIN credit c ON p.id_promo = c.id_promo AND p.id_promo = c.id_promo";
			$this->_giftcards_redeemed = Promo::q( $query, $this->db() );
		}
		return $this->_giftcards_redeemed;
	}

	public function giftcards_redeemed_total(){
		$query = "SELECT COUNT(*) AS total FROM promo_group_promo pgp INNER JOIN credit c ON pgp.id_promo = c.id_promo WHERE pgp.id_promo_group = {$this->id_promo_group}";
		$total = c::db()->get( $query );
		return $total->_items[0]->total;
	}

	public function remove_giftcards(){
		$query = "DELETE FROM promo_group_promo WHERE id_promo_group = {$this->id_promo_group}";
		Cana::db()->query( $query );
	}

	public function giftcards_total(){
		$query = "SELECT COUNT(*) AS total FROM promo p INNER JOIN promo_group_promo pgp ON p.id_promo = pgp.id_promo AND pgp.id_promo_group = {$this->id_promo_group}";
		$total = c::db()->get( $query );
		return $total->_items[0]->total;
	}

	public function save_giftcards( $range ){
		$this->remove_giftcards();
		$ids = $this->range_translator( $range );
		$ids = $this->validate_promo_ids( $ids );
		foreach ( $ids as $id_promo ) {
			$id_promo = intval( $id_promo );
			$new = new Crunchbutton_Promo_Group_Promo();
			$new->id_promo = $id_promo;
			$new->id_promo_group = intval( $this->id_promo_group );
			$new->save();
		}
	}

	public function validate_promo_ids( $ids ){
		$_ids = [];
		foreach ( $ids as $id ) {
			$promo = Crunchbutton_Promo::o( $id );
			if( $promo->id_promo ){
				$_ids[] = $promo->id_promo;
			}
		}
		return $_ids;
	}

	public function range_translator( $range ){
		$groups = explode( ',', $range );
		$ids = [];
		foreach ( $groups as $group ) {
			$numbers = trim( $group );
			$numbers = explode( '-', $numbers );
			if( sizeof( $numbers ) == 2 ){
				$ini = intval( $numbers[ 0 ] );
				$end = intval( $numbers[ 1 ] );
				for( $i = $ini; $i <= $end; $i++ ){
					$ids[] = $i;	
				}
			} else {
				$ids[] = intval( $numbers[ 0 ] );
			}
		}
		return $ids;
	}

	public function range(){
		if( $this->giftcards_total() > 0 ){
			$query = "SELECT p.id_promo FROM promo p INNER JOIN promo_group_promo pgp ON p.id_promo = pgp.id_promo AND pgp.id_promo_group = {$this->id_promo_group}";
			$giftcards = c::db()->get( $query );
			$ids = [];
			foreach ( $giftcards as $giftcard ) {
				$ids[] = intval( $giftcard->id_promo );
			}

			sort( $ids );
			
			$groups = array();
			$total = sizeof( $ids );
			for( $i = 0; $i < $total; $i++ ){
				if( $i > 0 && ( $ids[ $i - 1 ] == $ids[ $i ] - 1 ) ){
					$groups[ count( $groups ) - 1 ][ 1 ] =  $ids[ $i ];
				} else {
					$groups[] = array( $ids[ $i ] ); 
				}
			}

			$str_group = '';
			$commas = '';
			foreach($groups as $group){
				if( count( $group ) == 1 ){
					$str_group .= $commas . $group[ 0 ];
				} else {
					$str_group .= $commas . $group[0] . ' - ' . $group[ count( $group ) - 1];
				}
				$commas = ', ';
			}

			return $str_group;
		} else {
			return '';
		}
	}

	public static function find($search = []) {

		$query = 'SELECT `promo_group`.* FROM `promo_group` WHERE id_promo_group IS NOT NULL ';
		
		if ( $search[ 'name' ] ) {
			$query .= " AND name LIKE '%{$search[ 'name' ]}%' ";
		}

		$query .= " ORDER BY id_promo_group DESC";

		$gifts = self::q($query);
		return $gifts;
	}

}