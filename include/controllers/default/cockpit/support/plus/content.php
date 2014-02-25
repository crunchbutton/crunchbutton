<?php

class Controller_Support_Plus_Content extends Crunchbutton_Controller_Account {

	public function init() {

		if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'support-crud' ])) {
			return ;
		} 

		$resultsPerPage = 15;

		$page = ( $_REQUEST[ 'page' ] ) ? $_REQUEST[ 'page' ] : 1;
		$status = ( $_REQUEST[ 'status' ] ) ? $_REQUEST[ 'status' ] : '*';

		$paginationLink = '/support/plus/content?';

		$limit = ( $page == 1 ? 0 : ( ( ( $page - 1 ) * $resultsPerPage ) + 1 ) ) . ',' . $resultsPerPage;

		if( !c::admin()->permission()->check(['global', 'support-all', 'support-crud' ] ) ){
			$restaurants = c::admin()->getRestaurantsUserHasPermissionToSeeTheirTickets();
			$restaurants[] = -1;
			$where = ' AND id_restaurant IN( ' . join( $restaurants, ',' ) . ')';
		}

		if( $status != '*' && $status != '' ){
			$where = ' AND status = "' . $status . '"';
			$paginationLink .= '&status=' . $status;
		}

		$query = "SELECT * FROM support WHERE 1=1 {$where} ORDER BY id_support DESC LIMIT {$limit}";
		$tickets = Support::q( $query );

		// count the results
		$total = c::db()->get( "SELECT COUNT(*) AS Total FROM support WHERE 1=1 {$where}" );
		$total = $total->get(0)->Total;

		$startingAt = ( $tickets->count() > 0 ) ? ( ( $page - 1 ) * $resultsPerPage ) + 1 : 0;
		$endingAt = ( $startingAt + $resultsPerPage - 1 );
		$endingAt = ( $endingAt > $total ) ? $total : $endingAt;

		c::view()->totalOpened = Support::q("SELECT COUNT(*) AS count FROM support WHERE status = 'open' {$where}")->count;

		c::view()->tickets = $tickets;
		c::view()->total = $total;
		c::view()->page = $page;
		c::view()->status = ( $status == '' || $status == '*' ) ? 'all' : $status;
		c::view()->startingAt = $startingAt;
		c::view()->endingAt = $endingAt;
		c::view()->resultsPerPage = $resultsPerPage;
		c::view()->paginationLink = $paginationLink;
		c::view()->layout('layout/ajax');
		c::view()->display('support/plus/content');

	}


}
