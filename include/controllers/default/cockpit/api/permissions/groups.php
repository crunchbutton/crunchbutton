<?php

class Controller_api_Permissions_Groups extends Crunchbutton_Controller_RestAccount {
	public function init() {
		if (!c::admin()->permission()->check(['global','permissions-all', 'permission-groups'])) {
			return ;
		}
		switch ( $this->method() ) {
			case 'post':
				$id_group = c::getPagePiece( 3 );

				$action = c::getPagePiece( 4 );

				switch ( $action ) {

					case 'permissions':
						$group = Crunchbutton_Group::o( $id_group );
						$group->removePermissions();
						$group->addPermissions( $_REQUEST[ 'permissions' ] );
						echo json_encode( ['success' => $group->id_group ] );
						break;
					
					default:
						$name = $_REQUEST[ 'name' ];
						if( $id_group ){
							$group = Crunchbutton_Group::o( $id_group );
						} else {
							$group = new Crunchbutton_Group();
						}
						$name = str_replace( ' ' , '-', $name );
						$group->name = $name;
						$group->save();
						echo json_encode( ['success' => $group->id_group ] );
						break;
				}
			break;
			default:
				echo json_encode( [ 'error' => 'invalid object' ] );
			break;
		}
	}	
}