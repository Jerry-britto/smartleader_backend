<?php 
include "config.php";
$response = array();

if(isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    $connection_id = isset($_POST['connection_id']) ? $_POST['connection_id'] : '';  // Check if connection_id is set

    if(empty($user_id)) {
        $response['status'] = "false";
        $response['message'] = "Please fill user_id.";
    } else {
        // Construct SQL query based on the presence of connection_id
        if (!empty($connection_id)) {
            $sql = "SELECT * FROM `connection_event` WHERE user_id = '$user_id' AND connection_id = '$connection_id'";
        } else {
            $sql = "SELECT * FROM `connection_event` WHERE user_id = '$user_id'";
        }

        $result = mysqli_query($conn, $sql);

        if(mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
            $response["status"] = "true";
            $response['message'] = "Data retrieved successfully.";
            $response["result"] = $data;
        } else {
            $response['status'] = "false";
            $response['message'] = "No data found.";
        }
    }
} else {
    $response['status'] = "false";
    $response['message'] = "Invalid request.";
}

echo json_encode($response);
?>
