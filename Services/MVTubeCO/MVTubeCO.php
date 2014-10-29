<?php

Slim\Slim::registerAutoloader();

/*
 * MVTubeCO 
 */

class MVTubeCO {

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
        $this->app->map('/json/GetPlaylist', array($this, 'GetPlaylists'))->via("GET", "POST");
        $this->app->get('/json/GetYTPlaylist', array($this, 'GetYTPlaylists'));
        $this->app->get('/json/GetSong', array($this, 'GetSongByPlaylist500'));
        $this->app->get('/json/GetSongWP', array($this, 'GetSongByPlaylist500'));
        $this->app->run();
    }

    public function index() {
        $status = 200;
        $body = array("result" => array("message" => "Wellcome to " . APP_NAME));
        $headers = array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body);
    }

    public function GetPlaylists() {
        $status = 200;
        $headers = array();
        $body = array();
        try {
            ORM::configure($this->ORMConfig);
            $charts = ORM::for_table("Playlist")->select_many(array("id", "name", "image", "imageFlat"))->where_equal(array("isHide" => 0, "typePlaylist" => 1))->find_array();
            $languages = ORM::for_table("Playlist")->select_many(array("id", "name", "image", "imageFlat"))->where_equal(array("isHide" => 0, "typePlaylist" => 2))->find_array();
            $artists = ORM::for_table("Playlist")->select_many(array("id", "name", "image", "imageFlat"))->where_equal(array("isHide" => 0, "typePlaylist" => 3))->find_array();
            $albums = ORM::for_table("Playlist")->select_many(array("id", "name", "image", "imageFlat"))->where_equal(array("isHide" => 0, "typePlaylist" => 4))->find_array();
            $genres = ORM::for_table("Playlist")->select_many(array("id", "name", "image", "imageFlat"))->where_equal(array("isHide" => 0, "typePlaylist" => 5))->find_array();
            $body = array("result" => array("chart" => $charts) + array("language" => $languages) +
                array("artist" => $artists) + array("album" => $albums) +
                array("genre" => $genres)
            );
        } catch (Exception $e) {
            $status = 500;
            $headers += array("Connection" => "close", "Warning" => "Server execute in error");
            $body = array("result" => array("message" => "You have a trouble request", "error" => $e->getMessage()));
            s9Helper\MyFile\Log::write("File:" . $e->getFile() . PHP_EOL . "Message:" . $e->getMessage() . PHP_EOL . "Line:" . $e->getLine() . PHP_EOL . "Code:" . $e->getCode() . PHP_EOL . "Trace:" . $e->getTraceAsString(), ".ExecuteException", APP_NAME);
        }
        $headers += array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body, "app");
    }

    public function GetYTPlaylists() {
        $status = 200;
        $headers = array();
        $body = array();
        try {
            ORM::configure($this->ORMConfig);
            $popularPlaylists = ORM::for_table("PlaylistYT")->select_many(array("name", "imageFlat" => "imgFlat"))->where_equal(array("typePlaylist" => 1))->find_array();
            $artists = ORM::for_table("PlaylistYT")->select_many(array("name", "imageFlat" => "imgFlat"))->where_equal(array("typePlaylist" => 2))->find_array();
            $playlists = ORM::for_table("PlaylistYT")->select_many(array("name", "imageFlat" => "imgFlat"))->where_equal(array("typePlaylist" => 3))->find_array();

            $body = array("result" => array("popularPlaylist" => $popularPlaylists) + array("artist" => $artists) +
                array("playlist" => $playlists));
        } catch (Exception $e) {
            $status = 500;
            $headers += array("Connection" => "close", "Warning" => "Server execute in error");
            $body = array("result" => array("message" => "You have a trouble request", "error" => $e->getMessage()));
            s9Helper\MyFile\Log::write("File:" . $e->getFile() . PHP_EOL . "Message:" . $e->getMessage() . PHP_EOL . "Line:" . $e->getLine() . PHP_EOL . "Code:" . $e->getCode() . PHP_EOL . "Trace:" . $e->getTraceAsString(), ".ExecuteException", APP_NAME);
        }
        $headers += array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body, "app");
    }

    public function GetSongByPlaylist() {
        $status = 200;
        $headers = array();
        $body = array();
        try {
            $pId = $this->app->request()->get('id');
            ORM::configure($this->ORMConfig);
            $songs = ORM::for_table("Song")->select_many(array("name", "singer", "image", "youtubeId", "author" => "uploader", "duration", "viewCount", "rating"))->where_equal(array("isHide" => 0, "playlistId" => $pId))->find_array();
            
            if (count($songs)) {
                $body = array("result" => array("song" => $songs));
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
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body, "app");
    }

    public function GetSongByPlaylist500() {
        $status = 200;
        $headers = array();
        $body = array();
        try {
            $pId = $this->app->request()->get('id');
            ORM::configure($this->ORMConfig);
            $songs = ORM::for_table("Song")->select_many(array("name", "singer", "image", "youtubeId", "author" => "uploader", "duration", "viewCount", "rating"))->where_equal(array("isHide" => 0, "playlistId" => $pId))->limit(500)->find_array();
            if (count($songs)) {
                $body = array("result" => array("song" => $songs));
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
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body, "app");
    }
    
}
