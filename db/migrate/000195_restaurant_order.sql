ALTER TABLE  `order` ADD  `type` ENUM(  'web',  'restaurant',  'admin') NULL DEFAULT 'web';

INSERT INTO `group` (`name`, `description`) VALUES ( 'restaurant', 'Restaurant group' );