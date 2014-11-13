<?php

class Controller_api_deploy extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if (!c::admin()->permission()->check(['server-deploy'])) {
			exit;
		}

		switch (c::getPagePiece(2)) {
			case 'gitlog':
				$logs = [];
				exec('git log -n 20', $o);
				foreach ($o as $k => $line) {
					if (substr($line, 0, 6) == 'commit') {
						$log = [];
						$notes = '';
						$log['commit'] = preg_replace('/commit /', '', $line);
						$kk = $k;
						if (preg_match('/Merge:/', $o[$kk+1])) {
							$notes = $o[$kk+1];
							$kk++;
						}
						
						$log['author'] = preg_replace('/^Author: (.*) <.*$/i', '\\1', $o[$kk+1]);
						$log['date'] = strtotime(trim(preg_replace('/Date:/', '', $o[$kk+2])));
						$log['note'] =  ($notes ? $notes.'. ' : '').trim($o[$kk+4]);

						$logs[] = $log;
					}
				}
				echo json_encode($logs);
				exit;
				break;

			case 'servers':
				$r = Deploy_Server::q('select * from deploy_server');
				break;

			case 'server':
				switch (c::getPagePiece(4)) {
					case 'versions':
						$r = Deploy_Server::o(c::getPagePiece(3))->versions();
						break;
					default:
						$r = Deploy_Server::o(c::getPagePiece(3));
						break;
				}
				break;

			case 'versions':
				$r = Deploy_Version::q('select * from deploy_version order by date desc limit 10 ');
				break;

			case 'version':
				if ($this->method() == 'post') {
					$date = $this->request()['date'];
					$d = strtotime($date);
					if ($d < time()) {
						$date = date('Y-m-d H:i:s');
					}
					$r = new Deploy_Version([
						'date' => $date,
						'version' => $this->request()['version'],
						'id_deploy_server' => $this->request()['id_deploy_server'],
						'status' => 'new',
						'id_admin' => c::admin()->id_admin
					]);
					$r->save();
				} else {
					$r = Deploy_Version::o(c::getPagePiece(3));
				}
				break;
		}

		if ($r) {
			echo $r->json();
		}
		exit;

	}
}