<?php

\Slim\Slim::registerAutoloader();

/*
 * Chia-anime
 */

class getStreamAnime {

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

    public function enable() {
        $this->app->get('/', array($this, 'index'));
        $this->app->post('/stream', array($this, 'GetStreamAnime'));
        $this->app->run();
    }

    public function index() {
        $status = 200;
        $body = array("result" => array("message" => "This's private problem! Do not enter here!"));
        $headers = array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body);
    }

    public function GetStreamAnime() {
        $status = 200;
        $headers = array();
        $body = array();
        try {
            $post = s9Helper\HandlingRequest\MyRequest::cleanPOST($this->app->request());
            $url = $post['url'];
            $po = intval($post['position']) ? intval($post['position']) : 0;
            if (!empty($url)) {
                $link = ChiaAnime::getINFOLinkMovieSubFilm($url, $po);
                if (!empty($link)) {
                    $body = array('result' => array("link" => $link));
                } else {
                    $status = 404;
                    $body = array("result" => array("status" => "doesn't exists", "message" => "Get Stream link Fail!", "code" => 0));
                }    
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

}
