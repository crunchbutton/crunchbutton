<?php

class Controller_api_chains extends Crunchbutton_Controller_Rest {

	public function init() {

		if( !c::admin()->permission()->check( ['global', 'chain-all', 'chain-list', 'support-all', 'support-view', 'support-crud' ] ) ){
			$this->error( 401 );
		}

		$limit = $this->request()['limit'] ?$this->request()['limit'] : 20;
		$search = $this->request()['search'] ?$this->request()['search'] : '';
		$page = $this->request()['page'] ?$this->request()['page'] : 1;
		$status = $this->request()['status'] ?$this->request()['status'] : 'all';
		$getCount = $this->request()['fullcount'] && $this->request()['fullcount'] != 'false' ? true : false;

		$keys = [];

		if ($limit == 'none') {
			$page = 1;
		}

		if ($page == 1) {
			$offset = '0';
		} else {
			$offset = ($page-1) * $limit;
		}


		$q = '
			SELECT
				-WILD-
			FROM chain
			WHERE
				chain.name IS NOT NULL
		';

		if ($status != 'all') {
			$q .= '
				AND chain.active=?
			';
			$keys[] = $status == 'active' ? true : false;
		}

		if ($search) {
			$s = Crunchbutton_Query::search([
				'search' => stripslashes($search),
				'fields' => [
					'chain.name' => 'like'
				]
			]);
			$q .= $s['query'];
			$keys = array_merge($keys, $s['keys']);
		}

		$q .= '
			ORDER BY chain.name
		';

		$count = 0;

		// get the count
		if ($getCount) {
			$r = c::db()->query(str_replace('-WILD-','COUNT(DISTINCT `chain`.id_chain) as c', $q), $keys);
			while ($c = $r->fetch()) {
				$count = $c->c;
			}
		}

		if ($limit != 'none') {
			$q .= '
				LIMIT ?
				OFFSET ?
			';
			$keys[] = $getCount ? $limit : $limit+1;
			$keys[] = $offset;
		}

		// do the query
		$data = [];
		$r = c::db()->query(str_replace('-WILD-','chain.*', $q), $keys);


		$i = 1;
		$more = false;

		while ($s = $r->fetch()) {
			if ($limit != 'none' && !$getCount && $i == $limit + 1) {
				$more = true;
				break;
			}
			$data[] = $s;
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