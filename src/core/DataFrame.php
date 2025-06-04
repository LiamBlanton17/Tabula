<?php


namespace Tabula\Core;
use \ArrayAccess, \Countable, \IteratorAggregate, \JsonSerializable;

/**
 * 
 */
class DataFrame implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable {

    /**
     * @var array the associative array of data
     */
    private array $data;

    /**
     * @var array the column types
     */
    private array $types = [];

    public function __construct(array $data) {
        $this->data = $data;
        if(isset($data[0])){
            $columns = $this->columns();
            $this->types = array_fill_keys($columns, ColumnType::UNKNOWN);
        }
    }

    #
    # Typing functions
    #

    /**
     * Function to infer the types of each columns. Does not return a new DataFrame, just the current one
     * 
     * @return self the current DataFrame
     */
    public function inferTypes(): self {
        $columns = $this->columns();
        $this->types = array_reduce($this->head(100), function($out, $row) use($columns) {
            foreach($columns as $col){
                $out[$col] = $this->_inferType($row[$col], $out[$col]);
            }
            return $out;
        }, array_fill_keys($columns, ColumnType::UNKNOWN));

        return $this;
    }

    /**
     * Private function to infer the type of a column
     * 
     * @param mixed $new is the new value in the column to infer
     * @param mixed $type is the current type of the column
     * @return int the new column type of the column
     */
    private function _inferType(mixed $new, mixed $type): int {
        switch(TRUE){
            case is_string($new): return $type | ColumnType::STRING;
            case is_int($new): return $type | ColumnType::INT;
            case is_float($new): return $type | ColumnType::FLOAT;
            case is_bool($new): return $type | ColumnType::BOOL;
        }
        return $type;
    }

    #
    # Export functions
    #
    
    /**
     * Function to get the data in the DataFrame as a PHP array
     * 
     * @return array the data as an array
     */
    public function toArray(): array {
        return $this->data;
    }

    /**
     * Function to get the data in the DataFrame as a PHP array
     * 
     * @return string the data as JSON
     */
    public function toJSON(): string {
        return json_encode($this->data);
    }

    /**
     * Export the DataFrame to a CSV file
     *
     * @param string $filename The path to the CSV file
     * @param string $delimiter The field delimiter
     * @return void
     */
    public function toCSV(string $filename, string $delimiter = ","): void {
        $fp = fopen($filename, 'w');
        if(!$fp){
            throw new Exception("Unable to open file for writing: $filename");
        }

        if(!empty($this->data)){
            fputcsv($fp, array_keys($this->data[0]), $delimiter);
        }

        foreach($this->data as $row){
            fputcsv($fp, $row, $delimiter);
        }

        fclose($fp);
    }

    #
    # Basic functions
    #

    /**
     * Function to get the columns of the DataFrame
     * 
     * @return array<string> the names of the columns
     */
    public function columns(): array {
        return array_keys($this->data[0] ?? []);
    } 

    /**
     * Function to get the shape of the DataFrame
     * 
     * @return array<int, int> the count of rows and columns
     */
    public function shape(): array {
        $row_count = count($this->data);
        if($row_count === 0){
            return [0, 0];
        }
        $col_count = count($this->data[0]);
        return [$row_count, $col_count];
    }

    /**
     * Function to access a row by integer
     * 
     * @param int $n is the row number to acess
     * @return mixed the row of data or false if the row does not exist
     */
    public function get(int $n): mixed {
        return $this->data[$n] ?? FALSE;
    } 

    /**
     * Function to get the head of the DataFrame
     * 
     * @param int $n is the number of rows to get
     * @param bool $as_dataframe is whether to return as a DataFrame or not
     * @return mixed is the head, either as an array or a new DataFrame
     */
    public function head(int $n = 1, bool $as_dataframe = FALSE) {
        $data = array_slice($this->data, 0, $n);
        return $as_dataframe ? new self($data) : $data;
    }

    /**
     * Function to get the tail of the DataFrame
     * 
     * @param int $n is the number of rows to get
     * @param bool $as_dataframe is whether to return as a DataFrame or not
     * @return mixed is the tail, either as an array or a new DataFrame
     */
    public function tail(int $n = 1, bool $as_dataframe = FALSE) {
        $data = array_slice($this->data, -$n);
        return $as_dataframe ? new self($data) : $data;
    }

    /**
     * Function to get a slice of the DataFrame
     * 
     * @param int $start is the starting index (0-based)
     * @param int $n is the number of rows to get
     * @param bool $as_dataframe is whether to return as a DataFrame or not
     * @return mixed the slice, either as an array or a new DataFrame
     */
    public function slice(int $start, int $n, bool $as_dataframe = FALSE) {
        $data = array_slice($this->data, $start, $n);
        return $as_dataframe ? new self($data) : $data;
    }

    /**
     * Rename a column to another name
     * 
     * @param mixed $old is the current columns name
     * @param mixed $new is the new columns name
     * @return DataFrame is the new DataFrame
     */
    public function renameCol(mixed $old, mixed $new): DataFrame {
        $data = $this->data;

        foreach($data as &$row){
            $row[$new] = NULL;
            if(array_key_exists($old, $row)){
                $row[$new] = $row[$old];
                unset($row[$old]);
            }
        }

        return new self($data);
    }

    /**
     * Add a new column (alternative to array access)
     * 
     * @param string $col is the new column name
     * @param mixed $value is the value to set it too
     * @return DataFrame the new DataFrame
     */
    public function assign(string $col, $value): DataFrame {
        $data = array_map(function($row) use($col, $value) {
            $row[$col] = is_callable($value) ? $value($row) : $value;
            return $row;
        }, $this->data);

        return new self($data);
    }

    /**
     * Drop column(s) (alternative to array access)
     * 
     * @param mixed $col is the column name(s)
     * @return DataFrame the new DataFrame
     */
    public function drop($cols): DataFrame {
        if(!is_array($cols)){
            $cols = [$cols];
        }

        $data = array_map(function($row) use($cols) {
            foreach($cols as $col){
                unset($row[$col]);
            }
            return $row;
        }, $this->data);

        return new self($data);
    }

    /**
     * Create a new DataFrame with only the projected columns (alternate to $df[[['col1'...]]])
     */
    public function project(array $columns): DataFrame {
        return $this[[$columns]];
    }

    /**
     * Sort DataFrame with custom function
     * 
     * @param callable $func the sorting function
     * @return DataFrame the new, sorted DataFrame
     */
    public function sort(callable $func): DataFrame {
        $data = $this->data;
        uasort($data, $func);
        return new self($data);
    }

    /**
     * Sort DataFrame by selected columns
     * 
     * @param mixed $cols are the col(s) to sort by
     * @param bool $asc true to sort asc, false to desc
     * @return DataFrame the new, sorted DataFrame
     */
    public function sortBy($cols, bool $asc = TRUE): DataFrame {
        if(!is_array($cols)){
            $cols = [$cols];
        }
        return $this->sort(function($a, $b) use($cols, $asc) {
            foreach($cols as $col){
                $cmp = $asc ? $a[$col] <=> $b[$col] : $b[$col] <=> $a[$col];
                if($cmp) return $cmp;
            }
            return 0;
        });
    }

    #
    # Countable interface
    #

    /**
     * Implement a count function for Countable interface
     * 
     * @return int the number of rows
     */
    public function count(): int {
        return count($this->data);
    }

    #
    # Functional functions (filter, map, ect)
    #

    /**
     * Provide filter with a closure that returns a boolean to filter the DataFrame
     * 
     * @param callable $callback is the filter fuction
     * @param bool $as_dataframe is whether to return as a DataFrame or not
     * @return mixed the filtered data, either as an array or a new DataFrame 
     */
    public function filter(callable $callback, bool $as_dataframe = TRUE) {
        $data = array_filter($this->data, $callback);
        return $as_dataframe ? new self($data) : $data; 
    }

    /**
     * Provide map with a closure that thats a row of data an manipulates it
     * 
     * @param callable $callback is the map fuction
     * @param bool $as_dataframe is whether to return as a DataFrame or not
     * @return mixed the filtered data, either as an array or a new DataFrame 
     */
    public function map(callable $callback, bool $as_dataframe = TRUE) {
        $data = array_map($callback, $this->data);
        return $as_dataframe ? new self($data) : $data; 
    }

    #
    # Iterator function
    #

    /**
     * Used by the IteratorAggregate interface, allows DataFrame to be iterable
     */
    public function getIterator(): Traversable {
        return new ArrayIterator($this->data);
    }

    #
    # JSON serialize function
    #

    /**
     * Used by JsonSerializable
     */
    public function jsonSerialize(): mixed {
        return $this->data;
    }

    #
    # ArrayAccess functions
    #
    
    /**
     * Check if a column exists
     * 
     * @param mixed $offset is the column to check if it exists
     * @param bool if the offset exists
     */
    public function offsetExists($offset) {
        return isset($this->data[0][$offset]);
    }

    /**
     * Return either a new DataFrame or an array via array access
     * Accessing via a column name will return an array of data from that column
     * Accessing via an array of column names will return a new DataFrame
     * 
     * @param mixed $offset is either a column name or an array of column names
     * @return mixed either an array or a new DataFrame
     */
    public function offsetGet($offset) {

        /**
         * When using: $df['col_name']
         */
        if(!is_array($offset)){
            return array_column($this->data, $offset);
        }

        $new_data = [];

        /**
         * When using: $df[[['col_name_1', 'col_name_2']]]
         */
        if(is_array($offset[0])){
            foreach($this->data as $row){
                $newRow = [];
                foreach($offset[0] as $colName){
                    $newRow[$colName] = $row[$colName] ?? null;
                }
                $new_data[] = $newRow;
            }
            return new self($new_data);
        }

        /**
         * When using: $df[['col_name_1', 'col_name_2']]
         */
        foreach($this->data as $row){
            $newRow = [];
            foreach($offset as $colName){
                $newRow[$colName] = $row[$colName] ?? null;
            }
            $new_data[] = $newRow;
        }
        return $new_data; 

    }

    /**
     * Setting a new column in the DataFrame
     * 
     * @param mixed $offset is the column name to add
     * @param mixed $value is the value to set each row's data too
     * @return void
     */
    public function offsetSet($offset, $value) {
        foreach($this->data as &$row){
            $row[$offset] = $value;
        }
    }

    /**
     * Unsetting a column in the DataFrame
     * 
     * @param mixed $offset is the column name to unset
     * @return void
     */
    public function offsetUnset($offset) {
        foreach($this->data as &$row){
            unset($row[$offset]);
        }
    }

    #
    # Debug and string functions
    #

    /**
     * Simple toString function
     */
    public function __toString(): string {
        if(empty($this->data)){
            return "Empty DataFrame";
        }

        // Define columns and widths
        $columns = $this->columns();
        $cols_max_width = array_reduce($this->data, fn($out, $row) => array_combine(array_keys($out), array_map(fn($col) => max($out[$col], strlen($row[$col] ?? '')), array_keys($out))), array_combine($columns, array_map('strlen', $columns)));
        $rows = [];

        // Add row function
        $addRow = fn($spacer, $func) => "|$spacer".implode("$spacer|$spacer", array_map(fn($col) => $func($col), $columns))."$spacer|";

        // Seperator row function
        $addSeperatorRow = fn() => $addRow('-', fn($col) => str_repeat('-', $cols_max_width[$col]));

        // Separator row
        $rows[] = $addSeperatorRow();

        // Header row
        $rows[] = $addRow(' ', fn($col) => str_pad($col, $cols_max_width[$col]));

        // Separator row
        $rows[] = $addSeperatorRow();

        // Data rows
        $rows = array_merge($rows, array_map(fn($row) => $addRow(' ', fn($col) => str_pad($row[$col] ?? '', $cols_max_width[$col])), $this->head(100)));

        // Separator row
        $rows[] = $addSeperatorRow();

        return "\n".implode("\n", $rows)."\n\n";
    }

    /**
     * Simple debug function
     */
    public function __debugInfo(): array {
        return [
            'shape' => $this->shape(),
            'columns' => $this->columns(),
            'preview' => $this->head(10),
        ];
    }

}
