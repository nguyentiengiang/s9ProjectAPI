<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace s9Helper\MyFile;

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
