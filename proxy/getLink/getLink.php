<?php

\Slim\Slim::registerAutoloader();

/*
 * Chia-anime
 */

class getLinkMp4 {

    public function __construct() {
        $this->app = new \Slim\Slim(array(
            'debug' => true,
            'mode' => 'development',
//            'debug' => false,
//            'mode' => 'production',
        ));
    }

    public function enable() {
        $this->app->get('/', array($this, 'index'));
        $this->app->get('/MovieTube', array($this, 'GetLinkMovieTube'));
        $this->app->get('/Watch33TV', array($this, 'GetLinkWatch33TV'));
        $this->app->get('/YouTube', array($this, 'GetLinkYouTube'));
        $this->app->post('/YouTube', array($this, 'GetLinkYouTube'));
        $this->app->get('/GoogleDrive', array($this, 'GetLinkGoogleDrive'));
        $this->app->post('/GoogleDrive', array($this, 'GetLinkGoogleDrive'));
        $this->app->run();
    }

    public function index() {
        
    }

    public function GetLinkMovieTube() {
        $result = null;
        try {
            $id = $this->app->request()->get('id');
            if (!empty($id)) {
                $MovieTubeCC = new MovieTubeCC();
                $result = array("link" => $MovieTubeCC->requestUrlParse(trim($id)));
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
            $id = $this->app->request()->get('id');
            $content = $this->app->request()->post('content');
            if (!empty($id)) {
                $contents = YouTube::requestContent(trim($id));
                $result = YouTube::processContent($contents);
            } else if (!empty($content)) {
                $result = YouTube2::cleanLinkV3($content);
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
            $id = $this->app->request()->get('id');
            $content = $this->app->request()->post('content');
            if (!empty($id)) {
                $result = GoogleDrive::requestFromServer($id);
            } else if (!empty($content)) {
                $result = GoogleDrive::cleanLinkV31($content);
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

}

?>
