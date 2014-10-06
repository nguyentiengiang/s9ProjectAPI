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
/*
class LyricsNMusic {

    //http://api.lyricsnmusic.com/songs?api_key=506b6ee690d5df05dab005e1e4a6b6&artist=coldplay&track=clocks
    const apiKey = "506b6ee690d5df05dab005e1e4a6b6";
    const urlBase = "http://api.lyricsnmusic.com/songs";

    public static function processLyric($artist, $track) {
        $url = self::urlBase . "?api_key=" . self::apiKey . "&track=" . $track;
        $arrJson = json_decode(file_get_contents($url));
        $arrJsonCvt = array();
        foreach ($arrJson as $element) {
            $arrTemp['title'] = $element->title;
            $arrTemp['url'] = $element->url;
            $arrTemp['snippet'] = $element->snippet;
            $arrTemp['artist'] = $element->artist->name;
            array_push($arrJsonCvt, $arrTemp);
        }
        return $arrJsonCvt;
    }

    public static function requestLyric($url) {
        //echo $url; die();
        //$url = "http://localhost:82/s8/proxy/newhtml.html";
        $opts = array(
            'http' => array(
                'method' => "GET",
                'header' => "Accept-language: en\r\n"
            )
        );

        $context = stream_context_create($opts);
//        $fp = fopen($url, 'r', false, $context);
//        fpassthru($fp);
//        fclose($fp);
        $html = file_get_html($url, FALSE, $context);
        echo $html;
        die();
        //dd($html);
        $str = array_shift($html->find('pre[itemprop=description]'))->plaintext;
        dd($str);
        return $arrJsonCvt;
    }

}

class LyricsDotNet {

    const urlBase = "http://www.lyrics.net/";

    public static function requestLyric($url) {

        $html = file_get_html($url);

        $content = array_shift($html->find("pre[id=lyric-body-text]"))->plaintext;

        return $content;
    }

}
 * 
 */

class SongLyricsDotCom {

    const urlBase = "http://www.songlyrics.com/";

    public static function requestListLyric($song) {
        $arrList = array();
        $url = self::urlBase . "index.php?section=search&searchW=" . self::cvtString($song) . "&submit=Search";
        $html = file_get_html($url);
        if (!empty($html)) {
            $arrTemp = array();
            for ($i = 0; $i < 15; $i++) {
                $child = $html->find("div[class=serpresult]", $i);
                $arrTemp['song'] = array_shift($child->find("h3 a"))->plaintext;
                $arrTemp['artist'] = $child->find("div[class=serpdesc-2] p a", 0)->plaintext;
                $arrTemp['album'] = $child->find("div[class=serpdesc-2] p a", 1)->plaintext;
                $arrTemp['snippet'] = $child->find("div[class=serpdesc-2] p", 1)->plaintext;
                $arrTemp['thumb'] = array_shift($child->find("a img[class=imgserp]"))->src;
                $arrTemp['url'] = array_shift($child->find("h3 a"))->href;
                array_push($arrList, $arrTemp);
            }
        }
        unset($html);
        unset($arrTemp);
        return $arrList;
    }

    public static function requestLyric($url) {
        //http://www.songlyrics.com/toni-braxton/un-break-my-heart-lyrics/
        $content = array();
        $html = file_get_html($url);
        if (!empty($html)) {
            $content['lyric'] = trim(array_shift($html->find("div[id=songLyricsDiv-outer]"))->plaintext);
        }
        unset($html);
        return $content;
    }

    private static function cvtString($str) {
        $invalid = array(
            ' ' => '+'
        );
        $str = str_replace(array_keys($invalid), array_values($invalid), $str);
        return trim($str);
    }

}
