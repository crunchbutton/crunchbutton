<?php

class Controller_api_delivery_signup extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if (!c::admin()->permission()->check( [ 'global', 'delivery-sign-up-all' ] ) ) {

			$this->error(401, true);
		}

		switch ( c::getPagePiece( 3 ) ) {
			case 'list':
				$this->_list();
				break;
			case 'change-status':
				$this->_change_status();
				break;
		}
	}


	private function _change_status(){
		$id_delivery_signup = $this->request()[ 'id_delivery_signup' ];
		$status = $this->request()[ 'status' ];

		$delivery_signup = Crunchbutton_Delivery_Signup::o( $id_delivery_signup );
		if( $delivery_signup->id_delivery_signup ){
			$delivery_signup->status = $status;
			$delivery_signup->save();
			echo json_encode( [ 'success' => true ] );exit;
		} else {
			$this->error(401, true);
		}
	}

	private function _list(){

		$limit = $this->request()['limit'] ? $this->request()['limit'] : 20;
		$search = $this->request()['search'] ? $this->request()['search'] : '';
		$status = $this->request()['status'] ? $this->request()['status'] : 'new';
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
			FROM delivery_signup ds
			WHERE status=?
			';

		$keys[] = $status;


		if ($search) {
			$s = Crunchbutton_Query::search([
				'search' => stripslashes($search),
				'fields' => [
					'ds.name' => 'like',
					'ds.email' => 'like',
					'ds.university' => 'like',
					'ds.city' => 'like',
					'ds.state' => 'like',
					'ds.restaurants' => 'like',
				]
			]);
			$q .= $s['query'];
			$keys = array_merge($keys, $s['keys']);
		}


		$count = 0;

		// get the count
		if ($getCount) {
			$r = c::db()->query(str_replace('-WILD-','COUNT(DISTINCT `ds`.id_delivery_signup) as c', $q), $keys);
			while ($c = $r->fetch()) {
				$count = $c->c;
			}
		}
//			#, sm.id_support_message
		$q .= '
			ORDER BY ds.id_delivery_signup DESC
			LIMIT '.intval($getCount ? $limit : $limit+1).'
			OFFSET '.intval($offset).'
		';

		// do the query
		$d = [];
		$query = str_replace('-WILD-','
			ds.*
		', $q);

		$r = c::db()->query($query, $keys);

		$i = 1;
		$more = false;

		while ($row = $r->fetch()) {
			if (!$getCount && $i == $limit + 1) {
				$more = true;
				break;
			}
			$d[] = $row;
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
