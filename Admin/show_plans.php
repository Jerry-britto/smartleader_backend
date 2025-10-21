<?php include("header.php"); 
if (isset($_GET['delete_id'])) {
    $sqlDELETE = mysqli_query($conn, "DELETE FROM `plans` WHERE `id`='" . $_GET['delete_id'] . "'");
    echo "<script>alert('Plan deleted successfully!');window.location.href='show_plans.php';</script>";
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
                        <h1>Show Plans</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active">Show Plans</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    Plans List 
                    <a href="add_plan.php" class="btn btn-info float-end">Add Plan</a>
                </h3>
            </div>
            <div class="card-body">
                <table id="example1" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Sr.No.</th>
                            <th>Name</th>
                            <th>Amount</th>
                            <th>Currency</th>
                            <th>Interval</th>
                            <th>Period</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $i = 1;
                        $query = mysqli_query($conn,"SELECT * FROM `plans` ORDER BY `id` DESC");
                        while ($fetch = mysqli_fetch_assoc($query)) {
                    ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo $fetch['name']; ?></td>
                            <td><?php echo $fetch['amount']; ?></td>
                            <td><?php echo $fetch['currency']; ?></td>
                            <td><?php echo $fetch['interval']; ?></td>
                            <td><?php echo $fetch['period']; ?></td>
                            <td>
                                <?php if($fetch['status'] == 'active'){ ?>
                                    <span class="badge badge-success">Active</span>
                                <?php } else { ?>
                                    <span class="badge badge-danger">Inactive</span>
                                <?php } ?>
                            </td>
                           
                            <td>
                                <a href="edit_plan.php?edit=<?php echo $fetch['id']?>" class="btn btn-dark text-white"><i class="ion-compose"></i></a>
                                <a onclick="return confirm('Are you sure you want to delete?')" href="?delete_id=<?php echo $fetch['id']?>" class="btn btn-danger text-white"><i class="ion-trash-a"></i></a>
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
