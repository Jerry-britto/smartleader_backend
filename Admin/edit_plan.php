<?php
include("header.php");
$fetch = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM `plans` WHERE `id`='".$_GET['edit']."' "));
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1>Edit Plan</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Edit Plan</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <form id="form_plan_edit" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $fetch['id']; ?>">

            <div class="card">
                <div class="card-body row">
                    <div class="col-12">

                        <div class="form-group">
                            <label>Plan Name</label>
                            <input type="text" name="name" value="<?php echo $fetch['name']; ?>" class="form-control" required />
                        </div>

                        <div class="form-group">
                            <label>Amount</label>
                            <input type="number" name="amount" value="<?php echo $fetch['amount']; ?>" class="form-control" required />
                        </div>

                        <div class="form-group">
                            <label>Currency</label>
                            <input type="text" name="currency" value="<?php echo $fetch['currency']; ?>" class="form-control" required />
                        </div>

                        <div class="form-group">
                            <label>Interval</label>
                            <select name="interval" class="form-control" required>
                                <option value="1" <?php if($fetch['interval']=="1"){echo "selected";}?>>Monthly</option>
                                <option value="3" <?php if($fetch['interval']=="3"){echo "selected";}?>>Quarterly (3 months)</option>
                                <option value="6" <?php if($fetch['interval']=="6"){echo "selected";}?>>Half-Yearly (6 months)</option>
                                <option value="12" <?php if($fetch['interval']=="12"){echo "selected";}?>>Yearly</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Period</label>
                            <input type="text" name="period" value="<?php echo $fetch['period']; ?>" class="form-control" required />
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control" required>
                                <option value="active" <?php if($fetch['status']=="active"){echo "selected";}?>>Active</option>
                                <option value="inactive" <?php if($fetch['status']=="inactive"){echo "selected";}?>>Inactive</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <input type="submit" class="btn btn-primary" value="Update Plan">
                        </div>

                    </div>
                </div>
            </div>
            <div id="form_plan_edit_data"></div>
        </form>
    </section>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
$(document).ready(function() {
    $("#form_plan_edit").on('submit',(function(e) {
        e.preventDefault();
        $("#form_plan_edit_data").html('');
        $.ajax({
            url: "php/edit_plan.php",
            type: "POST",
            data:  new FormData(this),
            contentType: false,
            cache: false,
            processData:false,
            success: function(data){     
                $("#form_plan_edit_data").html(data);
            },   
            error: function(){}          
        });
    }));
});
</script>

<?php include("footer.php"); ?>
