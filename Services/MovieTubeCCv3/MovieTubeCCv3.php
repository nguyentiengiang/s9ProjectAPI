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
        $this->app->get('/json/admod', array($this, 'getConfigByPackage'));
        $this->app->get('/getAll', array($this, 'getAll'));
        $this->app->get('/getConfigById/:id', array($this, 'getConfigByID'));
        $this->app->post('/postAdd', array($this, 'postAddNew'));
        $this->app->run();
    }

    function dbConnect($cache = null) {
        $pdo = new \PDO('mysql:host=' . $this->dbHost . ';dbname=' . $this->dbName, $this->dbUser, $this->dbPass);
        $db = new \NotORM($pdo, null, $cache);
        return $db;
    }

    public function index() {
        
    }

    public function getConfigByPackage() {
        $result = null;
        try {
            $packageName = $this->app->request()->get('package');
            if (!empty($packageName)) {
                $this->db = $this->dbConnect();
                $cf = $this->db->admod_config("packageName = ?", $packageName)->select("admod_large as large, " .
                        "admod_small as small, adspaceid, publisherid, packageName, packageNameMarketing, developer, " .
                        "status as admobEnable, strSeparatorYT, strSeparatorGD, versionApp, isParserOnline, youtube_api_key, " .
                        "isHd, is_download");
                if (!empty($cf[0])) {
                    $result = $cf[0];
                } else {
                    $this->app->response()->status(404);
                    $result = array('message' => 'Get Package Setting Fail!');
                }
            } else {
                $this->app->response()->status(404);
                $result = array('message' => 'Invalid Parameters!');
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

    public function getAll() {
        $result = null;
        try {
//            $cache = new NotORM_Cache_File("notorm.cache");
            $db = $this->dbConnect();
            $cfs = $db->admod_config()->select("packageName, packageNameMarketing, developer, " .
                    "status as admobEnable, strSeparatorYT, strSeparatorGD, versionApp, isParserOnline, youtube_api_key, " .
                    "isHd, is_download");
            if (count($cfs)) {
                $result = array();
                foreach ($cfs as $cf) {
                    $data = iterator_to_array($cf);
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

    public function getConfigByID($id) {
        $result = null;
        try {
            if (intval($id) !== 0) {
                $this->db = $this->dbConnect();
                $cf = $this->db->admod_config("id = ?", intval($id))->select(
                        "app, packageName, packageNameMarketing, developer, admod_large, admod_small, 
                        adspaceid, publisherid, `status`, is_download, strSeparatorYT, strSeparatorGD, 
                        versionApp, isParserOnline, youtube_api_key, isHd");
                if (!empty($cf[0])) {
                    $result = $cf[0];
                } else {
                    $this->app->response()->status(404);
                    $result = array('message' => 'Get Package Setting Fail!');
                }
            } else {
                $this->app->response()->status(404);
                $result = array('message' => 'Invalid Parameters!');
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

    public function postAddNew() {
        $post = $this->app->request()->post();
        $result = null;
        $vaildate = self::ValidateAppList($post);
        if (empty($vaildate)) {
            try {
                $db = $this->dbConnect();
                $db->transaction = "BEGIN";
                $lastID = $db->admod_config()->insert(array(
                    "app" => $post['appName'], "packageName" => $post['packageName'], "packageNameMarketing" => $post['packageNameMarketing'],
                    "developer" => $post['developer'], "admod_large" => $post['admodLarge'], "admod_small" => $post['admodSmall'],
                    "adspaceid" => $post['adspaceId'], "publisherid" => $post['publisherId'],
                    "strSeparatorYT" => $post['strSeparatorYT'], "strSeparatorGD" => $post['strSeparatorGD'],
                    "versionApp" => $post['versionApp'], "status" => intval($post['status']), "is_download" => $post['isDownload'],
                    "isParserOnline" => intval($post['isParserOnline']), "youtube_api_key" => $post['youTubeApiKey'],
                    "isHd" => intval($post['isHD'])
                        )
                );
                $db->transaction = "COMMIT";
                $items = $db->admod_config("id = ?", $lastID);
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
        $request = $this->app->request();
        $name = $request->post('name');
        $url = $request->post('url');
        try {
            $sql = "INSERT INTO commodores (name, url) VALUES (:name, :url)";
            $s = $this->db->prepare($sql);
            $s->bindParam("name", $name);
            $s->bindParam("url", $url);
            $s->execute();
        } catch (\PDOException $e) {
            echo 'Exception: ' . $e->getMessage();
        }
    }

    public function putItem($id) {
        $request = $this->app->request();
        $name = $request->put('name');
        $url = $request->put('url');

        try {
            $sql = "update commodores set url=:url, name=:name where id=:id";
            $s = $this->db->prepare($sql);
            $s->bindParam("id", $id);
            $s->bindParam("name", $name);
            $s->bindParam("url", $url);
            $s->execute();
        } catch (\PDOException $e) {
            echo 'Exception: ' . $e->getMessage();
        }
    }

    public function deleteItem($id) {
        try {
            $sql = "delete from commodores where id=:id";
            $s = $this->db->prepare($sql);
            $s->bindParam("id", $id);
            $s->execute();
        } catch (\PDOException $e) {
            echo 'Exception: ' . $e->getMessage();
        }
    }

    private function ValidateAppList($arrayData) {
        $return = null;
        Valitron\Validator::langDir(LIB_ROOT . 'Valitron/lang');
        Valitron\Validator::lang('vi');
        $v = new Valitron\Validator($arrayData);
        Valitron\Validator::addRule('myBool', function($field, $value, array $params) {
                    $acceptable = array('0', '1', 0, 1, false, true);
                    return in_array($value, $acceptable, true);
                }, 'không đúng định dạng');
        $v->rule('required', ['appName', 'packageName', 'packageNameMarketing', 'developer', 'admodLarge', 'admodSmall', 'adspaceId', 'publisherId', 'strSeparatorYT', 'strSeparatorGD', 'versionApp', 'status', 'isDownload', 'isParserOnline', 'youTubeApiKey', 'isHD']);
        $v->rule('myBool', ['isDownload', 'isParserOnline', 'isHD']);
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
