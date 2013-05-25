<?php

class Controller_support extends Crunchbutton_Controller_Account {
	public function init() {
	
		$action = c::getPagePiece(1);

		switch ($action) {
			case 'new':
				self::create($support, $_REQUEST);
				header('Location: /support/'.$support->id_support);
				exit;
				break;

			default:
				$support = Support::o(c::getPagePiece(1));
				$action = c::getPagePiece(2);

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
		
					case 'conversation' :
						self::setRep($support);
						$sn = self::respond($support, $_POST);
						c::view()->display('support/conversation.note', ['set' => ['note' => $sn]]);
						exit;
						break;

					case 'update':
						self::setRep($support);
						self::update($support, $_POST);
						break;

					case 'actions':
						self::setRep($support);
						self::action($support, $_POST);
						break;
				}

				break;
			
		}

		c::view()->page = 'support';

		if ($support->id_support) {
			// show the support's form
			c::view()->support = $support;
			c::view()->display('support/support');	
		} else {
			// show the supports list
			c::view()->recent = Support::q('select * from support order by id_support desc limit 50');
			c::view()->total = Support::q('select count(*) as count from support where status="open"')->count;
			c::view()->display('support/index');
		}
	}

	// ---------------------------------------

	public static function respond($support, $args=[]) {
		if ($args['text'] == '') return;
		$sn = $support->addNote($args['text'], 'rep', 'external');
		$sn->notify();
		return $sn;
	}

	public static function update($support, $args=[]) {
		if($args['new_note'] != '') {
			$support->addNote($args['new_note'], 'rep', 'internal');
		}
		$support->description_client = $args['description_client'];
		$support->description_cb     = $args['description_cb'    ];
		$support->fault_of           = $args['fault_of'          ];
		$support->customer_happy     = $args['customer_happy'    ];
		$support->status             = $args['status'            ];
		$support->id_github          = $args['id_github'         ];
		$support->how_to_prevent     = $args['how_to_prevent'    ];
		$support->save();
	}

	public static function create(&$support, $args = []) {
		$support = new Support;
		$support->status = 'open';
		$support->message = 'Ticket created from admin panel.';
		$support->id_support_rep = self::getRep()->id_support_rep;

		if ($args['id_order']) {
			$order = Order::o($args['id_order']);
			$support->id_order = $order->id;
			$support->id_restaurant = $order->id_restaurant;
			$support->id_user = $order->id_user;
			$support->name = User::o($order->id_user)->name;
		}
		$support->save();
	}
	
	public static function getRep() {
		return Support_Rep::getLoggedInRep();
	}

	public static function setRep(&$support) {
		$support->id_support_rep = self::getRep()->id_support_rep;
		$support->save();
	}

	public static function action(&$support, $args=[]) {
		if($args['action_type'] == 'Unlink Restaurant') {
			$support->systemNote("Unlinked restaurant #RST$support->id_restaurant.");
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
			$support->systemNote("Linked restaurant #RST$support->id_restaurant.");
		}
		if($args['action_type'] == 'Unlink User') {
			$support->systemNote("Unlinked user #USER$support->id_user.");
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
			$support->systemNote("Linked user #USER$support->id_user.");
		}
		if($args['action_type'] == 'Unlink Order') {
			$support->systemNote("Unlinked order #ORD$support->id_order.");
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
			$support->systemNote("Linked order #ORD$support->id_order.");
		}
		if($args['action_type'] == 'Refund Order') {
			$order = $support->order();
			if(!$order->id_order) return;
			$order->refund();
		}
	}


}
