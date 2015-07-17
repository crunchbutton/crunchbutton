ALTER TABLE restaurant DROP FOREIGN KEY `fk_id_restaurant_pay_another_restaurant`;

ALTER TABLE restaurant DROP COLUMN `id_restaurant_pay_another_restaurant`;

ALTER TABLE restaurant_payment_type DROP FOREIGN KEY `restaurant_payment_type_ibfk2`;

ALTER TABLE restaurant_payment_type DROP COLUMN `id_restaurant_pay_another_restaurant`;
