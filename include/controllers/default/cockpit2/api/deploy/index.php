<?php

class Controller_api_deploy extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if (!c::admin()->permission()->check(['global', 'server-deploy-admin', 'server-deploy'])) {
			$this->error(401, true);
		}

		switch (c::getPagePiece(2)) {

			case 'servers':
				$r = Deploy_Server::q('select * from deploy_server where active=true order by name');
				break;

			case 'server':
				$server = Deploy_Server::o(c::getPagePiece(3));
				if (!$server->id_deploy_server) {
					$server = Deploy_Server::byName(c::getPagePiece(3));
				}
				if (!$server->id_deploy_server) {
					$this->error(404, true);
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
				if ($this->method() == 'post' || $this->method() == 'delete') {
					if (!c::admin()->permission()->check(['server-deploy-admin'])) {
						$this->error(401, true);
					}
					
					if ($this->method() == 'post') {

						$server = Deploy_Server::o($this->request()['id_deploy_server']);
						if (!$server->id_deploy_server) {
							$server = Deploy_Server::byName($this->request()['id_deploy_server']);
						}
						if (!$server->id_deploy_server) {
							$this->error(404, true);
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
						$d = Deploy_Version::o(c::getPagePiece(3));
						if (!$d->id_deploy_version) {
							$this->error(404, true);

						} elseif ($d->status != 'new') {
							$this->error(406);
						}

						$d->status = 'canceled';
						$d->save();

						$r = $d;
					}

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