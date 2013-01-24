<?php

class Crunchbutton_Grubhub_Restaurant extends Crunchbutton_Grubhub {
	public function __construct() {

		$url = 'http://www.grubhub.com/doSearch.action?rawSearchAddress=16400+Pacific+Coast+Hwy%2C+Huntington+Beach%2C+CA&cityId=50&custId=78437&lat=33.723692&lng=-118.076576&queryAddress=16400+Pacific+Coast+Hwy&queryCity=Huntington+Beach&queryState=CA&queryZip=92649&searchAddressStr=16400+Pacific+Coast+Hwy%2C+Huntington+Beach%2C+CA&searchSortMode=DEFAULT_NEW&stateDataString=queryCity%3DHuntington+Beach%2CqueryState%3DCA%2CqueryZip%3D92649%2Cpage%3Dsearchresultsitem%2Csecure%3Dfalse%2CcityId%3D50%2Clat%3D33.723692%2Clng%3D-118.076576%2CsearchAddress%3D16400+Pacific+Coast+Hwy%2C+Huntington+Beach%2C+CA%2Curlinfoid%3D1%2CqueryAddress%3D16400+Pacific+Coast+Hwy%2Csearchable%3Dtrue%2Cdeliverable%3Dtrue%2Cverified%3Dtrue%2CpoiSearchTerm%3Dnull&selectCustomer=Y';

		$doc = new DOMDocument('1.0');
		@$doc->loadHTML($this->_get($url));

		$qp = qp($doc, NULL, array('ignore_parser_warnings' => TRUE));

		foreach ($qp->top('.jOrderItemElement') as $item) {

			$a = clone $item;
			$dish = new Crunchbutton_Grubhub_Dish;
			
			$i = clone $item;
			$dish->name = $i->find('.item_name')->text();
			
			$i = clone $item;
			$dish->price = $i->find('.item_price')->text();
		
			$this->_dishes[] = $dish;
		
		}

	}
	
	public function dishes() {
		return $this->_dishes;
	}

}