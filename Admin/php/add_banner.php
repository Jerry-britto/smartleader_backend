<?php  include('../../common/config.php');
extract($_POST);
// $strtotime=strtotime('now');
$image=$_FILES['image']['name']; 
if(!empty($_FILES['image']['name']))
move_uploaded_file($_FILES['image']['tmp_name'],'../../images/'.$image);
$query=mysqli_query($conn,"INSERT INTO `banner`(`title`, `description`, `image`) VALUES ('$title','$description','$image')");
if($query)

{
// echo $idss;
  echo '<div class="alert alert-success"  style="">
  <button type="button" class="close" data-dismiss="alert" style="margin-left: 5px">    </button>
  <strong >Add Successfully..</strong></div>';
  echo '<script>setTimeout(function(){location.href="show_banner.php"},1000)</script>';
}
else
{
  echo '<div class="alert alert-danger"  style="">
  <button type="button" class="close" data-dismiss="alert" style="margin-left: 5px">    </button>
  <strong >Failed...!!</strong></div>';

}
