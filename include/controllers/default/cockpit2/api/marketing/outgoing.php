<?php

class Controller_Api_Marketing_Outgoing extends Crunchbutton_Controller_RestAccount {

	public function init() {
		$this->_outgoing();
	}

	private function _outgoing(){

		if( !c::admin()->permission()->check( [ 'global' ] ) ){
			$this->_error();
		}

		$limit = $this->request()['limit'] ? $this->request()['limit'] : 20;
		$search = $this->request()['search'] ? $this->request()['search'] : '';
		$type = $this->request()['type'] ? $this->request()['type'] : 'all';
		$page = $this->request()['page'] ? $this->request()['page'] : 1;
		$keys = [];

		if ($page == 1) {
			$offset = '0';
		} else {
			$offset = ($page-1) * $limit;
		}

		$table_phone = 'SELECT p1.phone AS `from`, p2.phone AS `to`, pl.date, "text" AS type, pl.reason, NULL AS `subject` FROM phone_log pl
											INNER JOIN phone p1 ON p1.id_phone = pl.id_phone_from
											INNER JOIN phone p2 ON p2.id_phone = pl.id_phone_to
										WHERE pl.type = "message" AND pl.direction = "outgoing"';

		$table_email = 'SELECT ea1.email AS `from`, ea2.email AS `to`, eal.date, "email" AS type, reason, subject FROM email_address_log eal
											INNER JOIN email_address ea1 ON ea1.id_email_address = eal.id_email_address_from
											INNER JOIN email_address ea2 ON ea2.id_email_address = eal.id_email_address_to';

	switch ( $type ) {
		case 'email':
			$tables = $table_email;
			break;
		case 'phone':
			$tables = $table_phone;
			break;
		default:
			$tables = $table_phone . ' UNION ' . $table_email;
			break;
	}
		$q = '
				SELECT -WILD-
				FROM
				  ( ' . $tables . ' ) log WHERE 1 = 1
		';

		if ($type != 'all') {
			$q .= '
				AND log.type = ?
			';
			$keys[] = $type;
		}

		if ($search) {
			$s = Crunchbutton_Query::search([
				'search' => stripslashes($search),
				'fields' => [
					'log.from' => 'like',
					'log.to' => 'like',
					'log.reason' => 'like',
					'log.subject' => 'like'
				]
			]);
			$q .= $s['query'];
			$keys = array_merge($keys, $s['keys']);
		}

		// get the count
		$r = c::db()->get(str_replace('-WILD-','COUNT(*) c', $q), $keys);
		$count = intval( $r->_items[0]->c );

		$q .= '
			ORDER BY log.date DESC
			LIMIT '.intval($limit).'
			OFFSET '.intval($offset).'
		';

		// do the query
		$d = [];
		$r = c::db()->query(str_replace('-WILD-','*', $q), $keys);

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

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}

}
