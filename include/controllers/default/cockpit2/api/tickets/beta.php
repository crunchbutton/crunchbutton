<?php

class Controller_api_tickets_beta extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if (!c::user()->permission()->check(['global', 'support-all', 'support-view', 'support-crud', 'community-cs' ])) {
			$this->error(401, true);
		}

		$limit = $this->request()['limit'] ? $this->request()['limit'] : 20;
		$status = $this->request()['status'] ? $this->request()['status'] : 'open';
		$type = $this->request()['type'] ? $this->request()['type'] : 'all';
		$search = $this->request()['search'] ? $this->request()['search'] : '';
		$admin = $this->request()['admin'] ? $this->request()['admin'] : 'all';
		$page = $this->request()['page'] ? $this->request()['page'] : 1;
		$getCount = $this->request()['fullcount'] && $this->request()['fullcount'] != 'false' ? true : false;
		$keys = [];

		if ($page == 1) {
			$offset = '0';
		} else {
			$offset = ($page-1) * $limit;
		}

		$q = '	SELECT -WILD- FROM (
							SELECT tickets.*, u.email AS customer_email, u.name AS customer_name, client_admin.name as client_admin_name FROM (
							SELECT
							s.id_support,
							s.id_order,
							s.phone AS support_phone,
							s.type,
							s.status,

							c.id_community,
							c.name AS community,

							o.name AS order_name,
							o.id_user AS order_id_user,
							o.phone AS order_phone,
							o.address AS order_address,

							client_message.body AS client_last_message,
							client_phone.phone AS client_phone,

							UNIX_TIMESTAMP( client_message.date ) AS client_message_timestamp,
							( SELECT COALESCE ( MAX( id_user ), NULL ) AS id_user FROM user WHERE user.id_phone = client_phone.id_phone ) AS client_id_user,
							( SELECT COALESCE ( MAX( id_admin ), NULL ) AS id_admin FROM admin WHERE admin.id_phone = client_phone.id_phone ) AS client_id_admin,

							rep.email AS rep_email,
							rep.name AS rep_name,
							rep_message.body AS rep_last_message,
							UNIX_TIMESTAMP( rep_message.date ) AS rep_timestamp,

							system_message.body AS system_last_message,
							recent_message.type AS recent_type,
							recent_message.from AS recent_from,

							UNIX_TIMESTAMP( system_message.date ) AS system_timestamp,

							UNIX_TIMESTAMP( recent_message.date ) AS recent_timestamp,
							id_support_message_recent


							 FROM ( SELECT
							s.id_support,
							( SELECT COALESCE ( MAX( support_message.id_support_message ), NULL ) AS id_support_message_client FROM support_message WHERE support_message.id_support = s.id_support AND ( support_message.from = \'client\' ) LIMIT 1 ) AS id_support_message_client,
							( SELECT COALESCE ( MAX( support_message.id_support_message ), NULL ) AS id_support_message_system FROM support_message WHERE support_message.id_support = s.id_support AND ( support_message.from = \'system\' )  LIMIT 1 ) AS id_support_message_system,
							( SELECT COALESCE ( MAX( support_message.id_support_message ), NULL ) AS id_support_message_rep FROM support_message WHERE support_message.id_support = s.id_support AND ( support_message.from = \'rep\' )  LIMIT 1 ) AS id_support_message_rep,
							( SELECT COALESCE ( MAX( support_message.id_support_message ), NULL ) AS id_support_message_recent FROM support_message WHERE support_message.id_support = s.id_support  LIMIT 1 ) AS id_support_message_recent
							FROM support s
							WHERE
							s.id_support > ( SELECT id_support FROM support ORDER BY id_support DESC LIMIT 1) - 10000 ) temp
							INNER JOIN support s ON temp.id_support = s.id_support
							LEFT JOIN community c ON c.id_community = s.id_community
							LEFT JOIN support_message client_message ON client_message.id_support_message = id_support_message_client
							LEFT JOIN support_message rep_message ON rep_message.id_support_message = id_support_message_rep
							LEFT JOIN support_message system_message ON system_message.id_support_message = id_support_message_system
							LEFT JOIN support_message recent_message ON recent_message.id_support_message = id_support_message_recent
							LEFT JOIN admin rep ON rep.id_admin = rep_message.id_admin
							LEFT JOIN phone client_phone ON client_phone.id_phone = client_message.id_phone
							LEFT JOIN `order` o ON o.id_order = s.id_order
							ORDER BY id_support_message_recent DESC
							) tickets
							LEFT JOIN user u ON u.id_user = tickets.client_id_user
							LEFT JOIN admin client_admin ON client_admin.id_admin = client_id_admin
							) tickets
							 WHERE 1 = 1';

		if (!c::user()->permission()->check(['global', 'support-all', 'support-view', 'support-crud'])) {
			$communities = c::user()->communitiesDriverDelivery();
			$q .= ' AND (';
			$or = '';
			foreach($communities as $community){
				$q .= $or . 'tickets.id_community = ?';
				$or = ' OR ';
				$keys[] = $community->id_community;
			}
			$q .= ') ';
		}

		if( $type == 'system' ){
			$q .= '
				tickets.type = ?
			';
			$keys[] = Crunchbutton_Support::TYPE_WARNING;
		} else if( $type == 'system' ){

		}
		if ($status != 'all') {
			$q .= "
				AND tickets.status = ?
			";
			$status == 'closed' ? 'closed' : 'open';
			$keys[] = $status;
		}

		if ($admin != 'all') {
			$q .= '
				AND tickets.client_id_admin = ?
			';
			$keys[] = $admin;
		}

		if (!c::user()->permission()->check(['global', 'support-all', 'support-view', 'support-crud' ])) {
			// only display support to their number
			// $phone = preg_replace('/[^0-9]/','', c::admin()->phone);
			// $q .= ' AND s.phone=?';
			// $keys[] = $phone;
		}

		if ($search) {
			$s = Crunchbutton_Query::search([
				'search' => stripslashes($search),
				'fields' => [
					'tickets.rep_name' => 'like',
					'tickets.order_name' => 'like',
					'tickets.order_name' => 'like',
					'tickets.customer_email' => 'like',
					'tickets.customer_name' => 'like',
					'tickets.system_last_message' => 'like',
					'tickets.rep_last_message' => 'like',
					'tickets.client_last_message' => 'like',
					'tickets.order_address' => 'like',
					'tickets.id_support' => 'inteq'
				]
			]);
			$q .= $s['query'];
			$keys = array_merge($keys, $s['keys']);
		}


		$count = 0;

		// // get the count
		if ($getCount) {
			$r = c::db()->query(str_replace('-WILD-','COUNT(*) as c', $q), $keys);
			while ($c = $r->fetch()) {
				$count = $c->c;
			}
		}

		$q .= '
			ORDER BY tickets.id_support_message_recent DESC
			LIMIT '.intval($getCount ? $limit : $limit+1).'
			OFFSET '.intval($offset).'
		';

		$d = [];
		$query = str_replace('-WILD-','*', $q);

		$r = c::db()->show_query($query, $keys);

		$i = 1;
		$more = false;

		while ($o = $r->fetch()) {
			if (!$getCount && $i == $limit + 1) {
				$more = true;
				break;
			}
			if($o->id_community){
				$c = Community::o($o->id_community);
				if($c->hasCommunityCS()){
					$o->community_has_cs = true;
				}
			}
			$o->recent_from = ucfirst($o->recent_from);
			$d[] = $o;
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
