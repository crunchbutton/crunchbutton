<?php

class Controller_api_config_geoorders extends Crunchbutton_Controller_RestAccount {

	public function init() {

		if( !c::admin()->permission()->check( ['global'] ) ){
			$this->_error();
		}

		switch ( $this->method() ) {
			case 'post':
				$this->_configSave();
				break;
			default:
				$this->_configValue();
				break;
		}

	}

	private function _configValue(){
		$settings = [];
		$settings[Crunchbutton_Order::CONFIG_KEY_GEO_TICKET_DELIVERY_RADIUS] = (intval(Crunchbutton_Config::getVal(Crunchbutton_Order::CONFIG_KEY_GEO_TICKET_DELIVERY_RADIUS)) == 1 ) ?  true : false;
		echo json_encode( $settings );exit;
	}

	private function _configSave(){
		if($this->method() == 'post'){
			$order_ticket_radius = Crunchbutton_Config::getConfigByKey(Crunchbutton_Order::CONFIG_KEY_GEO_TICKET_DELIVERY_RADIUS);
			if(!$order_ticket_radius->id_config){
				$order_ticket_radius = new Crunchbutton_Config;
				$order_ticket_radius->key = Crunchbutton_Order::CONFIG_KEY_GEO_TICKET_DELIVERY_RADIUS;
			}
			$order_ticket_radius->value = $this->request()[Crunchbutton_Order::CONFIG_KEY_GEO_TICKET_DELIVERY_RADIUS];
			$order_ticket_radius->save();

			$order_ticket_geo = Crunchbutton_Config::getConfigByKey(Crunchbutton_Order::CONFIG_KEY_GEO_TICKET_NOT_GET_ORDERS);
			if(!$order_ticket_geo->id_config){
				$order_ticket_geo = new Crunchbutton_Config;
				$order_ticket_geo->key = Crunchbutton_Order::CONFIG_KEY_GEO_TICKET_NOT_GET_ORDERS;
			}
			$order_ticket_geo->value = $this->request()[Crunchbutton_Order::CONFIG_KEY_GEO_TICKET_NOT_GET_ORDERS];
			$order_ticket_geo->save();

		}
		$this->_configValue();
	}
}