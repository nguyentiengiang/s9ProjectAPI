<?php

if (!defined("LIB_ROOT")) {
    define("LIB_ROOT", dirname(__FILE__) . "../../lib/");
}

//APIs Core libraries

spl_autoload_register('autoload');

/**
 * PSR-0 autoloader
 */
function autoload($className) {
    $thisClass = str_replace(__NAMESPACE__ . '\\', '', __CLASS__);

    $baseDir = LIB_ROOT;

    if (substr($baseDir, -strlen($thisClass)) === $thisClass) {
        $baseDir = substr($baseDir, 0, -strlen($thisClass));
    }

    $className = ltrim($className, '\\');
    $fileName = $baseDir;
    $namespace = '';
    if ($lastNsPos = strripos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName .= str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

    if (file_exists($fileName)) {
        require $fileName;
    }
}

//Libraries for debug and benckmark
if (MODE_APP === "DEBUG") {
    include_once(LIB_ROOT . "Kint/Kint.class.php");
} else {
    include_once(LIB_ROOT . "Kint/KintRelease.php");
}

