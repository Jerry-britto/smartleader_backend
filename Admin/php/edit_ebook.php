<?php  include('../../common/config.php');
extract($_POST);

$file=$_FILES['file']['name']; 
if(!empty($_FILES['file']['name']))
move_uploaded_file($_FILES['file']['tmp_name'],'../../images/'.$file);

$audio_file=$_FILES['audio_file']['name']; 
if(!empty($_FILES['audio_file']['name']))
move_uploaded_file($_FILES['audio_file']['tmp_name'],'../../images/'.$audio_file);

if(!empty($_FILES['file']['name']))
{
$query=mysqli_query($conn,"UPDATE `ebook` SET `file`='$file' WHERE `id`='$ids' ");
}
else if(!empty($_FILES['audio_file']['name'])){
    $query=mysqli_query($conn,"UPDATE `ebook` SET `book_id`='$book_id',`audio_file`='$audio_file' WHERE `id`='$ids' ");
}
else{
    $query=mysqli_query($conn,"UPDATE `ebook` SET `book_id`='$book_id' WHERE `id`='$ids' ");
}
if($query)
{
  echo '<div class="alert alert-success"  style="">
  <button type="button" class="close" data-dismiss="alert" style="margin-left: 5px">    </button
  <strong >Add Successfully..</strong></div>';
  echo '<script>setTimeout(function(){location.href="show_ebook.php"},1000)</script>';

}
else
{
  echo '<div class="alert alert-danger"  style="">
  <button type="button" class="close" data-dismiss="alert" style="margin-left: 5px">    </button>
  <strong >Failed...!!</strong></div>';
}

?>