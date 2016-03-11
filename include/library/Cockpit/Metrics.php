<?php

class MetricsDateException extends Exception
{
}

/**
 * class that wraps up a number of utility functions for easily dealing with metric data.
 **/
class Cockpit_Metrics
{

    /**
     * groupByIndex groups the given array of arrays by the values in groupIndex
     * @param array $arrays - the arrays to group
     * @param mixed $groupIndex - integer index or key to group on (checks if set on each element)
     * @param mixed $defaultGroup (default null) - value to use when groupIndex is not set
     * @return array $groupedArrays - groups of arrays by the values found at $groupIndex
     *
     * example:
     *    $ret = Cockpit_Metrics::groupByIndex([[2, 'a', 3], [4, 'c', 5], [1, 'a', 16], []], 1);
     *    $ret == ['a' => [[2, 'a', 3], [1, 'a', 16]], 'c' => [[4, 'c', 5]], null => [[]]];
     */
    public static function groupByIndex($arrays, $groupIndex, $defaultGroup = null)
    {
        $output = [];
        foreach ($arrays as $row) {
            $groupValue = isset($row[$groupIndex]) ? $row[$groupIndex] : $defaultGroup;
            if (!isset($output[$groupValue])) {
                $arr = [];
                $output[$groupValue] = $arr;
            }
            $output[$groupValue][] = $row;
        }
        return $output;
    }

    // whether date should be set to start or end.
    const START_PERIOD = TRUE;
    const END_PERIOD = FALSE;

    public static function getStartDate($timeString, $startDate = null)
    {
        return self::convertTime($timeString, self::START_PERIOD, $startDate);
    }

    public static function startOfPeriod($period, $date)
    {
        return self::getStartDate('-0' . $period, $date);
    }

    public static function endOfPeriod($period, $date)
    {
        return self::getEndDate('-0' . $period, $date);
    }

    public static function getEndDate($timeString, $startDate = null)
    {
        return self::convertTime($timeString, self::END_PERIOD, $startDate);
    }

    public static function availableMetrics()
    {
        return [
            ['type' => 'users', 'format' => 'line', 'description' => 'Unique Users'],
            ['type' => 'new-users', 'format' => 'line', 'description' => 'New Users'],
            ['type' => 'new-orders', 'format' => 'line', 'description' => 'New User Orders'],
            ['type' => 'new-users-gross-revenue', 'format' => 'line', 'description' => 'Gross Revenue - New Users'],
            ['type' => 'new-users-delivery-tips', 'format' => 'line', 'description' => 'Delivery Tips - New Users'],
            ['type' => 'new-users-delivery-fee', 'format' => 'line', 'description' => 'Delivery Fee - New Users'],
            ['type' => 'repeat-users', 'format' => 'line', 'description' => 'Repeat Users'],
            ['type' => 'repeat-orders', 'format' => 'line', 'description' => 'Repeat Orders'],
            ['type' => 'repeat-users-gross-revenue', 'format' => 'line', 'description' => 'Gross Revenue - Repeat Users'],
            ['type' => 'repeat-users-delivery-tips', 'format' => 'line', 'description' => 'Delivery Tips - Repeat Users'],
            ['type' => 'repeat-users-delivery-fee', 'format' => 'line', 'description' => 'Delivery Fee - Repeat Users'],
            ['type' => 'orders-by-hour', 'format' => 'bar', 'description' => 'Orders by Time of Day'],
            ['type' => 'orders-by-day-of-week', 'format' => 'bar', 'description' => 'Orders by Day Of Week'],
            ['type' => 'orders', 'format' => 'line', 'description' => 'All Orders'],
            ['type' => 'third-party-delivery-orders-by-hour', 'format' => 'bar', 'description' => '3rd Party Delivery Orders by Time of Day'],
            ['type' => 'third-party-delivery-orders-by-day-of-week', 'format' => 'bar', 'description' => '3rd Party Delivery Orders by Day Of Week'],
            ['type' => 'third-party-delivery-orders', 'format' => 'bar', 'description' => '3rd Party Delivery Orders'],
            ['type' => 'refunded', 'format' => 'line', 'description' => 'Refunded Orders'],
            ['type' => 'gross-revenue', 'format' => 'line', 'description' => 'Gross Revenue'],
            ['type' => 'order-projections-by-hour', 'format' => 'line', 'description' => 'Hourly Order Rate Projections by Hour of Day'],
            ['type' => 'order-projections-by-day-of-week', 'format' => 'line', 'description' => 'Hourly Order Rate Projections by Day of Week'],
            ['type' => 'community-open-hours', 'format' => 'line', 'description' => 'Community open hours'],
            ['type' => 'community-force-close-hours', 'format' => 'line', 'description' => 'Community force close hours'],
            ['type' => 'community-auto-close-hours', 'format' => 'line', 'description' => 'Community auto close hours'],
            ['type' => 'community-total-close-hours', 'format' => 'line', 'description' => 'Community total close hours'],
            ['type' => 'community2-closure-rate', 'format' => 'line', 'description' => 'Community closure %'],
        ];
    }

    // converts $timeString to a naive datetime using rules described by Metrics API below.
    // (because it's far easier to work with naive datetimes internally than timestamps,
    //  since we mostly care about Pacific-time orders)
    public static function convertTime($timeString, $atStart, $startDate = null)
    {
        if (is_null($startDate)) {
            $startDate = date_create('now');
        } else {
            // allow us to mutate start
            $startDate = clone $startDate;
        }
        if ($timeString == 'now') {
            return $startDate;
        } elseif (is_numeric($timeString)) {
            return date_create('@' . $timeString);
        } elseif (_MetricsDateHelper::isKnownPeriod(substr($timeString, -1))) {
            $period = substr($timeString, -1);
            $n = substr($timeString, 0, strlen($timeString) - 1);
            if (!is_numeric($n)) {
                throw new MetricsDateException('time delta must be expressed as numeric value');
            }
            return _MetricsDateHelper::modifyDateByPeriod($startDate, $n, $period, $atStart);
        } else {
            // convert tz FROM PST to UTC
            $now = new DateTime( $timeString, new DateTimeZone( Crunchbutton_Community_Shift::CB_TIMEZONE ) );
            $now->setTimeZone( new DateTimeZone( c::config()->timezone ) );
            // TODO: Try to figure out how to catch invalid dates here!
            return date_create($timeString);
        }
    }

    /**
     * returns all communities user has access to for metrics.
     * @return array of arrays of community properties
     **/
    public function availableCommunities($simple = true)
    {
        $hasPermissionFullPermission = c::admin()->permission()->check(['global', 'metrics-all', 'metrics-communities-all']);
        $qString = 'SELECT * FROM community WHERE id_community IN ( SELECT DISTINCT( id_community ) FROM `order` ) and name not like "%test%" and name not like "%Dollar%" and name not like "%No longer in use%" and private=0 ORDER BY name ASC';
        $communities = Crunchbutton_Community::q($qString);

        if (!$hasPermissionFullPermission) {
            $_communities = [];
            foreach ($communities as $community) {
                $permission_name = strtolower($community->name);
                $permission_name = str_replace(' ', '-', $permission_name);
                $permission_name = "metrics-communities-{$permission_name}";
                if (c::admin()->permission()->check([$permission_name])) {
                    $_communities[] = $community;
                }
            }
        } else {
            $_communities = $communities;
        }
        return $communities;
    }

}

// wraps together a set of functions (publicly accessible via modifyDate) for
// converting a Metrics API adjustment period into a date that starts at the
// start of that period.
// Valid identifiers are those as specified by the Metrics API. specify $n == 0
// to move to edge of the current period. Expects naive-ish datetimes.
class _MetricsDateHelper
{
    public static function isKnownPeriod($period)
    {
        return in_array($period, ['Y', 'M', 'w', 'd', 'h', 'm', 's']);
    }
    // adjusts the date by the specified period, setting the date to either the start or end of the period depending on the value of $atStart
    // see travis/Tests/MetricsTest.php for detailed examples of how this function works.
    // but in essence modifyDateByPeriod(date_create('2014-05-10'), -3, 'Y', START_PERIOD) == date_create('2011-05-10');
    // AND modifyDateByPeriod(date_create('2014-05-10', -3, 'Y', END_PERIOD) == date_create('2011-05-10 23:59:59');
    public static function modifyDateByPeriod($date, $n, $period, $atStart)
    {
        // allow internal functions not to care about mutating the date
        $date = clone $date;
        // use the constant for clarity, even though it's just a boolean.
        $atStart = $atStart == Cockpit_Metrics::START_PERIOD;
        switch ($period) {
            case 'Y':
                return _MetricsDateHelper::_modifyYears($date, $n, $atStart);
                break;
            case 'M':
                return _MetricsDateHelper::_modifyMonths($date, $n, $atStart);
                break;
            case 'w':
                return _MetricsDateHelper::_modifyWeeks($date, $n, $atStart);
                break;
            case 'd':
                return _MetricsDateHelper::_modifyDays($date, $n, $atStart);
                break;
            case 'h':
                return _MetricsDateHelper::_modifyHours($date, $n, $atStart);
                break;
            case 'm':
                return _MetricsDateHelper::_modifyMinutes($date, $n, $atStart);
                break;
            case 's':
                return _MetricsDateHelper::_modifySeconds($date, $n, $atStart);
                break;
            default:
                // not sure if we can really include the modifier with the date b/c otherwise won't be sanitized;
                throw new MetricsDateException('invalid modifier');
        }
    }

    public static function _modifyByPeriod($date, $n, $period)
    {
        if ($n == 0) {
            return $date;
        } else if ($n > 0) {
            return $date->modify('+' . $n . ' ' . $period);
        } else {
            return $date->modify($n . ' ' . $period);
        }
    }

    // each modify function adjusts the date by the given period of time and resets it to the start of the period
    public static function _modifySeconds($date, $n, $atStart)
    {
        $date = $date->setTime($date->format('H'), $date->format('m'), $date->format('s'));
        return _MetricsDateHelper::_modifyByPeriod($date, $n, 'second');
    }

    public static function _modifyMinutes($date, $n, $atStart)
    {
        if ($atStart) {
            $seconds = 0;
        } else {
            $seconds = 59;
        }
        $date = $date->setTime($date->format('H'), $date->format('i'), $seconds);
        return _MetricsDateHelper::_modifyByPeriod($date, $n, 'minute');
    }

    public static function _modifyHours($date, $n, $atStart)
    {
        $hours = $date->format('H');
        if ($atStart) {
            $date = $date->setTime($hours, 0, 0);
        } else {
            $date = $date->setTime($hours, 59, 59);
        }
        return _MetricsDateHelper::_modifyByPeriod($date, $n, 'hour');
    }

    public static function _modifyDays($date, $n, $atStart)
    {
        if ($atStart) {
            $date = $date->setTime(0, 0, 0);
        } else {
            $date = $date->setTime(23, 59, 59);
        }
        return _MetricsDateHelper::_modifyByPeriod($date, $n, 'day');
    }

    public static function _modifyWeeks($date, $n, $atStart)
    {
        $daysFromSunday = $date->format('w');
        if ($atStart) {
            // force to start on Sunday
            $date = _MetricsDateHelper::_modifyByPeriod($date, -$daysFromSunday, 'day');
            $date = $date->setTime(0, 0, 0);
        } else {
            // force to Saturday
            $date = _MetricsDateHelper::_modifyByPeriod($date, (6 - $daysFromSunday), 'day');
            $date = $date->setTime(23, 59, 59);
        }
        $date = _MetricsDateHelper::_modifyByPeriod($date, $n, 'week');
        return $date;
    }

    public static function _modifyMonths($date, $n, $atStart)
    {
        $year = $date->format('Y');
        $month = $date->format('m');
        $yearDelta = floor($n / 12);
        $sign = ($n >= 0) ? 1 : -1;
        $monthDelta = abs($n) - (12 * $yearDelta);
        $year += $yearDelta;
        $month += $sign * $monthDelta;
        $date = $date->setDate($year, $month, 1)->setTime(0, 0, 0);
        if (!$atStart) {
            // go to start of next month and then go one day back so we don't have to deal with different length months
            $date = $date->modify('+1 month')->modify('-1 day')->setTime(23, 59, 59);
        }
        return $date;
    }

    public static function _modifyYears($date, $n, $atStart)
    {
        $year = $date->format('Y');
        if ($atStart) {
            return $date->setDate($year + $n, 1, 1)->setTime(0, 0, 0);
        } else {
            return $date->setDate($year + $n, 12, 31)->setTime(23, 59, 59);
        }
    }
}

?>
