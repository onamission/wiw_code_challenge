<?php
include_once 'models/model.php';

/**
 * This is for me to test my scripts
 * 
 * It has been a couple years since I used PHPUnit for testing,
 * so in the interest of time, I have opted to not re-learn a testing
 * framework, but rather will create some simple functions that will 
 * enable me to test my code to ensure it works as expected.
 * 
 * @author Tim Turnquist <tim.turnquist@gmail.com>
 */

function test_create_sql_statements(){
    $my_model = new BaseModel();
    $my_model->table_name = 'shift';
    $my_model->column_list = ['id','user','time_in','time_out'];

    // READ ONE
    $expected = "SELECT id, user, time_in, time_out FROM shift WHERE 1 = 1 AND id = 32 ;";
    $actual = $my_model->prepare_read_one(32);
    check_test($expected, $actual, 'SELECT ONE');
    $expected = "SELECT id, user, time_in, time_out FROM shift WHERE 1 = 1 AND id = 32 ORDER BY user, time_in, time_out DESC;";
    $actual = $my_model->prepare_read_one(32, ['COLUMNS' => ['user', 'time_in', 'time_out'], 'DIRECTION' => 'DESC']);
    check_test($expected, $actual, 'SELECT ONE SORT');

    // READ
    $expected = "SELECT id, user, time_in, time_out FROM shift WHERE 1 = 1 AND user = 'Bob' ;";
    $actual = $my_model->prepare_read(['WHERE' =>['user' => "= 'Bob'"]]);
    check_test($expected, $actual, "SELECT ANY");
    $expected = "SELECT id, user, time_in, time_out FROM shift WHERE 1 = 1 AND user = 'Bob' ORDER BY user, time_in, time_out DESC;";
    $actual = $my_model->prepare_read(['WHERE' =>['user' => "= 'Bob'"],
      'SORT' => ['COLUMNS' => ['user', 'time_in', 'time_out'], 'DIRECTION' => 'DESC']]);
    check_test($expected, $actual, "SELECT ANY SORT");

    // UPDATE
    $expected = "UPDATE shift SET user = \"Otto\" WHERE 1 = 1 AND user = 'Bob';";
    $update_data =[
        'SET' => ['user' => "Otto"],
        'WHERE' =>['user' => "= 'Bob'"]
    ];
    $actual = $my_model->prepare_update('', $update_data);
    check_test($expected, $actual, "UPDATE");

    // INSERT
    $expected = "INSERT INTO shift SET user = \"Bob\";";
    $actual = $my_model->prepare_insert(['user' => "Bob"]);
    check_test($expected, $actual, "INSERT");

    // DELETE
    $expected = "DELETE FROM shift WHERE 1 = 1 AND user = 'Bob';";
    $actual = $my_model->prepare_remove('',['user' => "= 'Bob'"]);
    check_test($expected, $actual, "DELETE");
}

function test_foreign_key(){
    $my_model = new BaseModel();
    $my_model->table_name = 'makebelieve';
    $my_model->column_list = ['id', 'p.name','k.name'];
    $my_model->joined_tables = [
        ['TABLE' => 'prince AS p',
        'ON' => 'prince.id = makebelieve.prince_id',
        'TYPE' => 'LEFT JOIN'],
        ['TABLE' => 'kingdom AS k',
        'ON' => 'kingdom.id = makebelieve.kingdom_id']
    ];
    // READ
    $expected = "SELECT id, p.name, k.name FROM makebelieve " .
      "LEFT JOIN prince AS p ON prince.id = makebelieve.prince_id " .
      "INNER JOIN kingdom AS k ON kingdom.id = makebelieve.kingdom_id " .
      "WHERE 1 = 1 AND p.name = 'Charming' ;";
    $actual = $my_model->prepare_read(['WHERE' =>['p.name' => "= 'Charming'"]]);
    check_test($expected, $actual, "FORIEGN KEY");
}

function check_test($expected, $actual, $pass_message){
    if(trim($expected) == trim($actual)){
        echo "Test Passed: $pass_message\n";
    }else{
        echo "Test Failed:\nEXPECTED:     $expected\nACTUAL:       $actual\n";
    }
}

test_create_sql_statements();
test_foreign_key();