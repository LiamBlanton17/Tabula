<?php

/**
 * 
 */
class DataFrame implements ArrayAccess {

    /**
     * @var array the associative array of data
     */
    private array $data;

    public function __construct(array $data) {
        $this->data = $data;
    }

    #
    # Basic functions
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
    public function head(int $n = 1, bool $as_dataframe = FALSE): mixed {
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
    public function tail(int $n = 1, bool $as_dataframe = FALSE): mixed {
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
    public function slice(int $start, int $n, bool $as_dataframe = FALSE): mixed {
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
    public function filter(callable $callback, bool $as_dataframe = TRUE): mixed {
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
    public function map(callable $callback, bool $as_dataframe = TRUE): mixed {
        $data = array_map($callback, $this->data);
        return $as_dataframe ? new self($data) : $data; 
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
    public function offsetExists($offset): bool {
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
    public function offsetGet($offset): mixed {
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
    public function offsetSet($offset, $value): void {
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
    public function offsetUnset($offset): void {
        foreach($this->data as &$row){
            unset($row[$offset]);
        }
    }

}
