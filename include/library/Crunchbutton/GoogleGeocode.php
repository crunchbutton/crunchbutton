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
        print "$url\n";
        $return = Crunchbutton_GoogleGeocode::get_data($url);
        print "$return\n";
		$return = json_decode($return, true);

//        $cmd = 'curl '
//            . $rootUrl
//            . $address
//            . $extra_api_text
//            . c::config()->google->{$env}->key;
        //		exec($cmd, $return);
//		$return = json_decode(trim(join('', $return)));
//        print "$url\n";
        if (array_key_exists('results', $return)){
            $count = count($return['results']);
            print "Number of results from Google geocode is $count\n";
        } else{
            print "No results from Google geocode\n";
        }
		if ($return && array_key_exists('results', $return) && count($return['results']) == 1 && array_key_exists('geometry', $return['results'][0])) {
            $geometry = $return['results'][0]['geometry'];
//            print_r( $geometry );
            if (array_key_exists('location', $geometry) && array_key_exists('lat', $geometry['location']) && array_key_exists('lng', $geometry['location'])) {
                $lat = $geometry['location']['lat'];
                $lon = $geometry['location']['lng'];
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
