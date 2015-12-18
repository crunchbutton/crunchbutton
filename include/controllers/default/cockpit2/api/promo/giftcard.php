<?php

class Controller_api_promo_giftcard extends Crunchbutton_Controller_RestAccount {

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
		if (!c::admin()->permission()->check([ 'global', 'gift-card-all', 'gift-card-create-all' ])) {
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

		switch ( c::getPagePiece( 3 ) ) {
			case 'create':
				$this->_create();
				break;

			case 'generate':
				$this->_generate();
				break;
		}
	}

	private function _create(){

		$random_code = intval( $this->request()[ 'random_code' ] );
		if( !$random_code ){

			$code = trim( $this->request()[ 'code' ] );
			if( strlen( $code ) < 7 ){
				echo json_encode( [ 'error' => 'The code must to have at least 7 chars.' ] );exit;
			}

			$giftcard = Crunchbutton_Promo::byCode( $code )->get( 0 );
			if( $giftcard->id_promo ){
				echo json_encode( [ 'error' => 'This code was already taken, please type another one!' ] );exit;
			}
		}

		$value = intval( $this->request()[ 'value' ] );
		$id_admin = c::user()->id_admin;
		$created_by = c::user()->login;
		$paid_by = $this->request()[ 'paid_by' ];
		$phone = $this->request()[ 'phone' ];
		$notify_phone = $this->request()[ 'notify_phone' ];

		if( $notify_phone ){
			if( !Phone::clean( $phone ) ){
				echo json_encode( [ 'error' => 'Enter a valid phone number!' ] );exit;
			}
		}

		$giftcard = new Crunchbutton_Promo;
		$giftcard->value = $value;
		$giftcard->type = Crunchbutton_Promo::TYPE_GIFTCARD;
		$giftcard->active = 1;
		$giftcard->id_admin = $id_admin;
		$giftcard->paid_by = $paid_by;
		$giftcard->created_by = $created_by;
		$giftcard->amount_type = 'cash';
		$giftcard->date = date('Y-m-d H:i:s');
		$giftcard->phone = $phone;
		$giftcard->save();
		if( $code ){
			$giftcard->code = $code;
		} else {
			$giftcard->code = $giftcard->promoCodeGeneratorUseChars( '123456789', 7, $giftcard->id_promo, '' );
		}

		if( $notify_phone && $giftcard->phone ){
			$giftcard->queNotifySMS();
		}

		$giftcard->save();


		echo json_encode( [ 'success' => true ] );exit;
	}


	private function _generate(){

		$value = intval( $this->request()[ 'value' ] );
		$total = intval( $this->request()[ 'total' ] );
		$id_admin = c::user()->id_admin;
		$created_by = c::user()->login;
		$include_gift_card_id = $this->request()[ 'include_gift_card_id' ];
		$chars_to_use = $this->request()[ 'chars_to_use' ];
		$paid_by = $this->request()[ 'paid_by' ];
		$exclude_chars = $this->request()[ 'exclude_chars' ];
		$length = intval( $this->request()[ 'chars_length' ] );
		$prefix = $this->request()[ 'prefix' ];


		for( $i = 1; $i<= $total; $i++) {
			$giftcard = new Crunchbutton_Promo;
			$giftcard->value = $value;
			$giftcard->type = Crunchbutton_Promo::TYPE_GIFTCARD;
			$giftcard->active = 1;
			$giftcard->id_admin = $id_admin;
			$giftcard->paid_by = $paid_by;
			$giftcard->created_by = $created_by; // legacy
			$giftcard->amount_type = 'cash';
			if( $id_community ){
				$giftcard->id_community = $id_community;
			}
			$giftcard->date = date('Y-m-d H:i:s');
			$giftcard->save();

			if( $include_gift_card_id > 0 ){
				$giftcard->code = $giftcard->promoCodeGeneratorUseChars( $chars_to_use, $length, $giftcard->id_promo, $prefix );
			} else {
				$giftcard->code = $giftcard->promoCodeGeneratorUseChars( $chars_to_use, $length, '', $prefix );
			}
			$giftcard->save();
		}

		echo json_encode( [ 'success' => $i ] );exit;
	}

	private function _list(){

		$limit = $this->request()[ 'limit' ] ? $this->request()[ 'limit' ] : 20;
		$search = $this->request()[ 'search' ] ? $this->request()[ 'search' ] : '';
		$page = $this->request()[ 'page' ] ? $this->request()[ 'page' ] : 1;
		$redeemed = $this->request()[ 'redeemed' ] ? $this->request()[ 'redeemed' ] : null;
		$status = $this->request()[ 'status' ] ? $this->request()[ 'status' ] : null;

		if( $redeemed == 'all' ){
			$redeemed = null;
		}

		if( $status == 'all' ){
			$status = null;
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
			LEFT JOIN admin a ON a.id_admin = p.id_admin
			LEFT JOIN user u ON u.id_user = p.id_user
		';

		$q .='
			WHERE
				p.code IS NOT NULL AND ( p.is_discount_code = false OR p.is_discount_code = 0 OR p.is_discount_code IS NULL )
		';

		if ($redeemed) {
			if( $redeemed == 'no' ){
				$q .= '
					AND p.id_user IS NULL
				';
			} else {
				$q .= '
					AND p.id_user IS NOT NULL
				';
			}
		}

		if ($status) {
			if( $status == 'active' ){
				$q .= '
					AND p.active = 1
				';
			} else {
				$q .= '
					AND p.active = 0
				';
			}
		}

		if ($search) {
			$s = Crunchbutton_Query::search([
				'search' => stripslashes($search),
				'fields' => [
					'p.code' => 'like',
					'a.name' => 'like',
					'u.name' => 'like',
				]
			]);
			$q .= $s[ 'query' ];
			$keys = array_merge($keys, $s[ 'keys' ]);
		}

		// get the count
		$r = c::db()->get(str_replace('-WILD-','COUNT(*) c', $q), $keys)->get(0);
		$count = intval( $r->c );

		$q .= '
			ORDER BY p.id_promo DESC
			LIMIT '.intval($limit).'
			OFFSET '.intval($offset).'
		';

		// do the query
		$data = [];
		$r = c::db()->query(str_replace('-WILD-','
			p.id_promo, p.code, p.id_user, p.value, p.date, p.id_admin, p.active, a.name as admin, u.name as user, a.login as login, p.phone, p.note
		', $q), $keys);

		while ($s = $r->fetch()) {
			$s->active = intval( $s->active ) ? true : false;
			$s->value = floatval( $s->value );
			$s->id_promo = floatval( $s->id_promo );
			$s->id_admin = floatval( $s->id_admin );
			$s->redeemed = floatval( $s->id_user ) ? true : false;
			if( $s->note ){
				$s->note = nl2br( $s->note );
			}
			if( $s->phone ){
				$s->phone = Phone::formatted( $s->phone );
			}
			$data[] = $s;
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
