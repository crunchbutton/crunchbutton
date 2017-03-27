ALTER TABLE `driver_document` ADD `type` enum('driver','marketing-rep' ) DEFAULT NULL;

UPDATE driver_document SET type = 'driver';

INSERT INTO `driver_document` ( `name`, `order`, `url`, `required`, `type`)
VALUES
	('Independent Contractor Agreement', 1, '_URL_', 1, 'marketing-rep'),
	('W9', 2, '_URL_', 1, 'marketing-rep'),
	('Direct Deposit', 3, '_URL_', 1, 'marketing-rep');
