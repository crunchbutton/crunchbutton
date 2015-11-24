
INSERT INTO `site` (`id_site`, `domain`, `theme`, `name`, `sort`, `active`) VALUES (NULL, '/^.*$/', 'seven', 'UI2', '10', '1');

INSERT INTO `restaurant` (`id_restaurant`, `name`, `timezone`, `loc_lat`, `loc_long`, `delivery`, `takeout`, `credit`, `address`, `max_items`, `tax`, `phone`, `active`, `open_for_business`, `image`, `permalink`, `menu`, `fee_restaurant`, `fee_customer`, `delivery_min`, `delivery_min_amt`, `notes_todo`, `delivery_radius`, `delivery_estimated_time`, `pickup_estimated_time`, `delivery_area_notes`, `delivery_fee`, `notes_owner`, `confirmation`, `zip`, `customer_receipt`, `cash`, `giftcard`, `email`, `notes`, `balanced_id`, `balanced_bank`, `short_name`, `short_description`, `redirect`, `weight_adj`, `message`, `fee_on_subtotal`, `charge_credit_fee`, `waive_fee_first_month`, `pay_promotions`, `pay_apology_credits`, `check_address`, `contact_name`, `summary_fax`, `summary_email`, `summary_frequency`, `legal_name_payment`, `tax_id`, `open_holidays`, `community`, `delivery_service`, `formal_relationship`, `delivery_service_markup`, `promotion_maximum`, `summary_method`, `max_apology_credit`, `order_notifications_sent`, `confirmation_type`, `active_restaurant_order_placement`, `notes_to_driver`, `force_close_tagline`, `show_when_closed`, `delivery_radius_type`, `order_ahead_time`, `service_time`, `force_hours_calculation`)
VALUES
	(1, 'DEVINS TEST RESTAURANT', 'America/Los_Angeles', 33.1737, -96.6806, 1, 1, 1, '4690 Eldorado Parkway, McKinney, TX 75070, USA', 10, 8, '_PHONE_', 0, 1, '26.JPG', 'devins-test-restaurant', NULL, NULL, 10, 1, 'subtotal', NULL, 3.4, 35, 35, NULL, 1.5, '', 1, NULL, 0, 1, 1, NULL, 'asdasdsss', '_KEY_', '_KEY_', NULL, 'will still charge your card! beware!', NULL, 0, 'x', 0, 1, 0, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Testing', 0, 0, NULL, 1, 'fax', 5, 0, 'regular', 0, NULL, NULL, 1, 'community', 15, 999, 1);


INSERT INTO `community` (`id_community`, `name`, `permalink`, `loc_lat`, `loc_lon`, `active`, `private`, `prep`, `name_alt`, `range`, `image`, `driver_group`, `timezone`)
VALUES
	(1, 'Testing', 'test', '33.175101', '-96.677810', 1, 0, 'for', NULL, 2, 0, 'drivers-testing', 'America/Los_Angeles');


INSERT INTO `restaurant_community` (`id_restaurant_community`, `id_restaurant`, `id_community`, `sort`)
VALUES
	(1, 1, 1, NULL);

INSERT INTO `admin` (`id_admin`, `login`, `name`, `pass`, `timezone`, `active`)
VALUES
	(1, 'admin', 'Devin Smith test', '8b562e39bea259fd6506afa088d6290542477861', 'America/Los_Angeles', 1);

INSERT INTO `admin_permission` (`id_admin_permission`, `id_admin`, `permission`, `id_group`, `allow`)
VALUES
	(1, NULL, 'global', 1, 1);
