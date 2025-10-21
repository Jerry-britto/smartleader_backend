<?php
include("header.php");
$languages = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM list"), MYSQLI_ASSOC);
$fetch=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM `book` WHERE `id`='".$_GET['edit']."' "));
$tag=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM `tags` WHERE `id`='".$fetch['tag_id']."' "));
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Book</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Book</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
         <div id="form_abc1_data"></div>
        <form id="form_abc1" enctype="multipart/form-data">
        <input type="hidden" name="ids" value="<?php echo $_GET['edit']; ?>">
            <!-- Default box -->
            <div class="card">
                <div class="card-body row">
                  
                        <?php
                        $fetch=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM `book` WHERE `id`='".$_GET['edit']."' "));
                        ?>
                        <div class="col-4">
                            <div class="form-group">
                                <label for="inputName">Book Name</label>
                                <input type="Text" id="inputName" name="book_name" value="<?php echo $fetch['book_name'];?>" class="form-control" />
                            </div>
                        </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="inputName">Author's Name</label>
                                    <input type="text" id="inputName" name="writer_name" value="<?php echo $fetch['writer_name'];?>" class="form-control">
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="language">Language</label>
                                    <select class="form-control" id="language" name="language_key">
                                        <option value="">Select Language</option>
                                        <?php foreach ($languages as $language) { ?>
                                            <option value="<?php echo $language['id']; ?>" <?php if ($fetch['language_key'] == $language['id']) echo 'selected'; ?>>
                                                <?php echo $language['value']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                           
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="inputName">Book Image</label>
                                    <input type="file" id="inputName" name="image" class="form-control" /><br>
                                    <img src="../images/<?php echo $fetch['image'];?>" width="80px" alt="" srcset="">
                                </div>
                            </div>
                           
                            
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="inputName">Book Summary Audio</label>
                                    <input type="file" id="inputName" name="book_audio"  value="<?php echo $fetch['book_audio'];?>"class="form-control" />
                                </div>
                                <br>
                                    <audio controls>
                                    <source src="../images/<?php echo $fetch['book_audio'];?>" type="audio/ogg">
                                    </audio>
                            </div>
                            
                            <!-- Keep sort_order as hidden, don't let them edit it here -->
                            <input type="hidden" name="sort_order" value="<?php echo $fetch['sort_order'];?>">
                            
                       <div class="col-12">
                            <div class="form-group">
                            <input type="submit" class="btn btn-primary" name="submit" value="Submit">
                        </div>
                       </div>
                    
                </div>
            </div>
           
        </form>

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
    $(document).ready(function(abc1) {
        $("#form_abc1").on('submit', (function(abc1) {
            $("#form_abc1_data").html('');
            abc1.preventDefault();
            $.ajax({
                url: "php/edit_book.php",
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
