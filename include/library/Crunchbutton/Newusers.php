<?php
/**
 * Dish categories to group the dishes in a restaurant.
 *
 * @package  Crunchbutton.Newusers
 * @category model
 *
 * @property int    	id_newusers
 * @property string 	email
 * @property datetime  last_update
 */
class Crunchbutton_Newusers extends Cana_Table {
	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('newusers')
			->idVar('id_newusers')
			->load($id);
	}

	public static function getConfig(){
		return Crunchbutton_Newusers::o(1);
	}

	public static function queSendEmailCLI(){

		$config = static::getConfig();
		$orders = static::getNewOnes();	

		foreach( $orders as $order ){
			$user = $order->user();
			$email = '_EMAIL';
			$subject = $user->name . ' placed their first CB order';
			$mail = new Crunchbutton_Email_Newusers([
				'subject' => $subject,
				'email' => $email,
				'order' => $order,
				'user' => $user
			]);
			$mail->send();
		}
		static::updateConfig();
	}

	public static function queSendEmail(){

		$config = static::getConfig();
		$orders = static::getNewOnes();	

		foreach( $orders as $order ){
			$user = $order->user();
			$email = $config->email_to;
			$subject = $user->name . ' placed their first CB order';
			$mail = new Crunchbutton_Email_Newusers([
				'subject' => $subject,
				'email' => $email,
				'order' => $order,
				'user' => $user
			]);
			$mail->send();
		}
		static::updateConfig();
		echo '<script>alert("Sent ' . $orders->count() . ' emails!");location.href="/orders/newusers/";</script>';
	}

	public static function updateConfig(){
		$config = Crunchbutton_Newusers::o(1);
		$config->last_update = date('Y-m-d H:i:s');
		$config->save();
	}

	public static function getNewOnes(){

		$query = 'SELECT *
							FROM `order` o
							WHERE id_order IN
									(SELECT id_order
									 FROM
										 (SELECT count(*) AS orders, id_order, phone, date
											FROM `order` o
											GROUP BY phone HAVING orders = 1) orders
									 WHERE date >
											 (SELECT last_update
												FROM newusers
												WHERE id_newusers = 1)) ORDER BY id_order DESC';
/*
		$config = static::getConfig();
		$query = 'SELECT * FROM `order` WHERE id_order IN( 
								SELECT id_order FROM (
									SELECT 
										COUNT(*) orders, 
										u.phone,
										u.id_user,
										o.id_order
									FROM `order` o 
									INNER JOIN user u ON u.id_user = o.id_user
									WHERE o.date > "' . $config->last_update . '"
									GROUP BY u.phone HAVING orders = 1 ) orders ) ORDER BY id_order DESC';
echo 	$query;exit;
*/
		return Crunchbutton_Order::q($query);
	}

	public function date() {
		if (!isset($this->_last_update)) {
			$this->_last_update = new DateTime($this->last_update, new DateTimeZone(c::config()->timezone));
			$this->_last_update->setTimezone(new DateTimeZone( c::config()->timezone ));
		}
		return $this->_last_update;
	}
}