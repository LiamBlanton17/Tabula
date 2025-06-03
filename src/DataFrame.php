<?php

/**
 * 
 */
class DataFrame implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable {

    /**
     * @var array the associative array of data
     */
    private array $data;

    public function __construct(array $data) {
        $this->data = $data;
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
     * @param mixed $col is the new column name
     * @param mixed $value is the value to set it too
     * @return void
     */
    public function addCol(mixed $col, mixed $value): void {
        $this[$col] = $value;
    }

    /**
     * Drop a column (alternative to array access)
     * 
     * @param mixed $col is the new column name
     * @return void
     */
    public function dropCol(mixed $col): void {
        unset($this[$col]);
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
        if(is_array($offset)){
            $new_data = [];

            foreach($this->data as $row){
                $newRow = [];
                foreach($offset as $colName){
                    $newRow[$colName] = $row[$colName] ?? null;
                }
                $new_data[] = $newRow;
            }

            return new self($new_data);
        }

        return array_column($this->data, $offset);
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
        if (empty($this->data)) {
            return "Empty DataFrame";
        }

        $maxRows = 10; // show only first 10 rows
        $truncateLength = 15; // truncate long cell values
        $cols = $this->columns();

        // Collect column widths
        $colWidths = array_map('strlen', $cols);
        foreach (array_slice($this->data, 0, $maxRows) as $row) {
            foreach ($cols as $col) {
                $cell = $row[$col] ?? '';
                $str = is_scalar($cell) ? strval($cell) : json_encode($cell);
                $colWidths[$col] = max($colWidths[$col], strlen(mb_substr($str, 0, $truncateLength)));
            }
        }

        // Helper to pad/truncate values
        $formatCell = function ($val, $width) use ($truncateLength) {
            $val = is_scalar($val) ? strval($val) : json_encode($val);
            $val = mb_substr($val, 0, $truncateLength);
            return str_pad($val, $width);
        };

        // Header
        $output = '';
        foreach ($cols as $col) {
            $output .= str_pad($col, $colWidths[$col]) . ' | ';
        }
        $output = rtrim($output, ' | ') . "\n";

        // Divider
        foreach ($cols as $col) {
            $output .= str_repeat('-', $colWidths[$col]) . '-+-';
        }
        $output = rtrim($output, '-+-') . "\n";

        // Data rows
        foreach (array_slice($this->data, 0, $maxRows) as $row) {
            foreach ($cols as $col) {
                $output .= $formatCell($row[$col] ?? '', $colWidths[$col]) . ' | ';
            }
            $output = rtrim($output, ' | ') . "\n";
        }

        if (count($this->data) > $maxRows) {
            $output .= "... (" . count($this->data) . " rows total)\n";
        }

        return $output;
    }


    /**
     * Simple debugInfo function
     */
    public function __debugInfo(): array {
        return [
            'shape' => $this->shape(),
            'columns' => $this->columns(),
            'preview' => $this->head(5),
        ];
    }

}
