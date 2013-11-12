ALTER TABLE `restaurant_hour_override` ADD `notes`  varchar(250) DEFAULT '';

ALTER TABLE `restaurant_hour_override` ADD `id_admin` INT UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `restaurant_hour_override` ADD INDEX (  `id_admin` );
ALTER TABLE `restaurant_hour_override` ADD CONSTRAINT restaurant_hour_override_ibfk_2 FOREIGN KEY (id_admin) REFERENCES `admin`(id_admin);