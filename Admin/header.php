<?php 
include('../common/config.php');
if (empty($_SESSION['id'])) {
	echo '<script>setTimeout(function(){location.href="index.php"},1000)</script>';
 }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/x-icon" href="../images/login/thesmart.png">
  <title>Smartleader Admin </title>
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- IonIcons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css"> 
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <style>
    [class*=sidebar-dark-] {
    background-color: #131c49 !important;
}
  </style>

<script src="plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  
<!-- AdminLTE App -->
<script src="dist/js/adminlte.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="dist/js/demo.js"></script>
<!-- Page specific script -->
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <!--<a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>-->
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="dashboard.php" class="nav-link">Admin </a>
      </li>
      <!-- <li class="nav-item d-none d-sm-inline-block">
        <a href="#" class="nav-link">Contact</a>
      </li> -->
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- Navbar Search -->
      <li class="nav-item">
        <a class="nav-link" data-widget="navbar-search" href="#" role="button">
          <!--<i class="fas fa-search"></i>-->
        </a>
        <div class="navbar-search-block">
          <form class="form-inline">
            <div class="input-group input-group-sm">
              <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
              <div class="input-group-append">
                <button class="btn btn-navbar" type="submit">
                  <i class="fas fa-search"></i>
                </button>
                <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>
          </form>
        </div>
      </li>

      <!-- Messages Dropdown Menu -->
     
      <!-- Notifications Dropdown Menu -->
      <!-- <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-bell"></i>
          <span class="badge badge-warning navbar-badge">1</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <span class="dropdown-item dropdown-header">1 Notifications</span>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-envelope mr-2"></i> 1 new messages
            <span class="float-right text-muted text-sm">3 mins</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-users mr-2"></i> 8 friend requests
            <span class="float-right text-muted text-sm">12 hours</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-file mr-2"></i> 1 new reports
            <span class="float-right text-muted text-sm">2 days</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
        </div>
      </li> -->
      <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
          <i class="fas fa-th-large"></i>
        </a>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="#" class="brand-link">
      <img src="../images/login/thesmart.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3">
      <span class="brand-text font-weight-light">Smartleader</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">

      <!-- SidebarSearch Form -->
      <div class="form-inline">
        <div class="input-group" data-widget="sidebar-search">
          <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
          <div class="input-group-append">
            <button class="btn btn-sidebar">
              <i class="fas fa-search fa-fw"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
         
          <!-- <li class="nav-item menu-open">
            <a href="dashboard.php" class="nav-link ">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                Dashboard
              
              </p>
            </a>
          </li>
            <li class="nav-item">
            <a href="show_banner.php" class="nav-link">
              <i class="nav-icon far fa-image"></i>
              <p>
                Banner
              </p>
            </a>
          </li> -->
          <li class="nav-item">
            <a href="show_tags.php" class="nav-link">
            <i class="nav-icon fas fa-folder"></i>
              <p>
                  Tags
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="show_book.php" class="nav-link">
              <i class="nav-icon fas fa-book"></i>
              <p>
                Books
              </p>
            </a>
          </li>
          <!--<li class="nav-item">-->
          <!--  <a href="show_ebook.php" class="nav-link">-->
          <!--    <i class="nav-icon fas fa-bookmark"></i>-->
          <!--    <p>-->
          <!--      E-Books-->
          <!--    </p>-->
          <!--  </a>-->
          <!--</li>-->
             <li class="nav-item">
            <a href="show_free_video_audio.php" class="nav-link">
              <i class="nav-icon fas fa-video"></i>
              <p>
                Free Video Audio
              
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="show_video.php" class="nav-link">
              <i class="nav-icon fas fa-video"></i>
              <p>
                Videos
              
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="about.php" class="nav-link">
              <i class="nav-icon fas fa-file"></i>
              <p>About</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="terms_condition.php" class="nav-link">
              <i class="nav-icon fas fa-columns"></i>
              <p>
              Terms Condition
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="privacy_policy.php" class="nav-link">
              <i class="nav-icon fas fa-lock"></i>
              <p>
                  Privacy Policy
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="show_contact.php" class="nav-link">
              <i class="nav-icon fas fa-envelope"></i>
              <p>
                 Contact Us
              </p>
            </a>
          </li>
            
          <!-- <li class="nav-item">
            <a href="show_folder.php" class="nav-link">
            <i class="nav-icon fas fa-folder"></i>
              <p>
                  Folders
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="show_note.php" class="nav-link">
            <i class="nav-icon fas fa-edit"></i>
              <p>
                 Notes
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="show_meeting.php" class="nav-link">
            <i class="nav-icon fas fa-handshake"></i>
              <p>
                Meeting
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="show_task.php" class="nav-link">
            <i class="nav-icon fas fa-tasks"></i>
              <p>
                Task
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="show_connection.php" class="nav-link">
            <i class="nav-icon fas fa-link"></i>
              <p>
                Connection
              </p>
            </a>
          </li> -->
          <li class="nav-item">
            <a href="show_user.php" class="nav-link">
            <i class="nav-icon fas fa-user"></i>
              <p>
               User List
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="show_plans.php" class="nav-link">
             <i class="nav-icon fas fa-clipboard-list"></i>
              <p>
               Plans
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="logout.php" class="nav-link">
            <i class="nav-icon fas fa-power-off"></i>
              <p>
                LogOut
              </p>
            </a>
          </li>
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>