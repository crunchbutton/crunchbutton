<?php

class Controller_Support extends Crunchbutton_Controller_Account {

	public function init() {

		if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'support-crud' ])) {
			return ;
		}

		$action = c::getPagePiece(1);



		switch ($action) {

			case 'new-chat':

				$id_admin = c::getPagePiece(2);
				c::view()->id_admin = $id_admin;
				c::view()->admins = Crunchbutton_Admin::q( 'SELECT DISTINCT(a.id_admin) AS id, a.*, c.name AS community
																										  FROM admin a
																										INNER JOIN admin_group ag ON ag.id_admin = a.id_admin
																										INNER JOIN `group` g ON g.id_group = ag.id_group
																										INNER JOIN community c ON c.id_driver_group = g.id_group
																										WHERE a.phone != "" AND c.active = true ORDER BY a.name' );
				c::view()->layout('layout/ajax');
				c::view()->display('support/new-chat');
				break;

			case 'make-call':
				$id_admin = c::getPagePiece(2);
				c::view()->id_admin = $id_admin;
				c::view()->admins = Crunchbutton_Admin::q( 'SELECT DISTINCT(a.id_admin) AS id, a.*, c.name AS community
																										  FROM admin a
																										INNER JOIN admin_group ag ON ag.id_admin = a.id_admin
																										INNER JOIN `group` g ON g.id_group = ag.id_group
																										INNER JOIN community c ON c.id_driver_group = g.id_group
																										WHERE a.phone != "" AND c.active = true ORDER BY a.name' );
				c::view()->layout('layout/ajax');
				c::view()->display('support/make-call');
				break;

			case 'new':

				if ( !Crunchbutton_Support::adminHasCreatePermission() ) {
					return ;
				}

				self::create($support, $_REQUEST);
				header('Location: /support/'.$support->id_support);
				exit;
				break;

			default:

				$support = Support::o( c::getPagePiece(1) );
				$action = c::getPagePiece(2);

				// link rep #1723
				if( $_REQUEST[ 'r' ] && $support->id_support ){
					$support->id_admin = c::admin()->id_admin;
					$support->save();
				}

				switch ($action) {

					case 'checkconvo' :
						$notes = $support->notes('external', $_REQUEST['date']);
						if ($notes) {
							foreach ($notes as $note) {
								c::view()->display('support/conversation.note', ['set' => ['note' => $note]]);
							}
						}
						exit;
						break;

					case 'history':
						c::view()->layout('layout/ajax');
						c::view()->support = $support;
						c::view()->display( 'support/history' );
						exit;
						break;

					case 'remove-rep' :
						if ( $support->permissionToEdit() ) {
							$support->id_admin = null;
							$support->save();
							$support->addSystemMessage( c::admin()->name . ' unlinked rep' );
							echo 'ok';
							exit;
						}
						break;

					case 'remove-order' :
						if ( $support->permissionToEdit() ) {
							$support->id_order = null;
							$support->save();
							$support->addSystemMessage( c::admin()->name . ' unlinked order' );
							echo 'ok';
							exit;
						}
						break;

					case 'link-rep':
						if ( $support->permissionToEdit() ) {

							$admin = Admin::o( $this->request()[ 'id_admin' ] );
							if( $admin->id_admin ){
								$support->id_admin = $admin->id_admin;
								$support->save();
								$support->addSystemMessage( c::admin()->name . ' linked a new rep ' . $admin->name );
								echo 'ok';
							} else {
								echo 'error';
							}
							exit;
						}
						break;

					case 'link-order':
						if ( $support->permissionToEdit() ) {
							$order = Order::o( $this->request()[ 'id_order' ] );
							if( $order->id_order ){
								$support->id_order = $order->id_order;
								$support->save();
								$support->addSystemMessage( c::admin()->name . ' linked a new order ' . $order->id_order );
								echo 'ok';
							} else {
								echo 'error';
							}
							exit;
						}
						break;
					case 'close-ticket':
						if ( $support->permissionToEdit() ) {
							$support->status = Crunchbutton_Support::STATUS_CLOSED;
							$support->save();
							$support->emit ;
							$support->addSystemMessage( c::admin()->name . ' closed this ticket.', false, true );
							exit;
						}
						break;
						break;
					case 'conversation':
						if ( $support->permissionToEdit() ) {
							if( $this->request()['text'] ){
								$support->addAdminReply( $this->request()['text'] );
								if( ( $support->type == Crunchbutton_Support::TYPE_SMS || $support->type == Crunchbutton_Support::TYPE_BOX_NEED_HELP ) && $support->id_session_twilio ){
									$message = c::admin()->firstName() . ' replied @' . $support->id_session_twilio . ' : ' . $this->request()['text'];
									Crunchbutton_Support::tellCustomerService( $message );
								}
							}
							exit;
						}
						break;

					case 'note' :
						if ($support->permissionToEdit()) {
							$support->addNote( $this->request()['text'] );
							exit;
						}
						break;

					case 'update':
						if ($support->permissionToEdit()) {
							self::update( $support, $this->request() );
							echo $support->json();
							exit;
						}
						break;

					case 'actions':
						if ($support->permissionToEdit()) {
							self::setRep($support);
							self::action($support, $this->request());
						}
						break;

					default:

						c::view()->page = 'support';

						if ( $support->id_support ) {

							if( !$support->permissionToEdit() ){
								return ;
							}
							// show the support's form
							c::view()->support = $support;
							c::view()->display('support/support');
						} else {
							c::view()->display('support/index');
						}

						break;
				}

				break;

		}
	}

	// --------------------------------------

	public static function update( $support, $args = [] ) {

		if($args['new_note'] != '') {
			$support->addNote($args['new_note'], 'rep', 'internal');
		}

		$changes = array();

		if( $support->status != $args['status'] ){
			$changes[] = 'Status changed to: ' . $args['status'];
		}


		$changes = join( "\n", $changes );
		if( trim( $changes ) != '' ){
			$support->addSystemMessage( $changes );
		}

		$support->status             			= $args['status'            		];
		$support->save();
	}

	public static function create(&$support, $args = []) {
		$support = Crunchbutton_Support::createNewTicket(  [ 'id_order' => $args['id_order'], 'body' => 'Ticket created from admin panel.' ] );
	}

	public static function setRep(&$support) {
		$support->id_admin = c::admin()->id_admin;
		$support->save();
	}

	public static function action(&$support, $args=[]) {
		if($args['action_type'] == 'Unlink Restaurant') {
			$support->addSystemMessage("Unlinked restaurant #RST$support->id_restaurant.");
			$support->id_restaurant = null;
			$support->save();
		}
		if($args['action_type'] == 'Link Restaurant') {
			if(is_numeric($args['id_restaurant'])) {
				$restaurant = Restaurant::o($args['id_restaurant']);
			}
			else { // permalink
				$restaurant = Restaurant::permalink($args['id_restaurant']);
			}
			if(!$restaurant->id_restaurant) return;
			$support->id_restaurant = $restaurant->id_restaurant;
			$support->save();
			$support->addSystemMessage("Linked restaurant #RST$support->id_restaurant.");
		}
		if($args['action_type'] == 'Unlink User') {
			$support->addSystemMessage("Unlinked user #USER$support->id_user.");
			$support->id_user = null;
			$support->save();
		}
		if($args['action_type'] == 'Link User') {
			$id = preg_replace('/[^\dx]/i', '', $args['id_user']);
			if(is_numeric($id) && $id < 100000000) {
				$user = User::o($id);
			}
			else { // phone number
				$user = User::byPhone($id);
			}
			if(!$user->id_user) return;
			$support->id_user = $user->id_user;
			$support->phone = $user->phone;
			$support->save();
			$support->addSystemMessage("Linked user #USER$support->id_user.");
		}
		if($args['action_type'] == 'Unlink Order') {
			$support->addSystemMessage("Unlinked order #ORD$support->id_order.");
			$support->id_order = null;
			$support->save();
		}
		if($args['action_type'] == 'Link Order') {
			$order = Order::o($args['id_order']);
			if(!$order->id_order) return;
			$support->id_order = $order->id;
			$support->id_restaurant = $order->id_restaurant;
			$support->id_user = $order->id_user;
			$user = User::o($order->id_user);
			$support->name = $user->name;
			$support->phone = $user->phone;
			$support->save();
			$support->addSystemMessage("Linked order #ORD$support->id_order.");
		}
		if($args['action_type'] == 'Refund Order') {
			$order = $support->order();
			if(!$order->id_order) return;
			$order->refund();
		}
	}


}
