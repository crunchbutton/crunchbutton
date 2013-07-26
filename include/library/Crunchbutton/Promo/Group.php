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

	public function mktReport(){
		$data = [];
		$data[ 'giftcards' ] = [];
		$data[ 'giftcards' ][ 'total' ] = $this->giftcards_total();
		$data[ 'giftcards' ][ 'redeemed' ] = $this->giftcards_redeemed_total();
		$data[ 'giftcards' ][ 'used' ] = $this->giftcards_used_total();

		if( $data[ 'giftcards' ][ 'total' ] > 0 && $data[ 'giftcards' ][ 'used' ] > 0 ){
			$data[ 'giftcards' ][ 'used_percent' ] = ( $data[ 'giftcards' ][ 'used' ] * 100 ) / $data[ 'giftcards' ][ 'total' ];	
		} else {
			$data[ 'giftcards' ][ 'used_percent' ] = 0;
		}


		if( $data[ 'giftcards' ][ 'total' ] > 0 && $data[ 'giftcards' ][ 'redeemed' ] > 0 ){
			$data[ 'giftcards' ][ 'redeemed_percent' ] = ( $data[ 'giftcards' ][ 'redeemed' ] * 100 ) / $data[ 'giftcards' ][ 'total' ];	
		} else {
			$data[ 'giftcards' ][ 'redeemed_percent' ] = 0;
		}

		$data[ 'range' ] = $this->range;
		$data[ 'users' ] = [];
		$data[ 'users' ][ 'unique' ] = $this->unique_users();
		$data[ 'users' ][ 'new' ] = $this->new_users();
		$data[ 'users' ][ 'returned' ] = $this->returned_users();
		$data[ 'users' ][ 'active' ] = [];
		$data[ 'users' ][ 'active' ]['15'] = $this->active( 15 );
		$data[ 'users' ][ 'active' ]['30'] = $this->active( 30 );
		$data[ 'users' ][ 'active' ]['45'] = $this->active( 45 );
		$data[ 'users' ][ 'active' ]['60'] = $this->active( 60 );
		if( $data[ 'users' ][ 'new' ] > 0 && $data[ 'giftcards' ][ 'total' ] > 0 ){
			$data[ 'users' ][ 'new_per_giftcard' ] = $data[ 'users' ][ 'new' ] / $data[ 'giftcards' ][ 'total' ];
		} else {
			$data[ 'users' ][ 'new_per_giftcard' ] = 0;
		}
		if( $data[ 'users' ][ 'new' ] > 0 && $data[ 'giftcards' ][ 'redeemed' ] > 0 ){
			$data[ 'users' ][ 'new_per_giftcard_redeemed' ] = $data[ 'users' ][ 'new' ] / $data[ 'giftcards' ][ 'redeemed' ];
		} else {
			$data[ 'users' ][ 'new_per_giftcard_redeemed' ] = 0;
		}
		
		$data[ 'orders' ][ 'total' ] = $this->orders_total();
		if( $data[ 'orders' ][ 'total' ] > 0 && $data[ 'giftcards' ][ 'total' ] > 0 ){
			$data[ 'orders' ][ 'per_gift_card' ] = ( $data[ 'orders' ][ 'total' ] / $data[ 'giftcards' ][ 'total' ] );	
		} else {
			$data[ 'orders' ][ 'per_gift_card' ] = 0;	
		}

		if( $data[ 'orders' ][ 'total' ] > 0 && $data[ 'giftcards' ][ 'redeemed' ] > 0 ){
			$data[ 'orders' ][ 'per_gift_card_redeemed' ] = ( $data[ 'orders' ][ 'total' ] / $data[ 'giftcards' ][ 'redeemed' ] );	
		} else {
			$data[ 'orders' ][ 'per_gift_card_redeemed' ] = 0;	
		}

		if( $data[ 'orders' ][ 'total' ] > 0 && $data[ 'users' ][ 'new' ] ){
			$data[ 'orders' ][ 'per_new_users' ] = ( $data[ 'orders' ][ 'total' ] / $data[ 'users' ][ 'new' ] );	
		} else {
			$data[ 'orders' ][ 'per_new_users' ] = 0;	
		}

		// $data[ 'giftcards' ] = $this->giftcards()->count();
		return $data;
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

	public function giftcards_used_total(){
		$query = "SELECT COUNT( DISTINCT(c.id_credit_debited_from) ) AS total
							FROM credit c
							INNER JOIN
								(SELECT c.*
								 FROM credit c
								 INNER JOIN promo p ON p.id_promo = c.id_promo
								 INNER JOIN promo_group_promo pgp ON pgp.id_promo = p.id_promo
								 WHERE c.type = 'CREDIT'
									 AND pgp.id_promo_group = {$this->id_promo_group} ) redemmed ON c.id_credit_debited_from = redemmed.id_credit";
		$total = c::db()->get( $query );
		return $total->_items[0]->total;

	}

	public function remove_giftcards(){
		$query = "DELETE FROM promo_group_promo WHERE id_promo_group = {$this->id_promo_group}";
		Cana::db()->query( $query );
	}

	public function unique_users(){
		$query = "SELECT COUNT( DISTINCT( o.phone ) ) AS total FROM promo_group_promo pgp INNER JOIN credit c ON c.id_promo = pgp.id_promo INNER JOIN user u ON u.id_user = c.id_user INNER JOIN `order` o ON o.id_user = u.id_user WHERE pgp.id_promo_group = {$this->id_promo_group}";
		$total = c::db()->get( $query );
		return $total->_items[0]->total;
	}

	public function orders_total(){
		$query = "SELECT COUNT(*) AS total
							FROM
								(SELECT o.*
								 FROM `order` o
								 INNER JOIN
									 (SELECT c.*
										FROM credit c
										INNER JOIN
											(SELECT c.*
											 FROM credit c
											 INNER JOIN promo p ON p.id_promo = c.id_promo
											 INNER JOIN promo_group_promo pgp ON pgp.id_promo = p.id_promo
											 WHERE c.type = 'CREDIT'
												 AND pgp.id_promo_group = {$this->id_promo_group}) redeemed ON redeemed.id_credit = c.id_credit_debited_from
										WHERE c.type = 'DEBIT') debits ON o.id_order = debits.id_order) orders";
		$total = c::db()->get( $query );
		return $total->_items[0]->total;
	}

	public function active( $days = 45 ){
		$query = "SELECT COUNT(*) AS total FROM
								(SELECT o.phone, MAX(o.date) as last
								 FROM promo_group_promo pgp
								 INNER JOIN credit c ON c.id_promo = pgp.id_promo
								 INNER JOIN user u ON u.id_user = c.id_user
								 INNER JOIN `order` o ON o.id_user = u.id_user
								 WHERE pgp.id_promo_group = {$this->id_promo_group}
								 GROUP BY o.phone) orders
							WHERE last BETWEEN CURDATE() - INTERVAL {$days} DAY AND CURDATE()";
		$total = c::db()->get( $query );
		return $total->_items[0]->total;
	}

	public function new_users(){
		$query = "SELECT COUNT(*) AS total
							FROM
								(SELECT o.phone,
												MIN(o.date) as first
								 FROM promo_group_promo pgp
								 INNER JOIN credit c ON c.id_promo = pgp.id_promo
								 INNER JOIN user u ON u.id_user = c.id_user
								 INNER JOIN `order` o ON o.id_user = u.id_user
								 WHERE pgp.id_promo_group = {$this->id_promo_group}
								 GROUP BY o.phone) orders
							WHERE first >=
									(SELECT date_mkt
									 FROM promo_group pg
									 WHERE pg.id_promo_group = {$this->id_promo_group})";
		$total = c::db()->get( $query );
		return $total->_items[0]->total;
	}


	public function returned_users(){
		$query = "SELECT COUNT(*) AS total
							FROM
								(SELECT o.phone,
												MIN(o.date) as first,
												COUNT(*) total
								 FROM promo_group_promo pgp
								 INNER JOIN credit c ON c.id_promo = pgp.id_promo
								 INNER JOIN user u ON u.id_user = c.id_user
								 INNER JOIN `order` o ON o.id_user = u.id_user
								 WHERE pgp.id_promo_group = {$this->id_promo_group}
								 GROUP BY o.phone HAVING total > 1) orders
							WHERE first >=
									(SELECT date_mkt
									 FROM promo_group pg
									 WHERE pg.id_promo_group = {$this->id_promo_group})";
		$total = c::db()->get( $query );
		return $total->_items[0]->total;
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

	public function date_mkt(){
		$date_mkt = explode( '-', $this->date_mkt );
		$date_mkt = $date_mkt[1].'/'.$date_mkt[2].'/'.$date_mkt[0];
		return $date_mkt;
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