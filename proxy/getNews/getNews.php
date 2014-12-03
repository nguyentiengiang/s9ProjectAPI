<?php

Slim\Slim::registerAutoloader();

class Lyric {

    public function __construct() {
        $this->app = new \Slim\Slim(array(
            'debug' => true,
            'mode' => 'development',
        ));
    }

    public function enable() {
        $this->app->get('/', array($this, 'index'));
        $this->app->get('/ListLyric', array($this, 'GetListLyrics'));
        $this->app->get('/DetailsLyric', array($this, 'GetDetailsLyric'));
        $this->app->run();
    }

    public function index() {
        
    }

    public function GetListLyrics() {
        $result = null;
        try {
            $track = $this->app->request()->get('track');
            if (!empty($track)) {
                $result = SongLyricsDotCom::requestListLyric(trim($track));
            }
            if (empty($result)) {
                $this->app->response()->status(404);
                $result = array("status" => "doesn't exists", "message" => "Get List Lyrics Fail!");
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

    public function GetDetailsLyric() {
        $result = null;
        try {
            $url = $this->app->request()->get('url');
            if (!empty($url)) {
                $result = SongLyricsDotCom::requestLyric(trim($url));
            }
            if (is_null($result)) {
                $this->app->response()->status(404);
                $result = array("status" => "doesn't exists", "message" => "Get Lyrics Fail!");
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
