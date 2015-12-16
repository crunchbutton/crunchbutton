<?php

class Controller_api_staff extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if( !c::admin()->permission()->check( ['global', 'permission-users'] ) && !c::admin()->isCampusManager() ){

			if( c::getPagePiece(3) == 'status' ){
				$staff = Admin::o(c::user()->id_admin);
				$this->_status($staff);
				exit;
			}

			if( c::getPagePiece(3) == 'has_pexcard' ){
				$staff = Admin::o(c::user()->id_admin);
				$this->_has_pexcard($staff);
				exit;
			}

			$this->error( 401 );
		}

		if (c::getPagePiece(2) && c::getPagePiece(2) != 'support' ) {

			switch ( c::getPagePiece(2) ) {
				case 'phones':
					$this->_phones();
					break;

				case 'active':
					$this->_active();
					break;

				case 'notes-list':
					$this->_listNotes();
					break;

				case 'support':
					$this->_listSupport();
					break;

				default:

					$staff = Admin::o((int)c::getPagePiece(2));

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

						case 'change-status':
							$this->_change_status($staff);
							break;

						case 'group':
							$this->_permissionDenied();
							$this->_isPost();
							$this->_group($staff);
							break;

						case 'text-message-about-schedule':
							$this->_permissionDenied();
							$this->_isPost();
							$this->_textMessageAboutSchedule($staff);
							break;

						case 'note':
							$this->_permissionDenied();
							if( $this->method() == 'post' ){
								$this->_saveLastNote($staff);
							} else {
								$this->_openLastNote($staff);
							}
							break;

						case 'community':
							$this->_permissionDenied();
							$this->_isPost();
							$this->_community($staff);
							break;

						case 'has_pexcard':
							$this->_permissionDenied();
							$this->_has_pexcard($staff);
							break;

						case 'reverify':
							$this->_permissionDenied();
							$this->_reverify($staff);
							break;

						case 'support':
							$this->_listSupport();
							break;

						case 'notes-list':
							$this->_listNotes();
							break;

						case 'chat':
							$this->_chat($staff);
							break;

						default:
							$this->_permissionDenied();
							$this->_view($staff);
							break;
					}
					break;
			}

		} else {

			if( c::getPagePiece(2) == 'support' ){
				$this->_listSupport();
			} else {
				$this->_list();
			}
		}
	}

	private function _chat( $staff ){
		$params = [];
		$params[ 'Action' ] = 'FakeSMS';
		$params[ 'Name' ] = $staff->name;
		$params[ 'Created_By' ] = c::admin()->firstName();
		$params[ 'Body' ] = null;
		$params[ 'ignoreFistMessage' ] = true;
		$params[ 'ignoreReply' ] = true;
		$params[ 'chatMessage' ] = '(new chat)';
		$params[ 'From' ] = $staff->phone;
		$support = Crunchbutton_Support::createNewChat( $params );
		if( $support->id_support ){
			 $support->status = Crunchbutton_Support::STATUS_OPEN;
			 $support->save();
				echo json_encode( [ 'id_support' => $support->id_support ] );
		} else {
			echo json_encode( [ 'error' => 'Error creating new chat' ] );
		}

	}

	private function _listNotes(){
		$out = [];
		$admins = Admin::q( "SELECT DISTINCT( a.id_admin ), a.name FROM admin a
													INNER JOIN admin_note an ON an.id_admin = a.id_admin
													ORDER BY a.name" );
		foreach( $admins as $admin ){
			$out[] = [ 'id_admin' => $admin->id_admin, 'name' => $admin->name ];
		}
		echo json_encode( $out );exit;
	}

	private function _active(){
		$out = [];
		$admins = Admin::q( "SELECT a.id_admin, a.name FROM admin a
													WHERE a.active = 1 AND a.name IS NOT NULL AND a.name != ''
													ORDER BY a.name" );
		foreach( $admins as $admin ){
			$out[] = [ 'id_admin' => $admin->id_admin, 'name' => $admin->name ];
		}
		echo json_encode( $out );exit;	}

	private function _listSupport(){
		$out = [];
		$admins = Admin::q( "SELECT a.id_admin, a.name FROM admin a
													INNER JOIN ( SELECT DISTINCT( phone ) FROM support WHERE id_user IS NULL AND phone IS NOT NULL ) phones ON phones.phone = a.phone
													WHERE a.name IS NOT NULL AND a.name != '' AND a.name NOT LIKE '%test%' AND a.active = 1
													ORDER BY a.name" );
		foreach( $admins as $admin ){
			$out[] = [ 'id_admin' => $admin->id_admin, 'name' => $admin->name ];
		}
		echo json_encode( $out );exit;
	}

	private function _permissionDenied(){
		if (!c::admin()->permission()->check(['global', 'permission-all', 'permission-users'])) {
			$this->error(401);
		}
	}

	private function _reverify($staff) {
		$reverify = $staff->autoStripeVerify($this->request()['force'] ? true : false);
		$status = $staff->stripeVerificationStatus();
		echo json_encode(['stripe_id' => $staff->payment_type()->stripe_id, 'reverify' => $reverify, 'status' => $status]);
	}

	private function _locations($staff) {
		echo $staff->locations()->json();
	}

	private function _group( $staff ){
		$staff->removeGroups();
		$groups = $this->request()[ 'groups' ];
		if( $groups ){
			foreach ( $groups as $group ) {
				$new = new Crunchbutton_Admin_Group();
				$new->id_admin = $staff->id_admin;
				$new->id_group = intval( $group );
				$new->save();
			}
		}
		echo json_encode( [ 'success' => true ] );
	}

	private function _openLastNote( $staff ){
		$note = $staff->note();
		if( $note->id_admin_note ){
			$out = $note->exports();
			$out[ 'driver' ] = $staff->name;
			echo json_encode( $out );exit;
		} else {
			echo json_encode( [ 'id_admin' => $staff->id_admin, 'driver' => $staff->name, 'text' => '' ] );exit;
		}
	}

	private function _saveLastNote( $staff ){
		if( $staff->id_admin ){
			$text = $this->request()[ 'text' ];
			$staff->addNote( $text );
			$this->_openLastNote( $staff );
		} else {
			echo json_encode( [ 'error' => 'invalid object' ] );
		}
	}

	private function _change_status( $staff ){
		if( $staff->id_admin ){
			$staff->active = ( boolval( $this->request()[ "active" ] ) ? 1 : 0 );
			if( !$staff->active ){
				$staff->date_terminated = date( 'Y-m-d' );
			} else {
				$staff->date_terminated = null;
			}
			$staff->save();
			echo json_encode( [ 'success' => 'true' ] );
		} else {
			echo json_encode( [ 'error' => 'invalid object' ] );
		}
	}

	private function _textMessageAboutSchedule( $staff ){
		if( $staff->id_admin ){
			$value = $this->request()[ 'value' ];
			$value = ( $value && $value > 0 ) ? 1 : 0;
			$staff->setConfig( Crunchbutton_Admin::CONFIG_RECEIVE_DRIVER_SCHEDULE_SMS_WARNING, $value );
			echo json_encode( [ 'success' => 'success' ] );
		} else {
			echo json_encode( [ 'error' => 'invalid object' ] );
		}
	}

	private function _community( $staff ){
		$communities = $staff->communitiesHeDeliveriesFor();
		foreach( $communities as $community ){
			$group = $community->groupOfDrivers();
			if( $group->id_group ){
				$staff->removeGroup( $group->id_group );
			}
		}

		$communities = $this->request()[ 'communities' ];

		// relate the communities with the driver
		if( count( $communities ) > 0 && $communities != '' ){
			foreach ( $communities as $community ) {
				$community = Crunchbutton_Community::o( $community );
				if( $community->id_community ){
					$group = $community->groupOfDrivers();
					$adminGroup = new Crunchbutton_Admin_Group();
					$adminGroup->id_admin = $staff->id_admin;
					$adminGroup->id_group = $group->id_group;
					$adminGroup->save();
				}
			}
		}
		echo json_encode( [ 'success' => true ] );
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

		$out[ 'isMarketingRep' ] = $staff->isMarketingRep();
		$out[ 'isSupport' ] = $staff->isSupport();
		$out[ 'isDriver' ] = $staff->isDriver();
		$out[ 'isCampusManager' ] = $staff->isCampusManager();
		$out[ 'address' ] = $staff->address;

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
		if( $out[ 'payment_type' ] == Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_HOURS ||
		 		$out[ 'payment_type' ] == Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_HOURS_WITHOUT_TIPS ||
		 		$out[ 'payment_type' ] == Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_MAKING_WHOLE ){
			$out[ 'hour_rate' ] = intval( $payment_type->hour_rate );
		}

		if( $out[ 'payment_type' ] == Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_ORDERS ||
		 		$out[ 'payment_type' ] == Crunchbutton_Admin_Payment_Type::PAYMENT_TYPE_MAKING_WHOLE ){
			$out[ 'amount_per_order' ] = $payment_type->amountPerOrder();
		}



		$out['stripe_id'] = $payment_type->stripe_id;

		if( $staff->driver_info()->pexcard_date ){
			$out[ 'pexcard_date' ] = $staff->driver_info()->pexcard_date()->format( 'Y,m,d' );
		}

		if( $out[ 'weekly_hours' ] ){
			$out[ 'weekly_hours' ] = intval( $out[ 'weekly_hours' ] );
		}

		$note = $staff->lastNote();
		if( $note ){
			$out[ 'note' ] = $note->exports();
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
		$staffs = Admin::q( 'SELECT * FROM admin WHERE active = true AND phone IS NOT NULL AND name IS NOT NULL AND name != "" AND active = 1 ORDER BY name ASC' );
		foreach( $staffs as $staff ){
			$out[] = [ 'phone' => $staff->phone, 'name' => $staff->name ];
		}
		echo json_encode( $out );exit;
	}

	private function _isPost(){
		if( $this->method() != 'post' ){
			$this->error(404);
		}
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
		$group = $this->request()['group'] ? $this->request()['group'] : null;
		$send_text = $this->request()['send_text'] ? $this->request()['send_text'] : null;
		$getCount = $this->request()['fullcount'] && $this->request()['fullcount'] != 'false' ? true : false;
		$keys = [];

		if( ( !c::admin()->permission()->check(['global']) && c::admin()->isCampusManager() ) ){
			$brandreps = $this->request()['brandreps'] ? $this->request()['brandreps'] : false;
			$drivers = $this->request()['drivers'] ? $this->request()['drivers'] : false;
			if( $brandreps ){
				$type = 'marketing-rep';
				$community = c::user()->getMarketingRepGroups();
			}
			if( $drivers ){
				$type = 'driver';
				$community = c::user()->getMarketingRepGroups();
			}
		}

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
			INNER JOIN phone p ON admin.id_phone = p.id_phone
		';

		if( $group ){
			$q .= '
				INNER JOIN admin_group ag ON ag.id_admin = admin.id_admin AND ag.id_group = ?
			';
			$keys[] = $group;
		}


		if ($type == 'driver') {
			$q .= '
				INNER JOIN admin_group ag ON ag.id_admin=admin.id_admin
				INNER JOIN `group` g ON g.id_group=ag.id_group AND g.name LIKE ?
			';
			$keys[] = Crunchbutton_Group::DRIVER_GROUPS_PREFIX . '%';

			if ($community) {
				$q .= '
					LEFT JOIN community ON community.id_community=g.id_community
				';
			}
			$q .= '
					LEFT JOIN admin_config ac ON ac.id_admin = admin.id_admin AND ac.key = ?
			';
			$keys[] = Crunchbutton_Admin::CONFIG_RECEIVE_DRIVER_SCHEDULE_SMS_WARNING;
		} else {
			if ($community) {

				$q .= '
					INNER JOIN admin_group ag ON ag.id_admin=admin.id_admin
					INNER JOIN `group` g ON g.id_group=ag.id_group
				';

				$q .= '
					LEFT JOIN community ON community.id_community=g.id_community
				';
			}
		}

		if( !$community && $type == 'marketing-rep'  ){
			$q .= '
				INNER JOIN admin_group ag ON ag.id_admin=admin.id_admin
				INNER JOIN `group` g ON g.id_group=ag.id_group AND g.type = ?
			';
			$keys[] = Crunchbutton_Group::TYPE_MARKETING_REP;
		}

		if( !$community && $type == 'community-manager'  ){
			$q .= '
				INNER JOIN admin_group ag ON ag.id_admin=admin.id_admin
				INNER JOIN `group` g ON g.id_group=ag.id_group AND g.name = ?
			';
			$keys[] = Crunchbutton_Group::CAMPUS_MANAGER_GROUP;
		}

		if( $working != 'all' ){
			$q .= '
							INNER JOIN admin_shift_assign asa ON asa.id_admin = admin.id_admin
							INNER JOIN community_shift cs ON cs.id_community_shift = asa.id_community_shift AND DATE( cs.date_start ) = DATE( NOW() )
					';
		}


		$q .='
			WHERE 1=1
		';

		if ($status != 'all') {
			$q .= '
				AND admin.active=?
			';
			$keys[] = $status == 'active' ? true : false;
			//$keys[] = $status == 'active' ? 'true' : 'false';
		}

		if ($community) {
			$q .= '
				AND community.id_community=?
			';
			$keys[] = $community;
		}

		if ( $pexcard != 'all' ) {
			$q .= '
				AND apt.using_pex = ?
			';
			$keys[] = $pexcard == 'yes' ? true : false;
		}

		if( $community && $type == 'marketing-rep'  ){
			$q .= '
				AND g.type = ?
			';
			$keys[] = Crunchbutton_Group::TYPE_MARKETING_REP;
		}

		if( $community && $type == 'community-manager'  ){
			$q .= '
				AND g.name = ?
			';
			$keys[] = Crunchbutton_Group::CAMPUS_MANAGER_GROUP;
		}

		if ($type == 'driver') {
			if( !is_null( $send_text ) && $send_text != 'all' ){
				if( intval( $send_text ) == 0 ){
					$q .= '
						AND ac.value IS NULL OR ac.value = \'0\'
					';
				}
				if( intval( $send_text ) >= 1 ){
					$q .= '
						AND ac.value = \'1\'
					';
				}
			}
		}

		if ($search) {
			$s = Crunchbutton_Query::search([
				'search' => stripslashes($search),
				'fields' => [
					'admin.name' => 'like',
					'admin.phone' => 'like',
					'admin.login' => 'like',
					'admin.email' => 'like',
					'p.phone' => 'likephone',
					'admin.id_admin' => 'inteq',
					'admin.invite_code' => 'eq',
					'apt.stripe_id' => 'eq',
					'apt.stripe_account_id' => 'eq'
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
		';

		$q .= '
			ORDER BY `admin`.name ASC
		';

		if ($working == 'all') {
			$q .= '
				LIMIT '.intval($getCount ? $limit : $limit+1).'
				OFFSET '.intval($offset).'
			';
		}

		$docs = Cockpit_Driver_Document::driver();

		// do the query
		$data = [];

		if ($type == 'driver') {
			$wild = '
						admin.*,
						bool_and(apt.using_pex) using_pex,
						max(apt.id_admin_payment_type) id_admin_payment_type,
						max(ac.value) AS send_text_about_schedule
					';
		} else {
			$wild = '
						admin.*,
						bool_and(apt.using_pex) using_pex,
						max(apt.id_admin_payment_type) id_admin_payment_type
					';
		}

		$query = str_replace('-WILD-', $wild , $q);


		$r = c::db()->query($query, $keys );

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
			$staff[ 'isCampusManager' ] = $admin->isCampusManager();
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
				$staff[ 'type' ] .= $commas . 'Support';
			}

			if( $staff[ 'isMarketingRep' ] ){
				$commas = '';
				if( $staff[ 'type' ] ){
					$commas = ', ';
				}
				$staff[ 'type' ] .= $commas . 'Brand Representative';
			}

			if( $staff[ 'isCampusManager' ] ){
				$commas = '';
				if( $staff[ 'type' ] ){
					$commas = ', ';
				}
				$staff[ 'type' ] .= $commas . 'Community Manager';
			}

			$staff[ 'brand_representative_groups' ] = $admin->marketingGroups();
			$staff[ 'community_manager_groups' ] = $admin->campusManagerGroups();

			if ($type == 'driver') {

				$sentAllDocs = true;

				$payment_type = $admin->payment_type();

				// driver stuff
				$staff[ 'send_text_about_schedule' ] = ( $s->send_text_about_schedule ? true : false );
				// $staff[ 'orders_per_hour' ] = $admin->ordersPerHour();
				$note = $admin->note();
				if( $note->id_admin_note ){
					$staff[ 'note' ] = $note->exports();
				}
				$shift_status = $admin->shiftCurrentStatus();
				$staff[ 'shift_status' ] = $shift_status->exports();
				$assigned_shifts = $admin->shiftsCurrentAssigned();
				$staff[ 'total_shifts' ] = $assigned_shifts->count();

				$staff[ 'amount_per_order' ] = $payment_type->amountPerOrder();

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

			$staff[ 'email' ] = $admin->email;

			$data[] = $staff;
			$i++;
		}

		if ($working == 'all' ) {
			$pages = ceil( $count / $limit );
		} else {
			$pages = 1;
		}

		$out = [ 	'more' => $getCount ? $pages > $page : $more,
							'count' => intval($count),
							'pages' => $pages,
							'page' => intval($page),
							'results' => $data ];

		if( $brandreps ){
			$community = Community::o( c::user()->getMarketingRepGroups() );
			$out[ 'community' ] = $community->name;
		}

		echo json_encode( $out, JSON_NUMERIC_CHECK);
	}
}
