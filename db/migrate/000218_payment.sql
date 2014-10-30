ALTER TABLE `payment` ADD `balanced_status` enum('pending', 'failed', 'succeeded') DEFAULT 'pending';
ALTER TABLE `payment` ADD `balanced_failure_reason` TEXT DEFAULT null;
ALTER TABLE `payment` ADD `balanced_date_checked` datetime DEFAULT NULL;