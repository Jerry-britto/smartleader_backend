<?php include("header.php"); ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1>Add Plan</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Add Plan</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <form id="form_plan" enctype="multipart/form-data">
            <div class="card">
                <div class="card-body row">
                    <div class="col-12">

                        <div class="form-group">
                            <label>Plan Name</label>
                            <input type="text" name="name" class="form-control" required />
                        </div>

                        <div class="form-group">
                            <label>Amount</label>
                            <input type="number" name="amount" class="form-control" required />
                        </div>

                        <div class="form-group">
                            <label>Currency</label>
                            <input type="text" name="currency" class="form-control" placeholder="USD, INR" required />
                        </div>

                        <div class="form-group">
                            <label>Period</label>
                            <select name="period" class="form-control" required>
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Interval</label>
                            <input type="number" name="interval" class="form-control"required />
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <input type="submit" class="btn btn-primary" value="Add Plan">
                        </div>

                    </div>
                </div>
            </div>
            <div id="form_plan_data"></div>
        </form>
    </section>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
$(document).ready(function() {
    $("#form_plan").on('submit',(function(e) {
        e.preventDefault();
        $("#form_plan_data").html('');
        $.ajax({
            url: "php/add_plan.php",
            type: "POST",
            data:  new FormData(this),
            contentType: false,
            cache: false,
            processData:false,
            success: function(data){     
                $("#form_plan_data").html(data);
            },   
            error: function(){}          
        });
    }));
});
</script>

<?php include("footer.php"); ?>
