<?php
include("header.php");
$languages = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM list"), MYSQLI_ASSOC);
$tags = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM tags"), MYSQLI_ASSOC);

$fetch=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM `add_free_videos` WHERE `id`='".$_GET['edit']."' "));

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Video/Audio</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Video/Audio</li>
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
                        <div class="col-6">
                            <div class="form-group">
                                <label for="inputName">Name</label>
                                <input type="Text" id="inputName" name="name" value="<?php echo $fetch['name'];?>" class="form-control" />
                            </div>
                        </div>
                        <div class="col-6">
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
                                <label for="tags">Playlist/Tag</label>
                                <select class="form-control" id="tags" name="tag_id">
                                    <option value="">Select Playlist/Tag</option>
                                    <?php foreach ($tags as $tag) { ?>
                                        <option value="<?php echo $tag['id']; ?>" <?php if ($fetch['tag_id'] == $tag['id']) echo 'selected'; ?>>
                                            <?php echo $tag['tags']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                            <div class="col-6">
                                <div class="form-group">
                                    <label for="inputName">Video Link</label>
                                    <input type="text" id="inputName" name="video_link" value="<?php echo $fetch['video_link'];?>" class="form-control">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="inputName">Image</label>
                                    <input type="file" id="inputName" name="image" value="<?php echo $fetch['author_name'];?>" class="form-control">
                                    <br>
                                    <img src="../images/<?php echo $fetch['image']; ?>" width="200" alt="" srcset="">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="inputName">Audio</label>
                                    <input type="file" id="inputName" name="audio" class="form-control" /><br>
                                    <video width="320" height="240" controls>
                                    <source src="../images/<?php echo $fetch['audio']; ?>" type="audio/mp3">
                                    </video>
                                </div>
                            </div>
                          <div class="col-12">
                            <div class="form-group">
                                <input type="submit" class="btn btn-primary" name="submit" value="Submit">
                            </div>
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
                url: "php/free_edit_video_audio.php",
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