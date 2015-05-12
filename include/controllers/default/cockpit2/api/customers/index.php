<?php

class Controller_api_customers extends Crunchbutton_Controller_RestAccount {

	public function init() {
		if ($this->method() != 'get') {
			exit;
		}

		if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'support-crud'])) {
			$this->error(401);
		}
		
		
		// manual query is faster than using the Order->exports
		
		// @todo: merge this with Order::find when we get rid of old cockpit/orders
		
		$limit = $this->request()['limit'] ? $this->request()['limit'] : 20;
		$search = $this->request()['search'] ? $this->request()['search'] : '';
		$page = $this->request()['page'] ? $this->request()['page'] : 1;
		$sort = $this->request()['sort'] ? $this->request()['sort'] : null;
		$getCount = $this->request()['fullcount'] && $this->request()['fullcount'] != 'false' ? true : false;
		$keys = [];
		
		if ($sort{0} == '-') {
			$sort = substr($sort, 1);
			$sc = true;
		} else {
			$sc = false;
		}
		
		if ($page == 1) {
			$offset = '0';
		} else {
			$offset = ($page-1) * $limit;
		}

		$q = '
			SELECT
				-WILD-
			FROM `user`
			LEFT JOIN `order` using(id_user)
			WHERE 1=1
		';
		
		if ($search) {
			$s = Crunchbutton_Query::search([
				'search' => stripslashes($search),
				'fields' => [
					'user.name' => 'like',
					'user.phone' => 'like',
					'user.address' => 'like',
					'`order`.name' => 'like',
					'`order`.phone' => 'like',
					'`order`.address' => 'like',
					'user.id_user' => 'liker'
				]
			]);
			$q .= $s['query'];
			$keys = array_merge($keys, $s['keys']);
		}
		
		$q .= '
			GROUP BY `user`.id_user
		';

		// get the count
		$count = 0;
		$r = c::db()->query(str_replace('-WILD-','COUNT(*) c', $q), $keys);
		while ($c = $r->fetch()) {
			$count++;
		}
		
		switch ($sort) {
			case 'orders':
				$q .= ' ORDER BY orders '.($sc ? 'ASC' : 'DESC').', `user`.id_user DESC, `order`.date ASC ';
				break;
			case 'order':
				$q .= ' ORDER BY _order_date '.($sc ? 'ASC' : 'DESC').', `user`.id_user DESC, `order`.date ASC ';
				break;
			case 'name':
				$q .= ' ORDER BY _order_date '.($sc ? 'ASC' : 'DESC').', user.name '.($sc ? 'DESC' : 'ASC').', `user`.id_user DESC, `order`.date ASC ';
				break;
			case 'address':
				$q .= ' ORDER BY _order_date '.($sc ? 'ASC' : 'DESC').', user.address '.($sc ? 'DESC' : 'ASC').', `user`.id_user DESC, `order`.date ASC ';
				break;
			case 'phone':
				$q .= ' ORDER BY _order_date '.($sc ? 'ASC' : 'DESC').', user.phone '.($sc ? 'DESC' : 'ASC').', `user`.id_user DESC, `order`.date ASC ';
				break;
			default:
				$q .= ' ORDER BY _order_date '.($sc ? 'ASC' : 'DESC').', `user`.id_user '.($sc ? 'DESC' : 'ASC').', `order`.date ASC ';
				break;
			
		}

		$q .= '
			LIMIT ?
			OFFSET ?
		';
		$keys[] = $limit;
		$keys[] = $offset;
		
		// do the query
		$data = [];
		$r = c::db()->query(str_replace('-WILD-','
			`user`.*,
			(SELECT MAX(`order`.date) FROM `order` WHERE `order`.id_user = user.id_user) as _order_date,
			COUNT(`order`.id_order) orders
		', $q), $keys);

		while ($o = $r->fetch()) {
			$u = new User($o);
			$o->image = $u->image(false);
			$data[] = $o;
		}

		echo json_encode([
			'count' => intval($count),
			'pages' => ceil($count / $limit),
			'page' => $page,
			'results' => $data
		]);

	}
}