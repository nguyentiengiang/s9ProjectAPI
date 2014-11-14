<?php

\Slim\Slim::registerAutoloader();

/*
 * MovieTubeCCv3
 */

class MovieTubeCCv3 {

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
            'connection_string' => 'mysql:host=mdata.mobi;dbname=MovieTubeCCv2',
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
        $this->app = new \Slim\Slim($arrSlimConfig);
        // End Slim Config
    }

    public function enable() {
        $this->app->get('/', array($this, 'index'));
        // for all movies
        $this->app->get('/GetCategory', array($this, 'getCategories'));
        $this->app->get('/AllFilms', array($this, 'getAllFilms'));
        // for english movies
        $this->app->get('/english/GetCategory', array($this, 'getCategoriesEng'));
        $this->app->get('/english/AllFilms', array($this, 'getAllFilmsEng'));
        $this->app->run();
    }

    public function index() {
        $status = 200;
        $body = array("result" => array("message" => "Wellcome to " . APP_NAME . " APIs"));
        $headers = array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\ResponeHelper::result($this->app->request, $this->app->response, new s9Helper\RequestResult($status, $headers, $body));
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
            $genres = ORM::for_table("genre")->select_many(array("cateId" => "id", "cateName" => "name"))->where_equal("status", 1)->find_array();
            $countries = ORM::for_table("country")->select_many(array("countryId" => "id", "countryName" => "name"))->where_equal("status", 1)->find_array();
            $body = array("result" => array("listCategory" => $genres) + array("listCountry" => $countries));
        } catch (Exception $e) {
            $status = 500;
            $headers += array("Connection" => "close", "Warning" => "Server execute in error");
            $body = array("result" => array("message" => "You have a trouble request", "error" => $e->getMessage()));
            s9Helper\MyFile\Log::write("File:" . $e->getFile() . PHP_EOL . "Message:" . $e->getMessage() . PHP_EOL . "Line:" . $e->getLine() . PHP_EOL . "Code:" . $e->getCode() . PHP_EOL . "Trace:" . $e->getTraceAsString(), ".ExecuteException", APP_NAME);
        }
        $headers += array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body);
    }

    public function getAllFilms() {
        $status = 200;
        $headers = array();
        $body = array();
        try {
            $date = intval($this->app->request()->get('date'));
            /*
            ORM::configure($this->ORMConfig);
            //Field film table
            $strTbFilm = "film";
            $arrFilmField = array("id", "name", "year", "poster", "directors", "stars", "runtime", "releaseDate", "plot", "imdbRate", "MPAARate", "quanlity", "movieLink", "isHide", "countryId", "siteId");
            $arrFilmCondition = array("isHide" => 0);
            $strFilmDate = "updateDay BETWEEN FROM_UNIXTIME(?) AND NOW()";
            //Field genres table
            $strTbGenre = "film_genre";
            $arrGenreField = array("genre_id");
            $strFilmId = "film_id";
            //END Field
             */

            ORM::configure($this->ORMConfigMdata);
            //Field film table
            $strTbFilm = "film";
            $arrFilmField = array("id", "name", "year", "youtube_id", "thumb", "imdb", "country_id");
            $arrFilmCondition = array("is_hide" => 0);
            $strFilmDate = "update_day BETWEEN FROM_UNIXTIME(?) AND NOW()";
            //Field genres table
            $strTbGenre = "film_genre";
            $arrGenreField = array("genre_id");
            $strFilmId = "film_id";
            //END Field
            
            $films = ORM::for_table($strTbFilm)->select_many($arrFilmField)->where_equal($arrFilmCondition)
                    ->where_raw($strFilmDate, $date)
                    ->find_array();
            $arrFilms = array();
            foreach ($films as $film) {
                $filmGenre = ORM::for_table($strTbGenre)->select($arrGenreField)->where($strFilmId, $film['id'])->find_array();
                $strArrFG = ";";
                foreach ($filmGenre as $fg) {
                    $strArrFG .= $fg['genre_id'] . ";";
                }
                $filmDetails = array(
                        "filmId" => $film['id'],
                        "name" => trim($film['name']),
                        "year" => $film['year'],
                        "youtubeId" => s9Helper\Security\Base69::encodeType2(trim($film['youtube_id']), self::$_base69_positionCut),
                        "thumb" => s9Helper\Security\Base69::encodeType2(trim($film['thumb']), self::$_base69_positionCut),
                        "imdb" => $film['imdb'],
                        "countryId" => $film['country_id'],
                        "category" => $strArrFG
                    );
                array_push($arrFilms, $filmDetails);
            }
            $arrDel = array();
            if ($date != 0) {
                $arrFilmDelCondition = array("is_hide" => 1);
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
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body);
    }

    /*
     * For English Movies Only
     */

    public function getCategoriesEng() {
        $status = 200;
        $headers = array();
        $body = array();
        try {
            ORM::configure($this->ORMConfigMdata);
            $genres = ORM::for_table("genre")->select_many(array("cateId" => "id", "cateName" => "name"))
                            ->where_equal("status", 1)->find_array();
            if (count($genres) > 0) {
                $body = array("result" => $genres);
            } else {
                $status = 404;
                $body = array("result" => array('message' => 'Get Categories Fail!'));
            }
        } catch (Exception $e) {
            $status = 500;
            $headers += array("Connection" => "close", "Warning" => "Server execute in error");
            $body = array("result" => array("message" => "You have a trouble request", "error" => $e->getMessage()));
            s9Helper\MyFile\Log::write("File:" . $e->getFile() . PHP_EOL . "Message:" . $e->getMessage() . PHP_EOL . "Line:" . $e->getLine() . PHP_EOL . "Code:" . $e->getCode() . PHP_EOL . "Trace:" . $e->getTraceAsString(), ".ExecuteException", APP_NAME);
        }
        $headers += array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body);
    }

    public function getAllFilmsEng() {
        $status = 200;
        $headers = array();
        $body = array();
        try {
            $date = intval($this->app->request()->get('date'));
            ORM::configure($this->ORMConfigMdata);
            //Field film table
            $strTbFilm = "film";
            $arrFilmField = array("id", "name", "year", "youtube_id", "thumb", "imdb");
            $arrFilmCondition = array("is_hide" => 0, "country_id" => 1);
            $strFilmDate = "update_day BETWEEN FROM_UNIXTIME(?) AND NOW()";
            //Field genres table
            $strTbGenre = "film_genre";
            $arrGenreField = array("genre_id");
            $strFilmId = "film_id";
            //END Field

            $films = ORM::for_table($strTbFilm)->select_many($arrFilmField)->where_equal($arrFilmCondition)
                    ->where_raw($strFilmDate, $date)
                    ->find_array();
            $arrFilms = array();
            foreach ($films as $film) {
                $filmGenre = ORM::for_table($strTbGenre)->select($arrGenreField)->where($strFilmId, $film['id'])->find_array();
                $arrFG = array();
                foreach ($filmGenre as $fg) {
                    array_push($arrFG, $fg['genre_id']);
                }
                $filmDetails = array("film" => array(
                        "filmId" => $film['id'],
                        "name" => trim($film['name']),
                        "year" => $film['year'],
                        "youtubeId" => \s9Helper\Security\Base69::encodeType2(trim($film['youtube_id']), self::$_base69_positionCut),
                        "thumb" => \s9Helper\Security\Base69::encodeType2(trim($film['thumb']), self::$_base69_positionCut),
                        "imdb" => $film['imdb']
                    )) + array("category" => $arrFG);
                array_push($arrFilms, $filmDetails);
            }
            $arrDel = array();
            if ($date != 0) {
                $arrFilmDelCondition = array("is_hide" => 1, "country_id" => 1);
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
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body);
    }

}
