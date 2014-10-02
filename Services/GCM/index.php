<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

if (!defined('LIB_ROOT')) {
    define('LIB_ROOT', dirname(__FILE__) . '/../../lib/');
    define('HELP_ROOT', dirname(__FILE__) . '/../../helper/');
    //Libraries for run api
    require_once(LIB_ROOT . 'Slim/Slim.php');
    require_once(LIB_ROOT . 'NotORM.php');
    require_once(LIB_ROOT . 'Valitron/Validator.php');
    require_once(LIB_ROOT . 'class.File.php');    
    //Libraries for debug and benckmark
//    require_once(LIB_ROOT . 'Kint/Kint.class.php');
//    require_once(LIB_ROOT . 'Ubench.php');
    //Libraries for debug and benckmark
//    require_once(HELP_ROOT . 'XMLHelper.php');
}
require_once 'GCM.php';


$bench = new Ubench;
$bench->start();

$dbHost = "localhost";
//$dbHost = "gcmmobi.com";
$dbUser = "s2admin";
$dbPass = "mdata!6789";
$dbName = "AppGCM";

$app = new AppGCM($dbHost, $dbName, $dbUser, $dbPass);
$app->enable();

$bench->end();
$str .= PHP_EOL . 'Time: ' . $bench->getTime(true) . ' microsecond -> ' . $bench->getTime(false, '%d%s');
$str .= PHP_EOL . 'MemoryPeak: ' . $bench->getMemoryPeak(true) . ' bytes -> ' . $bench->getMemoryPeak(false, '%.3f%s');
$str .= PHP_EOL . 'MemoryUsage: ' . $bench->getMemoryUsage(true);
MyFile\Log::write($str, 'APIGCM', 'GCM');
?>
