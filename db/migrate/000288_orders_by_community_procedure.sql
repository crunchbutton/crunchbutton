-- This procedure generates orders by day by community over the period specified (hard coding days in order to make things work and avoid a ton of joins)
-- Drop the procedure

DROP PROCEDURE IF EXISTS _ordersByCommunityAtPeriod;

-- Create the procedure
DELIMITER ;;
CREATE PROCEDURE _ordersByCommunityAtPeriod( IN start_date varchar(20), IN end_date varchar(20) )

BEGIN
    SET @start_date := start_date;
    SET @end_date := end_date;


    SELECT Community.name,
        COALESCE(`All`.orders, 0) `All`,
        COALESCE(Monday.orders, 0) Monday,
        COALESCE(Tuesday.orders, 0) Tuesday,
        COALESCE(Wednesday.orders, 0) Wednesday,
        COALESCE(Thursday.orders, 0) Thursday,
        COALESCE(Friday.orders, 0) Friday,
        COALESCE(Saturday.orders, 0) Saturday,
        COALESCE(Sunday.orders, 0) Sunday
    FROM
    ( SELECT DISTINCT c.name,
                        c.id_community
    FROM community c
    INNER JOIN restaurant_community rc ON rc.id_community = c.id_community
    INNER JOIN restaurant r ON r.id_restaurant = rc.id_restaurant
    WHERE c.active = 1
        AND r.delivery_service = 1
    ORDER BY c.name ) Community
    LEFT JOIN
        ( SELECT id_community,
                COUNT(*) orders
        FROM `order` o
        LEFT JOIN restaurant r ON o.id_restaurant = r.id_restaurant
        WHERE date >= @start_date
            AND date <= @end_date
            AND (o.likely_test = FALSE
                OR o.likely_test IS NULL)
            AND r.delivery_service = 1
            AND o.do_not_reimburse_driver = 0
        GROUP BY id_community ) `All` ON `All`.id_community = Community.id_community
    LEFT JOIN
        ( SELECT id_community,
                COUNT(*) orders
        FROM `order` o
        LEFT JOIN restaurant r ON o.id_restaurant = r.id_restaurant
        WHERE DAYNAME(date) = "Monday"
            AND date >= @start_date
            AND date <= @end_date
            AND (o.likely_test = FALSE
                OR o.likely_test IS NULL)
            AND r.delivery_service = 1
            AND o.do_not_reimburse_driver = 0
        GROUP BY id_community ) Monday ON Monday.id_community = Community.id_community
    LEFT JOIN
        ( SELECT id_community,
                COUNT(*) orders
        FROM `order` o
        LEFT JOIN restaurant r ON o.id_restaurant = r.id_restaurant
        WHERE DAYNAME(date) = "Tuesday"
            AND date >= @start_date
            AND date <= @end_date
            AND (o.likely_test = FALSE
                OR o.likely_test IS NULL)
            AND r.delivery_service = 1
            AND o.do_not_reimburse_driver = 0
        GROUP BY id_community ) Tuesday ON Tuesday.id_community = Community.id_community
    LEFT JOIN
        ( SELECT id_community,
                COUNT(*) orders
        FROM `order` o
        LEFT JOIN restaurant r ON o.id_restaurant = r.id_restaurant
        WHERE DAYNAME(date) = "Wednesday"
            AND date >= @start_date
            AND date <= @end_date
            AND (o.likely_test = FALSE
                OR o.likely_test IS NULL)
            AND r.delivery_service = 1
            AND o.do_not_reimburse_driver = 0
        GROUP BY id_community) Wednesday ON Wednesday.id_community = Community.id_community
    LEFT JOIN
        ( SELECT id_community,
                COUNT(*) orders
        FROM `order` o
        LEFT JOIN restaurant r ON o.id_restaurant = r.id_restaurant
        WHERE DAYNAME(date) = "Thursday"
            AND date >= @start_date
            AND date <= @end_date
            AND (o.likely_test = FALSE
                OR o.likely_test IS NULL)
            AND r.delivery_service = 1
            AND o.do_not_reimburse_driver = 0
        GROUP BY id_community) Thursday ON Thursday.id_community = Community.id_community
    LEFT JOIN
        ( SELECT id_community,
                COUNT(*) orders
        FROM `order` o
        LEFT JOIN restaurant r ON o.id_restaurant = r.id_restaurant
        WHERE DAYNAME(date) = "Friday"
            AND date >= @start_date
            AND date <= @end_date
            AND (o.likely_test = FALSE
                OR o.likely_test IS NULL)
            AND r.delivery_service = 1
            AND o.do_not_reimburse_driver = 0
        GROUP BY id_community) Friday ON Friday.id_community = Community.id_community
    LEFT JOIN
        ( SELECT id_community,
                COUNT(*) orders
        FROM `order` o
        LEFT JOIN restaurant r ON o.id_restaurant = r.id_restaurant
        WHERE DAYNAME(date) = "Saturday"
            AND date >= @start_date
            AND date <= @end_date
            AND (o.likely_test = FALSE
                OR o.likely_test IS NULL)
            AND r.delivery_service = 1
            AND o.do_not_reimburse_driver = 0
        GROUP BY id_community) Saturday ON Saturday.id_community = Community.id_community
    LEFT JOIN
        ( SELECT id_community,
                COUNT(*) orders
        FROM `order` o
        LEFT JOIN restaurant r ON o.id_restaurant = r.id_restaurant
        WHERE DAYNAME(date) = "Sunday"
            AND date >= @start_date
            AND date <= @end_date
            AND (o.likely_test = FALSE
                OR o.likely_test IS NULL)
            AND r.delivery_service = 1
            AND o.do_not_reimburse_driver = 0
        GROUP BY id_community) Sunday ON Sunday.id_community = Community.id_community;


END;;
DELIMITER ;

-- Then you will be able to call the procedure via
-- CALL _ordersByCommunityAtPeriod( '2014-12-1', '2014-12-8' );
