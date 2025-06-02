<?php

include(__DIR__.'/../src/DataFrame.php');
include(__DIR__.'/../src/Tabula.php');

$data = [
    [
        'ID' => 1,
        'Name' => 'Alice',
        'Salary' => 70000,
        'Department' => 'IT',
        'Address' => '123 Main St',
        'StartDate' => '2020-01-15',
        'Rating' => 4.5,
    ],
    [
        'ID' => 2,
        'Name' => 'Bob',
        'Salary' => 52000,
        'Department' => 'HR',
        'Address' => '456 Oak Ave',
        'StartDate' => '2019-07-01',
        'Rating' => 3.8,
    ],
    [
        'ID' => 3,
        'Name' => 'Carol',
        'Salary' => 60000,
        'Department' => 'Finance',
        'Address' => '789 Pine Rd',
        'StartDate' => '2018-03-22',
        'Rating' => 4.2,
    ],
    [
        'ID' => 4,
        'Name' => 'David',
        'Salary' => 48000,
        'Department' => 'IT',
        'Address' => '321 Maple Dr',
        'StartDate' => '2021-09-10',
        'Rating' => 3.9,
    ],
    [
        'ID' => 5,
        'Name' => 'Eve',
        'Salary' => 75000,
        'Department' => 'Marketing',
        'Address' => '654 Birch Ln',
        'StartDate' => '2017-11-30',
        'Rating' => 4.7,
    ],
];

$df = Tabula::fromArray($data);

$names_of_50k_earners = $df->filter(fn($row) => $row['Salary'] > 50000)['Name'];

var_dump($names_of_50k_earners);