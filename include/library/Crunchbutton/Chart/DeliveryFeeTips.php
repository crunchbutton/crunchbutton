<?php 
class Crunchbutton_Chart_DeliveryFeeTips extends Crunchbutton_Chart {
	
	public $unit = 'US$';
	public $description = 'US$';
	
	public $groups = array( 

												'group-delivery-fee-tips-by-community' => array(
														'title' => 'Delivery fees + Tips',
														'tags' => array( 'reps' ),
														'charts' => array(  
																'delivery-fee-tips-week-by-community' => array( 'title' => 'Week', 'interval' => 'week', 'type' => 'column-community', 'method' => 'byWeekByCommunity' ),
															),
												),
										);

	public function __construct() {
		parent::__construct();
	}
	
	public function byWeekByCommunity( $render = false ){

		$community = ( $_REQUEST[ 'community' ] ) ? $_REQUEST[ 'community' ] : false;
		

		$orders = Order::q( 'SELECT o.*, YEARWEEK( o.date ) yearweek 
													FROM `order` o 
														LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant 
														INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
														INNER JOIN community c ON c.id_community = rc.id_community
														WHERE o.delivery_type = \'' . ORDER::SHIPPING_DELIVERY . '\' 
															AND o.pay_type = \'' . ORDER::PAY_TYPE_CREDIT_CARD . '\'
															AND YEARWEEK(o.date) >= ' . $this->weekFrom . ' 
															AND YEARWEEK(o.date) <= ' . $this->weekTo . '
															AND r.delivery_service = true' );
		$delivery_card = array();
		foreach( $orders as $order ){
			if( !$delivery_card[ $order->yearweek ] ){
				$delivery_card[ $order->yearweek ] = 0;
			}
			$weeks[ $order->yearweek ] = $order->yearweek;
			$delivery_card[ $order->yearweek ] += $order->deliveryFee() + $order->tip();
		}

		$orders = Order::q( 'SELECT o.*, YEARWEEK( o.date ) yearweek 
													FROM `order` o 
														LEFT JOIN restaurant r ON r.id_restaurant = o.id_restaurant 
														INNER JOIN restaurant_community rc ON r.id_restaurant = rc.id_restaurant
														INNER JOIN community c ON c.id_community = rc.id_community
														WHERE o.delivery_type = \'' . ORDER::SHIPPING_DELIVERY . '\'
															AND o.pay_type = \'' . ORDER::PAY_TYPE_CASH . '\'
															AND YEARWEEK(o.date) >= ' . $this->weekFrom . ' 
															AND YEARWEEK(o.date) <= ' . $this->weekTo . '
															AND r.delivery_service = true
															AND r.formal_relationship = false' );

		$delivery_cash = array();
		foreach( $orders as $order ){
			if( !$delivery_cash[ $order->yearweek ] ){
				$delivery_cash[ $order->yearweek ] = 0;
			}
			$delivery_cash[ $order->yearweek ] += $order->fee() + $order->customer_fee();
		}

		$allWeeks = $this->allWeeks();
		$weeks = array();
		
		for( $i = $this->from -1 ; $i < $this->to; $i++ ){
			$weeks[ $allWeeks[ $i ] ] = $allWeeks[ $i ];
		}

		$parsedData = array();
		foreach( $weeks as $week ){
			$total = 0;
			if( $delivery_card[ $week ] ){
				$total = $delivery_card[ $week ];
			}
			if( $delivery_cash[ $week ] ){
				$total -= $delivery_cash[ $week ];	
			}
			$parsedData[] = (object) array( 'Label' => $this->parseWeek( $week ), 'Total' => $total, 'Type' => $this->unit );
		}
		if( $render ){
			return array( 'data' => $parsedData, 'unit' => $this->unit );
		}
		return $parsedData;	
	}


}