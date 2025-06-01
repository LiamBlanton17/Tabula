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

}
