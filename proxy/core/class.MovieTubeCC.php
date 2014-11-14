<?php

/*
 * Tien Giang Developer
 * @Author TienGiang <br>Mail: nguyentiengiang@outlook.com<br>Phone: +84 1282 303 100
 * @Version Class Version <1/Jan/2014>
 * @Since 1.0
 */

class MovieTubeCC {

    static function requestUrlParse($siteId) {
        $link = '';
        try {
            $url = 'http://movietube.cc/index.php';
            $postData = array('c' => "result", 'a' => "getplayerinfo", 'p' => "{\"KeyWord\":\"" . $siteId . "\"}");
            $postString = http_build_query($postData);
            $opts = array('http' =>
                array('method' => 'POST', 'header' => 'Content-type: application/x-www-form-urlencoded', 'content' => $postString)
            );
            $context = stream_context_create($opts);
            $html = file_get_html($url, false, $context);
            if (!empty($html)) {
                try {
                    $link = array_shift($html->find("input[id='hdn_playertype']"))->data;
                    $html->clear();
                } catch (Exception $exc) {
                    $link = null;
                    File\Log::write($exc . " " . $siteId);
                }
            }
            unset($postData);
            unset($postString);
            unset($html);
            unset($context);
        } catch (Exception $exc) {
            
        }
        return urldecode(self::cleanLink($siteId, $link));
    }

    static function cleanLink($id, $url) {
        $link = '';
        if ($url != null || $url != '') {
            $arrSlip = explode("|", $url);
            $strSrc = trim($arrSlip[1]);
            $html = str_get_html(urldecode($strSrc));
            if (!empty($html)) {
                try {
                    $link = array_shift($html->find("source"))->src;
                    $html->clear();
                } catch (Exception $exc) {
                    $link = $strSrc;
                }
            } else {
                s9Helper\MyFile\Log::write($exc . " " . $id, ".ParseMovieTube", "test");
            }
        }
        return $link;
    }

}

class Watch33TV {

    function requestUrlParse($siteId, $episode, $part) {
        $link = '';
        try {
            $url = 'http://kissdrama.net/index.php';
            $postData = array('c' => "result", 'a' => "getplayerinfo", 'p' => "{\"KeyWord\":\"" . $siteId . "\",\"Episode\":\"" . $episode . "\",\"Part\":\"" . $part . "\"}");
            $postString = http_build_query($postData);
            $opts = array('http' =>
                array('method' => 'POST', 'header' => 'Content-type: application/x-www-form-urlencoded', 'content' => $postString)
            );
            $context = stream_context_create($opts);
            $html = file_get_html($url, false, $context);
            if (!empty($html)) {
                try {
                    $link = array_shift($html->find("video[id='mp4player2'] source"))->src;
                    $html->clear();
                } catch (Exception $exc) {
                    $link = null;
                    File\Log::write($exc . " " . $siteId);
                }
            }
            unset($postData);
            unset($postString);
            unset($html);
            unset($context);
        } catch (Exception $exc) {
        }
        return urldecode($link);
    }

}
