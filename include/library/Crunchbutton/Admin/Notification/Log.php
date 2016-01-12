<?php

class Crunchbutton_Admin_Notification_Log extends Cana_Table
{

    public static function attemptsWithNoAdmin($id_order)
    {
        $query = 'SELECT COUNT(*) AS Total FROM `admin_notification_log` a WHERE id_order = ? AND id_admin IS NULL';
        $result = c::db()->get($query, [$id_order]);
        return intval($result->_items[0]->Total);
    }

    public function lastAttemptsWithNoAdmin( $id_order ){
        $query = 'SELECT * FROM `admin_notification_log` a WHERE id_order = ? AND id_admin IS NULL ORDER BY id_admin_notification_log DESC LIMIT 1';
        $log = Crunchbutton_Admin_Notification_Log::q( $query, [ $id_order ] )->get( 0 );
        if( $log->id_admin_notification_log ){
            return $log;
        }
        return;
    }

    public function secondsSinceLastTry( $id_order ){
        $lastTry = self::lastAttemptsWithNoAdmin( $id_order );
        if( $lastTry ){
            $now = new DateTime( 'now', new DateTimeZone( c::config()->timezone ) );
            return Util::intervalToSeconds( $now->diff( $lastTry->date() ) );
        }
    }

    public static function attemptsWithAdminAndCutoff($id_order, $id_admin)
    {
        $nowDt = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $nowString = $nowDt->format('Y-m-d H:i:s');
        $query = 'SELECT COUNT(*) AS Total FROM `admin_notification_log` a WHERE id_order = ? AND id_admin = ? ' .
            'AND date <= ?';
        $result = c::db()->get($query, [$id_order, $id_admin, $nowString]);
        return intval($result->_items[0]->Total);
    }

    public static function adminHasUnexpiredNotification($id_order, $id_admin)
    {
        $nowDt = new DateTime('now', new DateTimeZone(c::config()->timezone));
        $nowString = $nowDt->format('Y-m-d H:i:s');
        $query = 'SELECT COUNT(*) AS Total FROM `admin_notification_log` a WHERE id_order = ? AND id_admin = ? ' .
            'AND date > ?';
        $result = c::db()->get($query, [$id_order, $id_admin, $nowString]);
        $count = intval($result->_items[0]->Total);
        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function sortedAttemptsWithAdmin($id_order, $id_admin)
    {
        $query = 'SELECT * FROM `admin_notification_log` a WHERE id_order = ? AND id_admin = ? ' .
            'ORDER BY date DESC';
        return Crunchbutton_Admin_Notification_Log::q($query, [$id_order, $id_admin]);
    }

    public static function attempts($id_order)
    {
        $query = 'SELECT COUNT(*) AS Total FROM `admin_notification_log` a WHERE id_order = ?';
        $result = c::db()->get($query, [$id_order]);
        return intval($result->_items[0]->Total);
    }

    public static function attemptsWithBuffer($id_order, $useTime)
    {
        $query = 'SELECT COUNT(*) AS Total FROM `admin_notification_log` a WHERE id_order = ? AND date > ?';
        $result = c::db()->get($query, [$id_order, $useTime]);
        return intval($result->_items[0]->Total);
    }


    public function byOrder($id_order)
    {
        $query = 'SELECT * FROM admin_notification_log a WHERE a.id_order = ? ORDER BY id_admin_notification_log ASC';
        return Crunchbutton_Admin_Notification_Log::q($query, [$id_order]);
    }

    // Clear the log to restart the notification process
    public function cleanLog($id_order)
    {
        $query = 'DELETE FROM admin_notification_log WHERE id_order = ?';
        c::dbWrite()->query($query, [$id_order]);
    }

    public function restaurant()
    {
        return Crunchbutton_Restaurant::q('SELECT r.* FROM restaurant r INNER JOIN `order` o ON o.id_restaurant = r.id_restaurant  WHERE id_order = ?', [$this->id_order]);
    }

    public function date()
    {
        if (!isset($this->_date)) {
            $this->_date = new DateTime($this->date, new DateTimeZone(c::config()->timezone));
            $this->_date->setTimezone(new DateTimeZone($this->restaurant()->timezone));
        }
        return $this->_date;
    }

    public function dateAtTz($timezone)
    {
        $date = new DateTime($this->date, new DateTimeZone(c::config()->timezone));
        $date->setTimezone(new DateTimeZone($timezone));
        return $date;
    }

    public static function register($id_order, $add = '' )
    {
        $attempts = self::attemptsWithNoAdmin($id_order);
        $log = new Crunchbutton_Admin_Notification_Log();

        $description = 'Notification #' . ($attempts + 1);

        if ($attempts == 0) {
            $description .= ' First txt message';
        } else if ($attempts == 1) {
            // Change 1st driver phone call to a text message #2812
            $description .= ' Second txt message';
        } else if ($attempts == 2) {
            $description .= ' Alert to CS';
        }

        $description .= $add;

        $log->id_order = $id_order;
        $log->description = $description;
        $log->date = date('Y-m-d H:i:s');
        $log->id_admin = null;
        $log->save();
    }

    public static function registerWithAdminForLogistics($id_order, $id_admin, $expirationSeconds, $numPriorAttempts)
    {
        $now = new DateTime('now', new DateTimeZone(c::config()->timezone));
        if ($expirationSeconds < 0) {
            $now->modify('- ' . abs($expirationSeconds) . ' seconds');
        } else {
            $now->modify('+ ' . $expirationSeconds . ' seconds');
        }
        $nowString = $now->format('Y-m-d H:i:s');

        $log = new Crunchbutton_Admin_Notification_Log();

        $description = 'Notification #' . ($numPriorAttempts + 1);

        if ($numPriorAttempts == 0) {
            $description .= ' First txt message';
        } else if ($numPriorAttempts == 1) {
            // Change 1st driver phone call to a text message #2812
            $description .= ' Second txt message';
        } else if ($numPriorAttempts == 2) {
            $description .= ' Phone call';
        } else if ($numPriorAttempts == 3) {
            $description .= ' Alert to CS';
        }

        $log->id_order = $id_order;
        $log->description = $description;
        $log->date = $nowString;
        $log->id_admin = $id_admin;
        $log->save();
    }

    public function __construct($id = null)
    {
        parent::__construct();
        $this
            ->table('admin_notification_log')
            ->idVar('id_admin_notification_log')
            ->load($id);
    }
}
