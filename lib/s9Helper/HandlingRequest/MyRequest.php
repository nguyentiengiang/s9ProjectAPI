<?php

/*
 * Clean input request from client
 */

namespace s9Helper\HandlingRequest;

class MyRequest {

    private $request = null;

    public function __construct($contextRequest) {
        $this->request = $contextRequest;
    }

    public static function cleanPOST($contextAppRequestBody, $xss = TRUE) {
        $post = array();
        if (is_null($contextAppRequestBody)) {
            $contextAppRequestBody = $this->request->post();
        }
        if ($xss) {
            foreach ($contextAppRequestBody->post() as $keyPost => $valuePost) {
                $post += array(trim($keyPost) => strip_tags(trim($valuePost)));
            }
        } else {
            foreach ($contextAppRequestBody->post() as $keyPost => $valuePost) {
                $post += array(trim($keyPost) => trim($valuePost));
            }
        }
        return $post;
    }

    public static function cleanGET($contextAppRequestBody) {
        $get = array();
        if (is_null($contextAppRequestBody)) {
            $contextAppRequestBody = $this->request->get();
        }
        foreach ($contextAppRequestBody->get() as $keyPost => $valuePost) {
            $get += array(trim($keyPost) => strip_tags(trim($valuePost)));
        }
        return $get;
    }

}

