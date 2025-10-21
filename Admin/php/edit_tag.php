<?php  include('../../common/config.php');
extract($_POST);

$query=mysqli_query($conn,"UPDATE `tags` SET `tags`='$tags' WHERE `id`='$ids' ");

if($query)
{
  echo '<div class="alert alert-success"  style="">
  <button type="button" class="close" data-dismiss="alert" style="margin-left: 5px">    </button
  <strong >Detail Add Successfully..</strong></div>';
  echo '<script>setTimeout(function(){location.href="show_tags.php"},1000)</script>';

}
else
{
  echo '<div class="alert alert-danger"  style="">
  <button type="button" class="close" data-dismiss="alert" style="margin-left: 5px">    </button>
  <strong >Failed...!!</strong></div>';
}

?>