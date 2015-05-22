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
				$this->error(404);
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
			$this->error(401);
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

		if( $staff->isDriver() ){

			$out[ 'is_driver' ] = true;
		}
		$driver_info = $staff->driver_info()->exports();

		$driver_info[ 'student' ] = strval( $driver_info[ 'student' ] );
		$driver_info[ 'permashifts' ] = strval( $driver_info[ 'permashifts' ] );

		$driver_info[ 'iphone_type' ] = '';
		$driver_info[ 'android_type' ] = '';
		$driver_info[ 'android_version' ] = '';

		if( $driver_info[ 'phone_type' ] == 'Android' ){
			$driver_info[ 'android_type' ] = $driver_info[ 'phone_subtype' ];
			$driver_info[ 'android_version' ] = $driver_info[ 'phone_version' ];
		}
		if( $driver_info[ 'phone_type' ] == 'iPhone' ){
			$driver_info[ 'iphone_type' ] = $driver_info[ 'phone_subtype' ];
		}

		$out = array_merge( $out, $driver_info );

		$payment_type = $staff->payment_type();
		$out[ 'payment_type' ] = $payment_type->payment_type;
		$out[ 'hour_rate' ] = intval( $payment_type->hour_rate );

		if( $staff->driver_info()->pexcard_date ){
			$out[ 'pexcard_date' ] = $staff->driver_info()->pexcard_date()->format( 'Y,m,d' );
		}

		if( $out[ 'weekly_hours' ] ){
			$out[ 'weekly_hours' ] = intval( $out[ 'weekly_hours' ] );
		}

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
		$staffs = Admin::q( 'SELECT * FROM admin WHERE active = true AND phone IS NOT NULL ORDER BY name ASC' );
		foreach( $staffs as $staff ){
			$out[] = [ 'phone' => $staff->phone, 'name' => $staff->name ];
		}
		echo json_encode( $out );exit;
	}

	private function _list() {

		$limit = $this->request()['limit'] ? $this->request()['limit'] : 20;
		$search = $this->request()['search'] ? $this->request()['search'] : '';
		$page = $this->request()['page'] ? $this->request()['page'] : 1;
		$type = $this->request()['type'] ? $this->request()['type'] : '';
		$status = $this->request()['status'] ? $this->request()['status'] : 'all';
		$working = $this->request()['working'] ? $this->request()['working'] : 'all';
		$pexcard = $this->request()['pexcard'] ? $this->request()['pexcard'] : 'all';
		$community = $this->request()['community'] ? $this->request()['community'] : null;
		$getCount = $this->request()['fullcount'] && $this->request()['fullcount'] != 'false' ? true : false;
		$keys = [];

		if ($page == 1) {
			$offset = '0';
		} else {
			$offset = ($page-1) * $limit;
		}


		$q = '
			SELECT -WILD- FROM admin
		';

		$q .= '
				INNER JOIN ( SELECT admin_payment_type.* FROM admin_payment_type INNER JOIN ( SELECT MAX( max.id_admin_payment_type ) AS max_id_admin_payment_type FROM admin_payment_type AS max GROUP BY max.id_admin ) max ON max.max_id_admin_payment_type = admin_payment_type.id_admin_payment_type ORDER BY id_admin_payment_type ) apt ON apt.id_admin = admin.id_admin
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
				AND active=?
			';
			$keys[] = $status == 'active' ? true : false;
		}

		if ($community) {
			$q .= '
				AND community.id_community=?
			';
			$keys[] = $community;
		}

		if ( $pexcard != 'all' ) {
			$q .= '
				AND apt.using_pex = "'.($pexcard == 'yes' ? '1' : '0').'"
			';
		}

		if ($search) {
			$s = Crunchbutton_Query::search([
				'search' => stripslashes($search),
				'fields' => [
					'admin.name' => 'like',
					'admin.phone' => 'like',
					'admin.login' => 'like',
					'admin.email' => 'like',
					'admin.id_admin' => 'liker',
					'admin.invite_code' => 'eq'
				]
			]);
			$q .= $s['query'];
			$keys = array_merge($keys, $s['keys']);
		}

		// get the count
		$count = 0;

		// get the count
		if ($working == 'all' && $getCount) {
			$r = c::db()->query(str_replace('-WILD-','COUNT(DISTINCT `admin`.id_admin) as c', $q), $keys);
			while ($c = $r->fetch()) {
				$count = $c->c;
			}
		}

		$q .= '
			GROUP BY `admin`.id_admin
			ORDER BY `admin`.name ASC
		';
		if ($working == 'all') {
			$q .= '
				LIMIT ?
				OFFSET ?
			';
			$keys[] = $getCount ? $limit : $limit+1;
			$keys[] = $offset;
		}

		$docs = Cockpit_Driver_Document::driver();

		// do the query
		$data = [];
		$r = c::db()->query( str_replace('-WILD-','admin.*, apt.using_pex, apt.id_admin_payment_type', $q), $keys );

		$i = 1;
		while ($s = $r->fetch()) {

			if (!$export && !$getCount && $i == $limit + 1) {
				$more = true;
				break;
			}

			$admin = Admin::o( $s );

			$staff = $admin->exports(['permissions', 'groups', 'working' => ($working == 'all' ? false : true)]);

			$staff['id_admin_payment_type'] = $s->id_admin_payment_type;
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


			if ($type == 'driver') {
				$sentAllDocs = true;

				$payment_type = $admin->payment_type();

				foreach( $docs as $doc ){

					if( $doc->id_driver_document == Cockpit_Driver_Document::ID_INDY_CONTRACTOR_AGREEMENT_HOURLY &&
						$payment_type->payment_type != Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_HOURS ){
						continue;
					}

					if( $doc->id_driver_document == Cockpit_Driver_Document::ID_INDY_CONTRACTOR_AGREEMENT_ORDER &&
						$payment_type->payment_type == Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_HOURS ){
						continue;
					}

					// see: https://github.com/crunchbutton/crunchbutton/issues/3393
					if( $doc->isRequired( $staff[ 'vehicle' ] ) ){
						$docStatus = Cockpit_Driver_Document_Status::document( $admin->id_admin, $doc->id_driver_document );
						if( !$docStatus->id_driver_document_status ){
							$sentAllDocs = false;
						}
					}
				}
				$staff[ 'sent_all_docs' ] = $sentAllDocs;
			}


			$data[] = $staff;
			$i++;
		}

		if ($working == 'all') {
			$pages = ceil($count / $limit);
		} else {
			$pages = 1;
		}

		echo json_encode([
			'more' => $getCount ? $pages > $page : $more,
			'count' => intval($count),
			'pages' => $pages,
			'page' => intval($page),
			'results' => $data
		], JSON_NUMERIC_CHECK);
	}
}