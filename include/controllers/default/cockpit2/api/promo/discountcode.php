<?php

class Controller_api_promo_discountcode extends Crunchbutton_Controller_RestAccount {

	public function init() {

		$this->_permission();

		switch ( $this->method() ) {
			case 'get':
				$this->_get();
			break;
			case 'post':
				$this->_post();
			break;
		}
	}

	private function _permission(){
		if (!c::admin()->permission()->check(['global', 'gift-card-all', 'gift-card-create-all' ])) {
			header('HTTP/1.1 401 Unauthorized');
			exit;
		}
	}

	private function _get(){

		switch ( c::getPagePiece( 3 ) ) {

			case 'list':
				$this->_list();
				break;

			default:
				$promo = Crunchbutton_Promo::o( c::getPagePiece( 3 ) );
				if( $promo->id_promo ){
					$out = $promo->exports();
					$date_start = $promo->dateStart();
					if( $date_start ){
						$out[ 'date_start' ] = $date_start->format( 'Y,m,d' );
					}
					if( !$promo->id_community ){
						$out[ 'all' ] = true;
					}
					$date_end = $promo->dateEnd();
					if( $date_end ){
						$out[ 'date_end' ] = $date_end->format( 'Y,m,d' );
					}

					$out[ 'paid_by' ] = strtoupper( $out[ 'paid_by' ] );
					$out[ 'value' ] = floatval( $out[ 'value' ] );

					echo json_encode( $out );exit;
				}
				$this->_error();
				break;
		}
	}

	private function _post(){
		$this->_save();
	}

	private function _save(){

		$code = $this->request()[ 'code' ];

		if( !$code ){
			$this->_error( 'Please enter a code!' );
		}

		if( !preg_match( '/^[a-zA-Z]+[a-zA-Z0-9._]+$/', $code ) ){
			$this->_error( 'Please use just letter and number at the code!' );
		}

		$code = strtolower( $code );

		if( $this->request()[ 'id_promo' ] ){
			$promo = Crunchbutton_Promo::o( $this->request()[ 'id_promo' ] );
			$_promo = Crunchbutton_Promo::q( 'SELECT * FROM promo WHERE code = ? AND id_promo != ?', [ $code, $this->request()[ 'id_promo' ] ] );
		}
		if( !$promo->id_promo ){
			$promo = new Crunchbutton_Promo;
			$_promo = Crunchbutton_Promo::q( 'SELECT * FROM promo WHERE code = ?', [ $code ] );
		}

		if( $_promo->id_promo ){
			$this->_error( 'This code ' . $code . ' already exists!' );
		}

		$promo->id_admin = c::user()->id_admin;
		$promo->is_discount_code = 1;
		$promo->code = $code;
		$promo->type = Crunchbutton_Promo::TYPE_GIFTCARD;

		$promo->date_start = $this->request()[ 'date_start' ];
		$promo->date_end = $this->request()[ 'date_end' ];
		$promo->usable_by = $this->request()[ 'usable_by' ];
		$promo->id_community = $this->request()[ 'id_community' ];
		if( $this->request()[ 'all' ] ){
			$promo->id_community = null;
		}

		$promo->paid_by = strtoupper( $this->request()[ 'paid_by' ] );
		$promo->delivery_fee = ( $this->request()[ 'delivery_fee' ] ? 1 : 0 );
		$promo->value = $this->request()[ 'value' ];

		$promo->active = ( $this->request()[ 'active' ] ? 1 : 0 );
		$promo->date = date( 'Y-m-d H:i:s' );
		$promo->save();

		echo json_encode( [ 'id_promo' => $promo->id_promo ] );
		exit;
	}

	private function _list(){

		$limit = $this->request()['limit'] ? $this->request()['limit'] : 20;
		$search = $this->request()['search'] ? $this->request()['search'] : '';
		$page = $this->request()['page'] ? $this->request()['page'] : 1;
		$community = $this->request()['community'] ? $this->request()['community'] : null;
		if( $community == 'all' ){
			$community = null;
		}
		$keys = [];

		if ($page == 1) {
			$offset = '0';
		} else {
			$offset = ($page-1) * $limit;
		}

		$q = '
			SELECT
				-WILD-
			FROM promo p
		';

		$q .='
			WHERE
				p.code IS NOT NULL AND p.is_discount_code = true
		';

		if ($community) {
			$q .= '
				AND p.id_community=?
			';
			$keys[] = $community;
		}

		if ($search) {
			$s = Crunchbutton_Query::search([
				'search' => stripslashes($search),
				'fields' => [
					'p.code' => 'like'
				]
			]);
			$q .= $s['query'];
			$keys = array_merge($keys, $s['keys']);
		}

		// get the count
		$count = 0;
		$r = c::db()->query(str_replace('-WILD-','COUNT(*) c', $q), $keys);
		while ($c = $r->fetch()) {
			$count++;
		}

		$q .= '
			ORDER BY p.id_promo DESC
			LIMIT '.intval($limit).'
			OFFSET '.intval($offset).'
		';

		// do the query
		$data = [];
		$r = c::db()->query(str_replace('-WILD-','
			p.*
		', $q), $keys);

		while ($s = $r->fetch()) {

			$promo = Crunchbutton_Promo::o($s);
			$out = $promo->exports();
			if( $promo->id_community ){
				$out['community'] = $promo->community()->name;
			} else {
				$out['all'] = true;
			}

			$out['admin'] = $promo->admin()->name;

			$out['delivery_fee'] = ( intval( $out[ 'delivery_fee'] ) ) ? true : false;

			if( $out['delivery_fee'] ){
				$out['value'] = 'Delivery Fee';
			}

			$out['date_start'] = $promo->dateStart()->format( 'm/d/Y' );;
			$out['date_end'] = $promo->dateEnd()->format( 'm/d/Y' );;

			switch ( $promo->usable_by ) {
				case Crunchbutton_Promo::USABLE_BY_NEW_USERS:
					$out['usable_by'] = 'New users';
					break;
				case Crunchbutton_Promo::USABLE_BY_OLD_USERS:
					$out['usable_by'] = 'Existing users';
					break;
				case Crunchbutton_Promo::USABLE_BY_ANYONE:
					$out['usable_by'] = 'All users';
					break;
			}

			$out['active'] = ( intval( $out[ 'active'] ) ) ? true : false;
			$data[] = $out;
		}

		echo json_encode([
			'count' => intval($count),
			'pages' => ceil($count / $limit),
			'page' => $page,
			'results' => $data
		]);

	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}

}
