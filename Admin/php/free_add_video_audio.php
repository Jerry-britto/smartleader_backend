<?php
include('../../common/config.php');

// Extract POST values
extract($_POST);

// Handle file uploads directly (no API compression)
$audio = '';
$image = '';

if (!empty($_FILES['audio']['tmp_name'])) {
    $audioName = time() . '_' . basename($_FILES['audio']['name']);
    $audioPath = '../../images/' . $audioName;
    if (move_uploaded_file($_FILES['audio']['tmp_name'], $audioPath)) {
        $audio = $audioName;
    }
}

if (!empty($_FILES['image']['tmp_name'])) {
    $imageName = time() . '_' . basename($_FILES['image']['name']);
    $imagePath = '../../images/' . $imageName;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
        $image = $imageName;
    }
}

// Sanitize input
$name = mysqli_real_escape_string($conn, $name);
$video_link = mysqli_real_escape_string($conn, $video_link);
$time = mysqli_real_escape_string($conn, $time);

// Insert into DB
$isFree = isset($_POST['isFree']) ? 1 : 0;

$query = "INSERT INTO `add_free_videos` (`language_key`,`tag_id`, `name`, `audio`, `image`, `video_link`, `time`, `isFree`) 
          VALUES ('$language_key','$tag_id' ,'$name', '$audio', '$image', '$video_link', '$time', '$isFree')";

if (mysqli_query($conn, $query)) {
    echo '<div class="alert alert-success"><strong>Added Successfully.</strong></div>';
    echo '<script>setTimeout(function(){location.href="show_free_video_audio.php"},1000)</script>';
} else {
    echo '<div class="alert alert-danger"><strong>Database Error: ' . mysqli_error($conn) . '</strong></div>';
}
?>
