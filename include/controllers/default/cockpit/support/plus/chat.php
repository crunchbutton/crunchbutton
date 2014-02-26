<?php

class Controller_Support_Plus_Chat extends Crunchbutton_Controller_Account {

	public function init() {

		if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'support-crud' ])) {
			return ;
		} 

		$support = Support::o( c::getPagePiece( 3 ) );
		$action = c::getPagePiece( 4 );
		switch ( $action ) {
			case 'history':
				c::view()->support = $support;
				c::view()->layout('layout/ajax');
				c::view()->display('support/plus/chat-history');		
				break;
			default:
				c::view()->support = $support;
				c::view()->layout('layout/ajax');
				c::view()->display('support/plus/chat');
				break;
		}
	}
}
