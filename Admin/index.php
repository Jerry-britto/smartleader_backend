<?php include("../common/config.php");?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="icon" type="image/x-icon" href="../images/login/thesmart.png">
    <title>Login</title>
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!--<script src="https://cdn.jsdelivr.net/npm/tsparticles@1.18.3/dist/tsparticles.min.js"></script>-->
  
</head>


<body class="bg-light" >
    <div class="containe mt-5  mb-3  d-flex justify-content-center " >
        <div class="row   " style="box-shadow: 1px 1px 10px 10px lightblue;">
            <div class="col-md-6 p-5 ">
                <div class="border p-5">
                    <h3 class="fw-bold text-center">Login</h3>
                <form  id="form_abc1" enctype="multipart/form-data" >
                     <div class="mb-3">
                            <label for="email" class="form-label">Username or Email</label>
                            <input class="form-control rounded-pill" type="text" name="email" required="">
                     </div>
                    <div class="mb-3">
                         <label for="password" class="form-label">Password</label>
                         <input class="form-control rounded-pill" type="password" name="password" required="">
                    </div>
                  <a href="#">
                   <input type="Submit" class="btn btn-primary mt-3" name="submit" value="Submit">
                  </a>
    
                 
                </form>
                </div>
            </div>

            
            <div class="col-md-6 p-5 ">

               <div class="p-5 " style="display: flex; flex-direction: column; align-items: center;}"> 
               <img src="/administrator/images/login/thesmart.png" class="img-fluid" style="max-width: 80px;">
                <h1 class=text-center>Welcome to the Smart Leader</h1>
               </div>
                <div id="form_abc1_data"></div>
               
            </div>
        </div>
   
    </div>


</body>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</html>

  <script>
        $(document).ready(function (abc1) {
        $("#form_abc1").on('submit',(function(abc1) {
        $("#form_abc1_data").html('');
          abc1.preventDefault();
          $.ajax({
           url: "php/login.php",
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