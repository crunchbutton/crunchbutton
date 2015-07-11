<?php

class Crunchbutton_Order_Logistics_DestinationList extends Cana_Model {

    public $parking_clusters;
    public $id_map;

	public function __construct() {
		$this->_destinations = [];
        $this->id_counter = 1;
        $this->parking_clusters = [];
        $this->id_map = [];
	}
	
	public function add($destination) {
		if (!$destination) {
			return false;
		}

		$this->_destinations[] = $destination;

        $this->id_map[$this->id_counter] = $destination;
        $destination->id_unique = $this->id_counter;
        if (!is_null($destination->cluster) && ($destination->type==Crunchbutton_Order_Logistics_Destination::TYPE_RESTAURANT)) {
            $this->parking_clusters[$destination->cluster][] = $destination;
        }
        $this->id_counter += 1;
		return true;
	}

	
	public function destinations() {
		return $this->_destinations;
	}

    public function count() {
        return count($this->_destinations);
    }
}