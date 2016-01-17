<?php

class Controller_api_suggestions extends Crunchbutton_Controller_Rest {

	public function init() {

		if (!c::admin()->permission()->check(['global', 'suggestions-all', 'suggestions-list-page', 'support-all', 'support-view', 'support-crud'])) {
			$this->error(401, true);
		}

		$limit = $this->request()['limit'] ?$this->request()['limit'] : 20;
		$search = $this->request()['search'] ?$this->request()['search'] : '';
		$page = $this->request()['page'] ?$this->request()['page'] : 1;
		$status = $this->request()['status'] ?$this->request()['status'] : 'all';
		$type = $this->request()['type'] ?$this->request()['type'] : 'all';

		$keys = [];

		$q = '
			SELECT
				-WILD-
			FROM suggestion s
			LEFT JOIN restaurant r ON r.id_restaurant = s.id_restaurant
			LEFT JOIN `user` u ON u.id_user = s.id_user
			LEFT JOIN community c ON c.id_community = s.id_community

			WHERE
				s.id_suggestion IS NOT NULL
		';

		if ($type != 'all') {
			$q .= '
				AND s.type=?
			';
			$keys[] = $type;
		}

		if ($status != 'all') {
			$q .= '
				AND s.status=?
			';
			$keys[] = $status;
		}

		if ($search) {
			$s = Crunchbutton_Query::search([
				'search' => stripslashes( $search ),
				'fields' => [
					'r.name' => 'like',
					's.name' => 'like',
					's.content' => 'like',
					'c.name' => 'like'
				]
			]);
			$q .= $s['query'];
			$keys = array_merge($keys, $s['keys']);
		}

		$count = 0;

		$r = c::db()->get(str_replace('-WILD-','COUNT( * ) as c', $q), $keys)->get( 0 );
		$count = intval( $r->c );
		$pages = ceil( $count / $limit );

		$q .= '
			ORDER BY s.id_suggestion DESC
		';

		if ($page == 1) {
			$offset = '0';
		} else {
			$offset = ( $page - 1 ) * $limit;
		}

		$q .= '
			LIMIT '.intval($limit).'
			OFFSET '.intval($offset).'
		';

		$data = [];

		$r = c::db()->query( str_replace( '-WILD-',' s.*, r.name AS restaurant, u.name AS customer, c.name as community ', $q ), $keys);

		while ( $s = $r->fetch() ) {
			$suggestion = Crunchbutton_Suggestion::o( $s );
			$s->date = $suggestion->date()->format( 'Y-m-d H:i:s' );
			$s->content = nl2br( $s->content );
			$data[] = $s;
		}

		echo json_encode([
			'more' => ( $page < $pages ),
			'count' => intval($count),
			'pages' => $pages,
			'page' => intval($page),
			'results' => $data
		]);
	}
}
