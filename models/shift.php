<?php
include_once 'model.php';
include_once 'user.php';

class Shift extends BaseModel{

    /**
     * Initializes the object
     * 
     * The responsibility of this method is to instanciate the object
     * by extending the BaseModel's __construct to add the attributes
     * specific for the 'shift' model
     * 
     * @author Tim Turnquist <tim.turnquist@gmail.com>
     */
    function __construct(){
        parent::__construct();
        $this->table_name = 'shift';
        $this->column_list = ['shift.id', 'shift.user_id', 'user.username', 'shift.time_in', 'shift.time_out'];
        $this->sort = 'ORDER BY time_in, time_out DESC';
        $this->joined_tables = [
            [ 'TABLE' => 'user',
            'ON' => 'user.id = shift.user_id',
            'TYPE' => 'INNER JOIN']
        ];
    }

    /**
     * Validate Time
     * 
     * The responsibility of this method is to ensure that we are not
     * overlapping on anyone's time. We have to compare the new or
     * changed record with other records for that same user in a
     * specific time period in order to make sure we don't overlap
     * 
     * @author Tim Turnquist <tim.turnquist@gmail.com>
     * 
     * @param int $id optional, used for UPDATING a record to get the user
     * @param array $data optional, the data to be added or changed
     * @return string a message showing the new data is valid or a
     * reason that it is not
     */
    function validate_time($id='', $data=[]){
        // if we have no data to set return warning
        if($data == []){
            return 'No data to set';
        }
        // if we have no user or record to get a user from, then return warning
        if(!array_key_exists('user.username', $data) && $id == ''){
            return "A user is needed for this operation";
        }
        // if we have no times to post, then return a warning
        if(!array_key_exists('shift.time_in', $data) &&
          !array_key_exists('shift.time_out', $data)){
            return 'At least one time is needed for this operation';
        }
        // determine the start and end times based on the SET data
        $start_time = array_key_exists('shift.time_in', $data) ? 
          $data['shift.time_in'] :
          '';
        $end_time = array_key_exists('shift.time_out', $data) ? 
          $data['shift.time_out'] :
          '';
        // determine the user from the SET data or the record passed
        $user = '';
        if($id != ''){
            $record = $this->run_read_one($id);
            $user = $record[0]['username'];
            if($start_time == ''){
                $start_time = $record[0]['time_in'];
            }
            if($end_time == ''){
                $end_time = $record[0]['time_out'];
            }
        }else{
            $user = $data['user.username'];
            if($start_time == ''){
                return "No Start Time";
            }
            if($end_time == ''){
                return "No End Time";
            }
        }
        // create the WHERE statment to get a list of records
        $filter = "user.username = '$user'";
        if($id != ''){
            $filter .= " AND shift.id != $id";
        }
        if($start_time != ''){
            $filter .= " AND shift.time_out >= '$start_time'";
        }
        if($end_time != ''){
            $filter .= " AND shift.time_in <= '$end_time'";
        }

        // get all shift records for the user within the proper timeframe
        $records = $this->run_read($filter);
        $start_timestamp = strtotime($start_time);
        $end_timestamp = strtotime($end_time);
        // if we have any records, loop through them to try to find overlap
        $valid = '';
        if($records != ''){ 
            foreach($records as $rec){
                if($start_time != '' && $end_time != '' &&
                  strtotime($rec['time_in']) >= $start_timestamp &&
                  strtotime($rec['time_out']) <= $end_timestamp){
                    $valid .= " * Time Conflict: Another shift is inside of this shift\n";
                    continue;
                }
                if($start_time != '' && $end_time != '' &&
                  strtotime($rec['time_in']) <= $start_timestamp &&
                  strtotime($rec['time_out']) >= $end_timestamp){
                    $valid .= " * Time Conflict: This shift is inside of another shift\n";
                    continue;
                }
                if($start_time != '' && strtotime($rec['time_in']) <= $start_timestamp &&
                  strtotime($rec['time_out']) > $start_timestamp){
                    $valid .= " * Time Conflict: Start time in is other shift\n";
                    continue;
                }
                if($end_time != '' && strtotime($rec['time_in']) < $end_timestamp && 
                  strtotime($rec['time_out']) >= $end_timestamp){
                    $valid .= " * Time Conflict: End time is in other shift\n";
                    continue;
                }
            }
        }
        // if we have made it this far, we have a Valid statement
        if($valid == ''){
            $valid = "Valid";
        }
        return $valid;
    }

    /**
     * Executes a UPDATE SQL statement
     * 
     * The responsibility of this method is to extend
     * the BaseModel's run_update to add the specific 
     * time validation on UPDATE
     * 
     * @author Tim Turnquist <tim.turnquist@gmail.com>
     * 
     * @param int $id optional, used for UPDATING a record to get the user
     * @param array $data optional, the data to be added or changed
     * @return string a message showing the rows updated or an exception
     */
    public function run_update($id = '', $data = []){
        if($id == '' || $data == []){
            return "No Data";
        }
        $valid = $this->validate_time($id, $data);
        if($valid == 'Valid'){
            return parent::run_update($id, $data);
        }
        return "ERROR:\n$valid";
    }

    /**
     * Executes a INSERT SQL statement
     * 
     * The responsibility of this method is to extend
     * the BaseModel's run_insert to add the specific 
     * time validation on INSERT
     * 
     * @author Tim Turnquist <tim.turnquist@gmail.com>
     * 
     * @param int $id optional, used for INSERTING a record to get the user
     * @param array $data optional, the data to be added or changed
     * @return string a message showing the rows inserted or an exception
     */
    public function run_insert($data){
        if($data == []){
            return "No Data";
        }
        $valid = $this->validate_time('', $data);
        if($valid == 'Valid'){
            return parent::run_insert($data);
        }
        return "ERROR\n$valid";
    }

    /**
     * Creates an UPDATE SQL statement
     * 
     * The responsibility of this method is to extend
     * the BaseModel's prepare_update to add the specific 
     * restraints on ONLY allowing the time_in and the
     * time_out to be updated
     * 
     * @author Tim Turnquist <tim.turnquist@gmail.com>
     * 
     * @param int $id used for UPDATING a record to get the user
     * @param array $data the data to be changed
     * @return string a valid SQL statement
     */
    function prepare_update($id = '', $data = []){
        // if there is no data to set, then return an empty string
        if($data == [] || $id == ''){
            return '';
        }
        // restrict the UPDATEable fields to only be the two time columns
        foreach (array_keys($data) as $col){
            if(!($col == 'shift.time_in' || $col == 'shift.time_out')){
                unset($data[$col]);
            }
        }
        return parent::prepare_update($id, $data);
    }

    /**
     * Creates a INSERT SQL statement
     * 
     * The responsibility of this method is to extend
     * the BaseModel's prepare_insert to add the ability to
     * pass the username from the user table (more human friendly)
     * and still get the right user_id in the shift table
     * 
     * @author Tim Turnquist <tim.turnquist@gmail.com>
     * 
     * @param array $data the data to be added
     * @return string a valid SQL statement
     */
    public function prepare_insert($data){
        if(array_key_exists('user.username', $data)){
            $u = new User();
            $user = $u->run_read("username = '{$data['user.username']}'", '');
            $data['shift.user_id'] = $user[0]['id'];
            unset($data['user.username']);
        }
        return parent::prepare_insert($data);
    }

    /**
     * Creates a DELETE SQL statement
     * 
     * The responsibility of this method is to extend
     * the BaseModel's prepare_remove to add the ability to
     * pass the username from the user table (more human friendly)
     * and still get the right user_id in the shift table
     * 
     * @author Tim Turnquist <tim.turnquist@gmail.com>
     * 
     * @param int $id optional, an easy way to filter by a record ID
     * @param array $filter optional -- a way to delete multiple records at once
     * @return string a valid SQL statement
     */
    public function prepare_remove($id, $filter){
        if(array_key_exists('user.username', $filter)){
            $u = new User();
            $user = $u->run_read("username {$filter['user.username']}", '');
            $filter['shift.user_id'] = "= {$user[0]['id']}";
            unset($filter['user.username']);
        }
        return parent::prepare_remove($id, $filter);
    }
}
?>