<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

\Slim\Slim::registerAutoloader();

/*
 * deviceId, registrationGcmId, packageName, gMail, isHide
 */

class AppGCM {

    public function __construct($dbHost, $dbName, $dbUser, $dbPass) {
        $this->dbHost = $dbHost;
        $this->dbName = $dbName;
        $this->dbUser = $dbUser;
        $this->dbPass = $dbPass;

        $this->app = new \Slim\Slim(array(
//            'mode' => 'production',
//            'debug' => false,
            'mode' => 'development',
            'debug' => true,
        ));
    }

    public function enable() {
        $this->app->get('/', array($this, 'index'));
        $this->app->get('/getToView', array($this, 'getToView'));
        $this->app->post('/json/GCMPost', array($this, 'postGCMAddNew'));
        $this->app->run();
    }

    function dbConnect($cache = null) {
        $pdo = new \PDO('mysql:host=' . $this->dbHost . ';dbname=' . $this->dbName, $this->dbUser, $this->dbPass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
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
            $cfs = $db->GCM_User()->select("deviceId, registrationGcmId, packageName, gMail, isHide");
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

    public function getToView() {
        $result = null;
        try {
            $db = $this->dbConnect();
            $cfs = $db->GCM_User()->select("packageName, COUNT(1) as 'Devices'")
                            ->group("packageName")->order("COUNT(1) DESC");
            if (count($cfs)) {
                $result = array();
                foreach ($cfs as $cf) {
                    $data = iterator_to_array($cf);
                    $result += array($data['packageName'] => $data['Devices']);
                }
            } else {
                $this->app->response()->status(400);
                $result = array('message' => 'Nothing to do there!');
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

    public function postGCMAddNew() {
        $post = $this->app->request()->post();
        $result = null;
        $vaildate = self::ValidatePostGCM($post);
        if (empty($vaildate)) {
            try {
                $db = $this->dbConnect();
                $db->transaction = "BEGIN";
                $lastID = $db->GCM_User()->insert(array(
                    "deviceId" => $post['deviceId'], "registrationGcmId" => $post['registrationGcmId'],
                    "packageName" => $post['packageName'], "gMail" => $post['gMail'], "isHide" => 0
                ));
                $db->transaction = "COMMIT";
                $items = $db->GCM_User("id = ?", $lastID);
                if (count($items) > 0) {
                    $this->app->response()->status(200);
                    $result = array('StatusCode' => 1, 'Message' => 'OK');
                } else {
                    $this->app->response()->status(200);
                    $result = array('StatusCode' => 1, 'Message' => 'OK But Not Found');
                }
            } catch (\PDOException $e) {
                $db->transaction = "ROLLBACK";
                $result = array(
                    'StatusCode' => 0, 'Message' => 'Invalid Parameters',
                    'reasons' => 'Validate broken!', 'errors' => $e->getMessage()
                );
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
            $result = array(
                'StatusCode' => 0, 'Message' => 'Vaildate Failue',
                'status' => 'error', 'errors' => $vaildate
            );
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

    private function ValidatePostGCM($arrayData) {
        $return = null;
        Valitron\Validator::langDir(LIB_ROOT . 'Valitron/lang');
        Valitron\Validator::lang('vi');
        $v = new Valitron\Validator($arrayData);
        $v->rule('required', ['deviceId', 'registrationGcmId', 'packageName']);
        $v->rule('email', ['gMail']);
        if ($v->validate()) {
            $return = null;
        } else {
            $return = $v->errors();
        }
        return $return;
    }

}

?>
