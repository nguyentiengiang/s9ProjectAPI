<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace s9Helper;

/**
 * Description of URL
 *
 * @author Tien Giang
 */
class URL {

    static function getURL($withport = false) {
        $u = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://' . $_SERVER["SERVER_NAME"] : 'https://' . $_SERVER["SERVER_NAME"];
        if ($withport) {
            $u .= ( $_SERVER["SERVER_PORT"] !== 80 ) ? ":" . $_SERVER["SERVER_PORT"] : "";
        }
        if (dirname($_SERVER['PHP_SELF']) != '\\') {
            $u .= dirname($_SERVER['PHP_SELF']);
        }
        return $u;
    }

    static function getFullWithURI($withport = false) {
        $u = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://' . $_SERVER["SERVER_NAME"] : 'https://' . $_SERVER["SERVER_NAME"];
        if ($withport) {
            $u .= ( $_SERVER["SERVER_PORT"] !== 80 ) ? ":" . $_SERVER["SERVER_PORT"] : "";
        }
        $u .= $_SERVER["REQUEST_URI"];
        return $u;
    }

    static function getURLWorking($withport = false) {
        $u = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://' . $_SERVER["SERVER_NAME"] : 'https://' . $_SERVER["SERVER_NAME"];
        if ($withport) {
            $u .= ( $_SERVER["SERVER_PORT"] !== 80 ) ? ":" . $_SERVER["SERVER_PORT"] : "";
        }
        $u .= $_SERVER["PHP_SELF"];
        return $u;
    }

    static function getCurrentPath() {
        if (dirname($_SERVER['PHP_SELF']) != '\\') {
            $u .= dirname($_SERVER['PHP_SELF']);
        }
        return $u;
    }
}

?>
