<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

\Slim\Slim::registerAutoloader();

/*
 * id, `name`, `year`, thumb, imdb, youtube_id, is_hide, 
 * country_id, create_day, update_day, site_id, type_link
 */

class Watch33TV {

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
        $this->app->post('/postAdd', array($this, 'postAddNew'));
        $this->app->post('/postUpdate/:id', array($this, 'postUpdateByID'));
        $this->app->run();
    }

    function dbConnect($cache = null) {
        $pdo = new \PDO('mysql:host=' . $this->dbHost . ';dbname=' . $this->dbName, $this->dbUser, $this->dbPass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
        $db = new \NotORM($pdo, null, $cache);
        return $db;
    }

    public function index() {
        
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
