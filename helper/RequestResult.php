<?php

namespace s9Helper;

class RequestResult {

    public $status;
    public $headers;
    public $body;
    public $root;
    public $cookies;

    public function __construct($status = null, $headers = null, $body = null, $root = "root", $cookies = null) {
        $this->headers = array("X-Powered-By" => "ongteu");
        // Set status respone
        if (empty($status)) {
            $this->status = 200;
        } else {
            $this->status = $status;
        }
        // Set headers respone
        if (empty($headers["Content-Type"])) {
            $this->headers += array(
                "Content-Type" => "application/json"
            );
        } else if (is_array($headers)) {
            $this->headers += $headers;
        }
        // Set body respone
        if (empty($body)) {
            $this->body = array(
                "result" => array(
                    "message" => "I just have rape and you know what i feel :*",
                    "error" => "No result!"
                )
            );
        } else if (is_array($body)) {
            $this->body = $body;
        }
        // Set root xml respone
        $this->root = $root;
        // Set cookies respone
        $this->cookies = $cookies;
    }

}

?>
