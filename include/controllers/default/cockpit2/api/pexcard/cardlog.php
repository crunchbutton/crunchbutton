<?php

class Controller_api_PexCard_CardLog extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if( !c::admin()->permission()->check( ['global', 'settlement', 'support-all', 'support-crud' ] ) ){
			$this->_error();
		}

		$limit = $this->request()['limit'] ? $this->request()['limit'] : 50;
		$search = $this->request()['search'] ? $this->request()['search'] : '';
		$type = $this->request()['type'] ? $this->request()['type'] : 'all';
		$status = $this->request()['status'] ? $this->request()['status'] : 'all';
		$page = $this->request()['page'] ? $this->request()['page'] : 1;
		$keys = [];

		if ($page == 1) {
			$offset = '0';
		} else {
			$offset = ($page-1) * $limit;
		}

		$q = '
				SELECT -WILD-
				FROM
				  (SELECT
				  				s.id_admin AS `id_driver`,
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
				   				s.id_admin AS `id_driver`,
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
				AND log.type = ?
			';
			$keys[] = $type;
		}

		if ($search) {
			$s = Crunchbutton_Query::search([
				'search' => stripslashes($search),
				'fields' => [
					'log.staff_name' => 'like',
					'log.admin_name' => 'like',
					'log.card_serial' => 'like'
				]
			]);
			$q .= $s['query'];
			$keys = array_merge($keys, $s['keys']);
		}


		// get the count
		$r = c::db()->get(str_replace('-WILD-','COUNT(*) c', $q), $keys);
		$count = intval( $r->_items[0]->c );

		$q .= '
			ORDER BY log.timestamp DESC
			LIMIT '.intval($limit).'
			OFFSET '.intval($offset).'
		';

		// do the query
		$d = [];
		$r = c::db()->query(str_replace('-WILD-','*', $q), $keys);
		// echo str_replace('-WILD-','*', $q);exit;

		while ($o = $r->fetch()) {
			$driver = Admin::o( $o->id_driver );
			$communities = [];
			$_communities = $driver->communitiesHeDeliveriesFor();
			foreach ( $_communities as $community ) {
				$communities[] = $community->name;
			}
			$date = new DateTime($o->timestamp, new DateTimeZone(c::config()->timezone));;
			$o->date = $date->format( 'M jS Y g:i:s A T' );
			$o->communities = join( $communities, ', ' );
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
