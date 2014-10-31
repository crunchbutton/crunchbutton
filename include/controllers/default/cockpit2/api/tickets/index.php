<?php

class Controller_api_tickets extends Crunchbutton_Controller_RestAccount {

	public function init() {
		if ($this->method() != 'get') {
			exit;
		}
		
		if (c::getPagePiece(2)) {
			$support = Support::o(c::getPagePiece(2));
			if (!$support->id_support) {
				header('HTTP/1.0 404 Not Found');
				exit;
			}
			
			if (get_class($support) != 'Crunchbutton_Support') {
				$support = $support->get(0);
			}

			echo $support->json();
			exit;
		}

		$limit = $this->request()['limit'] ? c::db()->escape($this->request()['limit']) : 25;
		$staus = $this->request()['status'] ? c::db()->escape($this->request()['status']) : 'open';
		$type = $this->request()['type'] ? c::db()->escape($this->request()['type']) : 'all';
		$search = $this->request()['search'] ? c::db()->escape($this->request()['search']) : '';
		
/*
		$q = '
			SELECT
				s.id_support,
				sm.id_support_message,
				sm.date,
				sm.name,
				u.name as user_name,
				u.id_user,
				sm.body as message
			FROM support s
			INNER JOIN support_message sm ON s.id_support = sm.id_support
			INNER JOIN (
				SELECT id_support, id_support_message, body, MAX(date) max_date
				FROM support_message
				GROUP BY id_support
			) sms on sm.id_support = sms.id_support AND sm.date = sms.max_date
			LEFT JOIN user u ON u.id_user=s.id_user
			WHERE 1=1
		';
*/
		$q = '
			SELECT
				s.id_support,
				sm.id_support_message,
				sm.date,
				sm.name,
				u.name as user_name,
				u.id_user,
				sm.body as message
			FROM support s
			INNER JOIN support_message sm ON s.id_support = sm.id_support
			LEFT JOIN user u ON u.id_user=s.id_user
			WHERE 1=1
		';

		if ($staus != 'all') {
			$q .= '
				AND s.status="'.$staus.'"
			';
		}

		if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'support-crud' ])) {
			// only display support to their number
		}

		$q .= '
			ORDER BY s.datetime DESC
			LIMIT '.$limit.'
		';

		$r = c::db()->query($q);
		while ($o = $r->fetch()) {
			/*
			if ($o->id_user) {
				$u = User::o($o->id_user);
			}
			$o->image = $u->image();
			*/
			$d[] = $o;
		}
		echo json_encode($d);
		exit;
	}
}