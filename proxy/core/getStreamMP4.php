<?php

\Slim\Slim::registerAutoloader();

/*
 * Chia-anime
 */

class getStreamLinkMP4 {

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
        $this->app->get('/MovieTube', array($this, 'GetLinkMovieTube'));
        $this->app->get('/Watch33TV', array($this, 'GetLinkWatch33TV'));
        $this->app->map('/YouTube', array($this, 'GetLinkYouTube'))->via('GET', 'POST');
        $this->app->map('/stream/YouTube', array($this, 'GetStreamYouTube'))->via('GET', 'POST');
        $this->app->map('/new/YouTube', array($this, 'GetLinkYouTubeNew'))->via('GET', 'POST');
        $this->app->map('/iOSnew/YouTube', array($this, 'GetLinkYouTubeNew'))->via('GET', 'POST');
        $this->app->map('/ios/YouTube', array($this, 'GetLinkYouTube'))->via('GET', 'POST');
        $this->app->map('/GoogleDrive', array($this, 'GetLinkGoogleDrive'))->via('GET', 'POST');
        $this->app->run();
    }

    public function index() {
        $status = 200;
        $body = array("result" => array("message" => "This's private problem! Do not enter here!"));
        $headers = array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body);
    }

    public function GetLinkMovieTube() {
        $status = 200;
        $headers = array();
        $body = array();
        try {
            $GET = s9Helper\HandlingRequest\MyRequest::cleanGET($this->app->request());
            $valid = self::ValidateData($GET, 1);
            if (empty($valid)) {
                $link = MovieTubeCC::requestUrlParse(trim($GET['id']));
                if (!empty($link)) {
                    $body = array("result" => array("link" => $link));
                } else {
                    $status = 404;
                    $body = array("result" => array("status" => "doesn't exists", "message" => "Get Stream link Fail!", "code" => 0));
                }
            } else {
                $status = 404;
                $body = array("result" => array("status" => $valid, "message" => "Get Stream link Fail!", "code" => 0));
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

    public function GetLinkWatch33TV() {
        $result = null;
        try {
            $id = $this->app->request()->get('id');
            $ep = $this->app->request()->get('ep');
            $p = $this->app->request()->get('part');
            $episode = isset($ep) ? sprintf("%02s", $ep) : sprintf("%02s", 1);
            $part = isset($part) ? sprintf("%02s", $part) : sprintf("%02s", 1);
            if (!empty($id)) {
                $watch33tv = new Watch33TV();
                $result = array("link" => $watch33tv->requestUrlParse(trim($id), $episode, $part));
            }
            if (is_null($result)) {
                $this->app->response()->status(404);
                $result = array("status" => "doesn't exists", "message" => "Get Stream link Fail!");
            }
        } catch (ResourceNotFoundException $e) {
            $this->app->response()->status(404);
            $result = array('message' => 'Resource Not Found!');
        } catch (Exception $e) {
            $this->app->response()->status(400);
            $this->app->response()->header('X-Status-Reason', $e->getMessage());
        }
        $this->app->response()->header('X-Powered-By', 'ongteu');
        $mediaType = $this->app->request()->getMediaType();
        if ($mediaType == 'application/xml') {
            $this->app->response()->header('Content-Type', 'application/xml');
            echo \s9ProjectHelper\ArrayToXML::toXml($result, 'app');
        } else {
            $this->app->response->headers->set('Content-Type', 'application/json');
            echo json_encode($result, JSON_NUMERIC_CHECK | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
        }
    }

    public function GetLinkYouTube() {
        $result = null;
        try {
            $method = strtoupper($this->app->request->getMethod());
            switch ($method) {
                case "GET":
                    $id = $this->app->request()->get('id');
                    if (!empty($id)) {
                        $contents = YouTube::requestContent(trim($id));
                        $result = YouTube::processContent($contents);
                    }
                    break;
                case "POST":
                    $content = $this->app->request()->post('content');
                    if (!empty($content)) {
                        $result = YouTube2::cleanLinkV3($content);
                    }
                    break;
                default:
                    $result = null;
                    break;
            }
            if (is_null($result)) {
                $this->app->response()->status(404);
                $result = array("status" => "doesn't exists", "message" => "Get Stream link Fail!");
            }
        } catch (ResourceNotFoundException $e) {
            $this->app->response()->status(404);
            $result = array('message' => 'Resource Not Found!');
        } catch (Exception $e) {
            $this->app->response()->status(400);
            $this->app->response()->header('X-Status-Reason', $e->getMessage());
        }
        $this->app->response()->header('X-Powered-By', 'ongteu');
        $mediaType = $this->app->request()->getMediaType();
        if ($mediaType == 'application/xml') {
            $this->app->response()->header('Content-Type', 'application/xml');
            echo \s9ProjectHelper\ArrayToXML::toXml($result, 'app');
        } else {
            $this->app->response->headers->set('Content-Type', 'application/json');
            echo json_encode($result, JSON_NUMERIC_CHECK | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
        }
    }

    public function GetLinkYouTubeNew() {
        $result = null;
        try {
            $method = strtoupper($this->app->request->getMethod());
            switch ($method) {
                case "GET":
                    $id = $this->app->request()->get('id');
                    if (!empty($id)) {
                        $contents = YouTube::requestContent(trim($id));
                        $result = YouTube::processContent($contents);
                    }
                    break;
                case "POST":
                    $content = $this->app->request()->post('content');
                    if (!empty($content)) {
                        $result = YouTube::processContent($content);
                    }
                    break;
                default:
                    $result = null;
                    break;
            }
            if (is_null($result)) {
                $this->app->response()->status(404);
                $result = array("status" => "doesn't exists", "message" => "Get Stream link Fail!");
            }
        } catch (ResourceNotFoundException $e) {
            $this->app->response()->status(404);
            $result = array('message' => 'Resource Not Found!');
        } catch (Exception $e) {
            $this->app->response()->status(400);
            $this->app->response()->header('X-Status-Reason', $e->getMessage());
        }
        $this->app->response()->header('X-Powered-By', 'ongteu');
        $mediaType = $this->app->request()->getMediaType();
        if ($mediaType == 'application/xml') {
            $this->app->response()->header('Content-Type', 'application/xml');
            echo \s9ProjectHelper\ArrayToXML::toXml($result, 'app');
        } else {
            $this->app->response->headers->set('Content-Type', 'application/json');
            echo json_encode($result, JSON_NUMERIC_CHECK | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
        }
    }

    public function GetStreamYouTube() {
        $result = null;
        try {
            $method = strtoupper($this->app->request->getMethod());
            switch ($method) {
                case "GET":
                    $id = $this->app->request()->get('id');
                    if (!empty($id)) {
                        $contents = YouTube3::requestContent(trim($id));
                        $result = YouTube3::processContent($contents);
                    }
                    break;
                case "POST":
                    $content = $this->app->request()->post('content');
                    if (!empty($content)) {
                        $result = YouTube2::cleanLinkV3($content);
                    }
                    break;
                default:
                    $result = null;
                    break;
            }
            if (is_null($result)) {
                $this->app->response()->status(404);
                $result = array("status" => "doesn't exists", "message" => "Get Stream link Fail!");
            }
        } catch (ResourceNotFoundException $e) {
            $this->app->response()->status(404);
            $result = array('message' => 'Resource Not Found!');
        } catch (Exception $e) {
            $this->app->response()->status(400);
            $this->app->response()->header('X-Status-Reason', $e->getMessage());
        }
        $this->app->response()->header('X-Powered-By', 'ongteu');
        $mediaType = $this->app->request()->getMediaType();
        if ($mediaType == 'application/xml') {
            $this->app->response()->header('Content-Type', 'application/xml');
            echo \s9ProjectHelper\ArrayToXML::toXml($result, 'app');
        } else {
            $this->app->response->headers->set('Content-Type', 'application/json');
            echo json_encode($result, JSON_NUMERIC_CHECK | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
        }
    }
    
    public function GetLinkGoogleDrive() {
        $result = null;
        try {
            $method = strtoupper($this->app->request->getMethod());
            switch ($method) {
                case "GET":
                    $id = $this->app->request()->get('id');
                    if (!empty($id)) {
                        $result = GoogleDrive::requestFromServer($id);
                    }
                    break;
                case "POST":
                    $content = $this->app->request()->params('content');
                    if (!empty($content)) {
                        $result = GoogleDrive::cleanLinkV31($content);
                    }
                default:
                    $result = null;
                    break;
            }
            if (is_null($result)) {
                $this->app->response()->status(404);
                $result = array("status" => "doesn't exists", "message" => "Get Stream link Fail!");
            }
        } catch (ResourceNotFoundException $e) {
            $this->app->response()->status(404);
            $result = array('message' => 'Resource Not Found!');
        } catch (Exception $e) {
            $this->app->response()->status(400);
            $this->app->response()->header('X-Status-Reason', $e->getMessage());
        }
        $this->app->response()->header('X-Powered-By', 'ongteu');
        $mediaType = $this->app->request()->getMediaType();
        if ($mediaType == 'application/xml') {
            $this->app->response()->header('Content-Type', 'application/xml');
            echo \s9ProjectHelper\ArrayToXML::toXml($result, 'app');
        } else {
            $this->app->response->headers->set('Content-Type', 'application/json');
            echo json_encode($result, JSON_NUMERIC_CHECK | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
        }
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

?>
