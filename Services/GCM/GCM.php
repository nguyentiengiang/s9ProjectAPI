<?php

Slim\Slim::registerAutoloader();

/**
 * for GCM database, get infomation of user
 * deviceId, registrationGcmId, packageName, gMail, isHide
 */
class AppGCM {

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
        $this->app->get('/getToView', array($this, 'getToView'));
        $this->app->post('/json/GCMPost', array($this, 'postGCMAddNew'));
        $this->app->post('/iOSUser', array($this, 'postAddNewiOSUser'));
        $this->app->run();
    }

    public function index() {
        $status = 200;
        $body = array("result" => array("message" => "Wellcome to GCM"));
        $headers = array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body);
    }

    public function getToView() {
        $status = 200;
        $headers = array();
        $body = array();
        ORM::configure($this->ORMConfig);
        try {
            $gcmArr = ORM::for_table("GCM_User")->select_many_expr(array("packageName", "Devices" => "COUNT(1)"))->group_by("packageName")->order_by_expr("COUNT(1) DESC")->find_array();
            if (count($gcmArr)) {
                $result = array();
                foreach ($gcmArr as $element) {
                    $result += array($element['packageName'] => $element['Devices']);
                }
                $body = array("result" => $result);
            } else {
                $body = array("result" => array('message' => 'Nothing here :('));
            }
        } catch (PDOException $e) {
            $status = 500;
            $headers += array("Connection" => "close", "Warning" => "Data Server executed error");
            $body = array("result" => array('StatusCode' => 0, 'message' => 'Request Query Broken', 'errors' => $e->getMessage()));
            s9Helper\MyFile\Log::write("File:" . $e->getFile() . PHP_EOL . "Message:" . $e->getMessage() . PHP_EOL . "Line:" . $e->getLine() . PHP_EOL . "Code:" . $e->getCode() . PHP_EOL . "Trace:" . $e->getTraceAsString(), ".PDOException", APP_NAME);
        } catch (Exception $e) {
            $status = 500;
            $headers += array("Connection" => "close", "Warning" => "Server execute in error");
            $body = array("result" => array('StatusCode' => 0, "message" => "You have a trouble request", "error" => $e->getMessage()));
            s9Helper\MyFile\Log::write("File:" . $e->getFile() . PHP_EOL . "Message:" . $e->getMessage() . PHP_EOL . "Line:" . $e->getLine() . PHP_EOL . "Code:" . $e->getCode() . PHP_EOL . "Trace:" . $e->getTraceAsString(), ".ExecuteException", APP_NAME);
        }
        $headers += array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body);
    }

    public function postGCMAddNew() {
        $status = 200;
        $headers = array();
        $body = array();
        ORM::configure($this->ORMConfig);
        try {
            $post = s9Helper\HandlingRequest\MyRequest::cleanPOST($this->app->request);
            $vaildate = self::ValidatePostGCM($post);
            if (empty($vaildate)) {
                ORM::get_db(ORM::DEFAULT_CONNECTION)->beginTransaction();
                $updateCf = ORM::for_table('GCM_User')->create();
                $updateCf->set(array(
                    "deviceId" => $post['deviceId'], "registrationGcmId" => $post['registrationGcmId'],
                    "packageName" => $post['packageName'], "gMail" => $post['gMail'],
                    "isHide" => 0));
                $updateCf->save();
                $commitOK = ORM::get_db(ORM::DEFAULT_CONNECTION)->commit();
                if ($commitOK) {
                    $body = array("result" => array('StatusCode' => 1, 'Message' => 'OK'));
                } else {
                    $rollBackOK = ORM::get_db(ORM::DEFAULT_CONNECTION)->rollBack();
                    if ($rollBackOK) {
                        $body = array("result" => array('StatusCode' => 0, 'Message' => 'Invalid Parameters', 'error' => "Rollback transaction!"));
                    } else {
                        $body = array("result" => array('StatusCode' => 0, 'Message' => 'Invalid Parameters', 'error' => "Can't rollback right now."));
                    }
                    s9Helper\MyFile\Log::write(ORM::get_query_log(ORM::DEFAULT_CONNECTION), "PDOFail", APP_NAME);
                }
            } else {
                $body = array("result" => array('StatusCode' => 0, 'message' => 'Valid data fail', 'errors' => $vaildate));
            }
        } catch (PDOException $e) {
            $status = 500;
            ORM::get_db(ORM::DEFAULT_CONNECTION)->rollBack();
            $headers += array("Connection" => "close", "Warning" => "Data Server executed error");
            $body = array("result" => array('StatusCode' => 0, 'message' => 'Validate field data broken!', 'errors' => $e->getMessage()));
            s9Helper\MyFile\Log::write("File:" . $e->getFile() . PHP_EOL . "Message:" . $e->getMessage() . PHP_EOL . "Line:" . $e->getLine() . PHP_EOL . "Code:" . $e->getCode() . PHP_EOL . "Trace:" . $e->getTraceAsString(), ".PDOException", APP_NAME);
        } catch (Exception $e) {
            $status = 500;
            $headers += array("Connection" => "close", "Warning" => "Server execute in error");
            $body = array("result" => array('StatusCode' => 0, "message" => "You have a trouble request", "error" => $e->getMessage()));
            s9Helper\MyFile\Log::write("File:" . $e->getFile() . PHP_EOL . "Message:" . $e->getMessage() . PHP_EOL . "Line:" . $e->getLine() . PHP_EOL . "Code:" . $e->getCode() . PHP_EOL . "Trace:" . $e->getTraceAsString(), ".ExecuteException", APP_NAME);
        }
        $headers += array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body);
    }

    public function postAddNewiOSUser() {
        $status = 200;
        $headers = array();
        $body = array();
        ORM::configure($this->ORMConfig);
        try {
            $post = s9Helper\HandlingRequest\MyRequest::cleanPOST($this->app->request);
            $vaildate = self::ValidatePostIosUser($post);
            if (empty($vaildate)) {
                ORM::get_db(ORM::DEFAULT_CONNECTION)->beginTransaction();
                $updateCf = ORM::for_table('iOS_User')->create();
                $updateCf->set(array(
                    "deviceId" => $post['deviceId'], "appId" => $post['appId'],
                    "mail" => $post['mail'], "isHide" => 0)
                );
                $updateCf->save();
                $commitOK = ORM::get_db(ORM::DEFAULT_CONNECTION)->commit();
                if ($commitOK) {
                    $body = array("result" => array('StatusCode' => 1, 'Message' => 'OK'));
                } else {
                    $rollBackOK = ORM::get_db(ORM::DEFAULT_CONNECTION)->rollBack();
                    if ($rollBackOK) {
                        $body = array("result" => array('StatusCode' => 0, 'Message' => 'Invalid Parameters', 'error' => "Rollback transaction!"));
                    } else {
                        $body = array("result" => array('StatusCode' => 0, 'Message' => 'Invalid Parameters', 'error' => "Can't rollback right now."));
                    }
                    s9Helper\MyFile\Log::write(ORM::get_query_log(ORM::DEFAULT_CONNECTION), "PDOFail", APP_NAME);
                }
            } else {
                $body = array("result" => array('StatusCode' => 0, 'message' => 'Valid data fail', 'errors' => $vaildate));
            }
        } catch (PDOException $e) {
            $status = 500;
            ORM::get_db(ORM::DEFAULT_CONNECTION)->rollBack();
            $headers += array("Connection" => "close", "Warning" => "Data Server executed error");
            $body = array("result" => array('StatusCode' => 0, 'message' => 'Validate field data broken!', 'errors' => $e->getMessage()));
            s9Helper\MyFile\Log::write("File:" . $e->getFile() . PHP_EOL . "Message:" . $e->getMessage() . PHP_EOL . "Line:" . $e->getLine() . PHP_EOL . "Code:" . $e->getCode() . PHP_EOL . "Trace:" . $e->getTraceAsString(), ".PDOException", APP_NAME);
        } catch (Exception $e) {
            $status = 500;
            $headers += array("Connection" => "close", "Warning" => "Server execute in error");
            $body = array("result" => array('StatusCode' => 0, "message" => "You have a trouble request", "error" => $e->getMessage()));
            s9Helper\MyFile\Log::write("File:" . $e->getFile() . PHP_EOL . "Message:" . $e->getMessage() . PHP_EOL . "Line:" . $e->getLine() . PHP_EOL . "Code:" . $e->getCode() . PHP_EOL . "Trace:" . $e->getTraceAsString(), ".ExecuteException", APP_NAME);
        }
        $headers += array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body);
    }

    private function ValidatePostGCM($arrayData) {
        $return = null;
        Valitron\Validator::langDir(LIB_ROOT . 'Valitron/lang');
        Valitron\Validator::lang('vi');
        $v = new Valitron\Validator($arrayData);
        $v->rule('required', ['registrationGcmId', 'packageName']);
        $v->rule('email', ['gMail']);
        if ($v->validate()) {
            $return = null;
        } else {
            $return = $v->errors();
        }
        return $return;
    }

    private function ValidatePostIosUser($arrayData) {
        $return = null;
        Valitron\Validator::langDir(LIB_ROOT . 'Valitron/lang');
        Valitron\Validator::lang('vi');
        $v = new Valitron\Validator($arrayData);
//        $v->rule('required', ['deviceId', 'appId']);
        $v->rule('email', ['mail']);
        if ($v->validate()) {
            $return = null;
        } else {
            $return = $v->errors();
        }
        return $return;
    }

}
