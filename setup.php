<?php
include_once 'config.php';
include_once 'db.php';

function new_setup($db_name=''){
    $c = new Config();
    $db = new Database();
    $db_name = $db_name ? $db_name : $c->configuration['dbname'];

    // create a connection to mysql
    $connection = $db->get_connection();
    if(is_string($connection)){
        return;
    }
    echo "Connected to db\n";

    // create the database
    $connection->query("CREATE DATABASE IF NOT EXISTS $db_name");
    if($connection->error){
        echo "ERROR creating `$db_name` Database: $connection->error\n";
        return $connection->error;
    }
    $connection->query("USE $db_name");
    echo "Database `$db_name` exists\n";

    // create the user table
    create_user_table($connection);
    if($connection->error){
        echo "ERROR creating `user` Table: $connection->error\n";
        return $connection->error;
    }
    echo "Table `user` exists\n";

    // create the shift table
    create_shift_table($connection);
    if($connection->error){
        echo "ERROR creating `shift` Table: $connection->error\n";
        return $connection->error;
    }
    echo "Table `shift` exists\n";

    echo "Everything is ready to go!";
    return $connection;

}

function create_user_table(&$connection){
    $sql = <<<EOF
CREATE TABLE IF NOT EXISTS user (
    id int NOT NULL AUTO_INCREMENT,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    username VARCHAR(100),
    PRIMARY KEY(id) );
EOF;
    $connection->query($sql);
    $sql = <<<EOF
    INSERT INTO user (first_name, last_name, username) VALUES
    ('Ella', 'Fitzgerald', 'summertime'),
    ('Jimmy', 'Buffet', 'margaritaville'),
    ('Roger', 'Daltrey', 'pinballwizard'),
    ('Taylor', 'Swift', 'shakeitoff'),
    ('Elvis', 'Presley', 'hounddog');
EOF;
return $connection->query($sql);
}

function create_shift_table(&$connection){
    $sql = <<<EOF
CREATE TABLE IF NOT EXISTS shift (
    id int NOT NULL AUTO_INCREMENT,
    user_id int,
    time_in DATETIME,
    time_out DATETIME,
    PRIMARY KEY(id),
    FOREIGN KEY (user_id)
        REFERENCES user(id)
        ON DELETE CASCADE);
EOF;
    $connection->query($sql);
    $sql = <<<EOF
    INSERT INTO shift (user_id, time_in, time_out) VALUES
    (1, STR_TO_DATE('2018-11-24 8:00',"%Y-%m-%d %H:%i:%s"), STR_TO_DATE('2018-11-24 11:00:00',"%Y-%m-%d %H:%i:%s")),
    (2, STR_TO_DATE('2018-11-24 8:00:00',"%Y-%m-%d %H:%i:%s"), STR_TO_DATE('2018-11-24 11:00:00',"%Y-%m-%d %H:%i:%s")),
    (3, STR_TO_DATE('2018-11-24 8:00:00',"%Y-%m-%d %H:%i:%s"), STR_TO_DATE('2018-11-24 11:00:00',"%Y-%m-%d %H:%i:%s")),
    (4, STR_TO_DATE('2018-11-24 8:00:00',"%Y-%m-%d %H:%i:%s"), STR_TO_DATE('2018-11-24 11:00:00',"%Y-%m-%d %H:%i:%s")),
    (5, STR_TO_DATE('2018-11-24 8:00:00',"%Y-%m-%d %H:%i:%s"), STR_TO_DATE('2018-11-24 11:00:00',"%Y-%m-%d %H:%i:%s")),
    (1, STR_TO_DATE('2018-11-24 12:00:00',"%Y-%m-%d %H:%i:%s"), STR_TO_DATE('2018-11-24 17:00:00',"%Y-%m-%d %H:%i:%s")),
    (2, STR_TO_DATE('2018-11-24 12:00:00',"%Y-%m-%d %H:%i:%s"), STR_TO_DATE('2018-11-24 17:00:00',"%Y-%m-%d %H:%i:%s")),
    (3, STR_TO_DATE('2018-11-24 12:00:00',"%Y-%m-%d %H:%i:%s"), STR_TO_DATE('2018-11-24 17:00:00',"%Y-%m-%d %H:%i:%s")),
    (4, STR_TO_DATE('2018-11-24 12:00:00',"%Y-%m-%d %H:%i:%s"), STR_TO_DATE('2018-11-24 17:00:00',"%Y-%m-%d %H:%i:%s")),
    (5, STR_TO_DATE('2018-11-24 12:00:00',"%Y-%m-%d %H:%i:%s"), STR_TO_DATE('2018-11-24 17:00:00',"%Y-%m-%d %H:%i:%s")),
    (1, STR_TO_DATE('2018-11-25 8:00:00',"%Y-%m-%d %H:%i:%s"), STR_TO_DATE('2018-11-25 11:00:00',"%Y-%m-%d %H:%i:%s")),
    (2, STR_TO_DATE('2018-11-25 8:00:00',"%Y-%m-%d %H:%i:%s"), STR_TO_DATE('2018-11-25 11:00:00',"%Y-%m-%d %H:%i:%s")),
    (3, STR_TO_DATE('2018-11-25 8:00:00',"%Y-%m-%d %H:%i:%s"), STR_TO_DATE('2018-11-25 11:00:00',"%Y-%m-%d %H:%i:%s")),
    (4, STR_TO_DATE('2018-11-25 8:00:00',"%Y-%m-%d %H:%i:%s"), STR_TO_DATE('2018-11-25 11:00:00',"%Y-%m-%d %H:%i:%s")),
    (5, STR_TO_DATE('2018-11-25 8:00:00',"%Y-%m-%d %H:%i:%s"), STR_TO_DATE('2018-11-25 11:00:00',"%Y-%m-%d %H:%i:%s")),
    (1, STR_TO_DATE('2018-11-25 12:00:00',"%Y-%m-%d %H:%i:%s"), STR_TO_DATE('2018-11-25 11:00:00',"%Y-%m-%d %H:%i:%s")),
    (2, STR_TO_DATE('2018-11-25 12:00:00',"%Y-%m-%d %H:%i:%s"), STR_TO_DATE('2018-11-25 11:00:00',"%Y-%m-%d %H:%i:%s")),
    (3, STR_TO_DATE('2018-11-25 12:00:00',"%Y-%m-%d %H:%i:%s"), STR_TO_DATE('2018-11-25 11:00:00',"%Y-%m-%d %H:%i:%s")),
    (4, STR_TO_DATE('2018-11-25 12:00:00',"%Y-%m-%d %H:%i:%s"), STR_TO_DATE('2018-11-25 11:00:00',"%Y-%m-%d %H:%i:%s")),
    (5, STR_TO_DATE('2018-11-25 12:00:00',"%Y-%m-%d %H:%i:%s"), STR_TO_DATE('2018-11-25 11:00:00',"%Y-%m-%d %H:%i:%s"));
EOF;
return $connection->query($sql);
}

new_setup('db');
?>