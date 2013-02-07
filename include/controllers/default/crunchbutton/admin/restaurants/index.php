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
	private function _form()
	{
		$view = Cana::view();
		/* @var $view Cana_View */

		$communities = Community::q('SELECT * FROM community');
		if (count($this->restaurant->community()->items())) {
			$community   = $this->restaurant->community()->items()[0];
		} else {
			$community  = new Crunchbutton_Community();
			$community  = $community->getTest();
		}

		$view->communities = $communities;
		$view->community   = $community;
		$view->display('admin/restaurants/restaurant');
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
		$view->display('admin/restaurants/index');
	}

	public function init() {
		c::view()->layout('layout/admin');
		c::view()->page = 'admin/restaurants';

		$restaurant       = Restaurant::o(c::getPagePiece(2));
		/* @var $restaurant Crunchbutton_Restaurant */
		$this->restaurant = $restaurant;

		if (c::getPagePiece(2) == 'new') {
			$this->_restaurantForm();
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
					c::view()->notification = $restaurant->fax();
					c::view()->display('admin/restaurants/fax');
					break;
				default:
					$this->_form();
					break;
			}

		} else {
			$this->_list();
		}


	}
}