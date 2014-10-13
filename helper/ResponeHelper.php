<?php

namespace s9Helper;

class ResponeHelper {
    /*
     * $responeValue array 
     * array(
     *      header => array("keyHeader" => "valueHeader", etc...),
     *      body => array(
     *          "result" => "value of result", //this value always not null
     *          etc...
     *      ),
     * )
     */

    public static function result($appRequest, $appRespone = "", $requestResult = "") {
        $appRespone->status($requestResult->status);
        foreach ($requestResult->headers as $headerKey => $headerValue) {
            $appRespone->header($headerKey, $headerValue);
        }
        if ($requestResult->headers["Content-Type"] === "application/xml") {
            require_once(HELP_ROOT . 'XMLHelper.php');
            echo \s9Helper\ArrayToXML::generate_valid_xml_from_array($requestResult->body['result'], $requestResult->root, "item");
        } else if ($appRequest->get("callback")) {
            $callback = trim($appRequest->get("callback")) ? trim($appRequest->get("callback")) : "undefine";
            echo $callback . "(" . json_encode($requestResult->body['result'], JSON_NUMERIC_CHECK | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) . ");";
        } else {
            echo json_encode($requestResult->body['result'], JSON_NUMERIC_CHECK | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
        }
    }

}

?>
