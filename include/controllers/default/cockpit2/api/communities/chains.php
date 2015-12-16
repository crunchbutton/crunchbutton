<?php

class Controller_api_communities_chains extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if( !c::admin()->permission()->check( ['global', 'chain-all', 'chain-list', 'support-all', 'support-view', 'support-crud' ] ) ){
			$this->error( 401 );
		}

		$limit = $this->request()['limit'] ?$this->request()['limit'] : 20;
		$search = $this->request()['search'] ?$this->request()['search'] : '';
		$page = $this->request()['page'] ?$this->request()['page'] : 1;
		$status = $this->request()['status'] ?$this->request()['status'] : 'all';
		$id_community = $this->request()['community'] ?$this->request()['community'] : null;
		$id_chain = $this->request()['chain'] ?$this->request()['chain'] : null;
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
			INNER JOIN community_chain ON community_chain.id_chain = chain.id_chain
			INNER JOIN community ON community.id_community = community_chain.id_community
			WHERE
				chain.name IS NOT NULL
		';

		if ($status != 'all') {
			$q .= '
				AND chain.active=?
			';
			$keys[] = $status == 'active' ? true : false;
		}

		if( $id_community ){
			$q .= '
				AND community_chain.id_community=?
			';
			$keys[] = $id_community;
		}

		if( $id_chain ){
			$q .= '
				AND community_chain.id_chain=?
			';
			$keys[] = $id_chain;
		}

		if ($search) {
			$s = Crunchbutton_Query::search([
				'search' => stripslashes($search),
				'fields' => [
					'chain.name' => 'like',
					'community.name' => 'like',
				]
			]);
			$q .= $s['query'];
			$keys = array_merge($keys, $s['keys']);
		}

		$q .= '
			ORDER BY chain.name, community.name
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
				LIMIT '.intval($getCount ? $limit : $limit+1).'
				OFFSET '.intval($offset).'
			';
		}

		// do the query
		$data = [];
		$r = c::db()->query(str_replace('-WILD-','community_chain.*, chain.name as chain, community.name as community', $q), $keys);


		$i = 1;
		$more = false;

		while ($s = $r->fetch()) {
			if ($limit != 'none' && !$getCount && $i == $limit + 1) {
				$more = true;
				break;
			}

			$restaurant = Cockpit_Restaurant_Chain::byIdCommunityChain( $s->id_community_chain );
			if( count( $restaurant ) ){
				$restaurant = $restaurant->get( 0 );
				if( $restaurant->id_restaurant ){
					$restaurant = $restaurant->restaurant();
					$s->id_restaurant = $restaurant->id_restaurant;
					$s->restaurant = $restaurant->name;
					$s->linked_restaurant = true;
				}
			}

			$s->exist_at_community = intval( $s->exist_at_community > 0 ) ? true : false;
			$s->within_range = intval( $s->within_range > 0 ) ? true : false;

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
