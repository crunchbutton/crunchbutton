<?php

class Crunchbutton_Optimizer
{

    static public function optimize($input)
    {
        $out = null;
        $env = c::getEnv();

        $url = c::config()->optimizer->{$env}->url;
        $options = array(
            'http' => array(
                'header' => 'Content-type: application/json',
                'method' => 'POST',
                'content' => json_encode($input)
            ),
        );

        $context = stream_context_create($options);
        try {
            $result = file_get_contents($url, false, $context);
            $out = json_decode($result);
        } catch (Exception $e) {
            echo 'Crunchbutton_Optimizer::optimize: Caught exception: ',  $e->getMessage(), "\n";
        }

		return $out;
	}

}

