<?php

class Controller_api_community_note extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'notes-all', 'community-cs' ])) {
			$this->error(401, true);
		}

		if( $this->method() == 'post' ){

			$community = Community::permalink( $this->request()[ 'community' ] );

			if (!$community->id_community) {
				$community = Community::o( $this->request()[ 'community' ] );
			}

			if (!$community->id_community) {
				$this->error(404, true);
			}

			$note = $community->addNote( $this->request()[ 'note' ] );
			echo json_encode( $note->exports() );exit;
		}

		$this->error(401, true);

	}

}