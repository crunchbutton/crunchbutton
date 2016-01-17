<?php

class Controller_api_customers extends Crunchbutton_Controller_RestAccount {

	public function init() {
		if ($this->method() != 'get') {
			exit;
		}

		if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'support-crud'])) {
			$this->error(401, true);
		}


		// manual query is faster than using the Order->exports

		// @todo: merge this with Order::find when we get rid of old cockpit/orders

		$limit = intval($this->request()['limit'] ? $this->request()['limit'] : 20);
		$search = $this->request()['search'] ? $this->request()['search'] : '';
		$page = intval($this->request()['page'] ? $this->request()['page'] : 1);
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
			LEFT JOIN `order` on `order`.id_user=`user`.id_user
			LEFT JOIN `restaurant` on `restaurant`.id_restaurant=`order`.id_restaurant
			LEFT JOIN restaurant_community ON restaurant_community.id_restaurant=restaurant.id_restaurant
			LEFT JOIN community ON community.id_community=restaurant_community.id_community
			LEFT JOIN `user_payment_type` on `user_payment_type`.id_user=`user`.id_user and `user_payment_type`.active = true
			WHERE 1=1
		';

		if ($search) {
			$s = Crunchbutton_Query::search([
				'search' => stripslashes($search),
				'fields' => [
					'`user`.name' => 'like',
					'`user`.phone' => 'likephone',
					'`user`.address' => 'like',
					'`order`.name' => 'like',
					'`order`.phone' => 'likephone',
					'`order`.address' => 'like',
					'restaurant.name' => 'like',
					'community.name' => 'like',
					'`user`.id_user' => 'inteq'
				]
			]);
			$q .= $s['query'];
			$keys = array_merge($keys, $s['keys']);
		}

		$q .= '
			GROUP BY `user`.id_user
		';

		$count = 0;

		// get the count
		if ($getCount) {
			$r = c::db()->query(str_replace('-WILD-','COUNT(DISTINCT `user`.id_user) as c', $q), $keys);
			while ($c = $r->fetch()) {
				$count = $c->c;
			}
		}

		switch ($sort) {
			case 'name':
				$q .= ' ORDER BY user.name '.($sc ? 'DESC' : 'ASC').', `user`.id_user DESC ';
				break;
			case 'address':
				$q .= ' ORDER BY user.address '.($sc ? 'DESC' : 'ASC').', `user`.id_user DESC ';
				break;
			case 'phone':
				$q .= ' ORDER BY user.phone '.($sc ? 'DESC' : 'ASC').', `user`.id_user DESC ';
				break;
			case 'stripe':
				$q .= ' ORDER BY `user_payment_type`.stripe_id '.($sc ? 'DESC' : 'ASC').', `user`.id_user DESC ';
				break;
			case 'order':
				$q .= ' ORDER BY _order_date '.($sc ? 'DESC' : 'ASC').', `user`.id_user DESC ';
				break;
			default:
				$q .= ' ORDER BY `user`.id_user DESC ';
				break;
		}

		$q .= '
			LIMIT '.($getCount ? $limit : $limit+1).'
			OFFSET '.$offset.'
		';

		// do the query
		$data = [];
		//(SELECT `order`.date FROM `order` WHERE `order`.id_user = user.id_user order by `order`.date desc limit 1) as _order_date,
		$query = str_replace('-WILD-','
			`user`.*,
			(SELECT MAX(`order`.date) FROM `order` WHERE `order`.id_user = `user`.id_user) as _order_date,
			max(`user_payment_type`.stripe_id) as _stripe_id,
			max(community.name) as _community_name,
			max(community.id_community) as _id_community
		', $q);
		$r = c::db()->query($query, $keys);

		$i = 1;
		$more = false;

		while ($o = $r->fetch()) {

			if (!$getCount && $i == $limit + 1) {
				$more = true;
				break;
			}

			$u = new User($o);
			$o->image = $u->image(false);
			$data[] = $o;
			$i++;
		}

		echo json_encode([
			'more' => $getCount ? $pages > $page : $more,
			'count' => intval($count),
			'pages' => $pages,
			'page' => intval($page),
			'results' => $data
		]);

	}
}
