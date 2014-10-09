<?php

namespace s9ProjectHelper;

class AppResponeHelper {
    
    /*
     * $responeValue array 
     * array(
     *      header => array("keyHeader" => "valueHeader", etc...),
     *      body => array("result" => "value of result", etc...),
     * )
     */
    public static function result($appRespone = "", $requestResult = "") {
//        dd($requestResult);
        $appRespone->status($requestResult->status);
        foreach ($requestResult->headers as $headerKey => $headerValue) {
            $appRespone->header($headerKey, $headerValue);
        }
        if ($requestResult->headers["Content-Type"] === "application/xml") {
            require(HELP_ROOT . 'XMLHelper.php');
            echo \s9ProjectHelper\ArrayToXML::generate_valid_xml_from_array($requestResult->body['result'], $requestResult->root, "item");
        } else {
            echo json_encode($requestResult->body['result'], JSON_NUMERIC_CHECK | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
        }
    }

}

?>
