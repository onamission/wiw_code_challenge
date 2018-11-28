<?php
require_once 'db.php';

class BaseModel{
    public $table_name;
    public $mysqli;
    public $column_list;
    public $joined_tables;

    /**
     * Initializes the object
     * 
     * The responsibility of this method is to instanciate the object
     * by creating a database connection
     * 
     * @author Tim Turnquist <tim.turnquist@gmail.com>
     * 
     */
    function __construct(){
        // Connect to the database
        if(!($this->mysqli)){
            $this->mysqli = Database::get_connection('db');
        }
    }

    /**
     * Creates an SQL SELECT statement (one record only based on ID)
     * 
     * The responsibility of this method is to create the SELECT
     * SQL statement
     * 
     * @author Tim Turnquist <tim.turnquist@gmail.com>
     * 
     * @param int   $id   a required record id to be selected
     * @return string a valid SQL SELECT statement
     */
    function prepare_read_one($id){
        if(!isset($id)){
            return '';
        }
        $tables_sql = $this->create_table_list();
        $where_sql = $this->create_where($id);
        $column_sql = implode(', ', $this->column_list);
        return "SELECT $column_sql FROM $tables_sql $where_sql;";
    }

    /**
     * Creates an SQL SELECT statement (possibly multiple records)
     * 
     * The responsibility of this method is to create the SELECT
     * SQL statement
     * 
     * @author Tim Turnquist <tim.turnquist@gmail.com>
     * 
     * @param string $filter an optional string of filtering criteria.
     * @param string $sort an optional string with sorting instructions.
     * @return string a valid SQL SELECT statement
     */
    function prepare_read($filter = '', $sort = ''){
        $tables_sql = $this->create_table_list();
        $where_sql = '';
        if($filter != ''){
            $where_sql = "WHERE 1 = 1 AND " . $this->substitute_symbols($filter);
        }
        $sort_sql = '';
        if($sort != ''){
            $sort_sql = "ORDER BY " . $this->substitute_symbols($sort);
        }elseif(isset($this->sort)){
            $sort_sql = $this->sort;
        }
        $column_sql = implode(', ', $this->column_list);
        $ret_val = "SELECT $column_sql FROM $tables_sql $where_sql $sort_sql;";
        return $ret_val;
    }

    /**
     * Exchanges codes for symbols and comparision operators
     * 
     * The responsibility of this method is convert the codes that
     * are passed in on the query string into more meaningful
     * symbols and comparision operators to be used in WHERE and
     * SORT clauses of the SQL statement
     * 
     * @author Tim Turnquist <tim.turnquist@gmail.com>
     * 
     * @param string $string to be decoded from a query string
     * @return string a SQL ready string
     */
    protected function substitute_symbols($string){
        $string = str_replace('__dot__', '.', $string);
        $string = str_replace('__q__', '"', $string);
        $string = str_replace('__t__', "'", $string);
        $string = str_replace('__gt__', ' > ', $string);
        $string = str_replace('__ge__', ' >= ', $string);
        $string = str_replace('__lt__', ' < ', $string);
        $string = str_replace('__le__', ' <= ', $string);
        $string = str_replace('__eq__', ' = ', $string);
        $string = str_replace('__ne__', ' != ', $string);
        $string = str_replace('__in__', ' IN ', $string);
        $string = str_replace('__cm__', ', ', $string);
        $string = str_replace('__as__', ' ASC', $string);
        $string = str_replace('__de__', ' DESC', $string);
        return $string;
    }

    /**
     * Creates an SQL DELETE statement
     * 
     * The responsibility of this method is to create the DELETE
     * SQL statement
     * 
     * @author Tim Turnquist <tim.turnquist@gmail.com>
     * 
     * @param int   $id   an optional record id to be deleted
     * @param array $filter an optional array of column_name => expression format
     * @return string a valid SQL DELETE statement
     */
    function prepare_remove($id = '', $filter = []){
        $where_sql = $this->create_where($id, $filter);
        $ret_val = "DELETE FROM {$this->table_name} $where_sql;";
        return $ret_val;
    }

    /**
     * Creates an SQL INSERT statement
     * 
     * The responsibility of this method is to create the INSERT
     * SQL statement
     * 
     * @author Tim Turnquist <tim.turnquist@gmail.com>
     * 
     * @param array $data an optional associative array of column_name => value format
     * @return string a valid SQL INSERT statement
     * 
     * NOTE: I prefer to use the INSERT INTO <table> SET format, not the typical
     * INSERT INTO <table> <column list> VALUES(<values list>) because it is more
     * consistent with the UPDATE statements. This syntax only works with MySQL.
     */
    function prepare_insert($data = []){
        // if there is no data to set, then return an empty string
        if($data == []){
            return '';
        }
        $set_sql = $this->create_set($data);
        // check again to make sure there were proper columns listed in the SET
        if(strlen($set_sql) == 0){
            return '';
        }
        $return_string = "INSERT INTO {$this->table_name} $set_sql;";
        return $return_string;
    }

    /**
     * Creates an SQL UPDATE statement
     * 
     * The responsibility of this method is to create the UPDATE
     * SQL statement
     * 
     * @author Tim Turnquist <tim.turnquist@gmail.com>
     * 
     * @param int   $id   an optional record id to be updated
     * @param array $data an optional associative array that contains
     *  a column_name => value pair
     *  
     * NOTE: I originally coded this to take an additional WHERE 
     * parameter, but then noticed the requirements call for only 
     * updating one record at a time by ID, so I removed that 
     * 
     * @return string a valid SQL UPDATE statement
     */
    function prepare_update($id = '', $data = []){
        // if there is no data to set, then return an empty string
        if($data == [] || $id == ''){
            return '';
        }
        $set_sql = '';
        $where_sql = '';
        $set_sql = $this->create_set($data);
        // check again to make sure there were proper columns listed in the SET
        if(strlen($set_sql) == 0){
            return '';
        }

        $where_sql = $this->create_where($id, []);
        
        return "UPDATE {$this->table_name} $set_sql $where_sql;";
    }

    /**
     * Creates the WHERE clause of the SQL statement
     * 
     * The responsibility of this method is to create a WHERE
     * portion of the SQL statement to be used for almost any
     * SQL statement. 
     * 
     * @author Tim Turnquist <tim.turnquist@gmail.com>
     * 
     * @param array $filter an assosiative array of column_names and expressions
     * example: $filter = ['column' => 'BETWEEN 1 and 10']
     * or
     * example: $filter = ['column' => '= 10']
     * @return string a valid SQL WHERE clause
     */
    protected function create_where($id = '', $filter = []){
        $where = 'WHERE 1 = 1';
        if($id != ''){
            $where .= " AND {$this->table_name}.id = $id";
        }
        foreach($filter as $column => $expression){
            // make sure it is in our column list before we add it to the WHERE
            if(in_array($column, $this->column_list)){
                $where .= " AND $column $expression";
            }
        }
        return $where;
    }

    /**
     * Creates the SET segment of the SQL statement
     * 
     * The responsibility of this method is to create the SET
     * portion of the SQL statement to be used for both UPDATE and
     * INSERT statements
     * 
     * @author Tim Turnquist <tim.turnquist@gmail.com>
     * 
     * @param array $data an associative array of column_name = value
     * @return string a valid SET clause to an SQL statement
     */
    protected function create_set($data = []){
        $set = '';
        foreach($data as $column => $value){
            // make sure it is in our column list before we add it to the WHERE
            if(in_array($column, $this->column_list)){
                // the first time through add the SET keyword, otherwise add a comma
                $set .= (strlen($set) == 0 ? "SET " : ", ") . "$column = \"$value\"";
            }
        }
        return $set;
    }

    /**
     * Creates the table list of an SQL SELECT statement
     * 
     * The responsibility of this method is to create the table
     * listing for a SELECT SQL statement which enables JOINS in
     * the return set
     * 
     * @author Tim Turnquist <tim.turnquist@gmail.com>
     * 
     * @return string a valid list of tables for an SQL SELECT statement
     */
    function create_table_list(){
        $tables = "{$this->table_name}";
        if(isset($this->joined_tables) && count($this->joined_tables)){
            foreach($this->joined_tables as $t){
                if(array_key_exists('TABLE', $t) && array_key_exists('ON', $t)){
                    $type = (array_key_exists('TYPE', $t)? $t['TYPE']: "INNER JOIN");
                    $tables .= " $type {$t['TABLE']} ON {$t['ON']}";
                }
            }
        }
        return $tables;
    }

    /**
     * Executes the SQL statement
     * 
     * The responsibility of this method is to execute the SQL 
     * statement
     * 
     * @author Tim Turnquist <tim.turnquist@gmail.com>
     * 
     * @param string $sql an SQL statement to be run
     * @return mixed a recordset if a SELECT or INSERT statement,
     *   a count of UPDATEd or DELETEd records or an exception
     */
    protected function run($sql = ''){
        if(strlen($sql) == 0 || !$this->mysqli){
            return "WARNING: Nothing to run";
        }
        return $this->mysqli->query($sql);
    }

    /**
     * Executes a SELECT ONE SQL statement
     * 
     * The responsibility of this method is to execute a single SELECT 
     * SQL statement -- returning only one row based on record ID
     * 
     * @author Tim Turnquist <tim.turnquist@gmail.com>
     * 
     * @param int $id the record ID to be found
     * @return mixed a recordset containing a single record
     */
    public function run_read_one($id){
        $sql = $this->prepare_read_one($id);
        $res = $this->run($sql);
        return mysqli_fetch_all($res, MYSQLI_ASSOC);
    }

    /**
     * Executes a SELECT (multiple) SQL statement
     * 
     * The responsibility of this method is to execute a SELECT 
     * SQL statement -- possibly being filtered and sorted
     * 
     * @author Tim Turnquist <tim.turnquist@gmail.com>
     * 
     * @param string $filter an optional filter
     * @param string $sort an optional string
     * @return mixed a recordset containing a single record
     * 
     * NOTE: Where other places I used associative arrays for my
     * filter variables, the SELECT is called from the
     * GET method, so it is easier to pass a query string.
     */
    public function run_read($filter = '', $sort = ''){
        $sql = $this->prepare_read($filter, $sort);
        $res = $this->run($sql);
        return mysqli_fetch_all($res, MYSQLI_ASSOC);
    }

    /**
     * Executes an UPDATE SQL statement
     * 
     * The responsibility of this method is to execute an UPDATE 
     * SQL statement
     * 
     * @author Tim Turnquist <tim.turnquist@gmail.com>
     * 
     * @param int $id the record ID to be found
     * @param array $data the new data to update the record
     * @return stirng a message showing the rows updated or an exception
     */
    public function run_update($id=[], $data=[]){
        $sql = $this->prepare_update($id, $data);
        $req = $this->run($sql);
        if(is_numeric($req)){
            return "Updated $req record" . ($req > 1 ? "s" : "");
        }
        return $req;
    }

    /**
     * Executes an INSERT SQL statement
     * 
     * The responsibility of this method is to execute an INSERT 
     * SQL statement, creating a new record
     * 
     * @author Tim Turnquist <tim.turnquist@gmail.com>
     * 
     * @param array $data the data for the new record
     * @return stirng a message showing the rows inserted or an exception
     */
    public function run_insert($data){
        if($data==[]){
            return "No Data To Set";
        }
        $sql = $this->prepare_insert($data);
        $req = $this->run($sql);
        if(is_numeric($req)){
            return "Inserted $req record" . ($req > 1 ? "s" : "");
        }
        return $req;
    }

    /**
     * Executes a DELETE SQL statement
     * 
     * The responsibility of this method is to execute a DELETE 
     * SQL statement 
     * 
     * @author Tim Turnquist <tim.turnquist@gmail.com>
     * 
     * @param int $id optional, an easy way to filter by a record ID
     * @param array $filter optional -- a way to delete multiple records at once
     * @return stirng a message showing the rows deleted or an exception
     */
    public function run_delete($id = '', $filter = []){
        $sql = $this->prepare_remove($id, $filter);
        $req = $this->run($sql);
        if(is_numeric($req)){
            return "Deleted $req record" . ($req > 1 ? "s" : "");
        }
        return $req;
    }
}
?>