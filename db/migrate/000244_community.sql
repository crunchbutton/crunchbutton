INSERT INTO `community`
	( `name`, `permalink`, `loc_lat`, `loc_lon`, `active`, `private`, `prep`, `name_alt`, `range`, `image`, `driver_group`, `timezone`, `close_all_restaurants`, `close_all_restaurants_note`, `close_3rd_party_delivery_restaurants`, `close_3rd_party_delivery_restaurants_note`, `close_all_restaurants_id_admin`, `close_3rd_party_delivery_restaurants_id_admin`)
VALUES
	( 'Customer Service', 'cs', NULL, NULL, 1, 1, 'in', 'cs', 2, 0, 'drivers-cs', 'America/Los_Angeles', 0, NULL, 0, NULL, NULL, NULL);


INSERT INTO `group` (`name`, `description`) VALUES ( 'drivers-cs', 'Customer Service');
