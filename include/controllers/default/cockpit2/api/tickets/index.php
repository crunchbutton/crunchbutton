<?php

class Controller_api_tickets extends Crunchbutton_Controller_RestAccount {

	public function init() {

		$limit = $this->request()['limit'] ? $this->request()['limit'] : 20;
		$status = $this->request()['status'] ? $this->request()['status'] : 'open';
		$type = $this->request()['type'] ? $this->request()['type'] : 'all';
		$search = $this->request()['search'] ? $this->request()['search'] : '';
		$admin = $this->request()['admin'] ? $this->request()['admin'] : 'all';
		$page = $this->request()['page'] ? $this->request()['page'] : 1;
		$getCount = $this->request()['fullcount'] && $this->request()['fullcount'] != 'false' ? true : false;
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
			LEFT JOIN (SELECT * FROM support_message order by support_message.id_support_message desc limit 1) as sm on sm.id_support=s.id_support
			LEFT JOIN `user` u ON u.id_user=s.id_user
			LEFT JOIN `order` o ON o.id_order=s.id_order
			LEFT JOIN admin a ON a.id_admin=sm.id_admin
			
			WHERE 1=1
		';

		if ($status != 'all') {
			$q .= "
				AND s.status='".($status == 'closed' ? 'closed' : 'open')."'
			";
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


		$count = 0;

		// get the count
		if ($getCount) {
			$r = c::db()->query(str_replace('-WILD-','COUNT(DISTINCT `s`.id_support) as c', $q), $keys);
			while ($c = $r->fetch()) {
				$count = $c->c;
			}
		}

		$q .= '
			GROUP BY s.id_support
			ORDER BY s.datetime DESC
			LIMIT ?
			OFFSET ?
		';
		$keys[] = $getCount ? $limit : $limit+1;
		$keys[] = $offset;

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
		
		$i = 1;
		$more = false;

		while ($o = $r->fetch()) {
			if (!$getCount && $i == $limit + 1) {
				$more = true;
				break;
			}

			if (!$o->name) {
				$o->name = Phone::name($o);
			}
			$support = Support::o( $o->id_support );
			$o->message = $support->lastMessage()->body;
			$o->date = $support->lastMessage()->date();
			$d[] = $o;
			$i++;
		}

		echo json_encode([
			'more' => $getCount ? $pages > $page : $more,
			'count' => intval($count),
			'pages' => $pages,
			'page' => intval($page),
			'results' => $d
		]);

		exit;
	}
}