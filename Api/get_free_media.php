<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include('../common/config.php');

// Get all free videos/audio from the database
$sql = "SELECT 
    afv.id,
    afv.name,
    afv.description,
    afv.image,
    afv.audio AS file_name,
    afv.video_link,
    afv.time,
    afv.language_key,
    afv.tag_id,
    CASE 
        WHEN afv.video_link IS NOT NULL AND afv.video_link != '' THEN '1'
        ELSE '0'
    END AS is_video,
    l.value AS language_name,
    t.tags AS tag_name
FROM add_free_videos afv
LEFT JOIN list l ON afv.language_key = l.id
LEFT JOIN tags t ON afv.tag_id = t.id
ORDER BY afv.id DESC
LIMIT 100";

$query = mysqli_query($conn, $sql);

if ($query) {
    $mediaList = [];
    
    while ($row = mysqli_fetch_assoc($query)) {
        $mediaList[] = [
            'id' => $row['id'],
            'name' => $row['name'] ?? 'Untitled',
            'description' => $row['description'] ?? '',
            'image' => $row['image'],
            'file_name' => $row['file_name'], // This is the audio file
            'video_link' => $row['video_link'],
            'time' => $row['time'],
            'is_video' => $row['is_video'],
            'language_key' => $row['language_key'],
            'language_name' => $row['language_name'] ?? '',
            'tag_name' => $row['tag_name'] ?? ''
        ];
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $mediaList,
        'count' => count($mediaList)
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch media: ' . mysqli_error($conn)
    ]);
}

mysqli_close($conn);
?>
