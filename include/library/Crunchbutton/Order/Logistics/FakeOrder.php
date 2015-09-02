<?php

class Crunchbutton_Order_Logistics_FakeOrder {

    // Lazy fake order creator
    private $fakeRestaurants;
    private $fakeCustomers;
    private $fakeOrderPairs;
    private $_dummyClusterCounter;
    private $community;
    private $orderTime;
    private $earlyWindow;
    private $midWindow;
    private $lateWindow;
    private $restaurantParkingTime;
    private $restaurantServiceTime;

    public $fakeRestaurantGeo;
    public $fakeCustomerGeo;

    public function __construct($dummyStart, $community, $orderTime, $earlyWindow, $midWindow, $lateWindow, $restaurantParkingTime, $restaurantServiceTime) {
        $this->fakeRestaurants = null;
        $this->fakeCustomers = null;
        $this->fakeOrderPairs = null;
        $this->_dummyClusterCounter = $dummyStart;
        $this->community = $community;
        $this->orderTime = $orderTime;
        $this->earlyWindow = $earlyWindow;
        $this->midWindow = $midWindow;
        $this->lateWindow = $lateWindow;
        $this->restaurantParkingTime = $restaurantParkingTime;
        $this->restaurantServiceTime = $restaurantServiceTime;
        $this->fakeResturantGeo = null;
        $this->fakeCustomerGeo = null;

    }

    private function getNextDummyClusterNumber() {
        $this->_dummyClusterCounter -= 1;
        return $this->_dummyClusterCounter;
    }


    private function getFakeRestaurant() {
        // Only handle one fake restaurant for now
        if (is_null($this->fakeRestaurants) || count($this->fakeRestaurants)==0) {
            // Randomly choose a restaurant from the community list
            $rs = Crunchbutton_Restaurant::getDeliveryRestaurantsWithGeoByIdCommunity($this->community->id_community);
            $rcount = $rs->count();
            if ($rcount > 0) {
                $select = rand(0, $rcount - 1);
                $this->fakeRestaurants[] = $rs->get($select);
            }
            else{
                return null;
            }
        }
        return $this->fakeRestaurants[0];
    }

    private function getFakeCustomer() {
        // Only handle one fake customer for now
        if (is_null($this->fakeCustomers) || count($this->fakeCustomers)==0) {
            // Randomly choose a fake customer from the community list
            $fcs = $this->community->fakecustomers();
            if (!is_null($fcs) && $fcs->count() > 0) {
                $select = rand(0, $fcs->count() - 1);
                $this->fakeCustomers[] = $fcs->get($select);
            }
            else{
                $c_geo =  $this->community->communityCenter();
                if (!is_null($c_geo)){
                    $this->fakeCustomers[] = $c_geo;
                }
            }
        }
        return $this->fakeCustomers[0];

    }

    public function getFakeOrderPairs() {
        if (is_null($this->fakeOrderPairs)){
            $fop = [];

            $fakeCustomer = $this->getFakeCustomer();
            $fakeRestaurant = $this->getFakeRestaurant();
            if ((!is_null($fakeCustomer) || !is_null($this->fakeCustomerGeo)) &&
                (!is_null($fakeRestaurant) || !is_null($this->fakeRestaurantGeo)) &&
                !is_null($this->orderTime) &&
                !is_null($this->earlyWindow) && !is_null($this->midWindow) && !is_null($this->lateWindow) &&
                !is_null($this->restaurantParkingTime) && !is_null($this->restaurantServiceTime)) {

                if (!is_null($this->fakeCustomerGeo)){
                    $customer_geo = $this->fakeCustomerGeo;
                } else {
                    $customer_geo = new Crunchbutton_Order_Location($fakeCustomer->lat, $fakeCustomer->lon);
                }
                if (!is_null($this->fakeRestaurantGeo)){
                    $r_geo = $this->fakeRestaurantGeo;
                } else {
                    $r_geo = new Crunchbutton_Order_Location($fakeRestaurant->loc_lat, $fakeRestaurant->loc_long);
                }
                $dcn = $this->getNextDummyClusterNumber();
                $restaurantDestination = new Crunchbutton_Order_Logistics_Destination([
                    'objectId' => $dcn,
                    'type' => Crunchbutton_Order_Logistics_Destination::TYPE_RESTAURANT,
                    'geo' => $r_geo,
                    'orderTime' => $this->orderTime,
                    'earlyWindow' => $this->earlyWindow,
                    'midWindow' => Crunchbutton_Order_Logistics::LC_HORIZON,
                    'lateWindow' => $this->lateWindow,
                    'restaurantParkingTime' => $this->restaurantParkingTime,
                    'restaurantServiceTime' => $this->restaurantServiceTime,
                    'cluster' => $dcn,
                    'isFake' => true
                ]);

                $customerDestination = new Crunchbutton_Order_Logistics_Destination([
                    'objectId' => $dcn - 1000,
                    'type' => Crunchbutton_Order_Logistics_Destination::TYPE_CUSTOMER,
                    'geo' => $customer_geo,
                    'orderTime' => $this->orderTime,
                    'earlyWindow' => $this->earlyWindow,
                    'midWindow' => $this->midWindow,
                    'lateWindow' => $this->lateWindow,
                    'isFake' => true
                ]);

                $fop["restaurant"] = $restaurantDestination;
                $fop["customer"] = $customerDestination;
                $this->fakeOrderPairs = [$fop];
            }

        }
        return $this->fakeOrderPairs;
    }


}