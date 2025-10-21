<?php
include("header.php");
$fetch=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM `ebook` WHERE `id`='".$_GET['edit']."' "));
$book=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM `book` WHERE `id`='".$fetch['book_id']."' "));
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>E-Book</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active"> E-Book</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <form id="form_abc1" enctype="multipart/form-data">
        <input type="hidden" name="ids" value="<?php echo $_GET['edit']; ?>">
            <!-- Default box -->
            <div class="card">
                <div class="card-body row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="inputName"> E-Book Name</label>
                                <select class="form-control" id="exampleFormControlSelect1" name="book_id">
                                <option><?php  echo $book['book_name']; ?></option>
                         <?php 
                         $sql=mysqli_query($conn,"select * from `book`");
                           while($fetch=mysqli_fetch_assoc($sql))
                           {
                               ?>
                           <option value="<?php echo $fetch['id']; ?>"><?php  echo $fetch['book_name']; ?></option>
                    
                           <?php } ?>
                         </select>
                 
                            </div>
                        </div>
                            <?php
                            $fetch=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM `ebook` WHERE `id`='".$_GET['edit']."' "));
                            ?>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="inputName">E-Book File</label>
                                    <input type="file" id="inputName" name="file" value="<?php echo $fetch['file'];?>" class="form-control" accept=".epub,.pdf" />
                                    <h5><?php echo $fetch['file'];?></h5>
                                    <small>Only upload .epub file</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="inputName">E-Book Audio</label>
                                    <input type="file" id="inputName" name="audio_file" class="form-control" accept="audio/.mp3" />
                                    <small>Only upload .mp3 audio</small>
                                    <br>
                                    <audio controls>
                                    <source src="../images/<?php echo $fetch['audio_file'];?>" type="audio/ogg">
                                    </audio>
                                </div>
                            </div>
                           
                        <div class="form-group">
                            <input type="submit" class="btn btn-primary" name="submit" value="Submit">
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
    $(document).ready(function(abc1) {
        $("#form_abc1").on('submit', (function(abc1) {
            $("#form_abc1_data").html('');
            abc1.preventDefault();
            $.ajax({
                url: "php/edit_ebook.php",
                type: "POST",
                data: new FormData(this),
                contentType: false,
                cache: false,
                processData: false,
                success: function(data) {
                    $("#form_abc1_data").html(data);
                    $("#messid").hide();
                    $("#messidmob").hide();
                },
                error: function() {}
            });
        }));
    });
</script>
<?php
include("footer.php");
?>