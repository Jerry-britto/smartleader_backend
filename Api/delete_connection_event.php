<?php include "config.php";
$response = array();
$id = $_POST['id'];


if(empty($id)){
    $response['status']="false";
    $response['massage']="places fill id";
}else{

    $sql = "DELETE FROM `connection_event` WHERE id = '$id'";
    $qur = mysqli_query($conn, $sql);

    if($qur){
        $response['status']="true";
        $response['massage']="data dalete successfully";
    }

}

echo json_encode($response);

?>