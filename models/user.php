<?php
include_once 'model.php';

class User extends BaseModel{
    function __construct(){
        parent::__construct();
        $this->table_name = 'user';
        $this->column_list = ['id','first_name','last_name','username'];
    }
}
?>