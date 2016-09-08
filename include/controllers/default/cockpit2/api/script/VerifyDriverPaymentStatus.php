<?php
class Controller_Api_Script_VerifyDriverPaymentStatus extends Crunchbutton_Controller_RestAccount {

	public function init() {
		$start = 300;
		$query = 'SELECT DISTINCT(a.id_admin), a.* FROM admin a
INNER JOIN admin_payment_type apt ON apt.id_admin = a.id_admin
INNER JOIN payment p ON p.id_driver = a.id_admin
AND apt.stripe_id IS NOT NULL
AND a.active = 1 ORDER BY a.name LIMIT '.$start.', 100';

		$admins = Admin::q($query);
		foreach ($admins as $admin) {
			$status = $admin->stripeVerificationStatus();
			if($status['status'] != 'verified'){
				$id = $status['id'];
				$disabled_reason = $status['disabled_reason'];
				$fields = $status['fields'];
				$details_code = $status['details_code'];
				echo "$admin->id_admin;$admin->name;$id;$disabled_reason;$details_code;" . join($fields,', ');
				echo "\n";
			}
		}


	}
}