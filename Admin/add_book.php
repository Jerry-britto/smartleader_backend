<?php
include("header.php");
include("php/getLanguage.php");
$fetcher = new LanguageFetcher();
$languages = $fetcher->GetLanguage();
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Add </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
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
            <!-- Default box -->
            <div class="card">
                <div class="card-body row">
                 

                        <div class="col-4">
                            <div class="form-group">
                                <label for="inputName">Book Name</label>
                                <input type="Text" id="inputName" name="book_name" class="form-control" />
                            </div>
                        </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="inputName">Author’s Name</label>
                                    <input type="text" id="inputName" name="writer_name" class="form-control">
                                </div>
                            </div>
                            <div class="col-4">
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
                                    <label for="inputName">Book Image</label>
                                    <input type="file" id="inputName" name="image" class="form-control" />
                                </div>
                            </div>
                            <!--<div class="col-4">-->
                            <!--    <div class="form-group">-->
                            <!--        <label for="inputName">Book Price</label>-->
                            <!--        <input type="text" id="inputName" name="book_price" class="form-control" />-->
                            <!--    </div>-->
                            <!--</div>-->
                            <!--<div class="col-4">-->
                            <!--    <div class="form-group">-->
                            <!--        <label for="inputName">E-Book File</label>-->
                            <!--        <input type="file" id="inputName" name="file" class="form-control" accept=".epub,.pdf" />-->
                            <!--        <small>Only upload .epub file</small>-->
                            <!--    </div>-->
                            <!--</div>-->
                            <!--<div class="col-4">-->
                            <!--    <div class="form-group">-->
                            <!--        <label for="inputName">E-Book Price</label>-->
                            <!--        <input type="text" id="inputName" name="e_book_price" class="form-control" />-->
                            <!--    </div>-->
                            <!--</div>-->
                            <!--<div class="col-4">-->
                            <!--    <div class="form-group">-->
                            <!--        <label for="inputName">E-Book Audio</label>-->
                            <!--        <input type="file" id="inputName" name="audio_file" class="form-control" accept="audio/.mp3" />-->
                            <!--        <small>Only upload .mp3 audio</small>-->
                            <!--    </div>-->
                            <!--</div>-->
                            <!--<div class="col-4">-->
                            <!--    <div class="form-group">-->
                            <!--        <label for="inputName">Audio Price</label>-->
                            <!--        <input type="text" id="inputName" name="audio_price" class="form-control" />-->
                            <!--    </div>-->
                            <!--</div>-->
                            <!--<div class="col-4">-->
                            <!--    <div class="form-group">-->
                            <!--        <label for="inputName">Amazon Link</label>-->
                            <!--        <input type="text" id="inputName" name="amazon_link" class="form-control" />-->
                            <!--    </div>-->
                            <!--</div>-->
                            <!--<div class="col-4">-->
                            <!--    <div class="form-group">-->
                            <!--        <label for="inputName">Amazon Price</label>-->
                            <!--        <input type="text" id="inputName" name="amazon_price" class="form-control" />-->
                            <!--    </div>-->
                            <!--</div>-->
                            <!--<div class="col-4">-->
                            <!--    <div class="form-group">-->
                            <!--        <label for="inputName">Flipkart Link</label>-->
                            <!--        <input type="text" id="inputName" name="flipkart_link" class="form-control" />-->
                            <!--    </div>-->
                            <!--</div>-->
                            <!--<div class="col-4">-->
                            <!--    <div class="form-group">-->
                            <!--        <label for="inputName">Flipkart Price</label>-->
                            <!--        <input type="text" id="inputName" name="flipkart_price" class="form-control" />-->
                            <!--    </div>-->
                            <!--</div>-->
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="inputName">Book Summary Audio</label>
                                    <input type="file" id="inputName" name="book_audio" class="form-control" />
                                </div>
                            </div>
                            
                            <!--<div class="col-6">-->
                            <!--    <div class="form-group">-->
                            <!--            <label for="inputName">Book Description</label>-->
                            <!--            <textarea type="text" id="inputName" name="description" class="form-control"></textarea>-->
                            <!--    </div>-->
                            <!--</div>-->
                            
                            
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js
"></script>
<script>
    $(document).ready(function(abc1) {
        $("#form_abc1").on('submit', (function(abc1) {
            $("#form_abc1_data").html('');
            abc1.preventDefault();
            $.ajax({
                url: "php/add_book.php",
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