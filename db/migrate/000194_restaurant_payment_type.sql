ALTER TABLE `restaurant_payment_type` DROP `pay_promotions`;
ALTER TABLE `restaurant_payment_type` ADD  `max_pay_promotion` FLOAT NOT NULL DEFAULT 2.0;

UPDATE `restaurant_payment_type` SET `max_pay_promotion` = 0 WHERE id_restaurant IN (259, 71, 70, 20, 121);

UPDATE `restaurant_payment_type` SET `max_pay_promotion` = 3 WHERE id_restaurant IN (5, 86, 28, 29, 44, 146, 73, 135, 25, 78, 17, 8, 110, 21, 85, 27, 143, 14, 46, 36, 122, 133, 142, 62, 60, 106, 134, 99, 56, 40, 67);

