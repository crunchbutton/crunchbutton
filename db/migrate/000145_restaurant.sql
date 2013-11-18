ALTER TABLE  `restaurant` ADD  `delivery_service_markup` float DEFAULT NULL;

ALTER TABLE  `order` ADD  `delivery_service_markup` float DEFAULT NULL;
ALTER TABLE  `order` ADD  `delivery_service_markup_value` float DEFAULT NULL;