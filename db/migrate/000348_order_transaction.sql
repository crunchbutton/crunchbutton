ALTER TABLE `order_transaction` ADD COLUMN `note` varchar(200) DEFAULT NULL;

ALTER TABLE `order_transaction` CHANGE `type` `type` enum('debit','credit','paid-to-restaurant','paid-to-driver','reimbursed-driver','refunded') DEFAULT NULL;