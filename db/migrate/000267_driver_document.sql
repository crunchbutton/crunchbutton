ALTER TABLE `driver_document` ADD `type` enum('driver','marketing-rep' ) DEFAULT NULL;

UPDATE driver_document SET type = 'driver';

INSERT INTO `driver_document` ( `name`, `order`, `url`, `required`, `type`)
VALUES
	('Independent Contractor Agreement', 1, 'https://s3.amazonaws.com/uploads.hipchat.com/41812/280262/4pWiCQFWY7k9CSr/Youbeo%20Rep%20Agreement%202015%20OSG.pdf', 1, 'marketing-rep'),
	('W9', 2, 'https://s3.amazonaws.com/uploads.hipchat.com/41812/283752/R816dLDLvXfD5cM/W9.pdf', 1, 'marketing-rep'),
	('Direct Deposit', 3, 'https://s3.amazonaws.com/uploads.hipchat.com/41812/283752/CYoVFxyk8OtNydv/Direct%20Deposit%207.10.pdf', 1, 'marketing-rep');
