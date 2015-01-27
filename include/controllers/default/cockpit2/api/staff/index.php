<?php

class Controller_api_staff extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if (c::getPagePiece(2)) {

			if( c::getPagePiece(2) == 'phones' ){
				$this->_phones();
			}

			$staff = Admin::o(c::getPagePiece(2));

			if (!$staff->id_admin) {
				$staff = Admin::login(c::getPagePiece(2), true);
			}

			if (!$staff->id_admin) {
				header('HTTP/1.0 404 Not Found');
				exit;
			}

			switch (c::getPagePiece(3)) {
				case 'locations':
					$this->_permissionDenied();
					$this->_locations($staff);
					break;

				case 'status':
					$this->_status($staff);
					break;

				case 'has_pexcard':
					$this->_permissionDenied();
					$this->_has_pexcard($staff);
					break;

				default:
					$this->_permissionDenied();
					$this->_view($staff);
					break;
			}

		} else {
			$this->_list();
		}

	}

	private function _permissionDenied(){
		if (!c::admin()->permission()->check(['global', 'permission-all', 'permission-users'])) {
			header('HTTP/1.1 401 Unauthorized');
			exit;
		}
	}

	private function _locations($staff) {
		echo $staff->locations()->json();
	}

	private function _status($staff) {
		echo json_encode($staff->status());
	}

	private function _has_pexcard( $staff ){
		$payment_type = $staff->payment_type();
		echo json_encode( [ 'success' => ( $payment_type->using_pex > 0 ? true : false ) ] );
	}

	private function _view($staff) {
		$out = $staff->exports();
		$cards = Cockpit_Admin_Pexcard::getByAdmin( $staff->id_admin )->get( 0 );
		$out[ 'pexcard' ] = ( $cards && count( $cards ) > 0 );

		/*
		$out['shifts'] = [];

//		$current = Community_Shift::getCurrentShiftByAdmin($staff->id_admin)->get(0);
		$next = Community_Shift::nextShiftsByAdmin($staff->id_admin);

		if ($next) {
			foreach ($next as $shift) {
				$shift = $shift->exports();
				if (strtotime($shift['date_start']) <= time() ) {
					$shift['current'] = true;
				} else {
					$shift['current'] = false;
				}
				$out['shifts'][] = $shift;
			}
		}
*/
		echo json_encode($out);
	}

	private function _phones(){
		$out = [];
		$staffs = Admin::q( 'SELECT * FROM admin WHERE active = 1 AND phone IS NOT NULL ORDER BY name ASC' );
		foreach( $staffs as $staff ){
			$out[] = [ 'phone' => $staff->phone, 'name' => $staff->name ];
		}
		echo json_encode( $out );exit;
	}

	private function _list() {

		$limit = $this->request()['limit'] ? c::db()->escape($this->request()['limit']) : 20;
		$search = $this->request()['search'] ? c::db()->escape($this->request()['search']) : '';
		$page = $this->request()['page'] ? c::db()->escape($this->request()['page']) : 1;
		$type = $this->request()['type'] ? c::db()->escape($this->request()['type']) : '';
		$status = $this->request()['status'] ? c::db()->escape($this->request()['status']) : 'all';
		$working = $this->request()['working'] ? c::db()->escape($this->request()['working']) : 'all';
		$pexcard = $this->request()['pexcard'] ? c::db()->escape($this->request()['pexcard']) : 'all';
		$community = $this->request()['community'] ? c::db()->escape($this->request()['community']) : null;

		if ($page == 1) {
			$offset = '0';
		} else {
			$offset = ($page-1) * $limit;
		}


		$q = '
			SELECT -WILD- FROM admin
		';

		$q .= '
				INNER JOIN admin_payment_type apt ON apt.id_admin = admin.id_admin
				';

		if ($type == 'driver') {
			$q .= '
				INNER JOIN admin_group ag ON ag.id_admin=admin.id_admin
				INNER JOIN `group` g ON g.id_group=ag.id_group AND g.name LIKE "' . Crunchbutton_Group::DRIVER_GROUPS_PREFIX . '%"
			';

			if ($community) {
				$q .= '
					LEFT JOIN community ON community.driver_group=g.name
				';
			}
		}

		if( $type == 'marketing-rep'  ){
			$q .= '
				INNER JOIN admin_group ag ON ag.id_admin=admin.id_admin
				INNER JOIN `group` g ON g.id_group=ag.id_group AND g.type = "' . Crunchbutton_Group::TYPE_DRIVER . '"
			';
		}

		$q .='
			WHERE 1=1
		';

		if ($status != 'all') {
			$q .= '
				AND active="'.($status == 'active' ? '1' : '0').'"
			';
		}

		if ($community) {
			$q .= '
				AND community.id_community="'.$community.'"
			';
		}

		if ( $pexcard != 'all' ) {
			$q .= '
				AND apt.using_pex = "'.($pexcard == 'yes' ? '1' : '0').'"
			';
		}

		if ($search) {
			$q .= Crunchbutton_Query::search([
				'search' => stripslashes($search),
				'fields' => [
					'admin.name' => 'like',
					'admin.phone' => 'like',
					'admin.login' => 'like',
					'admin.email' => 'like',
					'admin.id_admin' => 'liker'
				]
			]);
		}

		// get the count
		$count = 0;
		if ($working == 'all') {
			$r = c::db()->query(str_replace('-WILD-','COUNT(*) c', $q));
			while ($c = $r->fetch()) {
				$count = $c->c;
			}
		}

		$q .= '
			GROUP BY `admin`.id_admin
			ORDER BY `admin`.name ASC
		';
		if ($working == 'all') {
			$q .= ' LIMIT '.$offset.', '.$limit;
		}

		// do the query
		$data = [];
		$r = c::db()->query(str_replace('-WILD-','admin.*, apt.using_pex', $q));
		while ($s = $r->fetch()) {

			$admin = Admin::o($s);

			$staff = $admin->exports(['permissions', 'groups']);

			$staff['pexcard'] = ( $s->using_pex ) ? true : false;

			if( $staff['pexcard'] ){
				$pexcard = Cockpit_Admin_Pexcard::getByAdmin( $staff[ 'id_admin' ] );
				$pexcard = $pexcard->get( 0 );
				$staff['pexcard'] = [ 'card_serial' => $pexcard->card_serial, 'last_four' => $pexcard->last_four ];
			}

			if ( 	( $working == 'yes' && $staff[ 'working' ] ) ||
						( $working == 'no' && !$staff[ 'working' ] ) ||
						( $working == 'today' && $staff[ 'working_today' ] ) ) {
				$count++;
			}

			if ( 	( $working == 'yes' && !$staff[ 'working' ] ) ||
						( $working == 'no' && $staff[ 'working' ] ) ||
						( $working == 'today' && !$staff[ 'working_today' ] ) ) {
				continue;
			}

			$unset = ['email','timezone','testphone','txt'];
			foreach ($unset as $un) {
				unset($staff[$un]);
			}

			$staff[ 'isMarketingRep' ] = $admin->isMarketingRep();
			$staff[ 'isSupport' ] = $admin->isSupport();
			$staff[ 'isDriver' ] = $admin->isDriver();

			if( $staff[ 'isDriver' ] ){
				$staff[ 'type' ] = 'Driver';
			}

			if( $staff[ 'isSupport' ] ){
				$commas = '';
				if( $staff[ 'type' ] ){
					$commas = ', ';
				}
				$staff[ 'type' ] = $commas . 'Support';
			}

			if( $staff[ 'isMarketingRep' ] ){
				$commas = '';
				if( $staff[ 'type' ] ){
					$commas = ', ';
				}
				$staff[ 'type' ] = $commas . 'Marketing Rep';
			}

			$data[] = $staff;
		}

		if ($working == 'all') {
			$pages = ceil($count / $limit);
		} else {
			$pages = 1;
		}

		echo json_encode([
			'count' => intval($count),
			'pages' => $pages,
			'page' => $page,
			'results' => $data
		]);
	}
}