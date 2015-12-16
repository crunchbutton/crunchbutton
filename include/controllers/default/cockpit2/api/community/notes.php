<?php

class Controller_api_community_notes extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'notes-all' ])) {
			$this->error(401);
		}

		$limit = $this->request()['limit'] ? $this->request()['limit'] : 20;
		$search = $this->request()['search'] ? $this->request()['search'] : '';
		$page = $this->request()['page'] ? $this->request()['page'] : 1;
		$community = $this->request()['community'] ? $this->request()['community'] : null;
		if( $community == 'all' ){
			$community = null;
		}
		$keys = [];

		if ($page == 1) {
			$offset = '0';
		} else {
			$offset = ($page-1) * $limit;
		}

		$q = '
			SELECT
				-WILD-
			FROM community_note cn
			INNER JOIN admin a ON a.id_admin = cn.id_admin
		';

		$q .='
			WHERE
				cn.id_community IS NOT NULL
		';


		if ($community) {
			$q .= '
				AND cn.id_community=?
			';
			$keys[] = $community;
		}

		if ($search) {
			$s = Crunchbutton_Query::search([
				'search' => stripslashes($search),
				'fields' => [
					'cn.text' => 'like',
					'a.name' => 'like',
				]
			]);
			$q .= $s['query'];
			$keys = array_merge($keys, $s['keys']);
		}

		// get the count
		$count = 0;
		$r = c::db()->query(str_replace('-WILD-','COUNT(*) c', $q), $keys);
		while ($c = $r->fetch()) {
			$count++;
		}

		$q .= '
			ORDER BY cn.date DESC
			LIMIT '.intval($limit).'
			OFFSET '.intval($offset).'
		';

		// do the query
		$data = [];
		$r = c::db()->query(str_replace('-WILD-','
			cn.*
		', $q), $keys);

		while ($s = $r->fetch()) {
			$note = Community_Note::o($s);
			$out = $note->exports();
			$data[] = $out;
		}

		echo json_encode([
			'count' => intval($count),
			'pages' => ceil($count / $limit),
			'page' => $page,
			'results' => $data
		]);


	}

}
