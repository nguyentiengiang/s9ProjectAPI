<?php

/**
 * Tien Giang Developer
 * Email: nguyentiengiang@outlook.com
 * Phone: +84 1282 303 100
 * */

namespace MyFile;

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

/**
 * @todo Write to log file
 * */
class Log {

    /**
     * @todo write log message
     * @param $message string
     * */
    private static $path = null;
    private static $fileLog = null;
    private static $content = null;

    static function write($message = null, $app = 'API', $db = 'api') {
        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/.AppsLog/' . $app)) {
            mkdir($_SERVER['DOCUMENT_ROOT'] . '/.AppsLog/' . $app, 0755, true);
        }
        self::$path = $_SERVER['DOCUMENT_ROOT'] . '/.AppsLog/' . $app . '/';
        self::$fileLog = '[' . date('Y.m.d') . '] - ' . $app . '@' . $db . '.txt';
        self::$content = '[' . date('Y-m-d H:i:s') . '] - ' . $message . PHP_EOL . '--------------' . PHP_EOL;
        File::write(self::$path . '/' . self::$fileLog, self::$content, true);
    }

    function __destruct() {
        unset(self::$path);
        unset(self::$fileLog);
        unset(self::$content);
    }

}

class Upload {

    function __construct() {
        
    }

}

class Image {

    function __construct() {
        
    }

}

class CSV {

    static function write($data, $path) {
        $result = 0;
        $outstream = fopen($path, 'a+');
        foreach ($data as $fields) {
            fputcsv($outstream, $fields);
        }
        rewind($outstream);
        $csv = fgets($outstream);
        fclose($outstream);
        if (!empty($csv)) {
            $result = 1;
        }
        return $result;
    }

    static function read($path) {
        $csvarray = array();
        if (file_exists($path)) {
            try {
                # Open the File.
                if (($handle = fopen($path, "r")) !== FALSE) {
                    # Set the parent multidimensional array key to 0.
                    $nn = 0;
                    while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
                        # Count the total keys in the row.
                        $c = count($data);
                        # Populate the multidimensional array.
                        for ($x = 0; $x < $c; $x++) {
                            $csvarray[$nn][$x] = $data[$x];
                        }
                        $nn++;
                    }
                    # Close the File.
                    fclose($handle);
                }
            } catch (Exception $exc) {
                //File::write(AppURL::logFolder('CSV@FileFolder') . 'log.' . date('Y_m_d') . '.txt', '[' . date('Y-m-d H:i:s') . '] - ' . $exc->getMessage() . '.' . PHP_EOL);
                Log::write($exc->getMessage());
            }
        }
        # Return the contents of the multidimensional array.
        return $csvarray;
    }

}

?>