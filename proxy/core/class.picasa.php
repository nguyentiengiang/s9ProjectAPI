<?php

/**
 * @author Killer - killer.vn54119@hotmail.com
 * @example class.picasa.php Get direct link google plus & picasa web
 * @version 1.0
 */

class K_PicasaPlus {
    
    public $_url = null;
    public $_typeImage = array(
        'image/jpeg',
        'image/png',
        'image/gif'
    );
    public $_result = array();
    
    /**
     * Hàm định dạng kiểu dữ liệu
     * @return array & json
     */
    public function _format($type = 'json'){
        if(is_array($this->_result)){
            switch ($type) {
                case 'json':
                    return json_encode($this->_result);
                    break;
                case 'array':
                    return $this->_result;
                    break;
                default:
                    return json_encode($this->_result);
            } 
        }
    }
    
    /**
     * @return array & json
     */
    public function _getPicasaPlus(){
        switch($this->_checkUrl()){
            case 'plus':
                $countLink = preg_match_all("/[0-9]+/",$this->_getUrl(),$videoID);
                //echo '<pre>';print_r($videoID); die();
                $jsonURL = 'https://picasaweb.google.com/data/feed/tiny/user/'.$videoID[0][0].'/photoid/'.$videoID[0][2].'?alt=jsonm';
                $sourceJson = $this->_viewSource($jsonURL);
                break;
            case 'picasaweb':
                $sourceHTML = $this->_viewSource($this->_getUrl());
                $jsonURL = explode('"application/atom+xml","href":"',$sourceHTML);
                $jsonURL = explode('"},',$jsonURL[1]);
                $sourceJson = $this->_viewSource($jsonURL[0]);
                break;
            case false:
                return false;
                break;
            default:
                return false;
        }
        $decodeJson = json_decode($sourceJson);
        $decodeJson = $decodeJson->feed->media->content;
        $result = array();
        foreach($decodeJson as $key => $value){
            if(!in_array($value->type, $this->_typeImage)){
                $value->url = str_replace($value->url, $this->_getDirectLink($value->url), $value->url);
                $result[] = $value;
            }else{
                $result[] = $value;
            }
        }
        $this->_result = $result;
        return $this;
    }
    
    /**
     * Hàm lấy direct link
     * @param type $url
     * @return string
     */
    public function _getDirectLink($url) {
        $urlInfo = parse_url($url);
        $out  = "GET  {$url} HTTP/1.1\r\n";
        $out .= "Host: {$urlInfo['host']}\r\n";
        $out .= "User-Agent: {$_SERVER['USERAGENT']}\r\n";
        $out .= "Connection: Close\r\n\r\n";	
        if (!$con = @fsockopen($urlInfo['host'], 80, $errno, $errstr, 10))
            return $errstr." ".$errno;		
        fwrite($con, $out);
        $data = '';
        while (!feof($con)) {
            $data .= fgets($con, 512);
        }
        fclose($con);
        preg_match("!\r\n(?:Location|URI): *(.*?) *\r\n!", $data, $matches);
        $url = $matches[1];
        return trim($url);
    }
    
    /**
     * Hàm lấy mã nguồn (HTML) của một trang.
     * @param type $url
     * @param type $timeout
     * @return boolean
     */
    public function _viewSource($url,$timeout = 15){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_HTTPGET,true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_ENCODING , 'gzip, deflate');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $result = curl_exec($ch);
        if(curl_errno($ch)){
            return false;
        }else{
            return $result;
        }
    }
    
    /**
     * Hàm kiểm tra liên kết.
     * Plus & Picasa
     * @return boolean
     */
    public function _checkUrl(){
        if($this->_getUrl() != ''){
            if(preg_match_all('#plus.google.com#i',$this->_getUrl(),$output) == true){
                $type = 'plus';
            }elseif(preg_match_all('#picasaweb.google.com#i',$this->_getUrl(),$output) == true){
                $type = 'picasaweb';
            }else{
                $type = false;
            }
        }else{
            $type = false;
        }
        unset($output);
        
        return $type;
    }
    
    /**
     * Hàm gán biến
     * @param string
     */
    public function _setUrl($link){
        $this->_url = $link;
    }
    
    /**
     * Hàm trả kết quả của biến
     * @return type
     */
    public function _getUrl(){
        return $this->_url;
    }
}
