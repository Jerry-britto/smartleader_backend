<?php 
// Include config FIRST (before header for AJAX)
include('../common/config.php');

// Handle position update via AJAX
if (isset($_POST['update_order'])) {
    header('Content-Type: application/json');
    
    $video_id = intval($_POST['video_id']);
    $new_position = intval($_POST['new_position']);
    
    $current_query = mysqli_query($conn, "SELECT sort_order FROM add_video WHERE id = $video_id");
    $current = mysqli_fetch_assoc($current_query);
    
    if (!$current) {
        echo json_encode(['success' => false, 'message' => 'Video not found']);
        exit;
    }
    
    $old_position = intval($current['sort_order']);
    
    if ($new_position != $old_position) {
        if ($new_position > $old_position) {
            mysqli_query($conn, "UPDATE add_video SET sort_order = sort_order - 1 
                WHERE sort_order > $old_position AND sort_order <= $new_position");
        } else {
            mysqli_query($conn, "UPDATE add_video SET sort_order = sort_order + 1 
                WHERE sort_order >= $new_position AND sort_order < $old_position");
        }
        
        mysqli_query($conn, "UPDATE add_video SET sort_order = $new_position WHERE id = $video_id");
        echo json_encode(['success' => true, 'message' => 'Order updated successfully']);
    } else {
        echo json_encode(['success' => true, 'message' => 'No change needed']);
    }
    exit;
}

// NOW include header
include("header.php"); 
$languages = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM list"), MYSQLI_ASSOC);

// Handle delete with sort_order adjustment
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    $deleted_query = mysqli_query($conn, "SELECT sort_order FROM add_video WHERE id = $delete_id");
    $deleted = mysqli_fetch_assoc($deleted_query);
    
    if ($deleted) {
        $deleted_position = intval($deleted['sort_order']);
        mysqli_query($conn, "DELETE FROM `add_video` WHERE `id`='$delete_id'");
        mysqli_query($conn, "UPDATE add_video SET sort_order = sort_order - 1 WHERE sort_order > $deleted_position");
        echo "<script>alert('Video deleted successfully!');window.location.href='show_video.php';</script>";
    }
}
?>

<link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="plugins/datatables-buttons/css/buttons.bootstrap4.min.css">

<style>
.sort-order-input {
    width: 80px;
    text-align: center;
    font-weight: bold;
}
.sort-order-input:focus {
    border-color: #007bff;
    box-shadow: 0 0 5px rgba(0,123,255,.5);
}
</style>

<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Videos</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="show_book.php">Home</a></li>
                            <li class="breadcrumb-item active">Videos</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>
        
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <h3 class="card-title">Show All Videos</h3>
                        <a href="add_video.php" class="btn btn-info ml-3">Add Video</a>
                    </div>
                    
                    <div>
                        <form method="GET" class="d-flex">
                            <select name="filter_language" class="form-control w-auto me-2">
                                <option value="">All Languages</option>
                                <?php foreach ($languages as $lang) { ?>
                                    <option value="<?php echo $lang['id']; ?>" <?php if (isset($_GET['filter_language']) && $_GET['filter_language'] == $lang['id']) echo 'selected'; ?>>
                                        <?php echo $lang['value']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <button type="submit" class="btn btn-info">Filter By Languages</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <table id="example1" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 100px;">Display Order</th>
                            <th>Language</th>
                            <th>Video Name</th>
                            <th>Duration</th>
                            <th>Thumbnail</th>
                            <th style="width: 120px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                         <?php
                        $filterLang = isset($_GET['filter_language']) && $_GET['filter_language'] != '' ? intval($_GET['filter_language']) : null;
                        
                        $sql = "SELECT add_video.*, list.value AS language_name
                                FROM add_video
                                LEFT JOIN list ON add_video.language_key = list.id";
                        
                        if ($filterLang) {
                            $sql .= " WHERE add_video.language_key = $filterLang";
                        }
                        
                        // ✅ CRITICAL: Order by sort_order ASC
                        $sql .= " ORDER BY add_video.sort_order ASC";
                        
                        $query = mysqli_query($conn, $sql);
                        $total_videos = mysqli_num_rows($query);

                        while ($fetch = mysqli_fetch_assoc($query)) {
                        ?>
                            <tr>
                                <td>
                                    <input type="number" 
                                           class="form-control sort-order-input" 
                                           value="<?php echo $fetch['sort_order']; ?>" 
                                           min="1" 
                                           max="<?php echo $total_videos; ?>"
                                           data-video-id="<?php echo $fetch['id']; ?>"
                                           data-old-value="<?php echo $fetch['sort_order']; ?>"
                                           title="Change display position (1 = first)">
                                </td>
                                <td><?php echo htmlspecialchars($fetch['language_name']); ?></td>
                                <td><?php echo htmlspecialchars($fetch['video_name']); ?></td>
                                <td><?php echo htmlspecialchars($fetch['time'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php if ($fetch['image']) { ?>
                                        <img src="../images/<?php echo $fetch['image']; ?>" width="100px" height="60px" alt="">
                                    <?php } else { ?>
                                        <span class="text-muted">No thumbnail</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <a href="edit_video.php?edit=<?php echo $fetch['id'] ?>" class="btn btn-sm btn-dark text-white">
                                        <i class="ion-compose"></i>
                                    </a>
                                    <a onclick="return confirm('Are you sure you want to delete this video?')" 
                                       href="?delete_id=<?php echo $fetch['id'] ?>" 
                                       class="btn btn-sm btn-danger text-white">
                                        <i class="ion-trash-a"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
$(function() {
    $("#example1").DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "ordering": false,
        "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
    }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
});

$(document).on('change', '.sort-order-input', function() {
    var videoId = $(this).data('video-id');
    var newPosition = parseInt($(this).val());
    var oldPosition = parseInt($(this).data('old-value'));
    var $input = $(this);
    
    if (isNaN(newPosition) || newPosition < 1) {
        alert('Please enter a valid position number (1 or higher)');
        $input.val(oldPosition);
        return;
    }
    
    if (newPosition === oldPosition) {
        return;
    }
    
    if (confirm('Move this video to position ' + newPosition + '?\n\nAll other videos will shift automatically.')) {
        $input.prop('disabled', true);
        
        $.ajax({
            url: 'show_video.php',
            type: 'POST',
            data: {
                update_order: true,
                video_id: videoId,
                new_position: newPosition
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('✓ Display order updated successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                    $input.val(oldPosition);
                    $input.prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                alert('Error updating order. Please try again.');
                $input.val(oldPosition);
                $input.prop('disabled', false);
            }
        });
    } else {
        $input.val(oldPosition);
    }
});
</script>

<?php include("footer.php"); ?>

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
