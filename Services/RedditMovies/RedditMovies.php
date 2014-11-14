<?php

Slim\Slim::registerAutoloader();

/*
 * Reddit Movies
 */

class RedditMovies {

    public static $_base69_positionCut = 1;

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

        $this->ORMConfigMdata = array(
            'connection_string' => 'mysql:host=mdata.mobi;dbname=' . $this->dbName,
            'username' => $this->dbUser,
            'password' => $this->dbPass,
            'driver_options' => array(
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            )
        );
        // End data config
        // Start Slim Config
        $arrSlimConfig = array();
        if (MODE_APP == "RELEASE") {
            $arrSlimConfig = array('debug' => false, 'mode' => 'production');
        } else {
            $arrSlimConfig = array('debug' => true, 'mode' => 'development');
        }
        $this->app = new Slim\Slim($arrSlimConfig);
        // End Slim Config
    }

    public function enable() {
        $this->app->get('/', array($this, 'index'));
        
        // for all movies
        $this->app->get('/YT/GetCategory', array($this, 'getCategories'));
        $this->app->get('/YT/AllFilms', array($this, 'getAllFilms'));
        //Test
        $this->app->get('/test/GetCategory', array($this, 'getCategories'));
        $this->app->get('/test/AllFilms', array($this, 'getTestAllFilms'));
        
        $this->app->run();
    }

    public function index() {
        $status = 200;
        $body = array("result" => array("message" => "Wellcome to " . APP_NAME . " APIs"));
        $headers = array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body);
    }

    /*
     * For All Movies
     */

    public function getCategories() {
        $status = 200;
        $headers = array();
        $body = array();
        try {
            ORM::configure($this->ORMConfigMdata);
            $genres = ORM::for_table("Genre")->select_many(array("cateId" => "id", "cateName" => "name"))->where_equal("isHide", 0)->find_array();
            $body = array("result" => $genres);
        } catch (Exception $e) {
            $status = 500;
            $headers += array("Connection" => "close", "Warning" => "Server execute in error");
            $body = array("result" => array("message" => "You have a trouble request", "error" => $e->getMessage()));
            s9Helper\MyFile\Log::write("File:" . $e->getFile() . PHP_EOL . "Message:" . $e->getMessage() . PHP_EOL . "Line:" . $e->getLine() . PHP_EOL . "Code:" . $e->getCode() . PHP_EOL . "Trace:" . $e->getTraceAsString(), ".ExecuteException", APP_NAME);
        }
        $headers += array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body, "Categories");
    }

    public function getAllFilms() {
        $status = 200;
        $headers = array();
        $body = array();
        try {
            $date = intval($this->app->request->get('date'));
            ORM::configure($this->ORMConfigMdata);
            //Field film table
            $strTbFilm = "Film";
            $arrFilmField = array('id', 'name', 'year', 'image', 'youtubeId', 'viewCount', 
                'like', 'dislike', 'uploader', 'duration');
            $arrFilmCondition = array("isHide" => 0);
            $strFilmDate = "updateDay BETWEEN FROM_UNIXTIME(?) AND NOW()";
            //Field genres table
            $strTbGenre = "FilmGenre";
            $arrGenreField = array("genreId");
            $strFilmId = "filmId";
            //END Field

            $films = ORM::for_table($strTbFilm)->select_many($arrFilmField)->where_equal($arrFilmCondition)
                    ->where_raw($strFilmDate, $date)
                    ->find_array();
            $arrFilms = array();
            foreach ($films as $film) {
                $filmGenre = ORM::for_table($strTbGenre)->select($arrGenreField)->where($strFilmId, $film['id'])->find_array();
                $genreResult = array();
                foreach ($filmGenre as $valGen) {
                    array_push($genreResult, $valGen['genreId']);
                }
                $filmDetails = array(
                    "id" => $film['id'],
                    "name" => trim($film['name']),
                    "year" => $film['year'],
                    "image" => s9Helper\Security\Base69::encodeType2($film['image'], self::$_base69_positionCut),
                    "youtubeId" => s9Helper\Security\Base69::encodeType2(trim($film['youtubeId']), self::$_base69_positionCut),
                    "viewCount" => intval($film['viewCount']),
                    "like" => intval($film['like']),
                    "dislike" => intval($film['dislike']),
                    "uploader" => $film['uploader'],
                    "duration" => trim($film['duration']),
                    "category" => $genreResult
                );
                array_push($arrFilms, $filmDetails);
            }
            $arrDel = array();
            if ($date != 0) {
                $arrFilmDelCondition = array("isHide" => 1);
                $delIds = ORM::for_table($strTbFilm)->select_many("id")->where_equal($arrFilmDelCondition)
                        ->find_array();
                $arrDelTemp = array();
                foreach ($delIds as $delId) {
                    array_push($arrDelTemp, $delId['id']);
                }
                $arrDel = array("deletedId" => $arrDelTemp);
            }
            $body = array("result" => (array("now" => time()) + array("listFilm" => $arrFilms) + $arrDel));
        } catch (Exception $e) {
            $status = 500;
            $headers += array("Connection" => "close", "Warning" => "Server execute in error");
            $body = array("result" => array("message" => "You have a trouble request", "error" => $e->getMessage()));
            s9Helper\MyFile\Log::write("File:" . $e->getFile() . PHP_EOL . "Message:" . $e->getMessage() . PHP_EOL . "Line:" . $e->getLine() . PHP_EOL . "Code:" . $e->getCode() . PHP_EOL . "Trace:" . $e->getTraceAsString(), ".ExecuteException", APP_NAME);
        }
        $headers += array("Content-Type" => $this->app->request()->getMediaType(), "Keep-Alive" => 20);
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body, "films");
    }

    public function getTestAllFilms() {
        $status = 200;
        $headers = array();
        $body = array();
        try {
            $date = intval($this->app->request->get('date'));
            ORM::configure($this->ORMConfigMdata);
            //Field film table
            $strTbFilm = "Film";
            $arrFilmField = array('id', 'name', 'year', 'image', 'youtubeId', 'viewCount', 
                'like', 'dislike', 'uploader', 'duration');
            $arrFilmCondition = array("isHide" => 0);
            $strFilmDate = "updateDay BETWEEN FROM_UNIXTIME(?) AND NOW()";
            //Field genres table
            $strTbGenre = "FilmGenre";
            $arrGenreField = array("genreId");
            $strFilmId = "filmId";
            //END Field

            $films = ORM::for_table($strTbFilm)->select_many($arrFilmField)->where_equal($arrFilmCondition)
                    ->where_raw($strFilmDate, $date)->limit(500)->offset(0)
                    ->find_array();
            $arrFilms = array();
            foreach ($films as $film) {
                $filmGenre = ORM::for_table($strTbGenre)->select($arrGenreField)->where($strFilmId, $film['id'])->find_array();
                $genreResult = array();
                foreach ($filmGenre as $valGen) {
                    array_push($genreResult, $valGen['genreId']);
                }
                $filmDetails = array(
                    "id" => $film['id'],
                    "name" => trim($film['name']),
                    "year" => $film['year'],
                    "image" => s9Helper\Security\Base69::encodeType2($film['image'], self::$_base69_positionCut),
                    "youtubeId" => s9Helper\Security\Base69::encodeType2(trim($film['youtubeId']), self::$_base69_positionCut),
                    "viewCount" => intval($film['viewCount']),
                    "like" => intval($film['like']),
                    "dislike" => intval($film['dislike']),
                    "uploader" => $film['uploader'],
                    "duration" => trim($film['duration']),
                    "category" => $genreResult
                );
                array_push($arrFilms, $filmDetails);
            }
            $arrDel = array();
            if ($date != 0) {
                $arrFilmDelCondition = array("isHide" => 1);
                $delIds = ORM::for_table($strTbFilm)->select_many("id")->where_equal($arrFilmDelCondition)
                        ->find_array();
                $arrDelTemp = array();
                foreach ($delIds as $delId) {
                    array_push($arrDelTemp, $delId['id']);
                }
                $arrDel = array("deletedId" => $arrDelTemp);
            }
            $body = array("result" => (array("now" => time()) + array("listFilm" => $arrFilms) + $arrDel));
        } catch (Exception $e) {
            $status = 500;
            $headers += array("Connection" => "close", "Warning" => "Server execute in error");
            $body = array("result" => array("message" => "You have a trouble request", "error" => $e->getMessage()));
            s9Helper\MyFile\Log::write("File:" . $e->getFile() . PHP_EOL . "Message:" . $e->getMessage() . PHP_EOL . "Line:" . $e->getLine() . PHP_EOL . "Code:" . $e->getCode() . PHP_EOL . "Trace:" . $e->getTraceAsString(), ".ExecuteException", APP_NAME);
        }
        $headers += array("Content-Type" => $this->app->request()->getMediaType(), "Keep-Alive" => 20);
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body, "films");
    }
    
}
