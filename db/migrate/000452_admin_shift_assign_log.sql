ALTER TABLE `admin_shift_assign_log` ADD `reason` VARCHAR(50);
ALTER TABLE `admin_shift_assign_log` ADD `reason_other` VARCHAR(200);
ALTER TABLE `admin_shift_assign_log` ADD COLUMN `find_replacement` tinyint(1) unsigned NOT NULL DEFAULT 0;
