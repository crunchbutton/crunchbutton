<?php

class Controller_charts_community extends Crunchbutton_Controller_Account {

	public function init() {
		if (!c::admin()->permission()->check(['global'])) {
			return ;
		}

		c::view()->communities = Restaurant::getCommunitiesWithRestaurantsNumber();
		c::view()->display( 'charts/community/index' );

	}

}