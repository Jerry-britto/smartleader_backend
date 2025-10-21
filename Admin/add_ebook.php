<?php
include("header.php");
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
            <!-- Default box -->
            <div class="card">
                <div class="card-body row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="inputName"> E-Book Name</label>
                                <select class="form-control" id="exampleFormControlSelect1" name="book_id">
                                 
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
                            
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="inputName">E-Book File</label>
                                    <input type="file" id="inputName" name="file" class="form-control" accept=".epub,.pdf" />
                                    <small>Only upload .epub file</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="inputName">E-Book Audio</label>
                                    <input type="file" id="inputName" name="audio_file" class="form-control" accept="audio/.mp3" />
                                    <small>Only upload .mp3 audio</small>
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
                url: "php/add_ebook.php",
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