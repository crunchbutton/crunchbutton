<?php
	
class Crunchbutton_Order_Status extends Cana_Table {
	private $_order;
	private $_actions;
	
	public function __construct($order) {
		$actions = Order_Action::q('select * from order_action where id_order="'.$order->id_order.'" order by timestamp desc');
		$this->_order = $order;
		
		foreach ($actions as $action) {
			$this->_actions[] = $action;
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
		}

		$this->_statusOrder = [
			Crunchbutton_Order_Action::DELIVERY_NEW => 0,
			Crunchbutton_Order_Action::DELIVERY_ACCEPTED => 1,
			Crunchbutton_Order_Action::DELIVERY_PICKEDUP => 2,
			Crunchbutton_Order_Action::DELIVERY_DELIVERED => 3
		];
	}
	
	private function _exportStatus($action) {
		return [
			'status' => str_replace('delivery-','',$action->type),
			'date' => $action->date()->format('Y-m-d H:i:s'),
			'timestamp' => $action->date()->getTimestamp(),
			'order' => $this->_statusOrder[$action->type],
			'driver' => [
				'id_admin' => $action->id_admin,
				'name' =>  Admin::o($action->id_admin)->name,
			]
		];
	}

	public function last() {
		if (!count($this->actions())) {
			return [
				'status' => 'new',
				'date' => $this->order()->date()->format('Y-m-d H:i:s'),
				'timestamp' => $this->order()->date()->getTimestamp(),
				'order' => 0,
				'driver' => null
			];
		} else {
			return $this->_exportStatus($this->actions()[0]);
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