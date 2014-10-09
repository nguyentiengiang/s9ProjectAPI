<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class
 *
 * @author Tien Giang
 */
interface ParseAffMovie {

    static function parseContent($html);

    static function processContent($content, $url);

    static function requestContent($url);
}

class SupportParse {

    public static $listSite = array(
        "videomega.tv" => "VideoMegaDotTv",
        "vodlocker.com" => "VodlockerDotCom",
        "allmyvideos.net" => "AllMyVideos",
        "embed.nowvideo.sx" => "NowVideoDotSx",
        "embed.videoweed.es" => "VideoWeedDotEs",
        "embed.movshare.net" => "MovShareDotNet",
        "embed.yourupload.com" => "YourUploadDotCom",
        "embed.novamov.com" => "NovaMovDotCom",
        "cloudy.ec" => "CloudyDotEc",
        "ishared.eu" => "iSharedDotEu",
        "movpod.in" => "MovPodDotIn",
        "streamin.to" => "StreamInDotTo",
        "vk.com" => "VkDotCom",
        "firedrive.com" => "FireDriveDotCom",
        "putlocker.com" =>"FireDriveDotCom",
        "sockshare.com" => "SockShareDotCom",
        //slow link
        "vidto.me" => "VidtoDotMe",
        "gorillavid.in" => "GorillaVidDotIn",
        "play.flashx.tv" => "FlashXDotTv",
    );

    static function getFunction($url) {
        $invalid = array('http://' => '', 'https://' => '', 'www.' => '');
        $str = str_replace(array_keys($invalid), array_values($invalid), $url);
        $strC = str_replace(array_keys(self::$listSite), array_values(self::$listSite), $str);
        $arrStrC = explode("/", $strC);
        return $arrStrC[0];
    }

    static function cleanString($str) {
        $invalid = array(' ' => '', '<!--' => '', '-->' => '', '\/' => '/');
        $str = str_replace(array_keys($invalid), array_values($invalid), $str);
        return $str;
    }

    static function urlConvert($strUrl) {
        $invalid = array(' ' => '+');
        $str = str_replace(array_keys($invalid), array_values($invalid), $strUrl);
        return $str;
    }

}

//Type 1:
class VideoMegaDotTv implements ParseAffMovie {

    const urlBase = "http://videomega.tv/";

    public static function requestContent($url) {
        $link = null;
//        $url = self::urlBase . "iframe.php?ref=" . $id;
        $html = file_get_html($url);
        dd($html);
        $link = self::parseContent($html);
        unset($url);
        unset($html);
        return $link;
    }

    public static function processContent($content, $url) {
        $link = null;
        $html = str_get_html($content);
        if (!empty($html)) {
            $link = self::parseContent($html);
        }
        unset($content);
        unset($url);
        unset($html);
        return $link;
    }

    public static function parseContent($html) {
        $link = "";
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $arr1 = explode("document.write(unescape(\"", $dom->textContent);
        $arr2 = explode("\"", $arr1[1]);
        $strScript = rawurldecode($arr2[0]);
        $arr3 = explode("file: \"", $strScript);
        $arr4 = explode("\"", $arr3[1]);
        $link = $arr4[0];
        unset($dom);
        unset($arr1);
        unset($arr2);
        unset($strScript);
        unset($arr3);
        unset($arr4);
        return $link;
    }

}

class VodlockerDotCom implements ParseAffMovie {

    const urlBase = "http://vodlocker.com/";

    public static function requestContent($url) {
        $link = null;
//        $url = self::urlBase . "iframe.php?ref=" . $id;
        $html = file_get_html($url);
        $link = self::parseContent($html);
        unset($url);
        unset($html);
        return $link;
    }

    public static function processContent($content, $url) {
        $link = null;
        $html = str_get_html($content);
        if (!empty($html)) {
            $link = self::parseContent($html);
        }
        unset($content);
        unset($url);
        unset($html);
        return $link;
    }

    public static function parseContent($html) {
        $link = "";
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $arr1 = explode("setup({", $dom->textContent);
        $arr2 = explode("file: \"", $arr1[1]);
        $arr3 = explode("\"", $arr2[1]);
        $link = $arr3[0];
        unset($dom);
        unset($arr1);
        unset($arr2);
        unset($arr3);
        return $link;
    }

}

class AllMyVideos implements ParseAffMovie {

    const urlBase = "http://allmyvideos.net/";

    public static function requestContent($url) {
        $link = null;
        $html = file_get_html($url);
        $link = self::parseContent($html);
        unset($url);
        unset($html);
        return $link;
    }

    public static function processContent($content, $url) {
        $link = null;
        $html = str_get_html($content);
        if (!empty($html)) {
            $link = self::parseContent($html);
        }
        unset($content);
        unset($url);
        unset($html);
        return $link;
    }

    public static function parseContent($html) {
        $link = null;
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $arr1 = explode("setup(", $dom->textContent);
        $arr2 = explode(");", $arr1[1]);
        $arr3 = explode("\"playlist\":[", SupportParse::cleanString($arr2[0]));
        $arr4 = explode("],", $arr3[1]);
        $arrJson = json_decode($arr4[0] . "]}");
        $link = $arrJson->sources[0]->file;
        unset($dom);
        unset($arr1);
        unset($arr2);
        unset($arr3);
        unset($arr4);
        unset($arrJson);
        return $link;
    }

}

class iSharedDotEu implements ParseAffMovie {

    const urlBase = "http://ishared.eu/";

    public static function requestContent($url) {
        $link = null;
        $html = file_get_html($url);
        $link = self::parseContent($html);
        unset($url);
        unset($html);
        return $link;
    }

    public static function processContent($content, $url) {
        $link = null;
        $html = str_get_html($content);
        if (!empty($html)) {
            $link = self::parseContent($html);
        }
        unset($content);
        unset($url);
        unset($html);
        return $link;
    }

    public static function parseContent($html) {
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $arr1 = explode("path:\"", $dom->textContent);
        $arr1Q = explode("\"", $arr1[1]);
        $link = $arr1Q['0'];
        unset($dom);
        unset($arr1);
        unset($arr1Q);
        return $link;
    }

}

class YourUploadDotCom implements ParseAffMovie {

    const urlBase = "http://embed.yourupload.com/";

    public static function requestContent($url) {
        $link = null;
        $html = file_get_html($url);
        $link = self::parseContent($html);
        unset($url);
        unset($html);
        return $link;
    }

    public static function processContent($content, $url) {
        $link = null;
        $html = str_get_html($content);
        if (!empty($html)) {
            $link = self::parseContent($html);
        }
        unset($content);
        unset($url);
        unset($html);
        return $link;
    }

    public static function parseContent($html) {
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $arr1 = explode("{file: \"", $dom->textContent);
        $arr1Q = explode("\"", $arr1[1]);
//        dd($arr1Q);
        $link = $arr1Q[0];
        unset($dom);
        unset($arr1);
        unset($arr1Q);
        return $link;
    }

}

class MovPodDotIn implements ParseAffMovie {

    const urlBase = "http://movpod.in/";

    public static function requestContent($url) {
        $link = null;
        $html = file_get_html($url);
        $link = self::parseContent($html);
        unset($url);
        unset($html);
        return $link;
    }

    public static function processContent($content, $url) {
        $link = null;
        $html = str_get_html($content);
        if (!empty($html)) {
            $link = self::parseContent($html);
        }
        unset($content);
        unset($url);
        unset($html);
        return $link;
    }

    public static function parseContent($html) {
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $arr1 = explode("file: \"http://", $dom->textContent);
        $arr1Q = explode("\"", $arr1[1]);
        $link = "http://" . $arr1Q['0'];
        unset($dom);
        unset($arr1);
        unset($arr1Q);
        return $link;
    }

}

class StreamInDotTo implements ParseAffMovie {

    const urlBase = "http://streamin.to/";

    public static function requestContent($url) {
        $link = null;
        $html = file_get_html($url);
        $link = self::parseContent($html);
        unset($url);
        unset($html);
        return $link;
    }

    public static function processContent($content, $url) {
        $link = null;
        $html = str_get_html($content);
        if (!empty($html)) {
            $link = self::parseContent($html);
        }
        unset($content);
        unset($url);
        unset($html);
        return $link;
    }

    public static function parseContent($html) {
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $arr1 = explode("file: \"http://", $dom->textContent);
        $arr1Q = explode("\"", $arr1[1]);
        $link = "http://" . $arr1Q['0'];
        unset($dom);
        unset($arr1);
        unset($arr1Q);
        return $link;
    }
}

class VkDotCom implements ParseAffMovie {

    const urlBase = "http://vk.com/";

    public static function requestContent($url) {
        $link = null;
        $html = file_get_html($url);
        $link = self::parseContent($html);
        unset($url);
        unset($html);
        return $link;
    }

    public static function processContent($content, $url) {
        $link = null;
        $html = str_get_html($content);
        if (!empty($html)) {
            $link = self::parseContent($html);
        }
        unset($content);
        unset($url);
        unset($html);
        return $link;
    }

    public static function parseContent($html) {
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $arr1 = explode("\"url3240\":\"", $dom->textContent);
        $arr1Q = explode("\"", $arr1[1]);
        $link = SupportParse::cleanString($arr1Q['0']);
        unset($dom);
        unset($arr1);
        unset($arr1Q);
        return $link;
    }

}

//Type 2:
class NowVideoDotSx implements ParseAffMovie {

    const urlBase = "http://embed.nowvideo.sx/";

    /*
     * Param:
     * numOfErrors=0&
     * user=undefined&
     * pass=undefined&
     * file=grkla0ng59icn&
     * cid=undefined&
     * cid2=undefined&
     * key=58%2E187%2E61%2E65%2D3ee06d3881e6fcf6cc62b8935e178bfd&
     * cid3=undefined
     */
    const urlRequest = "http://www.nowvideo.sx/api/player.api.php?";

    public static function requestContent($url) {
        $link = null;
        $html = file_get_html($url);
        $link = self::parseContent($html);
        unset($url);
        unset($html);
        return $link;
    }

    public static function processContent($content, $url) {
        $link = null;
        $html = str_get_html($content);
        if (!empty($html)) {
            $link = self::parseContent($html);
        }
        unset($content);
        unset($url);
        unset($html);
        return $link;
    }

    public static function parseContent($html) {
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $arr1 = explode("var fkzd=\"", $dom->textContent);
        $arr1Q = explode("\"", $arr1[1]);
        $arr2 = explode("flashvars.file=\"", $dom->textContent);
        $arr2Q = explode("\"", $arr2[1]);
        $url = self::urlRequest . "numOfErrors=0&user=undefined&pass=undefined&file=" . $arr2Q[0] .
                "&cid=undefined&cid2=undefined&key=" . $arr1Q[0] . "&cid3=undefined";
        $packRequested = file_get_contents($url);
        $arr3 = explode("&", $packRequested);
        $arrC = array();
        foreach ($arr3 as $valArr3) {
            $arrT = explode("=", $valArr3);
            $arrC += array($arrT[0] => $arrT[1]);
            unset($arrT);
        }
        $link = $arrC['url'];
        unset($dom);
        unset($arr1);
        unset($arr1Q);
        unset($arr2);
        unset($arr2Q);
        unset($arr3);
        unset($arrC);
        return $link;
    }

}

class VideoWeedDotEs implements ParseAffMovie {

    const urlBase = "http://embed.videoweed.es/";

    /*
     * Param:
     * numOfErrors=0&
     * user=undefined&
     * pass=undefined&
     * file=grkla0ng59icn&
     * cid=undefined&
     * cid2=undefined&
     * key=58%2E187%2E61%2E65%2D3ee06d3881e6fcf6cc62b8935e178bfd&
     * cid3=undefined
     */
    const urlRequest = "http://www.videoweed.es/api/player.api.php?";

    public static function requestContent($url) {
        $link = null;
        $html = file_get_html($url);
        $link = self::parseContent($html);
        unset($url);
        unset($html);
        return $link;
    }

    public static function processContent($content, $url) {
        $link = null;
        $html = str_get_html($content);
        if (!empty($html)) {
            $link = self::parseContent($html);
        }
        unset($content);
        unset($url);
        unset($html);
        return $link;
    }

    public static function parseContent($html) {
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $arr1 = explode("var fkz=\"", $dom->textContent);
        $arr1Q = explode("\"", $arr1[1]);
        $arr2 = explode("flashvars.file=\"", $dom->textContent);
        $arr2Q = explode("\"", $arr2[1]);
        $url = self::urlRequest . "numOfErrors=0&user=undefined&pass=undefined&file=" . $arr2Q[0] .
                "&cid=undefined&cid2=undefined&key=" . $arr1Q[0] . "&cid3=undefined";
        $packRequested = file_get_contents($url);
        $arr3 = explode("&", $packRequested);
        $arrC = array();
        foreach ($arr3 as $valArr3) {
            $arrT = explode("=", $valArr3);
            $arrC += array($arrT[0] => $arrT[1]);
            unset($arrT);
        }
        $link = $arrC['url'];
        unset($dom);
        unset($arr1);
        unset($arr1Q);
        unset($arr2);
        unset($arr2Q);
        unset($arr3);
        unset($arrC);
        return $link;
    }

}

class MovShareDotNet implements ParseAffMovie {

    const urlBase = "http://embed.movshare.net/";

    /*
     * Param:
     * numOfErrors=0&
     * user=undefined&
     * pass=undefined&
     * file=grkla0ng59icn&
     * cid=undefined&
     * cid2=undefined&
     * key=58%2E187%2E61%2E65%2D3ee06d3881e6fcf6cc62b8935e178bfd&
     * cid3=undefined
     */
    const urlRequest = "http://www.movshare.net//api/player.api.php?";

    public static function requestContent($url) {
        $link = null;
        $html = file_get_html($url);
        $link = self::parseContent($html);
        unset($url);
        unset($html);
        return $link;
    }

    public static function processContent($content, $url) {
        $link = null;
        $html = str_get_html($content);
        if (!empty($html)) {
            $link = self::parseContent($html);
        }
        unset($content);
        unset($url);
        unset($html);
        return $link;
    }

    public static function parseContent($html) {
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $arr1 = explode("var fkzd=\"", $dom->textContent);
        $arr1Q = explode("\"", $arr1[1]);
        $arr2 = explode("flashvars.file=\"", $dom->textContent);
        $arr2Q = explode("\"", $arr2[1]);
        $url = self::urlRequest . "numOfErrors=0&user=undefined&pass=undefined&file=" . $arr2Q[0] .
                "&cid=undefined&cid2=undefined&key=" . $arr1Q[0] . "&cid3=undefined";
        $packRequested = file_get_contents($url);
//        echo $packRequested;
        $arr3 = explode("&", $packRequested);
        $arrC = array();
        foreach ($arr3 as $valArr3) {
            $arrT = explode("=", $valArr3);
            $arrC += array($arrT[0] => $arrT[1]);
            unset($arrT);
        }
        $link = $arrC['url'];
        unset($dom);
        unset($arr1);
        unset($arr1Q);
        unset($arr2);
        unset($arr2Q);
        unset($arr3);
        unset($arrC);
        return $link;
    }

}

class NovaMovDotCom implements ParseAffMovie {

    const urlBase = "http://embed.novamov.com/";

    /*
     * Param:
     * numOfErrors=0&
     * user=undefined&
     * pass=undefined&
     * file=grkla0ng59icn&
     * cid=undefined&
     * cid2=undefined&
     * key=58%2E187%2E61%2E65%2D3ee06d3881e6fcf6cc62b8935e178bfd&
     * cid3=undefined
     */
    const urlRequest = "http://www.novamov.com/api/player.api.php?";

    public static function requestContent($url) {
        $link = null;
        $html = file_get_html($url);
        $link = self::parseContent($html);
        unset($url);
        unset($html);
        return $link;
    }

    public static function processContent($content, $url) {
        $link = null;
        $html = str_get_html($content);
        if (!empty($html)) {
            $link = self::parseContent($html);
        }
        unset($content);
        unset($url);
        unset($html);
        return $link;
    }

    public static function parseContent($html) {
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $arr1 = explode("flashvars.filekey=\"", $dom->textContent);
        $arr1Q = explode("\"", $arr1[1]);
        $arr2 = explode("flashvars.file=\"", $dom->textContent);
        $arr2Q = explode("\"", $arr2[1]);
        $url = self::urlRequest . "numOfErrors=0&user=undefined&pass=undefined&file=" . $arr2Q[0] .
                "&cid=undefined&cid2=undefined&key=" . $arr1Q[0] . "&cid3=undefined";
        $packRequested = file_get_contents($url);
        $arr3 = explode("&", $packRequested);
        $arrC = array();
        foreach ($arr3 as $valArr3) {
            $arrT = explode("=", $valArr3);
            $arrC += array($arrT[0] => $arrT[1]);
            unset($arrT);
        }
        $link = $arrC['url'];
        unset($dom);
        unset($arr1);
        unset($arr1Q);
        unset($arr2);
        unset($arr2Q);
        unset($arr3);
        unset($arrC);
        return $link;
    }

}

class CloudyDotEc implements ParseAffMovie {

    const urlBase = "https://www.cloudy.ec/";

    /*
     * Param:
     * numOfErrors=0&
     * user=undefined&
     * pass=undefined&
     * file=grkla0ng59icn&
     * cid=undefined&
     * cid2=undefined&
     * key=58%2E187%2E61%2E65%2D3ee06d3881e6fcf6cc62b8935e178bfd&
     * cid3=undefined
     */
    const urlRequest = "https://www.cloudy.ec/api/player.api.php?";

    public static function requestContent($url) {
        $link = null;
        $html = file_get_html($url);
        $link = self::parseContent($html);
        unset($url);
        unset($html);
        return $link;
    }

    public static function processContent($content, $url) {
        $link = null;
        $html = str_get_html($content);
        if (!empty($html)) {
            $link = self::parseContent($html);
        }
        unset($content);
        unset($url);
        unset($html);
        return $link;
    }

    public static function parseContent($html) {
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $arr1 = explode("flashvars.filekey=\"", $dom->textContent);
        $arr1Q = explode("\"", $arr1[1]);
        $arr2 = explode("flashvars.file=\"", $dom->textContent);
        $arr2Q = explode("\"", $arr2[1]);
        $url = self::urlRequest . "numOfErrors=0&user=undefined&pass=undefined&file=" . $arr2Q[0] .
                "&cid=undefined&cid2=undefined&key=" . $arr1Q[0] . "&cid3=undefined";
        $packRequested = file_get_contents($url);
        echo $packRequested;
        $arr3 = explode("&", $packRequested);
        $arrC = array();
        foreach ($arr3 as $valArr3) {
            $arrT = explode("=", $valArr3);
            $arrC += array($arrT[0] => $arrT[1]);
            unset($arrT);
        }
        $link = urldecode($arrC['url']);
        unset($dom);
        unset($arr1);
        unset($arr1Q);
        unset($arr2);
        unset($arr2Q);
        unset($arr3);
        unset($arrC);
        return $link;
    }

}

class FireDriveDotCom implements ParseAffMovie {

    const urlBase = "http://www.firedrive.com/";

    public static function requestContent($url) {
        $link = null;
        $htmlGet = file_get_html($url);
        $confirm = array_shift($htmlGet->find("form[method=post] input[name=confirm]"))->value;
        $postData = array('confirm' => $confirm);
        $postString = http_build_query($postData);
        $opts = array('http' =>
            array('method' => 'POST', 'header' => 'Content-type: application/x-www-form-urlencoded', 'content' => $postString)
        );
        $context = stream_context_create($opts);
        $htmlPost = file_get_contents($url, false, $context);
        $link = self::parseContent($htmlPost);
        unset($url);
        unset($htmlGet);
        unset($htmlPost);
        return $link;
    }

    public static function processContent($content, $url) {
        $link = null;
        $htmlGet = str_get_html($content);
        if (!empty($htmlGet)) {
            $confirm = array_shift($htmlGet->find("form[method=post] input[name=confirm]"))->value;
            $postData = array('confirm' => $confirm);
            $postString = http_build_query($postData);
            $opts = array('http' =>
                array('method' => 'POST', 'header' => 'Content-type: application/x-www-form-urlencoded', 'content' => $postString)
            );
            $context = stream_context_create($opts);
            $htmlPost = file_get_contents($url, false, $context);
            $link = self::parseContent($htmlPost);
        }
        unset($content);
        unset($htmlGet);
        unset($htmlPost);
        return $link;
    }

    public static function parseContent($html) {
        $link = "";
        $arr1 = explode("file: '", $html);
        $arr2 = explode("',", $arr1[1]);
        $link = $arr2[0];
        unset($dom);
        unset($arr1);
        unset($arr2);
        return $link;
    }

}

class SockShareDotCom implements ParseAffMovie {

    const urlBase = "http://www.sockshare.com";

    public static function requestContent($url) {
        $link = null;
        $htmlGet = file_get_html($url);
        $fuck_you = array_shift($htmlGet->find("form[method=post] input[name=fuck_you]"))->value;
        $confirm = SupportParse::urlConvert(array_shift($htmlGet->find("form[method=post] input[name=fuck_you]"))->value);
        $postData = array('fuck_you' => $fuck_you, 'confirm' => $confirm);
        $postString = http_build_query($postData);
        $opts = array('http' =>
            array('method' => 'POST', 'header' => 'Content-type: application/x-www-form-urlencoded', 'content' => $postString)
        );
        $context = stream_context_create($opts);
        $htmlPost = file_get_contents($url, false, $context);
        $link = self::parseContent($htmlPost);
        unset($url);
        unset($htmlGet);
        unset($htmlPost);
        return $link;
    }

    public static function processContent($content, $url) {
        $link = null;
        $htmlGet = str_get_html($content);
        if (!empty($htmlGet)) {
            $fuck_you = array_shift($htmlGet->find("form[method=post] input[name=fuck_you]"))->value;
            $confirm = SupportParse::urlConvert(array_shift($htmlGet->find("form[method=post] input[name=fuck_you]"))->value);
            $postData = array('fuck_you' => $fuck_you, 'confirm' => $confirm);
            $postString = http_build_query($postData);
            $opts = array('http' =>
                array('method' => 'POST', 'header' => 'Content-type: application/x-www-form-urlencoded', 'content' => $postString)
            );
            $context = stream_context_create($opts);
            $htmlPost = file_get_contents($url, false, $context);
            $link = self::parseContent($htmlPost);
        }
        unset($content);
        unset($htmlGet);
        unset($htmlPost);
        return $link;
    }

    public static function parseContent($html) {
        $link = null;
        $arr1 = explode("playlist: '", $html);
        $arr2 = explode("',", $arr1[1]);
        $xml = file_get_html(self::urlBase . $arr2[0]);
        if (!empty($xml)) {
            $child = $xml->find("item", 1);
            $link = array_shift($child->find("media:content"))->url;
        }
        unset($dom);
        unset($arr1);
        unset($arr2);
        unset($xml);
        return $link;
    }

}

/*
 * http://hqq.tv/player/embed_player.php?vid=YW36XNSRNHKG&amp;autoplay=no // Pending
 * http://megashare.info/full_watch.php?id=TVRBdw //Cancel
 * http://putlocker.cz/embed/humshakals-tt3036740/ pending
 * http://yify.tv/embed/?movie=the-secret-sex-life-of-a-single-mom //cancel (captcha)
 */

// Shouldn't use the hosts below. Server will be request twice, may be 3(flashx).
class VidtoDotMe implements ParseAffMovie {

    const urlBase = "http://vidto.me/";

    public static function requestContent($url) {
        $link = null;
        $htmlGet = file_get_html($url);
        sleep(2);
        $op = array_shift($htmlGet->find("form[method=POST] input[name=op]"))->value;
        $usr_login = array_shift($htmlGet->find("form[method=POST] input[name=usr_login]"))->value;
        $id = array_shift($htmlGet->find("form[method=POST] input[name=id]"))->value;
        $fname = SupportParse::urlConvert(array_shift($htmlGet->find("form[method=POST] input[name=fname]"))->value);
        $referer = array_shift($htmlGet->find("form[method=POST] input[name=referer]"))->value;
        $hash = array_shift($htmlGet->find("form[method=POST] input[name=hash]"))->value;
        $imhuman = SupportParse::urlConvert(array_shift($htmlGet->find("form[method=POST] input[name=imhuman]"))->value);
        $postData = array(
            'op' => $op, 'usr_login' => $usr_login, 'id' => $id, 'fname' => $fname,
            'referer' => $referer, 'hash' => $hash, 'imhuman' => $imhuman);
        $postString = http_build_query($postData);
        $opts = array('http' =>
            array('method' => 'POST', 'header' => 'Content-type: application/x-www-form-urlencoded', 'content' => $postString)
        );
        $context = stream_context_create($opts);
        $htmlPost = file_get_contents($url, false, $context);
        $link = self::parseContent($htmlPost);
        unset($url);
        unset($htmlGet);
        unset($htmlPost);
        return $link;
    }

    public static function processContent($content, $url) {
        $link = null;
        $htmlGet = str_get_html($content);
        sleep(2);
        if (!empty($htmlGet)) {
            $op = array_shift($htmlGet->find("form[method=POST] input[name=op]"))->value;
            $usr_login = array_shift($htmlGet->find("form[method=POST] input[name=usr_login]"))->value;
            $id = array_shift($htmlGet->find("form[method=POST] input[name=id]"))->value;
            $fname = SupportParse::urlConvert(array_shift($htmlGet->find("form[method=POST] input[name=fname]"))->value);
            $referer = array_shift($htmlGet->find("form[method=POST] input[name=referer]"))->value;
            $hash = array_shift($htmlGet->find("form[method=POST] input[name=hash]"))->value;
            $imhuman = SupportParse::urlConvert(array_shift($htmlGet->find("form[method=POST] input[name=imhuman]"))->value);
            $postData = array(
                'op' => $op, 'usr_login' => $usr_login, 'id' => $id, 'fname' => $fname, 'referer' => $referer, 'hash' => $hash, 'imhuman' => $imhuman);
            $postString = http_build_query($postData);
            $opts = array('http' =>
                array('method' => 'POST', 'header' => 'Content-type: application/x-www-form-urlencoded', 'content' => $postString)
            );
            $context = stream_context_create($opts);
            $htmlPost = file_get_contents($url, false, $context);
            $link = self::parseContent($htmlPost);
        }
        unset($content);
        unset($htmlGet);
        unset($htmlPost);
        return $link;
    }

    public static function parseContent($html) {
        $link = "";
        $htmlCvt = SupportParse::cleanString($html);
        $arr1 = explode("varfile_link='", $htmlCvt);
        $arr2 = explode("';", $arr1[1]);
        $link = $arr2[0];
        unset($dom);
        unset($arr1);
        unset($arr2);
        return $link;
    }

}

class GorillaVidDotIn implements ParseAffMovie {

    const urlBase = "http://gorillavid.in/";

    public static function requestContent($url) {
        $link = null;
        $htmlGet = file_get_html($url);
        $op = array_shift($htmlGet->find("form[method=POST] input[name=op]"))->value;
        $usr_login = array_shift($htmlGet->find("form[method=POST] input[name=usr_login]"))->value;
        $id = array_shift($htmlGet->find("form[method=POST] input[name=id]"))->value;
        $fname = SupportParse::urlConvert(array_shift($htmlGet->find("form[method=POST] input[name=fname]"))->value);
        $referer = array_shift($htmlGet->find("form[method=POST] input[name=referer]"))->value;
        $channel = array_shift($htmlGet->find("form[method=POST] input[name=channel]"))->value;
        $method_free = SupportParse::urlConvert(array_shift($htmlGet->find("form[method=POST] input[name=method_free]"))->value);
        $postData = array(
            'op' => $op, 'usr_login' => $usr_login, 'id' => $id, 'fname' => $fname,
            'referer' => $referer, 'channel' => $channel, 'method_free' => $method_free);
        $postString = http_build_query($postData);
        $opts = array('http' =>
            array('method' => 'POST', 'header' => 'Content-type: application/x-www-form-urlencoded', 'content' => $postString)
        );
        $context = stream_context_create($opts);
        $htmlPost = file_get_contents($url, false, $context);
        $link = self::parseContent($htmlPost);
        unset($url);
        unset($htmlGet);
        unset($htmlPost);
        return $link;
    }

    public static function processContent($content, $url) {
        $link = null;
        $htmlGet = str_get_html($content);
        if (!empty($htmlGet)) {
            $op = array_shift($htmlGet->find("form[method=POST] input[name=op]"))->value;
            $usr_login = array_shift($htmlGet->find("form[method=POST] input[name=usr_login]"))->value;
            $id = array_shift($htmlGet->find("form[method=POST] input[name=id]"))->value;
            $fname = array_shift($htmlGet->find("form[method=POST] input[name=fname]"))->value;
            $referer = array_shift($htmlGet->find("form[method=POST] input[name=referer]"))->value;
            $channel = array_shift($htmlGet->find("form[method=POST] input[name=channel]"))->value;
            $method_free = SupportParse::urlConvert(array_shift($htmlGet->find("form[method=POST] input[name=method_free]"))->value);
            $postData = array(
                'op' => $op, 'usr_login' => $usr_login, 'id' => $id, 'fname' => $fname,
                'referer' => $referer, 'channel' => $channel, 'method_free' => $method_free);
            $postString = http_build_query($postData);
            $opts = array('http' =>
                array('method' => 'POST', 'header' => 'Content-type: application/x-www-form-urlencoded', 'content' => $postString)
            );
            $context = stream_context_create($opts);
            $htmlPost = file_get_contents($url, false, $context);
            $link = self::parseContent($htmlPost);
        }
        unset($content);
        unset($htmlGet);
        unset($htmlPost);
        return $link;
    }

    public static function parseContent($html) {
        $link = "";
        $htmlCvt = SupportParse::cleanString($html);
        $arr1 = explode("{file:\"", $htmlCvt);
        $arr2 = explode("\",", $arr1[1]);
        $link = $arr2[0];
        unset($dom);
        unset($arr1);
        unset($arr2);
        return $link;
    }

}

class FlashXDotTv implements ParseAffMovie {

    const urlBase = "http://play.flashx.tv/";
    const urlPost = "http://play.flashx.tv/player/player.php";

    public static function requestContent($url) {
        $link = null;
        $htmlGet = file_get_html($url);
        $hash = array_shift($htmlGet->find("form[method=POST] input[name=hash]"))->value;
        $sechash = array_shift($htmlGet->find("form[method=POST] input[name=sechash]"))->value;
        $width = array_shift($htmlGet->find("form[method=POST] input[name=width]"))->value;
        $height = SupportParse::urlConvert(array_shift($htmlGet->find("form[method=POST] input[name=height]"))->value);
        $postData = array(
            'hash' => $hash, 'sechash' => $sechash, 'width' => $width, 'height' => $height
        );
        $postString = http_build_query($postData);
        $opts = array('http' =>
            array('method' => 'POST', 'header' => 'Content-type: application/x-www-form-urlencoded', 'content' => $postString)
        );
        $context = stream_context_create($opts);
        $htmlPost = file_get_contents(self::urlPost, false, $context);
        $link = self::parseContent($htmlPost);
        unset($url);
        unset($htmlGet);
        unset($htmlPost);
        return $link;
    }

    public static function processContent($content, $url) {
        $link = null;
        $htmlGet = file_get_html($url);
        if (!empty($htmlGet)) {
            $hash = array_shift($htmlGet->find("form[method=POST] input[name=hash]"))->value;
            $sechash = array_shift($htmlGet->find("form[method=POST] input[name=sechash]"))->value;
            $width = array_shift($htmlGet->find("form[method=POST] input[name=width]"))->value;
            $height = SupportParse::urlConvert(array_shift($htmlGet->find("form[method=POST] input[name=height]"))->value);
            $postData = array(
                'hash' => $hash, 'sechash' => $sechash, 'width' => $width, 'height' => $height
            );
            $postString = http_build_query($postData);
            $opts = array('http' =>
                array('method' => 'POST', 'header' => 'Content-type: application/x-www-form-urlencoded', 'content' => $postString)
            );
            $context = stream_context_create($opts);
            $htmlPost = file_get_contents(self::urlPost, false, $context);
            $link = self::parseContent($htmlPost);
            $link = self::paserContent($htmlPost);
        }
        unset($content);
        unset($htmlGet);
        unset($htmlPost);
        return $link;
    }

    public static function parseContent($html) {
        $link = "";
        $htmlCvt = SupportParse::cleanString($html);
        $arr1 = explode("flashx.swf?config=", $htmlCvt);
        $arr2 = explode("\"", $arr1[1]);
        $htmlRequest = file_get_contents($arr2[0]);
        $dom = new DOMDocument();
        $dom->loadXML($htmlRequest);
        $link = $dom->getElementsByTagName("file")->item(0)->nodeValue;
        unset($dom);
        unset($arr1);
        unset($arr2);
        unset($htmlRequest);
        return $link;
    }

}
