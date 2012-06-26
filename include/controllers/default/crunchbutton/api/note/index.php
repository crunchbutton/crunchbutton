<?php

class Controller_api_v1_note extends Crunchbutton_Controller_Rest {
	public function init() {
		switch ($this->method()) {
			case 'get':
				$note = Note::o(c::getPagePiece(3));
				if (!$note->id_note) {
					echo json_encode(['error' => 'invalid resource']);
					exit;
				}
				echo $note->json();
				break;

			case 'post':
				$note = new Note;
				$note->serialize($this->request());
				$note->save();
				break;
		}
	}
}