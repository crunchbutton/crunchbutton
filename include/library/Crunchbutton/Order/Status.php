<?php

class Crunchbutton_Order_Status extends Cana_Table {
	private $_order;
	private $_actions;

	public function __construct($order) {
		$actions = Order_Action::q('
			select * from order_action
			where id_order=?
			and type!=?
			and type!=?
			and type!=?
			and type!=?
			and type!=?
			and type!=?
			and type!=?
			order by id_order_action desc
		', [$order->id_order,
			Crunchbutton_Order_Action::TICKET_NOT_GEOMATCHED,
			Crunchbutton_Order_Action::FORCE_COMMISSION_PAYMENT,
			Crunchbutton_Order_Action::TICKET_CAMPUS_CASH,
			Crunchbutton_Order_Action::TICKET_CAMPUS_CASH_REMINDER,
			Crunchbutton_Order_Action::DELIVERY_CANCELED,
			Crunchbutton_Order_Action::TICKET_REPS_FAILED_PICKUP,
			Crunchbutton_Order_Action::TICKET_DO_NOT_DELIVERY ]);
		$this->_order = $order;

		$this->_actions = [];

		if ($actions) {
			foreach ($actions as $action) {
				$this->_actions[] = $action;
			}
		}

		foreach ($this->_actions as $k => $status) {
			if ($status->type == Crunchbutton_Order_Action::DELIVERY_REJECTED) {
				$unset = $status->id_admin;
				if ($status->id_admin != c::user()->id_admin) {
					unset($this->_actions[$k]);
				}

			} elseif ($unset && $status->id_admin == $unset) {
				unset($this->_actions[$k]);

			} elseif ($unset && $status->id_admin != $unset) {
				$unset = null;
			}
			elseif($status->type == Crunchbutton_Order_Action::DELIVERY_ORDER_TEXT_5_MIN) {
				unset($this->_actions[$k]);
			}
		}

		$this->_statusOrder = [
			Crunchbutton_Order_Action::DELIVERY_NEW => 0,
			Crunchbutton_Order_Action::DELIVERY_ACCEPTED => 1,
			Crunchbutton_Order_Action::DELIVERY_PICKEDUP => 2,
			Crunchbutton_Order_Action::DELIVERY_DELIVERED => 3,
			Crunchbutton_Order_Action::DELIVERY_TRANSFERED => 4
		];
	}

	private function _exportStatus($action) {
		$date = $action->date();
		$date_timestamp = Crunchbutton_Util::dateToUnixTimestamp( $date );
		return [
			'status' => str_replace('delivery-','',$action->type),
			'date' => $date->format('Y-m-d H:i:s'),
			'timestamp' => $date->getTimestamp(),
			'date_timestamp' => $date_timestamp,
			'order' => $this->_statusOrder[$action->type],
			'driver' => [
				'id_admin' => $action->id_admin,
				'name' =>  Admin::o($action->id_admin)->name,
				'phone' =>  Admin::o($action->id_admin)->phone
			]
		];
	}

	public function last() {
		if (!count($this->actions())) {
			$date = $this->order()->date(true);
			$date_timestamp = Crunchbutton_Util::dateToUnixTimestamp( $date );
			return [
				'status' => 'new',
				'date' => $date->format('Y-m-d H:i:s'),
				'timestamp' => $date->getTimestamp(),
				'date_timestamp' => $date_timestamp,
				'order' => 0,
				'driver' => null
			];
		} else {
			return $this->_exportStatus(array_values($this->actions())[0]);
		}
	}

	private function _statusType($status) {
		foreach ($this->actions() as $action) {
			if ($action->status == 'delivery-'.$status) {
				return $action;
			}
		}
		return false;
	}

	public function driver() {
		if ($this->last()['driver']) {
			return Admin::o($this->last()['driver']['id_admin']);
		} else {
			return null;
		}
	}

	public function order() {
		return $this->_order;
	}

	public function actions() {
		return $this->_actions;
	}

	public function __call($fn, $args) {
		if (in_array($fn, ['delivered','accepted','pickedup'])) {
			$status = $this->_exportStatus($this->_statusType('pickedup'));
			return $status ? $status : false;
		} else {
			throw new Exception('Order_Status does not have a method of '.$fn);
		}
	}

}