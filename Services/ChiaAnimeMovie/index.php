<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

//require '../../lib/Slim/Slim.php';
//include "../../lib/NotORM.php";
//
//\Slim\Slim::registerAutoloader();

require 'ChiaAnimeMovie.php';

$dbUser = "s2admin";
$dbPass = "mdata!6789";
$dbHost = "localhost";
$dbName = "temp2";

$anime = new ChiaAnimeMovie\ChiaAnimeMovie($dbHost, $dbName, $dbUser, $dbPass);

$anime->enable();

?>
