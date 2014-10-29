<?php

/**
 * Tien Giang Developer
 * Email: nguyentiengiang@outlook.com
 * Phone: +84 1282 303 100
 * */

namespace s9Helper\MyFile;

/**
 * @todo Access File for read and write
 * */
class File {

    /**
     * 	Read file (with option to apply Unix LF as standard line ending)
     * 	@return string
     * 	@param $file string
     * 	@param $lf bool
     * */
    public static function read($file, $lf = FALSE) {
        $out = file_get_contents($file);
        return $lf ? preg_replace('/\r\n|\r/', "\n", $out) : $out;
    }

    /**
     * 	Exclusive file write
     * 	@return int|FALSE
     * 	@param $file string
     * 	@param $data mixed
     * 	@param $append bool
     * */
    public static function write($file, $data, $append = FALSE) {
        return file_put_contents($file, $data, LOCK_EX | ($append ? FILE_APPEND : 0));
    }

    /**
     * Get Extension of file
     * @return string extension
     * @param $path Path to file
     */
    function getExtension($str) {

        $i = strrpos($str, ".");
        if (!$i) {
            return "";
        }

        $l = strlen($str) - $i;
        $ext = substr($str, $i + 1, $l);
        return $ext;
    }

}
