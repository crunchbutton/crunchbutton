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
				default:
					$this->_view($staff);
					break;
			}

		} else {
			$this->_list();
		}

	}
	
	private function _view($staff) {
		echo $staff->json();
	}

	private function _list() {

		$limit = $this->request()['limit'] ? c::db()->escape($this->request()['limit']) : 25;
		$search = $this->request()['search'] ? c::db()->escape($this->request()['search']) : '';
		$page = $this->request()['page'] ? c::db()->escape($this->request()['page']) : 1;
		$type = $this->request()['type'] ? c::db()->escape($this->request()['type']) : '';
		$status = $this->request()['status'] ? c::db()->escape($this->request()['status']) : 'all';
		
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
			$words = preg_split("/[\s,]*\\\"([^\\\"]+)\\\"[\s,]*|" . "[\s,]*'([^']+)'[\s,]*|" . "[\s,]+/", $search, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
			foreach ($words as $word) {
				$sq .= ($sq ? ' OR ' : '').'(
					admin.name LIKE "%'.$word.'%"
					OR admin.phone LIKE "%'.$word.'%"
					OR admin.login LIKE "%'.$word.'%"
					OR admin.email LIKE "%'.$word.'%"
				)';
			}
			$q .= '
				AND ('.$sq.')
			';
		}

		
		// get the count
		$count = 0;
		$r = c::db()->query(str_replace('-WILD-','COUNT(*) c', $q));
		while ($c = $r->fetch()) {
			$count = $c->c;
		}
		
		$q .= '
			GROUP BY `admin`.id_admin
			ORDER BY `admin`.id_admin DESC
			LIMIT '.$offset.', '.$limit.'
		';
		
		// do the query
		$data = [];
		$r = c::db()->query(str_replace('-WILD-','admin.*', $q));
		while ($s = $r->fetch()) {
			$staff = Admin::o($s)->exports(['permissions', 'groups']);
			$unset = ['email','timezone','testphone','txt'];
			foreach ($unset as $un) {
				unset($staff[$un]);
			}
			$data[] = $staff;
		}

		// @todo: move this controll functionality into here
		// $staff = Crunchbutton_Admin::search( $search );


		echo json_encode([
			'count' => intval($count),
			'pages' => ceil($count / $limit),
			'page' => $page,
			'results' => $data
		]);
	}
}