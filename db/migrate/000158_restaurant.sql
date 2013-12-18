ALTER TABLE `restaurant` DROP `summary_method`;
ALTER TABLE `restaurant` ADD `summary_method` enum('fax','email') DEFAULT NULL;

ALTER TABLE `restaurant` DROP `max_apology_credit`;
ALTER TABLE `restaurant` ADD `max_apology_credit` INT DEFAULT 5;
