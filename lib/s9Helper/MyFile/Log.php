<?php


namespace s9Helper\MyFile;

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
