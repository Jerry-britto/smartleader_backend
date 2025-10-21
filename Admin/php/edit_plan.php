<?php  
include('../../common/config.php');
extract($_POST);

$query = mysqli_query($conn,"UPDATE `plans` 
SET `name`='$name',
    `amount`='$amount',
    `currency`='$currency',
    `interval`='$interval',
    `period`='$period',
    `status`='$status'
WHERE `id`='$id' ");

if($query) {
  echo '<div class="alert alert-success" style="">
  <button type="button" class="close" data-dismiss="alert" style="margin-left: 5px"></button>
  <strong>Plan Updated Successfully..</strong></div>';
  echo '<script>setTimeout(function(){location.href="show_plans.php"},1000)</script>';
} else {
  echo '<div class="alert alert-danger" style="">
  <button type="button" class="close" data-dismiss="alert" style="margin-left: 5px"></button>
  <strong>Failed...!!</strong></div>';
}
?>
