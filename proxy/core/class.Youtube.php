<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Youtube
 *
 * @author Tien Giang
 */
class Youtube {
    static function requestContent($v) {
        $url = "https://www.youtube.com/get_video_info?&video_id=" . $v . "&el=detailpage&ps=default&eurl=&gl=US&hl=en";
        $content = file_get_contents($url);
        d($v);
        dd($content);
        return $content;
    }
}
?>
