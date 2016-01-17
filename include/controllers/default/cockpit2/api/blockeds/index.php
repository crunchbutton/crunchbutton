<?php

class Controller_Api_Blockeds extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if (!c::admin()->permission()->check(['global', 'customer-all', 'customer-block' ])) {
			$this->error(401, true);
		}

		$limit = $this->request()['limit'] ? $this->request()['limit'] : 20;
		$search = $this->request()['search'] ? $this->request()['search'] : '';
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
			FROM blocked b
			LEFT JOIN phone p ON p.id_phone = b.id_phone
			LEFT JOIN user u ON u.id_user = b.id_user
			INNER JOIN admin a ON a.id_admin = b.id_admin
			WHERE 1=1
			';

		if ($search) {
			$s = Crunchbutton_Query::search([
				'search' => stripslashes($search),
				'fields' => [
					'u.name' => 'like',
					'p.phone' => 'like'
				]
			]);
			$q .= $s['query'];
			$keys = array_merge($keys, $s['keys']);
		}


		$count = 0;

		// get the count
		if ($getCount) {
			$r = c::db()->query(str_replace('-WILD-','COUNT(DISTINCT `p`.id_blocked) as c', $q), $keys);
			while ($c = $r->fetch()) {
				$count = $c->c;
			}
		}
//			#, sm.id_support_message
		$q .= '
			ORDER BY b.id_blocked DESC
			LIMIT '.intval($getCount ? $limit : $limit+1).'
			OFFSET '.intval($offset).'
		';

		// do the query
		$d = [];
		$query = str_replace('-WILD-','
			b.id_blocked,
			a.id_admin,
			b.id_user,
			a.name AS admin,
			u.name AS customer,
			p.phone,
			b.date
		', $q);

		$r = c::db()->query($query, $keys);

		$i = 1;
		$more = false;

		while ($blocked = $r->fetch()) {
			if (!$getCount && $i == $limit + 1) {
				$more = true;
				break;
			}
			if( $blocked->id_user ){
				$blocked->type = Crunchbutton_Blocked::TYPE_USER;
			} else {
				$user = User::byPhone( $blocked->phone );
				$blocked->type = Crunchbutton_Blocked::TYPE_PHONE;
				$blocked->customer = $user->name;
				$blocked->id_user = $user->id_user;
			}
			$d[] = $blocked;
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
