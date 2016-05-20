<?php

class Controller_api_group extends Crunchbutton_Controller_Rest {


	public function init(){

		if (!c::admin()->permission()->check(['global'])) {
			$this->error(401, true);
		}

		switch ( c::getPagePiece( 2 ) ) {
			case 'load':
				$this->_load();
				break;

			case 'save':
				$this->_save();
				break;


			default:
				$this->_list();
				break;
		}
	}

	public function _save(){

		$group = Group::o( $this->request()[ 'id_group' ] );
		if (!$group->id_group) {
			$group = new Group;
			// check unique group name
			$name = trim( $this->request()[ 'name' ] );
			$groups = Group::q( 'SELECT * FROM `group` WHERE name = ?', [$name]);
			if( $groups->count() == 0 ){
				$group->name = $name;
			} else {
				echo json_encode( [ 'error' => 'this group name is already in use' ] );exit;
			}
		}
		$group->description = $this->request()[ 'description' ];
		$group->save();
		echo json_encode( $group->exports() );exit;

	}

	public function _load(){

		$group = Group::o( c::getPagePiece( 3 ) );
		if( !$group->id_group ){
			$group = Group::byName( c::getPagePiece( 3 ) )->get( 0 );
		}

		if (!$group->id_group) {
			$this->error(404, true);
		}

		echo json_encode( $group->exports() );exit;

	}

	public function _list() {

		$limit = $this->request()['limit'] ?$this->request()['limit'] : 20;
		$search = $this->request()['search'] ?$this->request()['search'] : '';
		$page = $this->request()['page'] ?$this->request()['page'] : 1;

		$keys = [];

		if ($limit == 'none') {
			$page = 1;
		}

		if ($page == 1) {
			$offset = '0';
		} else {
			$offset = ($page-1) * $limit;
		}

		if($this->request()['active-only']){
			$w = ' AND c.active = true';
		} else {
			$w = '';
		}

		$q = '
			SELECT
				-WILD-
			FROM `group` g
			LEFT JOIN community c ON c.id_community = g.id_community ' . $w . '
			LEFT JOIN admin_group ag ON ag.id_group = g.id_group
			WHERE
				g.name IS NOT NULL
		';

		if ($search) {
			$s = Crunchbutton_Query::search([
				'search' => stripslashes($search),
				'fields' => [
					'g.name' => 'like',
					'g.description' => 'like',
					'c.name' => 'like',
				]
			]);
			$q .= $s['query'];
			$keys = array_merge($keys, $s['keys']);
		}


		$count = 0;

		$r = c::db()->query(str_replace('-WILD-','COUNT(DISTINCT `g`.name) as c', $q), $keys);
		while ($c = $r->fetch()) {
			$count = $c->c;
		}

		$q .= '
			GROUP BY g.id_group
		';

		$q .= '
			ORDER BY g.name ASC
		';
		if ($limit != 'none') {
			$q .= '
				LIMIT '.intval($limit).'
				OFFSET '.intval($offset).'
			';
			$pages = ceil( $count / $limit );
		}

		// do the query
		$data = [];

		$r = c::db()->query(str_replace('-WILD-','
			g.*,
			c.name AS community,
			c.active,
			c.id_community AS id_community,
			COUNT(ag.id_admin) AS members
		', $q), $keys);


		$i = 1;
		$more = false;

		while ($s = $r->fetch()) {
			$s->description = ( !$s->description ? '' : $s->description );
			if(!$s->id_community || ($s->id_community && $s->active)){
				$data[] = $s;
				$i++;
			}
		}


		echo json_encode([
			'more' => $pages >= $page,
			'count' => intval($count),
			'pages' => $pages,
			'page' => intval($page),
			'results' => $data
		]);
	}
}
