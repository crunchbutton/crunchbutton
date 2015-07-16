<?php

class Crunchbutton_Order_Logistics_DestinationList extends Cana_Model
{

    public $distanceType;
    public $driverMph;

    private $oldFakeOrderIds;
    private $newFakeOrderIds;

    private $newDestinations;

    private $oldFirstCoords;
    private $newFirstCoords;

    private $oldSecondCoords;
    private $newSecondCoords;

    private $oldNodeTypes;
    private $newNodeTypes;

    private $oldOrderTimes;
    private $newOrderTimes;

    private $oldEarlyWindows;
    private $newEarlyWindows;

    private $oldMidWindows;
    private $newMidWindows;

    private $oldLateWindows;
    private $newLateWindows;

    private $oldPickupIdxs;
    private $newPickupIdxs;

    private $oldDeliveryIdxs;
    private $newDeliveryIdxs;

    private $oldRestaurantParkingTimes;
    private $newRestaurantParkingTimes;

    private $oldClusters;
    private $newClusters;

    public function __construct($distanceType)
    {
        $this->id_old_counter = -1;
        $this->id_new_counter = -1;
        $this->old_parking_clusters = [];
        $this->new_parking_clusters = [];
        $this->distanceType = $distanceType;

        $this->oldFakeOrderIds = []; // We'll need this later to remove from the output route.
        $this->newFakeOrderIds = []; // We'll need this later to remove from the output route.
    }

    // This is the driver location and needs to be first
    public function addDriverDestination($destination)
    {
        $this->id_old_counter = 0;
        $this->id_new_counter = 0;
        $this->newDestinations = [$destination];

        // Two versions because we run the optimizations twice, once without and once with the new order

        $this->oldFirstCoords = [floatval($destination->geo->lat)];
        $this->newFirstCoords = [floatval($destination->geo->lat)];

        $this->oldSecondCoords = [floatval($destination->geo->lon)];
        $this->newSecondCoords = [floatval($destination->geo->lon)];

        $this->oldNodeTypes = [Crunchbutton_Optimizer_Input::TYPE_DRIVER];
        $this->newNodeTypes = [Crunchbutton_Optimizer_Input::TYPE_DRIVER];

        $this->oldOrderTimes = [0];
        $this->newOrderTimes = [0];

        $this->oldEarlyWindows = [0];
        $this->newEarlyWindows = [0];

        $this->oldMidWindows = [Crunchbutton_Order_Logistics::LC_HORIZON];
        $this->newMidWindows = [Crunchbutton_Order_Logistics::LC_HORIZON];

        $this->oldLateWindows = [Crunchbutton_Order_Logistics::LC_HORIZON];
        $this->newLateWindows = [Crunchbutton_Order_Logistics::LC_HORIZON];

        $this->oldPickupIdxs = [0];
        $this->newPickupIdxs = [0];

        $this->oldDeliveryIdxs = [0];
        $this->newDeliveryIdxs = [0];

        $this->oldRestaurantParkingTimes = [0];
        $this->newRestaurantParkingTimes = [0];

    }

    private function addRestaurantDestinationInfo($destination, $isNewOrder, $matchingOldCustomerId, $matchingNewCustomerId)
    {
        // Warning: Don't access id_old_counter or id_new_counter here


        // Two versions because we run the optimizations twice, once without and once with the new order
        if (!$isNewOrder) {
            $this->oldFirstCoords[] = floatval($destination->geo->lat);
            $this->oldSecondCoords[] = floatval($destination->geo->lon);
            $this->oldNodeTypes[] = Crunchbutton_Optimizer_Input::TYPE_RESTAURANT;
            $this->oldOrderTimes[] = $destination->orderTime;
            $this->oldEarlyWindows[] = $destination->earlyWindow;
            $this->oldMidWindows[] = $destination->midWindow;
            $this->oldLateWindows[] = $destination->lateWindow;
            $this->oldPickupIdxs[] = 0;
            $this->oldDeliveryIdxs[] = $matchingOldCustomerId;
            $this->oldRestaurantParkingTimes[] = $destination->restaurantParkingTime;
        }
        $this->newFirstCoords[] = floatval($destination->geo->lat);
        $this->newSecondCoords[] = floatval($destination->geo->lon);
        $this->newNodeTypes[] = Crunchbutton_Optimizer_Input::TYPE_RESTAURANT;
        $this->newOrderTimes[] = $destination->orderTime;
        $this->newEarlyWindows[] = $destination->earlyWindow;
        $this->newMidWindows[] = $destination->midWindow;
        $this->newLateWindows[] = $destination->lateWindow;
        $this->newPickupIdxs[] = 0;
        $this->newDeliveryIdxs[] = $matchingNewCustomerId;
        $this->newRestaurantParkingTimes[] = $destination->restaurantParkingTime;

    }

    private function addCustomerDestinationInfo($destination, $isNewOrder, $matchingOldRestaurantId, $matchingNewRestaurantId)
    {
        // Warning: Don't access id_old_counter or id_new_counter here

        // Two versions because we run the optimizations twice, once without and once with the new order
        if (!$isNewOrder) {
            $this->oldFirstCoords[] = floatval($destination->geo->lat);
            $this->oldSecondCoords[] = floatval($destination->geo->lon);
            $this->oldNodeTypes[] = Crunchbutton_Optimizer_Input::TYPE_CUSTOMER;
            $this->oldOrderTimes[] = $destination->orderTime;
            $this->oldEarlyWindows[] = $destination->earlyWindow;
            $this->oldMidWindows[] = $destination->midWindow;
            $this->oldLateWindows[] = $destination->lateWindow;
            $this->oldPickupIdxs[] = $matchingOldRestaurantId;
            $this->oldDeliveryIdxs[] = 0;
            $this->oldRestaurantParkingTimes[] = 0;
        }
        $this->newFirstCoords[] = floatval($destination->geo->lat);
        $this->newSecondCoords[] = floatval($destination->geo->lon);
        $this->newNodeTypes[] = Crunchbutton_Optimizer_Input::TYPE_CUSTOMER;
        $this->newOrderTimes[] = $destination->orderTime;
        $this->newEarlyWindows[] = $destination->earlyWindow;
        $this->newMidWindows[] = $destination->midWindow;
        $this->newLateWindows[] = $destination->lateWindow;
        $this->newPickupIdxs[] = $matchingNewRestaurantId;
        $this->newDeliveryIdxs[] = 0;
        $this->newRestaurantParkingTimes[] = 0;

    }


    // Non-driver destination
    public function addDestinationPair($restaurantDestination, $customerDestination, $isNewOrder)
    {
//        print "Adding destination pair\n";

        if (is_null($restaurantDestination) || is_null($customerDestination) ||
            $restaurantDestination->type != Crunchbutton_Order_Logistics_Destination::TYPE_RESTAURANT ||
            $customerDestination->type != Crunchbutton_Order_Logistics_Destination::TYPE_CUSTOMER
        ) {
            return false;
        }

        // All orders are added to the "new" optimization list
        $newRestaurantId = $this->id_new_counter + 1;
        $newCustomerId = $this->id_new_counter + 2;
        $this->id_new_counter += 2;

        $oldRestaurantId = null;
        $oldCustomerId = null;

        if (!$isNewOrder) {
            // This might be a bit confusing: new orders are not included in the "old" optimization list
            $oldRestaurantId = $this->id_old_counter + 1;
            $oldCustomerId = $this->id_old_counter + 2;
            $this->old_parking_clusters[$restaurantDestination->cluster][] = $oldRestaurantId;
            $this->id_old_counter += 2;
        }

        $this->newDestinations[] = $restaurantDestination;
        $this->newDestinations[] = $customerDestination;
        $this->addRestaurantDestinationInfo($restaurantDestination, $isNewOrder, $oldCustomerId, $newCustomerId);
        $this->addCustomerDestinationInfo($customerDestination, $isNewOrder, $oldRestaurantId, $newRestaurantId);
        $this->new_parking_clusters[$restaurantDestination->cluster][] = $newRestaurantId;
        return true;
    }


    public function destinations()
    {
        return $this->newDestinations;
    }

    public function fakeOrderIds()
    {
        return $this->newFakeOrderIds;
    }

    public function count()
    {
        return count($this->newDestinations);
    }

    // TODO: $withFakes operational only for situations with 1 new order for now
    public function appendFakeOrderPairs($input, $fakeOrderPairs, $fakeRestaurantIds, $fakeCustomerIds)
    {
        $fop = $fakeOrderPairs[0];
        $rid = $fakeRestaurantIds[0];
        $cid = $fakeCustomerIds[0];
        $restaurantDestination = $fop["restaurant"];
        $customerDestination = $fop["customer"];
        $input->firstCoords[] = floatval($restaurantDestination->geo->lat);
        $input->secondCoords[] = floatval($restaurantDestination->geo->lon);
        $input->nodeTypes[] = Crunchbutton_Optimizer_Input::TYPE_RESTAURANT;
        $input->orderTimes[] = $restaurantDestination->orderTime;
        $input->earlyWindows[] = $restaurantDestination->earlyWindow;
        $input->midWindows[] = $restaurantDestination->midWindow;
        $input->lateWindows[] = $restaurantDestination->lateWindow;
        $input->pickupIdxs[] = 0;
        $input->deliveryIdxs[] = $cid;
        $input->restaurantParkingTimes[] = $restaurantDestination->restaurantParkingTime;

        $input->firstCoords[] = floatval($customerDestination->geo->lat);
        $input->secondCoords[] = floatval($customerDestination->geo->lon);
        $input->nodeTypes[] = Crunchbutton_Optimizer_Input::TYPE_CUSTOMER;
        $input->orderTimes[] = $customerDestination->orderTime;
        $input->earlyWindows[] = $customerDestination->earlyWindow;
        $input->midWindows[] = $customerDestination->midWindow;
        $input->lateWindows[] = $customerDestination->lateWindow;
        $input->pickupIdxs[] = $rid;
        $input->deliveryIdxs[] = 0;
        $input->restaurantParkingTimes[] = 0;

        // Not clustering the fake order so, don't care about the cluster array.
        // As long as computeClusters gets the right number of nodes, including the fake order nodes,
        //  empty clusters will be created for the fake orders.
    }


    // TODO: $doCreateFakeOrders operational only for situations with 1 new order for now
    // Could a imagine a situation down the line where this isn't the case, when projected orders are taken into account
    // Only a single fake is allowed as well.
    public function createOptimizerInputs($fakeOrder, $doCreateFakeOrders)
    {
        $optInputsList = ['old' => null, 'new' => null, 'hasFakeOrder' => false];
        $numOldNodes = $this->id_old_counter + 1;
        $numNewNodes = $this->id_new_counter + 1;
//        print "Num new nodes: $numNewNodes\n";

        if ($numNewNodes == 3) {
//            print "Has only new order\n";
            $fakeOrderPairs = null;
            if ($doCreateFakeOrders && !is_null($fakeOrder)) {
                $fakeOrderPairs = $fakeOrder->getFakeOrderPairs();
//                print "Got fake order pairs\n";
            }
            if ($doCreateFakeOrders && !is_null($fakeOrderPairs) && count($fakeOrderPairs) == 1) {
                $totalOldNodes = $numOldNodes + 2;
                $totalNewNodes = $numNewNodes + 2;

                $this->computeClusters($totalOldNodes, $totalNewNodes);

                $old = $this->createBaseInput();
                $old->numNodes = $totalOldNodes;
                $this->copyOldArraysToOptimizerInput($old);
                $this->appendFakeOrderPairs($old, $fakeOrderPairs, [$this->id_old_counter + 1],
                    [$this->id_old_counter + 2]);
                $optInputsList['old'] = $old;
                $this->oldFakeOrderIds = [$this->id_old_counter + 1, $this->id_old_counter + 2];

                $new = $this->createBaseInput();
                $new->numNodes = $totalNewNodes;
                $this->copyNewArraysToOptimizerInput($new);
                $this->appendFakeOrderPairs($new, $fakeOrderPairs, [$this->id_new_counter + 1],
                    [$this->id_new_counter + 2]);
                $optInputsList['new'] = $new;
                $this->newFakeOrderIds = [$this->id_new_counter + 1, $this->id_new_counter + 2];
                $optInputsList['hasFakeOrder'] = true;
            } else if (!$doCreateFakeOrders) {
                // Only do the new optimization
//                print "Only doing the new optimization\n";
                $this->computeClusters(0, $numNewNodes);

                $new = $this->createBaseInput();
                $new->numNodes = 3;
                $this->copyNewArraysToOptimizerInput($new);
                $optInputsList['new'] = $new;
            }

        } else if ($numNewNodes > 3) {
//            print "Has more than the new order\n";
            $this->computeClusters($numOldNodes, $numNewNodes);

            $old = $this->createBaseInput();
            $old->numNodes = $numOldNodes;
            $this->copyOldArraysToOptimizerInput($old);
            $optInputsList['old'] = $old;

            $new = $this->createBaseInput();
            $new->numNodes = $numNewNodes;
            $this->copyNewArraysToOptimizerInput($new);
            $optInputsList['new'] = $new;

        }

        return $optInputsList;
    }

    public function copyNewArraysToOptimizerInput($input)
    {
        // TODO: Maybe switch to references, instead of doing all these copies
        $input->firstCoords = $this->newFirstCoords;
        $input->secondCoords = $this->newSecondCoords;
        $input->nodeTypes = $this->newNodeTypes;
        $input->orderTimes = $this->newOrderTimes;
        $input->earlyWindows = $this->newEarlyWindows;
        $input->midWindows = $this->newMidWindows;
        $input->lateWindows = $this->newLateWindows;
        $input->pickupIdxs = $this->newPickupIdxs;
        $input->deliveryIdxs = $this->newDeliveryIdxs;
        $input->restaurantParkingTimes = $this->newRestaurantParkingTimes;
        $input->clusters = $this->newClusters;
    }

    public function copyOldArraysToOptimizerInput($input)
    {
        // TODO: Maybe switch to references, instead of doing all these copies
        $input->firstCoords = $this->oldFirstCoords;
        $input->secondCoords = $this->oldSecondCoords;
        $input->nodeTypes = $this->oldNodeTypes;
        $input->orderTimes = $this->oldOrderTimes;
        $input->earlyWindows = $this->oldEarlyWindows;
        $input->midWindows = $this->oldMidWindows;
        $input->lateWindows = $this->oldLateWindows;
        $input->pickupIdxs = $this->oldPickupIdxs;
        $input->deliveryIdxs = $this->oldDeliveryIdxs;
        $input->restaurantParkingTimes = $this->oldRestaurantParkingTimes;
        $input->clusters = $this->oldClusters;
    }


    public function createBaseInput()
    {
        $input = new Crunchbutton_Optimizer_Input();
        $input->driverMph = $this->driverMph;
        $input->penaltyCoefficient = Crunchbutton_Order_Logistics::LC_PENALTY_COEFFICIENT;
        $input->customerDropoffTime = Crunchbutton_Order_Logistics::LC_CUSTOMER_DROPOFF_TIME;
        $input->restaurantPickupTime = Crunchbutton_Order_Logistics::LC_RESTAURANT_PICKUP_TIME;
        $input->slackMaxTime = Crunchbutton_Order_Logistics::LC_SLACK_MAX_TIME;
        $input->horizon = Crunchbutton_Order_Logistics::LC_HORIZON;
        $input->maxRunTime = Crunchbutton_Order_Logistics::LC_MAX_RUN_TIME;
        $input->distanceType = $this->distanceType;

        return $input;
    }


    public function hasOnlyNewOrder()
    {
//        print "The id_new_counter is: $this->id_new_counter\n";
        return $this->id_new_counter == 2; // Driver location + restaurant + customer, numbered starting at 0
    }

    // This will include fake orders too, so $numNewNodes is not necessarily the same as the id_new_counter + 1
    //  Same for $numOldNodes.
    private function computeClusters($numOldNodes, $numNewNodes)
    {
        $this->oldClusters = [];
        if ($numOldNodes > 0) {
            $this->oldClusters = array_fill(0, $numOldNodes, []);
            foreach ($this->old_parking_clusters as $key => $ids) {
                foreach ($ids as $id) {
                    foreach ($ids as $id2) {
                        if ($id != $id2) {
                            $this->oldClusters[$id][] = $id2;
                        }
                    }
                }
            }

        }
        $this->newClusters = [];
        if ($numNewNodes > 0) {
            $this->newClusters = array_fill(0, $numNewNodes, []);
            foreach ($this->new_parking_clusters as $key => $ids) {
                foreach ($ids as $id) {
                    foreach ($ids as $id2) {
                        if ($id != $id2) {
                            $this->newClusters[$id][] = $id2;
                        }
                    }
                }
            }

        }
    }


}