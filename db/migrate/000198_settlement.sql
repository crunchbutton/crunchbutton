ALTER TABLE  `payment_schedule` ADD `pay_type` enum('payment','reimbursement') DEFAULT 'payment';

ALTER TABLE  `payment` ADD `pay_type` enum('payment','reimbursement') DEFAULT 'payment';

ALTER TABLE  `payment` ADD  `id_driver` int(11) unsigned DEFAULT NULL;
ALTER TABLE  `payment` ADD KEY `payment_ibfk_3` (`id_driver`);
ALTER TABLE  `payment` ADD CONSTRAINT `payment_ibfk_3` FOREIGN KEY (`id_driver`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL;


ALTER TABLE `order_transaction` CHANGE `type` `type` enum('debit','credit','paid-to-restaurant','paid-to-driver','reimbursed-driver') DEFAULT NULL;


CREATE TABLE `payment_schedule_shift` (
  `id_payment_schedule_shift` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_payment_schedule` int(11) unsigned DEFAULT NULL,
  `id_admin_shift_assign` int(11) unsigned DEFAULT NULL,
  `hours` float DEFAULT NULL,
  `amount` float DEFAULT NULL,
  PRIMARY KEY (`id_payment_schedule_shift`),
  KEY `id_payment_schedule` (`id_payment_schedule`),
  KEY `payment_schedule_shift_ibfk_2` (`id_admin_shift_assign`),
  CONSTRAINT `payment_schedule_shift_ibfk_1` FOREIGN KEY (`id_payment_schedule`) REFERENCES `payment_schedule` (`id_payment_schedule`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `payment_schedule_shift_ibfk_2` FOREIGN KEY (`id_admin_shift_assign`) REFERENCES `admin_shift_assign` (`id_admin_shift_assign`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;