<?php

class Crunchbutton_Optimizer_Result extends Cana_Model {

    const RTYPE_NOTHING = 0;
    const RTYPE_BADINPUT = 1;
    const RTYPE_NOSOLUTION = 2;
    const RTYPE_DROPPED_ORDERS = 3;
    const RTYPE_OK = 4;

    public $hasDroppedNodes;
    public $numNodes;
    public $resultType;
    public $nodes;
    public $absFinishedTimes;
    public $relFinishedTimes;
    public $score;
    public $numBadTimes;

	public function __construct($params = []) {
        $this->resultType = self::RTYPE_NOTHING;
        $this->relFinishedTimes = null;
		foreach ($params as $key => $param) {
			$this->{$key} = $param;
		}
        $this->calculateScoreAndNumBadTimes();

	}

    public function calculateScoreAndNumBadTimes() {
        $score = null;
        $numBadTimes = null;
        if ($this->relFinishedTimes && $this->resultType == self::RTYPE_OK) {
            $score = 0;
            $numBadTimes = 0;
            foreach ($this->relFinishedTimes as $ft){
                if ($ft >= 0) {
                    if ($ft <= 60) {
                        $score += 5.5 - ($ft/60.0);
                    }
                    else if ($ft <= 120) {
                        $score += 4.5 - ($ft/60.0);
                    }
                    else {
                        $score += 1.0;
                    }
                    if ($ft >= Crunchbutton_Order_Logistics::LC_CUTOFF_BAD_TIME) {
                        $numBadTimes += 1;
                    }
                }
            }
        }
        $this->score = $score;
        $this->numBadTimes = $numBadTimes;
    }


    public function getRoute($fakeOrderIds) {
        // Need a list of $fakeOrderIds to remove from the route
        return null;
    }

}