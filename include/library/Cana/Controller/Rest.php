<?php

class Cana_Controller_Rest extends Cana_Controller {
    public $request;

    public function __construct() {
        $this->request = [];

        if (method_exists(get_parent_class($this),'__construct')) {
            parent::__construct();
        }

        if (isset($_SERVER['REQUEST_METHOD'])) {
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'PUT':
                case 'DELETE':
                    if ($_SERVER['CONTENT_TYPE'] === 'application/x-www-form-urlencoded') {
                        parse_str($this->getContent(), $this->request);

                    } elseif ($_SERVER['CONTENT_TYPE'] === 'application/json') {
                        $content = $this->getContent();
                        $request = json_decode($content,'array');
                        if (!$request) {
                            $this->request = false;
                        } else {
                            $this->request = $request;
                        }
                    }
                    break;

                case 'GET':
                    if ($_SERVER['CONTENT_TYPE'] === 'application/x-www-form-urlencoded' || !$_SERVER['CONTENT_TYPE']) {
                        $this->request = $_GET;
                    } elseif ($_SERVER['CONTENT_TYPE'] === 'application/json') {
                        $this->request = $this->getRawRequest();
                    }
                    break;

                case 'POST':
                    if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
                        $this->request = json_decode($this->getContent(), 'array');
                    /* Found a case where the CONTENT_TYPE was 'application/x-www-form-urlencoded; charset=UTF-8'
                     *
                     * @todo Is there any case where we do not set the $request to $_POST nor the json?
                     * If not, there there should be OK to use the fallback scenario
                     */
                    // } elseif ($_SERVER['CONTENT_TYPE'] === 'application/x-www-form-urlencoded') {
                    } else  {
                        $this->request = $_POST;
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
}