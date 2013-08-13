CREATE TABLE `chart` (
  `id_chart` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `permalink` varchar(255) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id_chart`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

/* INSERT THE CHARTS */
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 1, 'churn-rate-per-active-user-per-day', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 2, 'churn-rate-per-active-user-per-month', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 3, 'churn-rate-per-active-user-per-week', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 4, 'churn-rate-per-day', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 5, 'churn-rate-per-month', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 6, 'churn-rate-per-week', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 7, 'gift-cards-created-per-day', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 8, 'gift-cards-created-per-month', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 9, 'gift-cards-created-per-week', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 10, 'gift-cards-redeemed-per-day', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 11, 'gift-cards-redeemed-per-month', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 12, 'gift-cards-redeemed-per-week', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 13, 'gross-revenue-per-day', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 14, 'gross-revenue-per-month', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 15, 'gross-revenue-per-week', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 16, 'orders-by-weekday-by-community', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 17, 'orders-per-day', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 18, 'orders-per-month', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 19, 'orders-per-restaurant-by-community', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 20, 'orders-per-user-per-day', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 21, 'orders-per-user-per-month', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 22, 'orders-per-user-per-week', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 23, 'orders-per-week', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 24, 'orders-repeat-day', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 25, 'orders-repeat-month', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 26, 'orders-repeat-per-active-user-per-day', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 27, 'orders-repeat-per-active-user-per-month', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 28, 'orders-repeat-per-active-user-per-week', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 29, 'orders-repeat-vs-news-day', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 30, 'orders-repeat-vs-news-month', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 31, 'orders-repeat-vs-news-week', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 32, 'orders-repeat-week', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 33, 'orders-track-frequece', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 34, 'users-active-per-day', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 35, 'users-active-per-month', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 36, 'users-active-per-week', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 37, 'users-new-per-active-users-per-day', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 38, 'users-new-per-active-users-per-month', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 39, 'users-new-per-active-users-per-week', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 40, 'users-new-per-day', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 41, 'users-new-per-month', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 42, 'users-new-per-week', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 43, 'users-reclaimed-per-day', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 44, 'users-reclaimed-per-month', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 45, 'users-reclaimed-per-week', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 46, 'users-track-frequece', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 47, 'users-unique-per-day', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 48, 'users-unique-per-month', '');
INSERT INTO `chart` (`id_chart`, `permalink`, `description`) VALUES( 49, 'users-unique-per-week', '');