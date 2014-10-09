<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace s9ProjectHelper;

class ArrayToXML {

    function generate_xml_from_array($array, $node_name) {
        $xml = '';
        if (is_array($array) || is_object($array)) {
            foreach ($array as $key => $value) {
                if (is_numeric($key)) {
                    $key = $node_name;
                }
                $xml .= '<' . $key . '>' . self::generate_xml_from_array($value, $node_name) . '</' . $key . '>';
            }
        } else {
            $xml = htmlspecialchars($array, ENT_QUOTES);
        }
        return $xml;
    }

    static function generate_valid_xml_from_array($array, $node_block = 'nodes', $node_name = 'node') {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>';
        $xml .= '<' . $node_block . '>';
        $xml .= self::generate_xml_from_array($array, $node_name);
        $xml .= '</' . $node_block . '>';
        return $xml;
    }

}