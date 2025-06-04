<?php

namespace Tabula\Core;

/**
 * 
 */
class Tabula {

    /**
     * For now, Tabula will be a static entry point to the libray 
     */
    private function __construct() {}

    /**
     * Create a new DataFrame from an associative array
     * 
     * @param array $data an associative array of data
     * @return DataFrame the new DataFrame
     */
    public static function fromArray(array $data): DataFrame {
        return new DataFrame($data);
    }

    /**
     * Create a new DataFrame from a JSON string
     * 
     * @param string $data a JSON string
     * @return DataFrame the new DataFrame
     */
    public static function fromJSON(string $data): DataFrame {
        $decoded_data = json_decode($data, TRUE);
        return new DataFrame($decoded_data);
    }

    /**
     * Create a new DataFrame from a CSV file
     *
     * @param string $path Path to the CSV file
     * @return DataFrame The created DataFrame
     * @throws Exception If the file cannot be opened or is malformed
     */
    public static function fromCSV(string $path): DataFrame {
        $data = self::_readCSV($path);
        return new DataFrame($data);
    }

    /**
     * Internal CSV reader function
     *
     * @param string $path Path to the CSV file
     * @return array<int, array<string, string>> Parsed data
     * @throws Exception If file can't be opened or CSV is invalid
     */
    private static function _readCSV(string $path): array {
        $data = [];
        $handle = fopen($path, "r");

        if(!$handle){
            throw new Exception("Failed to open CSV file: $path");
        }

        $headers = fgetcsv($handle);
        if(!$headers){
            fclose($handle);
            throw new Exception("Empty or invalid CSV file: $path");
        }

        $num_headers = count($headers);
        while(($row = fgetcsv($handle)) !== false){
            if(count($row) !== $num_headers){
                fclose($handle);
                throw new Exception("Row does not match header length in CSV file: $path");
            }
            $data[] = array_combine($headers, $row);
        }

        fclose($handle);
        return $data;
    }

}
