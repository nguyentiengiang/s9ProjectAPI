<?php

\Slim\Slim::registerAutoloader();

/*
 * MovieTubeCCv3
 */

class MovieTubeCCv3 {

    public static $_base69_positionCut = 1;

//    public static $_base69_arrPositionSwap = array(0 => 1, 1 => 0);

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
//            'debug' => true,
//            'mode' => 'development',
            'debug' => false,
            'mode' => 'production',
        ));
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

    function dbConnect($cache = null) {
        $pdo = new \PDO('mysql:host=' . $this->dbHost . ';dbname=' . $this->dbName, $this->dbUser, $this->dbPass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
        $db = new \NotORM($pdo, null, $cache);
        return $db;
    }

    public function index() {
        
    }

    /*
     * For All Movies
     */

    public function getCategories() {
        $result = null;
        try {
//            $cache = new NotORM_Cache_File("notorm.cache");
            $db = $this->dbConnect();
            $genres = $db->genre()->select("id as cateId, name as cateName")->where("isHide = ?", 0);
            $countries = $db->country()->select("id as countryId, name as countryName")->where("isHide = ?", 0);
            $result = array();
            $arrCountries = array();
            $arrGenres = array();
            foreach ($countries as $country) {
                $data = iterator_to_array($country);
                array_push($arrCountries, $data);
            }
            foreach ($genres as $genre) {
                $data = iterator_to_array($genre);
                array_push($arrGenres, $data);
            }
            $result = array("listCategory" => $arrGenres) + array("listCountry" => $arrCountries);
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

    public function getAllFilms() {
        $result = null;
        try {
//            $cache = new NotORM_Cache_File("notorm.cache");
            $date = intval($this->app->request()->get('date'));
            $db = $this->dbConnect();
            $films = $db->film("isHide = ? ", 0)->select("id, name, year , poster, directors, stars, runtime, releaseDate, plot, 
                imdbRate, MPAARate, quanlity, movieLink, isHide, countryId, siteId")->where("updateDay BETWEEN FROM_UNIXTIME(?) AND NOW()", $date)->limit(3, 5);
            if (count($films)) {
                $arrFilms = array();
                foreach ($films as $film) {
                    $filmGenre = $db->film_genre("film_id = ?", $film['id'])->select("genre_id");
                    $arrFG = array();
                    foreach ($filmGenre as $fg) {
                        array_push($arrFG, $fg['genre_id']);
                    }
                    $filmDetails = array("film" => array(
                            "filmId" => $film['id'],
                            "name" => $film['name'],
                            "year" => $film['year'],
                            "youtubeId" => \s9ProjectHelper\Base69::encodeType2($film['movieLink'], 1),
                            "thumb" => $film['poster'],
                            "imdb" => $film['imdbRate'],
                            "category" => $arrFG
                    ));
                    array_push($arrFilms, $filmDetails);
                }
                $result = array("now" => time()) + array("listFilm" => $arrFilms);
            } else {
                $this->app->response()->status(404);
                $result = array('message' => 'Get Films Fail!');
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
            $this->app->response()->header('Content-Type', 'application/xml;charset=utf-8');
            echo \s9ProjectHelper\ArrayToXML::toXml($result, 'app');
        } else {
            $this->app->response->headers->set('Content-Type', 'application/json;charset=utf-8');
            echo json_encode($result, JSON_NUMERIC_CHECK | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
        }
    }

    /*
     * For English Movies Only
     */

    public function getCategoriesEng() {
        $result = null;
        try {
//            $cache = new NotORM_Cache_File("notorm.cache");
            $db = $this->dbConnect();
            $genres = $db->genre()->select("id as cateId, name as cateName")->where("status = ?", 1);
            $result = array();
            foreach ($genres as $genre) {
                $data = iterator_to_array($genre);
                array_push($result, $data);
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
            $this->app->response()->header('Content-Type', 'application/xml;charset=utf-8');
            echo \s9ProjectHelper\ArrayToXML::toXml($result, 'app');
        } else {
            $this->app->response->headers->set('Content-Type', 'application/json;charset=utf-8');
            echo json_encode($result, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
        }
    }

    public function getAllFilmsEng() {
        $result = null;
        try {
//            $cache = new NotORM_Cache_File("notorm.cache");
            $date = intval($this->app->request()->get('date'));
            $db = $this->dbConnect();
            $films = $db->film(array("is_hide = ? " => 0, "country_id = ?" => 1))
                    ->select("id, name, year, youtube_id, thumb, imdb")
                    ->where("update_day BETWEEN FROM_UNIXTIME(?) AND NOW()", $date);

            $arrFilms = array();
            foreach ($films as $film) {
                $filmGenre = $db->film_genre("film_id = ?", $film['id'])->select("genre_id");
                $arrFG = array();
                foreach ($filmGenre as $fg) {
                    array_push($arrFG, $fg['genre_id']);
                }
                $filmDetails = array("film" => array(
                        "filmId" => $film['id'],
                        "name" => $film['name'],
                        "year" => $film['year'],
                        "youtubeId" => \s9Helper\Base69::encodeType2($film['youtube_id'], self::$_base69_positionCut),
                        "thumb" => \s9Helper\Base69::encodeType2($film['thumb'], self::$_base69_positionCut),
                        "imdb" => $film['imdb']
                    )) + array("category" => $arrFG);
                array_push($arrFilms, $filmDetails);
            }
            $arrDel = array();
            if ($date != 0) {
                $delIds = $db->film(array("country_id = ?" => 1, "is_hide = ?" => 1))
                        ->select("id")
                        ->where("update_day BETWEEN FROM_UNIXTIME(?) AND NOW()", $date);
                $arrDelTemp = array();
                foreach ($delIds as $delId) {
                    array_push($arrDelTemp, $delId['id']);
                }
                $arrDel = array("deletedId" => $arrDelTemp);
            }
            $result = array("now" => time()) + array("listFilm" => $arrFilms) + $arrDel;

//            if (count($films)) {
//            } else {
//                $this->app->response()->status(404);
//                $result = array('message' => 'Get English Films Fail!');
//            }
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
            $this->app->response()->header('Content-Type', 'application/xml;charset=utf-8');
            echo \s9ProjectHelper\ArrayToXML::toXml($result, 'app');
        } else {
            $this->app->response->headers->set('Content-Type', 'application/json;charset=utf-8');
            echo json_encode($result, JSON_NUMERIC_CHECK | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
        }
    }

}

?>
