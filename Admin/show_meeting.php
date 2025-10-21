<?php include("header.php"); 
if (isset($_GET['delete_id'])) {
    $sqlDELETE = mysqli_query($conn, "DELETE FROM `add_meeting` WHERE `id`='" . $_GET['delete_id'] . "'");
    echo "<script>alert('deleted ..!!');window.location.href='show_meeting.php';</script>";
}
?>
<!-- DataTables -->
<link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
  
<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Meeting Users</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active">Meeting Users</li>
                        </ol>
                    </div>
                </div>
            </div>
            <!-- /.container-fluid -->
        </section>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Show Meeting User</h3>
            </div>
            <div class="card-body">
                <table id="example1" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Sr.No.</th>
                            <th>User Name</th>
                            <th>Select Type</th>
                            <th>Leader Name</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Reminder</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $n=1;
                        $query = mysqli_query($conn, "SELECT * FROM `add_meeting`");
                        while ($fetch = mysqli_fetch_assoc($query)) {
                   $user_name=mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `signup`  WHERE `id`='".$fetch['user_id']."'"));
                        ?>
                            <tr> 
                                <td><?php echo $n++; ?></td>
                                <td><?php echo $user_name['username']; ?></td>
                                <td><?php echo $fetch['select_type']; ?></td>
                                <td><?php echo $fetch['leader_name']; ?></td>
                                <td><?php echo $fetch['date']; ?></td>
                                <td><?php echo $fetch['time']; ?></td>
                                <td><?php echo $fetch['reminder']; ?></td>
                                <td>
                                    <a onclick="return confirm ('Are you sure delete?')" href="?delete_id=<?php echo $fetch['id'] ?>" class="btn btn-danger text-white "><i class="ion-trash-a"></i></a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    $(function() {
        $("#example1").DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
            "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
        $('#example2').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": false,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
        });
    });
</script>
<?php include("footer.php"); ?>
<!-- DataTables  & Plugins -->
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="plugins/jszip/jszip.min.js"></script>
<script src="plugins/pdfmake/pdfmake.min.js"></script>
<script src="plugins/pdfmake/vfs_fonts.js"></script>
<script src="plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="plugins/datatables-buttons/js/buttons.colVis.min.js"></script>