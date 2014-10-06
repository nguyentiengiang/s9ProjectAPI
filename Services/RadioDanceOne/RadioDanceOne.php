<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

\Slim\Slim::registerAutoloader();

/*
 * id, app, packageName, packageNameMarketing, developer, admod_large, admod_small, 
 * adspaceid, publisherid, `status`, is_download, strSeparatorYT, strSeparatorGD, 
 * versionApp, isParserOnline, youtube_api_key, isHd
 */

class RadioDanceOne {

    public function __construct($dbHost, $dbName, $dbUser, $dbPass) {
        $this->dbHost = $dbHost;
        $this->dbName = $dbName;
        $this->dbUser = $dbUser;
        $this->dbPass = $dbPass;

        $this->app = new \Slim\Slim(array(
            'debug' => true,
            'mode' => 'development',
        ));
    }

    public function enable() {
        $this->app->get('/', array($this, 'index'));
        $this->app->get('/json/AllVideos', array($this, 'getVideos'));
        $this->app->run();
    }

    function dbConnect($cache = null) {
        $pdo = new \PDO('mysql:host=' . $this->dbHost . ';dbname=' . $this->dbName, $this->dbUser, $this->dbPass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
        $db = new \NotORM($pdo, null, $cache);
        return $db;
    }

    public function index() {
        
    }

    public function getVideos() {
        $result = null;
        try {
//            $cache = new NotORM_Cache_File("notorm.cache");
            $db = $this->dbConnect();
            $videos = $db->videos()->select("name, img as thumb, youtube_id as youtubeId");
            if (count($videos)) {
                $result = array();
                foreach ($videos as $video) {
                    $data = iterator_to_array($video);
                    array_push($result, $data);
                }
            } else {
                $this->app->response()->status(404);
                $result = array('message' => 'Get Package Setting Fail!');
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
