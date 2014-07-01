ALTER TABLE  `payment_schedule` ADD `pay_type` enum('payment','reimbursement') DEFAULT 'payment';

ALTER TABLE  `payment` ADD `pay_type` enum('payment','reimbursement') DEFAULT 'payment';

ALTER TABLE `order_transaction` CHANGE `type` `type` enum('debit','credit','paid-to-restaurant','paid-to-driver','reimbursed-driver') DEFAULT NULL;