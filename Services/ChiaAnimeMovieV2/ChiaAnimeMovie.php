<?php

Slim\Slim::registerAutoloader();

/*
 * Chia-anime.com V2
 * Change code line 158
 */

class ChiaAnimeMovie {

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
        $this->app = new Slim\Slim($arrSlimConfig);
        // End Slim Config
    }

    public function enable() {
        $this->app->get('/', array($this, 'index'));
        $this->app->get('/json/GetCategory', array($this, 'GetCategories'));
        $this->app->get('/json/ListFilm', array($this, 'GetListFilm'));
        $this->app->get('/json/GetEpisodesByFilm', array($this, 'GetEpisodesByFilm'));
        $this->app->run();
    }

    public function index() {
        $status = 200;
        $body = array("result" => array("message" => "Wellcome to " . APP_NAME . " APIs"));
        $headers = array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body);
    }

    public function GetCategories() {
        $status = 200;
        $headers = array();
        $body = array();
        try {
            ORM::configure($this->ORMConfig);
            $categories = ORM::for_table("Category")->select_many(array("id", "name"))->where_equal(array("is_hide" => 0))->find_array();

            if (count($categories)) {
                $body = array("result" => $categories);
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
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body, "animeCategory");
    }

    public function GetListFilm() {
        $status = 200;
        $headers = array();
        $body = array();
        try {
            $date = intval($this->app->request()->get('date'));
            ORM::configure($this->ORMConfig);
            //Field film table
            $strTbFilm = "Film";
            $arrFilmField = array("id", "name", "thumb", "number_chap", "summary", "status", "type");
            $arrFilmCondition = array("is_hide" => 0);
            $strFilmDate = "update_day BETWEEN FROM_UNIXTIME(?) AND NOW()";

            $films = ORM::for_table($strTbFilm)->select_many($arrFilmField)->where_equal($arrFilmCondition)
                            ->where_raw($strFilmDate, $date)->find_array();
            $arrFilms = array();
            foreach ($films as $film) {
                $filmGenre = ORM::for_table("FilmCategory")->select("IdCat")
                                ->where_equal("IdFilm", $film['id'])->order_by_asc("IdCat")->find_array();
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
                $arrFilmDelCondition = array("is_hide" => 1);
                $delIds = ORM::for_table($strTbFilm)->select_many("id")->where_equal($arrFilmDelCondition)
                        ->find_array();
                $arrDelTemp = array();
                foreach ($delIds as $delId) {
                    array_push($arrDelTemp, $delId['id']);
                }
                $arrDel = array("deletedId" => $arrDelTemp);
            }
            $body = array("result" => (array("now" => time()) + array("list" => $arrFilms) + $arrDel));
        } catch (Exception $e) {
            $status = 500;
            $headers += array("Connection" => "close", "Warning" => "Server execute in error");
            $body = array("result" => array("message" => "You have a trouble request", "error" => $e->getMessage()));
            s9Helper\MyFile\Log::write("File:" . $e->getFile() . PHP_EOL . "Message:" . $e->getMessage() . PHP_EOL . "Line:" . $e->getLine() . PHP_EOL . "Code:" . $e->getCode() . PHP_EOL . "Trace:" . $e->getTraceAsString(), ".ExecuteException", APP_NAME);
        }
        $headers += array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body, "animes");
    }

    public function GetEpisodesByFilm() {
        $status = 200;
        $headers = array();
        $body = array();
        try {
            $id = $this->app->request()->get('id');
            ORM::configure($this->ORMConfig);
            $infoFilm = ORM::for_table("Film")->select_many(array("id", "name", "thumb", "number_chap", "summary", "status", "type"))
                            ->where_equal(array("is_hide" => 0, "id" => $id))->find_one();
            if (count($infoFilm)) {
                $filmDetails = array(
                    "id" => $id,
                    "name" => $infoFilm['name'],
                    "thumb" => base64_encode($infoFilm['thumb']),
                    "totalChap" => $infoFilm['number_chap'],
                    "summary" => $infoFilm['summary'],
                    "status" => $infoFilm['status'],
                    "type" => $infoFilm['type']
                );
                $arrEp = array();
                if (intval($infoFilm['type']) === 1) {
                    $eps = ORM::for_table("Episodes")->select_many("chap_Id", "name", "episode_web_id", "image")
                            ->where_equal("film_id", $id)->find_array();
                    foreach ($eps as $ep) {
                        $dataEncode = array(
                            "chapId" => $ep["chap_Id"],
                            "name" => $ep["name"],
                            "linkMp4" => base64_encode($ep["episode_web_id"]),
                            "image" => base64_encode($ep["image"]),
                        );
                        array_push($arrEp, $dataEncode);
                    }
                } else if (intval($infoFilm['type']) === 2) {
                    $eps = ORM::for_table("Episodes")->select_many("chap_Id", "name", "episode_web_id", "image")
                            ->where_equal("film_id", $id)->find_array();
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
                $body = array("result" => array("Film" => $filmDetails) + array("LstEpisode" => $arrEp));
            } else {
                $status = 404;
                $body = array("result" => array('message' => 'Get Episodes Fail!'));
            }
        } catch (Exception $e) {
            $status = 500;
            $headers += array("Connection" => "close", "Warning" => "Server execute in error");
            $body = array("result" => array("message" => "You have a trouble request", "error" => $e->getMessage()));
            s9Helper\MyFile\Log::write("File:" . $e->getFile() . PHP_EOL . "Message:" . $e->getMessage() . PHP_EOL . "Line:" . $e->getLine() . PHP_EOL . "Code:" . $e->getCode() . PHP_EOL . "Trace:" . $e->getTraceAsString(), ".ExecuteException", APP_NAME);
        }
        $headers += array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body, "anime");
    }
}
