<?php

class Controller_support extends Crunchbutton_Controller_Account {
	public function init() {

		$support = Support::o(c::getPagePiece(1));

		// handle any actions

		if(isset($_POST['action'])) {
			if($_POST['action'] == 'conversation') {
				self::respond_to_client($support, $_POST);
			}
			if($_POST['action'] == 'update') {
				self::update_support($support, $_POST);
			}
			if($_POST['action'] == 'new') {
				self::new_support($support, $_POST);
			}
			if($_POST['action'] == 'link') {
				self::link_or_unlink($support, $_POST);
			}
		}

		// set any vars

		// render in rep's local timezone
		// for now, reps don't have timezones
		c::view()->rep_timezone = timezone_open('America/New_York');

		c::view()->layout('layout/feather');
		c::view()->page = 'support';

		// route to view

		if( $support->id_support ){
			// show the support's form
			c::view()->support = $support;
			c::view()->title = '#SUP' . $support->id_support;
			c::view()->display('m/support/support');	
		} else {
			// show the supports list
			c::view()->supports = Support::q('select * from support order by id_support desc limit 5');
			c::view()->title = 'Support';
			c::view()->display('m/support/index');
		}
	}

	// ---------------------------------------

	public static function respond_to_client($support, $args=[]) {
		if($args['text'] == '') return;
		$sn = $support->addNote($args['text'], 'rep', 'external');
		$sn->notify();
	}

	public static function update_support($support, $args=[]) {
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

	public static function new_support(&$support, $args=[]) {
		$support = new Support();
		$rep = Support_Rep::q(
				'SELECT * FROM `support_rep` '.
				'WHERE `name` like \'' . $_SESSION['username'] . '\' '.
				'LIMIT 1');
		$support->id_support_rep = $rep->id_support_rep;
		$support->status = 'open';
		$support->message = 'Ticket created from admin panel.';
		if($args['id_order']) {
			$order = Order::o($args['id_order']);
			$support->id_order = $order->id;
			$support->id_restaurant = $order->id_restaurant;
			$support->id_user = $order->id_user;
			$support->name = User::o($order->id_user)->name;
		}
		$support->save();
	}

	public static function link_or_unlink(&$support, $args=[]) {
		if($args['link_type'] == 'Unlink Restaurant') {
			$support->systemNote("Unlinked restaurant #RST$support->id_restaurant.");
			$support->id_restaurant = null;
			$support->save();
		}
		if($args['link_type'] == 'Link Restaurant') {
			if(is_numeric($args['id_restaurant'])) {
				$restaurant = Restaurant::o($args['id_restaurant']);
			}
			else { // permalink
				$restaurant = Restaurant::permalink($args['id_restaurant']);
			}
			$support->id_restaurant = $restaurant->id_restaurant;
			$support->save();
			$support->systemNote("Linked restaurant #RST$support->id_restaurant.");
		}
		if($args['link_type'] == 'Unlink User') {
			$support->systemNote("Unlinked user #USER$support->id_user.");
			$support->id_user = null;
			$support->save();
		}
		if($args['link_type'] == 'Link User') {
			$id = preg_replace('/[^\dx]/i', '', $args['id_user']);
			if(is_numeric($id) && $id < 100000000) {
				$user = User::o($id);
			}
			else { // phone number
				$user = User::byPhone($id);
			}
			$support->id_user = $user->id_user;
			$support->save();
			$support->systemNote("Linked user #USER$support->id_user.");
		}
		if($args['link_type'] == 'Unlink Order') {
			$support->systemNote("Unlinked order #ORD$support->id_order.");
			$support->id_order = null;
			$support->save();
		}
		if($args['link_type'] == 'Link Order') {
			$order = Order::o($args['id_order']);
			if(!$order->id_order) return;
			$support->id_order = $order->id;
			$support->id_restaurant = $order->id_restaurant;
			$support->id_user = $order->id_user;
			$support->name = User::o($order->id_user)->name;
			$support->save();
			$support->systemNote("Linked order #ORD$support->id_order.");
		}
	}


}
