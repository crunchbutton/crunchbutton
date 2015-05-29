<?php

class Controller_api_tickets extends Crunchbutton_Controller_RestAccount {

	public function init() {

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

		$q = '
			SELECT
				-WILD-
			FROM support s
			inner join support_message sm on sm.id_support=s.id_support
			inner JOIN `user` u ON u.id_user=s.id_user
			inner JOIN `order` o ON o.id_order=s.id_order

			where
				sm.id_support_message=(
					SELECT MAX(support_message.id_support_message) a
					FROM support_message
					WHERE support_message.id_support=s.id_support
				)
		';

		if ($status != 'all') {
			$q .= "
				AND s.status='".($status == 'closed' ? 'closed' : 'open')."'
			";
		}

		if ($admin != 'all') {
			$q .= '
				AND s.id_admin=?
			';
			$keys['admin'] = $admin;
		}

		if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'support-crud' ])) {
			// only display support to their number
			$phone = preg_replace('/[^0-9]/','', c::admin()->phone);
			$q .= ' AND s.phone=?';
			$keys['phone'] = $phone;
		}

		if ($search) {
			$s = Crunchbutton_Query::search([
				'search' => stripslashes($search),
				'fields' => [
					'u.name' => 'like',
					'u.phone' => 'like',
					'u.address' => 'like',
					'o.name' => 'like',
					'o.phone' => 'like',
					'o.address' => 'like',
					's.id_support' => 'liker'
				]
			]);
			$q .= $s['query'];
			$keys = array_merge($keys, $s['keys']);
		}


		$count = 0;

		// get the count
		if ($getCount) {
			$r = c::db()->query(str_replace('-WILD-','COUNT(DISTINCT `s`.id_support) as c', $q), $keys);
			while ($c = $r->fetch()) {
				$count = $c->c;
			}
		}

		$q .= '
			GROUP BY s.id_support, sm.id_support_message
			ORDER BY sm.id_support_message DESC
			LIMIT ?
			OFFSET ?
		';
		$keys[] = $getCount ? $limit : $limit+1;
		$keys[] = $offset;

		// do the query
		$d = [];
		$query = str_replace('-WILD-','
			s.id_support,
			max(sm.body) as message,
			s.name,
			s.phone,
			s.type,
			max(sm.id_support_message) as id_support_message,
			max(sm.id_admin) as id_admin,
			max(sm.date) as date,
			max(sm.name) as message_name,
			max(sm.phone) as message_phone,
			max(sm.from) as "from",
			max(u.name) as user_name,
			max(u.id_user) as id_user,
			s.status
		', $q);

		$r = c::db()->query($query, $keys);

		$i = 1;
		$more = false;

		while ($o = $r->fetch()) {
			if (!$getCount && $i == $limit + 1) {
				$more = true;
				break;
			}

			if (!$o->name) {
				$n = Phone::name($o, true);
				$o->name = $n['name'];
				$o->id_admin_from = $n['id_admin'];
			}

			$support = Support::o( $o->id_support );
			$message = $support->lastMessage();
			$message = $message->get( 0 );
			$o->message = $message->body;
			$date = $message->date();
			$o->timestamp = $date->getTimestamp();
			$o->date = $date->format( 'Y-m-d H:i:s' );
			$o->ts = Crunchbutton_Util::dateToUnixTimestamp( $date );
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