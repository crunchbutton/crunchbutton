<?php

class Controller_Support_Content extends Crunchbutton_Controller_Account {

	public function init() {

		if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'support-crud' ])) {
			return ;
		} 

		$waiting = Crunchbutton_Support::pendingSupport()->count();

		$resultsPerPage = 15;

		$search = ( $_REQUEST[ 'search' ] ) ? $_REQUEST[ 'search' ] : '';
		$page = ( $_REQUEST[ 'page' ] ) ? $_REQUEST[ 'page' ] : 1;
		$status = ( $_REQUEST[ 'status' ] ) ? $_REQUEST[ 'status' ] : 'all';
		$type = ( $_REQUEST[ 'type' ] ) ? $_REQUEST[ 'type' ] : 'all';
		$autoRefresh = ( $_REQUEST[ 'autoRefresh' ] ) ? $_REQUEST[ 'autoRefresh' ] : 'on';

		$paginationLink = '/support/content?autoRefresh=' . $autoRefresh;

		$limit = ( $page == 1 ? 0 : ( ( ( $page - 1 ) * $resultsPerPage ) + 1 ) ) . ',' . $resultsPerPage;

		$where = '';

		if( !c::admin()->permission()->check(['global', 'support-all', 'support-crud' ] ) ){
			$restaurants = c::admin()->getRestaurantsUserHasPermissionToSeeTheirTickets();
			$restaurants[] = -1;
			$where .= ' AND s.id_restaurant IN( ' . join( $restaurants, ',' ) . ')';
		}

		if( $status != 'all' && $status != '' ){
			$where .= " AND status = '" . $status . "'";
			$paginationLink .= '&status=' . $status;
		}

		if( $type != 'all' && $type != '' ){
			if( $type == 'warning' ){
				$where = " AND s.type = '" . Crunchbutton_Support::TYPE_WARNING . "'";
			} else if(  $type == 'support' ){
				$where .= " AND ( s.type = '" . Crunchbutton_Support::TYPE_BOX_NEED_HELP . "' OR s.type = '" . Crunchbutton_Support::TYPE_SMS . "' ) ";
			} else if (  $type == 'ticket' ){
				$where = " AND s.type = '" . Crunchbutton_Support::TYPE_TICKET . "'";
			} else if (  $type == 'chat' ){
				$where = " AND s.type = '" . Crunchbutton_Support::TYPE_COCKPIT_CHAT . "'";
			}
			$paginationLink .= '&type=' . $type;
		}

		if( $search != '' ){
			$where .= ' AND ( '; 
			$where .= ' sm.name LIKE "%' . $search . '%"';
			$where .= ' OR '; 
			$where .= ' sm.phone LIKE "%' . Crunchbutton_Support::clearPhone( $search ) . '%"';
			$where .= ' ) ';
		}

		$query = "SELECT DISTINCT( s.id_support ) AS id, MAX(sm.id_support_message) id_support_message, s.* FROM support s 
									INNER JOIN support_message sm ON sm.id_support = s.id_support 
									WHERE 1=1 {$where}
									GROUP BY s.id_support
									ORDER BY sm.id_support_message DESC LIMIT {$limit}";

		$tickets = Support::q( $query );

		// count the results
		$total = c::db()->get( "SELECT COUNT(*) AS Total FROM support s INNER JOIN support_message sm ON s.id_support = sm.id_support WHERE 1=1 {$where}" );
		$total = $total->get(0)->Total;

		$startingAt = ( $tickets->count() > 0 ) ? ( ( $page - 1 ) * $resultsPerPage ) + 1 : 0;
		$endingAt = ( $startingAt + $resultsPerPage - 1 );
		$endingAt = ( $endingAt > $total ) ? $total : $endingAt;

		c::view()->totalOpened = Support::q("SELECT COUNT(*) AS count FROM support WHERE status = 'open'")->count;

		c::view()->tickets = $tickets;
		c::view()->waiting = $waiting;
		c::view()->total = $total;
		c::view()->search = $search;
		c::view()->type = $type;
		c::view()->page = $page;
		c::view()->autoRefresh = $autoRefresh;
		c::view()->status = ( $status == '' ) ? 'all' : $status;
		c::view()->startingAt = $startingAt;
		c::view()->endingAt = $endingAt;
		c::view()->resultsPerPage = $resultsPerPage;
		c::view()->paginationLink = $paginationLink;
		c::view()->layout('layout/ajax');
		c::view()->display('support/content');
	}
}
