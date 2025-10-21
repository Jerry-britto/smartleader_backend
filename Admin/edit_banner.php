<?php
include("header.php");
$fetch=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM `banner` WHERE `id`='".$_GET['edit']."' "));
?>

 <!-- Content Wrapper. Contains page content -->
 <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Banner</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Banner</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
    <form  id="form_abc1" enctype="multipart/form-data" >
    <input type="hidden" name="ids" value="<?php echo $_GET['edit']; ?>">
      <!-- Default box -->
      <div class="card">
        <div class="card-body row">
          <div class="col-12">
            <div class="form-group">
              <label for="inputName">Title</label>
              <input type="Text" id="inputName" name="title" value="<?php echo $fetch['title'];?>" class="form-control" />
          
          
            <div class="form-group">
              <label for="inputName">Description</label>
              <textarea type="text" id="inputName" name="description" class="form-control"><?php echo $fetch['description'];?></textarea>
            </div>
          
            <div class="form-group">
              <label for="inputName">Banner</label>
              <input type="file" id="inputName" name="image" class="form-control" />
              <br>
              <img src="../images/<?php echo $fetch['image'];?>" width="80px" alt="" srcset="">
            </div>
           </div>
            <div class="form-group">
              <input type="submit" class="btn btn-primary" name="submit" value="Submit">
            </div>
          </div>
        </div>
      </div>
      <div id="form_abc1_data"></div>
    </form>
     
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js
"></script>
<script>
$(document).ready(function (abc1) {
 $("#form_abc1").on('submit',(function(abc1) {
$("#form_abc1_data").html('');
  abc1.preventDefault();
  $.ajax({
   url: "php/edit_banner.php",
   type: "POST",
   data:  new FormData(this),
   contentType: false,
         cache: false,
   processData:false,
   success: function(data){     
   $("#form_abc1_data").html(data);
     	$("#messid").hide();
    	$("#messidmob").hide();
      },   
     error: function(){}          
    });
 }));
});
</script>
<?php
include("footer.php");
?>