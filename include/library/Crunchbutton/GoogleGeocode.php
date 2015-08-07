<?php

class Crunchbutton_GoogleGeocode
{

    static public function geocode($address)
    {
        $out = null;
        $env = c::getEnv();

        $rootUrl = 'https://maps.googleapis.com/maps/api/geocode/json?address=';
        $extra_api_text = '&key=';

        if ($address) {
            $address = str_replace(' ', '+', preg_replace('/\s+/', ' ', trim($address)));
            $address = str_replace('#', 'no', $address);
        }
        $url = $rootUrl
            . $address
            . $extra_api_text
            . c::config()->google->{$env}->key;

        $return = Crunchbutton_GoogleGeocode::get_data($url);
		$return = json_decode($return);

//        $cmd = 'curl '
//            . $rootUrl
//            . $address
//            . $extra_api_text
//            . c::config()->google->{$env}->key;
        //		exec($cmd, $return);
//		$return = json_decode(trim(join('', $return)));
//        print "$url\n";

		if ($return && isset($return->results) && count($return->results) == 1 && isset($return->results[0]->geometry)) {
            $geometry = $return->results[0]->geometry;
            print_r( $geometry );
            if (isset($geometry->location) && isset($geometry->location->lat) && isset($geometry->location->lng)) {
                $lat = $geometry->location->lat;
                $lon = $geometry->location->lng;
                $out = new Crunchbutton_Order_Location($lat, $lon);
            }
        }

		return $out;
	}

    static public function get_data($url) {
        $ch = curl_init();
        $timeout = 15;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);

        //getinfo gets the data for the request
        $info = curl_getinfo($ch);
        //output the data to get more information.
//        print_r($info);
        curl_close($ch);
        return $data;
    }

}
