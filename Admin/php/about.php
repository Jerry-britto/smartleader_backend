<?php include("../../common/config.php");
extract($_POST);
$d = addslashes($description);
$query=mysqli_query($conn,"UPDATE `about` SET `description`='$d' WHERE `id`='1'");
if($query)
{

    echo '<div class="alert alert-success"  style="">
    <button type="button" class="close" data-dismiss="alert" style="margin-left: 5px">  x  </button>
    <strong > Detail Added Successful..</strong></div>';
    echo '<script>setTimeout(function(){location.href="about.php"},1000)</script>';

}
else
{

    echo '<div class="alert alert-danger"  style="">
    <button type="button" class="close" data-dismiss="alert" style="margin-left: 5px">  x  </button>
    <strong >Failed...!!</strong></div>';

 }
?>