<?php include "config.php";
$response = array();
$user_id = $_POST['user_id'];
$name = $_POST['name'];
$mobile = $_POST['mobile'];
$occupation = $_POST['occupation'];
$time = $_POST['time'];
$date = $_POST['date'];
$remind = $_POST['remind'];
$meeting_required = $_POST['meeting_required'];
$meeting_count = $_POST['meeting_count'];
$meeting_happen = $_POST['meeting_happen'];
$notes = $_POST['notes'];
$connection_id = $_POST['connection_id'];
$title = $_POST['title'];
$description = $_POST['description'];
$meeting_type = $_POST['meeting_type'];
$meeting_place = $_POST['meeting_place'];
$birthday_parson = $_POST['birthday_parson'];
$place = $_POST['place'];
$added_type = $_POST['added_type'];
$list_type = $_POST['list_type'];


if(empty($user_id)){
$response['status'] = "false";
$response['massage'] = "plz fill user_id.";
    
}else{
    $sql = "INSERT INTO `connection_event`(`user_id`, `name`, `mobile`, `occupation`, `time`, `date`, `remind`, `meeting_required`, `meeting_count`, `meeting_happen`, `notes`, `connection_id`, `title`, `description`, `meeting_type`, `meeting_place`, `birthday_parson`, `place`, `added_type`, `list_type`) VALUES ('$user_id','$name ','$mobile','$occupation','$time','$date','$remind','$meeting_required','$meeting_count','$meeting_happen','$notes','$connection_id','$title','$description','$meeting_type','$meeting_place','$birthday_parson','$place','$added_type','$list_type')";
    $live = mysqli_query($conn, $sql);
    if($live){
        $response["status"] = "true";
        $response["massage"] = "data add successfully";
    }
}

echo json_encode($response);
?>