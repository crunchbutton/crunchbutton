<?php

class Controller_api_tickets extends Crunchbutton_Controller_RestAccount {

	public function init() {

		$limit = $this->request()['limit'] ? $this->request()['limit'] : 20;
		$status = $this->request()['status'] ? $this->request()['status'] : 'open';
		$type = $this->request()['type'] ? $this->request()['type'] : 'all';
		$search = $this->request()['search'] ? $this->request()['search'] : '';
		$admin = $this->request()['admin'] ? $this->request()['admin'] : 'all';
		$page = $this->request()['page'] ? $this->request()['page'] : 1;
		$keys = [];

		if ($page == 1) {
			$offset = '0';
		} else {
			$offset = ($page-1) * $limit;
		}

		$q = '
			SELECT
				-WILD-
			FROM support s
			INNER JOIN support_message sm ON s.id_support = sm.id_support
			LEFT JOIN user u ON u.id_user=s.id_user
			LEFT JOIN `order` o ON o.id_order=s.id_order
			LEFT JOIN admin a ON a.id_admin=sm.id_admin
			WHERE 1=1
		';

		if ($status != 'all') {
			$q .= '
				AND s.status="'.($status == 'closed' ? 'closed' : 'open').'"
			';
		}

		if ($admin != 'all') {
			$q .= '
				AND s.id_admin=?
			';
			$keys['admin'] = $admin;
		}

		if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'support-crud' ])) {
			// only display support to their number
			$phone = preg_replace('/[^0-9]/','', c::admin()->phone);
			$q .= ' AND s.phone=?';
			$keys['phone'] = $phone;
		}

		if ($search) {
			$s = Crunchbutton_Query::search([
				'search' => stripslashes($search),
				'fields' => [
					'u.name' => 'like',
					'u.phone' => 'like',
					'u.address' => 'like',
					'o.name' => 'like',
					'o.phone' => 'like',
					'o.address' => 'like',
					's.id_support' => 'liker'
				]
			]);
			$q .= $s['query'];
			$keys = array_merge($keys, $s['keys']);
		}

		$q .= '
			GROUP BY s.id_support
		';

		// get the count
		$count = 0;
		$r = c::db()->query(str_replace('-WILD-','COUNT(*) c', $q), $keys);
		while ($c = $r->fetch()) {
			$count++;
		}

		$q .= '
			ORDER BY s.datetime DESC
			LIMIT ?, ?
		';
		$keys[] = $offset;
		$keys[] = $limit;

		// do the query
		$d = [];
		$r = c::db()->query(str_replace('-WILD-','
			s.id_support,
			sm.id_support_message,
			sm.id_admin,
			sm.date,
			UNIX_TIMESTAMP(sm.date) as timestamp,
			sm.name,
			sm.phone,
			sm.from,
			u.name as user_name,
			a.name as admin_name,
			u.id_user,
			s.status,
			sm.body as message
		', $q), $keys);

		while ($o = $r->fetch()) {
			if (!$o->name) {
				$o->name = Phone::name($o);
			}
			$support = Support::o( $o->id_support );
			$o->message = $support->lastMessage()->body;
			$o->date = $support->lastMessage()->date();
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
}