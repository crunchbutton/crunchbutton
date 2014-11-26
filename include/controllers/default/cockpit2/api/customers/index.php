<?php

class Controller_api_customers extends Crunchbutton_Controller_RestAccount {

	public function init() {
		if ($this->method() != 'get') {
			exit;
		}

		if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'support-crud'])) {
			header('HTTP/1.1 401 Unauthorized');
			exit;
		}
		
		
		// manual query is faster than using the Order->exports
		
		// @todo: merge this with Order::find when we get rid of old cockpit/orders
		
		$limit = $this->request()['limit'] ? c::db()->escape($this->request()['limit']) : 20;
		$search = $this->request()['search'] ? c::db()->escape($this->request()['search']) : '';
		$page = $this->request()['page'] ? c::db()->escape($this->request()['page']) : 1;
		
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
			$q .= Crunchbutton_Query::search([
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
		}
		
		$q .= '
			GROUP BY `user`.id_user
		';

		// get the count
		$count = 0;
		$r = c::db()->query(str_replace('-WILD-','COUNT(*) c', $q));
		while ($c = $r->fetch()) {
			$count++;
		}

		$q .= '
			ORDER BY `user`.id_user DESC, `order`.date ASC
			LIMIT '.$offset.', '.$limit.'
		';
		
		// do the query
		$data = [];
		$r = c::db()->query(str_replace('-WILD-','
			`user`.*,
			(SELECT MAX(`order`.date) FROM `order` WHERE `order`.id_user = user.id_user) as _order_date,
			COUNT(`order`.id_order) orders
		', $q));

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