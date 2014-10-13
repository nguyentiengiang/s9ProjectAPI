<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
//define('LIB_ROOT', dirname(__FILE__) . '/../lib/');
//require(LIB_ROOT . 'Kint/Kint.class.php');
//require 'base69Helper.php';
//
//echo s9ProjectHelper\Base69::encodeType2("http://movietube.cc/watch.php?v=HpKzRPMe5JU", 1);
// 1. lấy tên hàm callback
$cb = $_GET['callback'];
# 2. trả lời với dữ liệu json {'a':1}
$json = '{"a":1}';
echo $cb + "(" . json_decode($json) . ");"
?>
