<?php include("../../common/config.php");
extract($_POST);
$d = addslashes($description);
$query=mysqli_query($conn," UPDATE `privacy_policy` SET `description`='$d' WHERE `id`='1'");
// echo "UPDATE `privacy_policy` SET `description`='$description' WHERE `id`='1'";
die();
if($query)
{

    echo '<div class="alert alert-success"  style="">
    <button type="button" class="close" data-dismiss="alert" style="margin-left: 5px">  x  </button>
    <strong > Detail Added Successful..</strong></div>';
    echo '<script>setTimeout(function(){location.href="privacy_policy.php"},1000)</script>';

}
else
{

    echo '<div class="alert alert-danger"  style="">
    <button type="button" class="close" data-dismiss="alert" style="margin-left: 5px">  x  </button>
    <strong >Failed...!!</strong></div>';

 }
?>