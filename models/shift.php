<?php
include_once 'model.php';
include_once 'user.php';

class Shift extends BaseModel{
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
            $sql = $this->prepare_read_one($id);
            $res = $this->run($sql);
            $record = mysqli_fetch_all($res, MYSQLI_ASSOC);
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
        $filter = ['user.username' => "= '$user'"];
        if($id != ''){
            $filter['shift.id'] = "!= $id";
        }
        if($start_time != ''){
            $filter['shift.time_out'] = ">= '$start_time'";
        }
        if($end_time != ''){
            $filter['shift.time_in'] = "<= '$end_time'";
        }

        // get all shift records for the user within the proper timeframe
        $sql = $this->prepare_read($filter);
        $res = $this->run($sql);
        $records = mysqli_fetch_all($res, MYSQLI_ASSOC);
        $start_timestamp = strtotime($start_time);
        $end_timestamp = strtotime($end_time);
        // if we have any records, loop through them to try to find overlap
        if($records != ''){ 
            foreach($records as $rec){
                if($start_time != '' && $end_time != '' &&
                  strtotime($rec['time_in']) >= $start_timestamp &&
                  strtotime($rec['time_out']) <= $end_timestamp){
                    return "Time Conflict: Another shift is inside of this shift";
                }
                if($start_time != '' && $end_time != '' &&
                  strtotime($rec['time_in']) <= $start_timestamp &&
                  strtotime($rec['time_out']) >= $end_timestamp){
                    return "Time Conflict: This shift is inside of another shift";
                }
                if($start_time != '' && strtotime($rec['time_in']) <= $start_timestamp &&
                  strtotime($rec['time_out']) > $start_timestamp){
                    return "Time Conflict: Start time in is other shift";
                }
                if($end_time != '' && strtotime($rec['time_in']) < $end_timestamp && 
                  strtotime($rec['time_out']) >= $end_timestamp){
                    return "Time Conflict: End time is in other shift";
                }
            }
        }
        // if we have made it this far, we have a Valid statement
        return 'Valid';
    }

    public function run_update($id='', $data=[]){
        if($id == '' || $data == [] || !array_key_exists('SET', $data)){
            return "No Data";
        }
        $valid = $this->validate_time($id, $data);
        if($valid == 'Valid'){
            return parent::run_update($id, $data['SET']);
        }
        return "ERROR $valid";
    }

    public function run_insert($data){
        if($data == []){
            return "No Data";
        }
        $valid = $this->validate_time('', $data);
        if($valid == 'Valid'){
            return parent::run_insert($data);
        }
        return "ERROR $valid";
    }

    function prepare_update($id = '', $data = []){
        // if there is no data to set, then return an empty string
        if($data == [] || $id = ''){
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

    public function prepare_insert($data){
        if(array_key_exists('user.username', $data)){
            $u = new User();
            $user = $u->run_read("username = '{$data['user.username']}'", '');
            $data['shift.user_id'] = $user[0]['id'];
            unset($data['user.username']);
        }
        return parent::prepare_insert($data);
    }

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