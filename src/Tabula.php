<?php

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

}
