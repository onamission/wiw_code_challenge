<?php
include_once 'model.php';

class User extends BaseModel{
    /**
     * Initializes the object
     * 
     * The responsibility of this method is to instanciate the object
     * by extending the BaseModel's __construct to add the attributes
     * specific for the 'user' model
     * 
     * @author Tim Turnquist <tim.turnquist@gmail.com>
     * 
     */
    function __construct(){
        parent::__construct();
        $this->table_name = 'user';
        $this->column_list = ['id','first_name','last_name','username'];
    }
}
?>