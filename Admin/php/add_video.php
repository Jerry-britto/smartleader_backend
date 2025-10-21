<?php
require '../../vendor/autoload.php';
include('../../common/config.php');

// Extract POST values
extract($_POST);

// File upload paths
$video = '';
$image = '';

if (!empty($_FILES['video']['tmp_name'])) {
    $video = basename($_FILES['video']['name']);
    move_uploaded_file($_FILES['video']['tmp_name'], '../../videos/' . $video);
}

if (!empty($_FILES['image']['tmp_name'])) {
    $image = basename($_FILES['image']['name']);
    move_uploaded_file($_FILES['image']['tmp_name'], '../../images/' . $image);
}

// Sanitize
$video_name = mysqli_real_escape_string($conn, $video_name);
$video_link = mysqli_real_escape_string($conn, $video_link);
$time = mysqli_real_escape_string($conn, $time);

// Insert into DB
$query = "INSERT INTO `add_video` 
          (`language_key`,`tag_id`, `video_name`, `video`, `image`, `video_link`, `time`) 
          VALUES ('$language_key','$tag_id' ,'$video_name', '$video', '$image', '$video_link', '$time')";

if (mysqli_query($conn, $query)) {
    echo '<div class="alert alert-success"><strong>Added Successfully.</strong></div>';
    echo '<script>setTimeout(function(){location.href="show_video.php"},1000)</script>';
} else {
    echo '<div class="alert alert-danger"><strong>Database Error.</strong></div>';
}
?>
