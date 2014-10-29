<?php

/*
 * Tien Giang Developer
 * @Author TienGiang <br>Mail: nguyentiengiang@outlook.com<br>Phone: +84 1282 303 100
 * @Version Class Version <1/Jan/2014>
 * @Since 1.0
 */

class GoogleDrive {

    static function requestFromServer($id) {
        $arrLink = null;

        $url = "https://docs.google.com/get_video_info?docid=" . $id . "&authuser=";
        $html = file_get_html($url);

//        d($html);

        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $arrSrc = explode("&", $dom->textContent);
//        d($arrSrc);
        $arrCvt = array();
        foreach ($arrSrc as $strSrc) {
            $arrElement = explode("=", $strSrc);
            $arrCvt += array($arrElement[0] => $arrElement[1]);
        }
//        d($arrCvt);
        $need = $arrCvt['url_encoded_fmt_stream_map'];
//        d($need);
//        dd($l);
//        $content = $arrJson[1]->videoplay->flashVars->fmt_stream_map;
        $arrLink = self::cleanLinkV31($need);
//        dd($arrLink);
        unset($html);
        unset($dom);
        unset($arr1stElement);
        unset($arr1stElementConverted);
        unset($arr2ndElement);
        unset($arr2ndElementConverted);
        unset($arrJson);
        unset($content);
        return $arrLink;
    }

    /*
     * @Return $arrLink streming from google video.
     *
     * @param $html string String source code html from google drive of client send to server
     */

    static function proccessContentFromClient($html) {
        $arrLink = null;
        /*
         * is fucking converter src html for the freaking guy's coder of Google
         * read it if you can @@
         */
        $clientSrc = str_get_html($html)->find("body", 0);
        //dd($clientSrc);
        $dom = new DOMDocument();
        $dom->loadHTML($clientSrc);
        $arr1stElement = explode("_main", $dom->textContent);
        $arr1stElementConverted = str_replace("'", '"', $arr1stElement[1]);
        $arr2ndElement = explode("{", $arr1stElementConverted, 2);
        $arr2ndElementConverted = substr_replace($arr2ndElement[1], "", -2);
        $arrJson = json_decode("[{" . $arr2ndElementConverted . "]");
        $content = $arrJson[1]->videoplay->flashVars->fmt_stream_map;
        // end this shit!
        $arrLink = self::cleanLink($content);
        unset($clientSrc);
        unset($dom);
        unset($arr1stElement);
        unset($arr1stElementConverted);
        unset($arr2ndElement);
        unset($arr2ndElementConverted);
        unset($arrJson);
        unset($content);

        return $arrLink;
    }

    static function cleanLinkV2($strSrc) {
        $arrLink = array();
        if ($strSrc != null || $strSrc != '') {
            $arrSrc1st = explode(",", $strSrc);
            $arrTemp = array();
            foreach ($arrSrc1st as $strSrc1st) {
                $arrSrc2nd = explode("|", $strSrc1st);
                array_push($arrTemp, self::unicode2utf8($arrSrc2nd[1]));
            }
            $linkHQ = array("hd" => $arrTemp[0]);
            $linkSQ = array();
            if (empty($arrTemp[1])) {
                $linkSQ = array("sd" => $arrTemp[0]);
            } else {
                $linkSQ = array("sd" => $arrTemp[1]);
            }
            $arrLink = $linkHQ + $linkSQ;
        }
        return $arrLink;
    }

    static function cleanLinkV3($strSrc) {
        //File\Log::write($strSrc);
        $link = array();
        if ($strSrc != null || $strSrc != '') {
            $arrSrc1st = explode(",", self::unicode2utf8($strSrc));
            $arrTemp = array();
            foreach ($arrSrc1st as $strSrc1st) {
                if (strpos($strSrc1st, "video%2Fmp4") !== FALSE) {
                    $arrVal1 = explode("url=", $strSrc1st);
                    foreach ($arrVal1 as $val1) {
                        if (strpos($val1, 'http') === 0) {
                            array_push($arrTemp, self::buildQueryString($val1));
                        }
                    }
                }
            }
            //dd($arrTemp);
            $arrHQ = array("hd" => $arrTemp[0] . '');
            $arrSQ = array();
            if (empty($arrTemp[1])) {
                $arrSQ = array("sd" => $arrTemp[0] . '');
            } else {
                $arrSQ = array("sd" => $arrTemp[1] . '');
            }
            $link = $arrHQ + $arrSQ;
        }
        echo '<pre>';
        print_r($link);
        die();
        return $link;
    }

    public static function unicode2utf8($strSrc) {
        $strCvt = null;
        $json = '["' . trim($strSrc) . '"]';
        $arr = json_decode($json);
        $strCvt = $arr[0];
        return $strCvt;
    }

    private static function buildQueryString($strSrc) {
        $strCvt = null;
        $invalid = array(
            "\u0026" => "&", "," => "&", "%3D" => "=", "%252C" => "%2C"
        );
        $strSrc = str_replace(array_keys($invalid), array_values($invalid), $strSrc);
        $arrSrc = explode("%3B", $strSrc);
        $arrSrc1 = explode("%3F", $arrSrc[0]);
        $arrSrc2 = explode("%26", $arrSrc1[1]);
        //dd($arrSrc2);
        $arrSrcCvt = array_unique($arrSrc2);
        $strCvt = urldecode($arrSrc1[0]) . "?";
        //dd($arrSrcCvt);
        foreach ($arrSrcCvt as $strUnique) {
            $strCvt .= $strUnique . "&";
        }
        return substr($strCvt, 0, -1);
    }

    private static function cleanLink($strSrc) {
        $arrLink = array();
        if ($strSrc != null || $strSrc != '') {
            $arrSrc1st = explode(",", $strSrc);
            $arrTemp = array();
            foreach ($arrSrc1st as $strSrc1st) {
                $arrSrc2nd = explode("|", $strSrc1st);
                array_push($arrTemp, $arrSrc2nd[1]);
            }
            $linkHQ = array("hd" => $arrTemp[0]);
            $linkSQ = array();
            if (empty($arrTemp[1])) {
                $linkSQ = array("sd" => $arrTemp[0]);
            } else {
                $linkSQ = array("sd" => $arrTemp[1]);
            }
            $arrLink = $linkHQ + $linkSQ;
        }
        return $arrLink;
    }

    static function cleanLinkV31($strSrc) {
        //\s9Helper\MyFile\Log::write($strSrc, "proxy", "proxyGD");
        $link = array();
        if ($strSrc != null || $strSrc != '') {

            $arrSrc1st = explode("%2C", $strSrc);
            $hd = null;
            $sd = null;
            foreach ($arrSrc1st as $str1St) {
                if (!empty($hd) && !empty($sd)) {
                    break;
                }
                if (strpos($str1St, "mp4") !== FALSE || strpos($str1St, "3gp") !== FALSE) {
                    $arrSrc2st = explode("%26", $str1St);
                    foreach ($arrSrc2st as $str2St) {
                        $decode = urldecode($str2St);
                        if (strpos($decode, "http") !== FALSE) {
                            $li = explode("http", $decode);
                            if (strpos($str1St, "hd") !== FALSE || strpos($str1St, "high") !== FALSE) {
                                $hd = "http" . $li[1];
                            } else {
                                $sd = "http" . $li[1];
                            }
                        }
                    }
                }
            }
            if (empty($hd)) {
                $hd = $sd;
            }
            $arrHQ = array("hd" => urldecode($hd));
            $arrSQ = array("sd" => urldecode($sd));
            $link = $arrHQ + $arrSQ;
        }
        return $link;
    }

}

class YouTube {

    /**
     * @Decription.
     *
     * @param $param [type] Decription
     */
    static function requestContent($youTubeId) {
        $content = null;
        $url = "https://www.youtube.com/get_video_info?&video_id=" . $youTubeId . "&el=detailpage&ps=default&eurl=&gl=US&hl=en";
        //$url = "https://www.youtube.com/watch?v=" . $youTubeId;
        $content = file_get_html($url);
        return $content;
    }

    static function processContent($content) {
        $link = '';
        $strSrc = urldecode($content);
//        \MyFile\Log::write($content, "getLinkYT", "YT");
        $link = self::cleanLinkTypeClient($strSrc);
        unset($strSrc);
        return $link;
    }

    static function processContentClient($content) {
        $arrLink = null;
        $html = str_get_html($content)->find("div[id=player]", 0);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $arr1stElement = explode("ytplayer.config = ", trim($dom->textContent));
        $arr2stElement = explode(";", $arr1stElement[1]);
        $arrJson = json_decode($arr2stElement[0]);

        $contentSQ = $arrJson->args->url_encoded_fmt_stream_map;
        $arrLink = self::cleanLinkTypeServer($contentSQ);
        unset($html);
        unset($dom);
        unset($arr1stElement);
        unset($arr2ndElement);
        unset($arrJson);
        unset($content);
        return $arrLink;
    }

    static function requestFromServer($id) {
        $arrLink = null;
        $url = "https://www.youtube.com/watch?v=" . $id;
        $html = file_get_html($url)->find("div[id=player]", 0);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        //Kint::dump($dom->textContent);        die();
        $arr1stElement = explode("ytplayer.config = ", trim($dom->textContent));
        $arr2stElement = explode(";", $arr1stElement[1]);
        $arrJson = json_decode($arr2stElement[0]);

//        $contentSQ = $arrJson->args->url_encoded_fmt_stream_map;
//        $signature = $arrJson->args->dashmpd;        
        $contentHQ = $arrJson->args->adaptive_fmts;
        //dd($contentSQ);
        $arrLink = self::cleanLinkTypeServer($contentHQ);
        unset($html);
        unset($dom);
        unset($arr1stElement);
        unset($arr2ndElement);
        unset($arrJson);
        unset($content);
        return $arrLink;
    }

    private static function cleanLinkTypeClient($strSrc) {
        $link = array();
        if ($strSrc != null || $strSrc != '') {
            $arrSrc1 = explode("url_encoded_fmt_stream_map", $strSrc);
            $arrSrc2 = explode(",", $arrSrc1[1]);
            $arrTemp = array();

            foreach ($arrSrc2 as $val) {
                if (strpos($val, "type=video%2Fmp4") !== FALSE) {
                    $arrVal1 = explode("url=", $val);
                    foreach ($arrVal1 as $val1) {
                        if (strpos($val1, 'http') === 0) {
                            $arrVal2 = explode(";", urldecode($val1));
                            array_push($arrTemp, YouTube::buildQueryString($arrVal2[0]));
                        }
                    }
                }
            }
            $arrHQ = array("hd" => $arrTemp[0] . '');
            $arrSQ = array();
            if (empty($arrTemp[1])) {
                $arrSQ = array("sd" => $arrTemp[0] . '');
            } else {
                $arrSQ = array("sd" => $arrTemp[1] . '');
            }
            $link = $arrHQ + $arrSQ;
        }
        return $link;
    }

    public static function cleanLinkTypeClientv2($strSrc) {
        //\s9Helper\MyFile\Log::write($strSrc, "proxy", "proxyYT");
        $link = array();
        if ($strSrc != null || $strSrc != '') {
            $arrTemp = array();
            if (strpos($strSrc, "type=video%2Fmp4") != FALSE) {
                $arrVal1 = explode("url=", $strSrc);
                foreach ($arrVal1 as $val1) {
                    if (strpos($val1, 'http') === 0) {
                        $arrVal2 = explode(";", urldecode($val1));
                        array_push($arrTemp, YouTube::buildQueryString($arrVal2[0]));
                    }
                }
            }
            $arrHQ = array("hd" => $arrTemp[0] . '');
            $arrSQ = array();
            if (empty($arrTemp[1])) {
                $arrSQ = array("sd" => $arrTemp[0] . '');
            } else {
                $arrSQ = array("sd" => $arrTemp[1] . '');
            }
            $link = $arrHQ + $arrSQ;
        }
        
        return $link;
    }

    private static function cleanLinkTypeServer($strSrc) {
        $arrLink = array();
        if ($strSrc != null || $strSrc != '') {
            $arrSrc1st = explode(",", $strSrc);
            $arrTemp = array();
            foreach ($arrSrc1st as $strSrc1st) {
                if (strpos($strSrc1st, "type=video%2Fmp4") !== FALSE) {
                    $arrVal1 = explode("url=", $strSrc1st);
                    foreach ($arrVal1 as $val1) {
                        if (strpos($val1, 'http') === 0) {
                            $arrVal2 = explode(";", urldecode($val1));
                            array_push($arrTemp, YouTube::buildQueryString($arrVal2[0]));
                        }
                    }
                }
//                array_push($arrTemp, urldecode($strSrc1st));
            }
//            Kint::dump($arrTemp);
            $linkHQ = array("hd" => $arrTemp[0]);
            $linkSQ = array();
            if (empty($arrTemp[1])) {
                $linkSQ = array("sd" => $arrTemp[0]);
            } else {
                $linkSQ = array("sd" => $arrTemp[1]);
            }
            $arrLink = $linkHQ + $linkSQ;
        }
        return $arrLink;
    }

    public static function buildQueryString($strSrc) {
        $strCvt = null;
        $invalid = array(
            "\\\\u0026" => "&", "," => "&"
        );
        $strSrc = str_replace(array_keys($invalid), array_values($invalid), $strSrc);
        $arrSrc = explode("?", $strSrc);
        $arrSrc2 = explode("&", $arrSrc[1]);
        $arrSrcCvt = array_unique($arrSrc2);
        $strCvt = $arrSrc[0] . "?";
        foreach ($arrSrcCvt as $strUnique) {
            $strCvt .= $strUnique . "&";
        }
        return substr($strCvt, 0, -1);
    }

}

class YouTube2 {

    static function cleanLinkV3($strSrc) {
        //s9Helper\MyFile\Log::write($strSrc, "proxy", "proxyYT2");
        $link = array();
        if ($strSrc != null || $strSrc != '') {

            $arrTemp = array();
            $invalid = array(
                "\\\\" => "\\",
            );
            $strSrc = str_replace(array_keys($invalid), array_values($invalid), $strSrc);
            $arrSrc1st = explode(",", $strSrc);
            $hd = null;
            $sd = null;
            foreach ($arrSrc1st as $str1St) {
                if (!empty($hd) && !empty($sd)) {
                    break;
                }
                if (strpos($str1St, "mp4") !== FALSE || strpos($str1St, "3gp") !== FALSE) {
                    $arrSrc2st = explode("\u0026", $str1St);
                    foreach ($arrSrc2st as $str2St) {
                        $decode = urldecode($str2St);
                        if (strpos($decode, "http") !== FALSE) {
                            $li = explode("http", $decode);
                            if (strpos($str1St, "hd") !== FALSE || strpos($str1St, "high") !== FALSE) {
                                $hd = "http" . $li[1];
                            } else {
                                $sd = "http" . $li[1];
                            }
                        }
                    }
                }
            }
            if (empty($hd)) {
                $hd = $sd;
            } else if (empty($sd)) {
                $sd = $hd;
            }
            if (empty($hd) && empty($sd)) {
                $link = null;
            } else {
                $link = array("hd" => $hd) + array("sd" => $sd);
            }
        }
        return $link;
    }

    private static function unicode2utf8($strSrc) {
        $strCvt = null;
        $json = '["' . trim($strSrc) . '"]';
        $arr = json_decode($json);
        $strCvt = $arr[0];
        return $strCvt;
    }

    private static function buildQueryString2($strSrc) {
        $strCvt = null;
        $arrVal1 = explode("\u", $strSrc1st);
        return substr($strCvt, 0, -1);
    }

}

class Picasa {

    /**
     * @param $id [string] picasa web id 
     */
    // pr5yOXNUv4hXRAqd1SKJHNMTjNZETYmyPJy0liipFm0
    public static function requestContent($id) {
        $content = null;
        $url = "https://picasaweb.google.com/lh/photo/" . $id . "?feat=directlink&autostart=true";
        $content = file_get_html($url)->find("body");

        return $content;
    }

    public static function processContent($content) {
        
    }

}
