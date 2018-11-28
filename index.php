<?php
// Parse the request
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'].'/'));
$data = json_decode(file_get_contents('php://input'),true);

// Include models
$dir = "./models";
$model_list = [
    'shift', 
    'user'
];
foreach($model_list as $model){
    require_once "$dir/$model.php";
}
 
// Get the model
$requested_model = preg_replace('/_/i',' ',$request[1]);
$requested_model = ucfirst($requested_model);
$requested_model = preg_replace('/[^a-z0-9]+/i','',$requested_model);
$model = new $requested_model;

// Get the ID if there is one
$id = $request[2];
$filter = $_GET['filter'];
$sort = $_GET['sort'];

// Call the proper method based on HTTP method
switch ($method) {
  case 'GET':
    if($id){
        echo json_encode($model->run_read_one($id, $sort));
    }
    else{
        echo json_encode($model->run_read($filter, $sort));
    }
    break;
  case 'PUT':
    echo $model->run_update($id, $data);
    break;
  case 'POST':
    echo $model->run_insert($data);
    break;
  case 'DELETE':
    echo $model->run_delete($id, $data);
    break;
}
