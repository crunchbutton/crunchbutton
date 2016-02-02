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
		$order = $this->request()['order'] ? $this->request()['order'] : 'support';
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
			inner join support_message smr on smr.id_support=s.id_support
			left JOIN `order` o ON o.id_order=s.id_order
			left join `phone` p on p.id_phone=sm.id_phone
			left JOIN `user` u ON u.id_phone=p.id_phone
			left JOIN `admin` a ON a.id_phone=p.id_phone
			where
			';

		if( $type == 'all' ){
			$q .= '
					( sm.id_support_message=(
						SELECT MAX(support_message.id_support_message) a
						FROM support_message
						WHERE
							support_message.id_support=s.id_support
							AND ( support_message.from=\'client\' OR support_message.from=\'system\' )
					) or sm.id_support_message IS NOT NULL )
					and smr.id_support_message=(
						SELECT MAX(support_message.id_support_message) a
						FROM support_message
						WHERE
							support_message.id_support=s.id_support
					)
			';
		} else if( $type == 'system' ){
			$q .= '
				s.type = \'' . Crunchbutton_Support::TYPE_WARNING . '\'
			';
		}
		if ($status != 'all') {
			$q .= "
				AND s.status='".($status == 'closed' ? 'closed' : 'open')."'
			";
		}

		if ($admin != 'all') {
			$q .= '
				AND s.id_admin=?
			';
			$keys[] = $admin;
		}

		if (!c::user()->permission()->check(['global', 'support-all', 'support-view', 'support-crud' ])) {
			// only display support to their number
			$phone = preg_replace('/[^0-9]/','', c::admin()->phone);
			$q .= ' AND s.phone=?';
			$keys[] = $phone;
		}

		if ($search) {
			$s = Crunchbutton_Query::search([
				'search' => stripslashes($search),
				'fields' => [
					's.name' => 'like',
					'o.name' => 'like',
					'u.name' => 'like',
					'u.email' => 'like',
					'sm.body' => 'like',

					'u.phone' => 'likephone',
					'u.address' => 'like',

					'o.phone' => 'likephone',
					'o.address' => 'like',
					's.id_support' => 'inteq'
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
			GROUP BY s.id_support
		';

		switch ( $order ) {
			case 'message':
				$q .= '
					ORDER BY smr.id_support_message DESC
				';
				break;

			default:
				$q .= '
					ORDER BY s.id_support DESC
				';
				break;
		}

		$q .= '
			LIMIT '.intval($getCount ? $limit : $limit+1).'
			OFFSET '.intval($offset).'
		';

		// do the query
		$d = [];
		$query = str_replace('-WILD-','
			s.id_support,
			s.name,
			s.phone,
			s.type,
			max(sm.phone) as message_phone,
			max(sm.from) as from_client,
			max(smr.from) as from_recent,
			max(sm.body) as message_client,
			max(smr.body) as message_recent,
			max(sm.id_support_message) as id_support_message_client,
			max(smr.id_support_message) as id_support_message_recent,
			UNIX_TIMESTAMP(max(sm.date)) as timestamp_client,
			UNIX_TIMESTAMP(max(smr.date)) as timestamp_recent,
			max(u.name) as name_user,
			max(a.name) as name_admin,
			max(a.id_admin) as id_admin,
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

			if( !$o->id_admin_from ){
				$phone = Phone::byPhone( $o->phone );
				$admin = Admin::q( 'SELECT * FROM admin WHERE id_phone = ? ORDER BY id_admin DESC LIMIT 1', [ $phone->id_phone ] )->get( 0 );
				if( $admin->id_admin ){
					$o->id_admin_from = $admin->id_admin;
				}
			}

			if( !$o->id_admin_from && !$o->id_user ){
				if( !$phone->id_phone ){
					$phone = Phone::byPhone( $o->phone );
				}
				$order = Order::q( 'SELECT * FROM `order` WHERE id_phone = ? ORDER BY id_order DESC LIMIT 1', [ $phone->id_phone ] )->get( 0 );
				if( $order->id_user ){
					$o->id_user = $order->id_user;
				}
			}

			$support = Support::o( $o->id_support );
			$lastReplyFrom = $support->lastMessage();
			$o->last_reply = $lastReplyFrom->from;
			$o->last_reply_type = $lastReplyFrom->type;

			$o->media = $lastReplyFrom->media;

			$lastNonSystemMessage = $support->lastNonSystemMessage();

			// if( $lastNonSystemMessage->id_support_message ){
				// $o->message_client = $lastNonSystemMessage->body;
			// }
			$o->message_client = $lastReplyFrom->body;

			if( $o->status == Crunchbutton_Support::STATUS_CLOSED && $lastNonSystemMessage->id_support_message ){
				$messageBeforeLast = Crunchbutton_Support_Message::q( 'SELECT * FROM support_message WHERE id_support = ? AND id_support_message > ? ORDER BY id_support_message DESC LIMIT 1', [ $o->id_support, $lastNonSystemMessage->id_support_message ] )->get( 0 );
				if( $messageBeforeLast->id_support_message ){
					$o->message_client = $o->message_client . "<br><i>{$messageBeforeLast->body}</i>" ;
				}
			}

			/*
			$support = Support::o( $o->id_support );
			$message = $support->lastMessage();
			$message = $message->get( 0 );
			$o->message = $message->body;
			$date = $message->date();
			$o->timestamp = $date->getTimestamp();
			$o->date = $date->format( 'Y-m-d H:i:s' );
			$o->ts = Crunchbutton_Util::dateToUnixTimestamp( $date );
			*/

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
