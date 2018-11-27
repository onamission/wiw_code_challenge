<?php
include_once 'config.php';

class Database{
    static function get_connection($db_name=''){
        $c = new Config();
        $mysqli = '';
        $db_name = $db_name ? $db_name : $c->configuration['db']['dbname'];
        $mysqli = new mysqli("{$c->configuration['db']['servername']}:{$c->configuration['db']['port']}",
            $c->configuration['db']['username'],
            $c->configuration['db']['password'],
            $db_name);
        if($mysqli->connect_errno){
            echo "Failed to connect to MySQLi: (" . $mysqli->connect_errno . ") " . 
              $mysqli->connect_error;
        }
        return $mysqli; 
    }
}
?>