ALTER TABLE payment CHANGE COLUMN `balanced_status` `payment_status` enum('pending','failed','succeeded','canceled') DEFAULT NULL;
ALTER TABLE payment CHANGE COLUMN `balanced_failure_reason` `payment_failure_reason` text DEFAULT NULL;
ALTER TABLE payment CHANGE COLUMN `balanced_date_checked` `payment_date_checked`  datetime DEFAULT NULL;