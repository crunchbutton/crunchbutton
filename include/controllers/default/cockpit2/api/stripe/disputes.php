<?php

class Controller_Api_Stripe_Disputes extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if (!c::admin()->permission()->check( [ 'global', 'disputes-all' ] ) ) {
			$this->error(401);
		}

		$limit = $this->request()['limit'] ? $this->request()['limit'] : 20;
		$search = $this->request()['search'] ? $this->request()['search'] : '';
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
			FROM stripe_dispute sd
			LEFT JOIN `order` o ON o.id_order=sd.id_order
			LEFT JOIN user u ON u.id_user=o.id_user
			WHERE 1=1
			';

		if ($search) {
			$s = Crunchbutton_Query::search([
				'search' => stripslashes($search),
				'fields' => [
					'sd.reason' => 'like',
					'o.id_order' => 'like',
					'u.name' => 'like',
					'u.phone' => 'like',
					'u.address' => 'like',
					'o.name' => 'like',
					'o.phone' => 'like',
					'o.address' => 'like'
				]
			]);
			$q .= $s['query'];
			$keys = array_merge($keys, $s['keys']);
		}


		$count = 0;

		// get the count
		if ($getCount) {
			$r = c::db()->query(str_replace('-WILD-','COUNT(DISTINCT `s`.id_stripe_dispute) as c', $q), $keys);
			while ($c = $r->fetch()) {
				$count = $c->c;
			}
		}
//			#, sm.id_support_message
		$q .= '
			ORDER BY sd.id_stripe_dispute DESC
			LIMIT '.intval($getCount ? $limit : $limit+1).'
			OFFSET '.intval($offset).'
		';

		// do the query
		$d = [];
		$query = str_replace('-WILD-','
			sd.id_order,
			sd.id_stripe_dispute,
			sd.status,
			sd.reason,
			sd.datetime,
			sd.due_to,
			sd.submission_count,
			o.name,
			o.phone,
			o.id_user
		', $q);

		$r = c::db()->query($query, $keys);

		$i = 1;
		$more = false;

		while ($dispute = $r->fetch()) {
			if (!$getCount && $i == $limit + 1) {
				$more = true;
				break;
			}
			$order = Order::o( $dispute->id_order );
			$dispute->reason = ucfirst( $dispute->reason );
			$dispute->charged = $order->charged();
			$d[] = $dispute;
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
