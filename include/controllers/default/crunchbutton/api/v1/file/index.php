<?php

class Controller_api_v1_file extends Crunchbutton_Controller_Rest {
	public function init() {
		switch ($this->method()) {
			case 'delete':
				$file = Crunchbutton_File::o(c::getPagePiece(3));
				if (!$file->id_file) {
					echo json_encode(['error' => 'invalid resource']);
					exit;
				}
				$file->active = 1;
				$file->save();

				break;

			case 'get':
				$file = Crunchbutton_File::o(c::getPagePiece(3));
				if (!$file->id_file) {
					echo json_encode(['error' => 'invalid resource']);
					exit;
				}
				switch (c::getPagePiece(4)) {
					case 'notes':
						echo $file->notes()->json();
						break;
					default:
						echo $file->json();
						break;
				}
				break;

			case 'post':
				$file = new Crunchbutton_File;
				$file->serialize($this->request());
				$file->save();
				break;
		}
	}
}