<?php

class Crunchbutton_Optimizer_Result extends Cana_Model {

    const RTYPE_NOTHING = 0;
    const RTYPE_BADINPUT = 1;
    const RTYPE_NOSOLUTION = 2;
    const RTYPE_DROPPED_ORDERS = 3;
    const RTYPE_OK = 4;

    const SEQ_FOR_BAD_ROUTE = -99;

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
            $total = 0;
            $numBadTimes = 0;
            $counter = 0;
            foreach ($this->relFinishedTimes as $ft){

                if ($ft >= 0) {
//                    $node = $this->nodes[$counter];
//                    print "$node $ft\n";
//                    $test = Crunchbutton_Order_Logistics::LC_PENALTY_THRESHOLD;
                    if ($ft <= Crunchbutton_Order_Logistics::LC_PENALTY_THRESHOLD) {
                        $total += $ft;
                    }
                    else {
                        $total += $ft + Crunchbutton_Order_Logistics::LC_PENALTY_COEFFICIENT * ($ft  - Crunchbutton_Order_Logistics::LC_PENALTY_THRESHOLD);
                    }
                    if ($ft >= Crunchbutton_Order_Logistics::LC_CUTOFF_BAD_TIME) {
                        $numBadTimes += 1;
                    }
                    ++$counter;
                }
            }
            if ($counter > 0) {
                $score = $total;
            }
        }
        $this->score = $score;
        $this->numBadTimes = $numBadTimes;
    }

    public function saveRouteToDb($status, $id_order, $id_admin, $curTime, $input, $nodeOrderIds=null, $fakeIndicators=null) {
        if ($status == Crunchbutton_Optimizer_Result::RTYPE_OK) {
            if ($this->numNodes == $input->numNodes) {
                for ($i = 0; $i < $this->numNodes; $i++) {
                    $inputNodeIndex = $this->nodes[$i];
                    $leavingTime = clone $curTime;
                    $leavingTime->modify('+ ' . $this->absFinishedTimes[$i] . ' minutes');
                    $leavingTime = $leavingTime->format('Y-m-d H:i:s');
                    $olr = Crunchbutton_Order_Logistics_Route::defaultOrderLogisticsRoute($id_order,
                        $nodeOrderIds[$inputNodeIndex],
                        $id_admin, $i,
                        $input->nodeTypes[$inputNodeIndex], $leavingTime,
                        $input->firstCoords[$inputNodeIndex], $input->secondCoords[$inputNodeIndex],
                        $fakeIndicators[$inputNodeIndex]);
                    $olr->save();
                }
            }
        } else{
            if ($input->numNodes > 0) {
                $leaving_time = $curTime->format('Y-m-d H:i:s');
                $olr = Crunchbutton_Order_Logistics_Route::defaultOrderLogisticsRoute($id_order, null, $id_admin,
                    self::SEQ_FOR_BAD_ROUTE, $status, $leaving_time,
                    $input->firstCoords[0], $input->secondCoords[0], false);
                $olr->save();
            }
        }
        return null;
    }


}