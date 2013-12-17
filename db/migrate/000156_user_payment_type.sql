CREATE TABLE `user_payment_type` (
  `id_user_payment_type` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(11) unsigned NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `stripe_id` varchar(255) DEFAULT NULL,
  `card` varchar(16) DEFAULT NULL,
  `card_type` enum('visa','mastercard','amex','discover') DEFAULT NULL,
  `balanced_id` varchar(255) DEFAULT NULL,
  `card_exp_year` int(4) DEFAULT NULL,
  `card_exp_month` int(2) DEFAULT NULL,
  `date` DATETIME NOT NULL,
  PRIMARY KEY (`id_user_payment_type`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


ALTER TABLE `user_payment_type` ADD CONSTRAINT `user_payment_type_ibfk1` FOREIGN KEY(`id_user`) REFERENCES `user`(`id_user`)  ON DELETE CASCADE ON UPDATE CASCADE;