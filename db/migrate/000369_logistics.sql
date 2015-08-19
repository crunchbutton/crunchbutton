ALTER TABLE `community` MODIFY COLUMN `delivery_logistics` TINYINT(2)  NULL  DEFAULT '0';

create index date_idx on admin_location (date);
