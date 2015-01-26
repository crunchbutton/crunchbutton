ALTER TABLE `driver_document` ADD `type` enum('driver','marketing-rep' ) DEFAULT NULL;

UPDATE driver_document SET type = 'driver';
