<?php
include("header.php");
include("php/getLanguage.php");

$fetcher = new LanguageFetcher();
$languages = $fetcher->GetLanguage();
$tags = $fetcher->GetTags();
?>


<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Video</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Video</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div id="form_abc1_data"></div>
        <form id="form_abc1" enctype="multipart/form-data">
            <!-- Default box -->
            <div class="card">
                <div class="card-body row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="inputName">Video Name</label>
                                <input type="Text" id="inputName" name="video_name" class="form-control" />
                            </div>
                        </div>
                         <div class="col-6">
                            <div class="form-group">
                                <label for="language">Language</label>
                                <select class="form-control" id="language" name="language_key">
                                     <option>Select Language</option>
                                   <?php  foreach ($languages as $language) { ?>

                                    <option value="<?php echo $language['id'] ?>"><?php echo $language['value'] ?></option>
                                    
                                   <?php }  ?>
 
                                   
                                </select>
                            </div>
                        </div>
                        
                         <div class="col-6">
                            <div class="form-group">
                                <label for="language">Playlist/Tag</label>
                                <select class="form-control" id="tags" name="tag_id">
                                     <option>Playlist/Tag</option>
                                   <?php  foreach ($tags as $tag) { ?>

                                    <option value="<?php echo $tag['id'] ?>"><?php echo $tag['tags'] ?></option>
                                    
                                   <?php }  ?>
 
                                   
                                </select>
                            </div>
                        </div>
                        
                            <!--<div class="col-6">-->
                            <!--    <div class="form-group">-->
                            <!--        <label for="inputName">Video Link </label>-->
                            <!--        <input type="text" id="inputName" name="video_link" class="form-control">-->
                            <!--    </div>-->
                            <!--</div>-->
                           
                            <!--<div class="col-6">-->
                            <!--    <div class="form-group">-->
                            <!--        <label for="inputName">Video Time</label>-->
                            <!--        <input type="text" id="inputName" name="time" class="form-control" />-->
                            <!--    </div>-->
                            <!--</div>-->
                            
                            <!--<div class="col-6">-->
                            <!--   <div class="col-6">-->
                            <!--        <div class="form-group">-->
                            <!--            <label for="video_time">Video Duration</label>-->
                            <!--            <input type="text" id="video_time" name="time" class="form-control"-->
                            <!--                   placeholder="HH:MM" pattern="^([01]?[0-9]|2[0-3]):[0-5][0-9]$"-->
                            <!--                   title="Enter time in 24-hour format (HH:MM)" required>-->
                            <!--            <small class="form-text text-muted">Format: 00:00 to 23:59 (24-hour)</small>-->
                            <!--        </div>-->
                            <!--    </div>-->
                            
                            <!--</div>-->



                            <div class="col-6">
                                <div class="form-group">
                                    <label for="inputName">Thumbnail Image</label>
                                    <input type="file" name="image" id="video-upload" class="form-control">
                                      <div id="meta"></div>
                                </div>
                            </div>
                            <!--<div class="col-6">-->
                            <!--    <div class="form-group">-->
                            <!--        <label for="inputName">Upload Video</label>-->
                            <!--        <input type="file" name="video" id="video-upload" class="form-control">-->
                            <!--          <div id="meta"></div>-->
                            <!--    </div>-->
                            <!--</div>-->
                            <br><br><br><br>
                            
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Choose Video Input Type</label><br>
                                    <input type="radio" name="video_input_type" value="link" checked> Video Link
                                    <!-- <input type="radio" name="video_input_type" value="upload"> Upload Video -->
                                </div>
                            </div>
                            
                            <div class="col-6" id="video_link_field">
                                <div class="form-group">
                                    <label for="video_link">Video Link</label>
                                    <input type="text" name="video_link" class="form-control">
                                </div>
                            </div>
                            
                            <!-- Commented out upload field -->
                            <!--
                            <div class="col-6" id="video_upload_field" style="display: none;">
                                <div class="form-group">
                                    <label for="video">Upload Video</label>
                                    <input type="file" name="video" class="form-control">
                                </div>
                            </div>
                            -->

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
<script>
    const fileUpload = document.getElementById("video-upload");

fileUpload.addEventListener("change", event => {
  const resultEl = document.getElementById("meta");
  const file = event.target.files[0];
  const videoEl = document.createElement("video");
  videoEl.src = window.URL.createObjectURL(file);
  
  // When the video metadata has loaded, check
  // the video width/height
  videoEl.onloadedmetadata = event => {
    window.URL.revokeObjectURL(videoEl.src);
    const { name, type } = file;
    const { videoWidth, videoHeight } = videoEl;
    
    resultEl.innerHTML = `
      Filename: ${name}<br/>
      Type: ${type}<br/>
      Size: ${videoWidth}px x ${videoHeight}px`;
  }
  
  // If there's an error, most likely because the file
  // is not a video, display an error.
  videoEl.onerror = () => {
    resultEl.innerHTML = 'Please upload a video file.';
  }
})
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js
"></script>
<script>
    $(document).ready(function(abc1) {
        $("#form_abc1").on('submit', (function(abc1) {
          
            abc1.preventDefault();
             $("#form_abc1_data").html('<div class="alert alert-info">⏳ Please wait, data is updating...</div>');
            $.ajax({
                url: "php/add_video.php",
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
<script>
$(document).ready(function() {
    $("input[name='video_input_type']").change(function() {
        var selected = $(this).val();
        if (selected === 'upload') {
            $("#video_upload_field").show();
            $("#video_link_field").hide();
        } else if (selected === 'link') {
            $("#video_upload_field").hide();
            $("#video_link_field").show();
        }
    });
});

</script>
<?php
include("footer.php");
?>