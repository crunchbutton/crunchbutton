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
		
		$limit = $this->request()['limit'] ? c::db()->escape($this->request()['limit']) : 25;
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
			$words = preg_split("/[\s,]*\\\"([^\\\"]+)\\\"[\s,]*|" . "[\s,]*'([^']+)'[\s,]*|" . "[\s,]+/", $search, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
			foreach ($words as $word) {
				$sq .= ($sq ? ' OR ' : '').'(
					user.name LIKE "%'.$word.'%"
					OR user.phone LIKE "%'.$word.'%"
					OR user.address LIKE "%'.$word.'%"
					OR `order`.name LIKE "%'.$word.'%"
					OR `order`.phone LIKE "%'.$word.'%"
					OR `order`.address LIKE "%'.$word.'%"
					OR user.id_user LIKE "%'.$word.'%"
				)';
			}
			$q .= '
				AND ('.$sq.')
			';
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
			ORDER BY `user`.id_user DESC, `order`.date DESC
			LIMIT '.$offset.', '.$limit.'
		';
		
		// do the query
		$data = [];
		$r = c::db()->query(str_replace('-WILD-','
			`user`.*,
			`order`.date as _order_date
		', $q));

		while ($o = $r->fetch()) {
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