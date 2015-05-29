<?php

class Cana_Controller_Rest extends Cana_Controller {
    public $request;

    public function __construct() {
        $this->request = [];

        if (method_exists(get_parent_class($this),'__construct')) {
            parent::__construct();
        }

        if (isset($_SERVER['REQUEST_METHOD'])) {

	        $contentType = explode(';',trim($_SERVER['CONTENT_TYPE']));
	        $contentType = trim($contentType[0]);

            switch ($_SERVER['REQUEST_METHOD']) {
                case 'PUT':
                case 'DELETE':
                    if ($contentType === 'application/x-www-form-urlencoded') {
                        parse_str($this->getContent(), $this->request);

                    } elseif ($contentType === 'application/json') {
                        $content = $this->getContent();

                        $request = json_decode($content,'array');
                        if (!$request) {
                            $this->request = false;
                        } else {
                            $this->request = $request;
                        }
                    } else {
	                    $content = $this->getContent();
	                    parse_str($content, $this->request);
                    }
                    break;

                case 'GET':
                    if ($contentType === 'application/x-www-form-urlencoded' || !$contentType) {
                        $this->request = $_GET;
                    } elseif ($contentType === 'application/json') {
                        $this->request = $this->getRawRequest();
                    }
                    break;

                case 'POST':
                    if ($contentType === 'application/json') {
                        $this->request = json_decode($this->getContent(), 'array');
                    /* Found a case where the CONTENT_TYPE was 'application/x-www-form-urlencoded; charset=UTF-8'
                     *
                     * @todo Is there any case where we do not set the $request to $_POST nor the json?
                     * If not, there there should be OK to use the fallback scenario
                     */
                    } elseif ($contentType === 'application/x-www-form-urlencoded') {
                        $this->request = $_POST;
                    } else {
	                    $content = $this->getContent();
	                    parse_str($content, $this->request);
                    }
                    break;
            }
        }
    }

    public function request() {
        return $this->request;
    }

    private function getContent() {
        if (!isset($this->_content)) {
            if (strlen(trim($this->_content = file_get_contents('php://input'))) === 0) {
                $this->_content = false;
            }
        }
        return $this->_content;
    }

    private function getRawRequest() {
        if (!isset($this->_rawRequest)) {

            $request = trim($_SERVER['REQUEST_URI']);
            $request = substr($request,strpos($request,'?')+1);
            $request = urldecode($request);
            $request = json_decode($request,'array');

            if (!$request) {
                $this->_rawRequest = false;
            } else {
                $this->_rawRequest = $request;
            }
        }
        return $this->_rawRequest;
    }

    public function method() {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }
	
	public function error($id = null, $exit = true) {

		switch ($id) {
			case 404:
				header('HTTP/1.0 404 Not Found');
				break;

			case 401:
				header('HTTP/1.1 401 Unauthorized');
				break;
			
			case 406:
				header('HTTP/1.0 406 Not Acceptable');
				break;
		}

		if ($exit) {
			exit;
		}
	}
}