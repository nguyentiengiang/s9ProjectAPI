<?php

\Slim\Slim::registerAutoloader();

/*
 * Chia-anime
 */

class ChiaAnimeMovie {

    public function __construct($dbHost, $dbName, $dbUser, $dbPass) {
        $this->dbHost = $dbHost;
        $this->dbName = $dbName;
        $this->dbUser = $dbUser;
        $this->dbPass = $dbPass;

        $this->app = new \Slim\Slim(array(
            'debug' => true,
            'mode' => 'development',
//            'debug' => false,
//            'mode' => 'production',
        ));
    }

    public function enable() {
        $this->app->get('/', array($this, 'index'));
        $this->app->get('/json/GetCategory', array($this, 'GetCategories'));
        $this->app->get('/json/ListFilm', array($this, 'GetListFilm'));
        $this->app->get('/json/GetEpisodesByFilm', array($this, 'GetEpisodesByFilm'));
        $this->app->run();
    }

    function dbConnect($cache = null) {
        $pdo = new \PDO('mysql:host=' . $this->dbHost . ';dbname=' . $this->dbName, $this->dbUser, $this->dbPass);
        $db = new \NotORM($pdo, null, $cache);
        return $db;
    }

    public function index() {
        
    }

    public function GetCategories() {
        $result = null;
        try {
            $db = $this->dbConnect();
            $categories = $db->Category("is_hide = ?", 0)->select("id, name");
            $result = array();
            foreach ($categories as $category) {
                $data = iterator_to_array($category);
                array_push($result, $data);
            }
            if (empty($result)) {
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

    public function GetListFilm() {
        $result = null;
        try {
            $date = intval($this->app->request()->get('date'));
            $db = $this->dbConnect();
            $films = $db->Film(array("is_hide = ? " => 0))
                    ->select("id, name, thumb, number_chap, summary, status, type")
                    ->where("update_day BETWEEN FROM_UNIXTIME(?) AND NOW()", $date);
            $arrFilms = array();
            foreach ($films as $film) {
                $filmGenre = $db->FilmCategory("IdFilm = ?", $film['id'])->select("IdCat")->order("IdCat ASC");
                $filmDetails = array(
                    "id" => $film['id'],
                    "name" => $film['name'],
                    "thumb" => base64_encode($film['thumb']),
                    "totalChap" => $film['number_chap'],
                    "summary" => $film['summary'],
                    "status" => $film['status'],
                    "type" => $film['type']
                        ) + array("category" => $filmGenre);
                array_push($arrFilms, $filmDetails);
            }
            $arrDel = array();
            if ($date != 0) {
                $delIds = $db->Film("is_hide = ?", 1)
                        ->select("id")
                        ->where("update_day BETWEEN FROM_UNIXTIME(?) AND NOW()", $date);
                $arrDelTemp = array();
                foreach ($delIds as $delId) {
                    array_push($arrDelTemp, $delId['id']);
                }
                $arrDel = array("deletedId" => $arrDelTemp);
            }
            $result = array("now" => time()) + array("list" => $arrFilms) + $arrDel;
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

    public function GetEpisodesByFilm() {
        $result = null;
        try {
            $id = $this->app->request()->get('id');
            $db = $this->dbConnect();
            $infoFilm = $db->Film(array("is_hide = ?" => 0, "id = ?" => $id))->select("id, name, thumb, number_chap, summary, status, type");
            $filmDetails = array(
                "id" => $id,
                "name" => $infoFilm[$id]['name'],
                "thumb" => base64_encode($infoFilm[$id]['thumb']),
                "totalChap" => $infoFilm[$id]['number_chap'],
                "summary" => $infoFilm[$id]['summary'],
                "status" => $infoFilm[$id]['status'],
                "type" => $infoFilm[$id]['type']
            );
            $arrEp = array();
            if (intval($infoFilm[$id]['type']) === 1) {
                $eps = $db->Episodes("film_id = ?", $id)->select("chap_Id, name, linkmp4, image");
                foreach ($eps as $ep) {
                    $dataEncode = array(
                        "chapId" => $ep["chap_Id"],
                        "name" => $ep["name"],
                        "linkMp4" => base64_encode($ep["linkmp4"]),
                        "image" => base64_encode($ep["image"]),
                    );
                    array_push($arrEp, $dataEncode);
                }
            } else if (intval($infoFilm[$id]['type']) === 2) {
                $eps = $db->Episodes("film_id = ?", $id)->select("chap_Id, name, episode_web_id, image");
                foreach ($eps as $ep) {
                    $dataEncode = array(
                        "chapId" => $ep["chap_Id"],
                        "name" => $ep["name"],
                        "linkMp4" => base64_encode("http://www.chia-anime.com/watch-anime-dub/download.php?id=" . $ep["episode_web_id"]),
                        "image" => base64_encode($ep["image"]),
                    );
                    array_push($arrEp, $dataEncode);
                }
            }
            $result = array("Film" => $filmDetails) + array("LstEpisode" => $arrEp);
            if (empty($filmDetails) || empty($arrEp)) {
                $this->app->response()->status(404);
                $result = array('message' => 'Get Episodes Fail!');
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
