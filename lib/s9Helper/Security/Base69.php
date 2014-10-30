<?php

namespace s9Helper\Security;

class Base69 {

    //OTlaWUlFQ3gxVnc=
    //laOTWUlFQ3gxVnc=

    /**
     * if $positionCut = 1, $positionSwap not effect
     */
    function encodeType1($data = "", $positionCut = 1, $arrPositionSwap = array(0 => 1, 1 => 0)) {
        $strBase64 = base64_encode($data);
        if ($positionCut > 1) {
            $arrNormal = str_split($strBase64, $positionCut);
        }
        $arrSwap = array();
        foreach ($arrPositionSwap as $keySwap => $positionSwap) {
            $arrSwap += array($keySwap => $arrNormal[$positionSwap]);
        }
        $arrBase69 = array_replace($arrNormal, $arrSwap);
        $strBase69 = null;
        foreach ($arrBase69 as $c) {
            $strBase69 .= $c;
        }
        return $strBase69;
    }

    function encodeType2($data = "", $positionCut = 1) {
        $strBase64 = base64_encode($data);
        if ($positionCut >= 1) {
            $arrNormal = str_split($strBase64, $positionCut);
        }
        $arrSwap = array();
        for ($i = 0; $i < count($arrNormal); $i+=2) {
            array_push($arrSwap, $arrNormal[$i+1]);
            array_push($arrSwap, $arrNormal[$i]);
        }
        $strBase69 = null;
        foreach ($arrSwap as $c) {
            $strBase69 .= $c;
        }
        return $strBase69;
    }
    
    function decode($data = "", $positionCut = 1, $positionSwap = 0) {
        $arrNormal = str_split($data, $positionCut);
        $arrReverse = array_map('strrev', str_split($data, $positionCut));
        $arrBase69 = array_replace($arrNormal, array($positionSwap => $arrReverse[$positionSwap]));
        $strBase69 = null;
        foreach ($arrBase69 as $e) {
            $strBase69 .= $e;
        }
        return base64_decode($strBase69);
    }

}
