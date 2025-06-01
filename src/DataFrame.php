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

    #
    # ArrayAccess functions
    #
    
    /**
     * Check if a column exists
     * 
     * @param mixed $offset is the column to check if it exists
     * @param bool if the offset exists
     */
    public function offsetExists(mixed $offset): bool {
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
    public function offsetGet(mixed $offset): mixed {
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
    public function offsetSet(mixed $offset, mixed $value): void {
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
    public function offsetUnset(mixed $offset): void {
        foreach($this->data as &$row){
            unset($row[$offset]);
        }
    }

}
