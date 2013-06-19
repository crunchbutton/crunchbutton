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

	public static function queSendEmail(){

		$config = static::getConfig();
		$orders = static::getNewOnes();	
		
		foreach( $orders as $order ){
			$user = $order->user();
			$email = $config->email_to;
			$subject = $user_name . ' placed their first CB order';
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
		return Crunchbutton_Order::q($query);
	}

	public function date() {
		if (!isset($this->_date)) {
			$this->_date = new DateTime($this->date, new DateTimeZone(c::config()->timezone));
		}
		return $this->_date;
	}
}