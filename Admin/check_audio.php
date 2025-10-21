<?php
include("../common/config.php");
// or whatever your connection file is

// Check the structure of add_video table
echo "<h2>📋 Table Structure: add_video</h2>";
$structure = mysqli_query($conn, "DESCRIBE add_video");
echo "<table border='1' style='border-collapse: collapse; margin: 20px;'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
while ($row = mysqli_fetch_assoc($structure)) {
    echo "<tr>";
    echo "<td>".$row['Field']."</td>";
    echo "<td>".$row['Type']."</td>";
    echo "<td>".$row['Null']."</td>";
    echo "<td>".$row['Key']."</td>";
    echo "<td>".$row['Default']."</td>";
    echo "</tr>";
}
echo "</table>";

// Count total records
$total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM add_video"));
echo "<h3>Total Records in add_video: ".$total['total']."</h3>";

// Check how many have video_link
$videos = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM add_video WHERE video_link IS NOT NULL AND video_link != ''"));
echo "<p>✅ Records with <strong>video_link</strong>: ".$videos['count']."</p>";

// Check how many have audio
$audios = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM add_video WHERE audio IS NOT NULL AND audio != '' AND audio != '0'"));
echo "<p>🎵 Records with <strong>audio</strong>: ".$audios['count']."</p>";

// Show sample audio records
echo "<h2>🎵 Sample Audio Records (First 10)</h2>";
$sample = mysqli_query($conn, "SELECT id, video_name, audio, video_link FROM add_video WHERE audio IS NOT NULL AND audio != '' AND audio != '0' LIMIT 10");

if (mysqli_num_rows($sample) > 0) {
    echo "<table border='1' style='border-collapse: collapse; margin: 20px;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Audio File</th><th>Has Video Link?</th></tr>";
    while ($row = mysqli_fetch_assoc($sample)) {
        echo "<tr>";
        echo "<td>".$row['id']."</td>";
        echo "<td>".$row['video_name']."</td>";
        echo "<td style='color: blue;'>".$row['audio']."</td>";
        echo "<td>".(!empty($row['video_link']) ? '✅ Yes' : '❌ No')."</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ <strong>NO AUDIO RECORDS FOUND!</strong></p>";
    echo "<p>This means your <code>add_video</code> table does not have an <code>audio</code> column OR it's empty.</p>";
}

// Check if audio column exists
$cols = mysqli_query($conn, "SHOW COLUMNS FROM add_video LIKE 'audio'");
if (mysqli_num_rows($cols) == 0) {
    echo "<div style='background: #ffcccc; padding: 20px; margin: 20px; border-radius: 8px;'>";
    echo "<h3>⚠️ AUDIO COLUMN DOES NOT EXIST!</h3>";
    echo "<p>Your <code>add_video</code> table doesn't have an <code>audio</code> column.</p>";
    echo "<p><strong>You need to add it:</strong></p>";
    echo "<pre>ALTER TABLE add_video ADD COLUMN audio VARCHAR(255) DEFAULT NULL;</pre>";
    echo "</div>";
}

// Check add_free_videos structure too
echo "<hr><h2>📋 Table Structure: add_free_videos</h2>";
$structure2 = mysqli_query($conn, "DESCRIBE add_free_videos");
echo "<table border='1' style='border-collapse: collapse; margin: 20px;'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
while ($row = mysqli_fetch_assoc($structure2)) {
    echo "<tr>";
    echo "<td>".$row['Field']."</td>";
    echo "<td>".$row['Type']."</td>";
    echo "<td>".$row['Null']."</td>";
    echo "<td>".$row['Key']."</td>";
    echo "<td>".$row['Default']."</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>✅ Check Complete!</h3>";
?>
