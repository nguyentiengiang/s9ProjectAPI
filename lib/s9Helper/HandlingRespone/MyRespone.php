<?php

namespace s9Helper\HandlingRespone;

/**
 * result. Set result to return client.
 *
 * @file    s9Helper/HandlingRespone/MyRespone.php
 * @desc    MyRespone
 * @author  Tien Giang (ongteu)
 * @license BSD/GPLv2
 *
 * Provides a basic respone function for s9Project Helper.
 *
 * copyright (c) TienGiang
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */

class MyRespone {

    private static $status;
    private static $headers;
    private static $body;
    private static $root;
    private static $cookies;

    /**
     * Push respone when all done
     * 
     * @param array? $contextAppRequest
     * @param array? $contextAppRespone
     * @param array $status
     * @param array $headers
     * @param array $body
     * @param array $root
     * @param array $cookies
     * 
     * @return self (print direct result containter)
     */
    public static function result($contextAppRequest, $contextAppRespone = "", $status = null, $headers = null, $body = null, $root = "root", $cookies = null) {
        self::$headers = array("X-Powered-By" => "ongteu");
        // Set status respone
        if (empty($status)) {
            self::$status = 200;
        } else {
            self::$status = $status;
        }
        // Set headers respone
        if (empty($headers["Content-Type"]) || $headers["Content-Type"] === "application/x-www-form-urlencoded") {
            self::$headers += array(
                "Content-Type" => "application/json"
            );
        } else if (is_array($headers)) {
            self::$headers += $headers;
        }
        // Set body respone
        if (empty($body)) {
            self::$body = array(
                "result" => array(
                    "message" => "I just have rape and you know what i feel :*",
                    "error" => "No result!"
                )
            );
        } else if (is_array($body)) {
            self::$body = $body;
        }
        // Set root xml respone
        self::$root = $root;
        // Set cookies respone
        self::$cookies = $cookies;

        $contextAppRespone->status(self::$status);
        foreach (self::$headers as $headerKey => $headerValue) {
            $contextAppRespone->header($headerKey, $headerValue);
        }
        if ($contextAppRequest->isPost() || $contextAppRequest->isPut()) {
            $contextAppRespone->header("Content-Type", "application/json");
        }
        if (self::$headers["Content-Type"] === "application/xml") {
            echo \s9Helper\MyFile\XML::generate_valid_xml_from_array(self::$body['result'], self::$root, "item");
        } else if ($contextAppRequest->get("callback")) {
            $callback = trim($contextAppRequest->get("callback")) ? trim($contextAppRequest->get("callback")) : "undefine";
            echo $callback . "(" . json_encode(self::$body['result'], JSON_NUMERIC_CHECK | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) . ");";
        } else {
            echo json_encode(self::$body['result'], JSON_NUMERIC_CHECK | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
        }
    }

}
