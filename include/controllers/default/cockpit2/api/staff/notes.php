<?php

class Controller_api_staff_notes extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'notes-all' ])) {
			$this->error(401);
		}

		$limit = $this->request()['limit'] ? $this->request()['limit'] : 20;
		$search = $this->request()['search'] ? $this->request()['search'] : '';
		$page = $this->request()['page'] ? $this->request()['page'] : 1;
		$admin = $this->request()['admin'] ? $this->request()['admin'] : null;
		$added_by = $this->request()['added_by'] ? $this->request()['added_by'] : null;
		if( $admin == 'all' ){
			$admin = null;
		}
		if( $added_by == 'all' ){
			$admin = null;
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
			FROM admin_note an
			INNER JOIN admin a ON a.id_admin = an.id_admin
		';

		$q .='
			WHERE
				an.id_admin IS NOT NULL
		';

		if ($admin) {
			$q .= '
				AND ( an.id_admin = ? OR a.login = ? )
			';
			$keys[] = $admin;
			$keys[] = $admin;
		}

		if ($added_by) {
			$q .= '
				AND an.id_admin_added = ?
			';
			$keys[] = $added_by;
		}

		if ($search) {
			$s = Crunchbutton_Query::search([
				'search' => stripslashes($search),
				'fields' => [
					'an.text' => 'like',
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
			ORDER BY an.date DESC
			LIMIT ?
			OFFSET ?
		';
		$keys[] = $limit;
		$keys[] = $offset;

		// do the query
		$data = [];
		$r = c::db()->query(str_replace('-WILD-','
			an.*
		', $q), $keys);

		while ($s = $r->fetch()) {
			$note = Admin_Note::o($s);
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