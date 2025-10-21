<?php include "config.php";
$response = array();
$id = $_POST['id'];
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

if(empty($id)){
    $response['status']="false";
    $response['massage']="places fill id";
}else{

    $sql = "UPDATE `connection_event` SET `user_id`='$user_id',`name`='$name',`mobile`='$mobile',`occupation`='$occupation',`time`='$time',`date`='$date',`remind`='$remind',`meeting_required`='$meeting_required',`meeting_count`='$meeting_count',`meeting_happen`='$meeting_happen',`notes`='$notes',`connection_id`='$connection_id',`title`='$title',`description`='$description',`meeting_type`='$meeting_type',`meeting_place`='$meeting_place',`birthday_parson`='$birthday_parson',`place`='$place',`added_type`='$added_type',`list_type`='$list_type' WHERE id = '$id'";
    $qur = mysqli_query($conn, $sql);

    if($qur){
        $response['status']="true";
        $response['massage']="data update successfully";
    }

}

echo json_encode($response);

?>