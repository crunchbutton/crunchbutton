<?php

class Controller_api_PexCard_Log extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if( !c::admin()->permission()->check( ['global', 'settlement', 'support-all', 'support-crud' ] ) ){
			$this->_error();
		}

		if( $this->request()['id_pexcard_action'] ){
			$this->_load_action( $this->request()['id_pexcard_action'] );
		}

		$limit = $this->request()['limit'] ? $this->request()['limit'] : 20;
		$search = $this->request()['search'] ? $this->request()['search'] : '';
		$type = $this->request()['type'] ? $this->request()['type'] : 'all';
		$status = $this->request()['status'] ? $this->request()['status'] : 'all';
		$action = $this->request()['_action'] ? $this->request()['_action'] : 'all';
		$page = $this->request()['page'] ? $this->request()['page'] : 1;
		$keys = [];

		if ($page == 1) {
			$offset = '0';
		} else {
			$offset = ($page-1) * $limit;
		}


		$q = '
			SELECT -WILD- FROM
				pexcard_action pa
				INNER JOIN admin_pexcard ap ON ap.id_admin_pexcard = pa.id_admin_pexcard
				INNER JOIN admin a ON a.id_admin = pa.id_driver
				WHERE 1 = 1
		';

		if ($status != 'all') {
			$q .= '
				AND pa.status = ?
			';
			$keys[] = $status;
		}

		if ($type != 'all') {
			$q .= '
				AND pa.type = ?
			';
			$keys[] = $type;
		}

		if ($action != 'all') {

			switch ( true ) {

				case ( $action == 'shift' ):
					$q .= '
						AND ( pa.action = "' . Crunchbutton_Pexcard_Action::ACTION_SHIFT_STARTED . '" OR pa.action = "' . Crunchbutton_Pexcard_Action::ACTION_SHIFT_FINISHED . '" )
					';
					break;

				case ( $action == 'order' ):
					$q .= '
						AND ( pa.action = "' . Crunchbutton_Pexcard_Action::ACTION_ORDER_ACCEPTED . '" OR pa.action = "' . Crunchbutton_Pexcard_Action::ACTION_ORDER_CANCELLED . '" )
					';
					break;

				default:
					$q .= '
						AND ( pa.action = ? )
					';
					$keys[] = $action;
					break;
			}
		}


		if ($search) {
			$s = Crunchbutton_Query::search([
				'search' => stripslashes($search),
				'fields' => [
					'a.name' => 'like',
					'pa.amount' => 'like',
					'pa.note' => 'like',
					'ap.id_pexcard' => 'like',
					'ap.card_serial' => 'like',
					'ap.last_four' => 'like'
				]
			]);
			$q .= $s['query'];
			$keys = array_merge($keys, $s['keys']);
		}


		// get the count
		$r = c::db()->get(str_replace('-WILD-','COUNT(*) c', $q), $keys);
		$count = intval( $r->_items[0]->c );

		$q .= '
			ORDER BY pa.id_pexcard_action DESC
			LIMIT '.intval($limit).'
			OFFSET '.intval($offset).'
		';

		// do the query
		$d = [];
		$r = c::db()->query(str_replace('-WILD-','
				pa.id_pexcard_action,
				pa.date,
				pa.status,
				a.name AS `driver`,
				a.login,
				pa.id_order,
				pa.id_admin_shift_assign,
				pa.id_admin,
				pa.amount,
				pa.action,
				pa.type,
				pa.note,
				pa.status,
				ap.id_pexcard,
				ap.card_serial,
				ap.last_four
			', $q), $keys);

		while ($o = $r->fetch()) {
			$date = new DateTime($o->date, new DateTimeZone(c::config()->timezone));;
			$o->date = $date->format( 'M jS Y g:i:s A T' );
			$d[] = $o;
		}

		echo json_encode([
			'count' => intval($count),
			'pages' => ceil($count / $limit),
			'page' => $page,
			'results' => $d
		]);

		exit;
	}


	private function _load_action( $id_pexcard_action ){
		$action = Crunchbutton_Pexcard_Action::o( $id_pexcard_action );
		if( $action->id_pexcard_action ){
			echo json_encode( [ 'success' => $action->exports() ] ); exit();
		} else {
			$this->_error();
		}
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}

}
