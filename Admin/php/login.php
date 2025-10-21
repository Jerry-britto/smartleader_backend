<?php include('../../common/config.php');
extract($_POST);
$AdminCheck = mysqli_query($conn,"SELECT * FROM `admin` WHERE `email`='$email' AND `password` = '$password'");


  if (mysqli_num_rows($AdminCheck)>0) {
      $GetAdminDetail = mysqli_fetch_assoc($AdminCheck);
     
       $_SESSION['id'] = $GetAdminDetail['id'];
       $_SESSION['email'] = $GetAdminDetail['email'];


      echo '<div class="alert alert-success"  style="">
 
      <strong > Successfully Login. Please Wait... </strong></div>';


      echo "<script>function auto_refresh(){

      // window.location='dashboard.php';
      window.location = 'show_tags.php';

       }

       var refreshId = setInterval(auto_refresh, 2000);

       </script>";
      }
       else

            {


            echo '<div class="alert alert-danger"  style="">
            <button type="button" class="close" data-dismiss="alert" style="margin-left: 5px">  x  </button>
            <strong > Please Enter Correct Username or Password   </strong></div>';
            
            }

?>
