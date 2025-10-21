<?php  
include('../../common/config.php');
extract($_POST);

// Handle video upload
$video = $_FILES['video']['name']; 
if (!empty($video)) {
    move_uploaded_file($_FILES['video']['tmp_name'], '../../images/' . $video);
}

// Handle image upload
$image = $_FILES['image']['name']; 
if (!empty($image)) {
    move_uploaded_file($_FILES['image']['tmp_name'], '../../images/' . $image);
}

// Start building the SQL UPDATE query
$updateFields = [
    "name = '$name'",
    "video_link = '$video_link'",
    "language_key = '$language_key'",
    "tag_id = '$tag_id'"
];

if (!empty($video)) {
    $updateFields[] = "video = '$video'";
}
if (!empty($image)) {
    $updateFields[] = "image = '$image'";
}

// Final query
$sql = "UPDATE `add_free_videos` SET " . implode(", ", $updateFields) . " WHERE `id` = '$ids'";
$query = mysqli_query($conn, $sql);

// Output result
if ($query) {
    echo '<div class="alert alert-success">
        <strong>Updated Successfully.</strong>
    </div>';
    echo '<script>setTimeout(function(){location.href="show_free_video_audio.php"}, 1000)</script>';
} else {
    echo '<div class="alert alert-danger">
        <strong>Update Failed!</strong>
    </div>';
}
?>
