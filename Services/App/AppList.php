<?php

// Slim Auto Loader
\Slim\Slim::registerAutoloader();

/**
 * APIs provider AppList.
 *
 * @file    Services/App/AppList.php
 * @desc    AppList
 * @author  Tien Giang (ongteu)
 * @license BSD/GPLv2
 *
 * Provides a basic respone function for s9Project Helper.
 *
 * copyright (c) TienGiang
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class AppList {

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
        $this->app->map('/json/admod', array($this, 'getConfigByPackage'))->via("GET", "POST");
        $this->app->get('/getAll', array($this, 'getAll'));
        $this->app->get('/getConfigById/:id', array($this, 'getConfigByID'));
        $this->app->post('/postAdd', array($this, 'postAddNew'));
        $this->app->post('/postUpdate/:id', array($this, 'postUpdate'));
        $this->app->delete('/delete', array($this, 'deleteConfig'))->via("GET", "POST");
        $this->app->run();
    }

    public function index() {
        $status = 200;
        $body = array("result" => array("message" => "Wellcome to list app APIs"));
        $headers = array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body);
    }

    public function getConfigByPackage() {
        $status = 200;
        $headers = array();
        $body = array();
        try {
            $package = trim($this->app->request->get('package'));
            $appName = trim($this->app->request->get('app'));
            $arrCondition = array();
            if (!empty($package)) {
                $arrCondition = array("packageName" => strip_tags($package));
            } else if (!empty($appName)) {
                $arrCondition = array("appName" => $appName);
            }
            ORM::configure($this->ORMConfig);
            $ads = ORM::for_table("AppList")->select_many(
                            array("large" => "admodLarge", "small" => "admodSmall", "adspaceid", "publisherid",
                                "packageName", "packageNameMarketing", "developer", "admobEnable",
                                "strSeparatorYT", "strSeparatorGD", "versionApp", "isParserOnline" => "isParserOnlineYT",
                                "isParserOnlineGD", "youtube_api_key", "isHD", "isDownload")
                    )->where_equal($arrCondition)->find_array();
            if (count($ads) > 0) {
                $body = array("result" => $ads[0]);
            } else {
                $status = 404;
                $body = array("result" => array('message' => 'Get Package Setting Fail!'));
            }
        } catch (Exception $e) {
            $status = 500;
            $headers += array("Connection" => "close", "Warning" => "Server execute in error");
            $body = array("result" => array("message" => "You have a trouble request", "error" => $e->getMessage()));
            s9Helper\MyFile\Log::write("File:" . $e->getFile() . PHP_EOL . "Message:" . $e->getMessage() . PHP_EOL . "Line:" . $e->getLine() . PHP_EOL . "Code:" . $e->getCode() . PHP_EOL . "Trace:" . $e->getTraceAsString(), ".ExecuteException", APP_NAME);
        }
        $headers += array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body, "app");
    }

    public function getAll() {
        $status = 200;
        $headers = array();
        $body = array();
        try {
            ORM::configure($this->ORMConfig);
            $ads = ORM::for_table("AppList")->select_many(
                            array("id", "appName", "packageName", "packageNameMarketing", "developer",
                                "admodLarge", "admodSmall", "adspaceid", "publisherid",
                                "strSeparatorYT", "strSeparatorGD", "isParserOnlineYT", "isParserOnlineGD",
                                "versionApp", "isHD", "isDownload", "admobEnable", "youtube_api_key")
                    )->find_array();
            if (count($ads) > 0) {
                $body = array("result" => $ads);
            } else {
                $status = 404;
                $body = array("result" => array('message' => 'Get Package Setting Fail!'));
            }
        } catch (Exception $e) {
            $status = 500;
            $headers += array("Connection" => "close", "Warning" => "Server execute in error");
            $body = array("result" => array("message" => "You have a trouble request", "error" => $e->getMessage()));
            s9Helper\MyFile\Log::write("File:" . $e->getFile() . PHP_EOL . "Message:" . $e->getMessage() . PHP_EOL . "Line:" . $e->getLine() . PHP_EOL . "Code:" . $e->getCode() . PHP_EOL . "Trace:" . $e->getTraceAsString(), ".ExecuteException", APP_NAME);
        }
        $headers += array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body, "apps");
    }

    public function getConfigByID($id) {
        $status = 200;
        $headers = array();
        $body = array();
        try {
            ORM::configure($this->ORMConfig);
            $ads = ORM::for_table("AppList")->select_many(
                            array("id", "appName", "packageName", "packageNameMarketing", "developer",
                                "admodLarge", "admodSmall", "adspaceid", "publisherid",
                                "strSeparatorYT", "strSeparatorGD", "isParserOnlineYT", "isParserOnlineGD",
                                "versionApp", "isHD", "isDownload", "admobEnable", "youtube_api_key")
                    )->where_equal("id", $id)->find_array();
            if (count($ads) > 0) {
                $body = array("result" => $ads[0]);
            } else {
                $status = 404;
                $body = array("result" => array('message' => 'Get Package Setting Fail!'));
            }
        } catch (Exception $e) {
            $status = 500;
            $headers += array("Connection" => "close", "Warning" => "Server execute in error");
            $body = array("result" => array("message" => "You have a trouble request", "error" => $e->getMessage()));
            s9Helper\MyFile\Log::write("File:" . $e->getFile() . PHP_EOL . "Message:" . $e->getMessage() . PHP_EOL . "Line:" . $e->getLine() . PHP_EOL . "Code:" . $e->getCode() . PHP_EOL . "Trace:" . $e->getTraceAsString(), ".ExecuteException", APP_NAME);
        }
        $headers += array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body, $app);
    }

    public function postAddNew() {
        $status = 200;
        $headers = array();
        $body = array();
        ORM::configure($this->ORMConfig);
        try {
            $post = $this->app->request()->post();
            $validate = self::ValidateAppList($post);
            if (empty($validate)) {
                ORM::get_db(ORM::DEFAULT_CONNECTION)->beginTransaction();
                $newCf = ORM::for_table('AppList')->create();
                $newCf->set(array(
                    "appName" => trim($post['appName']), "packageName" => trim($post['packageName']), "packageNameMarketing" => trim($post['packageNameMarketing']),
                    "developer" => trim($post['developer']), "admodLarge" => trim($post['admodLarge']), "admodSmall" => trim($post['admodSmall']),
                    "adspaceid" => trim($post['adspaceId']), "publisherid" => trim($post['publisherId']),
                    "strSeparatorYT" => trim($post['strSeparatorYT']), "strSeparatorGD" => trim($post['strSeparatorGD']),
                    "isParserOnlineYT" => intval($post['isParserOnlineYT']), "isParserOnlineGD" => intval($post['isParserOnlineGD']),
                    "versionApp" => trim($post['versionApp']), "isHD" => intval($post['isHD']), "isDownload" => intval($post['isDownload']),
                    "admobEnable" => intval($post['admobEnable']), "youtube_api_key" => trim($post['youTubeApiKey'])));
                $newCf->save();
                $commitOK = ORM::get_db(ORM::DEFAULT_CONNECTION)->commit();
                if ($commitOK) {
                    $body = array("result" => array('message' => 'Inserted ' . $newCf->id()));
                } else {
                    $rollBackOK = ORM::get_db(ORM::DEFAULT_CONNECTION)->rollBack();
                    if ($rollBackOK) {
                        $body = array("result" => array('message' => 'Insert fail! Notice admin for errors'));
                    } else {
                        $body = array("result" => array('message' => "Insert fail! Can't rollback rightnow!"));
                    }
                    s9Helper\MyFile\Log::write(ORM::get_query_log(ORM::DEFAULT_CONNECTION), "PDOFail", APP_NAME);
                }
            } else {
                $body = array("result" => array('message' => 'Valid data fail', 'errors' => $validate));
            }
        } catch (PDOException $e) {
            $status = 500;
            ORM::get_db(ORM::DEFAULT_CONNECTION)->rollBack();
            $headers += array("Connection" => "close", "Warning" => "Data Server executed error");
            $body = array("result" => array('message' => 'Valid field data broken!', 'errors' => $e->getMessage()));
            s9Helper\MyFile\Log::write("File:" . $e->getFile() . PHP_EOL . "Message:" . $e->getMessage() . PHP_EOL . "Line:" . $e->getLine() . PHP_EOL . "Code:" . $e->getCode() . PHP_EOL . "Trace:" . $e->getTraceAsString(), ".PDOException", APP_NAME);
        } catch (Exception $e) {
            $status = 500;
            $headers += array("Connection" => "close", "Warning" => "Server execute in error");
            $body = array("result" => array("message" => "You have a trouble request", "error" => $e->getMessage()));
            s9Helper\MyFile\Log::write("File:" . $e->getFile() . PHP_EOL . "Message:" . $e->getMessage() . PHP_EOL . "Line:" . $e->getLine() . PHP_EOL . "Code:" . $e->getCode() . PHP_EOL . "Trace:" . $e->getTraceAsString(), ".ExecuteException", APP_NAME);
        }
        $headers += array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body);
    }

    public function postUpdate($id) {
        $status = 200;
        $headers = array();
        $body = array();
        ORM::configure($this->ORMConfig);
        try {
            $post = $this->app->request()->post();
            $vaildate = self::ValidateAppList($post);
            if (empty($vaildate)) {
                ORM::get_db(ORM::DEFAULT_CONNECTION)->beginTransaction();
                $updateCf = ORM::for_table('AppList')->find_one($id);
                $updateCf->set(array(
                    "appName" => $post['appName'], "packageName" => $post['packageName'], "packageNameMarketing" => $post['packageNameMarketing'],
                    "developer" => $post['developer'], "admodLarge" => $post['admodLarge'], "admodSmall" => $post['admodSmall'],
                    "adspaceid" => $post['adspaceId'], "publisherid" => $post['publisherId'],
                    "strSeparatorYT" => $post['strSeparatorYT'], "strSeparatorGD" => $post['strSeparatorGD'],
                    "versionApp" => $post['versionApp'], "admobEnable" => intval($post['admobEnable']), "isDownload" => $post['isDownload'],
                    "isParserOnlineYT" => intval($post['isParserOnlineYT']), "isParserOnlineYT" => intval($post['isParserOnlineYT']), 
                    "youtube_api_key" => $post['youTubeApiKey'], "isHD" => intval($post['isHD'])));
                $updateCf->save();
                $commitOK = ORM::get_db(ORM::DEFAULT_CONNECTION)->commit();
                if ($commitOK) {
                    $body = array("result" => array('message' => 'Updated ' . $updateCf->get("id")));
                } else {
                    $rollBackOK = ORM::get_db(ORM::DEFAULT_CONNECTION)->rollBack();
                    if ($rollBackOK) {
                        $body = array("result" => array('message' => 'Insert fail! Notice admin for errors'));
                    } else {
                        $body = array("result" => array('message' => "Insert fail! Can't rollback rightnow!"));
                    }
                    s9Helper\MyFile\Log::write(ORM::get_query_log(ORM::DEFAULT_CONNECTION), "PDOFail", APP_NAME);
                }
            } else {
                $body = array("result" => array('message' => 'Valid data fail', 'errors' => $vaildate));
            }
        } catch (PDOException $e) {
            $status = 500;
            ORM::get_db(ORM::DEFAULT_CONNECTION)->rollBack();
            $headers += array("Connection" => "close", "Warning" => "Data Server executed error");
            $body = array("result" => array('message' => 'Validate field data broken!', 'errors' => $e->getMessage()));
            s9Helper\MyFile\Log::write("File:" . $e->getFile() . PHP_EOL . "Message:" . $e->getMessage() . PHP_EOL . "Line:" . $e->getLine() . PHP_EOL . "Code:" . $e->getCode() . PHP_EOL . "Trace:" . $e->getTraceAsString(), ".PDOException", APP_NAME);
        } catch (Exception $e) {
            $status = 500;
            $headers += array("Connection" => "close", "Warning" => "Server execute in error");
            $body = array("result" => array("message" => "You have a trouble request", "error" => $e->getMessage()));
            s9Helper\MyFile\Log::write("File:" . $e->getFile() . PHP_EOL . "Message:" . $e->getMessage() . PHP_EOL . "Line:" . $e->getLine() . PHP_EOL . "Code:" . $e->getCode() . PHP_EOL . "Trace:" . $e->getTraceAsString(), ".ExecuteException", APP_NAME);
        }
        $headers += array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body);
    }

    public function deleteConfig() {
        $status = 200;
        $headers = array();
        $body = array();
        ORM::configure($this->ORMConfig);
        try {
            $post = $this->app->request()->post();
        } catch (PDOException $e) {
            $status = 500;
            ORM::get_db(ORM::DEFAULT_CONNECTION)->rollBack();
            $body = array("result" => array('message' => 'Validate field broken!', 'errors' => $e->getMessage()));
            s9Helper\MyFile\Log::write("File:" . $e->getFile() . PHP_EOL . "Message:" . $e->getMessage() . PHP_EOL . "Line:" . $e->getLine() . PHP_EOL . "Code:" . $e->getCode() . PHP_EOL . "Trace:" . $e->getTraceAsString(), ".PDOException", APP_NAME);
        } catch (Exception $e) {
            $status = 500;
            $headers += array("Connection" => "close", "Warning" => "Server execute in error");
            $body = array("result" => array("message" => "You have a trouble request", "error" => $e->getMessage()));
            s9Helper\MyFile\Log::write("File:" . $e->getFile() . PHP_EOL . "Message:" . $e->getMessage() . PHP_EOL . "Line:" . $e->getLine() . PHP_EOL . "Code:" . $e->getCode() . PHP_EOL . "Trace:" . $e->getTraceAsString(), ".ExecuteException", APP_NAME);
        }
        $headers += array("Content-Type" => $this->app->request()->getMediaType());
        s9Helper\HandlingRespone\MyRespone::result($this->app->request, $this->app->response, $status, $headers, $body);
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
        $v->rule('required', [
            'appName', 'packageName', 'packageNameMarketing', 'developer', 
            'admodLarge', 'admodSmall', 'adspaceId', 'publisherId', 
            'strSeparatorYT', 'strSeparatorGD', 'isParserOnlineYT', 'isParserOnlineGD', 
            'versionApp', 'isHD', 'isDownload', 'admobEnable', 'youTubeApiKey']);
        $v->rule('myBool', ['isHD', 'isDownload']);
        $v->rule('integer', ['id', 'isParserOnlineYT', 'isParserOnlineGD']);
        $v->rule('min', ['id'], 1);
        if ($v->validate()) {
            $return = null;
        } else {
            $return = $v->errors();
        }
        return $return;
    }

}
