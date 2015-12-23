ALTER TABLE `community` ADD COLUMN `notify_non_shift_drivers` tinyint(1) unsigned NOT NULL DEFAULT 0;
ALTER TABLE `community` ADD COLUMN `notify_non_shift_drivers_min` int(11) unsigned NOT NULL DEFAULT 5;

ALTER TABLE `community` ADD COLUMN `display_eta` tinyint(1) unsigned NOT NULL DEFAULT 1;

