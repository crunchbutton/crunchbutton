ALTER TABLE  `order` ADD  `id_user_payment_type` int(11) unsigned DEFAULT NULL;
ALTER TABLE  `order` ADD KEY `order_ibfk_8` (`id_user_payment_type`);
ALTER TABLE  `order` ADD CONSTRAINT `order_ibfk_8` FOREIGN KEY (`id_user_payment_type`) REFERENCES `user_payment_type` (`id_user_payment_type`) ON DELETE SET NULL ON UPDATE SET NULL

