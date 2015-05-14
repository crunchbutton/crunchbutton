<?php

class Controller_api_dashboard extends Crunchbutton_Controller_RestAccount {

	public function init() {
		
		if (!c::admin()->permission()->check(['global', 'support-all', 'support-view', 'support-crud'])) {
			$this->error(401);
		}
		
		
		// define queries like this
		$queries['Just two users'] = 'select user.name, user.phone from user where name = "mr test" limit 2';

		// Community Crisis Status - Pacific
		$queries['Community Crisis Status - Pacific'] = "
			Select wow.community, wow.undelivered_orders, wow.drivers_on_call, holy.drivers_doing_shit
			from 

			(select tada.community, undelivered_orders, drivers_on_call 
			from 

			(select crazy.community as Community, Undelivered_Orders, drivers_who_should_be_on_call as Drivers_On_Call 
			from 

			(select c.name as Community, count(my_orders) as Undelivered_Orders
			from 

			(select MAX(oa.id_order_action) as last_action, oa.timestamp as time_last_action, oa.id_order as my_orders, oa.type as ord_action
			from order_action oa
			group by my_orders
			order by my_orders desc) results

			inner join `order` o on o.id_order = results.my_orders
			inner join community c on o.id_community = c.id_community
			WHERE DATE(o.date) BETWEEN date_format(now(),'%Y:%m:%d') AND date_format(now(),'%Y:%m:%d')
			and ord_action != 'delivery-delivered'
			and c.name != 'testing'
			and o.refunded = 0
			group by c.name
			order by Undelivered_Orders desc) resultados

			left join 

			(select c.name as Community, count(a.name) as Drivers_Who_Should_Be_On_Call 
			from 

			(select shift_id, community_name 
			from 

			(select id_community as community_name, id_community_shift as shift_id, CONVERT_TZ(date_start,'-05:00','-05:00') as starting_date, CONVERT_TZ(date_end,'-05:00','-05:00') as ending_date, now() as time_now
			from community_shift) results

			where time_now > starting_date
			and time_now < ending_date) results_two

			inner join admin_shift_assign on admin_shift_assign.id_community_shift = results_two.shift_id
			inner join community c on c.id_community = results_two.community_name
			inner join admin a on a.id_admin = admin_shift_assign.id_admin
			where c.name != 'testing'
			and a.name != 'this order is cancelled'
			and a.active = 1
			group by c.name) crazy 

			on crazy.community = resultados.community) tada

			inner join community c on c.name = tada.community
			where c.timezone = 'America/Los_Angeles') wow

			inner join

			(select tada.community, count(tada.drivers_last_action) as drivers_doing_shit 
			from 

			(select resultados.community, resultados.driver, date_format(resultados.last_status_update_to_an_order, '%r') as drivers_last_action 
			from

			(select c.name as Community, a.name as Driver, Last_Status_Update_To_An_Order
			from

			(select max(date_format(oa.timestamp,'%Y:%m:%d %k:%i:%s')) as Last_Status_Update_To_An_Order, try_this.admin_id, try_this.last_order_id, try_this.id_order_action, try_this.ord_action
			from
			(select oa.id_admin as admin_id, max(oa.id_order) as last_order_id, oa.id_order_action, oa.type as ord_action
			from order_action oa
			WHERE DATE(oa.timestamp) BETWEEN date_format(now(),'%Y:%m:%d') AND date_format(now(),'%Y:%m:%d')
			and oa.id_admin != 584
			and oa.id_admin != 497
			and oa.id_admin != 564
			and oa.id_admin != 1
			and oa.id_admin != 2
			and oa.id_admin != 3
			and oa.id_admin != 4
			and oa.id_admin != 5
			group by id_admin) try_this
			inner join order_action oa on oa.id_order = try_this.last_order_id
			group by id_admin) results

			inner join admin a on a.id_admin = results.admin_id
			inner join `order` o on results.last_order_id = o.id_order
			inner join community c on c.id_community = o.id_community
			where c.name != 'testing'
			and a.name != 'This Order Is Cancelled'
			order by Last_Status_Update_To_An_Order desc) resultados

			WHERE resultados.last_status_update_to_an_order > convert_tz(now(), '-05:00','-06:00')) tada

			group by tada.community) holy

			on holy.community = wow.community
		";
		
		
		$queries['Drivers Last Action'] = "
			Select resultados.community, resultados.driver, date_format(resultados.last_status_update_to_an_order, '%r') as drivers_last_action from(
			select c.name as Community, a.name as Driver, Last_Status_Update_To_An_Order
			from(
			select max(date_format(oa.timestamp,'%Y:%m:%d %k:%i:%s')) as Last_Status_Update_To_An_Order, oa.id_admin as admin_id, oa.id_order as last_order_id, oa.id_order_action, oa.type as ord_action
			from order_action oa
			WHERE DATE(oa.timestamp) BETWEEN date_format(now(),'%Y:%m:%d') AND date_format(now(),'%Y:%m:%d')
			  and oa.id_admin != 584
			and oa.id_admin != 497
			and oa.id_admin != 564
			and oa.id_admin != 1
			and oa.id_admin != 2
			and oa.id_admin != 3
			and oa.id_admin != 4
			and oa.id_admin != 5
			group by id_admin) results
			inner join admin a on a.id_admin = results.admin_id
			inner join `order` o on results.last_order_id = o.id_order
			inner join community c on c.id_community = o.id_community
			where c.name != 'testing'
			and a.name != 'This Order Is Cancelled'
			order by Last_Status_Update_To_An_Order desc) resultados
		";
		
		
		$this->_process($queries);
		
		
		
	}
	
	private function _process($queries) {
		$results = [];
		foreach ($queries as $key => $query) {
			$r = c::db()->query($query);
			$result = [];
			while ($o = $r->fetch()) {
				$result[] = $o;
			}
			$results[$key] = $result;
		}
		echo json_encode($results);
	}
}
