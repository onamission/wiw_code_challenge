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
        if(!array_key_exists('SET', $data)){
            return 'No data to set';
        }
        // if we have no user or record to get a user from, then return warning
        if(!array_key_exists('user.username', $data['SET']) && $id == ''){
            return "A user is needed for this operation";
        }
        // if we have no times to post, then return a warning
        if(!array_key_exists('shift.time_in', $data['SET']) &&
          !array_key_exists('shift.time_out', $data['SET'])){
            return 'At least one time is needed for this operation';
        }
        // determine the start and end times based on the SET data
        $start_time = array_key_exists('shift.time_in', $data['SET']) ? 
          $data['SET']['shift.time_in'] :
          '';
        $end_time = array_key_exists('shift.time_out', $data['SET']) ? 
          $data['SET']['shift.time_out'] :
          '';
        // determine the user from the SET data or the record passed
        $user = '';
        if($id != ''){
            $sql = $this->prepare_read_one($id);
            $res = $this->run($sql);
            $record = mysqli_fetch_all($res, MYSQLI_ASSOC);
            print_r($record);
            $user = $record[0]['username'];
            if($start_time == ''){
                $start_time = $record[0]['time_in'];
            }
            if($end_time == ''){
                $end_time = $record[0]['time_out'];
            }
        }else{
            $user = $data['SET']['user.username'];
            if($start_time == ''){
                return "No Start Time";
            }
            if($end_time == ''){
                return "No End Time";
            }
        }
        // create the WHERE statment to get a list of records
        $where_data = ['WHERE' => ['user.username' => "= '$user'"]];
        if($id != ''){
            $where_data['WHERE']['shift.id'] = "!= $id";
        }
        if($start_time != ''){
            $where_data['WHERE']['shift.time_out'] = ">= '$start_time'";
        }
        if($end_time != ''){
            $where_data['WHERE']['shift.time_in'] = "<= '$end_time'";
        }

        // get all shift records for the user within the proper timeframe
        $sql = $this->prepare_read($where_data);
        $res = $this->run($sql);
        $records = mysqli_fetch_all($res, MYSQLI_ASSOC);
        // print_r($records);
        $start_timestamp = strtotime($start_time);
        $end_timestamp = strtotime($end_time);
        // if we have any records, loop through them to try to find overlap
        if($records != ''){ 
            foreach($records as $rec){
                print_r($rec);
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

    public function run_update($id=[], $data=[]){
        $valid = $this->validate_time($id, $data);
        if($valid == 'Valid'){
            parent::run_update($id, $data);
        }else{
            return "ERROR $valid";
        }
    }

    public function run_insert($data){
        $valid = $this->validate_time('', $data);
        if($valid == 'Valid'){
            parent::run_insert($data);
        }else{
            return "ERROR $valid";
        }
    }

    public function prepare_insert($data){
        $return_string = '';
        if(array_key_exists('user.username', $data)){
            $u = new User();
            $sql = $u->prepare_read(['WHERE' => ["username" => "= '{$data['user.username']}'"]]);
            echo "$sql\n";
            $res = $u->run($sql);
            $user = mysqli_fetch_all($res, MYSQLI_ASSOC);
            print_r($user);
            $data['shift.user_id'] = $user[0]['id'];
            print_r($data);
            unset($data['user.username']);
            print_r($data);
            $return_string = parent::prepare_insert($data);
        }
        return $return_string;
    }
}
?>