<?php

/**
 *
 * @property $restaurant Crunchbutton_Restaurant
 */
class Controller_admin_restaurants extends Crunchbutton_Controller_Account
{

	/**
	 * Default method to show a restaurant form
	 *
	 * @return void
	 */
	private function _restaurantForm()
	{
		$view = Cana::view();
		/* @var $view Cana_View */

		$communities = Community::q('select * from community');
		$community   = $this->restaurant->community()->items()[0];

		$view->communities = $communities;
		$view->community   = $community;
		$view->display('admin/restaurants/restaurant');
	}


	public function init() {
		c::view()->layout('layout/admin');
		c::view()->page = 'admin/restaurants';

		$restaurant       = Restaurant::o(c::getPagePiece(2));
		$this->restaurant = $restaurant;

		if (c::getPagePiece(2) == 'new') {
			c::view()->display('admin/restaurants/restaurant');

		} elseif ($restaurant->id_restaurant) {
			c::view()->restaurant = $restaurant;
			switch (c::getPagePiece(3)) {
				case 'pay':
					c::view()->display('admin/restaurants/pay');
					break;
				case 'image':
					if ($_FILES['image']) {
						$ext = explode('.',$_FILES['image']['name']);
						$file = '/home/i.crunchbutton/www/image/'.$restaurant->permalink.'.'.$ext[1];

						if (copy($_FILES['image']['tmp_name'],$file)) {
							$restaurant->image = $restaurant->permalink.'.'.$ext[1];
							$restaurant->save();
							chmod($file,0777);
						}

					}
					c::view()->display('admin/restaurants/image');
					break;
				case 'fax':
					foreach ($restaurant->notifications() as $notification) {
						if ($notification->type == 'fax') {
							c::view()->notification = $notification->value;
						}
					}

					c::view()->display('admin/restaurants/fax');
					break;
				default:
					$this->_restaurantForm();
					break;
			}

		} else {
			c::view()->display('admin/restaurants/index');
		}


	}
}