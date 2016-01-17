<?php

class Controller_api_suggestion extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if (!c::admin()->permission()->check(['global', 'suggestions-all', 'suggestions-list-page', 'support-all', 'support-view', 'support-crud'])) {
			$this->error(401, true);
		}

		$id_suggestion = $this->request()[ 'id_suggestion' ];

		$suggestion = Suggestion::o( $id_suggestion );

		if( !$suggestion->id_suggestion ){
			$this->_error();
		}

		switch ( c::getPagePiece( 2 ) ) {
			case 'delete':
				$suggestion->status = 'deleted';
				$suggestion->save();
				echo json_encode( [ 'success' => $suggestion->id_suggestion ] );
				exit();
				break;

			case 'apply':
				$suggestion->status = 'applied';
				$suggestion->save();
				echo json_encode( [ 'success' => $suggestion->id_suggestion ] );
				exit();
				break;
		}
		$this->_error();
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}

}