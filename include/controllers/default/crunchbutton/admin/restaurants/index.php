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
		$view_name = c::getPagePiece(2) == 'legacy' ?
				'admin/restaurants/legacy/restaurant'		:
				'admin/restaurants/restaurant'					;
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
		$view->display('admin/restaurants/index');
	}

	public function init() {
		c::view()->layout('layout/admin');
		c::view()->page = 'admin/restaurants';

		if(c::getPagePiece(2) == 'legacy') {
			c::view()->page = 'admin/restaurants';
			$page_piece_index = 3;
		}
		else {
			$page_piece_index = 2;
		}

		$restaurant = Restaurant::o(c::getPagePiece($page_piece_index));

		/* @var $restaurant Crunchbutton_Restaurant */
		$this->restaurant = $restaurant;
		c::view()->restaurant = $restaurant;

		if (c::getPagePiece($page_piece_index) == 'new') {
			$restaurant->save();
			$this->_form();
		} elseif ($restaurant->id_restaurant) {
			switch (c::getPagePiece($page_piece_index+1)) {
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
