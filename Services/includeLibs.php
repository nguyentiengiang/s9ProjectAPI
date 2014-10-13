<?php

if (!defined("LIB_ROOT") && !defined("HELP_ROOT")) {
    define("LIB_ROOT", dirname(__FILE__) . "../../lib/");
    define("HELP_ROOT", dirname(__FILE__) . "../../helper/");
}

//API Core libraries
require_once(LIB_ROOT . "Slim/Slim.php");
//Libraries data access and valid data
require_once(LIB_ROOT . "idiorm.php");
require_once(LIB_ROOT . "Valitron/Validator.php");
//Libraries file and log
require_once(LIB_ROOT . "class.File.php");

//Libraries for debug and benckmark
if (MODE_APP === "DEBUG") {
    include_once(LIB_ROOT . "Kint/Kint.class.php");
    include_once(LIB_ROOT . "Ubench.php");
} else {
    include_once(LIB_ROOT . "Ubench.php");
    include_once(LIB_ROOT . "class.Release.php");
}

//Libraries helper of s9 project for request and respone
require_once(HELP_ROOT . "RequestResult.php");
require_once(HELP_ROOT . "ResponeHelper.php");