<?php 
// Include connection first (without header)
include('../common/config.php');

// Handle position update via AJAX - MUST BE BEFORE header.php
if (isset($_POST['update_order'])) {
    header('Content-Type: application/json');
    
    $book_id = intval($_POST['book_id']);
    $new_position = intval($_POST['new_position']);
    
    // Get current position
    $current_query = mysqli_query($conn, "SELECT sort_order FROM book WHERE id = $book_id");
    $current = mysqli_fetch_assoc($current_query);
    
    if (!$current) {
        echo json_encode(['success' => false, 'message' => 'Book not found']);
        exit;
    }
    
    $old_position = intval($current['sort_order']);
    
    if ($new_position != $old_position) {
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            if ($new_position > $old_position) {
                // Moving down: decrease positions between old and new
                $shift_query = "UPDATE book SET sort_order = sort_order - 1 
                    WHERE sort_order > $old_position AND sort_order <= $new_position";
                mysqli_query($conn, $shift_query);
            } else {
                // Moving up: increase positions between new and old
                $shift_query = "UPDATE book SET sort_order = sort_order + 1 
                    WHERE sort_order >= $new_position AND sort_order < $old_position";
                mysqli_query($conn, $shift_query);
            }
            
            // Update the moved book
            $update_query = "UPDATE book SET sort_order = $new_position WHERE id = $book_id";
            mysqli_query($conn, $update_query);
            
            mysqli_commit($conn);
            echo json_encode(['success' => true, 'message' => 'Order updated successfully']);
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => true, 'message' => 'No change needed']);
    }
    exit; // CRITICAL: Exit before any HTML is output
}

// NOW include header.php for regular page view
include("header.php"); 
$languages = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM list"), MYSQLI_ASSOC);

// Handle delete with sort_order adjustment
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Get the position of deleted book
    $deleted_query = mysqli_query($conn, "SELECT sort_order FROM book WHERE id = $delete_id");
    $deleted = mysqli_fetch_assoc($deleted_query);
    
    if ($deleted) {
        $deleted_position = intval($deleted['sort_order']);
        
        // Delete the book
        mysqli_query($conn, "DELETE FROM `book` WHERE `id`='$delete_id'");
        
        // Shift all books below it up
        mysqli_query($conn, "UPDATE book SET sort_order = sort_order - 1 WHERE sort_order > $deleted_position");
        
        echo "<script>alert('Book deleted successfully!');window.location.href='show_book.php';</script>";
    }
}
?>

<!-- DataTables -->
<link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="plugins/datatables-buttons/css/buttons.bootstrap4.min.css">

<style>
.sort-order-input {
    width: 80px;
    text-align: center;
    font-weight: bold;
    padding: 5px;
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
                        <h1>Show Books</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active">Show Books</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>
        
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <h3 class="card-title">Show All Books</h3>
                        <a href="add_book.php" class="btn btn-info ml-3">Add Book</a>
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
                            <th>Book Name</th>
                            <th>Author's Name</th>
                            <th>Book Image</th>
                            <th style="width: 120px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                         <?php
                        $filterLang = isset($_GET['filter_language']) && $_GET['filter_language'] != '' ? intval($_GET['filter_language']) : null;
                        
                        $sql = "
                            SELECT book.*, list.value AS language_name
                            FROM book
                            LEFT JOIN list ON book.language_key = list.id
                        ";
                        
                        if ($filterLang) {
                            $sql .= " WHERE book.language_key = $filterLang";
                        }
                        
                        // CRITICAL: Order by sort_order ASC (this is what you control as admin)
                        $sql .= " ORDER BY book.sort_order ASC";
                        
                        $query = mysqli_query($conn, $sql);
                        $total_books = mysqli_num_rows($query);

                        while ($fetch = mysqli_fetch_assoc($query)) {
                        ?>
                            <tr data-book-id="<?php echo $fetch['id']; ?>"> 
                                <td>
                                    <input type="number" 
                                           class="form-control sort-order-input" 
                                           value="<?php echo $fetch['sort_order']; ?>" 
                                           min="1" 
                                           max="<?php echo $total_books; ?>"
                                           data-book-id="<?php echo $fetch['id']; ?>"
                                           data-old-value="<?php echo $fetch['sort_order']; ?>"
                                           title="Change display position (1 = first)">
                                </td>
                                <td><?php echo htmlspecialchars($fetch['language_name']); ?></td>
                                <td><?php echo htmlspecialchars($fetch['book_name']); ?></td>
                                <td><?php echo htmlspecialchars($fetch['writer_name']); ?></td>
                                <td>
                                    <?php if ($fetch['image']) { ?>
                                        <img src="../images/<?php echo $fetch['image']; ?>" width="100px" height="100px" alt="">
                                    <?php } else { ?>
                                        <span class="text-muted">No image</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <a href="edit_book.php?edit=<?php echo $fetch['id'] ?>" class="btn btn-dark text-white" title="Edit">
                                        <i class="ion-compose"></i>
                                    </a>
                                    <a onclick="return confirm('Are you sure you want to delete this book?')" 
                                       href="?delete_id=<?php echo $fetch['id'] ?>" 
                                       class="btn btn-danger text-white" 
                                       title="Delete">
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
    // Initialize DataTable with ordering disabled (admin controls order manually)
    $("#example1").DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "ordering": false, // IMPORTANT: Disable DataTable sorting
        "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
    }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
});

// Handle order change when user modifies the Display Order input
$(document).on('change', '.sort-order-input', function() {
    var bookId = $(this).data('book-id');
    var newPosition = parseInt($(this).val());
    var oldPosition = parseInt($(this).data('old-value'));
    var $input = $(this);
    
    // Validate input
    if (isNaN(newPosition) || newPosition < 1) {
        alert('Please enter a valid position number (1 or higher)');
        $input.val(oldPosition);
        return;
    }
    
    if (newPosition === oldPosition) {
        return; // No change
    }
    
    if (confirm('Move this book to position ' + newPosition + '?\n\nAll other books will shift automatically.')) {
        // Disable input during update
        $input.prop('disabled', true);
        
        $.ajax({
            url: 'show_book.php',
            type: 'POST',
            data: {
                update_order: true,
                book_id: bookId,
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
                console.error('Response:', xhr.responseText);
                alert('Error updating order. Please check the console and try again.');
                $input.val(oldPosition);
                $input.prop('disabled', false);
            }
        });
    } else {
        $input.val(oldPosition); // User cancelled
    }
});
</script>

<?php include("footer.php"); ?>

<!-- DataTables & Plugins -->
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
