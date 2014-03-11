<?php

class Controller_tests_hours_dst extends Crunchbutton_Controller_Account {
	public function init() {

		$this->restaurant = Restaurant::o($_REQUEST['id_restaurant'] ? $_REQUEST['id_restaurant'] : 120);
		$this->utc = true;

		// the date that DST is
		$this->dstDate = new DateTime( '2014-03-09 02:00:00', new DateTimeZone(($this->utc ? 'UTC' : $this->restaurant->timezone)));

		echo '<pre>';

		$this->checkDay('-2 days');		
		$this->checkDay('-1 day');
		$this->checkDay();
		$this->checkDay('+1 day');
		$this->checkDay('+2 days');

		exit;
	}
	
	public function checkDay($dif = null) {
		$d = clone $this->dstDate;
		if ($dif) {
			$d = $d->modify($dif);
		}
		$date = new DateTime($d->format('Y-m-d').' 12:00:00', new DateTimeZone(($this->utc ? 'UTC' : $this->restaurant->timezone)));
		$res1 = Hour::getByRestaurantNext24Hours($this->restaurant, $this->utc, $date);

		
		$date->modify('+7 days');

		$date = new DateTime($date->format('Y-m-d').' 12:00:00', new DateTimeZone(($this->utc ? 'UTC' : $this->restaurant->timezone)));
		$res2 = Hour::getByRestaurantNext24Hours($this->restaurant, $this->utc, $date);

		
		if (!$this->resMatch($res1, $res2)) {
			echo '<b>Datetime discrepency!</b><br>';
			print_r($res1);
			print_r($res2);
			echo '<br><br><br>';

		}
	}
	
	public function resMatch($res1, $res2) {
		if (count($res1) != count($res2)) {
			return false;
		}
		
		foreach ($res1 as $k => $v) {
			$keys = get_object_vars($v);

			if ($this->cleanTime($res1[$k]->from) != $this->cleanTime($res2[$k]->from)) {
				return false;
			}
			
			if ($this->cleanTime($res1[$k]->to) != $this->cleanTime($res2[$k]->to)) {
				return false;
			}

		}
		return true;
	}
	
	public function cleanTime($time) {
		return preg_replace('/^\d{4}-\d{2}-\d{2} (.*)$/','\\1',$time);
	}
}