<?php

include(__DIR__.'/../src/core/ColumnType.php');
include(__DIR__.'/../src/core/DataFrame.php');
include(__DIR__.'/../src/core/Tabula.php');

use Tabula\Core\Tabula;

# Array of Test Data
$data = [
    ['ID' => 1, 'Name' => 'Alice', 'Salary' => 70000, 'Department' => 'IT', 'Address' => '123 Main St', 'StartDate' => '2020-01-15', 'Rating' => 4.5],
    ['ID' => 2, 'Name' => 'Bob', 'Salary' => 65000, 'Department' => 'HR', 'Address' => '456 Oak Ave', 'StartDate' => '2019-03-10', 'Rating' => 4.1],
    ['ID' => 3, 'Name' => 'Charlie', 'Salary' => 80000, 'Department' => 'IT', 'Address' => '789 Pine Rd', 'StartDate' => '2021-06-01', 'Rating' => 4.8],
    ['ID' => 4, 'Name' => 'Diana', 'Salary' => 72000, 'Department' => 'Finance', 'Address' => '101 Elm St', 'StartDate' => '2018-11-20', 'Rating' => 4.3],
    ['ID' => 5, 'Name' => 'Evan', 'Salary' => 69000, 'Department' => 'Marketing', 'Address' => '202 Cedar Ave', 'StartDate' => '2022-02-18', 'Rating' => 4.0],
    ['ID' => 6, 'Name' => 'Fay', 'Salary' => 76000, 'Department' => 'IT', 'Address' => '303 Maple Dr', 'StartDate' => '2017-09-25', 'Rating' => 4.6],
    ['ID' => 7, 'Name' => 'George', 'Salary' => 58000, 'Department' => 'HR', 'Address' => '404 Birch Ln', 'StartDate' => '2016-04-12', 'Rating' => 3.9],
    ['ID' => 8, 'Name' => 'Hannah', 'Salary' => 82000, 'Department' => 'Finance', 'Address' => '505 Spruce Ct', 'StartDate' => '2019-07-08', 'Rating' => 4.7],
    ['ID' => 9, 'Name' => 'Isaac', 'Salary' => 60000, 'Department' => 'Marketing', 'Address' => '606 Ash Blvd', 'StartDate' => '2020-10-30', 'Rating' => 3.8],
    ['ID' => 10, 'Name' => 'Jill', 'Salary' => 73000, 'Department' => 'IT', 'Address' => '707 Chestnut Way', 'StartDate' => '2021-12-15', 'Rating' => 4.4],
    ['ID' => 11, 'Name' => 'Kevin', 'Salary' => 68000, 'Department' => 'HR', 'Address' => '808 Willow Cir', 'StartDate' => '2018-05-03', 'Rating' => 4.2],
    ['ID' => 12, 'Name' => 'Lara', 'Salary' => 74000, 'Department' => 'Finance', 'Address' => '909 Poplar Ln', 'StartDate' => '2022-01-01', 'Rating' => 4.5],
    ['ID' => 13, 'Name' => 'Mike', 'Salary' => 67000, 'Department' => 'Marketing', 'Address' => '111 Walnut St', 'StartDate' => '2019-09-14', 'Rating' => 4.0],
    ['ID' => 14, 'Name' => 'Nina', 'Salary' => 79000, 'Department' => 'IT', 'Address' => '222 Redwood Dr', 'StartDate' => '2016-07-17', 'Rating' => 4.6],
    ['ID' => 15, 'Name' => 'Oscar', 'Salary' => 71000, 'Department' => 'Finance', 'Address' => '333 Cypress Ave', 'StartDate' => '2020-03-22', 'Rating' => 4.2],
    ['ID' => 16, 'Name' => 'Paula', 'Salary' => 62000, 'Department' => 'HR', 'Address' => '444 Beech St', 'StartDate' => '2021-11-11', 'Rating' => 3.7],
    ['ID' => 17, 'Name' => 'Quinn', 'Salary' => 85000, 'Department' => 'Marketing', 'Address' => '555 Sycamore Rd', 'StartDate' => '2018-06-06', 'Rating' => 4.9],
    ['ID' => 18, 'Name' => 'Rachel', 'Salary' => 78000, 'Department' => 'IT', 'Address' => '666 Magnolia Ct', 'StartDate' => '2017-02-02', 'Rating' => 4.3],
    ['ID' => 19, 'Name' => 'Sam', 'Salary' => 64000, 'Department' => 'HR', 'Address' => '777 Dogwood Blvd', 'StartDate' => '2022-04-04', 'Rating' => 4.1],
    ['ID' => 20, 'Name' => 'Tina', 'Salary' => 71000, 'Department' => 'Finance', 'Address' => '888 Hickory Dr', 'StartDate' => '2019-01-29', 'Rating' => 4.0]
];

# Using Tabula static interface to load data from array
$df = Tabula::fromArray($data);

# Defining a bonus function to calculate an employees bonus
function employeeBonus($salary, $rating){
    return $salary * ($rating / 20);
}

# Using the bonus function to create a new Bonus column
$df = $df->assign('Bonus', fn($row) => employeeBonus($row['Salary'], $row['Rating']));

# Sorting by the bonus - using FALSE to sort desc
$df = $df->sortBy('Bonus', FALSE);

# Hiding employee name, and other sensitive info - mimicking a bonus report that will only display an ambigous ID and Bonus
# Can also be written as:
# $df = $df->project(['ID', 'Bonus']);
$df = $df[[['ID', 'Bonus']]]->inferTypes();

var_dump($df);
# The previous sequence can be a one liner:
# $df->assign('Bonus', fn($row) => employeeBonus($row['Salary'], $row['Rating']))->sortBy('Bonus', FALSE)->project(['ID', 'Bonus']);

# Printing the DataFrame 
echo $df;