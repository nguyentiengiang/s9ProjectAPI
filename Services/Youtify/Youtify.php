<?php

\Slim\Slim::registerAutoloader();

/*
 * 
 */

class Youtify {

    public function __construct($dbHost, $dbName, $dbUser, $dbPass) {
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

        $this->app = new \Slim\Slim(array(
            'debug' => true,
            'mode' => 'development',
        ));
    }

    public function enable() {
        $this->app->get('/', array($this, 'index'));
        $this->app->get('/json/GetCategory', array($this, 'getCategories'));
        $this->app->get('/json/GetPlaylistsByCategory', array($this, 'getPlaylistsByCategoryId'));
        $this->app->get('/json/GetVideoByPlaylist', array($this, 'getVideosByPlaylistId'));
        $this->app->run();
    }

    function dbConnect($cache = null) {
        $pdo = new \PDO('mysql:host=' . $this->dbHost . ';dbname=' . $this->dbName, $this->dbUser, $this->dbPass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
        $db = new \NotORM($pdo, null, $cache);
        return $db;
    }

    public function index() {
        
    }

    public function getCategories() {
        $result = null;
        try {
            ORM::configure($this->ORMConfig);
            $categories = ORM::for_table("Category")->select_many(array("cateId" => "id", "cateName" => "name"))->find_array();
            if (!empty($categories)) {
                $result = $categories;
            } else {
                $this->app->response()->status(404);
                $result = array('message' => 'Get Categories Fail!');
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

    public function getPlaylistsByCategoryId() {
        $result = null;
        try {
            $id = intval($this->app->request->get("id"));
            ORM::configure($this->ORMConfig);
            $playlists = ORM::for_table("Playlist")
                            ->select_many(array("pId" => "id", "name", "thumb" => "img"))
                            ->where_equal(array("is_hide" => 0, "category_id" => $id))->find_array();
            if (!empty($playlists)) {
                $body = array("result" => $playlists);
            } else {
                $status = 404;
                $body = array("result" => array('message' => 'Get Playlists Fail!'));
            }
        } catch (ResourceNotFoundException $e) {
            $status = 404;
            $body = array("result" => array('message' => 'Resource Not Found!'));
        } catch (Exception $e) {
            $status = 400;
            $this->app->response()->header('X-Status-Reason', $e->getMessage());
        }
        $headers = array("Content-Type" => $this->app->request()->getMediaType());
        s9ProjectHelper\AppResponeHelper::result(
                $this->app->response, new s9ProjectHelper\RequestResult($status, $headers, $body)
        );
    }

    public function getVideosByPlaylistId() {
        $status = 200; $headers = array(); $body = array();
        try {
            $id = intval($this->app->request->get("id"));
            ORM::configure($this->ORMConfig);
            $videos = ORM::for_table("Video")->select_many("name", "youtube_id")->where_equal("playlist_id", $id)->find_array();
            if (!empty($videos)) {
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
        } catch (ResourceNotFoundException $e) {
            $status = 404;
            $body = array("result" => array("message" => "Resource Not Found!"));
        } catch (Exception $e) {
            $status = 400;
            $body = array("result" => array("message" => "Broken request!","error" => $e->getMessage()));
        }
        $headers += array("Content-Type" => $this->app->request()->getMediaType());
        s9ProjectHelper\AppResponeHelper::result(
                $this->app->response, new s9ProjectHelper\RequestResult($status, $headers, $body)
        );
    }

}

?>
