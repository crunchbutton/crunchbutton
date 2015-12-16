<?php

class Controller_api_credit_log extends Crunchbutton_Controller_RestAccount {

	public function init() {

		$this->_permission();

		switch ( $this->method() ) {
			case 'get':
				$this->_get();
			break;
			default:
				$this->_error();
		}

	}

	private function _get(){

		$this->_set_user( c::getPagePiece( 3 ) );

		switch ( c::getPagePiece( 4 ) ) {
			case 'points':
				// needs to be implemented
				break;
			case 'credits-points':
				// needs to be implemented
				break;
			case 'credits-debits':
				// needs to be implemented
				break;
			case 'status':
				$this->_status();
				break;
			case 'history':
			default:
				$this->_credits();
		}
	}

	private function _credits(){

		$out = [];

		$limit = $this->request()['limit'] ? $this->request()['limit'] : 20;
		$page = $this->request()['page'] ? intval( $this->request()['page'] ) : 1;

		$keys = [ $this->user()->id_user, Crunchbutton_Credit::CREDIT_TYPE_CASH ];

		$query = 'SELECT -WILD- FROM credit WHERE id_user=? AND ( credit_type = ? ) ORDER BY id_credit DESC';

		$r = c::db()->get( str_replace( '-WILD-', 'COUNT(*) AS c', $query ), $keys )->get( 0 );
		$count = intval( $r->c );
		$pages = ceil( $count / $limit );

		if ($page > 1) {
			$limit = ( ( $page -1 ) * $limit ) . ',' . $limit;
		}

		$query .= ' LIMIT '.intval($limit);

		$credits = Crunchbutton_Credit::q( ( str_replace( '-WILD-', ' credit.* ', $query ) ), $keys );

		if( $credits->count() > 0 ){

			$format_date = 'Y-m-d H:i:s';

			foreach ( $credits as $credit ) {
				$_credit = [];

				$_credit[ 'id_credit' ] = $credit->id_credit;
				$_credit[ 'value' ] = floatval( $credit->value );
				$_credit[ 'date' ] = Crunchbutton_Util::dateToUnixTimestamp( $credit->date() );
				$_credit[ 'type' ] = ucfirst( strtolower( $credit->type ) );
				if( $credit->type == Crunchbutton_Credit::TYPE_CREDIT ){
					$_credit[ 'left' ] = floatval( $credit->creditLeft() );
					$_credit[ 'note' ] = $credit->note;
					if( $credit->id_promo ){
						$promo = $credit->promo();
						$_credit[ 'promo' ] = [];
						$_credit[ 'promo' ][ 'id_promo' ] = $promo->id_promo;
						$_credit[ 'promo' ][ 'code' ] = $promo->code;
						$_credit[ 'promo' ][ 'note' ] = $promo->note;
						$_credit[ 'promo' ][ 'date' ] = Crunchbutton_Util::dateToUnixTimestamp( $promo->date() );
						if( $promo->id_admin ){
							$admin = $promo->admin();
							$_credit[ 'promo' ][ 'id_admin' ] = $admin->id_admin;
							$_credit[ 'promo' ][ 'name' ] = $admin->name;
						}
					}
					/*
					$_credit[ 'debits' ] = [];
					$debits = $credit->debitsFromCredit();
					if( $debits->count() > 0 ){
						foreach( $debits as $debit ){
							$_debit = [];
							$_debit[ 'id_order' ] = $debit->id_order;
							$_debit[ 'value' ] = floatval( $debit->value );
							$_debit[ 'date' ] = $debit->date()->format( $format_date );
							$_credit[ 'debits' ][] = $_debit;
						}
					}
					*/
				} else {
					$_credit[ 'value' ] = $_credit[ 'value' ] * -1;
				}
				$_credit[ 'id_order' ] = $credit->id_order;
				$out[] = $_credit;
			}
		}

		echo json_encode( [
			'count' => intval( $count ),
			'pages' => $pages,
			'page' => intval($page),
			'results' => $out
		] );
	}

	private function _status(){
		$status = Crunchbutton_Credit::creditsByIdUser( $this->user()->id_user );
		$status->credit = floatval( $status->credit );
		$status->credit_left = floatval( $status->credit_left );
		$status->debit = $status->debit * -1;
		echo json_encode( $status );exit;
	}

	private function _set_user( $id_user ){
		if( $id_user ){
			$user = User::o( $id_user );
			if( $user->id_user ){
				$this->_user = $user;
				return $this->user();
			}
		}
		$this->_error();
	}

	public function user(){
		return $this->_user;
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}

	private function _permission(){
		if (!c::admin()->permission()->check(['global', 'gift-card-all', 'gift-card-create-all', 'support-all', 'support-view', 'support-crud' ])) {
			header('HTTP/1.1 401 Unauthorized');
			exit;
		}
	}


}
