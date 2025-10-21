<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
error_reporting(0);
ini_set('display_errors', 0);

include('../common/config.php');

if (!$conn) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed',
        'count' => 0,
        'data' => []
    ]);
    exit;
}

// Get BOTH videos and audio from add_free_videos
$sql = "SELECT 
            id,
            language_key,
            tag_id,
            name,
            description,
            audio,
            video_link,
            time,
            image,
            isFree,
            original_video_id
        FROM add_free_videos
        WHERE isFree = 1
        ORDER BY id DESC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Query failed: ' . mysqli_error($conn),
        'count' => 0,
        'data' => []
    ]);
    exit;
}

$media = [];

while ($row = mysqli_fetch_assoc($result)) {
    // ✅ KEY LOGIC: Detect if it's video or audio
    $hasVideo = !empty($row['video_link']);
    $hasAudio = !empty($row['audio']);
    
    $media[] = [
        'id' => (string)$row['id'],
        'name' => $row['name'] ?? 'Untitled',
        'description' => $row['description'] ?? '',
        'image' => $row['image'] ?? '',
        'file_name' => $row['audio'] ?? '', // ✅ Audio filename goes here
        'video_link' => $row['video_link'] ?? '',
        'time' => $row['time'] ?? '',
        'is_video' => $hasVideo ? '1' : '0', // ✅ 1 = video, 0 = audio
        'language_key' => (string)($row['language_key'] ?? ''),
        'language_name' => '',
        'tag_name' => ''
    ];
}

mysqli_close($conn);

echo json_encode([
    'status' => 'success',
    'message' => 'Media fetched successfully',
    'count' => count($media),
    'data' => $media
]);
?>
