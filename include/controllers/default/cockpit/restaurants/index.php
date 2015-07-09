 <?php

/**
 *
 * @property $restaurant Crunchbutton_Restaurant
 */
class Controller_restaurants extends Crunchbutton_Controller_Account {

	/**
	 * Default method to show a restaurant form
	 *
	 * You can't use the $view->community var to set the current restaurant community
	 * as that var is used for something else.
	 *
	 * @return void
	 */
	private function _form()
	{

		$view = Cana::view();
		/* @var $view Cana_View */

		$communities = Community::q('SELECT * FROM community');
		if (count($this->restaurant->community()->items())) {
			$community = $this->restaurant->community()->items()[0];
		} else {
			$community = new Crunchbutton_Community();
			$community = $community->getTest();
		}

		$view->communities         = $communities;
		$view->restaurantCommunity = $community;
		$view_name = 'restaurants/restaurant';
		$view->display($view_name);
	}

	/**
	 * Shows all the restaurants
	 *
	 * @return void
	 */
	private function _list()
	{
		$view = Cana::view();
		/* @var $view Cana_View */

		$communities       = Crunchbutton_Community::q('SELECT * FROM community');
		$view->communities = $communities;
		$view->display('restaurants/index');
	}

	public function init() {

		c::view()->page = 'restaurants';

		if(c::getPagePiece(1) == 'analytics') {
			echo 1;
		} else if(c::getPagePiece(1) == 'hours_override') {
			c::view()->overrides = Crunchbutton_Restaurant_Hour_Override::getNexts();
			c::view()->display('restaurants/hours_override');
			exit;
		}
		else {
			$page_piece_index = 1;
		}

		$restaurant = Restaurant::o(c::getPagePiece($page_piece_index));

		if( $restaurant->id_restaurant != '' && $restaurant->id_restaurant ){
			if( !c::admin()->permission()->check( [ 'global', 'restaurants-all', 'restaurants-crud', "restaurant-{$restaurant->id_restaurant}-edit", "restaurant-{$restaurant->id_restaurant}-all" ] ) ){
				return;
			}
		}

		/* @var $restaurant Crunchbutton_Restaurant */
		$this->restaurant = $restaurant;
		c::view()->restaurant = $restaurant;

		if (c::getPagePiece($page_piece_index) == 'new') {

			// @permission check for restaurant permissions
			if (!c::admin()->permission()->check(['global','restaurants-all', 'restaurants-crud', 'restaurants-create'])) {
				return;
			}
			$restaurant->cash = 1;
			$restaurant->credit = 1;
			$restaurant->giftcard = 1;
			$restaurant->delivery = 1;
			$restaurant->takeout = 1;
			$restaurant->confirmation = 1;
			$restaurant->charge_credit_fee = 1;
			$restaurant->max_pay_promotion = 2;
			$restaurant->pay_apology_credits = 1;
			$restaurant->promotion_maximum = 2;
			$restaurant->pay_promotions = 1;
			$restaurant->max_apology_credit = 5;
			$restaurant->fee_customer = '0';
			$restaurant->formal_relationship = 1;
			$restaurant->save();

			$payment_type = $restaurant->payment_type();
			$payment_type->charge_credit_fee = 1;
			$payment_type->max_pay_promotion = 2;
			$payment_type->pay_apology_credits = 1;
			$payment_type->formal_relationship = 1;
			$payment_type->pay_promotions = 1;
			$payment_type->promotion_maximum = 2;
			$payment_type->max_apology_credit = 5;
			$payment_type->save();


			// Give the user the permission to edit the created restaurant
			$permission = array(
														"restaurant-{$restaurant->id_restaurant}-edit" => 1,
														"orders-list-restaurant-{$restaurant->id_restaurant}" => 1
													);
			c::admin()->addPermissions( $permission );

			$this->_form();

		} elseif ($restaurant->id_restaurant) {
			switch (c::getPagePiece($page_piece_index+1)) {
				case 'pay':
					// @permission

					if (!c::admin()->permission()->check(['global', 'restaurants-all','restaurant-'.$restaurant->id_restaurant.'-all','restaurant-'.$restaurant->id_restaurant.'-pay'])) {
						return;
					}
					c::view()->payment = $restaurant->payment_type();
					c::view()->display('restaurants/pay');
					break;
				case 'hour_override':
				$ss = new Crunchbutton_Restaurant_Hour_Override();
					// @permission
					if( !c::admin()->permission()->check( [ 'global', 'restaurants-all', 'restaurants-crud', "restaurant-{$restaurant->id_restaurant}-edit", "restaurant-{$restaurant->id_restaurant}-all" ] ) ){
						return;
					}
					c::view()->hours = Crunchbutton_Restaurant_Hour_Override::q( 'SELECT * FROM restaurant_hour_override WHERE id_restaurant = ' . $restaurant->id_restaurant . ' ORDER BY id_restaurant_hour_override DESC' );
					c::view()->restaurant = $restaurant;
					c::view()->layout('layout/ajax');
					c::view()->display('restaurants/hour_override');
					break;
				

				case 's3':
					if (!c::admin()->permission()->check(['global'])) {
						exit;
					}
					$r = $restaurant->updateImage();
					var_dump($r);
					break;
				
				case 's3all':
					if (!c::admin()->permission()->check(['global'])) {
						exit;
					}
					$restaurants = Crunchbutton_Restaurant::q('
						select * from restaurant where `image` is not null and permalink is not null
					');

					foreach ($restaurants as $restaurant) {
						//if ($resource->file != $resource->s3base()) {
							echo 'uploading '.$restaurant->name."\n";
							$s = $restaurant->updateImage();
							var_dump($s);
							echo "\n\n";
						//}
					}
					break;
				

				case 'image':
					// @permission
					if (!c::admin()->permission()->check(['global','restaurants-all', 'restaurants-crud', 'restaurant-'.$restaurant->id_restaurant.'-all', 'restaurant-'.$restaurant->id_restaurant.'-edit','restaurant-'.$restaurant->id_restaurant.'-image'])) {
						return;
					}
					if ($_FILES['image']) {

						$images_path = '/home/i.crunchbutton/www/image/';

						$current_image = $images_path . $restaurant->image;

						$ext = pathinfo( $_FILES['image']['name'], PATHINFO_EXTENSION );
						$image_name = $restaurant->id . '.'. $ext;
						$file = $images_path . $image_name;


						$remove_old_image = false;
						// check if the old image is being used by any other restaurant - if it is not, delete it
						if( !$restaurant->isImageUsedByOtherRestaurant() ){
							$remove_old_image = true;
						}

						if( $image_name == $restaurant->image ){
							$remove_old_image = true;
						}

						if( $remove_old_image ){
							if ( file_exists( $current_image ) ){
								unlink( $current_image );
							}
						}

						if ( copy( $_FILES['image']['tmp_name'],$file ) ) {
							$restaurant->image = $image_name;
							$restaurant->save();
							$restaurant->updateImage();
							chmod($file,0777);
						}

					}
					c::view()->display('restaurants/image');
					break;
				case 'duplicate':
					// @permission
					if (!c::admin()->permission()->check(['global', 'restaurants-all','restaurant-'.$restaurant->id_restaurant.'-all'])) {
						return;
					}
					$id_restaurant = $restaurant->duplicate();

					// Give the user the permission to edit the created restaurant
					$permission = array( "restaurant-{$id_restaurant}-edit" => 1, "orders-list-restaurant-{$id_restaurant}" => 1 );
					c::admin()->addPermissions( $permission );

					header( "Location: /restaurants/{$id_restaurant}" );
					exit;
				case 'fax':
					// @permission
					if (!c::admin()->permission()->check(['global', 'restaurants-all','restaurant-'.$restaurant->id_restaurant.'-all','restaurant-'.$restaurant->id_restaurant.'-fax'])) {
						return;
					}
					c::view()->notification = $restaurant->fax();
					c::view()->display('restaurants/fax');
					break;
				default:
					// @permission
					if (!c::admin()->permission()->check(['global','restaurants-all','restaurants-crud','restaurant-'.$restaurant->id_restaurant.'-all','restaurant-'.$restaurant->id_restaurant.'-edit'])) {
						return;
					}
					$this->_form();
					break;
			}

		} else {
			$this->_list();
		}


	}
}
