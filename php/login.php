<?php
// FORCE CACHE CLEAR
opcache_reset();
clearstatcache();

// Fix session path for cPanel
ini_set('session.save_path', '/tmp');
session_start();
// Fix config path - corrected from ../../ to ../
include('../common/config.php');

extract($_POST);

$AdminCheck = mysqli_query($conn, "SELECT * FROM `admin` WHERE `email`='$email' AND `password` = '$password'");

if (mysqli_num_rows($AdminCheck) > 0) {
    $GetAdminDetail = mysqli_fetch_assoc($AdminCheck);
    
    $_SESSION['id'] = $GetAdminDetail['id'];
    $_SESSION['email'] = $GetAdminDetail['email'];

    echo '<div class="alert alert-success">
    <strong>Successfully Login. Please Wait...</strong></div>';

    echo "<script>
    setTimeout(function(){
        window.location.href = '/administrator/index.php';  // Full absolute path

    }, 2000);
   
    </script>";
} else {
    echo '<div class="alert alert-danger">
    <strong>Please Enter Correct Username or Password</strong></div>';
}
?>
