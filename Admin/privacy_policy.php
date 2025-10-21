<?php
   include("header.php");
   $fetch_detail=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM `privacy_policy` WHERE `id`='1' "));
   
   ?>
<script src="https://cdn.ckeditor.com/4.20.0/standard/ckeditor.js"></script>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
   <!-- Content Header (Page header) -->
   <section class="content-header">
      <div class="container-fluid">
         <div class="row mb-2">
            <div class="col-sm-6">
               <h1>Privacy Policy </h1>
            </div>
            <div class="col-sm-6">
               <ol class="breadcrumb float-sm-right">
                  <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                  <li class="breadcrumb-item active">Privacy Policy</li>
               </ol>
            </div>
         </div>
      </div>
      <!-- /.container-fluid -->
   </section>
   <!-- Main content -->
   <section class="content">
      <form  id="form_abc1" enctype="multipart/form-data" >
         <!-- Default box -->
         <div class="card">
            <div class="card-body row">
               <div class="col-12">
                  <div class="form-group">
                     <label for="exampleInputEmail1">Description</label>
                     <textarea type="text" name="description"  required class="form-control" placeholder="Description" id="exampleInputEmail1">
                     <?php echo $fetch_detail['description']; ?>
                     </textarea>
                  </div>
               </div>
               <div class="form-group">
                  <input type="submit" class="btn btn-primary" name="submit" value="Submit">
               </div>
               <br>
               <div id="form_abc1_data"></div>
            </div>
         </div>
</form>
</section>
<!-- /.content -->
</div>
<!-- /.content-wrapper -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js
   "></script>
   <?php
   include("footer.php");
   ?>
<script src="https://cdn.tiny.cloud/1/hkmy2zdgr9txiy1puyb8j0gp75oamkk5wkxya0ipm0pewy3s/tinymce/4/tinymce.min.js" referrerpolicy="origin"></script>
<script>
   tinymce.init({
   selector: 'textarea',
   height: 100,
   theme: 'modern',
   plugins: 'print preview fullpage powerpaste searchreplace autolink directionality advcode visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists textcolor wordcount tinymcespellchecker a11ychecker imagetools mediaembed  linkchecker contextmenu colorpicker textpattern help',
   toolbar1: 'formatselect | bold italic strikethrough forecolor backcolor | link | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent  | removeformat',
   image_advtab: true,
   templates: [
     { title: 'Test template 1', content: 'Test 1' },
     { title: 'Test template 2', content: 'Test 2' }
   ],
   content_css: [
     '//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
     '//www.tinymce.com/css/codepen.min.css'
   ]
   });
   
</script>
<script>
   $(document).ready(function (abc1) {
    $("#form_abc1").on('submit',(function(abc1) {
   $("#form_abc1_data").html('');
     abc1.preventDefault();
     $.ajax({
      url: "php/privacy_policy.php",
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
