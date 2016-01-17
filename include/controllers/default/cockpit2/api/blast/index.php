<?php

class Controller_api_blast extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if (!c::admin()->permission()->check(['global', 'blast-all', 'blast-view' ])) {
			$this->error(401, true);
		}

		switch ($this->method()) {
			case 'delete':
				$blast = Blast::o(c::getPagePiece(2));
				if (!$blast->id_blast) {
					$this->error(404, true);
				}
				$blast->status = 'canceled';
				$blast->save();
				break;

			case 'get':
				$blast = Blast::o(c::getPagePiece(2));
				if (!$blast->id_blast) {
					$this->error(404, true);
				}
				switch (c::getPagePiece(3)) {
					case 'users':
						echo $blast->users()->json();
						exit;
						break;

					case 'run':
						$blast->run();
						exit;
						break;

					default:
						$out = $blast->exports();
						foreach ($blast->users() as $user) {
							$out['users'][] = $user->exports();
						}
						echo json_encode($out);
						exit;
				}
				break;

			case 'post':
				if (c::getPagePiece(2) == 'sample') {
					$data = Blast::parseCsv($this->request()['sample']);
					$sample = [];
					$max = 10;
					$i = 0;

					$blast = new Blast([
						'content' => $this->request()['content']
					]);

					foreach ($data as $item) {
						$phone = $item['phone'];
						if ($phone) {
							unset($item['phone']);
							$user = new Blast_User([
								'phone' => $phone,
								'data' => json_encode($item)
							]);
							$user->_blast = $blast;
							$sample[] = ['phone' => $phone, 'message' => $user->message()];
							$i++;
						}
						if ($max == $i) {
							break;
						}
					}
					echo json_encode($sample);
					exit;

				} else {
					$blast = new Blast($this->request());
					$blast->status = 'new';
					$blast->id_admin = c::user()->id_admin;
					$blast->type = 'phone';
					$blast->save();
					$blast->importData($this->request()['data']);
					$blast = Blast::o($blast->id_blast);
				}
				break;
		}

		echo $blast->json();
		exit;
	}

}