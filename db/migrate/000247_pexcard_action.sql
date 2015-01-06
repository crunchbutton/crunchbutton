ALTER TABLE `pexcard_action` ADD `status` enum('scheduled','processing','done','error') DEFAULT NULL;
ALTER TABLE `pexcard_action` ADD `status_date` DATETIME DEFAULT NULL;
ALTER TABLE `pexcard_action` ADD `tries` INT DEFAULT 0;
