<?php

namespace Tabula\Core;

/**
 * Enum used to type the columns
 * 
 */
final class ColumnType {
    const UNKNOWN = 0;  // 0000
    const STRING  = 1;  // 0001
    const INT     = 2;  // 0010
    const FLOAT   = 4;  // 0100
    const BOOL    = 8;  // 1000
    const ANY     = 15; // 1111

    /**
     * Function to get all types possibles
     * 
     * @return array an array of the types
     */
    public static function columnTypes(): array {
        return [self::BOOL, self::FLOAT, self::INT, self::STRING];
    }

    /**
     * Converts type to strings
     * 
     * @param int $type is the type to convert
     * @return string the type as a string
     */
    public static function typeToString(int $type): string {
        $names = [];

        if ($type & self::STRING) $names[] = 'String';
        if ($type & self::INT)    $names[] = 'Int';
        if ($type & self::FLOAT)  $names[] = 'Float';
        if ($type & self::BOOL)   $names[] = 'Bool';

        if (count($names) === 0) return 'Unknown';
        if (count($names) === 4) return 'Any';

        return implode('|', $names);
    }
    
}
