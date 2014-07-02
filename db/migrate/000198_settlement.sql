ALTER TABLE  `payment_schedule` ADD `pay_type` enum('payment','reimbursement') DEFAULT 'payment';

ALTER TABLE  `payment` ADD `pay_type` enum('payment','reimbursement') DEFAULT 'payment';

ALTER TABLE  `payment` ADD  `id_driver` int(11) unsigned DEFAULT NULL;
ALTER TABLE  `payment` ADD KEY `payment_ibfk_3` (`id_driver`);
ALTER TABLE  `payment` ADD CONSTRAINT `payment_ibfk_3` FOREIGN KEY (`id_driver`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL ON UPDATE SET NULL;


ALTER TABLE `order_transaction` CHANGE `type` `type` enum('debit','credit','paid-to-restaurant','paid-to-driver','reimbursed-driver') DEFAULT NULL;