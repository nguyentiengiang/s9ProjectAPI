<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$data = "http://movietube.cc/watch.php?v=HpKzRPMe5JU";
$positionCut = 2;
$positionSwap = 1;

$strBase64 = base64_encode($data);
$arrNormal = str_split($strBase64, 2);
$arrBase69 = array_replace($arrNormal, array(0 => $arrNormal[$positionSwap], 1 => $arrNormal[$positionSwap - 1]));
$strBase69 = null;
foreach ($arrBase69 as $e) {
    $strBase69 .= $e;
}
echo $strBase69 . "<br>";
echo base64_decode("OTlaWUlFQ3gxVnc=") . '<br>';
?>
<h1>proxy</h1>