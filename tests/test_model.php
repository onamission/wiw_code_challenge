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
    $my_model->column_list = ['shift.id', 'shift.user_id','user.username','shift.time_in','shift.time_out'];

    // READ ONE
    $expected = "SELECT shift.id, shift.user_id, user.username, shift.time_in, shift.time_out FROM shift WHERE 1 = 1 AND shift.id = 32;";
    $actual = $my_model->prepare_read_one(32);
    check_test($expected, $actual, 'SELECT ONE');
    
    // READ
    $expected = "SELECT shift.id, shift.user_id, user.username, shift.time_in, shift.time_out FROM shift WHERE 1 = 1 AND user_id = 'Bob' ;";
    $actual = $my_model->prepare_read("user_id = 'Bob'");
    check_test($expected, $actual, "SELECT ANY");
    $expected = "SELECT shift.id, shift.user_id, user.username, shift.time_in, shift.time_out FROM shift WHERE 1 = 1 AND user_id = 'Bob' ORDER BY 'user_id', 'time_in', 'time_out' DESC;";
    $actual = $my_model->prepare_read("user_id__eq____t__Bob__t__", 
        "__t__user_id__t____cm____t__time_in__t____cm____t__time_out__t____de__");
    check_test($expected, $actual, "SELECT ANY SORT");

    // UPDATE
    $expected = "UPDATE shift SET shift.time_in = \"2018-11-14 4:56\" WHERE 1 = 1 AND shift.id = 3;";
    $update_data = ['shift.time_in' => "2018-11-14 4:56"];
    $actual = $my_model->prepare_update(3, $update_data);
    check_test($expected, $actual, "UPDATE");

    // INSERT
    $expected = "INSERT INTO shift SET shift.user_id = \"3\", shift.time_in = \"2018-11-14 4:56\", shift.time_out = \"2018-11-14 4:56\";";
    $actual = $my_model->prepare_insert([
        'shift.user_id'=> 3, 
        'shift.time_in' => "2018-11-14 4:56", 
        'shift.time_out' => "2018-11-14 4:56"
        ]);
    check_test($expected, $actual, "INSERT");

    // DELETE
    $expected = "DELETE FROM shift WHERE 1 = 1 AND shift.user_id = 3;";
    $actual = $my_model->prepare_remove('',['shift.user_id' => "= 3"]);
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
    $actual = $my_model->prepare_read("p__dot__name__eq____t__Charming__t__");
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