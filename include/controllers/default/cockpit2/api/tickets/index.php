<?php

class Controller_api_tickets extends Crunchbutton_Controller_RestAccount {

	public function init() {
		
		if (c::getPagePiece(2)) {
			$support = Support::o(c::getPagePiece(2));
			if (!$support->id_support) {
				header('HTTP/1.0 404 Not Found');
				exit;
			}
			
			if (get_class($support) != 'Crunchbutton_Support') {
				$support = $support->get(0);
			}

			if ($this->method() == 'get') {
				echo $support->json();
				exit;
			}

			if (c::getPagePiece(3) == 'message' && $this->method() == 'post') {
				$message = $support->addAdminMessage([
					'body' => $this->request()['body'],
					'phone' => c::admin()->phone,
					'id_admin' => c::admin()->id_admin
				]);
				if ($message->id_support_message) {
					$message->notify();
				}
				echo $message->json();
				exit;
			}

			header('HTTP/1.0 409 Conflict');
			exit;
			 
		}

		$limit = $this->request()['limit'] ? c::db()->escape($this->request()['limit']) : 20;
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
				sm.id_admin,
				sm.date,
				UNIX_TIMESTAMP(sm.date) as timestamp,
				sm.name,
				sm.phone,
				sm.from,
				u.name as user_name,
				a.name as admin_name,
				u.id_user,
				s.status,
				sm.body as message
			FROM support s
			INNER JOIN support_message sm ON s.id_support = sm.id_support
			LEFT JOIN user u ON u.id_user=s.id_user
			LEFT JOIN admin a ON a.id_admin=sm.id_admin
			WHERE 1=1
		';

		if ($staus != 'all') {
			$q .= '
				AND s.status="'.$staus.'"
			';
		}

		if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'support-crud' ])) {
			// only display support to their number
			$phone = preg_replace('/[^0-9]/','', c::admin()->phone);
			$q .= ' AND s.phone="'.$phone.'"';
		}

		$q .= '
			ORDER BY s.datetime DESC
			LIMIT '.$limit.'
		';
		
		$d = [];

		$r = c::db()->query($q);
		while ($o = $r->fetch()) {
			/*
			if ($o->id_user) {
				$u = User::o($o->id_user);
			}
			$o->image = $u->image();
			*/
//			$d[$o->id_support] = $o;

			$phone = preg_replace('/[^0-9]/','', $o->phone);

			if ($o->from == 'system') {
				$o->name = 'SYSTEM';

			} elseif (!$o->name) {

				$phoneFormat = preg_replace('/([0-9]{3})([0-9]{3})([0-9]{4})/','\\1-\\2-\\3', $phone);

				if ($phone) {
					$user = Crunchbutton_Admin::q('select * from admin where phone="'.$phone.'"');

					if (!$user->id_admin) {
						$user = Crunchbutton_Admin::q('select * from admin where phone="'.$phoneFormat.'"');
					}
					
					if (!$user->id_admin) {
						$user = Crunchbutton_User::q('select * from `user` where phone="'.$phone.'"');
					}
					
					if ($user->id_admin || $user->id_user) {
						$o->name = $user->phone;
					}
				}
				
			}
			
			if (!$o->name) {
				$o->name = $phone;
			}
			
			$d[$o->id_support] = $o;
		}

		echo json_encode(array_values($d));
		exit;
	}
}