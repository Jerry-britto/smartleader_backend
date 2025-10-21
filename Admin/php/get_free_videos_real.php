<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    // Include database config
    include('../../common/config.php');
    
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception('Database connection failed');
    }

    // Fetch videos with IDs 57, 58, 59
    $sql = "SELECT 
                id,
                video_name as name,
                description,
                image,
                video as file_name,
                time,
                language_key
            FROM add_video
            WHERE id IN (57, 58, 59)
            ORDER BY sort_order ASC";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        throw new Exception('Query failed: ' . mysqli_error($conn));
    }

    $videos = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $videos[] = [
            'id' => (string)$row['id'],
            'name' => $row['name'] ?? 'Untitled',
            'description' => $row['description'] ?? '',
            'image' => $row['image'] ?? '',
            'file_name' => $row['file_name'] ?? '',
            'video_link' => '',
            'time' => $row['time'] ?? '',
            'is_video' => '1',
            'language_key' => (string)($row['language_key'] ?? ''),
            'language_name' => '',
            'tag_name' => ''
        ];
    }
    
    $response = [
        'status' => 'success',
        'message' => 'Videos fetched successfully',
        'count' => count($videos),
        'data' => $videos
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    $response = [
        'status' => 'error',
        'message' => $e->getMessage(),
        'count' => 0,
        'data' => []
    ];
    
    echo json_encode($response);
}
?>
