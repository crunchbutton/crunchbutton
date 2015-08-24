# run separatly

ALTER TABLE `queue` CHANGE `date_start` `date_run` DATETIME  NULL  DEFAULT NULL;
ALTER TABLE `queue` ADD `date_start` DATETIME  NULL  AFTER `date_run`;
