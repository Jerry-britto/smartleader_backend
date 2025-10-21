<?php  include('../../common/config.php');
extract($_POST);

$image=$_FILES['image']['name']; 
if(!empty($_FILES['image']['name']))
move_uploaded_file($_FILES['image']['tmp_name'],'../../images/'.$image);

$file=$_FILES['file']['name']; 
if(!empty($_FILES['file']['name']))
move_uploaded_file($_FILES['file']['tmp_name'],'../../images/'.$file);

$audio_file=$_FILES['audio_file']['name']; 
if(!empty($_FILES['audio_file']['name']))
move_uploaded_file($_FILES['audio_file']['tmp_name'],'../../images/'.$audio_file);

$book_audio=$_FILES['book_audio']['name']; 
if(!empty($_FILES['book_audio']['name']))
move_uploaded_file($_FILES['book_audio']['tmp_name'],'../../images/'.$book_audio);



if(!empty($_FILES['image']['name']))
{
$query=mysqli_query($conn,"UPDATE `book` SET `tag_id`='$tag_id',`book_name`='$book_name',`writer_name`='$writer_name',`description`='$description',
`image`='$image',`book_price`='$book_price',`amazon_link`='$amazon_link',`amazon_price`='$amazon_price',
`flipkart_link`='$flipkart_link',`flipkart_price`='$flipkart_price',`e_book_price`='$e_book_price',`audio_price`='$audio_price' WHERE `id`='$ids' ");
}
else if(!empty($_FILES['file']['name']))
{
$query=mysqli_query($conn,"UPDATE `book` SET `tag_id`='$tag_id',`book_name`='$book_name',`writer_name`='$writer_name',`description`='$description',
`book_price`='$book_price',`amazon_link`='$amazon_link',`amazon_price`='$amazon_price',
`flipkart_link`='$flipkart_link',`flipkart_price`='$flipkart_price',`file`='$file',`e_book_price`='$e_book_price',`audio_price`='$audio_price' WHERE `id`='$ids' ");
}
else if(!empty($_FILES['audio_file']['name'])){
    $query=mysqli_query($conn,"UPDATE `book` SET `tag_id`='$tag_id',`book_name`='$book_name',`writer_name`='$writer_name',`description`='$description',
`book_price`='$book_price',`amazon_link`='$amazon_link',`amazon_price`='$amazon_price',
`flipkart_link`='$flipkart_link',`flipkart_price`='$flipkart_price',`e_book_price`='$e_book_price',`audio_file`='$audio_file',`audio_price`='$audio_price' WHERE `id`='$ids' ");
}
else if(!empty($_FILES['book_audio']['name'])){
    $query=mysqli_query($conn,"UPDATE `book` SET `tag_id`='$tag_id',`book_name`='$book_name',`writer_name`='$writer_name',`description`='$description',
`book_price`='$book_price',`amazon_link`='$amazon_link',`amazon_price`='$amazon_price',
`flipkart_link`='$flipkart_link',`flipkart_price`='$flipkart_price',`book_audio`='$book_audio',`e_book_price`='$e_book_price',`audio_file`='$audio_file',`audio_price`='$audio_price' WHERE `id`='$ids' ");
}
else{
    $query=mysqli_query($conn,"UPDATE `book` SET `tag_id`='$tag_id',`book_name`='$book_name',`writer_name`='$writer_name',`description`='$description',
`book_price`='$book_price',`amazon_link`='$amazon_link',`amazon_price`='$amazon_price',
`flipkart_link`='$flipkart_link',`flipkart_price`='$flipkart_price',`e_book_price`='$e_book_price',`audio_price`='$audio_price' WHERE `id`='$ids'  ");
}
if($query)
{
  echo '<div class="alert alert-success"  style="">
  <button type="button" class="close" data-dismiss="alert" style="margin-left: 5px">    </button
  <strong >Add Successfully..</strong></div>';
  echo '<script>setTimeout(function(){location.href="show_book.php"},1000)</script>';

}
else
{
  echo '<div class="alert alert-danger"  style="">
  <button type="button" class="close" data-dismiss="alert" style="margin-left: 5px">    </button>
  <strong >Failed...!!</strong></div>';
}

?>