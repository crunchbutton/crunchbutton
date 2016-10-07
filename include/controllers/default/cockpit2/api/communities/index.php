<?php

class Controller_api_communities extends Crunchbutton_Controller_Rest {

	public function init() {

		if( !c::admin()->permission()->check( ['global', 'community-all', 'community-list', 'support-all', 'support-view', 'support-crud', 'community-director' ] ) ){
			$this->error( 401 );
		}

		$limit = $this->request()['limit'] ?$this->request()['limit'] : 20;
		$search = $this->request()['search'] ?$this->request()['search'] : '';
		$page = $this->request()['page'] ?$this->request()['page'] : 1;
		$status = $this->request()['status'] ?$this->request()['status'] : 'all';
		$open = $this->request()['open'] ?$this->request()['open'] : 'all';
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
			FROM community
			LEFT JOIN restaurant_community ON community.id_community=restaurant_community.id_community
			LEFT JOIN restaurant ON restaurant_community.id_restaurant=restaurant.id_restaurant
			LEFT JOIN `community_alias` ON community_alias.id_community = community.id_community
			WHERE
				community.name IS NOT NULL
		';

		if ($status != 'all') {
			$q .= '
				AND community.active=?
			';
			$keys[] = $status == 'active' ? true : false;
		}

		if ($open == 'open') {
			$q .= '
				AND (
					community.is_auto_closed = false
					AND community.close_all_restaurants = false
					AND community.close_3rd_party_delivery_restaurants = false
				)
			';
		} elseif ($open == 'closed') {
			$q .= '
				AND (
					community.is_auto_closed = true
					OR community.close_all_restaurants = true
					OR community.close_3rd_party_delivery_restaurants = true
				)
			';
		}

		if ($search) {
			$s = Crunchbutton_Query::search([
				'search' => stripslashes($search),
				'fields' => [
					'community.name' => 'like',
					'community.permalink' => 'like',
					'community.name_alt' => 'like',
					'community.name_alt' => 'like',
					'community_alias.alias' => 'like',
					'community_alias.name_alt' => 'like',
					'community.id_community' => 'inteq'
				]
			]);
			$q .= $s['query'];
			$keys = array_merge($keys, $s['keys']);
		}

		if(c::user()->isCommunityDirector()){
			$q .=  'AND community.id_community = ?';
			$keys[] = c::user()->communityDirectorCommunity()->id_community;
		}

		$q .= '
			GROUP BY community.id_community
		';

		$count = 0;

		// get the count
		if ($getCount) {
			$r = c::db()->query(str_replace('-WILD-','COUNT(DISTINCT `community`.id_community) as c', $q), $keys);
			while ($c = $r->fetch()) {
				$count = $c->c;
			}
		}

		$q .= '
			ORDER BY community.name ASC
		';
		if ($limit != 'none') {
			$q .= '
				LIMIT '.intval($getCount ? $limit : $limit+1).'
				OFFSET '.intval($offset).'
			';
		}

		// do the query
		$data = [];
		$r = c::db()->query(str_replace('-WILD-','
			community.*,
			COUNT(distinct `restaurant`.id_restaurant) restaurants
		', $q), $keys);


		$i = 1;
		$more = false;

		while ($s = $r->fetch()) {

			if ($limit != 'none' && !$getCount && $i == $limit + 1) {
				$more = true;
				break;
			}
			/*
			$restaurant = Restaurant::o($s);
			$out = $s;
			$out->communities = [];

			foreach ($restaurant->communities() as $community) {
				$out->communities[] = $community->properties();
			}
			*/

			// get whether its 3rd or not
			$community = Community::o($s);
			$s->type = $community->type();

			// ensure boolean values
			$s->close_3rd_party_delivery_restaurants = $s->close_3rd_party_delivery_restaurants ? true : false;
			$s->is_auto_closed = $s->is_auto_closed ? true : false;
			$s->auto_close = $s->auto_close ? true : false;
			$s->close_all_restaurants = $s->close_all_restaurants ? true : false;
			$s->active = $s->active ? true : false;
			$note = $community->lastNote();
			if( $note ){
				$s->note = $note->exports();
			}
			$closed = c::db()->get('
				select * from community_change_set ccs
				left join community_change cc ON ccs.id_community_change_set = cc.id_community_change_set
				left join admin a on a.id_admin=ccs.id_admin
				where
					ccs.id_community=?
					and ( cc.field = ? OR cc.field = ? OR cc.field = ? )
					AND cc.new_value = true
					order by timestamp desc
					limit 1
			', [$community->id_community, 'close_all_restaurants', 'close_3rd_party_delivery_restaurants', 'is_auto_closed'])->get(0);

			if ($closed) {
				$closedAt = new DateTime($closed->timestamp, new DateTimeZone(c::config()->timezone));
				$name = $closed->id_admin ? $closed->name : Admin::login( Crunchbutton_Community::AUTO_SHUTDOWN_COMMUNITY_LOGIN )->name;
				$s->closedLog = [
					'closed_at' => $closedAt->format('M jS Y g:i:s A T'),
					'closed_by' => $name,
					'note' => Community::closedNote($closed->id_community_change_set, $closed->field)
				];
				if ($closed->field == 'close_all_restaurants' ){
					$s->closedLog[ 'type' ] = Crunchbutton_Community::TITLE_CLOSE_ALL_RESTAURANTS;
				} elseif ( $closed->field == 'close_3rd_party_delivery_restaurants' ){
					$s->closedLog[ 'type' ] = Crunchbutton_Community::TITLE_CLOSE_3RD_PARY_RESTAURANTS;
				} elseif ( $closed->field == 'is_auto_closed' ){
					$s->closedLog[ 'type' ] = Crunchbutton_Community::TITLE_CLOSE_AUTO_CLOSED;
				}
			}

//



			// pull up community closed log
			// @todo seems to take a little longer. need to clean this up
			//$s->closedLog = $community->closedSince()[0];

//			$data[] = $out;
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
