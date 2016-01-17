<?php

class Controller_api_staff_verification extends Crunchbutton_Controller_RestAccount {
	public function init() {
		if (!c::admin()->permission()->check(['global', 'permission-all', 'permission-users'])) {
			$this->error(401, true);
		}
		
		$admins = Admin::q('
			select admin.*
			from admin
			left join admin_payment_type using(id_admin)
			where
				admin.active=true
				and admin_payment_type.stripe_id is not null
				and admin_payment_type.verified=false
		');
		
		foreach ($admins as $admin) {
			$admin->autoStripeVerify();
		}
	}
}