<?php  include('../../common/config.php');
extract($_POST);
$query=mysqli_query($conn,"INSERT INTO `tags`(`tags`) VALUES ('$tags')");
if($query)

{
// echo $idss;
  echo '<div class="alert alert-success"  style="">
  <button type="button" class="close" data-dismiss="alert" style="margin-left: 5px">    </button>
  <strong >Add Successfully..</strong></div>';
  echo '<script>setTimeout(function(){location.href="show_tags.php"},1000)</script>';
}
else
{
  echo '<div class="alert alert-danger"  style="">
  <button type="button" class="close" data-dismiss="alert" style="margin-left: 5px">    </button>
  <strong >Failed...!!</strong></div>';

}
