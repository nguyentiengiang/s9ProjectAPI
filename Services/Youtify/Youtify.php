<?php

/*
 * Slim Auto Loader
 */

\Slim\Slim::registerAutoloader();

/*
 * Youtify.com
 */

class Youtify {

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
        $this->app->get('/json/GetCategory', array($this, 'getCategories'));
        $this->app->get('/json/GetPlaylistsByCategory', array($this, 'getPlaylistsByCategoryId'));
        $this->app->get('/json/GetVideoByPlaylist', array($this, 'getVideosByPlaylistId'));
        $this->app->run();
    }

    public function index() {
        $status = 200;
        $body = array("result" => array("message" => "Wellcome to Youtify APIs"));
        $headers = array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\ResponeHelper::result($this->app->request, $this->app->response, new s9Helper\RequestResult($status, $headers, $body));
    }

    public function getCategories() {
        $status = 200;
        $headers = array();
        $body = array();
        try {
            ORM::configure($this->ORMConfig);
            $categories = ORM::for_table("Category")->select_many(array("cateId" => "id", "cateName" => "name"))->find_array();
            if (count($categories) > 0) {
                $body = array("result" => $categories);
            } else {
                $status = 404;
                $body = array("result" => array('message' => 'Get Categories Fail!'));
            }
        } catch (Exception $e) {
            $status = 500;
            $headers += array("Connection" => "close", "Warning" => "Server execute in error");
            $body = array("result" => array("message" => "You have a trouble request", "error" => $e->getMessage()));
            \MyFile\Log::write("File:" . $e->getFile() . PHP_EOL . "Message:" . $e->getMessage() . PHP_EOL . "Line:" . $e->getLine() . PHP_EOL . "Code:" . $e->getCode() . PHP_EOL . "Trace:" . $e->getTraceAsString(), "_ResultRequestException", APP_NAME);
        }
        $headers += array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\ResponeHelper::result($this->app->request, $this->app->response, new s9Helper\RequestResult($status, $headers, $body));
    }

    public function getPlaylistsByCategoryId() {
        $status = 200;
        $headers = array();
        $body = array();
        try {
            $id = intval($this->app->request->get("id"));
            ORM::configure($this->ORMConfig);
            $playlists = ORM::for_table("Playlist")
                            ->select_many(array("pId" => "id", "name", "thumb" => "img"))
                            ->where_equal(array("is_hide" => 0, "category_id" => $id))->find_array();
            if (count($playlists) > 0) {
                $body = array("result" => $playlists);
            } else {
                $status = 404;
                $body = array("result" => array('message' => 'Get Playlists Fail!'));
            }
        } catch (Exception $e) {
            $status = 500;
            $headers += array("Connection" => "close", "Warning" => "Server execute in error");
            $body = array("result" => array("message" => "You have a trouble request", "error" => $e->getMessage()));
            \MyFile\Log::write("File:" . $e->getFile() . PHP_EOL . "Message:" . $e->getMessage() . PHP_EOL . "Line:" . $e->getLine() . PHP_EOL . "Code:" . $e->getCode() . PHP_EOL . "Trace:" . $e->getTraceAsString(), "_ResultRequestException", APP_NAME);
        }
        $headers += array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\ResponeHelper::result($this->app->request, $this->app->response, new s9Helper\RequestResult($status, $headers, $body));
    }

    public function getVideosByPlaylistId() {
        $status = 200;
        $headers = array();
        $body = array();
        try {
            $id = intval($this->app->request->get("id"));
            ORM::configure($this->ORMConfig);
            $videos = ORM::for_table("Video")->select_many("name", "youtube_id")->where_equal("playlist_id", $id)->find_array();
            if (count($videos) > 0) {
                $playlist = ORM::for_table("Playlist")->select_many("intro")->find_one($id);
                $arrVideos = array();
                foreach ($videos as $video) {
                    array_push($arrVideos, array("name" => $video["name"], "youtubeId" => base64_encode($video["youtube_id"])));
                }
                $body = array(
                    "result" => array("playlistId" => $id, "intro" => $playlist['intro'], "video" => $arrVideos)
                );
                unset($playlist);unset($videos);unset($arrVideos);
            } else {
                $status = 404;
                $body = array("result" => array("message" => "Get Videos Fail!"));
            }
        } catch (Exception $e) {
            $status = 500;
            $headers += array("Connection" => "close", "Warning" => "Server execute in error");
            $body = array("result" => array("message" => "You have a trouble request", "error" => $e->getMessage()));
            \MyFile\Log::write("File:" . $e->getFile() . PHP_EOL . "Message:" . $e->getMessage() . PHP_EOL . "Line:" . $e->getLine() . PHP_EOL . "Code:" . $e->getCode() . PHP_EOL . "Trace:" . $e->getTraceAsString(), "_ResultRequestException", APP_NAME);
        }
        $headers += array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\ResponeHelper::result($this->app->request, $this->app->response, new s9Helper\RequestResult($status, $headers, $body));
    }

}
