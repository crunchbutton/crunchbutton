ALTER TABLE `admin_group` ADD `type` enum('driver','support','brand-representative','campus-manager') DEFAULT NULL;

-- driver
UPDATE `admin_group` ag
INNER JOIN `group` g ON ag.id_group = g.id_group AND g.type = 'driver'
SET ag.type = 'driver';

-- brand-representative
UPDATE `admin_group` ag
INNER JOIN `group` g ON ag.id_group = g.id_group AND g.type = 'marketing-rep'
SET ag.type = 'brand-representative';

-- support
UPDATE `admin_group` ag
INNER JOIN `group` g ON ag.id_group = g.id_group AND g.name = 'support'
SET ag.type = 'support';

-- campus-manager
UPDATE `admin_group` ag
INNER JOIN `group` g ON ag.id_group = g.id_group AND g.name = 'campus-manager'
SET ag.type = 'campus-manager';