<?php

class Controller_charts_community extends Crunchbutton_Controller_Account {

	public function init() {
		if (!c::admin()->permission()->check(['global','community-metrics-all','community-metrics-view'])) {
			return ;
		}
		


		c::view()->communities = c::admin()->communities();
		c::view()->display( 'charts/community/index' );

	}

}