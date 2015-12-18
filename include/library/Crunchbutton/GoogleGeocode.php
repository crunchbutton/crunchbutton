<?php

class Crunchbutton_GoogleGeocode
{

    const r_Earth_miles = 3958.754641; // 6371000 * 0.000621371;

    public static function toRadians($deg)
    {
        return $deg * M_PI / 180.0;
    }

    public static function latlonDistanceInMiles($lat1, $lon1, $lat2, $lon2) {
        $phi1 = self::toRadians($lat1);
        $phi2 = self::toRadians($lat2);
        $dPhi = self::toRadians($lat2 - $lat1);
        $dLambda = self::toRadians($lon2 - $lon1);

        $a = (sin($dPhi / 2.0) * sin($dPhi / 2.0)) +
            (cos($phi1) * cos($phi2) * sin($dLambda / 2.0) * sin($dLambda / 2.0));
        $c = 2.0 * atan2(sqrt($a), sqrt(1.0 - $a));
        return self::r_Earth_miles * $c;
    }

    public static function geocode($address)
    {
        $out = null;
        $env = c::getEnv();

        $rootUrl = 'https://maps.googleapis.com/maps/api/geocode/json?address=';
        $extra_api_text = '&key=';

        if ($address) {
            $address = str_replace(' ', '+', preg_replace('/\s+/', ' ', trim($address)));
            $address = str_replace('#', '+', $address);
        }
        $url = $rootUrl
            . $address
            . $extra_api_text
            . c::config()->google->{$env}->key;
        $return = Crunchbutton_GoogleGeocode::get_data($url);
//        print "$return\n";
        $return = json_decode($return);

//        $cmd = 'curl '
//            . $rootUrl
//            . $address
//            . $extra_api_text
//            . c::config()->google->{$env}->key;
        //		exec($cmd, $return);
//		$return = json_decode(trim(join('', $return)));
//        print "$url\n";
//        if (isset($return->results)){
//            $count = count($return->results);
//            print "Number of results from Google geocode is $count\n";
//        } else{
//            print "No results from Google geocode\n";
//        }
        if ($return && isset($return->results) && count($return->results) == 1 && isset($return->results[0]->geometry)) {
            $geometry = $return->results[0]->geometry;
//            print_r( $geometry );
            if (isset($geometry->location) && isset($geometry->location->lat) && isset($geometry->location->lng)) {
                $lat = $geometry->location->lat;
                $lon = $geometry->location->lng;
                $out = new Crunchbutton_Order_Location($lat, $lon);
            }
        }

        return $out;
    }

    public static function get_data($url)
    {
        $ch = curl_init();
        $timeout = 15;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_REFERER, "cockpit.la");
        $data = curl_exec($ch);

        //getinfo gets the data for the request
        $info = curl_getinfo($ch);
        //output the data to get more information.
//        print_r($info);
        curl_close($ch);
        return $data;
    }

}
