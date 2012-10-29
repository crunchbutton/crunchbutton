<?php

class Controller_admin_restaurants_loadfromdev extends Crunchbutton_Controller_Account {
	public function init() {
		c::view()->layout('layout/admin');
		c::view()->page = 'admin/restaurants';
		$restaurant = Restaurant::o($_REQUEST['id_restaurant']);
		if (!$restaurant->id_restaurant) {
			exit;
		}
		
		$cfg = new Cana_Config(c::config()->dirs->config.'config.xml');
		
		$connect = $cfg->db->beta;
		if ($connect->encrypted) {
			$connect->user = c::crypt()->decrypt($connect->user);
			$connect->pass = c::crypt()->decrypt($connect->pass);
		}
		$dev = new Cana_Db($connect);
		$connect = $cfg->db->live;
		if ($connect->encrypted) {
			$connect->user = c::crypt()->decrypt($connect->user);
			$connect->pass = c::crypt()->decrypt($connect->pass);
		}
		$live = new Cana_Db($connect);
		
		$rDev = new Restaurant;
		$rDev->db($dev);
		$rDev->load($restaurant->id_restaurant);

		echo 'dev: '.$rDev->dishes()->count().'<br />';
		
		$rLive = new Restaurant;
		$rLive->db($live);
		$rLive->load($restaurant->id_restaurant);
		echo 'live: '.$rLive->dishes()->count().'<br />';
		
		foreach ($rDev->dishes() as $dish) {
			$dish->options();
			$dish = clone $dish;
			$dish->db($live);
			$dish->dbWrite($live);

			try {
				$dish->save($dish->id_dish);
				echo 'Resaved dish: '.$dish->id_dish.'<br>';
			} catch (Exception $e) {
				echo '<br><span style="color: red;">'.$e->getMessage().'. Skipping dish: '.$dish->id_dish.'</span><br>';		
			}
			
			foreach ($dish->options() as $option) {
				$option = clone $option;
				$option->db($live);
				$option->dbWrite($live);
				
				try {
					$option->save($option->id_option);
					echo 'Resaved option: '.$option->id_option.'<br>';
				} catch (Exception $e) {
					echo '<br><span style="color: red;">'.$e->getMessage().'. Skipping option: '.$option->id_option.'</span><br>';		
				}
			
			}
		}


	}
}