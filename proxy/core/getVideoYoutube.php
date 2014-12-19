<?php

/**
 * Youtube Get Link API
 * @author Tien Giang <nguyentiengiang@outlook.com>
 */
Slim\Slim::registerAutoloader();

class getVideoYoutube {

    public function __construct() {
        // Start Slim Config
        $arrSlimConfig = array();
        if (MODE_APP == "RELEASE") {
            $arrSlimConfig = array('debug' => false, 'mode' => 'production');
        } else {
            $arrSlimConfig = array('debug' => true, 'mode' => 'development');
        }
        $this->app = new \Slim\Slim($arrSlimConfig);
        // End Slim Config
    }

    public function runable() {
        $this->app->get('/:v', array($this, 'GetFromYoutube'));
        $this->app->run();
    }

    public function index() {
        $status = 200;
        $body = array("result" => array("message" => "This's private API! Do not enter here!"));
        $headers = array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body);
    }

    public function GetFromYoutube($v) {
        $status = 200;
        $headers = array();
        $body = array();
        try {
            $link = Youtube::requestContent($v);
            if (!empty($link)) {
                $body = array("result" => array("link" => $link));
            } else {
                $status = 404;
                $body = array("result" => array("status" => "doesn't exists", "message" => "Get Stream link Fail!", "code" => 0));
            }
        } catch (Exception $e) {
            $status = 500;
            $headers += array("Connection" => "close", "Warning" => "Server execute in error");
            $body = array("result" => array("message" => "You have a trouble request", "error" => $e->getMessage()));
            s9Helper\MyFile\Log::write("File:" . $e->getFile() . PHP_EOL . "Message:" . $e->getMessage() . PHP_EOL . "Line:" . $e->getLine() . PHP_EOL . "Code:" . $e->getCode() . PHP_EOL . "Trace:" . $e->getTraceAsString(), ".ExecuteException", APP_NAME);
        }
        $headers += array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body, "stream");
    }

    private function ValidateData($arrayData, $ruleFnc) {
        $return = null;
        Valitron\Validator::langDir(LIB_ROOT . 'Valitron/lang');
        Valitron\Validator::lang('vi');
        $v = new Valitron\Validator($arrayData);
        if ($ruleFnc === 0) {
            $v->rule('required', ['content']);
        } else if ($ruleFnc === 1) {
            $v->rule('required', ['id']);
        }
        if ($v->validate()) {
            $return = null;
        } else {
            $return = $v->errors();
        }
        return $return;
    }

}
