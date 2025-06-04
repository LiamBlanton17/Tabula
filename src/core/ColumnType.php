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
}
