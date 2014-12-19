<?php

Slim\Slim::registerAutoloader();

/*
 * MVYouTube
 */

class MVYoutube {

    public function __construct($dbHost, $dbName, $dbUser, $dbPass) {
        // Start data config
        $this->dbHost = $dbHost;
        $this->dbName = $dbName;
        $this->dbUser = $dbUser;
        $this->dbPass = $dbPass;

        $this->ORMConfig = array(
            'connection_string' => 'mysql:host=' . $this->dbHost . ';dbname=' . $this->dbName,
            'username' => $this->dbUser,
            'password' => $this->dbPass,
            'driver_options' => array(
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            ),
            'return_result_sets' => false
        );
        // End data config
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
        $this->app->get('/GetCountry', array($this, 'GetCountry'));
        $this->app->get('/GetGenres', array($this, 'GetGenres'));
        $this->app->get('/GetPopular', array($this, 'GetPopular'));
        $this->app->get('/GetTopGenres', array($this, 'GetTopGenres'));
        $this->app->get('/GetVideos', array($this, 'GetVideoByPlaylist'));
        $this->app->run();
    }

    public function index() {
        $status = 200;
        $body = array("result" => array("message" => "Wellcome to " . APP_NAME. " APIs."));
        $headers = array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body);
    }

    public function GetCountry() {
        $status = 200;
        $headers = array();
        $body = array();
        try {
            ORM::configure($this->ORMConfig);
            $urlApi = s9Helper\URL::getURL();
            $country = ORM::for_table("Playlist")->select_many(array("id", "name", "image"))->where_equal(array("isHide" => 0, "type" => 1))->find_array();
            $arrCountry = array();
            foreach ($country as $c) {
                array_push($arrCountry, array("id" => $c['id'], "name" => $c['name'], 'image' => $urlApi . $c['image']));
            }
            $body = array("result" => array("country" => $arrCountry));
        } catch (Exception $e) {
            $status = 500;
            $headers += array("Connection" => "close", "Warning" => "Server execute in error");
            $body = array("result" => array("message" => "You have a trouble request", "error" => $e->getMessage()));
            s9Helper\MyFile\Log::write("File:" . $e->getFile() . PHP_EOL . "Message:" . $e->getMessage() . PHP_EOL . "Line:" . $e->getLine() . PHP_EOL . "Code:" . $e->getCode() . PHP_EOL . "Trace:" . $e->getTraceAsString(), ".ExecuteException", APP_NAME);
        }
        $headers += array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body, "playlist", null, true);
    }

    public function GetGenres() {
        $status = 200;
        $headers = array();
        $body = array();
        try {
            ORM::configure($this->ORMConfig);
            $genres = ORM::for_table("Playlist")->select_many(array("id", "name", "image", "author", "publishedDate", "totalVideos"))->where_equal(array("isHide" => 0, "type" => 2))->find_array();
            $arrGenres = array();
            foreach ($genres as $g) {
                array_push($arrGenres, array("id" => $g['id'], "name" => $g['name'], "image" => $g['image'], "author" => $g['author'], "publishedDate" => date_format(date_create($g['publishedDate']), 'd/m/Y'), "totalVideos" => intval($g['totalVideos'])));
            }
            $body = array("result" => array("genre" => $arrGenres));
        } catch (Exception $e) {
            $status = 500;
            $headers += array("Connection" => "close", "Warning" => "Server execute in error");
            $body = array("result" => array("message" => "You have a trouble request", "error" => $e->getMessage()));
            s9Helper\MyFile\Log::write("File:" . $e->getFile() . PHP_EOL . "Message:" . $e->getMessage() . PHP_EOL . "Line:" . $e->getLine() . PHP_EOL . "Code:" . $e->getCode() . PHP_EOL . "Trace:" . $e->getTraceAsString(), ".ExecuteException", APP_NAME);
        }
        $headers += array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body, "playlist", null, true);
    }
    
    public function GetPopular() {
        $status = 200;
        $headers = array();
        $body = array();
        try {
            ORM::configure($this->ORMConfig);
            $popular = ORM::for_table("Playlist")->select_many(array("id", "name", "image", "author", "publishedDate", "totalVideos"))->where_equal(array("isHide" => 0, "type" => 3))->find_array();
            $arrPopular = array();
            foreach ($popular as $p) {
                array_push($arrPopular, array("id" => $p['id'], "name" => $p['name'], "image" => $p['image'], "author" => $p['author'], "publishedDate" => date_format(date_create($p['publishedDate']), 'd/m/Y'), "totalVideos" => intval($p['totalVideos'])));
            }
            $body = array("result" => array("popular" => $arrPopular));
        } catch (Exception $e) {
            $status = 500;
            $headers += array("Connection" => "close", "Warning" => "Server execute in error");
            $body = array("result" => array("message" => "You have a trouble request", "error" => $e->getMessage()));
            s9Helper\MyFile\Log::write("File:" . $e->getFile() . PHP_EOL . "Message:" . $e->getMessage() . PHP_EOL . "Line:" . $e->getLine() . PHP_EOL . "Code:" . $e->getCode() . PHP_EOL . "Trace:" . $e->getTraceAsString(), ".ExecuteException", APP_NAME);
        }
        $headers += array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body, "playlist", null, true);
    }
    
    public function GetTopGenres() {
        $status = 200;
        $headers = array();
        $body = array();
        try {
            ORM::configure($this->ORMConfig);
            $topGenre = ORM::for_table("Playlist")->select_many(array("id", "name", "image", "author", "publishedDate", "totalVideos", "parent"))->where_equal(array("isHide" => 0, "type" => 4))->find_array();
            $arrTopGenre = array();
            foreach ($topGenre as $tg) {
                array_push($arrTopGenre, array("id" => $tg['id'], "name" => $tg['name'], "image" => $tg['image'], "author" => $tg['author'], "publishedDate" => date_format(date_create($tg['publishedDate']), 'd/m/Y'), "totalVideos" => intval($tg['totalVideos']), "parent" => $tg['parent']));
            }
            $body = array("result" => array("topGenres" => $arrTopGenre));
        } catch (Exception $e) {
            $status = 500;
            $headers += array("Connection" => "close", "Warning" => "Server execute in error");
            $body = array("result" => array("message" => "You have a trouble request", "error" => $e->getMessage()));
            s9Helper\MyFile\Log::write("File:" . $e->getFile() . PHP_EOL . "Message:" . $e->getMessage() . PHP_EOL . "Line:" . $e->getLine() . PHP_EOL . "Code:" . $e->getCode() . PHP_EOL . "Trace:" . $e->getTraceAsString(), ".ExecuteException", APP_NAME);
        }
        $headers += array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body, "playlist", null, true);
    }
    
    public function GetVideoByPlaylist() {
        $status = 200;
        $headers = array();
        $body = array();
        try {
            $pId = $this->app->request()->get('id');

            ORM::configure($this->ORMConfig);
            $songs = ORM::for_table("Video")->select_many(array("name", "image", "youtubeId", "author", "duration", "viewCount", "rating", "like", "dislike"))
                    ->where_equal(array("isHide" => 0, "playlistId" => $pId))->find_array();
            
            if (count($songs)) {
                $body = array("result" => array("videos" => $songs));
            } else {
                $this->app->response()->status(404);
                $body = array("result" => array('message' => 'Get Songs Fail!'));
            }
        } catch (Exception $e) {
            $status = 500;
            $headers += array("Connection" => "close", "Warning" => "Server execute in error");
            $body = array("result" => array("message" => "You have a trouble request", "error" => $e->getMessage()));
            s9Helper\MyFile\Log::write("File:" . $e->getFile() . PHP_EOL . "Message:" . $e->getMessage() . PHP_EOL . "Line:" . $e->getLine() . PHP_EOL . "Code:" . $e->getCode() . PHP_EOL . "Trace:" . $e->getTraceAsString(), ".ExecuteException", APP_NAME);
        }
        $headers += array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body, "songs", null, true);
    }
    
}
