<?php

class Controller_api_shifts_checkin extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if( !c::admin()->permission()->check( ['global', 'support-all', 'support-view', 'support-crud' ] ) ){
			$this->error( 401 );
		}

		$limit = 100;
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

		$q = ' SELECT -WILD- FROM community c
							INNER JOIN community_shift cs ON c.id_community = cs.id_community AND cs.date_start > ? AND cs.date_end < ? AND cs.active = 1
							INNER JOIN admin_shift_assign asa ON asa.id_community_shift = cs.id_community_shift
							INNER JOIN admin a ON a.id_admin = asa.id_admin
							WHERE c.driver_checkin = 1 ';


		$now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );

		$date_start = $now->format( 'Y-m-d' );
		$now->modify( '+1 day' );
		$date_end = $now->format( 'Y-m-d' ) . ' 23:59:59';

		$keys = [ $date_start, $date_end ];

		if ($status != 'all') {
			if ($status == 'confirmed') {
				$q .= '
					AND asa.confirmed = 1
				';
			}
			if ($status == 'not-confirmed') {
				$q .= '
					AND ( asa.confirmed IS NULL OR asa.confirmed = 0 )
				';
			}

		}

		if ($search) {
			$s = Crunchbutton_Query::search([
				'search' => stripslashes($search),
				'fields' => [
					'c.name' => 'like',
					'c.permalink' => 'like',
					'a.name' => 'like'
				]
			]);
			$q .= $s['query'];
			$keys = array_merge($keys, $s['keys']);
		}

		$q .= '
			ORDER BY cs.date_start ASC
		';

		$count = 0;

		// get the count
		if ($getCount) {
			$r = c::db()->query(str_replace('-WILD-','COUNT( * ) as c', $q), $keys);
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
		$r = c::db()->query(str_replace('-WILD-',' asa.id_admin_shift_assign, asa.confirmed, a.id_admin, a.name AS driver, a.login, a.phone, c.name AS community, c.permalink AS community_permalink ', $q), $keys);


		$i = 1;
		$more = false;

		while ($s = $r->fetch()) {

			if (!$getCount && $i == $limit + 1) {
				$more = true;
				break;
			}
			$asa = Crunchbutton_Admin_Shift_Assign::o( $s->id_admin_shift_assign );
			$shift = $asa->shift();

			$confirmations = Crunchbutton_Admin_Shift_Assign_Confirmation::q( 'SELECT * FROM admin_shift_assign_confirmation WHERE id_admin_shift_assign = ? ORDER BY id_admin_shift_assign_confirmation ASC ', [ $s->id_admin_shift_assign ] );
			foreach( $confirmations as $confirmation ){
				$s->{$confirmation->type} = $confirmation->datetime;
			}

			$s->confirmed = intval( $s->confirmed ) > 0 ? true : false;
			$s->date = $shift->dateStart()->format( 'M jS Y' );
			$s->period_sort = intval( $shift->dateStart( c::config()->timezone )->format( 'YmdHis' ) );
			$s->period = $shift->startEndToString() ;
			$s->community_tz = $shift->dateStart()->format( 'T' );
			$s->period_pst = $shift->startEndToString( c::config()->timezone );

			$data[] = $s;
			$i++;
		}

		usort( $data, function( $a, $b ){
			if( $a->period_sort > $b->period_sort ){
				return 1;
			}
			if( $a->period_sort == $b->period_sort ){
				return $a->community > $b->community;
			}
			if( $a->period_sort < $b->period_sort ){
				return -1;
			}
			; } );

		echo json_encode([
			'more' => $getCount ? $pages > $page : $more,
			'count' => intval($count),
			'pages' => $pages,
			'page' => intval($page),
			'results' => $data
		]);
	}
}