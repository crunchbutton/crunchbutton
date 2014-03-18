<?php

class Controller_Support_Chat extends Crunchbutton_Controller_Account {

	public function init() {

		if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'support-crud' ])) {
			return ;
		} 

		$support = Support::o( c::getPagePiece( 2 ) );
		$action = c::getPagePiece( 3 );
		switch ( $action ) {
			case 'history':
				c::view()->support = $support;
				c::view()->layout('layout/ajax');
				c::view()->display('support/chat-history');		
				break;
			default:
				c::view()->support = $support;
				c::view()->layout('layout/ajax');
				c::view()->display('support/chat');
				break;
		}
	}
}
