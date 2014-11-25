<?php

class Controller_api_staff extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if (!c::admin()->permission()->check(['global', 'permission-all', 'permission-users'])) {
			header('HTTP/1.1 401 Unauthorized');
			exit;
		}
		
		if (c::getPagePiece(2)) {
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
					$this->_locations($staff);
					break;
					
				case 'status':
					$this->_status($staff);
					break;

				default:
					$this->_view($staff);
					break;
			}

		} else {
			$this->_list();
		}

	}
	
	private function _locations($staff) {
		echo $staff->locations()->json();
	}
	
	private function _status($staff) {
		echo json_encode($staff->status());
	}
	
	private function _view($staff) {
		$out = $staff->exports();

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

	private function _list() {

		$limit = $this->request()['limit'] ? c::db()->escape($this->request()['limit']) : 25;
		$search = $this->request()['search'] ? c::db()->escape($this->request()['search']) : '';
		$page = $this->request()['page'] ? c::db()->escape($this->request()['page']) : 1;
		$type = $this->request()['type'] ? c::db()->escape($this->request()['type']) : '';
		$status = $this->request()['status'] ? c::db()->escape($this->request()['status']) : 'all';
		$working = $this->request()['working'] ? c::db()->escape($this->request()['working']) : 'all';
		
		if ($page == 1) {
			$offset = '0';
		} else {
			$offset = ($page-1) * $limit;
		}


		$q = '
			SELECT -WILD- FROM admin
		';
		
		if ($type == 'driver') {
			$q .= '
				INNER JOIN admin_group ag ON ag.id_admin=admin.id_admin
				INNER JOIN `group` g ON g.id_group=ag.id_group AND g.name LIKE "' . Crunchbutton_Group::DRIVER_GROUPS_PREFIX . '%"
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
		$r = c::db()->query(str_replace('-WILD-','admin.*', $q));
		while ($s = $r->fetch()) {
			$staff = Admin::o($s)->exports(['permissions', 'groups']);

			if (($working == 'yes' && $staff->working) || ($working == 'no' && !$staff->working)) {
				$count++;
			}
			
			if (($working == 'yes' && !$staff->working) || ($working == 'no' && $staff->working)) {
				continue;
			}

			$unset = ['email','timezone','testphone','txt'];
			foreach ($unset as $un) {
				unset($staff[$un]);
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