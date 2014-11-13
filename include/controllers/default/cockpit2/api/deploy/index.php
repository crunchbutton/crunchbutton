<?php

class Controller_api_deploy extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if (!c::admin()->permission()->check(['server-deploy'])) {
			exit;
		}
		
		switch (c::getPagePiece(2)) {
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
				if ($this->method == 'post') {
					$r = new Deploy_Version($this->request());
					$r->save();
				} else {
					$r = Deploy_Version::o(c::getPagePiece(3));
				}
				break;
		}

		echo $r->json();
		exit;

	}
}