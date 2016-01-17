<?php

class Controller_api_chain extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if (!c::admin()->permission()->check(['global', 'chain-all', 'chain-view', 'chain-crud'])) {
			$this->error(401, true);
		}

		switch ( $this->method() ) {

			case 'get':
				switch ( c::getPagePiece( 2 ) ) {
					case 'simple':

					$out = [];

					$chains = Chain::q( 'SELECT id_chain, name FROM chain WHERE active = 1 ORDER BY name ASC' );
					foreach( $chains as $chain ){
						$out[] = [ 'id_chain' => floatval( $chain->id_chain ), 'name' => $chain->name ];
					}
					echo json_encode( $out );exit;

					break;
					default:
						$chain = Chain::o( c::getPagePiece(2) );
						if (!$chain->id_chain) {
							$this->error(404, true);
						}
						echo $chain->json();exit();
				}

				break;

			case 'post':
				if (!c::admin()->permission()->check(['global','chain-all', 'chain-crud'])) {
					$this->error(401, true);
				}

				switch ( c::getPagePiece(3) ) {
					default:
						$id_chain = $this->request()[ 'id_chain' ];

						if( $id_chain ){
							$chain = Chain::o( $id_chain );
						} else {
							$chain = new Chain;
						}
						$chain->name = $this->request()[ 'name' ];
						$chain->active = $this->request()[ 'active' ];
						$chain->save();

						if( $chain->id_chain ){
							echo $chain->json();
						} else {
							$this->_error( 'error' );
						}
						break;
				}
				break;
		}
	}

	private function _error( $error = 'invalid request' ){
		echo json_encode( [ 'error' => $error ] );
		exit();
	}
}