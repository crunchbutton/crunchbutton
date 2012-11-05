<?php

class Controller_admin_orders_addcommunity extends Crunchbutton_Controller_Account {
	public function init() {
		Order::q('select * from `order` where id_community is null')->each(function() {
			$this->id_community = $this->restaurant()->community()->id_community;
			$this->save();
		});
	}
}
