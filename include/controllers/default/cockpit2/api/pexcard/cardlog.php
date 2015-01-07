<?php

class Controller_api_PexCard_CardLog extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if( !c::admin()->permission()->check( ['global', 'settlement', 'support-all', 'support-crud' ] ) ){
			$this->_error();
		}

		$limit = $this->request()['limit'] ? c::db()->escape($this->request()['limit']) : 20;
		$search = $this->request()['search'] ? c::db()->escape($this->request()['search']) : '';
		$type = $this->request()['type'] ? c::db()->escape($this->request()['type']) : 'all';
		$status = $this->request()['status'] ? c::db()->escape($this->request()['status']) : 'all';
		$page = $this->request()['page'] ? c::db()->escape($this->request()['page']) : 1;

		if ($page == 1) {
			$offset = '0';
		} else {
			$offset = ($page-1) * $limit;
		}

		$q = '
				SELECT -WILD-
				FROM
				  (SELECT
				          s.name AS `staff_name`,
				          a.name AS `admin_name`,
				          aptcs.timestamp,
				          NULL AS `card_serial`,
				          new_value AS `value`,
				          "using_pex" AS `type`
				   FROM admin_payment_type_change_set aptcs
				   INNER JOIN admin_payment_type_change aptc ON aptcs.id_admin_payment_type_change_set = aptc.id_admin_payment_type_change_set
				   AND aptc.field = "using_pex"
				   INNER JOIN admin_payment_type apt ON apt.id_admin_payment_type = aptcs.id_admin_payment_type
				   INNER JOIN admin s ON s.id_admin = apt.id_admin
				   INNER JOIN admin a ON a.id_admin = aptcs.id_admin

				   UNION

				   SELECT
	                s.name AS `staff_name`,
	                a.name AS `admin_name`,
	                apcs.timestamp,
	                ap.card_serial,
	                new_value AS `value`,
	                "card_assign" AS `type`
				   FROM admin_pexcard_change_set apcs
				   INNER JOIN admin_pexcard_change apc ON apcs.id_admin_pexcard_change_set = apc.id_admin_pexcard_change_set
				   AND apc.field = "id_admin"
				   INNER JOIN admin_pexcard ap ON ap.id_admin_pexcard = apcs.id_admin_pexcard
				   INNER JOIN admin s ON s.id_admin = apc.new_value
				   INNER JOIN admin a ON a.id_admin = apcs.id_admin ) log WHERE 1 = 1
		';

		if ($type != 'all') {
			$q .= '
				AND log.type = "' . $type  . '"
			';
		}

		if ($search) {
			$q .= Crunchbutton_Query::search([
				'search' => stripslashes($search),
				'fields' => [
					'log.staff_name' => 'like',
					'log.admin_name' => 'like',
					'log.card_serial' => 'like'
				]
			]);
		}


		// get the count
		$r = c::db()->get(str_replace('-WILD-','COUNT(*) c', $q));
		$count = intval( $r->_items[0]->c );

		$q .= '
			ORDER BY log.timestamp DESC
			LIMIT '.$offset.', '.$limit . '
		';

		// do the query
		$d = [];
		$r = c::db()->query(str_replace('-WILD-','*', $q));

		while ($o = $r->fetch()) {
			$date = new DateTime($o->timestamp, new DateTimeZone(c::config()->timezone));;
			$o->date = $date->format( 'M jS Y g:i:s A T' );
			unset( $o->timestamp );
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