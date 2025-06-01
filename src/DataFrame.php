<?php

/**
 * 
 */
class DataFrame {

    /**
     * @var array the associative array of data
     */
    private array $data;

    public function __construct(array $data) {
        $this->data = $data;
    }

    /**
     * Function to get the shape of the DataFrame
     */
    public function shape(): array {
        $row_count = count($this->data);
        if($row_count === 0){
            return [0, 0];
        }
        $col_count = count($this->data[0]);
        return [$row_count, $col_count];
    }

}
