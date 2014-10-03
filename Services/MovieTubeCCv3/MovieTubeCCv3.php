<?php

\Slim\Slim::registerAutoloader();

/*
 * MovieTubeCCv3
 */

class MovieTubeCCv3 {

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
        // for all movies
        $this->app->get('/GetCategory', array($this, 'getCategories'));
        $this->app->get('/AllFilms', array($this, 'getAllFilms'));
        // for english movies
        $this->app->get('/english/GetCategory', array($this, 'getCategoriesEng'));
        $this->app->get('/english/AllFilms', array($this, 'getAllFilmsEng'));
        $this->app->run();
    }

    function dbConnect($cache = null) {
        $pdo = new \PDO('mysql:host=' . $this->dbHost . ';dbname=' . $this->dbName, $this->dbUser, $this->dbPass);
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
//                    $data = iterator_to_array($film);
                    $filmGenre = $db->film_genre("film_id = ?", $film['id'])->select("genre_id");
                    $arrFG = array();
                    foreach ($filmGenre as $fg) {
                        array_push($arrFG, $fg['genre_id']);
                    }
                    $filmDetails = array("film" => array(
                            "filmId" => $film['id'],
                            "name" => $film['name'],
                            "year" => $film['year'],
                            "youtubeId" => $film['movieLink'],
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
            $this->app->response()->header('Content-Type', 'application/xml');
            echo \s9ProjectHelper\ArrayToXML::toXml($result, 'app');
        } else {
            $this->app->response->headers->set('Content-Type', 'application/json');
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
            $genres = $db->genre()->select("id as cateId, name as cateName")->where("isHide = ?", 0);
            $arrGenres = array();
            foreach ($genres as $genre) {
                $data = iterator_to_array($genre);
                array_push($arrGenres, $data);
            }
            $result = array("listCategory" => $arrGenres);
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

    public function getAllFilmsEng() {
        $result = null;
        try {
//            $cache = new NotORM_Cache_File("notorm.cache");
            $date = intval($this->app->request()->get('date'));
            $db = $this->dbConnect();
            $films = $db->film("isHide = ? ", 0)->select("id, name, year , poster, directors, stars, runtime, releaseDate, plot, 
                imdbRate, MPAARate, quanlity, movieLink, isHide, countryId, siteId")
                    ->where("updateDay BETWEEN FROM_UNIXTIME(?) AND NOW()", $date);
            if (count($films)) {
                $arrFilms = array();
                foreach ($films as $film) {
                    $filmGenre = $db->film_genre("film_id = ?", $film['id'])
                            ->select("genre_id")
                            ->where("genre_id < 23");
                    $arrFG = array();
                    foreach ($filmGenre as $fg) {
                        array_push($arrFG, $fg['genre_id']);
                    }
                    $filmDetails = array("film" => array(
                            "filmId" => $film['id'],
                            "name" => $film['name'],
                            "year" => $film['year'],
                            "youtubeId" => $film['movieLink'],
                            "thumb" => $film['poster'],
                            "imdb" => $film['imdbRate']
                        )) + array("category" => $arrFG);
                    array_push($arrFilms, $filmDetails);
                }
                $arrDel = array();
                if ($date != 0) {
                    $delIds = $db->film(array("countryId = ?" => 1, "isHide = ?" => 1))
                            ->select("id")
                            ->where("updateDay BETWEEN FROM_UNIXTIME(?) AND NOW()", $date);
                    $arrDelTemp = array();
                    foreach ($delIds as $delId) {
                        array_push($arrDelTemp, $delId['id']);
                    }
                    $arrDel = array("deletedId" => $arrDelTemp);
                }
                $result = array("now" => time()) + array("listFilm" => $arrFilms) + $arrDel;
            } else {
                $this->app->response()->status(404);
                $result = array('message' => 'Get English Films Fail!');
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
