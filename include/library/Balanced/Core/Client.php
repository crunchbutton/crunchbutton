<?php

namespace Balanced\Core;

use Balanced\Exceptions\HTTPError;
use Balanced\Settings;
use Httpful\Request;

class Client
{    
    public function __construct($request_class = null)
    {
        $this->request_class = $request_class == null ? 'Request' : $request_class;
    }
    
    public function get($uri)
    {
        $url = Settings::$url_root . $uri;
        $request = \Httpful\Request::get($url);
        return $this->_op($request);
    }    
    
    public function post($uri, $payload)
    {
        $url = Settings::$url_root . $uri;
        $request = Request::post($url, $payload, 'json');
        return $this->_op($request);
    }
    
    public function put($uri, $payload)
    {
        $url = Settings::$url_root . $uri;
        $request = Request::put($url, $payload, 'json');
        return $this->_op($request);
    }
    
    public function delete($uri)
    {
        $url = Settings::$url_root . $uri;
        $request = Request::delete($url);
        return $this->_op($request);
    }
    
    private function _op($request)
    {
        $user_agent = 'balanced-php/' . Settings::VERSION;
        $request->headers['User-Agent'] = $user_agent; 
        if (Settings::$api_key != null)
            $request = $request->authenticateWith(Settings::$api_key , '');
        $request->expects('json');
        $response = $request->sendIt();
        if ($response->hasErrors() || $response->code == 300)
            throw new HTTPError($response);
        return $response; 
    }
}
