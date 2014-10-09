<?php

namespace s9ProjectHelper;

class RequestResult {

    public $status;
    public $headers;
    public $body;
    public $root;
    public $cookies;

    public function __construct($status = null, $headers = null, $body = null, $root = "root", $cookies = null) {
        if (empty($status)) {
            $this->status = 200;
        } else {
            $this->status = $status;
        }
        $this->headers = array("X-Powered-By" => "ongteu");
        if (empty($headers["Content-Type"])) {
            $this->headers += array(
                "Content-Type" => "application/json"
            );
        } else {
            $this->headers += $headers;
        }
        if (empty($body)) {
            $this->body = array(
                "result" => array("message" => "I just have sex! and you know what i feel :*")
            );
        } else {
            $this->body = $body;
        }
        $this->root = $root;
        $this->cookies = $cookies;
    }

}

?>
