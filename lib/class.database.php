<?php

/*
 * Tien Giang Developer
 * Email: nguyentiengiang@outlook.com
 * Phone: +84 1282 303 100
 */

namespace Database;

use R;
use File\Log;
use \PDO;
use \PDOException;

class RedBean {

    const FETCH_ASSOC = 0;
    const FETCH_OBJ = 1;
    const FETCH_KEY_PAIR = 2;

    private $dbDSN = null;
    private $dbUsername = null;
    private $dbPassword = null;
            
    function __construct() {
        $this->setDbDSN($GLOBALS['app']['dbVar']['dbDriver'], $GLOBALS['app']['dbVar']['dbHost'], $GLOBALS['app']['dbVar']['dbPort'], $GLOBALS['app']['dbVar']['dbName']);
        $this->setDbUsername($GLOBALS['app']['dbVar']['dbUsername']);
        $this->setDbPassword($GLOBALS['app']['dbVar']['dbPassword']);
    }
    
    function findByFields($sqlTable = '', $arrField = array(), $arrCondition = array(), $arrSortBy = array(), $typeResult = 0) {
        $arrResult = array();
        R::setup($this->getDbDSN(), $this->getDbUsername(), $this->getDbPassword());
        R::selectDatabase('default');
        //$arrResult = R::getAssocRow("SELECT : FROM ? WHERE ? ORDER BY ? ?");
        $arrResult = (object) R::getAll("SELECT id, app, status FROM admod_config WHERE status = 1");
        return $arrResult;
    }
    
    public function __destruct() {
    }

    public function getDbDSN() {
        return $this->dbDSN;
    }

    public function getDbUsername() {
        return $this->dbUsername;
    }

    public function getDbPassword() {
        return $this->dbPassword;
    }

    public function setDbDSN($dbDriver = '', $dbHost = '', $dbPort = '', $dbName = '', $dbEncoding = '') {
        $this->dbDSN = $dbDriver . ':host=' . $dbHost . ':' . $dbPort . ';dbname=' . $dbName;
    }

    public function setDbUsername($dbUsername) {
        $this->dbUsername = $dbUsername;
    }

    public function setDbPassword($dbPassword) {
        $this->dbPassword = $dbPassword;
    }

}

class RedBeanHelper {
    
    public static function buildSQLSelectQuery($sqlTable = '', $arrField = array(), $arrCondition = array(), $arrSort = array(), $limit = '') {
        $strSQL = null;
        if (!empty($sqlTable)) {
            $strSQL = "SELECT ";
            if (!empty($arrField)) {
                foreach ($arrField as $field) {
                    $strSQL .= $field . ", ";
                }
                $strSQL = substr($strSQL, 0, -2);
            } else {
                $strSQL .= "*";
            }
            $strSQL .= " FROM ". $sqlTable;
            if (!empty($arrCondition)) {
                $strSQL .= " WHERE ";
                foreach ($arrCondition as $condition) {
                    $strSQL .= $condition . " AND ";
                }
                $strSQL = substr($strSQL, 0, -5);
            }
            if (!empty($arrSort)) {
                $strSQL .= " ORDER BY ";
                foreach ($arrSort as $orderVal) {
                    $strSQL .= $orderVal . ", ";
                }
                $strSQL = substr($strSQL, 0, -2);
            }
            if (!empty($limit)) {
                $strSQL .= " LIMIT " . $limit;
            }
        }
        return $strSQL;
    }
}

class DAL {

    static public function connect() {
        $dbName = $GLOBALS['app']['dbVar']['dbName'];
        $dbDriver = $GLOBALS['app']['dbVar']['dbDriver'];
        $dbHost = $GLOBALS['app']['dbVar']['dbHost'];
        $dbUsername = $GLOBALS['app']['dbVar']['dbUsername'];
        $dbPassword = $GLOBALS['app']['dbVar']['dbPassword'];
        $dbPort = $GLOBALS['app']['dbVar']['dbPort'];
        //$dbEncoding = $GLOBALS['app']['dbVar']['dbEncoding'];
        $dbCnn = null;
        try {
            $dbCnn = new PDO($dbDriver . ':host=' . $dbHost . ';port=' . $dbPort . ';dbname=' . $dbName, $dbUsername, $dbPassword);
            $dbCnn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exc) {
            Log::write($exc->getMessage());
        }
        return $dbCnn;
    }

    static public function connectWithDB($driver, $host, $port, $dbname, $user, $password) {
        $dbCnn = null;
        try {
            $dbCnn = new PDO($driver . ':host=' . $host . ';port=' . $port . ';dbname=' . $dbname, $user, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            $dbCnn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exc) {
            $fileLog = 'Log.' . date('Y_m_d') . '.' . $app->dbVar['dbLogin'] . '@' . $app->dbVar['dbName'] . '.txt';
            $content = '[' . date('Y-m-d H:i:s') . '] - ' . $exc->getMessage() . '.' . PHP_EOL;
            Log::write($content, $fileLog);
        }
        return $dbCnn;
    }

    static function querySelect($sql, $fetchType) {
        $arr = array();
        $cnn = self::connect();
        if ($cnn != null) {
            try {
                $stateSel = new \PDOStatement();
                $stateSel = $cnn->query($sql);
                $stateSel->execute();
                $stateSel->setFetchMode($fetchType);
                while ($rowSel = $stateSel->fetch()) {
                    if ($fetchType == PDO::FETCH_KEY_PAIR) {
                        $arr += $rowSel;
                    } else {
                        array_push($arr, $rowSel);
                    }
                }
            } catch (PDOException $exc) {
                $arr = null;
                $fileLog = 'Log.' . date('Y_m_d') . '.' . $app->dbVar['dbLogin'] . '@' . $app->dbVar['dbName'] . '.txt';
                $content = '[' . date('Y-m-d H:i:s') . '] - ' . $exc->getMessage() . '.' . PHP_EOL;
                Log::write($content, $fileLog);
            }
            $stateSel = null;
            $cnn = null;
        }
        return $arr;
    }

    static function querySelectWithDB($dbCnn, $sql, $fetchType) {
        $arr = array();
        if ($dbCnn != null) {
            try {
                $stateSel = new \PDOStatement();
                $stateSel = $dbCnn->query($sql);
                $stateSel->execute();
                $stateSel->setFetchMode($fetchType);
                while ($rowSel = $stateSel->fetch()) {
                    if ($fetchType == PDO::FETCH_KEY_PAIR) {
                        $arr += $rowSel;
                    } else {
                        array_push($arr, $rowSel);
                    }
                }
            } catch (PDOException $exc) {
                $arr = null;
                $fileLog = 'Log.' . date('Y_m_d') . '.' . $app->dbVar['dbLogin'] . '@' . $app->dbVar['dbName'] . '.txt';
                $content = '[' . date('Y-m-d H:i:s') . '] - ' . $exc->getMessage() . '.' . PHP_EOL;
                Log::write($content, $fileLog);
            }
            unset($stateSel);
        }
        return $arr;
    }

    static function exe($dbCnn, $sqlTable = '', $arrField = array(), $arrCondition = array(), $arrSortBy = array()) {
        $result = array();
        if ($dbCnn != null) {
            try {
                if (is_empty($sqlTable)) {
                    $sql = "SELECT ";
                    if (!is_empty($arrField)) {
                        foreach ($arrField as $field) {
                            $sql .= $field . ", ";
                        }
                        $sql = substr($sql, 0, -2);
                    } else {
                        $sql .= "*";
                    }
                    $sql .= " FROM " . $sqlTable;
                }
                $stateSel = new \PDOStatement();
            } catch (PDOException $exc) {
                $fileLog = 'Log.' . date('Y_m_d') . '.' . $app->dbVar['dbLogin'] . '@' . $app->dbVar['dbName'] . '.txt';
                $content = '[' . date('Y-m-d H:i:s') . '] - ' . $exc->getMessage() . '.' . PHP_EOL;
                Log::write($content, $fileLog);
            }
            unset($stateSel);
        }
        return $result;
    }

    static function isExists($dbCnn, $table, $fieldCheck, $condition, $valueCheck) {
        $flag = false;
        if ($dbCnn != null) {
            try {
                $stateSel = new \PDOStatement();
                $sqlCheck = "SELECT COUNT(" . $fieldCheck . ") FROM " . $table . " WHERE " . $fieldCheck . " " . $condition . " " . $valueCheck;
                $stateSel = $dbCnn->query($sqlCheck);
                $flag = ($stateSel->fetchColumn() > 0) ? true : false;
            } catch (PDOException $exc) {
                $fileLog = 'Log.' . date('Y_m_d') . '.' . $app->dbVar['dbLogin'] . '@' . $app->dbVar['dbName'] . '.txt';
                $content = '[' . date('Y-m-d H:i:s') . '] - ' . $exc->getMessage() . '.' . PHP_EOL;
                Log::write($content, $fileLog);
            }
            unset($stateSel);
            unset($dbCnn);
        }
        return $flag;
    }

}

class DBEntity {

    function __construct() {
        
    }

    /**
     * This function will try to encode $text to base64, except when $text is a number.
     * This allows us to Escape all data before they're inserted in the database, regardless of attribute type.
     * @param string $text
     * @return string encoded to base64
     */
    public function Escape($text) {
        if ($GLOBALS['app']['dbEncoding'] && !is_numeric($text)) {
            return base64_encode($text);
        }
        return addslashes($text);
    }

    /**
     * Decode $text to base64, except when $text is a number.
     * @param string $text
     * @return string decode
     */
    public function Unescape($text) {
        if ($GLOBALS['app']['dbEncoding'] && !is_numeric($text)) {
            return base64_decode($text);
        }
        return stripcslashes($text);
    }

    ////////////////////////////////
    // Table -> Object Mapping
    ////////////////////////////////

    /**
     * Executes $query against database and returns the result set as an array of POG objects
     *
     * @param string $query. SQL query to execute against database
     * @param string $objectClass. POG Object type to return
     * @param bool $lazy. If true, will also load all children/sibling
     */
    protected function fetchObjects($query, $objectClass, $lazy = true) {
        $databaseConnection = Database::Connect();
        $result = Database::Reader($query, $databaseConnection);
        $objectList = $this->createObjects($result, $objectClass, $lazy);
        return $objectList;
    }

    private function createObjects($mysql_result, $objectClass, $lazyLoad = true) {
        $objectList = array();
        if ($mysql_result != null) {
            while ($row = Database::Read($mysql_result)) {
                $pog_object = new $objectClass();
                $this->PopulateObjectAttributes($row, $pog_object);
                $objectList[] = $pog_object;
            }
        }
        return $objectList;
    }

    private function PopulateObjectAttributes($fetched_row, $pog_object) {
        $att = $this->GetAttributes($pog_object);
        foreach ($att as $column) {
            $pog_object->{$column} = $this->Unescape($fetched_row[strtolower($column)]);
        }
        return $pog_object;
    }

    public function GetAttributes($object, $type = '') {
        $columns = array();
        foreach ($object->pog_attribute_type as $att => $properties) {
            if ($properties['db_attributes'][0] != 'OBJECT') {
                if (($type != '' && strtolower($type) == strtolower($properties['db_attributes'][0])) || $type == '') {
                    $columns[] = $att;
                }
            }
        }
        return $columns;
    }

    //misc
    public static function IsColumn($value) {
        if (strlen($value) > 2) {
            if (substr($value, 0, 1) == '`' && substr($value, strlen($value) - 1, 1) == '`') {
                return true;
            }
            return false;
        }
        return false;
    }

    function __destruct() {
        
    }

}

?>