<?php

class Controller_api_calls extends Crunchbutton_Controller_RestAccount {

	public function init() {

		$limit = $this->request()['limit'] ? $this->request()['limit'] : 20;
		$page = $this->request()['page'] ? $this->request()['page'] : 1;
		$status = $this->request()['status'] ? $this->request()['status'] : 'all';
		$type = $this->request()['type'] ? $this->request()['type'] : 'all';
		$search = $this->request()['search'] ? $this->request()['search'] : '';
		$getCount = $this->request()['fullcount'] && $this->request()['fullcount'] != 'false' ? true : false;
		$datestart = $this->request()['datestart'] ? $this->request()['datestart'] : null;
		$today = $this->request()['today'] ? true : false;

		$keys = [];

		if ($page == 1) {
			$offset = '0';
		} else {
			$offset = ($page-1) * $limit;
		}

		$q = '
			SELECT
				-WILD-
			FROM `call` c
			LEFT JOIN `user` uf ON uf.id_user=c.id_user_from
			LEFT JOIN `user` ut ON ut.id_user=c.id_user_to
			LEFT JOIN admin af ON af.id_admin=c.id_admin_from
			LEFT JOIN admin at ON at.id_admin=c.id_admin_to
			WHERE 1=1
		';

		if ($status != 'all') {
			if (!is_array($status)) {
				$status = [$status];
			}
			foreach ($status as $s) {
				$st .= ($st ? ' OR ' : '').' c.status=? ';
				$keys[] = $s;
			}
			$q .= '
				AND ('.$st.')
			';
		}

		if ($today) {
			$q .= '
				AND c.date_start >= date_sub(now(), interval 2 hour)
			';
		} elseif ($datestart) {
			$datestart = date('Y-m-d', strtotime($datestart));
			$q .= '
				AND c.date_start >= ?
			';
			$keys[] = $datestart;
		}

		if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'support-crud' ])) {
			// only display support to their number
			$q .= ' AND (
				c.from=?
				OR c.to=?
			)';
			$keys[] = Phone::clean(c::admin()->phone);
			$keys[] = Phone::clean(c::admin()->phone);
		}

		if ($search) {
			$s = Crunchbutton_Query::search([
				'search' => stripslashes($search),
				'fields' => [
					'uf.name' => 'like',
					'uf.phone' => 'like',
					'ut.name' => 'like',
					'ut.phone' => 'likephone',
					'af.name' => 'like',
					'af.phone' => 'like',
					'at.name' => 'like',
					'at.phone' => 'likephone',
					'c.id_call' => 'inteq'
				]
			]);
			$q .= $s['query'];
			$keys = array_merge($keys, $s['keys']);
		}

		$q .= '
			GROUP BY c.id_call
		';

		$count = 0;

		// get the count
		if ($getCount) {
			$r = c::db()->query(str_replace('-WILD-','COUNT(DISTINCT c.id_call) as c', $q), $keys);
			while ($c = $r->fetch()) {
				$count = $c->c;
			}
		}

		$q .= '
			ORDER BY c.date_start DESC
			LIMIT '.intval($getCount ? $limit : $limit+1).'
			OFFSET '.intval($offset).'
		';

		// do the query
		$data = [];
		$query = str_replace('-WILD-','
			c.id_call,
			c.direction,
			c.date_start,
			c.date_end,
			c.location_to,
			c.location_from,
			c.status,
			c.twilio_id,
			c.recording_url,
			c.recording_duration,
			c.from,
			c.to,
			max(uf.name) as user_from,
			max(ut.name) as user_to,
			max(at.name) as admin_to,
			max(af.name) as admin_from,
			c.id_user_from,
			c.id_user_to,
			c.id_admin_from,
			c.id_admin_to
		', $q);
		$r = c::db()->query($query, $keys);

		$i = 1;
		$more = false;

		while ($o = $r->fetch()) {
			if (!$getCount && $i == $limit + 1) {
				$more = true;
				break;
			}
			if ($o->direction == 'inbound') {
				if ($o->user_from) {
					$name = $o->user_from;
				}
				if ($o->admin_from) {
					$name = $o->admin_from;
				}
				if (!$name) {
					$o->from_name = Phone::name($o->from);
				}
			} else {
				if ($o->user_to) {
					$name = $o->user_to;
				}
				if ($o->admin_to) {
					$name = $o->admin_to;
				}
				if (!$name) {
					$o->to_name = Phone::name($o->to);
				}
			}
			$i++;

			$data[] = $o;
		}

		echo json_encode([
			'more' => $getCount ? $pages > $page : $more,
			'count' => intval($count),
			'pages' => $pages,
			'page' => intval($page),
			'results' => $data
		], JSON_NUMERIC_CHECK);

		exit;
	}

/*
	public function _twilio() {
		//"Status" => "in-progress",
		$i = 0;
		$max = 5;
		foreach(c::twilio()->account->calls->getIterator(0,$max,[
			'Direction' => 'inbound',
			'To' => '_PHONE_'
		]) as $call) {
			if ($i == $max) {
				break;
			}
			$i++;

			$call->from = preg_replace('/^\+1/','',$call->from);
			$support = Support::byPhone($call->from);
			$ticket = [];

			if ($support && $support->get(0)) {
				$ticket = [
					'id_support' => $support->get(0)->id_support,
					'id_order' => $support->get(0)->id_order,
					'status' => $support->get(0)->status,
					'id_user' => $support->get(0)->id_user,
					'id_admin' => $support->get(0)->id_admin
				];
			}

			$calls[] = [
				'from' => $call->from,
				'status' => $call->status,
				'start_time' => $call->start_time,
				'end_time' => $call->end_time,
				'sid' => $call->sid,
				'ticket' => $ticket
			];

		}

		echo json_encode($calls);
		exit;
	}
*/
}
