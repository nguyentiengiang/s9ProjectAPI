<?php
//Autoload Slim
\Slim\Slim::registerAutoloader();

/*
 * Watch33.tv
 */

class Watch33TV {

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
        $this->app->get('/GetCategory', array($this, 'GetCategories'));
        $this->app->get('/AllFilm', array($this, 'GetAllFilms'));
        $this->app->get('/GetEpisodes', array($this, 'GetEpisodesByFilm'));
        $this->app->post('/postAdd', array($this, 'postAddNew'));
        $this->app->post('/postUpdate/:id', array($this, 'postUpdateByID'));
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

    public function GetAllFilms() {
        $status = 200;
        $headers = array();
        $body = array();
        try {
            $date = intval($this->app->request()->get('date'));
            $countryId = intval($this->app->request()->get('lang'));
            ORM::configure($this->ORMConfig);
            //Field film table
            $strTbFilm = "film";
            $arrFilmField = array("DISTINCT(site_id)", "id", "name", "year", "thumb", "imdb", "country_id", "site_id", "type_link");
            $arrFilmCondition = array("is_hide" => 0);
            if (!empty($countryId)) {
                $arrFilmCondition = array("is_hide" => 0, "country_id" => $countryId);
            }
            $strFilmDate = "update_day BETWEEN FROM_UNIXTIME(?) AND NOW()";
            //END Field

            $films = ORM::for_table($strTbFilm)->select_many_expr($arrFilmField)->where_equal($arrFilmCondition)
                    ->where_raw($strFilmDate, $date)->group_by("site_id")->order_by_asc("country_id")
                    ->find_array();
            $arrFilms = array();
            foreach ($films as $film) {
                if (intval($film['type_link']) === 2) {
                    $filmDetails = array(
                        "filmId" => $film['id'],
                        "name" => trim($film['name']),
                        "year" => $film['year'],
                        "thumb" => s9Helper\Security\Base69::encodeType2(trim($film['thumb']), self::$_base69_positionCut),
                        "imdb" => $film['imdb'],
                        "countryId" => $film['country_id'],
                        "siteId" => $film['site_id']
                    );
                } else {
                    $filmDetails = array(
                        "filmId" => $film['id'],
                        "name" => trim($film['name']),
                        "year" => $film['year'],
                        "thumb" => s9Helper\Security\Base69::encodeType2(trim($film['thumb']), self::$_base69_positionCut),
                        "imdb" => $film['imdb'],
                        "countryId" => $film['country_id'],
                    );
                }
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

    public function GetEpisodesByFilm() {
        $status = 200;
        $headers = array();
        $body = array();
        try {
            $id = intval($this->app->request()->get('id'));
            ORM::configure($this->ORMConfig);
            $episodes = ORM::for_table("episodes")->select_many(array("epId" => "chap_id", "link"))->where_equal("film_id", $id)->find_array();
            $arrEp = array();
            foreach ($episodes as $value) {
                array_push($arrEp, array("epId" => $value["epId"], "link" => s9Helper\Security\Base69::encodeType2(trim($value['link']), self::$_base69_positionCut)));
            }
            $body = array("result" => array("filmId" => $id) + array("listEpisodes" => $arrEp));
        } catch (Exception $e) {
            $status = 500;
            $headers += array("Connection" => "close", "Warning" => "Server execute in error");
            $body = array("result" => array("message" => "You have a trouble request", "error" => $e->getMessage()));
            s9Helper\MyFile\Log::write("File:" . $e->getFile() . PHP_EOL . "Message:" . $e->getMessage() . PHP_EOL . "Line:" . $e->getLine() . PHP_EOL . "Code:" . $e->getCode() . PHP_EOL . "Trace:" . $e->getTraceAsString(), ".ExecuteException", APP_NAME);
        }
        $headers += array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body);
    }

    public function postAddNew() {
        $post = $this->app->request()->post();
        $result = null;
        $vaildate = self::ValidWatch33TV($post);
        if (empty($vaildate)) {
            try {
                $db = $this->dbConnect();
                $db->transaction = "BEGIN";

                $timeZone = 'America/Los_Angeles';
                $dateTime = new DateTime();
                $dateTime->setTimeZone(new DateTimeZone($timeZone));
                $now = $dateTime->format('Y-m-d H:i:s');

                $lastID = $db->film()->insert(array(
                    "name" => $post['name'], "year" => $post['year'], "thumb" => $post['thumb'],
                    "imdb" => $post['imdb'], "is_hide" => $post['is_hide'], "country_id" => $post['country_id'],
                    "create_day" => $now, "update_day" => $now, "type_link" => 0
                        )
                );
                foreach ($post['genre'] as $vG) {
                    $db->film_genre()->insert(array("film_id" => $lastID, "genre_id" => $vG));
                }
                $arrEpisodes = array_filter(explode(";", $post['episodes']));
                $chapId = 1;
                foreach ($arrEpisodes as $vE) {
                    $db->episodes()->insert(array("film_id" => $lastID, "link" => $vE, "chap_id" => $chapId));
                    $chapId++;
                }
                $db->transaction = "COMMIT";
                $items = $db->film("id = ?", $lastID);
                if (count($items) > 0) {
                    $this->app->response()->status(200);
                    $result = array('message' => 'Inserted');
                } else {
                    $this->app->response()->status(200);
                    $result = array('message' => 'Inserted But Unknown');
                }
            } catch (\PDOException $e) {
                $db->transaction = "ROLLBACK";
                $result = array('message' => 'Validate broken!', 'errors' => $e->getMessage());
            } catch (ResourceNotFoundException $e) {
                $this->app->response()->status(404);
                $result = array('message' => 'Resource Not Found!', 'errors' => $e->getMessage());
            } catch (Exception $e) {
                $this->app->response()->status(400);
                $this->app->response()->header('X-Status-Reason', $e->getMessage());
                $result = array('message' => 'Unknown', 'errors' => $e->getMessage());
            }
        } else {
            $this->app->response()->status(200);
            $result = array('status' => 'error', 'errors' => $vaildate);
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

    public function postUpdateByID($id) {
        $post = $this->app->request()->post();
        if (intval($id) !== 0) {
            $result = null;
            $vaildate = self::ValidWatch33TV($post);
            if (empty($vaildate)) {
                try {
                    $db = $this->dbConnect();
                    $db->transaction = "BEGIN";

                    $timeZone = 'America/Los_Angeles';
                    $dateTime = new DateTime();
                    $dateTime->setTimeZone(new DateTimeZone($timeZone));
                    $now = $dateTime->format('Y-m-d H:i:s');

                    $db->film("id = ?", intval($id))->update(array(
                        "id" => intval($id), "name" => $post['name'], "year" => $post['year'], "thumb" => $post['thumb'],
                        "imdb" => $post['imdb'], "is_hide" => $post['is_hide'], "country_id" => $post['country_id'], "update_day" => $now
                            )
                    );
                    $db->film_genre("film_id = ?", intval($id))->delete();
                    $db->episodes("film_id = ?", intval($id))->delete();
                    foreach ($post['genre'] as $vG) {
                        $db->film_genre()->insert(array("film_id" => intval($id), "genre_id" => $vG));
                    }
                    $arrEpisodes = array_filter(explode(";", $post['episodes']));
                    $chapId = 1;
                    foreach ($arrEpisodes as $vE) {
                        $db->episodes()->insert(array("film_id" => intval($id), "link" => $vE, "chap_id" => $chapId));
                        $chapId++;
                    }
                    $db->transaction = "COMMIT";
                    $this->app->response()->status(200);
                    $result = array('message' => 'Updated');
                } catch (\PDOException $e) {
                    $db->transaction = "ROLLBACK";
                    $result = array('message' => 'Validate broken!', 'errors' => $e->getMessage());
                } catch (ResourceNotFoundException $e) {
                    $this->app->response()->status(404);
                    $result = array('message' => 'Resource Not Found!', 'errors' => $e->getMessage());
                } catch (Exception $e) {
                    $this->app->response()->status(400);
                    $this->app->response()->header('X-Status-Reason', $e->getMessage());
                    $result = array('message' => 'Unknown', 'errors' => $e->getMessage());
                }
            } else {
                $this->app->response()->status(200);
                $result = array('status' => 'error', 'errors' => $vaildate);
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

    private function ValidWatch33TV($arrayData) {
        $return = null;
        Valitron\Validator::langDir(LIB_ROOT . 'Valitron/lang');
        Valitron\Validator::lang('vi');
        $v = new Valitron\Validator($arrayData);
        Valitron\Validator::addRule('myBool', function($field, $value, array $params) {
                    $acceptable = array('0', '1', 0, 1, false, true);
                    return in_array($value, $acceptable, true);
                }, 'không đúng định dạng');
        $v->rule('required', ['name', 'year', 'thumb', 'imdb', 'is_hide', 'country_id', 'genre']);
        $v->rule('myBool', ['is_hide']);
        $v->rule('integer', ['id']);
        $v->rule('min', ['id'], 1);
        if ($v->validate()) {
            $return = null;
        } else {
            $return = $v->errors();
        }
        return $return;
    }

}

?>
