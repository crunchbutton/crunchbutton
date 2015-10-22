ALTER TABLE payment CHANGE COLUMN `payment_status` `payment_status` enum('pending','failed','succeeded','canceled','reversed') DEFAULT NULL;

ALTER TABLE payment_schedule CHANGE COLUMN `status` `status` enum('scheduled','processing','done','error','deleted','archived','reversed') DEFAULT NULL;