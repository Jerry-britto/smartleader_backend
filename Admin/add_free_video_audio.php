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
                    <h1>Add Free Video</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Free Video</li>
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
                                <label for="inputName">Name</label>
                                <input type="Text" id="inputName" name="name" class="form-control" />
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
                                    <label for="inputName">Image</label>
                                    <input type="file" name="image" id="video-upload" class="form-control">
                                      <div id="meta"></div>
                                </div>
                            </div>
                            
                            <br><br><br><br>
                            
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Choose Input Type</label><br>
                                    <input type="radio" name="video_input_type" value="link" checked> Video Link
                                     <input type="radio" name="video_input_type" value="upload"> Upload Audio 
                                </div>
                            </div>
                            
                            <div class="col-6" id="video_link_field">
                                <div class="form-group">
                                    <label for="video_link">Video Link</label>
                                    <input type="text" name="video_link" class="form-control">
                                </div>
                            </div>
                            
                            <div class="col-6" id="video_upload_field" style="display: none;">
                                <div class="form-group">
                                    <label for="video">Upload Audio</label>
                                    <input type="file" name="audio" class="form-control">
                                </div>
                            </div>
                            
                            <!-- NEW: isFree Checkbox -->
                            <div class="col-6">
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="isFree" value="1" checked> Mark as Free Content
                                    </label>
                                    <small class="form-text text-muted">Check this to make content available for free users</small>
                                </div>
                            </div>
                      

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
  const videoEl = document.createElement("audio");
  videoEl.src = window.URL.createObjectURL(file);
  
  videoEl.onloadedmetadata = event => {
    window.URL.revokeObjectURL(videoEl.src);
    const { name, type } = file;
    const { videoWidth, videoHeight } = videoEl;
    
    resultEl.innerHTML = `
      Filename: ${name}<br/>
      Type: ${type}<br/>
      Size: ${videoWidth}px x ${videoHeight}px`;
  }
  
  videoEl.onerror = () => {
    resultEl.innerHTML = 'Please upload a audio file.';
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
                url: "php/free_add_video_audio.php",
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
