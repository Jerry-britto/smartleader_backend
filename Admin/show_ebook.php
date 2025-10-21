<?php include("header.php"); 
if (isset($_GET['delete_id'])) {
    $sqlDELETE = mysqli_query($conn, "DELETE FROM `ebook` WHERE `id`='" . $_GET['delete_id'] . "'");
    echo "<script>alert('deleted ..!!');window.location.href='show_ebook.php';</script>";
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
                        <h1>Show E-Books</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active">Show E-Books</li>
                        </ol>
                    </div>
                </div>
            </div>
            <!-- /.container-fluid -->
        </section>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Show E-Books <a href="add_ebook.php" class="btn btn-info float-end">Add E-Book</a></h3>
            </div>
            <div class="card-body">
                <table id="example1" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Sr.No.</th>
                            <th>Book Name</th>
                            <th>E-Book File </th>
                            <th> E-Book Audio</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $n=1;
                        $query = mysqli_query($conn, "SELECT * FROM `ebook` ORDER BY `id` DESC");
                        while ($fetch = mysqli_fetch_assoc($query)) {
                       $book=mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `book` WHERE `id`='".$fetch['book_id']."'"));
                        ?>
                            <tr> 
                                <td><?php echo $n++; ?></td>
                                <td><?php echo $book['book_name']; ?></td>
                                <td><?php echo $fetch['file']; ?></td>
                                <td><audio controls>
                                    <source src="../images/<?php echo $fetch['audio_file'];?>" type="audio/ogg">
                                    </audio>
                                </td>
                                <td>
                                    <a href="edit_ebook.php?edit=<?php echo $fetch['id'] ?>" class="btn btn-dark text-white "><i class="ion-compose"></i></a>
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