ALTER TABLE `driver_info` CHANGE COLUMN `down_to_help_out` `notes_to_driver` text DEFAULT NULL;
ALTER TABLE `driver_info` ADD `down_to_help_out` INT(1)  NOT NULL  DEFAULT 0;