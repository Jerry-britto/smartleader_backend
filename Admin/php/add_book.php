<?php
include('../../common/config.php');

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Extract POST variables
    $language_key = $_POST['language_key'] ?? '';
    $tag_id = $_POST['tag_id'] ?? '';
    $book_name = $_POST['book_name'] ?? '';
    $writer_name = $_POST['writer_name'] ?? '';
    $description = $_POST['description'] ?? '';
    $book_price = $_POST['book_price'] ?? '';
    $amazon_link = $_POST['amazon_link'] ?? '';
    $amazon_price = $_POST['amazon_price'] ?? '';
    $flipkart_link = $_POST['flipkart_link'] ?? '';
    $flipkart_price = $_POST['flipkart_price'] ?? '';
    $e_book_price = $_POST['e_book_price'] ?? '';
    $audio_price = $_POST['audio_price'] ?? '';

    // Get the maximum sort_order and add 1 for the new book (NEW CODE)
    $max_query = $conn->query("SELECT MAX(sort_order) as max_order FROM book");
    $max_result = $max_query->fetch_assoc();
    $new_sort_order = ($max_result['max_order'] ?? 0) + 1;

    // Define the target directory for file uploads
    $target_dir = '../../images/';

    // Function to handle file uploads
    function handleFileUpload($file_input_name) {
        global $target_dir;
        
        if (!isset($_FILES[$file_input_name]) || $_FILES[$file_input_name]['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        
        if ($_FILES[$file_input_name]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        
        $file_name = $_FILES[$file_input_name]['name'];
        if (!empty($file_name)) {
            // Create unique filename to prevent overwriting
            $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
            $unique_filename = time() . '_' . uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $unique_filename;
            
            if (move_uploaded_file($_FILES[$file_input_name]['tmp_name'], $target_file)) {
                return $unique_filename;
            } else {
                return null;
            }
        }
        return null;
    }

    // Handle file uploads
    $image = handleFileUpload('image');
    $book_audio = handleFileUpload('book_audio');
    $file = handleFileUpload('file');
    $audio_file = handleFileUpload('audio_file');

    // Prepare SQL statement to prevent SQL injection (ADDED sort_order column)
    $stmt = $conn->prepare("INSERT INTO `book` 
        (`language_key`, `tag_id`, `book_name`, `writer_name`, `description`, `image`, `book_price`, `amazon_link`, 
        `amazon_price`, `flipkart_link`, `flipkart_price`, `book_audio`, `file`, `e_book_price`, `audio_file`, `audio_price`, `sort_order`, `created_at`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    // Bind parameters (ADDED sort_order parameter - note the extra 'i' for integer)
    $stmt->bind_param(
        'ssssssssssssssssi',
        $language_key, $tag_id, $book_name, $writer_name, $description, $image, $book_price,
        $amazon_link, $amazon_price, $flipkart_link, $flipkart_price, $book_audio, $file,
        $e_book_price, $audio_file, $audio_price, $new_sort_order
    );

    // Execute the statement
    if ($stmt->execute()) {
        $book_id = $stmt->insert_id;
        echo '<div class="alert alert-success">
            <button type="button" class="close" data-dismiss="alert" style="margin-left: 5px">×</button>
            <strong>Success!</strong> Book added successfully at display position ' . $new_sort_order . '!<br>
            Book ID: ' . $book_id . '</div>';
        echo '<script>setTimeout(function(){window.location.href="../show_book.php"},1500)</script>';
    } else {
        // If database insert fails, delete uploaded files
        if ($image && file_exists($target_dir . $image)) {
            unlink($target_dir . $image);
        }
        if ($book_audio && file_exists($target_dir . $book_audio)) {
            unlink($target_dir . $book_audio);
        }
        if ($file && file_exists($target_dir . $file)) {
            unlink($target_dir . $file);
        }
        if ($audio_file && file_exists($target_dir . $audio_file)) {
            unlink($target_dir . $audio_file);
        }
        
        echo '<div class="alert alert-danger">
            <button type="button" class="close" data-dismiss="alert" style="margin-left: 5px">×</button>
            <strong>Failed!</strong> ' . $stmt->error . '</div>';
    }

    // Close the statement
    $stmt->close();

    // Close the database connection
    $conn->close();
} else {
    echo '<div class="alert alert-danger">Invalid request method.</div>';
}
?>
