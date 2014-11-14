<?php

/*
 * Tien Giang Developer
 * @Author TienGiang <br>Mail: nguyentiengiang@outlook.com<br>Phone: +84 1282 303 100
 * @Version Class Version <17/Mar/2014>
 * @Since 1.0
 */

class ChiaAnime {

    public static $urlSub = 'http://www.chia-anime.com/index';
    public static $urlDub = 'http://www.chia-anime.com/watch-anime-dub/';

    public static function getINFOLinkMovieSubFilm($url, $po = 0) {
        $link = "";
        try {
            $html = file_get_html($url);
            $urlPlay = array_shift($html->find("div[id='more-nav'] a[id='download']"))->href;
            $urlPlayM = array_shift($html->find("div[id='more-nav'] a[rel=nofollow]"))->href;
            if ($po <= 4) {
                $htmlPlay = file_get_html($urlPlay);
                $link = $htmlPlay->find("div[id='wrap'] table table[class='table'] td[align='right'] a", $po)->href;
            }
            if ($po == 5) {
                $htmlPlay = file_get_html($urlPlayM);
                $link = array_shift($htmlPlay->find("div a[download]"))->href;
            }
            if (strpos($link, "http") === FALSE) {
                $htmlPlay = file_get_html($urlPlayM);
                $dom = new DOMDocument();
                $dom->loadHTML($htmlPlay);
                $arr = explode("file: '", $dom->textContent);
                $arrQ = explode("'", $arr[1]);
                $link = $arrQ[0];
            }
            unset($htmlPlay);
        } catch (Exception $exc) {
            $link = "";
        }
        return $link;
    }

}

