<?php

class Cockpit_Admin_Location extends Cana_Table {

	const TIME_LOCATION_VALID = 600; // seconds

	public function exports() {
		$exports = [
			'lat' => $this->lat,
			'lon' => $this->lon,
			'accuracy' => $this->accuracy,
			'date' => $this->date
		];
		return $exports;
	}

	public function valid($seconds = self::TIME_LOCATION_VALID) {
		$date = new DateTime($this->date);
		$now = new DateTime();
		$interval = $date->diff($now);
		$interval->format('%s');

		return $interval > $seconds ? false : true;
	}

	public function distance($to, $from = null) {
		if (!$from) {
			$from = $this;
		}

		$driver = $from->lat.','.$from->lon;
		if (!is_array($to)) {
			$to = [$to];
		}
		$toEncoded = urlencode(array_shift($to));

		// google maps api service
		$url = 'https://maps.googleapis.com/maps/api/directions/json?';

		// to and from
		$url .= 'origin='.$driver.'&destination='.$toEncoded;

		// get bike or car directions
		$url .= '&mode='. ($driver->vehicle() == 'car' ? 'driving ' : 'bicycling ');

		// add waypoints
		if (count($to)) {
			$url .= '&waypoints='.implode(',',$to);
		}

		//$url = 'https://maps.googleapis.com/maps/api/directions/json?origin=33.9848,-118.446&destination=1120%20princeton,%20marina%20del%20rey%20ca%2090292&waypoints=33.1751,-96.6778';

		$res = @json_decode(@file_get_contents($url));
		$eta = 0;
		$distance = 0;

		if ($res && $res->routes[0] && $res->routes[0]->legs) {
			foreach ($res->routes[0]->legs as $leg) {
				$eta += $leg->duration->value/60;
				$distance += $leg->distance->value * 0.000621371;
			}
		}

		return (object)[
			'time' => $eta,
			'distance' => $distance
		];
	}

	public function admin() {
		return Admin::o($this->id_admin);
	}

	public function preSave(){
		$location = self::q('SELECT * FROM admin_location WHERE id_admin = ? ORDER BY id_admin_location DESC LIMIT 1',[$this->id_admin])->get(0);
		if($location->id_admin_location){
			$location->lat = $this->lat;
			$location->lon = $this->lon;
			$location->accuracy = $this->accuracy;
			$location->date = $this->date;
			$location->save();
		} else {
			$this->save();
		}
	}

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('admin_location')
			->idVar('id_admin_location')
			->load($id);
	}
}
