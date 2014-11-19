<?php

class Controller_api_deploy extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if (!c::admin()->permission()->check(['global', 'server-deploy-admin', 'server-deploy'])) {
			exit;
		}

		switch (c::getPagePiece(2)) {

			case 'servers':
				$r = Deploy_Server::q('select * from deploy_server where active="1" order by name');
				break;

			case 'server':
				$server = Deploy_Server::o(c::getPagePiece(3));
				if (!$server->id_deploy_server) {
					$server = Deploy_Server::byName(c::getPagePiece(3));
				}
				if (!$server->id_deploy_server) {
					header('HTTP/1.0 404 Not Found');
					exit;
				}
				
				switch (c::getPagePiece(4)) {
					case 'commits':
						$r = $server->commits();
						break;

					case 'versions':
						$r = $server->versions();
						if (!$r || !$r->count()) {
							$r = [];
						}
						break;
					default:
						$r = $server;
						break;
				}
				break;

			case 'versions':
				$r = Deploy_Version::q('select * from deploy_version order by date desc limit 10');
				break;

			case 'version':
				if ($this->method() == 'post') {
					if (!c::admin()->permission()->check(['server-deploy-admin'])) {
						header('HTTP/1.1 401 Unauthorized');
						exit;
					}
					
					$server = Server::o($this->request()['id_deploy_server']);
					if (!$server->id_deploy_server) {
						header('HTTP/1.0 404 Not Found');
						exit;
					}

					$date = $this->request()['date'];
					$version = $this->request()['version'];

					$d = strtotime($date);
					if ($d < time()) {
						$date = date('Y-m-d H:i:s');
					}

					if ($version == 'master') {
						$version = $server->commits()[0]['commit'];
					}
					
					if ($server->tag) {
						$server->createTag($version);
					}

					$r = new Deploy_Version([
						'date' => $date,
						'version' => $version,
						'id_deploy_server' => $server->id_deploy_server,
						'status' => 'new',
						'id_admin' => c::admin()->id_admin
					]);
					$r->save();
				} else {
					$r = Deploy_Version::o(c::getPagePiece(3));
				}
				break;
		}

		if ($r && method_exists($r, 'json')) {
			echo $r->json();
		} else {
			echo json_encode($r);
		}
		exit;

	}
}